<?php
class Position_catalog{
	// экземпляр класса mysqli
	private $mysqli;

	// id юзера
	private $user_id;

	// допуски пользователя
	private $user_access;

	// права на редактирование поля определяются внутри 
	// некоторых функций 
	private $edit_admin;
	private $edit_men;
	private $edit_snab;

	// id позиции
	private $id_position;
	
	function __construct($user_access = 0){ // необязательный параметр доступов юзера, нет допуска - нет редактирования
		$this->user_id = $_SESSION['access']['user_id'];
		$this->user_access = $user_access;

		// обработчик AJAX // в первой редакции.... РАБОЧИЙ !!!!
		if(isset($_POST['global_change']) && isset($_POST['change_name'])){
			$this->_AJAX_first();
		}

		// обработчик AJAX // в конечной редакции... теперь все обработчики будут 
		// передававться через ключ AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}

	}

	private function _AJAX_(){
		$method_AJAX = $_POST['AJAX'].'_AJAX';
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}		
	}





	////////////      AJAX 2    /////////////////
	// вариант 1, 
	// все новые вызовы пишется по варианту 2 !!!!!
	private function _AJAX_first(){ // router
		$method_AJAX = $_POST['change_name'].'_AJAX';
		$this->$method_AJAX();
		exit;
	}

	private function get_uslugi_list_Database_Html_7777($id=0){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$html .= '<ul>';
			while($row = $result->fetch_assoc()){
				if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = $this->get_uslugi_list_Database_Html_($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
				}
			}
			$html.= '</ul>';
		}
		return $html;
	}

	// сохранение типа даты отгрузки (р/д + дата VS р/д)
	private function save_shipping_type_AJAX(){
		global $mysqli;
		$query = "UPDATE ".RT_DOP_DATA." SET 
		`shipping_type`='".$_POST['value']."', 
		`shipping_redactor_id`='".$this->user_id."',
		`shipping_redactor_access`='".$this->user_access."' 
		WHERE `id`='".$_POST['row_id']."';";
		
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK"}';
		exit;
	}

	// private function save_price_in_out_for_one_price_AJAX(){
	// 	global $mysqli;
	// 	$query = "UPDATE ".RT_DOP_DATA." SET 
	// 	`price_in`='".$_POST['price_in']."', 
	// 	`price_out`='".$_POST['price_out']."' 
	// 	WHERE `id`='".$_POST['dop_data']."';";
		
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	echo '{"response":"OK"}';
	// 	exit;
	// }

	private function size_in_var_AJAX(){
		global $mysqli;

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



	// private function size_in_var_all_AJAX(){
	// 	global $mysqli;
	// 	// echo "<pre>";
	// 	// print_r($_POST);
	// 	// echo "</pre>";
	// 	$tir = $_POST['val']; // array / тиражи
	// 	$key2 = $_POST['key']; // array / id _ row size
	// 	$dop = $_POST['dop']; // array / запас
	// 	$id = $_POST['id']; // array / id 

	// 		//print_r($_POST['id']);exit;
	// 	$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$id[0]."'";
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	$json = '';
	// 	if($result->num_rows > 0){
	// 		while($row = $result->fetch_assoc()){
	// 			$json = $row['tirage_json'];
	// 			//echo $row['tirage_json'];
	// 		}
	// 	}
	// 	//echo $json;
	// 	//$r = $json;
	// 	$arr_json = json_decode($json,true);
	// 	$sum_tir = 0;
	// 	$sum_zap = 0;
	// 	foreach ($key2 as $key => $value) {
	// 		//echo $value;
	// 		$arr_json[$value]['dop'] = $dop[$key];
	// 		$arr_json[$value]['tir'] = $tir[$key];

	// 		$sum_zap += $dop[$key];
	// 		$sum_tir += $tir[$key];
	// 	}

	// 	// $arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];
	// 	//echo $r .'   -   ';
	// 	//echo json_encode($arr_json);
	// 	$query = "UPDATE `".RT_DOP_DATA."` SET `quantity` = '".$sum_tir."',`zapas` = '".$sum_zap."',`tirage_json` = '".json_encode($arr_json)."' WHERE  `id` ='".$id[0]."'";	
	// 	// // echo $query;			
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	exit;
	// }
	
	private function change_archiv_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `row_status` = 'green' WHERE  `id` ='".$_POST['id']."';";
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		// $result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"1","text":"test"}';
		exit;
	}
	private function change_tirage_pz_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `print_z` = '".$_POST['pz']."' WHERE  `id` ='".$_POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;

	}
	private function change_variante_shipping_time_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_time` = '".$_POST['time']."' 
		WHERE  `id` ='".$_POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK"}';
	}


	/**
	 *	Преобразует массив содержащий id услуг нанесения прикреплённых к артикулу 
	 *	в массив названий этих нанесений
	 *
	 *	@param 	 object get_print_mode
	 *	@return  array()	
	 *	@author  Алексей	
	 *	@version 11:00 28.09.2015  OBSOLETE
	 */
	public function get_print_names_array(){
		$name_ru_arr = array();

		// получаем id нанесений
		if(isset($this->get_print_mode) && !empty($this->get_print_mode)){
			$n = 0; $id = '';
			foreach ($this->get_print_mode as $key => $value) {
				$id .= (($n>0)?",":"")."'".$value."'";
				$n++;
			}
			// делаем запрос по этим нанесениям
			global $mysqli;
			$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id.")";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name_ru_arr[] = $row['name'];
				}
			}
		}		
		return $name_ru_arr;
	}

	/**
	 *	Преобразует массив содержащий id услуг нанесения прикреплённых к артикулу 
	 *	в строку названий этих нанесений
	 *
	 *	@param 	 object get_print_mode
	 *	@return  string	
	 *	@author  Алексей	
	 *	@version 11:00 28.09.2015
	 */
	public function get_print_names_string(){
		$name_ru_arr = '';
		// получаем id нанесений
		if(isset($this->get_print_mode) && !empty($this->get_print_mode)){
			$n = 0; $id = '';
			foreach ($this->get_print_mode as $key => $value) {
				$id .= (($n>0)?", ":"")."'".$value."'";
				$n++;
			}
			// делаем запрос по этим нанесениям
			global $mysqli;
			$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id.")";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name_ru_arr .= '<span>'.$row['name'].'</span>';
					$n++;
				}
			}
		}		
		return '<div id="attaching_names_of_print_types">'.$name_ru_arr.'</div>';
	}

	// // копируем услуги варианта
	// private function copy_services_row_for_variant($dop_row_reference_id, $dop_row_new_id){
	// 	global $mysqli;
	// 	$query = "SELECT * FROM  `".RT_DOP_USLUGI."`
	// 			WHERE  `dop_row_id` ='".$dop_row_reference_id."'";  /// !!!! править тут !!!!
	// 	$services_arr = array();
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	if($result->num_rows > 0){
	// 		while($row = $result->fetch_assoc()){
	// 			$services_arr[] = $row;
	// 		}
	// 	}
	// 	// echo $query;
	// 	if(count($services_arr)>0){
			
	// 		foreach ($services_arr as $key => $service) {
	// 			$query = '';
	// 			$query .= "INSERT INTO `".RT_DOP_USLUGI."` SET";
	// 			$n = 0;
	// 			foreach ($service as $name_column => $value) {
	// 				if($name_column=="id"){continue;}
	// 				if($name_column!='dop_row_id'){
	// 					$query .= (($n>0)?',':'')." `".$name_column."`='".$value."' ";
	// 				}else{
	// 					$query .= (($n>0)?',':'')." `".$name_column."`='".$dop_row_new_id."' ";
	// 				}
	// 				$n++;
	// 			}
	// 			$query .= '; ';
	// 			$result = $mysqli->query($query) or die($mysqli->error);
	// 		}
	// 		// echo $query;
			
	// 	}
	// 	return;
	// }

	// создаём копию варианта
	// перенесён в universal class
	// private function new_variant_AJAX(){
	// 	global $mysqli;

	// 	$reference_id = (int)$_POST['id'];
	// 	// собираем запрос, копируем строку в БД
	// 	$query = "INSERT INTO `".RT_DOP_DATA."` 
	// 	(row_id, quantity,price_in, price_out,discount,tirage_json) 
	// 	(SELECT row_id, quantity,price_in, price_out,discount,tirage_json 
	// 		FROM `".RT_DOP_DATA."` WHERE id = '".$reference_id."')";
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	// запоминаем новый id
	// 	$insert_id = $mysqli->insert_id;

	// 	if(isset($_POST['services']) && $_POST['services'] == 'true'){
	// 		// копируем услуги
	// 		$this->copy_services_row_for_variant($reference_id, $insert_id);
	// 	}
	// 	// узнаем количество строк
	// 	$query = "SELECT COUNT( * ) AS `num`
	// 			FROM  `".RT_DOP_DATA."`
	// 			WHERE  `row_id` ='".$_POST['row_id']."'";  /// !!!! править тут !!!!
	// 	$result = $mysqli->query($query) or die($mysqli->error);
	// 	if($result->num_rows > 0){
	// 		while($row = $result->fetch_assoc()){
	// 			$num_rows = $row['num'];
	// 		}
	// 	}



	// 	echo '{ "response":"1",
	// 			"text":"test",
	// 			"new_id":"'.$insert_id.'",
	// 			"num_row":"'.($num_rows-1).'",
	// 			"num_row_for_name":"Вариант '.$num_rows.'"
	// 			}';
	// 	exit;
	// }

	private function save_standart_day_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` 
					SET 
					`work_days` =  '".$_POST['standart']."'
					WHERE  `id` ='".$_POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	private function change_variante_shipping_date_AJAX(){
		global $mysqli;

		$date = $_POST['date'];
		$date = strtotime($date);
		$date = date("Y-m-d", $date);
		$query = "UPDATE `".RT_DOP_DATA."` SET
		 `shipping_date` = '".$date."'  WHERE  `id` ='".$_POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	///////////    AJAX END   /////////////////

	public function get_all_info($art_id){
		$this->color = $this->get_color($art_id);		
		$this->material = $this->get_material($art_id);
		$this->get_print_mode = $this->get_print_mode($art_id);

		global $mysqli;
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `id` = '".(int)$art_id."'";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				
			}
		}

		$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `id` = '".(int)$_GET['id']."'";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info_main = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info_main = $row;
				
			}
		}
	}

	private function get_color($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_COLORS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['color'];
			}
		}
		return $arr;
	}

	private function get_material($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_MATERIALS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['material'];
			}
		}
		return $arr;
	}


	private function get_print_mode($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				// echo '<pre>';
				// print_r($row);
				// echo '</pre>';
					
				$arr[] = $row['print_id'];
			}
		}
		return $arr;
	}

	// ВЫВОДИТ СПИСОК УСЛУГ ПРИКРЕПЛЁННЫХ ДЛЯ ВАРИАНТА
	// $NO_show_head добавлен как необязательная переменная для отключения вывода 
	// $pause - флаг запрета редактирования
	// названия группы услуги
	// public function uslugi_template_cat_Html($arr=array(), $NO_show_head = 0, $status_snab='', $pause=0, $edit_true=true){
	public function uslugi_template_cat_Html($arr, $NO_show_head = 0, $status_snab='', $pause=0, $edit_true=true){
		// определяем редакторов для полей (html тегов)
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'

		// если работает снаб, ограничиваем права мена и наоборот
		if($status_snab=='in_calculation' || $status_snab=='in_recalculation'){
			$this->edit_snab = ($this->user_access == 8 && !$pause)?' contenteditable="true" class="edit_span"':'';
			$this->edit_men = '';						
		}else{
			$this->edit_men = ($this->user_access == 5 && !$pause)?' contenteditable="true" class="edit_span"':'';
			$this->edit_snab = '';						
		}

		// обнуляем все права при $edit_true == false
		if($edit_true == false){
			$this->edit_men = '';
			$this->edit_snab = '';
			$this->edit_admin = '';
			$pause = 1;
		}

		$html ='';
		// если массив услуг пуст возвращаем пустое значение 
		if(!count($arr)){return $html;}
		
		// сохраняем id услуг
		$id_s = array();
		foreach ($arr as $key => $value) {
			$id_s[] = $value['uslugi_id'];
		}
		$id_s = implode(', ', $id_s);

		// делаем запрос по услугам
		global $mysqli;
		$query = "SELECT `".OUR_USLUGI_LIST."`.`parent_id`,
		`".OUR_USLUGI_LIST."`.`tz`,
		`".OUR_USLUGI_LIST."`.`edit_pr_in`,
		`".OUR_USLUGI_LIST."`.`price_out`,
		`".OUR_USLUGI_LIST."`.`for_how`,
		`".OUR_USLUGI_LIST."`.`id`,
		`".OUR_USLUGI_LIST."`.`name`,
		`".OUR_USLUGI_LIST."_par`.`name` AS 'parent_name' 
		FROM ".OUR_USLUGI_LIST."
inner join `".OUR_USLUGI_LIST."` AS `".OUR_USLUGI_LIST."_par` ON `".OUR_USLUGI_LIST."`.`parent_id`=`".OUR_USLUGI_LIST."_par`.`id` WHERE `".OUR_USLUGI_LIST."`.`id` IN (".$id_s.") ORDER BY  `os__our_uslugi_par`.`name` ASC ";
		// $query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id_s.")";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);				
		$services_arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				foreach ($arr as $key => $value) {
					$services_arr[$row['id']] = $row;
				}
			}
		}

		include_once(ROOT."/libs/php/classes/print_calculators_class.php");

		$uslname = '';
		foreach ($services_arr as $key => $service) {
			// $NO_show_head добавлен как необязательная переменная для отключения вывода 
			// названия группы услуги

			if($uslname != $service['parent_name'] && !$NO_show_head){
				$html .= '<tr  class="group_usl_name" data-usl_id="'.$service['parent_id'].'">
		 				<th colspan="7">'.$service['parent_name'].'</th>
 				</tr>';
 				$uslname = $service['parent_name'];
			}
			foreach ($arr as $key2 => $value2) {
				if($value2['uslugi_id']==$key){

					$price_in = (($value2['for_how']=="for_all")?$value2['price_in']:($value2['price_in']*$value2['quantity']));
					$price_out_men = ($value2['for_how']=="for_all")?$value2['price_out']:$value2['price_out']*$value2['quantity'];
					
					$pribl = ($value2['for_how']=="for_all")?($value2['price_out']-$value2['price_in']):($value2['price_out']*$value2['quantity']-$value2['price_in']*$value2['quantity']);
					$dop_inf = ($value2['for_how']=="for_one")?'(за тираж '.$value2['quantity'].' шт.)':'';
					
					// информация из калькулятора
					$calc_info = '';$calc_class= '';
					if($service['parent_id'] == 6){
						$calc_class = ' service-calculator';
						$calc_info = '<span class="calc_info">/ '.printCalculator::convert_print_details($value2['print_details']).' /</span>';	
					}
					


					$price_out_snab = ($value2['for_how']=="for_all")?$value2['price_out_snab']:$value2['price_out_snab']*$value2['quantity'];


					$real_price_out = ($service['for_how']=="for_all")?$service['price_out']:$service['price_out']*$value2['quantity'];



					// ТЗ кнопки
					$buttons_tz = (trim($value2['tz'])=='')?'<span class="tz_text_new"></span>':'<span class="tz_text_edit"></span>';


					$html .= '<tr class="calculate calculate_usl " data-dop_uslugi_id="'.$value2['id'].'" data-our_uslugi_id="'.$service['id'].'" data-our_uslugi_parent_id="'.trim($service['parent_id']).'"  data-for_how="'.trim($service['for_how']).'">
										<td><div class="'.$calc_class.'">'.$service['name'].' '.$dop_inf.' <br> '.$calc_info.'</div></td>
										<td class="row_tirage_in_gen uslugi_class price_in"><span '.(($service['edit_pr_in'] == '1')?$this->edit_admin.$this->edit_snab.$this->edit_men:'').'>'.$this->round_money($price_in).'</span></td>
										<td class="row_tirage_in_gen uslugi_class percent_usl"><span '.$this->edit_admin.$this->edit_snab.$this->edit_men.'>'.$this->get_percent_Int($value2['price_in'],$value2['price_out']).'</span></td>
										<td class="row_price_out_gen uslugi_class price_out_men"><span '.$this->edit_admin.$this->edit_men.'>'.$this->round_money($price_out_men).'</span></td>
										<td class="row_pribl_out_gen uslugi_class pribl"><span>'.$this->round_money($pribl).'</span></td>
										<td class="usl_tz">'.$buttons_tz.'<span class="tz_text">'.base64_decode($value2['tz']).'</span><span class="tz_text_shablon">'.$service['tz'].'</span></td>';

					$html .= ($this->user_id == $value2['creator_id'] || $this->user_access == 1 )?'<td class="usl_del"><span class="del_row_variants"></span></td>':'';
					// $html .= $value2['creator_id'];
					$html .='</tr>';

				}
			}

		}

		return $html;
	}
	//№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№



	public function get_dop_params($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;

		/*$query = "SELECT `".BASE_DOP_PARAMS_TBL."`.* , `".RT_ART_SIZE."`.`variant_id`,`".RT_ART_SIZE."`.`tirage`,`".RT_ART_SIZE."`.`id` AS id2, `".RT_ART_SIZE."`.`tirage_dop` 
		FROM `".BASE_DOP_PARAMS_TBL."` 
		left JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`size_id` = `".BASE_DOP_PARAMS_TBL."`.`id`  
		WHERE `".BASE_DOP_PARAMS_TBL."`.`art_id` = '".$art_id."'";
*/
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	/**
	 * Меню выбора типа спецификации в карточке (дата/рд)	
	 *
	 * 		    
	 */
	public static function get_select_shipping_type($var){
		$html = '<select style="padding: 2px 5px 3px 5px;border: 1px solid #EAE6E6;" class="js-edit-type_specificate" data-id="'.$var['id'].'">';
		
		$option_arr = array(
			'none' => 'отгрузка...',
			'date' => 'по дате',
			'rd' => 'по рд'
		);

		foreach ($option_arr as $key => $name_ru) {
			$selected = ($var['shipping_type'] == $key)?' selected="selected"':'';
			$html .= '<option value="'.$key.'" '.$selected.'>'.$name_ru.'</option>';
		}

		$html .= '</select>';
		return $html;						
	}


	public function get_variants_arr_sort_for_type($variants_arr){
		if(!isset($this->variants_arr_sort_for_type)){
			$this->variants_arr_sort_for_type = array();
			foreach ($variants_arr as $key => $variant) {
				$this->variants_arr_sort_for_type[$variant['row_status']] = $variant['id'];
			}
		}
		return $this->variants_arr_sort_for_type;
	}
	
	public function generate_variants_menu($variants){		
		$html = ''; // контент функции
		
		$ch = 0; // счетчик количества выбранных элементов, может не больше одного
		
		
		$arr_for_type = $this->get_variants_arr_sort_for_type($variants);
		

		
		for ($i=0; $i < count($variants); $i++) { 
			$checked = ''; // имя класса для выбранного элемента

			$row_status = $variants[$i]['row_status'];

			// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
			if(!isset($_GET['show_archive']) && $row_status=='red'){ continue;}

			switch ( $row_status ) {
				case 'sgreen':// не история - рабочий вариант расчёта
					if($ch < 1){$checked='checked';$ch++;}					
					$html .='<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
					break;

				case 'green':// не история - рабочий вариант расчёта
					if($ch < 1 && @count($arr_for_type['sgreen']) == 0){$checked='checked';$ch++;}					
					$html .='<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
					break;				
				
				case 'grey':// не история - вариант расчёта не учитывается в РТ
					if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0){$checked='checked';$ch++;}
					$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
					break;			
				
				default: // вариант расчёта red (архив), остальное не важно
					
					if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0 && @count($arr_for_type['grey']) == 0){$checked='checked';$ch++;}
					$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name show_archive">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
					break;
			}
		}
		return $html;
	}

	public function get_size_table($dop_params_arr, $val){
		// преобразует массив дополнительных параметров в таблицу размеров

		// выборка данных о введённых ранее размерах из строки JSON 
		$tirage_json = json_decode($val['tirage_json'], true);

		$html = "";
		if(count($dop_params_arr)==0){
			$html = "Дополнительная информация отсутствует. Обратитесь к администратору.";
			return $html;
		};

		// собираем таблицу с доп размерами
		$html = '
			<table>
				<tr>
					<th>Размер</th>
					<th>на складе</th>
					<th>свободно</th>
					<th>тираж</th>
					<th>запас</th>
				</tr>
		';
		
		
		// подсчитываем сумму заказа и общий остаток для их сравнения
		$summ_zakaz = 0;
		$summ_ostatok = 0;
		
		// флаг под заказ
		$pod_zakaz = 0;

		foreach ($dop_params_arr as $k => $v) {
			$value = (isset($tirage_json[$v['id']]['tir']))?$tirage_json[$v['id']]['tir']:0;
			$value_dop = (isset($tirage_json[$v['id']]['dop']))?$tirage_json[$v['id']]['dop']:0;
			$summ_ostatok += $v['ostatok_free'];
			$summ_zakaz += $value + $value_dop;
			if($v['ostatok_free']<($value + $value_dop)){$pod_zakaz = 1;}
		}
		// перебираем строки размерной таблицы
		foreach ($dop_params_arr as $k => $v) {
			$value = (isset($tirage_json[$v['id']]['tir']))?$tirage_json[$v['id']]['tir']:0;
			$value_dop = (isset($tirage_json[$v['id']]['dop']))?$tirage_json[$v['id']]['dop']:0;
			$no_edit_class = (($v['ostatok_free']=='0' && $summ_ostatok>=$summ_zakaz && $pod_zakaz!=1)?' input_disabled':'');
			$rearonly = (($v['ostatok_free']=='0' && $summ_ostatok>=$summ_zakaz  && $pod_zakaz!=1)?'readonly="readonly"':'');
			$html .= '
					<tr class="size_row_tbl">
						<td>'.$v['size'].'</td>
						<td>'.$v['ostatok'].'<br><span>(в пути) '.$v['on_way_free'].'</span></td>
						<td class="ostatok_free">'.$v['ostatok_free'].'</td>
						<td><input type="text" data-dop="tir" data-var_id="'.$val['id'].'" class="val_tirage'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value.'" '.$rearonly.'></td>
						<td><input type="text" data-dop="dop" data-var_id="'.$val['id'].'" class="val_tirage_dop'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value_dop.'" '.$rearonly.'></td>
					</tr>
			';
		}
		$html .= '</table>';

		$html .= '
			<div class="sevrice_button_size_table">
				<span onclick="chenge_hidden_input_status(\'0\',this);" class="btn_var_std '.(($pod_zakaz==1)?'checked':'').'" name="order">под заказ</span>
				<span onclick="chenge_hidden_input_status(\'1\',this);" class="btn_var_std '.(($pod_zakaz==0)?'checked':'').'" name="reserve">под резерв</span>
			</div>
			';

		return $html;

	}

	##################################################
	#### СЕРВИСНЫЕ МЕТОДЫ (для удобства обсчёта)  ####
	##################################################
	// форматируем денежный формат + округляем
	private function round_money($num){
		return number_format(round($num, 2), 2, '.', '');
	}
	// подсчёт процентов наценки
	private function get_percent_Int($price_in,$price_out){
		$per = ($price_in!= 0)?$price_in:0.09;
		$percent = round((($price_out-$price_in)*100/$per),2);
		return $percent;
	}
	##################################################
	##################################################
	##################################################


	
	// далее старые функции 
	public function fetch_images_for_article2($art){
		if(!$art || $art=='0'){return array();}
		global $db;
		// основная картинка
		$i=0;
		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='big' ORDER BY  id ASC";
		// echo $query;
		$result =mysql_query($query,$db) or die(mysql_error());
		//$counter = 0;
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			   $big_images_id[] = $item['id'];
			   if(!isset($main_img_src)) $main_img_src = checkImgExists( APELBURG_HOST.'/img/'.$item['name']);

			   if(mysql_num_rows($result)>1) $big_images[] = $item['name'];
			   
			}
		}
		else $main_img_src = checkImgExists(APELBURG_HOST.'/img/no_image.jpg');
	   	
		// вычисляем превьющки
		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='small' ORDER BY  id ASC";
		$result =mysql_query($query,$db) or die(mysql_error());
		$counter = 0;
		$counter2 = 0;
		$counter3 = 0;
		$alt = (isset($name))?altAndTitle($name):'';
		// если артикул имеет больше одной картинки строим панель с превьюшками
		if(mysql_num_rows($result)>1){
		
			while($item = mysql_fetch_assoc($result)){
			
				//$deleting_img = (isset($_SESSION['access']['access']) &&  ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3))?'<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$item['id'].'|'.$big_images_id[$counter2++].'|'.$big_images[$counter3++].'|'.$item['name'].'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>':'3';
				$deleting_img = (isset($_SESSION['access']['access']) &&  ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3))?'<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$big_images[$counter3++].'|'.$item['name'].'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>':'';
				
				$previews_block[] = '<div  class="carousel-block"><img class="articulusImagesMiniImg imagePr" alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$item['name']).'" data-src_IMG_link="'.APELBURG_HOST.'/img/'.$big_images[$counter++].'">'.$deleting_img.'</div>';
				
			   //echo $item['size'].' '.$item['name'].'<br>';
			   $i++;
			}
		}
		if(isset($_SESSION['access']['access']) && ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3)){
			$previews_block[] = '<div  class="carousel-block" id="image_add"><img class="articulusImagesMiniImg imagePr" alt="" src="'.APELBURG_HOST.'/skins/images/general/add_image_d.png" data-src_IMG_link="'.APELBURG_HOST.'/skins/images/general/add_image_d.png"></div>';	
			$i++;	
		}
		if(isset($i) && $i>0){
			$string	= implode('',$previews_block);
			$html = '<div class="carousel shadow" style="">'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s2.png)"></a>'.PHP_EOL:'';
			$html .= '<div class="carousel-wrapper">'.PHP_EOL;
			$html .= '<div class="carousel-items">'.PHP_EOL;	
			$html .= $string;
			$html .= '</div>'.PHP_EOL;
			$html .= '</div>'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s22.png); float:right; margin-top:-70px"></a>'.PHP_EOL:'';
			$html .= '</div>'.PHP_EOL;
			$previews_block = $html;
		}else{
			$previews_block = '<div>нет дополнительных картинок</div>';
		}
		return array('main_img_src' => $main_img_src,'previews_block' => $previews_block);
	}

	
	//функция вывода вариантов цветов, при нали, при кол-ве цветов более 6 - выводим стрелки прокрутки
	public function color_variants_to_html2($color_variants){
		//print_r($color_variants);//		
		foreach($color_variants as $item){ $block[] = '<div class="carousel-block"><a target="_blank" href="'.APELBURG_HOST.'/description/'.$item['id'].'/" border="0"><img class="carousel-block"  alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$item['img']).'" ></a></div>'.PHP_EOL;}
		$string = implode('',$block);
		$html = '<div id="articulusImagesMiniImg" class="carousel shadow">'.PHP_EOL;
		$html .= count($block)>6?'<a href="" class="articulusImagesArrow1 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s1.png); margin-right:5px"></a>'.PHP_EOL:'';
		$html .= '<div class="carousel-wrapper">'.PHP_EOL;
		$html .= '<div class="carousel-items">';
		$html .=$string;
		$html .='</div>'.PHP_EOL;
		$html .='</div>'.PHP_EOL;
		$html .=count($block)>6?'<a href="" class="articulusImagesArrow1 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s11.png); margin-left:5px; background-position: 3px"></a>'.PHP_EOL:'';
		$html .='</div>';
		return $html;
	}
	public function get_art_color_variants($art){
		function find_matches($art,$pattern){
			global $db;
			//выбираем id артикулов соответсвующих патерну
			$query = "SELECT id FROM `".BASE_TBL."` WHERE art != '".$art."' AND SUBSTRING(art,1,".strlen($pattern).")='".$pattern."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			while($item = mysql_fetch_assoc($result)) $itog_ids_arr[] = $item['id'];		
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
				
			
			//отсекаем те артикулы которые у которых нулевые остатки и цена
			$arr15 = (isset($itog_ids_arr))?implode("','",$itog_ids_arr):'';
			$query = "SELECT art_id FROM `".BASE_DOP_PARAMS_TBL."` WHERE ( ostatok + on_way ) >= '0' AND price > '0' AND art_id IN('".$arr15."') GROUP BY art_id";
			
			$result = mysql_query($query,$db) or die(mysql_error());
			$itog_ids_arr = array();
			while($item = mysql_fetch_assoc($result)) $itog_ids_arr[] = $item['art_id'];
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
			
			//отсекаем те артикулы которые лежат в скрытых категориях
			$arr15 = implode("','",$itog_ids_arr);
			$query = "SELECT rel.article_id article_id, rel.category_id category_id
			           FROM `".BASE_ARTS_CATS_RELATION."` rel 
					   INNER JOIN `".GIFTS_MENU_TBL."` menu
					   ON  rel.category_id = menu.id
			           WHERE menu.hide != '1' AND rel.article_id IN('".$arr15."') ORDER BY rel.category_id ASC LIMIT 0,15";
			$result = mysql_query($query,$db) or die(mysql_error());
			$itog_ids_arr = array();
			while($item = mysql_fetch_assoc($result)){
				 // отказался от такого подхода  $hiden_cat_begining = get_menu_item_id(BEGINING_HIDEN_MENU_CATS);
				 // отказался от такого подхода  if((int)$item['category_id'] >= (int)$hiden_cat_begining['id']) break;
			     $itog_ids_arr[] = $item['article_id'];
			}
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
			
			
			//получаем изображения артикулов
			$arr15 = implode("','",$itog_ids_arr);
			$query = "SELECT base.id id,  base.art art, images.name name FROM `".BASE_TBL."` base
			          INNER JOIN `".IMAGES_TBL."` images 
					  ON  base.art = images.art  WHERE size = 'small' AND base.id IN('".$arr15."') GROUP BY  base.id ORDER BY images.id ASC";
			$result = mysql_query($query,$db) or die(mysql_error());
			while($item = mysql_fetch_assoc($result)){
				 $output[] = array('id'=>$item['id'],'art'=>$item['art'],'img'=>$item['name']);
			}
			//echo '<pre>';print_r($output);echo '</pre>';
			return  (isset($output))?$output:'';
			
		}
		
	    $prefix = substr($art,0,2);
		switch($prefix){
		   case '15':
		      // для Интерпрезента(15) следующее правило две цифры или латинские буквы заглавные с точкой  или слешем ними перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(\.[\dA-Z]{2})$/',$art,$matches) && !preg_match('/^(.*[^\.])(\/[\dA-Z]{2})$/',$art,$matches)) return FALSE;
		      
		      break;
		   case '26':
		      // для Оазиса(26) следующее правило две цифры (с точкой  или без перед ними) в конце номера артикула обозанчают цвет
		      if(!preg_match('/^([\d]{7})(\.[\d]{2})$/',$art,$matches) && !preg_match('/^([\d]{7})([\d]{1})$/',$art,$matches)) return FALSE;
		      break;
		  case '37':
		  	  // для Проекта(37) следующее правило две цифры с точкой перед ними в конце номера артикула обозанчают цвет		
		      if(!preg_match('/^(.*[^\.])(\.[\d]{2})$/',$art,$matches)) return FALSE;
		      break;
		  case '59':
		      // для Макроса(59) следующее правило от 1 до 2 цифр с тире перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(-[^-.]{1,2})$/',$art,$matches)) return FALSE;
		      break;
		  case 'e_':
		       // для Ебазара(e_) следующее правило от 3 до 6 СИМВОЛОВ с тире перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(-[^-.]{3,6})$/',$art,$matches)) return FALSE;
		      break;
		  default:
		      return FALSE;
			  break;
		
		}
			return find_matches($art,$matches[1]);	
		//exit;
	}

	// получаем все варианты просчёта по данному артикулу
	public function get_all_variants_info_Database_Array($id){
		global $mysqli;

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
		return $variants;
	}


	function __destruct(){}

}
