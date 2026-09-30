<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function index()
    {
        $homeworks = \App\Models\Homework::with(['schoolClass', 'section', 'subject', 'staff.user'])
            ->latest()
            ->paginate(15);
            
        return view('admin.homework.index', compact('homeworks'));
    }

    public function destroy(\App\Models\Homework $homework)
    {
        if ($homework->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($homework->file_path);
        }
        $homework->delete();
        return redirect()->route('admin.homework.index')->with('success', 'Homework deleted successfully.');
    }
}
