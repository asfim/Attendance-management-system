<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GradeRule;

class GradeRuleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'grade' => 'required|string|max:10',
            'min_percent' => 'required|numeric|min:0|max:100',
            'max_percent' => 'required|numeric|min:0|max:100|gte:min_percent',
            'point' => 'required|numeric|min:0|max:10',
        ]);

        $rule = GradeRule::create($request->all());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'rule' => $rule]);
        }

        return redirect()->back()->with('success', 'Grade rule added successfully.');
    }

    public function destroy(Request $request, GradeRule $gradeRule)
    {
        $gradeRule->delete();
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Grade rule removed successfully.');
    }
}
