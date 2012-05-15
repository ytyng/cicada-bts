<?php include("project-header.php"); ?>

<h3>全チケットリスト</h3>

<table class="ticket-list" style="border:0;"><tbody>
<?php
foreach($cicadaBtsProject->getStatusCategorizedTicketTable() as $status => $ticketList){
	
	if(!count($ticketList)) continue;
	
	echo "<tr><td colspan=\"9\" class=\"caption\">";
	echo $CONFIG['ticketStatus'][$status][0];
	echo "</td></tr>\n";
	
	echo "<tr><th>ID</th><th>件名</th><th>重要度</th><th>状態</th><th>カテゴリ</th><th>更新日時</th><th>起票者</th><th>更新者</th><th>数</th></tr>\n";
	foreach( $ticketList as $ticketId => $record){
		echo "<tr>";
		echo "<td class=\"right\">".$ticketId."</td>\n";
		echo "<td>";
		echo "<a href=\"./?module=ticket&projectId=".$cicadaBtsProject->getProjectId()."&ticketId=".$ticketId."\" >";
		echo $record['subject'];
		echo "</a>";
		echo "</td>";
		echo "<td>".$CONFIG['ticketSeverity'][$record['severity']][0]."</td>\n";
		echo "<td>".$CONFIG['ticketStatus'][$record['status']][0]."</td>\n";
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
