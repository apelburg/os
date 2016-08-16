<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['cabinet']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	$title = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0 )?'Карточка клиента':'';
	$title = (($title!="")?$title.'/':'').'Запросы';
	// $searchPlaceholder = 'по клиенту';

	save_way_back(array('page=cabinet'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	
	include_once './libs/php/classes/comments_class.php';
	new Comments_for_query_class;
	new Comments_for_order_class;
	$PositionComments = new Comments_for_order_dop_data_class;




    include_once './libs/php/classes/cabinet/pagination.class.php';
    include_once './libs/php/classes/os_form_class.php';


    include_once './libs/php/classes/supplier_class.php';
	include_once './libs/php/classes/rt_position_no_catalog_class.php';
	include_once './libs/php/classes/cabinet/cabinet_class.php';		
	include_once './libs/php/classes/cabinet/cabinet_general_class.php';		


	ob_start();	
		$CABINET = new Cabinet_general();
		$content = ob_get_contents();
	ob_get_clean();


	//////////////////////////
	//	search template
	//////////////////////////
	include'./skins/tpl/common/quick_bar.tpl';
	
	/////////////////////////////////
	//	краткая информация по клиенту
	/////////////////////////////////
		if(isset($_GET['client_id']) && $_GET['client_id']!=""){
			include_once './libs/php/classes/client_class.php';
			$Client = new Client();
            $Client->get_client__information($_GET['client_id']);
		}

	

	//////////////////////////
	//	Cabinet template
	//////////////////////////
	// echo $CABINET->get_menu_top_center_Html();
	include'./skins/tpl/cabinet/show.tpl';
	
	unset($content);
?>