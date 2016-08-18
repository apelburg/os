<?php
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

    // подключаем галлерею
    include_once './libs/php/classes/rt_KpGallery.class.php';
    new rtKpGallery();

    // класс комментов к запросу
	include './libs/php/classes/comments_class.php';
	new Comments_for_query_class();
	
    // класс центр услуг
	include_once ('./libs/php/classes/print_calculators_class.php');
	include_once ('./libs/php/classes/serviceCenter.class.php');
	new ServiceCenter();

    // client_details
	if(!$section || $section=='rt' || $section=='business_offers'  || $section=='agreements' || $section=='planner'){
		$client_id = (isset($_GET['client_id']) && $_GET['client_id']!='0')? $_GET['client_id'] :((isset($_POST['client_id']) && $_GET['client_id']!='0')? $_POST['client_id']: FALSE) ;
	
		if(!$client_id) echo '<div id="clientDontFound">Клиент не определен</div><script>$("#clientDontFound").dialog({autoOpen: false ,title: "Ошибка",modal:true,width: 600,close: function() { location = history.go(-1);}});
			$("#clientDontFound").dialog("open");</script>'; //class="alert_window"
		///////////////////////////////////////////    информация о клиенте   ////////////////////////////////////////////////
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
	
		 // end кураторы /////////
		$main_cont_face_data = get_main_client_cont_face($client_id);
		///////////////////////////////////////////  end  информация о клиенте   ////////////////////////////////////////////////
	 }
	
	 $planner_display = '<div class="quick_button_circle">
							<div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_2.png); border-color:red">
								<div class="quick_button_circle__alert"></div>
							</div>
							<div class="quick_button_circle__text"><a href="?page=client_folder&section=planner&client_id='.$client_id.'">Планировщик</a></div>
						</div>'; 
	
	
	ob_start();	
	 
	switch($section){
	 
		case 'rt':
		include 'rt/router.php';
		break;

		case 'order_tbl':
		include 'order_tbl/router.php';
		break;

		case 'rt_position':
		include 'rt_position/router.php';
		break;
		
		case 'business_offers':
		include 'business_offers/router.php';
		break;
		
		case 'agreements':
		include 'agreements/router.php';
		break;
		
		case 'planner':
		include 'planner/router.php';
		break;

		default: 
		include 'rt/router.php';
		break;
	
	}
	
	$content = ob_get_contents();
	ob_get_clean();


   

	include'./skins/tpl/client_folder/show.tpl';
	
	unset($content);
?>