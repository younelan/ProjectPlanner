<?php 
$pageTitle = "Sprint Board";
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
                    <a href="index.php?page=projects&action=view&id=<?= $sprint['PROJECT_ID'] ?>">
                        <?= htmlspecialchars($projectName) ?>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="index.php?page=sprints&action=list&projectId=<?= $sprint['PROJECT_ID'] ?>">Sprints</a>
                </li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($sprint['NAME']) ?></li>
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
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createIssueModal">
            <i class="fas fa-plus"></i> Create Issue
        </button>
        <a href="index.php?page=sprints&action=list&projectId=<?= $sprint['PROJECT_ID'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sprints
        </a>
    </div>
</div>

<!-- Add dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>

<style>
/* Board Container */
#boardContainer {
    max-width: 100%;
    overflow-x: auto;
    padding: 0 1rem;
}

/* Board Layout */
.board-container {
    display: flex;
    gap: 1rem;
    padding-bottom: 1rem;
    min-height: calc(100vh - 200px);
}

/* Column Styling */
.board-column {
    flex: 0 0 300px;
    background: var(--light);
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 180px);
}

.board-column-header {
    background: white;
    padding: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(0,0,0,.125);
    position: sticky;
    top: 0;
    z-index: 10;
}

.board-column-header h5 {
    margin: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1rem;
    font-weight: 600;
}

/* Issue List */
.issue-list {
    flex: 1;
    overflow-y: auto;
    min-height: 100px;
    padding: 0.5rem;
}

/* Card Styling */
.issue-card {
    background: white;
    border: 1px solid rgba(0,0,0,.125);
    border-radius: 0.25rem;
    margin-bottom: 0.75rem;
    cursor: grab;
}

.issue-card:active {
    cursor: grabbing;
}

.issue-card .card-body {
    padding: 1rem;
}

/* Dropdown Styling */
.dropdown-menu {
    min-width: 200px;
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    border: 1px solid rgba(0,0,0,.125);
    position: absolute;
    right: 0;  /* Align to right instead of left */
    left: auto !important;  /* Override Bootstrap's left positioning */
}

.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    right: 100%;  /* Open to the left instead of right */
    left: auto;
    margin-right: -1px;  /* Negative margin to overlap with parent menu */
    margin-left: 0;
}

/* Adjust submenu indicators for right-aligned dropdowns */
.dropdown-submenu .dropdown-item {
    padding-left: 1rem;
    padding-right: 1.5rem;
}

.dropdown-submenu .dropdown-toggle::after {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%) rotate(180deg); /* Flip the arrow for right alignment */
}

/* Ensure dropdowns stay in view on mobile */
@media (max-width: 768px) {
    .dropdown-menu {
        position: fixed !important;
        top: auto !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100%;
        max-height: 50vh;
        overflow-y: auto;
        margin: 0;
        border-radius: 1rem 1rem 0 0;
    }

    .dropdown-submenu .dropdown-menu {
        position: static !important;
        margin-left: 1rem;
        margin-right: 0;
        box-shadow: none;
        border-left: 2px solid var(--primary);
        width: auto;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .board-container {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding: 1rem 0;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
    }

    .board-column {
        flex: 0 0 85vw;
        margin-right: 1rem;
        scroll-snap-align: start;
        height: calc(100vh - 180px);
    }

    .board-column:last-child {
        margin-right: 1rem; /* Ensure last column has right margin on mobile */
    }

    .issue-card {
        margin-bottom: 1rem;
    }

    /* Ensure dropdowns stay on screen */
    .dropdown-menu {
        position: fixed !important;
        top: auto !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100%;
        max-height: 50vh;
        overflow-y: auto;
        margin: 0;
        border-radius: 1rem 1rem 0 0;
        transform: none !important;
    }

    .dropdown-submenu .dropdown-menu {
        position: static !important;
        margin-left: 1rem;
        box-shadow: none;
        border-left: 2px solid var(--primary);
    }

    /* Larger touch targets */
    .dropdown-item {
        padding: 0.75rem 1rem;
    }

    .btn-sm {
        padding: 0.5rem 0.75rem;
    }

    /* Header adjustments */
    #boardContainer > .d-flex {
        flex-wrap: wrap;
        gap: 1rem;
    }

    #boardContainer h2 {
        width: 100%;
        font-size: 1.5rem;
    }

    .btn-group {
        width: 100%;
        display: flex;
    }

    .btn-group .btn {
        flex: 1;
    }
}

