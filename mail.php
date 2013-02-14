<html>
<head>
<title></title>
</head>
<body>

<?php

//error_reporting(E_ALL);
//error_reporting(E_STRICT);

require_once('class.phpmailer.php');
include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded


function SendBeerMsg($recipients, $subject, $body) {

	$mail             = new PHPMailer();
	
	//$body             = 'Test';
	
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->Host       = "mail.yourdomain.com"; // SMTP server
	$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
	                                           // 1 = errors and messages
	                                           // 2 = messages only
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
	$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
	$mail->Username   = "beernerdstats@gmail.com";  // GMAIL username
	$mail->Password   = "compaq99";            // GMAIL password
	
	$mail->SetFrom('beernerdstats@gmail.com', 'BeerNerd Stats');
	
	//$mail->AddReplyTo("name@yourdomain.com","First Last");
	
	$mail->Subject    = $subject;
	
	//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	
	$mail->MsgHTML($body);
	
	//$mail->AddAddress("beernerdstats@gmail.com");
	foreach ($recipients as &$address) {
    $mail->AddBCC($address);
	}
	
	if(!$mail->Send()) {
	  //echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
	  //echo "Message sent!";
	}
}

?>

</body>
</html>