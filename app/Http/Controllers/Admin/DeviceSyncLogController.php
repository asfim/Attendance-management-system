<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceSyncLog;
use App\Models\BiometricDevice;
use Illuminate\Http\Request;

class DeviceSyncLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DeviceSyncLog::with('device')->orderBy('id', 'desc');

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $syncLogs = $query->paginate(20)->appends($request->all());
        $devices = BiometricDevice::orderBy('name')->get();

        return view('admin.device_sync_logs.index', compact('syncLogs', 'devices'));
    }
}
