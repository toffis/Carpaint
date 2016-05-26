<?php
// INTERDIRE LE CACHE IE
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Content-Type: text/html; charset=utf-8");

setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
$date =  (strftime("%A %d %B à %I:%M"));

if($_POST)
{
    $to_email       = "test@digital-surface.com"; //Recipient email, Replace with own email here
	$from_email 	= $_POST["user_email"]; //From email address (eg: no-reply@YOUR-DOMAIN.com)
	
    //check if its an ajax request, exit if not
    if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        $output = json_encode(array( //create JSON data
            'type'=>'error',
            'text' => 'Sorry Request must be Ajax POST'
        ));
        die($output); //exit script outputting json data
    }
   
    //Sanitize input data using PHP filter_var().
    $user_name      = filter_var($_POST["user_name"], FILTER_SANITIZE_STRING);
    $user_email     = filter_var($_POST["user_email"], FILTER_SANITIZE_EMAIL);
    //$country_code   = filter_var($_POST["country_code"], FILTER_SANITIZE_NUMBER_INT);
    //$phone_number   = filter_var($_POST["phone_number"], FILTER_SANITIZE_NUMBER_INT);
    //$subject        = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
	$user_societe   = filter_var($_POST["user_societe"], FILTER_SANITIZE_STRING);
	$subject        = "Formulaire de contact | carpaint.fr";
    $message        = filter_var($_POST["msg"], FILTER_SANITIZE_STRING);
   
    //additional php validation
	$alrt = "";
	
	if (strlen($user_name)<1){ // champs nom vide.
        $alrt .= '► Veuillez renseigner votre nom<br>';
    }else if(strlen($user_name)<4){ // If length is less than 4 it will output JSON error.
        $alrt .= '► Champs Nom : nb Caracteres insuffisant<br>';
    } 
	
    if (strlen($user_email)<1){ // champs email vide.
        $alrt .= '► Veuillez renseigner votre adresse email<br>';
    }else if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){ //email validation
        $alrt .= '► Adresse email invalide<br>';
    }
	

	if(strlen($message)<1){ //check emtpy message
        $alrt .= '► Merci d\'entrer votre message<br>';
    }
/*	
	else if(strlen($message)<7){ //check emtpy message
        $alrt .= '► Votre message est trop court.<br>';
    }
*/
	
	if (isset ($alrt) && $alrt != ""){
		$output = json_encode(array('type'=>'error', 'text' => $alrt));
		die($output);
	}
   
    //email body
    
	$message_body = "\n Date : ".$date."\n\n Nom : ".$user_name."\n Société : ".$user_societe."\n Email : ".$user_email;
	$message_body .= "\n\n----------------------------------------------\n\n";
	$message_body .= $message;

	### Attachment Preparation ###
	$file_attached = false;
	if(isset($_FILES['file_attach'])) //check uploaded file
	{
		//get file details we need
		$file_tmp_name 	  = $_FILES['file_attach']['tmp_name'];
		$file_name 		  = $_FILES['file_attach']['name'];
		$file_size 		  = $_FILES['file_attach']['size'];
		$file_type 		  = $_FILES['file_attach']['type'];
		$file_error 	  = $_FILES['file_attach']['error'];

		//exit script and output error if we encounter any
		if($file_error>0)
		{
			$mymsg = array( 
			1=>"Votre fichier est trop volumineux", 
			2=>"Votre fichier est trop volumineux", 
			3=>"Probleme de transfert du fichier", 
			4=>"Probleme de transfert du fichier", 
			6=>"Probleme de transfert du fichier" ); 
			
			$output = json_encode(array('type'=>'error', 'text' => $mymsg[$file_error]));
			die($output); 
		}
		
		//read from the uploaded file & base64_encode content for the mail
		$handle = fopen($file_tmp_name, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
		$encoded_content = chunk_split(base64_encode($content));
		//now we know we have the file for attachment, set $file_attached to true
		$file_attached = true;
	}
	

	if($file_attached) //continue if we have the file
	{
		$boundary = md5("sanwebe"); 
		
		//header
		$headers = "MIME-Version: 1.0\r\n"; 
		$headers .= "From:".$from_email."\r\n"; 
		$headers .= "Reply-To: ".$user_email."" . "\r\n";
		$headers .= "Content-Type: multipart/mixed; charset=utf-8; boundary = $boundary\r\n\r\n"; 

		//plain text 
		$body = "--$boundary\r\n";
		$body .= "Content-Type: text/plain; charset=utf-8\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
		$body .= chunk_split(base64_encode($message_body)); 
		
	
		//attachment
		$body .= "--$boundary\r\n";
		$body .="Content-Type: $file_type; name=\"$file_name\"\r\n";
		$body .="Content-Disposition: attachment; filename=\"$file_name\"\r\n";
		$body .="Content-Transfer-Encoding: base64\r\n";
		$body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n"; 
		$body .= $encoded_content; 

	}else{
		//proceed with PHP email.	
		$headers = "From: <".$from_email."> \r\n";
		$headers .= "Reply-To: <".$user_email."> \r\n";
		$headers .= "X-Mailer: PHP \n";
		$headers .= "X-Priority: 1 \n";
		$headers .= "MIME-Version: 1.0 \n";
		$headers .= "Content-Type: text/plain;; charset=utf-8 \n";
		
		$body = $message_body;
	}

	
	
	$send_mail = mail($to_email, $subject, $body, $headers);
   

    if(!$send_mail){
        $output = json_encode(array('type'=>'error', 'text' => 'Impossible d\'envoyer le message.'));
        die($output);
    }else{
        $output = json_encode(array('type'=>'message', 'text' => 'Hi '.$user_name .' Thank you for your email'));
        die($output);
    }
}
?>