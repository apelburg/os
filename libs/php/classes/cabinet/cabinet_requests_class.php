
<?php
	
	class Requests extends Cabinet{
		// фильтрация по запросу
		protected $filtres_query = '';
		// сортировка по запросу
		protected $filtres_query_sort = "  ORDER BY `id` DESC";
		// фильтрация по позиции
		protected $filtres_position = '';
		// сортировка по позиции
		protected $filtres_position_sort = "";
		// фильтрация по вариантам позиций
		protected $filtres_position_variant = '';
		// сортировка по вариантам позиций
		protected $filtres_position_variant_sort = '';
        // пагинация
        protected $filtres_query_pagination = '';


		function __construct($id_row = 0,$user_access,$user_id){
			include_once ('./libs/php/classes/rt_class.php');

			include_once ('./libs/php/classes/comments_class.php');

			if(isset($_GET['query_num']) && (int)$_GET['query_num'] > 0){
				$id_row = $_GET['query_num'];
			}



			$this->user_id = $user_id;
			$this->user_access = $user_access;	
			// echo 'привет мир';
			$method_template = $_GET['section'].'_'.$_GET['subsection'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			// echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// если в этом классе существует такой метод - выполняем его
            if(method_exists($this, $method_template)){
                $this->readPaginationParams();
                // echo $this->$method_template;
                $this->$method_template($id_row);

            }else{
                // обработка ответа о неправильном адресе
                echo 'фильтр '.$method_template.'() не найден';
            }


        }


        private function readPaginationParams(){
            $this->filtres_query_pagination = " LIMIT 10";
        }




    	
    	// Ожидают распределения (Админ)
    	protected function requests_query_wait_the_process_Template($id_row){
    		$this->filtres_query = " `".RT_LIST."`.`status` = 'not_process'";
    		$this->filtres_query .= " AND `".RT_LIST."`.`manager_id` IN ('24','0')";
    		$this->standart_request_method($id_row);
    	}
		

    	// Ожидают обработки (Мен)/ Не обработанные (Админ)
    	protected function requests_no_worcked_men_Template($id_row){    		
    		$this->filtres_query = "  (
    			`".RT_LIST."`.`status` IN ('not_process','taken_into_operation') 
    			)";
			$this->filtres_query .= " AND `".RT_LIST."`.`manager_id` <> '24'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";

    		$this->standart_request_method($id_row);
    	}

    	// В обработке (Админ/Мен)
    	protected function requests_query_taken_into_operation_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'taken_into_operation'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->standart_request_method($id_row);
    	}

    	// пауза МЕН
    	protected function requests_pause_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`row_status` LIKE '%pause%'";
    		$this->standart_request_method($id_row);
    	}

    	// Отказанные варианты (красный)
    	protected function requests_query_denided_variants_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` = 'red'";
    		$this->standart_request_method($id_row);
    	}


    	// В работе Sales (Админ) / В работе (Мен)
    	protected function requests_query_worcked_men_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->standart_request_method($id_row);
    	}

    	// рассчитанные Snab
    	protected function requests_accept_snab_job_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` = 'accept_snab_job'";
    		$this->standart_request_method($id_row);
    	}

    	// рассчитанные Snab
    	protected function requests_calk_snab_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` = 'calculate_is_ready'";
    		$this->standart_request_method($id_row);
    	}

    	// отправлено в Snab
    	protected function requests_send_to_snab_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` IN ('on_calculation_snab','on_recalculation_snab')";
    		$this->standart_request_method($id_row);
    	}

    	// ТЗ не корректно Snab
    	protected function requests_denied_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`row_status` IN ('tz_is_not_correct_on_recalculation','tz_is_not_correct')";
    		$this->standart_request_method($id_row);
    	}

    	// в работе Snab (Админ / Мен)
    	protected function requests_query_worcked_snab_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		if($this->user_access == 1){
    			// для админа работа снаб - это все статусы, где снаб принимает какое-либо участие
    			$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' 
    			AND (`".RT_DOP_DATA."`.`status_snab` IN (";
				$this->filtres_position .= "'on_calculation_snab'";
				$this->filtres_position .= ",'on_recalculation_snab'";
				$this->filtres_position .= ",'tz_is_not_correct_on_recalculation'";
				$this->filtres_position .= ",'tz_is_correct_on_recalculation'";
				$this->filtres_position .= ",'tz_is_correct'";
				$this->filtres_position .= ",'tz_is_not_correct'";
				$this->filtres_position .= ",'in_calculation'";
				$this->filtres_position .= ",'edit_and_query_the_recalculate'";
				$this->filtres_position .= ",'calculate_is_ready'";

    			$this->filtres_position .= "'in_calculation'";// в работе снаб
    			$this->filtres_position .= ",'on_calculation_snab'";// отправлено в снаб
    			$this->filtres_position .= ",'on_recalculation_snab'";// отправлено в снаб
    			$this->filtres_position .= ",'tz_is_not_correct_on_recalculation'";
    			$this->filtres_position .= ",'tz_is_not_correct'";
    			$this->filtres_position .= ") OR `".RT_DOP_DATA."`.`status_snab` LIKE '%pause%')";	
    		}else{
    			// для мена только статус в расчёте
    			$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` IN ('in_calculation')";	
    		}
    		
    		$this->standart_request_method($id_row);
    	}
    	// на паузе (Мен / Снаб)
    	protected function requests_query_variant_in_pause_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position .= " `".RT_DOP_DATA."`.`status_snab` LIKE '%pause%'";
    		$this->standart_request_method($id_row);
    	}

    	// история (Админ / Мен)
    	protected function requests_query_history_Template($id_row){
    		$this->filtres_query = " `".RT_LIST."`.`status` = 'history'";
    		$this->standart_request_method($id_row);
    	}

    	// все (Админ/Мен)
    	protected function requests_query_all_Template($id_row){
    		$this->filtres_query = " `".RT_LIST."`.`status` <> 'history'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->standart_request_method($id_row);
    	}

    	// ЗАПРОС из базы строк запросов
    	protected function get_queries_Database_Array($id_row){
    		$where = 0;
			global $mysqli;
		
			$query = "SELECT 
				`".RT_LIST."`.*, 
				(UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)-UNIX_TIMESTAMP())*(-1) AS `time_attach_manager_sec`,
				SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)) AS `time_attach_manager`,
				DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i')  AS `create_time`
				FROM `".RT_LIST."`";
				
			if((int)$id_row > 0){
				$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`query_num` = '".$id_row."'";
				$where = 1;
			}else{				
				// фильтрация по клиенту
				if(isset($_GET['client_id'])){
					$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`client_id` = '".$_GET['client_id']."'";
					$where = 1;
				}
				// для менеджера 
				if($this->user_access == 5){
					if($_GET['subsection'] == "no_worcked_men"){
						$query .= " ".(($where)?'AND':'WHERE')." 
						(`".RT_LIST."`.`manager_id` = '".$this->user_id."' 
						 OR (`".RT_LIST."`.`manager_id` = '0' AND `".RT_LIST."`.`dop_managers_id` LIKE '%$this->user_id%') )";
						

						$where = 1;
					}else if(!isset($_GET['client_id'])){
						// при фильтрации по клиенту показываем все запросы по данному клиенту
						// при желании тут же можно будет ограничить доступ для не кураторов
						$query .= " ".(($where)?'AND':'WHERE')." 
						`".RT_LIST."`.`manager_id` = '".$this->user_id."' 
						";
						$where = 1;
					}
				}
			}

			// фильтрация по запросу
			if($this->filtres_query != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_query;
				$where = 1;
			}
			// сортировка по запросу
			if($this->filtres_query_sort != ''){
				$query .= " ".$this->filtres_query_sort;
			}

			// пагинация
            if ($this->filtres_query_pagination != ''){
                $query .= " ".$this->filtres_query_pagination;
            }
			
			$result = $mysqli->query($query) or die($mysqli->error);
			 echo $query.'<br>';
			$this->Query_arr = array();

			$n = 0;
			$this->Query_id_str = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->Query_arr[] = $row;
					$this->Query_id_str .= (($n>0)?',':'')."'".$row['query_num']."'";
					$n++; 
				}
			}
    	}

    	// статусы / кнопки запроса
    	protected function get_button_status_for_request(){
    		// echo $this->user_access;
			if($this->user_access == 5){
				$this->status_or_button = $this->statusQueryNameArrEn2Ru[$this->Query['status']];

			}else {
				$this->status_or_button = (isset($this->statusQueryNameArrEn2Ru[$this->Query['status']])?$this->statusQueryNameArrEn2Ru[$this->Query['status']]:'статус не предусмотрен!!!!'.$this->Query['status']);
			}
		}

		protected function standart_request_method($id_row){

			// кнопка новый запрос
			if( $this->user_access == 5 ){ // кнопка новый запрос для админа не проработано
			// if($this->user_access == 5){
				global $quick_button;
				$quick_button = '<div class="quick_button_div"><a href="#" id="create_new_query" class="button add">Новый запрос</a></div>';	
			}

			// фильтрация start .. если есть


			// шаблон html вывода запроса
			$this->request_Template($id_row);
		}

		// шаблон html вывода запроса
    	protected function request_Template($id_row){
    		
    		// вывод шапки главной таблицы
    		echo $this->get_header_general_tbl();

			// запрос массива запросов
			$this->get_queries_Database_Array($id_row);

			
			
			// получаем позиции
			$this->positions_arr = $this->get_positions_Database_Array();

			
			
			$this->query_num = '';
			// итого за запрос
			$this->price_for_the_position_ITOGO = 0;
			foreach ($this->Query_arr as $this->Query) {
					$general_tbl_row = '';
					
					// получаем открыт/закрыт
					$this->open__close = $this->get_open_close_for_this_user($this->Query['open_close']);
						
					// наименование продукта
					$name_product = ''; 
					// порядковый номер варианта расчёта одного и того же продукта
					$this->name_count = 1;
					
					// Html строки вариантов 
					$html = '';

					// счетчик кнопок показа каталожных позиций
					// необходим для ограничения до одной кнопки
					$count_button_show_catalog_variants=0;


					// if($_GET['subsection'] != 'query_worcked_men' && $_GET['subsection'] != 'no_worcked_men' && $_GET['subsection'] != 'query_all' && $_GET['subsection'] != 'query_wait_the_process'){
					// 	// если позиций нет - переходим к следующей интерации цикла
					// 	if(empty($this->positions_arr[$this->Query['query_num']])){continue;}
					// }

					
					// если это первая строка нового запроса, выводим строку названий колонок вариантов
					if(isset($this->positions_arr[$this->Query['query_num']])){
						$this->price_for_the_position_ITOGO = 0;

						if($this->query_num != $this->Query['query_num']){
							$html .= $this->get_header_start_position_list_tr();
						}

						$this->position_count = count($this->positions_arr[$this->Query['query_num']]);
					
						$this->rowspan = $this->position_count +3;
						// перебор вариантов
						foreach ($this->positions_arr[$this->Query['query_num']] as $this->position) {
							////////////////////////////////////
							//	Расчёт стоимости позиций START  
							////////////////////////////////////
							$this->GET_PRICE_for_position($this->position);	

							$this->position['calc'] = true;
							$expel_arr = json_decode(($this->position['expel']!='')?$this->position['expel']:'{}',true);
							if(!isset($expel_arr['main'])){
								$this->position['calc'] = false;
								$this->price_for_the_position_ITOGO += $this->price_for_the_position;
							}

							
							
							//////////////////////////
							//	собираем строки вариантов по каждой позиции
							//////////////////////////
							// 
							if($name_product != $this->position['name']){$name_product = $this->position['name']; $this->name_count = 1;}
							
							$html .= $this->position_dop_data_Temp($this->Query, $this->position);

						}	
					}else{
						$this->rowspan = 3;
						$html .= $this->get_header_start_position_list_tr_empty();
					}
					

					// получаем статус 
					$this->get_button_status_for_request();

					//$this->status_or_button = (isset($this->statusQueryNameArrEn2Ru[$this->Query['status']])?$this->statusQueryNameArrEn2Ru[$this->Query['status']]:'статус не предусмотрен!!!!'.$this->Query['status']);
					

					// выделяем красным текстом если менеджер не взял запрос в обработку в течение 5 часов
					$this->overdue = (($this->Query['time_attach_manager_sec']*(-1) > 18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
					// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
					
						//////////////////////////
						//	собираем строку запроса
						//////////////////////////
					$general_tbl_row_body = $this->get_row_query_Html_temp();
					
					// если запрос по строке, возвращаем строку
					// if($id_row != 0){
					// 	return $general_tbl_row_body;
					// }

					$general_tbl_row .= '<tr data-id="'.$this->Query['id'].'" id="rt_list_id_'.$this->Query['id'].'"  class="order_head_row '.$this->open_close_row_class.'" '.$this->open_close_tr_style.'>
										'.$general_tbl_row_body.'
										</tr>';
					
					$general_tbl_row .= '<tr class="query_detail" '.$this->open_close_tr_style.'>';
				
					// прикручиваем найденные варианты
					$general_tbl_row .=	$html;
					
					$general_tbl_row .= '</tr>';
					
					echo $general_tbl_row;
					unset($general_tbl_row);	
					$this->query_num = $this->Query['query_num'];		
			}			
			echo $this->get_footer_tbl();
		}

		// шаблон строки строки запроса
		protected function get_row_query_Html_temp(){
			$html  = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->rowspan.'"><span class="cabinett_row_hide '.$this->open_close_class.'"></span></td>';
			// маркер не прикрепленного клиента
			// $js_message = 'Чтобы перейти в РТ необходимо прикрепить клиента!!!';
			$the_client_is_not_attached = 'onClick="return false;"';
			$href_RT = '#';
			if ($this->Query['client_id'] > 0) {
				$the_client_is_not_attached = '';
				$href_RT = './?page=client_folder&client_id='.$this->Query['client_id'].'&query_num='.$this->Query['query_num'];
			}

			$html .= '<td class="rt_href_click"><a '.$the_client_is_not_attached.' href="'.$href_RT.'">'.$this->Query['query_num'].'</a> </td>';
			
			$no_edit = 1;
			if($this->Query['client_id'] == 0){
				$no_edit = 0;
				if($this->user_access != 1){
					$no_edit = 1;
				}				
			}

			$html .= $this->get_client_name_for_query_Database(
				$this->Query['client_id'],$no_edit);
			$html .= '<td class="show_main_menu query_theme_and_comment">';
				$html .= '<div class="table" style="width:100%">';
					$html .= '<div class="row">';
						$html .= '<div clas="cell">'; 
							// тема
							$html .= $this->Query['theme'];
						$html .= '</div>';
						$html .= '<div class="cell">';
							// комментарии
							$html .= '<span data-rt_list_query_num="'.$this->Query['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Query['query_num']).'"></span>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';

			$html .= '</td>';
			$html .= '<td class="show_main_menu">';

				if($this->user_access == 1){
					$no_edit = 0;	
				}

				// if($this->user_access != 1 && $this->Query['client_id'] == 0){
				if($this->user_access != 1){
					$no_edit = 1;
				}else{
					$no_edit = 0;
				}			

				$html .= '<div>'.$this->get_all_manager_name_Database_Html($this->Query,$no_edit).'</div>';
				// если не история - считаем сколько времени назад взяли заказ в работу
				if($_GET['subsection'] != 'query_history'){
					$html .= '<div style="padding-top: 5px;"><span class="greyText" data-sec="'.$this->Query['time_attach_manager_sec']*(-1).'" '.$this->overdue.'>'.$this->Query['time_attach_manager'].'</span></div>';
				}				
			$html .= '</td>';
			
			$html .= '<td class="show_main_menu">'.$this->Query['create_time'].'</td>';
			$html .= '<td class="show_main_menu"></td>';
			$html .= '<td class="show_main_menu"></td>';
			
			// $rrr = RT::calcualte_query_summ($this->Query['query_num']);
			$rrr = $this->price_for_the_position_ITOGO;
			$html .='<td class="show_main_menu" td="'.$rrr.'">'.$rrr.'</td>';
			$html .='<td class="'.$this->Query['status'].'_'.$this->user_access.' show_main_menu query_status">'.$this->status_or_button.'</td>';
			return $html;
		}

		// шаблон строки с названиями ячеек по позициям
		protected function get_header_start_position_list_tr(){
			$html = '<tr class="query_detail cab_position_div" '.$this->open_close_tr_style.'>';
				$html .= '<th>артикул</th>';
				$html .= '<th>номенклатура</th>';
				$display = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0)?' style="display:none"':'';	
				$html .= '<th'.$display.'></th>';
				$html .= '<th>тираж</th>';
				$html .= '<th>товар</th>';
				$html .= '<th>печать</th>';
				$html .= '<th>доп. услуги</th>';
				$html .= '<th>в общем</th>';
				$html .= '<th></th>';
			$html .= '</tr>';
			return $html; 
		}
		// шаблон пустого запроса
		protected function get_header_start_position_list_tr_empty(){
			$html = '<tr class="query_detail cab_position_div" '.$this->open_close_tr_style.'>';
				$html .= '<th></th>';
				$html .= '<th colspan="8">Данные отсутствуют</th>';
			$html .= '</tr>';
			return $html; 	
		}


		// шаблон строки варианта позиции
		protected function position_dop_data_Temp($Query, $position){
			// подкрашиваем серые строки

			$grey_row_style = ($position['row_status'] == 'grey')?' grey_row_style':'';

			// маркер не прикрепленного клиента
			$js_message = 'Чтобы перейти в карточку артикула необходимо прикрепить клиента!!!';
			$the_client_is_not_attached = 'style="cursor:pointer" onClick="echo_message_js(\''.$js_message.'\',\'system_message\');"';
			$href_art_card = '<div '.$the_client_is_not_attached.'>'.$position['name'].' <span class="greyText"> вар '.$this->name_count.'</span></div>';
			if ($this->Query['client_id'] > 0) {
				$the_client_is_not_attached = '';
				$href_art_card = '<a class="go_to_position_card_link" target="_blank" href="./?page=client_folder&client_id='.$this->Query['client_id'].'&section=rt_position&id='.$position['id'].'&varID_checked='.$position['id_dop_data'].'">'.$position['name'].'</a> <span class="greyText"> вар '.$this->name_count.'</span>';
			}
			$this->name_count++;

			$html = '<tr data-id_dop_data="'.$position['id_dop_data'].'" class="'.$position['type'].'_1 query_detail '.$grey_row_style.'" '.$this->open_close_tr_style.'">';
				// $html .= '<td>'.$position['id_dop_data'].$this->print_arr($Query).'</td>';
				$html .= '<td>'.$position['art'].'</td>';
				$display = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0)?' style="display:none"':'';	
				$html .= '<td>'.$href_art_card.'</td>';				
				$html .= '<td '.$display.'></td>';
				$html .= '<td>'.$position['quantity'].'</td>';
				$html .= '<td>'.$this->Price_for_the_goods.'</td>';
				$html .= '<td>'.$this->Price_of_printing.'</td>';
				$html .= '<td>'.$this->Price_of_no_printing.'</td>';
				
				$title = $class_no_calc = '';
				if($position['calc']){
					$title = "Данная позиция не учавствует в итоговом расчёте";
					$class_no_calc = 'class_no_calc';
				}

				$html .= '<td title="'.$title.'" class="'.$class_no_calc.'">'.$position['calc'].' ' .$this->Price_for_the_position.'</td>';

				// $status_snab = ($Query['status'] != 'new_query')?$this->show_cirilic_name_status_snab($position['status_snab']):''; // без снабов статус работы не актуален
				$status_snab = '';
				$html .= '<td data-type="'.$position['type'].'" data-status="'.$position['status_snab'].'" class="'.$position['status_snab'].'_'.$this->user_access.' '.$Query['status'].'_status_snab_'.$this->user_access.'">'.$status_snab.'</td>';
			$html .= '</tr>';
			return $html;
		}

		// шаблон шапки главной таблицы
		protected function get_header_general_tbl(){
			$html = '<table class="query_tbl" id="general_panel_orders_tbl">';
				$html .= '<tr>';
					$html .= '<th id="show_allArt" style="width: 15px;min-width:15px;"></th>';
					$html .= '<th>Номер</th>';					
					$display = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0)?' style="display:none"':'';	
					// $theme_style = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0)?' style="min-width: 804px;':'';

					$html .= '<th'.$display.'>Компания</th>';
					$html .= '<th >Тема</th>';
					$html .= '<th>Менеджер</th>';
					$html .= '<th style="width:87px">Дата запроса</th>';
					$html .= '<th colspan="2"></th>';
					// $html .= '<th>Комментарий</th>';
					$html .= '<th>Сумма</th>';
					$html .= '<th>Статус</th>';
				$html .= '</tr>';
			return $html;
		}

		// возвращает закрывающий тег главной таблицы
		protected function get_footer_tbl(){
			return '</table>';
		}




		
		// ЗАПРОС позиции по запросу
		protected function get_positions_Database_Array(){
			if ($this->Query_id_str == '') {
				return array();
			}

			$where = 0;

			global $mysqli;
			$query = "
				SELECT 
					`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
					`".RT_DOP_DATA."`.`quantity`,	
					`".RT_DOP_DATA."`.`price_out`,
					`".RT_DOP_DATA."`.`row_status`,
					`".RT_DOP_DATA."`.`expel`,		
					`".RT_DOP_DATA."`.`print_z`,	
					`".RT_DOP_DATA."`.`zapas`,
					`".RT_DOP_DATA."`.`discount`,	
					`".RT_DOP_DATA."`.`status_snab`,	
					`".RT_MAIN_ROWS."`.*,
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`
					
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					
					WHERE `".RT_MAIN_ROWS."`.`query_num` IN (".$this->Query_id_str.")";

			$where = 1;

			// фильтрация по запросу
			if($this->filtres_position != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_position;
				$where = 1;
			}
			// сортировка по запросу
			if($this->filtres_position_sort != ''){
				$query .= " ".$this->filtres_position_sort;
			}else{
				$query .= " ORDER BY `".RT_MAIN_ROWS."`.`id` DESC";
			}


			

			$main_rows = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			// echo '<br><strong>position_query</strong> <br>'.$query.'<br>';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows[$row['query_num']][] = $row;
				}
			}
			// if($main_rows){ echo $query;}
			return $main_rows;
		}


   
	
}



