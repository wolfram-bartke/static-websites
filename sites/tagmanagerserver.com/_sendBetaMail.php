<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';


if(empty($_POST)){
    die('...');
}else if(strpos($_POST['beta-url'], '.') === false){
    die('email-error');
}else if(!empty($_POST['beta-email'])){
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    // Set PHPMailer to use the sendmail transport
    $mail->isSendmail();
    //Set who the message is to be sent from
    $mail->setFrom('w.bartke@tagmanagerserver.com', $_POST['beta-name']);
    //Set an alternative reply-to address
    $mail->addReplyTo($_POST['beta-email'], $_POST['beta-name']);
    //Set who the message is to be sent to
    $mail->addAddress('w.bartke@hokata.de', 'Wolfram Bartke');
    //Set the subject line
    $mail->Subject = 'Tag Manager Server Beta Anmeldung';
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    #$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
    $mail->isHTML(false);

    $aBody = array();
    foreach($_POST as $sVar => $sValue){
        $aBody[] = $sVar.': '.$sValue;
    }

    $mail->Body = implode("\n\r", $aBody); 
    //Replace the plain text body with one created manually
    #$mail->AltBody = 'This is a plain-text message body';
    //Attach an image file
    #$mail->addAttachment('images/phpmailer_mini.png');
#print_r($_POST);
    //send the message, check for errors
    if (!$mail->send()) {
        #echo 'Mailer Error: '. $mail->ErrorInfo;
        echo 'email-error';
    } else {
        #echo 'Message sent!';
        echo 'email-sent';
    }

}


