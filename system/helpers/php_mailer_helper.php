<?php
	if(! function_exists("sendEmail"))
	{
		function sendEmail($to,$subject,$message)
		{
			
			if(empty($to))
				return TRUE;
			if(!preg_match("/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/",$to))
			  return TRUE;
			  require_once "Mail/PHPMailerAutoload.php";
				$mail = new PHPMailer();
		
			try {
				//Server settings
				$mail->SMTPDebug = 0;                                       // Enable verbose debug output
				$mail->isSMTP(TRUE);                                            // Set mailer to use SMTP
				$mail->Host       = 'sg3plcpnl0096.prod.sin3.secureserver.net';  // Specify main and backup SMTP servers
				$mail->SMTPAuth   = TRUE;                                   // Enable SMTP authentication
				$mail->Username   = 'noreply@drinkingzone.in';                     // SMTP username
				$mail->Password   = 'DrinkingZone#2019';                               // SMTP password
				$mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
				$mail->Port       = 25;                                    // TCP port to connect to
		
				//Recipients
				$mail->setFrom('noreply@drinkingzone.in',"Drinking Zone");
				$mail->addAddress($to);     // Add a recipient
				// $mail->addReplyTo('shameel.annotation@gmail.com', 'Shameel Salih');
				// $mail->addCC('cc@example.com');
				// $mail->addBCC('annotation.noreply@gmail.com');
			
				// Attachments
				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		
				// Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject = $subject;
				$mail->Body    = $message;
				$mail->AltBody = 'Sorry This Mail Not Support HTML Contents';
		
				return $mail->send();
			} 
			catch (Exception $e) {
				echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
				return FALSE;
			}
		}

	}



?>
