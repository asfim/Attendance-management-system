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
     * Standard JSON API Endpoint for Biometric / Fingerprint Sync (Office Suite)
     */
    public function sync(Request $request)
    {
        $request->validate([
            'card_uid'    => 'required|string',
            'timestamp'   => 'nullable|date',
            'type'        => 'nullable|string|in:employee,staff,admin,student,auto',
            'device_sn'   => 'nullable|string',
            'punch_state' => 'nullable|string|in:check_in,check_out,auto',
        ]);

        $cardUid  = trim($request->card_uid);
        $time     = $request->filled('timestamp') ? Carbon::parse($request->timestamp) : Carbon::now();
        $dateStr  = $time->format('Y-m-d');
        $timeStr  = $time->format('H:i:s');
        $deviceSn = $request->input('device_sn', 'DEFAULT_DEVICE');

        // 1. Primary Lookup: Office Employee / Staff
        $numericId = (int) preg_replace('/[^0-9]/', '', $cardUid);

        $profile = StaffProfile::where('biometric_id', $cardUid)
            ->orWhere('fingerprint_id', $cardUid)
            ->orWhere('face_id', $cardUid)
            ->orWhere('phone', $cardUid)
            ->orWhere('national_id', $cardUid)
            ->when($numericId > 0, function($q) use ($numericId) {
                $q->orWhere('id', $numericId);
            })
            ->first();

        $attendableType = StaffProfile::class;
        $userCategory   = 'employee';

        // 2. Secondary Lookup fallback (if legacy student exists)
        if (!$profile) {
            $student = StudentProfile::where('biometric_id', $cardUid)
                ->orWhere('admission_no', $cardUid)
                ->first();
            if ($student) {
                $profile = $student;
                $attendableType = StudentProfile::class;
                $userCategory = 'student';
            }
        }

        if (!$profile) {
            BiometricDeviceLog::create([
                'device_sn'    => $deviceSn,
                'biometric_id' => $cardUid,
                'punch_time'   => $time,
                'punch_state'  => $request->input('punch_state', 'check_in'),
                'user_type'    => 'unknown',
                'status'       => 'failed',
                'message'      => 'No employee profile found matching biometric ID / Card UID: ' . $cardUid,
                'raw_payload'  => json_encode($request->all()),
                'ip_address'   => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No employee profile found matching biometric ID: ' . $cardUid,
            ], 404);
        }

        // Process Attendance Record
        $attendanceResult = $this->recordAttendance($profile, $attendableType, $dateStr, $timeStr, $time, $userCategory);

        // Audit log
        $userName = $profile->user->name ?? ($profile->full_name ?? 'Employee #' . $profile->id);
        BiometricDeviceLog::create([
            'device_sn'    => $deviceSn,
            'biometric_id' => $cardUid,
            'punch_time'   => $time,
            'punch_state'  => $attendanceResult['action'],
            'user_type'    => $userCategory,
            'user_name'    => $userName,
            'status'       => 'success',
            'message'      => "Attendance recorded as {$attendanceResult['status']} ({$attendanceResult['action']})",
            'raw_payload'  => json_encode($request->all()),
            'ip_address'   => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Biometric attendance recorded successfully for {$userName}.",
            'record'  => [
                'name'          => $userName,
                'employee_id'   => method_exists($profile, 'employeeId') ? $profile->employeeId() : $cardUid,
                'user_type'     => $userCategory,
                'status'        => $attendanceResult['status'],
                'entry_time'    => $attendanceResult['entry_time'],
                'exit_time'     => $attendanceResult['exit_time'],
                'working_hours' => $attendanceResult['working_hours'] ?? null,
                'timestamp'     => $time->toIso8601String(),
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
                    $dateStr   = $punchTime->format('Y-m-d');
                    $timeStr   = $punchTime->format('H:i:s');

                    // Find employee profile
                    $numId = (int) preg_replace('/[^0-9]/', '', $biometricId);
                    $profile = StaffProfile::where('biometric_id', $biometricId)
                        ->orWhere('fingerprint_id', $biometricId)
                        ->orWhere('face_id', $biometricId)
                        ->orWhere('phone', $biometricId)
                        ->orWhere('national_id', $biometricId)
                        ->when($numId > 0, function($q) use ($numId) {
                            $q->orWhere('id', $numId);
                        })
                        ->first();
                    $userCategory   = 'employee';
                    $attendableType = StaffProfile::class;

                    if (!$profile) {
                        $profile = StudentProfile::where('biometric_id', $biometricId)
                            ->orWhere('admission_no', $biometricId)
                            ->first();
                        if ($profile) {
                            $userCategory   = 'student';
                            $attendableType = StudentProfile::class;
                        }
                    }

                    if ($profile) {
                        $res = $this->recordAttendance($profile, $attendableType, $dateStr, $timeStr, $punchTime, $userCategory);
                        $userName = $profile->user->name ?? 'Employee #' . $profile->id;

                        BiometricDeviceLog::create([
                            'device_sn'    => $sn,
                            'biometric_id' => $biometricId,
                            'punch_time'   => $punchTime,
                            'punch_state'  => $res['action'],
                            'user_type'    => $userCategory,
                            'user_name'    => $userName,
                            'status'       => 'success',
                            'message'      => "ZKTeco ADMS Punch recorded as {$res['status']}",
                            'raw_payload'  => $line,
                            'ip_address'   => $request->ip(),
                        ]);
                        $processedCount++;
                    } else {
                        BiometricDeviceLog::create([
                            'device_sn'    => $sn,
                            'biometric_id' => $biometricId,
                            'punch_time'   => $punchTime,
                            'punch_state'  => 'check_in',
                            'user_type'    => 'unknown',
                            'status'       => 'failed',
                            'message'      => "Unrecognized Biometric ID: {$biometricId}",
                            'raw_payload'  => $line,
                            'ip_address'   => $request->ip(),
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
     * Helper to process attendance record for office staff/employee
     */
    private function recordAttendance($profile, string $attendableType, string $dateStr, string $timeStr, Carbon $punchTime, string $userCategory): array
    {
        $existing = Attendance::whereDate('attendance_date', $dateStr)
            ->where('attendable_type', $attendableType)
            ->where('attendable_id', $profile->id)
            ->first();

        // Get employee shift
        $shift = (method_exists($profile, 'shifts') && $profile->shifts->count() > 0)
            ? $profile->shifts->first()
            : null;

        $shiftStart   = $shift?->start_time ?? '09:00:00';
        $shiftEnd     = $shift?->end_time ?? '18:00:00';
        $graceMinutes = $shift?->grace_time_minutes ?? 15;

        $expectedStart = Carbon::parse("{$dateStr} {$shiftStart}");
        $lateThreshold = $expectedStart->copy()->addMinutes($graceMinutes);

        if (!$existing) {
            // First punch of the day -> Check-In
            $isLate = $punchTime->gt($lateThreshold);
            $lateMinutes = $isLate ? (int) $punchTime->diffInMinutes($expectedStart, true) : 0;
            $status = $isLate ? 'late' : 'present';

            $attendance = Attendance::create([
                'branch_id'           => $profile->branch_id ?? null,
                'department_id'       => $profile->department_id ?? null,
                'shift_id'            => $shift?->id,
                'attendance_date'     => $dateStr,
                'entry_time'          => $timeStr,
                'attendable_type'     => $attendableType,
                'attendable_id'       => $profile->id,
                'status'              => $status,
                'late_minutes'        => $lateMinutes,
                'early_leave_minutes' => 0,
                'overtime_minutes'    => 0,
                'working_hours'       => 0,
                'is_missing_punch'    => true,
                'remarks'             => "Biometric Punch Check-In at " . $punchTime->format('h:i A'),
            ]);

            return [
                'action'        => 'check_in',
                'status'        => $status,
                'entry_time'    => $timeStr,
                'exit_time'     => null,
                'working_hours' => 0,
            ];
        } else {
            // Second / Subsequent punch of the day -> Check-Out
            $expectedEnd = Carbon::parse("{$dateStr} {$shiftEnd}");
            $earlyLeaveMinutes = 0;
            $overtimeMinutes    = 0;

            if ($punchTime->lt($expectedEnd)) {
                $earlyLeaveMinutes = (int) $punchTime->diffInMinutes($expectedEnd, true);
            } elseif ($punchTime->gt($expectedEnd)) {
                $overtimeMinutes = (int) $punchTime->diffInMinutes($expectedEnd, true);
            }

            // Calculate working hours
            $workingHours = 0;
            if ($existing->entry_time) {
                $entryPunch = Carbon::parse("{$dateStr} {$existing->entry_time}");
                if ($punchTime->gt($entryPunch)) {
                    $workingHours = round($entryPunch->diffInMinutes($punchTime) / 60, 2);
                }
            }

            $newStatus = $existing->status;
            if ($existing->status === 'present' && $earlyLeaveMinutes > 30) {
                $newStatus = 'early_leave';
            }

            $existing->update([
                'exit_time'           => $timeStr,
                'status'              => $newStatus,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_minutes'    => $overtimeMinutes,
                'working_hours'       => $workingHours,
                'is_missing_punch'    => false,
                'remarks'             => ($existing->remarks ? $existing->remarks . ' | ' : '') . "Biometric Punch Check-Out at " . $punchTime->format('h:i A'),
            ]);

            return [
                'action'        => 'check_out',
                'status'        => $newStatus,
                'entry_time'    => $existing->entry_time,
                'exit_time'     => $timeStr,
                'working_hours' => $workingHours,
            ];
        }
    }
}
