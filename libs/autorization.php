<?php

	if(isset($_GET['set_user_id'])){ 
		if(!isset($_SESSION['access']['come_back_in_own_profile'])) $_SESSION['access']['come_back_in_own_profile'] = $_SESSION['access']['user_id']; 
		$_SESSION['access']['user_id'] = $_GET['set_user_id'];
		header ('Location:?'.addOrReplaceGetOnURL('','set_user_id'));
		exit;
	}
	if(isset($_GET['come_back_in_own_profile'])){ 
		$_SESSION['access']['user_id'] = $_SESSION['access']['come_back_in_own_profile']; 
		unset($_SESSION['access']['come_back_in_own_profile']);
		header ('Location:?'.addOrReplaceGetOnURL('','come_back_in_own_profile'));
		exit;
	}
	
	if(!empty($_SESSION['access'])){

		$result = select_manager_data($_SESSION['access']['user_id']);
		
	    $user_id = $result[0]['id'];
		$user_nickname = $result[0]['nickname'];
		$user_name = $result[0]['name'];
		$user_last_name = $result[0]['last_name'];
		$user_department = $result[0]['department'];
		$position = $result[0]['position'];
	    $user_status = $result[0]['access'];
	}
	else{
	    $user_id = '';
	    $user_nickname = '';
		$user_name = '';
		$user_last_name = '';
		$user_department = '';
		$position = '';
	    $user_status = '';
	}
	
	$authentication_menu_dop_items = '';
	
	
	
	if($user_status=='1'){
	    $display['firm'] = 'none';
		$display['invoiceforpay'] = 'none';
		$display['reports'] = 'none';
		
		//
		$arr = get_managers_list();
		$authentication_menu_dop_items .= '<div class="cap1"><nobr>войти как:</nobr></div>';
		for($i = 0; $i < count($arr);$i++)
		{
		    $authentication_menu_dop_items .= '<div><a href="?'.addOrReplaceGetOnURL('set_user_id='.$arr[$i]['id']).'"><nobr>'.$arr[$i]['name'].' '.$arr[$i]['last_name'].'</nobr></a></div>';
		}
		 
		
	}
	if(isset($_SESSION['access']['come_back_in_own_profile'])) $authentication_menu_dop_items .= '<div class="cap2"><a href="?'.addOrReplaceGetOnURL('come_back_in_own_profile=yes').'"><nobr>вернуться в свой профиль</nobr></a></div>';
?>