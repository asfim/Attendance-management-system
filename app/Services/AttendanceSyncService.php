<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceLog;
use App\Models\DeviceSyncLog;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AttendanceSyncService
{
    protected AttendanceProcessingService $processingService;

    public function __construct(AttendanceProcessingService $processingService)
    {
        $this->processingService = $processingService;
    }

    /**
     * Synchronize attendance data from a specific biometric device.
     */
    public function syncDevice(BiometricDevice $device, string $syncType = 'manual'): array
    {
        if (!$device->is_active) {
            return [
                'success' => false,
                'message' => "Device '{$device->name}' is inactive. Skipping sync.",
                'summary' => ['total' => 0, 'new' => 0, 'duplicate' => 0, 'unmapped' => 0, 'failed' => 0]
            ];
        }

        // Prevent simultaneous sync requests using Laravel Atomic Cache Lock
        $lockKey = "sync_device_{$device->id}";
        $lock = Cache::lock($lockKey, 60);

        if (!$lock->get()) {
            return [
                'success' => false,
                'message' => "Sync is already in progress for device '{$device->name}'.",
                'summary' => ['total' => 0, 'new' => 0, 'duplicate' => 0, 'unmapped' => 0, 'failed' => 0]
            ];
        }

        // Mark device status as syncing
        $device->update(['status' => 'syncing']);
        $startTime = now();

        $syncLog = DeviceSyncLog::create([
            'device_id' => $device->id,
            'sync_type' => $syncType,
            'started_at' => $startTime,
            'status' => 'running',
        ]);

        $totalRecords = 0;
        $newRecords = 0;
        $duplicateRecords = 0;
        $unmappedRecords = 0;
        $failedRecords = 0;

        try {
            $zk = new ZKTecoService($device->ip_address, $device->port, $device->comm_key ?? '0');

            if (!$zk->connect()) {
                $errorMsg = "Unable to connect device '{$device->name}' at {$device->ip_address}:{$device->port}.";
                $device->update([
                    'status' => 'offline',
                    'last_error' => $errorMsg,
                ]);

                $syncLog->update([
                    'completed_at' => now(),
                    'status' => 'failed',
                    'error_message' => $errorMsg,
                ]);

                $lock->release();
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'summary' => ['total' => 0, 'new' => 0, 'duplicate' => 0, 'unmapped' => 0, 'failed' => 1]
                ];
            }

            $rawLogs = $zk->getAttendanceLogs();
            $totalRecords = count($rawLogs);

            // Group punches by user & date for daily processing
            $profilePunchesMap = [];

            foreach ($rawLogs as $logItem) {
                $deviceUserId = $logItem['device_user_id'];
                $punchTimeStr = $logItem['timestamp'];
                $verifyType = $logItem['verify_type'] ?? null;
                $punchType = $logItem['punch_type'] ?? 'check_in';

                try {
                    $punchTime = Carbon::parse($punchTimeStr);
                    $dateStr = $punchTime->format('Y-m-d');

                    // Hash unique key: device_id + device_user_id + punch_time
                    $uniqueKey = md5("{$device->id}_{$deviceUserId}_{$punchTimeStr}");

                    // Check for duplicate log entry
                    $existing = BiometricDeviceLog::where('unique_key', $uniqueKey)->first();
                    if ($existing) {
                        $duplicateRecords++;
                        continue;
                    }

                    // Map to Student or Staff
                    $profile = StudentProfile::where('biometric_id', $deviceUserId)
                        ->orWhere('admission_no', $deviceUserId)
                        ->orWhere('roll_no', $deviceUserId)
                        ->first();
                    $userCategory = 'student';
                    $attendableType = StudentProfile::class;

                    if (!$profile) {
                        $profile = StaffProfile::where('biometric_id', $deviceUserId)
                            ->orWhere('phone', $deviceUserId)
                            ->first();
                        $userCategory = 'staff';
                        $attendableType = StaffProfile::class;
                    }

                    if (!$profile) {
                        // Unmapped Employee / Student
                        BiometricDeviceLog::create([
                            'device_id' => $device->id,
                            'device_sn' => $device->serial_number ?? $device->name,
                            'biometric_id' => $deviceUserId,
                            'punch_time' => $punchTime,
                            'punch_state' => $punchType,
                            'verify_type' => $verifyType,
                            'unique_key' => $uniqueKey,
                            'user_type' => 'unknown',
                            'status' => 'unmapped',
                            'message' => "Unmapped Biometric Device User ID: {$deviceUserId}",
                            'raw_payload' => json_encode($logItem),
                            'ip_address' => $device->ip_address,
                        ]);

                        $unmappedRecords++;
                        $newRecords++;
                        continue;
                    }

                    // Save Mapped Log Entry
                    $userName = $profile->user->name ?? 'User #' . $profile->id;
                    BiometricDeviceLog::create([
                        'device_id' => $device->id,
                        'device_sn' => $device->serial_number ?? $device->name,
                        'biometric_id' => $deviceUserId,
                        'punch_time' => $punchTime,
                        'punch_state' => $punchType,
                        'verify_type' => $verifyType,
                        'unique_key' => $uniqueKey,
                        'user_type' => $userCategory,
                        'user_name' => $userName,
                        'status' => 'success',
                        'message' => "Punch recorded for {$userName}",
                        'raw_payload' => json_encode($logItem),
                        'ip_address' => $device->ip_address,
                    ]);

                    $newRecords++;

                    // Collect punches for daily attendance processing
                    $mapKey = "{$attendableType}_{$profile->id}_{$dateStr}";
                    if (!isset($profilePunchesMap[$mapKey])) {
                        $profilePunchesMap[$mapKey] = [
                            'profile' => $profile,
                            'attendable_type' => $attendableType,
                            'date' => $dateStr,
                            'punches' => [],
                        ];
                    }
                    $profilePunchesMap[$mapKey]['punches'][] = $punchTimeStr;

                } catch (Exception $ex) {
                    Log::error("Failed to parse ZKTeco punch record: " . $ex->getMessage());
                    $failedRecords++;
                }
            }

            // Process daily attendance summary for all affected profiles
            foreach ($profilePunchesMap as $item) {
                $this->processingService->processDailyPunches(
                    $item['profile'],
                    $item['attendable_type'],
                    $item['date'],
                    $item['punches']
                );
            }

            $device->update([
                'status' => 'online',
                'last_connected_at' => now(),
                'last_sync_at' => now(),
                'last_error' => null,
            ]);

            $syncLog->update([
                'completed_at' => now(),
                'total_records' => $totalRecords,
                'new_records' => $newRecords,
                'duplicate_records' => $duplicateRecords,
                'unmapped_records' => $unmappedRecords,
                'failed_records' => $failedRecords,
                'status' => 'completed',
            ]);

            $lock->release();

            return [
                'success' => true,
                'message' => "Sync Completed for '{$device->name}'!",
                'summary' => [
                    'total' => $totalRecords,
                    'new' => $newRecords,
                    'duplicate' => $duplicateRecords,
                    'unmapped' => $unmappedRecords,
                    'failed' => $failedRecords,
                ]
            ];

        } catch (Exception $e) {
            Log::error("ZKTeco Sync Service Exception: " . $e->getMessage());
            $device->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);

            $syncLog->update([
                'completed_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $lock->release();

            return [
                'success' => false,
                'message' => "Sync Error: " . $e->getMessage(),
                'summary' => [
                    'total' => $totalRecords,
                    'new' => $newRecords,
                    'duplicate' => $duplicateRecords,
                    'unmapped' => $unmappedRecords,
                    'failed' => $failedRecords + 1,
                ]
            ];
        }
    }
}
