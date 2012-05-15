<?php include("project-header.php"); ?>

<h3>プロジェクト状態を編集</h3>


<div class="table-form">
<form name="editProject" method="post" 
	action="./?mode=saveProject&module=edit-project&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>"
>

<table><tbody>
<tr><th>プロジェクトID</th><td><?php echo $cicadaBtsProject->getProjectId(); ?></td></tr>
<tr><th>プロジェクト名</th><td>
<input class="textinput" type="text" name="projectName" value="<?php echo $cicadaBtsProject->getProjectName(); ?>" />
</td></tr>
<tr><th>進捗</th><td>
<select name="progress">
<?php foreach($CONFIG['projectProgress'] as $key => $value){ ?>
	<option value="<?php echo $key; ?>" <?php if($key == $cicadaBtsProject->getProgress()){ echo "selected=\"selected\"";} ?> ><?php echo $value; ?></option>
<?php } ?>
</td></tr>
<tr><td colspan="2"  class="submitarea" ><input type="submit" value="登録" /></td></tr>

</tbody></table>

<input type="hidden" name="projectId" value="<?php echo $cicadaBtsProject->getProjectId(); ?>" />
</form>
</div>

<h3>管理</h3>
<div class="main-header-links">
<a href="./?module=change-projectid">プロジェクトIDを変更する</a>
<a href="./?module=delete-project">プロジェクトを削除する</a>
</div>

<?php include("project-footer.php"); ?>
