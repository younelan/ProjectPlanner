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
                        <?php foreach ($statusIssues as $issue): ?>
                        <div class="card mb-2">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-info"><?= htmlspecialchars($issue['TYPE']) ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($project['PKEY']) ?>-<?= htmlspecialchars($issue['ID']) ?></small>
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
            <?php endforeach; ?>
        </div>
    </div>
</div>

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
