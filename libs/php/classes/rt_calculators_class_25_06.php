<?php
    class rtCalculators{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function grab_data($data){
		    // начальный общий метод для всех услуг (получение информации по калькуляторам)
			
			// ветвим
			// если расчитываем нанесение
            if($data->type=='print') self::grab_data_for_print($data);
	
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
		static function grab_data_for_print($data){
		    // global $mysqli;   
			// print_r($data);
			
			
			// в результате действия метода будет получен массив всех данных для артикула необходимых для совершения
			// расчетов в калькуляторе
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
           
			
			// ищем типы нанесения присвоенные данному артикулу на прямую 
			// возврашаемое значение: массив содержащий один элемент обозначающий (имитирующий)
			// стандартное (дефолтное) место нанесения с вложенными в него типами нанесения 
			$out_put = self::get_related_art_and_print_types($data->art_id);
			
			// получаем (если установленны) данные о конкретных местах нанесения для данного артикула
			// если были найдены места добавляем их в масив $out_put
			// и заполняем их данным о присвоенных местам типам нанесиния 
            $out_put = self::get_related_print_places($out_put,$data->art_id);

		    // получаем дополнительные данные соответсвующие нанесениям ( возможные размеры, цвета, таблицы прайсов )
            $out_put = self::get_print_types_related_data($out_put);
			
			//print_r($out_put);
			echo json_encode($out_put);
			//return print_r($out_put,true);
			return $out_put;
		
		}
		static function get_related_art_and_print_types($art_id){
		    global $mysqli;  
			$places = array();
			$print_types = array();
			//UPDATE `new__base__print_mode` SET `print_id`=13 WHERE `print` = 'шелкография'
			// получаем данные о типах нанесений соответсвующих данному артикулу на прямую
			$query="SELECT*FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    $places[0]['name'] = 'Стандартно';	
			    while($row = $result->fetch_assoc()){
					$places[0]['print'][$row['print_id']] = $row['print'];	
					$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00","size"=> "Стандартно"); 
					//$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00","print_id"=> '"'.$print_id.'"',"size"=> "Стандартно");   
					//$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00", "print_id"=>$print_id,"size"=> "Стандартно");   
				}/**/
				
			}
			else $places[0] = false;	
			
			return array('places'=>$places,'print_types'=>$print_types);
		}
		static function get_related_print_places($out_put,$art_id){
		    global $mysqli;  
			 
			// получаем данные о местах нанесений соответсвующих данному артикулу
			$query="SELECT tbl1.`place_id` place_id, tbl2.`name` name  FROM `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl1 
			        INNER JOIN  `".BASE__PRINT_PLACES_TYPES_TBL."` tbl2 
					ON tbl1.`place_id`  = tbl2.`id`
                    WHERE tbl1.`art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				    // получаем данные о типах нанесений соответсвующих данному месту
					$query_2="SELECT  tbl1.print_id print_id,tbl2.name name FROM `".BASE__PRINT_TYPES_SIZES_PLACES_REL_TBL."` tbl1
					          INNER JOIN  `".OUR_USLUGI_LIST."` tbl2 
					          ON tbl1.`print_id`  = tbl2.`id` 
							  WHERE tbl1.`place_id` = '".$row['place_id']."'";
					
					$result_2 = $mysqli->query($query_2)or die($mysqli->error);/**/
					if($result_2->num_rows>0){
					    $print_types = array();
						while($row_2 = $result_2->fetch_assoc()){
						    if(!isset($out_put['print_types'][$row_2['print_id']])) $out_put['print_types'][$row_2['print_id']] = array();	
							
						    $print_types[$row_2['print_id']] = $row_2['name'];
						}
						// добавляем результат в итоговый массив ключем устанавливаем id места нанесения
						$out_put['places'][$row['place_id']]['name'] = $row['name'];
						$out_put['places'][$row['place_id']]['print'] = $print_types;		
					}			
				}
			}
			
			return $out_put;
		}
		static function get_print_types_related_data($out_put){
		    global $mysqli;  
			
			
			 
			//$print_types = $out_put['print_types'];
			
			// 
			foreach($out_put['print_types'] as $print_id => $val){
			
			    // выбираем дополнительные параметры  определяющие вертикальную позицию в прайсе (такие как например цвета)
				$query="SELECT*FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE `print_type_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    
				    while($row = $result->fetch_assoc()){
					    $out_put['print_types'][$print_id][$row['param_type']][$row['value']] = array('percentage'=>$row['percentage'],'item_id'=>$row['id']);   
				    }
				}
				
				
				// выбираем таблицу с расценками 
				$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."' ORDER by id, param_val";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $count = $row['count'];
					    unset($row['id'],$row['print_type_id'],$row['count']);
						
						if(!isset($end[$row['price_type']]))$end[$row['price_type']] = false;
						
						if($row['param_val']==0){
						      $end_counter = 0;
							  foreach($row as $key => $val){
								  if($key!='param_val' && $key!='param_type'){
									  //echo $val;
									  if($val==0 && !$end[$row['price_type']]) $end[$row['price_type']] = $end_counter;
								  } 
								  $end_counter++;
							  }
						}
						
						if($end[$row['price_type']]){
							 $counter = 0;
							 foreach($row as $key => $val){
								  if($counter>=$end[$row['price_type']]){
									  unset($row[$key]);
								  } 
								  $counter++;
							  }
						  }	
						
					    if($row['price_type']=='out') $out_put['print_types'][$print_id]['priceOut_tbl'][$count][] = $row; 
						else if($row['price_type']=='in')  $out_put['print_types'][$print_id]['priceIn_tbl'][$count][] = $row;  
				    }
				}
				
				
				// выбираем данные по размерам нанесения в соответствии с типом и местом нанесения
				$query="SELECT*FROM `".BASE__PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE `print_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $place_id = $row['place_id'];
						$row['item_id'] = $row['id'];
					    unset($row['id'],$row['place_id']);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения и id места нанесения
					    $out_put['print_types'][$print_id]['sizes'][$place_id][] = $row;   
				    }
				}
				
				
				// выбираем данные по коэффициэнтам влияющим на цену товара
				$query="SELECT*FROM `".BASE__CALCULATORS_COEFFS."` WHERE `print_id` = '".$print_id."'";
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
				$query="SELECT*FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE `print_id` = '".$print_id."'";
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
		static function save_calculatoins_result($details_obj){
		    global $mysqli;  
			
			print_r($details_obj);
			 
           
            // если PHP 5.4 то достаточно этого
               /* $print_details = json_encode($details_obj->print_details,JSON_UNESCAPED_UNICODE);*/
			// но пришлось использовать это
			$print_details = self::json_fix_cyr(json_encode($details_obj->print_details)); 

			// если нет dop_uslugi_id или он равен ноль, добавляем новый расчет доп услуг для ряда 
			// иначе перезаписываем данные в строке где `id` = $details_obj->dop_uslugi_id
			if(!isset($details_obj->dop_uslugi_id) || $details_obj->dop_uslugi_id ==0){
			    $query="INSERT INTO `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `glob_type` ='print',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `print_details` ='".$print_details."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
				 //echo 1;
			}
			else if(isset($details_obj->dop_uslugi_id) && $details_obj->dop_uslugi_id !=0){
			   $query="UPDATE `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `glob_type` ='print',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `print_details` ='".$print_details."'
									    WHERE `id` ='".$details_obj->dop_uslugi_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
		}
		static function delete_prints_for_row($dop_row_id,$usluga_id,$all){
		    global $mysqli;  
			
			// если надо удалить все расчеты нанесения
			if($all && !$usluga_id){
			    $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									   `dop_row_id` ='".$dop_row_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
			else if($usluga_id && !$all){
			     $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									   `id` ='".$usluga_id."' AND `dop_row_id` ='".$dop_row_id."' "; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			}
		}
		static function change_quantity_and_calculators($quantity,$dop_data_id){
		    global $mysqli;  
			$itog_sums = array("summ_in"=>0,"summ_out"=>0);
			// если надо удалить все расчеты нанесения
		    $query="SELECT uslugi.print_details print_details, uslugi.id uslugi_row_id FROM `".RT_DOP_USLUGI."` uslugi INNER JOIN
			                    `".RT_DOP_DATA."` dop_data
								  ON dop_data.`id` =  uslugi.`dop_row_id`
			                      WHERE dop_data.`id` = '".$dop_data_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				while($row = $result->fetch_assoc()){
					$print_details_obj = json_decode($row['print_details']);
					//print_r($print_details_obj->dop_params);
					//echo "\r\n";
					$new_price_arr = self::change_quantity_and_calculators_price_query($quantity,$print_details_obj->print_id,$print_details_obj->priceIn_tblYindex,$print_details_obj->priceOut_tblXindex);
					//print_r($new_price_arr);
					//echo "\r\n";
					$new_data = self::make_calculations($quantity,$new_price_arr,$print_details_obj->dop_params);
					
					// перезаписываем новые значения прайсов и X индекса обратно в базу данных
					$query2="UPDATE `".RT_DOP_USLUGI."` 
					              SET 
								  quantity = '".$quantity."',
								  price_in = '".$new_data["new_price_arr"]["price_in"]."',
								  price_out = '".$new_data["new_price_arr"]["price_out"]."'
			                      WHERE id = '".$row['uslugi_row_id']."'";
					$mysqli->query($query2)or die($mysqli->error);
					
					$itog_sums["summ_in"] += $new_data["new_summs"]["summ_in"];
					$itog_sums["summ_out"] += $new_data["new_summs"]["summ_out"];
					//print_r($new_summs);
					//echo "\r\n";
				}
				
				// если дошли до этого места значит все нормально
				// отправляем новые данные обратно клиенту
				// print_r($itog_sums);
				echo '{"result":"ok","row_id":'.$dop_data_id.',"new_sums":'.json_encode($itog_sums).'}';
			}
		}
		static function change_quantity_and_calculators_price_query($quantity,$print_id,$priceIn_tblYindex,$priceOut_tblXindex){
		    global $mysqli;  
			
			$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."' ORDER by id, param_val";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				   $priceIn_tblXindex = 0;
				    while($row = $result->fetch_assoc()){
					    
						if($row['param_val']==0){
						    // здесь мы определяем в какой диапазон входит новое количество
							// !!!! и если оно за пределами указанных в прайсе тиражей надо вернуть отсюда предупреждение
							
							for($i=1;isset($row[$i]);$i++){
								if($row[$i] > $quantity) break;
								if($row['price_type']=='in') $newIn_Xindex = $i;
								if($row['price_type']=='out') $newOut_Xindex = $i;
							}
						}
						// определяем новые входящие и исходящие цены
						if($row['price_type']=='in' && $row['param_val']==$priceIn_tblYindex) $new_priceIn = $row[$newIn_Xindex];
						if($row['price_type']=='out' && $row['param_val']==$priceIn_tblYindex) $new_priceOut = $row[$newOut_Xindex];   
				    }
					// echo "\r\n In".$newIn_Xindex.' '.$new_priceIn; echo "\r\n Out".$newOut_Xindex.' '.$new_priceOut;
					return array("price_in"=> $new_priceIn,"price_out"=> $new_priceOut);
				}
		}
		static function make_calculations($quantity,$new_price_arr,$print_dop_params){
			
			$all_coeffs = 1;
			$new_summs = array();
			 
			foreach($print_dop_params as $set){
				foreach($set as $data){
				    //echo "coeff ".$data->coeff."\r\n";
					$all_coeffs *= (float)$data->coeff;
				}
			} 
			//echo "all_coeffs ".$all_coeffs."\r\n";
			$new_price_arr["price_in"] = (float)$new_price_arr["price_in"]*$all_coeffs;
			$new_price_arr["price_out"] = (float)$new_price_arr["price_out"]*$all_coeffs;
			
			$new_summs["summ_in"] = $quantity*$new_price_arr["price_in"];
			$new_summs["summ_out"] = $quantity*$new_price_arr["price_out"];
			
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_in"]." \r";
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_out"]."\r\n";
			
			return array("new_summs"=>$new_summs,"new_price_arr"=>$new_price_arr);
		}
		
    }

?>