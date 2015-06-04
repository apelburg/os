<?php

    $quick_button = '<div class="quick_button_div"><a href="#" class="button" onclick="openCloseMenu(event,\'quickMenu\'); return false;">&nbsp;</a></div>';
	$view_button = '<div class="quick_view_button_div"><a href="%23" class="button" onclick="openCloseMenu(event,\'rtViewTypeMenu\'); return false;">&nbsp;</a></div>';
	
    $control_num = get_control_num();
	 
	 /* 
	if(isset($_GET['shift_order'])){
		 shift_order($_GET['shift_order'],$client_id);
	 }
	 */
	 
	 if(isset($_GET['add_order_at_the_end_rt']))
	 {
	     add_order_at_the_end_rt(cor_data_for_SQL($_GET['type_row']),intval($_GET['num']));
		 exit;
	 }
	 
	 if(isset($_GET['add_copied_order_to_rt']))
	 {
	     add_copied_order_to_rt(intval($_GET['order_row_id']),intval($_GET['control_num']));
		 exit;
	 }
	 
	 
	 if(isset($_GET['add_print_row']))
	 {
	     //print_r($_GET);
		 add_rows_to_rt(intval($_GET['id']),'print',1,intval($_GET['control_num']));
		 exit;
	 }

	 if(isset($_GET['add_rows_to_rt']))
	 {
	     add_rows_to_rt(intval($_GET['id']),cor_data_for_SQL($_GET['type_row']),intval($_GET['num']),intval($_GET['control_num']));
		 exit;
	 }

	 if(isset($_GET['make_rows_changes_in_rt']))
	 {
	     
		 if($_GET['action'] == 'delete')
		 {
		     if(trim($_GET['id_nums_str']) != '')
			 {
				 delete_rows(cor_data_for_SQL($_GET['id_nums_str']),(int)$_GET['control_num']);
				 header('Location:?'.addOrReplaceGetOnURL('','make_rows_changes_in_rt&action&id_nums_str&control_num'));
				 exit; 
			 }
			 
		 }
		 
		// make_rows_changes_in_rt(cor_data_for_SQL($_GET['action']),intval($_GET['num']));
		 exit;
	 }
	
	
	ob_start();
	 
	$query = "SELECT*FROM `".CALCULATE_TBL."` 
	          WHERE `client_id` = '".$client_id."'	 
	          ORDER BY `id` DESC";
	
	///////////////////       page nav       ///////////////////
	$num_row = mysql_query($query,$db) or die(mysql_error());
	
	$all_itmes_num = mysql_num_rows($num_row);
	//echo $all_itmes_num;
	$one_page_itmes_num =  isset($_GET['one_page_itmes_num'])? $_GET['one_page_itmes_num'] : 40 ;//
	$all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
	$num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
	$url_query = $_SERVER['QUERY_STRING'];

	$page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
    //////////////////////////////////////////////////////////////
	
	// опция для сдвига ряда вверх
	/*$shift_row_num_step = isset($_GET['shift_row_num_step'])? $_GET['shift_row_num_step'] : 0 ;*/
	 
	
	 
	//$result = mysql_query($query." LIMIT ".(($num_page-1)*$one_page_itmes_num + $shift_row_num_step).", ".$one_page_itmes_num."",$db); 
	$result = mysql_query($query." LIMIT ".(($num_page-1)*$one_page_itmes_num).", ".$one_page_itmes_num."",$db); 
		
	if(mysql_num_rows($result) > 0)
	{	
        // calculate table rows(order)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/calculate_tbl_order_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_order_row = fread($fd,filesize($tpl_name));
		fclose($fd);		
		
		// calculate table rows(calculating row)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/calculate_tbl_calculating_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_calculating_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		
		// calculate table rows(itog)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/calculate_tbl_itog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_itog_row = fread($fd,filesize($tpl_name));
		fclose($fd); 
		
		// calculate table rows(interval)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/calculate_tbl_interval_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_interval_row = fread($fd,filesize($tpl_name));
		fclose($fd); 
		
		// calculate table rows(assosiated)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/calculate_tbl_assosiated_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_assosiated_row = fread($fd,filesize($tpl_name));
		fclose($fd); 
		
		// calculate extra panel(basic)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/extra_panel_for_basic_catalog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$extra_panel_for_basic_catalog_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		
		// calculate extra panel(other)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/extra_panel_for_other_catalog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$extra_panel_for_other_catalog_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		
		// calculate extra panel(polygraphy)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_table/extra_panel_for_polygraphy_catalog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$extra_panel_for_polygraphy_catalog_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		 
		$itog_coming_summ = $itog_summ = $itog_delta = $row_counter = $hidden_num = 0; 
		
		// first row in table always have to be assosiated row 
		eval('?>'.$tpl_assosiated_row.'<?php '); 
		  
		while($item = mysql_fetch_assoc($result)){
		
		    extract($item,EXTR_PREFIX_ALL,"rt");
			
			//данные
			$date = ($rt_type == 'order')? implode('.',array_reverse(explode('-',substr($rt_date,0,10)))) : '' ;
			$order_num = ($rt_type != 'empty')? $rt_order_num : '' ;
			
			// расчеты цен
			//$price = ($rt_discount != 0 )? (($rt_price/100)*(100 + $rt_discount)) : $rt_price ;
			$coming_price_summ = number_format(($rt_quantity * $rt_coming_price),"2",".",'');
			$price_summ = number_format(($rt_quantity * (float)$rt_price),"2",".",'');
			$delta = number_format(($rt_quantity * (float)$rt_price - $coming_price_summ),"2",".",'');
			$show_discount = ($rt_discount > 0 )? '+'.$rt_discount : $rt_discount ;
			
			// для совместимости с предыдущим OC
			$rt_marker_summ = ($rt_marker_summ == 'on')? '' : 'on';
			
			if($rt_marker_summ == 'on'){
			    $itog_summ += $price_summ;
				$itog_coming_summ += $coming_price_summ; 
				$itog_delta += $delta; 
			}
			
			// маркеры
            $viewing_marker_summ = ($rt_marker_summ != 'on')? '-' : '+';
			$style_marker_summ = ($rt_marker_summ != 'on')? '' : '_on';
			
			
		   	$hidden_num++;
			
		    if($rt_type == 'order')
			{ 
				$client_manager_data = get_client_cont_face_by_id($client_id,$rt_client_manager_id,false);
				$client_manager = ($client_manager_data)? $client_manager_data['name']:'';
	
				eval('?>'.$tpl_order_row.'<?php ');
			
			}
			if($rt_type == 'article' || $rt_type == 'ordinary' || $rt_type == 'print' || $rt_type == 'services')
			{
			    $row_counter++;
				eval('?>'.$tpl_calculating_row.'<?php ');
		    }
			if($rt_type == 'itog') eval('?>'.$tpl_itog_row.'<?php ');
			if($rt_type == 'empty') eval('?>'.$tpl_interval_row.'<?php ');
			
			//
			if($rt_type == 'itog' || $rt_type == 'order' || $rt_type == 'empty')
			{
			     $itog_summ = $itog_coming_summ = $itog_delta = $row_counter = 0;
			}
			
					   
				   
				   /* 
					 
					 //
					 
					 else $base_art_id = '';
					 //$flag_for_prev_itog_row = ($rt_type == 'itog')? true : ($rt_type == 'empty')?false :true ;
					 
					 $order_bord_counter = ($rt_type == 'empty' && !empty($flag_for_prev_itog_row))? 0 : ++$order_bord_counter; 
					 $flag_for_prev_itog_row = ($rt_type == 'itog')? true : false ;

					 // настройки
					 $time = ($rt_type == 'order')? substr($rt_date,11,5) : '' ;
					 $spread_rows_status=(!isset($_SESSION['spreadrows_stat']) || $_SESSION['spreadrows_stat']=='off')?'off':'on';
					 $rows_height_status=(!isset($_SESSION['rtrowsheight_status']) || $_SESSION['rtrowsheight_status']=='off')?'off':'on';
					 //$//article_rows_show_status = 1;
					 //$_SESSION['articlerowsshow_status']
					 $article_descript_top_div_display = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']!='1')? 'block' : 'none';
					 $article_name_textarea_display = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']!='2')? 'block' : 'none';
					 $auxiliary_row_classname=(!isset($_SESSION['spreadrows_stat']) || $_SESSION['spreadrows_stat']=='off')?'auxiliary_row':'auxiliary_row_on';
					 
					 
					 // высота текстовых полей
					 $textarea_height['name'] = set_textarea_height($rt_name,'12_arial',300);
					 $textarea_height['making_time'] = set_textarea_height($rt_making_time,'12_arial',140);
					 $textarea_height['comment'] = set_textarea_height($rt_comment,'12_arial',140);
					 
					 if($rt_type == 'order') $order_textarea_height = set_textarea_height($rt_comment,'11_arial',800);
					 
					 
	
					
					 if(isset($_SESSION['rtrowsheight_status']) && $_SESSION['rtrowsheight_status']=='on'){
	                     if($rt_type == 'article'){ 
						     $textarea_row_num = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']=='0')? 2 : 1;
					         $textarea_name_row_num = 1;
						 }
						 else{
						     $textarea_row_num = 1;
					         $textarea_name_row_num = 1;
							 $order_textarea_height = 1;
						 }
					  
					   
					 }
					 else{
						 
						  if($rt_type == 'article'){ 
							 if($textarea_height['name'] >= max($textarea_height)){
								 $textarea_name_row_num = max($textarea_height);
								 $textarea_row_num = $textarea_name_row_num + 1;
							 }
							 else{
								 $textarea_name_row_num= max($textarea_height) -1;
								 $textarea_row_num = max($textarea_height);
							 }
						 }
						 else{
							 $textarea_row_num = max($textarea_height);
					 }
					 
					 }*/
					 /*
					 
					 if($rt_type == 'order') $order_textarea_height = set_textarea_height($rt_comment,'11_arial',800);
					 
					 
	
					 if($rt_type == 'article'){ 
						 if($textarea_height['name'] == max($textarea_height)){
							 $min_name_textarea_height =(max($textarea_height) < 30)? 30 : max($textarea_height);
							 $min_textarea_height = $min_name_textarea_height + 22;
						 }
						 else{
							 $min_name_textarea_height =(max($textarea_height) < (30+22))? (30+22) : max($textarea_height) - 22;
							 $min_textarea_height = max($textarea_height);
						 }
					 }
					 else{
					     $min_textarea_height = max($textarea_height);
					 }
					 */
					
					 	 	
					/* 
					 // иконки
	                 $kp_button_img = ($rt_marker_kp == 'on')? 'kp_button_on.png' : 'kp_button.png';
					 $invoice_button_img = ($rt_marker_invoice == 'on')? 'invoice_button_on.png' : 'invoice_button.png';
					 $summ_button_img = ($rt_marker_summ == 'on')? 'summ_button_on.png' : 'summ_button.png';
					 $article_button_img = ($rt_marker_hidearticle == 'on')? 'article_button_on.png' : 'article_button.png';
					 $rowstatus_button_img = 'rowstatus_button_'.$rt_marker_rowstatus.'.png';
					 $spread_rows_img=(!isset($_SESSION['spreadrows_stat']) || $_SESSION['spreadrows_stat']=='off')?'spread_rows_button':'spread_rows_button_on';
					 $change_rt_rows_height_img=(!isset($_SESSION['rtrowsheight_status']) || $_SESSION['rtrowsheight_status']=='off')?'change_rt_rows_height_button':'change_rt_rows_height_button_on';
					 $articlerowsshow_status_0_img  = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']=='0')? 'articlerowsshow_status_button_0_on' : 'articlerowsshow_status_button_0';
					 $articlerowsshow_status_1_img  = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']=='1')? 'articlerowsshow_status_button_1_on' : 'articlerowsshow_status_button_1';
					 $articlerowsshow_status_2_img  = (!isset($_SESSION['articlerowsshow_status']) || $_SESSION['articlerowsshow_status']=='2')? 'articlerowsshow_status_button_2_on' : 'articlerowsshow_status_button_2'; 
					 $summ_print_button_a = ($type_prev_row['type'] == 'print' && $rt_type == 'print')? ($rt_marker_summ_print == 'on')? '<a  href="#" onclick="change_marker_status(\'marker_summ_print\','.$rt_id.','.$control_num.',\'summ_print_button\');return false;"><img id="summ_print_button_'.$rt_id.'" style="margin:0px 0px 2px 0px;" src="../../skins/tpl/admin/order_manager/img/summ_print_button_on.png" border="0" /></a>' : '<a  href="#" onclick="change_marker_status(\'marker_summ_print\','.$rt_id.','.$control_num.',\'summ_print_button\');return false;"><img id="summ_print_button_'.$rt_id.'" style="margin:0px 0px 2px 0px;" src="../../skins/tpl/admin/order_manager/img/summ_print_button.png" border="0" /></a>' : '';
					 
					 
					 $td_classname=($rt_marker_new_row=='new')?'calculate_table_td_new':'calculate_table_td_'.$rt_marker_rowstatus;
					 $price_td_classname=($rt_marker_new_row=='new')?'calculate_table_price_td_new':'calculate_table_price_td_'.$rt_marker_rowstatus;
					 $delta_td_classname=($rt_marker_new_row=='new')?'calculate_table_delta_td_new':'calculate_table_delta_td_'.$rt_marker_rowstatus;

					 // номер ряда
					 $row_num = ($num_page-1)*$one_page_itmes_num+$row_counter+$shift_row_num_step;
                               
					 // поднимание, опускание рядов
                     if($shift_row_num_step != 0 && !isset($up_row_flag)){
						 $up_row_a = '<a  href="?'.addOrReplaceGetOnURL('','shift_row_num_step').'"><img id="rt_up_row_img_'.$row_num.'" style="margin:1px 4px 1px 2px;" src="../../skins/tpl/admin/order_manager/img/rt_up_row_button_off.png" border="0" /></a>' ;
					     $up_row_flag = true;
					 }
                     else{
                        $up_row_a = '<a  href="?'.addOrReplaceGetOnURL('shift_row_num_step='.($shift_row_num_step + ($row_counter-1))).'"><img id="rt_up_row_img_'.$row_num.'" style="margin:1px 4px 1px 2px;" src="../../skins/tpl/admin/order_manager/img/rt_up_row_button.png" border="0" /></a>';
                     }
 
					
					 
					
					 // цвет
					 $dis_color = ($rt_discount == 0 )? '#BBBBBB' :( ($rt_discount > 0 )? '#FF0000':'#3333FF' );
					 
					 
					 // расчет суммы всех нанесений для конкретного артикула
					 if($rt_type == 'print' && !isset($summ_prints_for_art)){
					     if($rt_marker_summ != 'on'){
						     $summ_prints_for_art['commom_price'] = $commom_price+($type_prev_row['quantity']*$type_prev_row['price']) ;
						     $summ_prints_for_art['commom_coming_price'] =   $commom_coming_price+($type_prev_row['quantity']*$type_prev_row['coming_price']) ;
						 }
						 else{
						     $summ_prints_for_art['commom_price'] =  $type_prev_row['quantity']*$type_prev_row['price'];
						     $summ_prints_for_art['commom_coming_price'] =  $type_prev_row['quantity']*$type_prev_row['coming_price'];
						 }  
					 }
					 elseif($rt_type == 'print' && isset($summ_prints_for_art)){
						 $summ_prints_for_art['commom_price'] += ($rt_marker_summ != 'on')? $commom_price  : 0 ;
						 $summ_prints_for_art['commom_coming_price'] += ($rt_marker_summ != 'on')? $commom_coming_price : 0 ;
					 }
					 
					 if($type_prev_row['type'] == 'print' && $rt_type != 'print'){ 
					     $coming_price_for_one = ($summ_prints_for_art['commom_coming_price'] != 0 && $prev_good_quantity != 0)? $summ_prints_for_art['commom_coming_price']/$prev_good_quantity:0;
					     $price_for_one = ($summ_prints_for_art['commom_price'] != 0 && $prev_good_quantity != 0)? $summ_prints_for_art['commom_price']/$prev_good_quantity:0;
					     eval('?>'.$tpl_print_summ_row.'<?php ');
					 }
					 
					 
					 
					 ///
					 
					 if($rt_type != 'print') unset($summ_prints_for_art);
					 
					 if($rt_marker_summ == 'on')$rt_coming_price=$price= 0;
					 $type_prev_row=array('type'=>$rt_type,'quantity'=>$rt_quantity,'coming_price'=>$rt_coming_price,'price'=>$price);
					 $prev_good_quantity = ($rt_type == 'article' || $rt_type  == 'ordinary')? $rt_quantity : $prev_good_quantity ;
					 */
	    }
		
		$rows = ob_get_contents();
	    ob_get_clean();
    }
	
	//ob_start();
	//echo 'расчетная таблица';
	include('./skins/tpl/clients/client_folder/calculate_table/calculate_tbl_top.tpl');
	//$content = ob_get_contents();
	//ob_get_clean();
	
	
	//////////////////////////////////////////////////
	
	    /*
		// calculate table rows(basic catalog)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_tbl_basic_catalog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_basic_catalog_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		 
		// calculate table rows(other catalog)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_tbl_other_catalog_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_other_catalog_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		 
		// calculate table rows(polygraphy)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_tbl_polygraphy_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_polygraphy_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		 
		// calculate table rows(services)
		$tpl_name = './skins/tpl/clients/client_folder/calculate_tbl_services_row.tpl';
		$fd = fopen($tpl_name,'r');
		$tpl_services_row = fread($fd,filesize($tpl_name));
		fclose($fd);
		
		    if($rt_type == 'article') eval('?>'.$tpl_basic_catalog_row.'<?php ');
			if($rt_type == 'ordinary') eval('?>'.$tpl_other_catalog_row.'<?php ');
			if($rt_type == 'print') eval('?>'.$tpl_polygraphy_row.'<?php ');
			if($rt_type == 'services') eval('?>'.$tpl_services_row.'<?php ');
		*/
?>