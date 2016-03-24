<?php
	/*
	Все методы которые заканчиваются на AJAX относятся к обработчикам событий AJAX
	Причем название метода состоит из строки отправленной в переменной $_POST['AJAX']
	и приставки _AJAX
	*/


    class Cabinet_general  extends aplStdAJAXMethod{
    	// содержит Html левого меню
    	public $menu_left_Html;

    	// содержит Html кнопок фильрации
    	public $menu_top_center_Html;

		// id юзера
		private $user_id;

		// дефолтная расшифровка меню
		public $menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked_snab' => 'Не обработанные СНАБ',
		'no_worcked_men' => 'Не обработанные МЕН',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'Отказанные',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'Приостановлен',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные',
		'history' => 'История'													
		); 

		// допуски пользователя
		public $user_access;

		// права на редактирование поля определяются внутри 
		// некоторых функций 
		private $edit_admin =  ' contenteditable="true" class="edit_span"';
		private $edit_men = ' contenteditable="true" class="edit_span"';
		private $edit_snab = ' contenteditable="true" class="edit_span"';

				
		function __construct(){
			
			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);

			// обновляем доступы в соответствии с проверенными по базе допусками
			// на случай вхождения админом в чужой аккаунт 
			global $ACCESS_SHABLON;
			$this->ACCESS = $ACCESS_SHABLON[$this->user_access];
			
			// поиск по клиенту
			// if(isset($_GET['search']) && trim($_GET['search'])!=''){
			// 	$this->replace_search_query_on_client_id();
			// }
			
			// обработчик AJAX через ключ AJAX
			# если существует метод с названием из запроса AJAX - обращаемся к нему

			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

			

			## роутер классов по уровню допуска
			## т.е. на каждый допуск свои шаблоны и классы,
			## при желании можно инклудить несколько классов и обращаться к их методам
			$this->__ROUTER_CLASS__();

			//////////////////////////
			//	get content for menues
			//////////////////////////
			$this->menu_left_Html = $this->get_menu_left_Html();
			$this->menu_top_center_Html = $this->get_menu_top_center_Html();
		}

		//////////////////////////
		//	Для раздичных подразделений компании необходимы совершенно различные права
		//  и отображение информации, причём одни и теже статусы в программе для каждого пользователя 
		//  выгледят по разному, поэтому для каждого отдела предусмотрен отдельный класс
		//  в каждом классе содержится метод с названием '.$_GET['section'].'_Template для каждого пункта левого меню  
		//////////////////////////
		private Function __ROUTER_CLASS__(){
			switch ($this->user_access) {
				case '1':					
					$text = 'администратор <br>';
					//echo $this->wrap_text_in_warning_message($text);
					
					include_once 'cabinet_admin_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_admin_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					
					break;

				case '2':				
					$text = 'бухгалтер<br>';
					//echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_buch_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_buch_class($this->user_access);//echo $this->wrap_text_in_warning_message($this->print_arr($_SESSION));
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '4':					
					$text = 'производство';// УСЛУГИ
					//echo $this->wrap_text_in_warning_message($text);
					
					include_once 'cabinet_production_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_production_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;
				case '5':
					$text = 'менеджер';
					//echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_men_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_men_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '6':
					$text = 'водитель';// УСЛУГИ
					//echo $this->wrap_text_in_warning_message($text);
					break;

				case '7':
					$text = 'склад';
					//echo $this->wrap_text_in_warning_message($text);

					//echo '';


					include_once 'cabinet_sklad_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_sklad_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса снабжения формулировки для меню, понятные для снаба
					$this->menu_name_arr = $this->CLASS->menu_name_arr;


					break;

				case '8':
					$text = 'снабжение';
					//echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_snab_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_snab_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса снабжения формулировки для меню, понятные для снаба
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '9':
					$text = 'дизайнер';// УСЛУГИ
				//	echo $this->wrap_text_in_warning_message($text);

					include_once 'cabinet_designers_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_designer_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				default:					
					$text = 'У вас не хватает прав на доступ к данному разделу!!!';
					echo $this->wrap_text_in_warning_message($text);
					break;
			}

			$this->CLASS->check_the_filtres(); // обсчитываем включённые фильтры
		}

		############################################
		###				AJAX START               ###
		############################################
			// private function _AJAX_($name){
			// 	$method_AJAX = $name.'_AJAX';

			// 	// если в этом классе существует такой метод - выполняем его и выходим
			// 	if(method_exists($this, $method_AJAX)){
			// 		$this->$method_AJAX();
			// 		exit;
			// 	}		
			// }
			
			//////////////////////////
			//	paperwork START
			//////////////////////////
				private function replace_search_query_on_client_id(){
					global $mysqli;
					$query="SELECT * FROM `".CLIENTS_TBL."`  WHERE `company` = '".$_GET['search']."'";
					$result = $mysqli->query($query)or die($mysqli->error);
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							unset($_GET['search']);
							$_GET['client_id'] = $row['id'];
						}
					}
					// exit;
				}
				
				


				protected function change_payment_date_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_date` =  '".$_POST['date']."' WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				protected function change_payment_status_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				// private function change_invoce_num_AJAX(){
				// 	global $mysqli;
				// 	$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `invoice_num` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// }
				protected function number_payment_list_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `number_pyament_list` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				protected function select_global_status_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				
				protected function change_ttn_number_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `ttn_number` =  '".$_POST['value']."', ttn_get = NOW() WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}

				protected function change_delivery_tir_AJAX(){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `delivery_tir` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				
			// получаем форму выбора кураторов для нового клиента
			protected function get_choose_curators_form($managers_arr_all){
				// получаем список менеджеров
				// echo '<pre>';
				// print_r($managers_arr);
				// echo '</pre>';

				foreach ($managers_arr_all as $key => $manager) {
					if($manager['id'] != 24){
						$managers_arr[] = $manager;
					}
				}
				
				$menegers_checked_arr = array();
				$html = '';
				// echo '<pre>';
				// print_r($managers_arr);
				// echo '</pre>';

				$html .= '<form  id="chose_many_curators_tbl">';
				
				
						$html .='<table>';

						$count = count($managers_arr);
						for ($i=0; $i <= $count; $i) {
							$html .= '<tr>';
						    for ($j=1; $j<=3; $j++) {
						    	if(isset($managers_arr[$i])){
						    		//исключаем отдел продаж
							    	$checked = '';
							    	if(isset($_POST['manager_id']) && $managers_arr[$i]['id'] == $_POST['manager_id']){
							    		$checked = ' class="checked"';
							    		$menegers_checked_arr[$managers_arr[$i]['id']] = $managers_arr[$i]['id'];
							    	}

							    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
							    	$html .= '<td '.$checked.' date-lll="'.$i.'" data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
						    		$i++;
						    	}else{
						    		$html .= '<td  date-lll="'.$i.'"></td>';
						    		$i++;
						    	}				    	
						    }				    
						    $html .= '</tr>';
						}

						$html .= '</table>';
				$json_menegers_checked_arr = '{';
					foreach ($menegers_checked_arr as $key => $value) {
						$json_menegers_checked_arr .= '"'.$key.'":"'.$value.'"';
					}
				$json_menegers_checked_arr .= '}';

				$html .=' <input type="hidden" name="Json_meneger_arr" value=\''.$json_menegers_checked_arr.'\' id="json_manager_arr_val">';
				$html .=' <div id="json_manager_arr">'.$json_menegers_checked_arr.'</div>';
				$html .= '<input type="hidden" name="AJAX" value="create_new_client_and_insert_curators">';

				unset($_POST['AJAX']);
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}

				$html .= '</form>';

				return $html;
			}

			// выводит форму с выбором менеджеров 
			public function get_a_list_of_managers_to_be_attached_to_the_request_AJAX(){
				global $mysqli;
							
				// $html = 'test';
				$html = '';	
				if($_POST['client_id']!='0' && $_POST['client_id']!='new_client'){// если клиент приклеплён
					
					//$html .= $this->wrap_text_in_warning_message_post('Для прикреплённого клиента доступны следующие кураторы:');
					$message = 'Для выбранного клиента доступны следующие кураторы:';
					$this->responseClass->addMessage($message,'system_message');

					# получаем список кураторов
					// подключаем класс клиента
					include_once ('./libs/php/classes/client_class.php');
					$managers_arr = Client::get_relate_managers_2($_POST['client_id']);

					if(count($managers_arr) == 0 ){
						$managers_arr = array();
					    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `access` = '5'";
					    $result = $mysqli->query($query)or die($mysqli->error);
					    if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$managers_arr[] = $row;
							}
						}
					}
				}else{ // если клиент не прикреплён

					// сообщение
			        $message = 'Клиент не прикреплён, выберите менеджера, который обработает данный запрос.';
					$this->responseClass->addMessage($message,'system_message');

					$managers_arr = array();
				    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `access` = '5'";
				    $result = $mysqli->query($query)or die($mysqli->error);
				    if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$managers_arr[] = $row;
						}
					}
				}
				if(!isset($_POST['row_id']) && isset($_POST['rt_list_id'])){
					$_POST['row_id'] = $_POST['rt_list_id'];
				}
				if($this->user_access == 1){
					// форма мультивыбора для Админа
					$html = $this->get_choose_curators_form($managers_arr);
					echo '{"response":"show_new_window","title":"Выберите менеджера","html":"'.base64_encode($html).'"}';
							
					exit;
				}else{
					// форма выбора одного менеджера для Мена
				
					$html .= '<div  id="chose_manager_tbl">';
					$html .='<table>';

					$count = count($managers_arr);
					for ($i=0; $i <= $count; $i) {
						$html .= '<tr>';
					    for ($j=1; $j<=3; $j++) {
					    	if(isset($managers_arr[$i])){
						    	$checked = ($managers_arr[$i]['id'] == $_POST['manager_id']
						    		)?'class="checked"':'';
						    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
						    	$html .= '<td '.$checked.' date-lll="'.$i.'" data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
					    		$i++;
					    	}else{
					    		$html .= '<td  date-lll="'.$i.'"></td>';
					    		$i++;
					    	}
					    	
					    }
					    $html .= '</tr>';
					}

					$html .= '</table>';
					$html .= '<input type="hidden" value="attach_manager_to_request" name="AJAX">';
					$html .= '<input type="hidden" value="'.$_POST['manager_id'].'" name="manager_id">';
					$html .= '<input type="hidden" value="'.$_POST['rt_list_id'].'" name="rt_list_id">';
					$html .= '<input type="hidden" value="" name="client_id">';
					$html .= '</div>';
					$title = 'Выберите менеджера';
					
					$this->responseClass->addPostWindow($html,$title);
				}
				
			}
			// attach_manager_to_request

			protected function attach_manager_to_request_AJAX(){
				$this->db();
				// прикрепить менеджера к запросу	
				$client_id = (trim($_POST['client_id'])=='')?0:$_POST['client_id'];
				$query = " UPDATE  `".RT_LIST."` SET "; 
				$query .= " `manager_id` =  '".(int)$_POST['manager_id']."'"; 
				if($this->user_id != $_POST['manager_id']){
					$query .= ",`time_attach_manager` = NOW()";
					$query .= ",`status` = 'not_process'";	
					// перенаправление другому менеджеру
					include_once ('./libs/php/classes/manager_class.php');
					$manager = Manager::get_snab_name_for_query_String($_POST['manager_id']);
					
					$message = 'Запрос был перенаправлен менеджеру '.$manager;
					
					$this->responseClass->addMessage($message,'system_message');
					$this->responseClass->addResponseFunction('reload_order_tbl');

					// оповещение менеджера
					$mail_message = "";
					$Cabinet = new Cabinet();
					$this->Query = $this->get_query((int)$_POST['rt_list_id']);
					$managers_email_arr = $Cabinet->get_users_email(array($_POST['manager_id']));
					$subject = 'Вам доступен новый запрос.';
					$mail_message .= 'Здравствуйте, '.$manager.'!<br><br>';
					$mail_message .= 'Вам доступен <a href="http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num='.$this->Query['query_num'].'">новый запрос № '.$this->Query['query_num'].'</a><br>';
					$mail_message .= '<br>';
					$mail_message .= 'P.S. Запрос доступен только Вам!';
					    
					$mailClass = new Mail();
							
					foreach ($admin_email_arr as $key => $email) {
						$mailClass->send($email,'os@apelburg.ru',$subject,$mail_message);
					}
				}else{
					$query .= ",`status` = 'in_work'";
					$this->Query = $this->get_query((int)$_POST['rt_list_id']);

					$link = '?page=client_folder&client_id='.$this->Query['client_id'].'&query_num='.$this->Query['query_num'];
										
					// переадресация на другую вкладку
					$option['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/os/'.$link;
					$option['timeout'] = '0';
					$this->responseClass->addResponseFunction('location_href',$option);
					// $message = 'Вы взяли запрос в работу. Вы будете перенаправлены на другую вкладку.';
					// $this->responseClass->addMessage($message,'successful_message');	
					
				}				
				$query .= " WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				// echo '{"response":"OK"}';

				

				// $options['width'] = 1200;
				// $query .= $this->print_arr($_POST);
				// $this->responseClass->addSimpleWindow($query,'',$options);	
				
			}

			// выводит форму со списоком клиентов для прикрепления к запросу 
			protected function get_a_list_of_clients_to_be_attached_to_the_request_AJAX(){
				// if( !isset($_POST['client_name_search']) || strlen($_POST['client_name_search']) < 3 ){
				// 	get_client_sherch_form();
				// }

				// if(isset($_GET['query_status']) && $_GET['query_status']!= ''){
				// 	include_once ('cabinet_class.php');
				// 	$cabinet = new Cabinet;
				// 	$cabinet->command_for_change_status_query_AJAX();
				// }


				$html = $this->get_form_attach_the_client('attach_client_to_request');
				//echo '{"response":"show_new_window","function":"scroll_width_checked_client","html":"'.base64_encode($html).'","title":"Выберите клиента",'.(($this->i>30)?'"height":"600",':'').'"width":"1000"}';
				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				exit;
			}			

			// возвращает список клиентов
			private function get_form_attach_the_client($AJAX = 'test'){
				global $mysqli;
				$html ='';
				
				$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id`= '".(int)$_POST['client_id']."' ";
				if(isset($_POST['client_name_search'])){
					$query .= " OR `company` LIKE '%".trim($_POST['client_name_search'])."%' ORDER BY `company`";
				}

				// Запрос с сортировкой почему-то не хочет выводит ь некоторые компании
				// к примеру не выводит компанию *Морской салон ЗАО  - id = 18
				// $query = "SELECT * FROM `".CLIENTS_TBL."`  order by company ASC;";
				$result = $mysqli->query($query) or die($mysqli->error);				
				$clients = array();
				$clients[0]['id'] = 'new_client';
				$clients[0]['company'] = '<strong>НОВЫЙ КЛИЕНТ</strong>';
				if((int)$_POST['client_id'] > 0){
					$clients[1]['id'] = '0';
					$clients[1]['company'] = '';
				}


				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						
						if($_POST['client_id'] != $row['id']){
							$clients[] = $row;
						}else{
							$clients[1] = $row;
						}
					}
				}




				// $html .= '<br>'.count($clients);
				$html .= '<form  id="chose_client_tbl">';
				
				$html .='<table>';
				$column = 3;
				$num_client  = count($clients);
				$num_rows = ceil($num_client / $column);

				$td_count = $num_rows*$column;

				$clients_array = array();
				
				for ($td=0; $td <= $num_rows; $td++) { 
					for ($col=0; $col < $column; $col++) { 
						$html .= ($col == 0)?'<tr>':'';
						// $html .= '<td>';
						$id = ($col>0)?$td + $num_rows*$col+$col:$td + $num_rows*$col;
						if(isset($clients[$id])){
					    	$checked = ($clients[$id]['id'] == $_POST['client_id'])?'class="checked checked_client"':'';
					    	$html .= '<td '.$checked.' data-id="'.$clients[$id]['id'].'" id="client_'.$clients[$id]['id'].'">'.$clients[$id]['company']."</td>";
				    		// если присутствует выбранный клиент, ставим флаг
				    		if($checked!=''){$f_r = 1;}	
				    	}else{
				    		$html .= "<td></td>";
				    	}
						// $html .= (isset($clients[]))?$clients[($col>0)?$td + $num_rows*$col+$col:$td + $num_rows*$col]['company']:'';
						// $html .= ($col>0)?$td + $num_rows*$col+$col:$td + $num_rows*$col;
						// $html .= '</td>';
						$html .= ($col == $column)?'</tr>':'';
					}
				}
				$this->i = $num_rows;

				

				$html .= '</table>';
				$html .= '<input type="hidden" value="'.$AJAX.'" name="AJAX">';
				// $html .= '<input type="hidden" value="" name="manager_id">';
				if(isset($_POST)){
					unset($_POST['AJAX']);
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';	
					}	
				}

				
				$html .= '<form>';
				return $html;
			}


			// перенаправляем запрос в client_class.php
				protected function insert_new_client_AJAX(){
					include_once ('./libs/php/classes/client_class.php');
					new Client;
					exit;
				}

				protected function create_new_client_and_insert_curators_AJAX(){
					include_once ('./libs/php/classes/client_class.php');
					new Client;
					exit;
				}
				protected function get_form_the_create_client_AJAX(){
					include_once ('./libs/php/classes/client_class.php');
					new Client;
					exit;
				}

				protected function insert_new_client_for_new_qury_AJAX(){
					include_once ('./libs/php/classes/client_class.php');
					new Client;
					exit;
				}

			/**
			  *	получаем запрос по его id
			  *
			  *	@author  	Alexey Kapitonov
			  *	@version 	23:16 03.02.2016
			  */
			protected function get_query($id){
				global $mysqli;
				
				$query = "SELECT * FROM `".RT_LIST."` WHERE `id` = '".(int)$id."';";
				$Query = array();
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Query = $row;
					}
				}
				return $Query;
			}

			// прикрепляет клиента к запросу
			protected function attach_client_to_request_AJAX(){

				include_once ('./libs/php/classes/client_class.php');
				
				if($_POST['client_id'] == 'new_client'){
					// не админ создаёт клиента
					if($this->user_access != 1){
						$_POST['AJAX'] = 'insert_new_client';
						$client = new Client;	
					}else{

						// $_POST['client_id'] = '0';
						$_POST['manager_id'] = '0';
						$this->get_a_list_of_managers_to_be_attached_to_the_request_AJAX();
						return;
					}
					
					// $client -> get_new_client_form_from_query();
					// echo '321321321sdsad';
					// exit;
				}else{
					// получаем кураторов по выбранному клиенту
					// подключаем класс клиента
					if(isset($_POST['row_id'])){
						$_POST['rt_list_id'] = $_POST['row_id'];
					}
					
					$managers_arr = Client::get_relate_managers_2($_POST['client_id']);
								

					switch (count($managers_arr)) {
						case '0':
							// если у нас не прикреплено к данному клиенту ни одного менеджера - выводим сообщение об ошибке
							$message = 'К данному клиенту не прикреплено ни одного менеджера.';
							//echo '{"response":"show_new_window","title":"Ошибка","html":"'.base64_encode($html).'"}';
							$this->responseClass->addMessage($message,'system_message');
							break;
						case '1':
							// echo '<pre>';
							// print_r($_POST);
							// echo '</pre>';
								
							// если прикреплен только 1 - никаких проблем. 
							// прикрепляем к запросу клиента и менеджера 
							// Переписываем менеджера, отправляем данные о нем в браузер, вызываем там функцию и меняем имя менеджера на странице
							global $mysqli;
							// прикрепить клиента и менеджера к запросу	
							$query = "UPDATE  `".RT_LIST."` SET  ";
							$query .= "`manager_id` =  '".(int)$managers_arr[0]['id']."'";
							$query .= ",`client_id` =  '".(int)$_POST['client_id']."'";
						
							if($this->user_id != $managers_arr[0]['id']){
								$message = 'Запрос был перенаправлен менеджеру '.$managers_arr[0]['name'].' '.$managers_arr[0]['last_name'].'';
								$query .= ",`time_attach_manager` = NOW()";
								$query .= ",`status` = 'not_process' ";
							}else{
								$message = 'Запрос был переведён в работу';
								$query .= ",`time_attach_manager` = NOW()";
								$query .= ",`status` = 'in_work' ";
							}
							
							$query .= " WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
							$result = $mysqli->query($query) or die($mysqli->error);	

							
							// if($this->user_access != 5){
							// 	echo '{"response":"OK","function2":"change_attache_manager","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
							// }else{
							// 	echo '{"response":"OK","function":"reload_order_tbl"}';
							// }
							// echo '{"response":"OK","function":"change_attache_manager","rt_list_id":"'.$_POST['rt_list_id'].'", "manager_id":"'.$managers_arr[0]['id'].'","manager_name":"'.$managers_arr[0]['name'].' '.$managers_arr[0]['last_name'].'"}';
							
							// $options['width'] = 1200;
							// $this->responseClass->addSimpleWindow($query,"",$options);

							$this->responseClass->addMessage($message,'system_message');
							// $this->responseClass->addResponseFunction('change_attache_manager',array('rt_list_id'=>$_POST['rt_list_id'],'manager_id'=>$managers_arr[0]['id'],'name'=>$manager));
							$this->responseClass->addResponseFunction('reload_order_tbl');
							break;
						
						default:
							// если к клиенту присоединено несколько кураторов выполняем первый пункт по умолчанию, потом вызываем окно с выбором менеджера
							
							
							/*
								
							***************************************************************
							
							временно отключаем прикрепление первого менеджера автоматически
							
							***************************************************************
								global $mysqli;
								// прикрепить клиента и менеджера к запросу	
								$query ="UPDATE  `".RT_LIST."` SET  
									`manager_id` =  '".(int)$managers_arr[0]['id']."',
									`client_id` =  '".(int)$_POST['client_id']."', 
									`time_attach_manager` = NOW(),
									`status` = 'not_process'
									WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
								$result = $mysqli->query($query) or die($mysqli->error);	
							
							*/

							global $mysqli;
							// прикрепить клиента 
							$query ="UPDATE  `".RT_LIST."` SET  
								`client_id` =  '".(int)$_POST['client_id']."', 
								`time_attach_manager` = NOW(),
								`status` = 'not_process'
								WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
							$result = $mysqli->query($query) or die($mysqli->error);
			
							//////////////////////////
							//	осбираем форму для выбора одного из кураторов
							//  
							//////////////////////////
							$html = '<div  id="chose_manager_tbl">';
							$html .='<table>';					
							for ($i=0; $i < count($managers_arr); $i) {
								$html .= '<tr>';
							    for ($j=1; $j<=3; $j++) {
							    	if(isset($managers_arr[$i])){
								    	//$checked = ($managers_arr[$i]['id'] == $managers_arr[0]['id'])?'class="checked"':'';
								    	$checked = '';
								    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
								    	$html .= '<td '.$checked.' data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
							    	}else{
							    		$html .= "<td></td>";
							    	}
							    	$i++;
							    }

							    $html .= '</tr>';
							}
							$html .= '</table>';
							$html .= '<input type="hidden" value="attach_manager_to_request" name="AJAX">';
							$html .= '<input type="hidden" value="'.(isset($_POST['manager_id'])?$_POST['manager_id']:'').'" name="manager_id">';
							$html .= '<input type="hidden" value="'.$_POST['rt_list_id'].'" name="rt_list_id">';
							$html .= '<input type="hidden" value="" name="client_id">';
							$html .= '</div>';
							
							// записываем на странице пользователя в строку с установленным клиентом имя первого куратора из списка
							// затем даём выбрать из иставшихся
							
							// echo '{"response":"show_new_window",
							// 		"html":"'.base64_encode($html).'",
							// 		"title":"Выберите куратора", 
							// 		"function":"change_attache_manager",
							// 		"rt_list_id":"'.$_POST['rt_list_id'].'", 
							// 		"manager_id":"'.$managers_arr[0]['id'].'",
							// 		"manager_name":"'.$managers_arr[0]['name'].' '.$managers_arr[0]['last_name'].'"
							// 	}';


							$this->responseClass->addPostWindow($html,'Выберите куратора',array('width' => '1000'));
							
							$this->responseClass->addResponseFunction(
								'change_attache_manager',array(
								'rt_list_id'=>$_POST['rt_list_id'],
								'manager_id'=>$managers_arr[0]['id'],
								'name'=>$managers_arr[0]['name'].' '.$managers_arr[0]['last_name']
								)
							);
							$this->responseClass->addResponseFunction('reload_order_tbl');
							break;
					}

					if(isset($_POST['row_id']) && (int)$_POST['row_id'] > 0){
					$this->Query = $this->get_query((int)$_POST['row_id']);
					// echo '<pre>';
					// print_r($this->Query);
					// echo '</pre>';
					// echo '<pre>';
					// print_r($_POST);
					// echo '</pre>';
					if(isset($_POST['query_status']) && $_POST['query_status']!= $this->Query['status']){
						if((int)$_POST['client_id'] != $this->Query['client_id']){
							include_once ('cabinet_class.php');
							$cabinet = new Cabinet;
							$cabinet->responseClass = new responseClass;
							$cabinet->command_for_change_status_query_AJAX();
							// echo $this->responseClass->getResponse();					
							exit;
							
						}
					}
				}
				}


				// exit;			
			}

			//пример обработки AJAX запроса
			# выводит информацию из глобальных массивов и объекта текущего класса
			protected function show_globals_arrays_AJAX(){
				echo '<strong>POST:</strong>';
				echo '<pre>';
				print_r($_POST);
				echo '</pre>';

				echo '<strong>GET:</strong>';
				echo '<pre>';
				print_r($_POST);
				echo '</pre>';

				echo '<strong>SESSION:</strong>';
				echo '<pre>';
				print_r($_SESSION);
				echo '</pre>';

				echo '<strong>Object Class:</strong>';
				echo '<pre>';
				print_r($this);
				echo '</pre>';
				exit;
			}

			//////////////////////////
			//	ORDERS start
			//////////////////////////
			// выбор поставщика
			protected function chose_supplier_AJAX(){

				// запоминаем id уже выбранных поставщиков
				$already_chosen_arr = explode(',', $_POST['already_chosen']);

				$suppliers_arr = Supplier::get_all_suppliers_Database_Array();
				$html = '<form>';
				$html .='<table id="chose_supplier_tbl">';

				$n=0;
				for ($i=1; $i < count($suppliers_arr); $i++) {
					$html .= '<tr>';
				    for ($j=1; $j<=3; $j++) {
				    	$checked = '';
				    	foreach ($already_chosen_arr as $key => $id) {
				    		if(isset($suppliers_arr[$i]['id'])){
					    		if($suppliers_arr[$i]['id']==trim($id)){
					    			$checked = 'class="checked"';
					    		}
				    		}
				    	}
				    	$html .= (isset($suppliers_arr[$i]['nickName']))?'<td '.$checked.' data-id="'.$suppliers_arr[$i]['id'].'">'.$suppliers_arr[$i]['nickName']."</td>":"<td></td>";
				    	$i++;
				    }

				    $html .= '</tr>';
				}
				$html .= '</table>';
				$html .= '<input type="hidden" name="AJAX" value="change_supliers_info_dop_data">';
				$html .= '<input type="hidden" name="id_dop_data" value="'.$_POST['id_dop_data'].'">';
				$html .= '<input type="hidden" name="suppliers_id" value="'.$_POST['already_chosen'].'">';
				$html .= '<input type="hidden" name="suppliers_name" value="'.$_POST['suppliers_name'].'">';
				$html .= '</form>';
				echo $html;
				exit;	
			}

			protected function change_supliers_info_dop_data_AJAX(){
				$this->change_supliers_info_dop_data_Database();
				exit;
			}

			// редактируем информацию об поставщиках для некаталожного варианта расчёта
			public function change_supliers_info_dop_data_Database(){
				global $mysqli;
				$query ="UPDATE `".CAB_ORDER_DOP_DATA."` SET
				             `suppliers_id` = '".$_POST['suppliers_id']."',
				             `suppliers_name` = '".$_POST['suppliers_name']."' 
				             WHERE `id` =  '".$_POST['id_dop_data']."';
				             ";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				echo '{"response":"OK","name":"chose_supplier_end", "function":"del_id_chose_supplier_id"}';
			}

		############################################
		###				 AJAX END                ###
		############################################

		// получает контент левого меню
		private function get_menu_left_Html(){

			// ЛЕВОЕ МЕНЮ РАЗДЕЛОВ
			## обрабатываем массив разрешённых разделов
			$menu = "";
			// echo 'dwedfewfewqf wqe fqwe qefe q';
			// echo '<pre>';
			// print_r($this->CLASS->filtres_html);
			// echo '</pre>';
			$filters = '';
			if(is_array($this->CLASS->filtres_html)){
				foreach ($this->CLASS->filtres_html as $key => $value) {
					$filters .= '&'.$key.'='.$_GET[$key];
				}	
			}
			// т.к. фильтр по клиенту не входит в общий список фильтров - пишем его отдельно
			if(isset($_GET['client_id']) && $_GET['client_id']!=''){
				$filters .= '&client_id='.$_GET['client_id'];
			}
			
			foreach ($this->ACCESS['cabinet']['section'] as $key => $value) {
				if($value['access']){
					$menu .= '<li '.((isset($_GET["section"]) && $_GET["section"]==$key)?'class="selected"':'').'>';
						$menu .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section='.$key.'&subsection='.key($value['subsection']).$filters.'">';
							$menu .= $this->CLASS->menu_name_arr[$key];
						$menu .= '</a>';
					$menu .= '<li>';
									
				}
			}
			return $menu;			
		}

		// получает контент верхнего меню фильтров
		public function get_menu_top_center_Html($section = 'requests'){
			// ЦЕНТРАЛЬНОЕ МЕНЮ СВЕРХУ
			$menu = '<div class="cabinet_top_menu">';
			$menu .= '<ul class="central_menu">';
				
			$menu_central_arr = (array_key_exists($_GET["section"], $this->ACCESS['cabinet']['section']))?$this->ACCESS['cabinet']['section'][$_GET["section"]]['subsection']:array();
			
			$filters = '';
			if(is_array($this->CLASS->filtres_html)){
				foreach ($this->CLASS->filtres_html as $key => $value) {
					$filters .= '&'.$key.'='.$_GET[$key];
				}	
			}  /*45*/
			// т.к. фильтр по клиенту не входит в общий список фильтров - пишем его отдельно
			if(isset($_GET['client_id']) && $_GET['client_id']!=''){
				$filters .= '&client_id='.$_GET['client_id'];
			}
			foreach ($menu_central_arr as $key2 => $value2) {
				$menu .= '<li '.((isset($_GET["subsection"]) && $_GET["subsection"]==$key2)?'class="selected"':'').'>';
					$menu .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet'.((isset($_GET["section"]))?'&section='.$_GET["section"]:'').'&subsection='.$key2.$filters.'">';
						$menu .= '<div class="border">';
							$menu .= $this->CLASS->menu_name_arr[$key2];
						$menu .= '</div>';
					$menu .= '</a>';
				$menu .= '<li>';
			}
			$menu .= '</ul>';
			$menu .= '</div>';
			// return 654654;
			return $menu;
		}

		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
		private function get_user_access_Database_Int($id){
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

		//////////////////////////
		//	оборачивает в оболочку warning_message
		//////////////////////////
		// отключается при post запросе, важно для AJAX
		private function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			// в случае получения POST запроса предупреждения отключаем
			if(!empty($_POST)){$html='';}
			return $html;
		}

		private function wrap_text_in_warning_message_post($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}

		// отдаёт $html распечатанного массива
		protected function print_arr($arr){
			ob_start();
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
			$content = ob_get_contents();
			ob_get_clean();
			
			return $content;
		}

   	}