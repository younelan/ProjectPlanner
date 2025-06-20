<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? $appName ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
        :root {
            --primary-color: #2684FF;
            --nav-height: 56px;
        }
        .navbar {
            height: var(--nav-height);
            background: linear-gradient(135deg, #96866a 0%, #b4ab97 100%) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1px;
        }
        .navbar-brand img {
            height: 30px;
            margin-right: 10px;
        }
        .main-content {
            padding: 0;
            margin: 0;
        }
        .card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            border: none;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f4f5f7;
            border-bottom: 1px solid #dfe1e6;
        }
        .breadcrumb {
            background-color: transparent;
            padding-left: 0;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: 500;
        }
        .table th {
            background-color: #f4f5f7;
        }
        .app-logo {
            width: 32px;
            height: 32px;
            margin-right: 10px;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.2));
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-size: 1.4rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.svg" alt="Logo" class="app-logo">
                <?= htmlspecialchars($appName) ?>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-project-diagram"></i> Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=users"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=issues&action=search"><i class="fas fa-search"></i> Search Issues</a>
                    </li>
                    <!-- Add Sprint navigation item -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=sprints&action=list">Sprints</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="container-fluid">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= nl2br(htmlspecialchars($_SESSION['error'])) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
