<?php
    // client_details
	$client_id = (isset($_GET['client_id']))? $_GET['client_id'] :((isset($_POST['client_id']))? $_POST['client_id']: '') ;
    $client_data_arr = select_all_client_data($client_id);
	//echo '<pre>'; print_r($client_data_arr); echo '</pre>';
	$client_name = $client_data_arr['company'];
	$client_reg_date_arr = explode('-',$client_data_arr['set_client_date']);
	@$client_reg_date = $client_reg_date_arr[2].'.'.$client_reg_date_arr[1].'.'.substr($client_reg_date_arr[0],2);
	
	
	// кураторы //////////////
	 $manager_nickname = '';
	 
	 //print_r($_POST);
	 $manager_id_arr = detect_manager_for_client($client_id);
	 $forbidd_flag = true;	
	 foreach($manager_id_arr as $mngr_id){
	     if($user_id == $mngr_id)$forbidd_flag = false;
	     $manager_nickname .= get_manager_nickname_by_id($mngr_id).', ';	 
	 }
	 $manager_nickname = trim($manager_nickname,', ');
	 if($forbidd_flag && $user_status!='1'){
	     echo 'данная страница отсутствует';
		 exit;
	 }   
	 // end кураторы /////////
	///////////////////////////////////////// AJAX ////////////////////////////////////////////////////
	
	if(isset($_GET['reset_calculation_data_in_db'])){
	     set_changes_into_rt($_GET['data'],$_GET['row_id'],$_GET['control_num']);
		 exit;
	
	} 
	
	if(isset($_GET['reset_switching_calculation_marker_in_db'])){
	    // echo $_GET['status'].' '.$_GET['row_id'].' '.$_GET['control_num'];
	     reset_switching_calculation_marker_in_rt($_GET['row_id'],$_GET['status'],$_GET['control_num']);
		 exit;
	
	} 

	///////////////////////////////////////////////////////////////////////////////////////////////////
	$main_cont_face_data = get_main_client_cont_face($client_id);
	//echo '<pre>'; print_r($main_cont_face_data); echo '</pre>';
	
	
    ob_start();	
	 
    switch($subsection){
	 
	    case 'calculate_table':
	    include 'client_folder/calculate_table/calculate_table_controller.php';
	    break;

	    case 'client_card_table':		
		include 'client_folder/client_card/client_card_controller.php';
		break;
		
		case 'business_offers':
	    include 'client_folder/business_offers/business_offers_controller.php';
	    break;

	    default: 
	    include 'client_folder/default_controller.php';
	    break;
	
	}
	
	$content = ob_get_contents();
	ob_get_clean();
	
	include('./skins/tpl/common/quick_bar.tpl');
	// отключаем для карточки клиента
	if($subsection!="client_card_table"){
		include('./skins/tpl/clients/client_folder/client_details_field_additional.tpl');
	}
	include('./skins/tpl/clients/client_folder/client_details_field_general.tpl');
	echo $content;
    unset($content);
    
?>