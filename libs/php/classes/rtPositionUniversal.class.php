<?php
/** 
 * Правила комментирования можно посмотреть на странице 
 * описания http://www.oracle.com/technetwork/java/javase/documentation/index-137868.html#@since
 * 
 */
/**
 *	унифицированный класс работы с позицией
 *
 *	@author  	Алексей Капитонов
 *	@version 	12:33 17.12.2015
 */
class rtPositionUniversal extends Position_general_Class
{	
	public $user_id;
	public $user_access;
	public $position;
	function __construct($user_access = 0){
		$this->user_access($user_access);
		
		// подключаемся к базе
		$this->db();
		
		// получаем позицию
		
		
		$this->getPosition((isset($_GET['id']) && (int)$_GET['id']>0)?$_GET['id']:'0','id');
		$_GET['query_num'] = $this->position['query_num'] ;
		

		// получаем кириллическое название статуса
		$this->queryStatus = $this->get_query_status($this->position['status']);

		// передававться через ключ AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}			
	}

	/**
	 *  for change article
	 *	shearch article autocomlete
	 *
	 *	@author  	Alexey Kapitonov
	 *	@version 	12:29 23.03.2016
	 */
	protected function shearch_article_autocomlete_AJAX(){
		//RT_MAIN_ROWS
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `art` LIKE '%".$_POST['search']."%'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
		// $result = $mysqli->query($query)or die($mysqli->error);
		
		$articles_rows = array();
		$response = array(); 
		$object = array();

		$i=0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$object[$row['art']]['label'] = $row['art'].' '.$row['name'];
				$object[$row['art']]['value'] = $row['art'];

				$articles_rows[] = $row['art'];
				// $response[$i]['label'] = $row['art'].' '.$row['name'];
				// $response[$i++]['value'] = $row['art'];
				
			}
		}		
		
		$query = "SELECT * FROM `".IMAGES_TBL."` WHERE `size` = 'small' AND `art` IN ('".implode("','", $articles_rows)."') GROUP BY art ASC";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){

				$object[$row['art']]['img'] = $row['name'];
			}
		}

		// echo $query.'<pre>';
		// print_r($object);
		// echo '</pre>';
			
		foreach ($object as $key => $value) {
			$response[$i]['label'] = $value['label'];
			$response[$i]['value'] = $value['value'];
			if(isset($value['img'])){
				$response[$i++]['img'] = 'img/'.$value['img'];	
			}else{
				$response[$i++]['img'] = 'img/no_image.jpg';
			}
			
		}









		echo json_encode($response);
		exit;
	}

	/**
	 *	
	 *
	 *	@author  	Alexey Kapitonov
	 *	@version 	17:51 22.03.2016
	 */
	protected function search_and_replace_article_AJAX(){
		//RT_MAIN_ROWS
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `art` = '".$_POST['art']."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
		$artArr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$artArr[] = $row;
			}
		}

		if(count($artArr) > 0){
			foreach ($artArr as $art) {
				$query = "UPDATE `".RT_MAIN_ROWS."` SET";
		        $query .= " `art` = '".$art['art']."'";
		        $query .= " , `art_id` = '".$art['id']."'";
		        $query .= " , `name` = '".$art['name']."'";
		        $query .= " WHERE `id` = '".(int)$_POST['row_id']."'";
		        $result = $this->mysqli->query($query) or die($this->mysqli->error);	
			}
			
			$option['timeout'] = '1000';
			$this->responseClass->addResponseFunction('window_reload',$option);
			$this->responseClass->addMessage('Артикул успешно обновлён','system_message');		
		}else{
			$this->responseClass->addResponseFunction('js_edit_article_replace_back');
			$this->responseClass->addMessage('Такого артикула нет в каталоге','error_message');		
		}
		
	}
	/**
	 *	возвращает статуы
	 *
	 *	@param 		query_num
	 *	@author  	Алексей Капитонов
	 *	@version 	12:09 12.01.2016
	 */
	public function get_query_status($query_num){
		include_once ('cabinet/cabinet_class.php');
		$Cabinet = new Cabinet();
		return $Cabinet->name_cirillic_status[$query_num];
	}
	// получаем права и id юзера
	public function user_access($user_access = 0){
		$this->user_id = $_SESSION['access']['user_id'];
		if(!isset($this->user_access)){
			if ($user_access != 0) {
				$this->user_access = $user_access;
			}else{
				$this->user_access = $this->get_user_access_Database_Int($this->user_id);
			}
			
		}
	}
	/**
	 *	для генерации отвта выделен класс responseClass()
	 *
	 *	метод имеет область видимости private 
	 *  НО должен быть protected, для этого необходимо произвести рефакторинг всех 
	 *  AJAX методов и преобразовать их ответы в соответствии с новыми правилами
	 *
	 *	@param name		method name width prefix _AJAX
	 *	@return  		string
	 *	@see 			{"respons","OK"}
	 *	@author  		Алексей Капитонов
	 *	@version 		12:16 17.12.2015
	 */
	protected function _AJAX_(){
		$method_AJAX = $_POST['AJAX'].'_AJAX';
		//echo $method_AJAX;exit;
		
		if(method_exists($this, $method_AJAX)){
			// подключаем файл с набором стандартных утилит 
			// AJAX, stdApl
			include_once __DIR__.'/../../../../libs/php/classes/aplStdClass.php';
			// создаем экземпляр обработчика
			$this->responseClass = new responseClass();
			// обращаемся непосредственно 
			$this->$method_AJAX();				
			// вывод ответа
			echo $this->responseClass->getResponse();					
			exit;
		}					
	}
	//////////////////////////
	//	AJAX
	//////////////////////////

		/**
		 *	save dop info from not catalogue products
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	14:44 22.03.2016
		 */
		protected function save_dop_info_json_AJAX(){
			 /*
                naimenovanie
                product_dop_text
                quantity
            */
			$this->jsonArr = json_decode(base64_decode($_POST['json']),true);
			// if($this->user_id == 42){
			// 	$message = $this->printArr($jsonArr);
			// 	$this->responseClass->addMessage($message,'system_message');	
			// 	// return;
			// }


			
			// запрашиваем данные из RT_MAIN_ROWS
			$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `id` = '".(int)$_POST['main_row_id']."';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$this->position = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->position[] = $row;
				}
			}

			foreach ($this->position as $position) {
				
				// переписываем название позиции
				if(isset($this->jsonArr['naimenovanie']) && $this->jsonArr['naimenovanie'] != $position['name']){
					// пишем вариант
					$query  = "UPDATE `".RT_MAIN_ROWS."` SET "; 
					$query .= "`name` = '".$this->jsonArr['naimenovanie']."' ";
					$query .= " WHERE  `id` = '".(int)$_POST['main_row_id']."';";
					$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				}

				// считываем варианты для всех позиций
				$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE `row_id` = '".(int)$_POST['main_row_id']."';";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				$this->position['variants'] = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->position['variants'][$row['id']] = $row;
					}
				}

				// $message = $this->printArr($position);
				// $this->responseClass->addMessage($message);	
				$i = 0;
				foreach ($this->position['variants'] as $key => $variant) {
					$json = json_decode($variant['no_cat_json'],true);

					$this->position['variants'][$key]['json_encode'] = json_decode($variant['no_cat_json'],true);
					

					if($json['naimenovanie'] != $this->jsonArr['naimenovanie'] || isset($json['product_dop_text']) && $json['product_dop_text'] != $this->jsonArr['product_dop_text']){
						if($_POST['row_id'] != $variant['id']){
							if($json['naimenovanie'] != $this->jsonArr['naimenovanie']){
								$json['naimenovanie'] = $this->jsonArr['naimenovanie'];
							}else{
								$json['product_dop_text'] = $this->jsonArr['product_dop_text'];
							}
							$this->position['variants'][$key]['json_new'] = json_encode($json,JSON_UNESCAPED_UNICODE);							
						}
						$i++;
					}
					if($_POST['row_id'] == $key){
						$this->position['variants'][$key]['json_new'] = base64_decode($_POST['json']);
						$i++;	
					}
				}



				// переписываем варианты
				if ($i>0) {
					foreach ($this->position['variants'] as $key => $variant) {
												if(isset($this->position['variants'][$key]['json_new'])){
							$query  = "UPDATE `".RT_DOP_DATA."` SET "; 
							$query .= "`no_cat_json` = '".$this->position['variants'][$key]['json_new'] ."' ";
							$query .= " WHERE  `id` = '".$key."';";
							// echo $query;
							$result = $this->mysqli->query($query) or die($this->mysqli->error);	
						}
							
						
					}
				}


			}

			

			
				
			// if($this->user_id != 42){
				$option['timeout'] = '1000';
				$this->responseClass->addResponseFunction('window_reload',$option);
			// }

			$this->responseClass->addMessage('Характеристики изделия успешно сохранены','successful_message');	
		}
		
		/**
		  *	изменение статуса варианта (светофор)
		  *
		  *	@param 		$_POST
		  *	@return  	json
		  *	@author  	Алексей Капитонов
		  *	@version 	04:40 10.01.2016
		  */
		protected function change_status_row_AJAX(){
			$id_in = $_POST['id_in'];
			if(trim($id_in)!=''){
				$color = $_POST['color'];
				
				$query  = "UPDATE `".RT_DOP_DATA."` SET `row_status` = '".$color."' WHERE  `id` IN (".$id_in.");";
				
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			}
			
			$js_function_name = ($color == 'red')?'variant_edit_lock':'variant_edit_unlock';
			$this->responseClass->addResponseFunction($js_function_name,$_POST);
			// $this->responseClass->addMessage($html);
			// echo '{"response":"OK","text":"test"}';
			// exit;
		}
		/**
		 *	сохраняем примечания к вариантам
		 *
		 *	@author  	Алексей Капитонов
		 *	@version 	16:11 15.01.2016
		 */
		protected function dop_men_text_save_AJAX(){
			$query  = "UPDATE `".RT_DOP_DATA."` SET `dop_men_text` = '".$_POST['value']."' WHERE  `id` = '".$_POST['row_id']."';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// $this->responseClass->addMessage($query);
		}
		/**
		 * 	окно редактирования названия услуги	
		 *
		 *  @author  	Alexey Kapitonov
		 *	@version 	15:07 09.02.2016
		 */
		function get_window_service_other_name_AJAX(){
			$html = '';
			unset($_POST['AJAX']);
			$html .= '<div id="js-edit-service-other-name">';
			$html .= '<input type="text" style="width:100%" name="new_other_name" value="'.base64_decode($_POST['html']).'">';
			$html .= '<input type="hidden" name="AJAX" value="save_service_other_name">';
			$options['width'] = '350px';
			$html .= '</div>';
			$this->responseClass->addPostWindow($html,"Введите название услуги",$options);
		}
		/**
		 *	сохранение альтернативного имени
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	15:21 09.02.2016
		 */
		function save_service_other_name_AJAX(){
			if(trim($_POST['new_other_name'])!= ""){
				$query  = "UPDATE `".RT_DOP_USLUGI."` SET";
				$query .= " `other_name` = '".addslashes(trim($_POST['new_other_name']))."'";
				$query .= " WHERE  `id` = '".(int)$_POST['id_row']."'";
					
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				
				$message = "Новое имя услуги сохранено";
				$this->responseClass->addMessage($message,'successful_message');	
				
				// вставка имени на стороне клиента
				$option['tr_id'] = $_POST['tr_id'];
				$option['html'] = $_POST['new_other_name'];
				$this->responseClass->addResponseFunction('change_service_name_in_html', $option);
				
			}
		}
		/**
		 *	получаем окно примечания к вариантам
		 *
		 *	@author  	Алексей Капитонов
		 *	@version 	16:11 15.01.2016
		 */
		protected function get_dop_men_text_save_AJAX(){
			$html = '';
			$query  = "SELECT * FROM `".RT_DOP_DATA."` WHERE  `id` = '".$_POST['row_id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			$variant = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$variant = $row;
				}
			}
			$html .= '<div class="dop_men_text">
					<span style="color:red"></span>
					<textarea data-href="'.(isset($_POST['href'])?$_POST['href']:'').'" data-id="'.$variant['id'].'">'.base64_decode($variant['dop_men_text']).'</textarea>
				</div>';
			$options['width'] = 600;
			$options['height'] = 290;
			$this->responseClass->addSimpleWindow($html,'Примечания к варианту',$options);
		}
		private function save_image_open_close_AJAX(){
			$query = "UPDATE `".RT_MAIN_ROWS."` SET";
	        $query .= "`show_img` = '".$_POST['val']."'";
	        $query .= "WHERE `id` = '".(int)$_POST['id_row']."'";
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
			//echo '{"response":"OK"}';
		}
		// сохранение входящей цены за услугу
		private function save_service_price_in_AJAX(){
			$query = "UPDATE ".RT_DOP_USLUGI." SET ";
			$query .= "`price_in`='".$_POST['price_in']."'"; 
			$query .= "WHERE `id`='".$_POST['dop_uslugi_id']."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}
		// сохранение наценки / скидки
		private function variant_save_discount_AJAX(){
			$query = "UPDATE ".RT_DOP_DATA." SET ";
			$query .= "`price_out`='".$_POST['price_out']."'"; 
			$query .= ", `discount`='".$_POST['discount']."'"; 
			$query .= "WHERE `id`='".$_POST['row_id']."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}
		// сохранение исходящей цены за услугу
		private function save_service_price_out_AJAX(){
			$query = "UPDATE ".RT_DOP_USLUGI." SET ";
			$query .= "`price_out`='".$_POST['price_out']."'"; 
			$query .= "WHERE `id`='".$_POST['dop_uslugi_id']."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}
		// сохранение входящей цены за товар
		private function save_price_in_out_for_one_price_AJAX(){
			$query = "UPDATE ".RT_DOP_DATA." SET ";
			$query .= "`price_in`='".$_POST['price_in']."'"; 
			// $query .= ", `price_out`='".$_POST['price_out']."' ";
			$query .= "WHERE `id`='".$_POST['dop_data']."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// echo '{"response":"OK"}';
			// exit;
		}
		// изменение в размерной сетке
		private function size_in_var_all_AJAX(){
			
			if (!isset($_POST['val']) ||
				!isset($_POST['key']) ||
				!isset($_POST['dop']) ||
				!isset($_POST['id'])) {
				
				$this->responseClass->response['response'] = 'OK';
				$this->responseClass->response['answer'] = 'size_table_has_not_been_saved';
				return;
			}
			$tir = $_POST['val']; // array / тиражи
			$key2 = $_POST['key']; // array / id _ row size
			$dop = $_POST['dop']; // array / запас
			$id = $_POST['id']; // array / id 
				//print_r($_POST['id']);exit;
			$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$id[0]."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
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
			$sum_tir = 0;
			$sum_zap = 0;
			foreach ($key2 as $key => $value) {
				//echo $value;
				$arr_json[$value]['dop'] = $dop[$key];
				$arr_json[$value]['tir'] = $tir[$key];
				$sum_zap += $dop[$key];
				$sum_tir += $tir[$key];
			}
			// $arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];
			//echo $r .'   -   ';
			//echo json_encode($arr_json);
			$query = "UPDATE `".RT_DOP_DATA."` SET ";
			// $query .= "`quantity` = '".$sum_tir."',";
			$query .= "`zapas` = '".$sum_zap."',";
			$query .= "`tirage_json` = '".json_encode($arr_json)."' ";
			$query .= "WHERE  `id` ='".$id[0]."'";	
			// // echo $query;			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// exit;
		}
		/**
		 *	копирует услуги варианта
		 *
		 *	@author  Алексей Капитонов
		 *	@version 11:30 18.12.2015
		 */
		private function copy_services_row_for_variant($dop_row_reference_id, $dop_row_new_id){
			$query = "SELECT * FROM  `".RT_DOP_USLUGI."`
					WHERE  `dop_row_id` ='".$dop_row_reference_id."'";  /// !!!! править тут !!!!
			$services_arr = array();
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$services_arr[] = $row;
				}
			}
			// echo $query;
			if(count($services_arr)>0){
				
				foreach ($services_arr as $key => $service) {
					$query = '';
					$query .= "INSERT INTO `".RT_DOP_USLUGI."` SET";
					$n = 0;
					foreach ($service as $name_column => $value) {
						if($name_column=="id"){continue;}
						if($name_column!='dop_row_id'){
							$query .= (($n>0)?',':'')." `".$name_column."`='".$value."' ";
						}else{
							$query .= (($n>0)?',':'')." `".$name_column."`='".$dop_row_new_id."' ";
						}
						$n++;
					}
					$query .= '; ';
					$result = $this->mysqli->query($query) or die($this->mysqli->error);
				}
				// echo $query;
				
			}
			return;
		}
		/**
		 *	создаёт копию варианта
		 *
		 *	@param 		$_POST['id'] - RT_DOP_DATA_ID
		 *	@param 		$_POST['row_id'] - RT_MAIN_ROWS_ID
		 *  @param 		$_POST['services'] - true / false (копировать/не копировать) услуги 
		 *	@return  	JSON
		 *	@author  	Алексей Капитонов
		 *	@version 	11:30 18.12.2015
		 */
		private function new_variant_AJAX(){
			$reference_id = (int)$_POST['id'];
			// собираем запрос, копируем строку в БД
			$query = "INSERT INTO `".RT_DOP_DATA."` 
			(row_id, quantity,zapas,price_in, price_out,discount,tirage_json,no_cat_json) 
			(SELECT row_id, quantity,zapas,price_in, price_out,discount,tirage_json ,no_cat_json
				FROM `".RT_DOP_DATA."` WHERE id = '".$reference_id."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// запоминаем новый id
			$insert_id = $this->mysqli->insert_id;
			if(isset($_POST['services']) && $_POST['services'] == 'true'){
				// копируем услуги
				$this->copy_services_row_for_variant($reference_id, $insert_id);
			}
			// узнаем количество строк
			$query = "SELECT COUNT( * ) AS `num`
					FROM  `".RT_DOP_DATA."`
					WHERE  `row_id` ='".$_POST['row_id']."'";  /// !!!! править тут !!!!
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$num_rows = $row['num'];
				}
			}
			
			// AJAX options
			$options['text'] = "test";
			$options['new_id'] = $insert_id;
			$options['num_row'] = ($num_rows-1);
			$options['num_row_for_name'] = "Вариант ".$num_rows;
			// AJAX options prepare
			$this->responseClass->addResponseOptions($options);			
		}
	
	public function getPosition($id = 0,$type = 'id'){
		if(empty($this->position)){
			if($type == 'id'){
				$this->position = $this->getPositionDatabase($id);		
			}else{
				$this->position = $this->getPositionDatabaseQN($id);		
			}
			
		}
		return $this->position;
	}
	// получаем дополнительный параметры по артикулу
	public function getDopParams($art_id){
		// выгружает данные запроса в массив
		$query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	/**
	 * возвращает размерную сетку
	 *
	 *	@param dop_params_arr	массив размеров (строк доп. параметров) из базы
	 *	@param variant 			информация по варианту расчёта из базы
	 *	@return  				HTML размерная сетка
	 *	@author  				Алексей Капитонов
	 *	@version 				12:34 17.12.2015
	 */	
	public function getSizeTable($dop_params_arr, $variant){
		// преобразует массив дополнительных параметров в таблицу размеров
		// выборка данных о введённых ранее размерах из строки JSON 
		$tirage_json = json_decode($variant['tirage_json'], true);
		$html = "";
		if(count($dop_params_arr)==0){
			if($this->user_access == 1){
				//$html = "Дополнительная информация отсутствует.";
			}
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
		//	$pod_zakaz = 0;
		/*
		 *	Для версии лайт этот флаг везде 1
		 */
		$pod_zakaz = 1;
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
						<td><input type="text" data-dop="tir" data-var_id="'.$variant['id'].'" class="val_tirage'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value.'" '.$rearonly.'></td>
						<td><input type="text" data-dop="dop" data-var_id="'.$variant['id'].'" class="val_tirage_dop'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value_dop.'" '.$rearonly.'></td>
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
	// получаем характеристики изделия
	public function getCharacteristics(){
		$html = '';
		if($this->position['type'] == 'cat'){
			$this->get_all_info($this->position['art_id']);
			// ссылка на сайт 
			$link_of_the_site = '<a target="_blank" href="http://'.$_SERVER['HTTP_HOST'].'/description/'.$this->position['art_id'].'">APL</a>';
			ob_start();
			include_once __DIR__.'/../../../skins/tpl/client_folder/rt_position/characteristics_cat.tpl';
			$html = ob_get_contents();
			ob_get_clean();
			
		}else{
			$html .= '<strong>Характеристики изделия:</strong>';
			$html .= '<div id="js--characteristics-info" data-main_row_id="'.$this->position['id'].'">контент описания</div>';
			$html .= '<table>';
				$html .= '<tr>';
					$html .= '<td>Резерв</td>';
					$html .= '<td><input type="text" id="rezerv_save" data-id="'.$this->position['id'].'" value="'.base64_decode($this->position['number_rezerv']).'" placeholder="№ резерва"></td>';
				$html .= '</tr>';
			$html .= '</table>';
		}
		return $html;
	}
	// создаем экземпляр класса форм
	private function getForms(){
		if(!isset($this->FORM)){
			$this->FORM = new Forms;
		}
	}
	// описание товара некаталог
	public function variant_no_cat_json_Html($arr,$type_product){
		$this->getForms(); /*Экземпляр класса форм*/
		
		
		$html = '';
		// если у нас есть описание заявленного типа товара
		$type_product_arr_from_form = $this->FORM->get_names_form_type($this->type_product);
		if(isset($type_product_arr_from_form)){
			// $names = $type_product_arr_from_form; // массив описания хранится в классе форм
			$html .= '<div class="table inform_for_variant">';
			
			foreach ($arr as $key => $value) {
				$html .= '<div class="row">';
					$html .='<div class="cell" style="text-align:left">'.(isset($type_product_arr_from_form[$key]['name_ru'])?$type_product_arr_from_form[$key]['name_ru']:'<span style="color:red">имя не найдено</span>').'</div>';
						$html .='<div class="cell" style="text-align:left" data-type="'.$key.'" >';
							$html .= $value;
						$html .='</div>';
				$html .='</div>';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}
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
	/**
	 *	детальная информация по артикулу
	 *
	 *	@author  Алексей Капитонов
	 *	@version %TIME
	 */
	public function get_all_info($art_id){
		$this->color = $this->get_color($art_id);		
		$this->material = $this->get_material($art_id);
		$this->get_print_mode = $this->get_print_mode($art_id);
		// global $mysqli;
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `id` = '".(int)$art_id."'";
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;				
			}
		}
	}
	// цвета
	private function get_color($art_id){
		// выгружает данные запроса в массив
		$query = "SELECT * FROM `".BASE_COLORS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['color'];
			}
		}
		return $arr;
	}
	// материал
	private function get_material($art_id){
		// выгружает данные запроса в массив
		$query = "SELECT * FROM `".BASE_MATERIALS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['material'];
			}
		}
		return $arr;
	}
	// запрос видов печати
	private function get_print_mode($art_id){
		// выгружает данные запроса в массив
		$query = "SELECT * FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
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
	
	// получаем строку РТ(позицию) из базы
	private function getPositionDatabase($id){	
		// чеерез get параметр id мы получаем id 1 из строк запроса
		// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
		$query = "SELECT `".RT_LIST."`.*,`".RT_LIST."`.`id` AS `RT_LIST_ID`, `".RT_MAIN_ROWS."`.*, DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`
		  FROM `".RT_MAIN_ROWS."`
		  INNER JOIN `".RT_LIST."`
		  ON `".RT_LIST."`.`query_num` = `".RT_MAIN_ROWS."`.`query_num`
		   WHERE `".RT_MAIN_ROWS."`.`id` = '".$id."'";
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		
		$position = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$position = $row;
			}
		}
		return $position;
	}

	static function get_query($query_num){
		global $mysqli;
		// чеерез get параметр id мы получаем id 1 из строк запроса
		// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
		$query = "SELECT `".RT_LIST."`.*,DATE_FORMAT(create_time,'%d.%m.%Y %H:%i:%s') as `date_create`
		  FROM `".RT_LIST."`
		   WHERE `".RT_LIST."`.`query_num` = '".$query_num."'";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		
		$position = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$position = $row;
			}
		}
		return $position;
	}

	// получаем строку РТ(позицию) из базы
	private function getPositionDatabaseQN($query_num){	
		// чеерез get параметр id мы получаем id 1 из строк запроса
		// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
		$query = "SELECT `".RT_LIST."`.*,`".RT_LIST."`.`id` AS `RT_LIST_ID`, `".RT_MAIN_ROWS."`.*, DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`
		  FROM `".RT_MAIN_ROWS."`
		  INNER JOIN `".RT_LIST."`
		  ON `".RT_LIST."`.`query_num` = `".RT_MAIN_ROWS."`.`query_num`
		   WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$query_num."'";
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		
		$position = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$position = $row;
			}
		}
		return $position;
	}
	public function getVariants($position_id){		
		$this->Variants =  new Variants($position_id);
		return $this->Variants->getVariantsDatabase($position_id);
	}
	// получаем изображения
	public function getImage(){		
		$this->images =  new Images();
		return $this->images->getImageHtml();
	}
	// покдключение к базе
	// в дальнейшем подключим по уму
	protected function db(){
		if(!isset($this->mysqli)){
			global $mysqli;
			$this->mysqli = $mysqli;	
		}		
	}
}
/**
 *	класс расширение для добавления модуля изображений
 *
 *	@author  Алексей Капитонов
 *	@version 10:29 17.12.2015
 */
