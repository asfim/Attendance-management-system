<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mithun\PhpZkteco\Libs\ZKTeco;
use Illuminate\Support\Carbon;
use App\Models\BiometricDevice;
use App\Services\AttendanceSyncService;
use Exception;

class ZKTecoListenCommand extends Command
{
    protected $signature = 'biometric:listen {ip?} {port=4370}';
    protected $description = 'Listen for real-time live events from a ZKTeco device';

    public function handle(AttendanceSyncService $syncService)
    {
        $ip = $this->argument('ip');
        $port = (int) $this->argument('port');

        if (!$ip) {
            $device = BiometricDevice::where('status', 'online')->first();
            if (!$device) {
                $this->error("No IP provided and no online devices found in the database.");
                return Command::FAILURE;
            }
            $ip = $device->ip_address;
            $port = $device->port ?? 4370;
            $this->info("Using default online device IP: $ip");
        }

        while (true) {
            $this->info("Connecting to ZKTeco Device at $ip:$port for LIVE Listening...");

            $zk = new \App\Services\ZKTecoService($ip, $port);
            
            if (!$zk->connect()) {
                $this->error("Failed to connect to device at $ip:$port. Retrying in 5 seconds...");
                sleep(5);
                continue;
            }

            $this->info("Connected successfully! Polling for new punches every 2 seconds. Press Ctrl+C to stop.");

            try {
                while (true) {
                    $logs = $zk->getAttendanceLogs();
                    
                    if (!empty($logs)) {
                        $newPunches = 0;
                        foreach ($logs as $logData) {
                            // Check if log already exists
                            $exists = \App\Models\BiometricDeviceLog::where('biometric_id', $logData['device_user_id'])
                                ->where('punch_time', $logData['timestamp'])
                                ->exists();

                            if (!$exists) {
                                $this->line("[" . Carbon::now()->format('H:i:s') . "] New Punch Found! User ID: {$logData['device_user_id']} | Time: {$logData['timestamp']}");
                                
                                $biometricId = $logData['device_user_id'];
                                
                                $profile = \App\Models\StudentProfile::where('biometric_id', $biometricId)
                                    ->orWhere('admission_no', $biometricId)
                                    ->orWhere('roll_no', $biometricId)
                                    ->first();
                                $userCategory = 'student';

                                if (!$profile) {
                                    $profile = \App\Models\StaffProfile::where('biometric_id', $biometricId)
                                        ->orWhere('phone', $biometricId)
                                        ->first();
                                    if ($profile) {
                                        $userCategory = 'staff';
                                    } else {
                                        $userCategory = 'unknown';
                                    }
                                }

                                $userName = 'Unknown User';
                                if ($profile) {
                                    $userName = $profile->user->name ?? 'Unknown User';
                                }

                                \App\Models\BiometricDeviceLog::create([
                                    'device_sn' => 'ZK_' . $ip,
                                    'biometric_id' => $logData['device_user_id'],
                                    'punch_time' => \Illuminate\Support\Carbon::parse($logData['timestamp']),
                                    'punch_state' => $logData['punch_type'],
                                    'user_type' => $userCategory,
                                    'user_name' => $userName,
                                    'status' => 'success',
                                    'message' => 'Live Polling Sync',
                                    'raw_payload' => json_encode($logData),
                                    'ip_address' => $ip,
                                ]);

                                // Process Real-time core attendance table update!
                                if ($profile) {
                                    $attendableType = ($userCategory === 'student') ? \App\Models\StudentProfile::class : \App\Models\StaffProfile::class;
                                    $punchTimeObj = \Illuminate\Support\Carbon::parse($logData['timestamp']);
                                    
                                    $allPunchesForDay = \App\Models\BiometricDeviceLog::where('biometric_id', $biometricId)
                                        ->whereDate('punch_time', $punchTimeObj->format('Y-m-d'))
                                        ->where('status', 'success')
                                        ->orderBy('punch_time', 'asc')
                                        ->pluck('punch_time')
                                        ->map(function ($date) {
                                            return \Illuminate\Support\Carbon::parse($date)->toDateTimeString();
                                        })
                                        ->toArray();

                                    try {
                                        app(\App\Services\AttendanceProcessingService::class)->processDailyPunches(
                                            $profile,
                                            $attendableType,
                                            $punchTimeObj->format('Y-m-d'),
                                            $allPunchesForDay
                                        );
                                        
                                        // Send SMS for students
                                        if ($userCategory === 'student') {
                                            $state = count($allPunchesForDay) === 1 ? 'Check-In' : 'Check-Out';
                                            app(\App\Services\SmsService::class)->sendAttendanceSms($profile, $state, $punchTimeObj);
                                        }
                                        
                                    } catch (\Exception $e) {
                                        $this->error("Failed to process core attendance for {$userName}: " . $e->getMessage());
                                    }
                                }
                                
                                $newPunches++;
                            }
                        }
                    }

                    // Poll every 2 seconds
                    sleep(2);
                }
            } catch (Exception $e) {
                $this->error("Live listener crashed: " . $e->getMessage() . " - Reconnecting...");
            }

            $zk->disconnect();
            sleep(5);
        }
    }
}
