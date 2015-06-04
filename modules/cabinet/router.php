<?php
	
	$_SESSION['access']['access'] = 1;// админ
	// $_SESSION['access']['access'] = 2;// буч
	// $_SESSION['access']['access'] = 4;// про-во
	// $_SESSION['access']['access'] = 5;// мен
	// $_SESSION['access']['access'] = 6;// водитель
	// $_SESSION['access']['access'] = 7;// склад
	// $_SESSION['access']['access'] = 8;// снаб
	// $_SESSION['access']['access'] = 9;// диз
	// $_SESSION['access']['access'];
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['cabinet']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	include './libs/php/classes/cabinet_class.php';
	$CABINET = new Cabinet();
    /////////////////////////////////// AJAX //////////////////////////////////////
	if(isset($_POST['AJAX'])){
		// ИЗМЕНЕНИЯ РАЗРЕШЁННЫЕ ДЛЯ БУЧА
		if($_POST['AJAX'] == 'change_payment_date'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_date` =  '".$_POST['date']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_payment_status'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'number_payment_list'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `number_pyament_list` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'select_global_status'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'buch_status_select'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `buch_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_ttn_number'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `ttn_number` =  '".$_POST['value']."', ttn_get = NOW() WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_delivery_tir'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `delivery_tir` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_status_snab'){
		// print_r($_POST);
			$query = "UPDATE `".CAB_ORDER_MAIN."` SET  `status_snab` =  '".$_POST['value']."' WHERE  `".CAB_ORDER_MAIN."`.`id` =".$_POST['row_id'].";";
		// $query = "UPDATE  SET `status_snab` = ' ' WHERE `id` = ".$_POST['row_id'].";";
		echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}	
	}
	 
	/////////////////////////////////// AJAX //////////////////////////////////////
	$menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked' => 'Не обработанные',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'Отказанные',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'Приостановлен',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные'
													
		); 

	
	

	include'./skins/tpl/common/quick_bar.tpl';

	// обрабатываем массив разрешённых разделов
	$menu_left = "";
	foreach ($ACCESS['cabinet']['section'] as $key => $value) {
		if($value['access']){
			$menu_left .= '<li '.((isset($_GET["section"]) && $_GET["section"]==$key)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section='.$key.'&subsection='.key($value['subsection']).'">'.$menu_name_arr[$key].'</a><li>';
		}
	}
	
	// определяем controller для подраздела
	$section = (isset($_GET["section"]))?$_GET["section"]:'default';

	// инициируем центральное меню
	$menu_central = "";
	$menu_central_arr = (array_key_exists($section, $ACCESS['cabinet']['section']))?$ACCESS['cabinet']['section'][$section]['subsection']:array();

	//$access = (isset($_GET['user_access']) && $_GET['user_access']!="")?''.$_GET['user_access'].'/':'1/';
	$access = $_SESSION['access']['access'].'/';

	
	ob_start();
	// echo "<pre>";
	// print_r($menu_central_arr);
	// echo "</pre>";
	foreach ($menu_central_arr as $key2 => $value2) {
		// $menu_central .= "$key2 -";
		$menu_central .= '<li '.((isset($_GET["subsection"]) && $_GET["subsection"]==$key2)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet'.((isset($_GET["section"]))?'&section='.$_GET["section"]:'').'&subsection='.$key2.'">'.$menu_name_arr[$key2].'</a><li>';
	}
	// подгружаем контроллер подраздела

	switch ($section) {
		case 'important':
			include $access.'important_controller.php';
			break;

		case 'requests':
			include $access.'requests_controller.php';
			break;

		case 'paperwork':
			include $access.'paperwork_controller.php';
			break;

		case 'orders':
			include $access.'orders_controller.php';
			break;

		case 'for_shipping':
			include $access.'for_shipping_controller.php';
			break;

		case 'closed':
			include $access.'closed_controller.php';
			break;

		case 'simples':
			include $access.'simples_controller.php';
			break;
		
		default:
			include $access.'default_controller.php';
			break;
	}
		

	$content = ob_get_contents();

	ob_get_clean();

	include'./skins/tpl/cabinet/show.tpl';
	
	unset($content);
?>