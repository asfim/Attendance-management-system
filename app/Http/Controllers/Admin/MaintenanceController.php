<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class MaintenanceController extends Controller
{
    public function clearCache()
    {
        Artisan::call('cache:clear');

        return back()->with('success', 'Cache cleared successfully!');
    }
}
