<?php
	
	class Cabinet_men_class extends Cabinet{

		// расшифровка меню СНАБ
		public $menu_name_arr = array(
		// Запросы
		'no_worcked_men' => 'Не обработанные',
		// 'query_taken_into_operation' => 'В обработке',
		'query_taken_into_operation' => 'На рассмотрении',
		'query_worcked_men' => 'В работе',
		'send_to_snab' => 'Отправлено в Snab',
		'query_worcked_snab' => 'В работе Snab',
		'calk_snab' => 'Рассчитаные Snab',
		'denied' => 'ТЗ не корректно',
		'query_variant_in_pause' => 'На паузе',
		'query_denided_variants' => 'Отказанные',

		'query_all' => 'Все',
		'query_history' => 'Архив',


		// другое....
		'important' => 'Важно',
		'in_work' => 'В работе',
		'in_work_snab' => 'В работе СНАБ',
		'send_to_snab'=>'Отправлено в СНАБ',
		// 'send_to_snab' => '&&&',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'tz_no_correct' => 'ТЗ не корректно',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'на паузе',
		'history' => 'История',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		// 'otgrugen' => 'Отгруженные',
		'already_shipped' => 'Отгруженные',
		'partially_shipped' => 'Частично',
		'fully_shipped' => 'Полностью',
		'partially_shipped' => 'Частично отгружен',
		'requested_the_bill' => 'Счёт запрошен',
		'the_order_is_create' => 'Предзаказ',
		'payment_the_bill' => 'Счёт оплачен',		
		'refund_in_a_row' => 'возврат средств по счёту',
		'cancelled' => 'Счёт аннулирован',
		'all_the_bill' => 'Все документы',
			// заказы
			'order_all' => 'Все',
			'order_start' => 'Готовые к запуску',
			'order_in_work' => 'В обработке',
			'design_all' => 'Дизайн ВСЕ',
			'design_for_one_men' => 'Статус макета',
			'order_in_work_snab' => 'В работе',
			'production' => 'Наше производство',
			'stock' => 'Склад',
			'tpause_and_questions' => 'пауза/вопрос/ТЗ не корректно'								
		); 	

		// название подраздела кабинета
		private $sub_subsection;

		// содержит экземпляр класса кабинета вер. 1.0
		// private $CABINET;

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		public $POSITION_NO_CATALOG;

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 
			// echo '<pre>';
			// print_r($_SESSION);
			// echo '</pre>';
				
			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">this_snab_class </div>';
			
			// экземпляр класса продукции НЕ каталог
			$this->POSITION_NO_CATALOG = new Position_no_catalog();


			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

			// экземпляр класса кабинета вер. 1.0
			// $this = new Cabinet;

			//$this->FORM = new Forms;
		}



		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			// echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				header( 'Location: http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet());
				// // обработка ответа о неправильном адресе
				// $this->response_to_the_wrong_address($method_template);	
			}
		}



		############################################
		###		        AJAX START               ###
		############################################
			// взять в работу
			protected function get_in_work_AJAX(){
				global $mysqli;
				/*

				Проверяется в JS

				$query ="SELECT * FROM  `".RT_LIST."` WHERE `id` = '".(int)$_POST['row_id']."';";	
				$result = $mysqli->query($query) or die($mysqli->error);	
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Query[] = $row;
					}
				}
				if($this->Query['client_id'] == 0){
					$message = 'Чтобы взять запрос в работу необходимо прикрепить клиента!!!';
					$json = '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
					echo $json;
					exit;
				}
				*/

				// прикрепить клиента и менеджера к запросу	
				
				$query ="UPDATE  `".RT_LIST."` SET `status`='in_work',  `time_taken_into_operation` = NOW(), `manager_id` = '".$this->user_id."' WHERE `id` = '".(int)$_POST['row_id']."';";	
				$result = $mysqli->query($query) or die($mysqli->error);	
				echo '{"response":"OK","function":"reload_order_tbl"}';
				
				// $html = $this->print_arr($_SESSION);
				// echo '{"response":"show_new_window","html":"'.base64_encode($html).'"}';
			}


			// взять в обработку
			protected function take_in_operation_AJAX(){
				global $mysqli;

				// проверяем на уже прикреплённых менов к запросу
				$query =  " SELECT * FROM `".RT_LIST."`";
				$query .= " WHERE `id` = '".(int)$_POST['rt_list_id']."'";
				$this->Query = array();
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);		
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Query = $row;
					}
				}


				if($this->Query['manager_id'] != 0 && $this->Query['manager_id'] != $this->user_id){
					// получаем имя прикреплённого менеджера
					$men_arr = $this->get_manager_name_Database_Array($this->Query['manager_id']);

					// Lfyysq pfgh
					$message = "Извините, но данный запрос уже обрабатывает менеджер (".$men_arr['name']." ".$men_arr['last_name'].")";
					echo '{"response":"OK","function2":"reload_order_tbl","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
					exit;
				}

				// прикрепить клиента и менеджера к запросу	
				$query ="UPDATE  `".RT_LIST."` SET `status`='taken_into_operation',  `time_taken_into_operation` = NOW(), `manager_id` = '".$this->user_id."' WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
				$result = $mysqli->query($query) or die($mysqli->error);	
					
				// если передан id менеджера и он равен нулю
				if(isset($_POST['manager_id']) && isset($_POST['client_id']) && $_POST['manager_id'] == 0){		
					// проверяем прикреплен ли данный менеджер как куратор к данному клиенту
					// если нет , значит это вариант с новым клиентом и нам необходимо вписать к клиенту нового куратора
					include_once ('./libs/php/classes/client_class.php');
					$Client_class = new Client;

					$Client_class->attach_relate_manager((int)$_POST['client_id'], $this->user_id);
				}

				echo '{"response":"OK","function":"reload_order_tbl"}';
			}	

			


		function get_all_orders_Database_Array(){
			global $mysqli;
			$arr = array();
			$query = '';

		}

		private function get_client_name($id,$status){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					switch ($status) {
					case 'not_process':
						$name = '<div class="attach_the_client" data-id="'.$row['id'].'">'.$row['company'].'</div>';	
						break;
					case 'taken_into_operation':
						$name = '<div class="attach_the_client" data-id="'.$row['id'].'">'.$row['company'].'</div>';						
						break;					

					default:
						$name = '<div data-id="'.$row['id'].'">'.$row['company'].'</div>';					
						break;
				}
				}
			}else{
				$name = '<div class="attach_the_client" data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}



		function __destruct(){}
	}


?>