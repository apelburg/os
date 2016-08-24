<?php  
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);



	$supplier_id = (isset($_GET['suppliers_id']) && $_GET['suppliers_id']!=0)?$_GET['suppliers_id']:0;


	$url_string = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if(isset($_GET['supplier_edit'])){
		$url_string = str_replace("&supplier_edit", "", $url_string);
	}else{
		$url_string .= "&supplier_edit";
	}

	$quick_button = '<div class="quick_button_div"><a href="'.$url_string.'" id="" class="button '.((isset($_GET['supplier_edit']))?'add':'edit').'">'.((isset($_GET['supplier_edit']))?'Сохранить':'Редактировать').'</a></div>';


	$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';

	/////////////////////////////////// AJAX //////////////////////////////////////

	if(isset($_POST['AJAX'])){


		if ($_POST['AJAX'] == "edit_adress_row") {
	        $id_row = $_POST['id'];
	        $tbl = "CLIENT_ADRES_TBL";
	        //-- START -- //  логирование
	        $client_name_i = Supplier::get_supplier_name($client_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' отредактировал адрес клиента '.$client_name_i.' ';
	        
	        Supplier::history_edit_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
	        //-- END -- //  

	        //-- START --// сохранение данных
	        global $mysqli;
	        $query = "UPDATE  `" . constant($_POST['tbl']) . "` SET  
				`city` =  '" . $_POST['city'] . "',
				`street` =  '" . $_POST['street'] . "',
				`house_number` =  '" . $_POST['house_number'] . "', 
				`korpus` =  '" . $_POST['korpus'] . "',
				`office` =  '" . $_POST['office'] . "',
				`liter` =  '" . $_POST['liter'] . "', 
				`bilding` =  '" . $_POST['bilding'] . "',
				`postal_code` =  '" . $_POST['postal_code'] . "',
				`note` =  '" . $_POST['note'] . "' WHERE  `id` ='" . $_POST['id'] . "';";
	        $result = $mysqli->query($query) or die($mysqli->error);
	        echo '{
			       "response":"1",
			       "text":"Данные сохранены"
			      }';
	        //-- END --// сохранение данных
	        exit;
	    }

		if($_POST['AJAX']=="add_new_phone_row"){
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' добавил новый контактный телефон '.$supplier_name_i;
	        Supplier::history($user_id, $text_history ,'contact_face_new_form',$supplier_id);
	        //-- END -- //

			$query = "INSERT INTO `".CONT_FACES_CONTACT_INFO_TBL."` SET 
			`parent_id` ='".$_POST['client_id']."', 
			`table` = '".$_POST['parent_tbl']."', 
			`type` = 'phone', 
			`telephone_type` = '".$_POST['type_phone']."', 
			`contact` = '".$_POST['telephone']."',
			`dop_phone` = '".((trim($_POST['dop_phone'])!="" && is_numeric(trim($_POST['dop_phone'])))?trim($_POST['dop_phone']):'')."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;
			exit;
		}
		
		if($_POST['AJAX']=="update_reiting_cont_face"){
			// необходим перевод на новую структуру .... как в клиенте
			// или на старую .... так оставлять не дело
			$query = "UPDATE  `".SUPPLIERS_RATINGS_TBL."` SET  `rate` =  '".$_POST['rate']."' WHERE  `subject_id` = '".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
			       "response":"1",
			       "text":"Данные успешно сохранены"
			      }';
			exit;
		}
		if($_POST['AJAX']=="show_cont_face_in_json"){
			$query = "SELECT * FROM `".SUPPLIERS_CONT_FACES_TBL."` WHERE `id` = '".$_POST['id']."'";
			$arr = array();
			// echo $query;exit;
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}

			$my_json = json_encode($arr); 
			print $my_json; 
			exit;
		}

		if($_POST['AJAX']=="contact_face_edit_form"){
			$tbl = "SUPPLIERS_CONT_FACES_TBL";
			$id_row = $_POST['id'];
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' обновил информацию по контактному лицу '.$supplier_name_i;
	        Supplier::history_edit_type($supplier_id,$user_id, $text_history ,'contact_face_edit_form',$tbl,$_POST,$id_row);
	        //-- END -- //

			global $mysqli;
			$query = "UPDATE  `".SUPPLIERS_CONT_FACES_TBL."` SET  
			`surname` =  '".$_POST['surname']."',
			`last_name` =  '".$_POST['last_name']."',
			`name` =  '".$_POST['name']."', 
			`position` =  '".$_POST['position']."',
			`department` =  '".$_POST['department']."',
			`note` =  '".$_POST['note']."' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные успешно обновлены"
		      }';
			exit;
		}


		if($_POST['AJAX']=="contact_face_new_form"){
			$tbl = "SUPPLIERS_CONT_FACES_TBL";
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' добавил новое контактное лицо '.$supplier_name_i;
	        Supplier::history($user_id, $text_history ,'contact_face_new_form',$supplier_id);
	        //-- END -- //

			global $mysqli;
			$query = "INSERT INTO  `".SUPPLIERS_CONT_FACES_TBL."` SET  
			`supplier_id` =  '".$_POST['parent_id']."',
			`surname` =  '".$_POST['surname']."',
			`last_name` =  '".$_POST['last_name']."',
			`name` =  '".$_POST['name']."', 
			`position` =  '".$_POST['position']."',
			`department` =  '".$_POST['department']."',
			`note` =  '".$_POST['note']."' ";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "id":"'.$mysqli->insert_id.'",
		       "text":"Данные успешно обновлены"
		      }';
			exit;
		}


		if($_POST['AJAX']=="remove_curator"){
			$query = "DELETE FROM `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` WHERE `supplier_id` = '".$_GET['suppliers_id']."' AND `activity_id` = '".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo "Delete OK";
			exit;
		}


		if($_POST['AJAX']=="chenge_name_company"){
			$id = $_POST['id'];
			$tbl = $_POST['tbl'];
			$company = $_POST['company'];
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' обновил информацию по поставщику '.$supplier_name_i;
	        Supplier::history_edit_type($supplier_id,$user_id, $text_history ,'chenge_name_company',$tbl,$_POST,0);
	        //-- END -- //

			//тут обновляем название компании
			global $mysqli;
			$query = "UPDATE  `".constant($tbl)."` SET  `nickName` =  '".$company."' WHERE  `id` ='".$id."'; ";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
			exit;
		}

		if($_POST['AJAX']=="chenge_fullname_company"){
			$id = $_POST['id'];
			$tbl = $_POST['tbl'];
			$company = $_POST['company'];
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' обновил информацию по поставщику '.$supplier_name_i;
	        Supplier::history_edit_type($supplier_id,$user_id, $text_history ,'chenge_fullname_company',$tbl,$_POST,$id);
	        //-- END -- //
			//тут обновляем название компании
			global $mysqli;
			$query = "UPDATE  `".constant($tbl)."` SET  `fullName` =  '".$company."' WHERE  `id` ='".$id."'; ";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
			exit;
		}

		if($_POST['AJAX']=="new_adress_row"){
			ob_start();
			include('./skins/tpl/suppliers/supplier_data/new_adres.tpl');
			$content = ob_get_contents();
			ob_get_clean();
			echo $content;
			exit;
		}

		if($_POST['AJAX']=="get_adres"){
			$id_row = $_POST['id_row'];
			$tbl = "CLIENT_ADRES_TBL";
			$query = "SELECT * FROM ".constant($tbl)." WHERE `id` = '".$id_row."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr_adres = $row;
				}
			}
			extract($arr_adres, EXTR_PREFIX_SAME, "wddx");
			//получаем контент для окна 
			ob_start();
			include('./skins/tpl/suppliers/supplier_data/edit_adres.tpl');
			$content = ob_get_contents();
			ob_get_clean();
			echo $content;
			exit;
		}

		if($_POST['AJAX']=="add_new_adress_row"){
			$tbl = 'CLIENT_ADRES_TBL';
			$query = "";
			$adres_type = (isset($_POST['adress_type']) && $_POST['adress_type']!="")?$_POST['adress_type']:'office';
			$query = "INSERT INTO `".constant($tbl)."` SET 
			`parent_id` = '".addslashes($_POST['parent_id'])."',
			`table_name` = '".addslashes($_POST['tbl'])."',
			`adress_type` = '".addslashes($adres_type)."',
			`city` = '".addslashes($_POST['city'])."',
			`street` = '".addslashes($_POST['street'])."',
			`house_number` = '".addslashes($_POST['house_number'])."',
			`korpus` = '".addslashes($_POST['korpus'])."',
			`office` = '".addslashes($_POST['office'])."',
			`liter` = '".addslashes($_POST['liter'])."',
			`bilding` = '".addslashes($_POST['bilding'])."',
			`postal_code` = '".addslashes($_POST['postal_code'])."',
			`note` = '".addslashes($_POST['note'])."'
			;";
			//echo "$query";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;
			exit;
		}


		if($_POST['AJAX']=="add_new_other_row"){
			//-- START -- //  логирование
	        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента
	        $user_n = $user_name.' '.$user_last_name;
	        $text_history = $user_n.' добавил новый доп. контакт к поставщику '.$supplier_name_i;
	        Supplier::history($user_id, $text_history ,'add_new_other_row',$supplier_id);
	        //-- END -- //

			$query= "INSERT INTO `".CONT_FACES_CONTACT_INFO_TBL."` SET 			
			
			`parent_id` ='".$_POST['client_id']."', 
			`table` = '".$_POST['parent_tbl']."', 
			`type` = '".$_POST['type']."', 
			`telephone_type` = '', 
			`contact` = '".$_POST['input_text']."',
			`dop_phone` = '';";
			 // echo "$query";exit;
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;			
			exit;
		}

		if($_POST['AJAX']=="get_suppliers_profile"){
			$query = "SELECT * FROM  `".SUPPLIERS_ACTIVITIES_TBL."` ORDER BY  `name` ASC ";
			$result = $mysqli->query($query) or die($mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $profile[] = $row;
	            }
	        }

	        $num_rows = floor(count($profile)/3);
	        // получаем список кураторов
	        $get_activities_arr = Supplier::get_activities($_GET['suppliers_id']);

	        $num = 0;
	        $html = '';
	        foreach ($profile as $key => $value) {
	        	if(trim($value['name'])!=""){
	        	// перебираем всех кураторов
	        	// если профиль прикреплён добавляем ему класс enabled
	        	$enable = '';
	        	foreach($get_activities_arr as $k => $v){
	        		if($v['activity_id']==$value['id']){
	        			$enable = 'enabled';
	        		}
	        	}
	        	
	        	$str = '<span data-id="'.$value['id'].'" class="chose_curators '.$enable.'">'.$value['name'].'</span>';
		        	
		        if($num==0){
		        	$str = '<div class="column_chose_window">'.$str;
		        }else if($num==$num_rows){
		        	$str = $str.'</div>';
		        	$num=-1;
		        }
		          $html .= $str;
	        	
	        	$num++;
	        	}
	        }
	        echo $html;
	    	exit;

		}

		if ($_POST['AJAX'] == "update_profile_list_for_supplier") {
			global $mysqli;
			$suppliers_id = $_GET['suppliers_id'];
			$json = $_POST['profile_id'];
			$activity_id = json_decode($json,true);
			$str_id = '';
			$query = "";
			foreach($activity_id as $k => $v){

				$query .= "INSERT INTO `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` SET 
				`activity_id` = '".$v."', 
				`supplier_id` = '".$suppliers_id."';";

				$str_id .= ($str_id=='')?$v:', '.$v;
			}
			// echo $str_id;
			$query1 = "DELETE FROM `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` WHERE `supplier_id` = '".$suppliers_id."';";
			// $result = $mysqli->query($query) or die($mysqli->error);
			// ECHO $query;
			$result = $mysqli->multi_query($query1.$query) or die($mysqli->error);
			echo '{
				   "response":"1",
				   "text":"Данные успешно обновлены"
				  }';
			exit;
    	}



		###########################################
		##  DEFAULT
		###########################################

	
	}


	
	 
	 /////////////////////////////////// AJAX //////////////////////////////////////	
	
	$supplierClass = new Supplier($_GET['suppliers_id']);

	$supplier = $supplierClass->info;

	$cont_company_phone = $supplierClass->cont_company_phone;
	$cont_company_other = $supplierClass->cont_company_other;

	// получаем адрес
	$supplier_address = Supplier::get_addres($_GET['suppliers_id']);

	$contact_faces_contacts = Supplier::cont_faces($_GET['suppliers_id']);

	$edit_show = (isset($_GET['supplier_edit']))?'admin_':'';

	$adress_name_arr = array('office' => 'офиса', 'delivery' => 'доставки' );

	$supplier = $supplierClass->info;
	################################

	if($supplier==0){
		//такого поставщика не существует
		$quick_button = '<div class="quick_button_div"><a href="http://'.$_SERVER['SERVER_NAME'].'/os/?page=suppliers&section=suppliers_list" id="" class="button ">Показать всех</a></div>';
		include('./skins/tpl/suppliers/supplier_data/default.tpl'); 	
	}else{
		//получаем информацию по профилям поставщика
		$get_activities_arr = Supplier::get_activities($_GET['suppliers_id']);
		// echo '<pre>';
		// print_r($get_activities_arr);
		// echo '</pre>';exit;
		$get_activities = '';
	    
	    foreach ($get_activities_arr as $k => $v) {
	    	$del = (isset($_GET['supplier_edit']))?'<span class="del_curator">X</span>':'';
	        $get_activities.= '<span class="add_del_curator curator_names" data-id="' . $v['activity_id'] . '"><span>' . $v['name'] . '</span>'.$del.'</span>';
	    }

	    $get_activities.= (isset($_GET['supplier_edit']))?'<span class="add_del_curator" id="add_curator"> + </span>':'';


		// получаем рейтинг компании
		$supplierRating = Supplier::get_reiting($supplier_id);

		//получаем текущий адрес поставщика
		ob_start();
		foreach ($supplier_address as $adress_number => $adress) {			
			include('./skins/tpl/suppliers/supplier_data/supplier_adress_row.tpl');
		}

		$supplier_address_s .= ob_get_contents();
		ob_get_clean();

		//получаем информацию по поставщику
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_table.tpl');
		$supplier_content = ob_get_contents();
		ob_get_clean();

		
		//получаем информацию по контактным лицам данного поставщика
		ob_start();
		$supplier_content_contact_faces = "";
		$contact_face_d_arr = array();
		foreach($contact_faces_contacts as $k=>$this_contact_face){
			//print_r($this_contact_face);
			$contact_face_d_arr = $supplierClass->get_contact_info("SUPPLIERS_CONT_FACES_TBL",$this_contact_face['id']);
			// echo "<pre>";
			// print_r($contact_face_d_arr);
			// echo "</pre>";
			$cont_company_phone = (isset($contact_face_d_arr['phone']))?$contact_face_d_arr['phone']:''; 
			$cont_company_other = (isset($contact_face_d_arr['other']))?$contact_face_d_arr['other']:'';
			

			// шаблон для вывода контактных лиц
			include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_cotact_face_table.tpl');
		}

		$supplier_content_contact_faces .= ob_get_contents();
		ob_get_clean();
		// AJAX
		// на случай выдачи контента только с контактными лицами
		if(isset($_POST['AJAX']) && $_POST['AJAX']=="get_empty_cont_face"){
			echo $supplier_content_contact_faces;
			exit;
		}
		// ALAX END


		//получаем адрес папки и примечания
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_dop_info.tpl');
		$supplier_content_dop_info = ob_get_contents();
		ob_get_clean();

		// получаем подготовленный контент для модальных окон
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/dialog_windows.tpl');
		$dialog_windows = ob_get_contents();
		ob_get_clean();



		include('./skins/tpl/common/quick_bar.tpl');

		//выводим общий шаблон
		include('./skins/tpl/suppliers/supplier_data/show.tpl'); 
		// echo "<pre>";
		// print_r($supplierClass);
		// echo "</pre>";

	}





?>
