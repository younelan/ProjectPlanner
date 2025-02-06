<?php 
$pageTitle = "Issue: " . htmlspecialchars($issue['SUMMARY']);
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=projects&action=view&id=<?= htmlspecialchars($issue['PROJECT']) ?>">Project</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($issue['SUMMARY']) ?></li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><?= htmlspecialchars($issue['SUMMARY']) ?></h2>
                <div>
                    <a href="index.php?page=issues&action=edit&id=<?= $issue['ID'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit Issue
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="20%">ID</th>
                            <td><?= htmlspecialchars($issue['PROJECT_KEY'] . '-' . $issue['ID']) ?></td>
                        </tr>
                        <tr>
                            <th>Summary</th>
                            <td><?= htmlspecialchars($issue['SUMMARY']) ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?= nl2br($issue['DESCRIPTION'] ?: 'No description provided') ?></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <span class="badge badge-info">
                                    <?= htmlspecialchars($issue['TYPE']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge" style="background-color: #<?= htmlspecialchars($issue['STATUS_ICON']) ?>">
                                    <?= htmlspecialchars($issue['STATUS_NAME']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Priority</th>
                            <td>
                                <span class="badge" style="background-color: #<?= htmlspecialchars($issue['PRIORITY_COLOR']) ?>">
                                    <?= htmlspecialchars($issue['PRIORITY_NAME']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Assignee</th>
                            <td>
                                <?php if ($issue['ASSIGNEE']): ?>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($issue['ASSIGNEE']) ?>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-user-slash"></i> Unassigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Reporter</th>
                            <td>
                                <i class="fas fa-user"></i> <?= htmlspecialchars($issue['REPORTER'] ?: 'Unknown') ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($issue['CREATED']))) ?></td>
                        </tr>
                        <tr>
                            <th>Updated</th>
                            <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($issue['UPDATED']))) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Linked Issues</h3>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#linkIssueModal">
                    <i class="fas fa-link"></i> Add Link
                </button>
            </div>
            <?php if (!empty($linkedIssues)): ?>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="linked-issues-table">
                        <thead>
                            <tr>
                                <th>Issue ID</th>
                                <th>Link Name</th>
                                <th>Link Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($linkedIssues as $link): ?>
                                <tr>
                                    <td data-label="Issue ID">
                                        <a href="index.php?page=issues&action=view&id=<?= htmlspecialchars($link['LINK_ID']) ?>">
                                            Issue <?= htmlspecialchars($link['LINK_ID']) ?>
                                        </a>
                                    </td>
                                    <td data-label="Link Name"><?= htmlspecialchars($link['LINK_NAME']) ?></td>
                                    <td data-label="Link Type"><strong><?= htmlspecialchars($link['TYPE']) ?></strong></td>
                                    <td data-label="Actions">
                                        <button class="btn btn-sm btn-danger delete-link" 
                                                data-link-id="<?= htmlspecialchars($link['ID']) ?>">
                                            <i class="fas fa-unlink"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Link Issue Modal -->
        <?php if (!isset($linkTypes)) { $linkTypes = []; } ?>
        <div class="modal fade" id="linkIssueModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Link Issue</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="index.php?page=issues&action=addLink&id=<?= $issue['ID'] ?>" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="linkType">Link Type</label>
                                <select class="form-control" id="linkType" name="linkType" required>
                                    <?php foreach ($linkTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type['ID']) ?>">
                                            <?= htmlspecialchars($type['LINKNAME']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="linkedIssueId">Search Issue</label>
                                <div class="dropdown">
                                    <input type="text" class="form-control" id="issueSearch" 
                                           autocomplete="off"
                                           placeholder="Start typing issue number or title...">
                                    <ul id="searchResults" class="dropdown-menu w-100"></ul>
                                    <input type="hidden" id="linkedIssueId" name="linkedIssueId" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Comment Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">Add Comment</h3>
            </div>
            <div class="card-body">
                <form action="index.php?page=issues&action=addComment&id=<?= $issue['ID'] ?>" method="POST">
                    <div class="form-group">
                        <textarea name="comment" class="form-control" rows="3" required 
                                placeholder="Enter your comment..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Comment
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Author</th>
                                <th>Field</th>
                                <th>From</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $change): ?>
                                <tr>
                                    <td data-label="Date"><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($change['CREATED']))) ?></td>
                                    <td data-label="Author"><?= htmlspecialchars($change['AUTHOR']) ?></td>
                                    <td data-label="Field"><?= htmlspecialchars($change['FIELD']) ?></td>
                                    <td data-label="From"><?= htmlspecialchars($change['OLDSTRING'] ?: '-') ?></td>
                                    <td data-label="To"><?= htmlspecialchars($change['NEWSTRING'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for autocomplete and delete functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete link functionality
        document.querySelectorAll('.delete-link').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const linkId = this.dataset.linkId;
                if (confirm('Are you sure you want to remove this link?')) {
                    fetch(`index.php?page=issues&action=deleteLink&id=<?= $issue['ID'] ?>&linkId=${linkId}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.error || 'Failed to delete link');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete link');
                    });
                }
            });
        });

        // Issue search functionality
        const searchInput = document.getElementById('issueSearch');
        const searchResults = document.getElementById('searchResults');
        const linkedIssueId = document.getElementById('linkedIssueId');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const term = this.value.trim();
            
            if (term.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`index.php?page=issues&action=autocompleteIssues&projectId=<?= $issue['PROJECT'] ?>&term=${encodeURIComponent(term)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        data.forEach(item => {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.classList.add('dropdown-item');
                            a.href = '#';
                            a.textContent = `${item.ID} - ${item.LABEL}`;
                            a.addEventListener('click', (e) => {
                                e.preventDefault();
                                searchInput.value = `${item.ID} - ${item.LABEL}`;
                                linkedIssueId.value = item.ID;
                                searchResults.style.display = 'none';
                            });
                            li.appendChild(a);
                            searchResults.appendChild(li);
                        });
                        searchResults.style.display = data.length ? 'block' : 'none';
                    });
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    });
</script>

<style>
/* Desktop improvements */
.table-bordered {
    border-radius: 4px;
    overflow: hidden;
}

.table-bordered th {
    background-color: #f8f9fa;
    width: 200px;
    vertical-align: middle;
}

.badge {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
}

/* Tables in cards */
.card .table {
    margin-bottom: 0;
}

.card .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}

