<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['planner']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
	// чтобы не гонялись между собой - section= business_offers,planner
	save_way_back(array('section=agreements','section=business_offers','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	
	include 'planner_controller.php';
	//echo $content;
	//
    include ROOT.'/skins/tpl/common/quick_bar.tpl';
	// планка клиента
	include_once './libs/php/classes/client_class.php';
	$Client = new Client;
	$Client->get_client__information((int)$_GET['client_id']);
	// шаблон страницы
	include ROOT.'/skins/tpl/client_folder/planner/show.tpl';

?>
	
	