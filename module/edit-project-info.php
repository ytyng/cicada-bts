<?php include("project-header.php"); ?>

<h3>プロジェクト情報テキストを編集</h2>
<div class="table-form">
<form name="editProjectInfo" id="project-edit" method="post" action="./?mode=saveProjectInformation&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">
<table><tbody>
<tr><td colspan="2">
<textarea name="projectInformation" class="large" ><?php echo htmlspecialchars($cicadaBtsProject->getProjectInformation()); ?></textarea>
</td></tr>
<tr><td colspan="2" class="submitarea">
<input type="submit" value="登録" />
</td></tr>
</tbody></table>
<input type="hidden" name="projectId" value="<?php echo $cicadaBtsProject->getProjectId(); ?>" />
</form>
</div>

<?php include("project-footer.php"); ?>
