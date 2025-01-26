<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects</title>
</head>
<body>
    <h1>Projects</h1>
    <ul>
        <?php foreach ($projects as $project): ?>
            <li>
                <a href="/index.php?page=projects&action=view&id=<?= $project['ID'] ?>">
                    <?= htmlspecialchars($project['NAME']) ?>
                </a>
                (<?= htmlspecialchars($project['KEY']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

