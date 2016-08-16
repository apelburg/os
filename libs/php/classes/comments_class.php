<?php
/*
  Классы наследуют друг друга по очередности работы с ними
  класс работы с запросами не наследует ничего
  класс работы с заказами подрузомевает доступ (по необходимости по отдельному запросу из формы) к истории переписки по запросу
  класс работы с позициями подрузомевает доступ к комментариям заказа и запроса
*/


// класс комментариев к запросу
class Comments_for_query_class{
	// id пользователя
	private $user_id;

	function __construct($user_access = 0){
		$this->user_id = (isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0);
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	static function check_the_empty_query_coment_Database($os__rt_list_query_num){
		global $mysqli;
		$query = "SELECT count(*) AS `count` FROM `".RT_LIST_COMMENTS."` WHERE `query_num` = '".$os__rt_list_query_num."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		$count = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		if($count>0){
			return 'no_empty';
		}else{
			return '';
		}
	}

	
	##################################################
	####### 	ВЫЗОВ ФУНКЦИЙ AJAX start    	######
	##################################################

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}

	private function add_new_comment_for_query_AJAX(){
			$this->save_query_comment_Database();
			$html = '<div class="comment table">';
					$html .= '<div class="row">';
						$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
						$html .= '</div>';
						$html .= '<div class="cell comment_text">';
						$html .= '<div>'.$_POST['comment_text'].'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}
	// сохранение комментария к запросу
	private function save_query_comment_Database(){		
		$this->save_query_comment((int)$_POST['id'], (int)$_POST['query_num'], $_POST['name'], $_POST['comment_text']);
	}



	// сохранение комментария к заказу
	public function save_order_comment_Pub($id, $order_num, $name, $text){
		$this->save_order_comment($id, $order_num, $name, $text);		
	}
	// сохранение комментария к заказу
	public function save_query_comment_Pub($id, $query_num, $name, $text){
		$this->save_query_comment($id, $query_num, $name, $text);		
	}

	

	// сохранение комментария к запросу
	private function save_query_comment($id, $query_num, $name, $text){
		global $mysqli;
		$query ="INSERT INTO `".RT_LIST_COMMENTS."` SET
	             `user_id` = '".$id."',
	             `query_num` = '".$query_num."',
	             `user_name` = '".$name."',
	             `comment_text` = '".$text."',
	            `create_time` = NOW()";
		$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}

	private function get_comment_for_query_AJAX(){
		$html = '';
		$html .= $this->get_comment_for_query();
		$html .= $this->get_the_comment_for_query_form();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';

	}

	// получаем все комментарии по запросу
	protected function get_comment_for_query(){
		global $mysqli;
		$html = '';	
		$comments = array();
		$query = "SELECT `".RT_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".RT_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".RT_LIST_COMMENTS."`  WHERE `query_num` = '".(int)$_POST['query_num']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}		

		$html .= '<div class="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name">'.$value['user_name'].'</div>';
					$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<div>'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		}
		return $html;
	}

	// собираем форму отправки нового комментария по запросу
	private function get_the_comment_for_query_form(){
		// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';
		
		$html = '';
		$html .= '<form>';
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
						
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
							$html .= '<textarea name="comment_text"></textarea>';
							$html .= '<div class="div_for_button">';
								// $html .= '<button class="add_nah">ОК</button>';
								// $html .= '<button class="add_nah">Принял</button>';
								$html .= '<button id="add_new_comment_button">Сохранить</button>';
							$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
			$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_query"></input>';
			$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
			$html .= '<input name="query_num" type="hidden" value="'.$_POST['query_num'].'"></input>';
		$html .= '</form>';
		$html .= '</div>';
		return $html;
	}

	##################################################
	#######     	ВЫЗОВ ФУНКЦИЙ AJAX end    	#####
	##################################################
}

// класс комментариев к заказу
class Comments_for_order_class extends Comments_for_query_class{
	// id пользователя
	private $user_id;

