<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['suppliers']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
	
	include_once('./libs/php/classes/supplier_class.php');
	include_once('./libs/php/classes/mail_class.php');
	 
	 ob_start();	
	 
	 switch($section){
	 
	   case 'suppliers_list':
	   include 'suppliers_list_controller.php';
	   break;
	   
	   case 'suppliers_data':
	   include 'supplier_data_controller.php';
	   break;
	   
	   case 'profiles_list':
	   include 'profiles_list_controller.php';
	   break;
	   
	   case 'profile_data':
	   include 'profile_data_controller.php';
	   break;

	   default: 
	   include 'default_controller.php';
	   break;
	
	}
	$content = ob_get_contents();
	ob_get_clean();

	
	
	echo $content;
    unset($content);
	
?>