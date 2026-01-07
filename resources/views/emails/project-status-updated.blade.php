<h2>Project Status Updated</h2>

<p><strong>Project:</strong> {{ $project->name_or_url }}</p>
<p><strong>Updated Status:</strong> {{ $project->project_status }}</p>
<p><strong>Description:</strong> {{ $project->reason_description ?? 'N/A' }}</p>
<p><strong>Updated By:</strong> {{ optional($project->closedByUser)->name ?? 'System' }}</p>

<p>Please log in to your dashboard to review.</p>
