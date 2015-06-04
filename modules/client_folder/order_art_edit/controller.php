<?php
	
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['global_change'])){
		if(isset($_POST['change_name']) && $_POST['change_name']=='size_in_var'){

			$query = "SELECT `tirage_json`,`print_z` FROM ".RT_DOP_DATA." WHERE `id` = '".$_POST['id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$json = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$json = $row['tirage_json'];
					$print_z = $row['print_z'];
				}
			}
			$arr_json = json_decode($json,true);

			$arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];

			/*
				ОБСУДИТЬ С АНДРЕЕМ РАСПРЕДЕЛЕНИЕ ТИРАЖА 
				ВВЕДЁННОГО В ОБЩЕЕ поле
			*/
			// $quantity = 0;
			// foreach ($arr_json as $key => $value) {
			// 	$quantity += $arr_json[$key]['tir'];
			// 	if($print_z){$quantity += $arr_json[$key]['dop'];}
			// }


			$query = "UPDATE `".RT_DOP_DATA."` SET `tirage_json` = '".json_encode($arr_json)."', `quantity` = '".$quantity."' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='size_in_var_all'){
			// echo "<pre>";
			// print_r($_POST);
			// echo "</pre>";

			$tir = $_POST['val']; // array / тиражи
			$key2 = $_POST['key']; // array / id _ row size
			$dop = $_POST['dop']; // array / запас
			$id = $_POST['id']; // array / id 

			//print_r($_POST['id']);exit;

			$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$id[0]."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$json = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$json = $row['tirage_json'];
					//echo $row['tirage_json'];
				}
			}
			//echo $json;
			//$r = $json;
			$arr_json = json_decode($json,true);

			foreach ($key2 as $key => $value) {
				//echo $value;
				$arr_json[$value]['dop'] = $dop[$key];
				$arr_json[$value]['tir'] = $tir[$key];
			}

			// $arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];
			//echo $r .'   -   ';
			//echo json_encode($arr_json);
			$query = "UPDATE `".RT_DOP_DATA."` SET `tirage_json` = '".json_encode($arr_json)."' WHERE  `id` ='".$id[0]."'";	
			// // echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if(isset($_POST['change_name']) && $_POST['change_name']=='change_status_row'){
			$color = $_POST['color'];
			$id_in = $_POST['id_in'];
			$query  = "UPDATE `".RT_DOP_DATA."` SET `row_status` = '".$color."' WHERE  `id` IN (".$id_in.");";
			echo $query;
			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"1","text":"test"}';
			exit;
		}
		// if(isset($_POST['change_name']) && $_POST['change_name']=='change_draft'){
		// 	$query  = "UPDATE `".RT_DOP_DATA."` SET `archiv` = '1' WHERE  `row_id` ='".$_POST['row_id']."' AND `id` NOT LIKE  '".$_POST['id']."';";
		// 	$query .= "UPDATE `".RT_DOP_DATA."` SET `draft` = '0' WHERE  `id` ='".$_POST['id']."';";
		// 	$result = $mysqli->multi_query($query) or die($mysqli->error);
		// 	echo '{"response":"1","text":"test"}';
		// 	exit;
		// }

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_archiv'){
			echo 'не используется... или переделать';
			// $query  = "UPDATE `".RT_DOP_DATA."` SET `draft` = '1' WHERE  `row_id` ='".$_POST['row_id']."' AND `id` NOT LIKE  '".$_POST['id']."';";
			// // $query  = "UPDATE `".RT_DOP_DATA."` SET `archiv` = '1' WHERE  `row_id` ='".$_POST['row_id']."' AND `id` NOT LIKE  '".$_POST['id']."';";
			// $query .= "UPDATE `".RT_DOP_DATA."` SET `draft` = '1', `row_status` = 'green' WHERE  `id` ='".$_POST['id']."';";
			// $result = $mysqli->multi_query($query) or die($mysqli->error);
			// // $result = $mysqli->query($query) or die($mysqli->error);
			// echo '{"response":"1","text":"test"}';
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_tirage_pz'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `print_z` = '".$_POST['pz']."' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_variante_shipping_time'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_time` = '".$_POST['time']."', `standart` = '' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_variante_shipping_date'){
			$date = $_POST['date'];
			$date = strtotime($date);
			$date = date("Y-m-d", $date);
			// exit;

			$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_date` = '".$date."' , `standart` = '' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='save_standart_day'){
			$query = "UPDATE `".RT_DOP_DATA."` 
					SET `shipping_time` = '00:00:00',
					`shipping_date` = '0000-00-00' ,
					`standart` =  '".$_POST['standart']."'
					WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='new_variant'){
			// собираем запрос, копируем строку в БД
			$query = "INSERT INTO `".RT_DOP_DATA."` (row_id, row_status,quantity,price_in, price_out,discount,tirage_json) (SELECT row_id, row_status,quantity,price_in, price_out,discount,tirage_json FROM `".RT_DOP_DATA."` WHERE id = '".$_POST['id']."')";
			$result = $mysqli->query($query) or die($mysqli->error);
			// запоминаем новый id
			$insert_id = $mysqli->insert_id;
			// узнаем количество строк
			$query = "SELECT COUNT( * ) AS `num`
					FROM  `os__rt_dop_data` 
					WHERE  `row_id` ='1'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$num_rows = $row['num'];
				}
			}
			echo '{ "response":"1",
					"text":"test",
					"new_id":"'.$insert_id.'",
					"num_row":"'.($num_rows-1).'",
					"num_row_for_name":"Вариант '.$num_rows.'"
					}';
			exit;
		}
	}



	/*******************************  END AJAX  *********************************/


	//10147
	// $art_id =  (isset($_GET['art_id']))?$_GET['art_id']:0;
	// $art_id = 32285;

	$id = (isset($_GET['id']))?$_GET['id']:'none';


	
	// чеерез get параметр id мы получаем id 1 из строк запроса
	// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
	$query = "SELECT DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`, `query_num`, `name`,`id`, `art_id`, `type` FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			// id записи 
			$order_num_id = $row['id']; 
			
			// номер запроса
			$order_num = $row['query_num']; 
			
			// название артикула, возможно отредактированное менеджером
			$art_name = $row['name']; 
			
			// id строки артикула в базе
			$art_id = $row['art_id']; 
			
			// type тип продукции
			$type_poduct = $row['type']; 

			// дата создания строки
			$order_num_date = $row['date_create']; 
			
			// тип товара : 
			// каталог /cat, 
			// полиграфия /pol, 
			// сувениры под заказ /ext, 
			// нанесение на чужом сувенире /не определено
			$type_tovar = $row['type']; 
		}
	}

	// получаем все варианты просчёта по данному артикулу
	//$query = "SELECT `".RT_DOP_DATA."`.*,`".RT_ART_SIZE."`.`tirage_json`,`".RT_ART_SIZE."`.`id` AS `id_2` FROM `".RT_DOP_DATA."` INNER JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`variant_id` = `".RT_DOP_DATA."`.`id` WHERE `".RT_DOP_DATA."`.`row_id` = '".$id."'";
	$query = "SELECT `".RT_DOP_DATA."`.*, DATE_FORMAT(shipping_date,'%d.%m.%Y') AS `shipping_date` FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
	
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$variants[] = $row;
		}
	}
