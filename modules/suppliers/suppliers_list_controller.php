<?php 
    ////////////////////////////////////////////////////  AJAX /////////////////////////////////////////////////////////////////
	if(isset($_POST['AJAX'])){
		if($_POST['AJAX']=='create_supplier'){
			$nickName = $_POST['nickName'];
			$dop_info = $_POST['dop_info'];
			$fullName = $_POST['fullName'];
			if(Supplier::search_name($nickName)==0){
				if(Supplier::search_name($fullName)==0){
					echo '{
				       "response":"1",
				       "id":"'.Supplier::create($nickName,$fullName,$dop_info).'",
				       "text":"Данные успешно сохранены"
				      }';
				}else{
					echo '{
				       "response":"0",
				       "error":"2",
				       "text":"Название данной организации уже содержится в базе ОС"
				      }';
				}
			}else{
				echo '{
			       "response":"0",
				    "error":"1",
			       "text":"Это сокращённое название уже содержится в базе ОС"
			      }';
			}			
			exit;
		}
	}
    ////////////////////////////////////////////////////  AJAX /////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////   quick_bar   ///////////////////////////////////////////////////
	
	// $quick_button = '<div class="quick_button_div"><a href="/os/?page=suppliers&section=supplier_data" class="button">&nbsp;</a></div>';
	$quick_button = '<div class="quick_button_div" style="background:none"><a href="#" id="create_new_supplier" style="display: block;" class="button add">Добавить</a></div>';
	$_SESSION['view_type']['supplier_list']  = isset($_GET['view']) ? $_GET['view'] :( isset($_SESSION['view_type']['supplier_list']) ? $_SESSION['view_type']['supplier_list'] :'ordinary');
	$curViewType = $_SESSION['view_type']['supplier_list'];
	
	$view_button  = '<div class="quick_view_button_div"><a href="#" class="button" cur_view_type="'.$curViewType.'" onclick="openCloseMenu(event,\'subjectsListViewTypeMenu\'); return false;">&nbsp;</a></div>';

    ///////////////////////////////////////////////////   quick_bar   ///////////////////////////////////////////////////
	
	
    $search = (isset($_GET['search']))? $_GET['search'] : false;
	$filter_by_cities = isset($_GET['filter_by_cities'])? $_GET['filter_by_cities']:false;
	
	$rating_vals_arr = isset($_GET['filter_by_rating'])? explode(',',$_GET['filter_by_rating']): array();
	$rating_vals_arr = array_flip($rating_vals_arr);//print_r($rating_vals_arr);
	
	for($i=5;$i>=0;$i--){
		$str =($i!=0)? $i :'все' ;
		if($i!=0) $class = isset($rating_vals_arr[$i])?'active':'';
		if($i==0) $class = !isset($_GET['filter_by_rating'])?'active':'';
		$rating_buttons[] = '<td class="'.$class.'"><a href="#" onclick="return rating_filter(this,'.$i.');" >'.$str.'</a></td>';
		
	}
	$rating_bar = '<table class="toggle_bar"><tr>'.implode('',$rating_buttons).'</tr></table>
	               <input type="hidden" id="ratings_val_storage" value="'.(isset($_GET['filter_by_rating'])?$_GET['filter_by_rating']:'').'"/>';
    
	$by_alphabet_class = (((isset($_GET['sotring']) && $_GET['sotring'] == 'by_alphabet') || !isset($_GET['sotring']))?'active':'');
	$by_creating_date_class = ((isset($_GET['sotring']) && $_GET['sotring'] == 'by_creating_date')?'active':'');
	 
	
	$alphabets[0] = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','щ','ш','э','ю','я');//,'1','2','3','4','5','6','7','8','9','0'
	$alphabets[1] = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	
	for($i = 0 ; $i < count($alphabets); $i++){
	    foreach($alphabets[$i] as $simbol){
		   $alphabet_tpl[$i][] = '<a class="'.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == $simbol)?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter='.$simbol,'').'">'.$simbol.'</a>';
		}
	}
	
	$alphabet_plank[] = (isset($alphabet_tpl[0]) || isset($alphabet_tpl[1]))? '|<a href="?'.addOrReplaceGetOnURL('','filter_by_letter').'" class="'.(!isset($_GET['filter_by_letter'])?'active_letter_filter':'').'">все</a>|':'';
	$alphabet_plank[] = (isset($alphabet_tpl[0]))? '<td class="left">|'.implode('|',$alphabet_tpl[0]).'|<a class="lang_range '.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'а-я')?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter=а-я','').'">рус</a>|</td>':'<td></td>';
	$alphabet_plank[] =  (isset($alphabet_tpl[1]))? '<td class="right">|'.implode('|',$alphabet_tpl[1]).'|<a class="lang_range '.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'a-z')?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter=a-z','').'">eng</a>|</td>':'<td></td>';
	
	
	if(isset($_GET['sotring'])){
		    if($_GET['sotring'] == 'by_alphabet')$order = array ('nickName','ASC');
			if($_GET['sotring'] == 'by_creating_date')$order = array ('id','ASC');
	}
	elseif(isset($_POST['search_client'])) $order = array ('search',$_POST['search_client']);
	else $order =  array('nickName','ASC');
	
		
	$filters = array();
	if(isset($_GET['filter_by_rating']) && $_GET['filter_by_rating']) $filters[] = array ('type' =>'by_rating','val' => $_GET['filter_by_rating']);
	if(isset($_GET['filter_by_letter']) && $_GET['filter_by_letter']) $filters[] = array ('type' =>'by_letter','col' =>'nickName','val' => $_GET['filter_by_letter']);
	if(isset($_GET['filter_by_profies']) && $_GET['filter_by_profies']) $filters[] = array ('type' =>'by_profies','val' => $_GET['filter_by_profies']);
	
	$range = isset($_GET['filter_by_cities'])? array ('by' =>'cities','val' => $_GET['filter_by_cities']):false;
	
	$suppliers_data = get_suppliers_list($range,$order,$filters,$search,false/*$limit_str*/);
	/*echo '<pre>';
	print_r($suppliers_data);
	echo '</pre>';*/
	
	$file_prefix = ($curViewType == 'ordinary' || $curViewType == 'short')? 'ordinary':$curViewType;
	//$tpl_name = './skins/tpl/clients/client_list/'.$curViewType.'_row.tpl';
    $tpl_name = './skins/tpl/suppliers/suppliers_list/'.$file_prefix.'_row.tpl';
	$fd = fopen($tpl_name,'r');
	$tpl = fread($fd,filesize($tpl_name));
	fclose($fd);
	
	ob_start();	
	
	if(is_array($suppliers_data)){
	
		///////////////////        page nav        ///////////////////
		/*
		$all_itmes_num = $suppliers_data['all_clients_num'];
		$all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
		$num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
		$url_query = $_SERVER['QUERY_STRING'];
	
		$page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
		*/
		//////////////////////////////////////////////////////////////
        if($curViewType == 'ordinary' || $curViewType == 'short'){
		    $main_tbl_class = 'main_tbl_ordinary';
			$num_items_on_page = count($suppliers_data['data']);
			if($curViewType == 'ordinary') $num_cols = 3;
			if($curViewType == 'short') $num_cols = 4;
			$items_in_col = ceil($num_items_on_page/$num_cols);
			$counter = 0;
			echo "<tr><td>";
			foreach($suppliers_data['data'] as $item){
				eval('?>'.$tpl.'<?php '); 
				if(++$counter%$items_in_col == 0 && $counter/$items_in_col < $num_cols) echo "</td><td>";
			}
			echo "</td></tr>";
			$header_tbl = '';
			$header_tr = '<tr>'.str_repeat('<td>&nbsp;</td>',$num_cols).'</tr>';
		}
		else if($curViewType == 'wide' || $curViewType == 'expanded'){
		    foreach($suppliers_data['data'] as $item) $ids[$item['id']] = $item['id'];
			$suppliers_expanded_data = get_expanded_data_for_supplier_list($ids);
			//echo '<pre>';print_r($suppliers_expanded_data);echo '</pre>';
			$main_tbl_class = 'main_tbl_wide';
			
		    foreach($suppliers_data['data'] as $item){
			    if($curViewType == 'wide'){
					$str = 'нет данных';
					$activities=isset($suppliers_expanded_data[$item['id']]['activities'])?$suppliers_expanded_data[$item['id']]['activities'][0]:$str;
					$contacts=isset($suppliers_expanded_data[$item['id']]['contacts'])?$suppliers_expanded_data[$item['id']]['contacts'][0]:$str;
					$phones = isset($suppliers_expanded_data[$item['id']]['phones'])? $suppliers_expanded_data[$item['id']]['phones'][0]:$str;
					$emails = isset($suppliers_expanded_data[$item['id']]['emails'])? $suppliers_expanded_data[$item['id']]['emails'][0]:$str;
					$dop_data = isset($suppliers_expanded_data[$item['id']]['dop_data'])? $suppliers_expanded_data[$item['id']]['dop_data'][0]:$str;	
					
					$activities_full_list = make_drop_down_list(@$suppliers_expanded_data[$item['id']]['activities']);
					$contacts_full_list = make_drop_down_list(@$suppliers_expanded_data[$item['id']]['contacts']);
					$phones_full_list = make_drop_down_list(@$suppliers_expanded_data[$item['id']]['phones']);
					$emails_full_list = make_drop_down_list(@$suppliers_expanded_data[$item['id']]['emails']);
					$dop_data_full_list = make_drop_down_list(@$suppliers_expanded_data[$item['id']]['dop_data']);
					
					$activities_class = ($activities_full_list != '')?'roll':'';
					$contacts_class = ($contacts_full_list != '')?'roll':'';
					$phones_class = ($phones_full_list != '')?'roll':'';
					$emails_class = ($emails_full_list != '')?'roll':'';
					$dop_data_class = ($dop_data_full_list != '')?'roll':'';
					
					$activities_click = ($activities_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$contacts_click = ($contacts_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$phones_click = ($phones_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$emails_click = ($emails_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$dop_data_click = ($dop_data_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
				 }
				 if($curViewType == 'expanded'){
					$str = 'нет данных';
					
					$activities=isset($suppliers_expanded_data[$item['id']]['activities'])?make_inner_cell_list(@$suppliers_expanded_data[$item['id']]['activities'],'inner_row'):$str;
					$contacts=isset($suppliers_expanded_data[$item['id']]['contacts'])?make_inner_cell_list(@$suppliers_expanded_data[$item['id']]['contacts'],'inner_row'):$str;
					$phones = isset($suppliers_expanded_data[$item['id']]['phones'])? make_inner_cell_list(@$suppliers_expanded_data[$item['id']]['phones'],'inner_row'):$str;
					$emails = isset($suppliers_expanded_data[$item['id']]['emails'])? make_inner_cell_list(@$suppliers_expanded_data[$item['id']]['emails'],'inner_row'):$str;
					$dop_data = isset($suppliers_expanded_data[$item['id']]['dop_data'])? make_inner_cell_list(@$suppliers_expanded_data[$item['id']]['dop_data'],'inner_row'):$str;	
				 }
				/**/
				 eval('?>'.$tpl.'<?php ');
			}
			

			$tpl_name = './skins/tpl/suppliers/suppliers_list/header_tbl.tpl';
			$fd = fopen($tpl_name,'r');
			$header_tbl = fread($fd,filesize($tpl_name));
			fclose($fd);
			
			
			$tpl_name = './skins/tpl/suppliers/suppliers_list/header_tr.tpl';
			$fd = fopen($tpl_name,'r');
			$header_tr = fread($fd,filesize($tpl_name));
			fclose($fd);

		}
	}
	else{
	    
		// print_r($filters);
		$filtersDescriptNames = array('by_rating'=>'фильтрация по Рейтингу','by_letter'=>'фильтрация по Алфавиту','by_profies'=>'фильтрация по Профилю','by_alphabet'=>'сортировка по Алфавиту','by_creating_date'=>'сортировка по Дате создания');
		$activeFilters = array();
		// isset($_GET['filter_by_cities']) $_GET['sotring'] by_alphabet by_creating_date
		foreach($filters as $filter){
		    if(isset($filtersDescriptNames[$filter['type']])) $activeFilters[] = $filtersDescriptNames[$filter['type']].' <a href="?'.addOrReplaceGetOnURL('','filter_'.$filter['type']).'">снять</a>';
		}
		if(isset($_GET['filter_by_cities'])){
		    $activeFilters[] = 'фильтрация по Городам <a href="?'.addOrReplaceGetOnURL('','filter_by_cities').'">снять</a>';
		}
		if(isset($_GET['sotring'])){
		    if(isset($filtersDescriptNames[$_GET['sotring']]))  $activeFilters[] = $filtersDescriptNames[$_GET['sotring']].' <a href="?'.addOrReplaceGetOnURL('','sotring').'">снять</a>';
		}
		
		$notice_window =  '<div class="notice_window_box"><div class="window">';
		$notice_window .=  '<h3>Поставщики не найдены</h3>';
		if(count($activeFilters)>0){
		    $notice_window .=  '<h4>На странице применены фильтры:</h4>';
		    $notice_window .=  '<div class="row">'.implode('</div><div class="row">',$activeFilters).'</div>';
		}
		
		$notice_window .= '</div></div>';
		$page_navigation = $header_tbl = $header_tr = '';
	}
	
	$rows = ob_get_contents();
	ob_get_clean();
	
	
	ob_start();	
	
		include('./skins/tpl/suppliers/suppliers_list/top_plank.tpl');

	$top_plank = ob_get_contents();
	ob_get_clean();
	
	include('./skins/tpl/common/quick_bar.tpl');
	include('./skins/tpl/suppliers/suppliers_list/show.tpl');//

?>
