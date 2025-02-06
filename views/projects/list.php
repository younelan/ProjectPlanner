<?php 
$pageTitle = 'Projects | ' . $appName;
include 'views/templates/header.php'; 
?>

<style>
    :root {
        /* Professional color palette */
        --color-primary: #2684FF;
        --color-success: #36B37E;
        --color-warning: #FFAB00;
        --color-danger: #FF5630;
        --color-info: #6554C0;
        --color-bg: #F4F5F7;
        --color-text: #172B4D;
        
        /* Project card accent colors */
        --accent-1: var(--color-success);
        --accent-2: var(--color-primary);
        --accent-3: var(--color-info);
        --accent-4: var(--color-warning);
    }

    .app-container {
        margin: 0;
        min-height: calc(100vh - var(--nav-height));
        background: #f2f2f2; /* Softer global background */
    }

    .page-header {
        background: linear-gradient(135deg, rgb(224 228 202) 0%, rgb(236, 215, 190) 100%);
        padding: 1rem 1.5rem;
        margin: 0;
        margin-bottom: 3px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .projects-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        color: darkred;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .project-card {
        position: relative;
        background: #ffffff;
        color: #343a40;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e3e3e3;
    }

    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .project-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        position: relative;
        background: #fafafa !important;
        color: #212529 !important;
        cursor: pointer;
        transition: background 0.2s;
    }

    .project-header:hover {
        background: #f4f5f7 !important;
    }

    .project-header::before {
        content: none; /* Remove loud stripes */
    }

    .project-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .project-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--color-text);
        text-decoration: none;
        flex: 1;
    }

    .project-header a {
        text-decoration: none;
    }

    .project-header:hover .project-name {
        color: var(--color-primary);
    }

    .project-key {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 3px;
        white-space: nowrap;
    }

    .project-card:nth-child(4n+1) .project-key { background: rgba(54, 179, 126, 0.1); color: var(--accent-1); }
    .project-card:nth-child(4n+2) .project-key { background: rgba(38, 132, 255, 0.1); color: var(--accent-2); }
    .project-card:nth-child(4n+3) .project-key { background: rgba(101, 84, 192, 0.1); color: var(--accent-3); }
    .project-card:nth-child(4n+4) .project-key { background: rgba(255, 171, 0, 0.1); color: var(--accent-4); }

    .project-card:nth-child(4n+1) .project-header {
        background: linear-gradient(135deg, #edf7fa 0%, #dceef7 100%) !important;
        color: #172B4D !important;
    }
    .project-card:nth-child(4n+1) .project-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #aed4e4; /* subtle stripe */
    }

    .project-card:nth-child(4n+2) .project-header {
        background: linear-gradient(135deg, #f9f1e8 0%, #f6eadf 100%) !important;
        color: #172B4D !important;
    }
    .project-card:nth-child(4n+2) .project-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #e8dacb;
    }

    .project-card:nth-child(4n+3) .project-header {
        background: linear-gradient(135deg, #f3e9f6 0%, #ede2f1 100%) !important;
        color: #172B4D !important;
    }
    .project-card:nth-child(4n+3) .project-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #d5c4df;
    }

    .project-card:nth-child(4n+4) .project-header {
        background: linear-gradient(135deg, #e9fbf1 0%, #e2f7ec 100%) !important;
        color: #172B4D !important;
    }
    .project-card:nth-child(4n+4) .project-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #c0e2cf;
    }

    .project-lead {
        color: #5e6c84;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .project-content {
        padding: 1.5rem;
    }
    .project-description {
        color: #42526e;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .project-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .action-group {
        display: flex;
        gap: 0.75rem;
        margin: 0 0.75rem;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 3px;
        white-space: nowrap;
        min-width: 0;
        transition: all 0.2s;
    }
    .btn i {
        margin-right: 0.3rem;
        font-size: 0.875rem;
    }
    .btn-primary {
        background: var(--color-primary);
        border-color: var(--color-primary);
    }
    .btn-outline-primary,
    .btn-outline-info,
    .btn-outline-secondary {
        background: white;
        border: 1px solid #dfe1e6;
    }
    .btn-outline-primary:hover,
    .btn-outline-info:hover,
    .btn-outline-secondary:hover {
        background: #f4f5f7;
    }
    @media (max-width: 768px) {
        .page-header {
            position: relative; /* Remove sticky positioning */
            padding: 1rem;
        }

        .projects-container {
            padding: 0;
        }

        .project-grid {
            margin-top: 0;
            gap: 0;
        }

        .page-title {
            font-size: 1.25rem;
        }

        .create-btn {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
        }

        .projects-container {
            padding: 0;
        }

        .project-grid {
            grid-template-columns: 1fr;
            gap: 0;
            margin: 0;
        }

        .project-card {
            margin: 0;
            border-radius: 0;
            border-left: none;
            border-right: none;
            border-bottom: 1px solid #e3e3e3;
            margin-bottom: 0.5rem;
        }

        .project-card:first-child {
            border-top: none;
        }

        .project-card:last-child {
            border-bottom: none;
        }

        .project-content {
            padding: 0.75rem 0.5rem;
        }

        .project-actions {
            flex-wrap: nowrap;
            width: 100%;
            justify-content: space-between;
            gap: 0.25rem;
        }

        .action-group {
            flex: 1;
            justify-content: center;
            gap: 0.25rem;
            margin: 0 0.25rem;
        }

        .btn {
            padding: 0.35rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn i {
            margin-right: 0.25rem;
        }
    }
</style>

<div class="app-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-project-diagram"></i>
            Projects
        </h1>
        <a href="index.php?page=projects&action=create" class="create-btn">
            <i class="fas fa-plus"></i> Create Project
        </a>
    </div>

    <div class="projects-container">
        <div class="project-grid">
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <div class="project-header">
                        <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" class="d-block text-decoration-none">
                            <div class="project-title">
                                <span class="project-name">
                                    <?= htmlspecialchars($project['PNAME']) ?>
                                </span>
                                <span class="project-key"><?= htmlspecialchars($project['PKEY']) ?></span>
                            </div>
                            <div class="project-lead">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($project['LEAD']) ?>
                            </div>
                        </a>
                    </div>
                    
                    <div class="project-content">
                        <?php if ($project['DESCRIPTION']): ?>
                            <div class="project-description">
                                <?= htmlspecialchars($project['DESCRIPTION']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="project-actions">
                            <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-tasks"></i> Issues
                            </a>
                            <div class="action-group">
                                <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-columns"></i> Board
                                </a>
                                <a href="index.php?page=sprints&action=list&projectId=<?= $project['ID'] ?>" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-running"></i> Sprints
                                </a>
                            </div>
                            <a href="index.php?page=projects&action=edit&id=<?= $project['ID'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>
