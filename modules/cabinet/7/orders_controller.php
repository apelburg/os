<?php
// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **
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
	$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%В оформлении%'";
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
	$html = '';
	if(count($main_rows_id)==0){return 1;}
	foreach ($main_rows_id as $key => $value) {
		// print_r($value);

		$html .= '
				<tr>
					<td class="cabinett_row_show show"><span></span></td>
					<td><a href="./?page=client_folder&section=order_tbl&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.Cabinet::show_order_num($value['order_num']).'</a></td>
					<td>'.$value['create_time'].'</td>
					<td>'.$value['company'].'</td>
					<td><!--RT::calcualte_query_summ($value[\'order_num\'])--></td>
					<td></td>
					<td></td>
				</tr>
		';
		$html .= '<tr class="query_detail">';
		$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		$html .= '<td colspan="6" class="each_art">';
		

		$query = "
		SELECT 
			`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
			`".CAB_ORDER_DOP_DATA."`.`quantity`,	
			`".CAB_ORDER_DOP_DATA."`.`price_out`,		
			`".CAB_ORDER_DOP_DATA."`.`print_z`,	
			`".CAB_ORDER_DOP_DATA."`.`zapas`,	
			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
			`".CAB_ORDER_MAIN."`.*,
			`".CAB_ORDER_ROWS."`.`id` AS `request_id` 
			FROM `".CAB_ORDER_MAIN."` 
			INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
			LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
			WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
			ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
	                
		";
		// $html .= $query;
		$main_rows = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$main_rows_id = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$main_rows[] = $row;
			}
		}
		// echo '<pre>';
		// print_r($main_rows);
		// echo '</pre>';

		if(!isset($value2)){continue;}
		
		##################
		# START ВАРИАНТЫ #
		##################
		// ВЫВОД ВАРИАНТОВ
		$html .= '<table class="cab_position_div">';
		
		// шапка таблицы вариантов запроса
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

		foreach ($main_rows as $key1 => $val1) {
			//ОБСЧЁТ ВАРИАНТА ЗАКАЗА
			// получаем массив стоимости нанесения и доп услуг для данного варианта ЗАКАЗА
			$dop_usl = $CABINET -> get_order_dop_uslugi($val1['id_dop_data']);
			// выборка только массива стоимости печати
			$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
			// выборка только массива стоимости доп услуг
			$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

			// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
			// стоимость печати варианта
			$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,$val1['quantity']);
			// стоимость доп услуг варианта
			$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,$val1['quantity']);
			// стоимость товара для варианта
			$price_out = $val1['price_out'] * $val1['quantity'];
			// стоимость варианта на выходе
			$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

			$html .= '<tr>
			<td><!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
			<td>'.$val1['name'].'</td>
			<td>'.$val1['quantity'].'</td>
			<td></td>
			<td>'.$price_out.'</td>
			<td>'.$calc_summ_dop_uslug.'</td>
			<td>'.$calc_summ_dop_uslug2.'</td>
			<td>'.$in_out.'</td>
			<td><!--$val1[\'status_man\']--></td>
			<td><!--$val1[\'status_snab\']--></td>
					</tr>';
		}
		$html .= '</table>';
		################
		# END ВАРИАНТЫ #
		################
		// $html .= '<br><br><br>'.$query;

		////////////////
		$html .= '</td>';
		$html .= '</tr>';
	}
	echo $html;

