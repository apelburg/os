<?php
    $razdel = (empty($_GET['razdel']))? 'plans' : $_GET['razdel'] ;
	
     
	////////////////////////////////////////////////////////////////////////////

	if(isset($_POST['set_plan'])){
	    set_plan();
	}
	if(isset($_POST['edit_plan'])){
	    edit_plan();
	}
	if(isset($_GET['set_plan_status'])){
        set_plan_status($_GET['plan_id'],$_GET['set_plan_status']);
	}
	if(isset($_POST['set_result_for_plan'])){
        set_result_for_plan($_SESSION['access']['user_id']);
	}
	
	////////////////////////////////////////////////////////////////////////////
	

	$row_class_name = 'planner_rows';
	
    if($razdel == 'history'){ 
	    $query = "SELECT*FROM `".PLANNER."` WHERE `status` = 'done' AND `client_id` = '".$client_id."'";
	    $query .= " ORDER BY `exec_datetime` DESC";
	
        ///////////////////       page nav       ///////////////////
	    $num_row = mysql_query($query,$db);
	
	
	    $all_itmes_num = mysql_num_rows($num_row);
	    $one_page_itmes_num =  isset($_GET['one_page_itmes_num'])? $_GET['one_page_itmes_num'] : 40 ;//
	    $all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
	    $num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
	    $url_query = $_SERVER['QUERY_STRING'];

        $page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
	    //////////////////////////////////////////////////////////////
	
	   
	    $result = mysql_query($query." LIMIT ".($num_page-1)*$one_page_itmes_num.", ".$one_page_itmes_num."",$db); 
			
		    if(mysql_num_rows($result) > 0){
			
			    ob_start();
				// history_rows
	            $tpl_name = './skins/tpl/client_folder/planner/history_table_rows.tpl';
	            $fd = fopen($tpl_name,'r');
	            $tpl_row = fread($fd,filesize($tpl_name));
	            fclose($fd);
				 
				 
			    while($item = mysql_fetch_assoc($result)){
				    extract($item,EXTR_PREFIX_ALL,"pl");
					
					$write_date_in_format = implode('.',array_reverse(explode('-',substr($pl_write_datetime,0,10))));
					$write_time_in_format = substr($pl_write_datetime,11,5);
					$exec_date_in_format = implode('.',array_reverse(explode('-',substr($pl_exec_datetime,0,10))));
					$exec_time_in_format = substr($pl_exec_datetime,11,5);
					
					if($pl_manager_id!=$pl_close_manager_id && $pl_close_manager_id!=0) $author = 'открыто -'.get_manager_nickname_by_id($pl_manager_id).', закрыто -'.get_manager_nickname_by_id($pl_close_manager_id);
					else $author = get_manager_nickname_by_id($pl_manager_id);
					 
                    eval('?>'.$tpl_row.'<?php ');
				
			    }
				
				$palnner_rows = ob_get_contents();
	            ob_get_clean();
			}
			else $palnner_rows = '';
		


	    ob_start();
		 
        include './skins/tpl/client_folder/planner/history_table.tpl';
	    $palnner_content = ob_get_contents();
	    ob_get_clean();
	
	}
	elseif($razdel == 'plans'){
	    

		$query = "SELECT*FROM `".PLANNER."` WHERE (`status` = 'new' OR `status` = 'on_approval' OR `status` = 'rejected') AND  `client_id` = '".$client_id."'";	
	    $query .= " ORDER BY `exec_datetime`";
	    // `status` = 'new'
        ///////////////////       page nav       ///////////////////
	    $num_row = mysql_query($query,$db);
	
	
	    $all_itmes_num = mysql_num_rows($num_row);
	    $one_page_itmes_num =  isset($_GET['one_page_itmes_num'])? $_GET['one_page_itmes_num'] : 40 ;//
	    $all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
	    $num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
	    $url_query = $_SERVER['QUERY_STRING'];

        $page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
	    //////////////////////////////////////////////////////////////

	    $result = mysql_query($query." LIMIT ".($num_page-1)*$one_page_itmes_num.", ".$one_page_itmes_num."",$db); 
			
		    if(mysql_num_rows($result) > 0){
			
			    ob_start();
				// palnner_rows
	            $tpl_name = './skins/tpl/client_folder/planner/planner_table_rows.tpl';
	            $fd = fopen($tpl_name,'r');
	            $tpl_row = fread($fd,filesize($tpl_name));
	            fclose($fd);
				 
				 
			    while($item = mysql_fetch_assoc($result)){
				    extract($item,EXTR_PREFIX_ALL,"pl");
					
				    $write_date_in_format = implode('.',array_reverse(explode('-',substr($pl_write_datetime,0,10))));
					$write_time_in_format = substr($pl_write_datetime,11,5);
					$exec_date_in_format = implode('.',array_reverse(explode('-',substr($pl_exec_datetime,0,10))));
					$exec_time_in_format = substr($pl_exec_datetime,11,5);
					
					// цветовое выделение рядов по отношению к текущей дате
					$current_date_in_number = intval(date('Ymd'));
					$exec_date_in_number = intval(str_replace('-','',substr($pl_exec_datetime,0,10)));
					
					if($current_date_in_number > $exec_date_in_number) $row_class_name = 'planner_rows_expired';
					elseif($current_date_in_number == $exec_date_in_number) $row_class_name = 'planner_rows_today';
					elseif(($current_date_in_number < $exec_date_in_number) && ($current_date_in_number+7 > $exec_date_in_number)) $row_class_name = 'planner_rows_nearweek';
					else $row_class_name = 'planner_rows';
					
					$done_button = ($pl_status=='on_approval') ? 'на рассмотрении':'<a href="#" onclick="set_plan_making_result('.$pl_id.',\''.$pl_type.'\');return false;">выполнено</a>';
					//$pl_plan = ($pl_status=='on_approval')? '<span class="mini_cap">план</span><div>'.$pl_plan.'</div><span class="result_in_plans mini_cap">результат</span><div class="result_in_plans">'.$pl_result.'</div>': $pl_plan ;
					$onclick =  ($pl_status=='on_approval' || $pl_status=='rejected')? '':'show_planner_window_for_editing('.$pl_client_id.','.$pl_id.');';
					if(trim($pl_result) !="") $pl_plan = '<span class="mini_cap">план</span><div>'.$pl_plan.'</div><span class="result_in_plans mini_cap">результат</span><div class="result_in_plans">'.$pl_result.'</div>';
					 
                    eval('?>'.$tpl_row.'<?php ');
				
			    }
				
				$palnner_rows = ob_get_contents();
	            ob_get_clean();
			}
			else $palnner_rows = '';
		


	    ob_start();
		 
        include './skins/tpl/client_folder/planner/planner_table.tpl';
	    $palnner_content = ob_get_contents();
	    ob_get_clean();
    }
	elseif($razdel == 'common'){ 
	
		$query = "SELECT*FROM `".PLANNER."` WHERE `client_id` = '".$client_id."'";	
	    $query .= " ORDER BY `exec_datetime` DESC";
	// `status` = 'new'
        ///////////////////       page nav       ///////////////////
	    $num_row = mysql_query($query,$db);
	
	
	    $all_itmes_num = mysql_num_rows($num_row);
	    $one_page_itmes_num =  isset($_GET['one_page_itmes_num'])? $_GET['one_page_itmes_num'] : 40 ;//
	    $all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
	    $num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
	    $url_query = $_SERVER['QUERY_STRING'];

        $page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
	    //////////////////////////////////////////////////////////////
	
	   
	    $result = mysql_query($query." LIMIT ".($num_page-1)*$one_page_itmes_num.", ".$one_page_itmes_num."",$db); 
			
		    if(mysql_num_rows($result) > 0){
			
			    ob_start();
				
				// palnner_rows
	            $tpl_plan_name = './skins/tpl/client_folder/planner/planner_table_rows.tpl';
	            $fd = fopen($tpl_plan_name,'r');
	            $tpl_plan_row = fread($fd,filesize($tpl_plan_name));
	            fclose($fd);
				
				// history_rows
	            $tpl_history_name = './skins/tpl/client_folder/planner/history_table_rows.tpl';
	            $fd = fopen($tpl_history_name,'r');
	            $tpl_history_row = fread($fd,filesize($tpl_history_name));
	            fclose($fd);
				 
				 
			    while($item = mysql_fetch_assoc($result)){
				    extract($item,EXTR_PREFIX_ALL,"pl");
					
					$write_date_in_format = implode('.',array_reverse(explode('-',substr($pl_write_datetime,0,10))));
					$write_time_in_format = substr($pl_write_datetime,11,5);
					$exec_date_in_format = implode('.',array_reverse(explode('-',substr($pl_exec_datetime,0,10))));
					$exec_time_in_format = substr($pl_exec_datetime,11,5);
					
					// цветовое выделение рядов по отношению к текущей дате
					$current_date_in_number = intval(date('Ymd'));
					$exec_date_in_number = intval(str_replace('-','',substr($pl_exec_datetime,0,10)));
					
					if($current_date_in_number > $exec_date_in_number) $row_class_name = 'planner_rows_expired';
					elseif($current_date_in_number == $exec_date_in_number) $row_class_name = 'planner_rows_today';
					elseif(($current_date_in_number < $exec_date_in_number) && ($current_date_in_number+7 > $exec_date_in_number)) $row_class_name = 'planner_rows_nearweek';
					else $row_class_name = 'planner_rows';
					 
                    if($pl_status == 'new' ) eval('?>'.$tpl_plan_row.'<?php ');
					if($pl_status == 'done' ) eval('?>'.$tpl_history_row.'<?php ');
				
			    }
				
				$palnner_rows = ob_get_contents();
	            ob_get_clean();
			}
			else $palnner_rows = '';
		


	    ob_start();
		 
        include './skins/tpl/client_folder/planner/common_planner_table.tpl';
	    $palnner_content = ob_get_contents();
	    ob_get_clean();
	}
   

    ob_start();
	 
    include './skins/tpl/client_folder/planner/show_planner_menu.tpl';  
	
	$content = ob_get_contents();
	ob_get_clean();

?>