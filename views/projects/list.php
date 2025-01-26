<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project List</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1>Project List</h1>

        <!-- Add New Project Button -->
         <!--
        <a href="index.php?page=projects&action=add" class="btn btn-success mb-4">Add New Project</a>
        -->

        <!-- Projects Table -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Project Name</th>
                    <th scope="col">Project Key</th>
                    <th scope="col">Lead</th>
                    <th scope="col">Description</th>
                    <th scope="col">Project Type</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Make sure you are fetching projects correctly
                // Example query to fetch projects from the database (replace with your actual query)
                // $projects = $db->query("SELECT * FROM PROJECT");
                
                // Assuming $projects is an array of project data
                if (!empty($projects)) {
                    foreach ($projects as $project):
                ?>
                    <tr>
                        <th scope="row"><?= htmlspecialchars($project['ID']) ?></th>
                        <td><?= htmlspecialchars($project['PNAME']) ?></td>
                        <td><?= htmlspecialchars($project['PKEY']) ?></td>
                        <td><?= htmlspecialchars($project['LEAD']) ?></td>
                        <td><?= nl2br(htmlspecialchars($project['DESCRIPTION'])) ?></td>
                        <td><?= htmlspecialchars($project['PROJECTTYPE']) ?></td>
                        <td>
                            <a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($project['ID']) ?>" class="btn btn-info btn-sm">View</a>
                            <!--
                            <a href="index.php?page=projects&action=edit&id=<?= htmlspecialchars($project['ID']) ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="index.php?page=projects&action=delete&id=<?= htmlspecialchars($project['ID']) ?>" class="btn btn-danger btn-sm">Delete</a>
                    -->
                        </td>
                    </tr>
                <?php
                    endforeach;
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No projects available</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
