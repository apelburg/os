<?php
   
    $quick_button = '<div class="quick_button_div"><a href="#11" class="button">&nbsp;</a></div>';
	$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';

	
	// Собираем ряды для таблицы коммерческих предложений
	// выборка данных из базы данных производится на основании номера зпароса для КП нового типа 
	// и на основании client_id для КП старого типа
	if($create_list) $rows = Com_pred::create_list($query_num,$client_id);
	// Подключаем шаблон таблицы списка коммерческих предложений
	include ('skins/tpl/client_folder/business_offers/list_table.tpl');
	if(isset($detailed_view)) include ('skins/tpl/client_folder/business_offers/detailed_view.tpl');
	if(isset($in_blank_view)){
	     if(isset($_GET['show_old_kp'])) include ('skins/tpl/client_folder/business_offers/in_blank_view_old.tpl');
		 else{
		     $query="SELECT display_setting_2 FROM`".KP_LIST."` WHERE `id` = '".$kp_id."'";
		     $result = $mysqli->query($query)or die($mysqli->error);
			 $row = $result->fetch_assoc();
			 $display_setting_2 = $row['display_setting_2'];
		     include ('skins/tpl/client_folder/business_offers/in_blank_view.tpl');
		 }
	}
	
?>

