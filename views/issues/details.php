<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Details</title>
</head>
<body>
    <h1><?= htmlspecialchars($issue['SUMMARY']) ?></h1>
    <p><strong>Description:</strong> <?= htmlspecialchars($issue['DESCRIPTION']) ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($issue['TYPE']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($issue['STATUS']) ?></p>
    <p><strong>Assignee:</strong> <?= htmlspecialchars($issue['ASSIGNEE']) ?></p>
    <p><strong>Created:</strong> <?= htmlspecialchars($issue['CREATED']) ?></p>
    <p><strong>Updated:</strong> <?= htmlspecialchars($issue['UPDATED']) ?></p>
    <p><strong>Due Date:</strong> <?= htmlspecialchars($issue['DUEDATE']) ?></p>
</body>
</html>

