<h2>プロジェクト削除</h2>

<form action="./?mode=deleteProject&module=delete-project" method="post" onSubmit="return confirm('削除を実行してよろしいですか?');">

<p>削除するプロジェクトを選択してください。</p>

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

<p>確認のため、選択したプロジェクトのプロジェクトID(かっこ[～]内の英数字)を入力してください。</p>
<input type="text" name="deleteConfirm" />

<br />
<br />

<p>データファイルをディレクトリごと削除する場合は、チェックを入れてください。<br />
ディレクトリを削除した場合、元に戻すことはできません。</p>
<!--
(ディレクトリが残っている場合は、同じプロジェクトIDのプロジェクトを作成することで復活できます。)
-->
<input type="checkbox" name="deleteDirectory" />ディレクトリごと削除する<br />
<br />
<br />
確認後、ボタンを押してください。<br />
<br />
<input type="submit" value="削除実行" />
</form>

