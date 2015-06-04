<?php 

 class Com_pred{
        static function save_to_tbl($json){
		   global $mysqli;
	       global $user_id;
		   
		   $data_obj = json_decode($json);
           //print_r($data_obj);
		
 	       // !!! $conrtol_num
		   
		   // $data->ids - это двухмерный массив первый уровеннь которого содержит id строк из таблицы RT_MAIN_ROWS
		   // второй уровень содержит id дочерних строк из таблицы RT_DOP_DATA       
		   // проходим в цикле этот массив и поочередно копируем данные из таблиц РТ в таблицы КП
	
	
	       // записываем данные о КП в таблицу KP_LIST
		   $query="INSERT INTO `".KP_LIST."` 
							  SET 
							  `create_time` = NOW(),
							  `client_id` = '".$data_obj->client_id."',
							  `manager_id` = '".$user_id."',
							  `query_num` = '".$data_obj->query_num."'
							  ";
							  
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return 2;
		   $kp_id = $mysqli->insert_id;
		   
		   
	       foreach($data_obj->ids as $key => $dop_data){
		       // преобразуем объект в массив
		       $dop_data = (array)$dop_data;
		       if(count($dop_data)==0) continue;
			   
			   // Вставляем ряд в таблицу KP_MAIN_ROWS
               $query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE id = '".$key."'";//echo $query;

		       $result = $mysqli->query($query)or die($mysqli->error);
			   if($result->num_rows>0){
			       $row=$result->fetch_assoc();
				   $query2="INSERT INTO `".KP_MAIN_ROWS."` 
							   SET 
							   `kp_id` = '".$kp_id."',
							   `art` = '".$row['art']."',
							   `type` = '".$row['type']."',
							   `art_id` = '".$row['art_id']."',
							   `name` = '".$row['name']."'
							  ";
				   $result2 = $mysqli->query($query2)or die($mysqli->error);
				   $row_id = $mysqli->insert_id;
				   // Проходим по второму уровню массива
				   foreach($dop_data as $dop_key => $dop_val){
					   //echo $dop_key.',';
					   // Вставляем ряд в таблицу KP_DOP_DATA
					   $query3="SELECT*FROM `".RT_DOP_DATA."` WHERE id = '".$dop_key."'";//echo $query;

					   $result3 = $mysqli->query($query3)or die($mysqli->error);
					   if($result3->num_rows>0){
						    $row3=$result3->fetch_assoc();
						    $query4=  "INSERT INTO `".KP_DOP_DATA."` 
									   SET 
									   `row_id` = '".$row_id."',
									   `quantity` = '".$row3['quantity']."',
									   `price_in` = '".$row3['price_in']."',
									   `price_out` = '".$row3['price_out']."',
									   `discount` = '".$row3['discount']."',
									   `tirage_json` = '".$row3['tirage_json']."'
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
										   `glob_type` = '".$row5['glob_type']."',
										   `type` = '".$row5['type']."',
										   `quantity` = '".$row5['quantity']."',
										   `price_in` = '".$row5['price_in']."',
										   `price_out` = '".$row5['price_out']."'
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
		   $arr=array();
		   // !!! $conrtol_num
		   // выбираем из базы строки выбранные для создания КП
		   $query="DELETE FROM `".COM_PRED_ROWS."` WHERE kp_id = '".(int)$kp_id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
		   
		   $query="DELETE FROM `".COM_PRED_LIST."` WHERE id = '".(int)$kp_id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
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
		   
		   $query="UPDATE `".COM_PRED_LIST."` SET comment ='".$comment."'  WHERE id = '".(int)$id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
	   }
	   static function change_comment_old_version($file_name,$file_comment){
			global $client_id;
			
			//echo $file_name.$file_comment;
			$prefix = '../admin/order_manager/';
			
			
			
			$file_comment = strip_tags($file_comment,'<b><br><a>');
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
	   static function fetch_kp_rows($kp_id){
	       global $mysqli;
		
		   // !!! $conrtol_num
		   // выбираем из базы данных строки соответствующие данному КП
		   
		   $rows = array();
		 
		   $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art AS art ,main_tbl.name AS item_name ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.expel AS expel,
						  
						  dop_uslugi_tbl.id AS uslugi_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out
		          FROM 
		          `".KP_LIST."`  list_tbl 
				  LEFT JOIN  
		          `".KP_MAIN_ROWS."`  main_tbl  ON  list_tbl.id = main_tbl.kp_id
				  LEFT JOIN 
				  `".KP_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".KP_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE list_tbl.id ='".$kp_id."' ORDER BY main_tbl.id";
				  
		   $result = $mysqli->query($query) or die($mysqli->error);
		   $multi_dim_arr = array();
		  
		   while($row = $result->fetch_assoc()){
		       if(!isset($multi_dim_arr[$row['main_id']])){
			       $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				   $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				   $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
			   }
			   //$multi_dim_arr[$row['main_id']]['uslugi_id'][] = $row['uslugi_id'];
			   if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			       $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'expel' => $row['expel'],
																	'quantity' => $row['dop_t_quantity'],
																	'price_in' => $row['dop_t_price_in'],
																	'price_out' => $row['dop_t_price_out']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslugi_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslugi_id']] = array(
																									'type' => $row['uslugi_t_type'],
																									'quantity' => $row['uslugi_t_quantity'],
																									'price_in' => $row['uslugi_t_price_in'],
																									'price_out' => $row['uslugi_t_price_out'],
																									'uslugi_id' => $row['uslugi_id']
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
			
			$filename = '/Пробный_ПДФ_в_кириллицe_'.$client_id.'_'.$kp_id.'_'.date('Ymd_His').'.pdf';
			//$filename = '/probe_file_in_latin_'.$client_id.'_'.date('Ymd_His').'.pdf';
			$filename_utf = iconv("UTF-8","windows-1251", $filename);
			$save_to = $document_root.$dirname.$filename_utf;
			
            self::save_in_pdf_on_server($kp_id,$client_id,$user_id,$save_to);
			return $dirname.$filename;
            exit;
	   }
	   static function clear_client_kp_folder($kp_id,$attached_files){
	        $dirname = $_SERVER['DOCUMENT_ROOT'].'/os/data/com_offers/'.strval(intval($_GET['client_id'])).'/'.strval(intval($kp_id));
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
			 
			$query="UPDATE `".COM_PRED_LIST."` SET 	`send_time` = NOW() WHERE `id` = '".$kp_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
				//$row=$result->fetch_assoc();

	   }
	   static function save_in_pdf_on_server($kp_id,$client_id,$user_id,$filename){
	   
            $html = self::open_in_blank($kp_id,$client_id,$user_id);
			
			include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/mpdf60/mpdf.php");
			$mpdf=new mPDF();
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'F');
	   }
	   static function save_in_pdf($kp_id,$client_id,$user_id,$filename = '1.pdf'){
	   
            $html = self::open_in_blank($kp_id,$client_id,$user_id);
		
			include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/mpdf60/mpdf.php");
            //$stylesheet = file_get_contents('style.css');
				
			$mpdf=new mPDF();
			//$mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'D');
			//$mpdf->Output();
            exit;
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
				 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
				 if(@$dop_row['expel']!=''){
					$obj = @json_decode($dop_row['expel']);
					foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
				 }
				 //echo '<br>'; print_r($expel);
				 
				 // работаем с информацией о дополнительных услугах определяя что будет выводиться и где
				 // 1. определяем данные описывающие варианты нанесения логотипа, они хранятся в $dop_row['dop_uslugi']['print']
				 if(isset($dop_row['dop_uslugi']['print'])){ // если $dop_row['dop_uslugi']['print'] есть выводим данные о нанесениях 
					 $summ_in = $summ_out = array();
					 foreach($dop_row['dop_uslugi']['print'] as $extra_data){
						 $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
					 }
					 $print_btn = count($dop_row['dop_uslugi']['print']); 
					 $print_in_summ = array_sum($summ_in);
					 $print_out_summ = array_sum($summ_out);
				 }
				 else{// если данных по печати нет то проверяем - выводим кнопку добавление нанесения
					 $print_btn = '+';
					 $print_in_summ = 0;
					 $print_out_summ = 0;
				 }
				 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
				 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
					 $summ_in = $summ_out = array();
					 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
						 $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
					 }
					 $dop_uslugi_in_summ = array_sum($summ_in);
					 $dop_uslugi_out_summ = array_sum($summ_out);
				 }
				 else{
					 $dop_uslugi_in_summ = 0;
					 $dop_uslugi_out_summ = 0;
				 }
				
				 // подсчет сумм ряду
				 // 1. подсчитываем входящую сумму
				 $price_in_summ = $dop_row['quantity']*$dop_row['price_in'];
				 $in_summ = $price_in_summ;
				 if(!(!!$expel["print"]))$in_summ += $print_in_summ;
				 if(!(!!$expel["dop"]))$in_summ += $dop_uslugi_in_summ;
				 // 2. подсчитываем исходящую сумму 
				 $price_out_summ =  $dop_row['quantity']*$dop_row['price_out'];
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
				
				
				 $cur_row  =  '';
				 $cur_row .=  '<tr '.(($counter==0)?'pos_id="'.$key.'"':'').' row_id="'.$dop_key.'" class="'.(($key>1 && $counter==0)?'pos_edge':'').'">';
				 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="top" width="30">'.$glob_counter.'</td>':'';
				 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="top master_btn noselect" width="80"></td>':'';
				 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$dop_key.'</td>':'';
				 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$row['row_type'].'</td>':'';
				 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" width="300" class="top"><a href="?page=client_folder&section=order_art_edit&id='.$dop_key.'">'.$row['art'].''.$row['name'].'</a></td>':'';
				 $cur_row .=  '<td class="hidden"></td>
							   <td width="50" type="quantity" class="r_border"  editable="true">'.$dop_row['quantity'].'</td>
							   <td width="90" type="price_in" editable="true" connected_vals="art_price" c_stat="1" class="in right">'.$dop_row['price_in'].'</td>
							   <td width="15" connected_vals="art_price" c_stat="1" class="currency left">'.$currency.'</td>
							   <td width="90" type="price_in_summ" connected_vals="art_price" c_stat="0" class="in right hidden">'.$price_in_summ_format.'</td>
							   <td width="15" connected_vals="art_price" c_stat="0" class="currency left hidden">'.$currency.'</td>
							   <td width="90" type="price_out" editable="true" connected_vals="art_price" c_stat="1" class="out right">'.$dop_row['price_out'].'</td>
							   <td width="15" class="currency left r_border" connected_vals="art_price" c_stat="1" >'.$currency.'</td>
							   <td width="90" type="price_out_summ"  connected_vals="art_price" c_stat="0" class="out right hidden">'.$price_out_summ_format.'</td>
							   <td width="15" connected_vals="art_price" c_stat="0" class="currency left r_border hidden">'.$currency.'</td>';
				 $cur_row .=  '<td width="80" type="print_in_summ"  connected_vals="print" c_stat="0" class="test_data in hidden">'.$print_in_summ_format.$currency.'</td> 
							   <td width="80" type="print_out_summ"  connected_vals="print" c_stat="1" class="out '.(($expel['print']=='1')?' red_cell':'').'" expel="'.$expel['print'].'">'.$print_out_summ_format.$currency.'</td>';
				 $cur_row .=  '<td width="80" type="dop_uslugi_in_summ" connected_vals="uslugi" c_stat="0" class="test_data r_border in hidden">'.$dop_uslugi_in_summ.$currency.'</td>';
				 $cur_row .=  '<td width="80" type="dop_uslugi_out_summ" connected_vals="uslugi" c_stat="1"  class="out r_border'.(($expel['dop']=='1')?' red_cell':'').'" expel="'.$expel['dop'].'">'.$dop_uslugi_out_summ_format.$currency.'</td>
							   <td width="100" type="in_summ" connected_vals="total_summ" c_stat="0" class="in right hidden">'.$in_summ_format.'</td>
							   <td width="100" type="out_summ" connected_vals="total_summ" c_stat="1" class="out right '.(($expel['main']=='1')?' red_cell':'').'" expel="'.$expel['main'].'" >'.$out_summ_format.'</td>
							   <td width="100" type="delta" class="right">'.$delta_format.'</td>
							   <td width="100" type="margin" class="right">'.$margin_format.'</td>
							   <td stretch_column>&nbsp;</td>';
				 $cur_row .= '</tr>';
				 
				 // загружаем сформированный ряд в итоговый массив
				 $tbl_rows[]= $cur_row;
				 $counter++;
				 
			 }
		}
		 $rt = '<table class="rt_tbl_head" id="rt_tbl_head" scrolled="head" style="width: 100%;" border="1">
				  <tr class="cap">
					  <td width="30"></td>
					  <td width="80"></td>
					  <td class="hidden"></td>
					  <td class="hidden">тип</td>
					  <td width="300" class="right">
						  &nbsp;<a href="#" onclick="print_r(rtCalculator.tbl_model);">_</a>
						  прибыль ???? р подробно?
					  </td>
					  <td class="hidden">draft</td>
					  <td width="50" class="r_border">тираж</td>
					  <td width="90" connected_vals="art_price" c_stat="1" class="right pointer">$ товара<br><span class="small">входящая штука</span></td>
					  <td width="15" connected_vals="art_price" c_stat="1"></td>
					  <td width="90" connected_vals="art_price" c_stat="0" class="right hidden pointer">$ товара<br><span class="small">входящая тираж</span></td>
					  <td width="15" connected_vals="art_price" c_stat="0" class="hidden"></td>
					  <td width="90" connected_vals="art_price" c_stat="1" class="right pointer">$ товара<br><span class="small">исходящая штука</span></td>
					  <td width="15" connected_vals="art_price" c_stat="1"class="r_border"></td>
					  <td width="90" connected_vals="art_price" c_stat="0" class="right pointer hidden">$ товара<br><span class="small">исходящая тираж</span></td>
					  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden"></td>';
			   $rt.= '<td width="80" connected_vals="print" c_stat="0" class="pointer hidden">$ печать<br><span class="small">входящая тираж</span></td> 	  
					  <td width="80" connected_vals="print" c_stat="1" class="pointer">$ печать<br><span class="small">исходящая тираж</span></td>';
			   $rt.= '<td width="80"  connected_vals="uslugi" c_stat="0" class="pointer r_border hidden">$ доп. услуги<br><span class="small">входящая тираж</span></td> 
					  <td width="80"  connected_vals="uslugi" c_stat="1" class="out pointer r_border">$ доп. услуги<br><span class="small">исходящая тираж</span></td>
					  <td width="100" connected_vals="total_summ" c_stat="0" class="pointer hidden center">итого<br><span class="small">входящая</span></td>
					  <td width="100" connected_vals="total_summ" c_stat="1" class="pointer center">итого<br><span class="small">исходящая</span></td>
					  <td width="100" class="center">delta</td>
					  <td width="100"  class="center">маржина-<br>льность</td>
					  <td stretch_column>&nbsp;</td>';              
			$rt.= '</tr>
				   <tr row_id="total_row" class="grey bottom_border">
					  <td width="30" height="18"></td>
					  <td width="80"></td>
					  <td class="hidden"></td>
					  <td class="hidden"></td>
					  <td class="right">Счет №45384? оплата 70%?</td>
					  <td class="r_border"></td>  
					  <td connected_vals="art_price" c_stat="1"></td>
					  <td width="15" connected_vals="art_price" c_stat="1"></td>
					  <td type="price_in_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format($total['price_in_summ'],'2','.','').'</td>
					  <td width="15" connected_vals="art_price" c_stat="0" class="hidden">р</td>
					  <td connected_vals="art_price" c_stat="1"></td>
					  <td width="15" connected_vals="art_price" c_stat="1" class="r_border"></td>
					  <td type="price_out_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format($total['price_out_summ'],'2','.','').'</td>
					  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden">р</td>';
			   $rt.= '<td type="print_in_summ" connected_vals="print" c_stat="0" class="hidden">'.number_format($total['print_in_summ'],'2','.','').'р</td> 		  
					  <td type="print_out_summ" connected_vals="print" c_stat="1">'.number_format($total['print_out_summ'],'2','.','').'р</td>';
			   $rt.= '<td width="80" type="dop_uslugi_in_summ" connected_vals="uslugi" c_stat="0"  class="r_border hidden">'.number_format($total['dop_uslugi_in_summ'],'2','.','').'р</td> 
					  <td width="80" type="dop_uslugi_out_summ" connected_vals="uslugi" c_stat="1" class="out r_border">'.number_format($total['dop_uslugi_out_summ'],'2','.','').'р</td>
					  <td width="100" type="in_summ" connected_vals="total_summ" c_stat="0" class="right hidden">'.number_format($total['in_summ'],'2','.','').'</td>
					  <td width="100" type="out_summ" connected_vals="total_summ" c_stat="1" class="right">'.number_format($total['out_summ'],'2','.','').'</td>
					  <td width="100" type="delta" class="right">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
					  <td width="100" type="margin" class="right">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
					  <td stretch_column>&nbsp;</td>';              
		   $rt.= '</tr>
				  </table>
				  <div id="scrolled_part_container" class="scrolled_tbl_movable_part">
				  <table class="rt_tbl_body" id="rt_tbl_body" scrolled="body" border="1">'.implode('',$tbl_rows).'</table>
				  </div>';

		   return $rt;	
		   	
	   }
	    static function open_in_blank($kp_id,$client_id,$user_id,$save_on_disk = false){
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
			$multi_dim_arr=self::fetch_kp_rows($kp_id);
			//echo '<pre>';print_r($multi_dim_arr);echo '</pre>';
			
			
			/*************************************************************************/
			$kp_content = '<div style="width:625px;background-color:#FFFFFF;"><div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><br>В компанию: '.$client_data_arr['comp_full_name'].'<br>Кому: '.$cont_face_data_arr['name'].'<br>Контакты: '.$cont_face_data_arr['phone'].'<br>'.$cont_face_data_arr['email'].'<br><br></div>
			<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center">Коммерческое предложение</div>';
			$kp_content .=  '<table width="625"  style="border:#CCCCCC solid 1px; border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top">';
			$tr_td = '<tr><td style="border:#CCCCCC solid 1px;" width="300" valign="middle" align="center">';
			$td_tr = '</td></tr>';
			$td_td = '</td><td style="border:#CCCCCC solid 1px;padding:6px;" width="325" valign="top">';
			
			/********************   ++++++++++++++  *********************/
			// Разворачиваем массив 
			// 1. уровень позиции
			foreach($multi_dim_arr as $pos_key => $pos_level){
			   
				// Работаем с первой ячейкой ряда таблицы
				// в этой ячейке подразумевается отображение картинки товарной позиции
				// соответсвенное если есть что показывать, то добавляем тег img, если нет то  добавляем пустую строку
				$img_cell = '';
				if($pos_level['row_type']=='cat'){ // если позиция из каталога получаем картинку из базы данных каталога
					$art_img = new  Art_Img($pos_level['art']);
					// проверяем наличие изображения
					//$img_path = '../../img/'.$art_img->big;
					$img_path = 'http://www.apelburg.ru/img/'.$art_img->big;
					if($img_src = checkImgExists($img_path)){
						//$img_path = '';
						//$img_src = '../../skins/images/img_design/icon_index_2.jpg';
											
						// меняем размер изображения
						// $size_arr = transform_img_size($img_src,230,300);
						$size_arr = array(230,300);
						$img_cell = '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$img_src[1].'">';
					}
                }				
				
				// Работаем со второй ячейкой ряда таблицы
				
				
				// Описание товарной позиции её цена и тираж
				// количество
				
				
				// наименование товарной позиции
				// форматируем вывод, разбиваем строки на куски определенной длины и вставляем перед каждым отступ
				$str_len = 40;
				$pos_name = $pos_level['name'];
				$pos_name = nl2br($pos_name);
				$pos_name = iconv("UTF-8","windows-1251//TRANSLIT", $pos_name);
				
				if(strpos($pos_name,'<br>') == true) $pos_name = str_replace('<br>','<br />',$pos_name);
				$pos_name_arr = explode('<br />',$pos_name);
				$new_line = '<br />&nbsp;&nbsp;&nbsp;';
				foreach($pos_name_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $pos_name_arr[$key] = $piece;
					}
					else $pos_name_arr[$key] = trim($piece);
				}
				
				$pos_name = implode($new_line,$pos_name_arr);
				$pos_name = iconv("windows-1251","UTF-8//TRANSLIT", $pos_name);
				
				$pos_level['hide_article_marker'] = 'on';
				$article = ($pos_level['hide_article_marker'] == 'on')? '' :'арт.: <a href="/index.php?page=description&id='.$id.'" target="_blank">'.$pos_level['art'].'</a>';
				
				
				
				
				
				// 2. уровень расчетов
				// на этом уровне идет постороение рядов HTML таблицы 
				foreach($pos_level['dop_data'] as $r_key => $r_level){ //$r_ - сокращение обозначающее - уровень Расчёта позиции
					
					// количество
					$quantity = $r_level['quantity'];
					// стоимость
					$r_level['discount'] = 1;
					$price = ($r_level['discount'] == 0)? $r_level['price_out'] : $r_level['price_out']*$r_level['discount'];
					$summ = $quantity*$price;
				
				    // 3. уровень нанесения логотипа
				    // на этом уровне идет постороение рядов HTML таблицы 
					$counter = 0;
					if(isset($r_level['dop_uslugi']['print'])){
						foreach($r_level['dop_uslugi']['print'] as $u_key => $u_level){
						
							     // наименование нанесения
								 // форматируем вывод, разбиваем строки на куски определенной длины и вставляем перед каждым отступ
								 $str_len = 38;
								 $print_description = $u_level['type'];
								 $print_description = nl2br($print_description);
								 $print_description = iconv("UTF-8","windows-1251//TRANSLIT", $print_description);
								 if(strpos($print_description,'<br>') == true) $print_description = str_replace('<br>','<br />',$print_description);
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
								 
				                 $space_str['short'] = '&nbsp;&nbsp;&nbsp;';
								 $space_str['long']  = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
								 $section_space  = (count($r_level['dop_uslugi']['print'])>1)?  $space_str['long']:$space_str['short'];
								 
								 // количество
								 $print_quantity = $u_level['quantity'];
								 $quantity_for_division = ($quantity == 0)? 1: $quantity;
								 // стоимость
								 //$print_price = ($u_level['discount'] == 0)? $u_level['price_out'] : $u_level['price_out'] + $u_level['price_out']/100*$u_level['discount'];
								 $print_price = $u_level['price_out'];
								 $print_summ  = $print_quantity*$print_price;
								 
								 $print_description .= '<br />'.$section_space.'Тираж: '.$print_quantity.' шт.<br />
				                                              '.$section_space.'1шт.: '.number_format($print_price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($print_summ,2,'.',' ').'руб.</nobr><br />';
								 
								 
                                 $mark = (count($r_level['dop_uslugi']['print'])>1)? ++$counter.'. ' :'';                   
							     $print_details[] =  $space_str['short'].$mark.$print_description;
								 $pos_and_print_cost[] = $space_str['short'].'<span style="color:#00B050;font-weight:bold;">'.$mark.'1шт. : '.number_format(($summ+$print_summ)/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_summ),2,'.',' ').'руб.</nobr></span><br />';
		
							
							
						}
					}
				    // собираем содержимое ячейки
					$description_cell = '<b>Сувенир:</b><br />
					&nbsp;&nbsp;&nbsp;'.$pos_name.'<br />
					&nbsp;&nbsp;&nbsp;'.$article.'<br />
					&nbsp;&nbsp;&nbsp;Тираж: '.$quantity.' шт.<br />
					&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($summ,2,'.',' ').'руб.</nobr><br />';
					if(isset($print_details)){
					    $description_cell .= '<b>Лого:</b><br />';
						$description_cell .= implode('<br />',$print_details);
					    $description_cell .= '<br /><b>Стоимость сувенира + лого:</b><br />';
						$description_cell .= implode('<br />',$pos_and_print_cost);
				    }
					
					$tbl_rows[] = $tr_td.$img_cell.$td_td.$description_cell.$td_tr;
					$description_cell = $print_description ='';
					unset($print_details);
					unset($pos_and_print_cost);
				}
			}
		    /********************   ++++++++++++++  *********************/
			
			
			$kp_content .= implode('',$tbl_rows).'</table>
		   <div style="text-align:right;font-family:verdana;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div></div>';
		   
		   
		   return $kp_content;
			
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
			$kp_content = '<div style="width:625px;background-color:#FFFFFF;"><div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><br>В компанию: '.$client_data_arr['comp_full_name'].'<br>Кому: '.$cont_face_data_arr['name'].'<br>Контакты: '.$cont_face_data_arr['phone'].'<br>'.$cont_face_data_arr['email'].'<br><br></div>
			<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center">Коммерческое предложение</div>';
			$kp_content .=  '<table width="625"  style="border:#CCCCCC solid 1px; border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top">';
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
				
				if(strpos($article_name,'<br>') == true) $article_name = str_replace('<br>','<br />',$article_name);
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
				
				$description_str = str_replace('<br>',' ',$description_str);
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
				
				if(strpos($article_name,'<br>') == true) $article_name = str_replace('<br>','<br />',$article_name);
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
				
				if(strpos($print_description,'<br>') == true) $print_description = str_replace('<br>','<br />',$print_description);
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
		   $kp_content .= implode('',array_reverse($tbl_rows)).'</td></tr></table>
		   <div style="text-align:right;font-family:verdana;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div></div>';
		   
		   return $kp_content;
	   }
	   static function create_list($query_num,$client_id,$certain_kp = FALSE){
	        
	        // общая выборка данных из базы данных производится на основании номера заказа для КП нового типа
			// и на основании client_id для КП старого типа
			// КП старого типа выводятся общим списком( все КП для даного клиента) отдельно от КП нового типа
			// выборка конкретного КП производится на id основании конкретного КП для КП нового типа
			// и на основании имени файла для старого КП

		    $rows = '';
			if(!$certain_kp){// если не указан конкретный КП создаем полный список
				$rows .= self::create_list_new_version($query_num);
				$rows .= "<tr><td class='flank_cell'>&nbsp;</td><td colspan='8'>КП старого типа</td><td class='flank_cell'>&nbsp;</td></tr>";
				$rows .= self::create_list_old_version($client_id);
            }
			else{
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
		   
		   echo $query_num;
		   
		   $rows = '';
		   
		   
		   $query="SELECT*FROM `".KP_LIST."` WHERE `query_num` = '".$query_num."'";
		   if($certain_kp_id)$query.= " AND id = '".$certain_kp_id."'";
		   $query.= " ORDER BY id DESC";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		        ob_start();
		        while($row=$result->fetch_assoc()){ //
				     $client_id = $row['client_id'] ;
				     $send_time = substr($row['send_time'],0,10);
					 $send_time = ($send_time!='0000-00-00')? $send_time:'не отправленно';
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
						 //echo $dir_name.'/'.$file.'<br>';
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
					// echo $file.'<br>'; 
					// функция stat не подходит под unix //$stat = stat($file);
					//if($user_nickname == 'andrey') echo $file.' '.$stat[10].' '.filemtime($file).' ';
					//$data_mod = date("ymdHis", filemtime($file));
					// if($user_nickname == 'andrey') echo $data_mod.' <br>';
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
					 $comment = (isset($comments_arr[md5($file)]))? $comments_arr[md5($file)] : 'добавьте свой комментарий' ;
					 $comment_style = (trim($comment) == 'добавьте свой комментарий')? 'italic grey' : '' ;
					 //$file = trim($file);
					 eval(' ?>'.$rows_template.'<?php ');
				}	
				$rows .= ob_get_contents();
				ob_get_clean();	
			}
			return $rows;
		}
   }












?>