<?php 
$pageTitle = 'Create Project | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-plus"></i> Create Project</h2>
            </div>
            <div class="card-body">
                <form action="index.php?page=projects&action=store" method="POST">
                    <div class="form-group">
                        <label for="pname">Project Name *</label>
                        <input type="text" class="form-control" id="pname" name="PNAME" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pkey">Project Key *</label>
                        <input type="text" class="form-control" id="pkey" name="PKEY" required 
                               pattern="[A-Z0-9]+" title="Only uppercase letters and numbers allowed"
                               maxlength="10">
                        <small class="form-text text-muted">Upper case letters and numbers only (e.g., PROJ1)</small>
                    </div>

                    <div class="form-group">
                        <label for="lead">Project Lead *</label>
                        <input type="text" class="form-control" id="lead" name="LEAD" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="DESCRIPTION" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="url" class="form-control" id="url" name="URL">
                    </div>

                    <div class="form-group">
                        <label for="clone_project">Clone Workflow From *</label>
                        <select class="form-control" id="clone_project" name="clone_project_id" required>
                            <option value="">Select a project...</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['ID'] ?>">
                                    <?= htmlspecialchars($project['PKEY'] . ' - ' . $project['PNAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">The new project will inherit the selected project's workflow</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Create Project</button>
                        <a href="index.php?page=projects" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
