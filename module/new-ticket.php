<?php 

if(!isset($_POST['enableFilenameHash']))  $_POST['enableFilenameHash']  = false;
if(!isset($_POST['enableFileOverwrite'])) $_POST['enableFileOverwrite'] = false;

//ユーザー名デフォルト値を作る
if(isset($_POST['userName'])){
	$userNameValue = $_POST['userName'];
}else if($cicadaBts->getSavedUserName()){
	$userNameValue = $cicadaBts->getSavedUserName();
}else{
	$userNameValue = "";
}

//重要度デフォルト値を作る
if(isset($_POST['severity'])){
	$severityValue = $_POST['severity'];
}else{
	$severityValue = $CONFIG['ticketSeverityDefault'];
}

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

<?php include("project-header.php"); ?>

<h3>新規チケット</h3>
<div class="table-form">
<form name="editTicket" method="post"
	action="./?mode=writeTicket&module=new-ticket&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>"
	onSubmit="if(this.bodyText.value==''){alert('内容を入力してください');return false;}else{return confirm('登録してよろしいですか?');}"
	enctype="multipart/form-data"
>
<table><tbody>
<tr><th>記入者</th>
<td><input class="textinput" name="userName" value="<?php echo htmlspecialchars($userNameValue); ?>" /></td></tr>
<tr><th>件名</th>
<td><input class="textinput" name="subject" value="<?php echo isset($_POST['subject'])?htmlspecialchars($_POST['subject']):"" ?>" /></td></tr>
<tr><th>カテゴリ</th>
<td><input class="textinput" name="category" id="input-category" value="<?php echo isset($_POST['category'])?htmlspecialchars($_POST['category']):"" ?>" /><br />
<script type="text/JavaScript">renderCategoryPicker();</script>
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

<?php
/*
<tr><th>ステータス</th><td>
<select name="status">
<?php
foreach($CONFIG['ticketStatus'] as $key => $record){
	echo "<option value=\"".$key."\" ";
	if($key == $CONFIG['ticketStatusDefault']){
		echo "selected=\"selected\" ";
	}
	echo "title=\"".$record[2]."\" ";
	echo " >";
	echo "".$record[1]." : ".$record[0];
	echo "</option>\n";
} ?>
</select>
</td></tr>

<tr><th>担当者</th>
<td><input class="textinput" name="assign" value="<?php echo isset($_POST['assign'])?htmlspecialchars($_POST['assign']):"" ?>" /></td></tr>
*/
?>

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
<input type="hidden" name="ticketId" value="" />
<input type="hidden" name="status" value="<?php echo $CONFIG['ticketStatusDefault']; ?>" />
<input type="hidden" name="assign" value="" />
<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
</form>
</div>

<?php include("project-footer.php"); ?>
