<?php
	require_once('settings.inc.php');
	require_once('article.class.php');
	require_once('functions.inc.php');
	require_once($phpmailer_path);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta charset="utf-8" />
<title>Contact the author</title>
</head>
<body>
<h1>Contact DegreesOfWikipedia.com:</h1>

<?php
	//echo "<pre>";
	//print_r($_REQUEST);
	//echo "</pre>";
	
	//check that a valid contact id was passed in for this hour
	$contactid = $_REQUEST['contactid'];
	
	if($contactid == getContactID())
	{
		
		if($_REQUEST['submit'] == 'submit')
		{
			echo "Thanks for your feedback!  It's good to know that there's someone out there <br/>";
			
			//create an instance of the PHP Mailer Class
			$mail = new PHPMailer();
			$mail->IsSMTP();        // set mailer to use SMTP
			$mail->IsHTML(false); // set email format to plain text
			//$mail->WordWrap = 50;  // set word wrap to 50 characters
			
			//sender
			$mail->From = $GLOBALS['contact_email_address'];
			$mail->FromName = $GLOBALS['contact_email_address'];
			$mail->AddReplyTo($GLOBALS['contact_email_address'], $GLOBALS['notification_email_sender']);
			
			//recipient
			$mail->AddAddress($GLOBALS['contact_email_address']);
			
			//subject
			$mail->Subject = "DegreesOfWikipedia.com - $_REQUEST[name]/ $_REQUEST[email]";	//set the subject

			
			$body = "";
			foreach($_REQUEST as $key => $val)
			{
				$body .= '[' . $key . "]\t=>\t$val" . "\n";
			}
			
			//body
			$mail->Body    = $body;	//set the  body (HTML or Plain Text)
			//$mail->AltBody = "";	//alternate body if HTML
			
			
			//add the mail server settings
			$host = $GLOBALS['phpmailer_smtp_host'];
			$username = "outgoing@myearlyedition.com";
			$password = "PleaseDontBounce";
			$mail->Host = $host;  // specify main and backup server
			$mail->SMTPAuth = false;     // turn on SMTP authentication
			$mail->Username = $username;  // SMTP username
			$mail->Password = $password; // SMTP password
			
			
			if($mail->Send())
			{
			   //success!  update the database!
			   echo " :-)<br>";
			}
			else
			{
				echo "Message could not be sent. :-(<br/>";
			}

			
			
		}
		else
		{
			echo "Tell us what you think!  File comments, tell us about bugs, or just throw some random thoughts in the boxes below:<br/><br/>";
			echo "<form method=\"post\">";
			echo "<table>";
			echo "<tr>";
			echo "<td>Your Name:</td>";
			echo "<td><input type=\"text\" size=\"50\" name=\"name\"/></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Your Email address or contact info (if you want a response):</td>";
			echo "<td><input type=\"text\" size=\"50\" name=\"email\"/></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td valign=\"top\">Your Comments:</td>";
			echo "<td valign=\"top\"><textarea name=\"comments\" rows=\"20\" cols=\"50\"></textarea></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td></td>";
			echo "<td><input style=\"font-size:20pt;font-style:bold;\" type=\"submit\" name=\"submit\" value=\"submit\"></td>";
			echo "</tr>";
			echo "</table>";
			echo "<input type=\"hidden\" name=\"contactid\" value=\"" . htmlentities($contactid,ENT_NOQUOTES,'UTF-8') . "\">";
			echo "</form>";		
		}
		
	}
	else
	{
	
		echo "Something went wrong!  Please go <a href=\"/\">here</a> and use the 'contact' link near bottom of the page!!<br/>";
	
	}
	




	
?>
</body>