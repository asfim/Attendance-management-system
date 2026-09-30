<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\BiometricDeviceLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BiometricSyncController extends Controller
{
    /**
     * Standard JSON API Endpoint for Biometric / Fingerprint Sync
     */
    public function sync(Request $request)
    {
        $request->validate([
            'card_uid' => 'required|string',
            'timestamp' => 'nullable|date',
            'type' => 'nullable|string|in:student,employee,staff,teacher,auto',
            'device_sn' => 'nullable|string',
            'punch_state' => 'nullable|string|in:check_in,check_out,auto',
        ]);

        $cardUid = trim($request->card_uid);
        $time = $request->filled('timestamp') ? Carbon::parse($request->timestamp) : Carbon::now();
        $dateStr = $time->format('Y-m-d');
        $timeStr = $time->format('H:i:s');
        $deviceSn = $request->input('device_sn', 'DEFAULT_DEVICE');
        $type = strtolower($request->input('type', 'auto'));

        $profile = null;
        $attendableType = null;
        $userCategory = null;

        // 1. Try student lookup if explicitly student or auto
        if (in_array($type, ['student', 'auto'])) {
            $profile = StudentProfile::where('biometric_id', $cardUid)
                ->orWhere('admission_no', $cardUid)
                ->orWhere('roll_no', $cardUid)
                ->first();

            if ($profile) {
                $attendableType = StudentProfile::class;
                $userCategory = 'student';
            }
        }

        // 2. Try staff/teacher lookup if explicitly staff/employee/teacher or auto (and not found yet)
        if (!$profile && in_array($type, ['employee', 'staff', 'teacher', 'auto'])) {
            $profile = StaffProfile::where('biometric_id', $cardUid)
                ->orWhere('phone', $cardUid)
                ->orWhere('national_id', $cardUid)
                ->first();

            if ($profile) {
                $attendableType = StaffProfile::class;
                $userCategory = 'staff';
            }
        }

        if (!$profile) {
            BiometricDeviceLog::create([
                'device_sn' => $deviceSn,
                'biometric_id' => $cardUid,
                'punch_time' => $time,
                'punch_state' => $request->input('punch_state', 'check_in'),
                'user_type' => 'unknown',
                'status' => 'failed',
                'message' => 'No Student or Staff profile found matching biometric ID / Card UID: ' . $cardUid,
                'raw_payload' => json_encode($request->all()),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No student or teacher profile found matching biometric ID: ' . $cardUid,
            ], 404);
        }

        // Process Attendance Record
        $attendanceResult = $this->recordAttendance($profile, $attendableType, $dateStr, $timeStr, $time, $userCategory);

        // Audit log
        $userName = $profile->user->name ?? 'User #' . $profile->id;
        BiometricDeviceLog::create([
            'device_sn' => $deviceSn,
            'biometric_id' => $cardUid,
            'punch_time' => $time,
            'punch_state' => $attendanceResult['action'],
            'user_type' => $userCategory,
            'user_name' => $userName,
            'status' => 'success',
            'message' => "Attendance recorded as {$attendanceResult['status']} ({$attendanceResult['action']})",
            'raw_payload' => json_encode($request->all()),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Fingerprint attendance recorded successfully for {$userName}.",
            'record' => [
                'name' => $userName,
                'user_type' => $userCategory,
                'status' => $attendanceResult['status'],
                'entry_time' => $attendanceResult['entry_time'],
                'exit_time' => $attendanceResult['exit_time'],
                'timestamp' => $time->toIso8601String(),
            ]
        ]);
    }

    /**
     * ZKTeco ADMS Push Handshake / Heartbeat (GET /iclock/cdata)
     */
    public function admsHandshake(Request $request)
    {
        return response("OK\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * ZKTeco ADMS Push Attendance Punch Receiver (POST /iclock/cdata)
     */
    public function admsReceive(Request $request)
    {
        $sn = $request->query('SN', 'ZKTECO_DEVICE');
        $rawContent = $request->getContent();
        
        Log::info("ZKTeco ADMS Data received from SN {$sn}: " . substr($rawContent, 0, 500));

        $lines = explode("\n", str_replace("\r", "", $rawContent));
        $processedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Standard ZKTeco format: USER_ID \t TIMESTAMP \t STATUS \t VERIFY_TYPE ...
            $parts = explode("\t", $line);
            if (count($parts) >= 2) {
                $biometricId = trim($parts[0]);
                $punchTimeStr = trim($parts[1]);

                try {
                    $punchTime = Carbon::parse($punchTimeStr);
                    $dateStr = $punchTime->format('Y-m-d');
                    $timeStr = $punchTime->format('H:i:s');

                    // Find student or staff
                    $profile = StudentProfile::where('biometric_id', $biometricId)
                        ->orWhere('admission_no', $biometricId)
                        ->orWhere('roll_no', $biometricId)
                        ->first();
                    $userCategory = 'student';
                    $attendableType = StudentProfile::class;

                    if (!$profile) {
                        $profile = StaffProfile::where('biometric_id', $biometricId)
                            ->orWhere('phone', $biometricId)
                            ->first();
                        $userCategory = 'staff';
                        $attendableType = StaffProfile::class;
                    }

                    if ($profile) {
                        $res = $this->recordAttendance($profile, $attendableType, $dateStr, $timeStr, $punchTime, $userCategory);
                        $userName = $profile->user->name ?? 'User #' . $profile->id;

                        BiometricDeviceLog::create([
                            'device_sn' => $sn,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => $res['action'],
                            'user_type' => $userCategory,
                            'user_name' => $userName,
                            'status' => 'success',
                            'message' => "ZKTeco ADMS Punch recorded as {$res['status']}",
                            'raw_payload' => $line,
                            'ip_address' => $request->ip(),
                        ]);
                        $processedCount++;
                    } else {
                        BiometricDeviceLog::create([
                            'device_sn' => $sn,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => 'check_in',
                            'user_type' => 'unknown',
                            'status' => 'failed',
                            'message' => "Unrecognized Biometric ID: {$biometricId}",
                            'raw_payload' => $line,
                            'ip_address' => $request->ip(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("ZKTeco ADMS parsing error: " . $e->getMessage());
                }
            }
        }

        return response("OK: {$processedCount}\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * ZKTeco Command Polling Endpoint (GET /iclock/getrequest)
     */
    public function admsGetRequest(Request $request)
    {
        return response("OK\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Helper to process attendance record for student/staff
     */
    private function recordAttendance($profile, string $attendableType, string $dateStr, string $timeStr, Carbon $punchTime, string $userCategory): array
    {
        $existing = Attendance::whereDate('attendance_date', $dateStr)
            ->where('attendable_type', $attendableType)
            ->where('attendable_id', $profile->id)
            ->first();

        // Late cutoff time threshold (default 09:15:00 unless shift specifies otherwise)
        $lateCutoff = '09:15:00';
        if ($userCategory === 'student' && $profile->shift && $profile->shift->start_time) {
            $shiftStart = Carbon::parse($profile->shift->start_time);
            $lateCutoff = $shiftStart->copy()->addMinutes(15)->format('H:i:s');
        }

        if (!$existing) {
            // First punch of the day -> Clock-In
            $status = ($timeStr > $lateCutoff) ? 'late' : 'present';

            $attendance = Attendance::create([
                'attendance_date' => $dateStr,
                'entry_time' => $timeStr,
                'attendable_type' => $attendableType,
                'attendable_id' => $profile->id,
                'status' => $status,
                'remarks' => "Fingerprint Punch Entry at " . $punchTime->format('h:i A'),
            ]);

            return [
                'action' => 'check_in',
                'status' => $status,
                'entry_time' => $timeStr,
                'exit_time' => null,
            ];
        } else {
            // Second / Subsequent punch of the day -> Clock-Out
            $existing->update([
                'exit_time' => $timeStr,
                'remarks' => ($existing->remarks ? $existing->remarks . ' | ' : '') . "Fingerprint Punch Exit at " . $punchTime->format('h:i A'),
            ]);

            return [
                'action' => 'check_out',
                'status' => $existing->status,
                'entry_time' => $existing->entry_time,
                'exit_time' => $timeStr,
            ];
        }
    }
}
