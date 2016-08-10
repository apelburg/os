<?php
    // удалить insert_copied_rows_old

    class Agreement{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function add_items_for_specification($specification_num,$rows_data,$client_id,$agreement_id,$agreement_date, $our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$date,$short_description,$address,$prepayment/**/){
		
			global $mysqli;
			

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
			
			foreach($rows_data_arr as $data_arr){
			
				if(count($data_arr)==0) continue;
				
			    $summ_out = 0;
                $uslugi_summ_out = 0;
				
				$main_id = $data_arr->pos_id;
				$dop_id = $data_arr->row_id;
				 
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
						 // прежде чем записать ряд в спецификацию сверим совпадает ли количество в позиции и в услугах
						 // для этого делаем дополнительный запрос к таблице RT_DOP_USLUGI, далее после добавления ряда 
						 // будет такойже запрос к таблице RT_DOP_USLUGI но уже чтобы добавить доп услуги в спцификацию
						 $query2_dop="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type";
						 // echo $query."\r\n";
						 $result2_dop = $mysqli->query($query2_dop)or die($mysqli->error);
						 if($result2_dop->num_rows>0){
						     while($uslugi_data = $result2_dop->fetch_assoc()){
							     if($uslugi_data['glob_type']=='print' && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									 $reload['flag'] = true;
									 //echo $dop_data['quantity'];
									 include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
									 $json_out =  rtCalculators::change_quantity_and_calculators($dop_data['quantity'],$dop_data['id']);
									 $json_out_obj =  json_decode($json_out);
									 
									 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
									 // сбрасываем количество тиража и нанесения до 1шт.
									 if(isset($json_out_obj->outOfLimit) || isset($json_out_obj->needIndividCalculation)){
										 rtCalculators::change_quantity_and_calculators(1,$dop_data['id']);
										 
										 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_data['id']."'";
										 $result = $mysqli->query($query)or die($mysqli->error);
									 }
									 
			
								 } /**/
							 }
						 }
						 if(isset($reload['flag']) && $reload['flag'] == true){
							 header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
							 exit;
						 }
						 
				         // записываем ряд
						 Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$dop_data['quantity'],$price,$date);
						 
						 
						 $query3="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type";
						 // echo $query."\r\n";
						 $result3 = $mysqli->query($query3)or die($mysqli->error);
						 if($result3->num_rows>0){
						     
						     while($uslugi_data = $result3->fetch_assoc()){
					            // 3). uslugi_data
								 if($uslugi_data['glob_type'] == 'print' && !(!!$expel["print"])){
                                    $name = Agreement::convert_print($uslugi_data['print_details']);
									// записываем ряд
									Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$uslugi_data['quantity'],$uslugi_data['price_out'],$date);
								 }
								 if($uslugi_data['glob_type'] == 'extra' && !(!!$expel["dop"])){
								    $uslugi_summ_out += $uslugi_data['quantity']*$uslugi_data['price_out'];
								 }/**/
								 
								  
						       
						 
							 }
						 }
				    }
				}

				// echo '<pre>'; print_r($expel); echo '</pre>';
				// здесь как-то в учу надо собирать данные о нанесениях и о доп услугах
				// 1. брать суммму всех нанесений и допуслуг
				// складывать вместе с суммой стоимости ариткула и делить на количество артикулов
				// 2. в каком-то формате записывать данные о нанесениях и допуслугах
				
				// echo "<br>".(($summ_out+$uslugi_summ_out)/$dop_data['quantity'])*$dop_data['quantity']." -> $summ_out +$uslugi_summ_out / ".$dop_data['quantity']."<br>";
				// $price = ($dop_data['discount'] != 0 )? round(((($summ_out+$uslugi_summ_out)/$dop_data['quantity'])/100)*(100 + $dop_data['discount']),2) :  round(($summ_out+$uslugi_summ_out)/$dop_data['quantity'],2) ;
				
				
						
				
			}	
				
		//exit;
			
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
			if(file_exists($file_name)){
				echo 'файл с таким именем уже существует (2)';
				exit;
			}
	
			$origin_file_name = ROOT.'/modules/agreement/agreements_templates/specification.tpl';
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
			
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
		static function insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$quantity,$price,$date){
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
						  short_description='".$short_description."',
						  address='".$address."',
						  prepayment='".$prepayment."',
						  date = '".$date."',
						  name='".$name."',
						  makets_delivery_term='5 (пяти)',
						  item_production_term='10 (десять)',
						  quantity='".$quantity."',
						  price='".$price."',
						  summ='".$quantity*$price."'
						  ";
						  
			// echo $query4;		  
			  $result = $mysqli->query($query)or die($mysqli->error);
		
		}
		static function convert_print($print_details){
		
		    global $mysqli;
			
		    $print_details = json_decode($print_details);
			echo '<pre>'; print_r($print_details); echo '</pre>';
			$out_put = array();
			$out_put[] = $print_details->print_type;
			$out_put[] = 'место нанесения: '.$print_details->place_type;
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
					    $type = $row['param_type'];
					    $prefix = $type.': ';
						$result->data_seek(0);
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   echo '<pre>'; print_r($row); echo '</pre>';
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
				         echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					echo $query;
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
    }

?>