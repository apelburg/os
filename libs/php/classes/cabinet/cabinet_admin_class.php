<?php
	//test commit 
	class Cabinet_admin_class extends Cabinet{
		// разрешить показ сообщений
		// private $allow_messages = false;

		// словарь
		public $menu_name_arr = array(
			// запросы
			'query_wait_the_process' => 'Ожидают распределения',
			'no_worcked_men' => 'Ожидают обработки',
			// 'query_taken_into_operation' => 'В обработке',
			'query_taken_into_operation' => 'На рассмотрении',
			'query_worcked_men' => 'В работе Sales',
			'query_worcked_snab' => 'Работа Snab',
			'query_history' => 'Архив',
			'query_all' => 'Все',


			// другое.....
			'important' => 'Важно',
			'in_processed'=>'обрабатывается',
			'no_worcked_snab' => 'Не обработанные СНАБ',		
			'no_worcked_men' => 'Не обработанные МЕН',	
			'send_to_snab' => 'Отправлены в СНАБ',
			'calk_snab' => 'Рассчитанные',
			'ready_KP' => 'Выставлено КП',
			'denied' => 'ТЗ не корректно',
			'orders' => 'Заказы',
			'requests' =>'Запросы',
			'create_spec' => 'Документ создан',
			'signed' => 'Спецификация подписана',
			'expense' => 'Счёт выставлен',
			'requested_the_bill' => 'Счёт запрошен',
			'paperwork' => 'Предзаказ',
			'order_start' => 'Готовые к запуску',
			'tz_no_correct' => 'ТЗ не корректно',
			'purchase' => 'Закупка',
			'design' => 'Дизайн',
			
			'ready_for_shipment' => 'Готов к отгрузке',
			'paused' => 'на паузе',
			'history' => 'Архив',
			'simples' => 'Образцы',
			'closed'=>'Закрытые',
			'all' => 'Все',
			'issue'=>'Вопрос',
			'not accepted' => 'Не принято',
			'for_shipping' => 'На отгрузку',
			'my_orders_diz' => 'Мои заказы дизайн',
			'all_orders_diz' => 'Все заказы дизайн',
			'order_of_documents' => 'Заказ документов',
			'arrange_delivery' => 'Оформить доставку',
			'delivery' => 'Доставка',
			'pclosing_documents' => 'Закрывающие документы',
			'otgrugen' => 'Отгруженные',
			'already_shipped' => 'Отгруженные',
			'partially_shipped' => 'Частично',
			'fully_shipped' => 'Полностью',
			'partially_shipped' => 'Частично отгружен',
			'the_order_is_create' => 'Предзаказ',
			'payment_the_bill' => 'Счёт оплачен',	
			'refund_in_a_row' => 'Возврат средств',
			'cancelled' => 'Аннулированные',
			'all_the_bill' => 'Все документы',
			// запросы
			'in_work' => 'В работе',
			// заказы
			'order_all' => 'Все заказы',
			'order_in_work_snab' => 'В работе',
			// 'order_start' => 'Запуск в работу (заказ)',
			// 'order_in_work' => 'Заказы в работе',
			'design_all' => 'Дизайн',
			'order_in_work' => 'В обработке',
			'production' => 'Производство',
			'stock_all' => 'Склад'								
		);

	protected $user_id;
	protected $user_access;



	function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =))

		$this->user_id = $_SESSION['access']['user_id'];
		$this->user_access = $user_access;

		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}

		}


	// стадратный метод для вывода шаблона
	public function __subsection_router__(){
		$method_template = $_GET['section'].'_Template';
		// $method_template = $_GET['section'].'_Template';
		//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

		// если в этом классе существует такой метод - выполняем его
		if(method_exists($this, $method_template)){
			$this->$method_template();
		}else{
			header( 'Location: http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet());
			// exit;
			// // обработка ответа о неправильном адресе
			// $this->response_to_the_wrong_address($method_template);
		}
	}


    // получаем массив пользователей с правами 5 и 1
	protected function get_manager_list($access_arr = array(0 => 5)){
		$n = 0;
		$access_str = '';
		foreach ($access_arr as $key => $value) {
			$access_str .= (($n>0)?',':'')."'".$value."'";
			$n++;
		}

		global $mysqli;
		$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `access` IN (".$access_str.") ORDER BY `last_name` ASC;";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$manager_names[] = $row;
			}
		}
		return $manager_names;

	}

		

    // получаем форму выбора кураторов
	protected function get_choose_curators_edit(){
		// получаем список менеджеров
		$managers_arr = $this->get_manager_list(array(1, 5, 4));

		$managers_Json_arr  = explode(',', $_POST['managers_id_str']);
		$Json = '{';
		$n = 0;
		foreach ($managers_Json_arr as $value) {
			$Json .= (($n++>0)?',':'').'"'.$value.'":"'.$value.'"';

		}
		$Json .= '}';

		$html = '';
		$html .= '<form  id="chose_many_curators_tbl">';
		$html .=' <div id="json_manager_arr">'.$Json.'</div>';
		$html .=' <input type="hidden" name="Json_meneger_arr" value=\''.$Json.'\' id="json_manager_arr_val">';
				$html .='<table>';

				$count = count($managers_arr);
				for ($i=0; $i <= $count; $i) {
					$html .= '<tr>';
				    for ($j=1; $j<=3; $j++) {
				    	if(isset($managers_arr[$i])){
					    	$checked = (in_array($managers_arr[$i]['id'], $managers_Json_arr))?'class="checked"':'';
					    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
					    	$html .= '<td '.$checked.' date-lll="'.$i.'" d="'.$managers_arr[$i]['id'].'_'.in_array($managers_arr[$i]['id'], $managers_Json_arr).'" data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
				    	}else{
				    		$html .= '<td  date-lll="'.$i.'"></td>';
				    	}
				    	$i++;
				    }
				    $html .= '</tr>';
				}

				$html .= '</table>';

		$html .= '<input type="hidden" name="AJAX" value="create_new_client_and_insert_curators">';

		unset($_POST['AJAX']);
		foreach ($_POST as $key => $value) {
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}

		$html .= '</form>';

		return $html;
	}
}


?>