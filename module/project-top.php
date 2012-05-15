<?php include("project-header.php"); ?>

<?php if($cicadaBts->isAdminLogin()){ ?>
<h3>管理</h3>
<div class="main-header-links">
<a href="./?module=edit-project&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">プロジェクト状態を編集</a>
<a href="./?module=mail-setting&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">メール送信設定</a>
<a href="./?module=edit-category-list&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">カテゴリリストを編集</a>
<a href="./?module=logviewer-project&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">プロジェクトログ</a>
</div>
<?php } ?>

<h3>状態</h3>
<dl class="stats">
<nobr><dt>進捗</dt><dd><?php echo $CONFIG['projectProgress'][$cicadaBtsProject->getProgress()]; ?></dd></nobr>
<nobr><dt>作成日</dt><dd><?php echo CicadaBtsUtility::humanReadableDate($cicadaBtsProject->getCtime()); ?></dd></nobr>
<nobr><dt>更新日</dt><dd><?php echo CicadaBtsUtility::humanReadableDate($cicadaBtsProject->getMtime()); ?></dd></nobr>
<nobr><dt>未解決チケット</dt><dd><?php echo $cicadaBtsProject->getUnsolved(); ?></dd></nobr>
<nobr><dt>検証向け</dt><dd><?php echo $cicadaBtsProject->getUnsolvedForTest(); ?></dd></nobr>
<nobr><dt>開発向け</dt><dd><?php echo $cicadaBtsProject->getUnsolvedForDevelop(); ?></dd></nobr>
<nobr><dt>合計チケット</dt><dd><?php echo $cicadaBtsProject->getTotal(); ?></dd></nobr>
</dl>

<h3>プロジェクト内チケット検索</h3>
<form method="get" target="./">
<input type="text" name="q" value="<?php if(isset($_GET['q'])) echo htmlspecialchars($_GET['q']); ?>" />
<input type="submit" value="検索" />
<input type="hidden" name="module" value="ticket-search-word" />
<input type="hidden" name="projectId" value="<?php echo $cicadaBtsProject->getProjectId(); ?>" />
</form>

<?php
if(count($cicadaBtsProject->getCategoryTable())){
	echo "<h3>カテゴリリスト</h3>\n";
	echo join(" ",$cicadaBtsProject->makeTicketSearchCategoryLink($cicadaBtsProject->getCategoryTable()));
}
?>



<?php
//,$cicadaBtsProject->makeTicketSearchCategoryLink($record['categoryAry']))
?>
<h3>プロジェクト情報</h3>
<pre id="project-information"><?php
echo CicadaBtsUtility::autoLink(
	CicadaBtsUtility::stylizeLine(
		htmlspecialchars(
			$cicadaBtsProject->getProjectInformation()
		)
	)
); ?></pre>
<?php if($cicadaBts->isAdminLogin()){ ?>
<div class="main-footer-links">
<a href="./?module=edit-project-info&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">[ プロジェクト情報テキストを編集 ]</a>
</div>
<?php } ?>

<h3>検証向け未解決</h3>
<table class="ticket-list"><tbody>
<tr><th>ID</th><th>件名</th><th>重要度</th><th>状態</th><th>カテゴリ</th><th>更新日時</th><th>起票者</th><th>更新者</th><th>数</th></tr>
<?php
foreach( $cicadaBtsProject->getTicketTable() as $ticketId => $record){
	//if($record['unsolved'])
	if(in_array($record['status'],$CONFIG['ticketStatusUnsolvedForTest'])){
		echo "<tr>";
		echo "<td class=\"right\">".$ticketId."</td>\n";
		echo "<td>";
		echo "<a href=\"./?module=ticket&projectId=".$cicadaBtsProject->getProjectId()."&ticketId=".$ticketId."\" >";
		echo $record['subject'];
		echo "</a>";
		echo "</td>";
		echo "<td>".$cicadaBtsProject->getTicketSeverityText($record['severity'])."</td>\n";
		echo "<td>".$cicadaBtsProject->getTicketStatusText($record['status'])."</td>\n";
		echo "<td>".join(" ",$cicadaBtsProject->makeTicketSearchCategoryLink($record['categoryAry']))."</td>\n";
		echo "<td>".CicadaBtsUtility::humanReadableDateDiff($record['mtime'])."</td>\n";
		echo "<td>".$record['reporter']."</td>\n";
		echo "<td>".$record['modifier']."</td>\n";
		echo "<td class=\"right\">".count($record['reply'])."</td>\n";
		echo "</tr>";
	}
}
?>
</tbody></table>

<h3>開発向け未解決</h3>
<table class="ticket-list"><tbody>
<tr><th>ID</th><th>件名</th><th>重要度</th><th>状態</th><th>カテゴリ</th><th>更新日時</th><th>起票者</th><th>更新者</th><th>数</th></tr>
<?php
foreach( $cicadaBtsProject->getTicketTable() as $ticketId => $record){
	//if($record['unsolved'])
	if(in_array($record['status'],$CONFIG['ticketStatusUnsolvedForDevelop'])){
		echo "<tr>";
		echo "<td class=\"right\">".$ticketId."</td>\n";
		echo "<td>";
		echo "<a href=\"./?module=ticket&projectId=".$cicadaBtsProject->getProjectId()."&ticketId=".$ticketId."\" >";
		echo $record['subject'];
		echo "</a>";
		echo "</td>";
		echo "<td>".$cicadaBtsProject->getTicketSeverityText($record['severity'])."</td>\n";
		echo "<td>".$cicadaBtsProject->getTicketStatusText($record['status'])."</td>\n";
		echo "<td>".join(" ",$cicadaBtsProject->makeTicketSearchCategoryLink($record['categoryAry']))."</td>\n";
		echo "<td>".CicadaBtsUtility::humanReadableDateDiff($record['mtime'])."</td>\n";
		echo "<td>".$record['reporter']."</td>\n";
		echo "<td>".$record['modifier']."</td>\n";
		echo "<td class=\"right\">".count($record['reply'])."</td>\n";
		echo "</tr>";
	}
}
?>
</tbody></table>


<?php include("project-footer.php"); ?>