<?php
	
	class Cabinet_sklad_class extends Cabinet{
		
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

			'waits_products' => 'Продукция ожидается',
			'goods_in_stock' => 'На складе',
			'sended_on_outsource' => 'У поставщика в пр-ве',
			// 'pclosing_documents' => 'Закрывающие документы',
			// 'checked_and_packed'  => 'Заказы на отгрузку',
			// 'goods_shipped_for_client' => 'Отгруженные',
			'otgrugen' => 'Отгруженные',
			'we_are_waiting_the_products' => 'Ожидается',
			// заказы
			'stock_all' => 'Все в работе',
			'stock_waits_products' => 'Продукция ожидается',
			'stock_goods_in_stock' => 'На складе',
			'stock_sended_on_outsource' => 'Отправлено на аутсорс',
			'stock_checked_and_packed' =>'Заказы на отгрузку',
			'stock_goods_shipped_for_client' => 'Отгруженные',

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
				header( 'Location: http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet());
				// // обработка ответа о неправильном адресе
				// $this->response_to_the_wrong_address($method_template);	
			}
		}
		
		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		
			protected function orders_Template_OLD($id_row=0){

				$this->get_filters();
				$where = 0;
				// скрываем левое меню
				$html = '';
				$table_head_html = '<style type="text/css" media="screen">
										#cabinet_left_coll_menu{display:none;}
									</style>';
				// $html = '';
				$table_head_html .= '
					<table id="general_panel_orders_tbl">
					<tr>
						<th colspan="3">Артикул/номенклатура/печать</th>
						<th>тираж</th>
						<th>логотип</th>
						<th>поставщик товара</th>
						<th>№ резерва</th>
						<th>подрядчик печати</th>
						<th>дата отгрузки</th>
						<th>статус товара</th>
						<th>статус заказа</th>
					</tr>
				';

				global $mysqli;

				$query = "SELECT 
					`".CAB_ORDER_ROWS."`.*, 
					DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
					FROM `".CAB_ORDER_ROWS."`";
				
				if($id_row){
					$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
					$where = 1;
				}else{


					// filters for the client id
					if(isset($_GET['client_id'])){
						$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".(int)$_GET['client_id']."'";
						$where = 1;
					}

					// filters 
					if(isset($_GET['order_num'])){
						$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`order_num` = '".(int)$_GET['order_num']."'";
						$where = 1;
					}




					// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = ''";
				}

				
				
				//////////////////////////
				//	sorting
				//////////////////////////
				$query .= ' ORDER BY `id` DESC';
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$this->Order_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Order_arr[] = $row;
					}
				}

				$table_order_row = '';		
				// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
				// создаем экземпляр класса форм
				// $this->FORM = new Forms();

				// ПЕРЕБОР ЗАКАЗОВ
				foreach ($this->Order_arr as $this->Order) {
					// цена заказа
					$this->price_order = 0;

					//////////////////////////
					//	open_close   -- start
					//////////////////////////
						// получаем флаг открыт/закрыто
						$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
						
						// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
						// class для кнопки показать / скрыть
						#$this->open_close_class = "";
						// rowspan / data-rowspan
						#$this->open_close_rowspan = "rowspan";
						// стили для строк которые скрываем или показываем
						#$this->open_close_tr_style = ' style="display: table-row;"';

					//////////////////////////
					//	open_close   -- end
					//////////////////////////

					// запоминаем обрабатываемые номера заказа и запроса
					// номер запроса
					$this->query_num = $this->Order['query_num'];
					// номер заказа
					$this->order_num = $this->Order['order_num'];

					// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
					$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

					// запрашиваем информацию по позициям
					$table_order_positions_rows = $this->table_order_positions_rows_Html();
					if($table_order_positions_rows == ''){ continue; }
					// формируем строку с информацией о заказе
					$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
						$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
												<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
											</td>';
						$table_order_row .= '<td colspan="6" class="orders_info">
											<span class="greyText">Заказ №: </span><a href="?page=cabinet'.(isset($_GET['section'])?'&section='.$_GET['section']:'').(isset($_GET['subsection'])?'&subsection='.$_GET['subsection']:'').'&client_id='.$this->Order['client_id'].'&order_num='.$this->order_num_for_User.'">'.$this->order_num_for_User.'</a> 
											<span class="greyText">,&nbsp;&nbsp;&nbsp;   Кампания : </span>'.$this->get_client_name_link_Database($this->Order['client_id']).'
											<span class="greyText">,&nbsp;&nbsp;&nbsp;   Юр.лицо : в разработке</span>
											<span class="greyText">,&nbsp;&nbsp;&nbsp;   менеджер: '.$this->get_manager_name_Database_Html($this->Order['manager_id'],1).'</span>
											<span class="greyText">,&nbsp;&nbsp;&nbsp;   снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>
										</td>';
						$table_order_row .= '<td>
											<!--// comments -->
											<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>	
										</td>';
						// $table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
										$table_order_row .= '<td><strong></strong></td>';
						$table_order_row .= '<td><span class="greyText">заказа: </span></td>';
						$table_order_row .= '<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
					$table_order_row .= '</tr>';
					// включаем вывод позиций 
					$table_order_row .= $table_order_positions_rows;
				}		

				$html = $table_head_html.$table_order_row.'</table>';
				echo $html;
			}
			
			protected function get_filters_OLD(){
				$this->filter_order = ' '; // status_global и status_buch
				$this->filter_position = ' '; // status_sklad и status_snab
				$this->filter_uslugi = ' ';// uslugi_id и performer_status и performer


				if (isset($_GET['subsection'])) {
					switch ($_GET['section'].'_'.$_GET['subsection']) {
							case 'orders_waits_products': // взять в работу
								$this->filter_position = ' AND `'.CAB_ORDER_MAIN.'`.`status_snab` = \'waits_products\'';
								break;
							case 'orders_goods_in_stock': // на складе
								$this->filter_position = ' AND `'.CAB_ORDER_MAIN.'`.`status_sklad` = \'goods_in_stock\'';
								break;
							case 'orders_sended_on_outsource': // у поставщика в пр-ве
								$this->filter_position = ' AND `'.CAB_ORDER_MAIN.'`.`status_sklad` = \'sended_on_outsource\'';
								break;
							case 'orders_checked_and_packed': // у поставщика в пр-ве
								$this->filter_position = ' AND `'.CAB_ORDER_MAIN.'`.`status_sklad` = \'checked_and_packed\'';
								break;
							case 'orders_goods_shipped_for_client': // у поставщика в пр-ве
								$this->filter_position = ' AND `'.CAB_ORDER_MAIN.'`.`status_sklad` = \'goods_shipped_for_client\'';
								break;


							default:
								# code...
								break;
						}	
				}			
			}

			// возвращает html строки позиций
			private function table_order_positions_rows_Html_OLD(){			
				// получаем массив позиций заказа
				$positions_rows = $this->positions_rows_Database($this->Order['order_num']);
				$html = '';	

				$this->position_item = 1;// порядковый номер позиции
				// формируем строки позиций	(перебор позиций)		
				foreach ($positions_rows as $key => $position) {
					$this->Position_status_list = array(); // в переменную заложим все статусы

					$this->id_dop_data = $position['id_dop_data'];

					$this->logotip = $this->get_content_logotip($this->id_dop_data);
					
					$html .= '<tr class="positions_rows row__'.$this->position_item.'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
					// // порядковый номер позиции в заказе
					$html .= '<td><span class="orders_info_punct">'.$this->position_item.'п</span></td>';
					// // описание позиции
					$html .= '<td>';
					
					// наименование товара
					$html .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
				
					$html .= '</td>';
					// тираж
					$html .= '<td>';
						$html .= '<div class="quantity">'.($position['quantity']+$position['zapas']).'</div>';
					$html .= '</td>';


					// логотип
					$html .= '<td><span class="greyText">'.$this->logotip.'</span></td>';
					
					// поставщик товара  
					$html .= '<td>
								<div class="supplier">'.$this->get_supplier_name($position['art']).'</div>
							</td>';
					// № резерва
					$html .= '<td>
								<div class="number_rezerv">'.$position['number_rezerv'].'</div>
							</td>';

					// подрядчик печати
					$html .= '<td>
								<div>'.$position['suppliers_name'].'</div>
							</td>';

					// дата отгрузки
					$html .= '<td>';
						// $html .= '<div>'.$this->Order['date_of_delivery_of_the_order'].'</div>';
						$html .= '</td>';

					// статус товара
					$html .= '<td>
								<div>'.$this->decoder_statuslist_sklad($position['status_sklad'],$position['id']).'</div>
							</td>';
					// статус снабжение
					$html .= '<td>
								<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product'],0,$position['id']).'</div>
							</td>';

					$html .= '</tr>';	
					$this->position_item++;
				}				
				return $html;
			}	

			
		

		
		


		// //////////////////////////
		// //	Section - На отгрузку
		// //////////////////////////
		// private function for_shipping_Template(){
		// 	echo 'Раздел в разработке =)';
		// }	


		// //////////////////////////
		// //	Section - Закрытые
		// //////////////////////////
		// private function closed_Template(){
		// 	echo 'Раздел в разработке =)';
		// }	


		// //////////////////////////
		// //	Section - Образцы
		// //////////////////////////
		// private function simples_Template(){
		// 	echo 'Раздел в разработке =)';
		// }	


		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################

		
		
		function __destruct(){}
}


?>