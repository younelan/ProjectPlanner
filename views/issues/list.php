<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
</head>
<body>
    <h1>Issues</h1>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Summary</th>
                <th>Type</th>
                <th>Status</th>
                <th>Assignee</th>
                <th>Priority</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($issues as $issue): ?>
                <tr>
                    <td><?= htmlspecialchars($issue['ID']) ?></td>
                    <td><?= htmlspecialchars($issue['SUMMARY']) ?></td>
                    <td><?= htmlspecialchars($issue['TYPE']) ?></td>
                    <td><?= htmlspecialchars($issue['STATUS']) ?></td>
                    <td><?= htmlspecialchars($issue['ASSIGNEE']) ?></td>
                    <td><?= htmlspecialchars($issue['PRIORITY']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

