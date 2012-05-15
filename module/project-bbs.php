<?php include("project-header.php"); ?>

<h3>プロジェクト掲示板</h3>
<ul id="project-bbs">
<?php
$projectBbsTable = $cicadaBtsProject->getProjectBbsTable();

foreach($projectBbsTable as $record){
	echo "<li>\n";
	
	echo "<h4>";
	echo "<span class=\"subject\">".$record['subject']."</span>";
	echo "<span class=\"userName\">".$record['userName']."</span>";
	echo "<span class=\"etime\">".date('Y-m-d H:i',$record['etime'])."</span>";
	echo "</h4>\n";
	$bodyText = $record['bodyText'];
	$bodyText = CicadaBtsUtility::stylizeLine($bodyText,"<br />");
	$bodyText = CicadaBtsUtility::autoLink($bodyText);	
	echo "<p>".$bodyText."</p>\n";
	echo "</li>\n";
}
?>
</ul>

<h3>書き込み</h3>
<div class="table-form">
<form name="projectBbs" method="post"
	action="./?mode=projectBbsWrite&module=project-bbs&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>"
	onSubmit="if(this.bodyText.value==''){alert('内容を入力してください');return false;}else{return confirm('登録してよろしいですか?');}"
>
<table><tbody>
<?php /* width="30%" は、IEの表示崩れ対策 */ ?>
<tr><th width="30%">記入者</th><td><input class="textinput" name="userName" value="<?php echo htmlspecialchars($cicadaBts->getSavedUserName()); ?>" /></td></tr>
<tr><th>件名</th><td><input class="textinput" name="subject" /></td></tr>
<tr><td colspan="2"><textarea name="bodyText" class="large"></textarea></td></tr>
<tr><td colspan="2"  class="submitarea" ><input type="submit" value="登録" /></td></tr>
</tbody></table>
</form>
</div>

<?php include("project-footer.php"); ?>