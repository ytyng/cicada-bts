<?php
$cicadaBtsDefaultMailSetting = new CicadaBtsDefaultMailSetting($CONFIG);
?>
<?php include("project-header.php"); ?>

<h3>デフォルトメール送信設定</h3>

<div class="table-form">
<form name="editProject" method="post" 
	action="./?mode=saveDefaultMailSetting&module=default-mail-setting"
>

<table><tbody>
<tr><th>メール送信 To</th><td>
<textarea class="small" name="mailAddressTo"><?php echo $cicadaBtsDefaultMailSetting->getMailAddressToNl(); ?></textarea>
</td></tr>
<tr><th>メール送信 Cc</th><td>
<textarea class="small" name="mailAddressCc"><?php echo $cicadaBtsDefaultMailSetting->getMailAddressCcNl(); ?></textarea>
</td></tr>
<tr><th>メール送信 Bcc</th><td>
<textarea class="small" name="mailAddressBcc"><?php echo $cicadaBtsDefaultMailSetting->getMailAddressBccNl(); ?></textarea>
</td></tr>
<tr><td colspan="2"  class="submitarea" ><input type="submit" value="登録" /></td></tr>

</tbody></table>
</form>
</div>

<p>
ここで登録したメールアドレスは、プロジェクトのメール設定ページから簡単に呼び出せます。
</p>
