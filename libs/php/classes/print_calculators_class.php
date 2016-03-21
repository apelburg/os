<?php

    class printCalculator{
	    function __consturct(){
		}
		static function get_sizes(){
		
		    global $mysqli;
			
			$out_put = array();
			
			$query = "SELECT id, size FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."`";
			// echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()) {
				   $out_put[$row['id']] = $row['size'];
				}
			}
			return $out_put;
		}
		static function get_uslugi(){
		
		    global $mysqli;
			
			$out_put = array();
			
			$query = "SELECT id, name FROM `".OUR_USLUGI_LIST."`";
			// echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()) {
				   $out_put[$row['id']] = $row['name'];
				}
			}
			return $out_put;
		}
		static function convert_print_details($print_details){
		
		    global $mysqli;
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			// echo '<pre>'; print_r($print_details); echo '</pre>';//
			$out_put = array();
			$out_put[] = $print_details->print_type;
			$out_put[] = 'место нанесения: '.$print_details->place_type;
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
					    $type = $row['param_type'];
					    $prefix = $type.': ';
						$result->data_seek(0);
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   //echo '<pre>'; print_r($row); echo '</pre>';
						   $tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put[] = $prefix.$row['value'].$tail;
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){

						$prefix = 'Размер нанесния: ';
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put[] = $prefix.$row['size'].$tail;
						   $prefix='';
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$prefix='Коэффициэнты: ';
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   $multi = ($row['multi']>1)? ' '.$row['multi'].' раза ':'';
						   $out_put[] = $prefix.$row['title'].' увелич. на '.$row['percentage'].'%'.$dop_details[$row['id']]['multi'];
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза по ':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$prefix='Надбавки:';
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $out_put[] = $prefix.$row['title'].' +'.$dop_details[$row['id']]['multi'].''.$row['value'].'руб.';
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
	
			//echo '<pre>'; print_r($out_put); echo '</pre>';//
			//echo implode(', ',$out_put);
			//exit; 
		    return implode(', ',$out_put);
		}
		
		static function convert_print_details_for_TotalCom($print_details){
		
		    global $mysqli;
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			// echo '<pre>'; print_r($print_details); echo '</pre>';//
			
			
			$out_put = array();
			//'место нанесения: '
			$out_put['a_place_print'] = (isset($print_details->place_type))?$print_details->place_type:'';
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $colors[] = $row['value'];
						}
						$out_put['colors'] = implode(', ',$colors);
					}
				}
				unset($idsArr);
			}
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){

						while($row = $result->fetch_assoc()) {
						   $out_put['format'] = $row['size'];
						}
					}
					unset($idsArr);
				
				}
			}
	
			//echo '<pre>'; print_r($out_put); echo '</pre>';//
			//echo implode(', ',$out_put);
			//exit; 
		    return $out_put;
		}
		
		static function convert_print_details_for_kp($print_details){
		
		    global $mysqli;
			
			// Функция принимает сырые данные о нанесении логотипа и возвращает в отфораматированном виде для КП
			// распеределяя данные на два блока - 1-ый для вывода в блоке "Печать логотипа" в КП, 2-ой для вывода
			// в блоке "Дополнительные услуги" в КП
			
			$out_put = array('block1'=>array(),'block2'=>array());
			
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			
			// echo '<pre>'; print_r($print_details); echo '</pre>';////

			$out_put['block1']['print_type'] = $print_details->print_type;
			$out_put['block1']['place_type'] = $print_details->place_type;
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
						// общее наименование параметра
					    $out_put['block1']['price_data']['cap'] = $row['param_type'];
						
						$result->data_seek(0);
						while($row = $result->fetch_assoc()){
						   // наменование конкретных вариантов
						   $out_put['block1']['price_data']['y_params'][] = $row['value'];
						   $out_put['block1']['price_data']['y_params_ids'][$row['id']] = $row['value'];
						}
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			
			
			
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $out_put['block1']['print_size']= $row['size'];
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}
			
			// Задача для Коэффициэнтов и Надбавок вытащить данные в следующем виде
			// ЕСЛИ коеффициент отображается в калькуляторе как стандартный выпадающий список - то нам просто нужно поле title,
			// в данном случае он является одним в группе
			// ЕСЛИ коэффициент отображается в калькуляторе как список с мультивыбором (в этом случае он входит в группу коэффициэнтов
			// объеденненную параметром type) то нам нужно поле title данного коэффициэнта и поле title первой записи из данной Группы
			// коэффициэнтов МЕТКА данной задачи getRightTitle
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    // метка - getRightTitle
					    // сохраняем поле type переданных коэффициэнтов, для последующего выбора данных из базы относяшихся к 
						// данным типам  (хотя можно было и все выибрать) и к определенному типу нанесения 
					    $typesArr[] = $type; 
					    foreach($val as $index => $details){
							if($details->id!=0){
							    // выбираем id коэффициэнтов
								$idsArr[] = $details->id;
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE type IN('".implode("','",$typesArr)."') AND  print_id='".$print_details->print_id."' ORDER BY id";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						  // метка - getRightTitle
						  // собираем title коэффициэнтов в группы объединяя по значению type
						  // сохраняем данные из базы в отдельный масив
						  // чтобы затем в цикле пройти по ним и выбрать нужные данные с одновременной обработкой 
						  // групп коэффициэнтов из массива $arrByTypes
						  $arrByTypes[$row['type']][] = $row['title'];
						  $fullDataArr[] = $row;
						}
						foreach($fullDataArr as $index => $value){
						    if(in_array($value['id'],$idsArr) && (int)$value['percentage']!=0){
							    // метка - getRightTitle
							    // если коэффициэнты образуют группу берем title первого элемента группы и title переданного
								// если в группе один элемент только title переданного
							    $name = (count($arrByTypes[$value['type']])>1)? $arrByTypes[$value['type']][0].': '.$value['title']:$value['title'];
							    $out_put['block2']['data'][]=array('name'=>$name,'type'=>'coeff','value'=>$value['percentage'],'target'=>$value['target']);
							}
						}
						//echo '<pre>'; print_r($arrByTypes); echo '</pre>';//
					}
					
					
					
					unset($typesArr);
					unset($idsArr); 
					unset($arrByTypes);
					unset($fullDataArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    // метка - getRightTitle
					    // сохраняем поле type переданных надбавок, для последующего выбора данных из базы относяшихся к 
						// данным типам  (хотя можно было и все выибрать) и к определенному типу нанесения 
					    $typesArr[] = $type; 
					    foreach($val as $index => $details){
				            if($details->id!=0){
							    // выбираем id надбавок
							    $idsArr[] = $details->id;
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE type IN('".implode("','",$typesArr)."') AND  print_id='".$print_details->print_id."' ORDER BY id";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						  // метка - getRightTitle
						  // собираем title надбавок в группы объединяя по значению type
						  // сохраняем данные из базы в отдельный масив
						  // чтобы затем в цикле пройти по ним и выбрать нужные данные с одновременной обработкой 
						  // групп надбавок из массива $arrByTypes
						  $arrByTypes[$row['type']][] = $row['title'];
						  $fullDataArr[] = $row;
						}
						foreach($fullDataArr as $index => $value){
						    if(in_array($value['id'],$idsArr) && (int)$value['value']!=0){
							    // метка - getRightTitle
							    // если надбавки образуют группу берем title первого элемента группы и title переданного
								// если в группе один элемент только title переданного
							    $name = (count($arrByTypes[$value['type']])>1)? $arrByTypes[$value['type']][0].': '.$value['title']:$value['title'];
							    $out_put['block2']['data'][]=array('name'=>$name,'type'=>'addition','value'=>$value['value'],'target'=>$value['target']);
							}
						}
						//echo '<pre>'; print_r($arrByTypes); echo '</pre>';//
					}
					
					
					
					unset($typesArr);
					unset($idsArr); 
					unset($arrByTypes);
					unset($fullDataArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
	
			//echo '<pre>'; print_r($out_put); echo '</pre>';//
			//exit; 
			return $out_put;
		}
		static function convert_print_details_to_dop_tech_info($print_details){
		
		    global $mysqli;
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			// echo '<pre>'; print_r($print_details); echo '</pre>';//
			$out_put = array();
			
			// если пустые значения не создаем элемент
			if(isset($print_details->place_type) && trim($print_details->place_type) !=''){
			    if(trim($print_details->place_type) !='Стандартно' && trim($print_details->place_type) !='стандартно')  $out_put['mesto_pechati'] = base64_encode($print_details->place_type);
		    }
			
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0){
					    $idsArr[] = $details->id;
						$CMYKArr[$details->id] = (isset($details->cmyk))?$details->cmyk:'';
					}	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
					    //$type = $row['param_type'];
						$result->data_seek(0);
						while($row = $result->fetch_assoc()) {
                            $arr[] = $row['value'].' '.base64_decode($CMYKArr[$row[id]]);
						}
                        // если пустые значения не создаем элемент
						if(isset($arr)){
						    $out_put['Pantone'] =  base64_encode(implode(', ',$arr));
						    $out_put['kolvo_cvetov'] =  base64_encode(count($arr));
						    unset($arr);
						} 
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $out_put['razmer_pechati'] = base64_encode($row['size']);
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' ('.$details->multi.' раз)':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $arr[] = $row['title'].' '.$dop_details[$row['id']]['multi'];
						}
						// если пустые значения не создаем элемент
						if(isset($arr)){
						    $out_put['dop_opcii'] =  base64_encode(implode(', ',$arr));
						    unset($arr);
						} 
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' ('.$details->multi.' раз)':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $arr[] = $row['title'].''.$dop_details[$row['id']]['multi'];
						   $prefix='';
						}
						// если пустые значения не создаем элемент
						if(isset($arr)){
						    if(isset($out_put['dop_opcii'])) $out_put['dop_opcii'] .=  ', '.base64_encode(implode(', ',$arr));
							else $out_put['dop_opcii'] = base64_encode(implode(', ',$arr));
						    unset($arr);
						} 
					    
						unset($arr);
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			// echo '<pre>'; print_r($out_put); echo '</pre>';//
			return json_encode($out_put);
		}
		static function convert_print_details_to_arr($print_details){
		
		    global $mysqli;     
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			//echo '<pre>'; print_r($print_details); echo '</pre>';
			$out_put = array();
			$out_put['print_type'] = $print_details->print_type;
			$out_put['place'] = $print_details->place_type;
			if(isset($print_details->lackOfQuantInPrice)){
			    $out_put['lackOfQuantityInPrice']['minVal'] = $print_details->minQuantInPrice;
			}
			if(isset($print_details->lackOfQuantOutPrice)){
			    $out_put['lackOfQuantityOutPrice']['minVal'] = $print_details->minQuantOutPrice;
			}
			
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
                        $counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['place_size']['params'][$counter]['name'] = $row['size'];
						   $out_put['place_size']['params'][$counter]['target'] = 'price';
						   $out_put['place_size']['params'][$counter]['target_ru'] = 'к цене';
						   $out_put['place_size']['params'][$counter]['value'] = $row['percentage'];
						   $counter++;
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}

			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
						$out_put['YPriceParam']['name'] = $row['param_type'];
						$result->data_seek(0);
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   //echo '<pre>'; print_r($row); echo '</pre>';
						   //$tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put['YPriceParam']['params'][$counter]['name'] = $row['value'];
						   $out_put['YPriceParam']['params'][$counter]['target'] = 'price';
						   $out_put['YPriceParam']['params'][$counter]['target_ru'] = 'к цене';
						   $out_put['YPriceParam']['params'][$counter]['value'] = $row['percentage'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){

						$out_put['coeffs']['name'] = 'Коэффициэнт';
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['coeffs']['params'][$counter]['name'] = $row['title'];
						   $out_put['coeffs']['params'][$counter]['target'] = $row['target'];
						   $out_put['coeffs']['params'][$counter]['target_ru'] = ($row['target']=='price')? 'к цене':'к сумме';
						   $out_put['coeffs']['params'][$counter]['value'] = $row['percentage'];
						   $out_put['coeffs']['params'][$counter]['multi'] = $dop_details[$row['id']]['multi'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза по ':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$out_put['additions']['name'] = 'Надбавки';
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['additions']['params'][$counter]['name'] = $row['title'];
						   $out_put['additions']['params'][$counter]['target'] = $row['target'];
						   $out_put['additions']['params'][$counter]['target_ru'] = ($row['target']=='price')? 'к цене':'к сумме';
						   $out_put['additions']['params'][$counter]['value'] = $row['value'];
						   $out_put['additions']['params'][$counter]['multi'] = $dop_details[$row['id']]['multi'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
		    return $out_put;
		}
		
    }

?>