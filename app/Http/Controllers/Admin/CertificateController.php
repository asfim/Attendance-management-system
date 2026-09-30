<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\IssuedCertificate;
use App\Models\User;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $templates = Certificate::all();
        $issued = IssuedCertificate::with(['certificate', 'user'])->get();
        $users = User::whereHas('role', function($q) {
            $q->whereIn('name', ['student', 'teacher', 'staff']);
        })->get();

        return view('admin.certificates.index', compact('templates', 'issued', 'users'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|unique:certificates,template_name',
            'content' => 'required|string',
        ]);

        Certificate::create($request->all());

        return back()->with('success', 'Certificate template created successfully!');
    }

    public function issueCertificate(Request $request)
    {
        $request->validate([
            'certificate_id' => 'required|exists:certificates,id',
            'user_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
        ]);

        $certNo = 'CERT-' . strtoupper(uniqid());

        IssuedCertificate::create([
            'certificate_id' => $request->certificate_id,
            'user_id' => $request->user_id,
            'issue_date' => $request->issue_date,
            'certificate_no' => $certNo,
        ]);

        return back()->with('success', 'Certificate issued successfully!');
    }
}
