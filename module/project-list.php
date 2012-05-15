<h2>全プロジェクトリスト</h2>

<div class="main-header-links">
<a href="./">トップページ</a>
</div>

<table class="ticket-list" style="border:0;"><tbody>
<?php
foreach($cicadaBtsProjectList->getProgressCategorizedProjectTable() as $progress => $projectList){
	
	if(!count($projectList)) continue;
	
	echo "<tr><td colspan=\"9\" class=\"caption\">";
	echo $CONFIG['projectProgress'][$progress];
	echo "</td></tr>\n";
	
	echo "<tr><th>プロジェクト名</th><th>作成日時</th><th>更新日時</th>";
	echo "<th>全チケット</th><th>未解決チケット</th><th>検証未解決</th><th>開発未解決</th></tr>\n";
	foreach( $projectList as $projectId => $record){
		echo "<tr>";
		//echo "<td class=\"right\">".$projectId."</td>\n";
		echo "<td>";
		echo "<a href=\"./?module=project-top&projectId=".$projectId."\" >";
		echo $record['projectName'];
		echo "</a>";
		echo "</td>";
		//echo "<td>".$CONFIG['ticketSeverity'][$record['severity']][0]."</td>\n";
		//echo "<td>".$CONFIG['ticketStatus'][$record['status']][0]."</td>\n";
		//echo "<td>".join(" ",$cicadaBtsProject->makeTicketSearchCategoryLink($record['categoryAry']))."</td>\n";
		echo "<td>".CicadaBtsUtility::humanReadableDate($record['ctime'])."</td>\n";
		echo "<td>".CicadaBtsUtility::humanReadableDateDiff($record['mtime'])."</td>\n";
		echo "<td class=\"right\">".$record['total']."</td>\n";
		echo "<td class=\"right\">".$record['unsolved']."</td>\n";
		echo "<td class=\"right\">".$record['unsolvedForTest']."</td>\n";
		echo "<td class=\"right\">".$record['unsolvedForDevelop']."</td>\n";
		echo "</tr>";
	}
	
}


?>
</tbody></table>