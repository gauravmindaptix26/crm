<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $emailTemplates = EmailTemplate::latest()->paginate(10);
        return view('email-templates.index', compact('emailTemplates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'from_email' => 'required|email',
            'body' => 'required',
        ]);

        EmailTemplate::create($request->all());

        //return response()->json(['success' => 'Department added successfully!', 'department' => $department]);
        return response()->json(['success' => 'Email Template created successfully.']);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'from_email' => 'required|email',
            'body' => 'required',
        ]);

        $emailTemplate->update($request->all());

        return response()->json(['success' => 'Email Template Update successfully.']);    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return response()->json(['success' => 'Email Template Deleted successfully.']);
        
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return response()->json($emailTemplate);
    }
}

