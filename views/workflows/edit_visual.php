<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($appName); ?> - Visual Workflow Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .section-card { margin-bottom: 20px; }
        .meta-row, .action-row { border-bottom: 1px solid #eee; padding: 10px 0; }
        .meta-row:last-child, .action-row:last-child { border-bottom: none; }
        .btn-sm { margin: 2px; }
        .workflow-section { background: #f8f9fa; border-radius: 5px; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=workflows">Workflows</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=workflows&action=view&id=<?php echo $workflow['ID']; ?>"><?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? 'Workflow'); ?></a></li>
                        <li class="breadcrumb-item active">Visual Editor</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>Visual Workflow Editor</h1>
                    <div class="btn-group">
                        <a href="index.php?page=workflows&action=edit&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-code"></i> XML Editor
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

                <form method="post" action="index.php?page=workflows&action=update&id=<?php echo $workflow['ID']; ?>">
                    
                    <!-- Basic Workflow Info -->
                    <div class="card section-card">
                        <div class="card-header">
                            <h5>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Workflow Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="locked" name="locked" value="Y"
                                                   <?php echo $workflow['ISLOCKED'] === 'Y' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="locked">Lock Workflow</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Metadata -->
                    <?php if (isset($workflow['parsed_xml']['meta']) && !empty($workflow['parsed_xml']['meta'])): ?>
                    <div class="card section-card">
                        <div class="card-header">
                            <h5>Workflow Metadata</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($workflow['parsed_xml']['meta'] as $name => $value): ?>
                                <div class="meta-row">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Meta Name</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="meta_names[]" value="<?php echo htmlspecialchars($name); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Meta Value</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="meta_values[]" value="<?php echo htmlspecialchars($value); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label><br>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeMetaRow(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addMetaRow()">
                                <i class="fas fa-plus"></i> Add Meta
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Initial Actions -->
                    <?php if (isset($workflow['parsed_xml']['initial_actions']) && !empty($workflow['parsed_xml']['initial_actions'])): ?>
                    <div class="card section-card">
                        <div class="card-header">
                            <h5>Initial Actions</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($workflow['parsed_xml']['initial_actions'] as $index => $action): ?>
                                <div class="workflow-section">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Action ID</label>
                                            <input type="text" class="form-control" 
                                                   name="initial_actions[<?php echo $index; ?>][id]" 
                                                   value="<?php echo htmlspecialchars($action['id'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Action Name</label>
                                            <input type="text" class="form-control" 
                                                   name="initial_actions[<?php echo $index; ?>][name]" 
                                                   value="<?php echo htmlspecialchars($action['name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">View</label>
                                            <input type="text" class="form-control" 
                                                   name="initial_actions[<?php echo $index; ?>][view]" 
                                                   value="<?php echo htmlspecialchars($action['view'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Action Meta -->
                                    <?php if (!empty($action['meta'])): ?>
                                        <div class="mt-3">
                                            <h6>Action Meta</h6>
                                            <?php foreach ($action['meta'] as $metaName => $metaValue): ?>
                                                <div class="row mb-2">
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="initial_actions[<?php echo $index; ?>][meta_names][]" 
                                                               value="<?php echo htmlspecialchars($metaName); ?>" placeholder="Meta name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="initial_actions[<?php echo $index; ?>][meta_values][]" 
                                                               value="<?php echo htmlspecialchars($metaValue); ?>" placeholder="Meta value">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Common Actions -->
                    <?php if (isset($workflow['parsed_xml']['common_actions']) && !empty($workflow['parsed_xml']['common_actions'])): ?>
                    <div class="card section-card">
                        <div class="card-header">
                            <h5>Common Actions</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($workflow['parsed_xml']['common_actions'] as $index => $action): ?>
                                <div class="workflow-section">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Action ID</label>
                                            <input type="text" class="form-control" 
                                                   name="common_actions[<?php echo $index; ?>][id]" 
                                                   value="<?php echo htmlspecialchars($action['id'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Action Name</label>
                                            <input type="text" class="form-control" 
                                                   name="common_actions[<?php echo $index; ?>][name]" 
                                                   value="<?php echo htmlspecialchars($action['name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">View</label>
                                            <input type="text" class="form-control" 
                                                   name="common_actions[<?php echo $index; ?>][view]" 
                                                   value="<?php echo htmlspecialchars($action['view'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Action Meta -->
                                    <?php if (!empty($action['meta'])): ?>
                                        <div class="mt-3">
                                            <h6>Action Meta</h6>
                                            <?php foreach ($action['meta'] as $metaName => $metaValue): ?>
                                                <div class="row mb-2">
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="common_actions[<?php echo $index; ?>][meta_names][]" 
                                                               value="<?php echo htmlspecialchars($metaName); ?>" placeholder="Meta name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="common_actions[<?php echo $index; ?>][meta_values][]" 
                                                               value="<?php echo htmlspecialchars($metaValue); ?>" placeholder="Meta value">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Workflow Steps -->
                    <?php if (isset($workflow['parsed_xml']['steps']) && !empty($workflow['parsed_xml']['steps'])): ?>
                    <div class="card section-card">
                        <div class="card-header">
                            <h5>Workflow Steps</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($workflow['parsed_xml']['steps'] as $index => $step): ?>
                                <div class="workflow-section">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Step ID</label>
                                            <input type="text" class="form-control" 
                                                   name="steps[<?php echo $index; ?>][id]" 
                                                   value="<?php echo htmlspecialchars($step['id'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Step Name</label>
                                            <input type="text" class="form-control" 
                                                   name="steps[<?php echo $index; ?>][name]" 
                                                   value="<?php echo htmlspecialchars($step['name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Step Meta -->
                                    <?php if (!empty($step['meta'])): ?>
                                        <div class="mt-3">
                                            <h6>Step Meta</h6>
                                            <?php foreach ($step['meta'] as $metaName => $metaValue): ?>
                                                <div class="row mb-2">
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="steps[<?php echo $index; ?>][meta_names][]" 
                                                               value="<?php echo htmlspecialchars($metaName); ?>" placeholder="Meta name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="steps[<?php echo $index; ?>][meta_values][]" 
                                                               value="<?php echo htmlspecialchars($metaValue); ?>" placeholder="Meta value">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hidden field to indicate visual mode -->
                    <input type="hidden" name="edit_mode" value="visual">

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
        function removeMetaRow(button) {
            button.closest('.meta-row').remove();
        }
        
        function removeRow(button) {
            button.closest('.row').remove();
        }
        
        function addMetaRow() {
            const container = document.querySelector('.card-body');
            const newRow = document.createElement('div');
            newRow.className = 'meta-row';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Meta Name</label>
                        <input type="text" class="form-control form-control-sm" name="meta_names[]">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta Value</label>
                        <input type="text" class="form-control form-control-sm" name="meta_values[]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label><br>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeMetaRow(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.insertBefore(newRow, container.querySelector('.mt-2'));
        }
    </script>
</body>
</html>