/* History and Links tables */
.history-table, .linked-issues-table {
    width: 100%;
    margin-bottom: 1rem;
}

.history-table th, .linked-issues-table th {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-bottom: 2px solid #dee2e6;
}

.history-table td, .linked-issues-table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #dee2e6;
}

/* Mobile Responsive Design */
@media (max-width: 768px) {
    .table-responsive {
        border: 0;
    }
    
    .table-bordered {
        border: none;
    }

    .table-bordered tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .table-bordered th,
    .table-bordered td {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.75rem;
    }

    .table-bordered th {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    /* History table mobile view */
    .history-table thead {
        display: none;
    }

    .history-table tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 0.5rem;
    }

    .history-table td {
        display: block;
        text-align: left;
        padding: 0.5rem;
        border: none;
    }

    .history-table td:before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 100px;
    }

    /* Linked issues table mobile view */
    .linked-issues-table thead {
        display: none;
    }

    .linked-issues-table tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 0.5rem;
    }

    .linked-issues-table td {
        display: block;
        text-align: left;
        padding: 0.5rem;
        border: none;
    }

    .linked-issues-table td:before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 120px;
    }

    /* Buttons in mobile view */
    .btn-group {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .btn-sm {
        flex: 1;
    }
}

/* New markup styles */
blockquote {
    border-left: 4px solid #e0e0e0;
    margin: 1em 0;
    padding: 0.5em 1em;
    background-color: #f8f9fa;
}

cite {
    font-style: italic;
    color: #666;
}

del {
    color: #dc3545;
    text-decoration: line-through;
}

ins {
    color: #28a745;
    text-decoration: underline;
}

sup, sub {
    font-size: 75%;
}
</style>

<?php include 'views/templates/footer.php'; ?>
