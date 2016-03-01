<?php
  
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['agreement']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
   
	//print_r($_GET);
	//print_r($_SESSION);
	//print_r($_SESSION['data_for_specification']);
	set_time_limit(0);
	
	$section  = (!empty($_POST['section']))? $_POST['section']: ((!empty($_GET['section']))? $_GET['section']: FALSE ) ;
	$client_id = (!empty($_GET['client_id']))? (int)$_GET['client_id']: FALSE ;
	$agreement_id = (!empty($_GET['agreement_id']))? (int)$_GET['agreement_id']: FALSE ;
	$oferta_id = (!empty($_GET['oferta_id']))? (int)$_GET['oferta_id']: FALSE ;
	$requisit_id = (!empty($_GET['requisit_id']))? (int)$_GET['requisit_id']: FALSE ;
	$agreement_type = (!empty($_GET['agreement_type']))? $_GET['agreement_type']: FALSE ;
	$specification_num = (!empty($_GET['specification_num']))? (int)$_GET['specification_num']: FALSE ;
	$form_data = (!empty($_POST['form_data']))? $_POST['form_data'] : FALSE ;
	
	/////////////////////////////////// AJAX //////////////////////////////////////
	if(isset($_GET['update_specification_ajax']))
	{
		$field_val = $_POST['field_val'];
		$field_val = strip_tags($field_val, '<br><br />');
		$field_val = str_replace("'",'`',$field_val);
		//$field_val = nl2br($field_val);
		
		update_specification($_POST['id'],$_POST['field_name'],$field_val);
		exit;
    }
	
	if(isset($_GET['update_oferta_ajax']))
	{
		$field_val = $_POST['field_val'];
		$field_val = strip_tags($field_val, '<br><br />');
		$field_val = str_replace("'",'`',$field_val);
		//$field_val = nl2br($field_val);
		
		include_once(ROOT."/libs/php/classes/agreement_class.php");
		Agreement::update_oferta($_POST['id'],$_POST['field_name'],$field_val);
		exit;
    }
	
	if(isset($_GET['change_spec_num']))
	{
	    //echo $_GET['specification_num'].' '.$_GET['new_specification_num'];
		echo set_new_num_for_specification($_GET['path'],$_GET['client_id'],$_GET['agreement_id'],$_GET['specification_num'],$_GET['new_specification_num']);
		exit;
    }
	
	if(isset($_GET['update_specification_common_fields_ajax']))
	{
	    $field_val = strip_tags($_POST['field_val']);
		$field_val = str_replace("'",'`',$field_val);
		
		update_specification_common_fields($_POST['id'],$_POST['field_name'],$field_val);
		exit;
    }
	
	if(isset($_GET['update_oferta_common_fields_ajax']))
	{
	    $field_val = strip_tags($_POST['field_val']);
		$field_val = str_replace("'",'`',$field_val);
		
		include_once(ROOT."/libs/php/classes/agreement_class.php");
		Agreement::update_oferta_common_fields($_POST['id'],$_POST['field_name'],$field_val);
		exit;
    }
	if(isset($_GET['update_agreement_finally_sheet_ajax']))
	{
		$field_val = strip_tags($_POST['field_val']);
		$field_val = str_replace("'",'`',$field_val);
		
		update_agreement_finally_sheet($_POST['id'],$_POST['field_name'],$field_val);
		exit;
    }
	
	/////////////////////////////////// AJAX //////////////////////////////////////
  
    switch($section)
	{
         
		 
		 case 'presetting':
		 include 'presetting_controller.php';
		 break;	 
		 
		 case 'choice':
		 include 'choice_controller.php';
		 break;
		 
		 case 'choice_2':
		 include 'choice_2_controller.php';
		 break;
		 
		 case 'long_term_agr_setting':
		 include 'long_term_agr_setting_controller.php';
		 break;
		 
		 case 'short_term_agr_setting':
		 include 'short_term_agr_setting_controller.php';
		 break;
		 
		 case 'prepayment':
		 include 'prepayment_controller.php';
		 break;
		 
		 case 'delivery':
		 include 'delivery_controller.php';
		 break;
		 
		 case 'save_agreement':
		 include 'save_agreement_controller.php';
		 break;
		 
		 case 'agreement_editor':
		 include 'agreement_editor_controller.php';
		 break;
		 
		 case 'agreement_full_editor':
		 include 'agreement_full_editor_controller.php';
		 break;
		 
		 case 'specification_full_editor':
		 include 'specification_full_editor_controller.php';
		 break;
		 
		 case 'oferta_full_editor':
		 include 'oferta_full_editor_controller.php';
		 break;
		 
		 case 'our_agreements_full_editor':
		 include 'our_agreements_full_editor_controller.php';
		 break;
		 
		 case 'our_specifications_full_editor':
		 include 'our_specifications_full_editor_controller.php';
		 break;
		 
		 case 'editing_client_requisites':
		 include 'editing_client_requisites_controller.php';
		 break;
		 
		 case 'specification_editor':
		 include 'specification_editor_controller.php';
		 break;
		 
		 case 'save_client_requisites':
		 include 'save_client_requisites_controller.php';
		 break;
		 
		 case 'set_as_basic':
		 include 'set_as_basic_controller.php';
		 break;
		 
		 case 'short_description':
		 include 'short_description_controller.php';
		 break;
		  
		 case 'signator_choosing':
		 include 'signator_choosing_controller.php';
		 break;
		 
		 case 'delete_agreement':
		 include 'delete_agreement_controller.php';
		 break;
		 
		 case 'delete_specification':
		 include 'delete_specification_controller.php';
		 break;	 
		 
		 case 'delete_oferta':
		 include 'delete_oferta_controller.php';
		 break;	 
		 
		 default:
		 include 'default_controller.php';
		 break;
    }

?>