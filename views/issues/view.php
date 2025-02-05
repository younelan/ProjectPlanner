<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Details</title>
    <!-- Include Bootstrap CSS (from CDN) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container my-4">
        <h2>Details for Issue: <?php echo htmlspecialchars($issue['SUMMARY']); ?></h2>
        
        <!-- Link back to the project issues list page -->
        <a href="index.php?page=projects&action=view&id=<?php echo htmlspecialchars($project['ID']); ?>" class="btn btn-primary mb-4">Back to Project Issues</a>

        <!-- Issue Details Table -->
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($issue['ID']); ?></td>
            </tr>
            <tr>
                <th>Summary</th>
                <td><?php echo htmlspecialchars($issue['SUMMARY']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="badge" style="background-color: #<?php echo htmlspecialchars($issue['STATUS_ICON']); ?>">
                        <?php echo htmlspecialchars($issue['STATUS_NAME']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Priority</th>
                <td><?php echo htmlspecialchars($issue['PRIORITY']); ?></td>
            </tr>
        </table>

        <!-- Linked Issues Section -->
        <?php if (!empty($linkedIssues)): ?>
            <h3>Linked Issues</h3>
            <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Issue ID</th>
            <th>Link Name</th>
            <th>Link Type</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($linkedIssues as $link): ?>
            <tr>
                <td>
                    <a href="index.php?page=issues&action=view&id=<?php echo htmlspecialchars($link['LINK_ID']); ?>">
                        Issue <?php echo htmlspecialchars($link['LINK_ID']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($link['LINK_NAME']); ?></td>
                <td><strong><?php echo htmlspecialchars($link['TYPE']); ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <?php else: ?>
            <p>No linked issues available.</p>
        <?php endif; ?>

        <!-- Issue History Section -->
        <div class="issue-history">
            <h3>Issue History</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Author</th>
                        <th>Field</th>
                        <th>From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $change): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($change['CREATED']))) ?></td>
                            <td><?= htmlspecialchars($change['AUTHOR']) ?></td>
                            <td><?= htmlspecialchars($change['FIELD']) ?></td>
                            <td><?= htmlspecialchars($change['OLDSTRING'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($change['NEWSTRING'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
