<?php include("project-header.php"); ?>

<h3>カテゴリリスト</h3>
<?php
echo join(" ",$cicadaBtsProject->makeTicketSearchCategoryLink($cicadaBtsProject->getCategoryTable()));
?>

<?php if(isset($_GET['q']) && $_GET['q']){ ?>

<h3><?php echo htmlspecialchars($_GET['q']); ?> <small>(カテゴリ)</small></h3>
<table class="ticket-list"><tbody>
<tr><th>ID</th><th>件名</th><th>重要度</th><th>状態</th><th>カテゴリ</th><th>更新日時</th><th>起票者</th><th>更新者</th><th>数</th></tr>
<?php
foreach( $cicadaBtsProject->getTicketTable() as $ticketId => $record){
	if(in_array($_GET['q'],$record['categoryAry'])){
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

<?php }else{ ?>
検索ワードを指定してください。
<?php } ?>

<?php include("project-footer.php"); ?>