class Images extends rtPositionUniversal
{	
	function __construct($id = 0){
		// подключаем базу на всякий
		$this->db();
	}
	// проверка на наличие изображений
	private function checkImageExist(){
		return true;
	}
	//функция вывода вариантов цветов, при нали, при кол-ве цветов более 6 - выводим стрелки прокрутки
	private function color_variants_to_html($color_variants){
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
	private function find_matches($art,$pattern){
		// global $db;
		//выбираем id артикулов соответсвующих патерну
		$query = "SELECT id FROM `".BASE_TBL."` WHERE art != '".$art."' AND SUBSTRING(art,1,".strlen($pattern).")='".$pattern."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$itog_ids_arr[] = $row['id'];			
			}
		}
		//отсекаем те артикулы которые у которых нулевые остатки и цена
		$arr15 = (isset($itog_ids_arr))?implode("','",$itog_ids_arr):'';
		$query = "SELECT art_id FROM `".BASE_DOP_PARAMS_TBL."` WHERE ( ostatok + on_way ) >= '0' AND price > '0' AND art_id IN('".$arr15."') GROUP BY art_id";
			
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$itog_ids_arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$itog_ids_arr[] = $row['art_id'];	
			}
		}
			
		//отсекаем те артикулы которые лежат в скрытых категориях
		$arr15 = implode("','",$itog_ids_arr);
		$query = "SELECT rel.article_id article_id, rel.category_id category_id
		           FROM `".BASE_ARTS_CATS_RELATION."` rel 
				   INNER JOIN `".GIFTS_MENU_TBL."` menu
				   ON  rel.category_id = menu.id
		           WHERE menu.hide != '1' AND rel.article_id IN('".$arr15."') ORDER BY rel.category_id ASC LIMIT 0,15";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$itog_ids_arr = array();
			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				// отказался от такого подхода  $hiden_cat_begining = get_menu_item_id(BEGINING_HIDEN_MENU_CATS);
				// отказался от такого подхода  if((int)$item['category_id'] >= (int)$hiden_cat_begining['id']) break;
			    $itog_ids_arr[] = $row['article_id'];
			}
		}
		//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
			
			
		//получаем изображения артикулов
		$arr15 = implode("','",$itog_ids_arr);
		$query = "SELECT base.id id,  base.art art, images.name name FROM `".BASE_TBL."` base
		          INNER JOIN `".IMAGES_TBL."` images 
				  ON  base.art = images.art  WHERE size = 'small' AND base.id IN('".$arr15."') GROUP BY  base.id ORDER BY images.id ASC";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$output[] = array('id'=>$row['id'],'art'=>$row['art'],'img'=>$row['name']);
			}
		}
		//echo '<pre>';print_r($output);echo '</pre>';
		return  (isset($output))?$output:'';			
	}
	// получаем другие цвета по артикулу
	private function get_art_color_variants($art){		
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
			
		return $this->find_matches($art,$matches[1]);	
	}
	/**
	 *	старые функции 
	 *  перенесено из new_veiw.php
	 *	проверка на существование изображения
	 *
	 *  @return     путь до изображения
	 *	@author  	Андрей
	 *	@version 	17:27 14.12.2015
	 */
	private function checkImgExists($path,$no_image_name = NULL ){
	    $mime = getExtension($path);
		
		// если вдруг есть пробел заменяем его на '%20'
		if(strpos($path,' ') !== false){
		   $path = str_replace(' ','%20',$path);
		}
		if(@fopen($path, 'r')){//file_exists
			$img_src = $path;	
		}
		else{
		    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image';
			$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name.'.'.$mime;
		} 
		return $img_src;
	}
	/**
 	 *	получает изображения для артикула
 	 *  КОПИЯ ИЗ rt_KpGallery.class.php
 	 *
 	 *	@param 		$art - артикул
 	 *	@return  	array( index => image_name)
 	 *	@author  	Алексей Капитонов
 	 *	@version 	16:23 22.12.2015
 	 */
 	private function getBigImagesForArt($art_id){
 		// объявляем массив изображений
 		$img = array();
		if(trim($art_id) > 0){
 			$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art_id='".$art_id."' ORDER BY id";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$img[] = $row['name'];
				}
			}
 		}
 		
		//	если изображений для артикула нет
		//  или артикул не существует
		if(count($img) == 0){
			$img[] = 'no_image.jpg';
		}
		return $img;
 	}
 	private function getSmallImagesForArt($art_id){
 		// объявляем массив изображений
 		$img = array();
		if(trim($art_id) > 0){
 			$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'small' AND art_id='".$art_id."' ORDER BY id";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$img[] = $row['name'];
				}
			}
 		}
 		
		//	если изображений для артикула нет
		//  или артикул не существует
		if(count($img) == 0){
			$img[] = 'no_image.jpg';
		}
		return $img;
 	}
 	/**
 	 *	возвращает карусель превью изображений для карточки артикула
	 *  
	 *	@param 		big_images 
	 *	@param 		small_images
	 *  @return  	html
	 *	@author  	Алексей Капитонов
	 *	@version 	22:28 29.12.2015
	 */
 	private function find_checked_img(){
 		return true;
 	}
 	// определяет по имени файлпа выбран он или нет
 	protected function copare_and_calculate_checked_files($rt_main_row_id, $file_name){
 		if (!isset($this->checked_IMG)) {
 			$this->checked_IMG = $this->getCheckedImg($rt_main_row_id);
 		}
		foreach ($this->checked_IMG as $key => $value) {
			if($file_name == $value['img_name']){
				return "checked";
			}
		}
		return "";
	}
	// собираем загруженные изображения
	protected function getCheckedImg($rt_main_row_id){
		$query = "SELECT * FROM `".RT_MAIN_ROWS_GALLERY."` WHERE parent_id = ".(int)$rt_main_row_id.";";
	 	$arr = array();
 		$result = $this->mysqli->query($query) or die($this->mysqli->error);
 				
 		if($result->num_rows > 0){
			// echo $result->num_rows;
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}					
		return $arr;
	}
 	/**
	 *	возвращает карусель превью изображений для карточки артикула
	 *  
	 *	@param 		articul
	 *  @return  	array()
	 *	@author  	Алексей Капитонов
	 *	@version 	17:27 14.12.2015
	 */
	public function fetchImagesForArt($art,$rt_main_row_id){
		if (!isset($this->checked_IMG)) {
 			$this->checked_IMG = $this->getCheckedImg($rt_main_row_id);
 		}
		// return '';
		$b = count($this->checked_IMG)+1;
		$c = count($this->checked_IMG);
		// if($art && $art > 0){
			
		$big_images = $this->getBigImagesForArt($art);
		$small_images = $this->getSmallImagesForArt($art);
		
		$first_img= '';
		$main_img_src = '';
		// echo '<br><br><br><br><br><br><br><br><br><br><br><br> ';
		// изображения с сайта
		foreach ($small_images as $key => $img) {
			// удаление изображений для админов
			$deleting_img = '';
			if($img != 'no_image.jpg' && isset($_SESSION['access']['access']) && 
				$_SESSION['access']['access']== 1){
				$deleting_img = '<div class="catalog_delete_img_link">
				<a 
				href="#" 
				title="удалить изображение из базы" 
				data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$big_images[$key].'|'.$img.'" 
				onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a>
				</div>';
			}
			
			if ($this->copare_and_calculate_checked_files($rt_main_row_id,$big_images[$key]) == "checked"){
				
				if(!isset($main_img_src)) {
					$main_img_src = $this->checkImgExists( APELBURG_HOST.'/img/'.$big_images[$key]);
				}
				$previews_block[$c]  = '<div  class="carousel-block kp_checked">';
				$previews_block[$c] .= '<img class="articulusImagesMiniImg imagePr" alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$img).'" data-file="'.$big_images[$key].'" data-src_IMG_link="'.APELBURG_HOST.'/img/'.$big_images[$key].'">';
				$previews_block[$c] .= $deleting_img;
				$previews_block[$c--] .= '</div>';
			}else{
				if($b == 1) {
					// echo 'test';
					$main_img_src = $this->checkImgExists( APELBURG_HOST.'/img/'.$big_images[$key]);
				}
				// echo 'test'.$b.'654';
				$previews_block[$b]  = '<div  class="carousel-block">';
				$previews_block[$b] .= '<img class="articulusImagesMiniImg imagePr" alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$img).'" data-file="'.$big_images[$key].'" data-src_IMG_link="'.APELBURG_HOST.'/img/'.$big_images[$key].'">';
				$previews_block[$b] .= $deleting_img;
				$previews_block[$b++] .= '</div>';
			}			
			
		}
		//////////////////////////
		//	Загруженные изображения
		//////////////////////////
			$upload_dir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$this->position['img_folder'].'/';
			$global_link_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$this->position['img_folder'].'/';
			// если директория (папка) существует
			if($this->position['img_folder'] != '' && is_dir($upload_dir)){			
				// сканируем директории.		
				$files = scandir($upload_dir);
				// перебираем содержимое директории
				for ($i = 0; $i < count($files); $i++) { // Перебираем все файлы
				    if (($files[$i] == ".") || ($files[$i] == "..")) { // Текущий каталог и родительский пропускаем
				    	continue;
					}
					
					
					if($this->copare_and_calculate_checked_files($rt_main_row_id,$files[$i]) == "checked"){
						if(!isset($main_img_src)) {
							$main_img_src = $this->checkImgExists( $global_link_dir.''.$files[$i] );
						}
						$main_img_src = $this->checkImgExists( $global_link_dir.''.$files[$i] );
						$previews_block[$c] = '<div  class="carousel-block kp_checked">';
						$previews_block[$c] .= '<img class="articulusImagesMiniImg imagePr" alt="" data-file="'.$files[$i].'"  src="'.checkImgExists($global_link_dir.''.$files[$i]).'" data-src_IMG_link="'.$global_link_dir.''.$files[$i].'">';
						$previews_block[$c--] .= '</div>';
					}else{
						if($b == 1) {
							$main_img_src = $this->checkImgExists( $global_link_dir.''.$files[$i] );
						}
						$previews_block[$b]  = '<div  class="carousel-block">';
						$previews_block[$b] .= '<img class="articulusImagesMiniImg imagePr" alt="" data-file="'.$files[$i].'"  src="'.checkImgExists($global_link_dir.''.$files[$i]).'" data-src_IMG_link="'.$global_link_dir.''.$files[$i].'">';
						$previews_block[$b++] .= '</div>';
					}
					
				}
			}		
		// echo '<pre>';
		// print_r($previews_block);
		// echo '</pre>';
			// echo '*** '.$main_img_src.'***';
		if (isset($previews_block)) {
			
			ksort($previews_block);
			$string	= implode('',$previews_block);
		}else{
			$previews_block = array();
			$string	= '';
		}
		
		
		$html = '<div class="carousel shadow" style="">'.PHP_EOL;
		$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s2.png)"></a>'.PHP_EOL:'';
		$html .= '<div class="carousel-wrapper">'.PHP_EOL;
		$html .= '<div class="carousel-items">'.PHP_EOL;	
		$html .= $first_img.$string;
		$html .= '</div>'.PHP_EOL;
		$html .= '</div>'.PHP_EOL;
		$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s22.png); float:right; margin-top:-70px"></a>'.PHP_EOL:'';
		$html .= '</div>'.PHP_EOL;
		$html .= '<div id="image_add">Загрузить ещё</div>'.PHP_EOL;
		$previews_block = $html;
		
		return array('main_img_src' => $main_img_src,
			'previews_block' => $previews_block);
	}
	// вывод блока изображений
	public function getImageHtml(){
		parent::__construct();
		// шаблон изображений
			
		$this->color_variants_block = '';			
		// проверяем наличие других цветов по артикулу для КАТАЛОГА
		if($this->position['type'] == 'cat'){
			if($color_variants = $this->get_art_color_variants($this->position['art'])){
				$this->color_variants_block = $this->color_variants_to_html($color_variants);	
			}	
		}
		
		ob_start();
		$images_data = $this->fetchImagesForArt($this->position['art_id'],$this->position['id']);
		// echo '*********6546546546545 432132165456**********';
		// echo '<pre>';
		// print_r($images_data);
		// echo '</pre>';
		// echo '<pre>';
		// print_r($this->checked_IMG);
		// echo '</pre>';
		// echo '*********6546546546545 432132165456**********';
		include_once __DIR__.'/../../../skins/tpl/client_folder/rt_position/images_block.tpl';
		$content = ob_get_contents();
		ob_get_clean();
		return $content;
	}
}
/**
 *	класс работы с вариантами прикрепленнными к услуге
 *
 *	@param 		$id (id_main_row)
 *	@author  	Алексей Капитонов
 *	@version 	15:54 15.12.2015
 */
