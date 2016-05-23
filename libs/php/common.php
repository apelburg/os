<?php


if(isset($_SESSION['access']['user_id'])){ // && ($_SESSION['access']['access']==1
   
	require_once (ROOT.'/libs/php/classes/planner_class.php');

    $remainder_user_id = $_SESSION['access']['user_id'];
	
	Planner::init_warnings($remainder_user_id);
	
	
    if($remainder_user_id == '18'){
       //Planner::check_who_needs_approval($remainder_user_id);
	   //echo'<pre>';print_r($_SESSION['warnings']); echo'<pre>';
	   
	   //Planner::init_warnings(33);
	   //echo'<pre>';print_r($_SESSION['warnings']); echo'<pre>';
	   //$clients_ids_arr = Planner::get_related_clients_ids(33);
	   //echo'<pre>';print_r($clients_ids_arr); echo'<pre>';
	} 
	//Planner::set_approval_delay(42,583,24235,3600*3);
	//Planner::set_approval_result(18,$_POST['plan_id'],$result,$_POST['comment']);
	//Planner::check_who_needs_approval($remainder_user_id);//////
    //echo'<pre>';print_r($_SESSION['warnings']['warnings']); echo'<pre>';// exit;	
	if(isset($_POST['ajax_reminder'])){

		if($_POST['ajax_reminder']=="get_alert_planer"){
		    // print_r($_SESSION['warnings']['warnings']);
			echo json_encode($_SESSION['warnings']);
			exit;
		}
		
		if($_POST['ajax_reminder']=="OK"){
		    // при клике менеджером на кнопку в "OK" окне
		    
			Planner::push_OK($_POST['client_id'],$_POST['window_type'],$_POST['event_type']);
			echo '{"response":"1"}';
			exit;
		}
		if($_POST['ajax_reminder']=="remaind_after"){
		    // при клике менеджером на кнопку "ОТЛОЖИТЬ"
      
			// объединяем black,red,yellow,green в одну группу expired_event чтобы опция отложить действовала на смежные зоны 
			// при попадании даты в с межную зону
			Planner::set_delay($remainder_user_id,$_POST['client_id'],$_POST['window_type'],$_POST['event_type'],$_POST['time']);
			
			echo '{"response":"1"}';
			exit;
		}
		if($_POST['ajax_reminder']=="approval_remaind_after"){
		    // при клике контроллером  на кнопку "ОТЛОЖИТЬ"
     
			Planner::set_approval_delay($remainder_user_id,$_POST['client_id'],$_POST['plan_id'],$_POST['time']);
			
			echo '{"response":"1"}';
			exit;
		}
		if($_POST['ajax_reminder']=="approval_result"){
		    // отклонить
			if($_POST['status'] == 'approved') $status = 'done';
			if($_POST['status'] == 'no_approved') $status = 'rejected';
			
			Planner::set_approval_result($remainder_user_id,$_POST['plan_id'],$status,$_POST['comment']);
			
			echo '{"response":"1"}';
			exit;
		}

		if($_POST['ajax_reminder']=="window_set_minimize"){
		    // при клике менеджером на кнопку "СВЕРНУТЬ ОКНО"
			Planner::window_set_minimize($_POST['client_id'],$_POST['window_type'],$_POST['event_type'],$_POST['window_set_minimize']);
			exit;
		}
		if($_POST['ajax_reminder']=="show_help"){
		    // при клике менеджером на кнопку "?"
			echo getHelp('warnings.planner.terms');
			exit;
		}
		if($_POST['ajax_reminder']=="session_was_shown"){
		    // отправляется клиентом когда очередная сессия оповещений была показа первый раз 
			// яваскрипт отправляет запрос после того как были выгруженны окна оповещений
			// здесь в любом случае передаем id реального юзера (да же если админом в другом месте используется фейковый id для отладки)
			Planner::remaind_counter($_SESSION['access']['user_id']);
			exit;
		}
	}
}
   
    if(isset($_POST['add_data_to_rt_from_basket'])){
		 include_once ROOT.'/libs/php/classes/rt_class.php';
		 //echo $_POST['client_data'].' - '.$_POST['manager_login'];
		 echo RT::add_data_from_basket_directly($_POST['client_data'],$_POST['dop_info'],$_POST['manager_login']);
		 exit;
	}
	if(isset($_GET['subquery_for_planner_window'])){
	     include_once ROOT.'/libs/php/classes/client_class.php';
	     echo Client::cont_faces_list($_GET['client_id']);
	     exit;
    }
	
	function get_content($path){
	     $fd = fopen($path,"rb");
		 if( filesize($path) > 0 ) $content = fread($fd,filesize($path));
		 else $content = '';
		 fclose($fd);
	     return $content;
	}
	function put_content($path,$content){
	     //echo $path;
		 // echo '<br>';
		 // echo $page_content;
	     $fd = fopen($path,"wb");
		 fwrite($fd,$content);
		 fclose($fd);
	}
	
	
    function getHelp($topic){
	    $filename = ROOT.'/libs/help/'.$topic.'.txt';
	    $fd = fopen($filename,"rb");
		$content = fread($fd,filesize($filename));
		return $content;	
	}

    function addOrReplaceGetOnURL( $new_get, $del_get = NULL ){
	    // данные из строки запроса
        if($_SERVER['QUERY_STRING'] == '' && $new_get == '') return '';
        // данные из строки запроса
	    if($_SERVER['QUERY_STRING'] != ''){
		     $pairs = (strpos($_SERVER['QUERY_STRING'],'&'))? explode('&',$_SERVER['QUERY_STRING']): array($_SERVER['QUERY_STRING']);
			 foreach($pairs as $pair){
				 list($param,$value)= (strpos($pair,'='))? explode('=',$pair):array($pair,'');
				 $pairs_arr[$param] = urldecode($value);
			 }
		}
		
		// новые данные для замены существующих параметров или добавления новых
		if($new_get != ''){
		    // создаем массив новых данных
			$new_pairs = explode('&',$new_get);
			foreach($new_pairs as $pair){
			    list($param,$value)= explode('=',$pair);
				$new_pairs_arr[$param] = $value;
			}
			//
			foreach($new_pairs_arr as $new_param => $new_val) $pairs_arr[$new_param] = $new_val;			
		    //print_r($pairs_arr);
		}
		// параметры для удаления
		if($del_get){
			$del_get_params = explode('&',$del_get);
			// создаем параметров удаления
			foreach($del_get_params as $param) $del_get_params_arr[$param] = '';
			//
            foreach($del_get_params_arr as $del_param => $val) unset($pairs_arr[$del_param]);
		    //print_r($del_get_params_arr);
		} 
		//print_r($pairs_arr);
		
		if(count($pairs_arr) == 0) return '';
		
		foreach($pairs_arr as $param => $val) $itog_pairs[] = $param.'='.$val;
	    
		// htmlspecialchars()
		return implode('&',$itog_pairs);
    }
	
	function cor_data_for_SQL($data){
	    if(is_int($data) || is_double($data)) return($data);
	    //return strtr($data,"1","2");
		$data = strip_tags($data,'<b><br><br /><a>');
		return mysql_real_escape_string($data);
	}
	
	function save_way_back($exeptions,$default=HOST){
	    global $page;
		global $section;
		// Сфера применения, пример: мы можем зайти в РТ с нескольких "внешних" страниц на которые нам надо вернуться,
		// после работы в РТ, при этом в РТ есть внутренние разделы такие как карточка клиента, КП, Договоры и д.р.
		// после посещения которых нам надо чтобы у нас осталась ссылка на страницу с которой мы зашли изначально
		// для реализации этой задачи, функция запоминает все страницы с которых был осуществлен переход, кроме тех
		// которые передаются в функцию в виде исключений в (определение того какие страницы надо исключить происходит по 
		// $_GET параметрам page или section или другим)
		
		//
		
		// при входе на страницу функция запоминает обратный путь, по $_SERVER['HTTP_REFERER']
		// при этом делает это выборочно, что позволяет сделать ссылки возвращающие на "нужные" страницы
		// (в отличие от javascript:history.go(-1) которая возвращает на только что посещенную страницу)
		
		
		// форматируем переданный аргумент если это был не массив
	    if(!is_array($exeptions)) $exeptions = array((string)$exeptions);
		
	    foreach($exeptions as $exeption){
		    // если найдено совпадение в URL, значит это страница которую запоминать не нужно
		    if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$exeption)!== false) return;
		}
		// сохраняем URL
		// в соответсвии с тем имеет ли URL страницы параметры page или section
		// для дальнейшей идентификации сохраняем с использованием этих параметров
		// если страница была "открыта с нуля" и не имеет параметра $_SERVER['HTTP_REFERER']
		// устанавливается дефолтный URL
	    if($section) $_SESSION['go_back'][$page][$section]['link'] = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER']:$default;
		else $_SESSION['go_back'][$page]['link'] = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER']:$default;//'?'.$_SERVER['QUERY_STRING'];
	}
	
	function get_link_back(){
	    global $page;
		global $section;
		global $POSITION;
		
	    if($section){
		    $href = (isset($_SESSION['go_back'][$page][$section]['link']))? $_SESSION['go_back'][$page][$section]['link']:$_SERVER['HTTP_REFERER'];
	    }
		else $href = (isset($_SESSION['go_back'][$page]['link']))? $_SESSION['go_back'][$page]['link']:$_SERVER['HTTP_REFERER'];
		
		// исключение - при формировании обратной ссылки с карточки товара, для перехода в РТ, необходимо к сылке добавлять 
		// ссылку на анкор ввиде #rowНомерРядаВРТ
		if(isset($POSITION)) $href .= '#row'.$POSITION->position['sort'];
		
		return '<a href="'.$href.'"></a>';
	}
	
	// Содержит нерабочие дни года, кроме календарных выходных (их можно не указывать)
	// есть один момент который не учтен - если календарный выходной переносится на другой день и становится рабочим, то
	// это пока ни как не учитывается - пример 20 февраля 2016 по календарю суббота но это рабочий день. в 2015 году таких 
	// дней еще больше, в случае доработки надо доработать аналогичный JavaScript скрипт
	$SELEBRATIONS = array(
	                         "2015" => array("01.01","02.01","03.01","04.01","05.01","06.01","07.01","08.01","09.01","23.02","09.03","01.05","04.05","09.05","11.05","12.06","04.11"),   
							 "2016" => array("01.01","02.01","03.01","04.01","05.01","06.01","07.01","08.01","09.01","10.01","22.02","23.02","07.03","08.03","02.05","03.05","09.05","13.06","04.11")
						 );
	
	function goOnSomeWorkingDays($begin/*агрумент должен быть в формате "0000-00-00 00:00:00"*/,$days_num/*число рабочих дней*/,$direct/*+/-(вперед или назад)*/){
	    // функция принимает указанную дату и возвращает новую дату наступающую через указанное количество рабочих дней
		// пример вызова addWorkingDays("2015-11-05 10:41:01",5,'+');
	    global $SELEBRATIONS;
		if(!isset($SELEBRATIONS[substr($begin,0,4)])){ echo 'не установлен календарь праздничных дней на '.substr($begin,0,4).' год.'; }
	    $secondsInDay = 60*60*24;
		$out_date = dateToUnix($begin);

	    while($days_num>0){
	        if($direct=='+') $out_date+=$secondsInDay;
            if($direct=='-') $out_date-=$secondsInDay;

		    $dayInWeek = date("w",$out_date);
		    // если суббота или воскресенье
		    if($dayInWeek == 6 || $dayInWeek == 0) continue;

		    $year = date("Y",$out_date);
		    $dayMonth = date("d.m",$out_date);
		    // если праздничный день
		    if(isset($selebrations[$year]) && in_array($dayMonth,$selebrations[$year])) continue;
		  
		    $days_num--;
	    }
	    return  date("Y-m-d H:i:s",$out_date);
	     
	}
	function getWorkingDays($begin,$end/*агрументы должны быть в формате "0000-00-00 00:00:00"*/){
	    global $SELEBRATIONS;
        // функция подсчитывает количество рабочих дней со следующего дня после $begin по $end включительно
		// пример вызова getWorkingDays("2015-11-05 10:41:01","2015-11-07 10:41:01");
        // календарь праздничных дней - для каждого года массив дат в формате 00.00 ( день.месяц )
	   
        if(!isset($SELEBRATIONS[substr($begin,0,4)])){ echo 'не установлен календарь праздничных дней на '.substr($begin,0,4).' год.'; }
	    if(!isset($SELEBRATIONS[substr($end,0,4)])){ echo 'не установлен календарь праздничных дней на '.substr($end,0,4).' год.'; }   
       
        $begin = dateToUnix($begin);
	    $end = dateToUnix($end);
	   
	    $secondsInDay = 60*60*24;
	    $counter = 0;
	    while($begin<$end){
	        $begin+=$secondsInDay;

		    $dayInWeek = date("w",$begin);
		    // если суббота или воскресенье
		    if($dayInWeek == 6 || $dayInWeek == 0) continue;

		    $year = date("Y",$begin);
		    $dayMonth = date("d.m",$begin);
		    // если праздничный день
		    if(isset($selebrations[$year]) && in_array($dayMonth,$selebrations[$year])) continue;
		  
		    $counter++;
	    }
	    return $counter;
    }
    function dateToUnix($datetime){
        list($date,$time) = explode(" ",$datetime); 
	    list($year,$month,$day) = explode("-",$date); 
	    list($hour,$minute,$second) = explode(":",$time); 
	    return mktime($hour, $minute, $second, $month, $day, $year); 
    }
   
	function identify_supplier_by_prefix($article){// 
	   global $suppliers_data_by_prefix;					   
	   $prefix = substr($article,0,2);
	   // if(isset($_SESSION['access']['user_id']) && $_SESSION['access']['user_id'] == 42){
	   // 	echo '<pre>';
	   // 	print_r($suppliers_data_by_prefix);
	   // 	echo '</pre>';
	   // 	echo '<pre>';
	   // 	print_r($suppliers_data_by_prefix[$prefix]);
	   // 	echo '</pre>';
	   		
	   		
	   // }else {
	   	if(isset($suppliers_data_by_prefix[$prefix])){
	      $article_orig_name = substr($article,2);
	      return '<a target="_blank" class="rt_supplier_link" href="'.$suppliers_data_by_prefix[$prefix]['link'].$article_orig_name.'">'.$suppliers_data_by_prefix[$prefix]['name'].'</a>';
		  
	   }else{
			return '';
	   }
	   // }
	   
	}

	function identify_supplier_href($article){// 
	   global $suppliers_data_by_prefix;					   
	   $prefix = substr($article,0,2);
	   
	   if(isset($suppliers_data_by_prefix[$prefix])){
	      $article_orig_name = substr($article,2);
	      return $suppliers_data_by_prefix[$prefix]['link'].$article_orig_name;
		  
	   }
	   else return '';
	}
	/*   Чтение директрорий  */
	function read_Dir($path){
	     $dir = opendir($path);
		 while($item = readdir($dir)){
		    if($item != '.' && $item != '..' && strtolower($item) != 'thumbs.db'){
		        $item_arr[] = $item;
			}
		 }
	     return (isset($item_arr))? $item_arr : false ;
	 }
  
    /*   Проверка наличия изображения  */
	function checkImgExists($path,$no_image_name = NULL ){
	    $mime = getExtension($path);
		if(@fopen($path, 'r')){//file_exists
			$img_src = $path;	
		}
		else{
		    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image.jpg';
			$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name;
		} 
		return $img_src;
	}
	    
    /* Функция возвращаюющая раcширение файла */
    function getExtension($filename){
        $path_info = pathinfo($filename);
        return $path_info['extension'];
    }
	
	function transform_img_size($img,$limit_height,$limit_width){
     	list($img_width, $img_height, $type, $attr) = (fopen($img,'r'))? getimagesize($img): array($limit_width,$limit_height,'',''); 
		if($img_width==0 || $img_height ==0 )return array(0,0);
		$limit_relate = $limit_height/$limit_width;
		$img_relate = $img_height/$img_width;
		if($limit_relate < $img_relate) return array($limit_height,$limit_height/$img_relate); 
		else  return array($limit_width*$img_relate,$limit_width); 
		//return array($limit_height,$limit_width); 
	}
	
	function convert_bb_tags($text){
	    // bb tags
		$bb_arr  =  array('[B]', 
                          '[/B]', 
                          '[I]', 
                          '[/I]');
	    // html tags
		$tags_arr = array('<b>', 
                          '</b>', 
                          '<i>', 
                          '</i>');
		$text = str_ireplace($bb_arr,$tags_arr,$text);
		
		// url tag
		$text = preg_replace_callback('/\[url\]((.+?))\[\/url\]/',create_function('$matches','return "<a href=\"http://".htmlspecialchars($matches[1])."\">".htmlspecialchars($matches[1])."</a>";'),$text);
		// mail tag
		$text = preg_replace_callback('/\[mail\]((.+?))\[\/mail\]/',create_function('$matches','return "<a href=\"mailto:".htmlspecialchars($matches[1])."\">".htmlspecialchars($matches[1])."</a>";'),$text);
		$text = nl2br($text);
			              
        return $text;  
	}
	
	function pageNav($num_cur_page,$quant_show_page,$num_all_page,$query){// $quant_show_page - должно быть не четным числом
	       
	   if($num_all_page == 1) return ;
	   // strpos здесь не работает так как возвращеат 0 в некоторых случаях и условие не срабатывает 
	   // вместо substr надо использовать preg_replace
	   $query = preg_match('/num_page=/',$query)? preg_replace('/[&]?num_page=[\d]*/','',$query).'&' : $query.'&'; 

	   if($query == '&') $query = '';
	   
	   $page_nav = '';
	   if($num_all_page > $quant_show_page && $num_cur_page != 1 ){
			$page_nav .='<a href="?'.$query.'num_page=1">первая</a>|<a href="?'.$query.'num_page='.($num_cur_page - 1).'"><<</a>';
	   }
	   
	   if($num_cur_page > ($quant_show_page -1)/2 ) $s = $num_cur_page - ($quant_show_page -1)/2 ;
	   else  $s = 1;
	   if($num_cur_page + ($quant_show_page -1)/2 > $num_all_page) $quant_show_page = $num_all_page - $num_cur_page + ($quant_show_page -1)/2 + 1;
	   if($num_all_page < $quant_show_page) $quant_show_page = $num_all_page;
	   for($i = $s ; $i < $quant_show_page + $s; $i++){
	   
		  if($num_cur_page == $i) $page_nav .= '|<span class="page_nav_current_link">'.$i.'</span>';
		  else $page_nav .= '|<a href="?'.$query.'num_page='.$i.'">'.$i.'</a>';
	   
	   }
	   $page_nav .= '|';
	   
	   if($num_all_page > $quant_show_page && $num_cur_page != $num_all_page){
			$page_nav .=' <a href="?'.$query.'num_page='.($num_cur_page + 1).'">>></a>|<a href="?'.$query.'num_page='.$num_all_page.'">последняя</a>';
	   }
	   return $page_nav;
	 }

	function get_control_num(){
		global $db;
		
	    $query = "SELECT*FROM `".CALCULATE_TBL_PROTOCOL."`";	
		$result = mysql_query($query,$db);
		if(!$result)exit(mysql_error());
		
		return mysql_num_rows($result);
	}
	
	function check_changes_to_rt_protocol($control_num,$id){
		global $db;
		
	    $query = "SELECT*FROM `".CALCULATE_TBL_PROTOCOL."`";	
		$result = mysql_query($query,$db);
		if(!$result)exit(mysql_error());
		
		$num_rows = mysql_num_rows($result);
		if($num_rows == $control_num) return $id;
		
		//
		for($i = $control_num ; $i < $num_rows; $i++){
		    $row_id = mysql_result($result,$i,'row_id');
			$action = mysql_result($result,$i,'action');
			
			if($action == 'insert'){
			    if($row_id <= $id ) ++$id;
			} 
			if($action == 'delete'){
			    if($row_id < $id )--$id;
				if($row_id == $id ) return false;
			}
		}
		
		return $id;
	}
	
	function get_alphabets($range = false/*or array('by'=>some_value,'id'=>id_string)*/,$filters){
	    global $db;
		
		$go_on = true;
		
		if($range){
			if($range['by']){
				$query = "SELECT*FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `".$range['by']."` IN (".$range['id'].")";
				$result = mysql_query($query,$db);
				if(!$result) echo(mysql_error());
				if(mysql_num_rows($result)>0){
					// создаем строку содержащую список id клиентов
					$in_string = '';
					while($item = mysql_fetch_assoc($result)) $in_string .= $item['client_id'].',';
					$in_string = trim($in_string,",");
				}
				else $go_on = false;
			}
		}
		else $in_string = '';
		
		if($go_on){
		
			function get_alphabet($range,$in_string = FALSE,$filters,$alphabet_range = FALSE){
				global $db;
				
				$query = "SELECT LEFT(company,1) alphabet FROM `".CLIENTS_TBL."`";
				if($range) $where[]=  "`id` IN (".$in_string.")";
				/*/ учитывание фильтров пока отключено
				if(count($filters)>0){
				   foreach($filters as $filter){
					  if($filter['type']=='by_rating') $where[]=  "`".$filter['col']."` IN(".$filter['val'].")";
				   }
				}
				*/
				if($alphabet_range) $where[]=  "LEFT(company,1) BETWEEN '".$alphabet_range[0]."' AND '".$alphabet_range[1]."'";
				$query .= (isset($where))? "WHERE ".implode('AND ',$where):'';
				$query .=  ' GROUP BY LEFT(company,1) ORDER BY company';
				
				$result = mysql_query($query,$db); //or die(mysql_error())
				if(mysql_num_rows($result)>0){
					while($item = mysql_fetch_assoc($result)){
						//if($item['alphabet']!=' '){
							$alphabet[] = $item['alphabet'];
						//}
					}
					return $alphabet;
				}
				else return array();
			}
			return array(get_alphabet($range,$in_string,$filters,array('а','я')),get_alphabet($range,$in_string,$filters,$alphabet_range = array('a','z')));
		}
		else return array(array(),array());	
	}
	
	function get_suppliers_list($range = false/*or array('by'=>some_value,'val'=>some_value)*/,$order,$filters,$search,$limit_str){
	    global $db;
		
		$go_on = true;
		
		
        // вычисляем рейтинг
		$rate_arr = array();
		$query = "SELECT subject_id, COUNT(*) count , SUM(rate) rate FROM `".SUPPLIERS_RATINGS_TBL."` GROUP BY subject_id";
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)) $rate_arr[(int)$item['subject_id']] = round($item['rate']/$item['count'],1);
		}	
		//echo '<pre>'; print_r($rate_arr); echo '</pre>';
		/*exit;*/

		
		$query = "SELECT suppliers_tbl.id id,suppliers_tbl.nickName nickName FROM ".SUPPLIERS_TBL." suppliers_tbl";
		
		$where = array();
		// кроме тех которые в корзине
		$where[] = "suppliers_tbl.basket = '0'"; 
		
		if($range && $range['by'] == 'cities'){
			 $cities_name_patterns= array('msk' => array('мск','москва'),'spb' => array('спб','петербург','питер'));
			 foreach($cities_name_patterns[$range['val']] as $pattern) $sub_where[]=  "suppliers_tbl.addres LIKE '%".$pattern."%'";
			 $where[] = '('.implode(' OR ',$sub_where).')';
		}
			
		if($filters && count($filters)>0){
			foreach($filters as $filter){
				//if($filter['type']=='by_rating') $where[]=  "`".$filter['col']."` IN(".$filter['val'].")";
				if($filter['type']=='by_letter'){
					if(strpos($filter['val'],'-') === false){
						$where[]=  "LEFT(suppliers_tbl.".$filter['col'].",1) = '".$filter['val']."'";
					}
					else{
						$pairs = explode('-',$filter['val']);
						$where[]=  "LEFT(suppliers_tbl.".$filter['col'].",1) BETWEEN '".$pairs[0]."' AND '".$pairs[1]."'";
					}
				}
				if($filter['type']=='by_rating'){
					$filter_arr = explode(',',$filter['val']);
					foreach($rate_arr as $key => $val){
						foreach($filter_arr as $filter_val){
							if($val >= $filter_val && $val < $filter_val+1) $ids[] = $key;
						}
					}
					$where[]=  "suppliers_tbl.id IN (".implode(',',$ids).")";
				}
				if($filter['type']=='by_profies'){
					$join_with_profiles = true;
					$where[]=  "relate_tbl.activity_id IN (".$filter['val'].")";
				}
			}
		}
		if($search) $where[]=  "suppliers_tbl.nickName LIKE '%".$search."%'";
		
		//$query .= (isset($join_with_profiles))? " INNER JOIN ".RELATE_SUPPLIERS_ACTIVITIES_TBL." relate_tbl ON  suppliers_tbl.id = relate_tbl.supplier_id INNER JOIN ".SUPPLIERS_ACTIVITIES_TBL." activities_tbl ON  relate_tbl.activity_id = activities_tbl.id":'';
		$query .= (isset($join_with_profiles))? " INNER JOIN ".RELATE_SUPPLIERS_ACTIVITIES_TBL." relate_tbl ON  suppliers_tbl.id = relate_tbl.supplier_id ":'';
		$query .= (count($where)>0)? " WHERE ".implode('AND ',$where):'';

			
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		$all_clients_num = mysql_num_rows($result);
		
		if($order) $query .=  " ORDER BY suppliers_tbl.".$order[0]." ".$order[1];
		if($limit_str) $query .=  " ".$order[1]." ".$limit_str;
		
		//echo $query;
		//exit;
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
					$rate = (isset($rate_arr[$item['id']]))? $rate_arr[$item['id']] : 0 ;
					$clients_arr[] = array('id' => $item['id'],'name' => $item['nickName'],'rate' => $rate);//,'rate' => $item['rate']
			}
			return array('all_clients_num'=>$all_clients_num,'data'=>$clients_arr);		
		}
		else return false;
			
	}
	
	function get_all_suppliers_list_from_basket($order_by){
	    global $db;
		$query = "SELECT*FROM `".SUPPLIER_TBL."` WHERE `basket` = '1'  ORDER BY `".$order_by."`";		
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0) while($item = mysql_fetch_assoc($result)) $supplier_arr[] =  array('id' => $item['id'],'nickname' => $item['nickName']);
		else $supplier_arr = 'в корзине нет поставщиков';
		return $supplier_arr;
	}
	
	function get_clients_list($range = false/*or array('by'=>some_value,'id'=>id_string)*/,$order,$filters,$search,$limit_str){
	    global $db;
		
		$go_on = true;
		// echo '<pre>';print_r($range);echo '</pre>';
		
	    if($range){
		    if($range['by']){
			    $query = "SELECT*FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `".$range['by']."` IN (".$range['id'].")";
				$result = mysql_query($query,$db);
				if(!$result) echo(mysql_error());
				if(mysql_num_rows($result)>0){
					// создаем строку содержащую список id клиентов
					$in_string = '';
					while($item = mysql_fetch_assoc($result)) $in_string .= $item['client_id'].',';
					$in_string = trim($in_string,",");
				}
				else $go_on = false;
		    }
	    }

		if($go_on){// clients table
		
		    if($order[0] == 'time_change'){

			    $query = "SELECT `client_id` FROM `".CALCULATE_TBL."`";
				if($range['by'] == 'manager_id') $query .= " WHERE  `manager_id` IN (".$range['id'].") AND `client_id` IN (".$in_string.") ";
			    $query .= "ORDER BY `".$order[0]."`";	
				
			/*	$query = "SELECT client_id FROM  (SELECT tbl1.client_id FROM `".CALCULATE_TBL."` tbl1";
				if($range['by'] == 'user_id') $query .= " WHERE  tbl1.manager_id = '".$range['user_id']."' AND tbl1.client_id IN (".$in_string.") ";
			    $query .= "ORDER BY `".$order[0]."`) GROUP BY client_id";	*/
				
			    $result = mysql_query($query,$db);
				if(mysql_num_rows($result)>0){
					while($item = mysql_fetch_assoc($result)) $id_arr[]= $item['client_id'];
				    //print_r($id_arr);
					krsort($id_arr);
					reset($id_arr);
					$id_arr = array_unique($id_arr);
					//if($limit_str != '') $id_arr = array_slice($id_arr, intval(substr($limit_str,strpos($limit_str,'LIMIT')+ 6)),intval(substr($limit_str,strpos($limit_str,',')+2)));
					// echo '<br>';
					//print_r($id_arr);
					if(isset($id_arr)){
						foreach($id_arr as $id){
						   $query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id`  = '".$id."'"; //
						   if(count($filters)>0){
						       foreach($filters as $filter){
							        if($filter['type']=='by_rating') $query .=  " AND  `".$filter['col']."` IN(".$filter['val'].")";
								    if($filter['type']=='by_letter'){
										if(strpos($filter['val'],'-') === false){
											$query .= " AND LEFT(".$filter['col'].",1) = '".$filter['val']."'";
										}
										else{
											$pairs = explode('-',$filter['val']);
											$query .= " AND LEFT(".$filter['col'].",1) BETWEEN '".$pairs[0]."' AND '".$pairs[1]."'";
										}
									}
							   }
						   }
						   if($search) $query .=  " AND `company` LIKE '%".$search."%'";
						   $result = mysql_query($query,$db);
						   if(!$result) echo(mysql_error());
						   $item = mysql_fetch_assoc($result);
						   if(mysql_num_rows($result)>0){
						       $clients_arr[] = array('id' => $item['id'],'name' => $item['name'],'company' => $item['company'],'comp_full_name' => $item['comp_full_name'],'rate' => $item['rate']);
						   }
						}
						if(isset($clients_arr)){
						     $all_clients_num = count($clients_arr);
						     if($limit_str != '') $clients_arr = array_slice($clients_arr, intval(substr($limit_str,strpos($limit_str,'LIMIT')+ 6)),intval(substr($limit_str,strpos($limit_str,',')+2))); 
						}
						else $go_on = false;
					}
				}
				else $go_on = false;	
			}
			else{
			
				$query = "SELECT*FROM `".CLIENTS_TBL."`";
				
				$where = array();
				if($range) $where[]=  "`id` IN (".$in_string.")";
				if(count($filters)>0){
				    foreach($filters as $filter){
					    if($filter['type']=='by_rating') $where[]=  "`".$filter['col']."` IN(".$filter['val'].")";
					    if($filter['type']=='by_letter'){
						    if(strpos($filter['val'],'-') === false){
							    $where[]=  "LEFT(".$filter['col'].",1) = '".$filter['val']."'";
							}
							else{
							    $pairs = explode('-',$filter['val']);
							    $where[]=  "LEFT(".$filter['col'].",1) BETWEEN '".$pairs[0]."' AND '".$pairs[1]."'";
							}
						}
					}
				}
				if($search) $where[]=  "`company` LIKE '%".$search."%'"; // OR `comp_full_name` LIKE '%".$search."%'";
				
				$query .= (count($where)>0)? "WHERE ".implode('AND ',$where):'';

				
				$result = mysql_query($query,$db);
				if(!$result) echo(mysql_error());
				$all_clients_num = mysql_num_rows($result);
				
				$query .=  " ORDER BY `".$order[0]."` ".$order[1]." ".$limit_str;
				$result = mysql_query($query,$db);
				if(!$result) echo(mysql_error());
				if(mysql_num_rows($result)>0){
					while($item = mysql_fetch_assoc($result)){
							$clients_arr[] = array('id' => $item['id'],'name' => $item['name'],'company' => $item['company'],'comp_full_name' => $item['comp_full_name'],'rate' => $item['rate']);
					}		
				}
				else $go_on = false;
			}
			
			
		}
		
		if(!$go_on) return false;
		else return array('all_clients_num'=>$all_clients_num,'data'=>$clients_arr);
		
	}
	
	
	function get_expanded_data_for_client_list($ids,$user_id){
	    global $db;
		
		$ids_string = implode(',',$ids);
		//echo $ids_string;
		
		$query = "SELECT  relate_tbl.client_id client_id, mngs_tbl.id as manager_ids, mngs_tbl.name name, mngs_tbl.last_name last_name FROM `".RELATE_CLIENT_MANAGER_TBL."` relate_tbl 
		          INNER JOIN `".MANAGERS_TBL."` mngs_tbl
				  ON relate_tbl.manager_id = mngs_tbl.id
				  WHERE relate_tbl.client_id IN (".$ids_string.")";
		// $query = "  AND relate_tbl.manager_id = '".$user_id."'"		  ;

		$result = mysql_query($query,$db);
		$managers = array();

		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
				// $managers[$item['manager_ids']] = 1;
			    $arr[$item['client_id']]['curators'][] =  $item['name'].' '.$item['last_name'];
			    $arr[$item['client_id']]['curators_id'][$item['manager_ids']] =  $item['manager_ids'];
			}
		}
		
		###################################################
		function add_to_array($val,$type,&$array){
		    $val = trim($val);
		    if($val == '') return;
		    if(!isset($array[$type])){
			    $array[$type][]  = $val;
			}
			else{
			    if(in_array($val,$array[$type])) return;
			    $array[$type][]  = $val;
			}
		}
		###################################################
		// не запрашиваем данные для не кураторов 
		// echo '<pre>';
		// print_r($managers);
		// echo '</pre>';
		// if(!array_key_exists((string)$user_id,$managers)){
		// 	return $arr;
		// }
		$query = "SELECT client_tbl.id client_id, client_tbl.phone phone, client_tbl.email email, cont_faces_tbl.name name , cont_faces_tbl.phone phone2 , cont_faces_tbl.email email2 FROM `".CLIENTS_TBL."` client_tbl 
		          INNER JOIN `".CLIENT_CONT_FACES_TBL."` cont_faces_tbl
				  ON client_tbl.id = cont_faces_tbl.client_id
				  WHERE client_tbl.id IN (".$ids_string.")";
		
		$result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			    //if(!in_array($item['phone'],$arr[$item['client_id']]['phones'])) $arr[$item['client_id']]['phones'][] =  $item['phone'];
				//if(!in_array($item['email'],$arr[$item['client_id']]['emails'])) $arr[$item['client_id']]['emails'][] =  $item['email'];
				add_to_array($item['phone'],'phones',$arr[$item['client_id']]);
				add_to_array($item['phone2'],'phones',$arr[$item['client_id']]);
				add_to_array($item['email'],'emails',$arr[$item['client_id']]);
				add_to_array($item['email2'],'emails',$arr[$item['client_id']]);
				add_to_array($item['name'],'contacts',$arr[$item['client_id']]);
				
			}
		}
		
		return $arr;
	}
	
	function get_expanded_data_for_supplier_list($ids){
	    global $db;
		
		$ids_string = implode(',',$ids);
		//echo $ids_string;
		//exit;
		
		$query = "SELECT  relate_tbl.supplier_id supplier_id, activities_tbl.name name FROM `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` relate_tbl 
		          INNER JOIN `".SUPPLIERS_ACTIVITIES_TBL."` activities_tbl
				  ON relate_tbl.activity_id = activities_tbl.id
				  WHERE relate_tbl.supplier_id IN (".$ids_string.")";
		$result = mysql_query($query,$db);
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			    $arr[$item['supplier_id']]['activities'][] =  $item['name'];
			}
		}
		
		###################################################
		function add_to_array($val,$type,&$array){
		    $val = trim($val);
		    if($val == '') return;
		    if(!isset($array[$type])){
			    $array[$type][]  = $val;
			}
			else{
			    if(in_array($val,$array[$type])) return;
			    $array[$type][]  = $val;
			}
		}
		###################################################
		$query = "SELECT suppliers_tbl.id supplier_id, suppliers_tbl.phone phone, suppliers_tbl.email email, suppliers_tbl.web_site web_site, cont_faces_tbl.name name , cont_faces_tbl.phone phone2 , cont_faces_tbl.email email2 , cont_faces_tbl.isq_skype isq_skype FROM `".SUPPLIERS_TBL."` suppliers_tbl 
		          INNER JOIN `".SUPPLIERS_CONT_FACES_TBL."` cont_faces_tbl
				  ON suppliers_tbl.id = cont_faces_tbl.supplier_id
				  WHERE suppliers_tbl.id IN (".$ids_string.")";
		$result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			
			    $web_site = ( trim($item['web_site']) !='')?('<a href="'.((strpos($item['web_site'],'http://') === false )? 'http://':'').$item['web_site'].'" target="_blank">'.$item['web_site'].'</a>'):'';
				//$web_site = $item['web_site'];
				
				
				add_to_array($item['phone'],'phones',$arr[$item['supplier_id']]);
				add_to_array($item['phone2'],'phones',$arr[$item['supplier_id']]);
				add_to_array($item['email'],'emails',$arr[$item['supplier_id']]);
				add_to_array($item['email2'],'emails',$arr[$item['supplier_id']]);
				add_to_array($item['name'],'contacts',$arr[$item['supplier_id']]);
				add_to_array($item['isq_skype'],'dop_data',$arr[$item['supplier_id']]);
				add_to_array($web_site,'dop_data',$arr[$item['supplier_id']]);
				
			}
		}/**/
		
		return $arr;
	}
	
	function make_drop_down_list($array =false){
	     if(isset($array) && count($array)>1) return '<div hidden_list="yes" style="display:none;"><div>'.implode('</div><div>',$array).'</div></div>';
		 else return '';
	}
	function make_inner_cell_list($array =false,$class = ''){
	     if(isset($array)) return '<div class="'.$class.'">'.implode('</div><div class="'.$class.'">',$array).'</div>';
		 else return '';
	}
	
	function get_activities_list($search){
	    global $db;
		
	    $query = "SELECT*FROM`".SUPPLIERS_ACTIVITIES_TBL."` ORDER BY `name`";
	    // echo $query;
		if($search) $query .= " WHERE name LIKE '%".$search."%'";
		$result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			    $arr[] = array('id'=>$item['id'], 'name'=>$item['name']);
			}
			return array('data'=>$arr);//$arr;
		}
		else return false;
		
	}
	
/*   
** manager
*/

	 function select_manager_data($id){
	    global $db;
		$query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result)==0) exit('нет пользователя с таким id');
		return $result;	
	}
	
	function get_managers_list(){
	    global $db;
	    $query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `access` = '5' ORDER BY `name`";
	    if($_SESSION['access']['access'] == 1){
	    	$query = "SELECT*FROM `".MANAGERS_TBL."`  ORDER BY `name`";
	    }
		// $query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `access` = '5' ORDER BY `name`";
		
	    $result = mysql_query($query,$db);
		if(mysql_num_rows($result)>0) while($item = mysql_fetch_assoc($result)){
		     $manager_arr[] = array('id' => $item['id'],'access' => $item['access'],'name' => $item['name'],'last_name' => $item['last_name'],'email_2' => $item['email_2']);
		}
		else $manager_arr[] = 'none';
		return $manager_arr;
	}
	
	function get_manager_nickname_by_id($id){
	    global $db;
		$query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(mysql_num_rows($result)>0) $nickname = mysql_result($result,0,'nickname');
		else $nickname = false;
		return $nickname;
	}
/*   
** client
*/
	function select_all_client_data($id){
	    global $db;
		$query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		return mysql_fetch_assoc($result);	
	}
	
	function get_main_client_cont_face($id){
	    global $db;
		
		$array = array('name' => 'не задано',
					   'phone' => '',
					   'mobile' => '',
					   'email' => '',
					   'position' => '',
					   'department' => ''
						);
		
		$query="SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '$id'  ORDER BY `set_main` DESC, `id`";
	    $result=mysql_query($query,$db) or die(mysql_error());

		if(mysql_num_rows($result)>0)
		{
		    $array['name'] = mysql_result($result,0,'name');
			$array['phone'] = mysql_result($result,0,'phone');
			$array['mobile'] = mysql_result($result,0,'mobile');
			$array['email'] = mysql_result($result,0,'email');
			$array['position'] = mysql_result($result,0,'position');
			$array['department'] = mysql_result($result,0,'department');
		}

		return  $array;
	}
	
	function get_base_art_id($art){
	    global $db;
		
		$return = '';
		
		$query = "SELECT*FROM `".BASE_TBL."` WHERE `art` <> ''  AND `art` = '".trim($art)."'";
		$result = mysql_query($query,$db);
		
		if(!$result) $return = ($art.' '.mysql_error());
		if(mysql_num_rows($result)>0) $return = mysql_result($result,0,'id');
	    else $return = '';
		return  $return;
	}
	
	function detect_manager_for_client($client_id){
	    global $db;
	    $query = "SELECT*FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".$client_id."'";
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		//return mysql_result($result,0,'manager_id');
		if(mysql_num_rows($result)>0) while($item = mysql_fetch_assoc($result)) $manager_id_arr[] = $item['manager_id'] ;
		else $manager_id_arr[] = false;
		return $manager_id_arr;
	}

/*   
** calculating tbl
*/

    function set_changes_into_rt($data_arr,$id,$control_num){
	    global $db;
		
		
		$query_substr = '';
		foreach($data_arr as $field => $value){
		    $query_substr.= "`".$field."` = '".$value."', ";
		}
	    $query_substr = trim($query_substr,', ');
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).        получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).        разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
        $row_id = check_changes_to_rt_protocol($control_num,$id);
		if($row_id == false){
		    mysql_query("UNLOCK TABLES") or die(mysql_error());
		    return;
		}
		
        //  3)
		$query = "UPDATE `".CALCULATE_TBL."` SET ".$query_substr." WHERE `id` = '".$row_id."'";
		$result = mysql_query($query,$db) or die(mysql_error());
		
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
        ///////////////////////////
		//print($query);
	}
	
	
	function set_status_master_btn($id,$status,$control_num){
	    global $db;
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).   получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).   разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
        $row_id = check_changes_to_rt_protocol($control_num,$id);
		if($row_id == false){
		    mysql_query("UNLOCK TABLES") or die(mysql_error());
		    return;
		}
		
        //  3)
		$query = "UPDATE `".CALCULATE_TBL."` SET `master_btn` = '".$status."' WHERE `id` IN('".$id."')";
		echo $query;
		$result = mysql_query($query,$db) or die(mysql_error());
		
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
	
	}
	
	
	 function reset_switching_calculation_marker_in_rt($id,$status,$control_num){
	    global $db;
		
		// для совместимости с предыдущим OC
	    $status = ($status == 'on')? '' : 'on';
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).   получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).   разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
        $row_id = check_changes_to_rt_protocol($control_num,$id);
		if($row_id == false){
		    mysql_query("UNLOCK TABLES") or die(mysql_error());
		    return;
		}
		
        //  3)
		$query = "UPDATE `".CALCULATE_TBL."` SET `marker_summ` = '".$status."' WHERE `id` = '".$row_id."'";
		$result = mysql_query($query,$db) or die(mysql_error());
		
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
        ///////////////////////////
		//print($query);
	}
	
	
	function add_order_at_the_end_rt($type_row,$num){
	    global $db; 
		global $client_id; 
		global $user_id;
		
		
		function insert_row($row_id,$type_row,$date,$client_id,$user_id,$db){
		
		    $order_num = ($type_row == 'order')? define_next_order_num_for_rt() : '';	
			 
		    $query = "INSERT INTO `".CALCULATE_TBL."` SET `type` = '".$type_row."', `order_num` = '".$order_num."', `date` = '".$date."', `client_id` = '".$client_id."', `manager_id` = '".$user_id."' ";
		
		    $result = mysql_query($query,$db);
		    if(!$result)exit(mysql_error()); 
		    else make_note_to_rt_protocol('insert',$type_row,mysql_insert_id($db),$user_id,$client_id);
		}
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//  2).   производим изменения в таблице CALCULATE_TBL
		//  3).   разболкируем таблицы CALCULATE_TBL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());
		

		
		//insert_row('','empty','',$client_id,$user_id,$db);
		insert_row('','itog','',$client_id,$user_id,$db);

		for( $i = 0 ; $i < $num ; $i++)
	    {
			insert_row('',$type_row,'',$client_id,$user_id,$db);
		}
		
		insert_row('','order','',$client_id,$user_id,$db);
		
		
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		
	    header('Location:?'.addOrReplaceGetOnURL('','add_order_at_the_end_rt&type_row&num&id&control_num'));
	    exit; 
	}
	
	function add_rows_to_rt($row_id,$type_row,$num,$control_num){
	    global $db; 
		global $client_id; 
		global $user_id;
		
		
		function insert_row($row_id,$type_row,$date,$client_id,$user_id,$db){
		
		    $query_enlarge = "UPDATE `".CALCULATE_TBL."` SET `id` = `id` + 1  WHERE `id` >= '".$row_id."' ORDER BY `id` DESC";
			$result_enlarge = mysql_query($query_enlarge,$db) or die(mysql_error());
			 
		    $query = "INSERT INTO `".CALCULATE_TBL."` SET `id` = '".$row_id."', `type` = '".$type_row."', `order_num` = '', `date` = '".$date."', `client_id` = '".$client_id."', `manager_id` = '".$user_id."' ";
		
		    $result = mysql_query($query,$db);
		    if(!$result)exit(mysql_error()); 
		    else make_note_to_rt_protocol('insert',$type_row,mysql_insert_id($db),$user_id,$client_id);
		}
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//  2).   производим изменения в таблице CALCULATE_TBL
		//  3).   разболкируем таблицы CALCULATE_TBL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());
		
        $row_id = check_changes_to_rt_protocol($control_num,$row_id);
			if($row_id == false){
			mysql_query("UNLOCK TABLES") or die(mysql_error());
			return;
		}

		//  2)
		for( $i = 0 ; $i < $num ; $i++)
	    {
			insert_row($row_id++,$type_row,'',$client_id,$user_id,$db);
		}
		
        //  3)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		
	    header('Location:?'.addOrReplaceGetOnURL('','add_rows_to_rt&add_print_row&type_row&num&id&control_num'));
	    exit; 
	}
	
	
	function add_copied_order_to_rt($row_id,$control_num){
	    global $db; 
		global $client_id; 
		global $user_id;
		
		
		function insert_rows($rows){
			global $db;
			global $client_id; 
			global $user_id;
			
			foreach($rows as $row){
			
			    $order_num =  define_next_order_num_for_rt();
				
				$query = "INSERT INTO `".CALCULATE_TBL."` SET ";
				
				if($row['type'] == 'order') $params[] = "`order_num` = '".$order_num."',`date` = CURRENT_TIMESTAMP() ";
				
				
				foreach($row as $key => $val){
					if($key !='id' &&
					   $key !='time_change' &&
					   $key !='order_num' &&
					   $key !='date' &&
					   $key !='marker_kp' && 
					   $val !='') $params[] = "`".$key."` = '".$val."' ";
				}
				$query .= implode(',',$params);
				//echo $query;
				//echo '<br>';
				//exit;
	
				$result = mysql_query($query,$db);
				if(!$result)exit(mysql_error()); 
				else make_note_to_rt_protocol('insert',$row['type'],mysql_insert_id($db),$user_id,$client_id);
			    if(isset($params)) unset($params);
			}
		}
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//  2).   производим изменения в таблице CALCULATE_TBL
		//  3).   разболкируем таблицы CALCULATE_TBL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());
		
        $row_id = check_changes_to_rt_protocol($control_num,$row_id);
			if($row_id == false){
			mysql_query("UNLOCK TABLES") or die(mysql_error());
			return;
		}

		//  2)
		$query = "SELECT*FROM `".CALCULATE_TBL."` WHERE  `id` <= '".$row_id."' AND `client_id` = '".$client_id."' ORDER BY id DESC ";
		$result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result) > 0 ){
		    while($item = mysql_fetch_assoc($result)){
			    if($item['type'] == 'order' && $item['id'] != $row_id) break;
				$rows[] = $item;
			}
			insert_rows(array_reverse($rows));
			//exit;
		}
		
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		
	    header('Location:?'.addOrReplaceGetOnURL('','add_copied_order_to_rt&order_row_id&control_num'));
	    exit; 
	}
	
	
	
    function define_next_order_num_for_rt(){
	     global $db;
	     $query = "SELECT (MAX(order_num)+1) order_num FROM `".CALCULATE_TBL."` WHERE type = 'order'"; 
	     $result = mysql_query($query,$db) or die (mysql_error());
		 return mysql_result($result,0,'order_num');
	
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////
	function delete_rows($id_nums_str,$control_num){
	    global $db; 
		global $client_id; 
		global $user_id;
		
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		
		//  2).   получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).   разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
        
		
		//  3)
		$id_nums_arr = explode(';',$id_nums_str);
		
		
		for( $i = 0 ; $i < count($id_nums_arr) ; $i++)
	    {
		
			$row_id = check_changes_to_rt_protocol($control_num,$id_nums_arr[$i]);
			if($row_id == false){
				mysql_query("UNLOCK TABLES") or die(mysql_error());
				return;
			}
			
		    $query = "DELETE FROM `".CALCULATE_TBL."` WHERE `id` = '".$row_id."'";
	        $result = mysql_query($query,$db) or die(mysql_error());
			
			
			$query_reduce = "UPDATE `".CALCULATE_TBL."` SET `id` = `id` - 1  WHERE `id` > '".$row_id."' ORDER BY `id`"; // DESC
		    $result_reduce = mysql_query($query_reduce,$db);
			
			if(!$result_reduce)exit(mysql_error());// здесь еще необходимо отменить предыдущее действие
			else make_note_to_rt_protocol('delete','',$row_id,$user_id,$client_id);
		
		}
		
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
	}
	
	function make_note_to_rt_protocol($type_action,$description,$row_id,$user_id,$client_id){
		global $db;
	    $query = "INSERT INTO `".CALCULATE_TBL_PROTOCOL."` SET `action` = '".$type_action."', `row_id` = '".$row_id."',`description` = '".$description."', `client` = '".$client_id."',`user` = '".$user_id."'";
			
		$result = mysql_query($query,$db);
		if(!$result)exit(mysql_error());
	}
	
	function update_tr_field($id,$field_name,$field_val){
		global $db;
		echo $id.$field_name.$field_val;
		
		$query = "UPDATE `".CALCULATE_TBL."` SET ".$field_name." = '".$field_val."'  WHERE `id` = '".$id."'"; 
		$result = mysql_query($query,$db) or die(mysql_error());
			
	    exit;
	}
	
	function insert_copied_row($id,$control_num){
	    global $db; 
		global $client_id; 
		global $user_id;
		
		if(!isset($_SESSION['copy_row'])){
		    header('Location:?'.addOrReplaceGetOnURL('','insert_copied_row&row_number&control_num'));
	        exit; 
		}
		
		$copied_id = $_SESSION['copy_row']['id'];
		$copied_control_num = $_SESSION['copy_row']['control_num'];	
		
		
		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).        получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).        разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
        $copied_id = check_changes_to_rt_protocol($copied_control_num,$copied_id);
		$row_id = check_changes_to_rt_protocol($control_num,$id);
		if($copied_id == false || $row_id == false ){
		    mysql_query("UNLOCK TABLES") or die(mysql_error());
		    header('Location:?'.addOrReplaceGetOnURL('','insert_copied_row&row_number&control_num'));
	        exit; 
		}
		
		//  3)

	
			
	   $query = "SELECT*FROM `".CALCULATE_TBL."` WHERE `id` = '".$copied_id."'";
	   $result = mysql_query($query,$db) or die(mysql_error());
	   $row = mysql_fetch_assoc($result);
	    
	   $date = ($row['type'] == 'order')? date('Y-m-d H:i:s') : '';
	   $order_num = ($row['type'] == 'order')? define_next_order_num_for_rt() : '';	  
	   
	   $query_enlarge = "UPDATE `".CALCULATE_TBL."` SET `id` = `id` + 1  WHERE `id` >= '".$row_id."' ORDER BY `id` DESC";
	   $result_enlarge = mysql_query($query_enlarge,$db) or die(mysql_error());
			
	   $query = "INSERT INTO `".CALCULATE_TBL."` SET `id` = '".$row_id."', 
	                                           `type` = '".$row['type']."',
									           `client_id` = '".$client_id."',
											   `manager_id` = '".$user_id."',
								               `time_change` = '".$row['time_change']."',
									           `order_num` = '".$order_num."',
											   `date` = '".$date."',
										       `article` = '".$row['article']."',
											   `name` = '".$row['name']."',
									           `comment` = '".$row['comment']."',
				                               `hide_article_marker` = '".$row['hide_article_marker']."',
											   `quantity` = '".$row['quantity']."',
											   `coming_price` = '".$row['coming_price']."',
											   `price` = '".$row['price']."',
											   `discount` = '".$row['discount']."',
											   `supplier` = '".$row['supplier']."',
											   `making_time` = '".$row['making_time']."',
											   `marker_kp` = '".$row['marker_kp']."',
											   `marker_invoice` = '".$row['marker_invoice']."',
											   `marker_summ` = '".$row['marker_summ']."',
											   `marker_hidearticle` = '".$row['marker_hidearticle']."',
											   `marker_rowstatus` = '".$row['marker_rowstatus']."',
											   `marker_new_row` = '' ";
	   
	  
			
	   $result = mysql_query($query,$db);
	   if(!$result)exit(mysql_error()); // здесь еще необходимо отменить предыдущее действие
	   else make_note_to_rt_protocol('insert','copied',$row_id,$user_id,$client_id);
			
			

		
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		
	    header('Location:?'.addOrReplaceGetOnURL('','insert_copied_row&row_number&control_num'));
	    exit; 
	}
	
	function make_com_offer($id_arr,$stock,$order_num/*string*/,$client_manager_id,$control_num){
	    global $db;
		global $client_id;
		global $user_id;
		
		
		
		$order_num = ($order_num != 'false' && $order_num != '')? $order_num : '00000';
		$client_manager_id = ($client_manager_id != 'false' && $client_manager_id != '')? $client_manager_id : false;
		$com_offer_descriptions = array();
		$com_offer_description_length = 80;
		
		$prefix = '../admin/order_manager/';
        
		//echo $order_num.' '.$client_manager_id;

		$cont_face_data_arr = get_client_cont_face_by_id($client_id,$client_manager_id,true);
		$client_data_arr = select_all_client_data($client_id);
		
		//print_r($cont_face_data_arr);
		//exit;
		
		// собираем контент коммерческого предложения
		$file_content = '<div style="width:625px;background-color:#FFFFFF;"><div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><br>В компанию: '.$client_data_arr['comp_full_name'].'<br>Кому: '.$cont_face_data_arr['name'].'<br>Контакты: '.$cont_face_data_arr['phone'].'<br>'.$cont_face_data_arr['email'].'<br><br></div>
		<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center">Коммерческое предложение</div>';
		$file_content .=  '<table width="625"  style="border:#CCCCCC solid 1px; border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top">';
		$tr_td = '<tr><td style="border:#CCCCCC solid 1px;" width="300" valign="middle" align="center">';
		$td_tr = '</td></tr>';
		$td_td = '</td><td style="border:#CCCCCC solid 1px;padding:6px;" width="325" valign="top">';
		
        // этап создания контента меню
		// принцип следующий сортируем id в порядке возрастания, и считываем ряды из таблицы в порядке возрастания, при считывании        // рядов первоначально считывается персонализация относящаяся к артикулу, записываем её данные в массив, когда доходим до        // ряда "article" или ряда "ordinary" проверям были ли созданна переменная содержащая информацию о нанесении если да
		// записываем данные по нанесению которые были считанны до этого в общую строку, предварительно развернув массив
		// с этими данными, затем переменную с данными о нанесении удаляем, если нет добавляем пустую запись
		// собранную сроку записываем в итоговый массив который перед записью в файл разворачиваем
		// если в конце всей обработки были считанны данные по нанесению но строка артикул в итоге не последовала
		// тогда эти данные добавляются в итоговый массив без данных об артикуле, на последнем шаге
		// перед разворотом массива и записью данных в файл
		
		// этап создания файла КП и сохраниения его на диск
		// проверяем существует ли папка данного клиента если нет создаем её
		// если происходит ошибка выводим отчет
		// проверяем существует ли файл с таким названием если сушествует выводим предупреждение
		// создаем и записываем файл если происходит ошибка выводим отчет
		/**/
		natsort($id_arr);
	    $string = $article_string = $ordinary_string = $print_string = $itog_string = '';

	    // схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).        получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).        разблокируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE, ".COM_PRED_LIST_OLD." WRITE , ".LAST_COM_PRED_NUM." WRITE, ".BASE_TBL." READ ") or die(mysql_error());

		$previos_marker_summ_print = '';
		
		foreach($id_arr as $id_row){

			//  2)
       	    $row_id = check_changes_to_rt_protocol($control_num,$id_row);
				if($row_id == false){
		   	    mysql_query("UNLOCK TABLES") or die(mysql_error());
		   	    return;
			}
		
            //  3)
		    $query = "SELECT*FROM `".CALCULATE_TBL."` WHERE `id` = '".$row_id."'";
		    $result = mysql_query($query,$db);
		    if(!$result) echo(mysql_error());
		    $item = mysql_fetch_assoc($result);
	
			
			if($item['type'] == 'article'){
	
				$article_string = $tr_td;
				
				// проверяем наличие изображения
			    $query_dop = "SELECT*FROM `".BASE_TBL."` WHERE `art` = '".$item['article']."'";
			    $result_dop = mysql_query($query_dop,$db);
		        if(!$result_dop) echo(mysql_error());
			    $item_dop = mysql_fetch_assoc($result_dop);
				
			    $id = $item_dop['id'];
		
			    $img_path = '../../img/'.$item_dop['image'].'.jpg';
		        $img_src = checkImgExists($img_path);
				//$img_path = '';
				//$img_src = '../../skins/images/img_design/icon_index_2.jpg';
			                        
		        // меняем размер изображения
			     $size_arr = transform_img_size($img_src,230,300);
				//$size_arr = array(230,300);
				//$size_arr = array(100,100);
			
				
				// вставляем изображение
				$article_string .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width='.$img_src[1].'">'.$td_td;
				
				// количество
				$quantity = $item['quantity'];
				// стоимость
				$price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$summ = $quantity*$price;
				
				
				$article = ($item['marker_hidearticle'] == 'on')? '' :'арт.: <a href="/index.php?page=description&id='.$id.'" target="_blank">'.$item['article'].'</a>';
				
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
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: '.number_format($summ,2,'.',' ').'руб.<br />';
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
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: '.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: '.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</span><br />';
						}
				    }
				} 
				$rows_data[] = $article_string.$ostatok_block.$print_string.$itog_string.$td_tr;
				
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
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: '.number_format($summ,2,'.',' ').'руб.<br /><br />';
				
				
				
				
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
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: '.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: '.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</span><br />';
						}
				    }
				} 
				
				$rows_data[] = $ordinary_string.$print_string.$itog_string.$td_tr;
				
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
				{##}1шт.: '.number_format($print_price,2,'.',' ').'руб. / тираж: '.number_format($print_summ,2,'.',' ').'руб.<br />';
				
                // если предыдущий ряд был отмечен маркером marker_summ_print (объединить расчет нанесения) то объеденяем данные 
				// внося их в созданный ранее эелемент массива, если нет добавляем новый элемент.
				if($previos_marker_summ_print == 'on') $print_rows[(count($print_rows) - 1)] = array($print_rows[(count($print_rows) - 1)][0] + $print_summ, $string.'<br />{##}'.$print_rows[(count($print_rows) - 1)][1]);  
				else $print_rows[] = array($print_summ,$string);  
               
				$string = '';
				$previos_marker_summ_print = $item['marker_summ_print'];
			}
		}
		
		$com_pred_num = get_new_com_offer_num();
		
		$query = "INSERT INTO `".COM_PRED_LIST_OLD."` SET `client_id` = '".$client_id."',
		                                              `com_pred_num` = '".$com_pred_num."',
													  `order_num` = '".$order_num."',
													  `manager` = '". $cont_face_data_arr['name']."',
													  `description` = '".implode(', ',$com_offer_descriptions)."'
													  ";
													  
									
	    mysql_query($query,$db) or die(mysql_error());
	
		//  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		// если в конце были данные по нанесению, но не было строки артикул, записываем их в массив данных
		if(isset($print_rows)){
			$print_rows = array_reverse($print_rows);
			//$print_string = '<b>Лого:</b><br />';
			for( $i = 0 ; $i < count($print_rows); $i++ ){
				$print_string .= '&nbsp;&nbsp;&nbsp;'.($i + 1).'. '. $print_rows[$i][1];
			}
			$rows_data[] = $tr_td.''.$td_td.$article_string.$print_string.$td_tr;
		} 
		
		// записываем все данные в строку предварительно разварнув массив
		$file_content .= implode('',array_reverse($rows_data)).'</td></tr></table>
		   <div style="text-align:right;font-family:verdana;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div></div>';
		
		// этап создания файла КП и сохраниения его на диск
		// проверяем существует ли папка данного клиента если нет создаем её
		// если происходит ошибка выводим отчет
		// проверяем существует ли файл с таким названием если сушествует выводим предупреждение
		// создаем и записываем файл если происходит ошибка выводим отчет
		
		// проверяем есть папка данного клента, если её нет то создаем её
		$dir_name_full = $prefix.'data/com_offers/'.strval(intval($_GET['client_id']));
		//chmod("data/com_offers/", 0755);
		
		if(!file_exists($dir_name_full)){
		    if(!mkdir($dir_name_full, 0700)){
			    echo 'ошибка создания папки клиента (4)'.$dir_name_full;
			    exit;
			}
		}
		else{ 
			if(!is_dir($dir_name_full)){
			    if(!unlink($dir_name_full)){
				    echo 'ошибка удаления одноименного с папкой файла (3)';
				    exit;
				}
				echo 'повторите команду создания КП (3.1)';
				exit;
			}
		}
		
		// записываем файл
		$file_name = $dir_name_full.'/com_pred_'.date('Y_d_m__Gis').'_'.$order_num.'_'.$com_pred_num.'.doc';
		//$file_name = $dir_name_full.'/com_pred_1_1.doc';
		if(file_exists($file_name)){
		    echo 'файл с таким именем уже существует (2)';
		    exit;
		}
		
		$fd = fopen($file_name,'w');
		$write_result = fwrite($fd,$file_content); //\r\n
		fclose($fd);
	
		if($write_result) echo 1;
		else echo 'ошибка создания файла коммерческого предложения (1)';
		//print_r($id_arr);
		exit;
	
	}
	function get_new_com_offer_num(){
		global $db;
		
	
		$query = "SELECT*FROM `".LAST_COM_PRED_NUM."`";
		$result = mysql_query($query,$db) or die(mysql_error());
		$com_pred_num = mysql_result($result,0,'num');
		
		$query = "UPDATE `".LAST_COM_PRED_NUM."` SET num=num+1";
		$result = mysql_query($query,$db) or die(mysql_error());
       
		
		return $com_pred_num;
    }
	function get_com_offer_data($is_new_com_pred_format,$com_pred_num){
		global $db;
		
	    if(!$is_new_com_pred_format){
		     return  array('id' => '','manager' => '','description' => '');
		}
		
			$query = "SELECT*FROM `".COM_PRED_LIST_OLD."` WHERE `com_pred_num`='".$com_pred_num."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			
			return array('id' => mysql_result($result,0,'id'),'manager' => mysql_result($result,0,'manager'),'description' => mysql_result($result,0,'description'));
		
    }

	function get_clients_main_cont_face($id){
	    global $db;
		$query = "SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".$id."' ORDER BY `set_main` DESC, `id`";
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result) > 0 ){
		    return mysql_fetch_assoc($result);
		}
		else  return array('name'=>'name','department'=>'department','phone'=>'phone','email'=>'email');

	}
	
	
	function get_client_cont_face_by_id($client_id,$cont_face_id,$or_main){
	    global $db;
		
		$cont_face_data = false;
		
		$query = "SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `id` = '".$cont_face_id."' AND `client_id` = '".$client_id."'";
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    $cont_face_data = mysql_fetch_assoc($result);
			
		}
		else if($or_main) $cont_face_data = get_clients_main_cont_face($client_id);
	
		return $cont_face_data;

	}
	
	function set_manager_for_order_ajax($id,$manager_id,$control_num){
	    global $db; 
	

		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//  2).   получаем id
		//  3).   производим изменения в таблице CALCULATE_TBL
		//  4).   разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE ") or die(mysql_error());

		//  2)
		$row_id = check_changes_to_rt_protocol($control_num,$id);
	
		//  3)

	   $query = "UPDATE `".CALCULATE_TBL."` SET `client_manager_id` = '".$manager_id."'  WHERE `id` = '".$row_id."'";
	   $result = mysql_query($query,$db) or die(mysql_error());
			
	    //  4)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
	}
	function get_client_requisites_acting_manegement_face($id){
	    global $db;
	//	$query = "SELECT*FROM `".CLIENT_REQUISITES_MANAGEMENT_TBL."` WHERE `requisites_id` = '".$id."' AND `acting` =  '1'";
		$query ="SELECT
`s`.*,
`b`.`position`,
`b`.`position_in_padeg`
FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` AS `s`
INNER JOIN
`".CLIENT_PERSON_REQ_TBL."` AS `b`
ON s.post_id = b.id
WHERE `requisites_id` = '".$id."' AND `acting` =  '1'
";
		
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
		    return array('position' => $item['position'],'position_in_padeg' => $item['position_in_padeg'],'name' => $item['name'],'name_in_padeg' => $item['name_in_padeg'],'basic_doc' => $item['basic_doc']);
		    }
		}
	}
	function fetch_client_agreements_by_type($type,$client_id){
	    global $db;
	    $query = "SELECT*FROM `".GENERATED_AGREEMENTS_TBL."` WHERE client_id='$client_id' AND type='$type'";
		$result = mysql_query($query,$db);
		if($result) return array('results_num' => mysql_num_rows($result),'result' => $result);
		else  return array('results_num' => FALSE ,'result' => mysql_error());
	}
	function fetch_all_client_agreements($client_id){
	    global $db;
	    $query = "SELECT*FROM `".GENERATED_AGREEMENTS_TBL."` WHERE client_id='$client_id' ORDER BY basic DESC, type ASC,our_requisit_id ASC,client_requisit_id  ASC";
		$result = mysql_query($query,$db);
		if($result) return array('results_num' => mysql_num_rows($result),'result' => $result);
		else  return array('results_num' => FALSE ,'result' => mysql_error());
	}
	

	function add_items_for_specification($specification_num,$rows_id_str,$client_id,$agreement_id,$agreement_date,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$date,$short_description,$address,$prepayment){
	    global $db;
		

		// схема:
		//  1).   блокируем таблицу CALCULATE_TBL
		//        блокируем таблицу CALCULATE_TBL_PROTOCOL
		//        блокируем таблицу GENERATED_SPECIFICATIONS_TBL
		//  2).   
		//  3).  разболкируем таблицы CALCULATE_TBL и CALCULATE_TBL_PROTOCOL 
      
		
		
		//  1)
		mysql_query("LOCK TABLES ".CALCULATE_TBL." WRITE, ".CALCULATE_TBL_PROTOCOL." WRITE , ".GENERATED_SPECIFICATIONS_TBL." WRITE ") or die(mysql_error());

		//  2)
		if(!$specification_num){
			$query = "SELECT MAX(specification_num) specification_num FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			
			$specification_num = (mysql_num_rows($result) > 0)? (int)mysql_result($result,0,'specification_num') + 1 : 1 ;
		}
		$date_arr = explode('.',$date);
	    $date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
		
		
		$arr = explode(';',$rows_id_str);
		
		foreach($arr as $id){
		
		    $row_id = check_changes_to_rt_protocol($control_num,$id);
			
			$query = "SELECT * FROM `".CALCULATE_TBL."` WHERE id = '".$row_id."'";
		    $result = mysql_query($query,$db) or die(mysql_error());
		    if(mysql_num_rows($result) > 0){
		         $row = mysql_fetch_assoc($result);
				 //echo print_r($our_firm_acting_manegement_face).'<br>';
				 //echo print_r($client_firm_acting_manegement_face).'<br>';
				 //exit;
				  
				 $price = ($row['discount'] != 0 )? round(($row['price']/100)*(100 + $row['discount']),2) : $row['price'] ;
					
				 $query2 = "INSERT INTO `".GENERATED_SPECIFICATIONS_TBL."` SET 
				              client_id='".$client_id."',
				              agreement_id='".$agreement_id."',
							  our_chief='".$our_firm_acting_manegement_face['name']."',
							  our_chief_in_padeg='".$our_firm_acting_manegement_face['name_in_padeg']."',
							  our_chief_position='".$our_firm_acting_manegement_face['position']."',
							  our_chief_position_in_padeg='".$our_firm_acting_manegement_face['position_in_padeg']."',
							  our_basic_doc='".$our_firm_acting_manegement_face['basic_doc']."',
							  client_chief='".$client_firm_acting_manegement_face['name']."',
							  client_chief_in_padeg='".$client_firm_acting_manegement_face['name_in_padeg']."',
							  client_chief_position='".$client_firm_acting_manegement_face['position']."',
							  client_chief_position_in_padeg='".$client_firm_acting_manegement_face['position_in_padeg']."',
							  client_basic_doc='".$client_firm_acting_manegement_face['basic_doc']."',
							  specification_num='".$specification_num."',
							  short_description='".$short_description."',
							  address='".$address."',
							  prepayment='".$prepayment."',
							  date = '$date',
							  name='".(($row['article']!='')? 'арт.'.$row['article']:'')." ".$row['name']."',
							  makets_delivery_term='5 (пяти)',
							  item_production_term='10 (десять)',
							  quantity='".$row['quantity']."',
							  price='".$price."',
							  summ='".$row['quantity']*$price."'
							  ";
							  
							  
		         $result2 = mysql_query($query2,$db) or die(mysql_error());
		    }
		
		}
		
	    //  3)
		mysql_query("UNLOCK TABLES") or die(mysql_error());
		
		
		// этап создания отдельного файла Спецификации и сохраниения его на диск
		// проверяем существует ли папка данного клиента если нет создаем её
		// если происходит ошибка выводим отчет
		
		// проверяем есть папка данного клента, если её нет то создаем её
		$client_dir_name = 'data/agreements/'.strval($client_id);
		//chmod("data/com_offers/", 0775);
		
		if(!file_exists($client_dir_name)){
		    if(!mkdir($client_dir_name, 0775)){
			    echo 'ошибка создания папки клиента (4)'.$client_dir_name;
			    exit;
			}
		}
		
		// папка обозначающая год (название папки - название года)
		
		$agreement_date = explode('-',$agreement_date);
		$year_dir_name = $client_dir_name.'/'.$agreement_date[0];
		if(!file_exists($year_dir_name)){
		    if(!mkdir($year_dir_name, 0775)){
			    echo 'ошибка создания папки с именем года'.$year_dir_name;
			    exit;
			}
		}
		
		// папка для типа договора
		$type_dir_name = $year_dir_name.'/long_term';
		if(!file_exists($type_dir_name)){
		    if(!mkdir($type_dir_name, 0775)){
			    echo 'ошибка создания папки с именем года'.$type_dir_name;
			    exit;
			}
		}
		
		$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
		$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
		
		// папка для выбранных сторон
		$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
		if(!file_exists($full_dir_name)){
		    if(!mkdir($full_dir_name, 0775)){
			    echo 'ошибка создания папки с именем года'.$full_dir_name;
			    exit;
			}
		}
		
		// папка для выбранных спецификаций
		$full_dir_name = $full_dir_name.'/specifications';
		if(!file_exists($full_dir_name)){
		    if(!mkdir($full_dir_name, 0775)){
			    echo 'ошибка создания папки с именем года'.$full_dir_name;
			    exit;
			}
		}
		
		// записываем файл
		$file_name = $full_dir_name.'/'.$specification_num.'.tpl';
		//$file_name = $dir_name_full.'/com_pred_1_1.doc';
		if(file_exists($file_name)){
		    echo 'файл с таким именем уже существует (2)';
		    exit;
		}

		$origin_file_name = 'agreement/agreements_templates/specification.tpl';
		$fd_origin = fopen($origin_file_name,'r');
		$file_content = fread($fd_origin,filesize($origin_file_name));
		fclose($fd_origin);
		
		$fd = fopen($file_name,'w');
		$write_result = fwrite($fd,$file_content); //\r\n
		fclose($fd);
		
		return $specification_num;

	}
	
	function delete_specification($client_id,$agreement_id,$specification_num){
	    global $db;
		
		$query = "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."' AND specification_num = '".$specification_num."'";
		
		mysql_query($query,$db) or die(mysql_error());
		
		$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
		$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
		$date= fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'date','coll'=>'id','val'=>$agreement_id));
		
		
	    $file_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval($client_id).'/'.substr($date,0,4).'/long_term/'.$our_requisit_id.'_'.$client_requisit_id.'/specifications/'.$specification_num.'.tpl';
		
		if(file_exists($file_name)) unlink($file_name);
		
	}
	function fetch_our_firms_data(){
	    global $db;
	    $query = "SELECT*FROM `".OUR_FIRMS_TBL."`";
		$result = mysql_query($query,$db);
		if($result) return array('results_num' => mysql_num_rows($result),'result' => $result);
		else  return array('results_num' => FALSE ,'result' => mysql_error());
	}
	function fetch_our_certain_firm_data($id){
	    global $db;
	    $query = "SELECT*FROM `".OUR_FIRMS_TBL."` WHERE id='$id'";
		$result = mysql_query($query,$db) or die (mysql_error());
	    if(mysql_num_rows($result)>0) return mysql_fetch_assoc($result);
		else
		{
		    echo  'fetch_our_certain_firm_data() - наша фирма не определена';
			exit;
		}
	}
	
	function fetch_our_comp_full_name_requisites($id){
	    global $db;
		$query = "SELECT comp_full_name FROM `".OUR_FIRMS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		
		return mysql_result($result,0,'comp_full_name');
	}
	
	function fetch_our_requisites_nikename($id){
	    global $db;
		$query = "SELECT company FROM `".OUR_FIRMS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		
		return mysql_result($result,0,'company');
	}
	
	function fetch_all_agreements_data(){
	    global $db;
	    $query = "SELECT*FROM `".OUR_AGREEMENTS_TBL."`";
		$result = mysql_query($query,$db);
		if($result) return array('results_num' => mysql_num_rows($result),'result' => $result);
		else  return array('results_num' => FALSE ,'result' => mysql_error());
	}
	
	function fetch_agreement_type($id){
	    global $db;
	    $query = "SELECT*FROM `".OUR_AGREEMENTS_TBL."` WHERE id ='".$id."'";
		$result = mysql_query($query,$db) or die (mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    return array('type_ru' => mysql_result($result,0,'type_ru'),'type' => mysql_result($result,0,'type'));
		}
		else
		{
		    echo 'тип догорова не определен';
		    exit;
		}
	}
	function fetch_agreement_type_in_ru($type){
	    global $db;
	    $query = "SELECT type_ru FROM `".OUR_AGREEMENTS_TBL."` WHERE type ='".$type."'";
		$result = mysql_query($query,$db) or die (mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    return mysql_result($result,0,'type_ru');
		}
		else
		{
		    echo 'тип догорова не определен';
		    exit;
		}
	}
	
	function fetchOneValFromAgreementTbl($params){
	    global $db;
		
	    $query = "SELECT ".$params['retrieve']." FROM `".OUR_AGREEMENTS_TBL."` WHERE ".$params['coll']." ='".$params['val']."'";
		$result = mysql_query($query,$db) or die (mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    return  mysql_result($result,0,$params['retrieve']);
		}
		else
		{
		    echo 'тип догорова не определен';
		    exit;
		}
	
	}
	
	function fetchOneValFromGeneratedAgreementTbl($params){
	    global $db;
		
	    $query = "SELECT ".$params['retrieve']." FROM `".GENERATED_AGREEMENTS_TBL."` WHERE ".$params['coll']." ='".$params['val']."'";
		$result = mysql_query($query,$db) or die (mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    return  mysql_result($result,0,$params['retrieve']);
		}
	}
	
	function check_agreements_existence($client_id,$type,$date,$our_requisit_id,$client_requisit_id){
	    global $db;
		if($type == 'long_term')
		{
			$date_arr = explode('.',$date);
			$query = "SELECT id FROM `".GENERATED_AGREEMENTS_TBL."` 
					  WHERE client_id='$client_id' AND type='$type' AND 
					  our_requisit_id='$our_requisit_id' AND client_requisit_id='$client_requisit_id' AND LEFT(date,4) = '".$date_arr[2]."'";	  
			$result = mysql_query($query,$db) or die(mysql_error());
			if(mysql_num_rows($result)>0)
			{   
				echo 'лимит на создание долгосрочных договоров: 1 договор в год,<br>
					  договор на '.$date_arr[2].' год  между компаниями:<br>'.fetch_client_requisites_nikename($client_requisit_id).' и '.fetch_our_requisites_nikename($our_requisit_id).' уже создан';///.mysql_result($result,0,'id');
			    echo '<br><br>';
		        echo '<a href="'.$_SERVER['HTTP_REFERER'].'"><< назад</a>';
				exit;
			}
	    }
		
	}
	
	function save_agreement($id,$data_arr){
	    global $db;
		
		$data ='';
		foreach($data_arr as $key => $val){  $data .= '&'.$key.'='.$val;  }
		$data = trim($data,'&');
		
		$query = "UPDATE `".GENERATED_AGREEMENTS_TBL."` SET 
				  data='".$data."' WHERE id='$id'";
	    $result = mysql_query($query,$db) or die(mysql_error());

	}
	
	function delete_agreement($agreement_id){
	    global $db;

		
		$query = "SELECT*FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id='$agreement_id'";
		$result = mysql_query($query,$db) or die (mysql_error());

		$client_id = mysql_result($result,0,'client_id');
		$date = mysql_result($result,0,'date');
		$date = substr($date,0,4);
		$type = mysql_result($result,0,'type');
		$our_requisit_id = mysql_result($result,0,'our_requisit_id');
		$client_requisit_id = mysql_result($result,0,'client_requisit_id');
		$filename = 'agreement.tpl';
        $file_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.$client_id.'/'.$date.'/'.$type.'/'.$our_requisit_id.'_'.$client_requisit_id.'/'.$filename;
		if(file_exists($file_name)) unlink( $file_name );
		$path =  $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.$client_id.'/'.$date.'/'.$type.'/'.$our_requisit_id.'_'.$client_requisit_id;
		$specifications_folder = $path.'/specifications';
		if(file_exists($specifications_folder)){
			$dir = opendir($specifications_folder);
			while(($file = readdir()) !== FALSE)
			{
				if($file != '.' && $file != '..')
				{
				   if(file_exists($specifications_folder.'/'.$file)) unlink($specifications_folder.'/'.$file);			
				}
			}
		}
		// неработает
		//rmdir($specifications_folder);
		//rmdir($path);
	    $query = "DELETE FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id='$agreement_id'";
		mysql_query($query,$db) or die (mysql_error());
		
		$query = "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '$agreement_id'";
		mysql_query($query,$db) or die(mysql_error());
		
	}
	
	function set_agreement_as_basic($client_id,$agreement_id){
	    global $db;
	    $query = "UPDATE `".GENERATED_AGREEMENTS_TBL."` SET 
				  basic='0' WHERE client_id='$client_id'";
	    mysql_query($query,$db) or die(mysql_error());
		
		$query = "UPDATE `".GENERATED_AGREEMENTS_TBL."` SET 
				  basic='1' WHERE id='$agreement_id'";
	    mysql_query($query,$db) or die(mysql_error());
		
	}
	function GETtoINPUT($query,$change=array(0),$ignore=array(0))
	{
	     $data ='';
		 $pairs = explode('&',urldecode($query));
	     foreach($pairs as $pair)
		 {
			 list($prop,$val) = explode('=',$pair);
             if(in_array($prop,$ignore)) continue;
			 if(array_key_exists($prop, $change)) $val = $change[$prop];
			 $data .= '<input type="hidden" name="'.$prop.'" value="'.htmlspecialchars($val).'">'."\r\n";
			
		 }
		 return $data;
		 
	}
	
	function our_firm_acting_manegement_face($id){
	    global $db;
		$query = "SELECT*FROM `".OUR_FIRMS_MANAGEMENT_TBL."` WHERE `requisites_id` = '".$id."' AND `acting` =  '1'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0){
		    return array('position' => mysql_result($result,0,'position'),'position_in_padeg' => mysql_result($result,0,'position_in_padeg'),'name' => mysql_result($result,0,'name'),'name_in_padeg' => mysql_result($result,0,'name_in_padeg'),'basic_doc' => mysql_result($result,0,'basic_doc'));
		}
	}
	
	function our_firm_acting_manegement_face_new($id){
	    global $mysqli;
		$query = "SELECT*FROM `".OUR_FIRMS_MANAGEMENT_TBL."` WHERE `id` = '".$id."'";
	    $result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows>0){
		    $row=$result->fetch_assoc();
		    return array('position' => $row['position'], 'position_in_padeg' => $row['position_in_padeg'], 'name' => $row['name'],'name_in_padeg' => $row['name_in_padeg'], 'basic_doc' => $row['basic_doc']);
		}
	}
	
	function fetch_client_requisites_nikename($id){
	    global $db;
		$query = "SELECT company FROM `".CLIENT_REQUISITES_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		
		return mysql_result($result,0,'company');
	}
	
	function get_client_requisites($id){
	   /* global $db;
		$query = "SELECT*FROM `".CLIENT_REQUISITES_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());*/
		
		return array(); //mysql_fetch_assoc($result);
	}
	function num_word_transfer($number){
		global $num_word_transfer_arr;
		global $num_word_transfer_razriad_arr;
		
		$number = strval($number);
		for($i=0;$i<strlen($number);$i++) $number_arr[$i] = $number[$i]; // замена str_split(PHP5)
		$number_arr = array_reverse($number_arr);
		$counter = 0 ;
		for( $i=0; $i<count($number_arr); $i++){
			$index = $number_arr[$i];
			$number_arr_in_word[]= $num_word_transfer_arr[$counter++][$index].' '.$num_word_transfer_razriad_arr[$i];
			
		}
		return implode(' ',array_reverse($number_arr_in_word));
		
	}
	
	function set_plan(){
	    global $db;
		global $user_id;
		global $form_data;
		extract($form_data);
		
		//echo'<pre>';print_r($form_data); echo'<pre>';
		//exit;
	   
		$date_order = implode('',array_reverse(explode('.',$remind_date)));
		$time_order = (strlen($time_table_date)<5)?  '0'.str_replace('.','',$time_table_date):str_replace('.','',$time_table_date);

		$query = "INSERT INTO `".PLANNER."` SET 
		                                   `write_datetime` = CURRENT_TIMESTAMP(),
										   `exec_datetime` = CAST('".$remind_date." ".str_replace('.',':',$time_table_date).":00' AS DATETIME),
										   `type` = '$plan_type',`status` = 'new',
										   `manager_id` = '$user_id',`client_id` = '$client_id',
										   `cont_face` = '$cont_face', `plan` = '$plan'";
											
	    $result = mysql_query($query,$db) or die(mysql_error());
		header('Location:'.$_SERVER['HTTP_REFERER']);
	}
	
	function edit_plan(){
	    global $db;
		global $form_data;
		extract($form_data);
		 
		
		$date_order = implode('',array_reverse(explode('.',$remind_date)));
		$time_order = (strlen($time_table_date)<5)?  '0'.str_replace('.','',$time_table_date):str_replace('.','',$time_table_date);
		
		$query = "UPDATE `".PLANNER."` SET `write_datetime` = CURRENT_TIMESTAMP(),
										   `exec_datetime` = CAST('".$remind_date." ".str_replace('.',':',$time_table_date).":00' AS DATETIME),
										   `type` = '$plan_type',`cont_face` = '$cont_face', `plan` = '$plan'
										    WHERE `id` = '$id'";
	    $result = mysql_query($query,$db) or die(mysql_error());
		header('Location:'.$_SERVER['HTTP_REFERER']);
	}
	
	function set_plan_status($id,$status){
	    global $db;
		
		$query = "UPDATE `".PLANNER."` SET `status` = '$status' WHERE `id` = '$id'";
	    $result = mysql_query($query,$db) or die(mysql_error());
		
		header('Location:?'.addOrReplaceGetOnURL('','plan_id&set_plan_status'));
	}
	
	function set_result_for_plan($manager_id){
	    global $db;
		global $form_data;
		extract($form_data,EXTR_PREFIX_ALL,"in");

		include_once(ROOT."/libs/php/classes/manager_class.php");
	    $manager = new Manager($manager_id); 
		
		// проверяем пустое ли поле result если нет тогда оформляем как переписку
		$query = "SELECT id FROM `".PLANNER."` WHERE `id` = '".$in_row_id."' AND `result` <> ''";
	    $result = mysql_query($query,$db) or die(mysql_error());
		if($result && mysql_num_rows($result)>0) $in_result = '<div><span class="mini_cap">'.$manager->name.' '.$manager->last_name.'</span><div>'.$in_result.'</div></div>';
		
		
		$status = ($in_event_type == 'встреча')?'on_approval':'done';
		
		$query = "UPDATE `".PLANNER."` SET `close_manager_id` = '".$manager_id."', `result` =   CONCAT(`result`,'".$in_result."'), `status` = '".$status."', `emotion_mark` = '".$in_emotion_mark."' WHERE `id` = '".$in_row_id."'";
		//remind_date
	    $result = mysql_query($query,$db) or die(mysql_error());
		//exit;
		header('Location:?'.$_SERVER['QUERY_STRING']);
	}
	
	function get_clients_ids_for_user($user_id){
	    global $db;
		
		$ids_arr = array();
		// если $user_id==0 то те которые никому не пренадлежат
		if($user_id!=0) $query = "SELECT*FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `manager_id` = '".$user_id."'";
		else $query = "SELECT id AS client_id FROM `".CLIENTS_TBL."` WHERE `id` NOT IN(SELECT client_id FROM `".RELATE_CLIENT_MANAGER_TBL."` )";
		
		$result = mysql_query($query,$db) or die(mysql_error());
		
		if(mysql_num_rows($result)>0){
	       while($item = mysql_fetch_assoc($result)) $ids_arr[] = $item['client_id'];
        }
		return $ids_arr;
	}
	   function get_clients_list_for_user($user_id,$order = array ('id',''),$limit_str = '',$full_data_flag=false){
	    global $db;
		
		$flag_none_clients = FALSE;
		
		if($user_id!=0) $query_prev = "SELECT*FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `manager_id` = '".$user_id."'";
		else $query_prev = "SELECT id AS client_id FROM `".CLIENTS_TBL."` WHERE `id` NOT IN(SELECT client_id FROM `".RELATE_CLIENT_MANAGER_TBL."` )";
		
		$result_prev = mysql_query($query_prev,$db);
		if(!$result_prev) echo(mysql_error());
		if(mysql_num_rows($result_prev)>0){
		// создаем строку содержащую список id клиентов
		$in_string = '';
	    while($item = mysql_fetch_assoc($result_prev)) $in_string .= $item['client_id'].',';
	    $in_string = trim($in_string,",");
		//echo $in_string;
		
		
		// если передан параметр time_change сверяем список клиентов датами изменеий в расчетной таблице
		if($order[0] == 'time_change'){
			if($in_string != ''){
			   $query = "SELECT `client_id` FROM `".CALCULATE_TBL."` WHERE  `manager_id` = '".$user_id."' AND `client_id` IN (".$in_string.") ORDER BY `".$order[0]."`"; //
			   $result = mysql_query($query,$db);
			   $in_string = '';
			   while($item = mysql_fetch_assoc($result)) $id_arr[]= $item['client_id'];
               //print_r($id_arr);
			   krsort($id_arr);
			   reset($id_arr);
			   $id_arr = array_unique($id_arr);
			   if($limit_str != '') $id_arr = array_slice($id_arr, intval(substr($limit_str,strpos($limit_str,'LIMIT')+ 6)),intval(substr($limit_str,strpos($limit_str,',')+2)));
			   // echo '<br>';
			   //print_r($id_arr);
			   if(isset($id_arr)){
			   foreach($id_arr as $id){
				   $query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id`  = '".$id."'"; //
				   $result = mysql_query($query,$db);
				   if(!$result) echo(mysql_error());
				   $item = mysql_fetch_assoc($result);
				   $client_id_arr[] = array('id' => $item['id'],'name' => $item['name'],'company' => $item['company']);
			   }
 
			}
			else $flag_none_clients = TRUE;
			}
		}
		//elseif($order[0] == 'search'){
		  // $query = "SELECT*FROM ".RELATE_CLIENT_MANAGER_TBL." rl INNER JOIN ".CLIENTS_TBL." c ON rl.client_id = c.id  WHERE rl.manager_id = '".$user_id."' AND c.company LIKE '%".cor_data_for_SQL($order[1])."%' ".$limit_str;	
		elseif($order[0] == 'search'){
		   $query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id` IN (".$in_string.")  AND `company` LIKE '%".cor_data_for_SQL($order[1])."%' ORDER BY `company` ".$limit_str;

		    $result = mysql_query($query,$db);
		    if(!$result) echo(mysql_error());
		    if(mysql_num_rows($result)>0){
				 if($full_data_flag) while($item = mysql_fetch_assoc($result)) $client_id_arr[] = array($item['id'],$item['name'],$item['company'],$item['cont_face1'],$item['phone_cont_face1'],$item['email_cont_face1'],$item['dop_info']);
				 else while($item = mysql_fetch_assoc($result)) $client_id_arr[] = array('id' => $item['id'],'name' => $item['name'],'company' => $item['company']);
		    }
		    else $flag_none_clients = TRUE;
		}			
		// производим выборку с помощью оператора IN
		else{ 
			if($in_string != ''){
				if((isset($_GET['show_clients']) && $_GET['show_clients']=="all_my") || $_SESSION['access']['access'] != 5 || !isset($_GET['page'])){
			   		$query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id` IN (".$in_string.") ORDER BY `".$order[0]."` ".$order[1]." ".$limit_str; 
				}else{
					$query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id` IN (".$in_string.") AND`favorite` = 1 ORDER BY `".$order[0]."` ".$order[1]." ".$limit_str; 
				}
			   $result = mysql_query($query,$db);
			   if(!$result) echo(mysql_error());
			   if(mysql_num_rows($result)>0){
					if($full_data_flag) while($item = mysql_fetch_assoc($result)) $client_id_arr[] = array($item['id'],$item['name'],$item['company'],$item['cont_face1'],$item['phone_cont_face1'],$item['email_cont_face1'],$item['dop_info']);
					else while($item = mysql_fetch_assoc($result)) $client_id_arr[] = array('id' => $item['id'],'name' => $item['name'],'company' => $item['company']);
			   }
			   else $flag_none_clients = TRUE;
			}
			else $flag_none_clients = TRUE;
		}
		}
		else $flag_none_clients = TRUE;
		
		if($flag_none_clients) $client_id_arr[] = array('id' => '','name' => 'нет клиентов','company' => 'нет клиентов');
		
		return $client_id_arr;
	}
	function get_client_name($id){
	    global $db;
		$query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `id` = '".$id."'";
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result) > 0 ){
		   $name = mysql_result($result,0,'company');
		}
		else $name = '&nbsp;';
		return $name;	
	}
	function fetch_specifications($client_id,$agreement_id,$group_by = FALSE){
	    global $db;
		
		$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."'";
		
		if($group_by) $query .= " GROUP BY ".$group_by ;
		$query .= " ORDER BY id DESC";
		
		$result = mysql_query($query,$db) or die(mysql_error());
		
		if(mysql_num_rows($result) > 0){
		     return $result;
		}
		else return false;
	
	}
	
	function fetch_specification($client_id,$agreement_id,$specification_num){
	    global $db;
		
		$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."' AND specification_num = '".$specification_num."' ORDER BY id";
		
		$result = mysql_query($query,$db) or die(mysql_error());

		if(mysql_num_rows($result) > 0) return $result;
		else return false;
	
	}
	
	function fetch_specification_common_details($client_id,$agreement_id,$specification_num){
	    global $db;
		
		$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."' AND specification_num = '".$specification_num."' GROUP BY specification_num";
		
		$result = mysql_query($query,$db) or die(mysql_error());
		
		if(mysql_num_rows($result) > 0){
		     return mysql_fetch_assoc($result);
		}
		else return false;
	
	}
	function fetch_specification_num_list_for_agreement($agreement_id){
	    global $db;
		
		$query = "SELECT specification_num FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' GROUP BY  specification_num";
		
		$result = mysql_query($query,$db) or die(mysql_error());
		
		if(mysql_num_rows($result) > 0){
		     while($item = mysql_fetch_assoc($result)){
			     $arr[] = $item['specification_num'];
			 }
		     return $arr;
		}
		else return array();
	
	}
	function agregate_specification_rows($data){
	    global $db;
		
		for($i = 0 ; $i < count($data) ; $i++)
		{
		    $name = ''; $summ = 0;
		    for($j = 0 ; $j < count($data[$i]) ; $j++)
			{
			      $id = $data[$i][$j];
				  $query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."`
				            WHERE id='".$id."'";
				  $result = mysql_query($query,$db) or die(mysql_error());
				  
				  $name .= mysql_result($result,0,'name').'<br>';
				  if(!isset($quantity)) $quantity = (int)mysql_result($result,0,'quantity');
				  $summ += (float)mysql_result($result,0,'summ');
				  
				  if($j > 0)
				  {
					  $query = "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."`
								WHERE id='".$id."'";
					  $result = mysql_query($query,$db) or die(mysql_error());
				  }
			
			}
			
			$query = "UPDATE `".GENERATED_SPECIFICATIONS_TBL."`
			          SET 
					  name='".$name."',  
					  price='".$summ/$quantity."', 
					  summ='".$summ."'
				      WHERE id='".$data[$i][0]."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			
			unset($quantity);
		
		}
		    
	}
	
	function update_specification($row_id,$field_name,$field_val){
	    global $db;
		
		$query = "UPDATE `".GENERATED_SPECIFICATIONS_TBL."` SET 
				  ".$field_name." ='".$field_val."'
				  WHERE id='".$row_id."'";
						  
		mysql_query($query,$db) or die(mysql_error());
		    
	}
	
	function set_new_num_for_specification($path,$client_id,$agreement_id,$specification_num,$new_specification_num){
	    global $db;

		$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."`
				            WHERE client_id='".$client_id."' AND agreement_id='".$agreement_id."' AND specification_num='".$new_specification_num."'";
		$result = mysql_query($query,$db) or die(mysql_error());
		if(mysql_num_rows($result)>0) return 2;

		$query = "UPDATE `".GENERATED_SPECIFICATIONS_TBL."` SET 
				   specification_num='".$new_specification_num."'
				  WHERE client_id='".$client_id."' AND agreement_id='".$agreement_id."' AND  specification_num='".$specification_num."'";
						  
		mysql_query($query,$db) or die(mysql_error());
		
		$path = str_replace('-','/',$path);
		rename($path.'specifications/'.$specification_num.'.tpl',$path.'specifications/'.$new_specification_num.'.tpl');
		    
	}
	
	function update_specification_common_fields($row_id,$field_name,$field_val){
	    global $db;
		$query = "SELECT client_id,agreement_id,specification_num FROM `".GENERATED_SPECIFICATIONS_TBL."`
				  WHERE id='".$row_id."'";
		$result = mysql_query($query,$db) or die(mysql_error());
		$specification_num = mysql_result($result,0,'specification_num');
		$client_id = mysql_result($result,0,'client_id');
		$agreement_id = mysql_result($result,0,'agreement_id');
		
		$query = "UPDATE `".GENERATED_SPECIFICATIONS_TBL."` SET 
				  ".$field_name." ='".$field_val."'
				  WHERE specification_num='".$specification_num."' AND client_id='".$client_id."' AND agreement_id='".$agreement_id."'";
						  
		mysql_query($query,$db) or die(mysql_error());
		    
	}
	
	function update_agreement_finally_sheet($row_id,$field_name,$field_val){
	    global $db;
		
		$query = "UPDATE `".GENERATED_AGREEMENTS_TBL."` SET 
				  ".$field_name." ='".$field_val."'
				  WHERE id='".$row_id."'";
						  
		mysql_query($query,$db) or die(mysql_error());
		    
	}
	function set_discount(){
	   
		function make_it($id){
		    global $mysqli;
			global $form_data;
			unset($form_data['id']);
			extract($form_data);
			 
			$query = "SELECT discount, price_out FROM `".RT_DOP_DATA."`  WHERE `id` = '$id'";
			$result = $mysqli->query($query)or die($mysqli->error);
			//if($result->num_rows>0)
			$row = $result->fetch_assoc();
			
			if(isset($drop_discont) && $drop_discont == 'on'){
				$query = "UPDATE `".RT_DOP_DATA."` SET `discount` = '0' WHERE `id` = '$id'";
				$result = $mysqli->query($query)or die($mysqli->error);
				
				$query = "UPDATE `".RT_DOP_USLUGI."` SET `discount` = '0' WHERE `dop_row_id` = '$id'";
				$result = $mysqli->query($query)or die($mysqli->error);
			}
			/*else if(isset($new_price) && floatval($new_price) != 0){
				$percentage = $new_price*100/$row['price_out'];
				$discount = $percentage - 100; 
				
				$query = "UPDATE `".RT_DOP_DATA."` SET `discount` = '$discount' WHERE `id` = '$id'";
				$result = $mysqli->query($query)or die($mysqli->error);
			}*/
			else if(isset($type_action) && isset($percent) && floatval($percent) != 0){
				$discount = ($type_action == 'discount')? -$percent : $percent ;
				
				$query = "UPDATE `".RT_DOP_DATA."` SET `discount` = '$discount' WHERE `id` = '$id'";
				$result = $mysqli->query($query)or die($mysqli->error);
				
				$query = "UPDATE `".RT_DOP_USLUGI."` SET `discount` = '$discount' WHERE `dop_row_id` = '$id'";
				$result = $mysqli->query($query)or die($mysqli->error);
			}
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		
		global $mysqli;
		global $form_data;
		global $query_num;
		// print_r($form_data);
		// exit;
		
		extract($form_data);
		
		if(isset($which_rows) && $which_rows == 'one_row') make_it($id);
		if(isset($which_rows) && $which_rows == 'all_in_pos'){
			    
				// узнаем id позиций
			    $query = "SELECT id FROM `".RT_DOP_DATA."`  WHERE `row_id` = (SELECT row_id FROM `".RT_DOP_DATA."`  WHERE `id` = '$id')";
	            $result = $mysqli->query($query)or die($mysqli->error);
				while($row = $result->fetch_assoc()){
			        make_it($row['id']);
				}
		}
		if(isset($which_rows) && $which_rows == 'all_in_query'){
			
			    // узнаем номер заявки и id входящих в него позиций 
				$query = "SELECT id FROM `".RT_DOP_DATA."`  WHERE `row_id` IN (SELECT id FROM `".RT_MAIN_ROWS."` WHERE query_num = '$query_num')";
			    $result = $mysqli->query($query)or die($mysqli->error);
				while($row = $result->fetch_assoc()){
				    //echo $row['id'];
			        make_it($row['id']);
				}
		}
	}
	
/*	function get_client_requisites_acting_manegement_face($id){
	    global $db;
	//	$query = "SELECT*FROM `".CLIENT_REQUISITES_MANAGEMENT_TBL."` WHERE `requisites_id` = '".$id."' AND `acting` =  '1'";
		$query ="SELECT
		`s`.*,
		`b`.`position`,
		`b`.`position_in_padeg`
		FROM `".CLIENT_PERSON_REQ_TBL."` AS `s`
		INNER JOIN
		`".CLIENT_CONT_FACES_POST_TBL."` AS `b`
		ON s.post_id = b.id
		WHERE `requisites_id` = '".$id."' AND `acting` =  '1'
		";
		
	    $result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
		    return array('position' => $item['position'],'position_in_padeg' => $item['position_in_padeg'],'name' => $item['name'],'name_in_padeg' => $item['name_in_padeg'],'basic_doc' => $item['basic_doc']);
		    }
		}
	}
	*/


	function get_real_user_access($id){
		global $mysqli;
	   
	    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
	    $result = $mysqli->query($query)or die($mysqli->error);
	    if($result->num_rows>0){
			while($row = $result->fetch_assoc()){
			   return  $row['access'];
			}				
	    }
	    else{
	        return 0;
	    }
	}



	// возвращает ссылку на кабинет Html
	function get_worked_link_for_cabinet(){
		global $ACCESS_SHABLON;
			
		$user_id = get_real_user_access($_SESSION['access']['user_id']);

		echo $user_id;
		// echo $user_access;

		if( !isset($ACCESS_SHABLON[ $user_id ]['cabinet']['section'] ) ){
			return; 
		}else{
			// первый ключ section
			$n = 0;
			foreach ($ACCESS_SHABLON[$user_id]['cabinet']['section'] as $key => $value) {
				if ($n == 0) {
					$section = $key;
				}
				$n++;
			}

			// первый ключ section
			$n = 0;
			foreach ($ACCESS_SHABLON[$user_id]['cabinet']['section'][$section]['subsection'] as $key => $value) {
				if ($n == 0) {
					$subsection = $key;
				}
				$n++;
			}
			 
			//$subsection = key($ACCESS_SHABLON[$user_id]['cabinet']['section'][0]['subsection'][0]);
			return '<a href="?page=cabinet&section='.$section.'&subsection='.$subsection.'" class="'.((isset($_GET['page']) && $_GET['page'] =='cabinet')?'selected':'').'">Запросы</a>';
		}
	}

	// возвращает ссылку на кабинет
	function get_worked_link_href_for_cabinet(){
		global $ACCESS_SHABLON;

		$client = '';
		if(isset($_GET['client_id'])){
			$client = '&client_id='.$_GET['client_id'];
		}
			
		$user_id = get_real_user_access($_SESSION['access']['user_id']);

		// echo $user_access;

		if( !isset($ACCESS_SHABLON[ $user_id ]['cabinet']['section'] ) ){
			return; 
		}else{
			// первый ключ section
			$n = 0;
			foreach ($ACCESS_SHABLON[$user_id]['cabinet']['section'] as $key => $value) {
				if ($n == 0) {
					$section = $key;
				}
				$n++;
			}

			// первый ключ section
			$n = 0;
			foreach ($ACCESS_SHABLON[$user_id]['cabinet']['section'][$section]['subsection'] as $key => $value) {
				if ($n == 0) {
					$subsection = $key;
				}
				$n++;
			}

			// сказано сделать стартовой
			// if($_SESSION['access']['access'] == 5 || $_SESSION['access']['access'] == 1 ){
			// 	$subsection = 'in_work';
			// }
			
			 
			//$subsection = key($ACCESS_SHABLON[$user_access]['cabinet']['section'][0]['subsection'][0]);
			return 'os/?page=cabinet&section='.$section.'&subsection='.$subsection.$client;
		}
	}
?>
