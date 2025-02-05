<?php 
$pageTitle = htmlspecialchars($project['PNAME']) . ' | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($project['PNAME']) ?></li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-tasks"></i> 
                    <?= htmlspecialchars($project['PNAME']) ?> 
                    <small class="text-muted">(<?= htmlspecialchars($project['PKEY']) ?>)</small>
                </h2>
                <div class="btn-group">
                    <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-columns"></i> Board View
                    </a>
                    <?php if ($project['DESCRIPTION']): ?>
                        <button type="button" class="btn btn-outline-info" data-toggle="tooltip" title="<?= htmlspecialchars($project['DESCRIPTION']) ?>">
                            <i class="fas fa-info-circle"></i> Info
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-key"></i> Key</th>
                                <th>Summary</th>
                                <th><i class="fas fa-tag"></i> Type</th>
                                <th><i class="fas fa-tasks"></i> Status</th>
                                <th><i class="fas fa-user"></i> Assignee</th>
                                <th><i class="fas fa-flag"></i> Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($issues)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> No issues found for this project.
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($issues as $issue): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($project['PKEY']) ?>-<?= htmlspecialchars($issue['ID']) ?>
                                            </span>
                                        </td>
                                        <td class="w-25">
                                            <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" class="text-dark">
                                                <?= htmlspecialchars($issue['SUMMARY']) ?>
                                            </a>
                                        </td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($issue['TYPE']) ?></span></td>
                                        <td><span class="badge badge-<?= getStatusBadgeClass($issue['STATUS']) ?>"><?= htmlspecialchars($issue['STATUS']) ?></span></td>
                                        <td>
                                            <?php if ($issue['ASSIGNEE']): ?>
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($issue['ASSIGNEE']) ?>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= getPriorityIcon($issue['PRIORITY']) ?>
                                            <?= htmlspecialchars($issue['PRIORITY']) ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Helper functions
function getStatusBadgeClass($status) {
    $map = [
        'Open' => 'secondary',
        'In Progress' => 'primary',
        'Resolved' => 'info',
        'Closed' => 'success',
        'Reopened' => 'warning'
    ];
    return $map[$status] ?? 'secondary';
}

function getPriorityIcon($priority) {
    $icons = [
        'Highest' => '<i class="fas fa-arrow-up text-danger"></i>',
        'High' => '<i class="fas fa-arrow-up text-warning"></i>',
        'Medium' => '<i class="fas fa-minus text-info"></i>',
        'Low' => '<i class="fas fa-arrow-down text-success"></i>',
        'Lowest' => '<i class="fas fa-arrow-down text-muted"></i>'
    ];
    return $icons[$priority] ?? '';
}

include 'views/templates/footer.php'; 
?>
