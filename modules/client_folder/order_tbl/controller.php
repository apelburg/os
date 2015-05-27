<?php

$array_request = array();

		
	$query = "SELECT 
		* , 
		DATE_FORMAT(`create_time`,'%d.%m.%Y %H:%i')  AS `create_time`
		FROM `".CAB_ORDER_ROWS."`
		WHERE `order_num` = '".(int)$order_num."' AND `id` = '".$order_id."'";
	
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	$main_rows_id = array();
	$order_create_time='неизвестно';

	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$main_rows_id[] = $row;
			$order_create_time = $row['create_time'];
		}
	}

$CABINET = new Cabinet();
// переменная вывода
$order_tbl = $html = '';

		$html .= '<tr>
				<th>артикул</th>
				<th>номенклатура</th>
				<th>тираж</th>
				<th>цена за товар</th>
				<th>доп. услуги</th>
				<th>цена позиции</th>
				<th>ТТН</th>
				<th>статус мен</th>
				<th>статус снаб</th>
				</tr>';
//
//if(count($main_rows_id)==0){$order_tbl = 'в данном заказе число позиций равно нулю';die;}

		// print_r($value);
		$order_num_1 = Cabinet::show_order_num($order_num);

		// $html .= '<tr class="query_detail">';
		// $html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		// $html .= '<td colspan="6" class="each_art">';
		

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
			WHERE `".CAB_ORDER_MAIN."`.`order_num` = '".$order_id."'
			ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
	                
		";
		// $html .= $query;
		$main_rows = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		// $main_rows_id = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$main_rows[] = $row;
			}
		}


		function get_dop_uslugi_print($id){
			$query = "SELECT * FROM  `os__cab_dop_uslugi` WHERE `dop_row_id` = '".$id."'";
			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		
		##################
		# START ВАРИАНТЫ #
		##################
		// ВЫВОД ВАРИАНТОВ

		
		// шапка таблицы вариантов запроса

		$order_itog_price = 0; // стоимость заказа
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
		

			$html .= '<tr>
			<td> '.$val1['art'].'<!--артикул --></td>
			<td>'.$val1['name'].'<!--номенклатура --></td>
			<td>'.($val1['quantity']+$val1['zapas']).'<!--тираж + запас --></td>
			<td>'.$price_out.'<!-- стоимость товара --></td>
			<td>'.get_tbl_dop_uslugi($dop_usl,($calc_summ_dop_uslug2+$calc_summ_dop_uslug)).'<!-- стоимость доп услуг --></td>
			<td><span class="itogo_n_no_bold">р.</span><span class="itogo_n_no_bold">'.$in_out.'</span><!-- цена позиции --></td>
			<td><!-- TTH --></td>
			<td>'.$val1['status_men'].'<!-- статус мен --></td>
			<td>'.$val1['status_snab'].'<!--статус снаб --></td>
			</tr>';
			$order_itog_price +=$in_out;
		}
		
		

function get_tbl_dop_uslugi($dop_usl, $all_price){
	global $global_performer_type;
	$html = '';
	if(count($dop_usl)){
		$html .= '<table class="dop_usl_tbl">';
		$html .= '<tr>';
		$html .= '
		<td>id</td>
		<td>dop_row_id</td>
		<td>id услуги</td>
		<td>глоб. тип</td>
		<td>тип</td>
		<td>тираж</td>
		<td>цена вход.</td>
		<td>цена исх.</td>
		<td>for_how<!--применить к тиражу/шт.--></td>
		<td>готовность</td>
		<td>плёнки</td>
		<td>дата начала работ</td>
		<td>дата сдачи</td>
		<td>тип исп.</td>
		<td>id исп.</td>
		<td>статуc вып.</td>
		<td>услуга</td>
		<td>общая цена</td>
		';
		$html .= '</tr>';
		foreach ($dop_usl as $key => $value) {
			$html .= '<tr>';

			foreach ($value as $k => $v) {
				$html .= '<td>';
				switch ($k) {
					case 'performer_status': // статус исполнителя
						$html .='<span>'.$v.'</span>';
						break;	
					case 'performer_id': // тип исполнителя услуг
						$html .='<span>'.((int)$v==0)?'не указан':$v.'</span>';
						break;	
					case 'performer_type': // тип исполнителя услуг
						$html .='<span>'.$global_performer_type[$v].'</span>';
						break;	
					case 'date_ready': // дата начала работ
						$html .='<span>'.($v=='000-00-00')?'неизвестно':$v.'</span>';
						break;	
					case 'date_send_out': // дата сдачи
						$html .='<span>'.($v=='000-00-00')?'неизвестно':$v.'</span>';
						break;	
					case 'quantity': // тираж
						$html .='<span>'.$v.'</span> шт.';
						break;	
					case 'price_in': // цена входящая
						$html .='<span>'.$v.'</span> р.';
						break;	
					case 'price_out': // цена выход
						$html .='<span>'.$v.'</span> р.';
						break;	
					case 'status_readiness': // процент готовности
						$html .='<span>'.$v.'</span> %';
						break;						
					case 'plenki': // плёнки
						$html .='<span>'.((int)$v==0)?'нужно делать':'есть'.'</span>';
						break;								
					default:
						$html .= $v;
						break;
				}	
				$html .= '</td>';		
			}			
			if($key<1){
				$html .= '<td rowspan="'.count($dop_usl).'">';
				$html .= $all_price;
				$html .= '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
	}
	return $html;
}

$html .= '
<tr>
	<td colspan="5"><span class="itogo">ИТОГО: </span></td>
	<td><span class="itogo_n">р.</span><span  class="itogo_n">'.$order_itog_price.'</span><!-- цена заказа --></td>
	<td><span class="itogo"></span><!-- TTH --></td>
	<td><span class="itogo"></span></td>
	<td><span class="itogo"></span></td>
</tr>';

$order_tbl = $html;
