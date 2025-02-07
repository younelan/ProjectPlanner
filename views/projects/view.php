<?php 
$pageTitle = htmlspecialchars($project['PNAME']) . ' | ' . $appName;
include 'views/templates/header.php'; 
?>

<style>
    .page-header {
        background: linear-gradient(135deg, rgb(224 228 202) 0%, rgb(236, 215, 190) 100%);
        padding: 1rem 1.5rem;
        margin: 0;
        margin-bottom: 3px;
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

    .breadcrumb {
        margin: 0;
        padding: 0;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .breadcrumb-item {
        font-size: 1.25rem;
        font-weight: 600;
        color: darkred;
    }

    .breadcrumb-item a {
        color: darkred;
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: darkred;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: darkred;
        content: "›";
        font-size: 1.4rem;
        line-height: 1;
        padding: 0 0.5rem;
    }
</style>

<div class="page-header">
    <div class="d-flex align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="fas fa-project-diagram"></i>
                    <a href="index.php">Projects</a>
                </li>
                <li class="breadcrumb-item active">
                    <?= htmlspecialchars($project['PNAME']) ?>
                    <small class="text-muted"> &nbsp;(<?= htmlspecialchars($project['PKEY']) ?>)</small>
                </li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <a href="index.php?page=projects&action=board&id=<?= $project['ID'] ?>" class="btn btn-outline-primary">
            <i class="fas fa-columns"></i> Board View
        </a>
        <a href="index.php?page=sprints&action=list&projectId=<?= $project['ID'] ?>" class="btn btn-outline-info">
            <i class="fas fa-running"></i> Sprints
        </a>
        <a href="index.php?page=issues&action=new&projectId=<?= $project['ID'] ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Issue
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="issueFilter" class="form-control" placeholder="Type to filter issues...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
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
                
                <!-- Bulk Actions -->
                <div class="bulk-actions mt-3" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="selected-count">0 items selected</span>
                        </div>
                        <div class="col-auto">
                            <select class="form-select" id="bulkAction">
                                <option value="">Bulk Actions...</option>
                                <option value="assign">Assign To...</option>
                                <option value="status">Change Status...</option>
                                <option value="type">Change Type...</option>
                                <option value="move">Move to Project...</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="col-auto secondary-select" style="display: none;">
                            <select class="form-select" id="bulkActionValue">
                                <!-- Will be populated based on selected action -->
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="applyBulkAction">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Issue Type Filter Section -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Filter by Issue Type</h5>
    </div>
    <div class="card-body">
        <div id="issueTypeFilters" class="d-flex flex-wrap gap-3">
            <!-- Issue type checkboxes will be dynamically added here -->
        </div>
    </div>
</div>

<!-- Add Status Filter Section -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Filter by Status</h5>
    </div>
    <div class="card-body">
        <div id="issueStatusFilters" class="d-flex flex-wrap gap-3">
            <!-- Status checkboxes will be dynamically added here -->
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

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Table adjustments */
    .table-responsive {
        border: 0;
        margin-bottom: 0;
    }
    
    .table {
        display: block;
    }
    
    .table thead {
        display: none; /* Hide headers on mobile */
    }
    
    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .table tbody td {
        display: block;
        text-align: left;
        padding: 0.5rem;
        border: none;
    }
    
    /* Add labels before content */
    .table tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 100px;
    }

    /* Button group adjustments */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .btn-group .btn {
        flex: 1;
        white-space: nowrap;
    }

    /* Action buttons in table */
    td:last-child {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-start;
        padding-top: 1rem !important;
    }

    /* Filter input */
    #issueFilter {
        margin-bottom: 1rem;
    }
}

/* Add styles for type filters */
.gap-3 {
    gap: 1rem !important;
}

.form-check {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
    border-radius: 4px;
    margin: 0;
}

.form-check-input:checked {
    background-color: #0052cc;
    border-color: #0052cc;
}
</style>

<script>
// Define global variables and functions
window.issues = <?= json_encode($issues) ?>;
let selectedIssues = new Set(); // Initialize selectedIssues at the top level

