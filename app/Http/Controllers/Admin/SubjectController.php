<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('classes')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.subjects.index', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subjects,code|max:50',
            'type' => 'required|in:theory,practical',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $subject = Subject::create($request->only(['name', 'code', 'type']));
        $subject->classes()->attach($request->class_ids);

        return back()->with('success', 'Subject created and assigned to classes successfully!');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->classes()->detach();
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $id,
            'type' => 'required|in:theory,practical',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $subject = Subject::findOrFail($id);
        $subject->update($request->only(['name', 'code', 'type']));
        $subject->classes()->sync($request->class_ids);

        return back()->with('success', 'Subject updated successfully!');
    }
}
