<?php
$cicadaBtsDefaultMailSetting = new CicadaBtsDefaultMailSetting($CONFIG);
?>
<script type="text/JavaScript">
function sendProjectRootMail(){
	if(confirm('プロジェクトルートメールを送信してよろしいですか?')){
		window.location.href="./?mode=sendProjectRootMail&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>";
	}
}

defaultMailAddressTo  = "<?php echo $cicadaBtsDefaultMailSetting->getMailAddressTo();  ?>";
defaultMailAddressCc  = "<?php echo $cicadaBtsDefaultMailSetting->getMailAddressCc();  ?>";
defaultMailAddressBcc = "<?php echo $cicadaBtsDefaultMailSetting->getMailAddressBcc(); ?>";

function setDefaultMailAddressTo(){
	document.getElementById("mailAddressTo").value=defaultMailAddressTo.replace(",","\n");
}

function setDefaultMailAddressCc(){
	document.getElementById("mailAddressCc").value=defaultMailAddressCc.replace(",","\n");
}

function setDefaultMailAddressBcc(){
	document.getElementById("mailAddressBcc").value=defaultMailAddressBcc.replace(",","\n");
}

</script>

<h2 id="project-title">
<a href="./?module=project-top&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>">
<?php echo $cicadaBtsProject->getProjectName(); ?>
</a>
</h2>

<h3>メール送信設定</h3>

<div class="table-form">
<form name="mailSetting" method="post" 
	action="./?mode=saveMailSetting&module=mail-setting&projectId=<?php echo $cicadaBtsProject->getProjectId(); ?>"
>

<table><tbody>
<tr><th>メール送信 To</th><td>
<textarea class="small" name="mailAddressTo"  id="mailAddressTo" ><?php echo $cicadaBtsProject->getMailAddressToNl(); ?></textarea>
</td></tr>
<tr><th>メール送信 Cc</th><td>
<textarea class="small" name="mailAddressCc"  id="mailAddressCc" ><?php echo $cicadaBtsProject->getMailAddressCcNl(); ?></textarea>
</td></tr>
<tr><th>メール送信 Bcc</th><td>
<textarea class="small" name="mailAddressBcc" id="mailAddressBcc"><?php echo $cicadaBtsProject->getMailAddressBccNl(); ?></textarea>
</td></tr>
<tr><th title="プロジェクト ルートメール メッセージID">PRMM-ID</th>
<td><?php echo $cicadaBtsProject->getProjectRootMailMessageId(); ?></td></tr>
<tr><td colspan="2"  class="submitarea" ><input type="submit" value="登録" /></td></tr>

</tbody></table>
</form>
</div>

<h3>メール送信設定をデフォルトメール送信先設定から読み込み</h3>
<p><a href="./?module=default-mail-setting">デフォルトメール送信先設定</a>から、送信先を読み込み、上のフォームに反映します。</p>
<div class="main-header-links">
<a href="javascript:setDefaultMailAddressTo();void(0);">To読込</a>
<a href="javascript:setDefaultMailAddressCc();void(0);">Cc読込</a>
<a href="javascript:setDefaultMailAddressBcc();void(0);">Bcc読込</a>
</div>

<h3>プロジェクトルートメールを送信する</h3>
<p>プロジェクトルートメールを送信すると、チケットを起票した時に送信されるメールの References: ヘッダが、
プロジェクトルートメールのメッセージIDを参照するようになります。<br />
<br />
スレッド表示対応のメーラーの場合、メールの親子関係がわかりやすくなります。<br />
<br />
メールのメッセージID(PRMM-ID:プロジェクトルートメールメッセージID)は、メール送信時に採番されます。
送信は何度でも行えますが、行うたびにPRMM-IDは新たな値に変更されます。<br />
<br />
メールの送信先に変更を加えた場合は、プロジェクトルートメールを送信する前に必ず設定を<strong>登録</strong>してください。
</p>


<input type="button" onClick="sendProjectRootMail();" value="プロジェクトルートメールを送信" />
