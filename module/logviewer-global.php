<h2>グローバルログ</h2>


<?php
if($cicadaBts->isAdminLogin()){
	if(is_file($CONFIG['globalDataDir']."/".$CONFIG['globalLogFile'])){
		
		$log = file_get_contents($CONFIG['globalDataDir']."/".$CONFIG['globalLogFile']);
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
