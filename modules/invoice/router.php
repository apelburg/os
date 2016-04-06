<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt_position']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
    
	include 'controller.php';

	//////////////////////////
	//	search template
	//////////////////////////
	include'./skins/tpl/common/quick_bar.tpl';
	// ``
	/////////////////////////////////
	//	краткая информация по клиенту
	/////////////////////////////////
	if(isset($_GET['client_id']) && $_GET['client_id']!=""){
		include_once './libs/php/classes/client_class.php';
		Client::get_client__information($_GET['client_id'],'invoice');
	}

	// шаблон поиска
	include'./skins/tpl/invoice/show.tpl';
	

	

	


	

	

	

	
	