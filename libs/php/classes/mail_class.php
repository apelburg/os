<?php
 class Mail{
	    public $added_headers = NULL;
		public $multipart = FALSE;
		public $boundary = NULL;
		public $message_parts = array();
	    function __consturct(){
		}
		public function add_bcc($to){
		    if(!$this->added_headers) $this->added_headers = array();
		    if(!is_array($to)) $to = array($to);
			//$headers .= "Bcc: box@yandex.ru \r\n"; 
			array_push($this->added_headers,"Bcc: ".implode(",",$to)." \r\n");
		}
		public function add_cc($to){
		    if(!$this->added_headers) $this->added_headers = array();
		    if(!is_array($to)) $to = array($to);
			//$headers .= "Cc: box@yandex.ru \r\n"; 
			array_push($this->added_headers,"Cc: ".implode(",",$to)." \r\n");
		}
		public function attach_file($filepath){
		    if(!$this->multipart) $this->multipart = TRUE;
		    if(empty($this->boundary)) $this->boundary = md5(uniqid(time()));
		    if(!$this->added_headers) $this->added_headers = array();
			array_push($this->added_headers,"Content-Type: multipart/mixed; boundary = \"".$this->boundary."\"\r\n");
			$filepath_utf = iconv("UTF-8","windows-1251", $filepath);
            $filename = substr($filepath,strrpos($filepath,"/")+1);
			
			if($fd = @fopen($filepath_utf,"rb")){

				$content = fread($fd,filesize($filepath_utf));
				$content = chunk_split(base64_encode($content));
				fclose($fd);
				unset($fd);
 
				$filename = "=?utf-8?b?".base64_encode($filename)."?=";
				$message = "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
				$message .= "Content-Transfer-Encoding: base64\r\n";
				$message .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
				$message .= $content."\r\n\r\n";
				$this->message_parts[] = $message;
			}
			else $this->errors[] = 'Не удалось прочитать файл '.$filepath_utf.' с диска'; 
		}
		public function add_img_in_letter($filepath,$filename){
		    if(!$this->multipart) $this->multipart = TRUE;

			
			$filepath_utf = substr(ROOT,0,strrpos(ROOT,"/")).iconv("UTF-8","windows-1251", $filepath);
			
			if($fd = @fopen($filepath_utf,"rb")){

				$content = fread($fd,filesize($filepath_utf));
				$content = chunk_split(base64_encode($content));
				fclose($fd);
				
				$extention = strtolower(substr($filename,strrpos($filename,".")+1));
                $extention = ($extention == 'jpg' )? 'jpeg': $extention;
				
				$part = "Content-type: image/".$filename."; name=\"".$filename."\"\r\n";
				$part .= "Content-Disposition: inline; filename=\"".$filename."\"\r\n";
				$part .= "Content-Transfer-Encoding: base64\r\n";
				$part .= "X-Attachment-Id: ".$filename."\r\n";
				$part .= "Content-ID: ".$filename."\r\n\r\n";
				$part .= $content."\r\n\r\n";
				$index = isset($this->related_parts)?count($this->related_parts):0;
				$this->related_parts[$index] = $part; 
			}
			else $this->errors[] = 'Не удалось прочитать файл '.$filepath_utf.' с диска'; 
        }
	    public function send($to,$from,$subject,$message,$add_f_flag = FALSE){
		    // помимо всех прочих необходимых параметров есть еще один важный $add_f_flag который регулирует нужно ли передавать
			// флаг '-f' функции mail()
			// практика показала что где-то например в скриптах работающих через крон этот флаг необходим без него mail() не отрабатывает
			// в других местах как например отправка КП наоборот наличие '-f' флага в не дает срабатывать mail(), он там мешает так что 
			// в разных местах возникает необходимость по разному вызывать mail() , этот флаг позволяет это сделать при вызове метода send()
			// если в методе send() передам в для этого TRUE параметра то флаг '-f' добавляется,
			// по умолчанию ничего  в методе send() передавать  не надо и флаг '-f' добавлен не будет 
			
		    if(empty($to))$this->errors[] = 'Не указан отправитель';
			if(empty($from))$this->errors[] = 'Не указан получатель'; 
			if(empty($subject))$this->errors[] = 'Не указана тема письма'; 
			if(empty($message))$this->errors[] = 'Письмо не содержит сообщения'; 

			// Проверяем сообщение на наличие в нем вложенного изображения - тега <img>
			$pattern = '/<img.+src=[\'\"]{1}([^\'\"]+)[\'\"]{1}>/isU';
			///$pattern = '/<img.+src="([^\"]+)">/isU';
			if(preg_match_all($pattern,$message,$matches)){
			    // print_r($matches); exit;
			    for( $i = 0 ; $i < count($matches[0]); $i++){
					//! * обрабатывает только первое найденое изображение в тексте
					
					$filepath = $matches[1][$i];
					//! если тег найден, модифицируем атрибут src в соответсвии с протоколом формирования письма - <img src="cid:идентификатор" />
					//-> в качестве идентификатора используем имя файла
					$filename = substr($filepath,strrpos($filepath,"/")+1);
	
					//-> корректируем текст письма
					$message = str_replace($matches[0][$i],'<img src="cid:'.$filename.'" />',$message);
					//-> вызываем метод "прикрепляющий" изображение к письму
					$this->add_img_in_letter($filepath,$filename);
				}
			    
			}
			$message = iconv("UTF-8", "windows-1251", $message);
			
			if($this->multipart){
			    $text_message= "Content-Type:text/html; charset=\"windows-1251\"\r\n\r\n".$message."\r\n";
                if(isset($this->related_parts)){
				     $boundary_related = md5(uniqid(time()));
					 $header = 'Content-Type: multipart/related; boundary='.$boundary_related."\r\n\r\n";
					 $message  = $header;
					 $message .= "--".$boundary_related."\r\n";
					 $message .= $text_message."\r\n";
					 $message .= "--".$boundary_related."\r\n";
					 $message .= implode( "--".$boundary_related."\r\n",$this->related_parts);
					 $message .= "--".$boundary_related."--\r\n";
				}
			    
			    array_unshift($this->message_parts,$message);
			    $message =  "--".$this->boundary."\r\n".implode( "--".$this->boundary."\r\n",$this->message_parts). "--".$this->boundary."--\r\n";
			}
			
			$this->headers  = "MIME-Version: 1.0\r\n";
			$this->headers .= "Date: ". date('D, d M Y h:i:s O') ."\r\n";
			$this->headers .= "From: ".$from."\r\n";
			if(!$this->multipart) $this->headers .= "Content-Type:text/html; charset=\"windows-1251\"\r\n";
			if($this->added_headers) foreach($this->added_headers as $header) $this->headers .= $header;
			//echo $to."\r\n @ \r\n @ \r\n".$subject."\r\n @ \r\n @ \r\n".$message."\r\n @ \r\n @ \r\n".$this->headers;
			//exit;
			
			// если были ошибки прерываем дальшейшее выполнение функции
			if(!empty($this->errors)) return '[0,"'.implode("<br>",$this->errors).'"]';
			
			//if(mail($to,$subject,$message,$this->headers,"-f".$from)){ такой вариант почемуто не сработал
			//echo $message; exit;
			$subject = "=?utf-8?b?".base64_encode($subject)."?=";
			
			$f_flag =($add_f_flag)? '-fonline_service@apelburg.ru' : '';

			if(mail($to,$subject,$message,$this->headers,$f_flag)){
			
				 return '[1,"Cообщение отправлено"]';
			}
			else{
				 return '[0,"Cообщение не отправлено"]';
			}
	    }
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////     SENDING LETTER      ////////////////////////////////////////////
			////////////////////////////////////     СХЕМА ПРОТОКОЛА     ////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			/*
			
			Письмо с вложеннным изображением и тремя прикрепленными файлами
			Схема в картинке лежит в одной директории с файлом класса и назвается 
			
			
			Message-Id: <201503131348.t2DDmSEe030419@vps76774.vps.tech-logol.ru>
			X-Authentication-Warning: vps76774.vps.tech-logol.ru: apache set sender to kapitonoval2012@gmail.com using -f
			
			
			
			To: premier22@yandex.ru
			Subject: =?utf-8?b?0KLQtdC80LAg0L/QuNGB0YzQvNCw?=
			X-PHP-Originating-Script: 500:mail_class.php
			MIME-Version: 1.0
			Date: Fri, 13 Mar 2015 01:48:28 +0000
			From: andrey@apelburg.ru
			Cc: e-project1@mail.ru
			Return-Path: kapitonoval2012@gmail.com
	   !!!  Content-Type: multipart/mixed; boundary = "b3c881727c949658c508690556a4d793"
	   !!!  Content-Type: multipart/mixed; boundary = "b3c881727c949658c508690556a4d793"
	   !!!  Content-Type: multipart/mixed; boundary = "b3c881727c949658c508690556a4d793"
			
			
			
			--b3c881727c949658c508690556a4d793
			Content-Type: multipart/related; boundary=4b1fffed5d45e856bd9db66668fb69dd
			
			--4b1fffed5d45e856bd9db66668fb69dd
			Content-Type:text/html; charset="windows-1251"
			
			
			<br>
			<br>
			<br>
			<br>
			<br>
			<img src="cid:ii_14504dc8ea704442" />
			
			--4b1fffed5d45e856bd9db66668fb69dd
			Content-type: image/jpeg; name="header_logo.jpg"
			Content-Disposition: inline; filename="header_logo.jpg"
			Content-Transfer-Encoding: base64
			X-Attachment-Id: ii_14504dc8ea704442
			Content-ID: ii_14504dc8ea704442
			
			данные описывающие вложенную в текст картинку
			

			--4b1fffed5d45e856bd9db66668fb69dd--
			--b3c881727c949658c508690556a4d793
			Content-Type: application/pdf; name="=?utf-8?b?yX9C60LjRgNC40LvQu9C40YZlXzE4OTRfMjAx=?="
			Content-Transfer-Encoding: base64
			Content-Disposition: attachment; filename="=?utf-8?b?yX9C60LjRgNC40LvQu9C40YZlXzE4OTRfMjAx=?="
			
			данные описывающие прикрепленный файл
			
			
			--b3c881727c949658c508690556a4d793
			Content-Type: application/pdf; name="=?utf-8?b?bGF6ZXJfcHJpbnRfcHJpY2UucGRm?="
			Content-Transfer-Encoding: base64
			Content-Disposition: attachment; filename="=?utf-8?b?bGF6ZXJfcHJpbnRfcHJpY2UucGRm?="
			
			данные описывающие прикрепленный файл
			
			--b3c881727c949658c508690556a4d793
			Content-Type: application/pdf; name="=?utf-8?b?aGVhZGVyX2xvZ28uanBn?="
			Content-Transfer-Encoding: base64
			Content-Disposition: attachment; filename="=?utf-8?b?aGVhZGVyX2xvZ28uanBn?="
			
			данные описывающие прикрепленный файл
			
			
			--b3c881727c949658c508690556a4d793--
*/
			
			
			
		
			/*$boundary = md5(uniqid(time()));// разграничитель
			$boundary_2 = md5(uniqid(time()))."2";// разграничитель 2
		
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Date: ". date('D, d M Y h:i:s O') ."\r\n";
			$headers .= "Content-Type: multipart/mixed; boundary = \"".$boundary."\"\r\n";
			$headers .= "From: zakaz@vodkaspb.ru\r\n";
			$headers .= "Bcc: premier22@yandex.ru \r\n"; //
			
			
			
			$multipart  = "--".$boundary."\r\n";  
			   $multipart .= "Content-Type:multipart/alternative; boundary = \"".$boundary_2."\"\r\n"; 
			   $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."\r\n";
				  $multipart .= "Content-Type:text/plain; charset=windows-1251\r\n"; 
				  $multipart .= "\r\n"; // раздел между заголовками и телом plain-части 
				  $multipart .= $plain_content;   
				  $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."\r\n"; 
				  $multipart .= "Content-Type:text/html; charset=windows-1251\r\n";  
				  $multipart .= "\r\n"; // раздел между заголовками и телом html-части 
				  $multipart .= $html_content;   
				  $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."--\r\n";
			$multipart .= "--".$boundary."--\r\n";   
			
			
		*/
	}





 ?>
