<?php
    // удалить insert_copied_rows_old

    class Agreement{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function fetch_agreement_content($agreement_id){
			global $mysqli;
			$query = "SELECT*FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id='$agreement_id'";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows == 0){
				echo 'не удается получить содержимое договора';
				exit;
			} 
			else return $result->fetch_assoc();
		}
		static function add_new_agreement($client_id,$agreement_num,$type,$existent,$standart,$our_requisit_id,$client_requisit_id,$our_comp_full_name,$our_firm_acting_manegement_face,$client_comp_full_name,$client_firm_acting_manegement_face,$date,$expire_date,$short_description){
			global $mysqli;
			//echo print_r($our_firm_acting_manegement_face).'<br>';
			//echo print_r($client_firm_acting_manegement_face).'<br>';
			//exit;
	
			if($type == 'long_term')
			{
				$date_arr = explode('-',$date);
				$query = "SELECT id FROM `".GENERATED_AGREEMENTS_TBL."` 
						  WHERE client_id='$client_id' AND type='$type' AND 
						  our_requisit_id='$our_requisit_id' AND client_requisit_id='$client_requisit_id' AND LEFT(date,4) = '".$date_arr[0]."'";
						  
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows > 0)
				{   
					echo 'лимит на создание долгосрочных договоров: 1 договор в год,<br>
						  договор на '.$date_arr[0].' год  между компаниями:<br>'.fetch_client_requisites_nikename($client_requisit_id).' и '.fetch_our_requisites_nikename($our_requisit_id).' уже создан';///.mysql_result($result,0,'id');
					exit;
				}
			}
			
			if(!$agreement_num){
				$date_arr = explode('-',$date);
				$query = "SELECT MAX(agreement_num) agreement_num FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `standart` = '1' AND  `existent` = '0' AND  LEFT(date,4) = '".$date_arr[0]."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				$row = $result->fetch_assoc();
				$agreement_num = $row['agreement_num'];
			
				$agreement_num_arr = explode('/',$agreement_num);
				if($agreement_num_arr[0] == 0) $agreement_num_arr[0] = 100;
				$agreement_num = ((int)$agreement_num_arr[0]+1).'/'.$date_arr[1].substr($date_arr[0],2);
				
				//echo $agreement_num;
				//exit;
			}
			
			$query = "INSERT INTO `".GENERATED_AGREEMENTS_TBL."` SET
					  date = '$date', 
					  expire_date = '$expire_date',
					  client_id='$client_id',
					  type='$type',
					  standart='$standart',
					  existent='$existent',
					  agreement_num='$agreement_num',
					  our_comp_full_name='$our_comp_full_name',
					  our_chief='".$our_firm_acting_manegement_face['name']."',
					  our_chief_in_padeg='".$our_firm_acting_manegement_face['name_in_padeg']."',
					  our_chief_position_in_padeg='".$our_firm_acting_manegement_face['position_in_padeg']."',
					  our_chief_position='".$our_firm_acting_manegement_face['position']."',
					  our_basic_doc='".$our_firm_acting_manegement_face['basic_doc']."',
					  client_comp_full_name='$client_comp_full_name',
					  client_chief_position='".$client_firm_acting_manegement_face['position']."',
					  client_chief_position_in_padeg='".$client_firm_acting_manegement_face['position_in_padeg']."',
					  client_chief='".$client_firm_acting_manegement_face['name']."',
					  client_chief_in_padeg='".$client_firm_acting_manegement_face['name_in_padeg']."',
					  client_basic_doc='".$client_firm_acting_manegement_face['basic_doc']."',
					  our_requisit_id='$our_requisit_id',
					  client_requisit_id='$client_requisit_id',
					  short_description='$short_description'
					  ";
			$result = $mysqli->query($query)or die($mysqli->error);
			$last_agreement_id =  $mysqli->insert_id;
			
			
			
			
			// этап создания отдельного файла Договра и сохраниения его на диск
			// проверяем существует ли папка данного клиента если нет создаем её
			// если происходит ошибка выводим отчет
			
			// проверяем есть папка данного клента, если её нет то создаем её
			$client_dir_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval(intval($_GET['client_id']));
			//chmod("data/com_offers/", 0775);
			
			if(!file_exists($client_dir_name)){
				if(!mkdir($client_dir_name, 0775)){
					echo 'ошибка создания папки клиента (4)'.$client_dir_name;
					exit;
				}
			}
			
			
			// папка обозначающая год (название папки - название года)
			$year_dir_name = $client_dir_name.'/'.$date_arr[0];
			if(!file_exists($year_dir_name)){
				if(!mkdir($year_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$year_dir_name;
					exit;
				}
			}
			
			// папка для типа договора
			$type_dir_name = $year_dir_name.'/'.$type;
			if(!file_exists($type_dir_name)){
				if(!mkdir($type_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$type_dir_name;
					exit;
				}
			}
			
			
			// папка для выбранных сторон
			$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			if((boolean)$existent) return $last_agreement_id;
			
			// записываем файл
			$file_name = $full_dir_name.'/agreement.tpl';
			//if(file_exists($file_name)){
				//echo 'файл с таким именем уже существует (2)';
				//exit;
			//}
			

			$origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/long_term.tpl';	
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
		
			return $last_agreement_id;
	
		}
		static function create_oferta($dateArr,$rows_data,$client_id,$our_requisit_id,$client_requisit_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$short_description,$address,$prepayment){
		
		    global $mysqli;
			
			$year = date("Y");
			$date = date("Y-m-d");
			$time = date("H:i:s");

			
			$dates_data = self::date_terms_convert($dateArr);
		    // ЗАПИСЫВАЕМ ДАННЫЕ ОБ ОФЕРТЕ
			$query = "INSERT INTO `".OFFERTS_TBL."` SET 
						  num='НЕ НАЗНАЧЕН',
						  type='". $dateArr['date_type']."',
						  client_id='".$dateArr['client_id']."',
						  our_requisit_id='".$our_requisit_id."',
						  client_requisit_id='".$client_requisit_id."',		  
						  date_time = '".$date." ".$time."',
						  our_chief='".$our_firm_acting_manegement_face['name']."',
						  client_chief='".$client_firm_acting_manegement_face['name']."',
						  short_description='".$short_description."',
						  address='".$address."',
						  prepayment='".$prepayment."', 
						  makets_delivery_term='',
						  item_production_term='".$dates_data['item_production_term']."',
						  shipping_date_time='".$dates_data['shipping_date_time']."',
						  final_date_time='".$dates_data['final_date_time']."'
						  ";
			
			// echo $query;	
			// exit;	  
			$result = $mysqli->query($query)or die($mysqli->error);
			$oferta_id = $mysqli->insert_id;
			
			// ЗАПИСЫВАЕМ ТАБЛИЧНЫЕ ДАННЫЕ ОФЕРТЫ
			$rows_data_arr = json_decode($rows_data);
		    foreach($rows_data_arr as $key => $val){
			
				if(count($val)==0) continue;
				
			    $summ_out = 0;
                $uslugi_summ_out = 0;
				
				$main_id = $key;
				$dop_id = $val[0];
				 
				$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$main_id."'";
				// echo $query."\r\n";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    // 1). main_data
				    $main_data = $result->fetch_assoc();
					
					$query2="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id."'";
					// echo $query."\r\n";
					$result2 = $mysqli->query($query2)or die($mysqli->error);
					if($result2->num_rows>0){
					     // 2). dop_data
					     $dop_data = $result2->fetch_assoc();
						 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
						 if(@$dop_data['expel']!=''){
							 $obj = @json_decode($dop_data['expel']);
							 foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
						 }
					
						 $summ_out = $dop_data['quantity']*$dop_data['price_out'];
						 $name= (($main_data['art']!='')? 'арт.'.$main_data['art']:'')." ".$main_data['name'];					 
						   
						 // $price = ($dop_data['discount'] != 0 )? round((($summ_out/$dop_data['quantity'])/100)*(100 + $dop_data['discount']),2) :  round($summ_out/$dop_data['quantity'],2) ;
						 $price = ($dop_data['discount'] != 0 )? round(($dop_data['price_out']/100)*(100 + $dop_data['discount']),2) :  $dop_data['price_out'] ;
						 // прежде чем записать ряд в спецификацию сверим совпадает ли количество в расчете и в услугах
						 // для этого делаем дополнительный запрос к таблице RT_DOP_USLUGI, далее после добавления ряда 
						 // будет такойже запрос к таблице RT_DOP_USLUGI но уже чтобы добавить доп услуги в спецификацию
						 $query2_dop="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type";
						 // echo $query."\r\n";
						 $result2_dop = $mysqli->query($query2_dop)or die($mysqli->error);
						 if($result2_dop->num_rows>0){
						     while($uslugi_data = $result2_dop->fetch_assoc()){
							 
							     $print_details_arr = json_decode($uslugi_data['print_details'],TRUE);
							  		  
								 // если calculator_type =='free' то непроверяем, isset($print_details_arr["calculator_type"]) 
								 // для совместимости с предыдущими версиями в которых $print_details_arr["calculator_type"] нет
								 $revoke = (isset($print_details_arr["calculator_type"]) && $print_details_arr["calculator_type"] =='free')?true:false;
								 
							     if($uslugi_data['glob_type']=='print' && !$revoke && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									 $reload['flag'] = true;
									 //echo $dop_data['quantity'];
									 include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
									 $json_out =  rtCalculators::change_quantity_and_calculators($dop_data['quantity'],$dop_data['id'],'true','false');
									 $json_out_obj =  json_decode($json_out);
									 
									 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
									 // сбрасываем количество тиража и нанесения до 1шт.
									 if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
										 rtCalculators::change_quantity_and_calculators(1,$dop_data['id'],'true','false');
										 
										 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_data['id']."'";
										 $result = $mysqli->query($query)or die($mysqli->error);
									 }
									 
			
								 } /**/
								 if($uslugi_data['glob_type']=='extra' && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									  $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$dop_data['quantity']."'  WHERE `id` = '".$uslugi_data['id']."'";
									  $result = $mysqli->query($query)or die($mysqli->error);
									  $uslugi_data['quantity'] = $dop_data['quantity'];
									 
			
								 }
							 }
						 }
						 if(isset($reload['flag']) && $reload['flag'] == true){
							 header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
							 exit;
						 }
						 
						 if($main_data['type']!='cat'){
							 include_once(ROOT."/libs/php/classes/os_form_class.php");
							 include_once(ROOT."/libs/php/classes/cabinet/cabinet_class.php");//os_form_class.php
							 $cabinet = new Cabinet();
							 $details =  $cabinet->get_a_detailed_specifications($main_data['type'], $dop_data['no_cat_json']);
							 $details =  strip_tags($details,'<div><br><br/><br />');
							 $details =  str_replace(array('<div>','</div>'),array('<br>',''),$details);	
							 $details =  str_replace(array("\n","\r","\t"),'',$details);			
							 $details =  str_replace('<br><br>','<br>',$details);
							 $details =  preg_replace('/<div[^<]+>/','',$details);
							 $name .= $details;
							
						 }
						 
				         // записываем ряд
						 $specIdsArr[] =  self::insert_row_in_oferta($oferta_id,$name,$dop_data['quantity'],$dop_data['price_in'],$dop_data['price_out'],$dop_data['discount']);
						 
						 
						 $query3="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type DESC";
						 // echo $query."\r\n";
						 $result3 = $mysqli->query($query3)or die($mysqli->error);
						 if($result3->num_rows>0){
						     
						     while($uslugi_data = $result3->fetch_assoc()){
					            // 3). uslugi_data
								 if($uslugi_data['glob_type'] == 'print' && !(!!$expel["print"])){
									  //include_once(ROOT."/libs/php/classes/print_calculators_class.php");
								      //$name = printCalculator::convert_print_details($uslugi_data['print_details']);
									  
									  $print_details_arr = json_decode($uslugi_data['print_details'],TRUE);
									  
									  if(!isset($print_details_arr["calculator_type"]) || (isset($print_details_arr["calculator_type"]) && ($print_details_arr["calculator_type"] =='auto' || $print_details_arr["calculator_type"] =='manual'))){
									      include_once(ROOT."/libs/php/classes/print_calculators_class.php");
								          $name = printCalculator::convert_print_details($uslugi_data['print_details']);
									  }
									  if(isset($print_details_arr["calculator_type"]) && $print_details_arr["calculator_type"] =='free'){
									      $uslugi_data['quantity'] = $print_details_arr['quantity'];
										  $name = $print_details_arr['print_type'].' '.base64_decode($print_details_arr['commentForClient']);
									  }
									 // записываем ряд
									 $specIdsArr[] =  self::insert_row_in_oferta($oferta_id,$name,$uslugi_data['quantity'],$uslugi_data['price_in'],$uslugi_data['price_out'],$uslugi_data['discount']);
								 }
								 if($uslugi_data['glob_type'] == 'extra' && !(!!$expel["dop"])){
									 // если альтернативное название отсутствует (вводится при заведении услуги "НЕТ В СПИСКЕ")
									 if(trim($uslugi_data['other_name']) == ""){
									 	$extra_usluga_details = self::get_usluga_details($uslugi_data['uslugi_id']);
									 	$name = ($extra_usluga_details)? $extra_usluga_details['name']:'Неопределено'; 	
									 }else{
									 	$name = $uslugi_data['other_name'];
									 }
									 
									 
									 // меняем количество на 1(еденицу) если это надбавка на всю стоимость
									 $uslugi_data['quantity'] = ($uslugi_data['for_how']=='for_all')? 1: $uslugi_data['quantity'];
									 // записываем ряд
									 $specIdsArr[] =  self::insert_row_in_oferta($oferta_id,$name,$uslugi_data['quantity'],$uslugi_data['price_in'],$uslugi_data['price_out'],$uslugi_data['discount']);
								 }/**/
								 
								  
						       
						 
						    }
					    }
				    }
				}
			}	
		    
			
			// этап создания отдельного файла оферты и сохраниения его на диск
			// проверяем существует ли папка данного клиента если нет создаем её
			// если происходит ошибка выводим отчет
	
			
			// проверяем есть папка данного клента, если её нет то создаем её
			$client_dir_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval($client_id);
			//chmod("data/com_offers/", 0775);
			
			if(!file_exists($client_dir_name)){
				if(!mkdir($client_dir_name, 0775)){
					echo 'ошибка создания папки клиента (4)'.$client_dir_name;
					exit;
				}
			}
			
			// папка обозначающая год (название папки - название года)
			$year_dir_name = $client_dir_name.'/'.$year;
			if(!file_exists($year_dir_name)){
				if(!mkdir($year_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$year_dir_name;
					exit;
				}
			}
			
			// папка содержащая оферты
			$type_dir_name = $year_dir_name.'/offerts';
			if(!file_exists($type_dir_name)){
				if(!mkdir($type_dir_name, 0775)){
					echo 'ошибка создания папки "offerts" '.$type_dir_name;
					exit;
				}
			}
			
			// папка для выбранных сторон
			$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки для выбранных сторон'.$full_dir_name;
					exit;
				}
			}
			

			// записываем файл
			$file_name = $full_dir_name.'/'.$oferta_id.'.tpl';
			//if(file_exists($file_name)){
				//echo 'файл с таким именем уже существует (222)';
				//exit;
			//}
	
			if($dateArr['date_type']=='days') $origin_file_name = ROOT.'/modules/agreement/agreements_templates/oferta.tpl';
			if($dateArr['date_type']=='date') $origin_file_name = ROOT.'/modules/agreement/agreements_templates/oferta_type2_by_date.tpl';
			
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
			
			/*
			// создаем предзаказ 
			include_once(ROOT."/libs/php/classes/rt_class.php");
			
		    RT::make_order($rows_data,$client_id,$_GET['query_num'],0,$oferta_id,$dateDataObj->doc_type,$dateDataObj->data_type,$dates_data['shipping_date_time'],$dates_data['item_production_term'],$dates_data['final_date_time']);*/
			
			return $oferta_id;
		}
		
		static function fetch_oferta_data($oferta_id){
			global $mysqli;
			
			$oferta_id = (!is_array($oferta_id))? array($oferta_id):$oferta_id; 
			$query = "SELECT*FROM `".OFFERTS_ROWS_TBL."` WHERE oferta_id IN('".implode("','",$oferta_id)."')";
			$result = $mysqli->query($query)or die($mysqli->error);
			$array = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$array[] = $row;
				}
			}
			return $array;
		
		}
		static function update_oferta($row_id,$field_name,$field_val){
			global $mysqli;
			
			$query = "UPDATE `".OFFERTS_ROWS_TBL."` SET 
				  ".$field_name." ='".$field_val."'
				  WHERE id='".$row_id."'";
						  
		    $mysqli->query($query)or die($mysqli->error);
		
		}
		static function delete_oferta($client_id,$oferta_id){
			global $mysqli;

		    $query="DELETE oferts, oferts_rows
						 FROM `".OFFERTS_TBL."` oferts
						 LEFT JOIN `".OFFERTS_ROWS_TBL."` oferts_rows
						 ON oferts.id = oferts_rows.oferta_id
						 WHERE oferts.id = '".$oferta_id."'";
		    $mysqli->query($query)or die($mysqli->error);
		    $num_deleted_rows = $mysqli->affected_rows;	
		}
		static function agregate_oferta_rows($data){
			global $mysqli;

			for($i = 0 ; $i < count($data) ; $i++)
			{
				$name = ''; $summ = 0;
				for($j = 0 ; $j < count($data[$i]) ; $j++)
				{
					  $id = $data[$i][$j];
					  $query = "SELECT * FROM `".OFFERTS_ROWS_TBL."`
								WHERE id='".$id."'";
					  $result = $mysqli->query($query)or die($mysqli->error);
					  
					  $row = $result->fetch_assoc(); 
					  $name .= $row['name'].'<br>';
					  if(!isset($quantity)) $quantity = (int)$row['quantity'];
					  $summ += (float)$row['summ'];
					  
					  if($j > 0)
					  {
						  $query = "DELETE FROM `".OFFERTS_ROWS_TBL."`
									WHERE id='".$id."'";
						  $result = $mysqli->query($query)or die($mysqli->error);
					  }
				
				}
				
				$query = "UPDATE `".OFFERTS_ROWS_TBL."`
						  SET 
						  name='".$name."',  
						  price='".$summ/$quantity."', 
						  summ='".$summ."'
						  WHERE id='".$data[$i][0]."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				
				unset($quantity);
			
			}
		
		}
		
		static function fetch_oferta_common_data($oferta_id){
			global $mysqli;
			
			$query = "SELECT*FROM `".OFFERTS_TBL."` WHERE id = '".$oferta_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows > 0){
				return $result->fetch_assoc();
			}
			return false;
		
		}
		static function fetch_all_client_oferts($client_id){
			global $mysqli;
			
			$query = "SELECT*FROM `".OFFERTS_TBL."` WHERE client_id = '".$client_id."' ORDER BY id DESC";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows > 0){
				return $result;
			}
			return false;
		
		}
		static function update_oferta_common_fields($id,$field_name,$field_val){
			global $mysqli;
			
			$query = "UPDATE `".OFFERTS_TBL."` SET  ".$field_name." ='".$field_val."' WHERE id='".$id."'";
			$mysqli->query($query)or die($mysqli->error);

		}
		static function prepare_general_doc($doc,$general_data,$table_data){
			//global $mysqli;
			global $desjatichn_word_transfer_arr;
			global $change_word_ending_arr_I;
			global $change_word_ending_arr_II;
			global $change_word_ending_arr_III;
			global $change_word_ending_arr_IV;

			
	            list($first_part,$second_part) = explode('-',number_format($table_data['itogo'],2,'-',''));
				$for_pay = num_word_transfer($first_part);
				$for_pay = strtr($for_pay,$desjatichn_word_transfer_arr);
			
				//list($first_part_nds,$second_part_nds) = explode('-',number_format($table_data['nds'],2,'-',''));
				
				$for_pay = $for_pay.' рублей '.$second_part.' коп.';
				//$for_pay = '('.$for_pay.' рублей '.$second_part.' коп.), в т.ч. НДС 18% '.$first_part_nds.' руб. '.$second_part_nds.' коп.';
				$for_pay = strtr($for_pay,$change_word_ending_arr_I);
				$for_pay = strtr($for_pay,$change_word_ending_arr_II);
				$for_pay = strtr($for_pay,$change_word_ending_arr_III);
				$for_pay = strtr($for_pay,$change_word_ending_arr_IV);

				$delivery_adderss_tpl_path = ($general_data['address'] == 'samo_vivoz')? $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/samo_vivoz.tpl':$_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/nasha_dostavka.tpl';
				$fd = fopen($delivery_adderss_tpl_path,'rb');
				$delivery_adderss_string = fread($fd,filesize($delivery_adderss_tpl_path));
				fclose($fd);
				$delivery_adderss = str_replace('[DELIVERY_ADDRESS]',$general_data['address'],$delivery_adderss_string );
			
			   if($general_data['type'] == 'days'){
				    //$prepayment_term = '<?php include ($_SERVER[\'DOCUMENT_ROOT\'].\'/os/modules/agreement/agreements_templates/\'.$general_data[\'prepayment\'].\'_prepaiment_conditions.tpl\'); ? >';
					/**/
					$prepayment_term_tpl_path = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/'.$general_data['prepayment'].'_prepaiment_conditions_oferta.tpl';
					$fd = fopen($prepayment_term_tpl_path,'rb');
					$prepayment_term = fread($fd,filesize($prepayment_term_tpl_path));
					fclose($fd);
					$prepayment_term_block = str_replace('[FOR_PAY_TEXT]',number_format($table_data['itogo'],"2","."," ").' ('.$for_pay.') в том числе 18% НДС  '.number_format($table_data['nds'],"2","."," ").'руб.',$prepayment_term );
					
				
				}
				if($general_data['type'] == 'date'){
				   $delivery_date_arr = explode(' ',$general_data['shipping_date_time']); 
				    $delivery_date_arr[0] = implode('.',array_reverse(explode('-',$delivery_date_arr[0])));
					$delivery_date_arr[1] = explode(':',$delivery_date_arr[1]);
					$delivery_date_arr[1] = $delivery_date_arr[1][0].' часов '.$delivery_date_arr[1][1].' минут ';
					
				    $delivery_date = $delivery_date_arr[1].$delivery_date_arr[0]."г.";
					
					
					$final_date_time_arr = explode(' ',$general_data['final_date_time']); 
				    $final_date_time_arr[0] = implode('.',array_reverse(explode('-',$final_date_time_arr[0])));
					$final_date_time_arr[1] = explode(':',$final_date_time_arr[1]);
					$final_date_time_arr[1] = $final_date_time_arr[1][0].' часов '.$final_date_time_arr[1][1].' минут ';
					
					$maket_handing_date = $final_date_time_arr[1].$final_date_time_arr[0]."г.";
					$maket_sign_date = $final_date_time_arr[1].$final_date_time_arr[0]."г.";
					$paymnet_date = $final_date_time_arr[1].$final_date_time_arr[0].'г.';
					
					$doc = str_replace('[PAYMENT_DATE]',$paymnet_date,$doc );
				    $doc = str_replace('[DELIVERY_DATE]',$delivery_date,$doc );
					$doc = str_replace('[MAKET_HANDING_DATE]',$maket_handing_date,$doc );
					$doc = str_replace('[MAKET_SIGN_DATE]',$maket_sign_date,$doc );
				
				     
					$prepayment_term_tpl_path = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/'.$general_data['prepayment'].'_prepaiment_conditions_oferta_by_date.tpl';
					$fd = fopen($prepayment_term_tpl_path,'rb');
					$prepayment_term = fread($fd,filesize($prepayment_term_tpl_path));
					fclose($fd);
					$prepayment_term_block = str_replace('[PAYMENT_DATE]',$paymnet_date,$prepayment_term );
				}
			
				//$doc = str_replace('[SPECIFICATION_NUM]',$specification_num,$doc );
				//$doc = str_replace('[SPECIFICATION_DATE]',$specificationDate,$doc );
				//$doc = str_replace('[AGREEMENT_NUM]',$agreement_num,$doc );
				//$doc = str_replace('[AGREEMENT_DATE]',$agreementDate,$doc );
				$doc = str_replace('[DOC_NUM]','<?php echo $general_data[\'num\']; ?>',$doc );
				$doc = str_replace('[DOC_DATE]',implode('.',array_reverse(explode('-',substr($general_data['date_time'],0,10)))),$doc);
				$doc = str_replace('[PRODUCTION_TERM]','<?php echo $general_data[\'item_production_term\']; ?>',$doc );
				$doc = str_replace('[PREPAMENT_TERM_BLOCK]',$prepayment_term_block,$doc );
				$doc = str_replace('[PREPAMENT_TERM]','<?php echo $general_data[\'prepayment\']; ?>',$doc );
				$doc = str_replace('[PRODUCTON_DELIVERY_TERM]','<?php echo $general_data[\'item_production_term\']; ?>',$doc );
				//$doc = str_replace('[PREPAMENT_SUMM]','100',$doc );
				$doc = str_replace('[DELIVERY_TERM]','<?php echo $general_data[\'shipping_date_time\']; ?>',$doc );
				$doc = str_replace('[DELIVERY_ADDRESS]',$delivery_adderss,$doc );
				$doc = str_replace('[ITEMS_NUM]',$table_data['items_num'],$doc );
				$doc = str_replace('[FOR_PAY]',number_format($table_data['itogo'],"2",".",""),$doc );
				$doc = str_replace('[FOR_PAY_TEXT]',$for_pay,$doc );
				
				$doc = str_replace('[SPECIFICATION_TABLE]',$table_data['table'],$doc );





                $doc = str_replace('[OUR_DIRECTOR]','<?php echo $general_data[\'our_chief\']; ?>',$doc );
			    $doc = str_replace('[OUR_COMP_FULL_NAME]','<?php echo $our_firm[\'comp_full_name\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_LEGAL_ADDRESS]','<?php echo $our_firm[\'legal_address\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_POSTAL_ADDRESS]','<?php echo $our_firm[\'postal_address\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_INN]','<?php echo $our_firm[\'inn\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_OGRN]','<?php echo $our_firm[\'ogrn\']; ?>',$doc ); 
		        $doc = str_replace('[OUR_COMP_KPP]','<?php echo $our_firm[\'kpp\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_R_ACCOUNT]','<?php echo $our_firm[\'r_account\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_BANK]','<?php echo $our_firm[\'bank\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_BIK]','<?php echo $our_firm[\'bik\']; ?>',$doc );
				$doc = str_replace('[OUR_COMP_COR_ACCOUNT]','<?php echo $our_firm[\'cor_account\']; ?>',$doc );
				 
				$doc = str_replace('[CLIENT_COMP_FULL_NAME]','<?php echo $client_firm[\'comp_full_name\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_LEGAL_ADDRESS]','<?php echo $client_firm[\'legal_address\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_POSTAL_ADDRESS]','<?php echo $client_firm[\'postal_address\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_INN]','<?php echo $client_firm[\'inn\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_OGRN]','<?php echo $client_firm[\'ogrn\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_KPP]','<?php echo $client_firm[\'kpp\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_R_ACCOUNT]','<?php echo $client_firm[\'r_account\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_BANK]','<?php echo $client_firm[\'bank\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_BIK]','<?php echo $client_firm[\'bik\']; ?>',$doc );
				$doc = str_replace('[CLIENT_COMP_COR_ACCOUNT]','<?php echo $client_firm[\'cor_account\']; ?>',$doc );
				
				$director_data = explode(' ',$general_data['our_chief']);
		        $short_director = $director_data[0];
		        if(count($director_data)>1)for($i = 1 ; $i < count($director_data);$i++){  $short_director .= ' '.mb_substr($director_data[$i],0,1, 'UTF-8').'.';}
				$doc = str_replace('[OUR_DIRECTOR_SHORT]',$short_director,$doc );
				
				return $doc;
		}
		
		static function build_specification_tbl($doc_type,$data){

				$itogo=0;
				$nds=0;
				$items_num=0;
				$table = '<table id="spec_tbl" class="spec_tbl">';
				if($doc_type=='spec') $table .= '<tr class="bold_font"><td>№</td><td>Наименование и<br>описание продукции</td><td>Кол-во продукции</td><td colspan="2">стоимость за штуку</td><td colspan="2">Общая стоимость</td></tr>';
				if($doc_type=='oferta') $table .= '<tr class="bold_font"><td>№</td><td>Товары (работы, услуги)</td><td>Кол-во</td><td colspan="2">Цена</td><td colspan="2">Сумма</td></tr>';

		
                foreach($data as $key2 => $val2)
				{
				    $discount = $data[$key2]['discount'];
					$price = ($discount != 0)? round((($data[$key2]['price']/100)*(100 + $discount)),2) : $data[$key2]['price'] ;
				    $summ = $data[$key2]['quantity']*$price;
						 
				    $table .= '<tr><td class="num">'.($key2+1).'</td><td class="name">'.$data[$key2]['name'].'</td><td class="quantity">'.$data[$key2]['quantity'].'</td><td class="price">'.number_format($price,"2",".",'').'</td><td class="currensy">р.</td><td class="price">'.number_format($summ,"2",".",'').'</td><td class="currensy">р.</td></tr>';
					$itogo += (float)$summ;
					$nds += round(($summ/118*18), 2);
					$items_num++;
				}
				$table .= '<tr class="bold_font"><td colspan="5">Итого с НДС</td><td class="price">'.number_format($itogo,"2",".",'').'</td><td class="currensy">р.</td></tr>';
				$table .= '<tr class="bold_font"><td colspan="5">Из них НДС (18%)</td><td class="price">'.number_format($nds,"2",".",'').'</td><td class="currensy">р.</td></tr>';
				$table .= '</table>';
				
				return array('table'=>$table,'itogo'=>$itogo,'nds'=>$nds,'items_num'=>$items_num);
		}
		static function insert_row_in_oferta($oferta_id,$name,$quantity,$price_in,$price,$discount){
			global $mysqli;
			
			$query = "INSERT INTO `".OFFERTS_ROWS_TBL."` SET 
						  oferta_id='".$oferta_id."',
						  name='".$name."',
						  quantity='".$quantity."',
						  price_in='".$price_in."',
						  price='".$price."',
						  summ='".$quantity*$price."',
						  discount='".$discount."'
						  ";
						  
			  //echo $query;	
			  //exit;	  
			  $result = $mysqli->query($query)or die($mysqli->error);
			  return $mysqli->insert_id;
		
		}
		static function date_terms_convert($dataArr){
			print_r($dataArr);
		// настройки в завасимости от типа оферта
			if($dataArr['date_type']=='days'){
			    $specification_type = 'days';
			    $shipping_date_time = '';
				$final_date_time = '';
				$item_production_term = $dataArr['datetime'];
			}
			if($dataArr['date_type']=='date'){
			    $specification_type = 'date';
				
			    //echo $dataArr->datetime;echo '<br>';
			    $shipping_date_time_arr = explode(' ',$dataArr['datetime']);
				$shipping_date_time_arr[0] = implode('-',array_reverse(explode('.',$shipping_date_time_arr[0])));
				if(isset($shipping_date_time_arr[1])){
				     if(strlen($shipping_date_time_arr[1])==0 || strlen($shipping_date_time_arr[1])>8) $shipping_date_time_arr[1] = '00:00:00';
				     if(strlen($shipping_date_time_arr[1])==2) $shipping_date_time_arr[1] = $shipping_date_time_arr[1].':00:00';
				     if(strlen($shipping_date_time_arr[1])==5) $shipping_date_time_arr[1] = $shipping_date_time_arr[1].':00';
				}
				else $shipping_date_time_arr[1] = '00:00:00';
			    $shipping_date_time = implode(' ',$shipping_date_time_arr);
				//echo $shipping_date_time;echo '<br>';
				//echo $dataArr['final_date'];echo '<br>';
				$final_date_time_arr = explode(' ',$dataArr['final_date']);
				$final_date_time_arr[0] = implode('-',array_reverse(explode('.',$final_date_time_arr[0])));
				if(isset($shipping_date_time_arr[1])){
					if(strlen($final_date_time_arr[1])==0 || strlen($final_date_time_arr[1])>8) $final_date_time_arr[1] = '00:00:00';
					if(strlen($final_date_time_arr[1])==2) $final_date_time_arr[1] = $final_date_time_arr[1].':00:00';
					if(strlen($final_date_time_arr[1])==5) $final_date_time_arr[1] = $final_date_time_arr[1].':00';
				}
				else $final_date_time_arr[1] = '00:00:00';
				$final_date_time = implode(' ',$final_date_time_arr);
				//echo $final_date_time;
				$item_production_term = '';
			}
			return array('shipping_date_time'=>$shipping_date_time,'final_date_time'=>$final_date_time,'item_production_term'=>$item_production_term);
		
		}
		static function add_items_for_specification($dataArr,$specification_num,$rows_data,$client_id,$agreement_id,$agreement_date, $our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$date,$short_description,$address,$prepayment/**/){
		
			global $mysqli;
			
             
			// print_r($dataArr);
			// exit;

			if(!$specification_num){
				$query = "SELECT MAX(specification_num) specification_num FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows > 0){
				    $row = $result->fetch_assoc();
					$specification_num = $row['specification_num'] + 1 ;
				}
				else $specification_num = 1 ;
			}
			$date_arr = explode('.',$date);
			$date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
		
			$rows_data_arr = json_decode($rows_data);
			// echo $specification_num.'<pre>'; print_r($rows_data_arr); echo '</pre>';//
			// exit;
			
			// настройки в завасимости от типа спецификации
			$dates_data = self::date_terms_convert($dataArr);
			
			$shipping = '0000-00-00 00:00:00';
			foreach($rows_data_arr as $key => $val){
			
				if(count($val)==0) continue;
				
			    $summ_out = 0;
                $uslugi_summ_out = 0;
				
				$main_id = $key;
				$dop_id = $val[0];
				 
				$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$main_id."'";
				// echo $query."\r\n";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    // 1). main_data
				    $main_data = $result->fetch_assoc();
					
					$query2="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id."'";
					// echo $query."\r\n";
					$result2 = $mysqli->query($query2)or die($mysqli->error);
					if($result2->num_rows>0){
					     // 2). dop_data
					     $dop_data = $result2->fetch_assoc();
						 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
						 if(@$dop_data['expel']!=''){
							 $obj = @json_decode($dop_data['expel']);
							 foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
						 }
					
						 $summ_out = $dop_data['quantity']*$dop_data['price_out'];
						 $name= (($main_data['art']!='')? 'арт.'.$main_data['art']:'')." ".$main_data['name'];					 
						 $price_in =   $dop_data['price_in']; 
						 $price =   $dop_data['price_out']; 

						 // прежде чем записать ряд в спецификацию сверим совпадает ли количество в расчете и в услугах
						 // для этого делаем дополнительный запрос к таблице RT_DOP_USLUGI, далее после добавления ряда 
						 // будет такойже запрос к таблице RT_DOP_USLUGI но уже чтобы добавить доп услуги в спецификацию
						 $query2_dop="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type";
						 // echo $query."\r\n";
						 $result2_dop = $mysqli->query($query2_dop)or die($mysqli->error);
						 if($result2_dop->num_rows>0){
						     while($uslugi_data = $result2_dop->fetch_assoc()){
							 
							     $print_details_arr = json_decode($uslugi_data['print_details'],TRUE);
								  
								 // если calculator_type =='free' то непроверяем, isset($print_details_arr["calculator_type"]) 
								 // для совместимости с предыдущими версиями в которых $print_details_arr["calculator_type"] нет
								 $revoke = (isset($print_details_arr["calculator_type"]) && $print_details_arr["calculator_type"] =='free')?true:false;
									  
									  
							     if($uslugi_data['glob_type']=='print' && !$revoke && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									 $reload['flag'] = true;
									 //echo $dop_data['quantity'];
									 include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
									 $json_out =  rtCalculators::change_quantity_and_calculators($dop_data['quantity'],$dop_data['id'],'true','false');
									 $json_out_obj =  json_decode($json_out);
									 
									 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
									 // сбрасываем количество тиража и нанесения до 1шт.
									 if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
										 rtCalculators::change_quantity_and_calculators(1,$dop_data['id'],'true','false');
										 
										 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_data['id']."'";
										 $result = $mysqli->query($query)or die($mysqli->error);
									 }
									 
			
								 } /**/
								 if($uslugi_data['glob_type']=='extra' && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									  $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$dop_data['quantity']."'  WHERE `id` = '".$uslugi_data['id']."'";
									  $result = $mysqli->query($query)or die($mysqli->error);
									  $uslugi_data['quantity'] = $dop_data['quantity'];
									 
			
								 }
							 }
						 }
						 if(isset($reload['flag']) && $reload['flag'] == true){
							 header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
							 exit;
						 }
						 
						 if($main_data['type']!='cat'){
							 include_once(ROOT."/libs/php/classes/os_form_class.php");
							 include_once(ROOT."/libs/php/classes/cabinet/cabinet_class.php");//os_form_class.php
							 $cabinet = new Cabinet();
							 $details =  $cabinet->get_a_detailed_specifications($main_data['type'], $dop_data['no_cat_json']);
							 $details =  strip_tags($details,'<div><br><br/><br />');
							 $details =  str_replace(array('<div>','</div>'),array('<br>',''),$details);	
							 $details =  str_replace(array("\n","\r","\t"),'',$details);			
							 $details =  str_replace('<br><br>','<br>',$details);
							 $details =  preg_replace('/<div[^<]+>/','',$details);
							 $name .= $details;
							
						 }

						 
				         // записываем ряд
						 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$dop_data['quantity'],$price,$price_in,$dop_data['discount'],$date,$dataArr,$dates_data);
						 
						 
						 $query3="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type DESC";
						 // echo $query."\r\n";
						 $result3 = $mysqli->query($query3)or die($mysqli->error);
						 if($result3->num_rows>0){
						     
						     while($uslugi_data = $result3->fetch_assoc()){
					            // 3). uslugi_data
								 if($uslugi_data['glob_type'] == 'print' && !(!!$expel["print"])){
									  //include_once(ROOT."/libs/php/classes/print_calculators_class.php");
								      //$name = printCalculator::convert_print_details($uslugi_data['print_details']);
									  
									  $print_details_arr = json_decode($uslugi_data['print_details'],TRUE);
									  
									  if(!isset($print_details_arr["calculator_type"]) || (isset($print_details_arr["calculator_type"]) && ($print_details_arr["calculator_type"] =='auto' || $print_details_arr["calculator_type"] =='manual'))){
									      include_once(ROOT."/libs/php/classes/print_calculators_class.php");
								          $name = printCalculator::convert_print_details($uslugi_data['print_details']);
									  }
									  if(isset($print_details_arr["calculator_type"]) && $print_details_arr["calculator_type"] =='free'){
									      $uslugi_data['quantity'] = $print_details_arr['quantity'];
										  $name = $print_details_arr['print_type'].' '.base64_decode($print_details_arr['commentForClient']);
									  }
									  
									 // записываем ряд
									 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$uslugi_data['quantity'],$uslugi_data['price_out'],$uslugi_data['price_in'],$uslugi_data['discount'],$date,$dataArr,$dates_data);
								 }
								 if($uslugi_data['glob_type'] == 'extra' && !(!!$expel["dop"])){
									 // если альтернативное название отсутствует (вводится при заведении услуги "НЕТ В СПИСКЕ")
									 if(trim($uslugi_data['other_name']) == ""){
									 	$extra_usluga_details = self::get_usluga_details($uslugi_data['uslugi_id']);
										$name = ($extra_usluga_details)? $extra_usluga_details['name']:'Неопределено';									 	
									 }else{
									 	$name = $uslugi_data['other_name'];
									 }
										
									 // меняем количество на 1(еденицу) если это надбавка на всю стоимость
									 $uslugi_data['quantity'] = ($uslugi_data['for_how']=='for_all')? 1: $uslugi_data['quantity'];
									 // записываем ряд
									 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$uslugi_data['quantity'],$uslugi_data['price_out'],$uslugi_data['price_in'],$uslugi_data['discount'],$date,$dataArr,$dates_data);
								 }/**/
								 
								  
						       
						 
						    }
					    }
				    }
				}
			}	
			
			// exit;	
		
			
			// этап создания отдельного файла Спецификации и сохраниения его на диск
			// проверяем существует ли папка данного клиента если нет создаем её
			// если происходит ошибка выводим отчет
			
			// проверяем есть папка данного клента, если её нет то создаем её
			$client_dir_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval($client_id);
			//chmod("data/com_offers/", 0775);
			
			if(!file_exists($client_dir_name)){
				if(!mkdir($client_dir_name, 0775)){
					echo 'ошибка создания папки клиента (4)'.$client_dir_name;
					exit;
				}
			}
			
			// папка обозначающая год (название папки - название года)
			
			$agreement_date = explode('-',$agreement_date);
			$year_dir_name = $client_dir_name.'/'.$agreement_date[0];
			if(!file_exists($year_dir_name)){
				if(!mkdir($year_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$year_dir_name;
					exit;
				}
			}
			
			// папка для типа договора
			$type_dir_name = $year_dir_name.'/long_term';
			if(!file_exists($type_dir_name)){
				if(!mkdir($type_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$type_dir_name;
					exit;
				}
			}
			
			$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
			$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
			
			// папка для выбранных сторон
			$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			// папка для выбранных спецификаций
			$full_dir_name = $full_dir_name.'/specifications';
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			// записываем файл
			$file_name = $full_dir_name.'/'.$specification_num.'.tpl';
			//echo $file_name;
			//$file_name = $dir_name_full.'/com_pred_1_1.doc';
			//if(file_exists($file_name)){
				//echo 'файл с таким именем уже существует (232)';
				//exit;
			//}
	
			if($dataArr['date_type']=='days') $origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/specification.tpl';
			if($dataArr['date_type']=='date') $origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/specification_type2_by_date.tpl';
			
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
			
			/*
			// создаем предзаказ 
			include_once(ROOT."/libs/php/classes/rt_class.php");

			RT::make_order($rows_data,$client_id,$_GET['query_num'],$specification_num,$agreement_id,$dataArr['date_type'],$dataArr['date_type'],$dates_data['shipping_date_time'],$dates_data['item_production_term'],$dates_data['final_date_time']);
			*/
			return $specification_num;

		} 
		static function fetch_specification($client_id,$agreement_id,$specification_num){
		//function fetch_specification
		
			global $mysqli;
			
			$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."' AND specification_num = '".$specification_num."' ORDER BY id";
			$result = $mysqli->query($query)or die($mysqli->error);
			
	
			if($result->num_rows > 0) return $result;
			else return false;
	
	    }
		static function insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$quantity,$price,$price_in,$discount,$date,$dataArr,$dates_data){
			global $mysqli;
			
			$query = "INSERT INTO `".GENERATED_SPECIFICATIONS_TBL."` SET 
						  client_id='".$client_id."',
						  agreement_id='".$agreement_id."',
						  our_chief='".$our_firm_acting_manegement_face['name']."',
						  our_chief_in_padeg='".$our_firm_acting_manegement_face['name_in_padeg']."',
						  our_chief_position='".$our_firm_acting_manegement_face['position']."',
						  our_chief_position_in_padeg='".$our_firm_acting_manegement_face['position_in_padeg']."',
						  our_basic_doc='".$our_firm_acting_manegement_face['basic_doc']."',
						  client_chief='".$client_firm_acting_manegement_face['name']."',
						  client_chief_in_padeg='".$client_firm_acting_manegement_face['name_in_padeg']."',
						  client_chief_position='".$client_firm_acting_manegement_face['position']."',
						  client_chief_position_in_padeg='".$client_firm_acting_manegement_face['position_in_padeg']."',
						  client_basic_doc='".$client_firm_acting_manegement_face['basic_doc']."',
						  specification_num='".$specification_num."',
						  specification_type='".$dataArr['date_type']."',
						  short_description='".$short_description."',
						  address='".$address."',
						  prepayment='".$prepayment."',
						  date = '".$date."',
						  shipping_date_time='".$dates_data['shipping_date_time']."',
						  final_date_time='".$dates_data['final_date_time']."',
						  name='".$name."',
						  makets_delivery_term='5 (пяти)',
						  item_production_term='".$dates_data['item_production_term']."',
						  discount='".$discount."',
						  quantity='".$quantity."',
						  price='".$price."',
						  price_in='".$price_in."',
						  summ='".$quantity*$price."'
						  ";
						  
			// echo $query;	
			// exit;	  
			$result = $mysqli->query($query)or die($mysqli->error);
			return $mysqli->insert_id;
		
		}
		static function get_usluga_details($usluga_id){
	        global $mysqli;
			
			$query="SELECT name FROM `".OUR_USLUGI_LIST."` WHERE id = '".$usluga_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
			   $row=$result->fetch_assoc();
			   return $row;
			}
			return false;
			
			/*	
			$query="SELECT name FROM `".OUR_USLUGI_LIST."` WHERE id  = (SELECT parent_id FROM `".OUR_USLUGI_LIST."` WHERE id = '".$usluga_id."') UNION SELECT name FROM `".OUR_USLUGI_LIST."` WHERE id = '".$usluga_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
			  $name = array();
			   while($row=$result->fetch_assoc()){
			      $name[]=  $row['name'];
			   }
			   return array('name'=>implode(', ',$name));
			}
			return false;*/
			
		
	   }	
	   static function fetch_specifications($client_id,$agreement_id,$group_by = FALSE){
			global $mysqli;
			
			$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."'";
			
			if($group_by) $query .= "GROUP BY ".$group_by ;
			else $query .= "ORDER BY id ";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			
			if($result->num_rows > 0){
				 return $result;
			}
			else return false;
		
		}
	    static  function our_firm_manegement_faces($id){
			global $mysqli;
			
			$out_put = array();
			$query = "SELECT*FROM `".OUR_FIRMS_MANAGEMENT_TBL."` WHERE `requisites_id` = '".$id."' ORDER BY `acting`";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				while($item=$result->fetch_assoc()){
					if($item['name']!='') $out_put[] = array('id' => $item['id'],'position' => $item['position'],'name' => $item['name'],'acting' => $item['acting']);
				}
			}
			
			return (count($out_put)>0)? $out_put:false;
	    }
		static function getSpecificationsDates($inDataArr){
			global $mysqli;
			global $user_id;
			
			 //print_r($inDataArr);
			 $inDataArr = (array)$inDataArr;
			 $newDataArr = array();
			 
			 foreach($inDataArr['ids'] as $key => $data){
				//echo $data->row_id.' - <br>';
				$dataArr[$data->row_id] = $data->row_id;
			 }
			 
			 if(isset($dataArr)){
				 $query="SELECT dop.id id, dop.row_id row_id, shipping_date, shipping_time, shipping_type, work_days, shipping_redactor_id, shipping_redactor_access FROM `".RT_MAIN_ROWS."` main 
				 LEFT JOIN `".RT_DOP_DATA."` dop
				 ON main.id = dop.row_id
				 WHERE dop.id IN('".implode("','",$dataArr)."') ORDER BY main.sort";
				 $result = $mysqli->query($query)or die($mysqli->error);
				 if($result->num_rows>0){
				     // SELECT * FROM `os__rt_dop_data` WHERE `id` IN ('681','743','1003','1027') ORDER BY `shipping_type` DESC
					 // не понадобилось
					 // $day_num_count = 0;
					 // $max_day_num = '0';
					 // $defined_date = $expired_date = false;
					 
					 // $max_date = '1970-01-01 00:00:01';
					 // $cur_date = '2015-10-21 00:00:01';//date("Y-m-d H:i:s");    
					 while($row = $result->fetch_assoc()){
					     // echo '<pre>'; print_r($row); echo '</pre>';
					     // не понадобилось
					     //if($row['standart']!='' && $row['standart']!='0') $day_num_count++;
						 $value = $shablon = $shablon_en = ''; 
						 if(isset($user_id) && $row['shipping_redactor_id']!=$user_id && $row['shipping_redactor_access']=='1') $who = 'АДМИН';
						 else if(isset($user_id) && $row['shipping_redactor_id']==$user_id && $row['shipping_redactor_access']!='8') $who = 'вы';
						 else if($row['shipping_redactor_access']=='8') $who = 'СНАБ';
						 else $who = '';
						 // если установленна дата выбираем её, иначе количество рабочих дней
						 if($row['shipping_type']!='none'){
						 	 if($row['shipping_type']=='date'/*$row['shipping_date']>'1970-01-01'*/){
								 $value = $row['shipping_date']; 
								 $shablon = 'дата'; 
								 $shablon_en = 'date'; 
							 }
							 else if($row['shipping_type']=='rd'){
								 $value = $row['work_days']; 
								 $shablon = 'р/д'; 
								 $shablon_en = 'days'; 
							 }
							 $newDataArr[] = array('row_id'=> $row['row_id'],'value'=> $value,'shablon'=> $shablon,'shablon_en'=> $shablon_en, 'who'=> $who);
						 }
						 // не понадобилось
						 // определяем максимальное значение установленных рабочих дней в $row['standart']
						 // if($row['standart']>$max_day_num) $max_day_num = $row['standart'];
						
						 // $some_date = $row['shipping_date'].' '.$row['shipping_time'];
						 // если определенна хотябы одна дата 
						 // if($some_date>'1970-01-01') $defined_date = true;
						 // определяем максимальное значение установленных дат в $row['shipping_date'] и $row['shipping_time']
						 // if($some_date>$max_date) $max_date = $some_date;
						 
					 }
			
					 $outDataArr['data'] = $newDataArr;
					 $outDataArr['all_positions'] = $result->num_rows;
					 $outDataArr['defined_positions'] = count($newDataArr);
					 $outDataArr['min_allowed_date'] = substr(goOnSomeWorkingDays(date("Y-m-d H:i:s"),3,'+'),0,10);//,time()+60*60*24*
					 // если не во всех расчетах установлен срок изготовления содаем флаг undefined_days_warn
					 // if(count($dataArr)>$day_num_count) $outDataArr['undefined_days_warn'] = 1;
					 // если определенна хотябы одна дата 
					/* if($defined_date){
					     $outDataArr['defined_date'] = 1;
						 
						 // если количество рабочих дней между текущей и максимальной датой меньше 
						 // максимального установленного срока в рабочих днях то устанавливаем флаг 
						 if($cur_date > $max_date) $outDataArr['expired_date'] = 1;
						 else{
						     // определяем количество дней между текущей и максимальной датой
						     $outDataArr['working_days_range'] = getWorkingDays($cur_date,$max_date);
						     if($max_day_num > $outDataArr['working_days_range']) $outDataArr['expired_date'] = 1;
						 }
					 }*/
					 // мексмальное установленное количество рабочих дней 
					 // $outDataArr['max_day_num'] = $max_day_num;
					 // мексмально установленная дата
					 // $outDataArr['max_date'] = substr($max_date,0,10);
					 // $outDataArr['cur_date'] = substr($cur_date,0,10);
					 
				 }
				 
			 }
			 // print_r($outDataArr);
			 return json_encode($outDataArr);
		}
    }

?>