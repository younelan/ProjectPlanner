<?php
$pageTitle = isset($project) ? "Sprints - " . htmlspecialchars($project['PNAME']) : "All Sprints";
include 'views/templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <?php if (isset($project)): ?>
                <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" class="text-dark">
                    <?= htmlspecialchars($project['PNAME']) ?>
                </a> - 
            <?php endif; ?>
            Sprints
        </h2>
        <?php if (isset($project)): ?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSprintModal">
                <i class="fas fa-plus"></i> Create Sprint
            </button>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Goal</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Issues</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sprints as $sprint): ?>
                        <tr>
                            <td><?= htmlspecialchars($sprint['NAME']) ?></td>
                            <td><?= htmlspecialchars($sprint['GOAL']) ?></td>
                            <td><?= date('Y-m-d', $sprint['START_DATE']/1000) ?></td>
                            <td><?= date('Y-m-d', $sprint['END_DATE']/1000) ?></td>
                            <td>
                                <?php if ($sprint['CLOSED']): ?>
                                    <span class="badge badge-secondary">Closed</span>
                                <?php elseif ($sprint['STARTED']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Future</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $sprint['issue_count'] ?></td>
                            <td>
                                <a href="index.php?page=sprints&action=board&id=<?= $sprint['ID'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-columns"></i> Board
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Updated Create Sprint Modal -->
<div class="modal fade" id="createSprintModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createSprintForm">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Sprint</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="projectId" value="<?= $project['ID'] ?? '' ?>">
                    <div class="form-group">
                        <label>Sprint Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Goal</label>
                        <textarea class="form-control" name="goal"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="startDate" required 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="endDate" required 
                               value="<?= date('Y-m-d', strtotime('+2 weeks')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Sprint</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('createSprintForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    fetch('index.php?page=sprints&action=create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error creating sprint: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error creating sprint: ' + error);
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>
