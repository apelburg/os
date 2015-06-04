<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$url_string = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
if (isset($_GET['client_edit'])) {
    $url_string = str_replace("&client_edit", "", $url_string);
} 
else {
    $url_string.= "&client_edit";
}

$quick_button = '<div class="quick_button_div"><a href="' . $url_string . '" id="" class="button ' . ((isset($_GET['client_edit'])) ? 'add' : 'edit') . '">' . ((isset($_GET['client_edit'])) ? 'Сохранить' : 'Редактировать') . '</a></div>';

$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';

////////////////////////////// AJAX ///////////////////////////////////////
//обрабатываем ajax запросы из стандартного окна ОС
if (isset($_POST['ajax_standart_window'])) {
    
    if ($_POST['ajax_standart_window'] == "chenge_name_company") {
        $tbl = $_POST['tbl'];
        $company = $_POST['company'];
        $id_row = $_POST['id'];
        $tbl = "CLIENTS_TBL";
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' изменил название клиента ';
        Client::history_edit_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
        //-- END -- //  


        //тут обновляем название компании
        global $mysqli;
        $query = "UPDATE  `" . constant($tbl) . "` SET  `company` =  '" . $company . "' WHERE  `id` ='" . $id_row . "'; ";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "get_adres") {
        $id_row = $_POST['id_row'];
        $tbl = "CLIENT_ADRES_TBL";
        $query = "SELECT * FROM " . constant($tbl) . " WHERE `id` = '" . $id_row . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        extract($arr_adres, EXTR_PREFIX_SAME, "wddx");
        
        //получаем контент для окна
        ob_start();
        include ('./skins/tpl/clients/client_folder/client_card/edit_adres.tpl');
        $content = ob_get_contents();
        ob_get_clean();
        echo $content;
        exit;
    }
    if ($_POST['ajax_standart_window'] == "edit_adress_row") {
        $id_row = $_POST['id'];
        $tbl = "CLIENT_ADRES_TBL";
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' отредактировал адрес клиента '.$client_name_i.' ';
        Client::history_edit_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
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
    if ($_POST['ajax_standart_window'] == "delete_adress_row") {
        $id_row = $_POST['id_row'];
        $tbl = "CLIENT_ADRES_TBL";
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' удалил(а) адрес клиента '.$client_name_i.' ';
        Client::history_delete_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
        //-- END -- //  

        $query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
        exit;
    }
    if ($_POST['ajax_standart_window'] == "add_new_adress_row") {
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' создал новый адрес для клиента '.$client_name_i.' ';
        Client::history($user_id, $text_history ,'add_new_adress_row',$_GET['client_id']);
        //-- END -- //  логирование


        $tbl = 'CLIENT_ADRES_TBL';
        $query = "";
        $adres_type = (isset($_POST['adress_type']) && $_POST['adress_type'] != "") ? $_POST['adress_type'] : 'office';
        $query = "INSERT INTO `" . constant($tbl) . "` SET 
			`parent_id` = '" . addslashes($_POST['parent_id']) . "',
			`table_name` = '" . addslashes($_POST['tbl']) . "',
			`adress_type` = '" . addslashes($adres_type) . "',
			`city` = '" . addslashes($_POST['city']) . "',
			`street` = '" . addslashes($_POST['street']) . "',
			`house_number` = '" . addslashes($_POST['house_number']) . "',
			`korpus` = '" . addslashes($_POST['korpus']) . "',
			`office` = '" . addslashes($_POST['office']) . "',
			`liter` = '" . addslashes($_POST['liter']) . "',
			`bilding` = '" . addslashes($_POST['bilding']) . "',
			`postal_code` = '" . addslashes($_POST['postal_code']) . "',
			`note` = '" . addslashes($_POST['note']) . "'
			;";
        
        $result = $mysqli->query($query) or die($mysqli->error);
        echo $mysqli->insert_id;
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "new_adress_row") {
        ob_start();
        include ('./skins/tpl/clients/client_folder/client_card/new_adres.tpl');
        $content = ob_get_contents();
        ob_get_clean();
        echo $content;
        exit;
    }
    if ($_POST['ajax_standart_window'] == "add_new_phone_row") {
        
        $query = "INSERT INTO `" . CONT_FACES_CONTACT_INFO_TBL . "` SET 
			`parent_id` ='" . $_POST['client_id'] . "', 
			`table` = '" . $_POST['parent_tbl'] . "', 
			`type` = 'phone', 
			`telephone_type` = '" . $_POST['type_phone'] . "', 
			`contact` = '" . $_POST['telephone'] . "',
			`dop_phone` = '" . ((trim($_POST['dop_phone']) != "" && is_numeric(trim($_POST['dop_phone']))) ? trim($_POST['dop_phone']) : '') . "';";
        
        
        $result = $mysqli->query($query) or die($mysqli->error);
        $id_i = $mysqli->insert_id;

        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' завел новый контактный телефон для клиента '.$client_name_i.'(id = '.$id_i.') ';
        Client::history($user_id, $text_history ,'add_new_phone',$_POST['client_id']);
        //-- END -- //  логирование

        echo $id_i;
        exit;
    }
    if ($_POST['ajax_standart_window'] == "add_new_other_row") {
        $query = "INSERT INTO `" . CONT_FACES_CONTACT_INFO_TBL . "` SET 			
			
			`parent_id` ='" . $_POST['client_id'] . "', 
			`table` = '" . $_POST['parent_tbl'] . "', 
			`type` = '" . $_POST['type'] . "', 
			`telephone_type` = '', 
			`contact` = '" . $_POST['input_text'] . "',
			`dop_phone` = '';";
        
        $result = $mysqli->query($query) or die($mysqli->error);
        $insert_id = $mysqli->insert_id;
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;        
        $text_history = $user_n.' завел новую запись '.$_POST['type'].' для клиента '.$client_name_i.'(id = '.$insert_id.') ';
        Client::history($user_id, $text_history ,'add_new_other',$_POST['client_id']);
        //-- END -- //  логирование

        echo $insert_id;
        exit;
    }
    if ($_POST['ajax_standart_window'] == "delete_dop_cont_row") {
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $tbl = "CONT_FACES_CONTACT_INFO_TBL";
        $id_row = $_POST['id'];
        Client::history_delete_type($client_id,$user_id, $text_history ,'delete_dop_cont_row',$tbl,$_POST,$id_row);
        //-- END -- //  логирование

        $query = "DELETE FROM `" . CONT_FACES_CONTACT_INFO_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo "OK";
        exit;
    }
    if ($_POST['ajax_standart_window'] == "show_cont_face_in_json") {
        $query = "SELECT * FROM `" . CLIENT_CONT_FACES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
        $arr = array();
        
        // echo $query;exit;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr[] = $row;
            }
        }
        
        $my_json = json_encode($arr);
        print $my_json;
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "contact_face_edit_form") {
        $id_row = $_POST['id'];
        $tbl = "CLIENT_CONT_FACES_TBL";

        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' отредактировал данные из контактного лица '.$client_name_i.' ';

        Client::history_edit_type($client_id, $user_id, $text_history ,'edit_contact_face',$tbl,$_POST,$id_row);
        //-- END -- //  логирование

        global $mysqli;
        $query = "UPDATE  `" . constant($tbl) . "` SET  
			`surname` =  '" . $_POST['surname'] . "',
			`last_name` =  '" . $_POST['last_name'] . "',
			`name` =  '" . $_POST['name'] . "', 
			`position` =  '" . $_POST['position'] . "',
			`department` =  '" . $_POST['department'] . "',
			`note` =  '" . $_POST['note'] . "' WHERE  `id` ='" . $id_row . "';";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные успешно обновлены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "contact_face_new_form") {
        global $mysqli;
        $query = "INSERT INTO  `" . CLIENT_CONT_FACES_TBL . "` SET  
			`client_id` =  '" . $_POST['parent_id'] . "',
			`surname` =  '" . $_POST['surname'] . "',
			`last_name` =  '" . $_POST['last_name'] . "',
			`name` =  '" . $_POST['name'] . "', 
			`position` =  '" . $_POST['position'] . "',
			`department` =  '" . $_POST['department'] . "',
			`note` =  '" . $_POST['note'] . "' ";
        $result = $mysqli->query($query) or die($mysqli->error);

        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' создал новый контакт для клиента '.$client_name_i.' ';
        Client::history($user_id, $text_history ,'add_new_contact_row',$_GET['client_id']);
        //-- END -- //  логирование
        echo '{
		       "response":"1",
		       "id":"' . $mysqli->insert_id . '",
		       "text":"Данные успешно добавлены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "edit_client_dop_information") {
        $id_row = $_POST['id'];
        $tbl = "CLIENTS_TBL";
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' обновил блок доп. инфо. у клиента '.$client_name_i.' ';
        Client::history_edit_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
        //-- END -- //  

        global $mysqli;
        $query = "UPDATE  `" . CLIENTS_TBL . "` SET  
			`dop_info` =  '" . $_POST['dop_info'] . "',
			`ftp_folder` =  '" . $_POST['ftp_folder'] . "' WHERE  `id` ='" . $_POST['id'] . "';";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные успешно обновлены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "delete_cont_face_row") {
        $id_row = $_POST['id'];
        $tbl = "CLIENT_CONT_FACES_TBL";
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' удалил контактное лицо у клиента '.$client_name_i;
        Client::history_delete_type($client_id,$user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
        //-- END -- //  

        global $mysqli;
        $query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        
        echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "delete_cont_requisits_row") {
        $id_row = $_POST['id'];
        $tbl = $_POST['tbl'];

        $query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
        exit;
    }

    if ($_POST['ajax_standart_window'] == "client_delete") {
        $outer = Client::delete_for_manager($_POST['id'], $user_id);
        if ($outer == '1') {
            $client_name_i = Client::get_client_name($client_id); // получаем название клиента
            $text = (isset($_POST['text']))?'Куратор '.$user_name.' '. $user_last_name.' отказался от клиента '.$client_name_i.'. Причина: '.$_POST['text']:'Куратор '.$user_name.' '. $user_last_name.' отказался от клиента не указав причину.';
            Client::history($user_id, $text ,'rejection_of_the_client',$_GET['client_id']);
            echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      	}';
        } 
        else {
            echo '{
		       "response":"0",
		       "text":"Что-то пошло не так."
		      	}';
        }
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "update_requisites") {
        global $mysqli;
        
        $query = "
			UPDATE  `" . CLIENT_REQUISITES_TBL . "` SET
			`client_id`='" . $_POST['client_id'] . "', 
			`company`='" . $_POST['company'] . "', 
			`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
			`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
			`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
			`inn`='" . $_POST['form_data']['inn'] . "', 
			`kpp`='" . $_POST['form_data']['kpp'] . "', 
			`bank`='" . $_POST['form_data']['bank'] . "', 
			`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
			`r_account`='" . $_POST['form_data']['r_account'] . "', 
			`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
			`ogrn`='" . $_POST['form_data']['bik'] . "', 
			`okpo`='" . $_POST['form_data']['okpo'] . "', 
			`dop_info`='" . $_POST['form_data']['dop_info'] . "' WHERE id = '" . $_POST['requesit_id'] . "';";
        
        foreach ($_POST['form_data']['managment1'] as $key => $val) {
            if (trim($val['id']) != "") {
                $query.= "UPDATE  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  
					`requisites_id` =  '" . $val['requisites_id'] . "',
					`type` =  '" . $val['type'] . "',
					`post_id` =  '" . $val['post_id'] . "',
					`basic_doc` =  '" . $val['basic_doc'] . "',
					`name` =  '" . $val['name'] . "',
					`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
					`acting` =  '" . $val['acting'] . "'
					WHERE  `id` ='" . $val['id'] . "'; ";
            } 
            else {
                $query.= "INSERT INTO  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  
					`requisites_id` =  '" . $val['requisites_id'] . "',
					`type` =  '" . $val['type'] . "',
					`post_id` =  '" . $val['post_id'] . "',
					`basic_doc` =  '" . $val['basic_doc'] . "',
					`name` =  '" . $val['name'] . "',
					`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
					`acting` =  '" . $val['acting'] . "';";
            }
        }
        $result = $mysqli->multi_query($query) or die($mysqli->error);
        echo '{
			    "response":"1",
				"text":"Данные успешно обновлены"
			}';
        
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "create_new_requisites") {
        global $mysqli;
        $query = "
			INSERT INTO `" . CLIENT_REQUISITES_TBL . "` SET id = '" . $_POST['requesit_id'] . "',
			`client_id`='" . $_POST['client_id'] . "', 
			`company`='" . $_POST['company'] . "', 
			`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
			`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
			`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
			`inn`='" . $_POST['form_data']['inn'] . "', 
			`kpp`='" . $_POST['form_data']['kpp'] . "', 
			`bank`='" . $_POST['form_data']['bank'] . "', 
			`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
			`r_account`='" . $_POST['form_data']['r_account'] . "', 
			`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
			`ogrn`='" . $_POST['form_data']['bik'] . "', 
			`okpo`='" . $_POST['form_data']['okpo'] . "', 
			`dop_info`='" . $_POST['form_data']['dop_info'] . "'
			";
        $result = $mysqli->query($query) or die($mysqli->error);
        
        // запоминаем id созданной записи
        $req_new_id = $mysqli->insert_id;
        
        if (isset($_POST['form_data']['managment1'])) {
            $query = "";
            foreach ($_POST['form_data']['managment1'] as $key => $val) {
                $query.= "INSERT INTO  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  
					`requisites_id` =  '" . $req_new_id . "',
					`type` =  '" . $val['type'] . "',
					`post_id` =  '" . $val['post_id'] . "',
					`basic_doc` =  '" . $val['basic_doc'] . "',
					`name` =  '" . $val['name'] . "',
					`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
					`acting` =  '" . $val['acting'] . "';";
            }
            
            $result = $mysqli->multi_query($query) or die($mysqli->error);
        }
        echo '{
			    "response":"1",
				"id_new_req":"' . $req_new_id . '",
				"company":"' . $_POST['company'] . '"
			}';        
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "delete_requesit_row") {
        $id_row = $_POST['id'];
        $query = "DELETE FROM " . CLIENT_REQUISITES_TBL . " WHERE `id`= '" . $id_row . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        
        echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "update_reiting_cont_face") {

        $query = "UPDATE  `" . CLIENTS_TBL . "` SET  `rate` =  '" . $_POST['rate'] . "' WHERE  `id` = '" . $_POST['id'] . "';";
        $result = $mysqli->query($query) or die($mysqli->error);
        echo '{
		       "response":"1",
		       "text":"Данные успешно сохранены"
		      }';
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "show_requesit") {
        $query = "SELECT * FROM `" . CLIENT_REQUISITES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
        $requesit = array();
        
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requesit = $row;
            }
        }
        include ('./skins/tpl/clients/client_folder/client_card/show_requsits.tpl');
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "edit_requesit") {
        $query = "SELECT * FROM `" . CLIENT_REQUISITES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
        $requesit = array();
        
        // echo $query;exit;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requesit = $row;
            }
        }
        
        // получаем список должностей для персональных данных контактных лиц из реквизитов
        //$get__clients_persons_for_requisites = Client::get__clients_persons_for_requisites($client_id);
        // получаем контактные лица для реквизитов
        
        include ('./skins/tpl/clients/client_folder/client_card/edit_requsits.tpl');
        exit;
    }
    
    if ($_POST['ajax_standart_window'] == "create_requesit") {
        
        include ('./skins/tpl/clients/client_folder/client_card/new_requsits.tpl');
        exit;
    }
    if ($_POST['ajax_standart_window'] == "get_manager_lis_for_curator") {
    	$query = "SELECT * FROM ".MANAGERS_TBL." ORDER BY  `name` ASC ";
    	$requesit = array();
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $managers[] = $row;
            }
        }
        $num_rows = floor(count($managers)/3);
        //получаем список менеджеров прикреплённых к клиенту
        $curators_arr = Client::get_relate_managers($client_id);
        
        $num = 0;
        $html = '';
        foreach ($managers as $key => $value) {
        	if(trim($value['name'])!="" || trim($value['last_name'])!=""){
        	// перебираем всех менеджеров
        	// если менеджер прикреплён добавляем ему класс enabled
        	$enable = '';
        	foreach($curators_arr as $k => $v){
        		if($v['id']==$value['id']){
        			$enable = 'enabled';
        		}
        	}
        	
        	$str = '<span data-id="'.$value['id'].'" class="chose_curators '.$enable.'">'.$value['name'].' '.$value['last_name'].'</span>';
	        	
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
    if ($_POST['ajax_standart_window'] == "update_curator_list_for_client") {
        global $mysqli;
        $client_id = $_GET['client_id'];
        $json = $_POST['managers_id'];
        $manager_id = json_decode($json,true);
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' обновил список кураторов для клиента '.$client_name_i;
        Client::history($user_id, $text_history ,'update_curator_list',$_GET['client_id']);
        //-- END -- //  логирование
        
        $str_id = '';
        $query = "";
        foreach($manager_id as $k => $v){

            $query .= "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` SET 
            `client_id` = '".$client_id."', 
            `manager_id` = '".$v."';";

            $str_id .= ($str_id=='')?$v:', '.$v;
        }
        // echo $str_id;
        $query1 = "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".$client_id."';";
        // $result = $mysqli->query($query) or die($mysqli->error);
        // ECHO $query;
        $result = $mysqli->multi_query($query1.$query) or die($mysqli->error);
        echo '{
               "response":"1",
               "text":"Данные успешно обновлены"
              }';
        exit;


    }
    if ($_POST['ajax_standart_window'] == "remove_curator") {
        $client_id = $_GET['client_id'];
        $manager_id = $_POST['id'];
        Client::remove_curator($client_id,$manager_id);
        //-- START -- //  логирование
        $client_name_i = Client::get_client_name($client_id); // получаем название клиента
        $manager_name_i = Client::get_manager_name($manager_id);// получаем Фамилию Имя менеджера
        $user_n = $user_name.' '.$user_last_name;
        $text_history = $user_n.' удалил куратора '.$manager_name_i.' у клиента '.$client_name_i;
        Client::history($user_id, $text_history ,'remove_curator',$_GET['client_id']);
        //-- END -- //  логирование

        exit;
    }
    if ($_POST['ajax_standart_window'] == "new_person_type_req") {
        global $mysqli;
        $query = "INSERT INTO  `" . CLIENT_PERSON_REQ_TBL . "` SET  shouldBe();
			`type` =  '',
			`position` =  '" . $_POST['position'] . "',
			`position_in_padeg` =  '" . $_POST['position_in_padeg'] . "'";
        $result = $mysqli->query($query) or die($mysqli->error);
        
        // echo $query;
        $id_row = $mysqli->insert_id;
        echo '{
		       "response":"1",
		       "id_new_row":"' . $id_row . '",
		       "text":"Данные успешно обновлены"
		      }';
        exit;
    }
}

