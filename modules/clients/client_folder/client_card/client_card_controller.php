<?php
    save_way_back(array('page=clients','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();

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


$clientClass = new Client($client_id);



if ($clientClass->user_access != '2'){

    $quick_button = '<div class="quick_button_div"><a href="' . $url_string . '" id="" class="button ' . ((isset($_GET['client_edit'])) ? 'add' : 'edit') . '">' . ((isset($_GET['client_edit'])) ? 'Сохранить' : 'Редактировать') . '</a></div>';
}

$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';

////////////////////////////// AJAX ///////////////////////////////////////

// перенесено в client_class.php

/////////////////////////////////////  AJAX END /////////////////////////////////



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
    // echo $client_id;
    // получаем реквизиты компании
    $requisites = Client::get_requisites($client_id);
    
    // кураторы
    $manager_names_arr = Client::get_relate_managers($client_id);
    $manager_names = '';
    
    foreach ($manager_names_arr as $men_id => $value) {
        $del = (isset($_GET['client_edit']))?'<span class="del_curator">X</span>':'';
        $manager_names.= '<span class="add_del_curator curator_names" data-id="' . $value['id'] . '"><span>' . $value['name'] . ' ' . $value['last_name'] . '</span>'.$del.'</span>';
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
    if (isset($_POST['AJAX']) && $_POST['AJAX'] == "get_empty_cont_face") {
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