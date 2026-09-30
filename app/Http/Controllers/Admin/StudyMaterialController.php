<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudyMaterialController extends Controller
{
    public function index()
    {
        $materials = \App\Models\StudyMaterial::with(['schoolClass', 'section', 'subject', 'staff.user'])
            ->latest()
            ->paginate(15);
            
        return view('admin.study_materials.index', compact('materials'));
    }

    public function destroy(\App\Models\StudyMaterial $studyMaterial)
    {
        if ($studyMaterial->file_path && !filter_var($studyMaterial->file_path, FILTER_VALIDATE_URL)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($studyMaterial->file_path);
        }
        $studyMaterial->delete();
        return redirect()->route('admin.study-materials.index')->with('success', 'Study material deleted.');
    }
}