/////////////////////////////////////  AJAX END /////////////////////////////////

$clientClass = new Client($client_id);

$cont_company_phone = $clientClass->cont_company_phone;
$cont_company_other = $clientClass->cont_company_other;

$client = $clientClass->info;
if ($client == 0) {
    
    //такого клиента не существует
    $quick_button = '<div class="quick_button_div"><a href="http://' . $_SERVER['SERVER_NAME'] . '/os/?page=clients&section=clients_list" id="" class="button ">Показать всех</a></div>';
    include ('./skins/tpl/clients/client_folder/client_card/default.tpl');
} 
else {
    
    // получаем рейтинг компании
    $clientRating = Client::get_reiting($client_id, $client['rate']);
    
    // получаем реквизиты компании
    $requisites = Client::get_requisites($client_id);
    
    // кураторы
    $manager_names_arr = Client::get_relate_managers($client_id);
    $manager_names = '';
    
    foreach ($manager_names_arr as $k => $v) {
        $del = (isset($_GET['client_edit']))?'<span class="del_curator">X</span>':'';
        $manager_names.= '<span class="add_del_curator curator_names" data-id="' . $v['id'] . '"><span>' . $v['name'] . ' ' . $v['last_name'] . '</span>'.$del.'</span>';
    }    
    $manager_names.= (isset($_GET['client_edit']))?'<span class="add_del_curator" id="add_curator"> + </span>':'';
    


    $contact_faces_contacts = Client::cont_faces($client_id);
    
    $client_address = Client::get_addres($client_id);
    
    $edit_show = (isset($_GET['client_edit'])) ? 'admin_' : '';
    
    $adress_name_arr = array('office' => 'офиса', 'delivery' => 'доставки');
    
    //получаем текущий адрес клиента
    ob_start();
    foreach ($client_address as $adress_number => $adress) {
        include ('./skins/tpl/clients/client_folder/client_card/client_adress_row.tpl');
    }
    
    $client_address_s.= ob_get_contents();
    ob_get_clean();
    
    //получаем информацию по клиенту
    ob_start();
    include ('./skins/tpl/clients/client_folder/client_card/' . $edit_show . 'client_table.tpl');
    $client_content = ob_get_contents();
    ob_get_clean();
    
    //получаем информацию по контактным лицам данного клиента
    ob_start();
    $client_content_contact_faces = "";
    $contact_face_d_arr = array();
    foreach ($contact_faces_contacts as $k => $this_contact_face) {
        
        //print_r($this_contact_face);
        $contact_face_d_arr = $clientClass->get_contact_info("CLIENT_CONT_FACES_TBL", $this_contact_face['id']);
        
        // echo "<pre>";
        // print_r($contact_face_d_arr);
        // echo "</pre>";
        $cont_company_phone = (isset($contact_face_d_arr['phone'])) ? $contact_face_d_arr['phone'] : '';
        $cont_company_other = (isset($contact_face_d_arr['other'])) ? $contact_face_d_arr['other'] : '';
        
        //echo $clientClass->$this->get_contact_info("CLIENTS_TBL",$id)($contact_face_d_arr, 'phone',Client::$array_img);
        include ('./skins/tpl/clients/client_folder/client_card/' . $edit_show . 'client_cotact_face_table.tpl');
    }
    
    $client_content_contact_faces.= ob_get_contents();
    ob_get_clean();
    
    // AJAX
    // на случай выдачи контента только с контактными лицами
    if (isset($_POST['ajax_standart_window']) && $_POST['ajax_standart_window'] == "get_empty_cont_face") {
        echo $client_content_contact_faces;
        exit;
    }
    
    // ALAX END
    
    //получаем адрес папки и примечания
    ob_start();
    include ('./skins/tpl/clients/client_folder/client_card/' . $edit_show . 'client_dop_info.tpl');
    $client_content_dop_info = ob_get_contents();
    ob_get_clean();
    
    // получаем подготовленный контент для модальных окон
    ob_start();
    include ('./skins/tpl/clients/client_folder/client_card/dialog_windows.tpl');
    $dialog_windows = ob_get_contents();
    ob_get_clean();
    
    //выводим общий шаблон
    include ('./skins/tpl/clients/client_folder/client_card/show.tpl');
}
?>