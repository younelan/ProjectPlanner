<?php
$pageTitle = "Create Issue - " . htmlspecialchars($project['PNAME']);
include 'views/templates/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($project['ID']) ?>"><?= htmlspecialchars($project['PNAME']) ?></a></li>
            <li class="breadcrumb-item active">Create Issue</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle"></i> Create New Issue</h2>
        </div>
        <div class="card-body">
            <form action="index.php?page=issues&action=store" method="POST">
                <input type="hidden" name="projectId" value="<?= htmlspecialchars($project['ID']) ?>">
                
                <div class="form-group">
                    <label for="summary">Summary</label>
                    <input type="text" class="form-control" id="summary" name="summary" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="issuetype">Issue Type</label>
                            <select class="form-control" id="issuetype" name="issuetype" required>
                                <?php foreach ($issueTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type['ID']) ?>">
                                        <?= htmlspecialchars($type['PNAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <?php foreach ($priorities as $priority): ?>
                                    <option value="<?= htmlspecialchars($priority['ID']) ?>">
                                        <?= htmlspecialchars($priority['PNAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="assignee">Assignee</label>
                            <select class="form-control" id="assignee" name="assignee">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['LOWER_USER_NAME']) ?>">
                                        <?= htmlspecialchars($user['LOWER_USER_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reporter">Reporter</label>
                            <select class="form-control" id="reporter" name="reporter" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['LOWER_USER_NAME']) ?>">
                                        <?= htmlspecialchars($user['LOWER_USER_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="form-group text-right">
                    <a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($project['ID']) ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    margin-bottom: 2rem;
}
.form-group {
    margin-bottom: 1rem;
}
</style>

<?php include 'views/templates/footer.php'; ?>
