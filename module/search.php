<?php


echo "<h2>".htmlspecialchars($_GET['q'])." <small>(検索結果)</small></h2>\n";

$aryQ = CicadaBtsUtility::splitQueryWord($_GET['q']);

//print_r($aryQ);
if(count($aryQ)){
	
	
	if(isset($_GET['projectSearch']) && $_GET['projectSearch']){
		$searchStartTime = microtime(true);
		echo "<h3>プロジェクト名との一致</h3>\n";
		echo "<div class=\"search-results\">\n";
		$result = $cicadaBtsProjectList->searchProject($aryQ);
		if(count($result)){
			echo "<ul>\n";
			foreach($result as $record){
				echo "<li>";
				echo "<h4><a href=\"?module=project-top&projectId=".$record['projectId']."\">";
				echo $record['projectName']." ";
				echo "</a></h4>\n";
				echo "<dl class=\"stats\">";
				echo "<nobr><dt>未解決チケット</dt><dd>".$record['unsolved']."</dd></nobr>\n";
				echo "<nobr><dt>全チケット</dt><dd>".$record['total']."</dd></nobr>\n";
				echo "<nobr><dt>進捗</dt><dd>".$CONFIG['projectProgress'][$record['progress']]."</dd></nobr>\n";
				echo "<nobr><dt>更新日</dt><dd>".CicadaBtsUtility::humanReadableDate($record['mtime'])."</dd></nobr>\n";
				echo "</dl>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		}else{
			echo "結果なし";
		}
		
		echo "<div class=\"search-time\">";
		echo "検索時間:".(int)((microtime(true)*1000)-($searchStartTime*1000))."ms";
		echo "</div>\n";
		echo "</div>\n";
		
	}
	
	if(isset($_GET['ticketSearch']) && $_GET['ticketSearch']){
		$searchStartTime = microtime(true);
		
		echo "<h3>チケット内容との一致</h3>\n";
		echo "<div class=\"search-results\">\n";
		$result = $cicadaBtsProjectList->searchTicket($aryQ);
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
				echo "<nobr><dt>プロジェクト</dt><dd>".$record['projectName']."</dd></nobr>\n";
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
		echo "<div class=\"search-time\">";
		echo "検索時間:".(int)((microtime(true)*1000)-($searchStartTime*1000))."ms";
		echo "</div>\n";
		echo "</div>\n";
		
	}
	
	if(isset($_GET['grep']) && $_GET['grep'] && $CONFIG['searchGrepEnable']){
		$searchStartTime = microtime(true);
		
		echo "<h3>Grep</h3>\n";
		echo "<div class=\"search-results\">\n";
		$grepCommand = 'grep -i "'.$aryQ[0].'" '.$CONFIG['projectDataDir'].'/*/*';
		
		$a = shell_exec($grepCommand);
		//echo "<pre>";
		//echo $grepCommand."\n";
		//var_dump($a);
		//echo htmlspecialchars($a);
		$result = CicadaBtsUtility::parseGrepResult($a,array_slice($aryQ,1));
		if(count($result)){
			echo "<ul>\n";
			foreach($result as $record){
				echo "<li>";
				echo "<h4>";
				echo "<a href=\"".$record['url']."\">".$record['subject']."</a>";
				echo "</h4>\n";
				echo "<dl class=\"stats\">\n";
				echo "<nobr><dt>プロジェクト</dt><dd>".$record['projectName']."</dd></nobr>\n";
				echo "<nobr><dt>種類</dt><dd>".$record['type']."</dd></nobr>\n";
				echo "<nobr><dt>記入者</dt><dd>".$record['userName']."</dd></nobr>\n";
				if($record['timeStamp']){
					echo "<nobr><dt>更新日</dt><dd>".CicadaBtsUtility::humanReadableDate($record['timeStamp'])."</dd></nobr>\n";
				}
				echo "<p class=\"bodyText\">".$record['bodyText']."</p>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		}else{
			echo "結果なし";
		}
		echo "<div class=\"search-time\">";
		echo "検索時間:".(int)((microtime(true)*1000)-($searchStartTime*1000))."ms";
		echo "</div>\n\n";
		echo "</div>\n\n";
	}

}else{
	echo "検索ワードを指定してください。";
}
?>
