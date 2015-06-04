<?php
// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **

///////////////////////////// AJAX ////////////////////////////////
if(isset($_POST['AJAX'])){
	if($_POST['AJAX']=="change_invoce_num"){
		$query = "UPDATE  `apelburg_base`.`os__cab_orders_list` SET  `invoice_num` =  '".$_POST['value']."' WHERE  `os__cab_orders_list`.`id` ='".$_POST['row_id']."';";	
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
}


///////////////////////////// AJAX ////////////////////////////////



include ('./libs/php/classes/rt_class.php');

// простой запрос
	$array_request = array();

	
	$query = "SELECT 
		`".CAB_ORDER_ROWS."`.*, 
		DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
		`".CLIENTS_TBL."`.`company`,
		`".MANAGERS_TBL."`.`name`,
		`".MANAGERS_TBL."`.`last_name`,
		`".MANAGERS_TBL."`.`email` 
		FROM `".CAB_ORDER_ROWS."`
		INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
		INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
	// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
	$subsection = (isset($_GET['subsection']))?$_GET['subsection']:'';
	switch ($subsection) {
		case 'paused':
			# code...Приостановлен
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Приостановлен'";
			break;
		case 'ready_for_shipment':
			# code...Приостановлен
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Готов к отгрузке'";
			break;
		case 'in_work':
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
			break;
		
		default:
			# code...
			break;
	}
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	$main_rows_id = array();
	
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$main_rows_id[] = $row;
		}
	}

	// echo '<pre>';
	// print_r($zapros);
	// echo '</pre>';

	// собираем html строк-запросов
	$html1 = '';
	if(count($main_rows_id)==0){return 1;}

	foreach ($main_rows_id as $key => $value) {
		if(!isset($value2)){continue;}
		$order_num_1 = Cabinet::show_order_num($value['order_num']);
		$invoice_num = $value['invoice_num'];


		$query = "
		SELECT 
			`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
			`".CAB_ORDER_DOP_DATA."`.`quantity`,	
			`".CAB_ORDER_DOP_DATA."`.`price_out`,	
			`".CAB_ORDER_DOP_DATA."`.`print_z`,	
			`".CAB_ORDER_DOP_DATA."`.`zapas`,	
			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
			`".CAB_ORDER_MAIN."`.*,
			`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
			`".CAB_ORDER_ROWS."`.`global_status`,
			`".CAB_ORDER_ROWS."`.`payment_status`,
			`".CAB_ORDER_ROWS."`.`number_pyament_list`
			FROM `".CAB_ORDER_MAIN."` 
			INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
			LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
			WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
			ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
	                
		";

		$main_rows = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$main_rows_id = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$main_rows[] = $row;
			}
		}

		// СОБИРАЕМ ТАБЛИЦУ
		###############################
		// строка с артикулами START
		###############################
		$html = '<tr class="query_detail">';
		$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		$html .= '<td colspan="11" class="each_art">';
		
		
		// ВЫВОД позиций
		$html .= '<table class="cab_position_div">';
		
		// шапка таблицы позиций заказа
		$html .= '<tr>
				<th>артикул</th>
				<th>номенклатура</th>
				<th>тираж</th>
				<th>цены:</th>
				<th>товар</th>
				<th>печать</th>
				<th>доп. услуги</th>
			<th>в общем</th>
			<th></th>
			<th></th>
				</tr>';


		$in_out_summ = 0; // общая стоимость заказа
		foreach ($main_rows as $key1 => $val1) {
			//ОБСЧЁТ ВАРИАНТОВ
			// получаем массив стоимости нанесения и доп услуг для данного варианта 
			$dop_usl = $CABINET -> get_order_dop_uslugi($val1['id_dop_data']);
			// выборка только массива стоимости печати
			$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
			// выборка только массива стоимости доп услуг
			$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

			// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
			// стоимость печати варианта
			$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
			// стоимость доп услуг варианта
			$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
			// стоимость товара для варианта
			$price_out = $val1['price_out'] * $val1['quantity'];
			// стоимость варианта на выходе
			$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

			$html .= '<tr  data-id="'.$value['id'].'">
			<td> '.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
			<td>'.$val1['name'].'</td>
			<td>'.($val1['quantity']+$val1['zapas']).'</td>
			<td></td>
			<td><span>'.$price_out.'</span> р.</td>
			<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
			<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
			<td><span>'.$in_out.'</span> р.</td>
			<td></td>
			<td></td>
					</tr>';
			$in_out_summ +=$in_out; // прибавим к общей стоимости
		}
		$html .= '</table>';
		$html .= '</td>';
		$html .= '</tr>';
		###############################
		// строка с артикулами END
		###############################

		// получаем % оплаты
		$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
		// собираем строку заказа
		$html2 = '
				<tr data-id="'.$value['id'].'">
					<td class="cabinett_row_show show"><span></span></td>
					<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
					<td>'.$value['create_time'].'</td>
					<td>'.$value['company'].'</td>
					<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
					<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
					<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
					<td><span>'.$percent_payment.'</span> %</td>
					<td><span class="payment_status_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
					<td><span>'.$in_out_summ.'</span> р.</td>
					<td class="buch_status_select">'.$CABINET->select_status(2,$value['buch_status']).'</td>
					<td class="select_global_status">'.$CABINET->select_global_status($value['global_status']).'</td>
				</tr>
		';
		$html1 .= $html2 . $html;
	}
	echo '
	<table class="cabinet_general_content_row">
					<tr>
						<th id="show_allArt"></th>
						<th>Номер</th>
						<th>Дата/время заведения</th>
						<th>Компания</th>						
						<th class="invoice_num">Счёт</th>
						<th>Дата опл-ты</th>
						<th>№ платёжки</th>
						<th>% оплаты</th>
						<th>Оплачено</th>
						<th>стоимость заказа</th>
						<th></th>
						<th>Статус заказа.</th>
					</tr>';
	echo $html1;
	echo '</table>';
?>
