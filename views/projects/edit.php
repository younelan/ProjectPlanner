<?php 
$pageTitle = 'Edit Project | Scrum Viewer';
include 'views/templates/header.php'; 
?>
<h2>Edit Project</h2>
<form action="index.php?page=projects&action=update&id=<?= htmlspecialchars($project['ID']) ?>" method="post">
    <div class="form-group">
        <label for="PKEY">Project Key</label>
        <input type="text" name="PKEY" id="PKEY" class="form-control" value="<?= htmlspecialchars($project['PKEY']) ?>" required>
    </div>
    <div class="form-group">
        <label for="PNAME">Project Name</label>
        <input type="text" name="PNAME" id="PNAME" class="form-control" value="<?= htmlspecialchars($project['PNAME']) ?>" required>
    </div>
    <div class="form-group">
        <label for="URL">Project URL</label>
        <input type="text" name="URL" id="URL" class="form-control" value="<?= htmlspecialchars($project['URL']) ?>">
    </div>
    <div class="form-group">
        <label for="LEAD">Project Lead</label>
        <input type="text" name="LEAD" id="LEAD" class="form-control" value="<?= htmlspecialchars($project['LEAD']) ?>">
    </div>
    <div class="form-group">
        <label for="PROJECTTYPE">Project Type</label>
        <input type="text" name="PROJECTTYPE" id="PROJECTTYPE" class="form-control" value="<?= htmlspecialchars($project['PROJECTTYPE']) ?>">
    </div>
    <div class="form-group">
        <label for="ORIGINALKEY">Original Key</label>
        <input type="text" name="ORIGINALKEY" id="ORIGINALKEY" class="form-control" value="<?= htmlspecialchars($project['ORIGINALKEY']) ?>">
    </div>
    <div class="form-group">
        <label for="DESCRIPTION">Description</label>
        <textarea name="DESCRIPTION" id="DESCRIPTION" class="form-control"><?= htmlspecialchars($project['DESCRIPTION']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Project</button>
</form>
<?php 
include 'views/templates/footer.php'; 
?>