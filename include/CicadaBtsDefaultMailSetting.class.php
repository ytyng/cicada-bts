<?php

/*
================================================================================
 CicadaBts デフォルトメール設定クラス
--------------------------------------------------------------------------------
*/

class CicadaBtsDefaultMailSetting{
	
	private $CONFIG;
	private $defaultMailSettingFile; //デフォルトメール送信先設定
	
	/**
	 * コンストラクタ
	 */
	public function __construct(&$CONFIG){
		
		$this->CONFIG = &$CONFIG;
		
		//デフォルトメール送信先設定が無ければ作る
		$this->defaultMailSettingFile = $this->CONFIG['globalDataDir'].DIRECTORY_SEPARATOR.$this->CONFIG['defaultMailSettingFile'];
		if(!is_file($this->defaultMailSettingFile)) touch($this->defaultMailSettingFile);
		
		$this->loadMailSetting();
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
	
	
	
	//==============================================================================================
	// メール送信設定
	//----------------------------------------------------------------------------------------------
	/*
	[メール設定ファイル]
	Toアドレス(カンマ区切り)\n
	Ccアドレス(カンマ区切り)\n
	Bccアドレス(カンマ区切り)\n
	
	*/
	
	private $mailAddressTo     = "";
	private $mailAddressCc     = "";
	private $mailAddressBcc    = "";
	
	
	
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
		
		CicadaBtsUtility::setMessageDisplayNext("デフォルトメール設定を保存しました");
		CicadaBtsRedirector::setUrl("./?module=default-mail-setting");
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
		
		if($this->CONFIG['demoMode']){
			$this->debug(__METHOD__,"デモモードのため、ファイルへの書き込みは行いません");
		}else{
			file_put_contents($this->defaultMailSettingFile,$buffer);
		}
		
	}
	
	/**
	 * メール設定を読み込み
	 */
	private function loadMailSetting(){
		$this->debug(__METHOD__);
		$buffer = file($this->defaultMailSettingFile);
		if(isset($buffer[0])) $this->mailAddressTo  = trim($buffer[0]);
		if(isset($buffer[1])) $this->mailAddressCc  = trim($buffer[1]);
		if(isset($buffer[2])) $this->mailAddressBcc = trim($buffer[2]);
	}
	

}

?>
