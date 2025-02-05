<?php 
$pageTitle = $project['PNAME'] . ' Issues | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($project['PNAME']) ?> Issues</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-tasks"></i> 
                    <?= htmlspecialchars($project['PNAME']) ?> Issues
                </h2>
                <div>
                    <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-columns"></i> Switch to Board
                    </a>
                    <a href="index.php?page=issues&action=create&projectId=<?= htmlspecialchars($project['ID']) ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Issue
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="10%">ID</th>
                            <th width="30%">Summary</th>
                            <th width="10%">Type</th>
                            <th width="10%">Status</th>
                            <th width="15%">Assignee</th>
                            <th width="10%">Priority</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($issues as $issue): ?>
                        <tr>
                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($project['PKEY']) ?>-<?= htmlspecialchars($issue['ID']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($issue['SUMMARY']) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= htmlspecialchars($issue['TYPE']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-<?= getStatusBadgeClass($issue['STATUS']) ?>">
                                    <?= htmlspecialchars($issue['STATUS']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($issue['ASSIGNEE']): ?>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($issue['ASSIGNEE']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
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
                                <a href="index.php?page=issues&action=edit&id=<?= $issue['ID'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-issue" 
                                        data-issue-id="<?= $issue['ID'] ?>"
                                        data-issue-key="<?= htmlspecialchars($project['PKEY'] . '-' . $issue['ID']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-issue').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const issueId = this.dataset.issueId;
        const issueKey = this.dataset.issueKey;
        if (confirm(`Are you sure you want to delete issue ${issueKey}?`)) {
            fetch(`index.php?page=issues&action=delete&id=${issueId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                } else {
                    alert(data.error || 'Failed to delete issue');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete issue');
            });
        }
    });
});
</script>

<?php 
// Helper functions for the view
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

