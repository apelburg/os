<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt_position']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
    

	if(isset($_POST['set_discount'])){
      	//print_r($_POST['form_data'])."<br>";
      	set_discount($_POST['form_data']);
   		header('Location:'.$_SERVER['HTTP_REFERER']);
      	exit;
    }
	
	$forum = '';
	
	// // комментарии
	// include './libs/php/classes/comments_class.php';
	// $comments = new Comments_for_query_class;
	
	// класс работы с базой
	// include './libs/php/classes/db_class.php';
	// класс работы с формами
	include './libs/php/classes/os_form_class.php';

	// класс работы с поставщиками
	include './libs/php/classes/supplier_class.php';

	// класс карточки товара
	include './libs/php/classes/rt_position_gen_class.php';
	
	// отключить после приведения карточки товара к единому виду
		// класс работы с позициями каталога
		include './libs/php/classes/rt_position_catalog_class.php';
		// класс работы с позициями не каталога
		include './libs/php/classes/rt_position_no_catalog_class.php';
	
	// расширение класса карточки товара
	include_once './libs/php/classes/rtPositionUniversal.class.php';
	
	// класс работы с менеджерами
	include './libs/php/classes/manager_class.php';

	
	$id = (isset($_GET['id']))?$_GET['id']:'none';

	
	$POSITION = new rtPositionUniversal;

	$type_product = $POSITION->position['type'];
	// если тип продукции не определен
	//if(!isset($type_product)){echo 'Тип продукции не определён.';exit;}
    
	// генерация обратной ссылки (для перехода на другие страницы)
	save_way_back(array('section=rt_position','section=planner'),'?page=client_folder&client_id='.$client_id.'&query_num='.$POSITION->position['query_num']);
	$quick_button_back = get_link_back();
	

	include 'controller.php';

	//шаблон forum
	$forum = '';
	

	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	// $POSITION_GEN = new Position_general_Class();
	// $images_data = $POSITION_GEN->POSITION_CATALOG->fetch_images_for_article2($art_id,'1');


	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';
	
	// планка клиента
	include_once './libs/php/classes/client_class.php';
	Client::get_client__information($_GET['client_id']);
	
	// шаблон страницы
	include 'skins/tpl/client_folder/rt_position/show.tpl';

	

	


	

	

	

	
	