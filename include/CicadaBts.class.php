<?php
/*
================================================================================
 cicadaBts 基本クラス
--------------------------------------------------------------------------------

*/

class CicadaBts{

	/**
	 * クラス変数
	 */
	
	private $debugMessage = "";
	private $CONFIG;
	
	//public $projectList; //プロジェクトリストクラスのインスタンス
	
	private $siteTitle;
	
	private $notifyMessage = array();
	//2次元配列。$notifyMessage[][0]=メッセージ本文、$notifyMessage[][1]=レベル。
	//レベルは INFO,WARN,ERROR。(どのレベルでもユーザーに必ず通知されるが、表示スタイルが異なる)
	
	private $adminLogin = false;
	private $loggingUserId = "";
	
	private $breadcrumb = array(); //ぱんくずリスト。2次元配列。
	//レコードは、 'url' 'name' をキーとする連想配列。
	
	
	//==============================================================================================
	// グローバル
	//----------------------------------------------------------------------------------------------
	
	/**
	 * コンストラクタ
	 */
	function __construct(&$CONFIG){
		$this->debug(__METHOD__,"");
		$this->CONFIG = &$CONFIG;
		
		$this->siteTitle = $this->CONFIG['siteTitle'];
		
		//セッション開始
		session_name($this->CONFIG['sessionCookieName']);
		session_start();
		
		//管理者ログイン状態を取得
		if(
			isset($_SESSION[$CONFIG['sessionApplicationName']]['login']) &&
			$_SESSION[$CONFIG['sessionApplicationName']]['login'] &&
			isset($_SESSION[$CONFIG['sessionApplicationName']]['userId'])
		){
			$this->adminLogin=true;
			$this->loggingUserId=$_SESSION[$CONFIG['sessionApplicationName']]['userId'];
		}
	
	}
	
	/**
	 * デストラクタ
	 */
	function __destruct(){
		
	}
	/**
	 * デバッグ情報書き込み
	 */
	private function debug($method,$message = ""){
		$this->debugMessage .= "[".$method."] ".$message."\n";
	}
	
	/**
	 * デバッグ情報取得
	 */
	public function getDebugMessage(){
		return $this->debugMessage;
	}
	
	/**
	 * サイト名を取得
	 */
	public function getSiteTitle(){
		return $this->siteTitle;
	}
	/**
	 * サイト名を設定
	 */
	public function setSiteTitle($str){
		$this->siteTitle = $str;
	}
	
	/**
	 * 通知メッセージを登録
	 */
	public function setNotifyMessage($message,$level=""){
		$this->debug(__METHOD__,$message.",".$level);
		$this->notifyMessage[]=array($message,$level);
	}
	
	/**
	 * 通知メッセージ配列を返却
	 */
	public function getNotifyMessage(){
		return $this->notifyMessage;
	}
	
	/**
	 * ログに記載
	 * 主に、ファイル変更などが発生した際に記載する。
	 * 変更内容までは記載しない。
	 */
	public function writeLog($str){
		if($this->CONFIG['globalLogEnable']){
			$buffer  = date('Y-m-d H:i:s',time()) . $this->CONFIG['fieldSeparator'];
			$buffer .= $_SERVER['REMOTE_ADDR']    . $this->CONFIG['fieldSeparator'];
			//$buffer .= $this->getSavedUserName()  . $this->CONFIG['fieldSeparator'];
			$buffer .= $str                       . $this->CONFIG['fieldSeparator'];
			$buffer .= $this->CONFIG['lineSeparator'];
			$fh = fopen($this->CONFIG['globalDataDir'].DIRECTORY_SEPARATOR.$this->CONFIG['globalLogFile'],'a');
			fwrite($fh,$buffer);
			fclose($fh);
		}
	}
	
	
	//==============================================================================================
	// ログイン
	//----------------------------------------------------------------------------------------------
	
