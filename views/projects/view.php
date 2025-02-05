<?php 
$pageTitle = htmlspecialchars($project['PNAME']) . ' | Scrum Viewer';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($project['PNAME']) ?></li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-tasks"></i> 
                    <?= htmlspecialchars($project['PNAME']) ?> 
                    <small class="text-muted">(<?= htmlspecialchars($project['PKEY']) ?>)</small>
                </h2>
                <div class="btn-group">
                    <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-columns"></i> Board View
                    </a>
                    <a href="index.php?page=issues&action=create&projectId=<?= $project['ID'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Issue
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="issueFilter" class="form-control" placeholder="Type to filter issues...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="summary">Summary <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="type">Type <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="status">Status <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="assignee">Assignee <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="priority">Priority <i class="fas fa-sort"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="issueTableBody">
                            <!-- Issues will be dynamically populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Helper functions
function getStatusBadgeClass($status) {
    $map = [
        'Open' => 'secondary',
        'In Progress' => 'primary',
        'Resolved' => 'info',
        'Closed' => 'success',
        'Reopened' => 'warning'
    ];
    return $map[$status] ?? 'secondary';
}

function getPriorityIcon($priority) {
    $icons = [
        'Highest' => '<i class="fas fa-arrow-up text-danger"></i>',
        'High' => '<i class="fas fa-arrow-up text-warning"></i>',
        'Medium' => '<i class="fas fa-minus text-info"></i>',
        'Low' => '<i class="fas fa-arrow-down text-success"></i>',
        'Lowest' => '<i class="fas fa-arrow-down text-muted"></i>'
    ];
    return $icons[$priority] ?? '';
}
?>

<style>
/* Table and card styling */
.table-responsive {
    margin-top: 1rem;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    border: none;
    margin-bottom: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e3e6f0;
    padding: 1rem;
}

/* Sorting styles */
.sortable {
    cursor: pointer;
    position: relative;
    padding-right: 1.5rem;
}

.sortable:hover {
    background-color: #f1f4f9;
}

.sort-asc .fa-sort:before {
    content: "\f0de";
    color: #0052cc;
}

.sort-desc .fa-sort:before {
    content: "\f0dd";
    color: #0052cc;
}

/* Filter and search styling */
#issueFilter {
    border-radius: 4px;
    border: 1px solid #dfe1e6;
    padding: 8px 12px;
}

#issueFilter:focus {
    border-color: #4c9aff;
    box-shadow: 0 0 0 2px rgba(76, 154, 255, 0.2);
}

/* Badge styling */
.badge {
    padding: 5px 8px;
    font-weight: 500;
    font-size: 12px;
}

.badge-info {
    background-color: #0052cc;
}

/* Hidden elements */
.issue-row.hidden {
    display: none;
}