if(!isset($type_poduct)){echo "Тип товара не определён,<br>или строка с id=".$_GET['id']." в таблице `".RT_DOP_DATA."` не существует ";exit;}
if(isset($type_poduct) && $type_poduct!='cat'){ 
	echo 'Товар не относится к категории католожной продукции.';exit;
}else{

	$ARTICUL = new Articul($art_id);
	// основная информация по артикулу
	$articul = $ARTICUL->info;
	// акртикул
	$art = $articul['art'];
	//цвет
	$art_colors = implode(",", $ARTICUL->color);
	// материал
	$art_materials = implode(",", $ARTICUL->material);
	// вид печати
	$art_get_print_mode = implode(",", $ARTICUL->get_print_mode);
	// получаем изображения артикула
	$images_data = $ARTICUL->fetch_images_for_article2($art_id,'1');
	// получаем дополнительные параметры: размер, цену ...
	$art_dop_params = $ARTICUL->get_dop_params($art_id);
	
	

	// итак


	// // echo $art_colors;
	// echo '<pre>';
	// print_r($variants);
	// echo '</pre>';

	// вычисление вариантов данного артикула с другими цветами
	if($color_variants = $ARTICUL->get_art_color_variants($art)){
		$color_variants_block = $ARTICUL->color_variants_to_html2($color_variants);	
	}else{ $color_variants_block = '';}
}
?>