<?php
	$forum = '';
	include './libs/php/classes/articul_class.php';
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['order_art_edit']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	include 'controller.php';


	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';

	// шаблон forum
	// ob_start();	
	
	// include 'skins/tpl/client_folder/order_art_edit/forum.tpl';
	
	// $forum = ob_get_contents();
	// ob_get_clean();

	// варианты просчётов
	ob_start();		
	$dop_enable = array(0,0);
	foreach ($variants as $k => $v) {
		//if($v['draft']=='0' && $v['row_status']!='red'/*исключение на возможную ошибку в базе*/){$dop_enable[0] = 1;}			
		if($v['row_status']=="green"){$dop_enable[0] = 1;}		
		if($v['row_status']=="grey"){$dop_enable[1] = 1;}	
	}	
	$isset_green = $dop_enable[0];
	$isset_grey = $dop_enable[1];
	// echo '<pre>';
	// print_r($variants);
	// echo '</pre>';
	
	// print_r($dop_enable);

	$ch = 0;
	foreach ($variants as $key => $value) {
		// по умолчанию блоки скрыты
		$display_this_block = ' style="display:none"';
		// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
		if((!isset($_GET['show_archive']) && ($isset_green || $isset_grey)) && $value['row_status']=='red'){ continue;}

		// если это зона записи red, добавляем класс запрещающий редактирование данного блока
		if($value['row_status']=='red'){$show_archive_class = " archiv_opacity";}else{$show_archive_class ='';}

		///////// ВАРИАНТЫ СВЕТОФОР  ///////
		$var = $value['row_status'];
		switch ($var) {
			case 'green':// не история - рабочий вариант расчёта
				// может входить в КП
				if($ch < 1){$display_this_block=' style="display:block"';$ch++;}				
			break;
			
			case 'grey':// не история - вариант расчёта не учитывается в РТ
				// серый вариант расчёта не входит в КП	
				if (!$isset_green && $ch== 0){$display_this_block=' style="display:block"';$ch++;}
			break;						
			
			default: // вариант расчёта red (архив), остальное не важно
				if (!$isset_green && !$isset_grey && $ch== 0){$display_this_block=' style="display:block"';$ch++;}
			break;
		}
		
		$get_size_table = $ARTICUL->get_size_table($art_dop_params,$value);
		/* 
		старый вариант
		тут подсчитывается тираж и запас варианта расчета каталожной продукции 
		исходя из размерной сетки,
		позднее мы договорились, что при изменении тиража из РТ таблицы размеров обнуляются
		так что тираж берем из quantity и zapas
		$rr = json_decode($value['tirage_json'], true);
		$sum_tir = $sum_dop = 0;
		foreach ($rr as $k => $v) {
			$sum_tir += (isset($v['tir']))?(int)$v['tir']:0;
			$sum_dop += (isset($v['dop']))?(int)$v['dop']:0;
		}
		*/
		// тираж
		$sum_tir = $value['quantity'];
		$sum_dop = $value['zapas'];
		// сумма за тираж для нас
		$sum_of_tirage_in = round($value['price_in']*($sum_tir+$sum_dop),2);
		// сумма за тираж для клиента
		$sum_of_tirage_out = round($value['price_out']*($sum_tir+$sum_dop),2);
		// сумма прибыль
		$sum_prib_of_tirage = $sum_of_tirage_out-$sum_of_tirage_in;
		// печатаем / не печатаем запас
		$print_z = ($value['print_z']=='1')?'checked':'';
		$print_z_no = ($value['print_z']=='0')?'checked':'';

		$dop_uslugi = $ARTICUL->get_dop_uslugi_html($value['id'],($sum_tir+$sum_dop));
		// стандартное время изготовления
		// $std_pr = ($value['standart']=='10' && $type_tovar=='cat')?1:0;
		$std_time_print = ($value['standart']=='10' && $type_tovar=='cat')?'checked':'';


		include 'skins/tpl/client_folder/order_art_edit/variants_template.tpl';

	}
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	

	// шаблон страницы
	include 'skins/tpl/client_folder/order_art_edit/show.tpl';

	
	