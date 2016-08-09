static function cont_face_communications($client_id,$options = FALSE){
		global $mysqli;
	
		$tbls_constant_names = array('CLIENT_CONT_FACES_TBL','CLIENT_REQUISITES_MANAGMENT_FACES_TBL');
		if(!empty($options['tbls'])) {
		   $tbls_constant_names = (is_array($options['tbls']))? $options['tbls'] :array($options['tbls']);
		}
		foreach($tbls_constant_names as $tbl_name){
		    if($tbl_name == 'CLIENT_REQUISITES_MANAGMENT_FACES_TBL'){
			    $query_arr[] = "(SELECT id, position, name, 'CLIENT_REQUISITES_TBL' AS tbl_name FROM `".constant($tbl_name)."` WHERE requisites_id IN (SELECT id FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".$client_id."')) ";
			}
		    else $query_arr[] = "(SELECT id, position, name, '".$tbl_name."' AS tbl_name FROM `".constant($tbl_name)."` WHERE `client_id` = '".$client_id."')";
		}
		// print_r($query_arr);
		$query = implode(' UNION ', $query_arr);
		unset($query_arr);
		//echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		while($row = $result->fetch_assoc()){
		    //print_r($row);
			//echo '<br>';
			$data_arr[$row['tbl_name']][$row['id']] = $row;
		}
		 echo '<pre>';print_r($data_arr);echo '</pre>';
        //// 
		// 
		// 
		foreach($data_arr as $tbl_name => $data){
		     // echo '<pre>';print_r($data);echo '</pre>';
		     foreach($data as $val) $ids_arr[] = $val['id'];
			 //print_r($ids_arr);
		     $query_arr[] = "(SELECT*FROM `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl_name."' AND `parent_id` IN('".implode("','",$ids_arr)."'))";
			 unset($ids_arr);
		}
		$query = implode(' UNION ALL', $query_arr);
		echo $query;//
		$result = $mysqli->query($query) or die($mysqli->error);
		while($row = $result->fetch_assoc()){
		    $communications[$row['table']][$row['parent_id']][$row['type']][] = $row['contact'];
			//echo '<br>';////
		} 

		echo '<pre>';print_r($communications);echo '</pre>';
		foreach($data_arr as $tbl_name => $data){
		     // echo '<pre>';print_r($data);echo '</pre>';
		     foreach($data as $val){
			    $ids_arr[] = $val['id'];
			
				 $out_arr[] = array_merge($val,$communications[$tbl_name][$val['id']]);
			 }
		}
		echo '<pre>';print_r($out_arr);echo '</pre>';
	}