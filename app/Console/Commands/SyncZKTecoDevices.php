<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BiometricDevice;
use App\Services\AttendanceSyncService;

class SyncZKTecoDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically synchronize attendance records from all active ZKTeco biometric devices';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceSyncService $syncService): int
    {
        $devices = BiometricDevice::where('is_active', true)->get();

        if ($devices->isEmpty()) {
            $this->info('No active ZKTeco devices configured for automatic sync.');
            return 0;
        }

        $this->info("Starting automatic attendance sync for " . $devices->count() . " active device(s)...");

        foreach ($devices as $device) {
            $this->info("Syncing device [ID: {$device->id}] '{$device->name}' ({$device->ip_address}:{$device->port})...");

            try {
                $res = $syncService->syncDevice($device, 'scheduled');
                if ($res['success']) {
                    $s = $res['summary'];
                    $this->info(" -> Success: Total: {$s['total']}, New: {$s['new']}, Duplicate: {$s['duplicate']}, Unmapped: {$s['unmapped']}, Failed: {$s['failed']}");
                } else {
                    $this->warn(" -> Warning for '{$device->name}': " . $res['message']);
                }
            } catch (\Exception $e) {
                $this->error(" -> Error syncing '{$device->name}': " . $e->getMessage());
            }
        }

        $this->info('Automatic ZKTeco device sync completed.');
        return 0;
    }
}
