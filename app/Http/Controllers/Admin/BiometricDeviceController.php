<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Http\Requests\BiometricDeviceRequest;
use App\Services\ZKTecoService;
use App\Services\AttendanceSyncService;
use Illuminate\Http\Request;

class BiometricDeviceController extends Controller
{
    protected AttendanceSyncService $syncService;

    public function __construct(AttendanceSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display a listing of biometric devices.
     */
    public function index()
    {
        $devices = BiometricDevice::orderBy('name')->get();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('status', 'online')->count();
        $offlineDevices = $devices->where('status', 'offline')->count();

        return view('admin.biometric_devices.index', compact(
            'devices',
            'totalDevices',
            'onlineDevices',
            'offlineDevices'
        ));
    }

    /**
     * Store a newly created biometric device.
     */
    public function store(BiometricDeviceRequest $request)
    {
        $device = BiometricDevice::create($request->validated());

        return redirect()->route('admin.biometric-devices.index')
            ->with('success', "Biometric Device '{$device->name}' added successfully!");
    }

    /**
     * Display the specified biometric device dashboard details.
     */
    public function show(BiometricDevice $device)
    {
        $device->load(['logs' => function ($q) {
            $q->orderBy('punch_time', 'desc')->take(50);
        }, 'syncLogs' => function ($q) {
            $q->orderBy('id', 'desc')->take(20);
        }]);

        return view('admin.biometric_devices.show', compact('device'));
    }

    /**
     * Update the specified biometric device.
     */
    public function update(BiometricDeviceRequest $request, BiometricDevice $device)
    {
        $device->update($request->validated());

        return redirect()->route('admin.biometric-devices.index')
            ->with('success', "Biometric Device '{$device->name}' updated successfully!");
    }

    /**
     * Remove the specified biometric device.
     */
    public function destroy(BiometricDevice $device)
    {
        $name = $device->name;
        $device->delete();

        return redirect()->route('admin.biometric-devices.index')
            ->with('success', "Biometric Device '{$name}' deleted.");
    }

    /**
     * Test connection to a ZKTeco device via UDP Socket 4370.
     */
    public function testConnection(BiometricDevice $device)
    {
        $zk = new ZKTecoService($device->ip_address, $device->port, $device->comm_key ?? '0');
        $res = $zk->testConnection();

        if ($res['success']) {
            $device->update([
                'status' => 'online',
                'last_connected_at' => now(),
                'last_error' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'status' => 'online',
            ]);
        } else {
            $device->update([
                'status' => 'offline',
                'last_error' => $res['message'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $res['message'],
                'status' => 'offline',
            ], 422);
        }
    }

    /**
     * Sync attendance logs from a specific biometric device.
     */
    public function sync(BiometricDevice $device)
    {
        $res = $this->syncService->syncDevice($device, 'manual');

        if ($res['success']) {
            $s = $res['summary'];
            return response()->json([
                'success' => true,
                'message' => "{$res['message']} (Total: {$s['total']}, New: {$s['new']}, Duplicate: {$s['duplicate']}, Unmapped: {$s['unmapped']}, Failed: {$s['failed']})",
                'summary' => $s,
                'last_sync_at' => $device->fresh()->last_sync_at ? $device->fresh()->last_sync_at->format('d M Y, h:i A') : 'Now',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $res['message'],
                'summary' => $res['summary'],
            ], 422);
        }
    }

    /**
     * Clear raw logs from device memory (With confirmation).
     */
    public function clearDeviceLogs(BiometricDevice $device)
    {
        try {
            $zk = new ZKTecoService($device->ip_address, $device->port, $device->comm_key ?? '0');
            if ($zk->clearAttendanceLogs()) {
                return response()->json([
                    'success' => true,
                    'message' => "Cleared attendance logs on device '{$device->name}' successfully!",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to clear logs on device '{$device->name}'.",
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync attendance logs from all active biometric devices.
     */
    public function syncAll()
    {
        $devices = BiometricDevice::where('is_active', true)->get();
        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active biometric devices registered to sync.',
            ], 422);
        }

        $syncedCount = 0;
        $totalNewPunches = 0;

        foreach ($devices as $dev) {
            $res = $this->syncService->syncDevice($dev, 'manual');
            if ($res['success']) {
                $syncedCount++;
                $totalNewPunches += ($res['summary']['new'] ?? 0);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Synchronized {$syncedCount} device(s) successfully! Total new punch records: {$totalNewPunches}.",
        ]);
    }
}
