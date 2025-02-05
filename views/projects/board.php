<?php 
$pageTitle = htmlspecialchars($project['PNAME']) . ' Board | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>"><?= htmlspecialchars($project['PNAME']) ?></a></li>
                <li class="breadcrumb-item active">Board</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-columns"></i> <?= htmlspecialchars($project['PNAME']) ?> Board</h2>
            <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> List View
            </a>
        </div>

        <div class="row flex-nowrap overflow-auto pb-3">
            <?php foreach ($boardColumns as $status => $statusIssues): ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><?= htmlspecialchars($status) ?> 
                            <span class="badge badge-pill badge-secondary"><?= count($statusIssues) ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        <div class="issue-list" data-status="<?= htmlspecialchars($status) ?>">
                            <?php foreach ($statusIssues as $issue): ?>
                            <div class="card mb-2 issue-card" data-issue-id="<?= $issue['ID'] ?>">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-info"><?= htmlspecialchars($issue['TYPE']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($issue['PROJECT_KEY']) ?>-<?= htmlspecialchars($issue['ID']) ?></small>
                                    </div>
                                    <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" 
                                       class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($issue['SUMMARY']) ?>
                                    </a>
                                    <?php if ($issue['ASSIGNEE']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($issue['ASSIGNEE']) ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lists = document.querySelectorAll('.issue-list');
    
    lists.forEach(list => {
        new Sortable(list, {
            group: 'board',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function(evt) {
                const issueId = evt.item.dataset.issueId;
                const newStatus = evt.to.dataset.status;
                
                // Show loading indicator
                evt.item.style.opacity = '0.5';
                
                // Send update to server
                fetch('api/update_issue_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        issueId: issueId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update counter badges
                        updateCounters();
                    } else {
                        // Revert the move if there was an error
                        evt.from.appendChild(evt.item);
                        alert('Error updating issue status: ' + data.message);
                    }
                })
                .catch(error => {
                    // Revert the move if there was an error
                    evt.from.appendChild(evt.item);
                    alert('Error updating issue status');
                })
                .finally(() => {
                    evt.item.style.opacity = '1';
                });
            }
        });
    });

    function updateCounters() {
        document.querySelectorAll('.card-header').forEach(header => {
            const list = header.nextElementSibling.querySelector('.issue-list');
            const counter = header.querySelector('.badge');
            counter.textContent = list.children.length;
        });
    }
});
</script>

<style>
.overflow-auto::-webkit-scrollbar {
    height: 8px;
}
.overflow-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.overflow-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<?php include 'views/templates/footer.php'; ?>
