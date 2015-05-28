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
				<th>статус заказа</th>
				</tr>';
//
//if(count($main_rows_id)==0){$order_tbl = 'в данном заказе число позиций равно нулю';die;}

		// print_r($value);
		$order_num_1 = Cabinet::show_order_num($order_num);

		// $html .= '<tr class="query_detail">';
		// $html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		// $html .= '<td colspan="6" class="each_art">';
		

		// запрос из os__cab_orders_dop_data по id заказа
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
			`".CAB_ORDER_ROWS."`.`global_status`
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

		// получаем массив доп услуг по позиции (скопированному утверждённому варианту) 
		// по id os__cab_orders_dop_data 
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
			<td><span>'.($val1['quantity']+$val1['zapas']).'</span>шт.<!--тираж + запас --></td>
			<td><span>'.$price_out.'</span>р.<!-- стоимость товара --></td>
			<td>'.get_tbl_dop_uslugi($dop_usl,($calc_summ_dop_uslug2+$calc_summ_dop_uslug)).'<!-- стоимость доп услуг --></td>
			<td><span class="itogo_n_no_bold">р.</span><span class="itogo_n_no_bold">'.$in_out.'</span><!-- цена позиции --></td>
			<td contenteditable="true"></td>
			<td>'.select_status(5,$val1['status_men']).'<!-- статус мен --></td>
			<td>'.select_status(8,$val1['status_snab']).'<!--статус снаб --></td>
			<td>'.select_global_status($val1['global_status']).'<!--глобальный статус заказа--></td>
			</tr>';
			$order_itog_price +=$in_out;
		}
		
		

function get_tbl_dop_uslugi($dop_usl, $all_price){
	global $global_performer_type;

	$html = '';
	if(count($dop_usl)){
		$html .= '<table class="dop_usl_tbl">';
		$html .= '<tr>';
		// <!--
		// <td>id</td>
		// <td>dop_row_id</td>
		// <td>id услуги</td>
		// <td>глоб. тип</td>
		// <td>тип</td>-->
		// <td>for_how<!--применить к тиражу/шт.--></td>
		$html .= '

		
		<td>тираж</td>
		<td>цена вход.</td>
		<td>цена исх.</td>
		<td>готовность</td>
		<td>плёнки</td>
		<td>дата начала работ</td>
		<td>дата сдачи</td>
		<td>тип исп.</td>
		<td>сп.</td>
		<td>статуc вып.</td>
		<td>услуга</td>
		<td>общая цена</td>
		';
		$html .= '</tr>';
		foreach ($dop_usl as $key => $value) {
			$html .= '<tr';
			$html .= ' data-id="'.$value['id'].'"';
			$html .= ' data-dop_row_id="'.$value['dop_row_id'].'"';
			$html .= ' data-uslugi_id="'.$value['uslugi_id'].'"';
			$html .= ' data-glob_type="'.$value['glob_type'].'"';
			$html .= ' data-type="'.$value['type'].'"';
			$html .= ' data-for_how="'.$value['for_how'].'"';
			$html .= '>';
			switch ($value['type']) {
				case 'delivery':
					$int = 7;
					break;

				case 'design':
					$int = 9;
					break;


				case 'shelk':
					$int = 4;
					break;
				
				default:
					$int = 0;
					break;
			}
			foreach ($value as $k => $v) {
				switch ($k) {
					case 'performer_status': // статус исполнителя
						$html .='<td><span>'.select_status($int,$v).'</span></td>';
						break;	
					case 'performer_id': // тип исполнителя услуг
						$st = ((int)$v==0)?'не указан1':$v;
						$html .='<td><span>'.$st.'</span></td>';
						break;	
					case 'performer_type': // тип исполнителя услуг
						$html .='<td><span>'.$global_performer_type[$v].'</span></td>';
						break;	
					case 'date_ready': // дата начала работ
						$st = ($v=='000-00-00')?'неизвестно':$v;
						$html .='<td><span>'.$st.'</span></td>';
						break;	
					case 'date_send_out': // дата сдачи
						$st = ($v=='000-00-00')?'неизвестно':$v;
						$html .='<td><span>'.$st.'</span></td>';
						break;	
					case 'quantity': // тираж
						$html .='<td><span>'.$v.'</span> шт.</td>';
						break;	
					case 'price_in': // цена входящая
						$html .='<td><span>'.$v.'</span> р.</td>';
						break;	
					case 'price_out': // цена выход
						$html .='<td><span>'.$v.'</span> р.</td>';
						break;	
					case 'status_readiness': // процент готовности
						$html .='<td><span  contenteditable="true">'.$v.'</span> %</td>';
						break;						
					case 'plenki': // плёнки
						$st = ((int)$v==0)?'нужно делать':'есть';
						$html .='<td><span>'.$st.'</span></td>';
						break;	
					//СКРЫВАЕМ НЕНУЖНЫЕ						
					case 'id': 
						break;						
					case 'dop_row_id': 
						break;						
					case 'uslugi_id': 
						break;						
					case 'glob_type': 
						break;								
					case 'for_how': 
						break;				
					case 'type': 
						break;

					// ОСТАЛЬНОЕ ПОКАЗЫВАЕМ								
					default:
						$html .= '<td>'.$v.'</td>';
						break;
				}		
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

function select_status($rights_int/*права для определения списка статуса*/,$real_val){

	global $STATUS_LIST;
	$status_arr = $STATUS_LIST[$rights_int];
	$html = '<select><option value="">...</option>';
	foreach ($status_arr as $key => $value) {
		$is_checked = ($real_val==$value)?'selected="selected"':'';
		$html .= ' <option '.$is_checked.'>'.$value.'</option>';
	}	
	$html .= '</select>';
	return $html;
}
function select_global_status($real_val){

	global $GLOBAL_STATUS_ORDER;
	$status_arr = $GLOBAL_STATUS_ORDER;
	$html = '<select><option value="">...</option>';
	foreach ($status_arr as $key => $value) {
		$is_checked = ($real_val==$value)?'selected="selected"':'';
		$html .= ' <option '.$is_checked.'>'.$value.'</option>';
	}	
	$html .= '</select>';
	return $html;
}



