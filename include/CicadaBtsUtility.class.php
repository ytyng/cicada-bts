<?php
/*
================================================================================
 CicadaBts ユーティリティクラス
--------------------------------------------------------------------------------
*/

class CicadaBtsUtility{
	/**
	 * TSVファイル保存用に文字列を変換する。
	 * タブ除去など。
	 */
	public static function sanitizeToSave($str){
		$str = str_replace("\t"," ",$str);
		$str = htmlspecialchars($str);
		$str = nl2br($str);
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","",$str);
		return $str;
	}
	
	/**
	 * ディレクトリ名として使えるかチェック
	 */
	public static function isSafeNameForSystem($str){
		if(preg_match("/^[\\w\\d_\-]+$/",$str)){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 通知メッセージをHTML出力
	 */
	public static function makeNotifyMessageHtml($notifyMessageArray){
		$buffer="";
		if(count($notifyMessageArray)){
			$buffer.="<ul id=\"notify-message\">\n";
			foreach($notifyMessageArray as $message){
				$buffer.= "<li class=\"".htmlspecialchars($message[1])."\">".htmlspecialchars($message[0])."</li>\n";
			}
			$buffer.="</ul>\n";
		}
		return $buffer;
	}
	
	/**
	 * 人間が読みやすい形に日時を表示
	 */
	public static function humanReadableDate($timeStamp){
		$delta = time() - $timeStamp;
		if($delta <= 0){
			//未来のことは想定しない
			return date("Y-m-d H:i",$timeStamp);
		}else if($delta <= 864000){
			//10日以内は、年を表示しない
			return date("m-d H:i",$timeStamp);
		}else{
			//10日超過は、時刻を表示しない
			return date("Y-m-d",$timeStamp);
		}
	}
	
	/**
	 * 人間が読みやすい形に日時を差分表示
	 */
	public static function humanReadableDateDiff($timeStamp){
		$delta = time() - $timeStamp;
		if($delta < 0){
			//未来のことは想定しない
			return date("Y-m-d H:i",$timeStamp);
		}else if($delta <= 3720){
			//1時間以内は分で表示
			return (int)($delta/60)."分前";
		}else if($delta <= 90000){
			//25時間以内は時で表示
			return (int)($delta/3600)."時間前";
		}else if($delta <= 2592000){
			//30日以内 は日で表示
			return (int)($delta/86400)."日前";
		}else{
			//それ以外
			return date("Y-m-d",$timeStamp);
		}
	}
	
	/**
	 * メールアドレス文字列をカンマなどで分割して、配列にして返す
	 */
	public static function splitMailAddress($mailAddressString){
		$mailAddressArray = preg_split("/[\s;,]+/",$mailAddressString);
		$buffer = array();
		foreach($mailAddressArray as $mailAddress){
			$a = CicadaBtsUtility::getRealAddr($mailAddress);
			if($a){
				$buffer[] = $a;
			}
		}
		
		return $buffer;
	}
	
	/**
	 * メールアドレス取得正規表現
	 */
	public static function getRealAddr($str){
		//$reg = "/([a-z0-9_\-\.]+@[a-z0-9_\-\.]+[a-z]{2,6})/i";
		$reg = "/([a-zA-Z0-9_\-\.]+@[a-z0-9_\-\.]+[a-z]{2,6})/i";
		if(preg_match($reg,$str,$aryResult)){
			return $aryResult[1];
		}else{
			return "";
		}
	}
	
	
	/**
	 * セッション変数に次回表示用メッセージを登録
	 */
	public static function setMessageDisplayNext($str,$sessionApplicationName="CicadaBtsUtility"){
		$_SESSION[$sessionApplicationName]['messageDisplayNext'] = $str;
	}
	
	/**
	 * セッション変数の次回表示用メッセージを取得
	 */
	public static function getMessageDisplayNext($sessionApplicationName="CicadaBtsUtility"){
		$buffer = "";
		if(isset($_SESSION[$sessionApplicationName]['messageDisplayNext'])){
			$buffer = $_SESSION[$sessionApplicationName]['messageDisplayNext']; //1回取得したら
			unset($_SESSION[$sessionApplicationName]['messageDisplayNext']);    //消す
		}
		return $buffer;
	}
	
	/**
	 * 検索ワードを分割し、配列にして返す
	 */
	public static function splitQueryWord($q){
		$q = str_replace("'","",$q);
		$q = str_replace("　"," ",$q);
		$aryQ = preg_split("/[\s;,]+/",$q);
		//return $aryQ;
		//クリーンアップ
		$buffer = array();
		foreach($aryQ as $a){
			if($a){
				$buffer[] = $a;
			}
		}
		return $buffer;
	}
	
	/**
	 * 配列になっている検索ワード全てが文章とマッチするかチェック。
	 */
	public static function andMatch($aryNeedle,$strHaystack){
		foreach($aryNeedle as $needle){
			if(stripos($strHaystack,$needle) === false){
				return false;
			}
		}
		return true;
	}
	
	/**
	 * grep結果をパースする
	 * 引数に文字列からなる配列を入れると、
	 * その値でさらにフィルタリングする。(and検索)
	 */
	public static function parseGrepResult($str,$aryQ=array()){
		global $CONFIG;
		global $cicadaBtsProjectList;
		//print_r($aryQ);
		//$cicadaBtsProjectList->projectListTable[$record['projectId']]['projectName']
		
		$buffer = array();
		$lines = explode("\n",$str);
		foreach($lines as $line){
			$line = trim($line);
			if(!$line) continue;
			//echo "line=".$line."\n";
			list($filePath,$other)=explode(":",$line,2);
			//echo "filePath=".$filePath."\n";
			
			//追加検索文字がセットされている場合は絞り込みを行う
			if(!CicadaBtsUtility::andMatch($aryQ,$other)) continue;
			
			$aryFilePath = explode("/",$filePath);
			$c = count($aryFilePath);
			
			$record = array();
			$record['projectId'] = $aryFilePath[$c-2];
			$record['fileName']  = $aryFilePath[$c-1];
			
			if(isset($cicadaBtsProjectList->projectListTable[$record['projectId']]['projectName'])){
				$record['projectName'] = $cicadaBtsProjectList->projectListTable[$record['projectId']]['projectName'];
			}else{
				$record['projectName'] = "(プロジェクト名なし)";
			}
			
			/*
			$record['subject']
			$record['userName']
			$record['timeStamp']
			$record['bodyText']
			$record['url']
			*/
			$record['type'] = "";
			switch($record['fileName']){
			case $CONFIG['ticketFile']:
				$cell = explode($CONFIG['fieldSeparator'],$other);
				$record['type']     = "チケット";
				$record['subject']  = $cell[4];
				$record['userName'] = $cell[3];
				$record['timeStamp']= $cell[8];
				$record['bodyText'] = CicadaBtsUtility::makeShortStringSummary($cell[9]);
				$record['url']      = "./?module=ticket&projectId=".$record['projectId']."&ticketId=".$cell[1];
				break;
			case $CONFIG['projectBbsFile']:
				$cell = explode($CONFIG['fieldSeparator'],$other);
				$record['type']     = "プロジェクト掲示板";
				$record['subject']  = $cell[2];
				$record['userName'] = $cell[1];
				$record['timeStamp']= $cell[3];
				$record['bodyText'] = CicadaBtsUtility::makeShortStringSummary($cell[4]);
				$record['url']      = "./?module=project-bbs&projectId=".$record['projectId'];
				break;
			case $CONFIG['projectInformationFile']:
				$record['type']     = "プロジェクト情報テキスト";
				//$record['subject']  = $record['projectId'] ." / プロジェクト情報テキスト";
				$record['subject']  = $record['projectName'];
				$record['userName'] = "";
				$record['timeStamp']= "";
				$record['bodyText'] = CicadaBtsUtility::makeShortStringSummary($other);
				$record['url']      = "./?module=project-top&projectId=".$record['projectId'];
				break;
			case $CONFIG['mailSettingFile']:
				$record['type']     = "メール設定ファイル";
				//$record['subject']  = $record['projectId'] ." / メール設定ファイル";
				$record['subject']  = $record['projectName'];
				$record['userName'] = "";
				$record['timeStamp']= "";
				$record['bodyText'] = CicadaBtsUtility::makeShortStringSummary($other);
				$record['url']      = "./?module=project-top&projectId=".$record['projectId'];
				break;
			case $CONFIG['categoryListFile']:
				$record['type']     = "カテゴリリストファイル";
				//$record['subject']  = $record['projectId'] ." / カテゴリリストファイル";
				$record['subject']  = $record['projectName'];
				$record['userName'] = "";
				$record['timeStamp']= "";
				$record['bodyText'] = CicadaBtsUtility::makeShortStringSummary($other);
				$record['url']      = "./?module=project-top&projectId=".$record['projectId'];
				break;
			}
			if($record['type']){
				$buffer[] = $record;
			}
			//echo "fileName=".$record['fileName']."\n";
			//echo "other=".$other."\n";
			
			
		}
		return $buffer;
	}
	
	/**
	 * 検索結果用に文字を切り詰めて表示。
	 */
	public static function makeShortStringSummary($str){
		$str = strip_tags($str);
		$str = htmlspecialchars_decode($str);
		$str = mb_strimwidth($str,0,260,"...");
		$str = str_replace("\r","",$str);
		$str = str_replace("\n"," ",$str);
		$str = htmlspecialchars($str);
		return $str;
	}
	
	/**
	 * 各行を行頭の文字に応じて簡易スタイライズ
	 */
	function stylizeLine($str,$nlMark="\n"){
		$ary = explode($nlMark,$str);
		foreach($ary as $i => $line){
			$c = substr($line,0,1);
			switch($c){
			case "&": //実態参照されている場合
				$c4 = substr($line,0,4);
				switch($c4){
				case "&gt;":
					$ary[$i] = "<span class=\"sl-gt\">".$line."</span>";
					break;
				}
				break;
			case "*":
				$ary[$i] = "<span class=\"sl-asterisk\">".$line."</span>";
				break;
			case ">":
				$ary[$i] = "<span class=\"sl-gt\">".$line."</span>";
				break;
			case "|":
				$ary[$i] = "<span class=\"sl-vl\">".$line."</span>";
				break;
			case "#":
				$ary[$i] = "<span class=\"sl-sharp\">".$line."</span>";
				break;
			case "/":
				$ary[$i] = "<span class=\"sl-slash\">".$line."</span>";
				break;
			case "-":
				$ary[$i] = "<span class=\"sl-minus\">".$line."</span>";
				break;
			case "+":
				$ary[$i] = "<span class=\"sl-plus\">".$line."</span>";
				break;
			default :
				break;
			}
		}
		return join($nlMark,$ary);
	}
	
	/**
	 * URL自動リンクを作成
	 */
	function autoLink($text){
		$text = ereg_replace(
			"(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
			"<a class=\"autolink\" href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
			$text
		);
		return $text;
	}
	
	/**
	 * ファイル拡張子が同じか
	 * ignore case
	 */
	function fileExtentionMatch($filename,$extention){
		if(strlen($filename) == strlen($extention) + stripos($filename,$extention)){
			return true;
		}else{
			return false;
		}
	}
}

?>
