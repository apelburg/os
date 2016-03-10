<?php
    class rtCalculators{
	    //public $val = NULL;
		public static $outOfLimit = false;
		public static $outOfLimitDetails  = array();
		public static $needIndividCalculation = false;
		public static $needIndividCalculationDetaigrab_datals  = array();
		public static $lackOfQuantity  = false;
		public static $lackOfQuantityDetails = array();
	    function __consturct(){
		}
		static function grab_data($data){
		    // начальный общий метод для всех услуг (получение информации по калькуляторам)
			
			// ветвим
			// если расчитываем нанесение
            if($data->type=='print'){
			    self::grab_data_for_print($data);
			}
	
		}
		static function fetch_dop_uslugi_for_row($dop_data_row_id){
		    global $mysqli; 
			
			$out_put = array();
			  
            // получаем данные о расчетах дополнительных услуг для данного ряда расчета
			$query="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `glob_type` = 'print' AND `dop_row_id` = '".$dop_data_row_id."'";
			//echo $query;
			
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
					$out_put[] = $row;	
				}
			}
            //print_r($out_put);
			echo json_encode($out_put);
			
		}
		static function fetch_data_for_dop_uslugi_row($dop_uslugi_row_id){
		    global $mysqli; 
			
			$out_put = array();
			  
            // получаем данные о расчетах дополнительных услуг для данного ряда расчета
			$query="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `glob_type` = 'print' AND `id` = '".$dop_uslugi_row_id."'";
			//echo $query;
			
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    $row = $result->fetch_assoc();
				echo json_encode($row);
			}
		}
		static function grab_data_for_print($data){
		    // global $mysqli;   
			// print_r($data);
			
			
			// в результате действия метода будет получен массив всех данных для артикула необходимых для совершения
			// расчетов в калькуляторе JavaScript метод вызывающий данную функцию называется evoke_calculator()
			// массив 
			// 
            //  Array  (
            //            [places] => масссив мест нанесений (каждое из которых содержит массив типов нанесений связанных с местом)
            //            [print_types] => масссив всех типов нанесений,                        
			//                             каждый отдельный элемент массива соответсвует отдельному нанесению и содержит информацию
			//                             о цветах применяемых при печате, размерах печати и таблицы - прайсы с расценками 
			//                             нанесения.
			//          )
           
			
			// нам надо сделать чтобы первым в массиве [places] по умолчанию, всегда, шло место нанесения "не определено", затем все остальные прикрепленные к артикулу, ЕЩЕ ЕСТЬ случай когда нанесения прикреплены к артикулу напрямую не через места, их надо выводить отдельным ЭЛЕМЕНТОМ
			// в итоге на первом этапе в [places] создаем лемент "не определено", а в [print_types]  забрасывем абсолютно все типы нанесения (раньше забрасывали те которые могли понадобиться), а затем а [places] добавляем прикрепленные к артикулу места нанесения
			
			
			
			$out_put = self::get_all_print_types(); 
			//print_r($out_put);
			// получаем дополнительные данные соответсвующие нанесениям ( возможные размеры, цвета, таблицы прайсов )
			$level = (isset($data->level))?$data->level:'full'; // тип прайса - рекламщики, конечники
            
			
			// если id артикула передано и не равно 0 
			if(isset($data->art_id) && (int)$data->art_id!=0){ 
				$print_places = self::get_related_print_places($data->art_id);
				
				// здесь НИВКОЕМ СЛУЧАЕ не использовать array_merge() он перезаписывает значени ключей
				if($print_places) $out_put['places'] = $out_put['places']+$print_places['out_put']['places'];
			}
			
			// ищем типы нанесения присвоенные данному артикулу на прямую
			// места нанесения и добавляем их в $out_put внутри "стандартно"
			/* раньше еще делал это
			   // проверяем на совпадение с уже ранее добавленными в $out_put типами нанесения 
			   // если есть совпадение то пропускаем такой тип нанесения
			   // если были найдены какието оригинальные типы нанесения то добавляем их в $out_put внутри "Стандартно"*/
			$out_put = self::get_related_art_and_print_types($out_put,$data->art_id,false);
			
			$out_put = self::get_print_types_related_data($out_put,$level/*,$all_sizes_in_one_place*/);
			
			//print_r($out_put);
			echo json_encode($out_put);
		
		}
		static function grab_data_for_print_old($data){
		    // global $mysqli;   
			// print_r($data);
			
			// в результате действия метода будет получен массив всех данных для артикула необходимых для совершения
			// расчетов в калькуляторе JavaScript метод вызывающий данную функцию называется evoke_calculator()
			// массив 
			// 
            //  Array  (
            //            [places] => масссив мест нанесений (каждое из которых содержит массив типов нанесений)
            //            [print_types] => масссив типов нанесений, которые могут быть использованны применительно к данному артикулу,
			//                             вынесен в отдельный массив, чтобы избежать дублирования так как разные места нанесений могут
			//                             содержать один и тот же тип нанесения.
			//                             каждый отдельный элемент массива соответсвует отдельному нанесению и содержит информацию о 
			//                             цветах применяемых при печате, размерах печати и таблицы - прайсы с расценками нанесения.
			//          )
           
			
			// если id артикула равно 0 или id артикула совсем не передано добавляем все имеющиеся виды во вкладку Стандартно
			if((int)$data->art_id==0 || !isset($data->art_id)){ 
			    // $all_sizes_in_one_place = TRUE;
			    $out_put = self::get_all_print_types(); 
			}
			else if(isset($data->art_id) && (int)$data->art_id!=0){ 
			    // $all_sizes_in_one_place = FALSE;
			    // получаем (если установленны) данные о конкретных местах нанесения для данного артикула
				// создаем дополнительный (служебный) массив из id типов нанесения, который мы будем использовать 
				// на следующем этапе чтобы отфильтровать повторяющиеся типы нанесения
				//
                $out_put_data1 = self::get_related_print_places($data->art_id);
				$out_put = $out_put_data1['out_put'];
				
				// ищем типы нанесения присвоенные данному артикулу на прямую
				// при этом проверяем на совпадение с уже ранее добавленными в $out_put типами нанесения 
				// если есть совпадение то пропускаем такой тип нанесения
				// если были найдены какието оригинальные типы нанесения добавляем их в $out_put внутри 
				// места нанесения "Стандартно"
			    $out_put = self::get_related_art_and_print_types($out_put,$data->art_id,$out_put_data1['print_types_ids']);

				// если до этого момента никакой информации не было найдено 
				// добавляем все имеющиеся виды во вкладку Стандартно
				if(count($out_put)==0){
				    // $all_sizes_in_one_place = TRUE;
		            $out_put = self::get_all_print_types(); 
				}
				
			}
			
			// получаем дополнительные данные соответсвующие нанесениям ( возможные размеры, цвета, таблицы прайсов )
			$level = (isset($data->level))?$data->level:'full';
            $out_put = self::get_print_types_related_data($out_put,$level/*,$all_sizes_in_one_place*/);
			
			//print_r($out_put);
			echo json_encode($out_put);
		
		}
		static function get_related_art_and_print_types($out_put,$art_id,$print_types_ids){
		    global $mysqli;  
						
			//UPDATE `new__base__print_mode` SET `print_id`=13 WHERE `print` = 'шелкография'
			// получаем данные о типах нанесений соответсвующих данному артикулу на прямую
			// $query="SELECT*FROM `".BASE_PRINT_MODE_TBL."`  WHERE `art_id` = '".$art_id."'";
			
			$query="SELECT tbl1.`print_id` print_id, tbl2.`name` name FROM `".BASE_PRINT_MODE_TBL."` tbl1  INNER JOIN 
			                    `".OUR_USLUGI_LIST."` tbl2   ON tbl1.`print_id`  = tbl2.`id`
			                    WHERE tbl1.`art_id` = '".$art_id."'";
			
			 
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				$print_types = array();
				
			    while($row = $result->fetch_assoc()){
				    //if($row['print_id']!=0 && is_array($print_types_ids) && !in_array($row['print_id'],$print_types_ids)){
					if($row['print_id']!=0){
					
					    // создаем элемент (массив) с ключом равным id типа нанесения, дальше в этот массив будет добавленна подробная 
						// информация об этом нанесении с помощью функции get_print_types_related_data()
				        if(!isset($out_put['print_types'][$row['print_id']])) $out_put['print_types'][$row['print_id']] = array();
					    $print_types[$row['print_id']] = $row['name'];		
					}
				}
				// если после фильтрации были найдены данные 
                // добавляем результат в итоговый массив ключем устанавливаем 0 (чтобы данное место нанесения выводилось первым)
				// в остальных случаях используется id места нанесения (оно не может быть 0 поэтому не будет конфликта)
				if(count($print_types)>0){
				    //$index = count($out_put['places']);
					$index = -1;
				    $out_put['places'][$index]['name'] = 'стандартно';
					$out_put['places'][$index]['prints'] = $print_types;	
				}
			}
			return $out_put;
		}
		static function get_related_print_places($art_id){
		    global $mysqli;  
			
			$out_put = $print_types_ids = array();
			
			// получаем данные о местах нанесений соответсвующих данному артикулу
			$query="SELECT tbl1.`place_id` place_id, tbl2.`name` name  FROM `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl1 
			        INNER JOIN  `".BASE__PRINT_PLACES_TYPES_TBL."` tbl2 
					ON tbl1.`place_id`  = tbl2.`id`
                    WHERE tbl1.`art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				    // получаем данные о типах нанесений соответсвующих данному месту
					$query_2="SELECT  tbl1.print_id print_id,tbl2.name name FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` tbl1
					          INNER JOIN  `".OUR_USLUGI_LIST."` tbl2 
					          ON tbl1.`print_id`  = tbl2.`id` 
							  WHERE tbl1.`place_id` = '".$row['place_id']."' GROUP BY tbl1.print_id";
					
					$result_2 = $mysqli->query($query_2)or die($mysqli->error);
					if($result_2->num_rows>0){
					    $print_types = array();
						while($row_2 = $result_2->fetch_assoc()){
						    // создаем массив с ключом равным id типа нанесения, дальше в этот массив будет добавленна подробная
						    $print_types[$row_2['print_id']] = $row_2['name'];
							// служебный для следующего этапа
							$print_types_ids[] = $row_2['print_id'];
						}
						// добавляем результат в итоговый массив ключем устанавливаем id места нанесения
						$out_put['places'][$row['place_id']]['name'] = $row['name'];
						$out_put['places'][$row['place_id']]['prints'] = $print_types;		
					}			
				}
			}
			
			return (count($out_put)>0)? array('out_put'=>$out_put,'print_types_ids'=>$print_types_ids):false;
		}
		static function get_all_print_types(){
		    global $mysqli; 
			
			$out_put = array();

			$out_put['places'][0]['name'] = 'не определено';
			// получаем данные всех возможных типах нанесений
			$query="SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '6' ORDER BY id";
			
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
				$print_types = array();
				while($row = $result->fetch_assoc()){
					if(!isset($out_put['print_types'][$row['id']])) $out_put['print_types'][$row['id']] = array();	
					
					$print_types[$row['id']] = $row['name'];
				}
				// добавляем результат в итоговый массив ключем устанавливаем id места нанесения
				$out_put['places'][0]['prints'] = $print_types;		
			}	
			return $out_put;		
		}
		static function get_print_types_related_data($out_put,$level/*,$all_sizes_in_one_place*/){
		    global $mysqli;  
			
			//$print_types = $out_put['print_types'];
			// 
			foreach($out_put['print_types'] as $print_id => $val){
			    //if($print_id != 13) continue;
			    // выбираем дополнительные параметры  определяющие вертикальную позицию в прайсе (такие как например цвета)
				$query="SELECT*FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE `print_type_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    
				    while($row = $result->fetch_assoc()){
					    $out_put['print_types'][$print_id]['y_price_param'][$row['value']] = array('percentage'=>$row['percentage'],'item_id'=>$row['id']);   
				    }
				}
				
				
				// выбираем таблицу с расценками 
				$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."' AND `level` = '".$level."' ORDER by id, param_val";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $count = $row['count'];
					    unset($row['id'],$row['print_type_id'],$row['count']);
						
						if(!isset($end[$row['price_type']])){
						
						   $end[$row['price_type']] = false;
						   //echo "\r";
						 }
						
						//if($row['price_type'] == 'in') print_r($row);
						
						if($row['param_val']==0){
							 for($i=1;isset($row[$i]);$i++){
								 if($row[$i] >0){
								      $end[$row['price_type']] = $i;
									  $row['maxXIndex'] = $i;
								 }
							 }
						}
						//echo $end[$row['price_type']];
						if($end[$row['price_type']]){
							 for($i=1;isset($row[$i]);$i++){
								 if($i >(int)$end[$row['price_type']]){
								  //echo $i." ".(int)$end[$row['price_type']]." ". $row[$i]."\r ";
								  unset($row[$i]);
								  }
							 }
						}	
						
					    if($row['price_type']=='out') $out_put['print_types'][$print_id]['priceOut_tbl'][$count][] = $row; 
						else if($row['price_type']=='in')  $out_put['print_types'][$print_id]['priceIn_tbl'][$count][] = $row; /* */
				    }
				}
				
				// if($all_sizes_in_one_place)
				
				// выбираем данные по размерам нанесения в соответствии с типом и местом нанесения
				// если место нанесения одно ($out_put['places'] содержит один элемент) и его ключ равен 0 (ключ обозначает
				// типа нанесения) значит  это дефолтное место нанесения добавленное системой в случае когда не было найдено
				// мест нанесений добавленных напрямую (товару либо было присвоен тип нанесения напрямую либо вообще ничего
				// небыло присвоено) тогда добавляем к этому типу нанесения специально для такого случая промаркированные
				// размеры нанесения
				//if(count($out_put['places'])==1 && key($out_put['places'])==0) $default_sizes = true;
				
				$query="SELECT*FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE `print_id` = '".$print_id."'";
				$query .=" ORDER by id";
				// echo $query; exit;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $place_id = $row['place_id'];
						$row['item_id'] = $row['id'];
						$default = $row['default'];
					    unset($row['id'],$row['place_id'],$row['default']);
						
						
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения и id места нанесения
					    $out_put['print_types'][$print_id]['sizes'][$place_id][] = $row; 
						// а также сбрасываем все места нанесения в один общий элемент для типов "не определено" и "стандартно"
						if($default=='1') $out_put['print_types'][$print_id]['sizes'][0][] = $row; 
						
				    }
				}
				
				
				// выбираем данные по коэффициэнтам влияющим на цену товара
				$query="SELECT*FROM `".BASE__CALCULATORS_COEFFS."` WHERE `print_id` = '".$print_id."' ORDER by id";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				   $data = $coeff_data= array();
				    while($row = $result->fetch_assoc()){
					    $coeff_data[$row['target']][$row['type']][] = array('item_id'=>$row['id'],'title'=>$row['title'],'coeff'=>$row['percentage']);
						$data[$row['target']][$row['type']] =  array('optional'=>$row['optional'],'multi'=>$row['multi'],'data'=>$coeff_data[$row['target']][$row['type']]);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения
					    $out_put['print_types'][$row['print_id']]['coeffs'][$row['target']] = $data[$row['target']];   
				    }
				}
				
				// выбираем данные по надбавкам влияющим на цену товара
				$query="SELECT*FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE `print_id` = '".$print_id."' ORDER by id";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    $data = $additions_data= array();
				    while($row = $result->fetch_assoc()){
					    $additions_data[$row['target']][$row['type']][] = array('item_id'=>$row['id'],'title'=>$row['title'],'value'=>$row['value']);
						$data[$row['target']][$row['type']] =  array('optional'=>$row['optional'],'multi'=>$row['multi'],'data'=>$additions_data[$row['target']][$row['type']]);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения
					    $out_put['print_types'][$row['print_id']]['additions'][$row['target']] = $data[$row['target']];   
				    }
				}
				// НУЖНА ЕЩЕ ИНФОРМАЦИЯ О СТОИМОСТИ ПОДГОТОВИТЕЛЬНЫХ РАБОТ
			}
			
			return $out_put;
		}
		
		static function json_fix_cyr($json_str) { 
			$cyr_chars = array ( 
			'\u0430' => 'а', '\u0410' => 'А', 
			'\u0431' => 'б', '\u0411' => 'Б', 
			'\u0432' => 'в', '\u0412' => 'В', 
			'\u0433' => 'г', '\u0413' => 'Г', 
			'\u0434' => 'д', '\u0414' => 'Д', 
			'\u0435' => 'е', '\u0415' => 'Е', 
			'\u0451' => 'ё', '\u0401' => 'Ё', 
			'\u0436' => 'ж', '\u0416' => 'Ж', 
			'\u0437' => 'з', '\u0417' => 'З', 
			'\u0438' => 'и', '\u0418' => 'И', 
			'\u0439' => 'й', '\u0419' => 'Й', 
			'\u043a' => 'к', '\u041a' => 'К', 
			'\u043b' => 'л', '\u041b' => 'Л', 
			'\u043c' => 'м', '\u041c' => 'М', 
			'\u043d' => 'н', '\u041d' => 'Н', 
			'\u043e' => 'о', '\u041e' => 'О', 
			'\u043f' => 'п', '\u041f' => 'П', 
			'\u0440' => 'р', '\u0420' => 'Р', 
			'\u0441' => 'с', '\u0421' => 'С', 
			'\u0442' => 'т', '\u0422' => 'Т', 
			'\u0443' => 'у', '\u0423' => 'У', 
			'\u0444' => 'ф', '\u0424' => 'Ф', 
			'\u0445' => 'х', '\u0425' => 'Х', 
			'\u0446' => 'ц', '\u0426' => 'Ц', 
			'\u0447' => 'ч', '\u0427' => 'Ч', 
			'\u0448' => 'ш', '\u0428' => 'Ш', 
			'\u0449' => 'щ', '\u0429' => 'Щ', 
			'\u044a' => 'ъ', '\u042a' => 'Ъ', 
			'\u044b' => 'ы', '\u042b' => 'Ы', 
			'\u044c' => 'ь', '\u042c' => 'Ь', 
			'\u044d' => 'э', '\u042d' => 'Э', 
			'\u044e' => 'ю', '\u042e' => 'Ю', 
			'\u044f' => 'я', '\u042f' => 'Я', 
			
			'\r' => '',
			'\n' => '<br />', 
			'\t' => '' 
			); 

			foreach ($cyr_chars as $cyr_char_key => $cyr_char) { 
			    $json_str = str_replace($cyr_char_key, $cyr_char, $json_str); 
			} 
			return $json_str; 
         }
		 static function save_calculatoins_result_router($details_obj){
		 
		    //print_r($details_obj);
			//exit;
		    if(isset($details_obj->print_details)){
		        if($details_obj->print_details->calculator_type=='free'){
					// надо убирать из таблицы RT_DOP_USLUGI поле uslugi_id потому что его может не быть 
					// и убирать все дальнейшие связанные с этим полем обработки например в карточке товара
					$details_obj->print_details->print_id = 0;
					$details_obj->print_details->quantity = $details_obj->quantity;
				}
				
				if($details_obj->print_details->calculator_type=='auto' || $details_obj->print_details->calculator_type=='manual'){
				    if(isset($details_obj->print_details->dop_params->YPriceParam)){
						foreach($details_obj->print_details->dop_params->YPriceParam as $key => $data){
						   if(isset($data->cmyk)) $details_obj->print_details->dop_params->YPriceParam[$key]->cmyk =  base64_encode($data->cmyk);
						} 
				    }
				}
				
				$details_obj->print_details->comment = (isset($details_obj->print_details->comment))? base64_encode($details_obj->print_details->comment):'';
				$details_obj->print_details->type = (isset($details_obj->type))? $details_obj->type:'';
				$details_obj->print_details->quantity_details = (isset($details_obj->print_details->quantity_details))? $details_obj->print_details->quantity_details:'';
				$details_obj->print_details->comment = (isset($details_obj->print_details->comment))? base64_encode($details_obj->print_details->comment):'';
				
				if(isset($details_obj->type) && $details_obj->type=='union') $details_obj->print_details->union_price = array('in'=>$details_obj->price_in,'out'=>$details_obj->price_out);
				
				// если PHP 5.4 то достаточно этого
				   /* $print_details = json_encode($details_obj->print_details,JSON_UNESCAPED_UNICODE);*/
				// но пришлось использовать это
				$details_obj->print_details_json = self::json_fix_cyr(json_encode($details_obj->print_details)); 
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		 
		    if(isset($details_obj->print_details->distribution_type) && $details_obj->print_details->distribution_type=='union'){ // объединенный тираж
			     if(isset($details_obj->united_calculations)){// уже существующий расчет обновление
				     $united_calculations = explode(',',$details_obj->united_calculations);
				     $ln = count($united_calculations);
				     for($i=0; $i<$ln; $i++){
					     // echo $dop_data_row_id."\r\n";
						 $details_obj->dop_uslugi_id = $united_calculations[$i];
						 echo $details_obj->dop_uslugi_id;
						 $cur_data=array('type'=>$details_obj->type,'quantity'=>(int)$details_obj->print_details->quantity_details[$i],'union_quantity'=>array_sum($details_obj->print_details->quantity_details));
					     rtCalculators::save_calculatoins_result_new($cur_data,$details_obj);
					 }

			     }
				 else if(isset($details_obj->print_details->dop_data_ids)){// новый расчет
				     $last_uslugi_ids = array();
				     //  Здесь надо переделывать потом данные по тиражу и строке расчета должны быть связаны
					 $ln = count($details_obj->print_details->dop_data_ids);
					 for($i=0; $i<$ln; $i++){
					     // echo $dop_data_row_id."\r\n";
						 $cur_data=array('dop_data_row_id'=>$details_obj->print_details->dop_data_ids[$i],'distribution_type'=>$details_obj->print_details->distribution_type,'quantity'=>(int)$details_obj->print_details->quantity_details[$i],'union_quantity'=>array_sum($details_obj->print_details->quantity_details));
					     $last_uslugi_ids[] = rtCalculators::save_calculatoins_result_new($cur_data,$details_obj);
					 }
					 // вносим в базу id-шники связанных нанесений 
					 if(count($last_uslugi_ids)>0) rtCalculators::mark_united_calculatoins($last_uslugi_ids);
				 }
			 }
			 else{ // необъединенный тираж
			     if(isset($details_obj->dop_data_row_id)){

					 $ln = count($details_obj->dop_data_row_id);
					 for($i=0; $i<$ln; $i++){
					     // echo $dop_data_row_id."\r\n";
						 $cur_data=array('dop_data_row_id'=>$details_obj->dop_data_row_id[$i],'quantity'=>(int)$details_obj->quantity);
					     rtCalculators::save_calculatoins_result_new($cur_data,$details_obj);
					 }
				 }
			 }
			//print_r($last_uslugi_ids);
		 }
		 static function save_calculatoins_result_new($cur_data,$details_obj){
		    global $mysqli;  
			//echo $dop_data_row_id;
			
		   
		    // если это объедененный тираж высчитываем цену для каждого расчета отдельно исходя из общего тиража
			if(isset($cur_data['distribution_type']) && $cur_data['distribution_type']=='union'){
			    // echo ' - '.$details_obj->price_in.' - '.$cur_data['union_quantity'].' - '.$cur_data['quantity']."\r";
			    $price_in = round(($details_obj->price_in/$cur_data['union_quantity'])*$cur_data['quantity'],2);
			    $price_out = round(($details_obj->price_out/$cur_data['union_quantity'])*$cur_data['quantity'],2);
			}
			else{
			    $price_in = $details_obj->price_in;
			    $price_out = $details_obj->price_out;
			}

			// если нет dop_uslugi_id или он равен ноль, добавляем новый расчет доп услуг для ряда 
			// иначе перезаписываем данные в строке где `id` = $details_obj->dop_uslugi_id
			if(!isset($details_obj->dop_uslugi_id) || $details_obj->dop_uslugi_id ==0){
			    $query="INSERT INTO `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$cur_data['dop_data_row_id']."',
									   `uslugi_id` ='".$details_obj->print_details->print_id."',
									   `performer` ='".self::get_performer_id($details_obj->print_details->print_id)."',
									   `glob_type` ='print',
									   `tz` ='".cor_data_for_SQL($details_obj->print_details->comment)."',
									   `quantity` ='".$cur_data['quantity']."',
									   `price_in` = '".$price_in."',
									   `price_out` ='".$price_out."',
									   `discount` ='".$details_obj->discount."',
									   `creator_id` ='".$details_obj->creator_id."',
									   `print_details` ='".cor_data_for_SQL($details_obj->print_details_json)."'"; 
									   
				//echo $query;
				$mysqli->query($query)or die($mysqli->error);
				
				return $mysqli->insert_id;

			}
			else if(isset($details_obj->dop_uslugi_id) && $details_obj->dop_uslugi_id !=0){
			   $query="UPDATE `".RT_DOP_USLUGI."` SET
									   `uslugi_id` ='".$details_obj->print_details->print_id."',
									   `performer` ='".self::get_performer_id($details_obj->print_details->print_id)."',
									   `tz` ='".$details_obj->print_details->comment."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `creator_id` ='".$details_obj->creator_id."',
									   `print_details` ='".cor_data_for_SQL($details_obj->print_details_json)."'
									    WHERE `id` ='".$details_obj->dop_uslugi_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
			
		} 
		static function mark_united_calculatoins($ids){
		    global $mysqli; 
			
			echo 'mark_united_calculatoins-'; 
			$ln = count($ids);
			for($i=0; $i<$ln; $i++){
				 $query="UPDATE `".RT_DOP_USLUGI."` SET
									   `united_calculations` ='".implode(',',$ids)."'
									    WHERE `id` ='".$ids[$i]."'"; 
				 $mysqli->query($query)or die($mysqli->error);
			}
			//`united_calculations` ='".implode(',',array_diff($ids,array($ids[$i])))."'
		}
		static function save_calculatoins_result($details_obj){
		    global $mysqli;  
			
		     print_r($details_obj);
			exit;
			if($details_obj->print_details->calculator_type=='free'){
			    // надо убирать из таблицы RT_DOP_USLUGI поле uslugi_id потому что его может не быть 
				// и убирать все дальнейшие связанные с этим полем обработки например в карточке товара
			    $details_obj->print_details->print_id = 0;
				$details_obj->print_details->quantity = $details_obj->quantity;
			}
			
			if($details_obj->print_details->calculator_type=='auto' || $details_obj->print_details->calculator_type=='manual'){
				foreach($details_obj->print_details->dop_params->YPriceParam as $key => $data){
				   if(isset($data->cmyk)) $details_obj->print_details->dop_params->YPriceParam[$key]->cmyk =  base64_encode($data->cmyk);
				} 
			}
			
            $details_obj->print_details->comment = (isset($details_obj->print_details->comment))? base64_encode($details_obj->print_details->comment):'';
            // если PHP 5.4 то достаточно этого
               /* $print_details = json_encode($details_obj->print_details,JSON_UNESCAPED_UNICODE);*/
			// но пришлось использовать это
			if(isset($details_obj->print_details)) $print_details = self::json_fix_cyr(json_encode($details_obj->print_details)); 
			

			// если нет dop_uslugi_id или он равен ноль, добавляем новый расчет доп услуг для ряда 
			// иначе перезаписываем данные в строке где `id` = $details_obj->dop_uslugi_id
			if(!isset($details_obj->dop_uslugi_id) || $details_obj->dop_uslugi_id ==0){
			    $query="INSERT INTO `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `uslugi_id` ='".$details_obj->print_details->print_id."',
									   `performer` ='".self::get_performer_id($details_obj->print_details->print_id)."',
									   `glob_type` ='print',
									   `tz` ='".cor_data_for_SQL($details_obj->print_details->comment)."',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `discount` ='".$details_obj->discount."',
									   `creator_id` ='".$details_obj->creator_id."',
									   `print_details` ='".cor_data_for_SQL($print_details)."'"; 
				//echo $query;
				$mysqli->query($query)or die($mysqli->error);
				//echo 1;
			}
			else if(isset($details_obj->dop_uslugi_id) && $details_obj->dop_uslugi_id !=0){
			   $query="UPDATE `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `uslugi_id` ='".$details_obj->print_details->print_id."',
									   `performer` ='".self::get_performer_id($details_obj->print_details->print_id)."',
									   `glob_type` ='print',
									   `tz` ='".$details_obj->print_details->comment."',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `creator_id` ='".$details_obj->creator_id."',
									   `print_details` ='".$print_details."'
									    WHERE `id` ='".$details_obj->dop_uslugi_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
		}
		static function get_performer_id($usluga_id){
		    global $mysqli;  
			
			$query="SELECT performer FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$usluga_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			$row = $result->fetch_assoc();
			return $row['performer'];
		}
		static function delete_prints_for_row($dop_row_id,$usluga_id,$all){
		    global $mysqli;  
			// если надо удалить все расчеты нанесения
			if($all && !$usluga_id){
			    $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									    glob_type ='print' AND `dop_row_id` ='".$dop_row_id."'"; 
				 echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
			else if($usluga_id && !$all){
			     $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									   `id` ='".$usluga_id."' AND `dop_row_id` ='".$dop_row_id."' "; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			}
		}
		static function distribute_print($details){
		    global $mysqli;  
		    $details_obj = json_decode($details);
			unset($details_obj->calculationData->price_in);
			unset($details_obj->calculationData->price_out);
			unset($details_obj->calculationData->quantity);
			unset($details_obj->calculationData->dop_data_row_id);
			unset($details_obj->calculationData->print_details->priceIn_tblXindex);
			unset($details_obj->calculationData->print_details->priceOut_tblXindex);
									  
		    //print_r($details_obj);echo "\r\n";
		    $out = array();
			//// $details_obj->calculationData->dop_uslugi_id;
			// exit;
			// определяем к какому варианту расчета относится данное нанесение 
			// и затем исключим его в следующей выборке чтобы повторно не присвоить нанесение которое мы распределяем 
			// тому варианту расчета из которого оно было вызвано 
			// но если это было новое не сохраненное нанесение то у него отсутсвует $details_obj->calculationData->dop_uslugi_id 
			// для него по умолчанию присваиваем 0 для  $expel_dop_data_id
			$expel_dop_data_id = 0;
			if(isset($details_obj->calculationData->dop_uslugi_id)){
				$query="SELECT dop_data.id id FROM `".RT_DOP_DATA."` dop_data INNER JOIN
										`".RT_DOP_USLUGI."` uslugi 
										  ON   dop_data.id = uslugi.dop_row_id
										  WHERE uslugi.id = '".$details_obj->calculationData->dop_uslugi_id."'";
				
					//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					$row = $result->fetch_assoc();
					$expel_dop_data_id = $row['id'];
				}
			}
	 
			 
			foreach($details_obj->ids as $id){
			    $index = (isset($out['errors']))? count($out['errors']): 0;
				// выбираем данные о вариантах расчетов существующих для данных позиций
				 $query="SELECT dop_data.id dop_data_id ,dop_data.quantity quantity FROM `".RT_MAIN_ROWS."` main INNER JOIN
									`".RT_DOP_DATA."` dop_data
									  ON   main.`id` = dop_data.`row_id`
									  WHERE main.id = '".$id."' AND dop_data.id <> '".$expel_dop_data_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $print_details_obj = $details_obj->calculationData->print_details;
						// 
						if($row['quantity'] != 0){
							$YPriceParam = (isset($print_details_obj->dop_params->YPriceParam))? count($print_details_obj->dop_params->YPriceParam):1;
							// получаем новые исходящюю и входящюю цену исходя из нового таража
							$new_price_arr = self::change_quantity_and_calculators_price_query($row['quantity'],$print_details_obj,$YPriceParam);
						   // print_r($new_price_arr);echo "\r\n";//
						}
						
						
						// если все в порядке и нет ни каких исключений делаем дальнейшие операции
						if(!(self::$needIndividCalculation || self::$outOfLimit)){
							 

						    // рассчитываем окончательную стоимость с учетом коэффициентов и надбавок
							if($row['quantity'] != 0){
							    $new_data = self::make_calculations($row['quantity'],$new_price_arr,$print_details_obj->dop_params);
							}
							else  $new_data["new_price_arr"] =  array("price_in"=>0,"price_out"=>0);
							
							foreach($print_details_obj->dop_params->YPriceParam as $key => $data){
							   if(isset($data->cmyk)) $print_details_obj->dop_params->YPriceParam[$key]->cmyk =  base64_encode($data->cmyk);
							} 
							
							$print_details_obj->comment = (isset($print_details_obj->comment))? base64_encode($print_details_obj->comment):'';
							// вписываем новое нанесение для данного расчета в базу данных
							$query="INSERT INTO `".RT_DOP_USLUGI."` 
										  SET 
										  dop_row_id = '".$row['dop_data_id']."',
										  glob_type = 'print',
										  print_details = '".self::json_fix_cyr(json_encode($print_details_obj))."',
										  quantity = '".$row['quantity']."',
										  tz = '".$print_details_obj->comment."',
										  price_in = '".$new_data["new_price_arr"]["price_in"]."',
										  price_out = '".$new_data["new_price_arr"]["price_out"]."'";
							$mysqli->query($query)or die($mysqli->error);
							
						}
						
						if(self::$needIndividCalculation){
						      $out['errors'][$index]['errors'][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'needIndividCalculation' => 1);
							  $out['errors'][$index]['id'] = $id;
						}
						if(self::$outOfLimit){
						      $out['errors'][$index]['errors'][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'outOfLimit' => 1);
							  $out['errors'][$index]['id'] = $id;
						} 
						if(self::$lackOfQuantity){
							  $out['errors'][$index]['errors'][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'lackOfQuantity' => 1);
							  $out['errors'][$index]['id'] = $id;
						}
						/*if(self::$needIndividCalculation) $out['errors'][$id][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'needIndividCalculation' => 1);
						if(self::$outOfLimit) $out['errors'][$id][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'outOfLimit' => 1);
						if(self::$lackOfQuantity) $out['errors'][$id][$row['dop_data_id']] = array('quantity'=>$row['quantity'],'lackOfQuantity' => 1);*/
						 
                        self::$needIndividCalculation = false;
						self::$outOfLimit = false;
						self::$lackOfQuantity = false;
					}
				}
			}
			//print_r($out);
			echo (isset($out))? json_encode($out):'';
		}
		static function change_quantity_and_calculators($quantity,$dop_data_id,$print,$extra,$source){
		    global $mysqli;  
			
			// ЗАДАЧА:
			// произвести необходимые проверки по допустимости установки нового значения quantity, если изменения допустимы передать 
			// result - ok, и новые значения itog_sums для print или extra, если есть какие-то предупреждения передать их, если изменения
			// не допустимы передать result - error, и описания ошибок
			// в конце если зменения допустимы изменяем количество в строке расчета
			
            $out_put = array();
			$out_put['row_id'] = $dop_data_id;
			$out_put['print']['result']='ok';
			$out_put['extra']['result']='ok';
			if($source=='rt') $out_put['itog_values'] = array("price_in"=>0,"price_out"=>0,"summ_in"=>0,"summ_out"=>0);
			if($source=='card') $out_put['new_sums_details']	= array();
			
			if($print == 'true'){
				// делаем запрос чтобы получить данные о всех расчетах нанесений привязанных к данному ряду
				$query="SELECT uslugi.print_details print_details, uslugi.id uslugi_row_id, uslugi.discount discount FROM `".RT_DOP_USLUGI."` uslugi INNER JOIN
									`".RT_DOP_DATA."` dop_data
									  ON dop_data.`id` =  uslugi.`dop_row_id`
									  WHERE uslugi.glob_type ='print' AND dop_data.`id` = '".$dop_data_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					while($row = $result->fetch_assoc()){
						if($quantity != 0){
							// детали расчета нанесения
							$print_details_obj = json_decode($row['print_details']);
							//print_r($print_details_obj->dop_params);echo "\r\n";//
							$YPriceParam = (isset($print_details_obj->dop_params->YPriceParam))? count($print_details_obj->dop_params->YPriceParam):1;
							// получаем новые исходящюю и входящюю цену исходя из нового тиража
							$new_price_arr = self::change_quantity_and_calculators_price_query($quantity,$print_details_obj,$YPriceParam);
							//print_r($new_price_arr);echo "\r\n";//
						
							// сохраняем полученные данные в промежуточный массив
							$dataArr[]= array('new_price_arr' => $new_price_arr,'print_details_obj' => $print_details_obj,'uslugi_row_id' => $row['uslugi_row_id'],'discount' => $row['discount']);
						 }
						 else $dataArr[]= array('uslugi_row_id' => $row['uslugi_row_id']);
					}
					
					// если все в порядке и нет ни каких исключений делаем дальнейшие операции
					if(!(self::$needIndividCalculation || self::$outOfLimit)){
						 if($source=='card') $new_sums_details = array();
						 foreach($dataArr as $key => $dataVal){
						 // рассчитываем окончательную стоимость с учетом коэффициентов и надбавок
							if($quantity != 0) $new_data = self::make_calculations($quantity,$dataVal['new_price_arr'],$dataVal['print_details_obj']->dop_params);
							else{
								$new_data["new_price_arr"] = array("price_in"=>0,"price_out"=>0);
								$new_data["new_summs"] = array("summ_in"=>0,"summ_out"=>0); 
							}
							// print_r($new_data)."\r";
							// echo $dataVal['discount']."\r";//
							
							// перезаписываем новые значения прайсов и X индекса обратно в базу данных
							$query="UPDATE `".RT_DOP_USLUGI."` 
										  SET 
										  quantity = '".$quantity."',
										  price_in = '".$new_data["new_price_arr"]["price_in"]."',
										  price_out = '".$new_data["new_price_arr"]["price_out"]."'
										  WHERE id = '".$dataVal['uslugi_row_id']."'";
							$mysqli->query($query)or die($mysqli->error);
							
							$new_data["new_price_arr"]["price_out"] = ($dataVal['discount'] != 0 )? (($new_data["new_price_arr"]["price_out"]/100)*(100 + $dataVal['discount'])) : $new_data["new_price_arr"]["price_out"];
							$new_data["new_summs"]["summ_out"] = ($dataVal['discount'] != 0 )? (($new_data["new_summs"]["summ_out"]/100)*(100 + $dataVal['discount'])) : $new_data["new_summs"]["summ_out"];
							
							// print_r($new_data)."\r";
							if($source=='rt'){
							    $out_put['itog_values']["price_in"] += $new_data["new_price_arr"]["price_in"];
								$out_put['itog_values']["price_out"] += $new_data["new_price_arr"]["price_out"];
								$out_put['itog_values']["summ_in"] += $new_data["new_summs"]["summ_in"];
								$out_put['itog_values']["summ_out"] += $new_data["new_summs"]["summ_out"];
							}
							if($source=='card'){
							   $new_sums_details[$dataVal['uslugi_row_id']] = array('price_in' => $new_data["new_price_arr"]["price_in"],'price_out' => $new_data["new_price_arr"]["price_out"]);
							}
						}
					}
					
					// если дошли до этого места значит все нормально
					// отправляем новые данные обратно клиенту
					// print_r($itog_sums);
					//$result =(self::$needIndividCalculation || self::$outOfLimit)?'error':'ok';
	                if(self::$needIndividCalculation || self::$outOfLimit) $out_put['print']['result']='error';
					
					if(self::$lackOfQuantity)	$out_put['print']['lackOfQuantity'] = self::$lackOfQuantityDetails;
					if(self::$outOfLimit)  $out_put['print']['outOfLimit'] = self::$outOfLimitDetails;
					if(self::$needIndividCalculation)  $out_put['print']['needIndividCalculation'] = self::$needIndividCalculationDetails;
					
					if($out_put['print']['result']=='ok'){
						 if($source=='card') $out_put['new_sums_details'] = $new_sums_details;
					}
				}
			}
			if($extra == 'true' && $out_put['print']['result']=='ok'){// $out_put['print']['result']=='error' то работать с extra нет смысла
			    // считаем новые itog_sums
			    $query="SELECT*FROM `".RT_DOP_USLUGI."` WHERE glob_type ='extra' AND dop_row_id = '".$dop_data_id."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					while($row = $result->fetch_assoc()){
					     $row['price_out'] = ($row['discount'] != 0 )? (($row['price_out']/100)*(100 + $row['discount'])) : $row['price_out'];
					     if($source=='rt'){

							 $out_put['itog_values']["price_in"] += ($row['for_how']=='for_all')? $row['price_in']/$quantity:$row['price_in'];
							 $out_put['itog_values']["price_out"] += ($row['for_how']=='for_all')? $row['price_out']/$quantity:$row['price_out'];
							 $out_put['itog_values']["summ_in"] += ($row['for_how']=='for_all')? $row['price_in']:$quantity*$row['price_in'];
							 $out_put['itog_values']["summ_out"] += ($row['for_how']=='for_all')? $row['price_out']:$quantity*$row['price_out'];
						 }
						 if($source=='card'){
						     $out_put['new_sums_details'][$row['id']] = array('for_how' => $row['for_how'],'price_in' => $row['price_in'],'price_out' => $row['price_out']);
						 }
						
					}
				}
				
			    $query="UPDATE `".RT_DOP_USLUGI."` SET  quantity = '".$quantity."' WHERE dop_row_id = '".$dop_data_id."'";
				$mysqli->query($query)or die($mysqli->error);

			}
			// если по нужным услугам обновление с флагом result == ok, обновляем количество в RT_DOP_DATA
			if($out_put['print']['result']=='ok' && $out_put['extra']['result']=='ok'){
				// обновляем количество 
				if($source=='rt'){
					$query="SELECT tirage_json FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_data_id."'";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows>0){
						$row = $result->fetch_assoc();
						$tirage_json = @json_decode($row['tirage_json'],true);
						if(is_array($tirage_json) && count($tirage_json)>0){
							$tirage_json[key($tirage_json)]['tir'] = $quantity;
			
							// оставляем только один элемент
							$tirage_json = json_encode(array( key($tirage_json) => $tirage_json[key($tirage_json)] ));
						}		
						else $tirage_json = '{}';
		
					}
					else $tirage_json = '{}';
			    }
				
				$query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '".$quantity."'";
				if($source=='rt') $query.=" , `tirage_json` = '".$tirage_json."'";
				$query.=" WHERE `id` = '".$dop_data_id."'";
				$result = $mysqli->query($query)or die($mysqli->error);
			}
			//print_r($out_put);
			return json_encode($out_put);
		}
		static function change_quantity_and_calculators2($quantity,$dop_data_id,$print,$extra){
		    global $mysqli;  
			
			
			$itog_sums = array("summ_in"=>0,"summ_out"=>0);
 
			if($print == 'true'){
				// делаем запрос чтобы получить данные о всех расчетах нанесений привязанных к данному ряду
				$query="SELECT uslugi.print_details print_details, uslugi.id uslugi_row_id FROM `".RT_DOP_USLUGI."` uslugi INNER JOIN
									`".RT_DOP_DATA."` dop_data
									  ON dop_data.`id` =  uslugi.`dop_row_id`
									  WHERE uslugi.glob_type ='print' AND dop_data.`id` = '".$dop_data_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					while($row = $result->fetch_assoc()){
						if($quantity != 0){
							// детали расчета нанесения
							$print_details_obj = json_decode($row['print_details']);
							//print_r($print_details_obj->dop_params);echo "\r\n";//
							$YPriceParam = (isset($print_details_obj->dop_params->YPriceParam))? count($print_details_obj->dop_params->YPriceParam):1;
							// получаем новые исходящюю и входящюю цену исходя из нового таража
							$new_price_arr = self::change_quantity_and_calculators_price_query($quantity,$print_details_obj,$YPriceParam);
							//print_r($new_price_arr);echo "\r\n";//
						
							// сохраняем полученные данные в промежуточный массив
							$dataArr[]= array('new_price_arr' => $new_price_arr,'print_details_obj' => $print_details_obj,'uslugi_row_id' => $row['uslugi_row_id']);
						 }
						 else $dataArr[]= array('uslugi_row_id' => $row['uslugi_row_id']);
					}
					
					// если все в порядке и нет ни каких исключений делаем дальнейшие операции
					if(!(self::$needIndividCalculation || self::$outOfLimit)){
						 
						 foreach($dataArr as $key => $dataVal){
						 // рассчитываем окончательную стоимость с учетом коэффициентов и надбавок
							if($quantity != 0) $new_data = self::make_calculations($quantity,$dataVal['new_price_arr'],$dataVal['print_details_obj']->dop_params);
							else{
								$new_data["new_price_arr"] = array("price_in"=>0,"price_out"=>0);
								$new_data["new_summs"] = array("summ_in"=>0,"summ_out"=>0); 
							}
							
							// перезаписываем новые значения прайсов и X индекса обратно в базу данных
							$query="UPDATE `".RT_DOP_USLUGI."` 
										  SET 
										  quantity = '".$quantity."',
										  price_in = '".$new_data["new_price_arr"]["price_in"]."',
										  price_out = '".$new_data["new_price_arr"]["price_out"]."'
										  WHERE id = '".$dataVal['uslugi_row_id']."'";
							$mysqli->query($query)or die($mysqli->error);
							
							$itog_sums["summ_in"] += $new_data["new_summs"]["summ_in"];
							$itog_sums["summ_out"] += $new_data["new_summs"]["summ_out"];
							//print_r($new_summs);
							//echo "\r\n";
							// обновляем количество 
							$query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '".$quantity."'  WHERE `id` = '".$dop_data_id."'";
							$result = $mysqli->query($query)or die($mysqli->error);
						}
					}
					
					// если дошли до этого места значит все нормально
					// отправляем новые данные обратно клиенту
					// print_r($itog_sums);
					$result =(self::$needIndividCalculation || self::$outOfLimit)?'error':'ok';
	
					
					$json_str =  '{"result":"'.$result.'","row_id":'.$dop_data_id;
					if($result=='ok')	$json_str .= ',"new_sums":'.json_encode($itog_sums);
					if(self::$lackOfQuantity)	$json_str .= ',"lackOfQuantity":'.json_encode(self::$lackOfQuantityDetails);
					if(self::$outOfLimit)  $json_str .= ',"outOfLimit":'.json_encode(self::$outOfLimitDetails);
					if(self::$needIndividCalculation)  $json_str .= ',"needIndividCalculation":'.json_encode(self::$needIndividCalculationDetails);
					$json_str .=  '}';
					// используется в том числе при перерасчете нанесения при загрузке старницы в РТ
					return $json_str; 
				}
			}
		}
		static function change_quantity_and_calculators_price_query($quantity,$print_details_obj,$YPriceParam){
		    global $mysqli;  
			
			$level = (isset($print_details_obj->level))? $print_details_obj->level:'full';
			
			$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_details_obj->print_id."' AND `level` = '".$level."'  ORDER by id, param_val";
			
				//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    $priceIn_tblXindex = 0;
				while($row = $result->fetch_assoc()){
					//print_r($row);
					if($row['param_val']==0){
						
						// здесь мы определяем в какой диапазон входит новое количество
						for($i=1;isset($row[$i]);$i++){
							// если оно меньше минимального тиража
							
							//if($row[$i] > $quantity) break;
							if($row['price_type']=='in'){
								if($quantity < $row[1]){
									$newIn_Xindex = 1;
									$lackOfQuantInPrice = true;
									$minQuantInPrice  = $row[1];
								}
								else if($row[$i] >0 && $quantity >= $row[$i]){
									$newIn_Xindex = $i;
								}
								if($row[$i] >0){
									$in_limit = $row[$i];
									$in_limitIndex = $i;
								}
							}
							if($row['price_type']=='out'){
								if($quantity < $row[1]){
									$newOut_Xindex = 1;
									$lackOfQuantOutPrice = true;
									$minQuantOutPrice  = $row[1];
								}
								else if($quantity >= $row[$i]  &&  $row[$i] >0){
									$newOut_Xindex = $i;
								}
								if($row[$i] >0){
									$out_limit = $row[$i];
									$out_limitIndex = $i;
								}
							}
						}
					}
					// определяем новые входящие и исходящие цены
					if($row['price_type']=='in' && $row['param_val']==$YPriceParam) $new_priceIn = $row[$newIn_Xindex];
					if($row['price_type']=='out' && $row['param_val']==$YPriceParam) $new_priceOut = $row[$newOut_Xindex];   
				}
				$out = array("price_in"=> $new_priceIn,"price_out"=> $new_priceOut);
				
				// если тираж был меньше минимального значения в прайсе пересчитываем цены
				if(isset($lackOfQuantIntPrice) && $lackOfQuantInPrice==true){
					$out['price_in'] = $new_priceIn*$minQuantInPrice/$quantity;
					self::$lackOfQuantity = true;
					self::$lackOfQuantityDetails[] = array('minQuantity'=>(int)$minQuantInPrice,'print_type'=>$print_details_obj->print_type);
				}
				if(isset($lackOfQuantOutPrice) && $lackOfQuantOutPrice==true){
					$out['price_out'] = $new_priceOut*$minQuantOutPrice/$quantity;
					self::$lackOfQuantity = true;
					self::$lackOfQuantityDetails[] = array('minQuantity'=>(int)$minQuantOutPrice,'print_type'=>$print_details_obj->print_type);
					
				}
				
				//echo $newIn_Xindex .' - '. $in_limitIndex;  echo "\r" ;
				//echo $newOut_Xindex .' - '. $out_limitIndex;
				
				// если полученная цена оказалась равна 0 то значит стоимость не указана
				if((float)($out['price_in']) == 0 ||(float)($out['price_out']) == 0){

					// если это последние ряды прайс значит это лимит
					if($newIn_Xindex == $in_limitIndex){
						self::$outOfLimit = true;
						self::$outOfLimitDetails[] = array('limitValue'=>(int)$in_limit,'print_type'=>$print_details_obj->print_type);
					}
					elseif($newOut_Xindex == $out_limitIndex){
						self::$outOfLimit = true;
						self::$outOfLimitDetails[] = array('limitValue'=>(int)$out_limit,'print_type'=>$print_details_obj->print_type);
					}
					else{//иначе это индивидуальный расчет cancelCalculator
						if(!self::$outOfLimit){
						    self::$needIndividCalculation = true;
						    self::$needIndividCalculationDetails[] = array('print_type'=>$print_details_obj->print_type);
						}

					}
				}
				// echo "\r \$YPriceParam - ".$YPriceParam."\r In".$newIn_Xindex.' '.$new_priceIn; echo "\r Out".$newOut_Xindex.' '.$new_priceOut."\r";
				return $out;
			}
		}
		static function make_calculations($quantity,$new_price_arr,$print_dop_params){
			
			$square_coeff = 1;
			$price_coeff = $summ_coeff = $Y_coeff = 0;
			$price_addition = $summ_addition = 0;
			$new_summs = array();
			//print_r($print_dop_params);
			
		    // КОЭФФИЦИЕНТЫ НА ПРАЙС
			// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
			// НАДБАВКИ НА ПРАЙС
			// НАДБАВКИ НА ИТОГОВУЮ СУММУ
			foreach($print_dop_params as $glob_type => $set){
				
				if($glob_type=='YPriceParam'){
					foreach($set as $data){
					    // подстраховка
						if($data->coeff == 0) $data->coeff = 1;
						
						//echo "coeff ".$data->coeff."\r\n";
						$Y_coeff += (float)$data->coeff-1;
					}
				}
				if($glob_type=='sizes'){
					/*foreach($set as $data){
					    // подстраховка
						if($data->coeff == 0) $data->coeff = 1;
						
						//echo "coeff ".$data->coeff."\r\n";
						$price_coeff *= (float)$data->coeff;
					}*/
					foreach($set as $data){ 
					    if(isset($data->type)){
							if($data->type == 'coeff'){
								// подстраховка
								if($data->val == 0) $data->val = 1;
								if($data->target == 'price') $square_coeff =  (float)$data->val;// будет расчитан отдельно от остальных коэф-ов прайса
								if($data->target == 'summ') $summ_coeff += (float)$data->val-1;// будет расчитан также как остальные коэф-ты суммы
							}
							if($data->type == 'addition'){
								
								if($data->target == 'price') $price_addition +=(float)$data->val;
								if($data->target == 'summ') $summ_addition += (float)$data->val;
							}
						}
					}
				}
				if($glob_type=='coeffs'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
						    $count = count($details);
							for($i = 0;$i < $count;$i++){ 
							    // подстраховка
							    if((isset($details[$i]->multi)) && $details[$i]->multi == 0) $details[$i]->multi = 1;
								if($details[$i]->value == 0) $details[$i]->value = 1;
								
								if($target=='price'){
								     // echo 'coeffs price';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
								     $price_coeff += (isset($details[$i]->multi))?  ($details[$i]->value-1)*$details[$i]->multi : $details[$i]->value-1;
								}
								if($target=='summ'){
									 // echo 'coeffs summ';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
									 $summ_coeff += (isset($details[$i]->multi))?  ($details[$i]->value-1)*$details[$i]->multi : $details[$i]->value-1;
								}
							}								
						}
					}
				}
				if($glob_type=='additions'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
						    $count = count($details);
							for($i = 0;$i < $count;$i++){ 
							    // подстраховка
							    if((isset($details[$i]->multi)) && $details[$i]->multi == 0) $details[$i]->multi = 1;
								
								if($target=='price'){
								    // echo 'additions price';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
								     $price_addition += (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
								if($target=='summ'){
								     // echo 'additions summ';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
									 $summ_addition += (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
							}								
						}
					}
				}
			} 
			
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			//   
			//  CXEMA  - total_price = (((БЦ1*ПЛкф)+БЦ1*ЦВкф+БЦ2*ОПкф+НБпр)*КОЛ-ВО)+НБсумм+СУММА1*ОПкф(сумм)
			// 
			//  Коэффициэнт учитывается как Коэфф-1 т.е коэфф 1.2 = (1.2-1) = 0.2
			//  ПЛкф - коэффициент площади из поля площадь в калькуляторе
			//  ЦВкф - коэффициэнт цвета из поля выбора цвета в калькуляторе
			//  ОПкф - коэффициэнт опции из поля “дополнительно” в калькуляторе
			//  НБ - надбавка из поля “дополнительно” в калькуляторе
			//  пр или сумм - область действия надбавки или коэффициента- прайс или сумма
			
			//  ИТОГОВАЯ CXEMA  -
			//        var base_price1 = price;
			//        var base_price2 = price*square_coeff;
			//        var summ1 = (base_price2 + base_price1*Y_coeff + base_price2*price_coeff + price_addition)*quantity;
			//        var total_price = summ1 + sum_additions + summ1*summ_coeff
			
			
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
            $price_tblYindex=(isset($print_dop_params->YPriceParam))? ((count($print_dop_params->YPriceParam)==0)?1:count($print_dop_params->YPriceParam)):1;

	
			$base_price_for_Y = $new_price_arr['price_in']/$price_tblYindex;
			$base_price2 = $new_price_arr['price_in']*$square_coeff;
			$summ1 = ($base_price2 + $base_price_for_Y*$Y_coeff + $base_price2*$price_coeff + $price_addition)*$quantity;
			$new_summs["summ_in"] = $summ1 + $summ_addition + $summ1*$summ_coeff;
			//echo (' price_in '.$new_price_arr['price_in'].' priceIn_tblYindex '.$price_tblYindex.' base_price_for_Y '.$base_price_for_Y.' Y_coeff '.$Y_coeff.' base_price_for_Y*Y_coeff '.$base_price_for_Y*$Y_coeff );
			
			$base_price_for_Y = $new_price_arr['price_out']/$price_tblYindex;;
			$base_price2 = $new_price_arr['price_out']*$square_coeff;
			$summ1 = ($base_price2 + $base_price_for_Y*$Y_coeff + $base_price2*$price_coeff + $price_addition)*$quantity;
			$new_summs["summ_out"] = $summ1 + $summ_addition + $summ1*$summ_coeff;
			// echo (' price_out '.$new_price_arr['price_out'].' priceIn_tblYindex '.$price_tblYindex.' base_price_for_Y '.$base_price_for_Y.' Y_coeff '.$Y_coeff.' base_price2 '.$base_price2.' summ1 '.$summ1.' square_coeff '.$square_coeff);
            // echo "\r\n\r\n";
			
			//$new_summs["summ_in"] = round((((($new_price_arr['price_in']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
			//$new_summs["summ_out"] = round((((($new_price_arr['price_out']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
		
			
			//echo "all_coeffs ".$all_coeffs."\r\n";
			$new_price_arr["price_in"] =  round($new_summs["summ_in"]/$quantity,2);
			$new_price_arr["price_out"] = round($new_summs["summ_out"]/$quantity,2);
			$new_summs["summ_in"] = round($new_price_arr["price_in"]*$quantity,2);
			$new_summs["summ_out"] = round($new_price_arr["price_out"]*$quantity,2);
			
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_in"]." \r";
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_out"]."\r\n";
			
			return array("new_summs"=>$new_summs,"new_price_arr"=>$new_price_arr);
		}
		
	/*	static function make_calculations_for_kp($quantity,$price_arr,$print_dop_params){
			
			$price_coeff = $summ_coeff = 1;
			$price_addition = $summ_addition = 0;
			$new_summs = array();

			// echo  '<pre>'; print_r($print_dop_params); echo '</pre>';
			
		    // КОЭФФИЦИЕНТЫ НА ПРАЙС
			// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
			// НАДБАВКИ НА ПРАЙС
			// НАДБАВКИ НА ИТОГОВУЮ СУММУ
			foreach($print_dop_params as $glob_type => $set){
				
				if($glob_type=='YPriceParam'){
					foreach($set as $data){
					    // подстраховка
						if($data['coeff'] == 0) $data['coeff'] = 1;
						
						echo "coeff ".$data['coeff']."<br>\r\n";
						$price_coeff *= (float)$data['coeff'];
					}
				}
				if($glob_type=='sizes'){
					/*foreach($set as $data){
					    // подстраховка
						if($data['coeff'] == 0) $data['coeff'] = 1;
						
						//echo "coeff ".$data['coeff']."<br>\r\n";
						$price_coeff *= (float)$data['coeff'];
					}* /
					foreach($set as $data){ 
					    if(isset($data['type'])){
							if($data['type'] == 'coeff'){
								// подстраховка
								if($data['val'] == 0) $data['val'] = 1;
								if($data['target'] == 'price') $price_coeff *=  (float)$data['val'];
								if($data['target'] == 'summ') $summ_coeff *=  (float)$data['val'];
							}
							if($data['type'] == 'addition'){
								
								if($data['target'] == 'price') $price_addition +=(float)$data['val'];
								if($data['target'] == 'summ') $summ_addition += (float)$data['val'];
							}
						}
					}
				}
				if($glob_type=='coeffs'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
						    $count = count($details);
							for($i = 0;$i < $count;$i++){ 
							    // подстраховка
							    if((isset($details[$i]['multi'])) && $details[$i]['multi'] == 0) $details[$i]['multi'] = 1;
								if($details[$i]['value'] == 0) $details[$i]['value'] = 1;
								
								if($target=='price'){
								      echo 'coeffs price';echo "<br>\r\n"; echo $details[$i]['value'];echo "<br>\r\n";print_r($details[$i]);
								     $price_coeff *= (isset($details[$i]['multi']))?  $details[$i]['value']*$details[$i]['multi'] : $details[$i]['value'];
								}
								if($target=='summ'){
									  echo 'coeffs summ';echo "<br>\r\n"; echo $details[$i]['value'];echo "<br>\r\n";print_r($details[$i]);
									 $summ_coeff *= (isset($details[$i]['multi']))?  $details[$i]['value']*$details[$i]['multi'] : $details[$i]['value'];
								}
							}								
						}
					}
				}
				if($glob_type=='additions'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
						    $count = count($details);
							for($i = 0;$i < $count;$i++){ 
							    // подстраховка
							    if((isset($details[$i]['multi'])) && $details[$i]['multi'] == 0) $details[$i]['multi'] = 1;
								
								if($target=='price'){
								     echo 'additions price';echo "<br>\r\n"; echo $details[$i]['value'];echo "<br>\r\n";print_r($details[$i]);
								     $price_addition += (isset($details[$i]['multi']))?  $details[$i]['value']*$details[$i]['multi'] : $details[$i]['value'];
								}
								if($target=='summ'){
								      echo 'additions summ';echo "<br>\r\n"; echo $details[$i]['value'];echo "<br>\r\n";print_r($details[$i]);
									 $summ_addition += (isset($details[$i]['multi']))?  $details[$i]['value']*$details[$i]['multi'] : $details[$i]['value'];
								}
							}								
						}
					}
				}
			} 
			
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!  
			// CXEMA  - new_summ = ((((price*price_coeff)+price_addition)*quantity)*sum_coeff)+sum_addition
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
			
			
			$new_summs["summ_in"] = round((((($price_arr['price_in']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
			$new_summs["summ_out"] = round((((($price_arr['price_out']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
		
			
			//echo "all_coeffs ".$all_coeffs."<br>\r\n";
			$price_arr["price_in"] =  round($new_summs["summ_in"]/$quantity,2);
			$price_arr["price_out"] = round($new_summs["summ_out"]/$quantity,2);
			$new_summs["summ_in"] = round($price_arr["price_in"]*$quantity,2);
			$new_summs["summ_out"] = round($price_arr["price_out"]*$quantity,2);
			
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_in"]." \r";
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_out"]."<br>\r\n";
			
			return array("new_summs"=>$new_summs,"price_arr"=>$price_arr);
		}*/
		static function grab_dop_info(){
			global $mysqli;
	
			//$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
			//$result = $mysqli->query($query) or die($mysqli->error);
			return 1;
		}
	}
		
   
	
	
?>