<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder Snoozed — Wilson</title>
</head>
<body>
    <h1>Reminder snoozed</h1>

    @if ($snoozedUntil)
        <p>Your reminder has been rescheduled for <strong>{{ \Carbon\Carbon::parse($snoozedUntil)->format('M j, Y') }}</strong>.</p>
    @endif

    <p><a href="{{ route('login') }}">Log in to Wilson</a> to manage your maintenance schedule.</p>
</body>
</html>
