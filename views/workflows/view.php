<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($appName); ?> - Workflow Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .workflow-step { border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; padding: 15px; }
        .workflow-action { border-left: 3px solid #007bff; padding-left: 10px; margin: 5px 0; }
        .workflow-meta { background-color: #f8f9fa; padding: 10px; border-radius: 3px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
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
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? 'Workflow'); ?></li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1><?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? 'Unnamed Workflow'); ?></h1>
                    <div class="btn-group">
                        <a href="index.php?page=workflows&action=edit&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="index.php?page=workflows&action=export&id=<?php echo $workflow['ID']; ?>" 
                           class="btn btn-success">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>

                <!-- Workflow Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Workflow Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID:</strong> <?php echo $workflow['ID']; ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($workflow['WORKFLOWNAME'] ?? ''); ?></p>
                                <p><strong>Creator:</strong> <?php echo htmlspecialchars($workflow['CREATORNAME'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Locked:</strong> <?php echo $workflow['ISLOCKED'] === 'Y' ? 'Yes' : 'No'; ?></p>
                                <?php if (isset($workflow['SCHEME_NAME']) && $workflow['SCHEME_NAME'] !== null): ?>
                                <p><strong>Scheme:</strong> <?php echo htmlspecialchars($workflow['SCHEME_NAME']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($workflow['parsed_xml'])): ?>
                    <!-- Workflow Steps -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Workflow Steps</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($workflow['parsed_xml']['steps'] as $step): ?>
                                <div class="workflow-step">
                                    <h6><?php echo htmlspecialchars($step['name'] ?? 'Unnamed Step'); ?> (ID: <?php echo htmlspecialchars($step['id'] ?? ''); ?>)</h6>
                                    
                                    <?php if (!empty($step['meta'])): ?>
                                        <div class="workflow-meta mb-2">
                                            <strong>Meta:</strong>
                                            <?php foreach ($step['meta'] as $key => $value): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($key ?? ''); ?>: <?php echo htmlspecialchars($value ?? ''); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($step['actions'])): ?>
                                        <div class="mt-2">
                                            <strong>Actions:</strong>
                                            <?php foreach ($step['actions'] as $action): ?>
                                                <div class="workflow-action">
                                                    <?php if ($action['type'] === 'common'): ?>
                                                        <span class="badge bg-info">Common Action ID: <?php echo htmlspecialchars($action['id'] ?? ''); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($action['action']['name'] ?? 'Unnamed Action'); ?></span>
                                                        (ID: <?php echo htmlspecialchars($action['action']['id'] ?? ''); ?>)
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Common Actions -->
                    <?php if (!empty($workflow['parsed_xml']['common_actions'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Common Actions</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($workflow['parsed_xml']['common_actions'] as $action): ?>
                                    <div class="workflow-action mb-3">
                                        <h6><?php echo htmlspecialchars($action['name'] ?? 'Unnamed Action'); ?> (ID: <?php echo htmlspecialchars($action['id'] ?? ''); ?>)</h6>
                                        <?php if (!empty($action['meta'])): ?>
                                            <div class="workflow-meta">
                                                <?php foreach ($action['meta'] as $key => $value): ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($key ?? ''); ?>: <?php echo htmlspecialchars($value ?? ''); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Raw XML -->
                <div class="card">
                    <div class="card-header">
                        <h5>Raw XML Descriptor</h5>
                    </div>
                    <div class="card-body">
                        <pre><code><?php echo htmlspecialchars($workflow['DESCRIPTOR'] ?? ''); ?></code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>