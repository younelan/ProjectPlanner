<?php 
$pageTitle = 'Edit Project | Project Agile';
include 'views/templates/header.php'; 
?>
<h2>Edit Project</h2>
<form action="index.php?page=projects&action=update&id=<?= htmlspecialchars($project['ID']) ?>" method="post">
    <div class="form-group">
        <label for="PKEY">Project Key</label>
        <input type="text" name="PKEY" id="PKEY" class="form-control" value="<?= htmlspecialchars($project['PKEY'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="PNAME">Project Name</label>
        <input type="text" name="PNAME" id="PNAME" class="form-control" value="<?= htmlspecialchars($project['PNAME'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="URL">Project URL</label>
        <input type="text" name="URL" id="URL" class="form-control" value="<?= htmlspecialchars($project['URL'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="LEAD">Project Lead</label>
        <select name="LEAD" id="LEAD" class="form-control">
            <?php foreach ($users as $user): ?>
                <option value="<?= htmlspecialchars($user['USER_KEY'] ?? '') ?>" <?= $project['LEAD'] == $user['USER_KEY'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['USER_KEY'] ?? '') ?> (<?= htmlspecialchars($user['DISPLAY_NAME'] ?? $user['LOWER_USER_NAME'] ?? '') ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="PROJECTTYPE">Project Type</label>
        <input type="text" name="PROJECTTYPE" id="PROJECTTYPE" class="form-control" value="<?= htmlspecialchars($project['PROJECTTYPE'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="ORIGINALKEY">Original Key</label>
        <input type="text" name="ORIGINALKEY" id="ORIGINALKEY" class="form-control" value="<?= htmlspecialchars($project['ORIGINALKEY'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="DESCRIPTION">Description</label>
        <textarea name="DESCRIPTION" id="DESCRIPTION" class="form-control"><?= htmlspecialchars($project['DESCRIPTION'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Project</button>
</form>

<!-- [Done #2] New Process Overview Section -->
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Process Overview</h3>
        <a href="index.php?page=workflows&action=view&id=<?= htmlspecialchars($project['ID']) ?>" 
           class="btn btn-outline-primary">
            <i class="fas fa-cogs"></i> Edit Workflow
        </a>
    </div>
    <div class="row">
        <!-- Tasks by Status -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Tasks by Status</strong>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($workflowPhases as $phase): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($phase['PNAME'] ?? 'Unnamed') ?>
                            <span class="badge badge-secondary"><?= intval($phase['taskCount']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <!-- Tasks by Type -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Tasks by Type</strong>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($workflowStats['tasksByTypeAggregate'] as $type => $count): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($type) ?>
                            <span class="badge badge-primary"><?= intval($count) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- Overall Statistics -->
    <div class="alert alert-info mt-3">
        <strong>Total Tasks:</strong> <?= intval($workflowStats['totalTasks']) ?> &nbsp;
        <strong>Total Phases:</strong> <?= intval($workflowStats['totalPhases']) ?> &nbsp;
        <strong>Average Tasks per Phase:</strong> <?= htmlspecialchars($workflowStats['averageTasks']) ?>
    </div>
</div>

<?php 
include 'views/templates/footer.php'; 
?>