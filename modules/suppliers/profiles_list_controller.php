<?php  
    
	 ///////////////////////////////////////////////////   quick_bar   ///////////////////////////////////////////////////
	
	$quick_button = '<div class="quick_button_div"><a href="/os/?page=suppliers&section=profile_data" class="button">&nbsp;</a></div>';
	$view_button  = '<div class="quick_view_button_div"><a href="#" class="button" onclick="openCloseMenu(event,\'subjectsListViewTypeMenu\'); return false;">&nbsp;</a></div>';
    $curViewType = isset($_GET['view']) ? $_GET['view'] : 'ordinary';
	
    ///////////////////////////////////////////////////   quick_bar   ///////////////////////////////////////////////////
	
	$search = (isset($_GET['search']))? $_GET['search'] : false;
	$prev_checked = isset($_GET['filter_by_profies'])? explode(',',$_GET['filter_by_profies']):array();
	
	
	$activities_data = get_activities_list($search);
	/*echo '<pre>';
	print_r($activities_data);
	echo '</pre>';*/
	
    $tpl_name = './skins/tpl/suppliers/profiles_list/row.tpl';
	$fd = fopen($tpl_name,'r');
	$tpl = fread($fd,filesize($tpl_name));
	fclose($fd);
	
	
	ob_start();	
	if(is_array($activities_data)){
	
	
		
		$num_items_on_page = count($activities_data['data']);
        $num_cols = 4;
		$items_in_col = ceil($num_items_on_page/$num_cols);
		$counter = 0;
		echo "<tr><td>";
		foreach($activities_data['data'] as $item){
			eval('?>'.$tpl.'<?php '); 
			if(++$counter%$items_in_col == 0 && $counter/$items_in_col < $num_cols) echo "</td><td>";
		}
		echo "</td></tr>";
		$header_tr = '<tr>'.str_repeat('<td>&nbsp;</td>',$num_cols).'</tr>';
	

		
	}
	else{
	    echo 'клиенты не найдены';
		$page_navigation = '';
	}
	$rows = ob_get_contents();
	ob_get_clean();
	
	ob_start();	
	
		include('./skins/tpl/suppliers/profiles_list/top_plank.tpl');

	$top_plank = ob_get_contents();
	ob_get_clean();
	
	include('./skins/tpl/common/quick_bar.tpl');
	include('./skins/tpl/suppliers/profiles_list/show.tpl');//

?>
