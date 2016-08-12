<?php

	class Cabinet_designer_class extends Cabinet{

		// id начальника отдела дизайна
		protected $director_of_operations_ID = 77; 

		// допуски группы пользователей
		protected $group_access = 9;

		// полльхзователи (работники) производства
		protected $userlist;
		
		// расшифровка меню СНАБ
		public $menu_name_arr = array(
			'important' => 'Важно',
			'get_in_work' => 'Принять в работу',
			'in_processed'=>'обрабатывается',
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
			'production' => 'В производстве',
			'ready_for_shipment' => 'Готов к отгрузке',
			'paused' => 'на паузе',
			'history' => 'история',
			'simples' => 'Образцы',
			'closed'=>'Закрытые',
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
			'my_orders' => 'Мои заказы',
			'makets' => 'Макеты',
			'design' => 'Дизайн',
			'related_changes' => 'Правки',
			'on_production' => 'На производство',
			'films_for_withdrawal' => 'Плёнки на вывод',
			'on_foreign_production' => 'На чужое производство',
			'in_the_production_apelburg' => 'В производстве Апельбург',
			'in_the_production_supplier' => 'В производстве подрядчик',
			// заказы
			
			// 'order_start' => 'Запуск в работу (заказ)',
			// 'order_in_work' => 'Заказы в работе',
			
			// 'design_for_one_men' => 'Дизайн МОЁ',
			// 'production' => 'Производство',

			// новые зелёные вкладки
			'design_waiting_for_distribution' => 'Ожидают распределения',
			'design_develop_design' => 'Разработать дизайн',
			'design_laid_out_a_layout' => 'Сверстать макет',
			'design_wait_laid_out_a_layout' => 'Ожидает дизайн-эскиз',
			'design_edits' => 'Правки',
			'design_on_agreeing' => 'На согласовании',
			'design_prepare_to_print' => 'Подготовить в печать',
			'design_films_and_cliches' => 'Пленки и клише',
			'design_pause_question_TK_is_not_correct' => 'пауза/вопрос/ТЗ не корректно',
			'design_finished_models' => 'Готовые макеты',
			'design_all' => 'Все',
		); 

		// название подраздела кабинета
		private $sub_subsection;

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		// public $POSITION_NO_CATALOG;

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
			// echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// скрываем левое меню за ненадобностью
			echo '<style type="text/css" media="screen">#cabinet_left_coll_menu{display:none;}</style>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				header( 'Location: http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet());
				// // обработка ответа о неправильном адресе
				// $this->response_to_the_wrong_address($method_template);	
			}
		}

				
		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		// роутер по вкладкам 
		

		// private function orders_Template($id_row=0){
		// 	// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
		// 	// создаем экземпляр класса форм
		// 	$this->FORM = new Forms();

		// 	$where = 0;
		// 	// скрываем левое меню
		// 	$html = '';
		// 	$table_head_html = '';
			
		// 	// формируем шапку таблицы вывода
		// 	// $table_head_html .= $this->print_arr($_SESSION);
		// 	$table_head_html .= '<table id="general_panel_orders_tbl">';
		// 		$table_head_html .= '<tr>';
		// 			$table_head_html .= '<th colspan="3" rowspan="2">Артикул/номенклатура/печать</th>';
		// 			$table_head_html .= '<th  rowspan="2">Техническое задание</th>';
		// 			$table_head_html .= '<th>Подрядчик печати</th>';
		// 			$table_head_html .= '<th rowspan="2">Дата сдачи<br>макета</th>';
		// 			$table_head_html .= '<th rowspan="2">Дата утв.<br>макета</th>';
		// 			$table_head_html .= '<th rowspan="2">исполнитель</th>';
		// 			$table_head_html .= '<th rowspan="2">статус дизайна</th>';
		// 			$table_head_html .= '<th rowspan="2">статус снабжение</th>';
		// 		$table_head_html .= '</tr>';
		// 		$table_head_html .= '<tr>';
		// 		$table_head_html .= '<th><span style="float:left; height:100%; padding: 0 5px 0 0; border-right:1px solid grey">М</span><span style="folat:right; padding:0 5px;">пленки / клише</span></th>';
		// 		$table_head_html .= '</tr>';

		// 	global $mysqli;

		// 	$query = "SELECT 
		// 		`".CAB_ORDER_ROWS."`.*, 
		// 		DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		// 		FROM `".CAB_ORDER_ROWS."`";
			
		// 	if($id_row){
		// 		$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
		// 		$where = 1;
		// 	}else{
		// 		// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = ''";
		// 	}

		// 	if(isset($_GET['client_id'])){
		// 		$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
		// 		$where = 1;
		// 	}
			
		// 	$query .= ' ORDER BY `id` DESC';
		// 	// echo $query;
		// 	$result = $mysqli->query($query) or die($mysqli->error);
		// 	$this->Order_arr = array();
			
		// 	if($result->num_rows > 0){
		// 		while($row = $result->fetch_assoc()){
		// 			$this->Order_arr[] = $row;
		// 		}
		// 	}

		// 	$table_order_row = '';		
		// 	// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
		// 	// создаем экземпляр класса форм
		// 	// $this->FORM = new Forms();

		// 	// ПЕРЕБОР ЗАКАЗОВ
		// 	foreach ($this->Order_arr as $this->Order) {
		// 		// цена заказа
		// 		$this->price_order = 0;

		// 		//////////////////////////
		// 		//	open_close   -- start
		// 		//////////////////////////
		// 			// получаем флаг открыт/закрыто
		// 			$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
					
		// 			// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
		// 			// class для кнопки показать / скрыть
		// 			#$this->open_close_class = "";
		// 			// rowspan / data-rowspan
		// 			#$this->open_close_rowspan = "rowspan";
		// 			// стили для строк которые скрываем или показываем
		// 			#$this->open_close_tr_style = ' style="display: table-row;"';

		// 		//////////////////////////
		// 		//	open_close   -- end
		// 		//////////////////////////

		// 		// запоминаем обрабатываемые номера заказа и запроса
		// 		// номер запроса
		// 		$this->query_num = $this->Order['query_num'];
		// 		// номер заказа
		// 		$this->order_num = $this->Order['order_num'];

		// 		// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
		// 		$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

		// 		// запрашиваем информацию по позициям
		// 		$table_order_positions_rows = $this->table_order_positions_rows_Html();
				
		// 		// усли позиций по данному заказу нет - переходим к следующеё итерации цикла
		// 		if($table_order_positions_rows==''){continue;}

		// 		// формируем строку с информацией о заказе
		// 		$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
		// 			$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
		// 									<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
		// 								</td>';
		// 			$table_order_row .= '<td colspan="3" class="orders_info">
		// 								<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>
		// 								'.$this->get_client_name_link_Database($this->Order['client_id']).'
		// 								<span class="greyText">,&nbsp;&nbsp;&nbsp;   менеджер: '.$this->get_manager_name_Database_Html($this->Order['manager_id'],1).'</span>
		// 								<span class="greyText">,&nbsp;&nbsp;&nbsp;   снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>
		// 								<span class="greyText">,&nbsp;&nbsp;&nbsp;   оператор : в разработке</span>
		// 							</td>';
					
		// 			// дата сдачи
		// 			$table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
					
		// 			$table_order_row .= '<td colspan="5"></td>';
					
		// 		$table_order_row .= '</tr>';
		// 		// включаем вывод позиций 
		// 		$table_order_row .= $table_order_positions_rows;
		// 	}		

		// 	$html = $table_head_html.$table_order_row.'</table>';
		// 	echo $html;
		// }


		
		// // возвращает html строки позиций
		// private function table_order_positions_rows_Html(){			
		// 	// получаем массив позиций заказа
		// 	$positions_rows = $this->positions_rows_Database($this->Order['order_num']);
		// 	$html = '';	

		// 	$this->position_item = 1;// порядковый номер позиции
		// 	// формируем строки позиций	(перебор позиций)	
		// 	$n = 0;	
		// 	foreach ($positions_rows as $key => $position) {
		// 		$this->Position_status_list = array(); // в переменную заложим все статусы

		// 		$this->id_dop_data = $position['id_dop_data'];
				
		// 		// ТЗ на изготовление продукцию для НЕКАТАЛОГА
		// 		// для каталога и НЕкаталога способы хранения и получения данной информации различны
		// 		$this->no_cat_TZ = '';
		// 		if(trim($position['type'])!='cat' && trim($position['type'])!=''){
		// 			// доп инфо по некаталогу берём из json 
		// 			$this->no_cat_TZ = $this->decode_json_no_cat_to_html($position);
		// 		}

		// 		// получаем массив услуг по позиции
		// 		$this->position_services_arr = $this->get_order_dop_uslugi( $this->id_dop_data );

		// 		// выборка только массива услуг дизайна
		// 		$this->services_design = $this->get_dop_services_for_production( $this->position_services_arr , $this->user_access );
		// 		// выборка только массива услуг производства
		// 		$this->services_production = $this->get_dop_services_for_production( $this->position_services_arr , 4 );

		// 		$this->services_num  = count($this->services_design);
				
		// 		$n++;				
		// 		// если услуг для производства в данной позиции нет - переходм к следующей
		// 		if($this->services_num == 0){continue;}

		// 		$html_row_1 = '<tr class="position_for_production position_general_row row__'.$n.'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
					
		// 			// // порядковый номер позиции в заказе
		// 			$html_row_1 .= '<td rowspan="'.$this->services_num.'"><span class="orders_info_punct">'.$n.'п</span></td>';
					
		// 			// // описание позиции
		// 			$html_row_1 .= '<td  rowspan="'.$this->services_num.'" >';

		// 				// вставляем номер заказа
		// 				$html_row_1 .= '№ '.$this->order_num_for_User.'<br>';
		// 				// наименование товара
		// 				$html_row_1 .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
		// 				// описание некаталожной продукции
		// 				$html_row_1 .= $this->no_cat_TZ;
		// 				// места нанесения
		// 				$html_row_1 .= $this->get_service_printing_list();

		// 				// // массив по позиции
		// 				// $html_row_1 .= 'массив позиции<br>';
		// 				// $html_row_1 .= $this->print_arr($position);

		// 				// // массив всeх услуг
		// 				// $html_row_1 .= 'массив всех услуг<br>';
		// 				// $html_row_1 .= $this->print_arr($this->position_services_arr);

		// 				// // массив услуг печати
		// 				// $html_row_1 .= 'массив услуг печати<br>';
		// 				// $html_row_1 .= $this->print_arr($this->services_production);
		// 				// добавляем тираж
		// 				$html_row_1 .= 'Тираж: '.($position['quantity']) .' шт.';	


		// 				$html_row_1 .= '<div class="linked_div">'.identify_supplier_by_prefix($position['art']).'</div>';
		// 			$html_row_1 .= '</td>';

		// 			// $html_row_2 = '<td rowspan="'.$this->services_num.'">1</td>';
		// 			$html_row_2 = '<td rowspan="'.$this->services_num.'" >
		// 						<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product'],0,$position['id']).'</div>
		// 					</td>';

		// 		$html_row_2 .= '</tr>';	


		// 		$html .= $this->get_service_content_for_production($position,$this->services_design,$html_row_1,$html_row_2);


				


		// 		$this->position_item = $this->position_item+1+$this->services_num-1;
		// 	}				
		// 	return $html;
		// }

		// // места нанесения
		// private function get_service_printing_list(){
		// 	//если нет прикрепленных мест печати - выходим
		// 	if(empty($this->services_production)){return '';}


		// 	if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
		// 		$this->Services_list_arr = $this->get_all_services_Database();
		// 	}


		// 	$html = '';
		// 	$n = 1;
		// 	$service_name = '';
			
		// 	// перебираем услуги нанесения по позиции
		// 	foreach ($this->services_production as $key => $service) {
		// 		if($service_name != $this->Services_list_arr[$service['uslugi_id']]['name']){
		// 			if($service_name == ''){$html .= '<br>';}
		// 			$service_name = $this->Services_list_arr[$service['uslugi_id']]['name'];
		// 			$html .= $service_name.'<br>';
		// 			$n = 1;
		// 		}
		// 		$html .= 'место '.$n++.': ';
				
		// 		// декодируем dop_inputs для услуги печати
		// 		$decode_dop_inputs_information_for_servece = $this->decode_dop_inputs_information_for_servece($service);
		// 		$html .= (($decode_dop_inputs_information_for_servece != "")?$decode_dop_inputs_information_for_servece:'<span style="color:red">информация отсутствует</span>').'<br>';
		// 	}
		// 	return $html;	

		// }		


		// // фильтр услуг
		// private function get_dop_services_for_production($services_arr, $user_access){
		// 	if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
		// 		$this->Services_list_arr = $this->get_all_services_Database();
		// 	}
		// 	// объявляем массив, который будем возвращать 
		// 	$new_arr = array();
			
		// 	foreach ($services_arr as $key => $service) {
		// 		// если такая услуга существует в базе
		// 	 	if(isset( $this->Services_list_arr[$service['uslugi_id']]) ){
		// 	 		// если доступ позволяет её обрабатывать
		// 	 		/*
		// 				Т.к. в данном случае дизайнер работает не со всеми услугами производства, отфильтровываем все услуги по флагу maket_true
		// 			*/
		// 	 		if($this->Services_list_arr[ $service['uslugi_id'] ]['performer'] == $user_access && $this->Services_list_arr[ $service['uslugi_id'] ]['maket_true'] == "on"){
		// 	 			// добавляем услугу в новый массив 
		// 	 			$new_arr[] = $service;
		// 	 		}

		// 	 	}
		// 	}
		// 	// возвращаем отфильтрованный список услуг
		// 	return $new_arr;
		// }


		// // докодируем доп поля по услугам в читабельный вид
		// private function decode_dop_inputs_information_for_servece($service){
		// 	global $mysqli;
		// 	$html = '';
		// 	//////////////////////////
		// 	//	ДОП ПОЛЯ
		// 	//////////////////////////
		// 	if(!isset($this->dop_inputs_listing)){
		// 		// получаем список всех полей
		// 		$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
		// 		$result = $mysqli->query($query) or die($mysqli->error);
		// 		$this->dop_inputs_listing = array();
		// 		if($result->num_rows > 0){
		// 			while($row = $result->fetch_assoc()){
		// 				$this->dop_inputs_listing[$row['name_en']] = $row;
		// 			}
		// 		}

		// 	}
			
			

		// 	// получаем  json
		// 	$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
		// 	// декодируем json  в массив
		// 	$this->print_details_dop = json_decode($this->print_details_dop_Json, true);
			
		// 	if(!isset($this->print_details_dop)){
		// 		$html .= "<div>произошла ошибка json</div>";
		// 	}
				
		// 	$n=0;
		// 	// раскодируем jsondop_inputs	
		// 	if(isset($this->print_details_dop) && !empty($this->print_details_dop)){
		// 		//echo  $service['print_details_dop'];
		// 		$n=0;
		// 		foreach ($this->print_details_dop as $key => $text) {
		// 			$html .= (($n>0)?', ':'').$this->dop_inputs_listing[$key]['name_ru'].': '.base64_decode($text);
		// 			$n++;
		// 		}
		// 	}

		// 	return $html;
		// }


		// // выводит строки услуг для дизайнеров и операторов
		// private function get_service_content_for_production($position, $services_arr, $html_row_1, $html_row_2){
		// 	if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
		// 		$this->Services_list_arr = $this->get_all_services_Database();
		// 	}

		// 	$gen_html = '';
		// 	$n = 0;

		// 	$service_count = count($services_arr);
			
		// 	// перебираем услуги по позиции
		// 	foreach ($services_arr as $key => $service) {
		// 		// получаем  json
		// 		$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
		// 		// декодируем json  в массив
		// 		$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



		// 		// получаем наименование услуги
		// 		$this->Service_name = (isset($this->Services_list_arr[ $service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

		// 		$html = '';
		// 		$html .= ($n>0)?'<tr class="position_for_production row__'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>':'';
		// 			// место
					

		// 			// операция
		// 			$html .= '<td class="show_backlight show_dialog_tz_for_production" data-id="'.$service['id'].'">';
		// 				$html .= $this->Service_name;


		// 				// перебираем производственные услуги к которым дизайнер/оператор будет готовить макет или дизайн
		// 				foreach ($this->services_production as $key_production_service => $production_service) {
		// 					$html .= '<div class="seat_number_logo">';
		// 						$html .= 'место'.($key_production_service+1).' ('.$this->Services_list_arr[ $production_service['uslugi_id'] ]['name'].'): ';
		// 						$html .= $production_service['logotip'];
		// 					$html .='</div>';	
		// 				}

		// 				// выводим ТЗ
		// 				//$html .= '<br>'.$service['tz'];

		// 			$html .= '</td>';

					
		// 			$html .= '<td class="show_backlight">';
		// 				// подрядчик печати 	
		// 				$html .= $position['suppliers_name'];
		// 				// пленки / клише
		// 				$html .= $this->get_film_and_cliches();
		// 			$html .= '</td>';

		// 			// // плёнки / клише
		// 			// $html .= '<td class="show_backlight">';
		// 			// 	$html .= $this->get_statuslist_film_photos($service['film_photos_status'],$service['id']);
		// 			// $html .= '</td>';
					

		// 			// дата сдачи
		// 			$html .= '<td class="show_backlight">';
		// 				$html .= '<span class="greyText">'.$this->Order['date_of_delivery_of_the_order'].'</span>';
		// 			$html .= '</td>';
					
		// 			// дата работы
		// 			$html .= '<td class="show_backlight">';
		// 				$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" class="calendar_date_work">';
		// 			$html .= '</td>';

		// 			// исполнитель услуги
		// 			$html .= '<td class="show_backlight">';
		// 				$html .= $this->get_production_userlist_Html($service['performer_id'],$service['id']);
		// 			$html .= '</td>';

		// 			// статус готовности
		// 			$html .= '<td class="show_backlight">';
		// 				$html .= $this->get_statuslist_uslugi_Dtabase_Html($service['uslugi_id'],$service['performer_status'],$service['id'], $service['performer']);
		// 			$html .= '</td>';

		// 			// // % готовности
		// 			// $html .= '<td class="show_backlight percentage_of_readiness" contenteditable="true" data-service_id="'.$service['id'].'">';
		// 			// 	$html .= $service['percentage_of_readiness'];
		// 			// $html .= '</td>';
		// 		$html .= ($n>0)?'</tr>':'';

		// 		if($n==0){// это дополнительные колонки в уже сформированную строку
		// 			// оборачиваем колонки в html переданный в качестве параметра
		// 			$gen_html .= $html_row_1 . $html . $html_row_2;
		// 		}else{
		// 			$gen_html .= $html;
		// 		}
		// 		$n++;
		// 	}
		// 	return $gen_html ;
		// }

		// // информация о плёнках и клише
		// private function get_film_and_cliches(){
		// 	// если услуг печати нет - выходим
		// 	if(empty($this->services_production)){return '';}
			
		// 	$html = '';
		// 	//return $this->print_arr($this->services_production);
		// 	// перебираем услуги печати
		// 	$n = 1;
		// 	foreach ($this->services_production as $key => $production_service) {
		// 		$html .= '<div class="seat_number_film"><span class="seat_number">'.$n++.'</span>'.$this->get_statuslist_film_photos($production_service['film_photos_status'],$production_service['id']).'</div>';
		// 	}
		// 	return $html;
		// }

		// // отдаёт имя пользователя, список пользователей или 
		// private function get_production_userlist_Html($performer_id, $service_id){

		// 	// получаем список пользователей производства
		// 	$this->get_production_userlist_Database();

		// 	$html = '';
		// 	// регулируем вывод в зависимости от уровня доступа
		// 	switch ($this->user_access) {
		// 		case '1': // для админа список
		// 			$html .= '<select class="production_userlist">';
		// 			foreach ($this->userlist as $key => $user) {
		// 				$checked = ($performer_id == $user['id'])?' selected="selected"':'';
		// 				$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
		// 			}
		// 			$html .= '</select>';
		// 			return $html;
		// 			break;
		// 		case '4': 
		// 			if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
		// 				$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
		// 				$html .= '<option value=""></option>';
		// 				foreach ($this->userlist as $key => $user) {
		// 					$checked = ($performer_id == $user['id'])?' selected="selected"':'';
		// 					$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
		// 				}
		// 				$html .= '</select>';
		// 				return $html;
		// 			}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
		// 				if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
		// 					$user = $this->userlist[$performer_id];
		// 					return $user['name'].' '.$user['last_name'];
		// 				}else{
		// 					$user = $this->userlist[$this->user_id];
		// 					return '<input type="button" value="Взать в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
		// 				};
		// 			}
		// 			break;
		// 		case '9': 
		// 			if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
		// 				$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
		// 				$html .= '<option value=""></option>';
		// 				foreach ($this->userlist as $key => $user) {
		// 					$checked = ($performer_id == $user['id'])?' selected="selected"':'';
		// 					$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
		// 				}
		// 				$html .= '</select>';
		// 				return $html;
		// 			}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
		// 				if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
		// 					$user = $this->userlist[$performer_id];
		// 					return $user['name'].' '.$user['last_name'];
		// 				}else{
		// 					$user = $this->userlist[$this->user_id];
		// 					return '<input type="button" value="Взать в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
		// 				};
		// 			}
		// 			break;
				
		// 		default: // для остальных просто то, что хранится в ячейке
		// 			if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
		// 				$user = $this->userlist[$performer_id];
		// 				return $user['name'].' '.$user['last_name'];
		// 			}else{
		// 				return 'исполнитель не назначен';
		// 			};
		// 			break;
		// 	}			
		// }

		


		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################

		
		
		function __destruct(){}
}


?>