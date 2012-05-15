<?php
/*

ディレクトリパーミッションなどをセルフテストするクラス

*/

class CicadaBtsSelfTest{
	
	private static $CONFIG  = array();
	
	/**
	 * テスト実施
	 * @return list($isError,$errorMessageList)
	 */
	public static function executeTest(){
		
		global $CONFIG;
		self::$CONFIG  = &$CONFIG;
		
		define('REQUIRE_VERSION_MAJOR',5);
		define('REQUIRE_VERSION_MINOR',2);
		
		$isError = false;
		$errorMessageList = array();
		
		$phpVersionList = explode(".",PHP_VERSION);
		if($phpVersionList[0]>=REQUIRE_VERSION_MAJOR && $phpVersionList[1]>=REQUIRE_VERSION_MINOR){
			//OK
		}else{
			$isError = true;
			$errorMessageList[] = "PHPバージョン ".REQUIRE_VERSION_MAJOR.".".REQUIRE_VERSION_MINOR."以上が必要です。(現バージョン:".PHP_VERSION.")";
		}
		
		if(is_dir(self::$CONFIG['globalDataDir'])){
			if(is_writable(self::$CONFIG['globalDataDir'])){
				//OK
			}else{
				$isError = true;
				$errorMessageList[] = "グローバルデータ保存ディレクトリ(".self::$CONFIG['globalDataDir'].")への書き込み権限がありません。ディレクトリへの書き込み権限を設定してください。";
			}
		}else{
			$isError = true;
			$errorMessageList[] = "グローバルデータ保存ディレクトリ(".self::$CONFIG['globalDataDir'].")が存在しません。設定ファイル(config/config.php)を確認してください。";
		}
		
		if(is_dir(self::$CONFIG['projectDataDir'])){
			if(is_writable(self::$CONFIG['projectDataDir'])){
				//OK
			}else{
				$isError = true;
				$errorMessageList[] = "プロジェクトデータ保存ディレクトリ(".self::$CONFIG['projectDataDir'].")への書き込み権限がありません。ディレクトリへの書き込み権限を設定してください。";
			}
		}else{
			$isError = true;
			$errorMessageList[] = "プロジェクトデータ保存ディレクトリ(".self::$CONFIG['projectDataDir'].")が存在しません。設定ファイル(config/config.php)を確認してください。";
		}
		
		
		return array($isError,$errorMessageList);

	}
	
	/**
	 * テストを実施し、エラーだったら停止
	 */
	public static function stopOrContinue(){
		
		list($isError,$errorMessageList) = self::executeTest();
		if($isError){
			header("Content-Type: text/html; charset=".BASE_ENCODING);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			
			$baseEncoding = BASE_ENCODING;
			$message = implode("<br />\n",$errorMessageList);
			echo <<<__HERE__
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=${baseEncoding}" /> 
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<title>ERROR</title>
</head>
<body>
${message}
</body>
</html>
__HERE__;
			exit();
		}
	}
}

?>
