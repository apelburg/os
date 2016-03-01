<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['business_offers']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	// чтобы не гонялись между собой - section= business_offers,planner
	save_way_back(array('section=agreements','section=business_offers','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	
	include_once(ROOT."/libs/php/classes/art_img_class.php");
	include_once(ROOT."/libs/php/classes/com_pred_class.php");
	
	///////////////////////////////////////// AJAX ////////////////////////////////////////////////////
	
	if(isset($_GET['send_kp_by_mail'])){
	    // echo  $_GET['send_kp_by_mail'];exit;
		
		include_once(ROOT."/libs/php/classes/manager_class.php");
		$manager = new Manager($user_id);
		
		
		include_once(ROOT."/libs/php/classes/client_class.php");
		$client_mails = Client::cont_faces_data_for_mail($client_id);

	    $kp_id = $_GET['send_kp_by_mail'];
		$kp_filename = Com_pred::prepare_send_mail($kp_id,$client_id,$user_id);
		$theme = Com_pred::fetch_theme($kp_id);
        //$kp_filename = ROOT.'/data/com_offers/1894apelburg_1894_2015_56_01.pdf';
		
		$main_window_tpl_name = ROOT.'/skins/tpl/client_folder/business_offers/send_mail_window.tpl';
		$fd = fopen($main_window_tpl_name,'r');
		$main_window_tpl = fread($fd,filesize($main_window_tpl_name));
	    fclose($fd);
        
        // кодируем данные в формате HTML перед передачей в формате JSON
		$main_window_tpl = base64_encode($main_window_tpl); 
		
		
		// $message_tpl_filenames = array('recalculation','new_kp_new_client','new_kp');
		$message_tpl_filenames = array('empty','new_kp_new_client','order_performance');
		foreach($message_tpl_filenames as $tpl_filename){
			$tpl_path = ROOT.'/skins/tpl/common/mail_tpls/'.$tpl_filename.'.tpl';
			$fd = fopen($tpl_path,'r');
			$tpl = fread($fd,filesize($tpl_path));
			$tpl = str_replace('[MANAGER_DATA]',convert_bb_tags($manager->mail_signature),$tpl);
			fclose($fd);
			//$message_tpls[] = '"'.$tpl_filename.'":"'.base64_encode($tpl).'"';
			$message_tpls[$tpl_filename] = base64_encode($tpl);
		}
        $obj["kp_filename"] = $kp_filename;
		$obj["client_mails"] = $client_mails;
		$obj["manager_mails"] = array($manager->email,$manager->email_2);
		$obj["theme"] = $theme;
		$obj["main_window_tpl"] = $main_window_tpl;
		if(isset($message_tpls)) $obj["message_tpls"] = $message_tpls;
		echo json_encode($obj);
	    exit;
	}
	
	if(isset($_POST['send_kp_by_mail_final_step'])){
	     //echo $_POST['send_kp_by_mail_final_step'];
		
		if(($mail_details =json_decode($_POST['send_kp_by_mail_final_step'])) === NULL){
		     // echo $_POST['send_kp_by_mail_final_step'];
		     echo '[0,"Ошибка №109345 - конвертация данных"]';
			 exit;
		}

		// var_dump($mail_details); exit;

        // вызываем класс выполняющий отправку сообщения
		include_once(ROOT."/libs/php/classes/mail_class.php");
		$mail = new Mail();
		// ставим в копию того отчьего адреса отправляется письмо
		$mail->add_cc($mail_details->from);
		// $mail->add_bcc('e-project1@mail.ru');
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
	
	if(isset($_GET['change_comment'])){
	     if(isset($_POST['id'])) Com_pred::change_comment($_POST['id'],$_POST['field_val']);
		 if(isset($_POST['file_name'])) Com_pred::change_comment_old_version($_POST['file_name'],$_POST['field_val']);
		 exit;
	 }
	 if(isset($_GET['set_recipient'])){
	     // print_r($_GET);
		 Com_pred::set_recipient($_GET['set_recipient'],$_GET['row_id']);
		 exit;
	 }
	 if(isset($_POST['AJAX']) && isset($_POST['action'])){
		 if($_POST['action']=='changeKpPosDescription') Com_pred::changePosDescription($_POST['id'],$_POST['field_val'],$_POST['field_name']);
		 if($_POST['action']=='changeKpRepresentedData') Com_pred::changeRepresentedData($_POST['id'],$_POST['field_val'],$_POST['field_name']);//,$_POST['field_name']
		 exit;
	 }
	
		if(isset($_POST['AJAX'])){
				
		    if($_POST['AJAX']=='edit_query_theme'){
		        include_once (ROOT.'/libs/php/classes/rt_class.php');
		        RT::save_theme($_POST['query_num'],$_POST['theme']);
				echo '{"response":"OK"}';
				exit;
			}
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
		 $create_list = FALSE;
	}
	if(isset($_GET['show_old_kp'])){
		 $rows = Com_pred::create_list('',$client_id,array('type'=>'old','kp'=>$_GET['show_old_kp']));
		 $create_list = FALSE;
		 $in_blank_view = Com_pred::open_old_kp($_GET['show_old_kp']);
		 
	}
	if(isset($_GET['show_kp_in_blank'])){
	     $kp_id = (int)$_GET['show_kp_in_blank'];
		 $rows = Com_pred::create_list($query_num,$client_id,array('type'=>'new','kp'=>$kp_id));
		 $create_list = FALSE;
		 $in_blank_view = '<div style="margin:20px 0 0 10px;"><a href="?'.$_SERVER['QUERY_STRING'].'&save_in_pdf='.$kp_id.'" class="someABtn" >сохранить на диск</a></div>';
		 $in_blank_view .= Com_pred::open_in_blank($kp_id,$client_id,$user_id,false);
		 //$detailed_view .= '<a href="?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'">open_in_blank</a>';
		 
	}
	if(isset($_POST['saveChangesInBase'])){
		Com_pred::saveKpDisplayChangesInBase($_POST['kp_id'],$_POST['dataJSON']);
		exit;
	}
	if(isset($_POST['saveChangesRadioInBase'])){
		Com_pred::saveChangesRadioInBase($_POST['kp_id'],$_POST['val']);
		exit;
	}
	
	
	 if(isset($_GET['delete_com_offer'])){
          
	     if(isset($_GET['old_version'])) Com_pred::delete_old_version($_GET['delete_com_offer'],intval($_GET['client_id']),$_GET['id']/* must be string*/);
		 else Com_pred::delete($_GET['delete_com_offer']);
		 //exit;
		 header('Location:?'.addOrReplaceGetOnURL('client_id='.$client_id,'delete_com_offer&old_version&id'));
	 }
	 /////////////////////////////////////// end Временно /////////////////////////////////////// 
	
	
	
	// шаблон поиска
	include ROOT.'/skins/tpl/common/quick_bar.tpl';
	
	// планка клиента
	include_once './libs/php/classes/client_class.php';
	Client::get_client__information($_GET['client_id']);
	
	include 'business_offers_controller.php';

	// шаблон страницы
	//include ROOT.'/skins/tpl/client_folder/business_offers/show.tpl';

?>
	
	