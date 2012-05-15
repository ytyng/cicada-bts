<?php include("project-header.php"); ?>

<h3>プロジェクトログ</h3>
<?php
if($cicadaBts->isAdminLogin()){
	if(is_file($cicadaBtsProject->getProjectDataDir()."/".$CONFIG['projectLogFile'])){
		
		$log = file_get_contents($cicadaBtsProject->getProjectDataDir()."/".$CONFIG['projectLogFile']);
		echo "<pre id=\"logviewer\">";
		echo htmlspecialchars($log);
		echo "</pre>\n";
	}else{
		echo "ログファイルが存在しません。\n";
	}
}else{
	echo "権限がありません。\n";
}

?>

<?php include("project-footer.php"); ?>
