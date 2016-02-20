<?php
    if (php_sapi_name() !== 'cli') die();
    header('Content-Type: text/html; charset=utf-8');
    //error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
    set_time_limit(0);
	

	// высчитываем корневую дерикторию сайта и объявляем переменную $_SERVER['DOCUMENT_ROOT'] для совместимости с другими скриптами
	// из полного пути файла вынимаем путь от корня до файла
	$full_file_path = __FILE__;
	$file_path_by_root = (strpos($full_file_path,"/")=== false)? 'os\libs\php\cron\\':'os/libs/php/cron/';
	$_SERVER['DOCUMENT_ROOT'] = substr($full_file_path,0,strpos($full_file_path,$file_path_by_root)-1);
	//echo "<br>[".$full_file_path."]   [".$_SERVER['DOCUMENT_ROOT']."]<br>";
	
	// адрес для ссылки на клиента используемой в тексте письма
	$href = 'http://www.apelburg.ru/os/?page=client_folder&section=planner&client_id=';
	
	include($_SERVER['DOCUMENT_ROOT'].'/os/libs/php/classes/planner_class.php');
	include($_SERVER['DOCUMENT_ROOT'].'/os/libs/config.php');
	
	////////////////  XXXXXXXXXXXXXXXXXXX   \\\\\\\\\\\\\\\
	
    /*
	Planner::get_traced_managers_ids();
	//echo "<pre>"; print_r(Planner::$traced_managers_ids); echo "</pre>";
	include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/manager_class.php");
	
	foreach(Planner::$traced_managers_ids as $manager_id){
	    //if($manager_id != 18) continue;
		$manager = new Manager($manager_id);
	    Planner::check($manager_id);
	    echo "<pre><br>".$manager->name.' '.$manager->last_name."<br>"; print_r(Planner::$warnings); echo "</pre>";
	}
	
	exit; */
	////////////////  XXXXXXXXXXXXXXXXXXX   \\\\\\\\\\\\\\\
	
	// получаем id менеджеров по которым отселживается ведение планов
	Planner::get_traced_managers_ids();
	//Planner::$traced_managers_ids = array(18);
	//echo "<pre>"; print_r(Planner::$traced_managers_ids); echo "</pre>";
	//exit;
	function correct_name($name){
	    if(preg_match_all("/[^А-Яа-яЁёa-zA-Z\w\.,№\(\)\"\'«»  -]+/u",$name,$matches)!=0)  $name = 'НЕКОРРЕКТНОЕ ИМЯ';
	   //if(preg_match_all("/[\\\/]+/",$name,$matches)>0)  $name = 'некорректное имя';

	   return $name;
	}
	if(count(Planner::$traced_managers_ids)>0){
	    include($_SERVER['DOCUMENT_ROOT'].'/os/libs/php/classes/mail_class.php');
		include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/manager_class.php");
	
        
		$to_chief = '4380055@gmail.com'; //  'premier22@yandex.ru' 'e-project1@mail.ru'
		$messages_to_chief = array();
	    $from = 'Apelburg Online Service<online_service@apelburg.ru>';
		$common_subject = 'Планы';
		
		// Запускаем по каждому менеджеру проверку посредством на наличие оповешений и если такие есть отправляем менеджерам сборные письма 
		// по каждому отдельному уровню оповещения и типу события 
		foreach(Planner::$traced_managers_ids as $manager_id){
		    Planner::check($manager_id);
			$manager = new Manager($manager_id);
			$fio = $manager->name.' '.$manager->last_name;
			$fio_dop = ''; // $manager->name.' '.$manager->last_name.' '; // для отладки
			//if($manager_id == 18) continue;
			//$mailed_managers[] = $manager_id;// для отладки
			
			if(isset(Planner::$warnings['yellow']) && count(Planner::$warnings['yellow'])>0){
			     foreach(Planner::$warnings['yellow'] as $event_type => $data){
				     if($event_type == 'звонок')  $message = $fio_dop.' ВНИМАНИЕ!!! ВЫ не совершали <b>звонков более 35 дней</b> клиенту(там):<br>';
					 if($event_type == 'встреча') $message = $fio_dop.' ВНИМАНИЕ!!! У Вас не было <b>встречи более 150 дней</b> c клиентом(тами):<br>';
					 foreach($data as $details){
						$clients_links[] = "<a href='".$href.$details['client_id']."'>".correct_name($details['client_name'])."</a>";
					 }
					 
					 // если есть ссылки на клиентов отправляем сообщения
					 if(isset($clients_links)){
						 // инфа для отправки руководителю
						 if($event_type == 'встреча') $messages_to_chief['yellow'][$event_type][] = $message_to_chief.implode("<br>",$clients_links); 
						 
						 // письмо менеджеру 
						 $to = $manager->email;
						 //$to = 'premier22@yandex.ru'; // для отладки
						 $subject = $common_subject.' '.$event_type.' - жёлтый уровень';
						 $mail = new Mail();
						 $out_data = $mail->send($to,$from,$subject,$message.implode("<br>",$clients_links),TRUE);
						 unset($clients_links);
						 sleep(2);
					 }
				 }
			     
				
			}
			if(isset(Planner::$warnings['red']) && count(Planner::$warnings['red'])>0){
			     foreach(Planner::$warnings['red'] as $event_type => $data){
				     if($event_type == 'звонок'){
					     $message_to_manager = $fio_dop.' ВНИМАНИЕ!!! ВЫ не совершали <b>звонков более 40 дней</b> клиенту(там):<br>';
						 $message_to_chief = 'менеджер '.$fio.' не совершал(а) <b>звонков более 40 дней</b> клиенту(там):<br>';
					 }
					 if($event_type == 'встреча'){
					     $message_to_manager = $fio_dop.' ВНИМАНИЕ!!! У Вас не было <b>встречи более 180 дней</b> c клиентом(тами):<br>';
						 $message_to_chief = 'менеджер '.$fio.' не совершал(а) <b>встречи более 180 дней</b> клиенту(там):<br>';
					 }
					 foreach($data as $details){
						//$clients_links[] = "<a href='".$href.$details['client_id']."'>".md5($details['client_name'])."</a>";
						$clients_links[] = "<a href='".$href.$details['client_id']."'>".correct_name($details['client_name'])."</a>";
					 }
					 // если есть ссылки на клиентов отправляем сообщения
					 if(isset($clients_links)){
					     // инфа для отправки руководителю
						 $messages_to_chief['red'][$event_type][] = $message_to_chief.implode("<br>",$clients_links);
						 
						 // письмо менеджеру 
						 $to = $manager->email;
					     //$to = 'premier22@yandex.ru'; // для отладки
						 $subject = $common_subject.' '.$event_type.' - красный уровень';
						 $mail = new Mail();
						 $out_data = $mail->send($to,$from,$subject,$message_to_manager.implode("<br>",$clients_links),TRUE);
						 unset($clients_links);
						 sleep(2);
					 }
				 }
			     
				
			}
			if(isset(Planner::$warnings['black']['звонок']) && count(Planner::$warnings['black']['звонок'])>0){
			     $message_to_manager = $fio_dop.'ВНИМАНИЕ!!! ВЫ не совершали <b>звонков более 50 дней</b> клиенту(там):<br>';
				 $message_to_chief = 'менеджер '.$fio.' не совершал(а) <b>звонков более 50 дней</b> клиенту(там):<br>';
			     foreach(Planner::$warnings['black']['звонок'] as $details){
				    $clients_links[] = "<a href='".$href.$details['client_id']."'>".correct_name($details['client_name'])."</a>";
				 }
				 // если есть ссылки на клиентов отправляем сообщения
				 if(isset($clients_links)){
					 // инфа для отправки руководителю 
					 $messages_to_chief['black']['звонок'][] = $message_to_chief.implode("<br>",$clients_links);
					 
					 // письмо менеджеру 
					 $to = $manager->email;
					 //$to = 'premier22@yandex.ru'; // для отладки
					 $subject = $common_subject.'  звонок - чёрный уровень';
					 $mail = new Mail();
					 $out_data = $mail->send($to,$from,$subject,$message_to_manager.implode("<br>",$clients_links),TRUE);
					 unset($clients_links);
					 sleep(2);
				 }
			}
		}
		
		// отправка писем руководителю, если есть что отправлять 
		// и текущее время меньше 12 часов(чтобы не отрабатывало при второй рассылке (вечерней)) 
		// echo ' -- '.count($messages_to_chief);echo date('G');if((int)date('G')<12) echo 'G';
	    if((int)date('G')<12 && count($messages_to_chief)>0){
		     $level_ru = array('green'=>'зеленый','yellow'=>'жёлтый','red'=>'красный','black'=>'чёрный');
		    // отправляем одно общее письмо по каждому уровню
		    foreach($messages_to_chief as $level => $data){
				
			    foreach($data as $event_type => $details){
				    $subject = $common_subject.' '.$event_type.'  - '.$level_ru[$level].' уровень';
					$message = implode("<br><br>",$details);//count($details)
				
					$mail = new Mail();
					$mail->add_cc('ab@apelburg.ru');
					$mail->add_bcc('premier22@yandex.ru');
					$mail->add_bcc('e-project1@mail.ru');
					
					$out_data = $mail->send($to_chief,$from,$subject,$message,TRUE);
					sleep(5);
			    }
			}
				 
		}
	}
	/*
	$file_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/libs/php/cron/test.txt';
	$fd = fopen($file_name,"a");
	fwrite($fd,date('d M Y h:i:s').' '.implode(",",$mailed_managers)."\r\n");
	fclose($fd);*/
	
?>