class Variants extends rtPositionUniversal
{
	public $Variants = array(); // варианты расчета
	function __construct($id = 0){
		// если $id не указан или равен 0 - выходим
		if($id == 0){
			return;
		}
		// подключаемся к базе
		$this->db();
	}
	// выводит общую информацию по ВАРИАНТУ из json
	public function variant_decode_params_json_to_Html($JSON_str,$type_product){
		$arr = json_decode($JSON_str, true);
		// return $JSON_str;
		/*Экземпляр класса форм*/
		if (!isset($this->FORM)) {
			include_once __DIR__.'/os_form_class.php';
			$this->FORM = new Forms;
		}
		
		
		$html = '';
		// если у нас есть описание заявленного типа товара
		$type_product_arr_from_form = $this->FORM->get_names_form_type($type_product);
		/*
		$arr  = array(
			'kew' => 'val',
			'kew' => 'val',
			'kew' => 'val',
			'kew' => 'val',
			'kew' => 'val'
			);
		*/
		
		// определяемся по колличеству колонок в таблице
		$tab = array();
		foreach ($arr as $key => $value) {
			if($key == 'quantity'){ continue; }
			$tab[] = array( $key => $value);
		}
		if(count($tab) >= 11){
			$j_st = 3;
		}else if(count($tab) >= 6){
			$j_st = 2;
		}else{
			$j_st = 1;
		}

		$html = '';
		if(isset($type_product_arr_from_form)){
			$html .='<table class="table inform_for_variant">';
			for($i = 0; $i < count($tab); $i) {
				$html .= '<tr>';
					for ($j = 1; $j <= $j_st; $j++) {			
						if(isset($tab[$i])){
							foreach ($tab[$i] as $key => $value) {
								$html .= '<th  style="text-align:left">'.(isset($type_product_arr_from_form[$key]['name_ru'])?$type_product_arr_from_form[$key]['name_ru'].':':'<span style="color:red">имя не найдено</span>').'</th>';
								$html .= '<td  style="text-align:left" data-type="'.$key.'" class="js--edit_true">'.strip_tags($value).'</td>';$i++;
							}	
						}else{
							$html .= '<th></th>';
							$html .= '<td></td>';$i++;
						}
					}
				$html .= '</tr>';
			}
			$html .= '</table>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}
	}
	// сортируем варианты по светофору
	public function get_variants_arr_sort_for_type($variants_arr){
		if(!isset($variants_arr_sort)){
			$variants_arr_sort = array();
			foreach ($variants_arr as $key => $variant) {
				$variants_arr_sort[$variant['row_status']] = $variant['id'];
			}
		}
		return $variants_arr_sort;
	}
	// возвращает Html вкладок для переключения вариантов расчёта
	public function generate_variants_menu($variants){		
		$html = ''; // контент функции
		
		$ch = 0; // счетчик количества выбранных элементов, может не больше одного
		
		
		$arr_for_type = $this->get_variants_arr_sort_for_type($variants);
		
		
		for ($i=0; $i < count($variants); $i++) { 
			$checked = ''; // имя класса для выбранного элемента
			$row_status = $variants[$i]['row_status'];
			// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
			if(!isset($_GET['show_archive']) && $row_status=='red'){ continue;}
			// если вариант выбран по ссылке через GET параметр
			if(isset($_GET['varID_checked']) && $_GET['varID_checked'] ==  $variants[$i]['id']){
				
			}
			if(isset($_GET['varID_checked']) && $_GET['varID_checked'] > 0){
				if($_GET['varID_checked'] == $variants[$i]['id']){
					$checked='checked';$ch++;	
					
				}		
			}else{
				switch ( $row_status ) {
					case 'sgreen':// не история - рабочий вариант расчёта
						if($ch < 1){$checked='checked';$ch++;}					
						break;
					case 'green':// не история - рабочий вариант расчёта
						if($ch < 1 && @count($arr_for_type['sgreen']) == 0){$checked='checked';$ch++;}					
						break;					
					case 'grey':// не история - вариант расчёта не учитывается в РТ
						if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0){$checked='checked';$ch++;}
						break;					
					default: // вариант расчёта red (архив), остальное не важно						
						if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0 && @count($arr_for_type['grey']) == 0){$checked='show_archive';$ch++;}
						break;
				}				
			}
			$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
		}
		return $html;
	}
	// получаем все варианты расчёта по данному артикулу
	public function getVariantsDatabase($id){
		// global $mysqli;
		$query = "SELECT `".RT_DOP_DATA."`.*, 
		DATE_FORMAT(shipping_date,'%d.%m.%Y') AS `shipping_date` 
		FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
		
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$variants = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$variants[] = $row;
			}
		}	
		$this->Variants = $variants;
		return $variants;
	}
	// возвращает услуги для варианта	
	public function getServices($id){
		// получаем изображения
		$this->Services =  new Services($id);
		return $this->Services->getServicesDatabase($id);	
	}
}
/**
 *	класс услуг прикрепленных к варианту
 *
 *	@param 		???
 *	@author  	Алексей Капитонов
 *	@version 	15:54 15.12.2015
 */
