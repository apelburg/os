<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt_position']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
    
	include 'controller.php';

	// шаблон поиска
	include'./skins/tpl/invoice/show.tpl';
	

	

	


	

	

	

	
	