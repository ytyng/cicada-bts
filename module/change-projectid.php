<h2>プロジェクトID変更</h2>

<form action="./?mode=changeProjectId&module=change-projectid" method="post" onSubmit="return confirm('実行してよろしいですか?');">

<p>変更するプロジェクトを選択してください。</p>

<select name="projectId">
<?php
foreach($cicadaBtsProjectList->projectListTable as $record){
	
	echo "<option value=\"".$record['projectId']."\">";
	echo "[ ".$record['projectId']." ] ".$record['projectName'];
	echo "</option>\n";
	
}
?>
</select>

<br />
<br />

<p>新しいプロジェクトIDを入力してください</p>
<input type="text" name="newProjectId" />

<br />
<br />

<p>プロジェクトIDを変更すると、プロジェクトの内容を表示するURLが変更されます。<br />
そのため、変更前のURLではそのプロジェクトにアクセスすることが出来なくなります。</p>
<br />
<br />
確認後、ボタンを押してください。<br />
<br />
<input type="submit" value="プロジェクトIDを変更" />
</form>

