<?php 
$pageTitle = "Board View";
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
                <li class="breadcrumb-item">
                    <a href="index.php?page=projects&action=view&id=<?= $project['ID'] ?>">
                        <?= htmlspecialchars($project['PNAME']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active">Board View</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <button type="button" id="viewToggle" class="btn btn-outline-primary simple-dropdown-toggle" onclick="toggleSimpleDropdown(this)">
            <i class="fas fa-eye"></i> View As
        </button>
        <div class="dropdown-menu simple-dropdown-menu" style="display: none;">
            <a class="dropdown-item" href="#" onclick="selectView('flow'); return false;">Flow</a>
            <a class="dropdown-item" href="#" onclick="selectView('tabbed'); return false;">Tabbed View</a>
            <a class="dropdown-item" href="index.php?page=projects&action=view&id=<?= htmlspecialchars($_GET['id'] ?? '') ?>" onclick="hideDropdown(this);">List of Issues</a>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTaskModal">
            <i class="fas fa-plus"></i> Add Task
        </button>
    </div>
</div>

<!-- Add dependencies in the correct order -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>

<div id="boardContainer">
    <div id="board" class="board-container"></div>
</div>

<!-- New Modal: Add Task -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="addTaskForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="hideModal()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <!-- New field: Issue Title -->
            <div class="form-group">
                <label for="newTaskTitle">Title</label>
                <input type="text" id="newTaskTitle" class="form-control" name="SUMMARY" required>
            </div>
            <!-- New field: Issue Type as dropdown -->
            <div class="form-group">
                <label for="newTaskType">Issue Type</label>
                <select id="newTaskType" class="form-control" name="ISSUETYPE">
                    <option value="Bug">Bug</option>
                    <option value="Task" selected>Task</option>
                    <option value="Story">Story</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <!-- Updated field: Description, NOT required -->
            <div class="form-group">
                <label for="newTaskDescription">Description (optional)</label>
                <textarea id="newTaskDescription" class="form-control" name="DESCRIPTION"></textarea>
            </div>
            <!-- New field: Status -->
            <div class="form-group">
                <label for="newTaskStatus">Status</label>
                <select id="newTaskStatus" class="form-control" name="STATUS_ID">
                    <?php foreach ($workflow as $status): ?>
                        <option value="<?= htmlspecialchars($status['ID']) ?>">
                            <?= htmlspecialchars($status['PNAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- New field: Assignee -->
            <div class="form-group">
                <label for="newTaskAssignee">Assign To</label>
                <select id="newTaskAssignee" class="form-control" name="ASSIGNEE">
                    <option value="">Unassigned</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= htmlspecialchars($user['USER_KEY']) ?>">
                            <?= htmlspecialchars($user['display_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Existing field: Priority -->
            <div class="form-group">
                <label for="newTaskPriority">Priority</label>
                <select id="newTaskPriority" class="form-control" name="PRIORITY">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <!-- Hidden: Project Id -->
            <input type="hidden" name="projectId" id="projectId" value="<?= $project['ID'] ?>">
            <!-- Hidden: Status Id -->
            <input type="hidden" name="STATUS_ID" id="defaultStatusId" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="hideModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Task</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Filter by Issue Type</h5>
    </div>
    <div class="card-body">
        <div id="boardIssueTypeFilters" class="d-flex flex-wrap gap-3">
            <!-- Issue type checkboxes will be dynamically added here -->
        </div>
    </div>
</div>

<script>
// ...existing code...
const boardView = {
    // Use saved view preference, defaulting to 'tabbed'
    currentView: localStorage.getItem('boardView') || 'tabbed',
    data: null,
    async init() {
        await this.loadData();
        this.setupEventListeners();
        this.initializeDropdowns();
    },

    async loadData() {
        const response = await fetch('index.php?page=projects&action=board&id=<?= $_GET['id'] ?>&api=1');
        this.data = await response.json();
        console.log(this.data);
        await this.setupTypeFilters();
        this.render(); // Move render here, after filters are set up
    },

    setupTypeFilters() {
        // Get unique issue types
        const issueTypes = [...new Set(this.data.issues.map(issue => issue.TYPE))];
        
        // Use project-specific storage key
        const storageKey = `boardViewSelectedTypes-${this.data.project.ID}`;
        
        // Initialize selected types from localStorage or all types
        this.selectedTypes = new Set(
            JSON.parse(localStorage.getItem(storageKey)) || 
            issueTypes
        );

        // Create filters
        const filterContainer = document.getElementById('boardIssueTypeFilters');
        filterContainer.innerHTML = ''; // Clear existing filters
        
        issueTypes.forEach(type => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input board-type-filter" type="checkbox" 
                       id="board-type-${type}" value="${type}" 
                       ${this.selectedTypes.has(type) ? 'checked' : ''}>
                <label class="form-check-label" for="board-type-${type}">
                    ${type}
                </label>
            `;
            filterContainer.appendChild(div);
        });

        // Add event listeners
        document.querySelectorAll('.board-type-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    this.selectedTypes.add(checkbox.value);
                } else {
                    this.selectedTypes.delete(checkbox.value);
                }
                // Save to localStorage
                localStorage.setItem(storageKey, JSON.stringify([...this.selectedTypes]));
                this.render();
            });
        });
    },

    setupEventListeners() {
        // View switcher
        document.querySelectorAll('[data-view]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentView = e.target.dataset.view;
                this.render();
            });
        });
    },

    render() {
        const board = document.getElementById('board');
        board.innerHTML = ''; // Clear board content before rendering
        
        if (this.currentView === 'tabbed') {
            board.classList.remove('d-flex'); // Remove flex class for tabbed view
            board.style.display = 'block'; // Override flex display for tabbed view
            let workflow = Object.values(this.data.workflow || {});
            // Sort workflow by SEQUENCE (converted to number)
            workflow.sort((a, b) => Number(a.SEQUENCE) - Number(b.SEQUENCE));
            // Find the first tab that has tasks; if none, default to index 0
            let firstActiveIndex = 0;
            for (let i = 0; i < workflow.length; i++) {
                if (this.getIssuesForStatus(workflow[i].ID).length > 0) {
                    firstActiveIndex = i;
                    break;
                }
            }
            const tabsHtml = workflow.map((state, i) => `
                <li class="nav-item">
                    <a class="nav-link ${i === firstActiveIndex ? 'active' : ''}" href="#" onclick="switchTab(event, '${state.ID}')">${state.PNAME}</a>
                </li>
            `).join('');
            const contentHtml = workflow.map((state, i) => `
                <div class="tab-pane fade ${i === firstActiveIndex ? 'show active' : ''}" id="tab-${state.ID}">
                    ${this.getIssuesForStatus(state.ID).map(issue => this.createIssueCard(issue)).join('')}
                </div>
            `).join('');
            board.innerHTML = `
                <div class="tab-navigation" style="display:block;">
                    <ul class="nav nav-tabs">
                        ${tabsHtml}
                    </ul>
                </div>
                <div class="tab-content-container" style="display:block; margin-top:20px;">
                    <div class="tab-content">
                        ${contentHtml}
                    </div>
                </div>
            `;
        } else if (this.currentView === 'flow') {
            board.classList.add('d-flex'); // Ensure flex layout for swimlanes view
            board.style.display = 'flex'; // Use flex layout for swimlanes view
            this.renderSwimlanes(board);
        }
    },

    renderSwimlanes(container) {
        // Convert workflow object to array and sort by SEQUENCE
        const sortedWorkflow = Object.values(this.data.workflow || {}).sort((a, b) => Number(a.SEQUENCE) - Number(b.SEQUENCE));
        
        // Find issues with states not in current workflow
        const workflowStateIds = new Set(sortedWorkflow.map(s => String(s.ID)));
        const orphanedIssues = this.data.issues.filter(issue => 
            !workflowStateIds.has(String(issue.STATUS_ID || issue.ISSUESTATUS)) &&
            this.selectedTypes.has(issue.TYPE)
        );
        
        container.className = 'board-container d-flex';
        
        // Render regular workflow columns
        sortedWorkflow.forEach((status) => {
            const column = this.createColumn(status, this.getIssuesForStatus(status.ID));
            container.appendChild(column);
        });
        
        // If there are orphaned issues, create a special column for them
        if (orphanedIssues.length > 0) {
            const orphanedColumn = this.createOrphanedColumn(orphanedIssues);
            container.appendChild(orphanedColumn);
        }
        
        this.setupDragAndDrop();
    },

    createOrphanedColumn(issues) {
        const column = document.createElement('div');
        column.className = 'board-column orphaned-column';
        column.innerHTML = `
            <div class="board-column-header bg-warning">
                <h5>Other States
                    <span class="badge badge-pill badge-secondary">${issues.length}</span>
                </h5>
                <small class="text-muted">Issues in states not in current workflow</small>
            </div>
            <div class="issue-list" data-status-id="orphaned">
                ${issues.map(issue => this.createIssueCard(issue, true)).join('')}
            </div>
        `;
        return column;
    },

    createIssueCard(issue, isOrphaned = false) {
        return `
            <div class="card issue-card ${isOrphaned ? 'orphaned-issue' : ''}" data-issue-id="${issue.ID}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="badge badge-info">${issue.TYPE}</span>
                        ${isOrphaned ? `<span class="badge badge-warning">State: ${issue.STATUS || 'Unknown'}</span>` : ''}
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link simple-dropdown-toggle" type="button" onclick="toggleSimpleDropdown(this)">
                                <i class="fas fa-cog"></i>
                            </button>
                            <div class="dropdown-menu simple-dropdown-menu" style="display: none;">
                                <a class="dropdown-item" href="index.php?page=issues&action=view&id=${issue.ID}">View Issue</a>
                                <div class="dropdown-submenu">
                                    <button type="button" class="dropdown-item simple-dropdown-toggle" onclick="toggleSubMenu(this)">Set State</button>
                                    <div class="dropdown-menu simple-dropdown-menu" style="display: none;">
                                        ${Object.values(this.data.workflow || {}).map(state =>
                                            renderStateDropdownItem(issue, state)
                                        ).join('')}
                                    </div>
                                </div>
                                <div class="dropdown-submenu">
                                    <button type="button" class="dropdown-item simple-dropdown-toggle" onclick="toggleSubMenu(this)">Assign To</button>
                                    <div class="dropdown-menu simple-dropdown-menu" style="display: none;">
                                        ${(this.data.users || []).map(user =>
                                            `<a class="dropdown-item" href="#" onclick="assignTo(${issue.ID}, '${user.username}'); return false;">${user.name}</a>`
                                        ).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="index.php?page=issues&action=view&id=${issue.ID}" class="text-dark text-decoration-none">
                        ${issue.SUMMARY}
                    </a>
                    ${issue.ASSIGNEE ? `
                        <div class="mt-2">
                            <small class="text-muted"><i class="fas fa-user"></i> ${issue.ASSIGNEE}</small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    },

    getIssuesForStatus(statusId) {
        return this.data.issues.filter(i => 
            String(i.STATUS_ID || i.ISSUESTATUS) === String(statusId) &&
            this.selectedTypes.has(i.TYPE)
        );
    },

    setupDragAndDrop() {
        const lists = document.querySelectorAll('.issue-list');
        lists.forEach(list => {
            new Sortable(list, {
                group: 'shared',
                animation: 150,
                ghostClass: 'bg-light',
                emptyInsertThreshold: 50,
                onEnd: function(evt) {
                    // Don't allow dropping into orphaned column
                    if (evt.to.dataset.statusId === 'orphaned') {
                        evt.from.appendChild(evt.item);
                        return;
                    }
                    
                    const issueId = evt.item.dataset.issueId;
                    const newStatusId = evt.to.dataset.statusId;
                    
                    fetch('index.php?page=issues&action=updateStatus', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            issueId: issueId,
                            statusId: newStatusId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            evt.from.appendChild(evt.item);
                            alert('Error updating status: ' + data.message);
                        }
                        // Reload board to update totals
                        boardView.init();
                    });
                }
            });
        });
    },

    createColumn(status, issues) {
        const column = document.createElement('div');
        column.className = 'board-column';
        column.innerHTML = `
            <div class="board-column-header">
                <h5>${status.PNAME}
                    <span class="badge badge-pill badge-secondary">${issues.length}</span>
                </h5>
            </div>
            <div class="issue-list" data-status-id="${status.ID}">
                ${issues.map(issue => this.createIssueCard(issue)).join('')}
            </div>
        `;
        return column;
    },

    changeView(view) {
        this.currentView = view;
        this.render();
    },

    initializeDropdowns() {
        // No external library needed
    }
};

// Initialize everything when DOM is ready
$(document).ready(() => {
    boardView.init();
});

function toggleSimpleDropdown(button) {
    const menu = button.nextElementSibling;
    menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
}

// Update selectView so that user's choice is stored in localStorage
function selectView(view) {
    boardView.currentView = view;
    localStorage.setItem('boardView', view);
    boardView.render();
    hideDropdown();
}

// Hide any open dropdown (used after selection)
function hideDropdown(button) {
    // If a button is provided, hide its sibling menu; otherwise hide all
    if (button) {
        button.parentElement.style.display = 'none';
    } else {
        document.querySelectorAll('.simple-dropdown-menu').forEach(menu => menu.style.display = 'none');
    }
}

// Updated switchTab to hide dropdown if the tab was inside one (if needed)
function switchTab(evt, tabId) {
    evt.preventDefault();
    const tabs = document.querySelectorAll('.nav-link');
    const panes = document.querySelectorAll('.tab-pane');
    tabs.forEach(tab => tab.classList.remove('active'));
    panes.forEach(pane => pane.classList.remove('show', 'active'));
    evt.currentTarget.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('show', 'active');
}

// Replace the dummy setState function with an actual AJAX call.
function setState(issueId, stateId) {
    fetch('index.php?page=issues&action=updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ issueId: issueId, statusId: stateId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            boardView.init(); // refresh board view silently
        } else {
            alert("Error updating state: " + data.message);
        }
    })
    .catch(error => {
        alert("Error updating state: " + error);
    });
}

function assignTo(issueId, username) {
    $.ajax({
        url: 'index.php?page=issues&action=updateAssignee',
        type: 'POST',
        data: { issueId: issueId, assignee: username },
        success: function(response) {
            console.log('Assigned issue ' + issueId + ' to ' + username);
            // Optionally update the UI to show the new assignee
        },
        error: function() {
            alert('Failed to assign user.');
        }
    });
    hideDropdown();
}

function toggleSubMenu(button) {
    // Toggle display of the submenu associated with a dropdown item
    var submenu = button.nextElementSibling;
    submenu.style.display = (submenu.style.display === 'none' || submenu.style.display === '') ? 'block' : 'none';
}

function hideDropdown() {
    // Hide all open simple dropdown menus
    document.querySelectorAll('.simple-dropdown-menu').forEach(function(menu) {
        menu.style.display = 'none';
    });
}

// Global listener: if click occurs outside any dropdown toggle/menu, hide all menus.
document.addEventListener('click', function(event) {
    if (!event.target.closest('.simple-dropdown-toggle') && !event.target.closest('.simple-dropdown-menu')) {
        hideDropdown();
    }
});

function renderStateDropdownItem(issue, state) {
    return `<a class="dropdown-item" href="#" onclick="setState(${issue.ID}, '${state.ID}'); return false;">${state.PNAME}</a>`;
}

// Attach the new add-task form submission to AJAX call
document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = {
        projectId: document.getElementById('projectId').value,
        SUMMARY: document.getElementById('newTaskTitle').value,
        ISSUETYPE: document.getElementById('newTaskType').value,
        DESCRIPTION: document.getElementById('newTaskDescription').value,
        PRIORITY: document.getElementById('newTaskPriority').value,
        STATUS_ID: document.getElementById('newTaskStatus').value,
        ASSIGNEE: document.getElementById('newTaskAssignee').value
    };
    fetch('index.php?page=issues&action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Close modal and reload board (tasks + totals)
            hideModal();
            // Remove lingering backdrop
            $('.modal-backdrop').remove();
            boardView.init();
        } else {
            alert('Error adding task: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error adding task: ' + error);
    });
});

// Helper function to hide modal
function hideModal() {
    $('#addTaskModal').modal('hide');
    // Remove lingering backdrop
    $('.modal-backdrop').remove();
}
</script>

<style>
.overflow-auto::-webkit-scrollbar {
    height: 8px;
}
.overflow-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.overflow-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Desktop Board Styling */
.board-container {
    display: flex;
    gap: 1rem;
    min-height: calc(100vh - 200px);
}

.board-column {
    flex: 1;
    min-width: 300px;
    background: #ededea;
    border-radius: 4px;
    padding: 0.5rem;
}

.board-column-header {
    padding: 0.75rem;
    background: #fff;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    border: 1px solid #e3e6f0;
}

.board-column-header h5 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0;
}

.issue-card {
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: box-shadow 0.2s;
}

.issue-card:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.issue-card .card-body {
    padding: 0.75rem;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .board-container {
        /* Force block layout on mobile for both views */
        display: block !important;
        width: 100%;
    }

    .board-column {
        min-width: auto;
        margin-bottom: 1rem;
    }

    .issue-card {
        margin: 0.5rem 0;
    }

    /* Improved header for mobile */
    .d-flex.justify-content-between {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .d-flex.justify-content-between h2 {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .btn-group {
        width: 100%;
        display: flex;
        gap: 0.5rem;
    }

    .btn-group .btn {
        flex: 1;
        white-space: nowrap;
        padding: 0.5rem;
    }

    /* Make cards more touch-friendly */
    .issue-card .card-body {
        padding: 1rem;
    }

    .issue-card {
        margin-bottom: 0.75rem;
        border: 1px solid #e3e6f0;
    }

    /* Better status visibility */
    .board-column-header {
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Add scroll indicators */
    .overflow-auto {
        position: relative;
    }

    .overflow-auto::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 20px;
        background: linear-gradient(to right, transparent, rgba(0,0,0,0.05));
        pointer-events: none;
    }

    /* Optionally tighten up spacing for mobile */
    .tab-navigation, .tab-content-container {
        padding: 0 0.5rem;
    }

    .dropdown-menu {
        right: 0;
        left: auto;
        /* Optionally add some horizontal padding */
        margin-right: 0.5rem;
    }
}

/* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -1px;
}

.dropdown-submenu .dropdown-toggle::after {
    display: inline-block;
    margin-left: .255em;
    vertical-align: .255em;
    content: "";
    border-top: .3em solid transparent;
    border-right: 0;
    border-bottom: .3em solid transparent;
    border-left: .3em solid;
}

/* Tab styles */
.nav-tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
}

.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.issue-list {
    min-height: 50px; // added min-height so empty columns are droppable
}

.orphaned-column {
    background: #fff3cd;
    border: 1px solid #ffeeba;
}

.orphaned-issue {
    background: #fffbf3;
    border: 1px solid #ffeeba;
}

.orphaned-column .board-column-header {
    background: #fff3cd;
    border-bottom: 1px solid #ffeeba;
}

.orphaned-column small {
    display: block;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
</style>

<div class="workflow-details mt-4">
    <h3>Workflow Details</h3>
    <ul>
        <?php foreach ($workflowSteps as $step): ?>
            <li>
                <strong><?= htmlspecialchars($step['step_name'] ?? 'Step') ?></strong>:
                <?= htmlspecialchars($step['description'] ?? 'No description available') ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'views/templates/footer.php'; ?>
