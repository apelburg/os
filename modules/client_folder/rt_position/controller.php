<?php
	// инициализация класса работы с позициями
	// ВНИМАНИЕ!!!
	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	$POSITION_GEN = new Position_general_Class();


	// получаем все варианты просчёта по данному артикулу
	$variants_arr = $POSITION->getVariants((int)$POSITION->position['id']);

	// получаем дополнительные параметры: размер, цену ...
	$art_dop_params = $POSITION->getDopParams((int)$POSITION->position['art_id']);
	
	// тип продукта название
	$product_type_RU = 'Каталог';
	if($POSITION->position['type'] != 'cat'){
		$product_type_RU = 'Не каталог';
	}


	// варианты просчётов
	ob_start();		
	
	$arr_for_type = $POSITION->Variants->get_variants_arr_sort_for_type($variants_arr);

	$ch = 0;
	// echo '<pre>';
	// print_r($variants_arr);
	// echo '</pre>';
		
	foreach ($variants_arr as $key => $variant) {
		// по умолчанию блоки скрыты
		$display_this_block = ' style="display:none"';
		// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
		if(!isset($_GET['show_archive']) && $variant['row_status']=='red'){ continue;}

		// если это зона записи red, добавляем класс запрещающий редактирование данного блока
		if($variant['row_status']=='red'){
			$show_archive_class = " archiv_opacity";
		}else{
			$show_archive_class ='';
		}

		// если вариант выбран по ссылке через GET параметр
		if(isset($_GET['varID_checked']) && $_GET['varID_checked'] > 0){
			if($_GET['varID_checked'] == $variant['id']){
				$display_this_block =' style="display:block"';$ch++;	
			}		
		}else{
			///////// ВАРИАНТЫ СВЕТОФОР  ///////
			switch ( $variant['row_status'] ) {
				case 'sgreen':// 
					if($ch < 1){
						$display_this_block=' style="display:block"';$ch++;
					}	
					break;	

				case 'green':// не история - рабочий вариант расчёта
					// может входить в КП
					if($ch < 1 && @count($arr_for_type['sgreen']) == 0){
						$display_this_block=' style="display:block"';$ch++;
					}				
					break;	
				
				case 'grey':// не история - вариант расчёта не учитывается в РТ
					// серый вариант расчёта не входит в КП	
					if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0){
						$display_this_block=' style="display:block"';$ch++;
					}
					break;						
				
				default: // вариант расчёта red (архив), остальное не важно
					if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0 && @count($arr_for_type['grey']) == 0){
						$display_this_block =' style="display:block"';$ch++;
					}
					break;
			}
		}
		
		// размерная таблица
		$get_size_table = $POSITION->getSizeTable($art_dop_params,$variant);
	
		// тираж
		$sum_tir = $variant['quantity'];
		$sum_dop = $variant['zapas'];
		// сумма за тираж для нас
		$sum_of_tirage_in = round($variant['price_in'] * ($sum_tir + $sum_dop),2);
		// сумма за тираж для клиента
		$sum_of_tirage_out = round($variant['price_out'] * ($sum_tir + $sum_dop),2);
		// сумма прибыль
		$sum_prib_of_tirage = $sum_of_tirage_out-$sum_of_tirage_in;
		// печатаем / не печатаем запас
		$print_z = ($variant['print_z']=='1')?'checked':'';
		$print_z_no = ($variant['print_z']=='0')?'checked':'';

		
		// стандартное время изготовления
		//$std_time_print = ($variant['standart']=='10' && $type_tovar=='cat')?'checked':'';

		$shipping_type__show_date = ' style="display:none"';
		$shipping_type__show_rd = ' style="display:none"';
		
		switch ($variant['shipping_type']) {
			case 'date':
				$shipping_type__show_date = ' style=""';
				break;
			case 'rd':
				$shipping_type__show_rd = ' style=""';
				break;
			
			default:
				# code...
				break;
		}

		$tirage_json = json_decode($variant['tirage_json'], true);
		// echo '<pre>';
		// print_r($tirage_json);
		// echo '</pre>';
		// echo count($tirage_json);
		$count_quantity = 0;
		$count_zapas = 0;
		foreach ($tirage_json as $size) {
			if ($size['tir'] > 0 || $size['dop'] > 0) {
				$count_quantity++;
				$count_zapas++;
			}
		}
		$disabled_input_class = ($count_quantity > 1 || $count_zapas > 1)?' disabled_input':'';
		$disabled_input_readonly = ($count_quantity > 1 || $count_zapas > 1)?' readonly="readonly"':'';
		
		// include 'skins/tpl/client_folder/rt_position/variants_template.tpl';
		include 'skins/tpl/client_folder/rt_position/calculate_tbl.tpl';
	}
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
?>