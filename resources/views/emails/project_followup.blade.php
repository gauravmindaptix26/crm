<!DOCTYPE html>
<html>
<head>
    <title>{{ $subjectText }}</title>
</head>
<body>
    <h1>{{ $subjectText }}</h1>
    <p>Dear {{ $project->client_name ?? 'Client' }},</p>
    <p>We are following up on the paused project: {{ $project->name_or_url }}.</p>
    <p>{{ $bodyMessage }}</p>
    <p>Please let us know if you have any updates or wish to discuss resuming the project.</p>
    <p>Best regards,<br>{{ auth()->user()->name }}<br>Project Manager</p>
</body>
</html>
