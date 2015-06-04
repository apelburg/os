<?php

include("../../libs/mysql.php");
	$id_name=$_POST['id_name'];
	$query='UPDATE `samples` SET `stage` = \'1\' WHERE id IN ('.$id_name.')';
	//echo $query;
	//echo '<br/>';
	$result = mysql_query($query,$db);
	if(!$result)exit(mysql_error());
###########################################
# Функция отправки сообщения с вложением  #
###########################################
 
function sendmail_file($to,$bcc,$fromemail,$from_name,$subject,$message,$file_path) {
	//$charset="windows-1251";
	$charset='utf-8';
//	$from = '=?'. $charset .'?b?'. base64_encode($from_name) .'?='; 
	$from = '=?'. $charset .'?b?'. $from_name .'?='; 
	//$charset='utf-8';
//	$from = '=?'. $charset .'?b?'. $from_name .'?='; 
	$mailfrom = ' <'. $fromemail .'>';	
	
	$f         = fopen($file_path,"rb");
	$un        = strtoupper(uniqid(time()));
	$head     .= "To: ".$to."\n";
	$head     .= "Subject: ".$subject."\n";
	$head     .= "X-Mailer: PHPMail Tool\n";
	$head     .= "Reply-To: ".$from_mail."\n";
	$head 	  .= "From: ". $from . $mailfrom ."\r\n";
	$head	  .= "Bcc:".$bcc."\r\n";
	$head     .= "Mime-Version: 1.0\n";	
	$head     .= "Content-Type:multipart/mixed;";
	$head     .= "boundary=\"----------".$un."\"\n\n";
	$zag       = "------------".$un."\nContent-Type:text/plain; charset=utf-8" . "\r\n";
	$zag      .= "Content-Transfer-Encoding: 8bit\n\n".$message."\r\n\n\n";
	$zag      .= "------------".$un."\n";
	$zag      .= "Content-Type: application/octet-stream;";
	$zag      .= "name=\"".basename($file_path)."\"\n";
	$zag      .= "Content-Transfer-Encoding:base64\n";
	$zag      .= "Content-Disposition:attachment;";
	$zag      .= "filename=\"".basename($file_path)."\"\n\n";
	$zag      .= chunk_split(base64_encode(fread($f,filesize($file_path))))."\n";
	
	
	$to='kapitonoval2012@gmail.com';
	if(!mail($to, $subject, $zag, $head, '-f'. $fromemail)){
			echo "<br><b style='color:red'>ERROR!!! Собщение не  отправлено 1</b><br/>";  
	}else{
			$message.="<br/><br/><b style='color:red'>ФАЙЛ ПРИКРЕПЛЕН, отправка html</span>";
	}
};

############################################
# Функция отправки сообщения без вложениея #
############################################

function sendmail($to,$bcc,$fromemail,$from_name,$subject,$message) {
		$charset='utf-8';
		$from = '=?'. $charset .'?b?'. $from_name .'?='; 
		$mailfrom = ' <'. $fromemail .'>';
		$headers  = 'Content-type: text/plain; charset=utf-8' . "\r\n";
	    $headers .= "From: ". $from ." ". $mailfrom ."\r\n";
	    //$headers .= "From: =?utf-8?b?MjQgTElURSBQUklOVA==?= <i  apelburg.ru>\r\n";
		$html   .= "Content-Transfer-Encoding:base64\n"; 
      	$html   .= chunk_split(base64_encode($message)) ."\r\n"; 
		//$message = $html;
		$headers .= "Bcc: ".$bcc." \r\n"; 
		//$subject = '=?koi8-r?B?'.base64_encode(convert_cyr_string($subject, "w","k")).'?= ';
		$headers  .= "Subject: ".$subject."\n";
		//$headers = '';
		$to='kapitonoval2012@gmail.com';
	    if(!mail($to, $subject, $message, $headers, '-f '.$fromemail)){
			echo "<br><b style='color:red'>$to,<br/> $subject<br/>, $message<br/>, $headers<br/>, '-f '.$fromemail<br/>";  
		}else{
			//$message.="<br/><br/><b style='color:red'>БЕЗ ФАЙЛА, отправка html</span>";
			echo "<br/><br/><b style='color:red'>БЕЗ ФАЙЛА, отправка html</span><br/>mail($to, $subject, $message, $headers, '-f '.$fromemail)";
		}
};

############################################################
############################################################
#########                                          #########
#########        ОБРАБОТКА ВХОДЯЩИХ ДАННЫХ     #########
#########                                          ######### 
############################################################
############################################################

echo '<pre>';
print_r ($_POST);
echo'</pre>';
if(isset($_POST['number']) || ($_POST['number'])!=0){
	
	$path='';
	
	$number=$_POST['number'];
	$pdf_name=$_POST['file_pdf'];
	$send_pdf=$_POST['save_pdf'];//checkbox
	$supplier_email=$_POST['supplier_email'];
	$email_manager=$_POST['email_manager'];
	$name_manager=$_POST['name_manager'];
	
	$subject=$_POST['subject'];
	//$to = $supplier_email;
	$to='kapitonoval2012@gmail.com';
	$bcc = $email_manager;
	$fromemail = $email_manager;
	$from_name = "APELBURG / $name_manager";
	$message=$_POST['text_'.$number];
	$file_path = $path.$pdf_name;
	
	if(isset($send_pdf) && $send_pdf=='on'){
	sendmail_file($to,$bcc,$fromemail,$from_name,$subject,$message,$file_path);
	}else{
		 sendmail($to,$bcc,$fromemail,$from_name,$subject,$message);
	}
	if (file_exists($file_path)) unlink($file_path);
	
	
	
	
	
	
	
}else{echo 'ОШИБКА!!! данные не пришли';}

//print_r($_POST);

?>