/* Status colors */
.badge-secondary { background-color: #6c757d; }
.badge-primary { background-color: #0052cc; }
.badge-success { background-color: #36B37E; }
.badge-warning { background-color: #FFAB00; }
.badge-info { background-color: #00B8D9; }

/* Priority icons */
.priority-icon {
    margin-right: 5px;
}

/* Link styling */
a.text-dark {
    text-decoration: none;
}

a.text-dark:hover {
    color: #0052cc !important;
    text-decoration: underline;
}

/* Button styling */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const project = <?= json_encode($project) ?>;
    const issues = <?= json_encode($issues) ?>;
    const filterInput = document.getElementById('issueFilter');
    const tbody = document.getElementById('issueTableBody');
    let currentSort = { column: '', direction: 'asc' };

    // Add helper functions for JavaScript rendering
    function getStatusBadgeClass(status) {
        const map = {
            'Open': 'secondary',
            'In Progress': 'primary',
            'Resolved': 'info',
            'Closed': 'success',
            'Reopened': 'warning'
        };
        return map[status] || 'secondary';
    }

    function getPriorityIcon(priority) {
        const icons = {
            'Highest': '<i class="fas fa-arrow-up text-danger"></i>',
            'High': '<i class="fas fa-arrow-up text-warning"></i>',
            'Medium': '<i class="fas fa-minus text-info"></i>',
            'Low': '<i class="fas fa-arrow-down text-success"></i>',
            'Lowest': '<i class="fas fa-arrow-down text-muted"></i>'
        };
        return icons[priority] || '';
    }

    function renderIssues(filteredIssues) {
        tbody.innerHTML = filteredIssues.map(issue => `
            <tr>
                <td data-value="${issue.ID}">
                    <span class="badge badge-secondary">
                        ${project.PKEY}-${issue.ID}
                    </span>
                </td>
                <td data-value="${issue.SUMMARY}">
                    <a href="index.php?page=issues&action=view&id=${issue.ID}" class="text-dark">
                        ${issue.SUMMARY}
                    </a>
                </td>
                <td data-value="${issue.TYPE}">
                    <span class="badge badge-info">${issue.TYPE}</span>
                </td>
                <td data-value="${issue.STATUS}" data-status-order="${issue.STATUS_ORDER || 0}">
                    <span class="badge badge-${getStatusBadgeClass(issue.STATUS)}">
                        ${issue.STATUS}
                    </span>
                </td>
                <td data-value="${issue.ASSIGNEE || ''}">
                    ${issue.ASSIGNEE ? `<i class="fas fa-user"></i> ${issue.ASSIGNEE}` : 
                    '<span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>'}
                </td>
                <td data-value="${issue.PRIORITY || ''}">
                    ${getPriorityIcon(issue.PRIORITY)}
                    ${issue.PRIORITY}
                </td>
                <td>
                    <a href="index.php?page=issues&action=view&id=${issue.ID}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="index.php?page=issues&action=edit&id=${issue.ID}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger delete-issue" 
                            data-issue-id="${issue.ID}"
                            data-issue-key="${project.PKEY}-${issue.ID}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        // Reattach delete handlers after rendering
        //attachDeleteHandlers();
    }

    // function attachDeleteHandlers() {
    //     document.querySelectorAll('.delete-issue').forEach(button => {
    //         button.addEventListener('click', function(e) {
    //             e.preventDefault();
    //             const issueId = this.dataset.issueId;
    //             // if (confirm('Are you sure you want to delete this issue?')) {
    //             //     fetch(`index.php?page=issues&action=delete&id=${issueId}`, {
    //             //         method: 'POST'
    //             //     })
    //             //     .then(response => response.json())
    //             //     .then(data => {
    //             //         if (data.success) {
    //             //             this.closest('tr').remove();
    //             //         } else {
    //             //             alert(data.error || 'Failed to delete issue');
    //             //         }
    //             //     })
    //             //     .catch(error => {
    //             //         console.error('Error:', error);
    //             //         alert('Failed to delete issue');
    //             //     });
    //             // }
    //         });
    //     });
    //}

    function filterIssues() {
        const searchTerm = filterInput.value.toLowerCase();
        const filtered = issues.filter(issue => 
            issue.SUMMARY.toLowerCase().includes(searchTerm) ||
            issue.TYPE.toLowerCase().includes(searchTerm) ||
            (issue.ASSIGNEE && issue.ASSIGNEE.toLowerCase().includes(searchTerm)) ||
            issue.STATUS.toLowerCase().includes(searchTerm) ||
            (issue.PRIORITY && issue.PRIORITY.toLowerCase().includes(searchTerm))
        );
        
        if (currentSort.column) {
            sortIssues(filtered, currentSort.column, currentSort.direction);
        }
        renderIssues(filtered);
    }

    function sortIssues(issueList, column, direction) {
        issueList.sort((a, b) => {
            let aVal = String(a[column] || '');
            let bVal = String(b[column] || '');
            
            // Special handling for status and priority
            if (column === 'STATUS') {
                aVal = parseInt(a['STATUS_ORDER'] || '0');
                bVal = parseInt(b['STATUS_ORDER'] || '0');
                return direction === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Numeric comparison for ID
            if (column === 'ID') {
                return direction === 'asc' 
                    ? parseInt(aVal) - parseInt(bVal)
                    : parseInt(bVal) - parseInt(aVal);
            }

            // Default string comparison
            return direction === 'asc' 
                ? aVal.toLowerCase().localeCompare(bVal.toLowerCase())
                : bVal.toLowerCase().localeCompare(aVal.toLowerCase());
        });
    }

    // Set up event listeners
    filterInput.addEventListener('input', filterIssues);

    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort.toUpperCase();
            const direction = currentSort.column === column && currentSort.direction === 'asc' ? 'desc' : 'asc';
            
            // Reset all headers
            document.querySelectorAll('.sortable').forEach(h => 
                h.classList.remove('sort-asc', 'sort-desc')
            );
            
            // Update current header
            this.classList.add(`sort-${direction}`);
            
            currentSort = { column, direction };
            filterIssues(); // This will re-render with current filter and new sort
        });
    });

    // Initial render
    renderIssues(issues);

    // Add delete functionality to the buttons
    document.querySelectorAll('.delete-issue').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const issueId = this.dataset.issueId;
            const issueKey = this.dataset.issueKey;
            if (confirm(`Are you sure you want to delete issue ${issueKey}?`)) {
                fetch(`index.php?page=issues&action=delete&id=${issueId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                    } else {
                        alert(data.error || 'Failed to delete issue');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete issue');
                });
            }
        });
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>
