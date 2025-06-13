<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($appName); ?> - Workflows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Workflows</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>Workflows</h1>
                    <a href="index.php?page=workflows&action=import" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import Workflow
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($workflows as $workflow): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? 'Unnamed Workflow'); ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">ID: <?php echo $workflow['ID']; ?></small><br>
                                        <small class="text-muted">Creator: <?php echo htmlspecialchars($workflow['CREATORNAME'] ?? 'Unknown'); ?></small><br>
                                        <small class="text-muted">Locked: <?php echo $workflow['ISLOCKED'] === 'Y' ? 'Yes' : 'No'; ?></small>
                                    </p>
                                    <div class="btn-group" role="group">
                                        <a href="index.php?page=workflows&action=view&id=<?php echo $workflow['ID']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="index.php?page=workflows&action=edit&id=<?php echo $workflow['ID']; ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="index.php?page=workflows&action=duplicate&id=<?php echo $workflow['ID']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-copy"></i> Duplicate
                                        </a>
                                        <a href="index.php?page=workflows&action=export&id=<?php echo $workflow['ID']; ?>" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>