<?php
/*
Cicada BTS 設定ファイル

バグ・お問い合わせはこちらまで
info@ytyng.com

*/

//▼全体的な文字コード指定
define('BASE_ENCODING','UTF-8');

mb_http_output(BASE_ENCODING);
mb_internal_encoding(BASE_ENCODING);
mb_regex_encoding(BASE_ENCODING);

//▼セッション関係
session_set_cookie_params(31*24*60*60,"/");
session_cache_limiter('private');
session_cache_expire(30*24*60);

$CONFIG = array(
	
	//===================================================================================
	// サービス全体の設定
	//-----------------------------------------------------------------------------------
	
	//▼サイトのタイトル。titleタグ、ページ左上のサイトID部に表示される。
	'siteTitle'      => "Cicada BTS",
	//▼サイトのフロントページのh2
	'frontpageTitle' => "Cicada BTS Frontpage",
	
	
	//===================================================================================
	// メール関係
	//-----------------------------------------------------------------------------------
	
	//▼メールのメッセージIDの接尾語
	'mailMessageIdSuffix' => "@cicada.example.com",
	//▼メールの件名の接頭語
	'mailSubjectPrefix'   => "[CicadaBTS] ",
	//▼メールの本文のヘッダ
	'mailBodyTextHeader'  => "Cicada バグトラッキングシステム\r\n----------------------------------------\r\n",
	//▼メールの本文の水平線
	'mailBodyTextHr'      => "----------------------------------------\r\n",
	//▼メールの本文のURLリンクのルート
	'mailUrlRoot'         => "http://example.com/cicada/",
	//▼テストメッセージ送信先
	'mailTestSendTo'      => "test@example.com",
	'mailTestSendCc'      => "",
	'mailTestSendBcc'     => "",
	
	
	//===================================================================================
	// 管理者アカウント
	//-----------------------------------------------------------------------------------
	
	//管理者アカウント。アカウント名とパスワードの連想配列。
	//パスワードは、CicadaBtsクラスの、makePasswordHash()にてハッシュ化して比較される。
	//デフォルトは、adminPasswordSaltを文頭に追加して、sha1()
	//実際に設定する場合、パスワードの値はハッシュ化したものに直してください。
	'adminAccount' => array(
		'admin'  => sha1("AAAA"."password"),
		'admin2' => "65ae05962ae86fff6bc7a8e373188bb4ba1f854e", //sha1("AAAA"."password2"),
	),
	
	'adminPasswordSalt' => "AAAA",
	
	
	//===================================================================================
	// 添付ファイル
	//-----------------------------------------------------------------------------------
	
	//▼OSの、ファイル名のエンコーディング。linuxの場合は"utf-8",Windowsなら"SJIS-win"
	//'fileSystemEncoding' => "utf-8",
	'fileSystemEncoding' => strncasecmp(PHP_OS,'WIN',3)==0 ? "SJIS-win" : BASE_ENCODING ,
	
	//▼アップロード可能拡張子
	'uploadAllowExtention' => array(
		".png",".jpg",".jpeg",".gif",
		".pdf",".xls",".doc",".ppt",".ods",".odt",".txt",".rtf",
		".zip",".gz",".tgz",".pgp",
		".wmv",".mpg",".mpeg",".swf",".flv",
	),
	//▼アップロード拒否ファイル名
	'uploadDenyNeedle'  => array("php",".htaccess",".pl",".cgi",".js",".html",".xhtml",".exe",".hta"),
	//'uploadDenyNeedle' と部分一致せず、かつ末尾が'uploadAllowExtention'と一致する場合、アップロード許可。
	//'uploadAllowExtention'は空もしくは未定義にすれば機能しない
	//ともに、ignore-case.
	
	
	//===================================================================================
	// 状態リスト
	//-----------------------------------------------------------------------------------
	
	//プロジェクト進捗リスト
	'projectProgress' => array(
		  0 => "未確定",
		100 => "確定未開発",
		200 => "開発中",
		250 => "開発完了未テスト",
		300 => "検証テスト中",
		350 => "検証バグシュート",
		400 => "リリース待ち",
		500 => "本運用中",
		//510 => "本番バグシュート",
		800 => "終了",
		900 => "凍結",
		910 => "不可視", //現在の所、不可視は凍結や終了と挙動の差は無い
	),
	'defaultProgress' => 0,
	
	//プロジェクト完了とカウントするもの
	//'projectProgressComplete' => array(800,), //使っていない
	
	//無視するもの
	//'projectProgressIgnore' => array(900,910,),//使っていない
	
	//サイドバーに表示するプロジェクト進捗
	'projectProgressDisplaySidebar' => array(0,100,200,250,300,350,400,500,),
	
	//重要度(深刻度)リスト
	'ticketSeverity' => array(
		  0 => array("不明"    ,"重要度の判断不能"),
		100 => array("表示不備","誤字・脱字・デザイン不良など。機能への影響無し。"),
		200 => array("性能劣化","必要なパフォーマンス基準をクリアしていない。"),
		300 => array("機能欠落","必要な機能が期待通り動作しない。"),
		500 => array("情報漏洩","個人情報やプログラムコードを表示可能。スクリプトインジェクションもここ。"),
		600 => array("破壊"    ,"データの破壊、サーバ権限の奪取などが可能。SQLインジェクション、XSRFもここ。"),
		800 => array("要望"    ,"機能強化の要望、品質向上案"),
	),
	'ticketSeverityDefault' => 0,
	
	//チケットの状態
	'ticketStatus' => array(
		//ID          名称  想定起票者 想定返答者 詳細説明
		  0 => array("新規"    ,"検証","開発","新規に起票され、担当者が読んでいない状態。"),
		 50 => array("差し戻し","検証","開発","バグが未修正、もしくは別の問題が発生。"),
		100 => array("情報不足","開発","検証","現象発生時の情報が不足しているため再度検証を依頼"),
		120 => array("再現不能","開発","検証","現象再現が不可能なため対応できず"),
		200 => array("検討中"  ,"開発","開発","現象は確認したが、対応未定"),
		300 => array("修正中"  ,"開発","開発","バグと判断されたので、修正を行う。"),
		400 => array("再検依頼","開発","検証","バグが修正されたので、検証者に再度検証を依頼。"),
		500 => array("修正完了","検証","解決","バグが解消されたので解決。"),
		600 => array("未対応"  ,"開発","解決","バグだが、対応しない。"),
		700 => array("仕様"    ,"開発","解決","仕様通りの動作であり、問題なし"),
		800 => array("起票誤り","検証","解決","起票が誤りであり、問題なし"),
		820 => array("同件"    ,"検証","解決","他のチケットと重複しているため本件は無効"),
	),
	'ticketStatusDefault' => 0,
	
	//▼未解決にカウントするステータス
	'ticketStatusUnsolved' => array(0,100,120,200,300,400,),
	//▼検証向け未解決にカウントするステータス
	'ticketStatusUnsolvedForTest' => array(100,120,400,),
	//▼開発向け未解決にカウントするステータス
	'ticketStatusUnsolvedForDevelop' => array(0,200,300,),
	
	//▼解決済みステータス
	//'ticketStatusSolved' => array(500,600,700,800,820,), //使わない
	
	//▼バグとみなすステータス
	'ticketStatusBug'    => array(200,300,400,500,600,),
	
	
	//===================================================================================
	// 検索
	//-----------------------------------------------------------------------------------
	
	//▼検索窓の、プロジェクト検索チェックボックスデフォルト状態
	'searchProjectSearchDefaultCheck' => true,
	//▼検索窓の、チケット検索チェックボックスデフォルト状態
	'searchTicketSearchDefaultCheck'  => true,
	//▼検索窓の、Grepチェックボックスデフォルト状態
	'searchGrepDefaultCheck'          => false,
	//▼Grep機能の有効 (サーバがWindowsの場合はfalseに設定すること)
	'searchGrepEnable'                => true,
	
	
	//===================================================================================
	// ロギング
	//-----------------------------------------------------------------------------------
	
	//▼trueにすると、グローバルログを書き出す
	'globalLogEnable'                 => true,
	//▼trueにすると、プロジェクトログを書き出す
	'projectLogEnable'                => true,
	
	
	//===================================================================================
	// セッション
	//-----------------------------------------------------------------------------------
	
	//▼クッキーのセッションIDの項目名
	'sessionCookieName' => "s", 
	//▼アプリケーションID。サーバ内部で他アプリケーションと区別するため
	'sessionApplicationName' => "cicadaBts", 
	
	
	//===================================================================================
	
	//▼パンくずリストの項目の最大長さ(全角=2)
	'breadcrumbLength'   => 40,
	
	//▼ファイル・ディレクトリ設定
	'globalDataDir'             => "data",
	'projectDataDir'            => "data",
	'projectListFile'           => "projectlist.tsv",
	
	'projectInformationFile'    => "project-info.txt",
	'projectBbsFile'            => "project-bbs.tsv",
	'ticketFile'                => "ticket.tsv",
	'attachDir'                 => "attach",
	'mailSettingFile'           => "mail-setting.tsv",
	'categoryListFile'          => "category.tsv",
	'globalLogFile'             => "global.log",
	'projectLogFile'            => "project.log",
	
	'moduleDir'                 => "module",
	'moduleExtension'           => ".php",
	'moduleSidebar'             => "sidenavi",
	'globalNaviFile'            => "config/globalnavi.php",
	'footerFile'                => "config/footer.php",
	
	'fieldSeparator'            => "\t",
	'lineSeparator'             => "\n",
	'categorySeparator'         => ", ",
	
	'defaultMailSettingFile'    => "default-mail-setting.tsv",
	
	//▼有効モジュール名ここに書いてあるメインモジュール(ファイル名=>パンくず表示名) は有効となる。
	//index.phpで使う。
	//sidenavi.php, project-header.php, project-footer.php は
	//特別な使われ方をするのでここには記述不要。
	'moduleEnable'    => array(
		'project-list'           => "全プロジェクトリスト",
		'project-top'            => "",
		'project-bbs'            => "プロジェクト掲示板",
		'edit-project'           => "プロジェクト状態を編集",
		'edit-project-info'      => "プロジェクト情報テキストを編集",
		'new-ticket'             => "新規チケット",
		'ticket'                 => "",
		'ticket-list'            => "全チケットリスト",
		'mail-setting'           => "メール送信設定",
		'edit-category-list'     => "カテゴリリストを編集",
		'mail-test'              => "メールテスト",
		'ticket-search-category' => "カテゴリ別チケット",
		'search'                 => "検索結果",
		'logviewer-global'       => "グローバルログ",
		'logviewer-project'      => "プロジェクトログ",
		'ticket-search-word'     => "プロジェクト内チケット検索結果",
		'delete-project'         => "プロジェクト削除",
		'change-projectid'       => "プロジェクトID変更",
		'default-mail-setting'   => "デフォルトメール送信先設定",
	),
	
	'projectBbsNoUserName' => "---",
	'projectBbsNoSubject'  => "(件名なし)",
	
	//===================================================================================
	// デバッグ用
	//-----------------------------------------------------------------------------------
	
	//▼trueにすると、リダイレクションを行わなくなり、ページ下部にデバッグ文表示
	'debugMode'          => false,
	//▼trueにすると、ファイルへの変更を一切行わない
	'demoMode'           => false,
	

);