class Services extends Variants
{
	public $service;
	function __construct($id = 0){
		// если $id не указан или равен 0 - выходим
		if($id == 0){
			return;
		}
		// подключаемся к базе
		$this->db();
	}
	// получаем услуги по варианту расчета
	public function getServicesDatabase($id){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$id."' ORDER BY `glob_type` DESC";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$service_arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$service_arr[] = $row;
			}
		}
		return $service_arr;
	}
	
	// ВЫВОДИТ СПИСОК УСЛУГ ПРИКРЕПЛЁННЫХ ДЛЯ ВАРИАНТА
	// $NO_show_head добавлен как необязательная переменная для отключения вывода 
	// $pause - флаг запрета редактирования
	// названия группы услуги
	// public function uslugi_template_cat_Html($arr=array(), $NO_show_head = 0, $status_snab='', $pause=0, $edit_true=true){
	public function htmlTemplate($arr,$variant, $edit_true = true,$art_id){
		// запрос прав пользователя
		$this->user_access();
		// определяем редакторов для полей (html тегов)
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'
		
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
		// global $mysqli;
		$query = "SELECT `".OUR_USLUGI_LIST."`.`parent_id`,
		`".OUR_USLUGI_LIST."`.`tz`,
		`".OUR_USLUGI_LIST."`.`edit_pr_in`,
		`".OUR_USLUGI_LIST."`.`price_out`,
		`".OUR_USLUGI_LIST."`.`for_how`,
		`".OUR_USLUGI_LIST."`.`id`,
		`".OUR_USLUGI_LIST."`.`name`,
		`".OUR_USLUGI_LIST."`.`note`,
		`".OUR_USLUGI_LIST."_par`.`name` AS 'parent_name' 
		FROM ".OUR_USLUGI_LIST."
		inner join `".OUR_USLUGI_LIST."` AS `".OUR_USLUGI_LIST."_par` ON `".OUR_USLUGI_LIST."`.`parent_id`=`".OUR_USLUGI_LIST."_par`.`id` WHERE `".OUR_USLUGI_LIST."`.`id` IN (".$id_s.") ORDER BY  `os__our_uslugi_par`.`name` ASC ";
		// $query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id_s.")";
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);				
		$services_arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				foreach ($arr as $key => $value) {
					$services_arr[$row['id']] = $row;
				}
			}
		}
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/print_calculators_class.php");
		//foreach ($services_arr as $key => $service) {
			foreach ($arr as $key2 => $service_attach) {
				// исключение на ошибку в записи uslugi_id
				//if($service_attach['uslugi_id'] == 0){
					// $arr = json_decode($service_attach['print_details'],true);
					// if(isset($arr['dop_params']['YPriceParam'])){
					// 	$service_attach['uslugi_id']
					// }						
				//}
				// if($service_attach['uslugi_id']==$key){
					$quantity = ($service_attach['for_how']=="for_all")?1:$service_attach['quantity'];
					// цена за штуку
					$price_in = $service_attach['price_in'];
					$price_out = $service_attach['price_out'];
					if ($service_attach['discount'] != 0) {
						$price_out = $this->round_money((($price_out/100)*(100 + $service_attach['discount'])));
					}else{
						$price_out = $this->round_money( $price_out );
					}
					
					// цена за тираж
					$tir_price_in = $service_attach['price_in'] * $quantity;
					$tir_price_out = $price_out * $quantity;
					
					// прибыль за тираж
					$tir_pribl = $tir_price_out - $tir_price_in;
					
					$dop_inf = '';
					// запрет редактирования исходящей стоимости за 1 шт
					$discount_disabled_edit = $discount_disabled_edit_class = '';
					if((int)$service_attach['discount'] <> 0){
						$discount_disabled_edit = 'readonly';
						$discount_disabled_edit_class = "no_edit_class_disc";
					}
					
					// информация из калькулятора
					$calc_info = '';$calc_class= '';$calc_button='';$calculator_price_out='';
					$calc_tr_class = ''; $input_style = '';
					$td_calculator_price_out='';
					if(@$services_arr[$service_attach['uslugi_id']]['parent_id'] == 6){
						$calc_tr_class = 'calculator_row';
						$calc_class = ' service-calculator';
						$calc_button = '<div class="getCalculatorMethod" onclick="printCalculator.evoke_calculator_directly({art_id:'.$art_id.',dop_data_row_id:'.$variant['id'].',dop_uslugi_id:'.$service_attach['id'].'});"></div>';
						$calc_info = '';
						$calculator_price_out = 'readonly';
						$td_calculator_price_out = ' onclick="edit_calcPriceOut_readoly()" ';
						$input_style = 'style="border: 1px solid#fff;"';
						$discount_disabled_edit = $discount_disabled_edit_class = '';
					}
					
					// ТЗ кнопки
					$buttons_tz = (trim($service_attach['tz'])=='')?'<span class="tz_text_new"></span>':'<span class="tz_text_edit"></span>';
					$html .= '<tr id="editable_from_rtClass_'.$variant['id'].'_'.$service_attach['id'].'" class="calculate calculate_usl '.$calc_tr_class.'" data-dop_uslugi_id="'.$service_attach['id'].'" data-our_uslugi_id="'.@$services_arr[$service_attach['uslugi_id']]['id'].'" data-our_uslugi_parent_id="'.@trim($services_arr[$service_attach['uslugi_id']]['parent_id']).'"  data-for_how="'.@trim($services_arr[$service_attach['uslugi_id']]['for_how']).'">';
						$html .= '<td title="'.@$services_arr[$service_attach['uslugi_id']]['note'].'">';
							// класс для услуги "НЕТ В СПИСКЕ"
							if($service_attach['uslugi_id'] == 103){
								$calc_class .=  ' js-service-other-name';
							}
							$html .= '<div class="'.$calc_class.'" data-id="'.$service_attach['uslugi_id'].'" >';
								// кнопка для вызхова калькулятора
								$html .= $calc_button;
								// название услуги (калькулятора)
								if($service_attach['other_name'] != ""){
									$html .= $service_attach['other_name'];
								}else{
									if($service_attach['uslugi_id'] != 0){
										$html .= @$services_arr[$service_attach['uslugi_id']]['name'];	
									}else{
										// $html .= $this->printArr($service_attach);
										$print_datails = json_decode($service_attach['print_details'], true);
									
										if(isset($print_datails['print_type'])){
											$html .= ($print_datails['print_type']);
										}
										// $html .= Base64.decode(print_details.comment);
									}
	
								}
																
							$html .= '</div>';
							$string__a_new = base64_decode($service_attach['tz']);
							$string__a = mb_substr($string__a_new, 0, 80);
							if($string__a_new != $string__a)$string__a .= '...';

							$html .= '<div><span style="float:left" class="greyText">'.$string__a.'</span></div>';
						$html .= '</td>';
						
						// тираж
						
						if($service_attach['united_calculations']!=''){
							$html .= '<td class="service_group"  onClick="$(\'.open_service_center:visible\').click()">';
						}else{
						$html .= '<td>';
						}

						
						
						if($service_attach['for_how'] == 'for_all'){
							$html .= '<div class="greyText">';
								$html .= $quantity;
							$html .= '</div>';
						}else{
							$html .= $quantity;
						}
							
						$html .= '</td>';
						
						// входящая штука
						$html .= '<td class="row_tirage_in_gen uslugi_class price_in">';
							if(@$services_arr[$service_attach['uslugi_id']]['edit_pr_in'] != 0 || (isset($services_arr[$service_attach['uslugi_id']]['parent_id']) && $services_arr[$service_attach['uslugi_id']]['parent_id'] != 6 && ($this->user_id == 31 || $this->user_access == 1))){
								$html .= '<input type="text" value="'.$this->round_money($price_in).'">';
							}else{
								$html .= '<span>'.$this->round_money($price_in).'</span>';
							}							
						$html .= '</td>';
						
						// процент
						$html .= '<td  data-val="'.$service_attach['discount'].'"  data-id="'.$service_attach['id'].'" class="row_tirage_in_gen uslugi_class percent_usl">';
							$html .= '<span>';
								$html .= $service_attach['discount'];
							$html .= '%</span>';
							if($discount_disabled_edit!=''){
								$html .= '<span class="greyText">'.$this->round_money($service_attach['price_out']).'</span>';
							}
						$html .= '</td>';
						
						// исходящая (штука)
						$html .= '<td '.$td_calculator_price_out.' class="row_price_out_gen uslugi_class price_out_men '.$discount_disabled_edit_class.'" >';
							
							$html .= '<input '.$input_style.' type="text" '.$calculator_price_out.' value="'.$this->round_money($price_out).'" '.$discount_disabled_edit.'>';
						$html .= '</td>';
						
						// исходащая / входящая (сумма)
						// $html .= '<td>';
							// сумма исх / вход
							$price_out_summ_out = $this->round_money($quantity * $price_out);
							$price_out_summ_in = $this->round_money($quantity * $price_in);
						
						$html .= '<td class="price_out_summ for_out" data-for_in="'.$price_out_summ_in.'" 
						data-for_out="'.$price_out_summ_out.'">';
							$html .= '<span class="for_out">';
								$html .= $price_out_summ_out;
							$html .= '</span>';
							$html .= '<span class="for_in">';
								$html .= $price_out_summ_in;
							$html .= '</span>';
						$html .= '</td>';
						
						// маржа
						$html .= '<td class="row_pribl_out_gen uslugi_class pribl">';
							$html .= '<span>'.$this->round_money($tir_pribl).'</span>';
						$html .= '</td>';
						
						// кнопка ТЗ
						$html .= '<td class="usl_tz">'.$buttons_tz.'<span class="tz_text">'.base64_decode($service_attach['tz']).'</span><span class="tz_text_shablon">'.@$services_arr[$service_attach['uslugi_id']]['tz'].'</span></td>';
						// кнопка удаления услуги (только для автора услуги)
						$html .= ($this->user_id == $service_attach['creator_id'] || $this->user_access == 1 )?'<td class="usl_del"><span class="del_row_variants"></span></td>':'<td></td>';
					$html .='</tr>';
				// }
			}
		//}
		return $html;
	}
}
?>