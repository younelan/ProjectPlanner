<?php 
$pageTitle = 'Projects | Project Agile';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Projects</li>
            </ol>
        </nav>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-project-diagram"></i> Projects</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Name</th>
                                <th>Lead</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['PKEY']) ?></td>
                                <td><?= htmlspecialchars($project['PNAME']) ?></td>
                                <td><?= htmlspecialchars($project['LEAD']) ?></td>
                                <td>
                                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
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
