<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS[$_GET['page']]['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

	// покключение класса API
	include_once './libs/php/classes/user.class.php';
	new UserApi();


	

	

	


	

	

	

	
	