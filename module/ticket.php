<?php 

if(!isset($_POST['enableFilenameHash']))  $_POST['enableFilenameHash']  = false;
if(!isset($_POST['enableFileOverwrite'])) $_POST['enableFileOverwrite'] = false;

$ticket=$cicadaBtsProject->getTicket($_GET['ticketId']);

//ユーザー名デフォルト値を作る
if(isset($_POST['userName'])){
	$userNameValue = $_POST['userName'];
}else if($cicadaBts->getSavedUserName()){
	$userNameValue = $cicadaBts->getSavedUserName();
}else{
	$userNameValue = "";
}

//件名デフォルト値を作る
if(isset($_POST['subject'])){
	$subjectValue = htmlspecialchars($_POST['subject']);
}else{
	$subjectValue = $ticket['subject'];
}

//件名デフォルト値を作る
if(isset($_POST['subject'])){
	$subjectValue = htmlspecialchars($_POST['subject']);
}else{
	$subjectValue = $ticket['subject'];
}

//カテゴリデフォルト値を作る
$categoryFiredNotice = "";
if(isset($_POST['category'])){
	$categoryValue = htmlspecialchars($_POST['category']);
}else{
	list($a,$b) = $cicadaBtsProject->getCategoryTxtInCategoryListOnly($ticket['categoryAry']);
	$categoryValue = join($CONFIG['categorySeparator'],$a);
	if(count($b)){
		$categoryFiredNotice  = "<span class=\"category-fired-notice\">以下のカテゴリが削除されました : ";
		$categoryFiredNotice .= htmlspecialchars(join(",",$b));
		$categoryFiredNotice .= "</span>";
	}
}

//重要度デフォルト値を作る
if(isset($_POST['severity'])){
	$severityValue = $_POST['severity'];
}else{
	$severityValue = $ticket['severity'];
}

//状態デフォルト値を作る
if(isset($_POST['status'])){
	$statusValue = $_POST['status'];
}else{
	$statusValue = $ticket['status'];
}

//担当者デフォルト値を作る
if(isset($_POST['assign'])){
	$assignValue = htmlspecialchars($_POST['assign']);
}else{
	$assignValue = $ticket['assign'];
}

//print_r($ticket);
?>
<script type="text/JavaScript">
function renderCategoryPicker(){
	aryCategory = <?php echo $cicadaBtsProject->getCategoryListJsArray(); ?>;
	if(!aryCategory.length) return;
	document.write("<ul class=\"category-picker\">\n");
	for(i in aryCategory){
		document.write("<li><a href=\"javascript:addCategory('"+aryCategory[i]+"')\">"+aryCategory[i]+"</a></li>\n");
	}
	document.write("</ul>\n");
}
function addCategory(category){
	strOldCategory = document.getElementById('input-category').value;
	aryOldCategory = strOldCategory.split(/[\,\s]+/g);
	for(i in aryOldCategory){
		if(category == aryOldCategory[i]) return;
	}
	if(strOldCategory) document.getElementById('input-category').value += "<?php echo $CONFIG['categorySeparator']; ?>";
	document.getElementById('input-category').value += category;
	//aryOldCategory = document.getElementById('input-category').value
}
</script>


<h2 id="project-title">
<?php echo $ticket['subject']; ?><br />
<small><a href="./?module=project-top&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">
<?php echo $cicadaBtsProject->getProjectName(); ?>
</a></small>
</h2>

<h3>状態</h3>
<dl class="stats">
<nobr><dt>カテゴリ</dt><dd><?php echo $ticket['category']; ?></dd></nobr>
<nobr><dt>重要度</dt><dd><?php echo $cicadaBtsProject->getTicketSeverityText($ticket['severity']); ?></dd></nobr>
<nobr><dt>状態</dt><dd><?php echo $cicadaBtsProject->getTicketStatusText($ticket['status']); ?></dd></nobr>
<nobr><dt>担当者</dt><dd><?php echo $ticket['assign']; ?></dd></nobr>
<nobr><dt>更新日時</dt><dd><?php echo CicadaBtsUtility::humanReadableDate($ticket['mtime']); ?></dd></nobr>
</dl>

<h3>シーケンス</h3>

