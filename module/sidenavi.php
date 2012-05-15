<?php
//チェックボックスのデフォルト値。

$projectSearchChecked = "";
$ticketSearchChecked = "";
$grepChecked = "";
if(isset($_GET['q'])){
	if(isset($_GET['projectSearch']) && $_GET['projectSearch']) $projectSearchChecked = "checked=\"checked\" ";
	if(isset($_GET['ticketSearch']) && $_GET['ticketSearch'])   $ticketSearchChecked  = "checked=\"checked\" ";
	if(isset($_GET['grep']) && $_GET['grep'])                   $grepChecked          = "checked=\"checked\" ";
}else{
	if($CONFIG['searchProjectSearchDefaultCheck']) $projectSearchChecked = "checked=\"checked\" ";
	if($CONFIG['searchTicketSearchDefaultCheck'])  $ticketSearchChecked  = "checked=\"checked\" ";
	if($CONFIG['searchGrepDefaultCheck'])          $grepChecked          = "checked=\"checked\" ";
}

?>

<div id="search">
<form method="get" action="./">
<input name="q" id="q" value="<?php if(isset($_GET['q'])) echo htmlspecialchars($_GET['q']); ?>" />
<input class="textinput" type="hidden" name="module" value="search" />
<input class="submitbutton" type="submit" value="検索" /><br />
<span id="option-box">
<span title="プロジェクト検索 : プロジェクト名とプロジェクトIDを対象に検索します。">
<input type="checkbox" name="projectSearch" <?php echo $projectSearchChecked; ?>/>Proj</span> 
<span title="チケット検索 : すべてのチケットの内容を検索します。">
<input type="checkbox" name="ticketSearch"  <?php echo $ticketSearchChecked;  ?>/>Tick</span> 
<?php if($CONFIG['searchGrepEnable']){ ?>
<span title="Grep : チケット・プロジェクト掲示板・プロジェクト情報テキストなどをすべて検索します。">
<input type="checkbox" name="grep"          <?php echo $grepChecked;          ?>/>Grep</span> 
<?php } ?>
</span>
</form>
</div>

<?php

echo "<ul id=\"projectlist\">\n";
foreach($cicadaBtsProjectList->projectListTable as $record){
	if(in_array($record['progress'],$CONFIG['projectProgressDisplaySidebar'])){
		echo "<li>";
		echo "<a href=\"?module=project-top&projectId=".$record['projectId']."\">";
		echo $record['projectName']." ";
		echo "</a><br />";
		echo "<small>";
		echo "残".$record['unsolved']."<small>/".$record['total']."</small> ";
		echo "[".$CONFIG['projectProgress'][$record['progress']]."] ";
		echo CicadaBtsUtility::humanReadableDateDiff($record['mtime']);
		echo "</small>";
		echo "</li>\n";
	}
}
echo "</ul>\n";


?>
