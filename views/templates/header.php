<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Scrum Viewer' ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0052cc;
            --secondary-color: #172b4d;
        }
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .navbar-brand img {
            height: 30px;
            margin-right: 10px;
        }
        .main-content {
            margin-top: 30px;
            padding: 20px;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tasks"></i> Scrum Viewer
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
                        <a class="nav-link" href="index.php?page=issues&action=search"><i class="fas fa-search"></i> Search Issues</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container main-content">
