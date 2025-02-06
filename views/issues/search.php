<?php 
$pageTitle = 'Search Issues | Project Agile';
include 'views/templates/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Projects</a></li>
                <li class="breadcrumb-item active">Search Issues</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-search"></i> Search Issues</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="index.php" class="mb-4">
                    <input type="hidden" name="page" value="issues">
                    <input type="hidden" name="action" value="search">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" 
                                       name="q" 
                                       id="searchInput"
                                       class="form-control" 
                                       placeholder="Search term..." 
                                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                                       autocomplete="off">
                                <div id="searchSuggestions" class="dropdown-menu" style="width: 100%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="project" class="form-control">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $proj): ?>
                                    <option value="<?= $proj['ID'] ?>" 
                                        <?= (($_GET['project'] ?? '') == $proj['ID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($proj['PNAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($issues)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Summary</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issues as $issue): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($issue['PROJECT_KEY']) ?>-<?= htmlspecialchars($issue['ID']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($issue['SUMMARY']) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($issue['TYPE']) ?></span></td>
                                        <td><span class="badge" style="background-color: #<?= $issue['STATUS_ICON'] ?>">
                                            <?= htmlspecialchars($issue['STATUS']) ?>
                                        </span></td>
                                        <td>
                                            <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (isset($_GET['q']) || isset($_GET['project'])): ?>
                    <div class="alert alert-info">No issues found matching your search criteria.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionBox = document.getElementById('searchSuggestions');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value;
            if (searchTerm.length < 2) {
                suggestionBox.style.display = 'none';
                return;
            }

            fetch(`api/search_suggestions.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(suggestions => {
                    suggestionBox.innerHTML = '';
                    suggestions.forEach(suggestion => {
                        const item = document.createElement('a');
                        item.className = 'dropdown-item';
                        item.href = '#';
                        item.textContent = suggestion.SUMMARY;
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            searchInput.value = suggestion.SUMMARY;
                            suggestionBox.style.display = 'none';
                        });
                        suggestionBox.appendChild(item);
                    });
                    suggestionBox.style.display = suggestions.length ? 'block' : 'none';
                });
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
    });
});
</script>

<?php include 'views/templates/footer.php'; ?>
