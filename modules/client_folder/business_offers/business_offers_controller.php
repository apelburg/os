<?php
   
    $quick_button = '<div class="quick_button_div"><a href="#11" class="button">&nbsp;</a></div>';
	$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';
	
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/art_img_class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/com_pred_class.php");

	///////////////////////////////////////// AJAX ////////////////////////////////////////////////////
	
	if(isset($_GET['send_kp_by_mail'])){
	    //echo  $_GET['send_kp_by_mail'];
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/manager_class.php");
		$manager = new Manager($user_id);
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/client_class.php");

	    $kp_id = $_GET['send_kp_by_mail'];
		$kp_filename = Com_pred::prepare_send_mail($kp_id,$client_id,$user_id);
        //$kp_filename = ROOT.'/data/com_offers/1894apelburg_1894_2015_56_01.pdf';
		
		$main_window_tpl_name = ROOT.'/skins/tpl/clients/client_folder/business_offers/send_mail_window.tpl';
		$fd = fopen($main_window_tpl_name,'r');
		$main_window_tpl = fread($fd,filesize($main_window_tpl_name));
	    fclose($fd);
        
        // кодируем данные в формате HTML перед передачей в формате JSON
		$main_window_tpl = base64_encode($main_window_tpl); 
		
		
		$message_tpl_filenames = array('recalculation','new_kp_new_client','new_kp',);
		foreach($message_tpl_filenames as $tpl_filename){
			$tpl_path = ROOT.'/skins/tpl/common/mail_tpls/'.$tpl_filename.'.tpl';
			$fd = fopen($tpl_path,'r');
			$tpl = fread($fd,filesize($tpl_path));
			$tpl = str_replace('[MANAGER_DATA]',convert_bb_tags($manager->mail_signature),$tpl);
			fclose($fd);
			$message_tpls[] = '"'.$tpl_filename.'":"'.base64_encode($tpl).'"';
		}
		
		echo '{
		       "kp_filename":"'.$kp_filename.'",
		       "client_mails":[{"person":"менеджер - Наталья","mail":"premier22@yandex.ru"},{"person":"директор - Елена","mail":"premier_22@yandex.ru"}],
			   "manager_mails":["'.$manager->email.'","'.$manager->email_2.'"],
			   "main_window_tpl":"'.$main_window_tpl.'",';
		if(isset($message_tpls)) echo '"message_tpls":{'.implode(',',$message_tpls).'}';
		echo '}';
	    exit;
	}
	
	if(isset($_POST['send_kp_by_mail_final_step'])){
	    //var_dump(json_decode($_POST['send_kp_by_mail_final_step']));exit;
		$mail_details =json_decode($_POST['send_kp_by_mail_final_step']);

        // вызываем класс выполняющий отправку сообщения
		include(ROOT."/libs/php/classes/mail_class.php");
		$mail = new Mail();
		$mail->add_bcc('box1@yandex.ru');
		$mail->add_cc('e-project1@mail.ru');
		if($mail_details->attached_files){
		    foreach($mail_details->attached_files as $file) $mail->attach_file($_SERVER['DOCUMENT_ROOT'].$file);
		}
		
		// Декодируем текст сообщения
	    $message = base64_decode($mail_details->message);
		$message = urldecode($message);
		$out_data = $mail->send($mail_details->to,$mail_details->from,$mail_details->subject,$message);
		// если отправка прошла успешно
		// a. сохраняем дату в таблицу COM_PRED_LIST
		// b. удаляем предыдущий(ие) ПДФки по этому КП  
		$out_data_arr = json_decode($out_data);
		if($out_data_arr[0]=='1'){
		     Com_pred::save_mail_send_time($mail_details->kp_id);
			 Com_pred::clear_client_kp_folder($mail_details->kp_id,$mail_details->attached_files);
		}
		
		echo $out_data;
	    exit;
	}
	
	
	
	///////////////////////////////////////  END AJAX  /////////////////////////////////////////////////
	
	
	
	/////////////////////////////////////// Временно /////////////////////////////////////// 
	$create_list = TRUE;
	if(isset($_GET['save_in_pdf'])){
	     $kp_id=(int)$_GET['save_in_pdf'];
	     Com_pred::save_in_pdf($kp_id,$client_id,$user_id);
	}
	if(isset($_GET['show_kp'])){
	     // показать детали КП
		 $kp_id = (int)$_GET['show_kp'];
		 $rows = Com_pred::create_list($query_num,$client_id,array('type'=>'new','kp'=>$kp_id));
		 $detailed_view = Com_pred::open_in_tbl($_GET['show_kp']); 
		 //$detailed_view .= '<a href="?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'">open_in_blank</a>';
		 $detailed_view .= '<br><a href="?'.$_SERVER['QUERY_STRING'].'&save_in_pdf='.$kp_id.'">сохранить на диск</a>';
		 $create_list = FALSE;
	}
	if(isset($_GET['show_old_kp'])){
		 $rows = Com_pred::create_list($client_id,array('type'=>'old','kp'=>$_GET['show_old_kp']));
		 $create_list = FALSE;
		 $in_blank_view = Com_pred::open_old_kp($_GET['show_old_kp']);
		 
	}
	if(isset($_GET['show_kp_in_blank'])){
	     $kp_id = (int)$_GET['show_kp_in_blank'];
		 $rows = Com_pred::create_list($query_num,$client_id,array('type'=>'new','kp'=>$kp_id));
		 $create_list = FALSE;
		 $in_blank_view = Com_pred::open_in_blank($kp_id,$client_id,$user_id,true);
		 //$detailed_view .= '<a href="?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'">open_in_blank</a>';
		 $in_blank_view .= '<br><a href="?'.$_SERVER['QUERY_STRING'].'&save_in_pdf='.$kp_id.'">сохранить на диск</a>';
	}
	/////////////////////////////////////// end Временно /////////////////////////////////////// 
	
	
	 if(isset($_GET['delete_com_offer'])){
	     if($_GET['old_version']) Com_pred::delete_old_version($_GET['delete_com_offer'],intval($_GET['client_id']),$_GET['id']/* must be string*/);
		 else Com_pred::delete($_GET['delete_com_offer']);
		 
		 header('Location:?'.addOrReplaceGetOnURL('client_id='.$client_id,'delete_com_offer&old_version&id'));
	 }
	
	
	// Собираем ряды для таблицы коммерческих предложений
	// выборка данных из базы данных производится на основании номера зпароса для КП нового типа 
	// и на основании client_id для КП старого типа
	if($create_list) $rows = Com_pred::create_list($query_num,$client_id);
	// Подключаем шаблон таблицы списка коммерческих предложений
	include ('skins/tpl/client_folder/business_offers/list_table.tpl');
	if(isset($detailed_view)) include ('skins/tpl/client_folder/business_offers/detailed_view.tpl');
	if(isset($in_blank_view)) include ('skins/tpl/client_folder/business_offers/in_blank_view.tpl');
	
?>

