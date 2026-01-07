<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Reply</title>
</head>
<body>
    <h2>Support Ticket Updated</h2>

    <p><strong>Title:</strong> {{ $ticket->title }}</p>
    <p><strong>Reply by:</strong> {{ $reply->user->name }}</p>

    <p><strong>Reply:</strong></p>
    <p>{{ $reply->message }}</p>

    <br>
    <p>Thank you,</p>
    <p><strong>Team SEODiscovery</strong></p>
</body>
</html>
