<?php
/*
CicadaBtsのプロジェクトのクラス


プロジェクト掲示板のファイルフォーマット
seqId userName subject etime bodyText messageId
0     1        2       3     4        5
追記のみ可能。

チケットファイルのフォーマット
seqId ticketId parent username subject ticketSeverity ticketStatus etime bodytext messageId attach category
0     1        2      3        4       5              6            7     8        9         10     11
初めて出現したticketIdを親とみなせばよいので、parentフィールドは実質不要かもしれない。

*/
class CicadaBtsProject{
	
	/**
	 * クラス変数
	 */
	
	private $debugMessage     = "";
	private $CONFIG;
	
	private $notifyMessage    = array();
	
	private $projectId;
	private $projectName;
	private $progress;
	private $ctime;
	private $mtime;
	private $total;
	private $unsolved;
	
	private $projectDataDir;
	
	private $projectInformation = "";
	
	private $projectBbsTable = array();
	private $ticketTable = array();
	
	private $projectBbsMaxId   = 0;
	private $ticketMaxSeqId    = 0;
	private $ticketMaxTicketId = 0;
	
	/**
	 * コンストラクタ
	 */
	
	public function __construct(&$CONFIG){
		$this->debug(__METHOD__,"");
		$this->CONFIG = &$CONFIG;
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
	 */
	public function writeLog($str){
		if($this->CONFIG['projectLogEnable']){
			$buffer  = date('Y-m-d H:i:s',time()) . $this->CONFIG['fieldSeparator'];
			$buffer .= $_SERVER['REMOTE_ADDR']    . $this->CONFIG['fieldSeparator'];
			$buffer .= $str                       . $this->CONFIG['fieldSeparator'];
			$buffer .= $this->CONFIG['lineSeparator'];
			$fh = fopen($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectLogFile'],'a');
			fwrite($fh,$buffer);
			fclose($fh);
		}
	}
	
	/**
	 * プロジェクトレコードを飲み込む
	 */
	public function setProjectRecord($projectRecord){
		//$this->debug(__METHOD__,join(",",$projectRecord));
		$this->debug(__METHOD__);
		$this->projectId          = $projectRecord['projectId'];
		$this->projectName        = $projectRecord['projectName'];
		$this->progress           = $projectRecord['progress'];
		$this->ctime              = $projectRecord['ctime'];
		$this->mtime              = $projectRecord['mtime'];
		$this->total              = $projectRecord['total'];
		$this->unsolved           = $projectRecord['unsolved'];
		$this->unsolvedForTest    = $projectRecord['unsolvedForTest'];
		$this->unsolvedForDevelop = $projectRecord['unsolvedForDevelop'];
		
		$this->projectDataDir = $this->CONFIG['projectDataDir'].DIRECTORY_SEPARATOR.$this->projectId;
		if(!is_dir($this->projectDataDir)) die("[DIE] プロジェクトデータディレクトリがありません");
		$this->loadFile();
		
	}
	
	
	/**
	 * プロジェクトIDを取得
	 */
	public function getProjectId(){
		return $this->projectId;
	}
	
	/**
	 * プロジェクト名を取得
	 */
	public function getProjectName(){
		return $this->projectName;
	}
	
	/**
	 * プロジェクト進捗コードを取得
	 */
	public function getProgress(){
		return $this->progress;
	}
	
	/**
	 * 作成日時を取得
	 */
	public function getCtime(){
		return $this->ctime;
	}
	
	/**
	 * 更新日時を取得
	 */
	public function getMtime(){
		return $this->mtime;
	}
	/**
	 * 合計レコード数を取得
	 */
	public function getTotal(){
		return $this->total;
	}
	/**
	 * 未解決レコード数を取得
	 */
	public function getUnsolved(){
		return $this->unsolved;
	}
	/**
	 * 検証向け未解決レコード数を取得
	 */
	public function getUnsolvedForTest(){
		return $this->unsolvedForTest;
	}
	/**
	 * 開発向け未解決レコード数を取得
	 */
	public function getUnsolvedForDevelop(){
		return $this->unsolvedForDevelop;
	}
	
	/**
	 * プロジェクトデータディレクトリを取得
	 */
	public function getProjectDataDir(){
		return $this->projectDataDir;
	}
	/**
	 * 添付ファイルディレクトリを取得
	 * データディレクトリが絶対パスで指定されているとうまく動作しないかもしれないので注意。
	 */
	public function getAttachDir(){
		return $this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['attachDir'];
	}
	/**
	 * ファイルを読み込み、クラス配列に格納
	 */
	private function loadFile(){
		$this->debug(__METHOD__);
		
		//プロジェクト情報テキスト
		$this->projectInformation = file_get_contents($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectInformationFile']);
		
		//チケット
		$this->readTicketTable();
		
		//メール送信設定
		$this->loadMailSetting();
	}
	
	
	//==============================================================================================
	// プロジェクト情報テキスト
	//----------------------------------------------------------------------------------------------
	
	/**
	 * projectInformationを返す
	 */
	public function getProjectInformation(){
		return $this->projectInformation;
	}
	
	/**
	 * projectInformationを保存
	 */
	public function writeProjectInformation($projectInformation){
		$this->debug(__METHOD__);
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、ファイルへの書き込みは行いません");
		}else{
			file_put_contents($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectInformationFile'],$projectInformation);
		}
		CicadaBtsUtility::setMessageDisplayNext("プロジェクト情報テキストを保存しました。");
		CicadaBtsRedirector::setUrl("./?module=project-top&projectId=".$this->projectId);
		$this->writeLog("プロジェクト情報テキストを更新");
	}
	
	//==============================================================================================
	// プロジェクト掲示板
	//----------------------------------------------------------------------------------------------
	
	//メールのメッセージIDの参照先。
	private $projectBbsMailMessageIdReference = "";
	
	/**
	 * プロジェクト掲示板に書き込み
	 */
	public function writeProjectBbs($bodyText,$userName,$subject){
		$this->debug(__METHOD__);
		if(!count($this->projectBbsTable)){
			//プロジェクト掲示板が読み込まれていないようだったら読み込みを行う。
			$this->readProjectBbsTable();
		}
		
		$bodyText = trim($bodyText);
		$userName = trim($userName);
		$subject  = trim($subject);
		if(!$bodyText){
			$this->setNotifyMessage("本文を入力してください。","WARN");
		}
		if(!$userName){
			$this->setNotifyMessage("記入者を入力してください。","WARN");
		}
		
		if($this->CONFIG['demoMode']){
			$this->setNotifyMessage("デモモードのため処理を中止します。","WARN");
		}
		
		if(count($this->getNotifyMessage())) return;
		
		$seqId = $this->projectBbsMaxId+1;
		$messageId = $this->generateMailMessageId($this->projectId."-BBS-".$seqId);
		
		//if(!$userName) $userName  = $this->CONFIG['projectBbsNoUserName'];
		if(!$subject)  $subject   = $this->CONFIG['projectBbsNoSubject'];
		$buffer  = $seqId                                 .$this->CONFIG['fieldSeparator'];
		$buffer .= CicadaBtsUtility::sanitizeToSave($userName).$this->CONFIG['fieldSeparator'];
		$buffer .= CicadaBtsUtility::sanitizeToSave($subject) .$this->CONFIG['fieldSeparator'];
		$buffer .= time()                                 .$this->CONFIG['fieldSeparator'];
		$buffer .= CicadaBtsUtility::sanitizeToSave($bodyText).$this->CONFIG['fieldSeparator'];
		$buffer .= $messageId                             .$this->CONFIG['fieldSeparator'];
		$buffer .= $this->CONFIG['lineSeparator'];
		
		$fh = fopen($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectBbsFile'],'a');
		fwrite($fh,$buffer);
		fclose($fh);
		
		//メール送信を行う
		if($this->projectBbsMailMessageIdReference){
			//掲示板の1件目のメッセージIDが入る
			$references = $this->projectBbsMailMessageIdReference;
		}else if($this->projectRootMailMessageId){
			//無ければ、プロジェクトルートメールのメッセージIDを使う。
			$references = $this->projectRootMailMessageId;
		}else{
			$references = "";
		}
		$mailSubject   = $this->CONFIG['mailSubjectPrefix']."(BBS) ".$subject." - ".htmlspecialchars_decode($this->projectName);
		$mailBodyText  = $this->CONFIG['mailBodyTextHeader'];
		$mailBodyText .= "記入者 : ".$userName."\r\n";
		$mailBodyText .= "件名   : ".$subject."\r\n";
		$mailBodyText .= $this->CONFIG['mailBodyTextHr'];
		$mailBodyText .= $bodyText."\r\n";
		$mailBodyText .= $this->CONFIG['mailUrlRoot']."?module=project-bbs&projectId=".$this->projectId;
		
		//メールを送信。
		//この関数は config.php で定義している。
		cicadaBtsSendMail(
			$this->mailAddressTo,
			$this->mailAddressCc,
			$this->mailAddressBcc,
			$messageId,
			$references,
			$mailSubject,
			$mailBodyText
		);
		
		CicadaBtsUtility::setMessageDisplayNext("プロジェクト掲示板に登録しました。");
		CicadaBtsRedirector::setUrl("./?module=project-bbs&projectId=".$this->projectId);
		$this->writeLog("プロジェクト掲示板に登録 ".$userName." ".$subject);
	}
	
	/**
	 * プロジェクト掲示板を読み込んで連想配列に格納
	 */
	private function readProjectBbsTable(){
		$this->debug(__METHOD__);
		$records = file($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['projectBbsFile']);
		foreach($records as $record){
			$cells = explode($this->CONFIG['fieldSeparator'],$record);
			//list関数でもいいかも
			if($cells[0]){
				$this->projectBbsTable[] = array(
					'seqId'    => $cells[0],
					'userName' => $cells[1],
					'subject'  => $cells[2],
					'etime'    => $cells[3],
					'bodyText' => $cells[4],
					'messageId'=> $cells[5],
				);
				
				//最ID更新
				if($this->projectBbsMaxId < $cells[0]){
					$this->projectBbsMaxId = $cells[0];
				}
				
				//メッセージIDの参照元を更新。
				if($this->projectBbsMailMessageIdReference){
					//もし入っていたら更新しない。
				}else{
					//入ってなければ入れる。掲示板の1件目のメッセージIDが入る。
					$this->projectBbsMailMessageIdReference = $cells[5];
				}
			}
		}
	}
	
	/**
	 * プロジェクト掲示板の連想配列を取得
	 */
	public function getProjectBbsTable(){
		$this->debug(__METHOD__);
		if(!count($this->projectBbsTable)){
			//プロジェクト掲示板が読み込まれていないようだったら読み込みを行う。
			$this->readProjectBbsTable();
		}
		return $this->projectBbsTable;
	}
	
	//==============================================================================================
	// チケット
	//----------------------------------------------------------------------------------------------
	/**
	 * チケットを保存
	 */
	public function writeTicket($ticketId,$userName,$subject,$category,$severity,$status,$assign,$bodyText){
		//$ticketId がfalseの場合は、新規チケット発行。
		$this->debug(__METHOD__);
		
		$userName = trim($userName);
		$subject  = trim($subject);
		$bodyText = trim($bodyText);
		
		if(!$bodyText){
			$this->setNotifyMessage("本文を入力してください。","WARN");
		}
		if(!$userName){
			$this->setNotifyMessage("記入者を入力してください。","WARN");
		}
		if(!$subject){
			$this->setNotifyMessage("件名を入力してください。","WARN");
		}
		if(!isset($this->CONFIG['ticketSeverity'][$severity])){
			$this->setNotifyMessage("重要度が不正。","ERROR");
		}
		if(!isset($this->CONFIG['ticketStatus'][$status])){
			$this->setNotifyMessage("ステータスが不正。","ERROR");
		}
		
		if($this->CONFIG['demoMode']){
			$this->setNotifyMessage("デモモードのため処理を中止します。","WARN");
		}
		
		
		//print_r($_FILES['attach']);die(); //debug
		
		//ファイルアップロード
		$attachFileName ="";
		if(
			(! $this->CONFIG['demoMode']) &&
			isset($_FILES['attach']['name']) &&
			$_FILES['attach']['name'] 
		){
			switch($_FILES['attach']['error']){
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->setNotifyMessage("添付ファイルサイズエラーです。","WARN");
				break;
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				$this->setNotifyMessage("ファイルアップロードに失敗しました。","WARN");
				break;
			#default:
			case UPLOAD_ERR_OK:
				//アップロードエラーなし
				if(isset($_POST['enableFilenameHash']) && $_POST['enableFilenameHash']){
					//ファイル名を自動生成
					$attachFileName = time();
					$this->debug(__METHOD__,"ファイル名を自動生成 ".$attachFileName);
					//拡張子を付加
					if(preg_match('/\\.\\w+$/',$_FILES['attach']['name'],$result)){
						$attachFileName .= $result[0];
						$this->debug(__METHOD__,"拡張子を追加 ".$result[0]." -> ".$attachFileName);
					}
				}else{
					//アップロードファイル名をそのまま利用
					$attachFileName = $_FILES['attach']['name'];
					$this->debug(__METHOD__,"ファイル名をそのまま利用 ".$attachFileName);
				}
				
				//ファイル名(拡張子)のチェック
				//ファイルタイプも判断すべきかもしれない。
				if(substr($attachFileName,0,1) == "."){
					//ドット始まりのファイルは拒否
					$this->setNotifyMessage("禁止されているファイル名です。(Dot)","WARN");
					break;
				}
				//ブラックリストしか存在しない場合
				$isHit = false;
				foreach($this->CONFIG['uploadDenyNeedle'] as $needle){
					if(stripos($attachFileName,$needle) !== FALSE){
						$isHit = true; //１回でもヒットしたらNG
						break;
					}
				}
				if($isHit){
					$this->setNotifyMessage("禁止されているファイル名です。(DenyNeedle)","WARN");
					break;
				}
				if(isset($this->CONFIG['uploadAllowExtention']) && count($this->CONFIG['uploadAllowExtention'])){
					//ホワイトリストが存在する場合
					$isHit = false;
					foreach($this->CONFIG['uploadAllowExtention'] as $extention){
						if(CicadaBtsUtility::fileExtentionMatch($attachFileName,$extention)){
							$isHit = true; //１回でもヒットしたらOK
							break;
						}
					}
					if(!$isHit){
						$this->setNotifyMessage("禁止されているファイルタイプです。(Not allow)","WARN");
						break;
					}
					unset($isHit);
				}
				
				//システムエンコードされたファイル名
				$attachFileNameEnc = mb_convert_encoding(
					$attachFileName,
					$this->CONFIG['fileSystemEncoding'],
					mb_internal_encoding()
				);
				
				if(isset($_POST['enableFileOverwrite']) && $_POST['enableFileOverwrite']){
					//上書き許可が設定されている。pass
					$this->debug(__METHOD__,"上書き許可モード");
				}else{
					//上書き許可が設定されていなければ、既存ファイルをチェック
					if(is_file($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['attachDir'].DIRECTORY_SEPARATOR.$attachFileNameEnc)){
						//ファイルが存在する場合は末尾に連番をつける?
						$this->debug(__METHOD__,"上書き禁止モード 同名のファイルが既に存在するため、アップロードを中止しました。");
						$this->setNotifyMessage("同名のファイルが既に存在するため、アップロードを中止しました。","WARN");
						break;
					}else{
						$this->debug(__METHOD__,"上書き禁止モード だが、重複ファイル名なし");
					}
				}
				
				//デモモードかどうかの判断は上で既に行っている
				if(move_uploaded_file(
					$_FILES['attach']['tmp_name'],
					$this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['attachDir'].DIRECTORY_SEPARATOR.$attachFileNameEnc
				)){
					//アップロード完了。問題なし。
				}else{
					$this->setNotifyMessage("ファイルアップロードに失敗しました。(2)","WARN");
				}
				break;
			default:
				$this->setNotifyMessage("ファイルアップロードに失敗しました。エラーコード[".$_FILES['attach']['error']."]","WARN");
				break;
			}
			
		}
		
		if(count($this->getNotifyMessage())) return;
		
		$seqId = $this->ticketMaxSeqId +1;
		
		if($ticketId){
			//リプライ
			$parent   = $ticketId;
			//$nextUrl = "./?module=ticket&projectId=".$this->projectId."&ticketId=".$ticketId;
			$references = $this->ticketTable[$ticketId]['messageId'];
		}else{
			//新規。親。
			$parent   = 0;
			//$nextUrl = "./?module=project-top&projectId=".$this->projectId;
			$references = $this->projectRootMailMessageId;
			$ticketId = $this->ticketMaxTicketId +1;
		}
		
		$nextUrl = "?module=ticket&projectId=".$this->projectId."&ticketId=".$ticketId;
		$messageId = $this->generateMailMessageId($this->projectId."-".$ticketId."-".$seqId);
		
		$buffer  = $seqId                                 .$this->CONFIG['fieldSeparator']; // 0
		$buffer .= $ticketId                              .$this->CONFIG['fieldSeparator']; // 1
		$buffer .= $parent                                .$this->CONFIG['fieldSeparator']; // 2
		$buffer .= CicadaBtsUtility::sanitizeToSave($userName).$this->CONFIG['fieldSeparator']; // 3
		$buffer .= CicadaBtsUtility::sanitizeToSave($subject) .$this->CONFIG['fieldSeparator']; // 4
		$buffer .= $severity                              .$this->CONFIG['fieldSeparator']; // 5
		$buffer .= $status                                .$this->CONFIG['fieldSeparator']; // 6
		$buffer .= $assign                                .$this->CONFIG['fieldSeparator']; // 7
		$buffer .= time()                                 .$this->CONFIG['fieldSeparator']; // 8
		$buffer .= CicadaBtsUtility::sanitizeToSave($bodyText).$this->CONFIG['fieldSeparator']; // 9
		$buffer .= $messageId                             .$this->CONFIG['fieldSeparator']; //10
		$buffer .= $attachFileName                        .$this->CONFIG['fieldSeparator']; //11
		$buffer .= CicadaBtsUtility::sanitizeToSave($category).$this->CONFIG['fieldSeparator']; //12
		$buffer .= $this->CONFIG['lineSeparator'];
		
		//CicadaBtsProjectList#searchTicket() でもこのフィールド番号使っているので注意。
		
		//デモモード判断は既に行っている
		$fh = fopen($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['ticketFile'],'a');
		fwrite($fh,$buffer);
		fclose($fh);
		
		//projectListの総件数をアップデートする
		$this->readTicketTable(); //ファイルを読み込みなおし、件数を再生成
		
		global $cicadaBtsProjectList; //強引だが、グローバルのインスタンスを直接操作する
		$cicadaBtsProjectList->updateProjectCount(
			$this->projectId,$this->total,
			$this->unsolved,$this->unsolvedForTest,$this->unsolvedForDevelop
		);
		
		
		
		//
		// 新規カテゴリが発見された場合、ここで自動的にカテゴリリストに追加すべきか?
		// それとも、何もしなくて良いのか。
		//
		
		//メール送信を行う
		$mailSubject   = $this->CONFIG['mailSubjectPrefix'].$subject." - ".htmlspecialchars_decode($this->projectName);
		$mailBodyText  = $this->CONFIG['mailBodyTextHeader'];
		$mailBodyText .= "記入者 : ".$userName."\r\n";
		$mailBodyText .= "件名   : ".$subject."\r\n";
		$mailBodyText .= "重要度 : ".$this->getTicketSeverityText($severity)."\r\n";
		$mailBodyText .= "状態   : ".$this->getTicketStatusText($status)."\r\n";
		$mailBodyText .= $this->CONFIG['mailBodyTextHr'];
		$mailBodyText .= $bodyText."\r\n";
		
		$mailBodyText .= $this->CONFIG['mailUrlRoot'].$nextUrl;
		
		//メールを送信。
		//この関数は config.php で定義している。
		cicadaBtsSendMail(
			$this->mailAddressTo,
			$this->mailAddressCc,
			$this->mailAddressBcc,
			$messageId,
			$references,
			$mailSubject,
			$mailBodyText
		);
		
		CicadaBtsUtility::setMessageDisplayNext("チケットを登録しました。");
		CicadaBtsRedirector::setUrl("./".$nextUrl);
		$this->writeLog("チケットを登録 ".$userName." ".$subject);
	}
	
	/**
	 * チケットファイルを読み込み
	 */
	private function readTicketTable(){
		$this->debug(__METHOD__);
		
		//初期化
		$this->total              = 0;
		$this->unsolved           = 0;
		$this->unsolvedForTest    = 0;
		$this->unsolvedForDevelop = 0;
		$this->ticketTable        = array();
		$this->ticketMaxSeqId     = 0;
		$this->ticketMaxTicketId  = 0;
		
		$records = file($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['ticketFile']);
		foreach($records as $record){
			$cells = explode($this->CONFIG['fieldSeparator'],$record);
			
			//ticketTable
			//list関数でもいいかも
			if($cells[0]){
				$record = array(
					'seqId'    => &$cells[0],
					'ticketId' => &$cells[1],
					'parent'   => &$cells[2],
					'userName' => &$cells[3],
					'subject'  => &$cells[4],
					'severity' => &$cells[5],
					'status'   => &$cells[6],
					'assign'   => &$cells[7],
					'etime'    => &$cells[8],
					'bodyText' => &$cells[9],
					'messageId'=> &$cells[10],
					'attach'   => &$cells[11],
					'category' => &$cells[12],
				);
				//CicadaBtsProjectList#searchTicket() でもこのフィールド番号使っているので注意。
				
				
				//$parent が0だと、そのレコードはチケットの親である。
				//0以外は、親のチケットIDを表す。
				//→やっぱりやめ。新しいticketIdが出てきたら親とする。
				if(!isset($this->ticketTable[$record['ticketId']])){
					//親の場合は、まずレコードを定義
					$this->ticketTable[$record['ticketId']] = array(
						'ticketId'    => $record['ticketId'],
						'subject'     => $record['subject'],
						'severity'    => $record['severity'],
						'status'      => $record['status'],
						'assign'      => $record['assign'],
						'reporter'    => $record['userName'],
						'modifier'    => "",
						'ctime'       => $record['etime'],
						'mtime'       => $record['etime'],
						'messageId'   => $record['messageId'],
						//'unsolved'    => $this->isUnsolvedStatus($record['status']),
						'category'    => $record['category'], 
						'categoryAry' => $this->splitCategory($record['category']), 
						'reply'       => array(),
					);
					$this->total++;
				}else{
					//子の場合は、親レコードを上書きする
					$this->ticketTable[$record['ticketId']]['subject']  = $record['subject'];
					$this->ticketTable[$record['ticketId']]['severity'] = $record['severity'];
					$this->ticketTable[$record['ticketId']]['status']   = $record['status'];
					$this->ticketTable[$record['ticketId']]['assign']   = $record['assign'];
					$this->ticketTable[$record['ticketId']]['modifier'] = $record['userName'];
					$this->ticketTable[$record['ticketId']]['mtime']    = $record['etime'];
					//$this->ticketTable[$record['ticketId']]['unsolved'] = $this->isUnsolvedStatus($record['status']);
					if($this->ticketTable[$record['ticketId']]['category'] != $record['category' ]){
						//preg_splitするので、パフォーマンスを考慮し、変更されているときだけ処理する
						$this->ticketTable[$record['ticketId']]['category']    = $record['category'];
						$this->ticketTable[$record['ticketId']]['categoryAry'] = $this->splitCategory($record['category']);
					}
				
				}
				
				//子でも親でも、チケットテーブルの'reply'に追加する。
				$this->ticketTable[$record['ticketId']]['reply'][] = $record;
				
				//SeqId最大値を更新
				if($this->ticketMaxSeqId < $record['seqId']){
					$this->ticketMaxSeqId = $record['seqId'];
				}
				//TicketId最大値を更新
				if($this->ticketMaxTicketId < $record['ticketId']){
					$this->ticketMaxTicketId = $record['ticketId'];
				}
				
			}
		}
		
		//未解決を集計
		foreach($this->ticketTable as $record){
			//if($record['unsolved']){
			//	$this->unsolved++;
			//}
			if(in_array($record['status'],$this->CONFIG['ticketStatusUnsolved'])){
				$this->unsolved++;
			}
			if(in_array($record['status'],$this->CONFIG['ticketStatusUnsolvedForTest'])){
				$this->unsolvedForTest++;
			}
			if(in_array($record['status'],$this->CONFIG['ticketStatusUnsolvedForDevelop'])){
				$this->unsolvedForDevelop++;
			}
		}
	}
	
	/**
	 * statusが未解決かどうか判断する
	 */
	/*
	private function isUnsolvedStatus($status){
		return !in_array($status,$this->CONFIG['ticketStatusSolved']);
	}
	*/
	
	/**
	 * チケットの連想配列を取得
	 */
	public function getTicketTable(){
		$this->debug(__METHOD__);
		if(!count($this->ticketTable)){
			//チケットが読み込まれていないようだったら読み込みを行う。
			$this->readTicketTable();
		}
		return $this->ticketTable;
	}
	
	/**
	 * チケット1つを取得。チケットIDを指定する。
	 */
	public function getTicket($ticketId){
		$this->debug(__METHOD__);
		if(!count($this->ticketTable)){
			//チケットが読み込まれていないようだったら読み込みを行う。
			$this->readTicketTable();
		}
		
		if(isset($this->ticketTable[$ticketId])){
			return $this->ticketTable[$ticketId];
		}else{
			die("[DIE] No ticket.");
		}
	}
	
	/**
	 * ステータスでカテゴライズされたチケットテーブルを作成して返す
	 */
	public function getStatusCategorizedTicketTable(){
		$this->debug(__METHOD__);
		
		$buffer = array();
		
		//カテゴリごとの空の配列を作成
		foreach($this->CONFIG['ticketStatus'] as $key => $value){
			$buffer[$key] = array();
		}
		
		if(!count($this->ticketTable)){
			//チケットが読み込まれていないようだったら読み込みを行う。
			$this->readTicketTable();
		}
		
		//振り分け
		foreach($this->ticketTable as $record){
			$buffer[$record['status']][$record['ticketId']] = $record;
		}
		
		return $buffer;
	}
	
	/**
	 * 重要度のテキスト文字を取得。
	 * 未定義なら「未定義」を返す。
	 */
	public function getTicketSeverityText($severity){
		if(isset($this->CONFIG['ticketSeverity'][$severity])){
			return $this->CONFIG['ticketSeverity'][$severity][0];
		}else{
			return "未定義";
		}
	}
	
	/**
	 * 状態のテキスト文字を取得。
	 * 未定義なら「未定義」を返す。
	 */
	public function getTicketStatusText($status){
		if(isset($this->CONFIG['ticketStatus'][$status])){
			return $this->CONFIG['ticketStatus'][$status][0];
		}else{
			return "未定義";
		}
	}
	
	
	/**
	 * カテゴリ文字列を分割して配列にする
	 */
	private function splitCategory($strCategory){
		return preg_split("/[,\s]+/",$strCategory);
	}
	
	//==============================================================================================
	// メール送信設定
	//----------------------------------------------------------------------------------------------
	/*
	[メール設定ファイル]
	Toアドレス(カンマ区切り)\n
	Ccアドレス(カンマ区切り)\n
	Bccアドレス(カンマ区切り)\n
	ルートメールメッセージID
	
	*/
	
	private $mailAddressTo     = "";
	private $mailAddressCc     = "";
	private $mailAddressBcc    = "";
	private $projectRootMailMessageId = "";
	
	/**
	 * メールアドレスを取得
	 */
	public function getMailAddressTo(){
		return $this->mailAddressTo;
	}
	public function getMailAddressCc(){
		return $this->mailAddressCc;
	}
	public function getMailAddressBcc(){
		return $this->mailAddressBcc;
	}
	
	/**
	 * メールアドレスを改行区切りで取得
	 */
	public function getMailAddressToNl(){
		return str_replace(",","\n",$this->mailAddressTo);
	}
	public function getMailAddressCcNl(){
		return str_replace(",","\n",$this->mailAddressCc);
	}
	public function getMailAddressBccNl(){
		return str_replace(",","\n",$this->mailAddressBcc);
	}
	
	/**
	 * ルートメールメッセージIDを取得
	 */
	public function getProjectRootMailMessageId(){
		return $this->projectRootMailMessageId;
	}
	
	/**
	 * メール設定をクラス変数へ保存
	 */
	public function writeMailSetting($mailAddressTo,$mailAddressCc,$mailAddressBcc){
		$this->debug(__METHOD__);
		$ary = CicadaBtsUtility::splitMailAddress($mailAddressTo);
		$this->mailAddressTo = join(",",$ary);
		$ary = CicadaBtsUtility::splitMailAddress($mailAddressCc);
		$this->mailAddressCc = join(",",$ary);
		$ary = CicadaBtsUtility::splitMailAddress($mailAddressBcc);
		$this->mailAddressBcc = join(",",$ary);
		
		$this->saveMailSetting();
		
		CicadaBtsUtility::setMessageDisplayNext("メール設定を保存しました");
		CicadaBtsRedirector::setUrl("./?module=mail-setting&projectId=".$this->projectId);
		$this->writeLog("メール設定を保存");
	}
	
	/**
	 * メール設定をファイルへ保存
	 */
	private function saveMailSetting(){
		$this->debug(__METHOD__);
		$buffer = "";
		$buffer .= $this->mailAddressTo .$this->CONFIG['lineSeparator'];
		$buffer .= $this->mailAddressCc .$this->CONFIG['lineSeparator'];
		$buffer .= $this->mailAddressBcc.$this->CONFIG['lineSeparator'];
		$buffer .= $this->projectRootMailMessageId.$this->CONFIG['lineSeparator'];
		
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、ファイルへの書き込みは行いません");
		}else{
			file_put_contents($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['mailSettingFile'],$buffer);
		}
		
	}
	
	/**
	 * メール設定を読み込み
	 */
	private function loadMailSetting(){
		$this->debug(__METHOD__);
		$buffer = file($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['mailSettingFile']);
		if(isset($buffer[0])) $this->mailAddressTo  = trim($buffer[0]);
		if(isset($buffer[1])) $this->mailAddressCc  = trim($buffer[1]);
		if(isset($buffer[2])) $this->mailAddressBcc = trim($buffer[2]);
		if(isset($buffer[3])) $this->projectRootMailMessageId = trim($buffer[3]);
	}
	
	/**
	 * プロジェクトルートメールを送信
	 */
	public function sendProjectRootMail(){
		$messageId = $this->generateMailMessageId($this->projectId."-ROOT");
		$this->debug(__METHOD__,$messageId);
		
		$subject   = $this->CONFIG['mailSubjectPrefix'].htmlspecialchars_decode($this->projectName);
		$bodyText  = $this->CONFIG['mailBodyTextHeader'];
		$bodyText .= "プロジェクトが作成されました。\r\n";
		$bodyText .= "プロジェクト名: ".htmlspecialchars_decode($this->projectName)."\r\n";
		$bodyText .= $this->CONFIG['mailUrlRoot']."?module=project-top&projectId=".$this->projectId."\r\n";
		
		//メールを送信。
		//この関数は config.php で定義している。
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、メール送信は行いません");
		}else{
			cicadaBtsSendMail(
				$this->mailAddressTo,
				$this->mailAddressCc,
				$this->mailAddressBcc,
				$messageId,
				"",
				$subject,
				$bodyText
			);
		}
		$this->projectRootMailMessageId = $messageId;
		$this->saveMailSetting();
		
		CicadaBtsUtility::setMessageDisplayNext("プロジェクトルートメールを送信しました。");
		CicadaBtsRedirector::setUrl("./?module=mail-setting&projectId=".$this->projectId);
		$this->writeLog("プロジェクトルートメールを送信");
	}
	
	/**
	 * メールのメッセージIDを作成
	 * 引数はメッセージIDの中央部
	 */
	private function generateMailMessageId($str){
		$messageId = time()."-".substr(md5(uniqid()),10,12)."-".$str.$this->CONFIG['mailMessageIdSuffix'];
		return $messageId;
	}
	
	//==============================================================================================
	// カテゴリリスト
	//----------------------------------------------------------------------------------------------
	
	private $categoryTable = array();
	/**
	 * カテゴリリストを読み込み
	 */
	private function loadCategoryList(){
		$this->debug(__METHOD__);
		if(!is_file($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['categoryListFile'])){
			touch($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['categoryListFile']);
		}
		$this->categoryTable = $this->splitCategoryListText(
			file_get_contents($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['categoryListFile'])
		);
	}
	
	/**
	 * カテゴリリスト文字列を分割して配列で返す
	 */
	private function splitCategoryListText($str){
		$this->debug(__METHOD__);
		$bulkArray = explode($this->CONFIG['lineSeparator'],$str);
		$categoryListArray = array();
		foreach($bulkArray as $category){
			$category = trim($category);
			if($category && !in_array($category,$categoryListArray)){
				$categoryListArray[] = $category;
			}
		}
		return $categoryListArray;
	}
	
	/**
	 * カテゴリリストを配列で取得
	 */
	public function getCategoryTable(){
		$this->debug(__METHOD__);
		if(!count($this->categoryTable)){
			$this->loadCategoryList();
		}
		return $this->categoryTable;
	}
	
	/**
	 * カテゴリリストをテキストで取得
	 */
	public function getCategoryListText(){
		$this->debug(__METHOD__);
		if(!count($this->categoryTable)){
			$this->loadCategoryList();
		}
		return join("\n",$this->categoryTable);
	}
	
	/**
	 * カテゴリリストをJavaScript配列で取得
	 */
	public function getCategoryListJsArray(){
		$this->debug(__METHOD__);
		if(!count($this->categoryTable)){
			$this->loadCategoryList();
		}
		$aryTmp = array();
		foreach($this->categoryTable as $category){
			$aryTmp[] = "'".addslashes($category)."'";
		}
		$buffer = "new Array(".join(',',$aryTmp).")";
		return $buffer;
	}
	
	/**
	 * カテゴリリストをテキストで保存
	 */
	public function writeCategoryListText($str){
		$this->debug(__METHOD__);
		$this->categoryTable = $this->splitCategoryListText($str);
		$this->saveCategoryList();
		
		CicadaBtsUtility::setMessageDisplayNext("カテゴリリストを保存しました。");
		CicadaBtsRedirector::setUrl("./?module=edit-category-list&projectId=".$this->projectId);
		$this->writeLog("カテゴリリストを保存");
	
	}
	
	/**
	 * カテゴリリストに追加
	 */
	private function addCategory($str){
		
	}
	
	/**
	 * ファイルへ保存
	 */
	private function saveCategoryList(){
		$this->debug(__METHOD__);
		$buffer = join($this->CONFIG['lineSeparator'],$this->categoryTable);
		
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、ファイルへの書き込みは行いません");
		}else{
			file_put_contents($this->projectDataDir.DIRECTORY_SEPARATOR.$this->CONFIG['categoryListFile'],$buffer);
		}
	}
	
	/**
	 * カテゴリ配列のうち、カテゴリリストに存在するもののみ抽出して文字列にする。
	 * チケットリプライフォームで呼ばれる
	 *
	 * 戻り値: 存在するもの配列 , 消されたもの配列 の配列。list()で取得すること。
	 */
	public function getCategoryTxtInCategoryListOnly($aryCategory){
		$this->debug(__METHOD__);
		if(!count($this->categoryTable)){
			$this->loadCategoryList();
		}
		$aryExists = array();
		$aryFired  = array();
		foreach($aryCategory as $category){
			$category = trim($category);
			if(!$category) continue;
			if(in_array($category,$this->categoryTable)){
				$aryExists[] = $category; //存在するシリーズ
			}else{
				$aryFired[]  = $category; //消されたシリーズ
			}
		}
		return array($aryExists,$aryFired);
	}
	
	
	/**
	 * htmlのアンカータグを付与したカテゴリのリストの *配列* を返却
	 */
	public function makeTicketSearchCategoryLink($categoryAry){
		$buffer = array();
		foreach($categoryAry as $category){
			if(!$category) continue;
			$buffer[] = "<a href=\"./?module=ticket-search-category&projectId=".$this->projectId."&q=".$category."\">".
				$category."</a>";
		}
		return $buffer;
	}
}

?>
