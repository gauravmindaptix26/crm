<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Ticket Assigned</title>
</head>
<body>
    <h2>You've been assigned a new ticket</h2>

    <p><strong>Title:</strong> {{ $ticket->title }}</p>
    <p><strong>Description:</strong> {{ $ticket->description }}</p>
    <p><strong>Priority:</strong> {{ $ticket->priority }}</p>

    <p>Please check the system for more details.</p>

    <br>
    <p>Thank you,</p>
    <p><strong>Team SEODiscovery</strong></p>
</body>
</html>
