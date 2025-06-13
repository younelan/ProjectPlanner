<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($appName); ?> - Edit Workflow</title>
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
                        <li class="breadcrumb-item"><a href="index.php?page=workflows">Workflows</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=workflows&action=view&id=<?php echo $workflow['ID']; ?>"><?php echo htmlspecialchars($workflow['WORKFLOWNAME']); ?></a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>Edit Workflow</h1>
                    <div class="btn-group">
                        <a href="index.php?page=workflows&action=editVisual&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Visual Editor
                        </a>
                        <a href="index.php?page=workflows&action=view&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="card">
                        <div class="card-header">
                            <h5>Workflow Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Workflow Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($workflow['WORKFLOWNAME']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="scheme_name" class="form-label">Scheme Name</label>
                                <input type="text" class="form-control" id="scheme_name" name="scheme_name" 
                                       value="<?php echo htmlspecialchars($workflow['SCHEME_NAME'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="locked" name="locked" value="Y"
                                           <?php echo $workflow['ISLOCKED'] === 'Y' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="locked">
                                        Lock Workflow
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descriptor" class="form-label">XML Descriptor</label>
                                <textarea class="form-control" id="descriptor" name="descriptor" rows="20" 
                                          style="font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($workflow['DESCRIPTOR']); ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    Be careful when editing the XML descriptor. Invalid XML will break the workflow.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="index.php?page=workflows&action=view&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add syntax highlighting or validation if needed
        document.getElementById('descriptor').addEventListener('input', function() {
            // Could add XML validation here
        });
    </script>
</body>
</html>