<?php
foreach($ticket['reply'] as $record){
	echo "<div class=\"ticket\">\n";
	echo "<dl>\n";
	echo "<dt>記入者</dt><dd>".$record['userName']."</dd>\n";
	
	if($record['category']){
		echo "<dt>カテゴリ</dt><dd>".$record['category']."</dd>\n";
	}
	
	echo "<dt>重要度</dt><dd>".$cicadaBtsProject->getTicketSeverityText($record['severity'])."</dd>\n";
	echo "<dt>状態</dt><dd>".$cicadaBtsProject->getTicketStatusText($record['status'])."</dd>\n";
	echo "<dt>記入日</dt><dd>".CicadaBtsUtility::humanReadableDate($record['etime'])."</dd>\n";
	
	if($record['attach']){
		echo "<dt>添付ファイル</dt><dd>";
		echo "<a href=\"".$cicadaBtsProject->getAttachDir()."/".rawurlencode($record['attach'])."\" target=\"_blank\">";
		echo $record['attach'];
		echo "</a></dd>\n";
	}
	
	echo "<!-- <dt>件名</dt><dd>".$record['subject']."</dd> -->\n";
	echo "</dl>\n";
	$bodyText = $record['bodyText'];
	$bodyText = CicadaBtsUtility::stylizeLine($bodyText,"<br />");
	$bodyText = CicadaBtsUtility::autoLink($bodyText);
	echo "<p class=\"bodyText\">".$bodyText."</p>\n";
	echo "<br class=\"clear\" />\n";
	echo "</div>\n";
}
?>


<h3>リプライ</h3>
<div class="table-form">
<form name="editTicket" method="post"
	action="./?mode=writeTicket&module=ticket&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>&ticketId=<?php echo $ticket['ticketId']; ?>"
	onSubmit="if(this.bodyText.value==''){alert('内容を入力してください');return false;}else{return confirm('登録してよろしいですか?');}"
	enctype="multipart/form-data"
>
<table><tbody>
<tr><th>記入者</th>
<td><input class="textinput" name="userName" value="<?php echo htmlspecialchars($userNameValue); ?>" /></td></tr>
<tr><th>件名</th>
<td><input class="textinput" name="subject" value="<?php echo $subjectValue; ?>" /></td></tr>

<tr><th>カテゴリ</th>
<td><input class="textinput" name="category" id="input-category" value="<?php echo $categoryValue; ?>" /><br />
<script type="text/JavaScript">renderCategoryPicker();</script>
<?php echo $categoryFiredNotice; ?>
</td></tr>


<tr><th>重要度</th><td>
<select name="severity">
<?php
foreach($CONFIG['ticketSeverity'] as $key => $record){
	echo "<option value=\"".$key."\" ";
	if($key == $severityValue){
		echo "selected=\"selected\" ";
	}
	echo "title=\"".$record[1]."\" ";
	echo " >";
	echo $record[0];
	echo "</option>\n";
} ?>
</select>
</td></tr>

<tr><th>ステータス</th><td>
<select name="status">
<?php
foreach($CONFIG['ticketStatus'] as $key => $record){
	echo "<option value=\"".$key."\" ";
	if($key == $statusValue){
		echo "selected=\"selected\" ";
	}
	echo "title=\"".$record[1]."→".$record[2]." | ".$record[3]."\" ";
	echo " >";
	echo "".$record[1]." : ".$record[0];
	echo "</option>\n";
} ?>
</select>
</td></tr>

<tr><th>修正担当者</th>
<td><input class="textinput" name="assign" value="<?php echo $assignValue; ?>" /></td></tr>

<tr><th>添付ファイル</th>
<td><input class="fileinput" name="attach" type="file" /><br />
<nobr><input type="checkbox" name="enableFilenameHash"  <?php if($_POST['enableFilenameHash']) {echo "checked=\"checked\" ";} ?> /><small>ファイル名を自動生成</small></nobr> 
<nobr><input type="checkbox" name="enableFileOverwrite" <?php if($_POST['enableFileOverwrite']){echo "checked=\"checked\" ";} ?> /><small>ファイル上書きを許可</small></nobr> 
</td></tr>


<tr><td colspan="2">
<textarea name="bodyText" class="large"><?php echo isset($_POST['bodyText'])?htmlspecialchars($_POST['bodyText']):"" ?></textarea>
</td></tr>
<tr><td colspan="2">
<tr><td colspan="2" class="submitarea"><input type="submit" value="登録" /></td></tr>

</tbody></table>
<input type="hidden" name="ticketId" value="<?php echo $ticket['ticketId']; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
</form>
</div>

<?php include("project-footer.php"); ?>
