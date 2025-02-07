<?php
$pageTitle = isset($project) ? "Sprints - " . htmlspecialchars($project['PNAME']) : "All Sprints";
include 'views/templates/header.php';
?>

<style>
    .page-header {
        background: linear-gradient(135deg, rgb(224 228 202) 0%, rgb(236, 215, 190) 100%);
        padding: 1rem 1.5rem;
        margin: 0;
        margin-bottom: 3px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .breadcrumb {
        margin: 0;
        padding: 0;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .breadcrumb-item {
        font-size: 1.25rem;
        font-weight: 600;
        color: darkred;
    }

    .breadcrumb-item a {
        color: darkred;
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: darkred;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: darkred;
        content: "›";
        font-size: 1.4rem;
        line-height: 1;
        padding: 0 0.5rem;
    }
</style>

<div class="page-header">
    <div class="d-flex align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="fas fa-project-diagram"></i>
                    <a href="index.php">Projects</a>
                </li>
                <?php if (isset($project)): ?>
                <li class="breadcrumb-item">
                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>">
                        <?= htmlspecialchars($project['PNAME']) ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active">Sprints</li>
            </ol>
        </nav>
    </div>
    <?php if (isset($project)): ?>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSprintModal">
            <i class="fas fa-plus"></i> Create Sprint
        </button>
    <?php endif; ?>
</div>

<div class="container-fluid">
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
