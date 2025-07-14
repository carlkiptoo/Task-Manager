<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Task Assigned</title>
</head>
<body>
    <h2>Hello there!</h2>
    <p>You have been assigned a new task:</p>

    <ul>
        <li><strong>Title:</strong> {{ $task->title }}</li>
        <li><strong>Description:</strong> {{ $task->description }}</li>
        <li><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($task->deadline)->toFormattedDateString() }}</li>
    </ul>

    <p>Please log in to your dashboard to view more details.</p>

    <p>Regards,<br>Task Management System</p>
</body>
</html>
