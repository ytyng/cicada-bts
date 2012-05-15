<?php include("project-header.php"); ?>

<h3>プロジェクト内チケット検索</h3>
<form method="get" target="./">
<input type="text" name="q" value="<?php if(isset($_GET['q'])) echo htmlspecialchars($_GET['q']); ?>" />
<input type="submit" value="検索" />
<input type="hidden" name="module" value="ticket-search-word" />
<input type="hidden" name="projectId" value="<?php echo $cicadaBtsProject->getProjectId(); ?>" />
</form>


<?php
if(isset($_GET['q']) && $_GET['q']){
	
	echo "<h3>".htmlspecialchars($_GET['q'])."<small>(フリーワード)</small></h3>\n";
	$result = $cicadaBtsProjectList->searchTicket($_GET['q'],$cicadaBtsProject->getProjectId());
	echo "<div class=\"search-results\">\n";
	if(count($result)){
		echo "<ul>\n";
		foreach($result as $record){
			echo "<li>";
			echo "<h4>";
			echo "<a href=\"?module=ticket&projectId=".$record['projectId']."&ticketId=".$record['ticketId']."\">";
			echo $record['subject']." ";
			echo "</a>";
			echo "</h4>\n";
			echo "<dl class=\"stats\">\n";
			//echo "<nobr><dt>プロジェクト</dt><dd>".$record['projectName']."</dd></nobr>\n";
			echo "<nobr><dt>記入者</dt><dd>".$record['userName']."</dd></nobr>\n";
			if($record['category']){
				echo "<nobr><dt>カテゴリ</dt><dd>".$record['category']."</dd></nobr>\n";
			}
			
			echo "<nobr><dt>重要度</dt><dd>".$cicadaBtsProject->getTicketSeverityText($record['severity'])."</dd></nobr>\n";
			echo "<nobr><dt>状態</dt><dd>".$cicadaBtsProject->getTicketStatusText($record['status'])."</dd></nobr>\n";
			echo "<nobr><dt>記入日</dt><dd>".CicadaBtsUtility::humanReadableDate($record['etime'])."</dd></nobr>\n";
			echo "</dl>";
			echo "<p class=\"bodyText\">".$record['bodyText']."</p>\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
	}else{
		echo "結果なし";
	}
	echo "</div>\n";
}else{
echo "検索ワードを指定してください。";
}
?>

<?php include("project-footer.php"); ?>
