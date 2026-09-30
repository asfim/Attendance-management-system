<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->except(['_token']);

        // Handle image uploads
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('settings', 'public');
            $data['site_logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('settings', 'public');
            $data['site_favicon'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            Setting::set($key, $val);
        }

        return back()->with('success', 'Global system settings saved successfully!');
    }

    public function paymentConfig()
    {
        $settings = Setting::where('key', 'like', 'sslcommerz_%')->pluck('value', 'key')->toArray();
        return view('admin.settings.payment', compact('settings'));
    }

    public function updatePaymentConfig(Request $request)
    {
        $data = $request->only([
            'sslcommerz_mode',
            'sslcommerz_store_id',
            'sslcommerz_store_password',
            'sslcommerz_currency'
        ]);

        foreach ($data as $key => $val) {
            Setting::set($key, $val);
        }

        return back()->with('success', 'Payment configuration updated successfully!');
    }
}
