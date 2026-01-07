<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Task Assigned</title>
</head>
<body>
    <h2>You have been assigned a new task</h2>

    <p><strong>Task Name:</strong> {{ $task->name }}</p>
    <p><strong>Description:</strong> {{ $task->description }}</p>

    <p>Please check the system for more details.</p>

    <br>
    <p>Thank you,<br>Team SEODiscovery</p>
</body>
</html>
