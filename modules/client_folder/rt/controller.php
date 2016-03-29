<?php
	// ПРИМЕЧАНИЕ - после заверщения скрипта убрать из common.js функции с окончанием Old
	
	// Данные расчетной таблицы хронятся в 3-х таблицах базы данных
	// 1-я таблица является родительской для 2-ой , 2-ая родительской для 3-ей
	// os__rt_main_rows  (RT_MAIN_ROWS)  1-я таблица основные ряды таблицы, содержащие описательные данные о товарной позиции (наименование, тип )
	// os__rt_dop_data   (RT_DOP_DATA)  2-я таблица содрежит данные о количестве и цене для каждого варианта расчета товарной позиции (к каждой строке из 1-ой таблицы может соответсвовать любое количество стро из 2-ой таблицы)
	// os__rt_dop_uslugi (RT_DOP_USLUGI) 3-я таблица содержит данные о расчетах о дополнительных услугах к ним относятся - нанесение, доставка, упаковка. Причем данные в таблице разделены на группы нанесение входит в группу print, все остальное в группу extra (к каждой строке из 2-ой таблицы может соответсвовать любое количество строк из 3-ей таблицы)
	// os__rt_print_data (RT_PRINT_DATA) 3-я таблица содержит данные о расчетах нанесения (к каждой строке из 2-ой таблицы может соответсвовать любое количество стро из 3-ей таблицы)
	
	
	// cat catalogs - товары из каталогов 
	// pol polygraphy - полиграфия 
	// ext externals (внешние) - товары не из каталогов 
	
	// поля таблицы 
	// row_status - ствтус ряда (светофор)
	// glob_status - статус ряда в глобальном масштабе (в работе, на расчете)
	//
	//
	//
	//

	function fetch_rows_from_rt($query_num){
	     global $mysqli;
		 global $Position_no_catalog;
		 
		 $rows = array();
		 
		 $query = "SELECT main_tbl.id AS main_id,main_tbl.rt_row_color AS rt_row_color ,main_tbl.type AS main_row_type  ,main_tbl.art_id AS art_id ,main_tbl.art AS art ,main_tbl.name AS item_name ,main_tbl.master_btn AS master_btn , main_tbl.svetofor_display AS svetofor_display ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.glob_status AS glob_status, dop_data_tbl.expel AS expel, dop_data_tbl.shipping_date AS shipping_date,dop_data_tbl.shipping_type AS shipping_type, dop_data_tbl.shipping_time AS shipping_time, dop_data_tbl.status_snab AS status_snab, dop_data_tbl.dop_men_text AS dop_men_text,
						  
						  dop_uslugi_tbl.id AS uslgi_t_id ,dop_uslugi_tbl.other_name AS uslugi_t_other_name ,dop_uslugi_tbl.uslugi_id AS uslgi_t_uslugi_id ,dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out, dop_uslugi_tbl.discount AS uslugi_t_discount , dop_uslugi_tbl.for_how AS uslugi_t_for_how , dop_uslugi_tbl.print_details AS uslugi_t_print_details 
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.query_num ='".$query_num."' ORDER BY main_tbl.sort,dop_data_tbl.id";
		
			// echo $query;
		 $result = $mysqli->query($query) or die($mysqli->error);
		 $multi_dim_arr = array();
	     while($row = $result->fetch_assoc()){
		     if(!isset($multi_dim_arr[$row['main_id']])){
			     $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				 $multi_dim_arr[$row['main_id']]['master_btn'] = $row['master_btn'];
				 $multi_dim_arr[$row['main_id']]['art_id'] = $row['art_id'];
				 $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				 $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
				 $multi_dim_arr[$row['main_id']]['svetofor_display'] = $row['svetofor_display'];
				 $multi_dim_arr[$row['main_id']]['rt_row_color'] = $row['rt_row_color'];
				 
				 if($row['main_row_type']=='cat'){
				     $data = RT::getArtRelatedPrintInfo($row['art_id']);
				     $multi_dim_arr[$row['main_id']]['dop_details'] = $data;
				 }
			 }
			 //$multi_dim_arr[$row['main_id']]['uslgi_t_id'][] = $row['uslgi_t_id'];
			 if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			     $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'expel' => $row['expel'],
																	'shipping_date' => $row['shipping_date'],
																	'shipping_type' => $row['shipping_type'],
																	'shipping_time' => $row['shipping_time'],
																	'row_status' => $row['row_status'],
																	'glob_status' => $row['glob_status'],
																	'status_snab' => $row['status_snab'],
																	'dop_men_text_class' => ($row['dop_men_text']!='')?'filled':'empty',										
																	'quantity' => $row['dop_t_quantity'],
																	'discount' => $row['dop_t_discount'],
																	'price_in' => $row['dop_t_price_in'],
																	'price_out' => $row['dop_t_price_out']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslgi_t_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslgi_t_id']] = array(
						'type' => $row['uslugi_t_type'],
						'id' => $row['uslgi_t_id'],
						'uslugi_id' => $row['uslgi_t_uslugi_id'],
						'quantity' => $row['uslugi_t_quantity'],
						'other_name' => $row['uslugi_t_other_name'],
						'price_in' => $row['uslugi_t_price_in'],
						'price_out' => $row['uslugi_t_price_out'],
						'discount' => $row['uslugi_t_discount'],
						'for_how' => $row['uslugi_t_for_how'],	
						'print_details' => $row['uslugi_t_print_details']
						);
			}
			
			//print_r( $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['print_data']); echo "<br>";
			
			$rows[]= '<tr><td>'.implode('</td><td>',$row).'</td></tr>';
		   
		 }
	     return array($multi_dim_arr,$rows);
	 }
	
	 $rows = fetch_rows_from_rt($query_num);
	 
	 // получаем информацию по площадям нанесения для калькуляторов
	 require_once(ROOT."/libs/php/classes/print_calculators_class.php");
	 $print_sizes = printCalculator::get_sizes();
	 $uslugi_arr = printCalculator::get_uslugi();

	 $test_data = FALSE; // TRUE
	 // Построение таблицы РТ
	 // основой для рядов таблицы (тегов <tr>) является второй уровень массива $rows[0], тоесть это те данные которые лежат в $row['dop_data']
	 // каждый элемент этого массива выводится как отдельный ряд (в ходе обработки вывода могут быть добавлены дополнительные вспомагательные 
	 // элементы или изменен их порядок) 
	 // Тоесть таблица, количество (тегов <tr>), формируется на основе уровня $row['dop_data'], остальные уровни только влияют на результирующий
	 // вид таблицы
	 // элементы первого уровня выводятся один раз на весь блок элементов $row['dop_data']
	 // элементы третьего уровня $dop_row['dop_uslugi'] выводятся в виде ссылки внутри существующего ряда, если $dop_row['dop_uslugi'] существует
	 // draft - РЕАЛИЗАЦИЯ ФУНКЦИОНАЛА "ДРАФТ" оказалась не востребованной поле draft можно удалить из таблицы в базе данных
	 // если что реализация сохранена, закомментирована внизу скрипта 
	  
	 // echo '<pre>'; print_r($rows[0]); echo '</pre>';
	  /*if(@$_SESSION['access']['user_id']==18){ 
		   echo '<pre>'; print_r($rows[0]); echo '</pre>';
      }  */
	 
	 
	 $service_row[0] = array('quantity'=>'','price_in'=>'','price_out'=>'','row_status'=>'','glob_status'=>'');
	 $glob_counter = 0;
	 $mst_btn_summ = 0;
	 $svetofor_display_relay_status_all = 'on';
	 
	 foreach($rows[0] as $key => $row){
	     $glob_counter++;
		 $need_confirmation_flag = false;
		 $alarm_marker=$confirm_calc_tip='';
         // Проходим по первому уровню и определям некоторые моменты отображения таблицы, которые будут применены при проходе по второму
		 // уровню массива, ряды таблицы будут создаваться там
		 
		 
		 // считаем сколько мастер кнопок нажаты
		 $mst_btn_summ += $row['master_btn'];
		 
		 // приблуда выясняющая надо ли отображать маркер что есть ли нанесения которые надо подтверждать
		 if(isset($row['dop_data'])){
		     foreach($row['dop_data'] as $cnfrm_lev1){
			     if(isset($cnfrm_lev1['dop_uslugi'])){
				     foreach($cnfrm_lev1['dop_uslugi'] as $cnfrm_lev2){ 
					     foreach($cnfrm_lev2 as $cnfrm_lev3){
						     if(isset($cnfrm_lev3['print_details'])){
								 $cnfrm_arr = json_decode($cnfrm_lev3['print_details'],true);
								 if(isset($cnfrm_arr['need_confirmation'])) $need_confirmation_flag = true;
					         }
					     }
				     }
			     }
		     }
		 }
		 if($need_confirmation_flag){
			  $row['rt_row_color'] = 'red';
			  $alarm_marker = ' js--icon-alarm-services';
			  $confirm_calc_tip = '<div class="confirmCalcTip">проверьте цены, введенные вручную</div>';
		 }

		 // если товарная позиция имеет больше одного варианта расчета вставляем пустой ряд вверх
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 if(isset($row['dop_data']) && count($row['dop_data'])>1){
			  $row['dop_data']= $service_row + $row['dop_data']; 
		 }
		 // 
		 if(!isset($row['dop_data'])){
		      $row['name'] .= '<span style="color:red"> ОШИБКА - ДАННЫЕ ПО РАСЧЕТУ НЕ ПОЛУЧЕНЫ (УДАЛИТЕ ПОЗИЦИЮ)</span>';
			  $row['dop_data']= $service_row; 
		 }
		 
		 //array_unshift($row['dop_data'],array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0));
		 
		 // здесь мы определяем значение для атрибута rowspan тегов td которые будут выводится единой ячейкой для всей расчетов товарной позиции
		 $svetofor_display_relay_status = 'on';
		 $row_span = count($row['dop_data']);
		 if($row['svetofor_display']==1){
		      foreach($row['dop_data'] as $dop_row){
			      if($dop_row['row_status']=='red'){
				      $row_span--;
					  $svetofor_display_relay_status_all = 'off';
					  $svetofor_display_relay_status = 'off';
				  }
			  }
		 }
		 $counter=0;
		
		  
		  echo '<pre>'; print_r($row['dop_data']); echo '</pre>---';
		 // Проходим в цикле по второму уровню массива($row['dop_data']) на основе которого строится основной шаблон таблицы
	     foreach($row['dop_data'] as $dop_key => $dop_row){
		    $discount_arr = array();
			/*if(@$_SESSION['access']['user_id']==18){ 
			    // echo '<pre>'; print_r($dop_row); echo '</pre>';
	        } */
				// если $dop_key==0 это значит что это вспомогательный ряд (отображается пустым без функционала)
			// если ряд $dop_key!=0 не вспомогательный работаем с ним в обычном порядке
		    if($dop_key!=0){
				 // определяем какие расчеты будут учитываться в конечных суммах а какие нет и их отображение в таблице
				 // json_decode($row['details']);
				 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
				 if(@$dop_row['expel']!=''){
					$obj = @json_decode($dop_row['expel']);
					foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
				 }
				 //echo '<br>'; print_r($expel);
				
				 $print_exists_flag = $uslugi_exists_flag = '';  
				 $summ_in = $summ_out = $uslugi_details_trs = array();
				 // работаем с информацией о дополнительных услугах определяя что будет выводиться и где
				 // 1. определяем данные описывающие варианты нанесения логотипа, они хранятся в $dop_row['dop_uslugi']['print']
				 if(isset($dop_row['dop_uslugi']['print'])){ // если $dop_row['dop_uslugi']['print'] есть выводим данные о нанесениях 
					 $row_counter = 0; 
					 
					 foreach($dop_row['dop_uslugi']['print'] as $extra_data){
					     // если количество в расчете нанесения не равно количеству в колонке тираж товара 
						 // необходимо присвоить нанесениям такое же количество и пересчитать их
						//$extra_data['quantity'] = 250;
						$print_details = json_decode($extra_data['print_details'],true);

						 //echo '<pre>'; print_r(printCalculator::convert_print_details_for_TotalCom($extra_data['print_details'])); echo '</pre>';
						if(isset($print_details['calculator_type']) && ($print_details['calculator_type'] =='auto')){
							if($extra_data['quantity']!=$dop_row['quantity']){
								 $reload['flag'] = true;
								 //echo $dop_row['quantity'];
								 include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
								 $json_out =  rtCalculators::change_quantity_and_calculators($dop_row['quantity'],$dop_key,'true','false');
								 $json_out_obj =  json_decode($json_out);
								 
								 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
								 // сбрасываем количество тиража и нанесения до 1шт.
								 if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
									 rtCalculators::change_quantity_and_calculators(1,$dop_key,'true','false');
									 
									 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_key."'";
									 $result = $mysqli->query($query)or die($mysqli->error);
								 }
								 
		
							 } /**/
						 }
						 $discount_arr[] = $extra_data['discount'];
						 $extra_data['price_out'] = ($extra_data['discount'] != 0 )? (($extra_data['price_out']/100)*(100 + $extra_data['discount'])) : $extra_data['price_out'];
						 $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
						 
						
						 $YPriceParamCount = (@isset($print_details['dop_params']['YPriceParam']))? count($print_details['dop_params']['YPriceParam']):'';
						 if(isset($print_details['dop_params']['sizes'][0]['id'])){
						     if(isset($print_sizes[$print_details['dop_params']['sizes'][0]['id']])) $size = $print_sizes[$print_details['dop_params']['sizes'][0]['id']];
							 else $size='';
						 }
						 else $size='';
						 
						 if(isset($print_details['need_confirmation']))  $need_confirmation_flag = true;
						 
						 $uslugi_details_trs[] = '<tr class="'.(((++$row_counter)==count($dop_row['dop_uslugi']['print']))?'border_b':'').'"><td class="small right">'.(count($uslugi_details_trs)+1).'</td><td>'.$print_details['print_type'].'</td><td class="small">'.$print_details['place_type'].'</td><td class="center">'.$YPriceParamCount.'</td><td class="border_r">'.$size.'</td><td class="right">'.$extra_data['price_in'].'</td><td class="right">'.$extra_data['price_out'].'</td></tr>';
					 }
				     $print_exists_flag = '1'; 
				 }
			
				 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
				 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
				     $row_counter = 0; 
					 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
					     // если количество в расчете доп услуг не равно количеству в колонке тираж товара 
						 // необходимо присвоить доп услугам такое же количество
						
					     if($extra_data['quantity']!=$dop_row['quantity']){
						     
						     $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$dop_row['quantity']."'  WHERE `id` = '".$extra_data['id']."'";
						     $mysqli->query($query)or die($mysqli->error);
							 $extra_data['quantity']=$dop_row['quantity'];
					     }
						 
						 
						 $discount_arr[] = $extra_data['discount'];
						 $extra_data['price_out'] = ($extra_data['discount'] != 0 )? (($extra_data['price_out']/100)*(100 + $extra_data['discount'])) : $extra_data['price_out'];
						 $summ_in[] = ($extra_data['for_how']=='for_all')? $extra_data['price_in']:$extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = ($extra_data['for_how']=='for_all')? $extra_data['price_out']:$extra_data['quantity']*$extra_data['price_out'];
						 
						  if(isset($uslugi_arr[$extra_data['uslugi_id']])) $usluga = $uslugi_arr[$extra_data['uslugi_id']];
						  else $usluga='';
						  // поправка по НЕТ В СПИСКЕ
						  if($extra_data['other_name'] != ""){
						  	$usluga = $extra_data['other_name'];
						  }
						 
						 $uslugi_details_trs[] = '<tr class="'.(((++$row_counter)==count($dop_row['dop_uslugi']['extra']))?'border_b':'').'"><td class="small right">'.(count($uslugi_details_trs)+1).'</td><td>'.$usluga.'</td><td class="small"></td><td class="center"></td><td class="border_r"></td><td class="right">'.(($extra_data['quantity']!=0 && $extra_data['for_how']=='for_one')?$extra_data['price_in']:number_format($extra_data['price_in']/$extra_data['quantity'],'2','.','')).'</td><td class="right">'.(($extra_data['quantity']!=0 && $extra_data['for_how']=='for_one')?$extra_data['price_out']:number_format($extra_data['price_out']/$extra_data['quantity'],'2','.','')).'</td></tr>';
					 }
					 $uslugi_exists_flag = '1'; 
				 }
			
				 
				 if(count($summ_in)>0){
				     $uslugi_summ_in = array_sum($summ_in);
					 $uslugi_summ_out = array_sum($summ_out);
					 $uslugi_price_in = ($dop_row['quantity']==0)? $uslugi_summ_in:$uslugi_summ_in/$dop_row['quantity'];
					 $uslugi_price_out = ($dop_row['quantity']==0)? $uslugi_summ_out:$uslugi_summ_out/$dop_row['quantity'];
					 
					 $uslugi_details_trs[] = '<tr><td></td><td></td><td></td><td></td><td class="border_r">ИТОГО:</td><td class="right">'.number_format($uslugi_price_in,'2','.','').'</td><td class="right">'.number_format($uslugi_price_out,'2','.','').'</td></tr>';
					 $uslugi_details_window = '<div class="uslugi_details_window"><table border="1"><tr class="head border_b"><td>№</td><td width="200" class="left">вид услуги</td><td width="200" class="left">место</td><td width="60">цвет</td><td width="60" class="border_r">площадь</td><td width="60">вх. / шт</td><td width="60">исх. / шт</td></tr>'.implode('',$uslugi_details_trs).'</table></div>';
					 //$uslugi_btn = '<span>'.count($summ_in).'</span>&nbsp;&nbsp;<span>'.count($summ_in).'</span>'.$uslugi_details_window;
					 $uslugi_btn = '<span>'.count($summ_in).'</span>'.$uslugi_details_window;
				 }
				 else{// если данных по дополнительным услугам  нет выводим кнопку добавление дополнительных услуг
				     $uslugi_price_in = $uslugi_price_out = $uslugi_summ_in = $uslugi_summ_out = 0;
				     //$uslugi_btn = '<span>+</span>&nbsp;&nbsp;<span>+</span>';
					 $uslugi_btn = '<span>+</span>';
				 }
				 


				 // подсчет сумм в ряду
				 $item_price_out = ($dop_row['discount'] != 0 )? (($dop_row['price_out']/100)*(100 + $dop_row['discount'])) : $dop_row['price_out'] ;
				 $item_price_out = round($item_price_out, 2);
				 // 1. подсчитываем входящую сумму
				 $item_summ_in = $dop_row['quantity']*$dop_row['price_in'];
				 $in_summ = $item_summ_in;
				 //if(!(!!$expel["print"]))$in_summ += $print_in_summ;
				 //if(!(!!$expel["dop"]))$in_summ += $dop_uslugi_in_summ;
				 
				 $in_summ += $uslugi_summ_in;

				 
				 // 2. подсчитываем исходящую сумму 
				 $item_summ_out =  $dop_row['quantity']*$item_price_out;
				 $out_summ =  $item_summ_out;
				 //if(!(!!$expel["print"]))$out_summ += $print_out_summ;
				 //if(!(!!$expel["dop"]))$out_summ += $dop_uslugi_out_summ;
				 
				 $out_summ += $uslugi_summ_out;
				 
				 $delta = $out_summ-$in_summ; 
				 $margin = ($in_summ>0 && $out_summ>0)?(($out_summ-$in_summ)/$out_summ)*100:0;
				 
				 $item_price_out = number_format($item_price_out,'2','.','');
				 $item_summ_in_format = number_format($item_summ_in,'2','.','');
				 $item_summ_out_format = number_format($item_summ_out,'2','.','');
				 $uslugi_summ_in_format = number_format($uslugi_summ_in,'2','.','');
				 $uslugi_summ_out_format = number_format($uslugi_summ_out,'2','.','');
				 $uslugi_price_in_format = number_format($uslugi_price_in,'2','.','');
				 $uslugi_price_out_format = number_format($uslugi_price_out,'2','.','');
				 $total_summ_in_format = number_format($in_summ,'2','.','');
				 $total_summ_out_format = number_format($out_summ,'2','.','');
				 $total_price_in_format = ($dop_row['quantity']!=0)? number_format($in_summ/$dop_row['quantity'],'2','.',''):number_format($in_summ,'2','.','');
				 $total_price_out_format = ($dop_row['quantity']!=0)?number_format($out_summ/$dop_row['quantity'],'2','.',''):number_format($out_summ,'2','.','');
				 $delta_format = number_format($delta,'2','.','');
				 $margin_format = number_format($margin,'2','.','');
		         $margin_currency = '%';
				 
				 $svetofor_stat = ($dop_row['row_status']=='')?'green':$dop_row['row_status'];
				 // если ряд не исключен из расчетов добавляем значения в итоговый ряд
				 if(!(!!$expel["main"]) && ($svetofor_stat=='sgreen' || $svetofor_stat=='green')){// && ( || $dop_row['row_status']=='')
					 @$total['item_summ_in'] += $item_summ_in;
					 @$total['item_summ_out'] += $item_summ_out;
					 @$total['uslugi_summ_in'] += $uslugi_summ_in;
					 @$total['uslugi_summ_out'] += $uslugi_summ_out;
					 @$total['in_summ'] += $in_summ;
					 @$total['out_summ'] += $out_summ;
				 }
				 $img_design_path = HOST.'/skins/images/img_design/';
				 
				 $svetofor_src = $img_design_path.'rt_svetofor_'.$svetofor_stat.'.png';
				 $svetofor = '<img src="'.$svetofor_src.'" />';
				 $svetofor .= '<div class="comment_div '.$dop_row['dop_men_text_class'].'" data-href="?page=client_folder&section=rt_position&id='.$key.'&client_id='.$client_id.'" data-id="'.$dop_key.'"></div>';
				 $svetofor_td_attrs = 'svetofor="'.$svetofor_stat.'" class="svetofor pointer center"';
				 $svetofor_tr_display = ($row['svetofor_display']==1 && $dop_row['row_status']=='red')?'hidden':'';
				 $currency = 'р';
				 //$quantity_dim = 'шт';<td width="20" class=" left quantity_dim">'.$quantity_dim.'</td>
				 $discount_arr[] = $dop_row['discount'];
				 $discount = round((float)(array_sum($discount_arr)/count($discount_arr)),2);
				 $discount_str = number_format($discount,'2','.','') .'%';
				 //$srock_sdachi = implode('.',array_reverse(explode('-',$dop_row['shipping_date'])));
				 $srock_sdachi = ($dop_row['shipping_type']=='date')? implode('.',array_reverse(explode('-',$dop_row['shipping_date']))):'';
				 if($srock_sdachi=='00.00.0000') $srock_sdachi='';
				 
				 $expel_class_main = ($expel['main']=='1')?' red_cell':'';
				 $expel_class_print = ($expel['print']=='1')?' red_cell':'';
				 $expel_class_dop = ($expel['dop']=='1')?' red_cell':'';
				 
				  // дополнительная скрытая инфа 
		         
		     }
			 else{
			     $expel = array ("main"=>0,"print"=>0,"dop"=>0);
				 $svetofor = '<img src="'.HOST.'/skins/images/img_design/rt_svetofor_top_btn_'.$svetofor_display_relay_status.'.png" onclick="rtCalculator.svetofor_display_relay(this,true);" class="svetofor_btn">';
			     $svetofor_td_attrs = 'svetofor_btn';
				 $currency = $uslugi_btn = '';
				 $item_price_out = $item_summ_in_format = $item_summ_out_format = $print_in_summ_format = $print_out_summ_format = '';
				 $dop_uslugi_in_summ_format = $dop_uslugi_out_summ_format = $total_summ_in_format = $total_summ_out_format = '';
				 $delta_format = $margin_format = $expel_class_main = $expel_class_print = $expel_class_dop = $quantity_dim = $discount = $discount_str = $srock_sdachi = $uslugi_exists_flag = $print_exists_flag = $margin_currency = $uslugi_summ_in = $uslugi_summ_out = $uslugi_price_in = $uslugi_price_out = $uslugi_summ_in_format = $uslugi_summ_out_format = $uslugi_price_in_format = $uslugi_price_out_format = $total_price_in_format = $total_price_out_format =  '' ;
				 
				  
			 }
			 //$art_id = get_base_art_id($row['art']);
			  
			 $dop_details = '';
			  //echo $row['row_type'].' = ';
			 if($row['row_type'] == 'cat'){ 
				 $extra_panel = '<div data-color="'.$row['rt_row_color'].'" class="pos_plank cat js-color-'.$row['rt_row_color'].''.$alarm_marker.'">
								   <a href="?page=client_folder&section=rt_position&id='.$key.'&client_id='.$client_id.'">'.$row['art'].'</a>
								   <div class="pos_link_plank">
									  <div class="catalog">
										  <a id="" href="/description/'.$row['art_id'].'/" target="_blank" onmouseover="change_href(this);return false;">APL</a>
									  </div>
									  <div class="supplier">
										   '.identify_supplier_by_prefix($row['art']).'
									  </div>
								   </div>
								   '.$confirm_calc_tip.'
								 </div>
								 <div>'.$row['name'].'</div>
								 <div><input type="button" class="getSizesBtn" pos_id="'.$key.'" value="Размеры"></div>';
				 // дополнительная скрытая инфа 
		        if($counter==0 &&  count($row['dop_details'])>0)  $dop_details['allowed_prints'] = $row['dop_details'];
			 }
			 else if($row['row_type'] == 'ext'){
				 $extra_panel = '<div data-color="'.$row['rt_row_color'].'" class="pos_plank ext js-color-'.$row['rt_row_color'].''.$alarm_marker.'">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 '.$confirm_calc_tip.'
								 </div>';
			 }
			 else if($row['row_type'] == 'pol'){
				 $extra_panel = '<div data-color="'.$row['rt_row_color'].'" class="pos_plank pol js-color-'.$row['rt_row_color'].''.$alarm_marker.'">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 
								 '.$confirm_calc_tip.'
								 </div>';
			 }else{
			 	$extra_panel = '<div data-color="'.$row['rt_row_color'].'" class="pos_plank pol js-color-'.$row['rt_row_color'].''.$alarm_marker.'">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 '.$confirm_calc_tip.'
								 </div>';
			 }
			 $block = (isset($dop_row['status_snab']) && ($dop_row['status_snab']=='on_calculation_snab' || $dop_row['status_snab']=='on_recalculation_snab' || $dop_row['status_snab']=='in_calculation'))?1:0;
			 
		     $cur_row  =  '';
		     $cur_row .=  '<tr '.(($counter==0)?'pos_id="'.$key.'" type="'.$row['row_type'].'"':'').' row_id="'.$dop_key.'" art_id="'.$row['art_id'].'" class="'.(($key>1 && $counter==0)?'pos_edge ':'').(((count($row['dop_data'])-1)==$counter)?'lowest_row_in_pos ':'').(($counter!=0)?$svetofor_tr_display:'').(($row_span==0)?'hidden':'').(($block==1)?' block_snab':'').'"  block="'.$block.'">';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="glob_counter" class="top bgWhite glob_counter " width="30" oncontextmenu="openCloseMenu(event,\'contextmenuNew\',{\'pos_id\':\''.$key.'\',\'control_num\':\''.'4'.'\'});">'.$glob_counter.'</td>':'';
			 
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="master_btn" class="top bgWhite master_btn noselect" width="35">   
											<div class="masterBtnContainer" id=""  style="position:relative;">
											   <a name="row'.$glob_counter.'" style="position:absolute;top:-11px;"></a>
											   <input type="checkbox" id="masterBtn'.$key.'" rowIdNum="'.$key.'" name="masterBtn"   onclick="return onClickMasterBtn(this,\'rt_tbl_body\','.$key.');" '.(($row['master_btn'] == 1)? 'checked':'').'/><label for="masterBtn'.$key.'"></label>
											</div>
			                              </td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$dop_key.'</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$row['row_type'].'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="name" class="art_name bgWhite top">'.$extra_panel.'</td>':'';
										  
										  //extra_panel $dop_row['status_snab'].$block.
			 $cur_row .=  '<td class="hidden"></td>
			               <td type="dop_details" class="hidden">'.json_encode($dop_details).'</td>
			               <td width="40" type="svetofor" '.$svetofor_td_attrs.'>'.$svetofor.'</td>
			               <td width="65" type="quantity" class="quantity right r_border"  editable="true">'.$dop_row['quantity'].'</td>
						   <td width="1" style="position:relative"><!-- ячейка для кнопки сохранить --></td>
						   <td width="90" type="item_price_in" editable="true" class="in right">'.$dop_row['price_in'].'</td>
						   <td width="15" type="item_summ_in" class="currency left" style="position:relative;">'.$currency.'<div class="summ_cell">'.$item_summ_in_format.$currency.'</div></td>
						   <td width="90" type="item_price_out" editable="'.(($discount!=0)?'false':'true').'" class="out right">'.$item_price_out.'</td>
						    <td width="15" type="item_summ_out" class="currency left r_border" style="position:relative;">'.$currency.'<div class="summ_cell">'.$item_summ_out_format.$currency.'</div></td>
						   <td width="33" class="calc_btn" uslugi_btn="1" print_exists_flag="'.$print_exists_flag.'" uslugi_exists_flag="'.$uslugi_exists_flag.'" pos_id="'.$key.'" style="position:relative;">'.$uslugi_btn.'</td>
			               <td width="80" type="uslugi_price_in" class="out right '.$expel_class_print.'" expel_suspended="'.$expel['print'].'">'.$uslugi_price_in_format.'</td>
						   
						   
						   <td width="15" type="uslugi_summ_in" class="currency left" style="position:relative;">'.$currency.'<div class="summ_cell">'.$uslugi_summ_in_format.$currency.'</div></td>';
			     if($test_data)	 $cur_row .=  '<td class="test_data">'.$extra_open_data.'</td>';
			             $cur_row .=  '<td width="80" type="uslugi_price_out" class="out right '.$expel_class_dop.'" expel_suspended="'.$expel['dop'].'">'.$uslugi_price_out_format.'</td>
			 
			               <td width="15" type="uslugi_summ_out" class="currency left r_border" style="position:relative;">'.$currency.'<div class="summ_cell">'.$uslugi_summ_out_format.$currency.'</div></td>
			 
			               <td width="50" class="center r_border" type="discount" its_rt="true" discount_fieid="1">'.$discount_str.'</td>
						   <td type="total_price_in" c_stat="0" swiched_cols="summs" class="total in right hidden '.$expel_class_main.'"  expel="'.$expel['main'].'">'.$total_price_in_format.'</td>
						   <td width="15" type="total_summ_in" swiched_cols="summs" c_stat="0" class="currency hidden r_border '.$expel_class_main.'" style="position:relative;">'.$currency.'<div class="summ_cell">'.$total_summ_in_format.$currency.'</div></td>
						   <td type="total_price_out" swiched_cols="summs" c_stat="1" class="total out right '.$expel_class_main.'" expel="'.$expel['main'].'">'.$total_price_out_format.'</td>
						   <td width="15" type="total_summ_out" swiched_cols="summs" c_stat="1" class="currency r_border left '.$expel_class_main.'" style="position:relative;">'.$currency.'<div class="summ_cell">'.$total_summ_out_format.$currency.'</div></td>
						   <td width="70" class="r_border center">'.$srock_sdachi.'</td>
						   <td type="delta" class="delta right">'.$delta_format.'</td>
						   <td type="margin" width="10" class="left" style="position:relative;">'.$currency.'<div style="right:3px;" class="summ_cell">'.$margin_format.$margin_currency.'</div></td>
						   <td></td>
						   <!-- <td raschet_status="1" style="position: relative;overflow:hidden;white-space: nowrap;"  tooltip="'.((isset($dop_row['status_snab']))?$Position_no_catalog->get_name_group($dop_row['status_snab']):'').'"><div style="position:absolute;" class="tooltips">&nbsp;'.((isset($dop_row['status_snab']))?$Position_no_catalog->get_name_group($dop_row['status_snab']):'').'<div></td>-->
						   <td></td>
						</tr>';
			 // загружаем сформированный ряд в итоговый массив
		     $tbl_rows[]= $cur_row;
		     $counter++;
		 }
	 }
	 if(isset($reload['flag']) && $reload['flag'] == true){
	     header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
	     exit;
	 }/**/
	 
	 $rt = '<table class="rt_tbl_head" id="rt_tbl_head" scrolled="head" style="width: 100%;" border="0">
	          <tr class="w_border cap">
			      <td width="30"><button id="js-rt-resort-lock"></button></td>
			       <td width="35" class="top">
				      <div class="master_button_container">
						  <div class="master_button noselect">
							<a href="#" onclick="openCloseMenu(event,\'rtMenu\'); return false;">&nbsp;</a>
							<div id="reset_master_button" class="reset_button'.((count($rows[0])==$mst_btn_summ)?' on':'').'" onclick="resetMasterBtn(this,\'rt_tbl_body\');">&nbsp;</div>
						  </div>
					  </div>
				  </td>
	              <td class="hidden"></td>
				  <td class="hidden">тип</td>
				  <td class="art_name right">
				      <!--<a href="#" onclick="console.log(rtCalculator.tbl_model);/**/print_r(rtCalculator.tbl_model);">_</a>&nbsp;
					  <a href="#" onclick="printCalculator.evoke_calculator_directly({art_id:15431,dop_data_row_id:54,dop_uslugi_id:74});">_</a>&nbsp;
					  <a href="#" onclick="printCalculator.evoke_calculator_directly({art_id:15431,dop_data_row_id:3,quantity:1});">_</a>-->
				  </td>
				  <td class="hidden">dop_details</td>
				  <td class="hidden">draft</td>
				  <td width="40" class="center"><img src="'.HOST.'/skins/images/img_design/rt_svetofor_top_btn_'.$svetofor_display_relay_status_all.'.png" onclick="rtCalculator.svetofor_display_relay(this);"></td>
				  <td width="65" type="quantity" class="quantity right r_border">тираж</td>
				  <td width="1" style="position:relative"><!-- ячейка для кнопки сохранить --></td>
				  <td width="90" class="w_border relative"><div class="cap_name" style="left:105px;">сувенир</div><br><div class="cap_subname">входящая</div></td>
				  <td width="15" class="w_border"></td>
				  <td width="90" class="w_border"><br><div class="cap_subname">исходящая</div></td>
				  <td width="15" class="w_border r_border"></td>
				  <td width="33"><br><span class="small">кол-во</span></td>
			      <td width="80" class="relative"><div class="cap_name">дополнительные услуги</div><br><div class="cap_subname">входящая</div></td>
			      <td width="15"></td>';
    if($test_data)	 $rt.= '<td class="test_data_cap">доп.усл подробн</td>';
           $rt.= '<td width="80" class="out pointer"><br><div class="cap_subname">исходящая</div></td>
				  <td width="15" class="r_border"></td>
				  <td width="50" class="w_border center">наценка<br><span class="small">ср. знач-е</span></td>
				  <td swiched_cols="summs" c_stat="0" class="total grey pointer hidden right">итого<br><span class="small">входящая</span></td>
				  <td width="15" swiched_cols="summs" c_stat="0" class="hidden pointer grey r_border"></td>
				  <td swiched_cols="summs"  c_stat="1" class="total grey pointer right">итого<br><span class="small">исходящая</span></td>
				  <td width="15" swiched_cols="summs" c_stat="1" class="grey pointer r_border"></td>
				  <td width="70" class="center r_border">срок сдачи</td>
				  <td class="delta right">маржа</td>
				  <td width="10"></td>
				  <td class="margin center"></td>
				  <td><!--статус--></td>';              
	    $rt.= '</tr>
	           <tr row_id="total_row" class="grey bottom_border">
			      <td width="30" height="18"></td>
			      <td width="35"></td>
	              <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="right"></td>
				  <td class="hidden">dop_details</td>
				  <td width="15"></td>
				  <td class="quantity r_border"></td>
				  <td width="1" style="position:relative"><!-- ячейка для кнопки сохранить --></td>
				  <td type="item_summ_in" class="right">'.number_format(@$total['item_summ_in'],'2','.','').'</td>
				  <td width="15">р</td>
				  <td type="item_summ_out" class="right">'.number_format(@$total['item_summ_out'],'2','.','').'</td>
				  <td width="15" class="r_border">р</td>
				  <td></td>	  
			      <td type="uslugi_summ_in" class="right">'.number_format(@$total['uslugi_summ_in'],'2','.','').'</td>
			      <td>р</td>';
    if($test_data)	$rt.= '<td class="test_data_cap"></td>';
           $rt.= '<td width="80" type="uslugi_summ_out" class="out right">'.number_format(@$total['uslugi_summ_out'],'2','.','').'</td>
				  <td width="" class="r_border">р</td>
				  <td width="50" class=""></td>
			      <td type="total_summ_in" swiched_cols="summs" c_stat="0" class="grey right hidden">'.number_format(@$total['in_summ'],'2','.','').'</td>
				  <td width="15" swiched_cols="summs" c_stat="0" class="left hidden r_border">р</td>
				  <td type="total_summ_out" swiched_cols="summs" c_stat="1" class="grey right">'.number_format(@$total['out_summ'],'2','.','').'</td>
				  <td width="15" swiched_cols="summs" c_stat="1" class="left r_border">р</td>
				  <td width="70" class="r_border"></td>
				  <td type="delta" class="right">'.number_format((@$total['out_summ']-@$total['in_summ']),'2','.','').'</td>
				  <td width="10" class="left">р</td>
				  <td type="margin" class="right"></td>
                  <td></td>
				</tr>
	         </table>'; 
	   if(isset($tbl_rows)){          
	       $rt.= '<div id="scrolled_part_container" class="scrolled_tbl_movable_part" style="position:relative;">
	             <table class="rt_tbl_body" id="rt_tbl_body" scrolled="body" client_id="'.$client_id.'" query_num="'.$query_num.'" user_id="'.$user_id.'" border="0">'.implode('',$tbl_rows).'</table>'
				 .(($block_page_elements)?'<div id="blanket" style="position:absolute;top:0px;left:0px;right:0px;bottom:0px;border:#ff0000 solid 0px" onclick="noticeQueryBlocked();"></div>':'').'
			  </div>';
	    }
		else{
		   $rt.= '<table class="rt_tbl_body" id="rt_tbl_body" client_id="'.$client_id.'" query_num="'.$query_num.'" user_id="'.$user_id.'" border="0" width="100%" height="400"><tr><td align="center">зявка не содержит данных</td></tr></table>';
		} 
			  
	/*	
	     РЕАЛИЗАЦИЯ ФУНКЦИОНАЛА "ДРАФТ" оказалась не востребованной
		 
		 ПРИМ. поле draft можно удалить из таблицы в базе данных
	
		 // РЯДЫ ДЛЯ РАСЧЕТА ВАРИАНТОВ!!! $dop_row['draft'] - для реализации функционала при котором позиция может иметь несколько расчетов, 
		 // при этом может быть что ни один из низ не выбран как окончательный, или один выбран а остальные остаются вариантами, введено понятие
		 // draft - (черновик,проект,эскиз) для маркирования таких рядов в базе данных - объявляется следующее правило - если позиция имеет один  
		 // расчетный ряд то он не может быть draft (он окончательный и участвует в дальнейших расчетах), если несколько они могут все быть draft,
		 // (и в дальшейших расчетах не участвуют) если один из низ не draft значит он окончательный (он окончательный и участвует в дальнейших
		 // расчетах), если позиция имеет несколько расчетных рядов, то не draft из них может быть только один
		 // ПРИМ. если этот вариант останется рабочим удалить из таблицы поле final
		 //
		 // был еще вариант обратный маркировать окончательные финальные ряды - пока от него ушли
		 // ФИНАЛЬНЫЙ РЯД!!! $dop_row['final'] - для реализации функционала при котором позиция может иметь несколько расчетов, при этом может быть
		 // что ни один из низ не является финальным, или один может быть установлен как финальный и тогда он участвует в дальнейшем расчете заказа
		 
	 
	
	
		  
		 // если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета не является draft
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 // при этом мы указываем $all_draft = FALSE; что не все ряды являются draft
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 
		 $all_draft = FALSE;
		 if(isset($row['dop_data']) && count($row['dop_data'])>1){
		     $all_draft = TRUE;
			 foreach($row['dop_data'] as $dop_key => $dop_row){
				 if($dop_row['draft']!=1){
				      $all_draft = FALSE;
					  $row_to_lift_up[$dop_key] = $dop_row;
					  unset($row['dop_data'][$dop_key]);
				  }
			 }
			 // использование в данном случае не возможно потому что этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
			 if(isset($row_to_lift_up)){
			      $row['dop_data']= $row_to_lift_up + $row['dop_data']; 
				  unset($row_to_lift_up);
			 }
		 }
		 else{
		     // !!! НАДО КАК_ТО ЭТО ОТРАБОТАТЬ
		 }
		 
		 // здесь определяем выводить ли дополнительный вспомогательный пустой ряд сверху
		 // когда все остальные являются draft то выводим  т.е. (если не все draft то невыводим)
		 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
		 if($all_draft) $row['dop_data']= $service_row + $row['dop_data']; 
			  */
			  
			  
			  
			  
?>