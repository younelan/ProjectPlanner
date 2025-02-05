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
                            <td><?= nl2br(htmlspecialchars($issue['DESCRIPTION'] ?: 'No description provided')) ?></td>
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
            <div class="card-body">
                <table class="table table-bordered table-striped">
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
                                <td>
                                    <a href="index.php?page=issues&action=view&id=<?php echo htmlspecialchars($link['LINK_ID']); ?>">
                                        Issue <?php echo htmlspecialchars($link['LINK_ID']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($link['LINK_NAME']); ?></td>
                                <td><strong><?php echo htmlspecialchars($link['TYPE']); ?></strong></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-link" 
                                            data-link-id="<?php echo htmlspecialchars($link['ID']); ?>">
                                        <i class="fas fa-unlink"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
            <div class="card-body">
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
                                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($change['CREATED']))) ?></td>
                                <td><?= htmlspecialchars($change['AUTHOR']) ?></td>
                                <td><?= htmlspecialchars($change['FIELD']) ?></td>
                                <td><?= htmlspecialchars($change['OLDSTRING'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($change['NEWSTRING'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    .dropdown-menu {
        display: none;
        position: absolute;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        z-index: 1000;
        background: white;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: .25rem;
        padding: .5rem 0;
        margin: .125rem 0 0;
    }
    .dropdown-item {
        display: block;
        padding: .25rem 1.5rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-decoration: none;
        white-space: normal;
        word-wrap: break-word;
    }
    .dropdown-item:hover {
        color: #16181b;
        text-decoration: none;
        background-color: #f8f9fa;
    }
</style>

<?php include 'views/templates/footer.php'; ?>
