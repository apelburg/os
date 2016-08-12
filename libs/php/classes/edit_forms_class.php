 <?php



 class Form_editor{
 	// исполнитель услуг по правам
    
	
	function __construct(){

		$this->user_id = $_SESSION['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		// обработчик AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}
	}

	//////////////////////////
	//	старые методы
	//////////////////////////
				// /*
				// 	обработка AJAX внутри класса работает быстреее!!!!
				// 	скорее всего за счёт экономии памяти на ссылках на переменные
				// */


				// private function _AJAX_(){
				// 		$method_AJAX = $_POST['AJAX'].'_AJAX';
				// 		// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
				// 		if(method_exists($this, $method_AJAX)){
				// 			$this->$method_AJAX();
				// 			exit;
				// 		}					
				// 	}
				
				// // получаем контент по услуге и статусам
				// private function get_edit_content_for_usluga_AJAX(){
				// 	// блок редактирования цен, имени и типа услуги
				// 	echo $this->get_chenge_form_uslugi_Html();
				// 	// блок редактирования статусов
				// 	echo $this->get_status_uslugi_Html($_POST['id']);
				// 	// блок редактирования доп полей, необходимых для заполнения
				// 	echo $this->get_dop_input_uslugi_Html();
				// }

				// // создаем новое поле доп поле
				// // private function add_new_dop_input_AJAX(){
				// // 	// echo $this->add_new_dop_input_Html();
				// // }

				// // удаляем дополнительное поле из услуги
				// private function delete_dop_input_from_services_AJAX(){
				// 	global $mysqli;
				// 	// считываем из бызы данную услуги 
				// 	$usluga = $this->get_usluga_Database_Array($_POST['usl_id']);
				// 	// получаем массив id прикреплённых dop_inputs
				// 	$id_arr = explode(',', $usluga['uslugi_dop_inputs_id']);
				// 	// echo 'Удаляем '.$_POST['id_dop_imput'];
				// 	// удаляем элемент с данным id
				// 	while (($i = array_search(trim($_POST['id_dop_imput']), $id_arr)) !== false) {
				// 		unset($id_arr[$i]);
				// 	} 
					
				// 	// echo '<pre>';
				// 	// print_r($id_arr);
				// 	// echo '</pre>';
						
				// 	// вновь собираем строку id
				// 	$str = implode(',', $id_arr);
				// 	// echo $usluga['uslugi_dop_inputs_id'].'   -   '.$str;
				// 	// переписываем
				// 	$query = "UPDATE `".OUR_USLUGI_LIST."` SET 
				// 		`uslugi_dop_inputs_id` = '".$str."'
				// 	 WHERE `id`='".$_POST['usl_id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);

				// 	//echo '{"response":"OK","function":"alerting","html":"Поле успешно откреплено!"}';
				// }

				// // форма добавления нового поля
				// private function get_add_new_dop_input_form_AJAX(){
				// 	$html = ''; // $Html .= '';
				// 	$html .= '<form>';
				// 	$html .= '<div>';

				// 	$html .= '<input type="text" name="name_ru" value="Новое поле"><br>';
				// 	// $html .= '<input type="checkbox" name="disabled_editing" id="disabled_editing"><label for="disabled_editing" checked>Запретить редактирование после заполнения</label><br>';
				// 	// $html .= '<input type="checkbox" name="required_fields" id="required_fields"><label for="required_fields" checked>Не разрешать запуск заказа при незаполненном поле</label>';
				// 	$html .= '<input type="hidden" name="AJAX" value="add_new_dop_input">';
				// 	$html .= '<input type="hidden" name="usl_id" value="'.$_POST['usl_id'].'">';
				// 	$html .= '</div>';
				// 	$html .= '</form>';
				// 	echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				// }

				// // добавление нового поля
				// private function add_new_dop_input_AJAX(){
				// 	// принимаем кириллическое значение
				// 	$this->name_ru = trim($_POST['name_ru']);
				// 	// запоминаем транслитерацию
				// 	$this->name_en = $this->GetInTranslit(trim($_POST['name_ru']));
				// 	// проверяем по базе на совпадение поля name_en
				// 	global  $mysqli;
				// 	$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `name_en` = '".$this->name_en."'";
				// 	// echo $query.'<br>';
				// 	// обрабатываем запрос
				// 	$result = $mysqli->query($query) or die($mysqli->error);				
				// 	$count = 0;
				// 	if($result->num_rows > 0){
				// 		while($row = $result->fetch_assoc()){
				// 			$new_id = $row['id'];
				// 			$count++;
				// 		}
				// 	}else{
				// 		// если соответствий не найдено заводим новое поле
				// 		$query ="INSERT INTO `".CAB_DOP_USLUGI_DOP_INPUTS."` SET
				// 	             `name_en` = '".$this->name_en."',
				// 	             `name_ru` = '".$this->name_ru."'";

				// 		$result = $mysqli->multi_query($query) or die($mysqli->error);
				// 		// получаем id добавленного поля
				// 		$new_id = $mysqli->insert_id;
				// 	}
					
				// 	// считываем из бызы данную услуги 
				// 	$usluga = $this->get_usluga_Database_Array($_POST['usl_id']);
				// 	// echo '<pre>';
				// 	// print_r($usluga);
				// 	// echo '</pre>';
					
				// 	// если уже есть прикрепленные поля
				// 	if(trim($usluga['uslugi_dop_inputs_id'])!=""){
				// 		// получаем прикреплённые поля и разбиваем в массив
				// 		$inputs_arr = explode(",", trim($usluga['uslugi_dop_inputs_id']));
				// 	}
				// 	// добавляем в массив новый id
				// 	$inputs_arr[] = $new_id;


				// 	// echo '<pre>';
				// 	// print_r($inputs_arr);
				// 	// echo '</pre>';
						
				// 	// перезаписываем 
				// 	$query = "UPDATE `".OUR_USLUGI_LIST."` SET 
				// 		`uslugi_dop_inputs_id` = '".implode(",", $inputs_arr)."'
				// 	 WHERE `id`='".$_POST['usl_id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);

				// 	echo '{"response":"OK","function":"add_new_dop_inputs","dop_inputs_id":"'.$new_id.'","name_ru":"'.$this->name_ru.'"}';
				// }

				// private function additing_new_input(){

				// }



				// //GetInTranslit

				// // создаем новый статус в услуге
				// private function add_new_status_AJAX(){
				// 	echo $this->add_new_status_Html();
				// }

				// // сохраняем контент по услуге
				// private function save_edit_usluga_AJAX(){
				// 	echo $this->save_change_usluga();
				// }
				// // удаляем статус
				// private function delete_status_uslugi_AJAX(){
				// 	echo $this->delete_status_uslugi_Database();
				// }
				// // редактируем статусы
				// private function edit_name_status_AJAX(){
				// 	echo $this->edit_name_status_Database();
				// }
				// // удаление услуги
				// private function del_uslugu_AJAX(){
				// 	echo $this->del_uslugu_Database();
				// }

				// // добавление услуги
				// private function add_new_usluga_AJAX(){
				// 	echo  $this->add_new_usluga_Database();
				// }

				// private function add_new_usluga_Database(){
					
				// 	global $mysqli;

				// 	// 1 - приваращаем родительскую услугу в папку
				// 	// цены пока что оставляем
				// 	$query = "UPDATE `".OUR_USLUGI_LIST."` SET 
				// 		`for_how` = ''
				// 	 WHERE `id`='".$_POST['parent_id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);

				// 	// если этота услуга принадлежит к разделу нанесения 
				// 	if($_POST['parent_id']==6){
				// 		// - пишем пустую строку
				// 		$for_how = '';
				// 		// - выводим её с иконкой калькулятора
				// 		$class = 'calc_icon';
				// 		$buttons='';
				// 	}else{
				// 		$buttons = '<span class="button  usl_add">+</span>
				// 		<span class="button usl_del">X</span>';
				// 		$for_how = $class = 'for_all';
				// 	}
					
					



				// 	// 2 - добавляем услугу 
				// 	$query ="INSERT INTO `".OUR_USLUGI_LIST."` SET
				// 	             `parent_id` = '".$_POST['parent_id']."',
				// 	             `name` = 'Новая услуга',
				// 	             `price_in` = '0.00',
				// 	             `price_out` = '0.00',
				// 	             `for_how` = '".$for_how."',
				// 	             `type` = 'ЗАПОЛНИТЕ ПОЛЕ!!!' ";

				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);


					

				// 	$html = '
				// 	<div data-id="'.$mysqli->insert_id.'" data-parent_id="'.$_POST['parent_id'].'" class="lili '.$class.'" style="padding-left:'.($_POST['padding_left']+30).'px;background-position-x:'.($_POST['bg_x']+30).'px" data-bg_x="'.($_POST['bg_x']+30).'">
				// 		<span class="name_text">Новая услуга</span>
				// 		'.$buttons.'
				// 	</div>';
				// 	return $html;
				// }

				// private function del_uslugu_Database(){
				// 	global $mysqli;
				// 	$query = "UPDATE `".OUR_USLUGI_LIST."` SET `deleted` = '1' WHERE `id`='".$_POST['id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);
				// 	$return_json = '{"response":"OK"}'; 
				// 	return $return_json; 
				// }

				// private function edit_name_status_Database(){
				// 	global $mysqli;

				// 	$query = "UPDATE `".USLUGI_STATUS_LIST."` SET 
				// 		`name` = '".$_POST['name']."'
				// 	 WHERE `id`='".$_POST['id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);
				// 	$return_json = '{"response":"OK"}'; 
				// 	return $return_json; 
				// }

				// private function delete_status_uslugi_Database(){
				// 	global $mysqli;

				// 	$query = "DELETE FROM `".USLUGI_STATUS_LIST."` WHERE `id`='".$_POST['id']."'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);
				// 	$return_json = '{"response":"OK"}'; 
				// 	return $return_json; 
				// }

				// // добавление статуса к услуге
				// private function add_new_status_Html(){
				// 	global $mysqli;
				// 	$query ="INSERT INTO `".USLUGI_STATUS_LIST."` SET
				// 	             `parent_id` = '".$_POST['id']."',
				// 	             `name` = 'Новый статус для услуги'";
				// 	$result = $mysqli->multi_query($query) or die($mysqli->error);


				// 	$html = '<input class="status_name" type="text" value="Новый статус для услуги"> <span class="button status_del" data-id="'.$mysqli->insert_id.'">X</span>';
				// 	return $html;
				// }

				// // сохранение изменённых данных в услуге
				// private function save_change_usluga(){
				// 	global $mysqli;
				// 	$id = $_POST['id']; 
				// 	unset($_POST['id'],$_POST['AJAX']);

				// 	/////////////////////
				// 	//	-- checkboxes --
				// 	/////////////////////
				// 		// обрабатываем logotip_on
				// 		if(!isset($_POST['logotip_on'])){
				// 			$_POST['logotip_on'] = '';
				// 		}
				// 		// обрабатываем show_status_film_photos
				// 		if(!isset($_POST['show_status_film_photos'])){
				// 			$_POST['show_status_film_photos'] = '';
				// 		}
				// 		// обрабатываем delivery_apl
				// 		if(!isset($_POST['delivery_apl'])){
				// 			$_POST['delivery_apl'] = '';
				// 		}


				// 		// обрабатываем maket_true
				// 		if(!isset($_POST['maket_true'])){
				// 			$_POST['maket_true'] = '';
				// 		}
					
				// 	//////////////////
				// 	//    checkboxes 
				// 	///////////////////

				// 	$query = "UPDATE `".OUR_USLUGI_LIST."` SET ";		
				// 	$n=0;
				// 	foreach ($_POST as $key => $value) {
				// 		$query .= ($n>0)?', ':'';
				// 		$query .= "`".$key."` = '".$value."'";
				// 		$n++;
				// 	}

				// 	$query .=" WHERE `id`='".$id."';";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	// echo $query;
				// 	// echo '<pre>';
				// 	// print_r($_POST);
				// 	// echo '</pre>';
				// 	echo '{"response":"OK","message":"Изменения успешно сохранены"}';


				// }

				public function get_user_access_Database_Int($id){
					global $mysqli;
					$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
					$result = $mysqli->query($query) or die($mysqli->error);				
					$int = 0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$int = (int)$row['access'];
						}
					}
					//echo $query;
					return $int;
				}

				// // получаем список услуг в HTML для редактирования
				// public function get_uslugi_list_Database_Html($id=0,$pad=30){	
				// 	global $mysqli;
				// 	$html = '';
					
				// 	$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	if($result->num_rows > 0){
				// 		while($row = $result->fetch_assoc()){
				// 			if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
				// 				# Это услуги НЕ из КАЛЬКУЛЯТОРА
				// 				// запрос на детей
				// 				$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
				// 				// присваиваем конечным услугам класс may_bee_checked
				// 				$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span><span class="button  usl_add">+</span><span class="button usl_del">X</span></div>'.$child;
				// 			}else{
				// 				# Это услуги из КАЛЬКУЛЯТОРА
				// 				// кнопки добавления калькуляторов
				// 				$button_add = ($row['id']==6)?'<span class="button  usl_add">+</span>':'';
				// 				// кнопки удаления калькуляторов
				// 				$button_del = ($row['parent_id']==6)?'<span class="button usl_del calc_type">X</span>':'';

				// 				$buttons = $button_del.$button_add;

				// 				// запрос на детей
				// 				$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
				// 				// присваиваем конечным услугам класс may_bee_checked
				// 				$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$buttons.'</div>'.$child;
							
				// 			}
				// 		}
				// 	}
				// 	return $html;
				// }



				// // получаем список услуг 
				// public function get_ALL_uslugi_list_Database_Array(){	
				// 	global $mysqli;
							
				// 	$query = "SELECT * FROM `".OUR_USLUGI_LIST."`";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	$arr = array();
				// 	if($result->num_rows > 0){
				// 		while($row = $result->fetch_assoc()){
				// 			$arr[$row['id']] = $row;
				// 		}
				// 	}
				// 	return $arr;
				// }

				// private function select_performer_AJAX(){
				// 	global $mysqli;
				// 	$query = "UPDATE  `".OUR_USLUGI_LIST."`  SET  
				// 		`performer` =  '".(int)$_POST['val']."' 
				// 		WHERE  `id` ='".$_POST['usl_id']."';";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	echo '{"response":"OK"}';
				// }

				// // форма выбора исполнителя услуги
				// private function select_performer($real_val){	
				// 	$html = '<select class="select_performer">';
				// 		foreach ($this->performer as $key => $value) {
				// 			$is_checked = ($key==$real_val)?'selected="selected"':'';
				// 			$html .= ' <option '.$is_checked.' value="'.$key.'">'.$value.'</option>';
				// 		}	
				// 	$html .= '</select>';
					
				// 	return $html;
				// }


				// public function get_chenge_form_uslugi_Html(){
				// 	$html = '<div id="edit_block_usluga">';
				// 	$html .= '<form>';
				// 	//массив редактируемых данных по услуге
				// 	$usluga['parent_id'] = 'Родительский id';
				// 	$usluga['name'] = 'Наименование';
				// 	$usluga['price_in'] = 'Цена входящая';
				// 	$usluga['price_out'] = 'Цена исходящая';
				// 	$usluga['for_how'] = 'Kaк считать';
				// 	$usluga['type'] = 'Тип (англ. без пробелов)';

				// 	$radio_for_how[''] = 'none';
				// 	$radio_for_how['for_all'] = 'Для всех';
				// 	$radio_for_how['for_one'] = 'Для каждой ед. товара';


				// 	// получаем полную информацию из базы по одной услуге
				// 	$usluga = $this->get_usluga_Database_Array($_POST['id']);




				// 	// $html .= '<div></div>';
				// 	// наименовнаие услуги
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Наименование</div>';
				// 	$html .= '<div class="edit_info"><input type="text" value="'.$usluga['name'].'" name="name"></div>';
				// 	$html .= '</div>';

				// 	if($_POST['id'] != 6 && $_POST['parent_id'] != 6){
				// 		// тип услуги
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Тип</div>';
				// 		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['type'].'" name="type"></div>';
				// 		$html .= '</div>';

				// 		// Цена входящая
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Цена входащя</div>';
				// 		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_in'].'" data-real="'.$usluga['price_in'].'" name="price_in"> руб.</div>';
				// 		$html .= '</div>';

				// 		// Цена исходящая
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Цена исходащая</div>';
				// 		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_out'].'" data-real="'.$usluga['price_out'].'" name="price_out"> руб.</div>';
				// 		$html .= '</div>';

				// 		// Разрешить редактировать входящую цену
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Разрешить редактировать входящую цену</div>';
				// 		$html .= '<div class="edit_info"><input type="radio" id="edit_pr_in1" name="edit_pr_in" value="0" '.(($usluga['edit_pr_in']=="0")?'checked':'').'><label for="edit_pr_in1"><span>Запретить</span></label></div>';
				// 		$html .= '<div class="edit_info"><input type="radio" id="edit_pr_in2" name="edit_pr_in" value="1" '.(($usluga['edit_pr_in']=="1")?'checked':'').'><label for="edit_pr_in2"><span>Разрешить</span></label></div>';
				// 		$html .= '</div>';

				// 		// Как считаем
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Как считаем</div>';
				// 		$html .= '<div class="edit_info"><input type="radio" id="for_how1" name="for_how" value="" '.(($usluga['for_how']=="")?'checked':'').'><label for="for_how1"><span class="icon_style folder">папка</span></label></div>';
				// 		$html .= '<div class="edit_info"><input type="radio" id="for_how2" name="for_how" value="for_one" '.(($usluga['for_how']=="for_one")?'checked':'').'><label for="for_how2"><span class="icon_style for_one">на единицу товара</span></label></div>';
				// 		$html .= '<div class="edit_info"><input type="radio" id="for_how3" name="for_how" value="for_all" '.(($usluga['for_how']=="for_all")?'checked':'').'><label for="for_how3"><span class="icon_style for_all">на тираж</span></label></div>';
				// 		$html .= '</div>';

				// 		// Цена исходящая
				// 		$html .= '<div class="separation_container">';
				// 		$html .= '<div class="name_input">Шаблон ТЗ для менеджера</div>';
				// 		$html .= '<div class="edit_info"><textarea name="tz">'.$usluga['tz'].'</textarea></div>';
				// 		$html .= '</div>';
				// 	}
				// 	// исполнитель
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Выберите отдел ответственный за выставление статусов по услуге</div>';
				// 	$html .= '<div class="edit_info">'.$this->select_performer($usluga['performer']).'</div>';
				// 	$html .= '</div>';


				// 	// включение/отключение поля logotip
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Поле "Логотип"</div>';
				// 	$html .= '<div class="edit_info"><input type="checkbox" name="logotip_on" id="logotip_on" '.(($usluga['logotip_on']=="on")?'checked':'').'><label for="logotip_on">Включить</label><br>
				// 				<span class="greyText">(включает/отключает поле "логотип" в доп.тех.инфо)</span>
				// 			</div>';
				// 	$html .= '</div>';

				// 	// доставка
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Доставка Апельбург <span style="color:#FFAAAA; font-size:12px">экспорт услуг доставки в разработке к версии 2.0</span></div>';
				// 	$html .= '<div class="edit_info"><input type="checkbox" name="delivery_apl" id="delivery_apl" '.(($usluga['delivery_apl']=="on")?'checked':'').'><label for="delivery_apl">услуга относится к доставке Апл</label><br>
				// 				<span class="greyText">(указать если услуга относится к доставке АПЛ и должна экспортироваться в карту курьера)</span>
				// 			</div>';
				// 	$html .= '</div>';

				// 	// плёнки
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Плёнки / клише </div>';
				// 	$html .= '<div class="edit_info"><input type="checkbox" name="show_status_film_photos" id="show_status_film_photos" '.(($usluga['show_status_film_photos']=="on")?'checked':'').'><label for="show_status_film_photos">Показать в полях услуги статусы плёнок</label><br>
				// 				<span class="greyText">(указать если к услуге необходимо указывать статусы плёнок)</span>
				// 			</div>';
				// 	$html .= '</div>';

				// 	// наличие макета
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Наличие макета<span style="    color: rgba(255, 0, 0, 0.77); font-size:12px">&nbsp;Внимание. Если эта опция отключена, дизайнер не увидит эту услугу у себя в кабинете <br>(даже если отдел дизайна отвечает за переключение статусов по данной услуге)!!!!</span></div>';
				// 	$html .= '<div class="edit_info"><input type="checkbox" name="maket_true" id="maket_true" '.(($usluga['maket_true']=="on")?'checked':'').'><label for="maket_true">Для исполнения услуги необходимо наличие макета</label><br>
				// 				<span class="greyText">(указать если с услугой будет работать дизайнер или оператор, если включено - в доп.тех нфо появляется поле путь к макету)</span>
				// 			</div>';		
				// 	$html .= '</div>';

				// 	// Цена исходящая
				// 	$html .= '<div class="separation_container">';
				// 	$html .= '<div class="name_input">Описание услуги</div>';
				// 	$html .= '<div class="edit_info"><textarea name="note">'.$usluga['note'].'</textarea></div>';
				// 	$html .= '</div>';

				// 	// скрытое поле ID
				// 	$html .= '<div class="edit_info hidden_form_input"><input type="hidden" name="AJAX" value="save_edit_usluga"></div>';
				// 	$html .= '<div class="edit_info hidden_form_input"><input type="hidden" name="id" value="'.$usluga['id'].'"></div>';
					

				// 	$html .= '</form>';
				// 	$html .= '<div id="response_message"></div>';
				// 	$html .= '<div id="hidden_button"><input type="button" id="save_usluga" value="Сохранить"></div>';
				// 	$html .= '</div>';


				// 	return $html;
				// }


				// // получаем выпадающий список статусов для услуги
				// public function get_status_uslugi_Html($id,$uslugi_all_list = array()){
				// 	//получаем полный список услуг
				// 	if(empty($uslugi_all_list)){
				// 		$uslugi_all_list = $this->get_ALL_uslugi_list_Database_Array();
				// 	}
				// 	// получаем id по которым будем выбирать статусы для услуги
				// 	$id_s = implode(",",$this->get_id_parent_Database_Array($id,array()));
				// 	global $mysqli;
				// 	$html = '<div class="separation_container">';
				// 	$html .= '<div id="status_list">';
				// 	$html .= '<strong>Список статусов по разделам:</strong><br>';
				// 	$query = "SELECT * FROM `".USLUGI_STATUS_LIST."` WHERE `parent_id` IN (".$id_s.") ORDER BY `parent_id` ASC";
				// 	// echo $query.'<br>';
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	$gname = '';
				// 	if($result->num_rows > 0){		
				// 		while($row = $result->fetch_assoc()){
				// 			if($gname != $uslugi_all_list[$row['parent_id']]['name']){
				// 				$gname = $uslugi_all_list[$row['parent_id']]['name'];
				// 				// $html .= '<strong>'.$gname.'</strong>';
				// 				$html .= '<div class="gname">'.$gname.'</div>';
				// 			}
							
				// 			// $is_checked = ($real_val==$row['name'])?'selected="selected"':'';
				// 			// $html.= '<option value="'.$row['name'].'" '.$is_checked.'><!--'.$row['id'].' '.$row['parent_id'].'--> '.$row['name'].'</option>';
				// 			$html.= '<div>';
				// 			$html.= (($row['reserved_system']=='0')?'<input class="status_name" type="text" value="'.$row['name'].'"><span class="button status_del"  data-id="'.$row['id'].'">X</span>':$row['name']. '<span style="    color: rgba(255, 0, 0, 0.77); font-size:12px">&nbsp; статус зарезервирован системой </span>');
				// 			$html.= '</div>';
				// 		}
					
				// 	}
				// 	$html.= '</div>';
				// 	$html.= '<div><input type="button" id="add_new_status" value="Добавить +"></div>';
				// 	$html.= '</div>';
					

				// 	return $html;
				// }

				// public function get_dop_input_uslugi_Html(){
				// 	global $mysqli;
				// 	$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE id IN (".(($this->uslugi_dop_inputs_id=='')?0:$this->uslugi_dop_inputs_id).") ORDER BY `id` ASC";
				// 	// echo $query.'<br>';
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	$inputs = array();
				// 	if($result->num_rows > 0){
				// 		while($row = $result->fetch_assoc()){
				// 			$inputs[] = $row;
				// 		}
				// 	}
				// 	$html = '<div class="separation_container">';
				// 	$html .= '<br><strong>Доп. поля</strong>';
				// 	$html .= '<div id="dop_inputs_listing">';
				// 	foreach ($inputs as $value) {
				// 		$html .= '<div class="dop_inputs"  data-id="'.$value['id'].'"><span>'.$value['name_ru'].'</span><span class="button_del_dop_inputs status_del" data-id="'.$value['id'].'">X</span></div>';
				// 	}
				// 	$html .= '</div>';
				// 	$html.= '<div><input type="button" id="add_new_dop_input" value="Добавить +"></div>';
				// 	$html.= '</div>';
				// 	return $html;
				// }

				// // получаем полную информацию по услуге
				// private function get_usluga_Database_Array($id){
				// 	global $mysqli;

				// 	$arr = array();
				// 	$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$id."'";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	if($result->num_rows > 0){	
				// 		while($row = $result->fetch_assoc()){				
				// 			$arr = $row;				
				// 		}	
				// 	}
				// 	$this->uslugi_dop_inputs_id = $arr['uslugi_dop_inputs_id'];
				// 	return  $arr;
				// }



				// // получаем id родительских услуг 
				// // private function get_id_parent_Database_Array($id,$arr){
				// private function get_id_parent_Database_Array($id,$arr){
				// 	global $mysqli;
				// 	$arr[] = $id;
				// 	$id = implode(",",$arr);

				// 	$arr2 = array();
				// 	$query = "SELECT `id`,`parent_id` FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id.")";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	if($result->num_rows > 0){	
				// 		while($row = $result->fetch_assoc()){
				// 			$arr2[] = $row['parent_id'];
				// 			if($row['parent_id']!='0'){
				// 				$arr2 = array_merge($arr2, $this->get_id_parent_Database_Array($row['parent_id'],$arr2));
				// 			}
				// 			}	
				// 	}
				// 	return  array_unique(array_merge ($arr, $arr2));
				// }


	// ТРАНСЛИТЕРАЦИЯ
	public function GetInTranslit($string) {
		$replace=array(
			"'"=>"",
			"`"=>"",
			"а"=>"a","А"=>"a",
			"б"=>"b","Б"=>"b",
			"в"=>"v","В"=>"v",
			"г"=>"g","Г"=>"g",
			"д"=>"d","Д"=>"d",
			"е"=>"e","Е"=>"e",
			"ж"=>"zh","Ж"=>"zh",
			"з"=>"z","З"=>"z",
			"и"=>"i","И"=>"i",
			"й"=>"y","Й"=>"y",
			"к"=>"k","К"=>"k",
			"л"=>"l","Л"=>"l",
			"м"=>"m","М"=>"m",
			"н"=>"n","Н"=>"n",
			"о"=>"o","О"=>"o",
			"п"=>"p","П"=>"p",
			"р"=>"r","Р"=>"r",
			"с"=>"s","С"=>"s",
			"т"=>"t","Т"=>"t",
			"у"=>"u","У"=>"u",
			"ф"=>"f","Ф"=>"f",
			"х"=>"h","Х"=>"h",
			"ц"=>"c","Ц"=>"c",
			"ч"=>"ch","Ч"=>"ch",
			"ш"=>"sh","Ш"=>"sh",
			"щ"=>"sch","Щ"=>"sch",
			"ъ"=>"","Ъ"=>"",
			"ы"=>"y","Ы"=>"y",
			"ь"=>"","Ь"=>"",
			"э"=>"e","Э"=>"e",
			"ю"=>"yu","Ю"=>"yu",
			"я"=>"ya","Я"=>"ya",
			"і"=>"i","І"=>"i",
			"ї"=>"yi","Ї"=>"yi",
			"є"=>"e","Є"=>"e"
		);

		$text = iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
		// Удаляем знаки припенания 
		$text = preg_replace("|[^\d\w ]+|i","",$text); 
		// меняем пробелы на _
		$text = str_replace(" ", "_", trim($text)); 
		return $text;
	}

}


?>