window.filterIssues = null; // Will be assigned in DOMContentLoaded
window.handleDelete = function(issueId, issueKey) {
    if (confirm(`Are you sure you want to delete issue ${issueKey}? This action cannot be undone.`)) {
        fetch('index.php?page=issues&action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ids: [issueId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Simply remove the table row
                document.querySelector(`tr[data-issue-id="${issueId}"]`).remove();
            } else {
                throw new Error(data.error || 'Failed to delete issue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete issue: ' + error.message);
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const project = <?= json_encode($project) ?>;
    // Move issues to window scope so it's accessible to handleDelete
    window.issues = <?= json_encode($issues) ?>;
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
            <tr data-issue-id="${issue.ID}">
                <td style="width: 40px">
                    <input type="checkbox" 
                           class="form-check-input issue-checkbox" 
                           value="${issue.ID}" 
                           ${selectedIssues.has(parseInt(issue.ID)) ? 'checked' : ''}>
                </td>
                <td data-label="ID" data-value="${issue.ID}">
                    <span class="badge badge-secondary">
                        ${project.PKEY}-${issue.ID}
                    </span>
                </td>
                <td data-label="Summary" data-value="${issue.SUMMARY}">
                    <a href="index.php?page=issues&action=view&id=${issue.ID}" class="text-dark">
                        ${issue.SUMMARY}
                    </a>
                </td>
                <td data-label="Type" data-value="${issue.TYPE}">
                    <span class="badge badge-info">${issue.TYPE}</span>
                </td>
                <td data-label="Status" data-value="${issue.STATUS}" data-status-order="${issue.STATUS_ORDER || 0}">
                    <span class="badge badge-${getStatusBadgeClass(issue.STATUS)}">
                        ${issue.STATUS}
                    </span>
                </td>
                <td data-label="Assignee" data-value="${issue.ASSIGNEE || ''}">
                    ${issue.ASSIGNEE ? `<i class="fas fa-user"></i> ${issue.ASSIGNEE}` : 
                    '<span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>'}
                </td>
                <td data-label="Priority" data-value="${issue.PRIORITY || ''}">
                    ${getPriorityIcon(issue.PRIORITY)}
                    ${issue.PRIORITY}
                </td>
                <td data-label="Actions">
                    <div class="btn-group">
                        <a href="index.php?page=issues&action=view&id=${issue.ID}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i><span class="d-none d-md-inline"> View</span>
                        </a>
                        <a href="index.php?page=issues&action=edit&id=${issue.ID}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i><span class="d-none d-md-inline"> Edit</span>
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="handleDelete(${issue.ID}, '${project.PKEY}-${issue.ID}')">
                            <i class="fas fa-trash"></i><span class="d-none d-md-inline"> Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        updateBulkActionVisibility();
    }

    function updateBulkActionVisibility() {
        const bulkActionsDiv = document.querySelector('.bulk-actions');
        const selectedCount = document.querySelector('.selected-count');
        if (selectedIssues.size > 0) {
            bulkActionsDiv.style.display = 'block';
            selectedCount.textContent = `${selectedIssues.size} items selected`;
        } else {
            bulkActionsDiv.style.display = 'none';
        }
    }

    function handleBulkActionChange() {
        const bulkAction = document.getElementById('bulkAction');
        const secondarySelect = document.querySelector('.secondary-select');
        const bulkActionValue = document.getElementById('bulkActionValue');
        
        bulkActionValue.innerHTML = ''; // Clear existing options
        
        switch (bulkAction.value) {
            case 'assign':
                secondarySelect.style.display = 'block';
                // Add user options
                const users = <?= json_encode($users ?? []) ?>;
                bulkActionValue.add(new Option('-- Select User --', ''));
                users.forEach(user => {
                    // Use correct property names from User model
                    bulkActionValue.add(new Option(user.DISPLAY_NAME || user.USERNAME, user.USERNAME));
                });
                break;
                
            case 'status':
                secondarySelect.style.display = 'block';
                // Add status options
                const statuses = <?= json_encode($statuses ?? []) ?>;
                bulkActionValue.innerHTML = '<option value="">-- Select Status --</option>';
                // Use Object.values() to handle object iteration
                Object.values(statuses).forEach(status => {
                    bulkActionValue.innerHTML += `<option value="${status.ID}">${status.PNAME}</option>`;
                });
                break;

            case 'move':
                secondarySelect.style.display = 'block';
                // Add project options from allProjects
                const projects = <?= json_encode($allProjects ?? []) ?>;
                bulkActionValue.add(new Option('-- Select Project --', ''));
                if (projects) {
                    projects.forEach(proj => {
                        if (proj.ID !== <?= $project['ID'] ?>) { // Exclude current project
                            bulkActionValue.add(new Option(proj.PNAME, proj.ID));
                        }
                    });
                }
                break;
                
            case 'type':
                secondarySelect.style.display = 'block';
                // Add issue type options
                const issueTypes = <?= json_encode($issueTypes ?? []) ?>;
                bulkActionValue.innerHTML = '<option value="">-- Select Type --</option>';
                issueTypes.forEach(type => {
                    bulkActionValue.innerHTML += `<option value="${type.ID}">${type.NAME}</option>`;
                });
                break;
                
            case 'delete':
                secondarySelect.style.display = 'none';
                break;
                
            default:
                secondarySelect.style.display = 'none';
        }
    }

    function applyBulkAction() {
        const action = document.getElementById('bulkAction').value;
        const value = document.getElementById('bulkActionValue').value;
        const issueIds = Array.from(selectedIssues);
        
        if (!action || !issueIds.length) return;
        if (!value && action !== 'delete') {
            alert('Please select a value');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${issueIds.length} issues? This cannot be undone.`)) {
                return;
            }
        }
        
        const endpoint = 'index.php?page=issues&action=' + action;
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ids: issueIds,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'delete' || action === 'move') {
                    // Remove affected issues from the list
                    window.issues = window.issues.filter(issue => !selectedIssues.has(issue.ID));
                } else if (action === 'assign') {
                    // Update assignee in the list
                    window.issues = window.issues.map(issue => {
                        if (selectedIssues.has(issue.ID)) {
                            return { ...issue, ASSIGNEE: value };
                        }
                        return issue;
                    });
                } else if (action === 'type') {
                    const typeName = data.typeName; // Use the type name from the server response
                    
                    // Add new type to filters if it doesn't exist
                    const newTypeOption = document.getElementById('type-' + typeName);
                    if (!newTypeOption) {
                        const typeFilterContainer = document.getElementById('issueTypeFilters');
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        div.innerHTML = `
                            <input class="form-check-input issue-type-filter" type="checkbox" 
                                   id="type-${typeName}" value="${typeName}" checked>
                            <label class="form-check-label" for="type-${typeName}">
                                ${typeName}
                            </label>
                        `;
                        typeFilterContainer.appendChild(div);
                        
                        // Add event listener to new checkbox
                        const newCheckbox = div.querySelector('input');
                        newCheckbox.addEventListener('change', function() {
                            if (this.checked) {
                                selectedTypes.add(this.value);
                            } else {
                                selectedTypes.delete(this.value);
                            }
                            localStorage.setItem(storageKey, JSON.stringify([...selectedTypes]));
                            filterIssues();
                        });
                    }
                    
                    // Always add the new type to selectedTypes
                    selectedTypes.add(typeName);
                    localStorage.setItem(storageKey, JSON.stringify([...selectedTypes]));
                    
                    // Update all matching issues in the array
                    window.issues = window.issues.map(issue => {
                        if (selectedIssues.has(issue.ID)) {
                            return { ...issue, TYPE: typeName };
                        }
                        return issue;
                    });
                } else if (action === 'status') {
                    window.issues = window.issues.map(issue => {
                        if (selectedIssues.has(issue.ID)) {
                            return { 
                                ...issue, 
                                STATUS: data.statusName, // Use statusName from response
                                STATUS_ID: value 
                            };
                        }
                        return issue;
                    });

                    // Add new status to filters if it doesn't exist
                    const newStatusOption = document.getElementById('status-' + data.statusName);
                    if (!newStatusOption) {
                        const statusFilterContainer = document.getElementById('issueStatusFilters');
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        div.innerHTML = `
                            <input class="form-check-input issue-status-filter" type="checkbox" 
                                   id="status-${data.statusName}" value="${data.statusName}" checked>
                            <label class="form-check-label" for="status-${data.statusName}">
                                <span class="badge badge-${getStatusBadgeClass(data.statusName)}">${data.statusName}</span>
                            </label>
                        `;
                        statusFilterContainer.appendChild(div);
                        
                        // Add event listener to new checkbox
                        const newCheckbox = div.querySelector('input');
                        newCheckbox.addEventListener('change', function() {
                            if (this.checked) {
                                selectedStatuses.add(this.value);
                            } else {
                                selectedStatuses.delete(this.value);
                            }
                            localStorage.setItem(statusStorageKey, JSON.stringify([...selectedStatuses]));
                            filterIssues();
                        });
                    }
                    
                    // Always add the new status to selectedStatuses
                    selectedStatuses.add(data.statusName);
                    localStorage.setItem(statusStorageKey, JSON.stringify([...selectedStatuses]));

                    // Check the new status filter checkbox if it exists
                    const statusCheckbox = document.getElementById('status-' + data.statusName);
                    if (statusCheckbox) {
                        statusCheckbox.checked = true;
                    }
                }
                
                // Clear selection and refresh display
                selectedIssues.clear();
                document.getElementById('selectAll').checked = false;
                filterIssues();
            } else {
                throw new Error(data.error || 'Operation failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to perform bulk action: ' + error.message);
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
    function handleDelete(issueId, issueKey) {
        if (confirm(`Are you sure you want to delete issue ${issueKey}?`)) {
            fetch(`index.php?page=issues&action=delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids: [issueId]  // Send as array to support bulk delete
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the deleted issue from our issues array
                    issues = issues.filter(issue => issue.ID !== issueId);
                    // Re-render the filtered issues
                    filterIssues();
                } else {
                    alert(data.error || 'Failed to delete issue');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete issue');
            });
        }
    }

    // Track selected issue types
    // Use project-specific storage key
    const storageKey = `projectViewSelectedTypes-${project.ID}`;
    
    // Initialize selectedTypes from localStorage or create new Set
    let selectedTypes = new Set(
        JSON.parse(localStorage.getItem(storageKey)) || 
        issues.map(issue => issue.TYPE)
    );

    // Get unique issue types from issues array
    const issueTypes = [...new Set(issues.map(issue => issue.TYPE))];
    
    // Create and populate issue type filters
    const filterContainer = document.getElementById('issueTypeFilters');
    issueTypes.forEach(type => {
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input issue-type-filter" type="checkbox" 
                   id="type-${type}" value="${type}" 
                   ${selectedTypes.has(type) ? 'checked' : ''}>
            <label class="form-check-label" for="type-${type}">
                ${type}
            </label>
        `;
        filterContainer.appendChild(div);
    });

    // Add event listeners for type filters
    document.querySelectorAll('.issue-type-filter').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedTypes.add(this.value);
            } else {
                selectedTypes.delete(this.value);
            }
            // Save to localStorage
            localStorage.setItem(storageKey, JSON.stringify([...selectedTypes]));
            filterIssues();
        });
    });

    // Add status filtering
    const statusStorageKey = `projectViewSelectedStatuses-${project.ID}`;
    let selectedStatuses = new Set(
        JSON.parse(localStorage.getItem(statusStorageKey)) || 
        issues.map(issue => issue.STATUS)
    );

    // Get unique statuses from issues array
    const issueStatuses = [...new Set(issues.map(issue => issue.STATUS))];
    
    // Create and populate status filters
    const statusFilterContainer = document.getElementById('issueStatusFilters');
    issueStatuses.forEach(status => {
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input issue-status-filter" type="checkbox" 
                   id="status-${status}" value="${status}" 
                   ${selectedStatuses.has(status) ? 'checked' : ''}>
            <label class="form-check-label" for="status-${status}">
                <span class="badge badge-${getStatusBadgeClass(status)}">${status}</span>
            </label>
        `;
        statusFilterContainer.appendChild(div);
    });

    // Add event listeners for status filters
    document.querySelectorAll('.issue-status-filter').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedStatuses.add(this.value);
            } else {
                selectedStatuses.delete(this.value);
            }
            // Save to localStorage
            localStorage.setItem(statusStorageKey, JSON.stringify([...selectedStatuses]));
            filterIssues();
        });
    });

    // Update filterIssues function to include both type and status filtering
    function filterIssues() {
        const searchTerm = filterInput.value.toLowerCase();
        const filtered = issues.filter(issue => 
            selectedTypes.has(issue.TYPE) && 
            selectedStatuses.has(issue.STATUS) &&
            (
                issue.SUMMARY.toLowerCase().includes(searchTerm) ||
                issue.TYPE.toLowerCase().includes(searchTerm) ||
                (issue.ASSIGNEE && issue.ASSIGNEE.toLowerCase().includes(searchTerm)) ||
                issue.STATUS.toLowerCase().includes(searchTerm) ||
                (issue.PRIORITY && issue.PRIORITY.toLowerCase().includes(searchTerm))
            )
        );
        
        if (currentSort.column) {
            sortIssues(filtered, currentSort.column, currentSort.direction);
        }
        renderIssues(filtered);
    }

    // Initial render with filtered issues
    filterIssues(); // Add this line to apply filters on load

    // Add new event listeners
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.issue-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedIssues.add(parseInt(checkbox.value));
            } else {
                selectedIssues.delete(parseInt(checkbox.value));
            }
        });
        updateBulkActionVisibility();
    });
    
    document.getElementById('issueTableBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('issue-checkbox')) {
            const issueId = parseInt(e.target.value);
            if (e.target.checked) {
                selectedIssues.add(issueId);
            } else {
                selectedIssues.delete(issueId);
                // Uncheck "Select All" if any individual checkbox is unchecked
                document.getElementById('selectAll').checked = false;
            }
            updateBulkActionVisibility();
        }
    });
    
    document.getElementById('bulkAction').addEventListener('change', handleBulkActionChange);
    document.getElementById('applyBulkAction').addEventListener('click', applyBulkAction);
    
    // Trigger bulk action change if there's a default value
    const bulkAction = document.getElementById('bulkAction');
    if (bulkAction.value) {
        handleBulkActionChange();
    }
});
</script>

<?php include 'views/templates/footer.php'; ?>
