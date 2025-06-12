<?php 
$pageTitle = 'Edit User | ' . $appName;
include 'views/templates/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-user-edit"></i> Edit User</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=users&action=update&id=<?= urlencode($user['USER_KEY']) ?>" method="POST">
                    <div class="form-group">
                        <label for="user_key">User Key *</label>
                        <input type="text" class="form-control" id="user_key" name="USER_KEY" 
                               value="<?= htmlspecialchars($_POST['USER_KEY'] ?? $user['USER_KEY']) ?>" required>
                        <small class="form-text text-muted">Unique identifier (letters, numbers, dots, underscores, hyphens only)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_name">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="DISPLAY_NAME" 
                               value="<?= htmlspecialchars($_POST['DISPLAY_NAME'] ?? $user['DISPLAY_NAME']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email_address">Email Address</label>
                        <input type="email" class="form-control" id="email_address" name="email_address" 
                               value="<?= htmlspecialchars($_POST['email_address'] ?? $user['email_address'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                   <?= (isset($_POST['active']) || $user['active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="active">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="index.php?page=users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>