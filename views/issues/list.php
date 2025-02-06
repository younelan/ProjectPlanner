<?php 
$pageTitle = $project['PNAME'] . ' Issues | Project Agile';
include 'views/templates/header.php'; 
?>

<style>
    .app-container {
        background: #f2f2f2;
        min-height: calc(100vh - var(--nav-height));
    }

    .page-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        padding: 1.5rem;
        margin-bottom: 2rem;
        color: white;
    }

    .page-title {
        font-size: 1.25rem;
        font-weight: 500;
        margin: 0;
    }

    .issues-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: none;
    }

    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-weight: 500;
    }

    .badge-secondary { background: #f1f5f9; color: #475569; }
    .badge-primary { background: #dbeafe; color: #1e40af; }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-info { background: #e0f2fe; color: #075985; }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .issues-container {
                            </td>
                            <td>
                                <span class="badge badge-<?= getStatusBadgeClass($issue['STATUS']) ?>">
                                    <?= htmlspecialchars($issue['STATUS']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($issue['ASSIGNEE']): ?>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($issue['ASSIGNEE']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= getPriorityIcon($issue['PRIORITY']) ?>
                                <?= htmlspecialchars($issue['PRIORITY']) ?>
                            </td>
                            <td>
                                <a href="index.php?page=issues&action=view&id=<?= $issue['ID'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="index.php?page=issues&action=edit&id=<?= $issue['ID'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-issue" 
                                        data-issue-id="<?= $issue['ID'] ?>"
                                        data-issue-key="<?= htmlspecialchars($project['PKEY'] . '-' . $issue['ID']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<?php 
// Helper functions for the view
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

include 'views/templates/footer.php'; 
?>

