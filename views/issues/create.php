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
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e3e6f0;
    padding: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    padding: 0.75rem;
}

select.form-control {
    height: calc(1.5em + 1.5rem + 2px);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .container {
        padding: 0;
    }

    .card {
        border-radius: 0;
        margin-bottom: 0;
    }

    .card-body {
        padding: 1rem;
    }

    .row {
        margin-left: 0;
        margin-right: 0;
    }

    .col-md-6 {
        padding: 0;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    textarea.form-control {
        min-height: 120px;
    }

    /* Button group in footer */
    .form-group.text-right {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        margin: 0;
        z-index: 1000;
        display: flex;
        gap: 0.5rem;
    }

    .form-group.text-right .btn {
        flex: 1;
        padding: 0.75rem;
    }

    /* Add padding to bottom of form to account for fixed buttons */
    form {
        padding-bottom: 80px;
    }

    /* Improve select dropdowns on mobile */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 12px;
        padding-right: 2.5rem;
    }

    /* Improve labels visibility */
    label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
}
</style>

<?php include 'views/templates/footer.php'; ?>
