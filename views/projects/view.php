<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Issues</title>
    <!-- Include Bootstrap CSS (from CDN) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container my-4">
        <h2>Issues for Project: <?php echo htmlspecialchars($project['PNAME']); ?></h2>
        
        <!-- Link back to the project list page -->
        <a href="index.php" class="btn btn-primary mb-4">Back to Project List</a>

        <!-- Table of Issues -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Summary</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                <tr>
                    <td><?php echo htmlspecialchars($issue['ID']); ?></td>
                    <td><?php echo htmlspecialchars($issue['SUMMARY']); ?></td>
                    <td><?php echo htmlspecialchars($issue['TYPE']); ?></td>
                    <td>
                        <span class="badge" style="background-color: #<?php echo htmlspecialchars($issue['STATUS_ICON']); ?>">
                            <?php echo htmlspecialchars($issue['STATUS_NAME']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($issue['PRIORITY']); ?></td>
                    <td>
                        <!-- Link to issue details page -->
                        <a href="index.php?page=issues&action=view&id=<?php echo htmlspecialchars($issue['ID']); ?>" class="btn btn-info btn-sm">View Details</a>
                    </td>
                </tr>

                <!-- Display Issue Links -->
                <?php if (!empty($issuesWithLinks[$issue['ID']])): ?>
                    <tr>
                        <td colspan="6">
                            <strong>Linked Issues:</strong>
                            
                            <?php 
$linksDisplay = [];
foreach ($issuesWithLinks[$issue['ID']] as $link): 
    if ($link['SOURCE'] == $issue['ID']) {
        // Outward link
        //htmlspecialchars($link['OUTWARD']) . ' ' . 
        $linksDisplay[] = 
        '<a href="index.php?page=issues&action=view&id=' . htmlspecialchars($link['DESTINATION']) . '">' . 
        htmlspecialchars($link['DESTINATION']) . '</a>';
    } else {
        // Inward link
        //htmlspecialchars($link['INWARD']) . ' ' . 
        $linksDisplay[] = 
        '<a href="index.php?page=issues&action=view&id=' . htmlspecialchars($link['SOURCE']) . '">' . 
        htmlspecialchars($link['SOURCE']) . '</a>';
    }
endforeach;

// Join the array elements with commas
echo implode(', ', $linksDisplay);
?>

                        </td>
                    </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
