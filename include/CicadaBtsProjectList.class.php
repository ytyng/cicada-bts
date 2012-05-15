<?php

/*
================================================================================
 CicadaBts プロジェクトリストクラス
--------------------------------------------------------------------------------


[ projectListTable ]
プロジェクトID プロジェクト名 進捗度   作成タイムスタンプ 更新タイムスタンプ 全報告数 未解決数 検証向け未解決  開発向け未解決
projectId      projectName    progress ctime              mtime              total    unsolved unsolvedForTest unsolvedForDevelop
0              1              2        3                  4                  5        6        7               8

・プロジェクト名には実態参照化された文字が入る。

連想配列キーはプロジェクトID

*/
class CicadaBtsProjectList{
	
	/**
	 * クラス変数
	 */
	
	private $debugMessage = "";
	private $CONFIG;
	
	private $notifyMessage = array();
	
	public  $projectListTable = array();
	
	private $projectListFile; //プロジェクトリストファイル名;
	
	/**
	 * コンストラクタ
	 */
	public function __construct(&$CONFIG){
		$this->debug(__METHOD__,"");
		$this->CONFIG = &$CONFIG;
		
		//プロジェクトリストが無ければ作る
		$this->projectListFile = $this->CONFIG['globalDataDir'].DIRECTORY_SEPARATOR.$this->CONFIG['projectListFile'];
		if(!is_file($this->projectListFile)) touch($this->projectListFile);
		
		$this->loadFile();
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
	 * ログに記載
	 * 主に、ファイル変更などが発生した際に記載する。
	 */
	public function writeLog($str){
		if($this->CONFIG['globalLogEnable']){
			$buffer  = date('Y-m-d H:i:s',time()) . $this->CONFIG['fieldSeparator'];
			$buffer .= $_SERVER['REMOTE_ADDR']    . $this->CONFIG['fieldSeparator'];
			$buffer .= $str                       . $this->CONFIG['fieldSeparator'];
			$buffer .= $this->CONFIG['lineSeparator'];
			$fh = fopen($this->CONFIG['globalDataDir'].DIRECTORY_SEPARATOR.$this->CONFIG['globalLogFile'],'a');
			fwrite($fh,$buffer);
			fclose($fh);
		}
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
	 * 新規プロジェクト作成
	 */
	public function makeNewProject($projectId,$projectName){
		$this->debug(__METHOD__,$projectId.",".$projectName);
		if(!$projectId){
			$this->setNotifyMessage("プロジェクトIDを指定してください。","WARN");
			return;
		}
		if(!CicadaBtsUtility::isSafeNameForSystem($projectId)){
			$this->setNotifyMessage("プロジェクト名は半角英数字のみで構成してください。","WARN");
			return;
		}
		if(isset($this->projectListTable[$projectId])){
			$this->setNotifyMessage("同じプロジェクトIDが既に存在します。","WARN");
			return;
		}
		
		if($this->CONFIG['demoMode']){
			$this->setNotifyMessage("デモモードのため処理を中止します。","INFO");
			return;
		}
		
		if(!$projectName){
			$projectName = $projectId;
		}
		
		$this->projectListTable[$projectId] = array(
			'projectId'          => $projectId,
			'projectName'        => CicadaBtsUtility::sanitizeToSave($projectName),
			'progress'           => $this->CONFIG['defaultProgress'],
			'ctime'              => time(),
			'mtime'              => time(),
			'total'              => 0,
			'unsolved'           => 0,
			'unsolvedForTest'    => 0,
			'unsolvedForDevelop' => 0,
		);
		
		//プロジェクト用ディレクトリを作成
		//実際にアクセスする瞬間に作るのが正解だろうか？
		$projectDir = $this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$projectId;
		if(!is_dir($projectDir)){
			mkdir($projectDir);
			mkdir($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['attachDir']);
		}
		touch($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectInformationFile']);
		touch($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectBbsFile']);
		touch($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['ticketFile']);
		touch($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['mailSettingFile']);
		touch($projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['categoryListFile']);
		//ログファイルはアクセスする直前に作る
		
		$this->saveFile();
		CicadaBtsUtility::setMessageDisplayNext("プロジェクトを作成しました。");
		CicadaBtsRedirector::setUrl("./?module=edit-project&projectId=".$projectId);
		
		$this->writeLog("新規プロジェクト作成 ".$projectId.",".$projectName);
	}
	
	/**
	 * プロジェクト削除
	 */
	public function deleteProject($projectId,$confirmProjectId,$deleteDirectory=false){
		$this->debug(__METHOD__,$projectId.",".$confirmProjectId.",".$deleteDirectory);
		
		if($confirmProjectId==""){
			$this->setNotifyMessage("確認用のプロジェクトIDを入力してください。","WARN");
			return;
		}
		
		if($projectId != $confirmProjectId){
			$this->setNotifyMessage("確認用のプロジェクトIDが一致しません。","WARN");
			return;
		}
		
		if(!isset($this->projectListTable[$projectId])){
			$this->setNotifyMessage("プロジェクトが存在しません。","WARN");
			return;
		}
		
		if($this->CONFIG['demoMode']){
			$this->setNotifyMessage("デモモードのため処理を中止します。","INFO");
			return;
		}
		
		unset($this->projectListTable[$projectId]);
		$this->saveFile();
		
		if($deleteDirectory){
			$this->debug(__METHOD__,"Directory delete mode");
			$projectDir  = $this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$projectId;
			$attachDir   = $projectDir.DIRECTORY_SEPARATOR.$this->CONFIG['attachDir'];
			$attachFiles = scandir($attachDir);
			foreach($attachFiles as $file){
				if($file == ".")  continue;
				if($file == "..") continue;
				$delFile = $attachDir .DIRECTORY_SEPARATOR. $file;
				$this->debug(__METHOD__,"unlink:".$delFile);
				unlink($delFile);
			}
			rmdir($attachDir);
			$this->debug(__METHOD__,"rmdir:".$attachDir);
			foreach(scandir($projectDir) as $file){
				if($file == ".")  continue;
				if($file == "..") continue;
				$delFile = $projectDir .DIRECTORY_SEPARATOR. $file;
				$this->debug(__METHOD__,"unlink:".$delFile);
				unlink($delFile);
			}
			rmdir($projectDir);
			$this->debug(__METHOD__,"rmdir:".$projectDir);
			
			CicadaBtsUtility::setMessageDisplayNext("ディレクトリごと、プロジェクトを削除しました。 ");
			CicadaBtsRedirector::setUrl("./");
			$this->writeLog("ディレクトリごとプロジェクト削除 ".$projectId);
			
		}else{
			
			CicadaBtsUtility::setMessageDisplayNext("プロジェクトを削除しました。 ");
			CicadaBtsRedirector::setUrl("./");
			$this->writeLog("プロジェクト削除 ".$projectId);
		}
	}
	
	/**
	 * プロジェクトID変更
	 */
	public function changeProjectId($oldProjectId,$newProjectId){
		$this->debug(__METHOD__,$oldProjectId.",".$newProjectId);
		
		if($newProjectId==""){
			$this->setNotifyMessage("新しいプロジェクトIDを入力してください。","WARN");
			return;
		}
		
		if(!isset($this->projectListTable[$oldProjectId])){
			$this->setNotifyMessage("プロジェクトが存在しません。","WARN");
			return;
		}
		
		if(!CicadaBtsUtility::isSafeNameForSystem($newProjectId)){
			$this->setNotifyMessage("プロジェクト名は半角英数字のみで構成してください。","WARN");
			return;
		}
		
		if(isset($this->projectListTable[$newProjectId])){
			$this->setNotifyMessage("新しいプロジェクトIDが既に存在します。","WARN");
			return;
		}
		
		$oldProjectDir  = $this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$oldProjectId;
		$newProjectDir  = $this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$newProjectId;
		
		$this->debug(__METHOD__,"oldProjectDir=".$oldProjectDir.",newProjectDir=".$newProjectDir);
		
		if(is_dir($newProjectDir)){
			$this->setNotifyMessage("新しいプロジェクトIDのディレクトリが既に存在します。","WARN");
			return;
		}
		
		if($this->CONFIG['demoMode']){
			$this->setNotifyMessage("デモモードのため処理を中止します。","INFO");
			return;
		}
		
		//ディレクトリ名の変更
		rename($oldProjectDir,$newProjectDir);
		
		//レコードの登録
		$this->projectListTable[$oldProjectId]['projectId'] = $newProjectId;
		$this->projectListTable[$newProjectId] = $this->projectListTable[$oldProjectId]; //必要ないけど一応
		unset($this->projectListTable[$oldProjectId]); //必要ないけど一応
		$this->saveFile();
		
		CicadaBtsUtility::setMessageDisplayNext("プロジェクトIDを変更しました。 ");
		CicadaBtsRedirector::setUrl("./");
		$this->writeLog("プロジェクトID変更 ".$oldProjectId." → ".$newProjectId);
		
	}
	
	/**
	 * プロジェクトファイル保存
	 */
	private function saveFile(){
		$this->debug(__METHOD__);
		
		$this->sortProjectList('mtime'); //保存時ソート
		
		$buffer="";
		foreach($this->projectListTable as $record){
			//もし、(設定ファイルが編集されるなどして) 進捗コードが未定義になった場合、
			//デフォルト値(0)に戻す
			/*読み込み時に行うようにしたので処理不要とする
			if(!isset($this->CONFIG['projectProgress'][$record['progress']])){
				$this->debug(__METHOD__,"Reset progress.".$record['projectId'].",".$record['progress']);
				$record['progress'] = $this->CONFIG['defaultProgress'];
			}
			*/
			
			$buffer .= $record['projectId']         .$this->CONFIG['fieldSeparator']; // 0
			$buffer .= $record['projectName']       .$this->CONFIG['fieldSeparator']; // 1
			$buffer .= $record['progress']          .$this->CONFIG['fieldSeparator']; // 2
			$buffer .= $record['ctime']             .$this->CONFIG['fieldSeparator']; // 3
			$buffer .= $record['mtime']             .$this->CONFIG['fieldSeparator']; // 4
			$buffer .= $record['total']             .$this->CONFIG['fieldSeparator']; // 5
			$buffer .= $record['unsolved']          .$this->CONFIG['fieldSeparator']; // 6
			$buffer .= $record['unsolvedForTest']   .$this->CONFIG['fieldSeparator']; // 7
			$buffer .= $record['unsolvedForDevelop'].$this->CONFIG['fieldSeparator']; // 8
			$buffer .= $this->CONFIG['lineSeparator'];
		}
		
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、ファイルへの書き込みは行いません");
		}else{
			file_put_contents($this->projectListFile,$buffer);
		}
	}
	
	/**
	 * プロジェクトファイル読み込み
	 */
	private function loadFile(){
		$this->debug(__METHOD__);
		$records = file($this->projectListFile);
		foreach($records as $record){
			$cells = explode($this->CONFIG['fieldSeparator'],$record);
			if($cells[0]){
				if(isset($this->CONFIG['projectProgress'][$cells[2]])){
					$progress = $cells[2];
				}else{
					$progress = $this->CONFIG['defaultProgress'];
				}
				
				$this->projectListTable[$cells[0]] = array(
					'projectId'          => $cells[0],
					'projectName'        => $cells[1],
					'progress'           => $progress,
					'ctime'              => $cells[3],
					'mtime'              => $cells[4],
					'total'              => $cells[5],
					'unsolved'           => $cells[6],
					'unsolvedForTest'    => $cells[7],
					'unsolvedForDevelop' => $cells[8],
				);
			}
		}
		
	}
	
	/**
	 * プロジェクト一覧をソート
	 * sortFileList(ソートキー,descにするか否か)
	 */
	private $sortProjectListOrder;
	private $sortProjectListDesc;
	public function sortProjectList($order="mtime",$desc=false){
		$this->debug(__METHOD__,"");
		$this->sortProjectListOrder=$order;
		$this->sortProjectListDesc=$desc;
		usort($this->projectListTable,array($this, "sortProjectListCompare"));
		
	}
	
	/**
	 * ソート用比較関数
	 */
	private function sortProjectListCompare($a, $b){
		if(isset($a[$this->sortProjectListOrder]) && isset($b[$this->sortProjectListOrder])){
			
			if($this->sortProjectListDesc){ //ソート方向を決める
				$direction =-1;
			}else{
				$direction =1;
			}
			
			if(is_string($a[$this->sortProjectListOrder])){
				return $direction * strnatcasecmp($b[$this->sortProjectListOrder],$a[$this->sortProjectListOrder]);
			}else{
				if($a[$this->sortProjectListOrder]==$b[$this->sortProjectListOrder]){
					return 0;
				}else if($a[$this->sortProjectListOrder]<$b[$this->sortProjectListOrder]){
					return  1 * $direction;
				}else{
					return -1 * $direction;
				}
			}
		}
		$this->writeDebug(__METHOD__,"No exist order key. order=".$this->sortProjectListOrder);
		die();
	}
	
	/**
	 * 単一レコードを取得
	 */
	public function getRecord($dictionaryKey){
		if(!isset($this->projectListTable[$dictionaryKey])) die("[DIE] No exist record. or check permission.");
		return $this->projectListTable[$dictionaryKey];
	}
	
	/**
	 * プロジェクトレコードを上書き
	 */
	public function writeProject($projectId,$projectName,$progress){
		if(!isset($this->projectListTable[$projectId])){
			$this->setNotifyMessage("そのプロジェクトIDは存在しません。","ERROR");
			return;
		}
		
		$this->projectListTable[$projectId]['projectName'] = CicadaBtsUtility::sanitizeToSave($projectName);
		$this->projectListTable[$projectId]['progress']    = $progress;
		$this->projectListTable[$projectId]['mtime']       = time();
		$this->saveFile();
		
		CicadaBtsUtility::setMessageDisplayNext("プロジェクト設定を保存しました");
		CicadaBtsRedirector::setUrl("./?module=project-top&projectId=".$projectId);
		
		$this->writeLog(
			"プロジェクト設定変更 ".$projectId.",".$projectName.",".$progress.
			"(".$this->CONFIG['projectProgress'][$progress].")"
		);
		
	}
	
	/**
	 * プロジェクトレコードの件数を上書き。projectListから呼ばれる。
	 */
	public function updateProjectCount($projectId,$total,$unsolved,$unsolvedForTest,$unsolvedForDevelop){
		if(!isset($this->projectListTable[$projectId])){
			$this->setNotifyMessage("そのプロジェクトIDは存在しません。","ERROR");
			return;
		}
		$this->projectListTable[$projectId]['unsolved']           = $unsolved;
		$this->projectListTable[$projectId]['unsolvedForTest']    = $unsolvedForTest;
		$this->projectListTable[$projectId]['unsolvedForDevelop'] = $unsolvedForDevelop;
		$this->projectListTable[$projectId]['total']              = $total;
		$this->projectListTable[$projectId]['mtime']              = time();
		$this->saveFile();
		//リダイレクションはしない
		//ログには書かない
	}
	
	/**
	 * プロジェクトレコードを検索する
	 */
	public function searchProject($q){
		$hitRecord = array();
		if(is_array($q)){
			$aryQ = $q;
		}else{
			$aryQ = CicadaBtsUtility::splitQueryWord($q);
		}
		
		foreach($this->projectListTable as $record){
			if(CicadaBtsUtility::andMatch($aryQ,$record['projectName'])){
				$hitRecord[] = $record;
			}else if(CicadaBtsUtility::andMatch($aryQ,$record['projectId'])){
				$hitRecord[] = $record;
			}
			
		}
		return $hitRecord;
	}
	
	/**
	 * チケットを検索する
	 * ファイルを読み込み、1行づつ検索するため、遅い。
	 * 可能ならGrep検索の方がいいと思う。
	 * →そんなことはないかも。これで十分早い。
	 * $projectId が指定された場合は、そのprojectIdのみで行う
	 */
	public function searchTicket($q,$projectId=""){
		$hitRecord = array();
		if(is_array($q)){
			$aryQ = $q;
		}else{
			$aryQ = CicadaBtsUtility::splitQueryWord($q);
		}
		foreach($this->projectListTable as $record){
			//$projectId が指定された場合は、そのprojectIdのみで行う
			if($projectId != "" && $projectId != $record['projectId']) continue;
			//チケットファイルを直接読み込み
			$ticketFileArray = file($this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$record['projectId'].DIRECTORY_SEPARATOR.$this->CONFIG['ticketFile']);
			foreach($ticketFileArray as $line){
				$cells = explode($this->CONFIG['fieldSeparator'],$line);
				if(
					CicadaBtsUtility::andMatch($aryQ,$cells[4]) || //件名との一致
					CicadaBtsUtility::andMatch($aryQ,$cells[9])     //本文との一致
				){
					$record = array(
						'projectId'   => $record['projectId'],
						'projectName' => $record['projectName'],
						'seqId'       => $cells[0],
						'ticketId'    => $cells[1],
						'parent'      => $cells[2],
						'userName'    => $cells[3],
						'subject'     => $cells[4],
						'severity'    => $cells[5],
						'status'      => $cells[6],
						'assign'      => $cells[7],
						'etime'       => $cells[8],
						'bodyText'    => CicadaBtsUtility::makeShortStringSummary($cells[9]),
						'messageId'   => $cells[10],
						'attach'      => $cells[11],
						'category'    => $cells[12],
					);
					$hitRecord[] = $record;
				}
				
			}
			
		}
		return $hitRecord;
	}
	
	/**
	 * 進捗でカテゴライズされたプロジェクトテーブルを作成して返す
	 */
	private $progressCategorizedProjectTable = array(); //キャッシュ
	public function getProgressCategorizedProjectTable(){
		$this->debug(__METHOD__);
		
		if(count($this->progressCategorizedProjectTable)<=0){
			
			//カテゴリごとの空の配列を作成
			foreach($this->CONFIG['projectProgress'] as $key => $value){
				$this->progressCategorizedProjectTable[$key] = array();
			}
			
			//振り分け
			foreach($this->projectListTable as $record){
				$this->progressCategorizedProjectTable[$record['progress']][$record['projectId']] = $record;
			}
		}
		
		return $this->progressCategorizedProjectTable;
	}
	
}



?>
