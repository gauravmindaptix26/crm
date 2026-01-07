<?php

namespace App\Http\Controllers;

use App\Models\SaleTeamProject;
use App\Models\SalesProjectAttachment;
use Illuminate\Http\Request;

class SalesProjectAttachmentController extends Controller
{
    public function create(SaleTeamProject $sales_project)
    {
        return view('sales-projects.create', compact('sales_project'));
    }
    

    public function store(Request $request, SaleTeamProject $sales_project)
    {
        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);
    
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('sales_project_attachments', 'public');
    
            $sales_project->attachments()->create([
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getClientMimeType(),
            ]);
        }
    
        return redirect()->back()
        ->with('success', 'Attachments uploaded successfully.');
    


    }
    
}