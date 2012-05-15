<?php
$STARTTIME = microtime(true);

require_once("config/config.php");

require_once("include/CicadaBtsSelfTest.class.php");
if(!$_SERVER['QUERY_STRING']){
	//セルフテストを実施
	CicadaBtsSelfTest::stopOrContinue();
};

require_once("include/CicadaBts.class.php");            //常時生成
require_once("include/CicadaBtsProject.class.php");     //常時生成
require_once("include/CicadaBtsProjectList.class.php"); //常時生成
require_once("include/CicadaBtsUtility.class.php");     //static
require_once("include/CicadaBtsRedirector.class.php");  //static
require_once("include/CicadaBtsDefaultMailSetting.class.php"); //使用時生成

$cicadaBts            = new CicadaBts($CONFIG);
$cicadaBtsProjectList = new CicadaBtsProjectList($CONFIG);
$cicadaBtsProject     = new CicadaBtsProject($CONFIG);

//ProjectIdが指定されている場合は、cicadaBtsProjectインスタンスのデータ読み込みメソッド起動
if(isset($_GET['projectId'])){
	$cicadaBtsProject->setProjectRecord($cicadaBtsProjectList->getRecord($_GET['projectId']));
	$cicadaBts->addBreadcrumb(
		$cicadaBtsProject->getProjectName(),
		"./?module=project-top&projectId=".$cicadaBtsProject->getProjectId()
	);
	
	//ticketIdが指定されている場合は、cicadaBtsProjectインスタンスのチケットを読み込み
	if(isset($_GET['ticketId'])){
		$ticket=$cicadaBtsProject->getTicket($_GET['ticketId']);
		$cicadaBts->setSiteTitle(
			$ticket['subject']." - ".$cicadaBtsProject->getProjectName()." - ".$cicadaBts->getSiteTitle()
		);
		$cicadaBts->addBreadcrumb(
			$ticket['subject'],
			"./?module=ticket&projectId=".$cicadaBtsProject->getProjectId()."&ticketId=".$ticket['ticketId']
		);
	}else{
		$cicadaBts->setSiteTitle($cicadaBtsProject->getProjectName()." - ".$cicadaBts->getSiteTitle());
	}
}

//モードディスパッチャ
require("include/mode-dispatcher.php");

//リダイレクション
CicadaBtsRedirector::setDebugMode($CONFIG['debugMode']);
CicadaBtsRedirector::executeRedirection();

//モジュールディスパッチャ
//メイン部で、 module/[$mainModule].php のファイルがインクルードされる
$mainModule = "frontpage";
if(isset($_GET['module'])){
	if(isset($CONFIG['moduleEnable'][$_GET['module']])){ //設定ファイルに記述があるなら
		$mainModule = $_GET['module'];                   //有効なモジュールなので読み込む
		if($CONFIG['moduleEnable'][$_GET['module']]){    //パンくずタイトルがセットされてるなら
			$cicadaBts->addBreadcrumb($CONFIG['moduleEnable'][$_GET['module']]); //パンくずに追加
		}
	}
}else{
	 $_GET['module'] = ""; //不要か?
}



//お知らせメッセージを作成
$notifyMessage = array_merge($cicadaBts->getNotifyMessage(),$cicadaBtsProjectList->getNotifyMessage());
if(isset($cicadaBtsProject)) $notifyMessage = array_merge($notifyMessage,$cicadaBtsProject->getNotifyMessage());

$acceptReport = CicadaBtsUtility::getMessageDisplayNext();
if($acceptReport) $notifyMessage = array_merge($notifyMessage,array(array($acceptReport,'INFO')));

$notifyMessageHtml = CicadaBtsUtility::makeNotifyMessageHtml($notifyMessage);

//文字コードを強制
@header("Content-Type: text/html; charset=".BASE_ENCODING);
@header("Cache-Control: no-cache, must-revalidate");
@header("Pragma: no-cache");
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
<meta HTTP-EQUIV="Content-type" CONTENT="text/html; charset=<?php echo BASE_ENCODING; ?>" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<title><?php echo $cicadaBts->getSiteTitle(); ?></title>

</head>
<body>

<div id="wrapper">

<div id="header">
<h1>
<a href="./"><?php echo $CONFIG['siteTitle']; ?></a>
<?php echo $cicadaBts->getBreadcrumbHtml(); ?>
</h1>
</div>

<?php include($CONFIG['globalNaviFile']); ?>

<div id="content">

<div id="main">
<div class="inner">
<?php echo $notifyMessageHtml; ?>
<?php include($CONFIG['moduleDir']."/".$mainModule.$CONFIG['moduleExtension']); ?>

</div>
</div>

<div id="sidebar">
<?php include($CONFIG['moduleDir']."/".$CONFIG['moduleSidebar'].$CONFIG['moduleExtension']); ?>
</div>

</div>


<div id="footer">
<?php include($CONFIG['footerFile']); ?>
</div>

</div>

<?php
if($CONFIG['debugMode']){
	echo "<pre>\n";
	echo htmlspecialchars($cicadaBts->getDebugMessage());
	echo htmlspecialchars($cicadaBtsProjectList->getDebugMessage());
	echo htmlspecialchars($cicadaBtsProject->getDebugMessage());
	echo "</pre>\n";
}
?>

</body>
</html>
