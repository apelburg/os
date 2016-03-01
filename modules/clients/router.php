<?php

    // ** БЕЗОПАСНОСТЬ ** 
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['clients']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	$searchPlaceholder = 'по базе клиентов';
	save_way_back(array('page=clients'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	
     include_once('./libs/php/classes/client_class.php');
	 
	 /////////////////////////////////// AJAX //////////////////////////////////////
	
	 if(isset($_GET['update_tr_field_ajax']))
	 {
	      echo('update_tr_field_ajax');
		 update_tr_field(intval($_POST['id']),$_POST['field_name'],$_POST['field_val']);
		 exit;//cor_data_for_SQL($_POST['field_name']),cor_data_for_SQL($_POST['field_val'])
	 }
	
	 
	 if(isset($_GET['remember_row_id']))
	 {
	     unset($_SESSION['copy_row']);
	     $_SESSION['copy_row']['id'] = $_GET['id'];
		 $_SESSION['copy_row']['control_num'] = $_GET['control_num'];
		 exit;
	 }
	 
	 if(isset($_GET['insert_copied_row']))
	 {
	     insert_copied_row((int)$_GET['id'],(int)$_GET['control_num']);
		 exit;
     }
	 /*  УДАЛИТЬ ПОСЛЕ ЗАПУСКА
	  if(isset($_GET['ajax_make_com_offer']))
	 {
         include_once(ROOT."/libs/php/classes/com_pred_class.php");
	     $id_arr = explode(";",$_GET['data']);
		 
		 echo Com_pred::save_to_tbl($id_arr,(int)$_GET['conrtol_num']);

		 /* старый вариант создания коммерческого предложени
		 echo make_com_offer($id_arr,(int)$_GET['stock'],$_GET['order_num']/ *string* /,$_GET['client_manager_id']/ *string* /,(int)$_GET['conrtol_num']);
		  * /
		 exit;
     }
	 */
	  if(isset($_GET['ajax_set_samples_list']))
	 {

	     $id_arr = explode(";",$_GET['data']);

		 echo (int)$_GET['conrtol_num'].'<br>';
		 print_r($id_arr);
		 exit;
     }
	 
	 if(isset($_POST['change_kp_comment_old_version'])){
 
		 $file_name = $_POST['file_name'];
		 $file_comment = urldecode($_POST['change_kp_comment_old_version']);
		 Com_pred::change_comment_old_version($file_name,$file_comment);
		 exit;
	 }

	 if(isset($_GET['get_client_cont_faces']))
	 {
	     echo Client::get_cont_faces_ajax($_GET['get_client_cont_faces']);
		 exit;
	 }
	 
	 if(isset($_GET['set_manager_for_order']))
	 {
	 	 set_manager_for_order_ajax($_GET['row_id'],$_GET['set_manager_for_order'],$_GET['control_num']);
		 exit;
	 }
	 
	 if(isset($_GET['set_status_master_btn']))
	 {
	     
		 $id = (strpos($_GET['id'],';') != false)? str_replace(";","','",$_GET['id']):$_GET['id'];
		 //echo $id;
		 set_status_master_btn($id,$_GET['set_status_master_btn'],$_GET['control_num']);
		 //echo $_GET['id'].' '.$_GET['set_status_master_btn'].' '.$_GET['control_num'];
		 exit;
	 }
	 /////////////////////////////////// AJAX //////////////////////////////////////
	 
	 ob_start();	
	 
	 switch($section){
	 
	   case 'clients_list':
	   include 'clients_list_controller.php';
	   break;
	   
	   case 'client_folder':
	   include 'client_folder_controller.php';
	   break;

	   default: 
	   include 'default_controller.php';
	   break;
	
	}
	


	$content = ob_get_contents();
	ob_get_clean();

	
	include'./skins/tpl/clients/show.tpl';
	
	unset($content);
?>