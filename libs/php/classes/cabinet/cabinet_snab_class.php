<?php
	
	class Cabinet_snab_class extends Cabinet{

		// расшифровка меню СНАБ
		public $menu_name_arr = array(

		// запросы
		'send_to_snab' => 'Не обработанные СНАБ',
		'query_worcked_snab' => 'В работе',
		'calk_snab' => 'Рассчитанные',
		'accept_snab_job' => 'Принятые МЕН',
		'denied' => 'ТЗ не корректно',
		'pause' => 'На паузе',
		'query_all' => 'Все',
		'query_history' => 'История',



		'important' => 'Важно',
		'no_worcked_snab' => 'Не обработанные СНАБ',		
		'no_worcked_men' => 'Не обработанные МЕН',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'ТЗ не корректно',
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
		// кнопки фильтрации заказа для СНАБ
		'get_in_work' => 'Принять',
		'my_orders' => 'Мои заказы',
		'only_get_in' => 'Только принятые',
		'expected_a_union' => 'Ожидает объединения',
		'partially_shipped' => 'Частично отгружен',
			// заказы
			'snab_starting_in_processing' => 'Запуск в обработку',
			'snab_in_Progress' => 'В обработке',
			'snab_mock_ups_of_the_work' => 'Макеты в работу',
			'snab_waiting' => 'Ожидают',
			'snab_products' => 'Продукция',
			'snab_in_the_production_of' => 'В производстве',
			'snab_our_production' => 'Наше производство',
			'tpause_and_questions' => 'пауза/вопрос/ТЗ не корректно',
			'snab_all' => 'Все',
			// 'order_all' => 'Все заказы',
			// 'order_start' => 'Запуск в работу (заказ)',
			// 'order_in_work' => 'Заказы в работе',
			// 'design_all' => 'Дизайн ВСЕ',
			// 'design_for_one_men' => 'Дизайн МОЁ',
			// 'production' => 'Производство',
			// 'stock' => 'Склад'							
		);

		//////////////////////////
		//	фильтры по разделам для кнопок подраздела
		//////////////////////////
		protected $filtres = array();

		// название подраздела кабинета
		private $sub_subsection;

		// содержит экземпляр класса кабинета вер. 1.0
		private $CABINET;

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		public $POSITION_NO_CATALOG;

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 

			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">this->Cabinet_snab_class </div>';
			
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
			$this->CABINET = new Cabinet;

			//$this->FORM = new Forms;
		}


		
		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				header( 'Location: http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet());
				// // обработка ответа о неправильном адресе
				// $this->response_to_the_wrong_address($method_template);	
			}
		}


		#############################################################
		##                          START                          ##
		##      методы для работы с поддиректориями subsection     ##
		#############################################################

	
		

		function __destruct(){}
	}


?>