<?php 
$pageTitle = "Edit Issue: " . htmlspecialchars($issue['SUMMARY']);
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item">
                    <a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($issue['PROJECT']) ?>">
                        Project
                    </a>
                </li>
                <li class="breadcrumb-item active">Edit Issue</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Edit Issue</h2>
            </div>
            <div class="card-body">
                <form action="index.php?page=issues&action=update&id=<?= $issue['ID'] ?>" method="POST">
                    <div class="form-group">
                        <label for="summary">Summary</label>
                        <input type="text" class="form-control" id="summary" name="summary" 
                               value="<?= htmlspecialchars($issue['SUMMARY']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($issue['DESCRIPTION']) ?></textarea>
                    </div>

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
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reporter">Reporter</label>
                        <select class="form-control" id="reporter" name="reporter">
                        <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['LOWER_USER_NAME']) ?>">
                                        <?= htmlspecialchars($user['LOWER_USER_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="issuetype">Issue Type</label>
                        <select class="form-control" id="issuetype" name="issuetype">
                            <?php foreach ($issueTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type['ID']) ?>"
                                    <?= $type['ID'] === $issue['ISSUETYPE'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['PNAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <?php foreach ($priorities as $priority): ?>
                                <option value="<?= htmlspecialchars($priority['ID']) ?>"
                                    <?= $priority['ID'] === $issue['PRIORITY'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($priority['PNAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" 
                           class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
