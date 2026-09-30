<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZKTecoService;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\BiometricDeviceLog;
use Illuminate\Support\Carbon;

class SyncZktecoDeviceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:sync-zk {ip=192.168.1.201} {port=4370}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync biometric attendance records directly from ZKTeco device IP via LAN (Port 4370)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->argument('ip');
        $port = (int) $this->argument('port');

        $this->info("Connecting to ZKTeco device at {$ip}:{$port}...");

        try {
            $zk = new ZKTecoService($ip, $port);
            if (!$zk->connect()) {
                $this->error("Could not connect to ZKTeco device at {$ip}:{$port}. Please check IP/LAN connection.");
                return 1;
            }

            $this->info("Connected successfully! Fetching attendance logs...");
            $logs = $zk->getAttendanceLogs();

            $this->info("Retrieved " . count($logs) . " raw attendance logs.");
            $processed = 0;

            foreach ($logs as $log) {
                $biometricId = $log['biometric_id'];
                $punchTimeStr = $log['timestamp'];

                try {
                    $punchTime = Carbon::parse($punchTimeStr);
                    $dateStr = $punchTime->format('Y-m-d');
                    $timeStr = $punchTime->format('H:i:s');

                    // Check if already logged today
                    $existingLog = BiometricDeviceLog::where('biometric_id', $biometricId)
                        ->where('punch_time', $punchTime->toDateTimeString())
                        ->first();

                    if ($existingLog) {
                        continue;
                    }

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
                        $existingAttendance = Attendance::whereDate('attendance_date', $dateStr)
                            ->where('attendable_type', $attendableType)
                            ->where('attendable_id', $profile->id)
                            ->first();

                        $lateCutoff = '09:15:00';
                        $action = 'check_in';
                        $status = 'present';

                        if (!$existingAttendance) {
                            $status = ($timeStr > $lateCutoff) ? 'late' : 'present';
                            Attendance::create([
                                'attendance_date' => $dateStr,
                                'entry_time' => $timeStr,
                                'attendable_type' => $attendableType,
                                'attendable_id' => $profile->id,
                                'status' => $status,
                                'remarks' => "Direct ZKTeco LAN Sync at " . $punchTime->format('h:i A'),
                            ]);
                        } else {
                            $action = 'check_out';
                            $existingAttendance->update([
                                'exit_time' => $timeStr,
                                'remarks' => ($existingAttendance->remarks ? $existingAttendance->remarks . ' | ' : '') . "Direct ZKTeco LAN Exit at " . $punchTime->format('h:i A'),
                            ]);
                        }

                        $userName = $profile->user->name ?? 'User #' . $profile->id;
                        BiometricDeviceLog::create([
                            'device_sn' => 'ZK_' . $ip,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => $action,
                            'user_type' => $userCategory,
                            'user_name' => $userName,
                            'status' => 'success',
                            'message' => "LAN direct punch recorded as {$status} ({$action})",
                            'raw_payload' => json_encode($log),
                            'ip_address' => $ip,
                        ]);

                        $processed++;
                    } else {
                        BiometricDeviceLog::create([
                            'device_sn' => 'ZK_' . $ip,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => 'check_in',
                            'user_type' => 'unknown',
                            'status' => 'failed',
                            'message' => "Unrecognized Biometric ID: {$biometricId}",
                            'raw_payload' => json_encode($log),
                            'ip_address' => $ip,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing record: " . $e->getMessage());
                }
            }

            $this->info("Direct sync completed! Processed {$processed} new attendance records.");
            return 0;

        } catch (\Exception $ex) {
            $this->error("ZKTeco LAN Sync Error: " . $ex->getMessage());
            return 1;
        }
    }
}
