<?php 

  /*if(@$_SESSION['access']['user_id']==18){ 
		echo  '111'; echo '<pre>'; print_r(); echo '</pre>';
  } */

 class Com_pred{
        static function save_to_tbl($json){
		   global $mysqli;
	       global $user_id;
		   //echo $json;
		   $data_obj = json_decode($json);
           // print_r($data_obj);
		   // exit;
		   
		   //предварительно получаем данные о контактном лице прикрепленном к запросу
		   $query = "SELECT client_face_id FROM `".RT_LIST."` WHERE `query_num` = '".$data_obj->query_num."'"; 
           $result = $mysqli->query($query) or die($mysqli->error);
		   $row = $result->fetch_assoc();
		   $recipient_id = $row['client_face_id'];
			
		   // $data->ids - это двухмерный массив первый уровеннь которого содержит id строк из таблицы RT_MAIN_ROWS
		   // второй уровень содержит id дочерних строк из таблицы RT_DOP_DATA       
		   // проходим в цикле этот массив и поочередно копируем данные из таблиц РТ в таблицы КП
	
	
	       // записываем данные о КП в таблицу KP_LIST
		   $query="INSERT INTO `".KP_LIST."` 
							  SET 
							  `create_time` = NOW(),
							  `client_id` = '".$data_obj->client_id."',
							  `manager_id` = '".$user_id."',
							  `theme` = '".$data_obj->query_theme."',
							  `query_num` = '".$data_obj->query_num."',
							  `recipient_id` = '".$recipient_id."'
							  ";
		  		  
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return 2;
		   $kp_id = $mysqli->insert_id;
		   
		   
	       foreach($data_obj->ids as $key => $dop_data){
		       // преобразуем объект в массив
		       $dop_data = (array)$dop_data;
		       if(count($dop_data)==0) continue;
			   
			   
			   // прежде чем данные в таблицы КП сверим совпадают ли количество в расчетах и в услугах
			   // может получиться что они не совпадают ( было что-то не досохранено в РТ)
			   // для этого делаем предварительные запросы к таблицам RT_DOP_DATA и RT_DOP_USLUGI
			   foreach($dop_data as $dop_key => $dop_val){
			        // RT_DOP_DATA
			        $query_dop1 ="SELECT*FROM `".RT_DOP_DATA."` WHERE id = '".$dop_key."'";//echo $query;
				    $result_dop1 = $mysqli->query($query_dop1)or die($mysqli->error);
					
				    if($result_dop1->num_rows>0){
						 $row_dop1=$result_dop1->fetch_assoc();
		                 //RT_DOP_USLUGI
					     $query_dop2="SELECT*FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$row_dop1['id']."'";//echo $query;
						 $result_dop2 = $mysqli->query($query_dop2)or die($mysqli->error);
						 if($result_dop2->num_rows>0){
						      while($row_dop2=$result_dop2->fetch_assoc()){
							      $print_details_arr = json_decode($row_dop2['print_details'],TRUE);
								  
								  // echo '<pre>'; print_r($print_details_arr); echo '</pre>';
								  
								  // если calculator_type =='free' то непроверяем, isset($print_details_arr["calculator_type"]) 
								  // для совместимости с предыдущими версиями в которых $print_details_arr["calculator_type"] нет
								  $revoke = (isset($print_details_arr["calculator_type"]) && $print_details_arr["calculator_type"] =='free')?true:false;
								  if($row_dop2['glob_type']=='print' && !$revoke && ($row_dop2['quantity']!=$row_dop1['quantity'])){
								       $reload['flag'] = true;
									   //echo $dop_data['quantity'];
									   
									   include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
									   $json_out =  rtCalculators::change_quantity_and_calculators($row_dop1['quantity'],$row_dop1['id'],'true','false');
									   $json_out_obj =  json_decode($json_out);
									 
									   // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
									   // сбрасываем количество тиража и нанесения до 1шт.
									   if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
										   rtCalculators::change_quantity_and_calculators(1,$row_dop1['id'],'true','false');
										 
										   $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$row_dop1['id']."'";
										   $result = $mysqli->query($query)or die($mysqli->error);
									   }
						           }
								   if($row_dop2['glob_type']=='extra' && ($row_dop2['quantity']!=$row_dop1['quantity'])){
									   $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$row_dop1['quantity']."'  WHERE `id` = '".$row_dop2['id']."'";
									   $result = $mysqli->query($query)or die($mysqli->error);
						           }
							  }
						 }
			        }
			   }

			   if(isset($reload['flag']) && $reload['flag'] == true){
				   header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
				   exit;
			   }
			   
			   
			   // Вставляем ряд в таблицу KP_MAIN_ROWS
               $query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE id = '".$key."'";//echo $query;
               $rt_main_row_id = $key;

		       $result = $mysqli->query($query)or die($mysqli->error);
			   if($result->num_rows>0){
			       $row=$result->fetch_assoc();
				   
				   $description = ($row['description']!='')? 'описание: '.$row['description']:'';
				   
				   if($row['characteristics']!=''){
				       $arr =  json_decode($row['characteristics'],TRUE);
					   $ch_arr = array();
					   foreach($arr as $key => $data){
					       if($key == 'colors') $ch_arr[] = 'цвет: '.implode(', ',$data);
						   if($key == 'materials') $ch_arr[] = 'материал: '.implode(', ',$data);
					   }
				       $characteristics =(count($ch_arr)>0)? implode('<br>',$ch_arr):'';
				   }
				   else $characteristics = '';
				   
				   $query2="INSERT INTO `".KP_MAIN_ROWS."` 
							   SET 
							   `kp_id` = '".$kp_id."',
							   `sort` = '".$row['sort']."',
							   `art` = '".$row['art']."',
							   `type` = '".$row['type']."',
							   `art_id` = '".$row['art_id']."',
							   `name` = '".$row['name']."',
							   `description` = '".$row['description']."',
							   `characteristics` = '".mysql_real_escape_string($characteristics)."',
							   `img_folder` = '".(($row['img_type'] == 'g_std')?'img':$row['img_folder'])."'							   
							  ";


				   $result2 = $mysqli->query($query2)or die($mysqli->error);
				   $row_id = $mysqli->insert_id;
				   
				   // копируем выбранные для КП изображения
				   $query7 = "SELECT * FROM `".RT_MAIN_ROWS_GALLERY."` WHERE `parent_id` = '".$rt_main_row_id."';";
				   $result7 = $mysqli->query($query7)or die($mysqli->error);
 				
 					// echo $query7.'<br>';
 					// echo '<pre>';
 					// print_r($result7);
 					// echo '</pre>';
				   $choosen_img_arr = array();

	 				if($result7->num_rows > 0){
						while($row7 = $result7->fetch_assoc()){
			       			// $row7 = $result7->fetch_assoc();
							$choosen_img_arr[] = $row7;
						}
					}


					// echo '<pre>';
					// print_r($choosen_img_arr);
					// echo '</pre>';

					foreach ($choosen_img_arr as $key => $row7) {
							$query8 = "INSERT INTO `".KP_MAIN_ROWS_GALLERY."` 
							   SET 
							   `parent_id` = '".$row_id."',
							   `img_name` = '".$row7['img_name']."',
							   `folder` = '".$row7['folder']."',
							   `on` = '".$row7['on']."',
							   `sort` = '".$row7['sort']."'
							  ";
							  // echo $query8.'<br>';
							 $result8 = $mysqli->query($query8) or die($mysqli->error);
					}


				   // Проходим по второму уровню массива
				   foreach($dop_data as $dop_key => $dop_val){
					   //echo $dop_key.',';
					   // Вставляем ряд в таблицу KP_DOP_DATA
					   $query3="SELECT*FROM `".RT_DOP_DATA."` WHERE id = '".$dop_key."'";//echo $query;

					   $result3 = $mysqli->query($query3)or die($mysqli->error);
					   if($result3->num_rows>0){
						    $row3=$result3->fetch_assoc();
							
							if($row['type']!='cat'){
							    include_once(ROOT."/libs/php/classes/os_form_class.php");
							    include_once(ROOT."/libs/php/classes/cabinet/cabinet_class.php");//os_form_class.php
							    $cabinet = new Cabinet();
							    $details =  $cabinet->get_a_detailed_specifications($row['type'], $row3['no_cat_json']);
								$details =  strip_tags($details,'<div><br><br/><br />');
								$details =  str_replace(array('<div>','</div>'),array('<br>',''),$details);	
								$details =  str_replace(array("\n","\r","\t"),'',$details);			
								$details =  str_replace('<br><br>','<br>',$details);
								$details =  preg_replace('/<div[^<]+>/','',$details);
								$details =  $row['name'].'<br>'.$details;
								
							}
							else $details ='';
							
						    $query4=  "INSERT INTO `".KP_DOP_DATA."` 
									   SET 
									   `row_id` = '".$row_id."',
									   `expel` = '".$row3['expel']."',
									   `shipping_time` = '".$row3['shipping_time']."',
									   `shipping_date` = '".$row3['shipping_date']."',
									   `quantity` = '".$row3['quantity']."',
									   `price_in` = '".$row3['price_in']."',
									   `price_out` = '".$row3['price_out']."',
									   `discount` = '".$row3['discount']."',
									   `details` = '".$details."',
									   `dop_men_text_details` = '".$row['id']."|".$dop_key."',
									   `tirage_str` = '".Com_pred::convertTirageJSON($row3['tirage_json'])."' 
									  ";
		 			       $result4 = $mysqli->query($query4)or die($mysqli->error);
						   $dop_row_id = $mysqli->insert_id; 
						   
						   
						   // Вставляем ряд в таблицу KP_DOP_USLUGI
					       $query5="SELECT*FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$row3['id']."'";//echo $query;
						   $result5 = $mysqli->query($query5)or die($mysqli->error);
						   
						   if($result5->num_rows>0){
						       while($row5=$result5->fetch_assoc()){
							       $query6="INSERT INTO `".KP_DOP_USLUGI."` 
										    SET 
										   `dop_row_id` = '".$dop_row_id."',
										   `uslugi_id` = '".$row5['uslugi_id']."',
										   `glob_type` = '".$row5['glob_type']."',
										   `type` = '".$row5['type']."',
										   `quantity` = '".$row5['quantity']."',
										   `price_in` = '".$row5['price_in']."',
										   `price_out` = '".$row5['price_out']."',
										   `discount` = '".$row5['discount']."',
										   `for_how` = '".$row5['for_how']."',
										   `other_name` = '".$row5['other_name']."',
										   `print_details` = '".$row5['print_details']."' 
										   ";
										   
							       $result6 = $mysqli->query($query6)or die($mysqli->error);
							   }
						   }			  
		 		       }
				   }
			   }
		   }
	       return '1';
	   }
	   /*static function get_last_kp_num(){
	       global $mysqli;
	       $query="SELECT kp_num FROM `".COM_PRED_LIST."` GROUP BY kp_num ORDER BY id DESC";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		       $kp_num = $result->fetch_assoc();
		   }
		   return (!empty($kp_num['kp_num']))? ++$kp_num['kp_num']: 100000;
	   }*/
	   static function delete($kp_id){
 	       global $mysqli;
           // если таблицы будут содержать родительские записи, которые следует удалить, но для которых нет соответствующих дочерних записей, то           // ничего не получится. Инструкция WHERE не найдет соответствий для родительской записи в дочерней и, следовательно, не выберет
		   // родительскую запись для удаления. Чтобы обеспечить выбор и удаление родительской записи даже при отсутствии у нее дочерних записей,           // используйте LEFT JOIN:
           $query="DELETE list, main_rows, dop_data, uslugi
				                 FROM `".KP_LIST."` list
                                 LEFT  JOIN `".KP_MAIN_ROWS."` main_rows
								 ON list.id = main_rows.kp_id 
								 LEFT  JOIN `".KP_DOP_DATA."` dop_data 
								 ON main_rows.id = dop_data.row_id
								 LEFT  JOIN `".KP_DOP_USLUGI."` uslugi 
								 ON dop_data.id = uslugi.dop_row_id 
								 WHERE list.id = '".$kp_id."'";
		   $mysqli->query($query)or die($mysqli->error);
	   }
	   static function delete_old_version($file,$client_id,$id){
		
		  function delete_file_comment($file_name){
		      global $client_id;
		
		      $dir_name = 'data/com_offers/'.$client_id;
		      $file_path = $dir_name.'/comments.txt';
		      if(file_exists($file_path)){
			      $string_arr = file($file_path);

			      foreach($string_arr as $string){
			           $string = trim($string,"\r\n");
				       list($old_file_name,$old_comment)= explode(';',$string);
				       if($old_file_name != $file_name){
					        $file_content_arr[] = $old_file_name.';'.$old_comment;	
				       }
			      }
			      if(isset($file_content_arr)) $file_content = implode("\r\n",$file_content_arr);
			      else $file_content = '';
			 
			      $fd = fopen($file_path,'w');
	              $write_result = fwrite($fd,$file_content); //\r\n
	              fclose($fd);
		      }
		      else return;
	      }

	      $prefix = '../admin/order_manager/';
		  $file = urldecode($file);
	      unlink($prefix.'data/com_offers/'.trim($client_id).'/'.trim(iconv("UTF-8","windows-1251", $file)));
		  delete_file_comment($file);

	   }
	   static function change_comment($id,$comment){
	       global $mysqli;
		   
		   $query="UPDATE `".KP_LIST."` SET comment ='".$comment."'  WHERE id = '".(int)$id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
	   }
	   static function change_comment_old_version($file_name,$file_comment){
			global $client_id;
			
			//echo $file_name.$file_comment;
			$prefix = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/';
			
			
			
			$file_comment = strip_tags($file_comment,'<b><br /><a>');
			$file_comment = trim($file_comment," ");
			$file_comment = trim($file_comment,"\r");
			$file_comment = trim($file_comment,"\n");
			$dir_name = $prefix.'data/com_offers/'.$client_id;
			$file_path = $dir_name.'/comments.txt';
			if(file_exists($file_path)){
			
				$string_arr = file($file_path);
				$flag = 0;
				foreach($string_arr as $string){
					 $string = trim($string,"\r\n");
					 list($old_file_name,$old_comment)= explode(';',$string);
					 if($old_file_name == $file_name){
						  $file_content_arr[] = $old_file_name.';'.$file_comment;
						  $flag = 1;
					 }
					 else  $file_content_arr[] = $old_file_name.';'.$old_comment;
				}
				if($flag == 0) $file_content_arr[] = $file_name.';'.$file_comment;
				$file_content = implode("\r\n",$file_content_arr);  
				
				
			}
			else $file_content = $file_name.';'.$file_comment;
			
			$fd = fopen($file_path,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
	   }
	   static function fetch_theme($kp_id){
		    global $mysqli; 
			
			$query = "SELECT theme FROM `".KP_LIST."` WHERE id ='".$kp_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$row = $result->fetch_assoc();
			return $row['theme'];
		}

	   static function fetch_kp_rows($kp_id){
	       global $mysqli;
		   // выбираем из базы данных строки соответствующие данному КП
		   
		   $rows = array();
		 
		   $query = "SELECT list_tbl.recipient_id AS recipient_id ,list_tbl.display_setting AS display_setting ,list_tbl.display_setting_2 AS display_setting_2, main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art_id AS art_id ,main_tbl.art AS art ,main_tbl.name AS item_name ,main_tbl.characteristics AS characteristics, main_tbl.description AS description, main_tbl.img_folder AS img_folder, main_tbl.img AS img,	
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.expel AS expel,dop_data_tbl.shipping_date AS shipping_date,dop_data_tbl.shipping_time AS shipping_time,
dop_data_tbl.details AS details, dop_data_tbl.tirage_str AS tirage_str, dop_data_tbl.dop_men_text_details AS dop_men_text_details,		  
						  dop_uslugi_tbl.id AS uslugi_id ,dop_uslugi_tbl.uslugi_id AS dop_usluga_id ,dop_uslugi_tbl.other_name AS other_name , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out, dop_uslugi_tbl.discount AS uslugi_t_discount, dop_uslugi_tbl.for_how AS for_how, dop_uslugi_tbl.print_details AS print_details
		          FROM
		          `".KP_LIST."`  list_tbl 
				  LEFT JOIN  
		          `".KP_MAIN_ROWS."`  main_tbl  ON  list_tbl.id = main_tbl.kp_id
				  LEFT JOIN 
				  `".KP_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".KP_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE list_tbl.id ='".$kp_id."' ORDER BY main_tbl.sort";
				  
		   $result = $mysqli->query($query) or die($mysqli->error);
		   $multi_dim_arr = array();
		  
		   while($row = $result->fetch_assoc()){
		       if(!isset($multi_dim_arr[$row['main_id']])){
			       $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				   $multi_dim_arr[$row['main_id']]['art_id'] = $row['art_id'];
				   $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				   $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
				   $multi_dim_arr[$row['main_id']]['characteristics'] = $row['characteristics'];
				   $multi_dim_arr[$row['main_id']]['description'] = $row['description'];
				   $multi_dim_arr[$row['main_id']]['img_folder'] = $row['img_folder'];
				   $multi_dim_arr[$row['main_id']]['img'] = $row['img'];
				   $multi_dim_arr[$row['main_id']]['recipient_id'] = $row['recipient_id'];
				   $multi_dim_arr[$row['main_id']]['display_setting'] = $row['display_setting'];
				   $multi_dim_arr[$row['main_id']]['display_setting_2'] = $row['display_setting_2'];
				   
				   
			   }
			   //$multi_dim_arr[$row['main_id']]['uslugi_id'][] = $row['uslugi_id'];
			   if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			   
			   
			       $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'expel' => $row['expel'],
																	'shipping_date' => $row['shipping_date'],
																	'shipping_time' => $row['shipping_time'],
																	'tirage_str' => $row['tirage_str'],
																	'details' => $row['details'],
																	'dop_men_text_details' => $row['dop_men_text_details'],
																	'quantity' => $row['dop_t_quantity'],
																	'discount' => $row['dop_t_discount'],
																	'price_in' => $row['dop_t_price_in'],
																	'price_out' => $row['dop_t_price_out']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslugi_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslugi_id']] = array(
									'type' => $row['uslugi_t_type'],
									'usluga_id' => $row['dop_usluga_id'],
									'quantity' => $row['uslugi_t_quantity'],
									'price_in' => $row['uslugi_t_price_in'],
									'price_out' => $row['uslugi_t_price_out'],
									'discount' => $row['uslugi_t_discount'],
									'other_name' => $row['other_name'],
									'for_how' => $row['for_how'],
									'print_details' => $row['print_details']
									);
			}
		   
		 }
	     return $multi_dim_arr;
	   }
	   static function open_old_kp($show_old_kp){
	        $prefix = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/';
            $file_name = $prefix.'data/com_offers/'.$show_old_kp;
			
			//echo $file_name;
			$fd = fopen($file_name,"rb");
			$fcontent = fread($fd,filesize($file_name));
			$fcontent = str_replace('src="../..','src="',$fcontent);
			
			return $fcontent;
	   }
	   static function prepare_send_mail($kp_id,$client_id,$user_id){
	        // проверяем есть папка данного клента, если её нет то создаем её
	        $document_root = $_SERVER['DOCUMENT_ROOT'];
			$dirname = '/os/data/com_offers/'.strval(intval($_GET['client_id']));
			if(!file_exists($document_root.$dirname)){
				if(!mkdir($document_root.$dirname, 0777)){
					echo 'ошибка создания папки клиента (kp#1)'.$document_root.$dirname;
					exit;
				}
			}
			$dirname = $dirname.'/'.strval(intval($kp_id));
			if(!file_exists($document_root.$dirname)){
				if(!mkdir($document_root.$dirname, 0777)){
					echo 'ошибка создания папки клиента (kp#1)'.$document_root.$dirname;
					exit;
				}
			}
			
			//$filename = '/Пробный_ПДФ_в_кириллицe_'.$client_id.'_'.$kp_id.'_'.date('Ymd_His').'.pdf';
			$filename = '/Презентация_'.$client_id.'_'.date('Ymd_His').'.pdf';
			$filename_utf = iconv("UTF-8","windows-1251", $filename);
			$save_to = $document_root.$dirname.$filename_utf;
			
            self::save_in_pdf_on_server($kp_id,$client_id,$user_id,$save_to);
			return $dirname.$filename;
            exit;
	   }
	   static function clear_client_kp_folder($kp_id,$attached_files){
	        $dirname = ROOT.'/data/com_offers/'.strval(intval($_GET['client_id'])).'/'.strval(intval($kp_id));
	        if($files_arr = read_Dir($dirname)){
			    foreach($files_arr as $file){
				     $flag=TRUE;
				     foreach($attached_files as $attached_file){
					     $attached_file = substr($attached_file,strrpos($attached_file,"/")+1);
						 $file_utf = iconv("windows-1251","UTF-8", $file);
					     if($file_utf==$attached_file) $flag=FALSE;
					 }
					 if($flag) unlink($dirname.'/'.$file);
				}
			} 
	   }
	   static function save_mail_send_time($kp_id){
	        global $mysqli;
			 
			$query="UPDATE `".KP_LIST."` SET 	`send_time` = NOW() WHERE `id` = '".$kp_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
				//$row=$result->fetch_assoc();

	   }
	   static function save_in_pdf_on_server($kp_id,$client_id,$user_id,$filename){
	   
            $html = self::open_in_blank($kp_id,$client_id,$user_id,true);
			
			include(ROOT."/libs/php/mpdf60/mpdf.php");
			$mpdf=new mPDF();
			//$mpdf->SetHTMLHeader('<div style="height:80px;border:#000 solid 1px;"><img src="'.HOST.'/skins/images/img_design/spec_offer_top_plank_2.jpg"></div><br><br><br><br>'); 
			$mpdf->SetHTMLHeader('<img src="'.HOST.'/skins/images/img_design/spec_offer_top_plank_2.jpg">');
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'F');
	   }
	   static function save_in_pdf($kp_id,$client_id,$user_id,$filename = '1.pdf'){
	   
            $html = self::open_in_blank($kp_id,$client_id,$user_id,true);
		    //echo $html;
		    //exit;
			include(ROOT."/libs/php/mpdf60/mpdf.php");
            //$stylesheet = file_get_contents('style.css');
			$filename = 'Презентация_'.$client_id.'_'.date('Ymd_His').'.pdf';

	
			$mpdf=new mPDF();
			//$mpdf->WriteHTML($stylesheet,1);
			//$mpdf->SetHTMLHeader('<div style="height:80px;border:#000 solid 1px;"><img src="'.HOST.'/skins/images/img_design/spec_offer_top_plank_2.jpg"></div><br><br><br><br>'); 
			$mpdf->SetHTMLHeader('<img src="'.HOST.'/skins/images/img_design/spec_offer_top_plank_2.jpg">'); 
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'D');
			//$mpdf->Output();
            exit;
	   }
	    static function set_recipient($recipient,$row_id){
            global $mysqli;
			 
			$query="UPDATE `".KP_LIST."` SET `recipient_id` ='".$recipient."' WHERE `id` = '".$row_id."'";
			$mysqli->query($query)or die($mysqli->error);
	   }
	   static function open_in_tbl($kp_id){
	       $arr=self::fetch_kp_rows($kp_id);
		  
           //echo '<pre>';print_r($arr);echo '</pre>';
		   //exit;	

		 $glob_counter = 0;
	     $mst_btn_summ = 0;
		 $service_row[0] = array('quantity'=>'','price_in'=>'','price_out'=>'','row_status'=>'','glob_status'=>'');
	     foreach($arr as $key => $row){
			 $glob_counter++;
			 // Проходим по первому уровню и определям некоторые моменты отображения таблицы, которые будут применены при проходе по второму
			 // уровню массива, ряды таблицы будут создаваться там
			 
			 // если товарная позиция имеет больше одного варианта расчета вставляем пустой ряд вверх
			 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
			 if(isset($row['dop_data']) && count($row['dop_data'])>1){
			      $row['dop_data']= $service_row + $row['dop_data']; 
			 }
			 
			 
			 // здесь мы определяем значение для атрибута rowspan тегов td которые будут выводится единой ячейкой для всей товарной позиции
			 $row_span = count($row['dop_data']);
			 $counter=0;
			
			  
			 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>---';
			 // Проходим в цикле по второму уровню массива($row['dop_data']) на основе которого строится основной шаблон таблицы
			 foreach($row['dop_data'] as $dop_key => $dop_row){
				 // определяем какие расчеты будут учитываться в конечных суммах а какие нет и их отображение в таблице
				 // json_decode($row['details']);
				 if($dop_key!=0){ 
					 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
					 if(@$dop_row['expel']!=''){
						$obj = @json_decode($dop_row['expel']);
						foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
					 }
					 //echo '<br />'; print_r($expel);
					 
					 // работаем с информацией о дополнительных услугах определяя что будет выводиться и где
					 // 1. определяем данные описывающие варианты нанесения логотипа, они хранятся в $dop_row['dop_uslugi']['print']
					 if(isset($dop_row['dop_uslugi']['print'])){ // если $dop_row['dop_uslugi']['print'] есть выводим данные о нанесениях 
						 $summ_in = $summ_out = array();
						 foreach($dop_row['dop_uslugi']['print'] as $extra_data){
							 $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
							 $extra_data['price_out'] = ($extra_data['discount'] != 0 )? (($extra_data['price_out']/100)*(100 + $extra_data['discount'])) : $extra_data['price_out'] ;
							 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
						 }
						 $print_btn = '<span>'.count($dop_row['dop_uslugi']['print']).'</span>'; 
						 $print_in_summ = array_sum($summ_in);
						 $print_out_summ = array_sum($summ_out);
					 }
					 else{// если данных по печати нет то проверяем - выводим кнопку добавление нанесения
						 $print_btn = '<span>+</span>';
						 $print_in_summ = 0;
						 $print_out_summ = 0;
					 }
					 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
					 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
						 $summ_in = $summ_out = array();
						 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
						 
							 $summ_in[] = ($extra_data['for_how']=='for_all')? $extra_data['price_in']:$extra_data['quantity']*$extra_data['price_in'];
						     $extra_data['price_out'] = ($extra_data['discount'] != 0 )? (($extra_data['price_out']/100)*(100 + $extra_data['discount'])) : $extra_data['price_out'] ;
							 
							 $summ_out[] = ($extra_data['for_how']=='for_all')? $extra_data['price_out']:$extra_data['quantity']*$extra_data['price_out'];
						 }
						 $dop_uslugi_btn =  '<span>'.count($dop_row['dop_uslugi']['extra']).'</span>';
						 $dop_uslugi_in_summ = array_sum($summ_in);
						 $dop_uslugi_out_summ = array_sum($summ_out);
					 }
					 else{
					     $dop_uslugi_btn = '<span>+</span>';
						 $dop_uslugi_in_summ = 0;
						 $dop_uslugi_out_summ = 0;
					 }
					 
					 // подсчет сумм в ряду
				     $price_out = ($dop_row['discount'] != 0 )? (($dop_row['price_out']/100)*(100 + $dop_row['discount'])) : $dop_row['price_out'] ;
						 
					 // 1. подсчитываем входящую сумму
					 $price_in_summ = $dop_row['quantity']*$dop_row['price_in'];
					 $in_summ = $price_in_summ;
					 if(!(!!$expel["print"]))$in_summ += $print_in_summ;
					 if(!(!!$expel["dop"]))$in_summ += $dop_uslugi_in_summ;
					 // 2. подсчитываем исходящую сумму 
					 $price_out_summ =  $dop_row['quantity']*$price_out;
					 $out_summ =  $price_out_summ;
					 if(!(!!$expel["print"]))$out_summ += $print_out_summ;
					 if(!(!!$expel["dop"]))$out_summ += $dop_uslugi_out_summ;
					 
					 $delta = $out_summ-$in_summ; 
					 $margin = $out_summ-$in_summ;
					 
					 $price_in_summ_format = number_format($price_in_summ,'2','.','');
					 $price_out_summ_format = number_format($price_out_summ,'2','.','');
					 $print_in_summ_format = number_format($print_in_summ,'2','.','');
					 $print_out_summ_format = number_format($print_out_summ,'2','.','');
					 $dop_uslugi_in_summ_format = number_format($dop_uslugi_in_summ,'2','.','');
					 $dop_uslugi_out_summ_format = number_format($dop_uslugi_out_summ,'2','.','');
					 $in_summ_format = number_format($in_summ,'2','.','');
					 $out_summ_format = number_format($out_summ,'2','.','');
					 $delta_format = number_format($delta,'2','.','');
					 $margin_format = number_format($margin,'2','.','');
			
					 
					 // если ряд не исключен из расчетов добавляем значения в итоговый ряд
					 if(!(!!$expel["main"])){
						 @$total['price_in_summ'] += $price_in_summ;
						 @$total['price_out_summ'] += $price_out_summ;
						 if(!(!!$expel["print"])) @$total['print_in_summ'] += $print_in_summ;
						 if(!(!!$expel["print"])) @$total['print_out_summ'] += $print_out_summ;
						 if(!(!!$expel["dop"])) @$total['dop_uslugi_in_summ'] += $dop_uslugi_in_summ;
						 if(!(!!$expel["dop"])) @$total['dop_uslugi_out_summ'] += $dop_uslugi_out_summ;
						 @$total['in_summ'] += $in_summ;
						 @$total['out_summ'] += $out_summ;
					 }
					 $img_design_path = HOST.'/skins/images/img_design/';
					 $currency = 'р';
					 $quantity_dim = 'шт';
					 $discount = $dop_row['discount'].'%';
					 $srock_sdachi = implode('.',array_reverse(explode('-',$dop_row['shipping_date'])));
					 if($srock_sdachi=='00.00.0000') $srock_sdachi='';
					 
					 $expel_class_main = ($expel['main']=='1')?' red_cell':'';
					 $expel_class_print = ($expel['print']=='1')?' red_cell':'';
					 $expel_class_dop = ($expel['dop']=='1')?' red_cell':'';
					
				}
				else{
			        $expel = array ("main"=>0,"print"=>0,"dop"=>0);
					 $currency = $print_btn = $dop_uslugi_btn = '';
					 $price_out = $price_in_summ_format = $price_out_summ_format = $print_in_summ_format = $print_out_summ_format = '';
					 $dop_uslugi_in_summ_format = $dop_uslugi_out_summ_format = $in_summ_format = $out_summ_format = '';
					 $delta_format = $margin_format = $expel_class_main = $expel_class_print = $expel_class_dop = $quantity_dim = $discount = $srock_sdachi = $print_exists_flag ='';
				 
				  
			   }
			   
			   $men_text_details_arr  = (isset($dop_row['dop_men_text_details']) && $dop_row['dop_men_text_details']!='')? explode('|',$dop_row['dop_men_text_details']):array(0,0); 
				 
		  if($row['row_type'] == 'cat'){ 
				 $extra_panel = '<div class="pos_plank cat">
								   <a href="?page=client_folder&section=rt_position&id='.$key.'">'.$row['art'].'</a>
								   <div class="pos_link_plank">
									  <div class="catalog">
										  <a id="" href="/?page=description&id='.$row['art_id'].'" target="_blank" onmouseover="change_href(this);return false;">APL</a>
									  </div>
									  <div class="supplier">
										   '.identify_supplier_by_prefix($row['art']).'
									  </div>
								   </div>
								 </div>
								 <div>'.$row['name'].'</div>';
			 }
			 else{
			     $extra_panel = '<div class="pos_plank pol">
								   <a href="?page=client_folder&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 </div>';
			 }
				 
			 
				 
			 $cur_row  =  '';
		     $cur_row .=  '<tr '.(($counter==0)?'pos_id="'.$key.'" type="'.$row['row_type'].'"':'').' row_id="'.$dop_key.'" art_id="'.$row['art_id'].'" class="'.(($key>1 && $counter==0)?'pos_edge ':'').(((count($row['dop_data'])-1)==$counter)?'lowest_row_in_pos ':'').'">';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="glob_counter" class="top glob_counter" width="30">'.$glob_counter.'</td>':'';

		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$dop_key.'</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$row['row_type'].'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" width="270" type="name" class="top">'.$extra_panel.'</td>':'';
			 $cur_row .=  '<td width="30" style="position:relative;"><div class="comment_div" data-href="?page=client_folder&section=rt_position&id='.$men_text_details_arr[0].'&client_id='.$_GET['client_id'].'" data-id="'.$men_text_details_arr[1].'"></div></td>
			               <td width="60" class="right">'.$dop_row['quantity'].'</td>
						   <td width="20" class="r_border left quantity_dim">'.$quantity_dim.'</td>
						   <td width="90" type="price_in" connected_vals="art_price" c_stat="1" class="in right">'.$dop_row['price_in'].'</td>
						   <td width="15" connected_vals="art_price" c_stat="1" class="currency left">'.$currency.'</td>
						   <td width="90" type="price_in_summ" connected_vals="art_price" c_stat="0" class="in right hidden">'.$price_in_summ_format.'</td>
						  
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left hidden">'.$currency.'</td>
						   <td width="40" class="center">'.$discount.'</td>
						   <td width="90"  connected_vals="art_price" c_stat="1" class="out right">'.$price_out.'</td>
						   <td width="15" class="currency left r_border" connected_vals="art_price" c_stat="1" >'.$currency.'</td>
						   <td width="90" connected_vals="art_price" c_stat="0" class="out right hidden">'.$price_out_summ_format.'</td>
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left r_border hidden">'.$currency.'</td>
						   <td width="25" class="calc_btn_no_active">'.$print_btn.'</td>
			               <td width="80" connected_vals="print" c_stat="0" class="test_data in hidden '.$expel_class_print.'">'.$print_in_summ_format.$currency.'</td> 
			               <td width="80" connected_vals="print" c_stat="1" class="out '.$expel_class_print.'">'.$print_out_summ_format.$currency.'</td>
			               <td width="25" class="calc_btn_no_active">'.$dop_uslugi_btn.'</td>';
			 $cur_row .=  '<td width="80" connected_vals="uslugi" c_stat="0" class="test_data r_border in hidden '.$expel_class_dop.'">'.$dop_uslugi_in_summ_format.$currency.'</td>';
			 $cur_row .=  '<td width="80" connected_vals="uslugi" c_stat="1"  class="out r_border '.$expel_class_dop.'">'.$dop_uslugi_out_summ_format.$currency.'</td>
						   <td width="100" connected_vals="total_summ" c_stat="0" class="in right hidden '.$expel_class_main.'">'.$in_summ_format.'</td>
						   <td width="15" connected_vals="total_summ" c_stat="0" class="currency hidden r_border '.$expel_class_main.'">'.$currency.'</td>
						   <td width="100" connected_vals="total_summ" c_stat="1" class="out right '.$expel_class_main.'">'.$out_summ_format.'</td>
						   <td width="15" connected_vals="total_summ" c_stat="1" class="currency r_border left '.$expel_class_main.'">'.$currency.'</td>
						   <td width="70" class="grey r_border center">'.$srock_sdachi.'</td>
						   <td width="80" type="delta" class="right">'.$delta_format.'</td>
						   <td width="10" class="left">'.$currency.'</td>
						   <td width="80" type="margin" class="right">'.$margin_format.'</td>
						   <td width="10" class="left">'.$currency.'</td>
						   <td stretch_column>&nbsp;</td>';
			 $cur_row .=  '<td ></td>';  
			 $cur_row .= '</tr>';
				
				 
				 // загружаем сформированный ряд в итоговый массив
				 $tbl_rows[]= $cur_row;
				 $counter++;
				 
			 }
		}
		
		$rt = '<table class="rt_tbl_head" id="rt_tbl_head" scrolled="head" style="width: 100%;" border="0">
	          <tr class="w_border cap">
			      <td width="30"></td>
	              <td class="hidden"></td>
				  <td class="hidden">тип</td>
				  <td width="270" class="right"></td>
				  <td width="30"></td>
				  <td width="60" class="right">тираж</td>
				  <td width="20" class="r_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="grey w_border  right pointer">$ товара<br /><span class="small">входящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="grey w_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="grey w_border right hidden pointer">$ товара<br /><span class="small">входящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="grey w_border hidden"></td>
				  <td width="40" class="grey w_border">наценка</td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="grey w_border right pointer">$ товара<br /><span class="small">исходящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="grey w_border r_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="grey w_border right pointer hidden">$ товара<br /><span class="small">исходящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="grey w_border r_border hidden"></td>
				  <td width="25"></td>
	              <td width="80" connected_vals="print" c_stat="0" class="pointer hidden">$ печать<br /><span class="small">входящая тираж</span></td> 	  
			      <td width="80" connected_vals="print" c_stat="1" class="pointer">$ печать<br /><span class="small">исходящая тираж</span></td>
			      <td width="25"></td>';
           $rt.= '<td width="80"  connected_vals="uslugi" c_stat="0" class="pointer r_border hidden">$ доп. услуги<br /><span class="small">входящая тираж</span></td> 
			      <td width="80"  connected_vals="uslugi" c_stat="1" class="out pointer r_border">$ доп. услуги<br /><span class="small">исходящая тираж</span></td>
				  <td width="100" connected_vals="total_summ" c_stat="0" class="pointer hidden center">итого<br /><span class="small">входящая</span></td>
				  <td width="15" connected_vals="total_summ" c_stat="0" class="hidden r_border"></td>
				  <td width="100" connected_vals="total_summ" c_stat="1" class="pointer center">итого<br /><span class="small">исходящая</span></td>
				  <td width="15" connected_vals="total_summ" c_stat="1" class="r_border"></td>
				  <td width="70" class="center grey r_border">срок сдачи</td>
				  <td width="80" class="center">delta</td>
				  <td width="10"></td>
				  <td width="80"  class="center">маржина-<br />льность</td>
				  <td width="10"></td>
				  <td stretch_column>&nbsp;</td>
                  <td width="70">статус</td>';              
	    $rt.= '</tr>
	           <tr row_id="total_row" class="grey bottom_border">
			      <td width="30" height="18"></td>
	              <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td></td>
				  <td class="right"></td>
				  <td></td>
				  <td width="20" class="r_border"></td>
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1"></td>
				  <td type="price_in_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format(@$total['price_in_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="hidden">р</td>
				  <td width="40" class=""></td>
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="r_border"></td>
				  <td type="price_out_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format(@$total['price_out_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden">р</td>
				  <td></td>
	              <td type="print_in_summ" connected_vals="print" c_stat="0" class="hidden">'.number_format(@$total['print_in_summ'],'2','.','').'р</td> 		  
			      <td type="print_out_summ" connected_vals="print" c_stat="1">'.number_format(@$total['print_out_summ'],'2','.','').'р</td>
			      <td></td>';
           $rt.= '<td width="80" type="dop_uslugi_in_summ" connected_vals="uslugi" c_stat="0"  class="r_border hidden">'.number_format(@$total['dop_uslugi_in_summ'],'2','.','').'р</td> 
			      <td width="80" type="dop_uslugi_out_summ" connected_vals="uslugi" c_stat="1" class="out r_border">'.number_format(@$total['dop_uslugi_out_summ'],'2','.','').'р</td>
			      <td width="100" type="in_summ" connected_vals="total_summ" c_stat="0" class="right hidden">'.number_format(@$total['in_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="total_summ" c_stat="0" class="left hidden r_border">р</td>
				  <td width="100" type="out_summ" connected_vals="total_summ" c_stat="1" class="right">'.number_format(@$total['out_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="total_summ" c_stat="1" class="left r_border">р</td>
				  <td width="70" class="grey r_border"></td>
				  <td width="80" type="delta" class="right">'.number_format((@$total['out_summ']-@$total['in_summ']),'2','.','').'</td>
				  <td width="10" class="left">р</td>
				  <td width="80" type="margin" class="right">'.number_format((@$total['out_summ']-@$total['in_summ']),'2','.','').'</td>
				  <td width="10" class="left">р</td>
				  <td stretch_column></td>
                  <td></td>';        
	  
		   $rt.= '</tr>
				  </table>
				  <div id="scrolled_part_container" class="scrolled_tbl_movable_part">
				  <table class="rt_tbl_body" id="rt_tbl_body" scrolled="body" border="0">'.implode('',$tbl_rows).'</table>
				  </div>';

		   return $rt;	
		   	
	   }
	    static function open_in_blank($kp_id,$client_id,$user_id,$save_on_disk = false){
	        global $mysqli;
			
		    // Здесь делаем то что в старой версии делали при сохранении КП в файл
			
	
			// Данные из РТ 
			$multi_dim_arr=self::fetch_kp_rows($kp_id);
			// echo '<pre>';print_r($multi_dim_arr);echo '</pre>';//exit;
			  /*if(@$_SESSION['access']['user_id']==18){ 
			 		echo '<pre>';print_r($multi_dim_arr);echo '</pre>';
			  } */
			
			// Настройки отображения состовляющих КП
			$display_setting = $multi_dim_arr[key($multi_dim_arr)]['display_setting'];
			$display_setting_2 = $multi_dim_arr[key($multi_dim_arr)]['display_setting_2'];

			$dispSetObj = json_decode($display_setting);
			

			
			$itogo=$itogo_print_uslugi=$itogo_extra_uslugi=0;
			$itogo_print_uslugi1 = $itogo_print_uslugi2= $itogo_print_uslugi3=0;
			$itogo_extra_uslugi1 = $itogo_extra_uslugi2= $itogo_extra_uslugi3=0;




			// echo '<pre>';
			// print_r($multi_dim_arr);
			// echo '</pre>';
			// Разворачиваем массив 
			foreach($multi_dim_arr as $pos_key => $pos_level){
			   
				// РАБОТАЕМ С ПЕРВОЙ ЯЧЕЙКОЙ РЯДА ТАБЛИЦЫ КП
				// в этой ячейке подразумевается отображение картинки товарной позиции
				// соответсвенное если есть что показывать, то добавляем тег img, если нет то  добавляем пустую строку
				$img_src = 'http://www.apelburg.ru/img/no_image.jpg';
				$img_cell = '<img src="'.$img_src.'" height="180" width="180">';
				
				$art_img = new  Art_Img_development($pos_key,$pos_level['img_folder'],$pos_level['img'], $pos_level['art']);
				
				// $img_src = $art_img->big;
				// echo '<pre>';
				// print_r($art_img);
				// echo '</pre>';
				// меняем размер изображения
				$img_cell = '';
				foreach ($art_img->big as $key => $img_src) {
					$size_arr = transform_img_size($img_src,230,300);
					// $size_arr = array(230,300);
					$img_cell .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$size_arr[1].'">';
				}
				
				// $img_cell .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$size_arr[1].'">';

				// $img_cell .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$size_arr[1].'">';
				

				
				// if($pos_level['row_type']=='cat'){ // если позиция из каталога получаем картинку из базы данных каталога
				// 	$art_img = new  Art_Img($pos_level['art']);
				// 	// проверяем наличие изображения
				// 	$img_path = 'http://www.apelburg.ru/img/'.$art_img->big;
				// 	if($img_src = checkImgExists($img_path)){	
				// 		// меняем размер изображения
				// 		$size_arr = transform_img_size($img_src,230,300);
				// 		// $size_arr = array(230,300);
				// 		$img_cell = '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$size_arr[1].'">';
				// 	}
    //             }	
                

							
				
				// РАБОТАЕМ СО ВТОРОЙ ЯЧЕЙКОЙ РЯДА ТАБЛИЦЫ КП
				
				
				// наименование товарной позиции
				// форматируем вывод, разбиваем строки на куски определенной длины и вставляем перед каждым отступ
				$str_len = 40;
				$pos_name = $pos_level['name'];
				$pos_name = nl2br($pos_name);
				
				
				
				if($save_on_disk && isset($dispSetObj->art)){
				   $article = '';
				}
				else if($pos_level['row_type']!='cat'){
				   $article = '';
				}
				else{
				   $article = '<managedDisplay name="art" style="display:'.(isset($dispSetObj->art)?'none':'inline-block').'">арт.: <a href="/description/'.@$pos_level['art_id'].'/" target="_blank">'.@$pos_level['art'].'</a></managedDisplay>';
				}
				
			
				// НАЧИНАЕМ ПЕРЕБИРАТЬ ДАННЫЕ РАСЧЕТОВ
				// примечание - у позиции может быть любое количество расчетов( а каждый расчет в свою очередь может содержать
				//  любое количество нанесений и доп услуг)
				foreach($pos_level['dop_data'] as $r_key => $r_level){ 
					
				    if($pos_level['row_type']!='cat' && isset($r_level['details'])){
				       $pos_name = $r_level['details'];
				    }
				
				    $all_print_summ=$all_extra_summ=0;
					
				    //$r_ - сокращение обозначающее - уровень Расчёта позиции
					
					// стоимост артикула в данном расчете (без нанесения и услуг)
					// чтобы в дальнейшем не было проблем с делением преобразуем $quantity в 1 если оно равно 0
					$quantity = ($r_level['quantity']==0)? 1 :$r_level['quantity'];
					// стоимость
					$price = ($r_level['discount'] != 0 )? round(($r_level['price_out']/100)*(100 + $r_level['discount']),2) :  $r_level['price_out'] ;
					
					$summ = $quantity*$price;
					$itogo+=$summ;
			        
					// добавляем данные в  содержимое ячейки<td><div contenteditable="true" class="saveKpPosDescription"  pos_id="'.$pos_key.'">'.$pos_name.'</div></td>
					$description_cell = '<div style="margin-top:5px;"><b>Сувенир:</b></div>
					<table border="0" style="font-family:arial;font-size:13px;" tbl="managed">
					  <tr>
						<td style="width:6px;"></td>
						<td style="width:400px;" managed="text" bd_row_id="'.(($pos_level['row_type']=='cat')? $pos_key:$r_key).'" bd_field="'.(($pos_level['row_type']=='cat')?'name':'details').'" action="changeKpPosDescription">'.$pos_name.'</td>
					  </tr>
					  <tr>
						<td style="width:6px;"></td>
						<td>'.$article.'</td>
					  </tr>
					  </table>';
					  if(!($save_on_disk && isset($dispSetObj->sizes))) $description_cell .= '<managedDisplay name="sizes" style="display:'.((!isset($dispSetObj->sizes) && $r_level['tirage_str']!='')?'inline-block':'none').'">
						 <table border="0" style="font-family:arial;font-size:13px;" tbl="managed">
						  <tr>
							<td style="width:6px;"></td>
							<td style="width:400px;" managed="text" bd_row_id="'.$r_key.'" action="changeKpPosDescription" bd_field="tirage_str">'.$r_level['tirage_str'].'</td>
						  </tr>
						  </table>
					  </managedDisplay>';
                      if(!($save_on_disk && isset($dispSetObj->characteristics))) $description_cell .= '<managedDisplay name="characteristics" style="display:'.((!isset($dispSetObj->characteristics) && $pos_level['characteristics']!='')?'inline-block':'none').'">
						 <table border="0" style="font-family:arial;font-size:13px;" tbl="managed">
						  <tr>
							<td style="width:6px;"></td>
							<td style="width:400px;" managed="text" bd_row_id="'.$pos_key.'" action="changeKpRepresentedData" bd_field="characteristics">'.$pos_level['characteristics'].'</td>
						  </tr>
						  </table>
					  </managedDisplay>';
					  if(!($save_on_disk && isset($dispSetObj->description))) $description_cell .= '<managedDisplay name="description" style="display:'.((!isset($dispSetObj->description) && $pos_level['description']!='')?'inline-block':'none').'">
					   <table border="0" style="font-family:arial;font-size:13px;" tbl="managed">
						  <tr>
							<td style="width:6px;"></td>
							<td style="width:400px;" managed="text" bd_row_id="'.$pos_key.'" action="changeKpRepresentedData" bd_field="description">'.$pos_level['description'].'</td>
						  </tr>
						</table>
					</managedDisplay>';
					 $description_cell .= '<table style="font-family:arial;font-size:13px;margin-top:10px;width:100%;border-collapse:collapse;width:350px;table-layout:_fixed;" border="0">
					  <tr>
						<td align="right" style="width:250px;color:#888;">1шт.</td>
						<td align="right" style="width:70px;padding:0 5px;"><nobr>'.number_format($price,2,'.',' ').'</nobr></td>
						<td align="left" style="width:30px;">руб.</td>
					  </tr>
					  <tr>
						<td align="right" style="color:#888;">тираж: '.$quantity.' шт. </td>
						<td align="right" style="padding:0 5px;"><nobr>'.number_format($summ,2,'.',' ').'</nobr></td>
						<td align="left">руб.</td>
					  </tr>
					</table>';
					
				    // РАБОТАЕМ С НАНЕСЕНИЯМИ И ДОП УСЛУГАМИ СОБИРАЕМ В ЕДИННЫЕ БЛОКИ ДЛЯ ПОСЛЕДУЮЩЕЙ СБОРКИ
					$print_block = $details_block = array();
					$counter = 0;
					// если есть нанесение
					if(isset($r_level['dop_uslugi']['print'])){
					    $show_count = (count($r_level['dop_uslugi']['print'])>1)?true:false;
						$counter2 = 0;
						
						foreach($r_level['dop_uslugi']['print'] as $u_key => $u_level){
						   
							
						    if($u_level['print_details']=='') continue;

							$print_details_obj = json_decode($u_level['print_details']);
							if($print_details_obj == NULL) continue;

							$print_details_arr = json_decode($u_level['print_details'],TRUE);
							
							// if(@$_SESSION['access']['user_id']==42){ 
							// 	echo '<pre>';
							// 	print_r($u_level);
							// 	echo '</pre>';
							// }
							 
							//$quantity = (isset($print_details_obj->calculator_type) && $print_details_obj->calculator_type == 'free')?$u_level['quantity']:$quantity;
							$quantity = (isset($print_details_arr['calculator_type']) && $print_details_arr['calculator_type'] == 'free')?$u_level['quantity']:$quantity;
							
							if(isset($print_details_arr['quantity']) && (int)$print_details_arr['quantity'] > 0 && $print_details_arr['quantity'] != $quantity){
								$quantity = $print_details_arr['quantity'];
							}

							if(isset($print_details_arr['dop_params']['sizes'])){
							    if($print_details_arr['dop_params']['sizes'][0]['type'] == 'coeff'){
								    $size_coeff = (isset($print_details_arr['dop_params']['sizes'][0]['val']) && $print_details_arr['dop_params']['sizes'][0]['val']!=0)?$print_details_arr['dop_params']['sizes'][0]['val']: 1 ;
								}
								if($print_details_arr['dop_params']['sizes'][0]['type'] == 'addition'){
								    $size_coeff = (isset($print_details_arr['dop_params']['sizes'][0]['val']))?$print_details_arr['dop_params']['sizes'][0]['val']: 0 ;
									$size_coeff =($print_details_arr['dop_params']['sizes'][0]['target']=='summ')?round($size_coeff/$quantity,2):$size_coeff;
								}
							}
							else $size_coeff = false;
							
							$new_price_arr['price_in'] = $u_level['price_in'];
							$new_price_arr['price_out'] = ($u_level['discount'] != 0 )? ($u_level['price_out']/100)*(100 + $u_level['discount']) :  $u_level['price_out'] ;
						
							
							if(isset($print_details_obj->dop_params)){
							    include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
							    $calculations = rtCalculators::make_calculations($quantity,$new_price_arr,$print_details_obj->dop_params);
							}
							// echo  '<pre>'; print_r($new_price_arr); echo '</pre>';  //
                            /*if(@$_SESSION['access']['user_id']==18){ 
									echo '<pre>';print_r($calculations);echo '</pre>';
							} */
							// наименование нанесения
							include_once(ROOT."/libs/php/classes/print_calculators_class.php");
							$print_data = printCalculator::convert_print_details_for_kp($u_level['print_details']);
                            /*if(@$_SESSION['access']['user_id']==18){ 
									echo '<pre>';print_r($print_data);echo '</pre>';
							} */
							// Собираем данные для print_block (Печать логотипа)
							$print_block[] = '<table border="0" style="font-family:arial;font-size:13px;right;margin:15px 0 0 11px;width:100%;border-collapse:collapse;width:350px;table-layout:fixed;">';
							$print_block[] = '<tr><td valign="top" style="width:90px;">Вид '.(($show_count)?(++$counter2).': ':'').' </td><td style="width:170px;">'.$print_data['block1']['print_type'].'</td></tr>';
						    if(!isset($print_details_obj->calculator_type) || (isset($print_details_obj->calculator_type) && $print_details_obj->calculator_type != 'free')) $print_block[] = '<tr><td valign="top">Место нанесения: </td><td>'.$print_data['block1']['place_type'].'</td></tr>';
							if(isset($print_details_obj->calculator_type) && $print_details_obj->calculator_type == 'free') $print_block[] = '<tr><td valign="top"></td><td>'.base64_decode($print_details_obj->commentForClient).'</td></tr>';
							
							if(isset($print_data['block1']['price_data']['y_params'])){
								 $print_block[] = '<tr><td valign="top">'.$print_data['block1']['price_data']['cap'].': </td><td>'.count($print_data['block1']['price_data']['y_params']).' ('.implode(', ',$print_data['block1']['price_data']['y_params']).')</td></tr>';
								$y_params_count = count($print_data['block1']['price_data']['y_params']);
								$y_params_coeff = 0;
								foreach($print_details_arr['dop_params']['YPriceParam']as $y_data){
								    if($y_data['coeff']==0) $y_data['coeff'] =1;
									$y_params_coeff += $y_data['coeff']-1;
								}
							}
							if(isset($print_data['block1']['print_size'])){
							    // если тип нанесения Тампопечать ( id =18 ) - то тогда не отображаем площать печати
							    if($u_level['usluga_id'] != 18) $print_block[] = '<tr><td valign="top">Площадь печати: </td><td>'.$print_data['block1']['print_size'].'</td></tr>'; 
								//echo  '<pre>--2--'; print_r($print_details_arr['dop_params']); echo '</pre>';
							}
							if(isset($print_data['block2']['data'])){
							    foreach($print_data['block2']['data'] as $block2_data){
								     $print_block[] = '<tr><td valign="top" colspan="2">'.$block2_data['name'].'</td></tr>'; 
								}
							}
							$print_block[] = '</table>';
							
							$print_block_price = $new_price_arr['price_out'];
							$print_block_price1= $new_price_arr['price_out'];
							
							if($display_setting_2==0){ // вариант 1
							    
							    // коэффициент площади
								/*if($size_coeff!==false){
									if($print_details_arr['dop_params']['sizes'][0]['type'] == 'coeff'){
									    
										$print_block_price = $new_price_arr['price_out']*$size_coeff;
									}
									if($print_details_arr['dop_params']['sizes'][0]['type'] == 'addition'){
										$print_block_price = $new_price_arr['price_out']+$size_coeff;
										 
									}
								}
								
								// добавить коэфф цвета если есть
								if(isset($y_params_count) && $y_params_count>0){
								    $y_params_coeff = (isset($y_params_coeff))?$y_params_coeff:1;
								    $print_block_price += ($new_price_arr['price_out']/$y_params_count)*$y_params_coeff;
								}*/
								$print_block_summ = $quantity*$print_block_price;
								 
								$all_print_summ+= $quantity*$new_price_arr['price_out'];
							    $itogo_print_uslugi += $print_block_summ;
								//$itogo_extra_uslugi += $calculations['new_summs']['summ_out'] - $print_block_summ;

							}
							else if($display_setting_2==1){ // вариант 2
							    $print_block_price = $new_price_arr['price_out'];
							    $print_block_summ = $quantity*$new_price_arr['price_out'];
								
								$all_print_summ+=$calculations['new_summs']['summ_out'];
							    $itogo_print_uslugi += $print_block_summ;
								//$itogo_extra_uslugi += $calculations['new_summs']['summ_out'] - $print_block_summ;
								
							    
							}
							else if($display_setting_2==2){ // вариант 3
								
								$print_block_price = $new_price_arr['price_out'];
							    $print_block_summ = $quantity*$new_price_arr['price_out'];
								
								$all_print_summ+=$calculations['new_summs']['summ_out'];
							    $itogo_print_uslugi += $calculations['new_summs']['summ_out'];
							}
							
							//////////////////////////////////////////////////////////////////////////////
					        //////////////////////////////////////////////////////////////////////////////
							// коэффициент площади
							/*if($size_coeff!==false){
								if($print_details_arr['dop_params']['sizes'][0]['type'] == 'coeff'){
									$print_block_price1 = $new_price_arr['price_out']*$size_coeff;
								}
								if($print_details_arr['dop_params']['sizes'][0]['type'] == 'addition'){
									$print_block_price1 = $new_price_arr['price_out']+$size_coeff;
									 
								}
							}
								
							//$size_coeff = (isset($size_coeff))?$size_coeff:1;
							//$print_block_price1 = $new_price_arr['price_out']*$size_coeff;
							
							// добавить коэфф цвета если есть
							if(isset($y_params_count) && $y_params_count>0){
								$y_params_coeff = (isset($y_params_coeff))?$y_params_coeff:1;
								$print_block_price1 += ($new_price_arr['price_out']/$y_params_count)*$y_params_coeff;
							}*/
							$print_block_summ1 = $quantity*$print_block_price1;
							$itogo_print_uslugi1 += $quantity*$print_block_price1;
							//$itogo_extra_uslugi1 += $calculations['new_summs']['summ_out'] - $print_block_summ1;
							
							
						    $print_block_price2 = $new_price_arr['price_out'];
							$print_block_summ2 = $quantity*$new_price_arr['price_out'];
							$itogo_print_uslugi2 += $quantity*$new_price_arr['price_out'];
	                       // $itogo_extra_uslugi2 += $calculations['new_summs']['summ_out'] - $quantity*$new_price_arr['price_out'];
							
							
							$print_block_price3= $new_price_arr['price_out'];
							$print_block_summ3 = $quantity*$new_price_arr['price_out'];
							//$itogo_print_uslugi3 += $calculations['new_summs']['summ_out'];
						    //////////////////////////////////////////////////////////////////////////////
							//////////////////////////////////////////////////////////////////////////////
							
							unset($size_coeff);
							unset($y_params_coeff);
							unset($y_params_count);
							
							if($save_on_disk && isset($dispSetObj->full_summ)){
								$print_block[] = '<table style="font-family:arial;font-size:13px;right;margin:0 0 5px 0;width:100%;border-collapse:collapse;width:350px;table-layout:_fixed;" border="0">
									  <tr>
										<td align="right" style="width:250px;color:#888;">1шт.</td>
										<td align="right" style="width:70px;padding:0 5px;"><nobr>'.number_format($print_block_price,2,'.',' ').'</nobr></td>
										<td align="left" style="width:30px;">руб.</td>
									  </tr>
									  <tr>
										<td align="right" style="color:#888;">тираж: '.$quantity.' шт. </td>
										<td align="right" style="padding:0 5px;"><nobr>'.number_format($print_block_summ,2,'.',' ').'</nobr></td>
										<td align="left">руб.</td>
									  </tr>
									 </table>';
							}
							else{									
								$print_block[] = '<table style="font-family:arial;font-size:13px;right;margin:0 0 5px 0;width:100%;border-collapse:collapse;width:350px;table-layout:_fixed;" border="0">
									  <tr>
										<td align="right" style="width:250px;color:#888;">1шт.</td>
										<td align="right" style="width:70px;padding:0 5px;"><nobr><span id="metod_display_setting_'.$counter2.'1_0"  style="display:'.(($display_setting_2==0)?'inline-block':'none').'">'.number_format($print_block_price1,2,',',' ').'</span><span id="metod_display_setting_'.$counter2.'1_1" style="display:'.(($display_setting_2==1)?'inline-block':'none').'">'.number_format($print_block_price2,2,',',' ').'</span><span id="metod_display_setting_'.$counter2.'1_2" style="display:'.(($display_setting_2==2)?'inline-block':'none').'">'.number_format($print_block_price3,2,',',' ').'</span></nobr></td>
										<td align="left" style="width:30px;">руб.</td>
									  </tr>
									  <tr>
										<td align="right" style="color:#888;"  data-quantity="'.@$u_level['quantity'].'">тираж: '.$quantity.' шт. </td>
										<td align="right" style="padding:0 5px;"><nobr><span id="metod_display_setting_'.$counter2.'2_0"  style="display:'.(($display_setting_2==0)?'inline-block':'none').'">'.number_format($print_block_summ1,2,',',' ').'</span><span id="metod_display_setting_'.$counter2.'2_1" style="display:'.(($display_setting_2==1)?'inline-block':'none').'">'.number_format($print_block_summ2,2,',',' ').'</span><span id="metod_display_setting_'.$counter2.'2_2" style="display:'.(($display_setting_2==2)?'inline-block':'none').'">'.number_format($print_block_summ3,2,',',' ').'</span></nobr></td>
										<td align="left">руб.</td>
										'.((isset($print_details_obj->distribution_type) && $print_details_obj->distribution_type == 'union')?'<tr><td valign="top" colspan="3" style="color:#FF0000;">Стоимость оказываемых услуг указана за комплекс работ и может быть пересмотрена при изменении условий оказываемых услуг.</td></tr>':'').
									'</tr>
									</table>';
							}	
								
					            unset($print_block_summ);
								unset($print_block_price);
								unset($print_block_price3);
							
							// Собираем данные для details_block (деталировка по нанесению)
							$square_coeff = 1;
							//echo  '<pre>--1--'; print_r( $print_data['block1']['price_data']['y_params']); echo '</pre>';
							//echo  '<pre>--2--'; print_r( $print_details_arr['dop_params']); echo '</pre>';
							if(isset($print_details_arr['dop_params'])){
								foreach($print_details_arr['dop_params'] as $type => $data){
								   
									$price_addition = $summ_addition = 0;
									//
									if($type == 'sizes' && isset($data[0]['val'])){// в итгое не выводится первым потому что в исходном массиве не на первом месте
										if($data[0]['val'] == 0) $data[0]['val'] = 1;
										if($data[0]['target'] == 'price') $square_coeff =  $data[0]['val'];
										//!!if($data->target == 'summ') $summ_coeff += (float)$data->val-1;
										
										if($square_coeff==1) continue;
	
										$print_summ = $quantity*($new_price_arr['price_out']*($square_coeff-1));
										
										
										
										   if($save_on_disk && $display_setting_2!=0){
												$rows_2[] = '<tr><td align="left" style="width:230px;padding:0 5px 0 15px;">+ '.(($square_coeff-1)*100).'% за увелич. площади печати</td>';
												$rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
												$rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>';/**/
											
											}
											else if(!$save_on_disk){
												$rows_2[] = '<tr id="metod_display_setting_'.$counter2.'3" style="display:'.(($display_setting_2!=0)?'table-row':'none').'"><td align="left" style="width:230px;padding:0 5px 0 15px;">+ '.(($square_coeff-1)*100).'% за увелич. площади печати</td>';
												$rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
												$rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>';/**/ 
											}
									
									}
									if($type == 'YPriceParam'){
										
										$price_tblYindex=(count($data)==0)?1:count($data);
										$base_price_for_Y = $new_price_arr['price_out']/$price_tblYindex;
										foreach($data as $index => $Y_data){
											if($Y_data['coeff']==1) continue;
											$Y_coeff = (float)$Y_data['coeff']-1;     
											$print_summ = $quantity*($base_price_for_Y*$Y_coeff);
	
											
											if($save_on_disk && $display_setting_2!=0){
												$rows_2[] = '<tr><td align="left" style="width:230px;padding:0 5px 0 15px;">+ '.(($Y_data['coeff']-1)*100).'% за металлик ('.$print_data['block1']['price_data']['y_params_ids'][$Y_data['id']].')</td>';
												$rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
												$rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>';
											
											}
											else if(!$save_on_disk){
												$rows_2[] = '<tr id="metod_display_setting_'.$counter2.$index.'4" style="display:'.(($display_setting_2!=0)?'table-row':'none').'"><td align="left" style="width:230px;padding:0 5px 0 15px;">+ '.(($Y_data['coeff']-1)*100).'% за металлик ('.$print_data['block1']['price_data']['y_params_ids'][$Y_data['id']].')</td>';
												$rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
												$rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>';  
											}
										}
									}
								}
                            }
 

							$base_price2 = $new_price_arr['price_out']*$square_coeff;
							$spechial_summ = 100;
							foreach($print_data['block2'] as $data){
								foreach($data as $data2){
								    if($data2['type'] == 'coeff'){
									    if($data2['value']==1) continue;
										$spechial_summ = 1;
										$coeff = $data2['value']-1;
									    $print_summ =($data2['target']== 'price')? ($quantity*($base_price2*$coeff)):($spechial_summ/100)*$data2['value'];
										 
									}
									if($data2['type'] == 'addition'){
									    if($data2['value']==0) continue;
									    $print_summ =($data2['target'] == 'price')? ($quantity*$data2['value']):$data2['value'];
									}
		
									$rows_2[] = '<tr><td align="left" style="width:230px;padding:0 5px 0 15px;">'.$data2['name'].'</td>';
								    $rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
								    $rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>'; 
								}
							}
							
							if(isset($rows_2)){
							     //$details_block11[$counter]['cap'] = 'для метода печати '.(($show_count)? $counter2.': ':'');
								 //$details_block11[$counter]['data'] = $rows_2;
							}
						
							$counter++;
							// echo  '<pre>'; print_r($rows_2); echo '</pre>';
							unset($rows_2);
							 //
							//echo '<table style="margin-top:5px;border-collapse:collapse;" border="1">'.implode('',$rows_2).'</table>';
						}
					}

					// перебор доп услуг
					if(isset($r_level['dop_uslugi']['extra'])){
	
						foreach($r_level['dop_uslugi']['extra'] as $u_key => $u_level){
					        if($u_level['price_out']==0) continue;
                            include_once(ROOT."/libs/php/classes/agreement_class.php");
                            // если альтернативное название отсутствует (вводится при заведении услуги "НЕТ В СПИСКЕ")
							if(trim($u_level['other_name']) == ""){
								$extra_usluga_details = Agreement::get_usluga_details($u_level['usluga_id']);
								$u_level['name'] = ($extra_usluga_details)? $extra_usluga_details['name']:'Неопределено'; 
							}else{
							  	$u_level['name'] = $u_level['other_name'];
							}
							 
							 $u_level['price_out'] = ($u_level['discount'] != 0 )? ($u_level['price_out']/100)*(100 + $u_level['discount']) :  $u_level['price_out'] ;
						     $print_summ = ($u_level['for_how']=='for_all')? $u_level['price_out'] :$quantity*$u_level['price_out'];
						     $all_extra_summ += $print_summ;
							 $itogo_extra_uslugi += $print_summ;
							 $itogo_extra_uslugi1 += $print_summ;
							 $itogo_extra_uslugi2 += $print_summ;
							 $itogo_extra_uslugi3 += $print_summ;
							 
							 $rows_2[] = '<tr><td align="left" style="width:230px;height:10px;line-height:10px;padding:0 5px 0 15px;">'.$u_level['name'].'</td>';
							 $rows_2[] = '<td align="right" style="width:90px;">'.number_format($print_summ,2,'.',' ').'</td>';
							 $rows_2[] = '<td align="left" style="width:30px;">руб. </td></tr>';    
						}
						if(isset($rows_2)){
							 $details_block11[$counter]['cap'] = 'для сувенира ';
							 $details_block11[$counter]['data'] = $rows_2;
						}
						unset($rows_2);
					}
 
				    // Вставляем блоки в тело КП
					if(isset($print_block) && count($print_block)>0){
					    $description_cell .= '<hr style="border:none;border-top:#888 solid 1px;"><div style="margin-top:5px;"><b>Дополнительно:</b></div>';
						$description_cell .= '<div style="">'.implode('<div></div>',$print_block).'</div>';
					   
				    }
					if(isset($details_block11) && count($details_block11)>0){

					    /*if(true!($save_on_disk && isset($dispSetObj->dop_uslugi))){ //style="display:'.(isset($dispSetObj->dop_uslugi)?'none':'block').'}"*/
					
						$description_cell .= '<managedDisplay name="dop_uslugi" style="display:'.(isset($dispSetObj->dop_uslugi)?'none':'block').'"><hr style="border:none;border-top:#888 solid 1px;"><div><b>Дополнительные услуги:</b></div>';
						$description_cell .=  '<table style="margin-top:5px;border-collapse:collapse; font-family:arial;font-size:13px;" border="0">';
						foreach($details_block11 as $key => $rows){
						   //$description_cell .=  '<tr><td align="left" height="25" colspan="3" style="padding:0 5px 0 15px;color:#888">'.$rows['cap'].'</td>';
						   $description_cell .= implode('',$rows['data']);
						} 
						$description_cell .=  '</table></managedDisplay>';
						
					}
					
				
					
					$description_cell .= '<table style="margin:5px 0 10px 0;border-collapse:collapse;" border="0"><tr><td align="left" style="width:220px;"><b>Итого</b></td>';
					  /*if(@$_SESSION['access']['user_id']==18){ 
									echo '---'.$summ.'--'.$all_print_summ.'--'.$all_extra_summ.'---';
							} */
				    $description_cell .= '<td align="right" style="width:100px;"><b>'.number_format(($summ+$all_print_summ+$all_extra_summ),2,'.',' ').'</b></td>';
					$description_cell .= '<td align="left" style="width:30px;"><b>руб.</b></td></tr></table>';
					
					$tbl_rows[] = '<tr><td style="border-bottom:#91B73F solid 2px; border-top:#91B73F solid 2px;" width="300" valign="middle" align="center">'.$img_cell.'</td><td style="border-bottom:#91B73F solid 2px; border-top:#91B73F solid 2px;padding:6px;" width="325" valign="top">'.$description_cell.'</td></tr>';
					$description_cell = $print_description ='';
	
					unset($details_block11);
					unset($print_details);
					unset($extra_details);
					unset($pos_and_print_cost);
				}
			}
		    /********************   ++++++++++++++  *********************/
			
			// КОНЕЧНАЯ СБОРКА КП
			
			
			 // Данные для шапки
		    $cont_face_data_arr = get_client_cont_face_by_id($client_id,$user_id,true);
		    $client_data_arr = select_all_client_data($client_id);
			// получаем данные о получателе КП, сначала вычисляем его id 
			// потом передаем его в метод Client::get_cont_face_details($recipient_id);
			reset($multi_dim_arr);
			$recipient_id = $multi_dim_arr[key($multi_dim_arr)]['recipient_id'];
			include_once(ROOT."/libs/php/classes/client_class.php");
			$cont_face_data = Client::get_cont_face_details($recipient_id);
			//print_r($cont_face_data_arr);//exit;625
			
			$kp_content = '<div id="kpBlankConteiner" style="width:675px;background-color:#FFFFFF;border:#91B73F solid 0px;"><table width="675"  style="border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top"><tr><td colspan="2" style="text-align:right;">
			<input  type="hidden" style="width:90px;" id="kpDisplaySettings" value='.$display_setting.'><input  type="hidden" id="kpDisplaySettings_kpId" value='.$kp_id.'>';

			if(!($save_on_disk && isset($dispSetObj->header))){
			   $kp_content .= '<div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><managedDisplay name="header" style="display:'.(isset($dispSetObj->header)?'none':'block').'">В компанию: '.Client::get_client_name($client_id).'<br />Кому: '.$cont_face_data['last_name'].' '.$cont_face_data['name'].' '.$cont_face_data['surname'].'</managedDisplay></div>';
			}
			//s$kp_content .= '<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center;border:#91B73F solid 1px;width:675px;">Презентация</div>';
			$kp_content .= '</td></tr>
			                <tr><td colspan="2" style="text-align:center;">
							<div style="font-family:verdana; font-size:18px;padding:10px;color:#10B050;">Презентация</div></td></tr>';
			$kp_content .=  '</td></tr>'.implode('',$tbl_rows).'<tr><td colspan="2" style="text-align:right;">';
			// <div style="border-top:#91B73F solid 2px;width:675px;"></div>
			
			
			
			/********************   ++++++++++++++  *********************/
			

			if($save_on_disk && !isset($dispSetObj->full_summ)){
			    if($itogo != 0){
			         $full_itog = $itogo + $itogo_print_uslugi + $itogo_extra_uslugi;
					 $kp_content .= '<div style="text-align:right;">
					 <managedDisplay name="full_summ" style="text-align:right;display:'.(isset($dispSetObj->full_summ)?'none':'inline-block').'">
					 <table align="right" style="margin:15px 0px 10px 0;font-family:arial" border="0">';
						 if(($itogo_print_uslugi+$itogo_extra_uslugi) != 0) $kp_content .= '<tr>
							 <td width="230" height="20" align="right" valign="top" style="padding-right:2px;" >Общая стоимость сувениров:</td><td width="150" align="right" valign="top">'.number_format($itogo,2,',',' ').'руб.</td>
						 </tr>';
						if($itogo_print_uslugi != 0)  $kp_content .= '<tr>
							 <td align="right" height="20" valign="top">Общая стоимость нанесения:</td><td align="right" valign="top">'.number_format($itogo_print_uslugi,2,',',' ').'руб.</td>
						 </tr>';
						if($itogo_extra_uslugi != 0)  $kp_content .= '<tr>
							 <td align="right" height="30" valign="top">Общая стоимость доп услуг:</td><td align="right" valign="top">'.number_format($itogo_extra_uslugi,2,',',' ').'руб.</td>
						 </tr>';
						$kp_content .= '<tr>
							 <td align="right" valign="top" style="font-family:verdana;font-size:14px;font-weight:bold;">Итоговая сумма:</td><td align="right" valign="top" style="font-family:verdana;font-size:14px;font-weight:bold;white-space: nowrap">'.number_format($full_itog,2,',',' ').'руб.</td>
						 </tr>
					 </table>
					 </managedDisplay>';
				 }
			}
			else if(!$save_on_disk){
				if($itogo != 0){
			
					 $full_itog = $itogo + $itogo_print_uslugi + $itogo_extra_uslugi;
					 $kp_content .= '<div style="text-align:right;">
					 <managedDisplay name="full_summ" style="text-align:right;display:'.(isset($dispSetObj->full_summ)?'none':'inline-block').'">
					 <table align="right" style="margin:15px 0px 10px 0;font-family:arial" border="0">';
					if(($itogo_print_uslugi+$itogo_extra_uslugi) != 0) $kp_content .= '<tr>
							 <td width="230" height="20" align="right" valign="top" style="padding-right:2px;" >Общая стоимость сувениров:</td><td width="150" align="right" valign="top">'.number_format($itogo,2,',',' ').'руб.</td>
						 </tr>';
				    if($itogo_print_uslugi != 0)  $kp_content .= '<tr>
							 <td align="right" height="20" valign="top">Общая стоимость нанесения:</td><td align="right" valign="top"><span id="itogo_display_setting_1_0"  style="display:'.(($display_setting_2==0)?'inline-block':'none').'">'.number_format($itogo_print_uslugi1,2,',',' ').'</span><span id="itogo_display_setting_1_1" style="display:'.(($display_setting_2==1)?'inline-block':'none').'">'.number_format($itogo_print_uslugi2,2,',',' ').'</span><span id="itogo_display_setting_1_2" style="display:'.(($display_setting_2==2)?'inline-block':'none').'">'.number_format($itogo_print_uslugi3,2,',',' ').'</span>руб.</td>
						 </tr>';
					if($itogo_extra_uslugi != 0)  $kp_content .= '<tr>
							 <td align="right" height="30" valign="top">Общая стоимость доп услуг:</td><td align="right" valign="top"><span id="itogo_display_setting_2_0" style="display:'.(($display_setting_2==0)?'inline-block':'none').'">'.number_format($itogo_extra_uslugi1,2,',',' ').'</span><span id="itogo_display_setting_2_1" style="display:'.(($display_setting_2==1)?'inline-block':'none').'">'.number_format(($itogo_extra_uslugi2),2,',',' ').'</span><span id="itogo_display_setting_2_2" style="display:'.(($display_setting_2==2)?'inline-block':'none').'">'.number_format(($itogo_extra_uslugi3),2,',',' ').'</span>руб.</td>
						 </tr>';
					 $kp_content .= '<tr style="font-family:verdana;font-size:14px;font-weight:bold;">
							 <td align="right" valign="top">Итоговая сумма:</td><td align="right" valign="top" style="white-space: nowrap">'.number_format($full_itog,2,',',' ').'руб.</td>
						 </tr>
					 </table>
					 </managedDisplay>';
				}
			}
			
			
			
		   $kp_content .= '<div style="text-align:right;font-family:arial;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div>';
		   $kp_content .= '<div style="text-align:justify;font-family:verdana;font-size:10px;line-height:11px;padding:0 20px;"><br>Данная презентация носит исключительно информационный характер и никакая информация, опубликованная в ней, ни при каких условиях не является офертой или публичной офертой, определяемой положениями пункта 2 статьи 437 и статьи 435 Гражданского кодекса Российской Федерации. Для получения подробной информации о реализуемых товарах, работах и услугах и их цене необходимо обращаться к менеджерам компании Апельбург<br><br><br></div></td></tr></table></div>';
		   
		   
		   return $kp_content;
			
	   }
	   static function split_description($str,$str_len,$section_space){
	         $str = nl2br($str);
			 $str = iconv("UTF-8","windows-1251//TRANSLIT", $str);
			 if(strpos($str,'<br />') == true) $str = str_replace('<br />','<br />',$str);
			 $str_arr = explode('<br />',$str);
			 
			 $new_line = '<br />'.$section_space;
			 foreach($str_arr as $key => $piece){
				 if(strlen($piece) > $str_len){  
					 $piece = wordwrap($piece,$str_len,$new_line);
					 $str_arr[$key] = $piece;
				 }
				 else $str_arr[$key] = trim($piece);
			 }
			
			 $str = implode($new_line,$str_arr);
			 $str = iconv("windows-1251","UTF-8//TRANSLIT", $str);
			 
			 return $str;
			 
			 
			 /* $print_description = nl2br($print_description);
			 $print_description = iconv("UTF-8","windows-1251//TRANSLIT", $print_description);
			 if(strpos($print_description,'<br />') == true) $print_description = str_replace('<br />','<br />',$print_description);
			 $print_description_arr = explode('<br />',$print_description);
			 
			 $space_str['short'] = '&nbsp;&nbsp;&nbsp;';
			 $space_str['long']  = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			 $section_space  = (count($r_level['dop_uslugi']['print'])>1)?  $space_str['long']:$space_str['short'];
			 
			 $new_line = '<br />'.$section_space;
			 foreach($print_description_arr as $key => $piece){
				 if(strlen($piece) > $str_len){  
					 $piece = wordwrap($piece,$str_len,$new_line);
					 $print_description_arr[$key] = $piece;
				 }
				 else $print_description_arr[$key] = trim($piece);
			 }
			
			 $print_description = implode($new_line,$print_description_arr);
			 $print_description = iconv("windows-1251","UTF-8//TRANSLIT", $print_description);*/
	   }
	   static function open_in_blank_old($kp_id,$client_id,$user_id,$save_on_disk = false){
	        global $mysqli;
			$stock = false;
			$com_offer_descriptions = array();
		    $com_offer_description_length = 80;
			$string = $article_string = $ordinary_string = $print_string = $itog_string = $previos_marker_summ_print = '';
		    // Здесь делаем то что в старой версии делали при сохранении КП в файл
		   
		    $cont_face_data_arr = get_client_cont_face_by_id($client_id,$user_id,true);
		    $client_data_arr = select_all_client_data($client_id);
			
			//print_r($cont_face_data_arr);
			//exit;
			$rows_data=self::fetch_kp_rows($kp_id);
			$rows_data = array_reverse($rows_data);
			
			// собираем контент коммерческого предложения
			//if($save_on_disk)//?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'
			$kp_content = '<div style="width:675px;background-color:#FFFFFF;"><div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><br />В компанию: '.$client_data_arr['comp_full_name'].'<br />Кому: '.$cont_face_data_arr['name'].'<br />Контакты: '.$cont_face_data_arr['phone'].'<br />'.$cont_face_data_arr['email'].'<br /><br /></div>
			<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center">Презентация</div>';
			$kp_content .=  '<table width="675"  style="border:#CCCCCC solid 1px; border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top">';
			$tr_td = '<tr><td style="border:#CCCCCC solid 1px;" width="300" valign="middle" align="center">';
			$td_tr = '</td></tr>';
			$td_td = '</td><td style="border:#CCCCCC solid 1px;padding:6px;" width="325" valign="top">';
			
			
			
			// этап создания контента меню
			// принцип следующий сортируем id в порядке возрастания, и считываем ряды из таблицы в порядке возрастания, при считывании
			// рядов первоначально считывается персонализация относящаяся к артикулу, записываем её данные в массив, когда доходим до            // ряда "article" или ряда "ordinary" проверям были ли созданна переменная содержащая информацию о нанесении если да
			// записываем данные по нанесению которые были считанны до этого в общую строку, предварительно развернув массив
			// с этими данными, затем переменную с данными о нанесении удаляем, если нет добавляем пустую запись
			// собранную сроку записываем в итоговый массив который перед записью в файл разворачиваем
			// если в конце всей обработки были считанны данные по нанесению но строка артикул в итоге не последовала
			// тогда эти данные добавляются в итоговый массив без данных об артикуле, на последнем шаге
			// перед разворотом массива и записью данных в файл
			foreach($rows_data as $item){
			  
		    
		     if($item['type'] == 'article'){
	
	            $query="SELECT*FROM `".BASE_TBL."` WHERE art = '".$item['article']."'";
			    $result = $mysqli->query($query)or die($mysqli->error);
				$row=$result->fetch_assoc();
			    $id = $row['id'];
	
	
				$article_string = $tr_td;
				$art_img = new  Art_Img($item['article']);
				// проверяем наличие изображения
	    	    //$img_path = '../../img/'.$art_img->big;
				$img_path = 'http://www.apelburg.ru/img/'.$art_img->big;
		        $img_src = checkImgExists($img_path);
				//$img_path = '';
				//$img_src = '../../skins/images/img_design/icon_index_2.jpg';
			                        
		        // меняем размер изображения
			     $size_arr = transform_img_size($img_src,230,300);
				//$size_arr = array(230,300);
				//$size_arr = array(100,100);
			
				
				// вставляем изображение
				$article_string .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$img_src[1].'">'.$td_td;
				
				// количество
				$quantity = $item['quantity'];
				// стоимость
				$price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$summ = $quantity*$price;
				
				
				$article = ($item['hide_article_marker'] == 'on')? '' :'арт.: <a href="/index.php?page=description&id='.$id.'" target="_blank">'.$item['article'].'</a>';
				
				// наименование сувенира
				$str_len = 40;
				$article_name = $item['name'];
				$article_name = nl2br($article_name);
				$article_name = iconv("UTF-8","windows-1251//TRANSLIT", $article_name);
				
				if(strpos($article_name,'<br />') == true) $article_name = str_replace('<br />','<br />',$article_name);
				$article_name_arr = explode('<br />',$article_name);
				$new_line = '<br />&nbsp;&nbsp;&nbsp;';
				foreach($article_name_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $article_name_arr[$key] = $piece;
					}
					else $article_name_arr[$key] = trim($piece);
				}
				
				$article_name = implode($new_line,$article_name_arr);
				$article_name = iconv("windows-1251","UTF-8//TRANSLIT", $article_name);
				//iconv_strlen($article_name,'UTF-8')
	
				
			    $article_string .= '<b>Сувенир:</b><br />
				&nbsp;&nbsp;&nbsp;'.$article_name.'<br />
				&nbsp;&nbsp;&nbsp;'.$article.'<br />
				&nbsp;&nbsp;&nbsp;Тираж: '.$item['quantity'].' шт.<br />
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($summ,2,'.',' ').'руб.</nobr><br />';
				// формирование описания для коммерческого предложения помещается в списке КП-шек
				$description_str = strip_tags($article_name);
				
				$description_str = str_replace('<br />',' ',$description_str);
				$description_str = str_replace('<br/>',' ',$description_str);
				$description_str = str_replace('<br />',' ',$description_str);
				$description_str = str_replace('&nbsp;',' ',$description_str);
				$description_str = preg_replace('|[\s]+|s',' ',$description_str);
				$description_str = trim($description_str,' ');
				$description_str = str_replace(' ',',',$description_str);
				
				$com_offer_description = substr($description_str,0,strpos($description_str,','));
				$com_offer_description = (strlen($com_offer_description) > $com_offer_description_length)? substr($com_offer_description,0,$com_offer_description_length):$com_offer_description;
				if(trim($com_offer_description) != '') $com_offer_descriptions[] = $com_offer_description;
				

				if($stock){
				$ostatok_update_time = substr($item_dop['ostatok_update_time'],11,5);
				$ostatok_update_date = substr($item_dop['ostatok_update_time'],8,2).substr($item_dop['ostatok_update_time'],4,4).substr($item_dop['ostatok_update_time'],0,4);
				$ostatok = $item_dop['ostatok'];
				$ostatok_block = 
				  '<div style="font-size:10px;color:#669900;">
					 &nbsp;&nbsp;&nbsp;<span style="font-size:13px;font-family:Arial;">остаток - '.$ostatok.'</span> шт. на&nbsp; 
					 <span style="font-size:11px;font-family:Arial;">'.$ostatok_update_time.' &nbsp;'.$ostatok_update_date.'</span>
				  </div><br />';
			    }
			    else $ostatok_block ='<br />';
				
				
				$short_space_str = '&nbsp;&nbsp;&nbsp;';
				$long_space_str = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$quantity_for_division = ($quantity == 0)? 1: $quantity;
				
				if(isset($print_rows)){
				    $print_rows = array_reverse($print_rows);
				    $print_string = '<b>Лого:</b><br />';
					$itog_string = '<br /><b>Стоимость сувенира + лого:</b><br />';
					
				    for( $i = 0 ; $i < count($print_rows); $i++ ){
						if(count($print_rows)==1){
						    $print_string .= $short_space_str.str_replace('{##}',$short_space_str,$print_rows[$i][1]);
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
				    }
				} 
				$tbl_rows[] = $article_string.$ostatok_block.$print_string.$itog_string.$td_tr;
				
				unset($print_rows);
				$article_string = $print_string = $itog_string = '';
		    }
			elseif($item['type'] == 'ordinary'){
	            // пустая ячейка
				$ordinary_string = $tr_td.'&nbsp;'.$td_td;				
				
				// количество
				$quantity = $item['quantity'];
				// стоимость
				$price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$summ = $quantity*$price;
				
				 // наименование сувенира
				$str_len = 40;
				$article_name = $item['name'];
				$article_name = nl2br($article_name);
				$article_name = iconv("UTF-8","windows-1251//TRANSLIT", $article_name);
				
				if(strpos($article_name,'<br />') == true) $article_name = str_replace('<br />','<br />',$article_name);
				$article_name_arr = explode('<br />',$article_name);
				$new_line = '<br />&nbsp;&nbsp;&nbsp;';
				foreach($article_name_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $article_name_arr[$key] = $piece;
					}
					else $article_name_arr[$key] = trim($piece);
				}
				
				$article_name = implode($new_line,$article_name_arr);
				$article_name = iconv("windows-1251","UTF-8//TRANSLIT", $article_name);
				//iconv_strlen($article_name,'UTF-8')
	
				
			    $ordinary_string .= '
				&nbsp;&nbsp;&nbsp;'.$article_name.'<br />
				&nbsp;&nbsp;&nbsp;Тираж: '.$item['quantity'].' шт.<br />
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($summ,2,'.',' ').'руб.</nobr><br /><br />';
				
				
				
				
				$short_space_str = '&nbsp;&nbsp;&nbsp;';
				$long_space_str = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$quantity_for_division = ($quantity == 0)? 1: $quantity;
				
				if(isset($print_rows)){
				    $print_rows = array_reverse($print_rows);
				    $print_string = '<b>Лого:</b><br />';
					$itog_string = '<br /><b>Стоимость сувенира + лого:</b><br />';
					
				    for( $i = 0 ; $i < count($print_rows); $i++ ){
						if(count($print_rows)==1){
						    $print_string .= $short_space_str.str_replace('{##}',$short_space_str,$print_rows[$i][1]);
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
				    }
				} 
				
				$tbl_rows[] = $ordinary_string.$print_string.$itog_string.$td_tr;
				
				unset($print_rows);
				$ordinary_string = $print_string = $itog_string = '';
		    }
			elseif($item['type'] == 'print'){
				// количество
				$print_quantity = $item['quantity'];
				// стоимость
				$print_price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$print_summ = $item['quantity']*$print_price;
				
				// наименование нанесения
				$str_len = 38;
				$print_description = $item['name'];
				$print_description = nl2br($print_description);
				$print_description = iconv("UTF-8","windows-1251//TRANSLIT", $print_description);
				
				if(strpos($print_description,'<br />') == true) $print_description = str_replace('<br />','<br />',$print_description);
				$print_description_arr = explode('<br />',$print_description);
				$new_line = '<br />{##}';
				foreach($print_description_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $print_description_arr[$key] = $piece;
					}
					else $print_description_arr[$key] = trim($piece);
				}
				
				$print_description = implode($new_line,$print_description_arr);
				$print_description = iconv("windows-1251","UTF-8//TRANSLIT", $print_description);
				//iconv_strlen($print_description,'UTF-8')
				
                $string .= $print_description.'<br />
				{##}Тираж: '.$item['quantity'].' шт.<br />
				{##}1шт.: '.number_format($print_price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($print_summ,2,'.',' ').'руб.</nobr><br />';
				
                // если предыдущий ряд был отмечен маркером marker_summ_print (объединить расчет нанесения) то объеденяем данные 
				// внося их в созданный ранее эелемент массива, если нет добавляем новый элемент.
				if($previos_marker_summ_print == 'on') $print_rows[(count($print_rows) - 1)] = array($print_rows[(count($print_rows) - 1)][0] + $print_summ, $string.'<br />{##}'.$print_rows[(count($print_rows) - 1)][1]);  
				else $print_rows[] = array($print_summ,$string);  
               
				$string = '';
				$previos_marker_summ_print = $item['marker_summ_print'];
			}
			/**/
			
			
			}
			
			//$tbl_rows[] = $tr_td.'proba'.$td_tr;
			
			// записываем все данные в строку предварительно развернув массив
		   $kp_content .= '<div style="text-align:right;font-family:arial;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div>';
		   $kp_content .= '<div style="text-align:justify;font-family:verdana;font-size:10px;line-height:11px;padding:0 20px;"><br>Данная презентация носит исключительно информационный характер и никакая информация, опубликованная в ней, ни при каких условиях не является офертой или публичной офертой, определяемой положениями пункта 2 статьи 437 и статьи 435 Гражданского кодекса Российской Федерации. Для получения подробной информации о реализуемых товарах, работах и услугах и их цене необходимо обращаться к менеджерам компании Апельбург<br><br><br></div></div>';
		   
		   return $kp_content;
	   }
	   static function create_list($query_num,$client_id,$certain_kp = FALSE){
	        
	        // общая выборка данных из базы данных производится на основании номера заказа для КП нового типа
			// и на основании client_id для КП старого типа
			// КП старого типа выводятся общим списком( все КП для даного клиента) отдельно от КП нового типа
			// выборка конкретного КП производится на id основании конкретного КП для КП нового типа
			// и на основании имени файла для старого КП

		    $rows = '';//."*****".(($query_num=="")?'null':$query_num)."*****";
			if(!$certain_kp){// если не указан конкретный КП создаем полный список
				
				if(isset($_GET['show_all'])){
					$rows .= self::create_list_new_version('');
					$rows .= "<tr><td class='flank_cell'>&nbsp;</td><td colspan='8'>КП старого типа</td><td class='flank_cell'>&nbsp;</td></tr>";
					$rows .= self::create_list_old_version($client_id);	
				}else{
					$rows .= self::create_list_new_version($query_num);
				}				
            }
			else{
				//echo "*****$query_num*****";
			    if($certain_kp['type'] == 'new') $rows .= self::create_list_new_version($query_num,$certain_kp['kp']);
				if($certain_kp['type'] == 'old')  $rows .= self::create_list_old_version($client_id,substr($certain_kp['kp'],strpos($certain_kp['kp'],"/")+1));
			}
			return (!empty($rows))?$rows:"<tr><td class='flank_cell'>&nbsp;</td><td colspan='8'>для данного клиента пока небыло создано коммерческих предложений</td><td class='flank_cell'>&nbsp;</td></tr>";
		}

		static function create_list_new_version($query_num,$certain_kp_id = FALSE){
		   global $mysqli;
		   global $user_id;
		   // шаблон ряда таблицы списка КП
		   $tpl_name = 'skins/tpl/client_folder/business_offers/list_table_rows.tpl';
		   $fd = fopen($tpl_name,'r');
		   $rows_template = fread($fd,filesize($tpl_name));
		   fclose($fd);
		   
		   // echo $query_num;
		   
		   $rows = '';	   
		   
		   if((int)$query_num > 0 ){
		   		// $query="SELECT*FROM `".KP_LIST."` WHERE `query_num` = '".$query_num."'";	
		   		$query = "SELECT `".KP_LIST."`.*,`".RT_LIST."`.`theme` FROM `".KP_LIST."` INNER JOIN `".RT_LIST."` ON `".RT_LIST."`.`query_num` = `".KP_LIST."`.`query_num` WHERE `".KP_LIST."`.`query_num` = '".$_GET['query_num']."'";
		   }else if(isset($_GET['client_id'])){
		   		// $query = "SELECT*FROM `".KP_LIST."` WHERE `client_id` = '".$_GET['client_id']."'";	
		   		$query = "SELECT `".KP_LIST."`.*,`".RT_LIST."`.`theme` FROM `".KP_LIST."` INNER JOIN `".RT_LIST."` ON `".RT_LIST."`.`query_num` = `".KP_LIST."`.`query_num` WHERE `".KP_LIST."`.`client_id` = '".$_GET['client_id']."'";
		   }
		   
		   if($certain_kp_id)$query.= " AND `".KP_LIST."`.`id` = '".$certain_kp_id."'";
		   $query.= " ORDER BY id DESC";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		        ob_start();
		        while($row=$result->fetch_assoc()){ //
				     $client_id = $row['client_id'] ;
					 if($row['send_time']!='0000-00-00 00:00:00'){
					     $send_time_arr = explode("-",substr($row['send_time'],0,10));
					     $send_time = implode(".",array_reverse($send_time_arr));
					 }
					 else $send_time = 'не отправленно';
					 
					 include_once(ROOT."/libs/php/classes/client_class.php");
					 $cont_face_data = Client::get_cont_face_details($row['recipient_id']);
					 
					 $recipient = '<div class="client_faces_select1" sourse="kp" row_id="'.$row['id'].'" client_id="'.$client_id.'" onclick="openCloseMenu(event,\'clientManagerMenu\');">'.(($row['recipient_id']==0)?'не установлен':$cont_face_data['last_name'].' '.$cont_face_data['name'].' '.$cont_face_data['surname']).'</div>';
					 //$recipient = $row['recipient'];контакт: 
					 $date_arr = explode("-",substr($row['create_time'],0,10));
					 $date = implode(".",array_reverse($date_arr));
					 $query_num = $row['query_num'] ;
					 $comment = 'добавьте свой комментарий' ;
					 $comment = (!empty($row['comment']))? $row['comment'] : 'добавьте свой комментарий' ;
					 $comment_style = (trim($comment) == 'добавьте свой комментарий')? 'italic grey' : '' ;
					 eval(' ?>'.$rows_template.'<?php ');
				}
				$rows .= ob_get_contents();
		        ob_get_clean();	
				
				
	        }
			return $rows;
		}
		static function create_list_old_version($client_id,$certain_kp_filename = FALSE){

            $rows = '';
		    $prefix = '../admin/order_manager/';
            $dir_name = $prefix.'data/com_offers/'.$client_id;
			
			if(file_exists($dir_name)){
				// считываем комментарии
				$comments_file_path = $dir_name.'/comments.txt';
				if(file_exists($comments_file_path)){
					$srting_arr = file($comments_file_path);
					foreach($srting_arr as $comment_srting){
						list($file_name,$comment) = explode(';',$comment_srting);
						//$file_name = trim(iconv("windows-1251","UTF-8", $file_name));
						//$file_name = trim(strtr($file_name,' ',''));
						$comments_arr[md5($file_name)] = $comment ;
					}	 
				}
				
				// считываем файлы КП из директории
				$dir = opendir($dir_name);
				while($file = readdir($dir)){// считываем файлы и создаем массив с датой создания в качестве ключа
					if($file != "." && $file != ".." && $file != "comments.txt"){ 
						 //echo $dir_name.'/'.$file.'<br />';
						 if(!$certain_kp_filename){ 
						     $data_mod = date("ymdHis", filemtime($dir_name.'/'.$file));
						     $file_arr[$data_mod] = array(date("d.m.Y", filemtime($dir_name.'/'.$file)),basename($dir_name.'/'.$file));
						 }
						 else if($certain_kp_filename && $file == $certain_kp_filename) {
						     $data_mod = date("ymdHis", filemtime($dir_name.'/'.$file));
						     $file_arr[$data_mod] = array(date("d.m.Y", filemtime($dir_name.'/'.$file)),basename($dir_name.'/'.$file));
						 }
					}
				}
				closedir($dir);
				
				// альтернатива предыдушей комбинации
				//foreach(glob($dir_name.'/*.doc') as $file){
					// echo $file.'<br />'; 
					// функция stat не подходит под unix //$stat = stat($file);
					//if($user_nickname == 'andrey') echo $file.' '.$stat[10].' '.filemtime($file).' ';
					//$data_mod = date("ymdHis", filemtime($file));
					// if($user_nickname == 'andrey') echo $data_mod.' <br />';
					// $file_arr[$data_mod] = array(date("d.m.Y", filemtime($file)),basename($file));
				 //}
				 //clearstatcache();
				 krsort($file_arr);
				 // if($user_nickname == 'andrey') 
				 /*
				 echo '<pre>';
				 print_r($file_arr);
				 echo '</pre>';
				*/
				 // шаблон ряда таблицы
				 $tpl_name = 'skins/tpl/client_folder/business_offers/list_table_rows_old_version.tpl';
				 $fd = fopen($tpl_name,'r');
				 $rows_template = fread($fd,filesize($tpl_name));
				 fclose($fd);
				 
				 ob_start();
				
				 
				 foreach($file_arr as $key => $file_data){
					 $new_com_pred_format = (strrpos($file_data[1],'_')==33)? true: false;
					 $file_name_arr = explode('_',substr($file_data[1],0,strrpos($file_data[1],'.')));
					 $query_num = ($new_com_pred_format)? $file_name_arr[count($file_name_arr)-2]:'';
					 $com_pred_num = ($new_com_pred_format)? $file_name_arr[count($file_name_arr)-1]:'';
					 $com_pred_data = get_com_offer_data($new_com_pred_format,$com_pred_num);
				 
					 
					 list($date,$file) = $file_data;
					 $file = trim(iconv("windows-1251", "UTF-8", $file));
					 $comment = (isset($comments_arr[md5($file)]) && trim($comments_arr[md5($file)])!='' )? $comments_arr[md5($file)] : 'добавьте свой комментарий' ;
					 $comment_style = (trim($comment) == 'добавьте свой комментарий')? 'italic grey' : '' ;
					 //$file = trim($file);
					 eval(' ?>'.$rows_template.'<?php ');
				}	
				$rows .= ob_get_contents();
				ob_get_clean();	
			}
			return $rows;
		}
		static function saveKpDisplayChangesInBase($kp_id,$dataJSON){
		    global $mysqli;
			
		    $query="UPDATE `".KP_LIST."` SET 
							  `display_setting` = '".$dataJSON."' WHERE `id` = '".$kp_id."'";
			// echo $query;
		    $mysqli->query($query)or die($mysqli->error);
		}
		static function saveChangesRadioInBase($kp_id,$val){
		    global $mysqli;
			
		    $query="UPDATE `".KP_LIST."` SET 
							  `display_setting_2` = '".$val."' WHERE `id` = '".$kp_id."'";
		    $mysqli->query($query)or die($mysqli->error);
		}
		static function convertTirageJSON($tirage_json){
		
		    global $mysqli;
			if($tirage_json=='' || $tirage_json=='{}') return '';
		    // Задача получить данные о размерах в рассчете
			
		
			$tirageArr = json_decode($tirage_json,true);
			if($tirageArr!=NULL){
			    // echo print_r($tirageArr);
				foreach($tirageArr as $sizeId => $data){
					 if($data['tir']!=0){
						 $sizesArr[$sizeId] = array('tir'=>$data['tir']);
						 $sizesIdsArr[] = $sizeId;
					 }
				 }
				 if(isset($sizesIdsArr)){
					 $query2 = "SELECT id, size FROM `".BASE_DOP_PARAMS_TBL."`  WHERE  id IN('".implode("','",$sizesIdsArr)."')";
					 $result2 = $mysqli->query($query2) or die($mysqli->error);
					 if($result2->num_rows > 0){
						 while($row2 = $result2->fetch_assoc()){
							 if($row2['size']!='') $sizesFinalArr[] = $row2['size'].': '.$sizesArr[$row2['id']]['tir'].' шт.';
						 }
					 }
				 }
			 }
			 return (isset($sizesFinalArr))? 'размеры: '.implode(', ',$sizesFinalArr):'';
		}
		static function getSizesForRow($row_id){
		
		    global $mysqli;
		    // Задача получить данные о размерах в рассчете
			
			 $query = "SELECT tirage_json, quantity
							  FROM `".KP_DOP_DATA."`  WHERE id ='".$row_id."'";
			 $result = $mysqli->query($query) or die($mysqli->error);
			 $row = $result->fetch_assoc();
			 if(!($row['tirage_json']=='' || $row['tirage_json']=='{}')){
			     $tirageArr = json_decode($row['tirage_json'],true);
				 if($tirageArr!=NULL){
					 // echo print_r($tirageArr);
					 foreach($tirageArr as $sizeId => $data){
						 if($data['dop']!=0 || $data['tir']!=0){
							 $sizesArr[$sizeId] = array('tir'=>$data['tir'],'dop'=>$data['dop']);
							 $sizesIdsArr[] = $sizeId;
						 }
					 }
					 if(isset($sizesIdsArr)){
						 $query2 = "SELECT id, size FROM `".BASE_DOP_PARAMS_TBL."`  WHERE  id IN('".implode("','",$sizesIdsArr)."')";
						 $result2 = $mysqli->query($query2) or die($mysqli->error);
						 if($result2->num_rows > 0){
							 while($row2 = $result2->fetch_assoc()){
								 if($row2['size']!='') $sizesFinalArr['sizes'][] = $row2['size'].': '.$sizesArr[$row2['id']]['tir'].(($sizesArr[$row2['id']]['dop']>0)?' + '.$sizesArr[$row2['id']]['dop']:'').' шт.';
							 }
						 }
					 }
				 }
			 }
			 
			 return (isset($sizesFinalArr))?$sizesFinalArr['sizes']:false;
		}
		static function changePosDescription($id,$val,$bd_field){
		    global $mysqli;

			//echo '----'.$val.'----';
		    if($bd_field=='name') $query="UPDATE `".KP_MAIN_ROWS."` SET `name`='".cor_data_for_SQL($val)."' WHERE `id`='".$id."'";
		    if($bd_field=='details') $query="UPDATE `".KP_DOP_DATA."` SET `details`='".cor_data_for_SQL($val)."' WHERE `id`='".$id."'";
			if($bd_field=='tirage_str') $query="UPDATE `".KP_DOP_DATA."` SET `tirage_str`='".cor_data_for_SQL($val)."' WHERE `id`='".$id."'";
			//echo $query;
		    $mysqli->query($query)or die($mysqli->error);
		}
		static function changeRepresentedData($id,$val,$bd_field){
		    global $mysqli;
            $query="UPDATE `".KP_MAIN_ROWS."` SET `".$bd_field."`='".cor_data_for_SQL($val)."' WHERE `id`='".$id."'";
"'";
		    $mysqli->query($query)or die($mysqli->error);
		}
		
		
   }
?>
