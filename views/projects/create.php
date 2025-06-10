<?php 
$pageTitle = 'Create Project | Project Agile';
include 'views/templates/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-plus"></i> Create Project</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=projects&action=store" method="POST">
                    <div class="form-group">
                        <label for="pname">Project Name *</label>
                        <input type="text" class="form-control" id="pname" name="PNAME" 
                               value="<?= htmlspecialchars($_POST['PNAME'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pkey">Project Key *</label>
                        <input type="text" class="form-control" id="pkey" name="PKEY" 
                               value="<?= htmlspecialchars($_POST['PKEY'] ?? '') ?>"
                               required pattern="[A-Z0-9]+" 
                               title="Only uppercase letters and numbers allowed"
                               maxlength="10">
                        <small class="form-text text-muted">Upper case letters and numbers only (e.g., PROJ1)</small>
                    </div>

                    <div class="form-group">
                        <label for="lead">Project Lead *</label>
                        <select class="form-control" id="lead" name="LEAD" required>
                            <option value="">Select a lead...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['USER_KEY']) ?>" 
                                    <?= (($_POST['LEAD'] ?? '') === $user['USER_KEY']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['USER_KEY']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="DESCRIPTION" 
                                  rows="3"><?= htmlspecialchars($_POST['DESCRIPTION'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="url" class="form-control" id="url" name="URL" 
                               value="<?= htmlspecialchars($_POST['URL'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="clone_project">Clone Workflow From</label>
                        <select class="form-control" id="clone_project" name="clone_project_id">
                            <option value="">Select a project...</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['ID'] ?>"
                                    <?= ($_POST['clone_project_id'] ?? '') == $project['ID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['PKEY'] . ' - ' . $project['PNAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">The new project will inherit the selected project's workflow</small>
                    </div>

                    <?php if (empty($projects)): ?>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="use_default_workflow" name="use_default_workflow" value="1"
                                   <?= isset($_POST['use_default_workflow']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="use_default_workflow">
                                Use Default Workflow
                            </label>
                        </div>
                        <small class="form-text text-muted">Since no projects exist yet, check this to create a default workflow</small>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="use_default_workflow" name="use_default_workflow" value="1"
                                   <?= isset($_POST['use_default_workflow']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="use_default_workflow">
                                Use Default Workflow Instead
                            </label>
                        </div>
                        <small class="form-text text-muted">Check this to use the default workflow instead of cloning from an existing project</small>
                    </div>
                    <?php endif; ?>

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
