<?php 
$pageTitle = "Issue: " . htmlspecialchars($issue['SUMMARY']);
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($issue['PROJECT']) ?>">Project</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($issue['SUMMARY']) ?></li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="mb-0"><?= htmlspecialchars($issue['SUMMARY']) ?></h2>
            </div>
            <div class="card-body">
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
            </div>
        </div>

        <?php if (!empty($linkedIssues)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">Linked Issues</h3>
            </div>
            <div class="card-body">
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
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">History</h3>
            </div>
            <div class="card-body">
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
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
