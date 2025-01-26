<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Details</title>
</head>
<body>
    <h1><?= htmlspecialchars($project['NAME']) ?> (<?= htmlspecialchars($project['KEY']) ?>)</h1>
    <p><?= htmlspecialchars($project['DESCRIPTION']) ?></p>
    <h2>Issues</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Summary</th>
                <th>Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($issues as $issue): ?>
                <tr>
                    <td><?= $issue['ID'] ?></td>
                    <td>
                        <a href="/index.php?page=issues&action=view&id=<?= $issue['ID'] ?>">
                            <?= htmlspecialchars($issue['SUMMARY']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($issue['TYPE']) ?></td>
                    <td><?= htmlspecialchars($issue['STATUS']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

