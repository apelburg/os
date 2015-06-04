<?php


// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **


include ('./libs/php/classes/rt_class.php');

// $RT = new RT;



// подсчет стоимости запроса
function get_gen_price_out($variable){
		return;
	}

// простой запрос
	$array_request = array();

	
	$query = "SELECT 
		`".RT_LIST."`.*, 
		DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
		`".CLIENTS_TBL."`.`company`,
		`".MANAGERS_TBL."`.`name`,
		`".MANAGERS_TBL."`.`last_name`,
		`".MANAGERS_TBL."`.`email` 
		FROM `".RT_LIST."`
		INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".RT_LIST."`.`client_id`
		INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".RT_LIST."`.`manager_id`";
	$result = $mysqli->query($query) or die($mysqli->error);
	$main_rows_id = array();
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$zapros[] = $row;
		}
	}

	
	// $main_rows_id = implode(',', $main_rows_id);
	// echo $main_rows_id;
	// echo '<pre>';
	// print_r($zapros);
	// echo '</pre>';

	// собираем html строк-запросов 
	$html = '';
	foreach ($zapros as $key => $value) {

		$html .= '
				<tr>
					<td class="cabinett_row_show show"><span></span></td>
					<td><a href="./?page=client_folder&query_num='.$value['query_num'].'">'.$value['query_num'].'</a></td>
					<td>'.$value['create_time'].'</td>
					<td>'.$value['company'].'</td>
					<td>'.RT::calcualte_query_summ($value['query_num']).'</td>
					<td></td>
					<td></td>
				</tr>
		';
		$html .= '<tr class="query_detail">';
		$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		$html .= '<td colspan="6" class="each_art">';
		

		$query = "
		SELECT 
			`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
			`".RT_DOP_DATA."`.`quantity`,	
			`".RT_DOP_DATA."`.`price_out`,		
			`".RT_DOP_DATA."`.`print_z`,	
			`".RT_DOP_DATA."`.`zapas`,	
			DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
			`".RT_MAIN_ROWS."`.*,
			`".RT_LIST."`.`id` AS `request_id` 
			FROM `".RT_MAIN_ROWS."` 
			INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
			LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
			WHERE `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".RT_MAIN_ROWS."`.`query_num` = '".$value['query_num']."'
			ORDER BY `".RT_MAIN_ROWS."`.`id` ASC
	                
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
			//ОБСЧЁТ ВАРИАНТОВ
			// получаем массив стоимости нанесения и доп услуг для данного варианта 
			$dop_usl = $CABINET -> get_query_dop_uslugi($val1['id_dop_data']);
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
			<td>'.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
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
		$html .= '</td>';
		$html .= '</tr>';
	}
	echo '
	<table class="cabinet_general_content_row">
					<tr>
						<th id="show_allArt"></th>
						<th>Номер</th>
						<th>Дата/время</th>
						<th>Компания</th>
						<!-- <th>Клиент</th> -->
						<th>Сумма</th>
						<th>Статус мен.</th>
						<th>Статус снаб.</th>
					</tr>';
	echo $html;
	echo '</table>';


// echo 'раздел '.$_GET["section"].'<br>';
// echo 'подраздел '.$_GET["subsection"].'<br>';
// echo 'тестим текст в разделе ';