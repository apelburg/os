<?php
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
	ob_start();	
	 
	switch($section){
	 
		case 'rt':
		include 'rt/router.php';
		break;

		case 'order_tbl':
		include 'order_tbl/router.php';
		break;

		case 'order_art_edit':
		include 'order_art_edit/router.php';
		break;
		
		case 'business_offers':
		include 'business_offers/router.php';
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