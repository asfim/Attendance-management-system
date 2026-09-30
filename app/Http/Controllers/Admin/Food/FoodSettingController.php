<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Services\FoodService;
use Illuminate\Http\Request;

class FoodSettingController extends Controller
{
    public function __construct(protected FoodService $foodService) {}

    public function index()
    {
        $settings = $this->foodService->getSettings();
        return view('admin.food.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'min_non_consumption_days' => 'required|integer|min:1|max:31',
            'billing_days'              => 'required|integer|min:1|max:31',
            'calculation_method'        => 'required|in:food_day,individual_meal',
        ]);

        $this->foodService->updateSettings([
            'food_fee_adjustment_enabled' => $request->has('food_fee_adjustment_enabled'),
            'min_non_consumption_days'   => $request->input('min_non_consumption_days', 10),
            'billing_days'                => $request->input('billing_days', 30),
            'calculation_method'          => $request->input('calculation_method', 'food_day'),
            'allow_manual_adjustment'     => $request->has('allow_manual_adjustment'),
            'auto_generate_monthly_fee'   => $request->has('auto_generate_monthly_fee'),
        ]);

        return redirect()->route('admin.food.settings.index')->with('success', 'Food Settings updated successfully!');
    }

    public function downloadManual()
    {
        $pdf = \Pdf::loadView('admin.food.settings.manual');
        return $pdf->download('Food_Setting_User_Manual.pdf');
    }
}