	function __construct(){
		$this->user_id = $_SESSION['access']['user_id'];
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	static function check_the_empty_order_coment_Database($os__cab_list_order_num){
		global $mysqli;
		$query = "SELECT count(*) AS `count` FROM `".CAB_LIST_COMMENTS."` WHERE `order_num` = '".$os__cab_list_order_num."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		$count = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		if($count>0){
			return 'no_empty';
		}else{
			return '';
		}
	}


	##################################################
	####### 	ВЫЗОВ ФУНКЦИЙ AJAX start    	######
	##################################################

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}

	private function add_new_comment_for_order_AJAX(){
			$this->save_order_comment_Database();
			$html = '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
						$html .= '<div class="create_time_message">'.$_POST['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}

	// сохранение комментария к заказу
	private function save_order_comment_Database(){
		$this->save_order_comment((int)$_POST['id'], (int)$_POST['order_num'], $_POST['name'], $_POST['comment_text']);		
	}
	// сохранение комментария к заказу
	private function save_order_comment($id, $order_num, $name, $text){
		global $mysqli;
		$query ="INSERT INTO `".CAB_LIST_COMMENTS."` SET
	             `user_id` = '".$id."',
	             `order_num` = '".$order_num."',
	             `user_name` = '".$name."',
	             `comment_text` = '".$text."',
	            `create_time` = NOW()";
			$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}


	private function get_comment_for_order_AJAX(){
		$html = '';	
		$html .= '<div class="add_new_comment">';
			$html .= '<div id="add_comments_of_query" data-query_num="'.$_POST['query_num'].'">переписка по запросу</div>';
		$html .= '</div>';
		$html .= $this->get_comment_for_order();
		$html .= $this->get_the_comment_for_order_form();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';		
	}

	private function get_comment_fo_query_without_form_AJAX(){
		$html = $this->get_comment_for_query();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}

	// получаем все комментарии по заказу
	protected function get_comment_for_order(){
		global $mysqli;
		$html = '';	
		$comments = array();
		$query = "SELECT `".CAB_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".CAB_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".CAB_LIST_COMMENTS."`  WHERE `order_num` = '".(int)$_POST['order_num']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}
		

		$html .= '<div class="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$value['user_name'].'</div>';
						$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
						$html .= '<div>'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		
		}
		$html .= '</div>';
		return $html;
	}

	// собираем форму отправки нового комментария по заказу
	private function get_the_comment_for_order_form(){
		// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';


		$html = '';
		$html .= '<div class="add_new_comment">';
		$html .= '<form>';
		$html .= '<div class="comment table">';
			$html .= '<div class="row">';
				$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
					
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<textarea name="comment_text"></textarea>';
					$html .= '<div class="div_for_button">';
						// $html .= '<button class="add_nah">ОК</button>';
						// $html .= '<button class="add_nah">Принял</button>';
						$html .= '<button id="add_new_comment_button">Сохранить</button>';
					$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
			$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_order"></input>';
			$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
			$html .= '<input name="order_num" type="hidden" value="'.$_POST['order_num'].'"></input>';
		$html .= '</form>';
		$html .= '</div>';
		return $html;
	}


	##################################################
	#######     	ВЫЗОВ ФУНКЦИЙ AJAX end    	######
	##################################################
}

// класс комментариев к позициям заказа
class Comments_for_order_dop_data_class extends Comments_for_order_class{
	//CAB_DOP_DATA_LIST_COMMENTS
	// id пользователя
	private $user_id;

	function __construct(){
		$this->user_id = $_SESSION['access']['user_id'];
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	static function check_the_empty_position_coment_Database($os__cab_orders_dop_data_id){
		global $mysqli;
		$query = "SELECT count(*) AS `count` FROM `".CAB_DOP_DATA_LIST_COMMENTS."` WHERE `position_id` = '".$os__cab_orders_dop_data_id."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		$count = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		if($count>0){
			return 'no_empty';
		}else{
			return '';
		}
	}


