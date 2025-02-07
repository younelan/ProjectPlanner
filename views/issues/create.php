<?php
$pageTitle = "Create Issue - " . htmlspecialchars($project['PNAME']);
include 'views/templates/header.php';
?>

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
                <li class="breadcrumb-item">
                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>">
                        <?= htmlspecialchars($project['PNAME']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active">Create Issue</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container">
    <div class="card">
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
                                        <?= htmlspecialchars($type['NAME']) ?> <!-- Keep using NAME as it's aliased in the model -->
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
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= htmlspecialchars($status['ID']) ?>">
                                        <?= htmlspecialchars($status['PNAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reporter">Reporter</label>
                            <select class="form-control" id="reporter" name="reporter">
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['USERNAME']) ?>" 
                                            <?= $user['USERNAME'] === User::getCurrentUser() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['DISPLAY_NAME'] ?: $user['USERNAME']) ?>
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

<?php include 'views/templates/footer.php'; ?>
