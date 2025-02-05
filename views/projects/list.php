<?php 
$pageTitle = 'Projects | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-project-diagram"></i> Projects</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%"><i class="fas fa-key"></i> Key</th>
                                <th width="40%">Name</th>
                                <!--<th width="20%"><i class="fas fa-user"></i> Lead</th>-->
                                <th width="30%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= htmlspecialchars($project['PKEY']) ?></span></td>
                                <td>
                                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" class="text-dark">
                                        <?= htmlspecialchars($project['PNAME']) ?>
                                        <?php if ($project['DESCRIPTION']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($project['DESCRIPTION']) ?></small>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <!--<td><?= htmlspecialchars($project['LEAD']) ?></td>-->
                                <td>
                                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-tasks"></i> Issues
                                    </a>
                                    <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-columns"></i> Board
                                    </a>
                                    <!-- New Edit button -->
                                    <a href="index.php?page=projects&action=edit&id=<?= $project['ID'] ?>" 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Edit
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
</div>

<?php include 'views/templates/footer.php'; ?>
