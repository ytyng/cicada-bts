<?php
//モードディスパッチャ
if(!isset($_GET['mode'])) $_GET['mode'] = "";
$frontModule = "";
switch($_GET['mode']){
case "adminLogin":
	//ログイン
	if(!isset($_POST['uid'])) die('[DIE] No set parameter.');
	if(!isset($_POST['upw'])) die('[DIE] No set parameter.');
	$cicadaBts->login($_POST['uid'],$_POST['upw']);
	break;
	
case "adminLogout":
	//ログアウト
	$cicadaBts->logout();
	break;
/*
case "saveUserName":
	//ユーザー名をクッキーに保存
	if(!isset($_POST['userName'])) die('[DIE] No set parameter.');
	$cicadaBts->setSavedUserName($_POST['userName'],true);
	break;
*/	
case "newProject":
	//新規プロジェクト
	if(!isset($_POST['projectId']))   die('[DIE] No set parameter.');
	if(!isset($_POST['projectName'])) die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())   die('[DIE] Have no permission.');
	$cicadaBtsProjectList->makeNewProject($_POST['projectId'],$_POST['projectName']);
	break;
	
case "saveProject":
	//プロジェクト状態保存
	if(!isset($_POST['projectId']))          die('[DIE] No set parameter.');
	if(!isset($_POST['projectName']))        die('[DIE] No set parameter.');
	if(!isset($_POST['progress']))           die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	if(!isset($CONFIG['projectProgress'][$_POST['progress']])) die('[DIE] No set parameter.'); //プロジェクト進捗IDが不正ならここでDIE
	$cicadaBtsProjectList->writeProject($_POST['projectId'],$_POST['projectName'],$_POST['progress']);
	break;
	
case "saveProjectInformation":
	//プロジェクト情報テキスト保存
	if(!isset($_POST['projectInformation'])) die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProject->writeProjectInformation($_POST['projectInformation']);
	break;
case "writeCategoryList":
	//カテゴリリストを保存
	if(!isset($_POST['categoryList']))       die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProject->writeCategoryListText($_POST['categoryList']);
	break;
	
case "projectBbsWrite":
	//プロジェクト掲示板に書き込み
	if(!isset($_POST['bodyText'])) die('[DIE] No set parameter.');
	if(!isset($_POST['userName'])) die('[DIE] No set parameter.');
	if(!isset($_POST['subject']))  die('[DIE] No set parameter.');
	$cicadaBts->setSavedUserName($_POST['userName']);
	$cicadaBtsProject->writeProjectBbs($_POST['bodyText'],$_POST['userName'],$_POST['subject']);
	break;

case "writeTicket":
	//新規チケット発行
	if(!isset($_POST['ticketId'])) die('[DIE] No set parameter.');
	if(!isset($_POST['userName'])) die('[DIE] No set parameter.');
	if(!isset($_POST['subject']))  die('[DIE] No set parameter.');
	if(!isset($_POST['category'])) die('[DIE] No set parameter.');
	if(!isset($_POST['severity'])) die('[DIE] No set parameter.');
	if(!isset($_POST['status']))   die('[DIE] No set parameter.');
	if(!isset($_POST['assign']))   die('[DIE] No set parameter.');
	if(!isset($_POST['bodyText'])) die('[DIE] No set parameter.');
	$cicadaBts->setSavedUserName($_POST['userName']);
	$cicadaBtsProject->writeTicket(
		$_POST['ticketId'],$_POST['userName'],$_POST['subject'],$_POST['category'],
		$_POST['severity'],$_POST['status'],$_POST['assign'],$_POST['bodyText']
	);
	break;
	
case "saveMailSetting":
	//メール送信設定保存
	if(!isset($_POST['mailAddressTo']))      die('[DIE] No set parameter.');
	if(!isset($_POST['mailAddressCc']))      die('[DIE] No set parameter.');
	if(!isset($_POST['mailAddressBcc']))     die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProject->writeMailSetting($_POST['mailAddressTo'],$_POST['mailAddressCc'],$_POST['mailAddressBcc']);
	break;
	
case "sendProjectRootMail":
	//プロジェクトルートメールを送信
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProject->sendProjectRootMail();
	break;
	
case "deleteProject":
	//プロジェクト削除
	if(!isset($_POST['projectId']))          die('[DIE] No set parameter.');
	if(!isset($_POST['deleteConfirm']))      die('[DIE] No set parameter.');
	if(!isset($_POST['deleteDirectory']))    $_POST['deleteDirectory'] = false;
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProjectList->deleteProject($_POST['projectId'],$_POST['deleteConfirm'],$_POST['deleteDirectory']);
	break;
	
case "changeProjectId":
	//プロジェクトID変更
	if(!isset($_POST['projectId']))          die('[DIE] No set parameter.');
	if(!isset($_POST['newProjectId']))       die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsProjectList->changeProjectId($_POST['projectId'],$_POST['newProjectId']);
	break;
	
case "saveDefaultMailSetting":
	//デフォルトメール送信設定保存
	if(!isset($_POST['mailAddressTo']))      die('[DIE] No set parameter.');
	if(!isset($_POST['mailAddressCc']))      die('[DIE] No set parameter.');
	if(!isset($_POST['mailAddressBcc']))     die('[DIE] No set parameter.');
	if(!$cicadaBts->isAdminLogin())          die('[DIE] Have no permission.');
	$cicadaBtsDefaultMailSetting = new CicadaBtsDefaultMailSetting($CONFIG);
	$cicadaBtsDefaultMailSetting->writeMailSetting($_POST['mailAddressTo'],$_POST['mailAddressCc'],$_POST['mailAddressBcc']);
	break;

default:
	//pass
}
?>