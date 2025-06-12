<?php 
$pageTitle = 'Users | ' . $appName;
include 'views/templates/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-users"></i> Users</h2>
                <a href="index.php?page=users&action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add User
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No users found. <a href="index.php?page=users&action=create">Create the first user</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User Key</th>
                                    <th>Display Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['USER_KEY']) ?></td>
                                        <td><?= htmlspecialchars($user['DISPLAY_NAME']) ?></td>
                                        <td><?= htmlspecialchars($user['email_address'] ?? '') ?></td>
                                        <td>
                                            <?php if ($user['active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="index.php?page=users&action=edit&id=<?= urlencode($user['USER_KEY']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=users&action=delete&id=<?= urlencode($user['USER_KEY']) ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>