	##################################################
	####### 	ВЫЗОВ ФУНКЦИЙ AJAX start    	######
	##################################################

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}

	private function add_new_comment_for_position_AJAX(){
		// echo 'comment ok';exit;
			$this->save_position_comment_Database();
			$html = '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
						$html .= '<div class="create_time_message">'.$_POST['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}
	
	private function save_position_comment_Database(){
		global $mysqli;
		$query ="INSERT INTO `".CAB_DOP_DATA_LIST_COMMENTS."` SET
	             `user_id` = '".(int)$_POST['id']."',
	             `position_id` = '".(int)$_POST['position_id']."',
	             `user_name` = '".$_POST['name']."',
	             `comment_text` = '".$_POST['comment_text']."',
	            `create_time` = NOW()";
			$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}

	// вывод комментариев позиции
	// Комментарии по позиции показаны
	private function get_comment_for_position_AJAX(){
		$html = $this->get_comment_for_position();
		$html .= $this->get_the_comment_for_position_form();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';		
	}



	// вывод комментариев позиции для сторонних классов 
	// Комментарии по позиции показаны
	public function get_comment_for_position_without_Out_Open(){		
		$html = '';	
		$html .= $this->get_comment_for_position();
		$html .= $this->get_the_comment_for_position_form();
		return $html;
	}

	// вывод комментариев позиции для сторонних классов 
	// Комментарии по позиции скрыты
	public function get_comment_for_position_without_Out(){		
		$html = '';	
		// показываем кнопку если по запросу есть комменты
		if(self::check_the_empty_query_coment_Database($_POST['query_num'])){
			$html .= '<div class="add_new_comment">';
			$html .= '<div id="add_comments_of_query" data-query_num="'.$_POST['query_num'].'">переписка по запросу</div>';
			$html .= '</div>';
		}
		// показываем кнопку если по заказу есть комменты
		$exists = self::check_the_empty_order_coment_Database($_POST['order_num']);
		if($exists){
			$html .= '<div class="add_new_comment">';
			$html .= '<div id="add_comments_of_order" data-order_num="'.$_POST['order_num'].'">переписка по заказу</div>';
			$html .= '</div>';			
		}
		// подсвечиваем, если по позиции уже есть комменты
		$exists = self::check_the_empty_order_coment_Database($_POST['order_num']);
		$no_empty = ($exists)?' no_empty':'';
		$html .= '<div class="add_new_comment">';
		$html .= '<div id="add_comments_of_position" class="'.$no_empty.'" data-position_id="'.$_POST['position_id'].'">переписка по позиции</div>';
		$html .= '</div>';
		return $html;
	}

	private function get_comment_for_query_without_form_AJAX(){
		$html = $this->get_comment_for_query();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}
	private function get_comment_for_order_without_form_AJAX(){
		$html = $this->get_comment_for_order();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}

	// получаем все комментарии по заказу
	protected function get_comment_for_position(){
		global $mysqli;
		$html = '';	
		$comments = array();
		$query = "SELECT `".CAB_DOP_DATA_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".CAB_DOP_DATA_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".CAB_DOP_DATA_LIST_COMMENTS."`  WHERE `position_id` = '".(int)$_POST['position_id']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}
		

		$html .= '<div class="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$value['user_name'].'</div>';
						$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
						$html .= '<div>'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		}
		return $html;
	}

	// собираем форму отправки нового комментария по заказу
	private function get_the_comment_for_position_form(){
		// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';


		$html = '';
		$html .= '<form>';
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
						$html .= '<textarea name="comment_text"></textarea>';
						$html .= '<div class="div_for_button">';
							// $html .= '<button class="add_nah">ОК</button>';
							// $html .= '<button class="add_nah">Принял</button>';
							$html .= '<button id="add_new_comment_button">Сохранить</button>';
						$html .= '</div>';
						$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
						$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_position"></input>';
						$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
						$html .= '<input name="position_id" type="hidden" value="'.$_POST['position_id'].'"></input>';
					$html .= '</div>';						
				$html .= '</div>';
			$html .= '</div>';	
		$html .= '</form>';
		return $html;
	}


	##################################################
	#######     	ВЫЗОВ ФУНКЦИЙ AJAX end    	######
	##################################################
}


?>