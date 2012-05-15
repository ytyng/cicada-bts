
<h2><?php echo $CONFIG['frontpageTitle']; ?></h2>

<div class="main-header-links">
<a href="./?module=project-list">全プロジェクトリスト</a>
</div>

<h3>検証向け未解決チケットを含むプロジェクト</h3>
<table class="ticket-list"><tbody>
<tr><th>プロジェクト</th><th>進捗</th><th title="チケット数">数</th><th colspan="2">更新日時</th></tr>
<?php
foreach($cicadaBtsProjectList->projectListTable as $record){
	if($record['unsolvedForTest'] > 0){
		echo "<tr>";
		echo "<td>";
		echo "<a href=\"?module=project-top&projectId=".$record['projectId']."\">";
		echo $record['projectName'];
		echo "</a></td>\n";
		echo "<td>".$CONFIG['projectProgress'][$record['progress']]."</td>\n";
		echo "<td class=\"right\">".$record['unsolvedForTest']."</td>\n";
		echo "<td class=\"right\">".CicadaBtsUtility::humanReadableDateDiff($record['mtime'])."</td>";
		echo "<td>".CicadaBtsUtility::humanReadableDate($record['mtime'])."</td>";
		echo "</tr>";
	}
}
?>
</table>

<h3>開発向け未解決チケットを含むプロジェクト</h3>
<table class="ticket-list"><tbody>
<tr><th>プロジェクト</th><th>進捗</th><th title="チケット数">数</th><th colspan="2">更新日時</th></tr>

<?php
foreach($cicadaBtsProjectList->projectListTable as $record){
	if($record['unsolvedForDevelop'] > 0){
		echo "<tr>";
		echo "<td>";
		echo "<a href=\"?module=project-top&projectId=".$record['projectId']."\">";
		echo $record['projectName'];
		echo "</a></td>\n";
		echo "<td>".$CONFIG['projectProgress'][$record['progress']]."</td>\n";
		echo "<td class=\"right\">".$record['unsolvedForDevelop']."</td>\n";
		echo "<td class=\"right\">".CicadaBtsUtility::humanReadableDateDiff($record['mtime'])."</td>";
		echo "<td>".CicadaBtsUtility::humanReadableDate($record['mtime'])."</td>";
		echo "</tr>";
	}
}
?>
</table>


<h3>管理者ログイン</h3>
<?php if($cicadaBts->isAdminLogin()){ ?>
	管理者ログイン中です。 
	[ <?php echo $cicadaBts->getLoggingUserId(); ?> ]
	<a href="./?mode=adminLogout">ログアウト</a>
<?php }else{ ?>
	<form name="adminlogin" method="post" action="./?mode=adminLogin">
	ID:<input name="uid" type="text" value="<?php echo isset($_POST['uid'])?htmlspecialchars($_POST['uid']):"" ;?>" /> 
	Password:<input name="upw" type="password" /> 
	<input type="submit" value="ログイン" />
<?php } ?>
</form>


<?php if($cicadaBts->isAdminLogin()){ ?>
	<h3>プロジェクト新規作成</h3>
	
	<form name="newproject" method="post" action="./?mode=newProject">
	プロジェクトID:<input name="projectId" type="text" value="<?php
		echo isset($_POST['projectId'])?htmlspecialchars($_POST['projectId']):"" ;
	?>" title="半角英数字のみ" /> 
	プロジェクト名:<input name="projectName" type="text" value="<?php
		echo isset($_POST['projectName'])?htmlspecialchars($_POST['projectName']):"" ;
	?>" /> 
	<input type="submit" value="作成" />
	</form>
	
	<h3>管理</h3>
	<div class="main-header-links">
	<a href="./?module=default-mail-setting">デフォルトメール送信先設定</a>
	<a href="./?module=logviewer-global">グローバルログ</a>
	<a href="./?module=change-projectid">プロジェクトID変更</a>
	<a href="./?module=delete-project">プロジェクト削除</a>
	</div>
	
<?php } ?>

<!--
<h3>テスト用</h3>
<a href="./?module=mail-test">メールテスト</a>
-->
