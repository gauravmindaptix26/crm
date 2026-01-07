<p>Hi {{ $project->employee->name ?? 'Employee' }},</p>

<p><strong>Project Name:</strong> 
    <a href="{{ $project->name_or_url }}">{{ $project->name_or_url }}</a>
</p>

<p><strong>Project Description:</strong></p>
<p>{!! nl2br(e($project->description)) !!}</p>

<p><strong>This project has been assigned to you. Please review it and begin work as per the schedule.</strong></p>

<p>Thanks,<br>Seo Discovery Team</p>
