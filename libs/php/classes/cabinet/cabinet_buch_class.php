<?php
	
	class Cabinet_buch_class extends Cabinet{

		private $order_status2 = array(
			'being_prepared'=>'В оформлении',
			'request_expense'=>'Запрошен счёт',
			'in_work'=>'В работе',
			'ready_for_shipment'=>'Готов к отгрузке',
			'shipped'=>'Отгружен',
			'paused'=>'Приостановлен',		
			'cancelled'=>'Аннулирован'
			);

		private $buch_status2 = array(
    		'score_exhibited' => 'счёт выставлен',
			'payment' => 'оплачен',//дата в таблицу
			'partially_paid' => 'частично оплачен',//дата в таблицу			
			'prihodnik_on_bail' => 'приходник на залог',
			'cancelled'=>'Аннулирован',		
			'returns_client_collateral' => 'возврат залога клиенту',
			'refund_in_a_row' => 'возврат денег по счёту',
			'ogruzochnye_accepted' => 'огрузочные приняты (подписанные)'
    	);

		// расшифровка меню СНАБ
		public $menu_name_arr = array(
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
		'history' => 'история',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные'													
		); 

		// название подраздела кабинета
		private $sub_subsection;

		

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

			
		}


		private function _AJAX_($name){
			$method_AJAX = $name.'_AJAX';
			// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
			if(method_exists($this, $method_AJAX)){
				$this->$method_AJAX();
				exit;
			}					
		}

		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();
			}else{
				echo 'метод '.$method_template.' не предусмотрен';
			}
		}



		############################################
		###				AJAX START               ###
		############################################
		// возвращает html строки запроса в json 
		private function replace_query_row_AJAX(){
			global $mysqli;
			// получаем строку из os__rt_list
			$query = "SELECT `".RT_LIST."`.*, 
				(UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)-UNIX_TIMESTAMP())*(-1) AS `time_attach_manager_sec`,
				SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)) AS `time_attach_manager`,
				
				DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".RT_LIST."` WHERE `id` = '".(int)$_POST['os__rt_list_id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$zapros = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$zapros[] = $row;
				}
			}
			// для обсчёта суммы за тираж			
			include_once ('./libs/php/classes/rt_class.php');
			
			// массви с переводом статусов запроса
			$name_cirillic_status['new_query'] = 'новый запрос'; // видит только админ
			$name_cirillic_status['not_process'] = 'не обработан менеджером';
			$name_cirillic_status['taken_into_operation'] = 'взят в обработку';
			$name_cirillic_status['in_work'] = 'в работе';
			$name_cirillic_status['history'] = 'история';
			
			foreach ($zapros as $key => $value) {
				$overdue = (($value['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
				$html = '<td class="show_hide" rowspan="2"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&query_num='.$value['query_num'].'">'.$value['query_num'].'</a> </td>
							<td><span data-sec="'.$value['time_attach_manager_sec']*(-1).'" '.$overdue.'>'.$value['time_attach_manager'].'</span></td>
							<td>'.$value['create_time'].''.$this->get_manager_name_Database_Html($value['manager_id']).'</td>
							<td><span data-rt_list_query_num="'.$value['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($value['query_num']).'"></span></td>
							<td>'.$this->get_client_name_Database($value['client_id']).'</td>
							<td>'.RT::calcualte_query_summ($value['query_num']).'</td>
							<td class="'.$value['status'].'_'.$this->user_access.'">'.$name_cirillic_status[$value['status']].'</td>';

			}
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
					
			// echo $html;
		}
		############################################
		###				AJAX END                 ###
		############################################







		#############################################################
		##                          START                          ##
		##      методы для работы с поддиректориями subsection     ##
		#############################################################

		##########################################
		################ Важно
		Private Function important_Template(){
			echo 'Раздел в разработке =)';
		}

		################ Важно_END
		##########################################


		##########################################
		## Предзаказ
		Private Function paperwork_Template(){

			global $mysqli;
			
			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'being_prepared'";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			
			
			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				//if(!isset($value2)){continue;} // !!!!!!!!!!!!!!!!!
				$order_num_1 = Cabinet::show_order_num($value['order_num']);
				$invoice_num = $value['invoice_num'];


				$query = "
				SELECT 
					`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
					`".CAB_ORDER_DOP_DATA."`.`quantity`,	
					`".CAB_ORDER_DOP_DATA."`.`price_out`,	
					`".CAB_ORDER_DOP_DATA."`.`print_z`,	
					`".CAB_ORDER_DOP_DATA."`.`zapas`,	
					DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".CAB_ORDER_MAIN."`.*,
					`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
					`".CAB_ORDER_ROWS."`.`global_status`,
					`".CAB_ORDER_ROWS."`.`payment_status`,
					`".CAB_ORDER_ROWS."`.`number_pyament_list`
					FROM `".CAB_ORDER_MAIN."` 
					INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
					LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

				$main_rows = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				$main_rows_id = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$main_rows[] = $row;
					}
				}

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				//$html .= '<td class="show_hide"><span class="this->cabinett_row_hide"></span></td>';
				$html .= '<td colspan="11" class="each_art">';
				
				
				// ВЫВОД позиций
				$html .= '<table class="cab_position_div">';
				
				// шапка таблицы позиций заказа
				$html .= '<tr>
						<th>артикул</th>
						<th>номенклатура</th>
						<th>тираж</th>
						<th>цены:</th>
						<th>товар</th>
						<th>печать</th>
						<th>доп. услуги</th>
					<th>в общем</th>
					<th></th>
					<th></th>
						</tr>';


				$in_out_summ = 0; // общая стоимость заказа
				foreach ($main_rows as $key1 => $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this-> get_order_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this-> get_dop_uslugi_no_print_type($dop_usl);

					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this-> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this-> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость товара для варианта
					$price_out = $val1['price_out'] * $val1['quantity'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

					$html .= '<tr  data-id="'.$value['id'].'">
					<td> '.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
					<td>'.$val1['name'].'</td>
					<td>'.($val1['quantity']+$val1['zapas']).'</td>
					<td></td>
					<td><span>'.$price_out.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
					<td><span>'.$in_out.'</span> р.</td>
					<td></td>
					<td></td>
							</tr>';
					$in_out_summ +=$in_out; // прибавим к общей стоимости
				}

				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = ($in_out_summ!=0)?round($value['payment_status']*100/$in_out_summ,2):'0.00';		
				// собираем строку заказа
				$html2 = '
						<tr data-id="'.$value['id'].'">
							<td class="show_hide" rowspan="2"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$value['create_time'].'</td>
							<td>'.$value['company'].'</td>
							<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span edit_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
							<td><span>'.$in_out_summ.'</span> р.</td>
							<td class="buch_status_select">'.$this->select_status($value['buch_status'],$this->buch_status).'</td>
							<td class="select_global_status">'.$this->order_status[$value['global_status']].'</td>
						</tr>';

				$html1 .= $html2 . $html;
			}
			echo '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>Дата/время заведения</th>
								<th>Компания</th>						
								<th class="invoice_num">Счёт</th>
								<th>Дата опл-ты</th>
								<th>№ платёжки</th>
								<th>% оплаты</th>
								<th>Оплачено</th>
								<th>стоимость заказа</th>
								<th>стутус БУХ</th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}

		################ Заказы
		Private Function orders_Template(){

			global $mysqli;
			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
			$subsection = (isset($_GET['subsection']))?$_GET['subsection']:'';
			switch ($subsection) {
				case 'paused':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Приостановлен'";
					break;
				case 'ready_for_shipment':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Готов к отгрузке'";
					break;
				case 'in_work':
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
					break;
				
				default:
					# code...
					break;
			}
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';

			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				//if(!isset($value2)){continue;}
				$order_num_1 = Cabinet::show_order_num($value['order_num']);
				$invoice_num = $value['invoice_num'];

				//$main_rows = $this->get_main_rows_Database($value['id']);

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				$html .= '<td class="show_hide"><span class="this->cabinett_row_hide" style="  top: -26px;
		  padding-top: 25px;"></span></td>';
				$html .= '<td colspan="5" class="each_art">';
				
				
				// ВЫВОД позиций
				$html .= '<table class="cab_position_div">';
				
				// шапка таблицы позиций заказа
				$html .= '<tr>
						<th>артикул</th>
						<th>номенклатура</th>
						<th>тираж</th>
						<th>цены:</th>
						<th>товар</th>
						<th>печать</th>
						<th>доп. услуги</th>
					<th>в общем</th>
					<th>стутаус снаб</th>
					<th>статус склад</th>
					<th>статус мен</th>
						</tr>';


				$in_out_summ = 0; // общая стоимость заказа
				$num_position = count($main_rows);$r=0;
				foreach ($main_rows as $key1 => $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this->get_order_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this-> get_dop_uslugi_no_print_type($dop_usl);

					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this-> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this-> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость товара для варианта
					$price_out = $val1['price_out'] * $val1['quantity'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;


					$html .= '<tr  data-id="'.$val1['id'].'">
					<td> '.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
					<td>'.$val1['name'].'</td>
					<td>'.($val1['quantity']+$val1['zapas']).'</td>
					<td></td>
					<td><span>'.$price_out.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
					<td><span>'.$in_out.'</span> р.</td>
					<td class="status_snab">'.$this->select_status(8,$val1['status_snab']).'</td>
					<td>'.$val1['status_sklad'].'</td>
					<td>'.$val1['status_men'].'</td>
							</tr>';
					$in_out_summ +=$in_out; // прибавим к общей стоимости
					$r++;
				}
				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
				// собираем строку заказа
				$html2 = '
						<tr data-id="'.$value['id'].'">
							<td class="this->cabinett_row_show show"><span></span></td>
							<td class="number_order"><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$value['company'].'</td>
							<td></td>
							<td>'.$value['payment_date'].'</td>
							<td class="select_global_status">'.$value['global_status'].'</td>
						</tr>
				';
				$html1 .= $html2 . $html;
			}
			echo '
			<table class="this->cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Заказ</th>
								<th>Компания</th>			
								<th></th>
								<th>Дата опл-ты</th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}
		## Заказы __ запросы к базе		
		private function orders_Template_get_main_rows_Database($id){
			global $mysqli;
			$query = "
				SELECT 
					`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
					`".CAB_ORDER_DOP_DATA."`.`quantity`,	
					`".CAB_ORDER_DOP_DATA."`.`price_out`,	
					`".CAB_ORDER_DOP_DATA."`.`print_z`,	
					`".CAB_ORDER_DOP_DATA."`.`zapas`,	
					DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".CAB_ORDER_MAIN."`.*,
					`".CAB_ORDER_MAIN."`.`id` AS `main_rows_id`,
					`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
					`".CAB_ORDER_ROWS."`.`global_status`,
					`".CAB_ORDER_ROWS."`.`payment_status`,
					`".CAB_ORDER_ROWS."`.`number_pyament_list`
					FROM `".CAB_ORDER_MAIN."` 
					INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
					LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

			$main_rows = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows[] = $row;
				}
			}
			return $main_rows;
		}
		################ Заказы __ END
		
		## На отгрузку
		Private Function for_shipping_Template(){
			global $mysqli;
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
			$subsection = (isset($_GET['subsection']))?$_GET['subsection']:'';
			switch ($subsection) {
				case 'ready_for_shipment':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Готов к отгрузке'";
					break;
					
				case 'otgrugen':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Отгружен'";
					break;
					

				default:
					# code...
					break;
			}
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';

			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				//if(!isset($value2)){continue;}
				$order_num_1 = Cabinet::show_order_num($value['order_num']);
				$invoice_num = $value['invoice_num'];


				$query = "
				SELECT 
					`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
					`".CAB_ORDER_DOP_DATA."`.`quantity`,	
					`".CAB_ORDER_DOP_DATA."`.`price_out`,	
					`".CAB_ORDER_DOP_DATA."`.`print_z`,	
					`".CAB_ORDER_DOP_DATA."`.`zapas`,	
					DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".CAB_ORDER_MAIN."`.*,
					`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
					`".CAB_ORDER_ROWS."`.`global_status`,
					`".CAB_ORDER_ROWS."`.`payment_status`,
					`".CAB_ORDER_ROWS."`.`number_pyament_list`
					FROM `".CAB_ORDER_MAIN."` 
					INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
					LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";
				// echo $query.'<br><br><br>';

				$main_rows = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				$main_rows_id = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$main_rows[] = $row;
					}
				}

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				//$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
				$html .= '<td colspan="11" class="each_art">';
				
				
				// ВЫВОД позиций
				$html .= '<table class="cab_position_div">';
				
				// шапка таблицы позиций заказа
				$html .= '<tr>
							<th>артикул</th>
							<th>номенклатура</th>
							<th class="change_ttn_number">ТТН</th>
							<th>отгружено</th>
							<th>тираж</th>
							<th>цены:</th>
							<th>товар</th>
							<th>печать</th>
							<th>доп. услуги</th>
							<th>в общем</th>
							<th></th>
							<th></th>
						</tr>';


				$in_out_summ = 0; // общая стоимость заказа
			// 		echo '<pre>';
			// print_r($main_rows);
			// echo '</pre>';
				foreach ($main_rows as $key1 => $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this -> get_order_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this -> get_dop_uslugi_no_print_type($dop_usl);

					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this -> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this -> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость товара для варианта
					$price_out = $val1['price_out'] * $val1['quantity'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

					$html .= '<tr  data-id="'.$val1['id'].'">
					<td> <!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
					<td>'.$val1['name'].'</td>
					<td class="change_ttn_number"  contenteditable="true">'.$val1['ttn_number'].'</td>
					<td><span class="change_delivery_tir" contenteditable="true">'.$val1['delivery_tir'].'</span>шт.</td>
					<td>'.($val1['quantity']+$val1['zapas']).'</td>
					<td></td>
					<td><span>'.$price_out.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
					<td><span>'.$in_out.'</span> р.</td>
					<td></td>
					<td></td>
							</tr>';
					$in_out_summ +=$in_out; // прибавим к общей стоимости
				}
				$html .= '</table>';


				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
				// собираем строку заказа
				$html2 = '
						<tr data-id="'.$value['id'].'">
							<td class="show_hide" rowspan="2"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$value['create_time'].'</td>
							<td>'.$value['company'].'</td>
							<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
							<td><span>'.$in_out_summ.'</span> р.</td>
							<td class="buch_status_select">'.$CABINET->select_status(2,$value['buch_status']).'</td>
							<td class="select_global_status">'.$CABINET->select_global_status($value['global_status']).'</td>
						</tr>
				';
				$html1 .= $html2 . $html;
			}
			echo '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>Дата/время заведения</th>
								<th>Компания</th>						
								<th class="invoice_num">Счёт</th>
								<th>Дата опл-ты</th>
								<th>№ платёжки</th>
								<th>% оплаты</th>
								<th>Оплачено</th>
								<th>стоимость заказа</th>
								<th></th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}
		## Закрытые
		Private Function closed_Template(){
			global $mysqli;
			//include ('./libs/php/classes/rt_class.php');

			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Откружен' OR `".CAB_ORDER_ROWS."`.`buch_status` = 'огрузочные приняты (подписанные)'";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';

			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				if(!isset($value2)){continue;}
					$order_num_1 = Cabinet::show_order_num($value['order_num']);
					$invoice_num = $value['invoice_num'];


					$query = "
					SELECT 
						`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
						`".CAB_ORDER_DOP_DATA."`.`quantity`,	
						`".CAB_ORDER_DOP_DATA."`.`price_out`,	
						`".CAB_ORDER_DOP_DATA."`.`print_z`,	
						`".CAB_ORDER_DOP_DATA."`.`zapas`,	
						DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
						`".CAB_ORDER_MAIN."`.*,
						`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
						`".CAB_ORDER_ROWS."`.`global_status`,
						`".CAB_ORDER_ROWS."`.`payment_status`,
						`".CAB_ORDER_ROWS."`.`number_pyament_list`
						FROM `".CAB_ORDER_MAIN."` 
						INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
						LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
						WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
						ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
				                
					";

					$main_rows = array();
					$result = $mysqli->query($query) or die($mysqli->error);
					$main_rows_id = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$main_rows[] = $row;
						}
					}

					// СОБИРАЕМ ТАБЛИЦУ
					###############################
					// строка с артикулами START
					###############################
					$html = '<tr class="query_detail">';
					$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
					$html .= '<td colspan="11" class="each_art">';
					
					
					// ВЫВОД позиций
					$html .= '<table class="cab_position_div">';
					
					// шапка таблицы позиций заказа
					$html .= '<tr>
							<th>артикул</th>
							<th>номенклатура</th>
							<th>тираж</th>
							<th>цены:</th>
							<th>товар</th>
							<th>печать</th>
							<th>доп. услуги</th>
						<th>в общем</th>
						<th></th>
						<th></th>
							</tr>';


					$in_out_summ = 0; // общая стоимость заказа
					foreach ($main_rows as $key1 => $val1) {
						//ОБСЧЁТ ВАРИАНТОВ
						// получаем массив стоимости нанесения и доп услуг для данного варианта 
						$dop_usl = $CABINET -> get_order_dop_uslugi($val1['id_dop_data']);
						// выборка только массива стоимости печати
						$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
						// выборка только массива стоимости доп услуг
						$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

						// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
						// стоимость печати варианта
						$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
						// стоимость доп услуг варианта
						$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
						// стоимость товара для варианта
						$price_out = $val1['price_out'] * $val1['quantity'];
						// стоимость варианта на выходе
						$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

						$html .= '<tr  data-id="'.$value['id'].'">
						<td> '.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
						<td>'.$val1['name'].'</td>
						<td>'.($val1['quantity']+$val1['zapas']).'</td>
						<td></td>
						<td><span>'.$price_out.'</span> р.</td>
						<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
						<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
						<td><span>'.$in_out.'</span> р.</td>
						<td></td>
						<td></td>
								</tr>';
						$in_out_summ +=$in_out; // прибавим к общей стоимости
					}
					$html .= '</table>';
					$html .= '</td>';
					$html .= '</tr>';
					###############################
					// строка с артикулами END
					###############################

					// получаем % оплаты
					$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
					// собираем строку заказа
					$html2 = '
							<tr data-id="'.$value['id'].'">
								<td class="cabinett_row_show show"><span></span></td>
								<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
								<td>'.$value['create_time'].'</td>
								<td>'.$value['company'].'</td>
								<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
								<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
								<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
								<td><span>'.$percent_payment.'</span> %</td>
								<td><span class="payment_status_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
								<td><span>'.$in_out_summ.'</span> р.</td>
								<td class="buch_status_select">'.$CABINET->select_status(2,$value['buch_status']).'</td>
								<td class="select_global_status">'.$CABINET->select_global_status($value['global_status']).'</td>
							</tr>
					';
					$html1 .= $html2 . $html;
				}
				echo '
				<table class="cabinet_general_content_row">
								<tr>
									<th id="show_allArt"></th>
									<th>Номер</th>
									<th>Дата/время заведения</th>
									<th>Компания</th>						
									<th class="invoice_num">Счёт</th>
									<th>Дата опл-ты</th>
									<th>№ платёжки</th>
									<th>% оплаты</th>
									<th>Оплачено</th>
									<th>стоимость заказа</th>
									<th></th>
									<th>Статус заказа.</th>
								</tr>';
				echo $html1;
				echo '</table>';
					// $message = 'important_Template';
					// $html = '';
					// other content template

					// $html .= $message;
					// return $html;this->
		}
		## Образцы
		Private Function simples_Template(){
			// $message = 'important_Template';
			// $html = '';
			// other content template

			// $html .= $message;
			// return $html;
		}

		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################




		#################################################
		##                   START                     ##
		##      методы для работы с базой данных       ##
		#################################################

		function get_all_orders_Database_Array(){
			global $mysqli;
			$arr = array();
			$query = '';

		}

		#################################################
		##      методы для работы с базой данных       ##
		##                    END                      ##
		#################################################
		
		//////////////////////////
		//	service method
		//////////////////////////
		private function show_cirilic_name_status_snab($status_snab){
			if(substr_count($status_snab, '_pause')){
				$status_snab = 'На паузе';
			}
			// echo '<pre>';
			// print_r($this->POSITION_NO_CATALOG->status_snab);
			// echo '</pre>';
						
			if(isset($this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'])){
				$status_snab = $this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'];
			}else{
				$status_snab;
			}

			return $status_snab;
		}

		private function get_client_name_Database($id){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<div class="attach_the_client" data-id="'.$row['id'].'">'.$row['company'].'</div>';
				}
			}else{
				$name = '<div class="attach_the_client add" data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}

		private 	function get_manager_name_Database_Html($id){
		    global $mysqli;
		    $String = '<span class="attach_the_manager add" data-id="0">Прикрепить менеджера</span>';
		   	$arr = array();
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $arr[$key] = $val;
				}
		    }

		    
		    if(count($arr)){
		    	$String = '<span class="attach_the_manager" data-id="'.$arr['id'].'">'.$arr['name'].' '.$arr['last_name'].'</span>';
		    }
		    return $String;
		}

		//////////////////////////
		//	оборачивает в оболочку warning_message
		//////////////////////////
		private function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}



		//////////////////////////
		//	комментарии к запросу
		//////////////////////////
		private function get_comment_for_query_Database(){
			global $mysqli;
			$query = "";
		}





		function __destruct(){}
	}


?>