	/**
	 * 管理者でログインしているか
	 */
	public function isAdminLogin(){
		return $this->adminLogin;
	}
	
	
	/**
	 * 管理者ログインを試みる
	 */
	public function login($id,$password){
		$this->debug(__METHOD__,$id.",".$password);
		if(!$id){
			$this->setNotifyMessage("ログインIDを入力してください。","WARN");
			return;
		}
		if(!$password){
			$this->setNotifyMessage("パスワードを入力してください。","WARN");
			return;
		}
		if(
			isset($this->CONFIG['adminAccount'][$id]) &&
			$this->CONFIG['adminAccount'][$id] === $this->makePasswordHash($password)
		){
			//IDとパスワードが一致したためログイン
			
			$_SESSION[$this->CONFIG['sessionApplicationName']]['login'] = 1;
			$_SESSION[$this->CONFIG['sessionApplicationName']]['userId']  = $id;
			$this->adminLogin=true;
			$this->loggingUserId=$id;
			
			//CicadaBtsUtility::setMessageDisplayNext("ログインしました");
			$this->writeLog("管理者ログイン成功 ".$id);
			CicadaBtsRedirector::setUrl("./");
			
		}else{
			$this->setNotifyMessage("ログインIDもしくはパスワードが違います。","WARN");
			return;
		}
		
	}
	/**
	 * 管理者ログアウト
	 */
	public function logout(){
		unset($_SESSION[$this->CONFIG['sessionApplicationName']]['login']);
		unset($_SESSION[$this->CONFIG['sessionApplicationName']]['userId']);
		$this->adminLogin=false;
		$this->loggingUserId="";
		CicadaBtsUtility::setMessageDisplayNext("ログアウトしました");
		CicadaBtsRedirector::setUrl("./");
	}
	
	/**
	 * パスワードハッシュを作成
	 */
	private function makePasswordHash($str){
		return sha1($this->CONFIG['adminPasswordSalt'].$str);
	}
	
	/**
	 * ログインユーザーIdを取得
	 */
	public function getLoggingUserId(){
		return $this->loggingUserId;
	}
	
	//==============================================================================================
	// セッションユーザー名
	//----------------------------------------------------------------------------------------------
	/**
	 * セッションに保存してあるユーザー名を取得
	 */
	public function getSavedUserName(){
		if(isset($_SESSION[$this->CONFIG['sessionApplicationName']]['userName'])){
			return $_SESSION[$this->CONFIG['sessionApplicationName']]['userName'];
		}else{
			return "";
		}
	}
	/**
	 * セッションにユーザー名を保存
	 */
	public function setSavedUserName($userName){
		$_SESSION[$this->CONFIG['sessionApplicationName']]['userName'] = $userName;
		/*
		if($redirection){
			if($this->CONFIG['debugMode']){
				$this->debug(__METHOD__,"No redirection. Because debugmode is true. Check config file.");
			}else{
				header("Location: ./");
				die();
			}
		}
		*/
	}

	//==============================================================================================
	// パンくず
	//----------------------------------------------------------------------------------------------
	
	/**
	 * パンくずセット
	 * $nameはhtml実体参照化していれること!
	 */
	public function addBreadcrumb($name,$url=""){
		$this->breadcrumb[] = array(
			'name' => $name,
			'url'  => $url,
		);
	}
	
	/**
	 * パンくずHTMLを作って返す
	 * nameはhtml実体参照しないので注意!
	 */
	public function getBreadcrumbHtml(){
		$buffer = "";
		if(count($this->breadcrumb)){
			$buffer .= "<ul id=\"breadcrumb\">\n";
			foreach($this->breadcrumb as $record){
				$buffer .= "<li>";
				$buffer .= " &gt; "; //cssでできれば楽なのだが
				if($record['url']){
					$buffer .= "<a href=\"".htmlspecialchars($record['url'])."\">";
					
					//$buffer .= $record['name'];
					$buffer .= mb_strimwidth(
						$record['name'],
						0,
						$this->CONFIG['breadcrumbLength'],
						"..."
					);
					$buffer .= "</a>";
				}else{
					$buffer .= $record['name'];
				}
				$buffer .= "</li>\n";
			}
			$buffer .= "</ul>\n";
		}
		return $buffer;
	}


}



?>
