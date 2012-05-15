<?php

$to  = $CONFIG['mailTestSendTo'];
$cc  = $CONFIG['mailTestSendCc'];
$bcc = $CONFIG['mailTestSendBcc'];;
$messageId = time()."-".substr(md5(uniqid()),10,12)."-TEST".$CONFIG['mailMessageIdSuffix'];
$reference = (time()-1000)."-".substr(md5(uniqid()),10,12)."-TEST".$CONFIG['mailMessageIdSuffix'];
$subject   = "メッセージ送信のテスト";
$bodyText  = "メッセージテストです。";

cicadaBtsSendMail($to,$cc,$bcc,$messageId,$reference,$subject,$bodyText);
?>
テストメッセージを送信しました。
<div class="table-form">
<table><tbody>
<tr><th>To:</th><td><?php echo $to; ?></td></tr>
<tr><th>Cc:</th><td><?php echo $cc; ?></td></tr>
<tr><th>Bcc:</th><td><?php echo $bcc; ?></td></tr>
<tr><th>Message-Id:</th><td><?php echo $messageId; ?></td></tr>
<tr><th>References:</th><td><?php echo $reference; ?></td></tr>
<tr><th>Subject:</th><td><?php echo $subject; ?></td></tr>
<tr><th>BodyText:</th><td><?php echo $bodyText; ?></td></tr>
</tbody></table>
</div>
