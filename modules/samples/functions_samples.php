<?php
///////////////////////////////////////////////////////////////
	//////////                Образцы               //////////////
	//////////////////////////////////////////////////////////////

function change_stage_samples($td,$value,$StrArr){ 
	$db= mysql_connect ("localhost","php_3477686","3477686");
	//$db= mysql_connect ("localhost","php_3477686","3477686");
	if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	mysql_query('SET NAMES utf8');          
	mysql_query('SET CHARACTER SET utf8');  
	mysql_query('SET COLLATION_CONNECTION="utf8_general_ci"');
	$query = "UPDATE `samples` SET `".$td."` = '".$value."' WHERE `samples`.`id` IN (
	";
	$i=1;
	foreach ($StrArr as $key => $value){
		if($key>0){
			if($i==1){
				$query.= $value." ";
			}else{
				$query.= ",".$value." ";
			}
			$i++;
		}
	}
	$query.=")";
	//echo $query;
	$result = mysql_query($query,$db) or die(mysql_error());
	
}



if(isset($_POST['change_note'])){//редактируем заметки с AJAX
	$db= mysql_connect ("localhost","php_3477686","3477686");
	$db= mysql_connect ("localhost","php_3477686","3477686");
	//if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	mysql_query('SET NAMES utf8');          
	mysql_query('SET CHARACTER SET utf8');  
	mysql_query('SET COLLATION_CONNECTION="utf8_general_ci"');
	
	$text = $_POST['text'];
	$StrArr = explode("c_",$_POST['change_note']); 
	
	change_stage_samples('note',$text,$StrArr);
	//sleep(2);//задержка для тестирования
	echo 'okey';	
}

if(isset($_POST['button'])){
	if($_POST['button']=='received_1'){//быстрая доставка образцов   " офис -> клиент "
			
	}else if($_POST['button']=='received_2'){//продление сроков жизни образцов		
		$StrArr = explode("c_",$_POST['position']);
		$td = $_POST['td'];
		//$text = $_POST['value'];//отформатированная дата
		
		$access_date = $_POST['value'];		 
		$date_elements  = explode(".",$access_date);
		$text = $date_elements[2].'-'.$date_elements[1].'-'.$date_elements[0];
		change_stage_samples($td,$text,$StrArr);
		echo 'okey';
	}else if($_POST['button']=='received_3' || $_POST['button']=='received_4' || $_POST['button']=='received_5' || $_POST['button']=='button_1' || $_POST['button']=='button_2'){//возврат образцов   " клиент -> офис "
		$StrArr = explode("c_",$_POST['position']); 
		$td = $_POST['td'];
		$text  = $_POST['value'];		
		change_stage_samples($td,$text,$StrArr);
		//sleep(2);//задержка для тестирования
		echo 'okey';
	}
}
if(isset($_POST['send_driver_form']) && $_POST['send_driver_form']=='yes'){//запрос из формы доставки образцов
	$db= mysql_connect ("localhost","php_3477686","3477686");
	$db= mysql_connect ("localhost","php_3477686","3477686");
	//if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	mysql_query('SET NAMES utf8');          
	mysql_query('SET CHARACTER SET utf8');  
	mysql_query('SET COLLATION_CONNECTION="utf8_general_ci"');

	$StrArr = explode("c_",$_POST['id_samples']); 
	$supplier_arr = explode("c_",$_POST['input_supplier']);
	$client_arr = explode("c_",$_POST['input_client']); 
	$samples_already_collected = $_POST['samples_already_collected'];
	$post_adress = $_POST['post_adress'];//адрес доставки
	//echo $_POST['post_adress'];
	$date_of_receipt = $_POST['date_of_receipt'];
	$dateParts=explode('.', $date_of_receipt);
	$date_of_receipt="{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";//дата поездки
	/*от данной даты высчитываем число возврата образца*/
	
	
	$time_of_receipt = $_POST['time_of_receipt'];

	
	foreach ($StrArr as $key => $value){
		if($key>0){
			$query = "UPDATE `samples` SET `stage` = '".$_POST['stage']."', `samples_already_collected` = '".$samples_already_collected."',`under_pledge_supplier` = '".$supplier_arr[$key]."',`under_pledge_client` = '".$client_arr[$key]."', `date_confirm_supplier` = '".date('Y-m-d')."', `date_of_receipt` = '".$date_of_receipt."', `time_of_receipt` = '".$time_of_receipt."' WHERE `samples`.`id` = '".$value."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			//echo $query.'<br/>';
		}
	}
	echo 'OK';
	
	
	
	/*точка входа*/
	function great_drive_row_in_MC(){
		/*
		функция создания строки в КК(дописать при готовности карты курьера, все переменные в этой функции )
		
		*/
	}
}

if(isset($_POST['nearest_travel_date']) && $_POST['nearest_travel_date']=='yes'){
	$supplier_id = $_POST['nearest_travel_date'];//id поставщика
	/*
	здесь пишем запрос в базу КК, при наличии запланированных и несовершенных поездок к поставщику выводим ближайшую дату, 
	в противном случае отпраляем прочерк "-----"
	*/
	echo '-----';
	}

function red_date($date3){ //проверка и индикация просроченной даты продления жизни образца
	if(isset($date3) && $date3!=''){
	$ts1 = time();          
	$ts2 = strtotime($date3); 
	if(($ts2-$ts1)<= (60 * 60 * 24 * 2)){
	  $date_elements  = explode("-",$date3);
	  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
	  return '<span style="color:red;font-weight: bold;">'.$text.'</span>';
	}else{
	  $date_elements  = explode("-",$date3);
	  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
	  return '<span>'.$text.'</span>';
	}
	}else{
		return '<img src="skins/images/img_design/reset_btn_minus.png">';
	}
}
function deposit_red_date($date,$date2){	
	if(isset($date2) && $date2!=''){
		$ts1 = time();          
		$ts2 = strtotime($date); 
		if(($ts2-$ts1)<= (60 * 60 * 24 * 2)){
		  $date_elements  = explode("-",$date);
		  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
		  return '<span style="color:grey;font-weight: bold;">'.$text.'</span>';
		}else{
		  $date_elements  = explode("-",$date);
		  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
		  return '<span style="color:grey;">'.$text.'</span>';
		}
	}else{
		$ts1 = time();          
		$ts2 = strtotime($date); 
		if(($ts2-$ts1)<= (60 * 60 * 24 * 2)){
		  $date_elements  = explode("-",$date);
		  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
		  return '<span style="color:red;font-weight: bold;">'.$text.'</span>';
		}else{
		  $date_elements  = explode("-",$date);
		  $text = $date_elements[2].'.'.$date_elements[1].'.'.$date_elements[0];
		  return '<span>'.$text.'</span>';
		}
	}
}

function add_row_sample_table($client_id,$tovar_id,$supplier_id,$manager_id){//заведение образца
	$date_added = date("Y-m-d");
	
	$db= mysql_connect ("localhost","php_3477686","3477686");
	$db= mysql_connect ("localhost","php_3477686","3477686");
	//if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	mysql_query('SET NAMES utf8');          
	mysql_query('SET CHARACTER SET utf8');  
	mysql_query('SET COLLATION_CONNECTION="utf8_general_ci"');
	
	$query= "INSERT INTO `samples` SET  `stage`='0' ,`client_id`='".$client_id."',`tovar_id`='".$tovar_id.",`supplier_id`='".$supplier_id.",`manager_id`='".$manager_id." ,`date_added_id`='".$date_added_id."";
	$result = mysql_query($query,$db) or die(mysql_error());
}

?>