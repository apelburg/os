<?php

	class Cabinet_production_class extends Cabinet{

		// id начальника отдела производство
		protected $director_of_operations_ID = 42;

		// допуски группы пользователей
		protected $group_access = 4;

		// полльхзователи (работники) производства
		protected $userlist;
		
		// расшифровка меню СНАБ
		public $menu_name_arr = array(
			'important' => 'Важно',
			'in_processed'=>'обрабатывается',
			'no_worcked_snab' => 'Не обработанные СНАБ',		
			'no_worcked_men' => 'Не обработанные МЕН',
			'in_work' => 'В работе',
			'send_to_snab' => 'Отправлены в СНАБ',
			'calk_snab' => 'Рассчитанные',
			'ready_KP' => 'Выставлено КП',
			'denied' => 'ТЗ не корректно',
			// 'all' => 'Все заказы',
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

			////////////////////////////
			// заказы
			/////////////////////////////
			// новые зелёные вкладки
			////////////////////////////
			
				'production_get_in_work' => 'Ожидают распределения',
				'set_in_the_plan' => 'Поставлены в план',
			'production_stencil_shelk_and_transfer' => 'Трафарет',
			'production_shelk' => 'Шелкография',
			'production_transfer' => 'Термотрансфер',
			'production_tampoo' => 'Тампопечать',
			'production_tisnenie' => 'Тиснение',
			'production_dop_uslugi' => 'Доп. услуги',
			'production_plenki_and_klishe' => 'Проверить пленки',
				'question_pause' => 'Вопрос, пауза',
				'the_service_is_performed' => 'Услуга выполнена',
				// 'order_all' => 'Все'
				'production' => 'Все'

				
				// 'order_start' => 'Запуск в работу (заказ)',
				// 'order_in_work' => 'Заказы в работе',
				// 'design_all' => 'Дизайн ВСЕ',
				// 'design_for_one_men' => 'Дизайн МОЁ',
				// 'production' => 'Всё',
				// 'stock' => 'Склад'
				
				

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
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// скрываем левое меню за ненадобностью
			echo '<style type="text/css" media="screen">#cabinet_left_coll_menu{display:none;}</style>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				// обработка ответа о неправильном адресе
				$this->response_to_the_wrong_address($method_template);	
			}
		}

		
		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		// private function orders_Template($id_row=0){
		// 	$where = 0;
		// 	// скрываем левое меню
		// 	$html = '';
		// 	$table_head_html = '<style type="text/css" media="screen">
		// 		#cabinet_left_coll_menu{display:none;}
		// 	</style>';
			
		// 	// формируем шапку таблицы вывода
		// 	$table_head_html .= '
		// 		<table id="general_panel_orders_tbl">
		// 		<tr>
		// 			<th colspan="3">Артикул/номенклатура/печать</th>
		// 			<th>М</th>
		// 			<th>операции</th>
		// 			<th>тираж</th>
		// 			<th>запас</th>
		// 			<th>цвета</th>
		// 			<th>логотип нанесения</th>
		// 			<th>пплёнки/клише</th>
		// 			<th>статус товара</th>
		// 			<th>дата сдачи</th>
		// 			<th>дата работы</th>
		// 			<th>дмастер</th>
		// 			<th>статус операции</th>
		// 			<th>% гот-ти</th>
		// 			<th>статус позиции</th>
		// 		</tr>
		// 	';

		// 	global $mysqli;

		// 	$query = "SELECT 
		// 		`".CAB_ORDER_ROWS."`.*, 
		// 		DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		// 		FROM `".CAB_ORDER_ROWS."`";
			
		// 	if($id_row){
		// 		$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
		// 		$where = 1;
		// 	}else{
		// 		// фильтрация по клиенту
		// 		if(isset($_GET['client_id'])){
		// 			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
		// 			$where = 1;
		// 		}

		// 		//////////////////////////
		// 		//	filter_list   -- start
		// 		//////////////////////////
		// 			$query .= $this->get_filter_list_Order( $where );
		// 		//////////////////////////
		// 		//	filter_list 	-- end
		// 		//////////////////////////

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
				
		// 		if($table_order_positions_rows == ''){continue;}

				
		// 		// формируем строку с информацией о заказе
		// 		$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
		// 			$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
		// 									<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
		// 								</td>';
		// 			$table_order_row .= '<td colspan="10" class="orders_info">
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
		// 	foreach ($positions_rows as $key => $position) {
		// 		$this->Position_status_list = array(); // в переменную заложим все статусы

		// 		$this->id_dop_data = $position['id_dop_data'];

				
		// 		// выборка только массива печати
		// 		$this->services_print = $this->get_dop_services_for_production( $this->get_order_dop_uslugi( $this->id_dop_data ) );

		// 		$this->services_num  = count($this->services_print);
								
		// 		// если услуг для производства в данной позиции нет - переходм к следующей
		// 		if($this->services_num == 0){continue;}

		// 		$html_row_1 = '<tr class="position_for_production position_general_row row__'.$this->position_item.'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
				
		// 			// // порядковый номер позиции в заказе
		// 			$html_row_1 .= '<td rowspan="'.$this->services_num.'"><span class="orders_info_punct">'.$this->position_item.'п</span></td>';
					
		// 			// // описание позиции
		// 			$html_row_1 .= '<td  rowspan="'.$this->services_num.'" >';
		// 				// наименование товара
		// 				$html_row_1 .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
		// 			$html_row_1 .= '</td>';

		// 			// $html_row_2 = '<td rowspan="'.$this->services_num.'">1</td>';
		// 			// статус снабжение
		// 			$html_row_2 = '<td rowspan="'.$this->services_num.'" >
		// 						<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product'],0,$position['id']).'</div>
		// 					</td>';

		// 		$html_row_2 .= '</tr>';	


		// 		$html .= $this->get_service_content_for_production($position,$this->services_print,$html_row_1,$html_row_2);


				


		// 		$this->position_item = $this->position_item+1+$this->services_num-1;
		// 	}				
		// 	return $html;
		// }		

		// фильтр услуг для производства
		private function get_dop_services_for_production($services_arr){
			if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
				$this->Services_list_arr = $this->get_all_services_Database();
			}
			// объявляем массив, который будем возвращать 
			$new_arr = array();
			
			foreach ($services_arr as $key => $service) {
				// если такая услуга существует в базе
			 	if(isset( $this->Services_list_arr[$service['uslugi_id']]) ){
			 		// если доступ позволяет её обрабатывать
			 		if($this->Services_list_arr[ $service['uslugi_id'] ]['performer'] == $this->user_access){
			 			// добавляем услугу в новый массив 
			 			$new_arr[] = $service;
			 		}

			 	}
			}
			// возвращаем отфильтрованный список услуг
			return $new_arr;
		}

		// выводит строки услуг для производства
		private function get_service_content_for_production($position, $services_arr, $html_row_1, $html_row_2){
			if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
				$this->Services_list_arr = $this->get_all_services_Database();
			}

			$gen_html = '';
			$n = 0;
			
			// перебираем услуги по позиции
			foreach ($services_arr as $key => $service) {
				// получаем  json
				$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
				// декодируем json  в массив
				$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



				// получаем наименование услуги
				$this->Service_name = (isset($this->Services_list_arr[ $service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

				$html = '';
				$html .= ($n>0)?'<tr class="position_for_production row__'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>':'';
					// место
					$html .= '<td class="show_backlight">';
						$html .= ($key+1);

						// ob_start();
						// echo '<pre>';
						// print_r($position);
						// echo '</pre>';			    	
						// $content = ob_get_contents();
						// ob_get_clean();
					$html .= '</td>';

					// операция
					$html .= '<td class="show_backlight show_dialog_tz_for_production" data-id="'.$service['id'].'">';
						$html .= $this->Service_name;

						// ob_start();
						// echo '<pre>';
						// print_r($service);
						// echo '</pre>';			    	
						// $content = ob_get_contents();
						// ob_get_clean();
						// $html .= $content;
					$html .= '</td>';

					// тираж
					$html .= '<td class="show_backlight">';
						$html .= $position['quantity'];
					$html .= '</td>';

					// запас
					$html .= '<td class="show_backlight">';
						$html .= (($position['zapas']!=0 && trim($position['zapas'])!='')?(($position['print_z']==0)?'+'.$position['zapas'].'<br>НПЗ':'+'.$position['zapas'].'<br>ПЗ'):'');
					$html .= '</td>';

					// цвета, логотип и другие персонализированные данные мы оставляем в окне ТЕХ. ЗАДАНИЕ
					$html .= '<td class="show_backlight">';
						// комментарии для решения возможных проблем

						$html .= '<!--// ключ к полю возможно будет отличаться не в локальной версии... при изменении названия поля Пантоны... выгрузка информации сюда изменится -->';
						$html .= (isset($this->print_details_dop['pantony'])?$this->print_details_dop['pantony']:'');
					$html .= '</td>';

					
					$html .= '<td class="show_backlight" data-id="'.$service['id'].'">';
						$html .= $service['logotip'];
					$html .= '</td>';

					// плёнки / клише
					$html .= '<td class="show_backlight">';
						$html .= $this->get_statuslist_film_photos($service['film_photos_status'],$service['id']);
					$html .= '</td>';
					// статус товара
					$html .= '<td class="show_backlight">';
						$html .= $this->decoder_statuslist_sklad($position['status_sklad'], $position['id']);
					$html .= '</td>';
					// дата сдачи
					$html .= '<td class="show_backlight">';
						$html .= '<span class="greyText">'.$this->Order['date_of_delivery_of_the_order'].'</span>';
					$html .= '</td>';
					// дата работы
					$html .= '<td class="show_backlight">';
						$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" class="calendar_date_work">';
					$html .= '</td>';
					// мастер
					$html .= '<td class="show_backlight">';
						$html .= $this->get_production_userlist_Html($service['performer_id'],$service['id']);
					$html .= '</td>';
					// статус готовности
					$html .= '<td class="show_backlight">';
						$html .= $this->get_statuslist_uslugi_Dtabase_Html($service['uslugi_id'],$service['performer_status'],$service['id'], $service['performer']);
					$html .= '</td>';
					// % готовности
					$html .= '<td class="show_backlight percentage_of_readiness" contenteditable="true" data-service_id="'.$service['id'].'">';
						$html .= $service['percentage_of_readiness'];
					$html .= '</td>';
				$html .= ($n>0)?'</tr>':'';

				if($n==0){// это дополнительные колонки в уже сформированную строку
					// оборачиваем колонки в html переданный в качестве параметра
					$gen_html .= $html_row_1 . $html . $html_row_2;
				}else{
					$gen_html .= $html;
				}
				$n++;
			}
			return $gen_html ;
		}

		/*
		ПЕРЕНЕСЁН В cabinet_order_class.php
		// отдаёт имя пользователя, список пользователей или 
		private function get_production_userlist_Html($performer_id, $service_id){

			// получаем список пользователей производства
			$this->get_production_userlist_Database();

			$html = '';
			// регулируем вывод в зависимости от уровня доступа
			switch ($this->user_access) {
				case '1': // для админа список
					$html .= '<select class="production_userlist">';
					foreach ($this->userlist as $key => $user) {
						$checked = ($performer_id == $user['id'])?' selected="selected"':'';
						$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
					}
					$html .= '</select>';
					return $html;
					break;
				case '4': 
					if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
						$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
						$html .= '<option value=""></option>';
						foreach ($this->userlist as $key => $user) {
							$checked = ($performer_id == $user['id'])?' selected="selected"':'';
							$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
						}
						$html .= '</select>';
						return $html;
					}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
						if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
							$user = $this->userlist[$performer_id];
							return $user['name'].' '.$user['last_name'];
						}else{
							$user = $this->userlist[$this->user_id];
							return '<input type="button" value="Взять в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
						};
					}
					break;
				
				default: // для остальных просто то, что хранится в ячейке
					if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
						$user = $this->userlist[$performer_id];
						return $user['name'].' '.$user['last_name'];
					}else{
						return 'исполнитель не назначен';
					};
					break;
			}			
		}
		*/		


		

		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################

		
		
		function __destruct(){}
}


?>