/*

メールを送信する関数

PHPの組み込み関数、Pear、外部システムなど、いろいろな方法が考えられるので
コンフィグファイル内に作成。
voidの関数なので、ただreturnすれば無効化できる。
$to,$cc,$bccには複数のメールアドレスがカンマ区切りで来る。

メール送信のテストを行う場合は、
/?module=mail-test をリクエストしてください。

*/
function cicadaBtsSendMail($to,$cc,$bcc,$messageId,$references,$subject,$bodyText){
	
	/*
	==============================================================================
	パターン1.メール送信を一切無効にする場合。
	下の行のコメント句を削除し、有効にしてください。
	ただreturnすれば、メール送信は一切無効となります。
	*/
	
	//return;
	
	/*
	==============================================================================
	値チェックとメールヘッダ作成
	*/
	if(!$to || !$subject || !$bodyText) return;
	
	$rcptTo = $to;
	if($cc)  $rcptTo .= ",".$cc;
	if($bcc) $rcptTo .= ",".$bcc;
	
	$exHeader  = "To: ".$to."\r\n";
	if($cc){
		$exHeader .= "Cc: ".$cc."\r\n";
	}
	$exHeader .= "From: test@example.com\r\n";
	$exHeader .= "Message-ID: <".$messageId.">\r\n";
	$exHeader .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";
	if($references){
		$exHeader .= "References: <".$references.">\r\n";
		$exHeader .= "In-Reply-To: <".$references.">\r\n";
	}	
	
	/*
	==============================================================================
	パターン2.PHPの内部関数 mb_send_mailを使う場合。
	設定次第ですが、Linuxなどの場合はローカルのSMTPサーバを使い、
	Windowsの場合はphp.iniで設定してあるSMTPサーバに接続すると思います。
	*/
	
	mb_send_mail($rcptTo,$subject,$bodyText,$exHeader);
	return;
	
	/*
	==============================================================================
	パターン3.ytyng.comの Esmtp.class.php を使ってメールを送信する
	同梱のEsmtp.class.phpを使って、SMTPサーバにログインし、メールを送信します。
	*/
	
	/*
	require_once("Esmtp.class.php");
	
	$m = new Esmtp(
		'mail@example.com',  //From mailAddress
		'smtp.example.com',  //server
		587                  //port
	);
	$m->auth(
		'LOGIN',    //authtype
		'foo00000', //accountId
		'bar00000'  //password
	);
	
	$m->mb_send(
		$rcptTo,     //RCPT-TO
		$subject,    //Subject
		$bodyText,   //BodyText
		$exHeader    //ex_header
	);
	$m->quit();
	//echo "<pre>".htmlspecialchars($m->log)."</pre>";
	return;
	*/
	
}

?>