/* Tabbed View Styles */
.tab-navigation {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.nav-tabs {
    white-space: nowrap;
    flex-wrap: nowrap;
}

.nav-tabs .nav-link {
    padding: 0.75rem 1rem;
}

.tab-content {
    padding: 1rem;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
}

/* Additional styles */
.form-check {
    margin-right: 1rem;
    margin-bottom: 0.5rem;
}

.gap-3 {
    gap: 1rem;
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

<div id="boardContainer">
    <div id="board"></div>

    <!-- Add this filter card -->
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
</div>

<script>
// ...existing code...
const sprintBoard = {
    // Use saved view preference, defaulting to 'tabbed'
    currentView: localStorage.getItem('sprintBoard') || 'tabbed',
    data: null,
    async init() {
        await this.loadData();
        await this.setupTypeFilters(); // Add this line
        this.setupEventListeners();
        this.render();
        this.initializeDropdowns();
    },

    async loadData() {
        // Fix the URL to use the sprint endpoint instead of project endpoint
        const response = await fetch('index.php?page=sprints&action=board&id=<?= $sprint['ID'] ?>&api=1');
        this.data = await response.json();
        
        console.log('Sprint Board Data:', this.data);
    },

    setupEventListeners() {
        // Remove this since we're using onclick handlers
        // document.querySelectorAll('[data-view]').forEach(link => {
        //     link.addEventListener('click', (e) => {
        //         e.preventDefault();
        //         this.currentView = e.target.dataset.view;
        //         this.render();
        //     });
        // });
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
            !workflowStateIds.has(String(issue.ISSUESTATUS))
        );
        
        container.className = 'board-container d-flex';
        
        // Render regular workflow columns
        sortedWorkflow.forEach((status) => {
            const column = this.createColumn(status, this.getIssuesForStatus(status.ID));
            container.appendChild(column);
        });
        
        // If there are orphaned issues, create a special column for them
        if (orphanedIssues.length > 0) {
            const filteredOrphanedIssues = orphanedIssues.filter(issue => 
                this.selectedTypes.has(issue.ISSUETYPE)
            );
            const orphanedColumn = this.createOrphanedColumn(filteredOrphanedIssues);
            container.appendChild(orphanedColumn);
        }
        
        this.setupDragAndDrop();
    },

    getIssuesForStatus(statusId) {
        // Simply match the status IDs directly from workflow
        return this.data.issues.filter(i => 
            String(i.ISSUESTATUS) === String(statusId) &&
            this.selectedTypes.has(i.ISSUETYPE)
        );
    },

    setupDragAndDrop() {
        const lists = document.querySelectorAll('.issue-list');
        lists.forEach(list => {
            const options = {
                group: {
                    name: 'shared',
                    pull: true,
                    put: list.dataset.statusId !== 'orphaned'
                },
                animation: 150,
                ghostClass: 'bg-light',
                emptyInsertThreshold: 50,
                onEnd: function(evt) {
                    const issueId = evt.item.dataset.issueId;
                    const newStatusId = evt.to.dataset.statusId;
                    
                    // Only update the status
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
                        sprintBoard.init();
                    });
                }
            };
            new Sortable(list, options);
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

    createIssueCard(issue, isOrphaned = false) {
        // Get the actual state name directly from the issue's STATUS field
        const stateName = isOrphaned ? 
            issue.STATUS || 'Unknown' : 
            Object.values(this.data.workflow).find(w => w.ID === issue.ISSUESTATUS)?.PNAME || 'Unknown';

        return `
            <div class="card issue-card ${isOrphaned ? 'orphaned-issue' : ''}" data-issue-id="${issue.ID}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="badge badge-info">${issue.ISSUETYPE}</span>
                        ${isOrphaned ? `<span class="badge badge-warning">State: ${stateName}</span>` : ''}
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
                                            `<a class="dropdown-item" href="#" onclick="assignTo(${issue.ID}, '${user.USER_KEY}'); return false;">
                                                <i class="fas fa-user"></i> ${user.USER_KEY}
                                            </a>`
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

    changeView(view) {
        this.currentView = view;
        this.render();
    },

    initializeDropdowns() { 
        // No external library needed
    },

    setupTypeFilters() {
        // Get unique issue types from ALL issues including orphaned ones
        const issueTypes = [...new Set(this.data.issues.map(issue => issue.ISSUETYPE))];
        
        // Use sprint-specific storage key
        const storageKey = `sprintBoardSelectedTypes-${this.data.sprint.ID}`;
        
        // Initialize selected types from localStorage or all types
        this.selectedTypes = new Set(
            JSON.parse(localStorage.getItem(storageKey)) || 
            issueTypes
        );

        // Create filters
        const filterContainer = document.getElementById('boardIssueTypeFilters');
        filterContainer.innerHTML = ''; // Clear existing filters
        
        // Sort issue types alphabetically
        issueTypes.sort().forEach(type => {
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
                localStorage.setItem(storageKey, JSON.stringify([...this.selectedTypes]));
                this.render();
            });
        });
    }
};

// Initialize everything when DOM is ready
$(document).ready(() => {
    sprintBoard.init();
});

function toggleSimpleDropdown(button) {
    const menu = button.nextElementSibling;
    menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
}

// Update selectView so that user's choice is stored in localStorage
function selectView(view) {
    sprintBoard.currentView = view;
    localStorage.setItem('sprintBoard', view);
    sprintBoard.render();
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

// Remove hardcoded setState mapping and use workflow data directly
function setState(issueId, workflowId) {
    fetch('index.php?page=issues&action=updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            issueId: issueId,
            statusId: workflowId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            sprintBoard.init();
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
</script>


<?php include 'views/templates/footer.php'; ?>
