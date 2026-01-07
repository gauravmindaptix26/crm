<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Http\Request;

class ProjectAttachmentController extends Controller
{
    public function create(Project $project)
    {
        return view('projects.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xlsx,xls,zip',
        ]);

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('project_attachments', 'public');

            $project->attachments()->create([
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getClientMimeType(),
            ]);
        }

        return redirect()->route('projects.index')->with('success', 'Attachments uploaded successfully.');
    }
}


