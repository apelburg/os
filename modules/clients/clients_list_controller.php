<?php
	$mailClass = new Mail;
	$clientClass = new Client($client_id);
	
    ////////////////////////////////  AJAX   ////////////////////////////////////
    if(isset($_GET['generate_manager_list'])){
	    $arr = get_managers_list();
		foreach($arr as $manager) if(trim($manager['name']) != '' && ($manager['access']==1 || $manager['access']==5)) $managers[] = $manager['id'].'[,]'.$manager['name'].' '.$manager['last_name'];
	    ////print_r($arr); 
		echo implode('[&]',$managers);
		exit;
	}
	if(isset($_POST['ajax_standart_window']) && $_POST['ajax_standart_window']=="create_client"){
		$arr_2['company'] = urldecode ($_POST['company']);
		$arr_2['dop_info'] = urldecode ($_POST['dop_info']);
		$arr_2['rate'] = $_POST['rate'];
		// если клиентов с таким именем не существует - добавляем нового
		if($clientClass->search_name($arr_2['company'])==0){		
		echo '{
	       "response":"1",
	       "id":"'.$clientClass->create($arr_2).'"
	      }';
		}else{		
		echo '{
	       "response":"2",
	       "text":"Клиент с таким именем уже содержится в базе Апельбурга."
	      }';
		}
		exit;
	}
    /////////////////////////////////////////////////////////////////////////////
	
    $quick_button = '<div class="quick_button_div" style="background:none"><a href="#" id="create_new_client" style="
  display: block;" class="button add">Добавить</a></div>';
	
	$_SESSION['view_type']['clients_list']  = isset($_GET['view']) ? $_GET['view'] :( isset($_SESSION['view_type']['clients_list']) ? $_SESSION['view_type']['clients_list'] :'ordinary');
	$curViewType = $_SESSION['view_type']['clients_list'];
	
	$view_button  = '<div class="quick_view_button_div"><a href="#" class="button" cur_view_type="'.$curViewType.'" onclick="openCloseMenu(event,\'subjectsListViewTypeMenu\'); return false;">&nbsp;</a></div>';


	/////////////////////////////////////////////////////////////////////////////
	$file_prefix = ($curViewType == 'ordinary' || $curViewType == 'short')? 'ordinary':$curViewType;
	//$tpl_name = './skins/tpl/clients/client_list/'.$curViewType.'_row.tpl';
    $tpl_name = './skins/tpl/clients/client_list/'.$file_prefix.'_row.tpl';
	$fd = fopen($tpl_name,'r');
	$tpl = fread($fd,filesize($tpl_name));
	fclose($fd);		

	ob_start();
	
	if(isset($_GET['sotring'])){
		    if($_GET['sotring'] == 'by_alphabet')$order = array ('company','ASC');
			if($_GET['sotring'] == 'by_creating_date')$order = array ('id','DESC');
			if($_GET['sotring'] == 'by_rt_update')$order = array ('time_change','DESC');
			//if($_GET['sotring'] == 'by_manager')$order = array ('company','ASC');
			//if($_GET['sotring'] == 'by_rating')$order = array ('company','ASC');
	}
	elseif(isset($_POST['search_client'])) $order = array ('search',$_POST['search_client']);
	else $order =  array('company','ASC');
	
    $filters = array();
	if(isset($_GET['filter_by_rating']) && $_GET['filter_by_rating']) $filters[] = array ('type' =>'by_rating','col' =>'rate','val' => $_GET['filter_by_rating']);
	if(isset($_GET['filter_by_letter']) && $_GET['filter_by_letter']) $filters[] = array ('type' =>'by_letter','col' =>'company','val' => $_GET['filter_by_letter']);
	
	// echo $user_status;
	if($user_status == 1 || isset($_GET['search'])) { // при поиске разрешаем видеть всех клиентов
		$range = false;
		$_GET['view'] = 'wide';
	}else{
		$range = array('by'=>'manager_id','id'=> $user_id);
		$_GET['view'] = 'wide';
	}
	
	if(isset($_GET['filter_by_range'])) $range = array ('by' =>'manager_id','id' => $_GET['filter_by_range']);
	
	$search = (isset($_GET['search']))? $_GET['search'] : false;
	
	//////////////////////////////////////////////// alphabets ///////////////////////////////////////////
	//$alphabets[0] = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','щ','ш','э','ю','я');//,'1','2','3','4','5','6','7','8','9','0'
	//$alphabets[1] = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	$alphabets = get_alphabets($range,$filters);
	
	/*echo '<pre>';
	print_r($alphabets);
	echo '</pre>';*/
	
	// если есть $_GET['filter_by_letter'] но запрошенный символ не может быть выведен в alphabet_plank ( планке с алфавитом )
	// по причине того что при всех остальных условиях данные отвечающие этому символу не входят в диапазон
	// производится презагрузка страницы без параметра $_GET['filter_by_letter'] 

	
	// задание перезагрузки
    $reload_clear_get = isset($_GET['filter_by_letter'])? true: false;
	if(isset($_GET['filter_by_letter']) && ($_GET['filter_by_letter'] == 'а-я' || $_GET['filter_by_letter'] == 'a-z')) $reload_clear_get = false;
	
	for($i = 0 ; $i < count($alphabets); $i++){
	    foreach($alphabets[$i] as $simbol){
		   // отмена перезагрузки
		   if(isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == $simbol) $reload_clear_get = false;
		   
		   
		   $alphabet_tpl[$i][] = '<a class="'.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == $simbol)?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter='.$simbol,'').'">'.$simbol.'</a>';
		}
		
		// подтверждение перезагрузки если $_GET['filter_by_letter'] == 'а-я' но русский алфавит не выводится
		if(isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'а-я' &&  count($alphabets[0]) == 0) $reload_clear_get = true;
		// подтверждение перезагрузки если $_GET['filter_by_letter'] == 'a-z' но английский алфавит не выводится
		if(isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'a-z' &&  count($alphabets[1]) == 0) $reload_clear_get = true;
	}

	// make reload - сделать перезагрузку
	if($reload_clear_get){
	    header('location:?'.addOrReplaceGetOnURL('','filter_by_letter'));
	    exit;
	}

	$limit_str =(0)?" LIMIT ".$one_page_itmes_num*($num_page-1).", ".$one_page_itmes_num:'';
	
	$clients_data = get_clients_list($range,$order,$filters,$search,$limit_str);
	if(is_array($clients_data) && count($clients_data['data'])>0){
		$rr = ' ('.count($clients_data['data']).')';
	}else{
		$rr = '';
	}
	$alphabet_plank[] = (isset($alphabet_tpl[0]) || isset($alphabet_tpl[1]))? '|<a href="?'.addOrReplaceGetOnURL('','filter_by_letter').'" class="'.(!isset($_GET['filter_by_letter'])?'active_letter_filter':'').'">все'.($rr).'</a>|':'';
	$alphabet_plank[] = (isset($alphabet_tpl[0]))? '<td class="left">|'.implode('|',$alphabet_tpl[0]).'|<a class="lang_range '.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'а-я')?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter=а-я','').'">рус</a>|</td>':'<td></td>';
	$alphabet_plank[] =  (isset($alphabet_tpl[1]))? '<td class="right">|'.implode('|',$alphabet_tpl[1]).'|<a class="lang_range '.((isset($_GET['filter_by_letter']) && $_GET['filter_by_letter'] == 'a-z')?'active_letter_filter':'').'" href="?'.addOrReplaceGetOnURL('filter_by_letter=a-z','').'">eng</a>|</td>':'<td></td>';

 
	//////////////////////////////////////////// alphabets ///////////////////////////////////////////////
	
	
	//," LIMIT ".(($num_page-1)*$one_page_itmes_num).", ".$one_page_itmes_num."",$full_data_flag
	//$full_data_flag = false;
	
	
	///////////////////        page nav        ///////////////////
	//$one_page_itmes_num =  isset($_GET['one_page_itmes_num'])? $_GET['one_page_itmes_num'] : 51 ;
	//////////////////////////////////////////////////////////////
	
	
	
	
	 // echo '<pre>';print_r($clients_data);echo '</pre>'; 
	if(is_array($clients_data)){
	
		///////////////////        page nav        ///////////////////
		/*
		$all_itmes_num = $clients_data['all_clients_num'];
		$all_num_page = intval(($all_itmes_num - 1)/ $one_page_itmes_num) + 1;
		$num_page = ($num_page > $all_num_page)? $all_num_page : $num_page ;
		$url_query = $_SERVER['QUERY_STRING'];
	
		$page_navigation = pageNav($num_page,9,$all_num_page,$url_query);
		*/
		//////////////////////////////////////////////////////////////
		
        if($curViewType == 'ordinary' || $curViewType == 'short'){

        	foreach($clients_data['data'] as $item) $ids[$item['id']] = $item['id'];
        	$client_expanded_data = get_expanded_data_for_client_list($ids,$user_id);


        	$no_use_for_client_href = '';
	     	$main_tbl_class = 'main_tbl_ordinary';
			$num_items_on_page = count($clients_data['data']);
			if($curViewType == 'ordinary') $num_cols = 3;
			if($curViewType == 'short') $num_cols = 4;
			$items_in_col = ceil($num_items_on_page/$num_cols);
			$counter = 0;
			echo "<tr><td>";
			foreach($clients_data['data'] as $item){
				// проверка прав
				$no_use_for_client_href = '';
				if($user_status != 1 && @!in_array($user_id,$client_expanded_data[$item['id']]['curators_id'])){
					$no_use_for_client_href = ' onclick="echo_message_js(\'Вы не можете просматривать в чужого клиента.\');return false;" ';
				}

			    //$width = $item['rate']*12;
				eval('?>'.$tpl.'<?php '); 
				if(++$counter%$items_in_col == 0 && $counter/$items_in_col < $num_cols) echo "</td><td>";
			}
			echo "</td></tr>";
			$header_tbl = '';
			$header_tr = '<tr>'.str_repeat('<td>&nbsp;</td>',$num_cols).'</tr>';
		}
		
		else if($curViewType == 'wide' || $curViewType == 'expanded'){

		    foreach($clients_data['data'] as $item) $ids[$item['id']] = $item['id'];
			
			// echo $user_id;
		    // echo '<pre>';
		    // print_r($ids);
		    // echo '</pre>';

			$client_expanded_data = get_expanded_data_for_client_list($ids,$user_id);
			/*echo '<pre>';print_r($client_expanded_data);echo '</pre>';*/
			$main_tbl_class = 'main_tbl_wide';
			
			// echo '<pre>';
			// print_r($clients_data['data']);
			// echo '</pre>';
			// echo '<pre>';
			// print_r($client_expanded_data);
			// echo '</pre>';
		    foreach($clients_data['data'] as $item){
			    $no_use_for_client_href = '';
			    $str = 'нет данных';
			    if($curViewType == 'wide'){
			    	
					$curators=isset($client_expanded_data[$item['id']]['curators'])?$client_expanded_data[$item['id']]['curators'][0]:$str;

					
					// не выводим информацию по чужим клиентам для всех кроме администратора
					$phones = $emails = $contacts = $str;
					if($user_status == 1 ){
						$phones = (isset($client_expanded_data[$item['id']]['phones']) )? $client_expanded_data[$item['id']]['phones'][0]:$str;
						$emails = (isset($client_expanded_data[$item['id']]['emails']) )? $client_expanded_data[$item['id']]['emails'][0]:$str;
						$contacts = (isset($client_expanded_data[$item['id']]['contacts']) )?$client_expanded_data[$item['id']]['contacts'][0]:$str;
					}else{
						if(isset($client_expanded_data[$item['id']]['curators_id']) && (in_array($user_id,$client_expanded_data[$item['id']]['curators_id']))){
							$phones = (isset($client_expanded_data[$item['id']]['phones']) )? $client_expanded_data[$item['id']]['phones'][0]:$str;
							$emails = (isset($client_expanded_data[$item['id']]['emails']) )? $client_expanded_data[$item['id']]['emails'][0]:$str;
							$contacts = (isset($client_expanded_data[$item['id']]['contacts']) )?$client_expanded_data[$item['id']]['contacts'][0]:$str;
						}else{
							$no_use_for_client_href = ' onclick="echo_message_js(\'Вы не можете просматривать в чужого клиента.\');return false;" ';
						}
						
					}
					// $phones = (isset($client_expanded_data[$item['id']]['phones']))? $client_expanded_data[$item['id']]['phones'][0]:$str;
					// $emails = (isset($client_expanded_data[$item['id']]['emails']))? $client_expanded_data[$item['id']]['emails'][0]:$str;
					// $contacts=(isset($client_expanded_data[$item['id']]['contacts']))?$client_expanded_data[$item['id']]['contacts'][0]:$str;
					
					$curators_full_list = make_drop_down_list(@$client_expanded_data[$item['id']]['curators']);
					$contacts_full_list = '';
					$phones_full_list = '';
					$emails_full_list = '';
					if($user_status != 1 && isset($client_expanded_data[$item['id']]['curators_id']) && (in_array($user_id,$client_expanded_data[$item['id']]['curators_id']))){
						$contacts_full_list = make_drop_down_list(@$client_expanded_data[$item['id']]['contacts']);
						$phones_full_list = make_drop_down_list(@$client_expanded_data[$item['id']]['phones']);
						$emails_full_list = make_drop_down_list(@$client_expanded_data[$item['id']]['emails']);	
					}
					
					
					$curators_class = ($curators_full_list != '')?'roll':'';
					$contacts_class = ($contacts_full_list != '')?'roll':'';
					$phones_class = ($phones_full_list != '')?'roll':'';
					$emails_class = ($emails_full_list != '')?'roll':'';
					
					$curators_click = ($curators_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$contacts_click = ($contacts_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$phones_click = ($phones_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
					$emails_click = ($emails_full_list != '')?'onclick="openCloseMenu(event,\'subjectsList\');"':'';
				}
				if($curViewType == 'expanded'){
					
					$curators=isset($client_expanded_data[$item['id']]['curators'])?make_inner_cell_list(@$client_expanded_data[$item['id']]['curators'],'inner_row'):$str;
					
					$phones = $emails = $contacts = $str;
					if($user_status == 1 ){
						$contacts=isset($client_expanded_data[$item['id']]['contacts'])?make_inner_cell_list(@$client_expanded_data[$item['id']]['contacts'],'inner_row'):$str;
						$phones = isset($client_expanded_data[$item['id']]['phones'])? make_inner_cell_list(@$client_expanded_data[$item['id']]['phones'],'inner_row'):$str;
						$emails = isset($client_expanded_data[$item['id']]['emails'])? make_inner_cell_list(@$client_expanded_data[$item['id']]['emails'],'inner_row'):$str;
					}else{
						if(isset($client_expanded_data[$item['id']]['curators_id']) && (in_array($user_id,$client_expanded_data[$item['id']]['curators_id']))){
							$contacts=isset($client_expanded_data[$item['id']]['contacts'])?make_inner_cell_list(@$client_expanded_data[$item['id']]['contacts'],'inner_row'):$str;
							$phones = isset($client_expanded_data[$item['id']]['phones'])? make_inner_cell_list(@$client_expanded_data[$item['id']]['phones'],'inner_row'):$str;
							$emails = isset($client_expanded_data[$item['id']]['emails'])? make_inner_cell_list(@$client_expanded_data[$item['id']]['emails'],'inner_row'):$str;
						}else{
							$no_use_for_client_href = ' onclick="echo_message_js(\'Вы не можете просматривать в чужого клиента.\');return false;" ';
						}
						
					}
					
				 }
				
				eval('?>'.$tpl.'<?php '); 
				//if(++$counter%$items_in_col == 0 && $counter/$items_in_col < $num_cols) echo "</td><td>";
			}
			
			$tpl_name = './skins/tpl/clients/client_list/header_tbl.tpl';
			$fd = fopen($tpl_name,'r');
			$header_tbl = fread($fd,filesize($tpl_name));
			fclose($fd);
			
			
			$tpl_name = './skins/tpl/clients/client_list/header_tr.tpl';
			$fd = fopen($tpl_name,'r');
			$header_tr = fread($fd,filesize($tpl_name));
			fclose($fd);
		}
	}
	else{
	    echo 'клиенты не найдены';
		$page_navigation = $header_tbl = $header_tr = '';
	}
	
	$rows = ob_get_contents();
	ob_get_clean();
	// filters rendering
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
				    
	//
	$by_alphabet_class = (((isset($_GET['sotring']) && $_GET['sotring'] == 'by_alphabet') || !isset($_GET['sotring']))?'active':'');
	$by_rt_update_class = ((isset($_GET['sotring']) && $_GET['sotring'] == 'by_rt_update')?'active':'');
	$by_creating_date_class = ((isset($_GET['sotring']) && $_GET['sotring'] == 'by_creating_date')?'active':'');
	
	ob_start();
	include('./skins/tpl/clients/client_list/dialog_windows.tpl');
	$dialog_windows = ob_get_contents();
	ob_get_clean();

	ob_start();

	include('./skins/tpl/clients/client_list/top_plank.tpl');
	include('./skins/tpl/clients/client_list/list.tpl');

	// echo 'asd65ds1ads3c21dasv3da2s1v32asd1vasd3v24d 12a';
	$content = ob_get_contents();
	ob_get_clean();
	

	include('./skins/tpl/common/quick_bar.tpl');
	include('./skins/tpl/clients/client_list/show.tpl');
	
	
?>