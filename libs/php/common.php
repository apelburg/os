<?php
   
    if(isset($_GET['add_data_to_rt_from_basket'])){
		 include_once ROOT.'/libs/php/classes/rt_class.php';
		 //echo $_GET['client_data'].' - '.$_GET['manager_login'];
		 RT::add_data_from_basket($_GET['client_data'],$_GET['manager_login']);
		 exit;
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
	    
		// 
		return implode('&',$itog_pairs);
    }
	
	function cor_data_for_SQL($data){
	    if(is_int($data) || is_double($data)) return($data);
	    //return strtr($data,"1","2");
		$data = strip_tags($data,'<b><br><a>');
		return mysql_real_escape_string($data);
	}
	
	function identify_supplier_by_prefix($article){// 
	   global $suppliers_data_by_prefix;					   
	   $prefix = substr($article,0,2);
	   
	   if(isset($suppliers_data_by_prefix[$prefix])){
	      $article_orig_name = substr($article,2);
	      return '<a target="_blank" class="rt_supplier_link" href="'.$suppliers_data_by_prefix[$prefix]['link'].$article_orig_name.'">'.$suppliers_data_by_prefix[$prefix]['name'].'</a>';
		  
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
     	list($img_width, $img_height, $type, $attr) = (file_exists($img))? getimagesize($img): array($limit_width,$limit_height,'',''); 
		$limit_relate = $limit_height/$limit_width;
		$img_relate = $img_height/$img_width;
		if($limit_relate < $img_relate) $limit_width = $limit_height/$img_relate; 
		else $limit_height = $limit_width*$img_relate;
		return array($limit_height,$limit_width); 
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
						foreach($filter_arr as $filter){
							if($val >= $filter && $val < $filter+1) $ids[] = $key;
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
	
	
	function get_expanded_data_for_client_list($ids){
	    global $db;
		
		$ids_string = implode(',',$ids);
		//echo $ids_string;
		
		$query = "SELECT  relate_tbl.client_id client_id, mngs_tbl.name name, mngs_tbl.last_name last_name FROM `".RELATE_CLIENT_MANAGER_TBL."` relate_tbl 
		          INNER JOIN `".MANAGERS_TBL."` mngs_tbl
				  ON relate_tbl.manager_id = mngs_tbl.id
				  WHERE relate_tbl.client_id IN (".$ids_string.")";
		$result = mysql_query($query,$db);
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			    $arr[$item['client_id']]['curators'][] =  $item['name'].' '.$item['last_name'];
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
		
	    $query = "SELECT*FROM`".SUPPLIERS_ACTIVITIES_TBL."`";
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
	    global $mysqli;
	    //global $db;
		$query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}else{exit('нет пользователя с таким id');}
		return $arr;
	    //$result = mysql_query($query,$db) or die(mysql_error());
		//if(mysql_num_rows($result)==0) exit('нет пользователя с таким id');
		//return $result;	
	}
	
	function get_managers_list(){
	    //global $db;
		global $mysqli;
		$query = "SELECT*FROM `".MANAGERS_TBL."`";
	    $result = $mysqli->query($query) or die($mysqli->error);
	    $manager_arr = array();
	    if($result->num_rows > 0){
			while($item = $result->fetch_assoc()){
				$manager_arr[] = array('id' => $item['id'],'name' => $item['name'],'last_name' => $item['last_name'],'email_2' => $item['email_2']);
			}
		}else{$manager_arr[] = 'none';}
		return $manager_arr;

	 //    $result = mysql_query($query,$db);
		// if(mysql_num_rows($result)>0) 
		// 	while($item = mysql_fetch_assoc($result)){
		//      $manager_arr[] = array('id' => $item['id'],'name' => $item['name'],'last_name' => $item['last_name'],'email_2' => $item['email_2']);
		// 	}
		// else $manager_arr[] = 'none';
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
		
		$query = "SELECT*FROM `".BASE_TBL."` WHERE `art` = '".trim($art)."'";
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
	
	function get_client_cont_faces_ajax($client_id){
	    global $db;
		
		$cont_faces_arr = array();
		
		$query = "SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".$client_id."'";
		$result = mysql_query($query,$db);
		if(!$result) echo(mysql_error());
		if(mysql_num_rows($result)>0)
		{
		    while($item = mysql_fetch_assoc($result)) $cont_faces_arr[] = $item['id'].'{;}'.$item['name'];				
		}
		
	
		return implode('{@}',$cont_faces_arr);

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
	
	
?>
