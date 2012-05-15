<?php

/*
リダイレクション先URLを保持するクラス
*/

class CicadaBtsRedirector{
	protected static $url;
	protected static $debugMode;
	
	public static function setUrl($url){
		CicadaBtsRedirector::$url = $url;
	}
	
	public static function setDebugMode($debugMode){
		CicadaBtsRedirector::$debugMode = $debugMode;
	}
	
	public static function executeRedirection(){
		if(CicadaBtsRedirector::$url){
			if(CicadaBtsRedirector::$debugMode){
				echo "Redirection abort. Because debugmode. ";
				echo "URL=".CicadaBtsRedirector::$url."<br />";
			}else{
				header("Location: ".CicadaBtsRedirector::$url);
				die();
			}
		}
	}
}

?>