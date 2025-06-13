<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($appName); ?> - Visual Workflow Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .section-card { margin-bottom: 20px; }
        .meta-row, .action-row { border-bottom: 1px solid #eee; padding: 10px 0; }
        .meta-row:last-child, .action-row:last-child { border-bottom: none; }
        .btn-sm { margin: 2px; }
        .workflow-section { 
            background: #f8f9fa; 
            border-radius: 5px; 
            padding: 15px; 
            margin: 10px 0; 
            border: 1px solid #dee2e6;
        }
        .sortable-item {
            cursor: move;
            position: relative;
            transition: all 0.2s ease;
        }
        .sortable-item:hover {
            background-color: #e9ecef;
        }
        .sortable-item.sortable-drag {
            opacity: 0.8;
            transform: rotate(5deg);
        }
        .sortable-ghost {
            opacity: 0.4;
        }
        .sortable-chosen {
            cursor: grabbing;
        }
        .drag-handle {
            cursor: move;
            color: #6c757d;
            margin-right: 10px;
        }
        .drag-handle:hover {
            color: #495057;
        }
        .item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffffff;
            border-radius: 3px;
            border: 1px solid #dee2e6;
        }
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

                <form method="post" action="index.php?page=workflows&action=update&id=<?php echo $workflow['ID']; ?>" id="visual-workflow-form">
                    
                    <!-- Hidden field to indicate visual mode -->
                    <input type="hidden" name="edit_mode" value="visual">

                    <!-- Serialize the current workflow structure as hidden fields for reference -->
                    <?php if (isset($workflow['parsed_xml'])): ?>
                        <input type="hidden" name="original_xml" value="<?php echo htmlspecialchars($workflow['DESCRIPTOR'] ?? ''); ?>">
                    <?php endif; ?>
                    
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
                    <div class="card section-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Initial Actions</h5>
                                <button type="button" class="btn btn-sm btn-success" onclick="addInitialAction()">
                                    <i class="fas fa-plus"></i> Add Initial Action
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="initial-actions-container">
                            <?php 
                            $initialActions = isset($workflow['parsed_xml']['initial_actions']) ? $workflow['parsed_xml']['initial_actions'] : [];
                            foreach ($initialActions as $index => $action): 
                            ?>
                                <div class="workflow-section sortable-item" data-type="initial-action">
                                    <div class="item-header">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-grip-vertical drag-handle"></i>
                                            <strong>Initial Action <?php echo $index + 1; ?></strong>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Action Meta</h6>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addActionMeta(this, <?php echo $index; ?>, 'initial_actions')">
                                                <i class="fas fa-plus"></i> Add Meta
                                            </button>
                                        </div>
                                        <div class="action-meta-container">
                                            <?php if (!empty($action['meta'])): ?>
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
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Validators -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Validators</h6>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="addValidator(this, <?php echo $index; ?>, 'initial_actions')">
                                                <i class="fas fa-plus"></i> Add Validator
                                            </button>
                                        </div>
                                        <div class="validators-container">
                                            <?php if (!empty($action['validators'])): ?>
                                                <?php foreach ($action['validators'] as $validatorIndex => $validator): ?>
                                                    <div class="card mt-2">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Validator Type</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][validators][<?php echo $validatorIndex; ?>][type]" 
                                                                           value="<?php echo htmlspecialchars($validator['type'] ?? ''); ?>" placeholder="e.g., class">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Validator Name</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][validators][<?php echo $validatorIndex; ?>][name]" 
                                                                           value="<?php echo htmlspecialchars($validator['name'] ?? ''); ?>" placeholder="Validator name">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Actions</label>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeValidatorCard(this)">
                                                                        <i class="fas fa-trash"></i> Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <!-- Validator Args -->
                                                            <?php if (!empty($validator['args'])): ?>
                                                                <div class="mt-2">
                                                                    <label class="form-label">Arguments</label>
                                                                    <?php foreach ($validator['args'] as $argName => $argValue): ?>
                                                                        <div class="row mb-1">
                                                                            <div class="col-md-4">
                                                                                <input type="text" class="form-control form-control-sm" 
                                                                                       name="initial_actions[<?php echo $index; ?>][validators][<?php echo $validatorIndex; ?>][arg_names][]" 
                                                                                       value="<?php echo htmlspecialchars($argName); ?>" placeholder="Arg name">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control form-control-sm" 
                                                                                       name="initial_actions[<?php echo $index; ?>][validators][<?php echo $validatorIndex; ?>][arg_values][]" 
                                                                                       value="<?php echo htmlspecialchars($argValue); ?>" placeholder="Arg value">
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                                                    <i class="fas fa-minus"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Conditions (restrict-to) -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Conditions (Restrict To)</h6>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="addCondition(this, <?php echo $index; ?>, 'initial_actions')">
                                                <i class="fas fa-plus"></i> Add Condition
                                            </button>
                                        </div>
                                        <div class="conditions-container">
                                            <?php if (!empty($action['conditions'])): ?>
                                                <?php foreach ($action['conditions'] as $conditionIndex => $condition): ?>
                                                    <div class="card mt-2">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Condition Type</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][conditions][<?php echo $conditionIndex; ?>][type]" 
                                                                           value="<?php echo htmlspecialchars($condition['type'] ?? ''); ?>" placeholder="e.g., class">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Actions</label>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeConditionCard(this)">
                                                                        <i class="fas fa-trash"></i> Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Results -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Results</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addResult(this, <?php echo $index; ?>, 'initial_actions')">
                                                <i class="fas fa-plus"></i> Add Result
                                            </button>
                                        </div>
                                        <div class="results-container">
                                            <?php if (!empty($action['results'])): ?>
                                                <?php foreach ($action['results'] as $resultIndex => $result): ?>
                                                    <div class="card mt-2">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Old Status</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][results][<?php echo $resultIndex; ?>][old_status]" 
                                                                           value="<?php echo htmlspecialchars($result['old_status'] ?? ''); ?>" placeholder="Old status">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Status</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][results][<?php echo $resultIndex; ?>][status]" 
                                                                           value="<?php echo htmlspecialchars($result['status'] ?? ''); ?>" placeholder="New status">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Step</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="initial_actions[<?php echo $index; ?>][results][<?php echo $resultIndex; ?>][step]" 
                                                                           value="<?php echo htmlspecialchars($result['step'] ?? ''); ?>" placeholder="Step ID">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Actions</label>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeResultCard(this)">
                                                                        <i class="fas fa-trash"></i> Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

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
                    <div class="card section-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Workflow Steps</h5>
                                <button type="button" class="btn btn-sm btn-success" onclick="addWorkflowStep()">
                                    <i class="fas fa-plus"></i> Add Step
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="workflow-steps-container">
                            <?php 
                            $steps = isset($workflow['parsed_xml']['steps']) ? $workflow['parsed_xml']['steps'] : [];
                            foreach ($steps as $index => $step): 
                            ?>
                                <div class="workflow-section sortable-item" data-type="workflow-step">
                                    <div class="item-header">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-grip-vertical drag-handle"></i>
                                            <strong>Step <?php echo $index + 1; ?>: <?php echo htmlspecialchars($step['name'] ?? 'Unnamed Step'); ?></strong>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Step ID</label>
                                            <input type="text" class="form-control" 
                                                   name="steps[<?php echo $index; ?>][id]" 
                                                   value="<?php echo htmlspecialchars($step['id'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Step Name</label>
                                            <input type="text" class="form-control step-name-input" 
                                                   name="steps[<?php echo $index; ?>][name]" 
                                                   value="<?php echo htmlspecialchars($step['name'] ?? ''); ?>"
                                                   onchange="updateStepHeader(this)">
                                        </div>
                                    </div>
                                    
                                    <!-- Step Meta -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Step Meta</h6>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addStepMeta(this, <?php echo $index; ?>)">
                                                <i class="fas fa-plus"></i> Add Meta
                                            </button>
                                        </div>
                                        <div class="step-meta-container">
                                            <?php if (!empty($step['meta'])): ?>
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
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

                <!-- Debug: Show what will be sent -->
                <script>
                document.getElementById('visual-workflow-form').addEventListener('submit', function(e) {
                    console.log('Form data being submitted:');
                    const formData = new FormData(this);
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                });
                </script>
            </div>
        </div>
    </div>

    <script>
        let initialActionCounter = <?php echo count($initialActions ?? []); ?>;
        let workflowStepCounter = <?php echo count($steps ?? []); ?>;

        // Initialize sortable containers
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing sortable containers...');
            
            // Check if Sortable is available
            if (typeof Sortable === 'undefined') {
                console.error('SortableJS library not loaded');
                return;
            }
            
            // Make initial actions sortable
            const initialActionsContainer = document.getElementById('initial-actions-container');
            if (initialActionsContainer) {
                console.log('Setting up initial actions sortable');
                try {
                    const sortableInitial = Sortable.create(initialActionsContainer, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.drag-handle',
                        forceFallback: true,
                        onStart: function(evt) {
                            console.log('Drag started for initial action');
                        },
                        onEnd: function(evt) {
                            console.log('Drag ended for initial action, reindexing...');
                            setTimeout(() => {
                                reindexContainer('initial-actions-container', 'initial_actions');
                            }, 50);
                        }
                    });
                    console.log('Initial actions sortable created successfully');
                } catch (error) {
                    console.error('Error creating initial actions sortable:', error);
                }
            }

            // Make workflow steps sortable
            const workflowStepsContainer = document.getElementById('workflow-steps-container');
            if (workflowStepsContainer) {
                console.log('Setting up workflow steps sortable');
                try {
                    const sortableSteps = Sortable.create(workflowStepsContainer, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.drag-handle',
                        forceFallback: false,
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        onStart: function(evt) {
                            console.log('Drag started for workflow step');
                        },
                        onEnd: function(evt) {
                            console.log('Drag ended for workflow step, reindexing...');
                            console.log('Old index:', evt.oldIndex, 'New index:', evt.newIndex);
                            console.log('Item moved:', evt.item);
                            
                            // Ensure the DOM change has taken place
                            if (evt.oldIndex !== evt.newIndex) {
                                console.log('Position actually changed, reindexing...');
                                
                                // Debug: Check DOM order before reindexing
                                console.log('DOM order before reindexing:');
                                const stepsBefore = workflowStepsContainer.querySelectorAll('.sortable-item');
                                stepsBefore.forEach((step, index) => {
                                    const stepName = step.querySelector('.step-name-input')?.value || 'Unknown';
                                    console.log(`Position ${index}: ${stepName}`);
                                });
                                
                                setTimeout(() => {
                                    reindexContainer('workflow-steps-container', 'steps');
                                    updateStepNumbers();
                                    
                                    // Debug: Check DOM order after reindexing
                                    console.log('DOM order after reindexing:');
                                    const stepsAfter = workflowStepsContainer.querySelectorAll('.sortable-item');
                                    stepsAfter.forEach((step, index) => {
                                        const stepName = step.querySelector('.step-name-input')?.value || 'Unknown';
                                        console.log(`Position ${index}: ${stepName}`);
                                    });
                                }, 50);
                            } else {
                                console.log('Position did not change');
                            }
                        }
                    });
                    console.log('Workflow steps sortable created successfully');
                } catch (error) {
                    console.error('Error creating workflow steps sortable:', error);
                }
            }
        });

        function removeMetaRow(button) {
            button.closest('.meta-row').remove();
        }
        
        function removeRow(button) {
            button.closest('.row').remove();
        }

        function removeItem(button) {
            const item = button.closest('.sortable-item');
            const container = item.parentElement;
            item.remove();
            
            // Reindex the container
            if (container.id === 'initial-actions-container') {
                reindexContainer('initial-actions-container', 'initial_actions');
            } else if (container.id === 'workflow-steps-container') {
                reindexContainer('workflow-steps-container', 'steps');
                updateStepNumbers();
            }
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

        function addInitialAction() {
            const container = document.getElementById('initial-actions-container');
            const newAction = document.createElement('div');
            newAction.className = 'workflow-section sortable-item';
            newAction.setAttribute('data-type', 'initial-action');
            
            newAction.innerHTML = `
                <div class="item-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <strong>Initial Action ${initialActionCounter + 1}</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Action ID</label>
                        <input type="text" class="form-control" name="initial_actions[${initialActionCounter}][id]" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Action Name</label>
                        <input type="text" class="form-control" name="initial_actions[${initialActionCounter}][name]" value="">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">View</label>
                        <input type="text" class="form-control" name="initial_actions[${initialActionCounter}][view]" value="">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Action Meta</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addActionMeta(this, ${initialActionCounter}, 'initial_actions')">
                            <i class="fas fa-plus"></i> Add Meta
                        </button>
                    </div>
                    <div class="action-meta-container">
                    </div>
                </div>
            `;
            
            container.appendChild(newAction);
            initialActionCounter++;
        }

        function addWorkflowStep() {
            const container = document.getElementById('workflow-steps-container');
            const newStep = document.createElement('div');
            newStep.className = 'workflow-section sortable-item';
            newStep.setAttribute('data-type', 'workflow-step');
            
            newStep.innerHTML = `
                <div class="item-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <strong>Step ${workflowStepCounter + 1}: New Step</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Step ID</label>
                        <input type="text" class="form-control" name="steps[${workflowStepCounter}][id]" value="">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Step Name</label>
                        <input type="text" class="form-control step-name-input" name="steps[${workflowStepCounter}][name]" value="" onchange="updateStepHeader(this)">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Step Meta</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addStepMeta(this, ${workflowStepCounter})">
                            <i class="fas fa-plus"></i> Add Meta
                        </button>
                    </div>
                    <div class="step-meta-container">
                    </div>
                </div>
            `;
            
            container.appendChild(newStep);
            workflowStepCounter++;
            updateStepNumbers();
        }

        function addActionMeta(button, actionIndex, actionType) {
            const container = button.parentElement.nextElementSibling;
            const newMeta = document.createElement('div');
            newMeta.className = 'row mb-2';
            newMeta.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="${actionType}[${actionIndex}][meta_names][]" placeholder="Meta name">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control form-control-sm" name="${actionType}[${actionIndex}][meta_values][]" placeholder="Meta value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newMeta);
        }

        function addStepMeta(button, stepIndex) {
            const container = button.parentElement.nextElementSibling;
            const newMeta = document.createElement('div');
            newMeta.className = 'row mb-2';
            newMeta.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="steps[${stepIndex}][meta_names][]" placeholder="Meta name">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control form-control-sm" name="steps[${stepIndex}][meta_values][]" placeholder="Meta value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newMeta);
        }

        function addValidator(button, actionIndex, actionType) {
            const container = button.parentElement.nextElementSibling;
            const validatorIndex = container.children.length;
            const newValidator = document.createElement('div');
            newValidator.className = 'card mt-2';
            newValidator.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Validator Type</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][validators][${validatorIndex}][type]" 
                                   placeholder="e.g., class">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validator Name</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][validators][${validatorIndex}][name]" 
                                   placeholder="Validator name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Actions</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeValidatorCard(this)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newValidator);
        }

        function addCondition(button, actionIndex, actionType) {
            const container = button.parentElement.nextElementSibling;
            const conditionIndex = container.children.length;
            const newCondition = document.createElement('div');
            newCondition.className = 'card mt-2';
            newCondition.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Condition Type</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][conditions][${conditionIndex}][type]" 
                                   placeholder="e.g., class">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Actions</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeConditionCard(this)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newCondition);
        }

        function addResult(button, actionIndex, actionType) {
            const container = button.parentElement.nextElementSibling;
            const resultIndex = container.children.length;
            const newResult = document.createElement('div');
            newResult.className = 'card mt-2';
            newResult.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Old Status</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][results][${resultIndex}][old_status]" 
                                   placeholder="Old status">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][results][${resultIndex}][status]" 
                                   placeholder="New status">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Step</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="${actionType}[${actionIndex}][results][${resultIndex}][step]" 
                                   placeholder="Step ID">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Actions</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeResultCard(this)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newResult);
        }

        function removeValidatorCard(button) {
            button.closest('.card').remove();
        }

        function removeConditionCard(button) {
            button.closest('.card').remove();
        }

        function removeResultCard(button) {
            button.closest('.card').remove();
        }

        function updateStepHeader(input) {
            const stepSection = input.closest('.workflow-section');
            const header = stepSection.querySelector('.item-header strong');
            const stepNumber = Array.from(stepSection.parentElement.children).indexOf(stepSection) + 1;
            const stepName = input.value || 'New Step';
            header.textContent = `Step ${stepNumber}: ${stepName}`;
        }

        function updateStepNumbers() {
            console.log('updateStepNumbers called');
            const stepSections = document.querySelectorAll('#workflow-steps-container .sortable-item');
            stepSections.forEach((section, index) => {
                const header = section.querySelector('.item-header strong');
                const nameInput = section.querySelector('.step-name-input');
                const stepName = nameInput?.value || 'New Step';
                const newHeaderText = `Step ${index + 1}: ${stepName}`;
                console.log(`Updating step ${index} header to: ${newHeaderText}`);
                if (header) {
                    header.textContent = newHeaderText;
                }
            });
            console.log('updateStepNumbers completed');
        }

        function reindexContainer(containerId, fieldPrefix) {
            console.log(`Reindexing container: ${containerId} with prefix: ${fieldPrefix}`);
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Container ${containerId} not found`);
                return;
            }
            
            const items = container.querySelectorAll('.sortable-item');
            console.log(`Found ${items.length} items to reindex`);
            
            items.forEach((item, index) => {
                console.log(`Reindexing item ${index}`);
                
                // Update all input names within this item
                const inputs = item.querySelectorAll('input[name*="["]');
                inputs.forEach(input => {
                    const oldName = input.getAttribute('name');
                    // Replace the first occurrence of [number] with [index]
                    const newName = oldName.replace(/\[\d+\]/, `[${index}]`);
                    console.log(`Updating input name from ${oldName} to ${newName}`);
                    input.setAttribute('name', newName);
                });

                // Update onclick handlers for meta buttons
                const metaButtons = item.querySelectorAll('button[onclick*="addActionMeta"], button[onclick*="addStepMeta"]');
                metaButtons.forEach(button => {
                    const oldOnclick = button.getAttribute('onclick');
                    const newOnclick = oldOnclick.replace(/,\s*\d+/, `, ${index}`);
                    console.log(`Updating button onclick from ${oldOnclick} to ${newOnclick}`);
                    button.setAttribute('onclick', newOnclick);
                });

                // Update header text for initial actions
                if (containerId === 'initial-actions-container') {
                    const header = item.querySelector('.item-header strong');
                    if (header) {
                        header.textContent = `Initial Action ${index + 1}`;
                    }
                }
            });
            
            console.log('Reindexing completed');
        }
    </script>
</body>
</html>