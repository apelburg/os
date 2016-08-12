<?php

/**
 * Class Cabinet
 *
 * использование AJAX
 *
 * вывод системных сообщений сообщений
 * echo_message - имя функции вывода
 * $message - текст сообщения
 * $message_type - тип сообщения
 * - system_message (orange)
 * - error_message  (red)
 * - successful_message (green)
 */
    class Cabinet extends aplStdAJAXMethod{
        /**
         * дополнения к запросам в базе
         * @var string
         */

        // заказ
        protected $filtres_order = '';
        protected $filtres_order_sort = ' ORDER BY `id` DESC';  // сортировка по умолчанию
        // документ
        protected $filtres_specificate = '';                    // фильтр
        protected $filtres_specificate_sort = '';               // сортировка по умолчанию
        // позиция
        protected $filtres_position = '';                       // фильтр
        protected $filtres_position_sort = '';                  // сортировка по умолчанию
        // услуги
        protected $filtres_services = '';                       // фильтр
        protected $filtres_services_sort = '';                  // сортировка по умолчанию


        /**
         * содержит html фильтров по кабинету
         * @var array
         */
    	public $filtres_html;

        /**
         * услуга с которой работает система в данный момент времени, содержит массив строки из
         * @var array
         */
    	protected $Service;

        /**
         * содержит массив всех прикреплённых к позиции услуг
         * @var array
         */
    	protected $Services_for_position_arr;

        /**
         * содержит массив всех прикреплённых к позиции услуг отсортирована по подразделениям
         * @var array
         */
    	protected $Services_for_position_arr_sort_by_performer;


        /**
         * содержит массив всех существующих услуг содержащихся в OUR_USLUGI_LIST (все предоставляемые услуги)
         * @var array
         */
    	protected $Services_list_arr;

    	
    	// список станков
    	protected $machine_list = array(   
			'poluavtomat' => 'Полуавтомат', 
    		'paketnik' => 'Ручник плоский',		
    		'karusel_6' => 'Карусель 6цв.',
    		'karusel_8' => 'Карусель 8цв.',
    		'tampo_1' => 'Winon',
    		'tampo_2' => 'Kent',
    		'termopress_a5_1' => 'Kепочник 1',
    		'termopress_a5_2' => 'Kепочник 2',
    		'termopress_a3_insta' => 'Пресс Insta плоский',
    		'termopress_a3_china' => 'Пресс Китай плоский',
    		'tisnenie' => 'Тic',
    		'hand_made' => 'На руках'
    		);


        /**
         * исполнитель услуг по правам
         * @var array
         */
    	protected $performer = array(
    		'0' => 'noname', // ответственный не указан
			'1' => 'Админ',
			'2' => 'Бухгалтерия',
			'4' => 'Производство',
			'5' => 'Менеджер',
			'6' => 'Доставка',
			'7' => 'Склад',
			'8' => 'Снабжение',
			'9' => 'Дизайнер'
		);

		/**
         * СТАТУСЫ ПОДРАЗДЕЛЕНИЙ ПО ПОЗИЦИЯМ, ЗАКАЗУ И ПРЕДЗАКАЗУ
         */

        /**
         * допуски пользователя
         * @var int
         */
        protected $user_access = 0;

        /**
         * @var array
         */
		public $statusQueryNameArrEn2Ru = array(
			'new_query' => 'новый запрос',
			'not_process' => 'не обработан менеджером',
			'taken_into_operation' => 'на рассмотрении',
			'in_work' => 'в работе',
			'history' => 'история'
		);

		// глобальные статусы ПРЕДЗАКАЗА(заказа)
    	protected $paperwork_status = array(
    		// ПРЕДЗАКАЗ
			'being_prepared'=>'Предзаказ',
			'in_operation'=>'Запуск в работу',
        );

    	// глобальные статусы ЗАКАЗА
    	// содержится в базе в `os__cab_orders_list` в global_status
    	protected $order_status = array(
			// ЗАКАЗ
			'in_operation'=>'Запуск в работу', // нельзя выбрать // вытекает из статусов BUCH
			'in_work'=>'В работе', // вытекает из кнопки Запуск в работу
			// 'ready_for_shipment'=>'Готов к отгрузке',
			'shipped'=>'Отгружен', // вытекает из статусов позиций
			);

    	protected $order_service_status = array(
    		/*
				при выборе данного статуса
    		*/
			'query_in_work' => 'Перевести заказ в работу',
			'in_operation' => 'Запуск в работу',
    		'maket_without_payment' =>'Макет без оплаты',
    		'paused'=>'Заказ приостановлен',
    		'paperwork_paused' => 'Предзаказ приостановлен',
    		'cancelled'=>'Аннулирован'

        );

		// статусы БУХ - вывод в select
	    protected $buch_status = array(
	    	'is_pending' => 'ожидает обработки',
	    	'score_exhibited' => 'счёт выставлен ',
	    	'ttn_created' => 'ТТН готова',
	    	//////////////////////////
	    	//	статусы разрешающие перевод заказа в работу
	    	//////////////////////////
			'payment' => 'оплачен',
			'partially_paid' => 'частично оплачен',
			'letter_of_guarantee' => 'гарантийное письмо',
			'ogruzochnye_accepted' => 'огрузочные приняты (подписанные) ВСЕ',
			'client_collateral_returns' => 'Залог клиенту возвращен',
			'refund_in_a_row_ok' => 'Деньги по счёту возвращены'// -> статус предзаказа =  'shipped'
			// 'return_order_in_paperorder' => 'вернуть заказ в предзаказ'
	    );
		// статусы БУХ - сервисные (если уже не выставлены)
	    protected $buch_status_service = array(
	    	'request_expense'=>'Запрошен счёт',
	    	'reget_the_bill' => 'Перевыставить счёт',
	    	'refund_in_a_row' => 'Возврат денег по счёту',
			'get_the_pko' => 'Запрошен ПКО',
			'get_the_bill_oferta' => 'Запрошен счёт-оферта',
			'returns_client_collateral' => 'Возврат залога клиенту',
			'cancelled'=>'Аннулирован',
			'get_ttn' =>'Запрошена ТТН',
			'uslovno_oplachen' => 'Условно оплачен',
			// 'get_vaselin' => 'Берите вазелин и дуйте к начальству' //
			// 'maket_without_payment' =>'Макет без оплаты'
	    );

		// комманды менеджера  (при клике на статус буха меню)
		protected $commands_men_for_buch = array(
			'reget_the_bill' => 'перевыставить счёт',
			'returns_client_collateral' => 'вернуть залог клиенту',
			'refund_in_a_row' => 'вернуть денеги по счёту',
			'get_ttn' =>'Запросить отгрузочные',
			'cancelled'=>'статус "Аннулировано"'
		);

			

	    // типы счетов которые мы можем запросить
	    protected $type_the_bill =array(
	    	'the_bill' => array(
	    		'счёт на оплату',// кто, что
	    		'счёта на оплату' // кого, чего
	    		),
	    	'the_bill_offer' => array(
	    		/*
					в будущем должен создаваться минуя алгоритм создания спецификации
					НА ОБСУЖДЕНИЕ С АНДРЕЕМ !!!!!!
	    		*/
	    		'счёт - оферта',// кто, что
	    		'счёта - оферты' // кого, чего
	    		),
	    	// 'the_bill_for_simples' => array(
	    	// 	'счёт на образцы',
	    	// 	'счёта на образцы'
	    	// 	),
	    	// 'prihodnik' => array(
	    	// 	'приходник',
	    	// 	'приходника
	    	// 	'),
        );


	    	
		// статусы склад
		protected $statuslist_sklad = array(
			'no_goods' => 'нет в наличии',
			// 'waiting' => 'ожидаем',
			'goods_in_stock' => 'принято на склад', // ->
			'sended_on_outsource' => 'отправлено на аутсорс',
			'ready_for_shipment'  => 'готов к отгрузке',
			// 'goods_shipped_for_client_part' => 'позиция частично отгружена',
			'goods_shipped_for_client' => 'отгружен клиенту'
		);

		// статусы СНАБ
		protected $statuslist_snab = array(
			// 'adopted' => 'Принят',
			// 'maquette_adopted' => 'Макет принят',
			// 'not_adopted' => 'Не принят',
			'maquette_maket' => 'Ожидает макет',
			'waits_union' => 'Ожидает объединения',
			// 'products_capitalized_warehouse' => 'Продукция оприходована складом',// сервисный статус, вытекает из статуса склада - принято на склад
			'waits_the_bill_of_supplier' => 'Ожидает счет от поставщика',
			// 'on_outsource' => 'уехало на аутсорсинг',
			'waits_the_sell_of_supplier' => 'Ожидаем отправку постащика',
			'products_bought' => 'Продукция выкуплена',
			'to_bought_products' => 'Выкупить продукцию',
			'waits_products' => 'Продукция ожидается:',
			'in_production' => 'В Производстве', // -> запуск всех услуг кроме доставки и дизайна, при этом услуга Диза ставится на "услуга выполнена"
			'ready_for_shipment' => 'Готов к отгрузке',
			// 'goods_shipped_for_client' => 'отгружен клиенту',
			'question' => 'Вопрос'
		);
		// статусы СНАБ сервисные
		protected $statuslist_snab_service = array(
			'in_operation' => 'Ожидает запуска', // статус удалён
			'is_pending' => 'Ожидает обработки',
		);
			

			

		// статусы плёнок для услуги
		protected $status_film_photos = array(
			// админ
			1 => array(
				'проверить наличие', // диз не видит
				'нужно делать',
				'в наличии',
				'не требуются', // диз не видит
				'готовы к отправке',
				'отправлены',
				'получены', // диз не видит
				'клише клиента'
				),
			// снабжениец
			// 8 => array(),
			// менеджер
			5 => array(
				'проверить наличие', // диз не видит
				'в наличии',
				'клише клиента',
				'нужно делать',
				'не требуются' // диз не видит
				),
			// дизайн
			9 => array(
				'готовы к отправке',
				'отправлены на фотовывод',
				'клише заказано'
				),
			// производство
			4 => array(
				'перевывод',
				'в наличии', // диз не видит
				'получены' // // диз не видит
				)
		);



			////////////////////////////////////////////////////////
			//  --- START ---  СЛЕДСТВИЯ СТАТУСОВ  --- START ---  //
			////////////////////////////////////////////////////////
				/*
					следствия нужны для переключения статусов других подразделений при изменении какого-либо статуса			
				*/

				// статус БУХ -> статус Заказа/Предзаказа
		    	// protected $CONSEQUENCES_of_status_buch = array(
		    	// 	'return_order_in_paperorder' => 'being_prepared', // вернуть заказ в предзаказ -> предзаказ ожидает обработки
		    	// 	'score_exhibited' => 'waiting_for_payment', // счёт выставлен -> ждём оплаты
		    	// 	'payment' => 'in_operation', // оплачен -> кнопка "Запуск в работу"
		    	// 	'partially_paid' => 'in_operation', // чатично оплачен -> кнопка "Запуск в работу"
		    	// 	'collateral_received' => 'in_operation', // принят залог -> кнопка "Запуск в работу"
		    	// 	'letter_of_guarantee' => 'in_operation', // гарантийное -> кнопка "Запуск в работу"
		    		
		    	// 	'ogruzochnye_accepted' => 'shipped'// отгрузочные приняты ???
		    	// );

		    	// // статус склад -> статус снаб
		    	// protected $CONSEQUENCES_of_status_sklad = array(
		    	// 	'goods_in_stock' => 'goods_in_stock',// принято на склад -> продукция оприходована складом
		    	// 	'sended_on_outsource' => 'goods_in_stock',// отправлено на оутсорс -> отправлено на оутсорсинг
		    	// 	'checked_and_packed' => 'goods_in_stock', // проверено и упаковано -> готово к отгрузке
		    	// 	'goods_shipped_for_client' => 'goods_shipped_for_client'// отгружен клиенту -> отгружен клиенту
		    	// );

	    	////////////////////////////////////////////////////////
			//   --- END ---   СЛЕДСТВИЯ СТАТУСОВ   --- END ---   //
			////////////////////////////////////////////////////////

	    ////////////////////////////////////////////////////////////////////////////////
    	// -- END -- СТАТУСЫ ПОДРАЗДЕЛЕНИЙ ПО ПОЗИЦИЯМ, ЗАКАЗУ И ПРЕДЗАКАЗУ -- END -- //
    	////////////////////////////////////////////////////////////////////////////////

		function __consturct(){	
    	}

    	//////////////////////////
    	//	фильтры
    	//////////////////////////
	    	public function check_the_filtres(){
				// проверяем на наличие фильтра по заказу
				if(isset($_GET['order_num']) && $_GET['order_num'] != ''){
					$this->filtres_html['order_num'] = '<li>заказ №'.self::show_order_num($_GET['order_num']).'<a href="'.$this->link_exit_out_filters('order_num').'" class="close">x</a></li>';	
				}

				// проверяем на наличие фильтра по заказу
				if(isset($_GET['query_num']) && $_GET['query_num'] != ''){
					$this->filtres_html['query_num'] = '<li>запрос №'.self::show_query_num($_GET['query_num']).'<a href="'.$this->link_exit_out_filters('query_num').'" class="close">x</a></li>';	
				}
				
				// проверяем на наличие фильтра по заказу
				if(isset($_GET['manager_id']) && $_GET['manager_id'] != ''){
					$meneger_name_for_order = $this->get_name_employee_Database_Html($_GET['manager_id']);
					$this->filtres_html['manager_id'] = '<li>мен: '.$meneger_name_for_order.'<a href="'.$this->link_exit_out_filters('manager_id').'" class="close">x</a></li>';	
				}

				// проверяем на наличие фильтра по снабу
				if(isset($_GET['snab_id']) && $_GET['snab_id'] != ''){
					$snab_name_for_order = $this->get_name_employee_Database_Html($_GET['snab_id']);
					$this->filtres_html['snab_id'] = '<li>снаб: '.$snab_name_for_order.'<a href="'.$this->link_exit_out_filters('snab_id').'" class="close">x</a></li>';	
				}

				// проверяем на наличие фильтра по дате, когда мы ожидаем продукцию
				if(isset($_GET['date_delivery_product']) && $_GET['date_delivery_product'] != ''){
					$date = $_GET['date_delivery_product'];
					$this->filtres_html['date_delivery_product'] = '<li>продукция ожидается: '.$date.'<a href="'.$this->link_exit_out_filters('date_delivery_product').'" class="close">x</a></li>';	
				}


				// проверяем на наличие фильтра по дате отгрузки
				if(isset($_GET['shipping_date']) && $_GET['shipping_date'] != ''){
					$date = $_GET['shipping_date'];
					$this->filtres_html['shipping_date'] = '<li>дата отгрузки: '.$date.'<a href="'.$this->link_exit_out_filters('shipping_date').'" class="close">x</a></li>';	
				}
				// проверяем на наличие фильтра по дате утверждения макета
				if(isset($_GET['approval_date']) && $_GET['approval_date'] != ''){
					$date = $_GET['approval_date'];
					$this->filtres_html['approval_date'] = '<li>дата утв. макета: '.$date.'<a href="'.$this->link_exit_out_filters('approval_date').'" class="close">x</a></li>';	
				}

				// фильтр по номеру резерва
				if(isset($_GET['number_rezerv']) && $_GET['number_rezerv'] != ''){
					$this->filtres_html['number_rezerv'] = '<li>номер резерва: '.base64_decode($_GET['number_rezerv']).'<a href="'.$this->link_exit_out_filters('number_rezerv').'" class="close">x</a></li>';	
				}

				// фильтр по поставщику --- ДОДЕЛАТЬ
				if(isset($_GET['supplier']) && $_GET['supplier'] != ''){
					$this->filtres_html['supplier'] = '<li>Поставщик: '.$this->get_supplier_name($_GET['supplier']).'<a href="'.$this->link_exit_out_filters('supplier').'" class="close">x</a></li>';	
				}
				

				// проверяем на наличие фильтра по Статусу снабжение
				if(isset($_GET['status_snab']) && $_GET['status_snab'] != ''){
					if(isset($this->statuslist_snab[$_GET['status_snab']])){
						$status_snab = $this->statuslist_snab[$_GET['status_snab']];
					}else if(isset($this->statuslist_snab_service[$_GET['status_snab']])){
						$status_snab = $this->statuslist_snab_service[$_GET['status_snab']];
					}else{
						$status_snab = $_GET['status_snab'];
					}
					$this->filtres_html['status_snab'] = '<li>статус Снаб: '.$status_snab.'<a href="'.$this->link_exit_out_filters('status_snab').'" class="close">x</a></li>';	
				}
				// // проверяем на наличие фильтра по Статусу склада
				// if(isset($_GET['status_sklad']) && $_GET['status_sklad'] != ''){
				// 	$this->filtres_html['status_sklad'] = '<li>статус Склад: '.$_GET['status_sklad'].'<a href="'.$this->link_exit_out_filters('status_sklad').'" class="close">x</a></li>';	
				// }
				// // проверяем на наличие фильтра по Статусу дизайна
				// if(isset($_GET['status_design']) && $_GET['status_design'] != ''){
				// 	$this->filtres_html['status_design'] = '<li>статус Диз: '.$_GET['status_design'].'<a href="'.$this->link_exit_out_filters('status_design').'" class="close">x</a></li>';	
				// }
				// проверяем на наличие фильтра по Статусу производства
				// if(isset($_GET['status_production']) && $_GET['status_production'] != ''){
				// 	$this->filtres_html['status_production'] = '<li>статус Пр-во: '.$_GET['status_production'].'<a href="'.$this->link_exit_out_filters('status_production').'" class="close">x</a></li>';	
				// }

				// фильтр по услуге
				if(isset($_GET['service_id']) && $_GET['service_id'] != ''){
					if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
						$this->Services_list_arr = $this->get_all_services_Database();
					}
					// получаем наименование услуги
					$this->Service_name = (isset($this->Services_list_arr[ $_GET['service_id'] ]['name'])?$this->Services_list_arr[ $_GET['service_id'] ]['name']:'данная услуга в базе не найдена');

					$this->filtres_html['service_id'] = '<li>услуга: '.$this->Service_name.'<a href="'.$this->link_exit_out_filters('service_id').'" class="close">x</a></li>';	
					// $this->filtres_html['service_name'] = $this->print_arr($this->Services_list_arr);
				}
	    	}

		
		/////////////////////////////////////////////////////////////////////////////////////
		//	-----  START  ----- 	ДЕКОДЕРЫ СТАТУСОВ ПОДРАЗДЕЛЕНИЙ 	-----  START  -----
		/////////////////////////////////////////////////////////////////////////////////////

	    	// вывод выбранного станка на протизводстве
	    	protected function get_machine_list($real_val,$row_id){
	    		$html = '';
	    		$select_html = '';

	    		// провкерка допуска на редактирование
	    		if($this->user_access == 1 || $this->user_access == 4){
	    			$html .='<select class="machine_type" data-id="'.$row_id.'">';
	    			if(!isset($this->user_access[$real_val])){
	    				$is_checked = 'selected="selected"';
	    				$select_html .= '<option value=\''.$real_val.'\' '.$is_checked.'>'.$real_val.'</option>';
	    			}
	    			foreach ($this->machine_list as $key => $value) {
						if ($key == $real_val) {
							$is_checked = 'selected="selected"';
						}else{
							$is_checked = '';
						}							
						$select_html .= '<option value=\''.$key.'\' '.$is_checked.'>'.$value.'</option>';
					}
					$html .= $select_html.'</select>';
	    		}else{
	    			if(isset($this->machine_list[$real_val])){
	    				$html = $this->machine_list[$real_val];	    				
	    			}else{
	    				$html = $real_val;
	    			}
	    		}
	    		return $html;
	    	}


			// вывод статусов плёнок
			protected function get_statuslist_film_photos($real_val,$cab_uslugi_id){
				$html = '';
				// флаг соответствия
				$conformity_true = 0;
				// если плёнки или клише не требуются то для всех кроме менеджеров и админов выводим неизменяемый статус
				if($real_val == 'не требуются' && ($this->user_access != 5 && $this->user_access != 1)){ return '<span class="greyText">'.$real_val.'</span>';}
				

				// проверяем предусмотрена ли для пользователя возможность выставлять статусы
				if(isset($this->status_film_photos[$this->user_access])){
					$html .= '<select class="statuslist_film_photos" data-id="'.$cab_uslugi_id.'">';
						$select_html = '';
						foreach ($this->status_film_photos[$this->user_access] as $key => $value) {
							if ($value == $real_val) {
								$is_checked = 'selected="selected"';
								$conformity_true = 1;
							}else{
								$is_checked = '';
							}
							
							$select_html .= '<option value=\''.$value.'\' '.$is_checked.'>'.$value.'</option>';
						}
					// если соответствий в разрешённых для пользователя статусах так и не было найдено, добавляем в список возможность выбора того, что стояло до того
					if(!$conformity_true){
						$html .= '<option value=\''.$real_val.'\' selected="selected">'.$real_val.'</option>';
					}
					$html .= $select_html;
					$html .= '</select>';
				}else{
					// узер без прав, просто транслируем тот статус который содержится в базе
					$html .= $real_val;
				}

				// если $html всё ещё пуст, значит прав на выставление статуса у юзера нет и поле пустое
				if(trim($html) == ""){$html="нет информации";}
				return $html;
			}

			// вывод статусов склада с возможностью их редактирования (опционально по флагу $enable_selection)
			protected function decoder_statuslist_sklad($real_val, $main_rows_id, $enable_selection = 0){
				/*
					$real_val - реальное значение поля в базе
					 
					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено


					в случае со складом статус по умолчанию имеется в массиве рабочих статусов и исключения для него писать не нужно
				*/

				$html = '';
				
				// проверяем на разрешение смены статуса снабжения
				if($this->user_access == 7 || $this->user_access == 1){ // на будущеее, пока работаем по параметру
				// if($enable_selection){
					$html .= '<select data-document_id="'.$this->specificate['id'].'" data-order_id="'.$this->Order['id'].'" class="choose_statuslist_sklad" data-id="'.$main_rows_id.'">';
						foreach ($this->statuslist_sklad as $name_en => $name_ru) {
							$is_checked = ($name_en == $real_val)?'selected="selected"':'';
							$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';
						}
					$html .= '</select>';
				}else{
					$html .='<span class="greyText">'.(isset($this->statuslist_sklad[$real_val])?$this->statuslist_sklad[$real_val]:$real_val).'</span>';
					
				}
				// возвращаем
				return $html;
			}

			// вывод статусов снабжения с возможностью выбора статуса
			protected function decoder_statuslist_snab($real_val, $date_delivery_product = '', $enable_selection = 0,$main_rows_id){
				/*
					$real_val - реальное значение поля в базе

					$date_delivery_product - дата ожидаемой поставки продукции на склад
					заводится при выборе статуса продукция ожидается

					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено
				*/

				$this->js_dop_class = '';
				// подсветка статусво снаба
				switch ($real_val) {
					case 'question':
						$this->js_dop_class = 'alert_class-red_service_status';
						break;	
					case 'not_adopted':
						$this->js_dop_class = 'alert_class-red_service_status';
						break;					
					default:
						break;
				}
				// $red_bg_color = (trim($real_val) == "question")?' style="background-color:rgba(255, 0, 0, 0.4);"':'';

				$html = '';
				// проверяем на разрешение смены статуса снабжения
				if($this->user_access == 8 || $this->user_access == 1 || $enable_selection){ // на будущеее, пока работаем по параметру
					$html .= '<select '.((isset($this->Order['id']) && $this->Order['snab_id'] == 0)?' data-order_id="'.$this->Order['id'].'"':"").' data-id="'.$main_rows_id.'" class="choose_statuslist_snab">';
						
						if($real_val == 'in_processed'){$html .= '<option value="in_processed" selected="selected">в обработке</option>';}
						// перебираем статусы склада (т.к. статусы склада транслируются в статусы снабжения)
						foreach ($this->statuslist_sklad as $name_en => $name_ru) {
							if ($name_en == $real_val) {
								$is_checked = 'selected="selected"';
								$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';	
							}
						}
						// если существует соответствие в сервисных статусах снаб
						if(isset($this->statuslist_snab_service[$real_val])){
							$html .= '<option value=\''.$real_val.'\' >'.$this->statuslist_snab_service[$real_val].'</option>';
							// $decoder_status_name = $this->statuslist_snab_service[$real_val];
						}
						// перебираем статусы снабжения
						foreach ($this->statuslist_snab as $name_en => $name_ru) {
							$is_checked = ($name_en == $real_val)?'selected="selected"':'';
							$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';
						}


					$html .= '</select>';
					// добавляем div с iput для редактирования ожидаемой даты поставки
					$html .= '<div data-id="'.$main_rows_id.'" class="waits_products_div '.(($date_delivery_product!='' && $real_val == "waits_products")?'show':'').'"><input typwe="text" value="'.$date_delivery_product.'"></div>';
				
				}else{
					if($real_val == 'in_processed'){// если статус in_processed, то его не декодировать в кириллицу с помощью стандартного массива, поэтому пишем исключение
						$html .='<span class="greyText">в обработке</span>';
					}else{
						$decoder_status_name = $real_val;
						// если существует соответствие в статусах склад
						if(isset($this->statuslist_sklad[$real_val])){
							$decoder_status_name = $this->statuslist_sklad[$real_val];
						}
						// если существует соответствие в статусах снаб
						if(isset($this->statuslist_snab[$real_val])){
							$decoder_status_name = $this->statuslist_snab[$real_val];
						}
						// если существует соответствие в сервисных статусах снаб
						if(isset($this->statuslist_snab_service[$real_val])){
							$decoder_status_name = $this->statuslist_snab_service[$real_val];
						}


						// выводим трансляцию статуса снабжения
						$html .='<span class="greyText">'.$decoder_status_name.'</span>';
					}
					// добавляем div c ожидаемой датой поставки
					if(isset($this->group_access) && $this->group_access == 7){// ффильтр по ожидаемой дате поставки для склада
						$html .= '<div  data-id="'.$main_rows_id.'" class="waits_products_div '.(($date_delivery_product!='' && $real_val == "waits_products")?'show':'').'">';
							$html .= '<a href="'.$this->link_enter_to_filters('date_delivery_product',$date_delivery_product).'">'.$date_delivery_product.'</a>';
						$html .= '</div>';
					}else{
						$html .= '<div  data-id="'.$main_rows_id.'" class="waits_products_div '.(($date_delivery_product!='' && $real_val == "waits_products")?'show':'').'">'.$date_delivery_product.'</div>';
					}
					
					
				}
				

				// возвращаем
				return $html;
			}

			// вывод статусов бухгалтерии с возможностью выбора статуса
			protected function decoder_statuslist_buch($real_val, $enable_selection = 0, $document = array()){
				/*
					$real_val - реальное значение поля в базе

					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено
				*/

				$html = '';
				// если стоит is_pending - ставим кнопку
				if($real_val == "is_pending" && $this->user_access!=2 && $this->user_access!=8){
					$button_name = 'Запросить счёт';
					$button_class = 'query_the_bill';
					if (!empty($document) && $document['doc_type'] == 'oferta' && $document['doc_num'] == 0) {
						$button_name = 'Запросить ОФ';
						$button_class = 'query_the_bill_oferta';
					}
					return '<input type="button" name="query_the_bill" class="'.$button_class.'" value="'.$button_name.'">';
				}

				if(isset($this->buch_status[$real_val])){
					$html .='<span class="greyText get_requeried_expense_menu">'.$this->buch_status[$real_val].'</span>';
				}else if(isset($this->buch_status_service[$real_val])){
					$html .='<span class="greyText get_requeried_expense_menu">'.$this->buch_status_service[$real_val].'</span>';
				}else{
					$html .='<span class="greyText get_requeried_expense_menu">'.$real_val.'</span>';
				}
				return $html;
			}

			// вывод статусов заказа/предзаказа с возможностью выбора статуса
			protected function decoder_statuslist_order_and_paperwork($real_val, $enable_selection = 0){
				$html = '';
				// определяем рабочий массив статусов для работы (ЗАКАЗ или ПРЕДЗАКАЗ)
				if (array_key_exists($real_val, $this->paperwork_status) && isset($_GET['section']) && $_GET['section'] != 'orders') { // ищем ключ
					$status_arr = $this->paperwork_status;
				}else if (array_key_exists($real_val, $this->order_status)){
					$status_arr = $this->order_status;
				}else if (array_key_exists($real_val, $this->order_service_status)){
					$status_arr = $this->paperwork_status;
				}else{
					return $real_val.' (статус не известен)';// статус не известен
				}

				if(isset($status_arr[$real_val])){
					$html .='<span class="greyText">'.$status_arr[$real_val].'</span>';
				}else if(isset($this->order_service_status[$real_val])){
					$html .='<span class="greyText">'.$this->order_service_status[$real_val].'</span>';
				}else{
					$html .='<span class="greyText">'.$real_val.'</span>';
				}
				return $html;
			}

			// ВЫВОД ВСЕХ СТАТУСОВ ПО ПОЗИЦИЯМ
			protected function position_status_list_Html($cab_order_main_row){	
				
				// собираем массив для поиска
				$search_glob_status_arr = $this->paperwork_status; // закидываем туда статусы предзаказа
				$search_glob_status_arr['in_operation'] = 'Запуск в работу';// добавляем статус запуск в работу
				
				if(array_key_exists($this->Order['global_status'], $search_glob_status_arr)){
					 // если в созданном нами массиве попался текущий статус заказа - возвращаем html
					return '<td style="width: 78px;">
								<span class="greyText">Отделы</span>
							</td>
							<td>
								<span>Ожидают запуска заказа</span>
							</td>';
				}

				// убиваем массив поиска
				unset($search_glob_status_arr);



				// собираем вывод
				$html = '<td colspan="2"  class="orders_status_td_tbl"  style="width:250px">';
				$html .= '<table  style="min-width:240px">';


				// выодим статус снабжения
				$this->performer_status = $this->decoder_statuslist_snab($cab_order_main_row['status_snab'],$cab_order_main_row['date_delivery_product'],0,$cab_order_main_row['id']);
				if($this->user_access == 8){
					// $this->performer_status = ''.$this->performer_status.'</a>';
				}
				$html .= '<tr>';
					$html .= '<td style="width: 78px;"  class="filter_class" data-href="'.$this->link_enter_to_filters('status_snab',$cab_order_main_row['status_snab']).'">';
					$html .= '<div class="otdel_name" style="padding: 5px;">Снабжение</div>';
					$html .= '</td>';
					$html .= '<td>';				

						$snab_comment_text = base64_decode($cab_order_main_row['snab_comment']);
						$snab_comment_text_tag = ($snab_comment_text != '')?'data-hint="'.$snab_comment_text.'"':'';
						$comment_text_tag_class = ($snab_comment_text != '')?'hint hint_red':'';

						$html .= '<div class="otdel_status '.$this->js_dop_class.' '.$comment_text_tag_class.'" '.$snab_comment_text_tag.'>';
							// привеодим статус снабжения к необходимому виду		
							$html .= '<div class="performer_status">'.$this->performer_status.'</div>';				
						$html .= '</div>';									
					$html .= '</td>';
				$html .= '</tr>';

				//выводис статус склад
				$html .= '<tr>';
					$html .= '<td>';
					$html .= '<div class="otdel_name">Cклад</div>';
					$html .= '</td>';
					$html .= '<td>';				
						$html .= '<div class="otdel_status">';	
							// приводим статус склада к необходимому виду			
							$html .= '<div class="performer_status">'.$this->decoder_statuslist_sklad($cab_order_main_row['status_sklad'], $cab_order_main_row['id']).'</div>';				
						$html .= '</div>';									
					$html .= '</td>';
				$html .= '</tr>';

				//$html .= '<tr><td colspan="2">'.$this->print_arr($this->Position_status_list).'</td></tr>';
				
				$this->position_question = 0;
				// выводим статусы услуг
				foreach ($this->Position_status_list as $performer => $performer_status_arr) {
					$html .= '<tr>';
					$html .= '<td>';

					$html .= '<div class="otdel_name">'.$performer.'</div>';
					$html .= '</td>';
					$html .= '<td>';

					foreach ($performer_status_arr as $key => $value) {
						$performer_status = $this->get_statuslist_uslugi_Dtabase_Html($value['id'],$value['performer_status'],$value['id_dop_uslugi_row'],$value['performer']);
						$this->get_performer_status($value,$performer_status);

						if($performer_status == ''){
							$this->position_question = 1;
						}

						$snab_comment_text = base64_decode($value['performer_comment']);
						$snab_comment_text_tag = ($snab_comment_text != '')?'data-hint="'.$snab_comment_text.'"':'';
						$comment_text_tag_class = ($snab_comment_text != '')?'hint hint_red':'';

						$html .= '<div class="otdel_status '.$this->js_dop_class.' '.$comment_text_tag_class.'" data-pisition_id="'.$this->position['id'].'" data-id="'.$value['id_dop_uslugi_row'].'" '.$snab_comment_text_tag.'>';
						$html .= '<div class="service_name">'.$value['service_name'].'</div>';
						$html .= '<div class="performer_status">'.$this->performer_status.'</div>';
							
						$html .= '</div>';		
					}

					$html .= '</td>';
					$html .= '</tr>';
				}						
				$html .= '</table>';
				$html .= '</td>';	
				// echo '<pre>';
				// print_r($this->Position_status_list);
				// echo '</pre>';
				return $html;
			}

			// получаем статусы услуг по которым услуги должны попасть во вкладку ТЗ не корреутно/ пауза /вопрос
			protected function get_services_status_pause(){
				if(!isset($this->services_status_pause)){
					global $mysqli;
					$query = "SELECT * FROM `".USLUGI_STATUS_LIST."` WHERE `pause`='on'";
					
					$result = $mysqli->query($query) or die($mysqli->error);
						
					if($result->num_rows > 0){			
						while($row = $result->fetch_assoc()){
							$this->services_status_pause[$row['id']] = $row['name'];
						}						
					}
				}
				return $this->services_status_pause;
			}

			protected function get_performer_status($service,$performer_status){
						$this->js_dop_class = '';

						$this->performer_status = $performer_status;

						$this->get_services_status_pause();
						if(in_array($service['performer_status'], $this->services_status_pause)){
							$this->poused_and_question = 0;		
							$this->js_dop_class = 'alert_class-red_service_status';					
						}

						switch ($service['performer_status']) {
							case 'ТЗ не корректно':
								
								break;
							// case 'стоимость работ не корректна':
							// 	$this->poused_and_question = 0;	
							// 	$this->js_dop_class = 'alert_class-black_service_status';
							// 	break;
							// case 'пауза':
							// 	$this->poused_and_question = 0;	
							// 	$this->js_dop_class = 'alert_class-red_service_status';
							// 	break;
							// case 'вопрос':
							// 	$this->poused_and_question = 0;
							// 	$this->js_dop_class = 'alert_class-red_service_status';
							// 	break;
							case 'макет отправлен в СНАБ':
								if($this->user_access == 8){
									$this->js_dop_class = 'js-button-maket_is_adopted';
									$this->performer_status = '<button  data-position_id="'.$this->position['id'].'"  data-id="'.$service['id_dop_uslugi_row'].'">Макет принят</button>  ';
								}
								break;
							case 'дизайн-эскиз готов':
								if($this->user_access == 5){
									// echo 'меню: дизайн утвержден, правка';
									$this->js_dop_class = 'js-modal_menu-design_what';
								}
								break;

							case 'оригинал-макет готов':
								if($this->user_access == 5){
									// echo 'меню: макет утвержден, правка';
									$this->js_dop_class = 'js-modal_menu-maket_what';
								}
								break;
									
							default:
								# code...										
								break;
						}
					}

			// выпадающий список статусов услуги
			protected function get_statuslist_uslugi_Dtabase_Html($id,$real_val,$cab_dop_usl_id, $performer){
				// $performer - подразделение (права доступа)
				
				
				$this->js_dop_class = '';
				// подсветка статусов снаба
				switch ($real_val) {
					case 'question':
						$this->js_dop_class = 'alert_class-red_service_status';
						break;					
					default:
						break;
				}


				if(trim($real_val)!="" || $real_val == "in_processed"){// если есть статус - значит услуга запущена
					// проверяем права доступа на редактирование статуса
					$position_id = (isset($this->position['id']))?$this->position['id']:'';
					if($this->user_access == $performer || $this->user_access==1){
						// получаем id по которым будем выбирать статусы для услуги
						$id_s = $this->get_id_parent_Database($id);
						global $mysqli;
						$html = '';
						
						$html .= '<select class="get_statuslist_uslugi" data-position_id="'.$position_id.'" data-id="'.$cab_dop_usl_id.'"><option value=""></option>';
						
						if($real_val=="исправить дизайн"){
							$html.= '<option value="'.$real_val.'" selected="selected"> '.$real_val.'</option>';
						}
						if($real_val=="Ожидает обработки"){
							$html.= '<option value="'.$real_val.'" selected="selected"> '.$real_val.'</option>';
						}

						$query = "SELECT * FROM `".USLUGI_STATUS_LIST."` WHERE `parent_id` IN (".$id_s.") ORDER BY `parent_id` ASC";
						
						$result = $mysqli->query($query) or die($mysqli->error);
						
						if($result->num_rows > 0){			
							while($row = $result->fetch_assoc()){
								$is_checked = ($real_val==$row['name'])?'selected="selected"':'';
								$question = ($row['get_performer_comment'] == "on")?'data-question="1"':'data-question="0"';
								$html.= '<option value="'.$row['name'].'" '.$question.' '.$is_checked.'><!--'.$row['id'].' '.$row['parent_id'].'--> '.$row['name'].'</option>';
							}						
						}
						$html.= '</select>';	
					}else{
						$html = '<span  class="greyText"  data-id="'.$cab_dop_usl_id.'">'.(($real_val=="in_processed")?'обрабатывается':$real_val).'</span>';
					}					
					return $html;
				
				}else{// если статус отсутствует - услуга ещё не запущена
					if($real_val=="in_processed"){
						if($this->user_access == $performer){
							$html = '<input type="button" value="Взять в работу" class="start_statuslist_uslugi" data-id="'.$cab_dop_usl_id.'">';	
						}else{
							$html = 'ожидает обработки';
						}
						
					}else{
						// все услуги могут запускать только АДМИНЫ и СНАБЫ
						if($this->user_access == 1 || $this->user_access== 8){
							$html = '<input type="button" value="Запуск" class="start_statuslist_uslugi" data-id="'.$cab_dop_usl_id.'">';	
						}else{

							$html = 'ожидает запуска';
						}
					}
					
					
					return $html;
				}
			}

			
			//////////////////////////////////////////////////////////////////////////////////////////
			//  -- START --  получаем id по которым будем выбирать статусы для услуги  -- START --  //
			//////////////////////////////////////////////////////////////////////////////////////////
				protected function get_id_parent_Database($id){
					global $mysqli;
					if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
						$this->Services_list_arr = $this->get_all_services_Database();
					}
					return $this->merge_arr_id_s($id);			 
				}
				// возвращает строку с id услуги и её родителей по id услуги
				protected function merge_arr_id_s($id){
					$arr = array();
					$arr[] = $id;
					$parent_id = $id;
					while ($parent_id!=0) {
						if(isset($this->Services_list_arr[$parent_id]['parent_id'])){
							$parent_id = $this->Services_list_arr[$parent_id]['parent_id'];						
						}else{
							$parent_id = 0;
						}
						
						if($parent_id!=0){
							$arr[] = $parent_id;
						}
					}
					$str = implode(',', $arr);
					return $str;
				}

				// запрос на все услуги
				protected function get_all_services_Database(){
					global $mysqli;
					$arr = array();
					$query = "SELECT *
					 FROM `".OUR_USLUGI_LIST."`";
					 // echo $query;	
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){	
						while($row = $result->fetch_assoc()){
							$arr[$row['id']] = $row;
						}
					}	
					return $arr;
				}

			//////////////////////////////////////////////////////////////////////////////////////
			//  -- END --  получаем id по которым будем выбирать статусы для услуги  -- END --  //
			//////////////////////////////////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////////
		//   -----  END  -----     ДЕКОДЕРЫ СТАТУСОВ ПОДРАЗДЕЛЕНИЙ 	    -----  END  -----
		///////////////////////////////////////////////////////////////////////////////////

			
			// роутер по запросам
			protected function requests_Template($id_row=0){
				include_once './libs/php/classes/cabinet/cabinet_requests_class.php';
				new Requests($id_row,$this->user_access,$this->user_id);					
			}



			
			// ссылка на фильтр по номеру заказа
			protected function link_enter_to_filters($keyName,$newVal){
				$name = $keyName.'link';
				if(!isset($$name)){
					$link = 'http://'.$_SERVER['HTTP_HOST'].'/os/';

					$n = 0;
					foreach ($_GET as $key => $value) {
						if($key!=$keyName){
							$link .= (($n>0)?'&':'?').$key.'='.$value;
							$n++;
						}
					}
					$link .= (($n>0)?'&':'?').$keyName.'='.$newVal;				
					$name = $link;	
				}
				return $name;				
			}
			// ссылка выхода из фильтра
			protected function link_exit_out_filters($keyName){
				//$this->order_num;
					
				$link = 'http://'.$_SERVER['HTTP_HOST'].'/os/';

				$n = 0;
				foreach ($_GET as $key => $value) {
					if($key!=$keyName){
						$link .= (($n>0)?'&':'?').$key.'='.$value;
						$n++;
					}
				}

				return $link;
			}
			

					

			// 	//comments
			// }

			protected function create_the_comment($title, $check_for_empty_val = 1){
				if (!isset($_POST['comment_text']) || isset($_POST['comment_text']) && trim($_POST['comment_text']) =="" ) {
					$html = '';
					$html .= '<form>';
					// перебираем остальные значения для передачи их далее
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}
					if (isset($_POST['comment_text']) && trim($_POST['comment_text']) =="" && $check_for_empty_val == 1) {
						$html .= $this->wrap_text_in_warning_message('Пожалуйста заполните описание более подробно');
					}

					$html .= '<div class="comment table">';
						$html .= '<div class="row">';
							$html .= '<div class="cell comment_text">';
									$html .= '<textarea name="comment_text"></textarea>';
									$html .= '<div class="div_for_button">';
										$html .= '<button class="add_nah">Без комментария</button>';
										// $html .= '<button id="add_new_comment_button">Отправить</button>';
									$html .= '</div>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '</form>';
					echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"'.$title.'","width":"600"}';
					exit;
				}
			}

			// создаем пустой счёт
			protected function create_the_new_bill_AJAX(){
				// если мы имеем дело не с обычным счётом выводим окно с комментариями по заказанному документу(счёту)
				if(isset($_POST['type_the_bill']) && $_POST['type_the_bill'] != "the_bill" && (!isset($_POST['comment_text']) || trim($_POST['comment_text']) =="" )){
					$html = '';
					$html .= '<form>';
					// перебираем остальные значения для передачи их далее
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}

					//////////////////////////////
					//	форма комментария для БУХ
					//////////////////////////////


					$html .= '<div class="comment table">';
						$html .= '<div class="row">';
							$html .= '<div class="cell comment_text">';
									$html .= '<textarea name="comment_text"></textarea>';
									$html .= '<div class="div_for_button">';
										// $html .= '<button class="add_nah">Нах</button>';
										// $html .= '<button class="add_nah">Нах?</button>';
										$html .= '<button class="add_nah">Без комментария</button>';
										// $html .= '<button id="add_new_comment_button">Отправить</button>';
									$html .= '</div>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '</form>';
					echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Комментарии для Бухгалтерии:","width":"600"}';
				

				}else{

					if(isset($_POST['get_html_row_the_bill'])){ // если нам нужно вернуть  только строку
						echo '{"response":"OK","function":"add_new_bill_in_window","html":"'.base64_encode($this->create_a_new_bill()).'"}';	
						return;
					}
					// меняем статус бух и заказа
					$this->buch_status_select($_POST['status_buch'],$_POST['order_id']);
					// заводим счёт
					//$this->create_a_new_bill();						
					// вывод окна бух учёта
					echo '{"response":"OK"}';
					//$this->get_window_buh_uchet_AJAX();
					// echo '{"response":"show_new_window_simple", "html":"'.base64_encode($this->get_window_buh_uchet()).'","title":"Бухгалтерский учёт:","width":"1100"}';				
				}
				exit;
			}

			// редактирование входящей стоимости услуги из окна фин. инфо
			protected function edit_price_in_for_postfactum_service_AJAX(){
				$this->db_edit_one_val(CAB_DOP_USLUGI,'quantity',(int)$_POST['row_id'],(int)$_POST['value']);
				echo '{"response":"OK"}';
				exit;
				// echo '{"response":"show_new_window_simple","title":"test","html":"'.base64_encode($this->print_arr($_POST)).'"}';
			}

			// редактирование тиража услуги из окна фин. инфо
			protected function edit_quantity_for_postfactum_service_AJAX(){
				if(!isset($_POST['for_how'])){
					echo '{"response":"show_new_window_simple","title":"test","html":"'.base64_encode("Почему-то пришли не все данные. =(").'"}';
					exit;	
				}
				$this->db_edit_one_val(CAB_DOP_USLUGI,'price_in',(int)$_POST['row_id'],(int)$_POST['value']);
				echo '{"response":"OK"}';
				// echo '{"response":"show_new_window_simple","title":"test","html":"'.base64_encode($this->print_arr($_POST)).'"}';
				exit;
			}

			// смена даты подписи спецификации
			protected function change_date_specification_signed_AJAX(){
				// $html = '';
				// $html .= $this->print_arr($_POST);

				// $html .= date("Y-m-d",strtotime($_POST['date']));
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`date_specification_signed` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
				//echo '{"response":"show_new_window_simple", "html":"'.base64_encode($html).'","title":"Разработчику!!!"}';
			}

			// смена даты выставления счёта
			protected function change_date_create_the_bill_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`date_create_the_bill` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['id_row']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}
			// смена даты выставления счёта-оферты
			protected function change_date_create_the_bill_oferta_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`date_create_the_bill` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['id_row']."';";
				$result = $mysqli->query($query) or die($mysqli->error);

				// правим дату создания оферты 
				$query = "UPDATE  `".OFFERTS_TBL."`  SET  
					`date_time` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['doc_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// смена даты возврата подписанной спецификации
			protected function change_date_return_width_specification_signed_AJAX(){
				// $html = '';
				// $html .= $this->print_arr($_POST);

				// $html .= date("Y-m-d",strtotime($_POST['date']));
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`date_return_width_specification_signed` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				//echo '{"response":"show_new_window_simple", "html":"'.base64_encode("Запрос нужно делать").'","title":"Разработчику!!!"}';
				exit;
			}

			// сохранение номера счёта
			protected function change_number_the_bill_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`number_the_bill` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}
			// сохранение номера счёта-оферты
			protected function change_number_the_bill_offerta_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`number_the_bill` =  '".addslashes($_POST['value'])."' 
					,`doc_num` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);

				// правим номер счёта оферты в спецификации
				$query = "UPDATE  `".OFFERTS_TBL."`  SET  
					`num` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['doc_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				echo '{"response":"OK"}';
				exit;
			}

			// сохранение суммы счёта
			protected function change_for_price_the_bill_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`payment_status` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// нажатие на кнопку счёт выставлен
			protected function the_bill_is_ready_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`buch_status` =  'score_exhibited' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK","function":"reload_order_tbl"}';
				exit;
			}


			
			//получаем информацию по документу
			protected function get_info_for_document($spec_id){
				global $mysqli;
				//date_create_the_bill
				//получаем информацию по спецификации
				$query = "SELECT *,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y')  AS `create_time`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_specification_signed`,'%d.%m.%Y')  AS `date_specification_signed`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_return_width_specification_signed`,'%d.%m.%Y')  AS `date_return_width_specification_signed`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_create_the_bill`,'%d.%m.%Y')  AS `date_create_the_bill`
				 FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `id` IN ('".(int)$spec_id."');";
				$result = $mysqli->query($query) or die($mysqli->error);
				$Specificate_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Specificate_arr[] = $row;
					}
				}
				return $Specificate_arr;
			}
			// получаем спецификации по номеру заказа
			protected function get_info_for_spec_for_order_num($order_num){
				global $mysqli;
				//date_create_the_bill
				//получаем информацию по спецификации
				$query = "SELECT *,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y')  AS `create_time`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_specification_signed`,'%d.%m.%Y')  AS `date_specification_signed`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_return_width_specification_signed`,'%d.%m.%Y')  AS `date_return_width_specification_signed`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_create_the_bill`,'%d.%m.%Y')  AS `date_create_the_bill`
				 FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_num` IN ('".(int)$order_num."');";
				$result = $mysqli->query($query) or die($mysqli->error);
				$Specificate_arr = array();
				// echo $query;

				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Specificate_arr[] = $row;
					}
				}
				return $Specificate_arr;
			}


			// прикрепление спецификаций к существующему заказу
			protected function attach_the_specification_for_other_order_AJAX(){
				// запрашиваем список заказов по client_id
				global $mysqli;
				$query = "SELECT * FROM `".CAB_ORDER_ROWS."` WHERE `client_id` = '".(int)$_GET['client_id']."';";
				$order_arr = array();
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$order_arr[$row['order_num']]['order_num'] = $row['order_num'];
						$order_arr[$row['order_num']]['order_id'] = $row['order_id'];
					}
				}

				if(empty($order_arr)){
					echo '{"response":"show_new_window_simple","title":"Предупреждение","html":"'.base64_encode('К сожалению у данного клиента не сформировано ни одного заказа.').'"}';
					return;
				}

				$html  = '';
				$html = '<form>';

				$html .= '<div class="inform_message">Введите номер заказа:</div>';
				$html .= '<input type="text" value="" name="order_num" id="input_width_order_num"><br>';
				//$html .= '<input type="text" value="'.base64_encode(json_encode($order_arr)).'" name="order_arr"><br>';
				$html .= '<input type="text" value="'.$_POST['checked_spec_id'].'" name="checked_spec_id"><br>';
				// $html .= '<input type="text" value="" name="order_id" id="input_width_order_id"><br>';				
				$html .= '<input type="text" value="attach_the_specification_for_other_order_steep_2" name="AJAX" id="attach_the_specification_for_other_order_steep_2">';
				$html .= '</form>';

				echo '{"response":"show_new_window","html":"'.base64_encode($html).'","title":"Прикрепить к заказу"}';
				exit;
			}


			// шаг второй, проверка введённого номера заказа на существование онного
			// если заказ существует, спецификация прикрепляется к нему
			protected function attach_the_specification_for_other_order_steep_2_AJAX(){
				// запрашиваем список заказов по client_id
				global $mysqli;
				$query = "SELECT * FROM `".CAB_ORDER_ROWS."` WHERE `client_id` = '".(int)$_GET['client_id']."';";
				$order_arr = array();
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$order_arr[$row['order_num']]['order_num'] = $row['order_num'];
						$order_arr[$row['order_num']]['order_id'] = $row['order_id'];
					}
				}


				$order_num = (int)$_POST['order_num'];
				if(!isset($order_arr[$order_num])){
					$html = '<form>';
					$html .= '<div class="warning_message"><div>Такого заказа не существует. Попробуйте ввести другой номер.</div></div>';
					$html .= '<div class="inform_message">Введите номер заказа:</div>';
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" value="'.$value.'" name="'.$key.'" >';
					}
					$html .= '<input type="text" value="" name="order_num" id="input_width_order_num"><br>';
					$html .= '</form>';

					echo '{"response":"show_new_window","html":"'.base64_encode($html).'","title":"Прикрепить к заказу"}';
					return;
				}else{
					$this->attach_the_specification_for_other_order_Database($order_arr[$order_num]['order_id'],$order_arr[$order_num]['order_num'],$_POST['checked_spec_id']);
					echo '{"response":"OK","function":"reload_order_tbl"}';
				}
				exit;
			}

			// создание заказа
			protected function create_new_order_AJAX(){
				global $mysqli;

				// запрашиваем максимальный номер заказа
				$query  = "SELECT max(order_num) AS `order_num` FROM `".CAB_ORDER_ROWS."`;";
				$result = $mysqli->query($query) or die($mysqli->error);
				$order_num_MAX = 0;
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$order_num_MAX = $row['order_num'];
					}
				}
				$order_num_NEW = $order_num_MAX + 1;




				// создаем строку заказа
				$query = "INSERT INTO `".CAB_ORDER_ROWS."` SET ";
				$query .= " `order_num` = '".$order_num_NEW."'";
				$query .= ", `client_id` = '".$_GET['client_id']."'";
				$query .= ", `manager_id` = '".$_POST['manager_id']."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				// $query .= ", `manager_id` = '".$_GET['client_id']."'";

				$order_id = $mysqli->insert_id;
				
				$this->attach_the_specification_for_other_order_Database($order_id,$order_num_NEW,$_POST['checked_spec_id']);

				echo '{"response":"OK","function":"location_href","href":"?page=cabinet&section=paperwork&subsection=the_order_is_create"}';
				exit;
			}

			protected function attach_the_specification_for_other_order_Database($order_id,$order_num_NEW,$id_string){
				global $mysqli;
				// запоминаем номер заказа в спецификациях
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`order_num` =  '".$order_num_NEW."', 
					`order_id` =  '".$order_id."'
					WHERE  `id` IN (".$id_string.");";
					// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
			}

			// записывает статус запроса
			protected function chenge_query_status($query_num,$new_status){
				// устанавливаем статус запроса на history
				global $mysqli;
                $query = "UPDATE `".RT_LIST."` SET `status`= '".$new_status."' 
                WHERE  `query_num` = '".$query_num."';";
                $result = $mysqli->query($query) or die($mysqli->error);
                return true;
			}

        /**
         * @param $id
         * @param $new_status
         * @return bool
         */
			protected function updateQueryStatus($id, $new_status){
				// устанавливаем статус запроса на history
				global $mysqli;
                $query = "UPDATE `".RT_LIST."` SET `status`= '".$new_status."'"; 
                if($new_status=="in_work" && $this->user_access != 1){
                	$query .= " ,`manager_id`= '".$this->user_id."'";
                }
                $query .= " WHERE  `id` = '".$id."';";
                $result = $mysqli->query($query) or die($mysqli->error);
                return true;
			}

			// пересчёт оплаты по спецификации
			protected function calculate_the_pyment_price($spec_id){
				// проверка на дату оплаты
					if(!isset($_POST['date']) || isset($_POST['date']) && trim($_POST['date']) == ''){
						$message = 'Для корректного сохранения данных по оплате, сначала заполните поле "дата"!!!';
						$json = '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
						echo $json;
						exit;
					}




				// запрашиваем информацию по строке спец-ии
				global $mysqli;
				$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `id` = '".$spec_id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$Specificate = array();
				if($result->num_rows > 0){	
					while($row = $result->fetch_assoc()){
						$Specificate = $row;
					}
				}

				$query_num = $Specificate['query_num'];
				// $Specificate_arr = $this->get_info_for_spec($spec_id);
				// получаем ПКО
				$pko_arr = $this->get_pko_list_for_document_Database($spec_id);

				// получаем ПП 
				$pp_arr = $this->get_pp_list_for_document_Database($spec_id);

				$pp_summ = 0; // общая сумма проплаченных денег по документу
				foreach ($pp_arr as $key => $pp) {
					$pp_summ += $pp['price_from_pyment'];
				}
				foreach ($pko_arr as $key => $pp) {
					$pp_summ += $pp['price_from_pyment'];
				}

				
				$percent_payment = $this->calculation_percent_of_payment($Specificate['spec_price'], $pp_summ);


				//////////////////////////
				//	обновляем данные по спецификации
				//////////////////////////
					$query = "UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET";
					$query .= "`payment_status` = '".$pp_summ."'";

					if($pp_summ > 0 && $pp_summ < $Specificate['spec_price']){
						if($Specificate['buch_status'] != 'partially_paid'){
							$query .= ", `buch_status` = 'partially_paid'";
							$message = "Статус по документу изменён на \"частично оплачен\".";		
							
							// перевод запроса в history
							if($this->chenge_query_status($query_num,'history')){
								$message .= '&nbsp; Запрос № '.$query_num.' перемещён во вкладку "История"';	
							}				
							
						}
					}else if($pp_summ > 0 && $pp_summ >= $Specificate['spec_price']){
						if($Specificate['buch_status'] != 'payment'){
							$query .= ", `buch_status` = 'payment'";
							$message = "Статус по документу изменён на \"оплачен\"";
							// перевод запроса в history
							if($this->chenge_query_status($query_num,'history')){
								$message .= '&nbsp; Запрос № '.$query_num.' перемещён во вкладку "История"';	
							}	
						}
					}else if($pp_summ == 0 && $pp_summ >= $Specificate['spec_price']){
						if($Specificate['buch_status'] != 'payment'){
							$query .= ", `buch_status` = 'payment'";
							$message = "Статус по документу изменён на \"оплачен\"";
							// перевод запроса в history
							if($this->chenge_query_status($query_num,'history')){
								$message .= '&nbsp; Запрос № '.$query_num.' перемещён во вкладку "История"';	
							}	
						}
					}

					//////////////////////////
					//	если процент оплаты превышает или равен оговорённому в спецификации - делаем пометку
					//////////////////////////
						if ($percent_payment >= (int)$Specificate['prepayment']) {
							$query .= ", `enabled_start_work` = '1'";
							$query .= ", `payment_date` = '".date('Y-m-d',strtotime($_POST['date']))."'";
						}else{
							$query .= ", `enabled_start_work` = '0'";
							$query .= ", `payment_date` = '0000-00-00'";
						}
						

					$query .= " WHERE `id` = '".$spec_id."'";
					// echo $query;
					$result = $mysqli->query($query) or die($mysqli->error);

				//////////////////////////
				//	если процент оплаты превышает или равен оговорённому в спецификации
				//  а так же спецификация принадлежит заказу, проверяем все спецификации по данному заказу	
				//////////////////////////
				if ($percent_payment >= (int)$Specificate['prepayment']) {
					// $query .= ", `enabled_start_work` = '1'";
					// если cпецификация прикреплена к заказу
					if($Specificate['order_id'] > 0){ 
						// запрашиваем все спецификации
						$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$Specificate['order_id']."'";
						$result = $mysqli->query($query) or die($mysqli->error);
						$gorder_go_start = 1;

						if($result->num_rows > 0){	
							while($row = $result->fetch_assoc()){
								if($row['enabled_start_work'] == 0){
									$gorder_go_start = 0;
								}
							}
						}
						if($gorder_go_start){
							
							// получаем статус заказа
							$order_status = '';
							$query = "SELECT * FROM `".CAB_ORDER_ROWS."` ";
							$query .= " WHERE `id` = '".$Specificate['order_id']."'";
							$result = $mysqli->query($query) or die($mysqli->error);
							if($result->num_rows > 0){	
								while($row = $result->fetch_assoc()){
									$order_status = $row['global_status'];									
								}
							}

							//$message = (isset($message)?$message.'<br>':'').$gorder_go_start.'<br>'.$order_status.'<br>'.(!isset($this->order_status[$order_status])?1:0);

							// если статус получен и заказ еще в разделе предзаказа
							if($order_status != '' && !isset($this->order_status[$order_status])){
								$query = "UPDATE `".CAB_ORDER_ROWS."` SET";
								$query .= "`global_status` = 'in_operation'";
								$query .= " WHERE `id` = '".$Specificate['order_id']."'";
								$result = $mysqli->query($query) or die($mysqli->error);
								$message = "Статус заказа № ".$this->show_order_num($Specificate['order_num'])." изменён на \"запуск в работу\"";
							}							
						}else{
							// перевод заказа в оформление
							$query = "UPDATE `".CAB_ORDER_ROWS."` SET";
								$query .= "`global_status` = 'being_prepared'";
								$query .= " WHERE `id` = '".$Specificate['order_id']."'";
								$result = $mysqli->query($query) or die($mysqli->error);
								$message = "Статус заказа № ".$this->show_order_num($Specificate['order_num'])." изменён на \"В оформлении\"";
						}
					}
				}


				// $message .= '<br>'.$query;
				if(isset($message) && $message!=''){
					echo '{"response":"OK","function2":"reload_order_tbl","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
					exit;	
				}
				
			}



			//////////////////////////
			//	ПП 
			//////////////////////////
				//////////////////////////
				//	редактирование строки
				//////////////////////////
					// редактирование номера
					protected function pp_edit_number_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_ORDER."` SET";
						$query .= "`number` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}

					// редактирование даты
					protected function pp_edit_date_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_ORDER."` SET";
						$query .= "`payment_date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);

						// $query = "UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET";
						// $query .= "`payment_date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						// $query .= " WHERE `id` = '".$_POST['spec_id']."'";
						// // echo $query;
						// $result = $mysqli->query($query) or die($mysqli->error);

						echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode('Дата оплаты по счёту успешно изменена на '.$_POST['value']).'"}';
						exit;
					}
					// редактирование суммы
					protected function pp_edit_payment_summ_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_ORDER."` SET";
						$query .= "`price_from_pyment` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						
						$this->calculate_the_pyment_price((int)$_POST['specification_id']);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование комментариев
					protected function pp_edit_comments_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_ORDER."` SET";
						$query .= "`comments` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}

				// создание ПП
				protected function create_row_pp_AJAX(){
					global $mysqli;
					$query = "INSERT INTO `".CAB_PYMENT_ORDER."` SET";
					$query .= " `specificate_id` = '".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);			

					echo '{"response":"OK","function":"show_new_row_pp","id":"'.$mysqli->insert_id.'"}';
					exit;
				}
				// удалить ПП
				protected function delete_PP_AJAX(){
					global $mysqli;
					$query = "DELETE FROM `".CAB_PYMENT_ORDER."` WHERE `id`='".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					echo '{"response":"OK"}';
					exit;
				}


				// получаем ПП 
				protected function get_pp_list_for_document_Database($id){
					global $mysqli;
					$query = "SELECT *,
					DATE_FORMAT(`".CAB_PYMENT_ORDER."`.`payment_date`,'%d.%m.%Y %H:%i:%s')  AS `payment_date`
					 FROM `".CAB_PYMENT_ORDER."` WHERE `specificate_id` = '".$id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					$pp_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$pp_arr[] = $row;
						}
					}
					return $pp_arr;
				}

				// возвращает список ПП по спецификации
				protected function get_pp_list_for_document_Html($document){
					$html = '';
					// шаблон скрытой пустой формы для заведения 
					$html_hidden_template = '<div class="document_pp_hidden">';
						$html_hidden_template .= '<span style="font-style: italic;">номер (п/п): </span>';
						$html_hidden_template .= '<input type="text" data-id=""  name="number" class="number_pp">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">от</span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="дата оплаты"  name="payment_date" class="payment_date">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">в размере: </span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="сумма оплаты"  name="price_from_pyment" class="price_from_pyment">';
						$html_hidden_template .= '   <input type="text" data-id="" placeholder="комментарии"  name="comments" class="comments">';
						$html_hidden_template .= '<span class="del_pp">X</span>';
					$html_hidden_template .= '</div>';

					// получаем ПП 
					$pp_arr = $this->get_pp_list_for_document_Database($document['id']);

					if(empty($pp_arr)){
						$html .= '<div class="buh_window add_pp" style="display:none" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Оплата (п/п)</strong><br></span>';
							$html .= $html_hidden_template;
						$html .= '</div>';
					}else{
						$html .= '<div class="buh_window add_pp" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Оплата (п/п)</strong><br></span>';
							$html .= $html_hidden_template;
							foreach ($pp_arr as $key => $pp) {
								$html .= '<div class="document_pp" data-id="'.$pp['id'].'">';
									$html .= '<span style="font-style: italic;">номер (п/п): </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" name="number" value="'.$pp['number'].'" class="number_pp">';
									$html .= '&nbsp;<span style="font-style: italic;">от</span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="дата оплаты"  name="payment_date" value="'.$pp['payment_date'].'" class="payment_date">';
									$html .= '&nbsp;<span style="font-style: italic;">в размере: </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="сумма оплаты"  name="price_from_pyment" value="'.$pp['price_from_pyment'].'" class="price_from_pyment">';
									$html .= '   <input '.$this->disabled_edit.' type="text" data-id="" placeholder="комментарии"  name="comments" value="'.$pp['comments'].'" class="comments">';
									$html .= ($this->disabled_edit=='')?'<span class="del_pp">X</span>':'';
								$html .= '</div>';
							}
						$html .= '</div>';
					}
					return $html;
				}

			//////////////////////////
			//	ПКО
			//////////////////////////
				//////////////////////////
				//	редактирование строки
				//////////////////////////
					// редактирование номера
					protected function pko_edit_number_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_PKO."` SET";
						$query .= "`number` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование даты
					protected function pko_edit_date_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_PKO."` SET";
						$query .= "`payment_date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);

						
						$query = "UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET";
						$query .= "`payment_date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						$query .= " WHERE `id` = '".$_POST['spec_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);

						echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode('Дата оплаты по счёту успешно изменена на '.$_POST['value']).'"}';
						exit;
					}
					// редактирование суммы
					protected function pko_edit_payment_summ_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_PKO."` SET";
						$query .= "`price_from_pyment` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						$this->calculate_the_pyment_price((int)$_POST['specification_id']);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование комментариев
					protected function pko_edit_comments_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_PYMENT_PKO."` SET";
						$query .= "`comments` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
				// создание ПКО
				protected function create_row_pko_AJAX(){
					global $mysqli;
					$query = "INSERT INTO `".CAB_PYMENT_PKO."` SET";
					$query .= " `specificate_id` = '".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);			

					echo '{"response":"OK","function":"show_new_row_pko","id":"'.$mysqli->insert_id.'"}';
					exit;
				}

				// удалить ПКО
				protected function delete_PKO_AJAX(){
					global $mysqli;
					$query = "DELETE FROM `".CAB_PYMENT_PKO."` WHERE `id`='".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					echo '{"response":"OK"}';
					exit;
				}


				// получаем ПКО 
				protected function get_pko_list_for_document_Database($id){
					global $mysqli;
					$query = "SELECT *,
					DATE_FORMAT(`".CAB_PYMENT_PKO."`.`payment_date`,'%d.%m.%Y %H:%i:%s')  AS `payment_date`
					 FROM `".CAB_PYMENT_PKO."` WHERE `specificate_id` = '".$id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					$pko_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$pko_arr[] = $row;
						}
					}
					return $pko_arr;
				}

				// возвращает список ПКО по спецификации
				protected function get_pko_list_for_document_Html($document){
					$html = '';
					// шаблон скрытой пустой формы для заведения 
					$html_hidden_template = '<div class="document_pko_hidden">';
						$html_hidden_template .= '<span style="font-style: italic;">номер (ПКО): </span>';
						$html_hidden_template .= '<input type="text" data-id=""  name="number" class="number_pko">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">от</span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="дата оплаты"  name="payment_date" class="payment_date">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">в размере: </span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="сумма оплаты"  name="price_from_pyment" class="price_from_pyment">';
						$html_hidden_template .= '   <input type="text" data-id="" placeholder="комментарии"  name="comments" class="comments">';
						$html_hidden_template .= '<span class="del_pko">X</span>';
					$html_hidden_template .= '</div>';

					// получаем ПП 
					$pko_arr = $this->get_pko_list_for_document_Database($document['id']);

					if(empty($pko_arr)){
						$html .= '<div class="buh_window add_pko" style="display:none" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Оплата (ПКО)</strong><br></span>';
							$html .= $html_hidden_template;
						$html .= '</div>';
					}else{
						$html .= '<div class="buh_window add_pko" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Оплата (ПКО)</strong><br></span>';
							$html .= $html_hidden_template;
							foreach ($pko_arr as $key => $pko) {
								$html .= '<div class="document_pko" data-id="'.$pko['id'].'">';
									$html .= '<span style="font-style: italic;">номер (ПКО): </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="'.$pko['specificate_id'].'"  name="number" value="'.$pko['number'].'" class="number_pko">';
									$html .= '&nbsp;<span style="font-style: italic;">от</span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="дата оплаты"  name="payment_date" value="'.$pko['payment_date'].'" class="payment_date">';
									$html .= '&nbsp;<span style="font-style: italic;">в размере: </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="сумма оплаты"  name="price_from_pyment" value="'.$pko['price_from_pyment'].'" class="price_from_pyment">';
									$html .= '   <input '.$this->disabled_edit.' type="text" data-id="" placeholder="комментарии"  name="comments" value="'.$pko['comments'].'" class="comments">';
									$html .= ($this->disabled_edit=='')?'<span class="del_pko">X</span>':"";
								$html .= '</div>';
							}
						$html .= '</div>';
					}
					return $html;
				}
			//////////////////////////
			//	TTN
			//////////////////////////
				//////////////////////////
				//	редактирование строки
				//////////////////////////
					// редактирование номера
					protected function ttn_edit_number_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_TTN."` SET";
						$query .= " `number` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование даты
					protected function ttn_edit_date_AJAX(){
						global $mysqli;
						// дата отгрузки для ttn
						$query = "UPDATE `".CAB_TTN."` SET";
						$query .= " `date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);



						//////////////////////////
						//	обновляем дату отгрузки по позициям
						//////////////////////////
							// запрашиваем строки позиций
							$query = "SELECT * FROM `".CAB_ORDER_MAIN."` WHERE `the_bill_id` = '".(int)$_POST['spec_id']."'";
							$result = $mysqli->query($query) or die($mysqli->error);
							$str_id = '';
							
							$n = 0;
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									// echo $row['id'].'  ';
									$str_id .= (($n>0)?', ':'')."'".$row['id']."'";
									$n++;
								}
							}
							// обновляем дату
							$query = "UPDATE `".CAB_ORDER_DOP_DATA."` SET";
							$query .= " `shipping_date` = '".date("Y-m-d",strtotime($_POST['value']))."'";
							$query .= " WHERE `row_id` IN (".$str_id.")";
							// echo $query;
							$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование даты возврата
					protected function ttn_edit_date_return_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_TTN."` SET";
						$query .= " `date_return` = '".date("Y-m-d",strtotime($_POST['value']))."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
					// редактирование комментариев
					protected function ttn_edit_comments_AJAX(){
						global $mysqli;
						$query = "UPDATE `".CAB_TTN."` SET";
						$query .= " `comments` = '".$_POST['value']."'";
						$query .= " WHERE `id` = '".$_POST['row_id']."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}';
						exit;
					}
				// создание ТТН
				protected function create_row_ttn_AJAX(){
					global $mysqli;
					$query = "INSERT INTO `".CAB_TTN."` SET";
					$query .= " `specificate_id` = '".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);			

					echo '{"response":"OK","function":"show_new_row_ttn","id":"'.$mysqli->insert_id.'"}';
					exit;
				}
				// удалить ТТН
				protected function delete_TTN_AJAX(){
					global $mysqli;
					$query = "DELETE FROM `".CAB_TTN."` WHERE `id`='".(int)$_POST['row_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					echo '{"response":"OK"}';
					exit;
				}
				// получаем ТТН 
				protected function get_ttn_list_for_document_Database($id){
					global $mysqli;
					$query = "SELECT *,
					DATE_FORMAT(`".CAB_TTN."`.`date`,'%d.%m.%Y %H:%i:%s')  AS `date`,
					DATE_FORMAT(`".CAB_TTN."`.`date_return`,'%d.%m.%Y %H:%i:%s')  AS `date_return`
					 FROM `".CAB_TTN."` WHERE `specificate_id` = '".$id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					$ttn_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$ttn_arr[] = $row;
						}
					}
					return $ttn_arr;
				}
				// возвращает список ТТН по спецификации/оферте
				protected function get_ttn_list_for_document_Html($document){
					$html = '';
					// шаблон скрытой пустой формы для заведения 
					$html_hidden_template = '<div class="document_ttn_hidden">';
						$html_hidden_template .= '<span style="font-style: italic;">№ ттн: </span>';
						$html_hidden_template .= '<input type="text" data-id=""  name="number" class="number_ttn">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">от</span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="дата"  name="date" class="ttn_date">';
						$html_hidden_template .= '&nbsp;<span style="font-style: italic;">возврат с подписью: </span>';
						$html_hidden_template .= '<input type="text" data-id="" placeholder="возврат с подписью"  name="price_from_pyment" class="price_from_pyment">';
						$html_hidden_template .= '   <input type="text" data-id="" placeholder="комментарии"  name="comments" class="comments">';
						$html_hidden_template .= '<span class="del_ttn">X</span>';
					$html_hidden_template .= '</div>';

					// получаем ПП 
					$ttn_arr = $this->get_ttn_list_for_document_Database($document['id']);

					if(empty($ttn_arr)){
						$html .= '<div class="buh_window add_ttn" style="display:none" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Товарно-транспортная накладная</strong><br></span>';
							$html .= $html_hidden_template;
						$html .= '</div>';
					}else{
						$html .= '<div class="buh_window add_ttn" data-specification_id="'.$document['id'].'">';
							$html .= '<span style="font-style: italic;"><strong>Товарно-транспортная накладная</strong><br></span>';
							$html .= $html_hidden_template;
							foreach ($ttn_arr as $key => $ttn) {
								$html .= '<div class="document_ttn" data-id="'.$ttn['id'].'">';
									$html .= '<span style="font-style: italic;">№ ттн: </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="'.$ttn['specificate_id'].'"  name="number" value="'.$ttn['number'].'" class="number_ttn">';
									$html .= '&nbsp;<span style="font-style: italic;">от</span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="дата"  name="date" value="'.$ttn['date'].'" class="ttn_date">';
									$html .= '&nbsp;<span style="font-style: italic;">возврат с подписью: </span>';
									$html .= '<input '.$this->disabled_edit.' type="text" data-id="" placeholder="дата"  name="date_return" value="'.$ttn['date_return'].'" class="date_return">';
									$html .= '   <input '.$this->disabled_edit.' type="text" data-id="" placeholder="комментарии"  name="comments" value="'.$ttn['comments'].'" class="comments">';
									$html .= ($this->disabled_edit=='')?'<span class="del_ttn">X</span>':'';
								$html .= '</div>';
							}
						$html .= '</div>';
					}
					return $html;
				}
			
			
			// окно бух учета по заказу

			protected function get_buh_uchet_for_order_AJAX(){
				//получаем информацию по спецификации
				$document_arr = $this->get_info_for_spec_for_order_num($_POST['order_num']);
				$this->get_buh_uchet($document_arr);
			}

			// окно бух учета для спецификации
			protected function get_buh_uchet_for_spec_AJAX(){
				//получаем информацию по спецификации
				$document_arr = $this->get_info_for_document($_POST['spec_id']);
				$this->get_buh_uchet($document_arr);
			}

			// получаем реквизиты клиента по ID
			protected function get_requisits($id){
				global  $mysqli;				

				$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `id`  = '".$id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$requsit_arr = array();
					
					// echo $query;
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$requsit_arr = $row;
					}
				}
				return $requsit_arr;
			}

			// получаем наши реквизиты по ID
			protected function get_requisits_our($id){
				global  $mysqli;				

				$query = "SELECT * FROM `".OUR_FIRMS_TBL."` WHERE `id`  = '".$id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$requsit_arr = array();
					
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$requsit_arr = $row;
					}
				}
				return $requsit_arr;
			}


			protected function get_buh_uchet($document_arr){
				$html_head = '<ul>';
				$gen_info = '';
				$html = '';
						
				
				
				foreach ($document_arr as $key => $document) {
					// формируем id блока
					$id = 'tabs_'.($key);
					// общая информация для всех спецификаций
					if($gen_info==""){
						$gen_info .= '<div class="general_info_for_order">';
							include_once './libs/php/classes/manager_class.php';					
							$gen_info .= '&nbsp;<span><span style="font-style: italic;">Менеджер:</span> '.Manager::get_snab_name_for_query_String($document['manager_id']).'</span>';
							$gen_info .= '&nbsp;<span><span style="font-style: italic;">Компания:</span> '.$this->get_client_name_simple_Database($document['client_id'],1).'</span>';
						$gen_info .= '</div>';
					}

					// запрет на редактирование полей для не админа и не буха
					$this->disabled_edit = ($this->user_access !=1 && $this->user_access !=2)?' disabled':'';

					// фильтрация по типу документа
					switch ($document['doc_type']) {
						case 'oferta': // шаблон по оферте
							$html_head .= '<li class="check_the_anather_spec '.($key==0?' checked':'').'" data-id="'.$id.'"><strong>Счёт оферта</strong> '.$this->get_document_link($document,$document['client_id'],$document['create_time']).'</li>';
							
							// запррос информации по договору
							$offerta_arr = $this->get_info_for_offerta_Database($document['doc_id']);
							
							// номер заказа, менеджер, компания
							$html .= '<div class="spec_div" id="'.$id.'"'.($key>0?' style="display:none"':'').'>';
								// договор
								$html .= '<div class="buh_window" data-specification_id="'.$document['id'].'">';
									// получаем реквизиты клиента выбранные при формировании оферты
									$requsit_arr = $this->get_requisits($offerta_arr['client_requisit_id']);
									$html .= '<span><span style="font-style: italic;">Юр/л клиента:</span> '.$requsit_arr['comp_full_name'].'</span><br>';

									// получаем наши реквизиты выбранные при формировании оферты
									$requsit_our_arr = $this->get_requisits_our($offerta_arr['our_requisit_id']);
									$html .= '<span><span style="font-style: italic;">Юр/л АПЛ:</span> '.$requsit_our_arr['comp_full_name'].'</span><br>';

									// $html .= '<span><span style="font-style: italic;">по договору:</span> '.$this->get_agreement_link($document,$document['client_id'],$document['create_time']).'</span>';
									$html .= '&nbsp;<span><span style="font-style: italic;">подписана</span>  <input '.$this->disabled_edit.' data-id="'.$document['id'].'" type="text" value="'.$document['date_specification_signed'].'" class="date_specification_signed"></span>';
									$html .= '&nbsp;<span><span style="font-style: italic;">возвращена с подписью </span> <input '.$this->disabled_edit.' data-id="'.$document['id'].'" type="text" value="'.$document['date_return_width_specification_signed'].'" class="date_return_width_specification_signed"></span>'; 
								$html .= '</div>';

								// счёт
								$html .= '<div class="buh_window" data-specification_id="'.$document['id'].'">';
									$html .= '<strong>Счёт-оферта №:</strong>';
									// $html .= '<span style="font-style: italic;">номер:</span>';
									$html .= '&nbsp;<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" data-doc_id="'.$document['doc_id'].'" name="number" class="number_the_bill_oferta" value="'.$document['doc_num'].'">';
										
									$html .= '<span style="font-style: italic;"> от:</span>';
									$html .= '<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" data-doc_id="'.$document['doc_id'].'" name="date_create" class="date_create_the_bill_oferta" value="'.$document['date_create_the_bill'].'">';

									$html .= '<span style="font-style: italic;"> на сумму:</span>';
									$html .= '<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" name="for_price" class="for_price_the_bill" value="'.$document['spec_price'].'"> р.';

									// кнопка счёт выставлен
									$html .= '<button class="the_bill_is_ready" '.$this->disabled_edit.' data-id="'.$document['id'].'">Счёт выставлен</button>';
								$html .= '</div>';
							break;
						
						default: // шаблон по спецификации и договору
							$html_head .= '<li class="check_the_anather_spec '.($key==0?' checked':'').'" data-id="'.$id.'"><strong>Спецификация</strong> '.$this->get_document_link($document,$document['client_id'],$document['create_time']).'</li>';
							

							// запррос информации по договору
							$agreement_arr = $this->get_info_for_agreement_Database($document['doc_id']);
							
							// номер заказа, менеджер, компания
							$html .= '<div class="spec_div" id="'.$id.'"'.($key>0?' style="display:none"':'').'>';
								// договор
								$html .= '<div class="buh_window" data-specification_id="'.$document['id'].'">';
									// получаем реквизиты клиента выбранные при формировании оферты
									$requsit_arr = $this->get_requisits($agreement_arr['client_requisit_id']);
									$html .= '<span><span style="font-style: italic;">Юр/л клиента:</span> '.(isset($requsit_arr['comp_full_name'])?$requsit_arr['comp_full_name']:'реквизиты не найдены').'</span><br>';
									// $html .= '$agreement_arr = '.$this->print_arr($agreement_arr);
									// $html .= '$requsit_arr = '.$this->print_arr($requsit_arr);
									// $html .= '$document = '.$this->print_arr($document);
									// получаем наши реквизиты выбранные при формировании оферты
									$requsit_our_arr = $this->get_requisits_our($agreement_arr['our_requisit_id']);
									$html .= '<span><span style="font-style: italic;">Юр/л АПЛ:</span> '.$requsit_our_arr['comp_full_name'].'</span><br>';
									// $html .= '<span><span style="font-style: italic;">Юр. Лицо клиента:</span> '.$agreement_arr['client_comp_full_name'].'</span><br>';
									// $html .= '<span><span style="font-style: italic;">Юр/л АПЛ:</span> '.$agreement_arr['our_comp_full_name'].'</span><br>';
									$html .= '<span><span style="font-style: italic;">по договору:</span> '.$this->get_agreement_link($document,$document['client_id'],$document['create_time']).'</span>';
									$html .= '&nbsp;<span><span style="font-style: italic;">подписана</span>  <input '.$this->disabled_edit.' data-id="'.$document['id'].'" type="text" value="'.$document['date_specification_signed'].'" class="date_specification_signed"></span>';
									$html .= '&nbsp;<span><span style="font-style: italic;">возвращена с подписью </span> <input '.$this->disabled_edit.' data-id="'.$document['id'].'" type="text"  value="'.$document['date_return_width_specification_signed'].'" class="date_return_width_specification_signed"></span>'; 
								$html .= '</div>';

								// счёт
								// $html .= '<div class="buh_window" data-specification_id="'.$document['id'].'">';
								// 	$html .= '<div><strong>Счёт </strong>
								// 			<span style="font-style: italic;">тип:</span><input type="text" value="'.(isset($this->type_the_bill[$document['type_the_bill']][0])?$this->type_the_bill[$document['type_the_bill']][0]:'не указан').'" disabled>
								// 			<span style="font-style: italic;">юр/л:</span>'.$agreement_arr['our_comp_full_name'].'
								// 		</div>';
								// $html .= '</div>';
								// если указан тип счёта, значит счёт был заказан и мы выводим поля для ввода информации по счёту
								$html .= '<div class="buh_window" data-specification_id="'.$document['id'].'">';
									$html .= '<span style="font-style: italic;"><strong>Счёт №:</strong></span>';
									$html .= '&nbsp;<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" name="number" class="number_the_bill" value="'.$document['number_the_bill'].'">';
										
									$html .= '<span style="font-style: italic;"> от:</span>';
									$html .= '<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" name="date_create" class="date_create_the_bill" value="'.$document['date_create_the_bill'].'">';

									$html .= '<span style="font-style: italic;"> на сумму:</span>';
									$html .= '<input type="text" '.$this->disabled_edit.' data-id="'.$document['id'].'" name="for_price" class="for_price_the_bill" value="'.$document['spec_price'].'"> р.';

									// кнопка счёт выставлен
									$html .= '<button class="the_bill_is_ready" data-id="'.$document['id'].'">Счёт выставлен</button>';
								$html .= '</div>';
								
							break;
					}
					
					// ПП
					$html .= $this->get_pp_list_for_document_Html($document);
					
					// ПКО
					$html .= $this->get_pko_list_for_document_Html($document);
						
					// ТТН						
					$html .= $this->get_ttn_list_for_document_Html($document);

					// кнопки
					$html .= '<div class="buh_window" data-specification_id="'.$document['id'].'" '.(($this->disabled_edit == "")?'':'style="display:none"').'>';
						$html .= '<button сlass="button_to_add_lines" id="add_pp">Добавить П/П</button>';
						$html .= '<button сlass="button_to_add_lines" id="add_pko">Добавить ПКО</button>';
						$html .= '<button сlass="button_to_add_lines" id="add_ttn">Добавить ТТН</button>';
					$html .= '</div>';
					// комментарии от менеджеров
					if($document['comments'] != ''){
						$html .= $document['comments'];					
					}	
					$html .= '</div>';

									
				}
				$html_head .= '</ul>';

				$html = '<div id="tabs">'.$gen_info.$html_head.$html.'</div>';

				// $html = 'Ghbdtn vbh';
				echo '{"response":"show_new_window_simple","function":"windows_on","html":"'.base64_encode($html).'","title":"Бух. учёт"}';
				// echo '{"response":"show_new_window","title":"Комментарии для Бухгалтерии:" "html":"'..'"}';
			}


			// окно бух учёта
			protected function get_window_buh_uchet(){
				$html = '';
				// строка заказ, менеджер, Компания
				$this->Order = $this->get_one_order_row_Database((int)$_POST['order_id']);

				// получаем массив спецификаций счетов
				$this->Specification_arr = $this->get_specification_arr((int)$_POST['order_id']); 

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

				// номер заказа, менеджер, компания
				$html .= '<div class="buh_window general_info_for_order">';
					$html .= '<span><strong>Заказ: </strong>'.$this->order_num_for_User.'</span>';
					include_once './libs/php/classes/manager_class.php';
					
					$html .= '&nbsp;<span>Менеджер: '.Manager::get_snab_name_for_query_String($this->Order['manager_id']).'</span>';
					$html .= '&nbsp;<span>Компания: '.$this->get_client_name_simple_Database($this->Order['client_id'],1).'</span>';
					$html .= '<button type="button" id="replace_the_dialog_window" data-id="'.$this->Order['id'].'">Обновить</button>';
				$html .= '</div>';


				// вывод всех спецификаций прикреплённых к заказу
				foreach ($this->Specification_arr as $key => $this->Specification) {

					$html .= '<div class="buh_window" data-specification_id="'.$this->Specification['id'].'">';
						// 
						$html .= '<table>';
						switch ($this->Specification['type_the_bill']) {
							case 'the_bill_offer': // счёт оферта
								$html .= '<tr>';
									$html .= '<td  class="check_the_specification">';
										$html .= '<span><input type="checkbox" name="" ></span>';	
									$html .= '</td>';
									$html .= '<td>';
										// счёт оферта не имеет спецификации и договора, поэтому выводим только строку счёта
										$html .= '<div id="container_from_the_bill">';
											$html .= $this->get_the_bill_for_order_Html($this->Specification);
										$html .= '</div>';
									$html .= '</td>';
								$html .= '</tr>';
								break;
							
							default:// остальные
								$html .= '<tr>';
									$html .= '<td  class="check_the_specification">';
										$html .= '<span><input type="checkbox" name="" ></span>';	
									$html .= '</td>';
									$html .= '<td>';
										// для всех строк кроме счёта=оферты выводим строку с указанием спецификации
										$html .= '<span><strong>Спецификация и счёт </strong></span><br>';
										$html .= '<span>Спецификация: '.$this->get_document_link($this->Specification,$this->Order['client_id'],$this->Order['create_time']).'</span>';
										$html .= '<span>для договора: '.$this->get_agreement_link($this->Specification).'</span>';
										$html .= '&nbsp;<span>подписана: <input data-id="'.$this->Specification['id'].'" type="text" value="'.$this->Specification['date_specification_signed'].'" class="date_specification_signed"></span>';
										$html .= '&nbsp;<span>возвращена с подписью: <input data-id="'.$this->Specification['id'].'" type="text" value="'.$this->Specification['date_return_width_specification_signed'].'" class="date_return_width_specification_signed"></span>';
									$html .= '</td>';
									$html .= '<td>';
										// счета
										$html .= '<div id="container_from_the_bill">';
											$html .= $this->get_the_bill_for_order_Html($this->Specification);
										$html .= '</div>';	
									$html .= '</td>';
								$html .= '</tr>';
								break;
						}
						$html .= '</table>';
					$html .= '</div>';
				}

				// кнопки управления
				$html .= '<div id="buch_window_optional_buttons">';
					$html .= '<button>Заказать счёт</button>';
					$html .= '<button>Выставить счёт на всё</button>';
					$html .= '<button>Приходник на залог</button>';
				$html .= '</div>';
				

				// // оплата
				// $html .= '<div class="buh_window">';
				// 	$html .= '<span><strong>Оплата</strong></span><br>';
				// 	$html .= '<div id="container_from_the_payment">';
				// 		$html .= $this->get_the_payment_order_for_bill_Html($this->Order);
				// 		$html .= '<div id="add_the_payment_order_link" data-id="'.$this->Order['id'].'">';
				// 			$html.= '<span>добавить строчку платежа</span>';
				// 		$html .= '</div>';	
				// 	$html .= '</div>';				
				// $html .= '</div>';

				$html .= $this->print_arr($this->Order);				
				$html .= $this->print_arr($_POST);

				//////////////////////////
				//	выводим скрытый POST для обновления окна
				//////////////////////////
					$html .= '<form>';
					// перебираем остальные значения для передачи их далее
						foreach ($_POST as $key => $value) {
							$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
						}
					$html .= '</form>';
				//////////////////////////
				//	выводим скрытый POST для обновления окна
				//////////////////////////
				return $html;
			}

			// получаем html платёжных поручений
			protected function get_the_payment_order_for_bill_Html($order){
				// получаем массив полатежных поручений
				$payment_order_arr = $this->get_the_payment_order_for_bill_Database($order['id']);
				if(empty($payment_order_arr)){return '<table></table>';}

				$html = $this->print_arr($payment_order_arr);				
				$html .= 'method - get_the_payment_order_for_bill_Html - OK';
				$html .= '<table>';
				foreach ($payment_order_arr as $key => $payment) {
					$html .= '<tr>';
						// номер п/п
						$html .= '<td>';
							$html .= '<span>№ п/п: </span>';
							$html .= '<input type="text" placeholder="введите номер п/п" value="'.$payment['number'].'" >';
						$html .= '</td>';

						// по счёту
						$html .= '<td>';
							$html .= '<span>по счёту: </span>';
							// создаем селект по счетам (если его ещё нет)
							$this->get_Html_select_from_the_bill($payment);
							// вывод селекта по счетам
							$html .= $this->Html_select_from_the_bill;
						$html .= '</td>';
						// дата
						$html .= '<td>';
							$html .= '<span>дата: </span>';
							$html .= '<input type="text"  class="date_payment_order" placeholder="введите дату оплаты" value="'.$payment['payment_date'].'" >';
						$html .= '</td>';

						// сумма оплаты
						$html .= '<td>';
							$html .= '<span>в размере:</span>';
							$html .= '<input type="text" class="date_payment_order" placeholder="введите дату оплаты" value="'.$payment['price_from_pyment'].'" >';
						$html .= '</td>';


						$html .= '<td>';
							$html .= '<span class="'.(($payment['comments']=="")?'tz_text_new':'buch_comments').'"  data-id="'.$payment['id'].'"></span>';
						$html .= '</td>';
						$html .= '<td>';
							$html .= '<span class="button usl_del" data-id="'.$payment['id'].'">X</span>';
						$html .='</td>';

					$html .= '</tr>';
				}




				$html .= '</table>';
				return $html;
			}
			//вывод выпадающего списка по счетам
			protected function get_Html_select_from_the_bill($payment){
				if(!isset($this->Html_select_from_the_bill)){
					$html = '';
					$html .= '<select data-id="'.$payment['id'].'">';
					$html .= '<option value="">счёт не прикреплён...</optiopn>';	
					foreach ($this->the_bill_arr as $key => $the_bill) {
						$selected = ($payment['bill_id'] == $the_bill['id'])?' selected="selected"':'';
						if($the_bill['deleted'] != 1){
							$html .= '<option value="'.$the_bill['id'].'" '.$selected.'>'.$this->type_the_bill[$the_bill['type_the_bill']][0].' '.$the_bill['number'].'</optiopn>';	
						}else{
							$html .= ($selected!='')?'<option value="'.$the_bill['id'].'" '.$selected.' class="deleted_bill_option">(счёт удалён)'.$this->type_the_bill[$the_bill['type_the_bill']][0].' '.$the_bill['number'].'</optiopn>':'';	
						}						
					}
					$html .= '</select>';
					$this->Html_select_from_the_bill = $html;	
				}				
			}


			// запрашиваем из базы платёжные порчения
			protected function get_the_payment_order_for_bill_Database($order_id){
				global $mysqli;
				$query = "SELECT 
				`".CAB_PYMENT_ORDER."`.*, 
				DATE_FORMAT(`".CAB_PYMENT_ORDER."`.`payment_date`,'%d.%m.%Y %H:%i:%s')  AS `payment_date`
				FROM `".CAB_PYMENT_ORDER."`";
				$query .= " WHERE `".CAB_PYMENT_ORDER."`.`order_id` = '".$order_id."'";

				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$payment_order_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$payment_order_arr[] = $row;
					}
				}
				return $payment_order_arr;
			}





			// запрос информации по заказу
			protected function get_one_order_row_Database($order_id){
				global $mysqli;
				$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".CAB_ORDER_ROWS."`";
				$query .= " WHERE `".CAB_ORDER_ROWS."`.`id` = '".$order_id."'";

				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$Order_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Order_arr = $row;
					}
				}
				return $Order_arr;
			}


			// заказ нового счёта 
			protected function order_a_new_account_AJAX(){
				// $html = $this->print_arr($_POST);
				global $mysqli;
				// поправка по старым скриптам
				if(isset($_POST['specificate_row_id'])){
					$id = $_POST['specificate_row_id'];
				}else if (isset($_POST['order_id'])) {
					$id = $_POST['order_id'];
				}

				$status_buch = isset($_POST['status_buch'])?$_POST['status_buch']:'request_expense';
				$query = "UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET";

				$query .= "`type_the_bill` = '".$_POST['type_the_bill']."',";
				$query .= "`buch_status` = '".$status_buch."',";//date_order_the_bill
				$query .= "`date_order_the_bill` = NOW()";//
				$query .= " WHERE `id` = '".$id."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK","function":"reload_order_tbl"}';
				exit;
			}

			// запрос из кнопки выставить счёт
			protected function get_listing_type_the_bill_AJAX(){
				if(isset($_POST['status_buch']) && isset($_POST['order_id'])){
					// если это статус содержащийся в коммандах для бухгалтера от менеджеров
					// просим ввести комментарии
					if(isset($this->commands_men_for_buch[$_POST['status_buch']]) && (!isset($_POST['comment_text']) || (isset($_POST['comment_text']) && trim($_POST['comment_text']) == '') )){
						$html = '';
						$html .= '<form>';
						// перебираем остальные значения для передачи их далее
						foreach ($_POST as $key => $value) {
							$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
						}

						//////////////////////////////
						//	форма комментария для БУХ
						//////////////////////////////
						if(isset($_POST['comment_text']) && trim($_POST['comment_text']) == ''){
							$html .= $this->wrap_text_in_warning_message_post('Пожалуйста, чиркните ну хать что-нибудь =((((');
						}

						$html .= '<div class="comment table">';
							$html .= '<div class="row">';
								$html .= '<div class="cell comment_text">';
										$html .= '<textarea name="comment_text"></textarea>';
										$html .= '<div class="div_for_button">';
											// $html .= '<button class="add_nah">Нах</button>';
											// $html .= '<button class="add_nah">Нах?</button>';
											$html .= '<button class="add_nah">Без комментария</button>';
											// $html .= '<button id="add_new_comment_button">Отправить</button>';
										$html .= '</div>';
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</div>';

						$html .= '</form>';
						echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Комментарии для Бухгалтерии:","width":"600"}';
					}else{
						$this->buch_status_select($_POST['status_buch'],$_POST['order_id']);
						echo '{"response":"OK","function":"reload_order_tbl"}';return;
					}
				}else{
					$message = "Что-то пошло не так в методе: get_listing_type_the_bill_AJAX()";
						echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
						// exit;
				}


				exit;
				// $html = '';
				// $html .= '<form>';
				// $html .= '<ul id="get_listing_type_the_bill" class="check_one_li_tag">';
				// $n = 0;
				// $first_val = '';
				// foreach ($this->type_the_bill as $name_en => $name_ru) {
				// 	$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru[0].'</li>';
				// 	if($n==0){$first_val = $name_en;}
				// 	$n++;
				// }
				// $html .= '<input type="hidden" name="type_the_bill" value="'.$first_val.'">';	
				// $html .= '<input type="hidden" name="AJAX" value="order_a_new_account">';	
				// $html .= '</ul>';
				// // если информации о статусе бух не пришло
				// if(!isset($_POST['status_buch'])){$html .= '<input type="hidden" name="status_buch" value="request_expense">';}
				// // удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				// unset($_POST['AJAX']);
				// // перебираем остальные значения для передачи их далее
				// foreach ($_POST as $key => $value) {
				// 	$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				// }
				// $html .= '</form>';

				// echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите тип счёта:","width":"230"}';
			}

			// вывод меню комманд по спецификации 
			protected function get_commands_men_for_buch_AJAX(){
				if($this->user_access != 1 and $this->user_access != 2 and $this->user_access != 5){
					$message = "У вас не достаточно прав для изменения статуса по документам.";
					echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
					exit;
				}

				$html = '';
				$n = 0;
				$html .= '<ul id="get_commands_men_for_buch" class="check_one_li_tag">';
				$first_val = '';
				switch ($this->user_access) {
					case '1':
						foreach ($this->buch_status as $name_en => $name_ru) {
							$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
							if($n==0){$first_val = $name_en;}
							$n++;
						}
						foreach ($this->buch_status_service as $name_en => $name_ru) {
							$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
							if($n==0){$first_val = $name_en;}
							$n++;
						}
						foreach ($this->commands_men_for_buch as $name_en => $name_ru) {
							$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
							if($n==0){$first_val = $name_en;}
							$n++;
						}
						break;

					case '2':
						foreach ($this->buch_status as $name_en => $name_ru) {
							$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
							if($n==0){$first_val = $name_en;}
							$n++;
						}
						break;	

					default:
						foreach ($this->commands_men_for_buch as $name_en => $name_ru) {
							$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
							if($n==0){$first_val = $name_en;}
							$n++;
						}
						break;
				}
				

				$html .= '</ul>';


				$html .= '<form>';

				$html .= '<input type="hidden" name="status_buch" value="'.$first_val.'">';	
				$html .= '<input type="hidden" name="AJAX" value="get_listing_type_the_bill">';	

				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}

				$html .= '</form>';

				echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите действие:","height":"450"}';
				// echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				// echo 'base';
				exit;
			}	

			// вывод меню комманд по запросу
			protected function get_command_for_change_status_query_AJAX(){
				
				// global $mysqli;
				// // запрос информации по заказу
				// $query = "SELECT * FROM `".RT_LIST."` WHERE `id` = '".(int)$_POST['row_id']."';";
				// $this->Query = array();
				// // echo $query;
				// $result = $mysqli->query($query) or die($mysqli->error);
				// if($result->num_rows > 0){
				// 	while($row = $result->fetch_assoc()){
						$this->Query = $this->get_query((int)$_POST['row_id']);
				// 	}
				// }
				unset($_POST['AJAX']);
				$Query_menu = new CabinetMainMenu($this->Query, array('access'=>$this->user_access,'id'=>$this->user_id));
				
				return $Query_menu;
				
				echo 'asd';
				exit;

			}

			// возврат запроса в отдел прадаж (ожидают распределения)
			protected function command_not_process_admin_AJAX(){
				$query = "UPDATE  `".RT_LIST."` SET ";
				$query .= " `manager_id` = '24'";
				// 2) меняем статус на "на рассмотрении" - taken_into_operation
				$query .= ", `status` = 'not_process'";
				
				$query .= ", `dop_managers_id` = ''";
				
				$query .= " WHERE `id` = '".(int)$_POST['row_id']."'";
				global $mysqli;

				$result = $mysqli->query($query) or die($mysqli->error);
				$Query = $this->get_query((int)$_POST['row_id']);
				// echo '<pre>';
				// print_r($Query);
				// echo '</pre>';
				$option['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section=requests&subsection=query_wait_the_process';
				$this->responseClass->addResponseFunction('location_href',$option);
			}

			// берём на рассмотрение (скрываем запрос от других)
			function command_taken_into_operation_AJAX(){
				// получаем данные по запросу
				$Query = $this->get_query((int)$_POST['row_id']);

				// если прикреплено несколько менеджеров на обработку запроса
				// получа информацию в массив
				$dop_managers_id =  trim($Query['dop_managers_id']);
				$managers_arr = array();
				if(trim($Query['dop_managers_id']) != ""){
					$managers_arr = explode(",", $dop_managers_id);
				}

				// проверяем права на данную комманду
				if($Query['manager_id'] != $this->user_id && !in_array($this->user_id,$managers_arr) && $this->user_access != 1){
					$message = 'ОЙ, что-то пошло не так!!! К сожалению у Вас не достаточно прав для данной комманды.';
					$this->responseClass->addResponseFunction('reload_order_tbl',array('timeout'=>'5000'));
					$this->responseClass->addMessage($message,'error');	
					return;
				}

				$query = "UPDATE  `".RT_LIST."` SET ";
				// 1) устанавливаем себя на место менеджера
				if($this->user_access != 1){
					$query .= " `manager_id` = '".$this->user_id."',";	
				}
				
				// 2) меняем статус на "на рассмотрении" - taken_into_operation
				$query .= " `status` = 'taken_into_operation'";
				// 3) перезаписываем список.... менеджеров.... если их несколько
				if(in_array($this->user_id,$managers_arr)){
					unset($managers_arr[array_search($this->user_id, $managers_arr)]);
				}
				$query .= ", `dop_managers_id` = '".implode(',',$managers_arr)."'";
				
				$query .= " WHERE `id` = '".(int)$_POST['row_id']."'";
				global $mysqli;
				$result = $mysqli->query($query) or die($mysqli->error);

				$message = 'Запрос взят на рассмотрение.';
				$this->responseClass->addMessage($message,'successful_message');	
				$this->responseClass->addResponseFunction('reload_order_tbl',array('timeout'=>'0'));
			}

			/**
			  *	возвращает массив email 
			  *
			  *	@param 		$user_id_arr
			  *	@return  	
			  *	@author  	Alexey Kapitonov
			  *	@version 	
			  */
			public function get_users_email($user_id_arr){
				global $mysqli;
				$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id in ('".implode("','",$user_id_arr)."')";
				$email = array();
				$result = $mysqli->query($query) or die($mysqli->error);

				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						if(trim($row['email'] != '')){
							$email[] = $row['email'];
						}else if(trim($row['email_2']) != ''){
							$email[] = $row['email_2'];
						}
					}
				}
				return $email;
			}	


			/**
			  *	отказ от запроса
			  *
			  *	@author  	Alexey Kapitonov
			  *	@version 	23:58 02.02.2016
			  */
			function command_refused_AJAX(){
				// проверяем есть ли объяснение причины
				if(!isset($_POST['comment']) || (isset($_POST['comment']) && trim($_POST['comment']) == "")){
					$html = '';
					$html .= '<form>';
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}
					if(isset($_POST['comment']) && trim($_POST['comment']) == ""){
						$message = "Причину необходимо указать ОБЯЗАТЕЛЬНО!!!!";
						$this->responseClass->addMessage($message,'error_message');
					}
					$html .= '<textarea name="comment" style="width:100%">'.(isset($_POST['comment'])?$_POST['comment']:'').'</textarea>';
					$html .= '</form>';
					$this->responseClass->addPostWindow($html,'укажите причину',array('width'=>"800"));
					return;
				}

				// получаем данные по запросу
				$Query = $this->get_query((int)$_POST['row_id']);

				$dop_managers_id =  trim($Query['dop_managers_id']);
				$managers_arr = array();
				if(trim($Query['dop_managers_id']) != ""){
					$managers_arr = explode(",", $dop_managers_id);
				}

				// проверяем права на данную комманду
				if($Query['manager_id'] != $this->user_id && !in_array($this->user_id, $managers_arr) && $this->user_access != 1){
					$message = 'ОЙ, что-то пошло не так!!! К сожалению у Вас не достаточно прав для данной комманды.';
					$this->responseClass->addResponseFunction('reload_order_tbl',array('timeout'=>'5000'));
					$this->responseClass->addMessage($message,'error');	
					return;
				}

				$query = "UPDATE  `".RT_LIST."` SET ";
				//if($this->user_id == $Query['manager_id']){
					$query .= " `manager_id` = '0'";	
				//}

				// 3) перезаписываем список.... менеджеров.... если их несколько
				if(in_array($this->user_id,$managers_arr)){
					unset($managers_arr[array_search($this->user_id, $managers_arr)]);
				}
				$query .= ", `dop_managers_id` = '".implode(',',$managers_arr)."'";
				// если прикреплённых менеджеров более одного 
				$query .= ", `status` = 'not_process'";
				

				global $mysqli;

				$query .= " WHERE `id` = '".(int)$_POST['row_id']."'";
				$result = $mysqli->query($query) or die($mysqli->error);


				$name_arr = $this->get_manager_name_Database_Array($this->user_id);
				$Manager_name = $name_arr['name'].' '.$name_arr['last_name'];

				


				$message = 'вы отказались от запроса.';
				$this->responseClass->addMessage($message,'successful_message');	
				$this->responseClass->addResponseFunction('reload_order_tbl',array('timeout'=>'1000'));

				// include_once($_SERVER['DOCUMENT_ROOT'].'/libs/php/classes/mail_class.php');

				$admin_email_arr = $this->get_users_email(array(4,6,42));

				switch (count($managers_arr)) {
						case 0:
							// оповещение админа !!!!!!!!!
							$subject = 'Отказ от запроса';
							$mail_message = "Менеджер $Manager_name отказаля от <a href=\"http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num=".$Query['query_num']."\">запроса № ".$Query['query_num']."</a><br>";
							$mail_message .= "Причина:<br>";
							$mail_message .= $_POST['comment'];
							

						    
						    
						    $mailClass = new Mail();
							foreach ($admin_email_arr as $key => $email) {
								$mailClass->send($email,'os@apelburg.ru',$subject,$mail_message);
							}
							
							
							break;

						case 1:

							$mail_message = "";
							// оповещение остальных !!!!!!!!!
							$managers_email_arr = $this->get_users_email($managers_arr);
							$subject = 'Отказ от запроса';
							$mail_message .= 'Вам доступен новый запрос.';
							$mail_message .= "Менеджер $Manager_name отказаля от <a href=\"http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num=".$Query['query_num']."\">запроса № ".$Query['query_num']."</a><br>";
							$mail_message .= "Причина:<br>";
							$mail_message .= $_POST['comment'];
						    
						    $mailClass = new Mail();
							
							foreach ($admin_email_arr as $key => $email) {
								$mailClass->send($email,'os@apelburg.ru',$subject,$mail_message);
							}
							break;
						
						default:
							$mail_message = "";
							// оповещение остальных !!!!!!!!!
							$managers_email_arr = $this->get_users_email($managers_arr);
							$subject = 'Отказ от запроса';
							$mail_message .= 'Вам доступен новый запрос.';
							$mail_message .= "Менеджер $Manager_name отказаля от <a href=\"http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num=".$Query['query_num']."\">запроса № ".$Query['query_num']."</a><br>";
							$mail_message .= "Причина:<br>";
							$mail_message .= $_POST['comment'];
						    
						    $mailClass = new Mail();
																					
							foreach ($admin_email_arr as $key => $email) {
								$mailClass->send($email,'os@apelburg.ru',$subject,$mail_message);
							}
							break;
							break;
					}

				$manager_refused_comment = "Отказался от запроса<br>";
				$manager_refused_comment .= "Причина:<br>".$_POST['comment'];
				$this->save_query_comment($this->user_id, $Query['query_num'], $Manager_name, $manager_refused_comment);

				global $mysqli;
				
			}

			// сохранение комментария
			protected function save_query_comment($user_id, $query_num, $name, $text){
				global $mysqli;
				$query ="INSERT INTO `".RT_LIST_COMMENTS."` SET
			             `user_id` = '".$user_id."',
			             `query_num` = '".$query_num."',
			             `user_name` = '".$name."',
			             `comment_text` = '".$text."',
			            `create_time` = NOW()";
				$result = $mysqli->query($query) or die($mysqli->error);	

				return  $mysqli->insert_id;
			}

			/**
			  *	проверка запроса на принадлежность кому-либо
			  * проще говоря взял его кто-то обработать или ещё нет
			  *
			  *	@return  	user_id хозяина
			  *	@author  	Alexey Kapitonov
			  *	@version 	23:32 14.01.2016
			  */
			protected function check_the_empty_query_old(){
				global $mysqli;
				// echo $query;
				$query = "SELECT * FROM `".RT_LIST."` WHERE `id` = '".(int)$_POST['row_id']."';";
				$Query = array();
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Query = $row;
					}
				}
				if(!isset($Query['manager_id'])){
					return 0;
				}
				return $Query['manager_id'];
			}

			/**
			  *	проверка запроса на принадлежность кому-либо
			  * проще говоря взял его кто-то обработать или ещё нет
			  *
			  * @param 		$Query - выборка запроса из базы
			  *	@return  	user_id хозяина
			  *	@author  	Alexey Kapitonov
			  *	@version 	23:32 14.01.2016
			  */
			protected function check_the_empty_query($Query){
				if(!isset($Query['manager_id'])){
					return 0;
				}
				return $Query['manager_id'];
			}

			/**
			  *	получаем запрос по его id
			  *
			  *	@author  	Alexey Kapitonov
			  *	@version 	23:32 14.01.2016
			  */
			public function get_query($id){
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

			// возвращает строку поиска по клиентам
			protected function get_client_sherch_form_AJAX(){
				$html = '';
				if(isset($_POST['AJAX'])){unset($_POST['AJAX']);}
				// if(isset($_POST['client_id'])){unset($_POST['client_id']);}

				// $html .= '<form id="js--window_client_sherch_form">';
				// foreach ($_POST as $key => $value) {
				// 	$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				// }
				$html .= '<div class="quick_bar_tbl"><div class="search_div">
                    <div class="search_cap">Поиск:</div>
                    <div class="search_field">                    
                        <input id="client_name_search" name="client_name_search" placeholder="поиск по клиентам" type="text" onclick="" value=""><div class="undo_btn"><a href="#" onclick="$(this).parent().prev().val(\'\');return false;">×</a></div></div>
                    <div class="search_button" onclick="search_and_show_client_list();">&nbsp;</div>
                    <div class="clear_div"></div>
                </div></div>';
				// $html .= '<input type="text" name="client_name_search">';
				$html .= '<input type="hidden" name="AJAX" value="get_a_list_of_clients_to_be_attached_to_the_request">';
				$html .= '<input type="hidden" name="client_id" value="'.$_POST['client_id'].'">';
				// $html .= '</form>';

				$options = array(
					"height"=>"300",
					"width"=>"1000",
					"form_id"=>"js--window_client_sherch_form"
					);
				$this->responseClass->addPostWindow($html,"Поиск клиента",$options);
				// echo '{"response":"show_new_window",
				// "html":"'.base64_encode($html).'",
				// "title":"Поиск клиента","height":"300","width":"1000"}';
				// exit;
			}

			/**
			  *	присваиваем запросу новый статус
			  *
			  *	@author  	Alexey Kapitonov
			  *	@version	23:30 14.01.2016 	
			  */
			public function command_for_change_status_query_AJAX(){
				// если статус меняет не админ
				$query = $this->get_query((int)$_POST['row_id']);

				

				if( $this->user_access != 1 ){
					// если клиент не назначен
					if($query['client_id'] == 0){
						$message = "Прикрепите клиента.";
						// $message = 'Для выбранного клиента доступны следующие кураторы:';
						$this->responseClass->addMessage($message,'system_message');
						$this->get_client_sherch_form_AJAX();
						return;
					}
					// проверяем не принадлежит ли данный запрос другому менеджеру					
					$master = $this->check_the_empty_query($query);
					if($master > 0 && $master != $this->user_id){
						$user_name_arr = $this->get_manager_name_Database_Array($master);
						$user_name = (!empty($user_name_arr))?$user_name_arr['last_name'].' '.$user_name_arr['name']:'';
						
						$message = "Извините, но с данным запросом уже работает: <strong>$user_name</strong>";
						// $message = 'Для выбранного клиента доступны следующие кураторы:';
						$this->responseClass->addMessage($message,'error_message');
						// обновление данных в таблице
						$this->responseClass->addResponseFunction('reload_order_tbl');
						// echo '{"response":"OK","function":"echo_message","message_type":"","message":
						// "'.base64_encode($message).'","function2":"reload_order_tbl"}';	
						// exit;
						return;
					}
				}

				if($this->updateQueryStatus((int)$_POST['row_id'], $_POST['query_status'])){
					$section_locate= array(
						'not_process' => 'no_worcked_men',
						'taken_into_operation' => 'query_taken_into_operation',
						'in_work' => 'query_worcked_men',
						'history' => 'query_history'
						);
					
					$link = '?';
					$i = 0;
					if($_POST['query_status'] != 'in_work'){
						foreach ($_GET as $key => $value) {
							if($i > 0){$link .= '&';}
							if($key == 'subsection' && isset($section_locate[$_POST['query_status']])){
								$link .= $key.'='.$section_locate[$_POST['query_status']];
							}else{
								$link .= $key.'='.$value;	
							}
							$i++;
						}	
					}else{
						// ?page=client_folder&client_id=209&query_num=10008
						$link = '?page=client_folder&client_id='.$_POST['client_id'].'&query_num='.$_POST['query_num'];
					}
					
                    // исключение в бизнеслогике при переводе заказа в архив
                    if($_POST['query_status'] == 'history'){
                        $message = 'Статус успешно изменён. Запрос был перемещён в архив.';
                        $this->responseClass->addMessage($message,'successful_message');
                        $this->responseClass->addResponseFunction('reload_order_tbl');
                        return;
                    }else{
                        // переадресация на другую вкладку
                        $option['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/os/'.$link;
                        $option['timeout'] = '2000';
                        $this->responseClass->addResponseFunction('location_href',$option);
                        $message = 'Статус успешно изменён. Вы будете перенаправлены на другую вкладку.';
                        $this->responseClass->addMessage($message,'successful_message');
                    }

					// $this->responseClass->addResponseFunction('reload_order_tbl');
				}else{
					$message = "Что-то пошло не так.";
					$this->responseClass->addMessage($message,'error_message');					
				}
				// exit;
			}



			// статусы заказов
			protected function command_for_change_status_order_AJAX(){
				global $mysqli;
				$json_answer = '{"response":"OK"}';
				// если статус не пришел - что-то пошло не так
				if(!isset($_POST['status_order']) || !isset($_POST['order_id'])){
					$message = 'Что-то пошло не так, статус не был получен.';
					echo '{"response":"show_new_window_simple","html":"'.base64_encode($message).'","title":"Что-то пошло не так..."}';
					exit;
				}
				
				$new_status = $_POST['status_order'];

				// обработка следствий из различных статусов
				switch ($_POST['status_order']) {
					case 'query_in_work':
						if($this->check_the_pyment_order((int)$_POST['order_id'])){
							$href = 'http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section=orders&subsection=order_start';
							$json_answer = '{"response":"OK","function":"location_href","href":"'.$href.'"}';
							$new_status = 'in_operation';
						}else{
							$message = "Заказ не был оплачен в достаточном размере для его запуска!";
							$json_answer = '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
							echo $json_answer;
							exit;
						}
						break;
					case 'in_work':
						if(!isset($_POST['confirm_question'])){
							$html = '<form>';
							$html = '<input type="hidden" value="1" name="confirm_question">';
							foreach ($_POST as $key => $value) {
								$html .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
							}
							$html .= '</form>';
							
							$html .= 'При подтверждении этого статуса редактирование основной информации по услугам в заказе будет не доступно!<br>';
							$html .= 'Проверьте НОМЕР РЕЗЕРВА!<br>';
							$html .= 'Проверьте КОРРЕКТНОСТЬ ЗАПОЛНЕНИЯ ТЗ для подразделений!<br>';
							$html .= 'Спасибо.';

							echo '{"response":"show_new_window","html":"'.base64_encode($html).'","title":"Внимание","width":"600"}';
							exit;
						}




						if($this->check_the_pyment_order((int)$_POST['order_id'])){
							$href = 'http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section=orders&subsection=order_start';
							$json_answer = '{"response":"OK","function":"location_href","href":"'.$href.'"}';
							}else{
							$message = "Заказ не был оплачен в достаточном размере для его запуска!";
							$json_answer = '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
							echo $json_answer;
							exit;
						}
						break;
					
					default: 						
						$json_answer = '{"response":"OK","function":"reload_order_tbl"}';
						break;
				}			
				$query = "UPDATE  `".CAB_ORDER_ROWS."` SET";
				$query .= " `global_status` =  '".$new_status."' ";
				if ($_POST['status_order'] == 'maket_without_payment') {
					$query .= ", `flag_design_see_everywhere` =  '1' ";
				}
				if ($_POST['status_order'] == 'in_work') {
					$query .= ", `get_in_work_time` =  NOW() ";
				}
				$query .= "WHERE  `id` ='".$_POST['order_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				// echo '{"response":"OK", "function":"window_reload"}';
				echo $json_answer;
				exit;
				
			}
			// проверка оплаты по заказу
			protected function check_the_pyment_order($id){
				global $mysqli;
				$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$this->Specificate_arr = array();
					
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Specificate_arr[] = $row;
					}
				}

				$enabled_start_work = 1;

				// перебираем все спецификации и выесняем их оплату
				foreach ($this->Specificate_arr as $key => $this->specificate) {
					if ($this->specificate['enabled_start_work'] == 0) {
						$enabled_start_work = 0;
					}
				}
				return $enabled_start_work;
			}

			

			// правим дату сдачи заказа
			protected function change_date_of_delivery_of_the_order_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`date_of_delivery_of_the_order` =  '".$_POST['date']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// правим дату сдачи по спецификации
			protected function date_of_delivery_of_the_specificate_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`shipping_date` =  '".date("Y-m-d",strtotime($_POST['date']))."',
					`shipping_date_redactor_id` =  '".$this->user_id."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode('Данные успешно обновлены.').'"}';
				exit;
			}

			// правим дату утверждения макета
			protected function change_approval_date_AJAX(){
				
				// вносим правки в позицию
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`approval_date` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".$_POST['row_id']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				// запускаем все прикреплённые услуги


				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
					// запрашиваем id прикреплённых услуг, которые необходимо стартануть
					$str = '';
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."`
					WHERE  `dop_row_id` ='".$_POST['dop_data_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					// echo $query;
					$n = 0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							if($row['performer_status'] == 'being_prepared' || trim($row['performer_status']) == '')
							$str .= (($n>0)?",":"")."'".$row['id']."'";
							$n++;
						}
					}

					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`performer_status` =  'in_processed' 
					WHERE  `id` IN (".$str.")";
					// echo $query;
					if($str!=''){
						$result = $mysqli->query($query) or die($mysqli->error);	
					}
					
					// меняем на in_processed
					
				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
				
				echo '{"response":"OK","function":"reload_order_tbl"}';
				exit;
				//echo 'необходимо доделать функцию. ищем \'being_prepared\' меняем на in_processed';
			}
			// меняем дату утверждения
			protected function change_approval_date($id,$dop_data_id){
				
				// вносим правки в позицию
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`approval_date` =  NOW() 
					WHERE  `id` ='".$id."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				// запускаем все прикреплённые услуги


				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
					// запрашиваем id прикреплённых услуг, которые необходимо стартануть
					$str = '';
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."`
					WHERE  `dop_row_id` ='".$dop_data_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					// echo $query;
					$n = 0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							if($row['performer_status'] == 'being_prepared' || trim($row['performer_status']) == '')
							$str .= (($n>0)?",":"")."'".$row['id']."'";
							$n++;
						}
					}

					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`performer_status` =  'in_processed' 
					WHERE  `id` IN (".$str.")";
					// echo $query;
					if($str!=''){
						$result = $mysqli->query($query) or die($mysqli->error);	
					}
					
					// меняем на in_processed
					
				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
				
				echo '{"response":"OK","function":"reload_order_tbl"}';
				exit;
				//echo 'необходимо доделать функцию. ищем \'being_prepared\' меняем на in_processed';
			}

			// меню выбора комманды для ДИЗА по дизайну
			protected function get_modal_menu_design_what_AJAX(){
				$html = '';
				$n = 0;
				$html .= '<ul id="get_commands_men_for_design" class="check_one_li_tag">';
				
				$html .= '<li data-name_en="услуга выполнена" class="checked">Дизайн утверждён</li>';
				$html .= '<li data-name_en="исправить дизайн">правка</li>';

				$html .= '</ul>';


				$html .= '<form>';

				$html .= '<input type="hidden" name="status_design" value="услуга выполнена">';	
				$html .= '<input type="hidden" name="AJAX" value="edit_design_status_for_manager">';	

				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}

				$html .= '</form>';

				echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите действие:"}';
				exit;
			}
			// меню выбора комманды для ДИЗА по макету
			protected function get_modal_menu_maket_what_AJAX(){
				$html = '';
				$n = 0;
				$html .= '<ul id="get_commands_men_for_design" class="check_one_li_tag">';
				
				$html .= '<li data-name_en="подготовить в печать" class="checked">Макет утверждён</li>';
				$html .= '<li data-name_en="исправить макет">правка</li>';

				$html .= '</ul>';


				$html .= '<form>';

				$html .= '<input type="hidden" name="status_design" value="подготовить в печать">';	
				$html .= '<input type="hidden" name="AJAX" value="edit_design_status_for_manager">';	

				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}

				$html .= '</form>';

				echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите действие:"}';
				exit;
			}

			// смена статуса услуги дизайна или макета менеджером
			protected function edit_design_status_for_manager_AJAX(){
				// если услуга принята
				if(isset($_POST['status_design']) && 
					($_POST['status_design'] == 'услуга выполнена' ||
					 $_POST['status_design'] == 'подготовить в печать')){
					global $mysqli;
					// запрос информации по услуге
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['id_row']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
					$service = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$service = $row;
						}
					}

					switch ($_POST['status_design']) {
						case 'подготовить в печать':
							$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
							$query .= " `performer_status` =  '".$_POST['status_design']."'";
							$query .= ", `flag_design_prepare_to_print` =  '1'";
							$query .= " WHERE  `id` ='".$_POST['id_row']."';";
							$result = $mysqli->query($query) or die($mysqli->error);

							if(isset($_POST['position_id'])){
								$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
								$query .= " `performer_status` =  'задача принята ожидает'";
								$query .= " WHERE  `dop_row_id` = '".$service['dop_row_id']."' AND `performer_status` = 'ожидаем утверждения дизан-эскиза';";								
								$result = $mysqli->query($query) or die($mysqli->error);	

								$this->change_approval_date($_POST['position_id'],$service['dop_row_id']);
							}
							break;
						case 'услуга выполнена': //
							
							
							$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
							$query .= " `performer_status` =  'задача принята ожидает'";
							$query .= " WHERE  `dop_row_id` = '".$service['dop_row_id']."' AND `performer_status` = 'ожидаем утверждения дизан-эскиза';";								
							$result = $mysqli->query($query) or die($mysqli->error);

							$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
							$query .= " `performer_status` =  '".$_POST['status_design']."'";
							$query .= " WHERE  `id` ='".$_POST['id_row']."';";
							$result = $mysqli->query($query) or die($mysqli->error);

							break;
						
						default:
							# code...
							break;
					}
					
					echo '{"response":"OK"}';
				}else{// если нужна правка
					if(!isset($_POST['comment']) || (isset($_POST['comment']) && $_POST['comment'] == '')){
						// проверяем на наличие комментариев
						$html = '<form>';
						if(isset($_POST['comment']) && $_POST['comment'] == ''){
							$html .= '<div style="color:red; padding:5px; margin-bottom: 10px;border:1px solid red;">Вы забыли написать тут что-нибудь =)</div>';	
						}
						foreach ($_POST as $key => $value) {
							$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
						}
						$html .= '<div>';
							$html .= '<textarea class="" name="comment"></textarea>';
						$html .= '</div>';
						$html .= '</form>';
						echo '{"response":"show_new_window","title":"Опишите правку, дополните своими комментариями и пожелания","html":"'.base64_encode($html).'","width":"600"}';
					}else{
						
						
						global $mysqli;
						// получаем информацию из ТЗ
						$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['id_row']."';";
						$result = $mysqli->query($query) or die($mysqli->error);
						$service = array();
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$service = $row;
							}
						}
						// запрос имени мена
						$user = $this->get_manager_name_Database_Array($this->user_id);
						$tz = base64_decode($service['tz']);
						$tz .= PHP_EOL.PHP_EOL.'<br><br>Правка по макету на '.date('d.m.Y H:i',time()).' от менеджера: '.$user['last_name'].' '.$user['name'].':<br>'.PHP_EOL;
						$tz .= $_POST['comment'];
						// $html = $query.'<br>'.$tz.'<br>'.$this->print_arr($service);
						// echo '{"response":"show_new_window","title":"Опишите правку, дополните своими комментариями и пожелания","html":"'.base64_encode($html).'","width":"600"}';
						// exit;
						//////////////////////////
						//	смена статуса услуги
						//////////////////////////
						$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
						$query .= " `performer_status` =  '".$_POST['status_design']."'";
						$query .= ", `flag_design_edits` =  '1'";
						$query .= ", `tz` =  '".base64_encode($tz)."'";
						$query .= " WHERE  `id` ='".$_POST['id_row']."';";
						$result = $mysqli->query($query) or die($mysqli->error);
						// echo '{"response":"OK"}';

						// запись сервисного сообщения в комментарии по правке

						echo '{"response":"OK"}';
					}
				}
				exit;
			}

			// правим срок по дс
			protected function change_deadline_value_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
					`work_days` =  '".$_POST['value']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}
			// детализация позиции по прикреплённым услугам
			protected function get_a_detailed_article_on_the_price_of_positions_AJAX(){
				$html = '';
				 	
				// собираем Object по заказу
				$this->Positions_arr = $this->positions_rows_Database($_POST['specificate_id']);
				foreach ($this->Positions_arr as $key => $value) {
					$this->Positions_arr[$key]['SERVICES'] = $this->get_order_dop_uslugi($value['id_dop_data']);	 								
				}

				// собираем HTML
				$html .= $this->get_a_detailed_article_on_the_price_of_positions_Html();
				$title = 'Заказ № '.$_POST['order_num_user'].' - финансовые расчёты';
				echo '{"response":"show_new_window_simple","title":"'.$title.'","html":"'.base64_encode($html).'"}';
				exit;
			}

			// присваиваем значение поля логотип (в окне доп. тех. инфо) ко всем услугам по текущей позиции
			protected function save_logotip_for_all_position_AJAX(){
				global $mysqli;

				// если массив услуг пуст - заполняем его
				if(empty($this->Services_list_arr)){
					$this->Services_list_arr = $this->get_all_services_Database();
				}

				// завпрашиваем услуги прикрепленные к данной позиции
				$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".(int)$_POST['id_dop_data']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$service_union_arr[] = $row;
					}
				}
				
				$id_s = array();

				// перебираем прикрепленные услуги
				foreach ($service_union_arr as $key => $union_service) {
					// проеряем существует ли описание такой услуги и если существует то включено ли поле логотип
					if(isset($this->Services_list_arr[$union_service['uslugi_id']]) && $this->Services_list_arr[$union_service['uslugi_id']]['logotip_on'] == 'on'){
						// запоминаем id прикреплённой услуги, который мы намереваемся изменить
						$id_s[] = $union_service['id'];
					}else{
						// если такой услуги у нас в списках почему-то нет, но она у нас прикреплена
						// т.е. мы не можем проверить включено ли поле логотип в данной прикрепленнной услуге и заполняем ету ячейку в базе без проверки 
						$id_s[] = $union_service['id'];
					}
				}

				// перебор выписанных нами id услуг, к которым мы будем прикреплять логотип
				$id_s_str = '';
				foreach ($id_s as $key => $value) {
					$id_s_str .= (($key>0)?',':'')."'".$value."'";
				}



				if($id_s_str != ''){
					//////////////////////////////////////////////////////////////////////////////
					//	запрос на прикрепление логотипа к услугам прикреплённым к позиции
					//////////////////////////////////////////////////////////////////////////////
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
						`logotip` =  '".trim($_POST['logotip'])."' 
						WHERE  `id` IN (".$id_s_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					// формируем ответ
					$Message = 'Значение поля логотип успешно прикреплено ко всем услугам по текущей позиции.';
				}else{
					// формируем ответ
					$Message = 'К данной позиции не прикреплено ни одной услуги<br> в которой можно было бы заполнить поле логотип.';

				}

				echo '{"response":"OK","message":"'.base64_encode($Message).'", "function":"echo_message","message_type":"system_message"}';
				exit;
			}

			// присваиваем значение поля логотип (в окне доп. тех. инфо) ко всем услугам по текущему заказу
			protected function save_logotip_for_all_order_AJAX(){
				global $mysqli;

				// если массив услуг пуст - заполняем его
				if(empty($this->Services_list_arr)){
					$this->Services_list_arr = $this->get_all_services_Database();
				}

				// запрос спецификаций 
				$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_num` = '".(int)$_POST['order_num']."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$spec_id = '';
				$n = 0;
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$spec_id .= (($n>0)?',':'')."'".$row['id']."'";
						$n++;
					}
				}

				// echo '{"response":"OK","message":"'.base64_encode($spec_id).'", "function":"echo_message","message_type":"system_message"}';
				// exit;


				// запрашиваем позиции прикреплённые к спецификации
				$query = "SELECT *, `".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data` 
				FROM `".CAB_ORDER_DOP_DATA."` 
				INNER JOIN ".CAB_ORDER_MAIN." ON `".CAB_ORDER_MAIN."`.`id` = `".CAB_ORDER_DOP_DATA."`.`row_id` 
				WHERE `".CAB_ORDER_MAIN."`.`the_bill_id` IN (".$spec_id.")";
				$dop_row_id_str = '';
				$result = $mysqli->query($query) or die($mysqli->error);
				$n = 0;
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$dop_row_id_str .= (($n>0)?',':'')."'".$row['id_dop_data']."'";
						$n++;
					}
				}
				// echo $query.'  <br>  '.$dop_row_id_str; 

				$service_union_arr = array();
				// если у нас есть список dop_row_id позиций
				if($dop_row_id_str != ''){
					// завпрашиваем услуги прикрепленные к позициям заказа
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` IN (".$dop_row_id_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$service_union_arr[] = $row;
						}
					}
				}

				// echo '<br>'.$query.'<br>';
				
				$id_s = array();

				// перебираем прикрепленные услуги
				foreach ($service_union_arr as $key => $union_service) {
					// проеряем существует ли описание такой услуги и если существует то включено ли поле логотип
					if(isset($this->Services_list_arr[$union_service['uslugi_id']]) && $this->Services_list_arr[$union_service['uslugi_id']]['logotip_on'] == 'on'){
						// запоминаем id прикреплённой услуги, который мы намереваемся изменить
						$id_s[] = $union_service['id'];
					}else{
						// если такой услуги у нас в списках почему-то нет, но она у нас прикреплена
						// т.е. мы не можем проверить включено ли поле логотип в данной прикрепленнной услуге и заполняем ету ячейку в базе без проверки 
						$id_s[] = $union_service['id'];
					}
				}

				// перебор выписанных нами id услуг, к которым мы будем прикреплять логотип
				$id_s_str = '';
				foreach ($id_s as $key => $value) {
					$id_s_str .= (($key>0)?',':'')."'".$value."'";
				}

				// echo $id_s_str,'  <br>';

				if($id_s_str != ''){
					//////////////////////////////////////////////////////////////////////////////
					//	запрос на прикрепление логотипа к услугам прикреплённым к позиции
					//////////////////////////////////////////////////////////////////////////////
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
						`logotip` =  '".trim($_POST['logotip'])."' 
						WHERE  `id` IN (".$id_s_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					// формируем ответ
					$Message = 'Значение проля логотип успешно прикреплено ко всем услугам заказа № '.Cabinet::show_order_num($_POST['order_num']).'.';
					
					// echo $query;
				}else{
					// формируем ответ
					$Message = 'К данному заказу не прикреплено ни одной услуги<br> в которой можно было бы заполнить поле логотип.';

				}

				echo '{"response":"OK","message":"'.base64_encode($Message).'", "function":"echo_message","message_type":"system_message"}';
				exit;
			}

			// сохранение пути к макету
			protected function save_the_url_for_layout_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`the_url_for_layout` =  '".base64_encode($_POST['text'])."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				// echo $query;
				echo '{"response":"OK"}';
				exit;
			}

			// сохранение % готовности (функция с таймингом в JS)
			protected function change_percentage_of_readiness_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `percentage_of_readiness` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}'; 	
				exit;
			}

			// присваиваем пользователя исполнителя услуги к услуге (взять услугу в работу)
			protected function get_in_work_service_AJAX(){
				global $mysqli;
				
				switch ($this->user_access) {
					case '9':
						$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET";
						$query .= " `performer_id` =  '".$_POST['user_id']."'";
						$query .= ", `performer_status` = 'задача принята ожидает'";
						$query .= "WHERE  `id` ='".$_POST['row_id']."';";
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK","function":"reload_order_tbl"}';
						break;
					
					default:
					// *********** !!!!!!!!!
						$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET";
						$query .= " `performer_id` =  '".$_POST['user_id']."'";
						$query .= "WHERE  `id` ='".$_POST['row_id']."';";
						$result = $mysqli->query($query) or die($mysqli->error);
						echo '{"response":"OK"}'; 	
						break;
				}
				exit;
				
			}

			// редактирование даты и времени начала работы над услугой
			protected function change_date_work_of_service_AJAX(){
				
				// проверка принятых значений даты
				if (($timestamp = strtotime($_POST['date'])) === false) {
				    return '{"error":"Строка ('.$_POST['date'].') недопустима"}';
				}

				global $mysqli;
				//записываем дату в базу
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `date_work` =  '".date("Y-m-d H:i:s",$timestamp)."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				// echo $query;
				echo '{"response":"OK"}'; 	
				exit;
			}	

			// редактирование даты и времени окончания работы над услугой	
			protected function change_date_ready_of_service_AJAX(){
				
				// проверка принятых значений даты
				if (($timestamp = strtotime($_POST['date'])) === false) {
				    return '{"error":"Строка ('.$_POST['date'].') недопустима"}';
				}

				global $mysqli;
				//записываем дату в базу
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `date_ready` =  '".date("Y-m-d H:i:s",$timestamp)."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				// echo $query;
				echo '{"response":"OK"}'; 
				exit;	
			}


			// сохранение типа/названия станка на котором будет выполняться работ
			protected function change_machine_type_AJAX(){
				
				global $mysqli;
				//записываем дату в базу
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `machine` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				// echo $query;
				echo '{"response":"OK"}'; 	
				exit;
			}

			// запуск услуг в работу
			protected function start_services_in_processed_AJAX(){
				global $mysqli;

					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_status` =  'Ожидает обработки' ";
					$query .= "WHERE  `id` ='".$_POST['id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
					echo '{"response":"OK","function":"reload_order_tbl"}'; 
					exit;

					
					// получаем информацию по услуге в которой меняем статус
					$this->Service = $this->get_service((int)$_POST['id']);
					// получаем список прикреплённных услуг отсортированных по Performer
					$this->Services_for_position_arr_sort_by_performer = $this->position_service_sort_of_perforfer($this->Service['dop_row_id']);

					// проверяем все ли услуги Дизайна выполнены
					$design_already_made = true; $n = 0; $design_service_ids = '';
					if(isset($this->Services_for_position_arr_sort_by_performer['9'])){
						foreach ($this->Services_for_position_arr_sort_by_performer['9'] as $key => $design_service) {
							if(trim($design_service['performer_status']) == 'услуга выполнена' || trim($design_service['performer_status']) == 'Макет отправлен в СНАБ'){
								// запоминаем id ДИЗ услуг, которые будем менять на услуга выполнена
								$design_service_ids .=  ((0 != $n++)?',':'')."'".$design_service['id']."'";
							}else{
								// меняем флаг
								$design_already_made = false;									
							}
						}
					}

					// если услуги дизайна выполнены - 
					if ($design_already_made == true) { 
						// переводим статусы услуг дизайно в окончательный статус
						$this->update_performer_status_for_services('услуга выполнена', $design_service_ids);

						// правим статус указанной услуги
						$this->update_performer_status_for_services('in_processed', "'".(int)$_POST['id']."'");
						// отвечаем, что всё ОК и перезагружаем страницу
						echo '{"response":"OK","function":"window_reload"}'; 
					}




					// для не админов и всех кто так или иначе получил доступ к кнопке или для всех поголовно 
					// --- нужно обсудить с Серёгой
					//!!!!!! доделать !!!!!
					/*
					1). сдалеть запрос в базу и узнать dop_data_id в данной услуге
					2). опросить базу на услуги с куратором ДИЗ по данному dop_data_id
					3). если НЕ Нашли - просто запускаем в работу
						если НАШЛИ - выводим окно с перечислением услуг диза, их текущего статуса и предлагаем 
						3 варианта:
						1). Запустить и изменить статус услуг диза на "услуга выполнена"
						2). Запустить и не менять статусы
						3). Отменить запуск услуги в работу


						или вообще не давать запустить если ты не админ.... ведь запуск без дизайна 
						обычно не возможен.... хотя это ведь может быть распаковка и её нужно сделать заранее
						что тогда???? выходит придётся дать такую превилегию снабженцам???? 

					*/
						exit;

			}

			/////////////////////////////////////////////////////////////////////////////////////
			//	-----  START  -----  service->  -----  START  -----
			/////////////////////////////////////////////////////////////////////////////////////
				// обновление статусов у группы услуг
				protected function update_performer_status_for_services($status, $IDs){ // нужно ли???? !!!! 
					global $mysqli;
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_status` =  '".$status."' ";
					$query .= "WHERE  `id` IN (".$IDs.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					return;
				}

				// получаем прикреплённую услугу по id
				protected function get_service($service_id){
					global $mysqli;
					$service = 0;
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".$service_id."'";
					
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$service = $row;
						}
					}
					return $service;
				}

				// получаем список услуг к позиции
				protected function get_all_services_for_position($dop_row_id){
					global $mysqli;
					$position_service_arr = array();
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$position_service_arr = $row;
						}
					}
					return $position_service_arr;
				}

				// сортируем массив прикреплённных услуг по Performer (куратору, подразделению) 
				protected function position_service_sort_of_perforfer($dop_row_id = 0){
					if(!isset($this->Services_for_position_arr)){
						if($dop_row_id != 0){
							$this->Services_for_position_arr = $this->get_all_services_for_position($dop_row_id);
						}else{
							echo 'dop_row_id не указан!!! ';exit;
						}
					}

					$service_arr_filtering_for_performer = array();
					foreach ($this->Services_for_position_arr as $service) {
						$service_arr_filtering_for_performer[$service['performer']][] = $service;
					}

					return $service_arr_filtering_for_performer;
				}



				
			//////////////////////////////////////////////////////////////////////////////////
			//   -----  END  -----  service->  -----  END  -----
			///////////////////////////////////////////////////////////////////////////////////

			// выяисляем свёрнут или развёрнут заказ для данного пользователя
			protected function get_open_close_for_this_user($open_close){
				$open_close_arr = ($open_close!="")?json_decode($open_close,true):array();
				//return true;
				if(isset($open_close_arr[$this->user_id])){
					// class для кнопки показать / скрыть
					$this->open_close_class = "";
					// rowspan / data-rowspan
					$this->open_close_rowspan = "rowspan";
					// стили для строк которые скрываем или показываем
					$this->open_close_tr_style = ' style="display: table-row;"';
					// доп class открытия / закрытия для строки заказа/ запроса
					$this->open_close_row_class = " row_open";
					return true;
				}else{
					// class для кнопки показать / скрыть
					$this->open_close_class = " show";
					// rowspan / data-rowspan
					$this->open_close_rowspan = "data-rowspan";
					// стили для строк которые скрываем или показываем
					$this->open_close_tr_style = '';
					// доп class открытия / закрытия для строки заказа/ запроса
					$this->open_close_row_class = " row_close";
					return false;
				}
			}

			// раскрыть скрыть заказ
			protected function open_close_order_AJAX(){

				/*
					т.к. один и тот же заказ может просматривать очень много пользователей одновременно 
					хранить информацию о том закрыт данный заказ или открыть у данного пользователя будем в Json
					массив имеет вид
					array( $user_id => 1,$user_id => 1 )
					примем за правило:
						- если записи нет - то заказ свёрнут
						- если запись есть - заказ развёрнут

				*/
				$tbl = CAB_ORDER_ROWS;
				if(isset($_GET['section'])){
					switch ($_GET['section']) {
						case 'requests':
							$tbl = RT_LIST;
							break;
						case 'paperwork':
							// исключение для работы по документам
							switch ($_GET['subsection']) {
								case 'the_order_is_create':
									# code...
									break;
								case 'order_in_work':
									# code...
									break;
								case 'order_is_paperwork':
									# code...
									break;
								case 'order_shipped':
									# code...
									break;
								
								default:
									$tbl = CAB_BILL_AND_SPEC_TBL;	
									break;
							}					
							break;						
						default:
							break;
					}
				}
				//$tbl = (isset($_GET['section']) && $_GET['section'] == 'requests')?RT_LIST:CAB_ORDER_ROWS;


				// делаем запрос на хранящуюся у нас в базе информацию по данной строке заказа
				global $mysqli;
				$open_close = "";
				$query = "SELECT `id`, `open_close` FROM `".$tbl."`";
				$query .= " WHERE  `id` ='".$_POST['order_id']."';";

				// делаем выборку open_close
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$open_close = $row['open_close'];
					}
				}

				// echo $query;

				// декодируем json (если там что-то есть)
				//  !!!! если будут вылезать ошибке о некорректном json - будем решать по мере их поступления
				$open_close_arr = array();
				
				if(trim($open_close)!=""){
					$open_close_arr = json_decode($open_close,true);
				}

				// если заказ нужно открыть
				if($_POST['open_close'] == 1){
					// вставляем в наш массив информацию, что для данного пользователя заказ раскрыт
					$open_close_arr[(string)$this->user_id] = '1';	
				}else{
					// удаляем из массива информацию от том, что заказ 
					if(isset($open_close_arr[$this->user_id])){
						unset($open_close_arr[$this->user_id]);
					}
				}

				// echo '<pre>';
				// print_r($open_close_arr);
				// echo '</pre>';

				// переписываем информацию в строке заказа
				$query = "UPDATE  `".$tbl."`  SET  `open_close` =  '".json_encode($open_close_arr)."' ";
				$query .= " WHERE  `id` ='".$_POST['order_id']."';";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				
				echo '{"response":"OK"}';
				exit;
			}

			// ТЗ для производства
			protected function get_dialog_tz_for_production_AJAX(){
				$html = '';
				
				global $mysqli;

				$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['row_id']."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$service = $row;
					}
				}

				// ограничиваем выгрузку стоимости по допуску юзера
				if($this->user_access == 1 || $this->user_access == 2 || $this->user_access == 5 || $this->user_access == 8 || $this->user_access == 9){
					// стоимость услуги
					$html .= '<div class="separation_container">';
						$html .= '<strong>Входящая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_in'].' р.</div>';
						$html .= '<strong>Исходящая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_out'].' р.</div>';			
					$html .= '</div>';
				}else if($this->user_access == 4){
					// стоимость услуги
					$html .= '<div class="separation_container">';
						$html .= '<strong>Входящая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_in'].' р.</div>';
					$html .= '</div>';
				}
				// $html .= '$this->user_id = '.$this->user_id.' *** '.$this->director_of_operations_ID.'<br>';
				// путь к макету
				// если указан
				if (trim($service['the_url_for_layout'])!='') {
					$html .= '<div class="separation_container">';
					$html .= '<strong>Путь к макету</strong>:<br>';
					$html .= '<div class="data_info">'.base64_decode($service['the_url_for_layout']).'</div>';			
					$html .= '</div>';
				}




				//////////////////////////
				//	ДОП ПОЛЯ
				//////////////////////////
				if(!isset($this->dop_inputs_listing)){
					// получаем список всех полей
					$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
					$result = $mysqli->query($query) or die($mysqli->error);
					$this->dop_inputs_listing = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->dop_inputs_listing[$row['name_en']] = $row;
						}
					}

				}


				// получаем  json
				$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
				// декодируем json  в массив
				$this->print_details_dop = json_decode($this->print_details_dop_Json, true);
				
				if(!isset($this->print_details_dop)){
					$html .= "<div>произошла ошибка json</div>";
				}

				if(isset($this->print_details_dop) && !empty($this->print_details_dop)){
					//echo  $service['print_details_dop'];
					foreach ($this->print_details_dop as $key => $text) {
						$html .= '<div class="separation_container">';
							$html .= '<strong>'.$this->dop_inputs_listing[$key]['name_ru'].'</strong>:<br>';
							$html .= '<div class="data_info">'.base64_decode($text).'</div>';		
						$html .= '</div>';			
					}
				}

				//////////////////////////
				//	текст TЗ 
				//////////////////////////
				$html .= '<div class="separation_container">';
					$html .= '<strong>Техническое задание, пояснения:</strong><br>';
					$tz = base64_decode($service['tz']);
					$html .= '<div class="data_info">'.(($tz!='')?$tz:'   -   ').'</div>';
				$html .= '</div>';

				if($this->user_access == 4){
					$html .= '<div class="separation_container">';
					$html .= '<strong>Логотип:</strong><br>';
					$html .= '<div class="data_info">'.(($service['logotip']!="")?$service['logotip']:"   -   ").'</div>';
					// $html .= .'654';
					$html .= '</div>';
				}

				$html .= '<div class="add_new_comment">';
				$html .= '<form>';
				$html .= '<div id="add_comments_of_position" class="" data-position_id="'.$_POST['row_id'].'">переписка по позиции</div>';
				$html .= '</form>';
				$html .= '</div>';

				echo '{"response":"OK","html":"'.base64_encode($html).'","title":"ТЗ"}';
				exit;
			}

			// смена статуса плёнок по услуге
			protected function choose_statuslist_film_photos_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `film_photos_status` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// сохраняет комментарий по позиции
			protected function save_position_comment_Database($position_id,$user_name,$comment_text){
				global $mysqli;
				$query ="INSERT INTO `".CAB_DOP_DATA_LIST_COMMENTS."` SET
			             `user_id` = '".(int)$this->user_id."',
			             `position_id` = '".(int)$position_id."',
			             `user_name` = '".$user_name."',
			             `comment_text` = '".$comment_text."',
			            `create_time` = NOW()";
					$result = $mysqli->query($query) or die($mysqli->error);	
				return  $mysqli->insert_id;
			}

			// смена статуса услуги
			protected function choose_service_status_AJAX(){
				$reload = 0;
				if(isset($_POST['question']) && $_POST['question'] == 1){
					// просим комментарий
					$this->create_the_comment('Опешите пожалуйста проблему.');
				}


				global $mysqli;
				//////////////////////////
				//	смена статуса услуги
				//////////////////////////
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET ";
				$query .= " `performer_status` =  '".$_POST['value']."'";
				if($_POST['value'] == "дизайн-эскиз готов" || $_POST['value'] == "оригинал-макет готов"){
					$query .= ", `flag_design_edits` =  '0'";
				}
				if(isset($_POST['comment_text']) && $_POST['comment_text'] != ''){
					$query .= ", `performer_comment` =  '".base64_encode($_POST['comment_text'])."'";
					$reload = 1;

					$user_name_arr = $this->get_manager_name_Database_Array($this->user_id);
					$user_name = (!empty($user_name_arr))?$user_name_arr['last_name'].' '.$user_name_arr['name']:'';
					$this->save_position_comment_Database($_POST['position_id'],$user_name,$_POST['comment_text']);
				}else{
					$query .= ", `performer_comment` =  ''";
				}

				if($_POST['value'] == "макет отправлен в СНАБ"){
					// делаем пометку на позиции для снаб
					// 8// 88888888888888888888888
					if(isset($_POST['position_id'])){
						$this->db_edit_one_val(CAB_ORDER_MAIN,'flag_check_the_maket',(int)$_POST['position_id'],1);
					}
					$query .= ", `flag_design_prepare_to_print` =  '0'";
				}
				if($_POST['value'] == "услуга выполнена"){
					// делаем пометку на позиции для снаб
					if(isset($_POST['position_id'])){
						$this->db_edit_one_val(CAB_ORDER_MAIN,'flag_check_the_maket',(int)$_POST['position_id'],0);
					}
				}

				if($_POST['value'] == "подготовить в печать"){
					$query .= ", `flag_design_prepare_to_print` =  '1'";
				}
				$query .= " WHERE  `id` ='".$_POST['id_row']."';";
				$result = $mysqli->query($query) or die($mysqli->error);

				

				////////////////////////////////////////////////
				//	следствие окончания всех услуг на позицию  START
				////////////////////////////////////////////////
					// каждый раз при смене статуса по услуге 
					// (если этот статус меняется из под профиля пр-во )  !!!!!!!
					// система должна проверить статусы по всем услугам прикрепленным 
					// к текущей позиции и при условии, что все статусы будут иметь значение 
					// "услуга выполнена" система выставляет статусы склада и снабжения 
					// по данной позиции на готов к отгрузке

					if($this->user_access == 4 && isset($_POST['value']) && $_POST['value'] == 'услуга выполнена'){
						

						// $query = "SELECT `dop_row_id` FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['id_row']."'";
						// $dop_row_id = 0;
						// if($result->num_rows > 0){
						// 	while($row = $result->fetch_assoc()){
						// 		$dop_row_id = $row['dop_row_id'];
						// 	}
						// }




						// проверяем все ли услуги выполнены 
						// услуги доставки отключены по id
						$query = "SELECT `performer_status`,`dop_row_id` FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` IN (SELECT `dop_row_id` FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['id_row']."') AND `uslugi_id` not in ('5','4','64','71')";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						$union_services_arr = array();

						$UPDATE_FLAG = true;
						$dop_row_id = 0;
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								if (trim($row['performer_status']) != 'услуга выполнена') {
									$UPDATE_FLAG = false;									
								}
								$dop_row_id = $row['dop_row_id'];
							}
						}

						// если $UPDATE_FLAG всё ещё true , т.е. все услуги готовы
						if($UPDATE_FLAG == true && $dop_row_id!=0){
							// делаем запрос по $dop_row_id и узнаем id строки из CAB_ORDER_MAIN
							$query = "SELECT * FROM `".CAB_ORDER_DOP_DATA."` WHERE `id` = '".$dop_row_id."'";
							$result = $mysqli->query($query) or die($mysqli->error);
							$row_id = 0;
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									$row_id = $row['row_id'];
								}
							}

							if($row_id != 0){
								// обновляем статусы снабжения и склада
								$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
								`status_snab` =  'ready_for_shipment',
								`status_sklad` =  'ready_for_shipment' 
								WHERE  `id` ='".$row_id."';";
								$result = $mysqli->query($query) or die($mysqli->error);
							}						
						}
					}
				////////////////////////////////////////////////
				//	следствие окончания всех услуг на позицию  END
				////////////////////////////////////////////////

				// echo '{"response":"OK", "function":"php_message","text":"Статус услуги успешно изменён на ` '.$_POST['value'].' `"}';
				

				
				// если нужно - обновляем контент на странице
				if($reload == 1){ 
					$json_answer = '{"response":"OK","function":"reload_order_tbl"}';	
				}else{
					$json_answer = '{"response":"OK"}';	
				}

				echo $json_answer;
				
				exit;
			}

			// смена глобального статуса ЗАКАЗА
			protected function choose_statuslist_order_and_paperwork_AJAX(){
				$this->chenge_order_status($_POST['value'], $_POST['row_id']);
				if(isset($this->order_status[$_POST['value']])){
					$status = $this->order_status[$_POST['value']];
				}else if(isset($this->paperwork_status[$_POST['value']])){
					$status = $this->paperwork_status[$_POST['value']];
				}elseif (isset($this->order_service_status[$_POST['value']])) {
					$status = $this->order_service_status[$_POST['value']];
				}else{
					$status = $_POST['value'];
				}

				$message = "Статус заказа изменён на \"".$status."\"";

				echo '{"response":"OK","function":"reload_order_tbl","function2":"echo_message","message_type":"successful_message","message":"'.base64_encode($message).'"}';
				exit;
			}
			// смена статуса заказа
			protected function chenge_order_status($status,$row_id){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$status."' ";
				if ($status == 'in_work') {
					$query .= ", `flag_design_see_everywhere` =  '0' ";
				}
				$query .= "WHERE  `id` ='".$row_id."';";
				$result = $mysqli->query($query) or die($mysqli->error);
			}

			//////////////////////////
			// смена статуса бухгалтерии
			//////////////////////////
				protected function buch_status_select($value,$row_id){
					global $mysqli;
					$query = "UPDATE  `".CAB_BILL_AND_SPEC_TBL."`  SET  
						`buch_status` =  '".$value."' ";
					if(isset($_POST['comment_text'])){
						// запрос предыдущих комментов
						$sub_query = "SELECT `id`,`comments` FROM `".CAB_BILL_AND_SPEC_TBL."`";
						$sub_query .= "WHERE  `id` ='".$row_id."';";
						$result = $mysqli->query($sub_query) or die($mysqli->error);
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){							
								$comments = $row['comments'];							
							}
						}


						$user = $this->get_manager_name_Database_Array($this->user_id);
						$tz = PHP_EOL.PHP_EOL.'<br><br>Запрос <strong>"'.$this->buch_status_service[$_POST['status_buch']].'"</strong> в '.date('d.m.Y H:i',time()).' от менеджера: '.$user['last_name'].' '.$user['name'].':<br>'.PHP_EOL;
						$tz .= $_POST['comment_text'];

						$query .= ", comments = '".$comments.$tz."' ";
					}
					$query .= "WHERE  `id` ='".$row_id."';";
					// echo $query;

					$result = $mysqli->query($query) or die($mysqli->error);
				}

				protected function buch_status_select_AJAX(){
					$this->buch_status_select($_POST['value'],$_POST['row_id']);

					if(isset($this->buch_status_service[$_POST['value']])){
						$status = $this->buch_status_service[$_POST['value']];
					}else if(isset($this->buch_status[$_POST['value']])){
						$status = $this->buch_status[$_POST['value']];
					}else{
						$status = $_POST['value'];
					}

					$message = "Статус бухгалтерии по СПФ / ОФ изменён на \"".$status."\"";
					echo '{"response":"OK","function":"reload_order_tbl","function2":"reload_order_tbl","function3":"echo_message","message_type":"successful_message","message":"'.base64_encode($message).'"}';
					exit;
				}


				// protected function get_IO_AJAX(){
				// 	$this->check_the_payment_for_order_Database($_POST['row_id']);
				// }

				// проверка всего заказа на оплату и если оплата достаточна - предложение похгалтеру выставить статус в работу
				protected function check_the_payment_for_order_Database($row_id){
					$message = '';
					// $html = '';
					global $mysqli;
					
					$query = "SELECT `order_num`,`order_id` FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `id` = '".$row_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);

					$this->Order_num = 0;
					$this->Order_id = 0;
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){							
							$this->Order_num = $row['order_num'];
							$this->Order_id = $row['order_id'];
						}
					}



					$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE 'order_id' = '".$this->Order_id."';";
					// $message .= $query.'<br>';
					$result = $mysqli->query($query) or die($mysqli->error);

					$this->Specificate_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->Specificate_arr[] = $row;
						}
					}

					$enabled_start_work = 1;
					
					// перебираем все спецификации и выесняем их оплату
					foreach ($this->Specificate_arr as $key => $this->specificate) {
						if ($this->specificate['enabled_start_work'] == 0) {
							$enabled_start_work = 0;
						}
					}

					// если все спец-ии оплачены в достаточном эквиваленте и сформированы в заказ
					if($enabled_start_work != 0 && $this->Order_id != 0 && $this->Order_num != 0){
						// автоматический перевод статус заказа в "запуск в работу"
						$this->chenge_order_status('in_operation', $this->Order_id);
						$message .= "Минимальная оплата достаточная для запуска заказа была произвдена.";
						$message .= "<br>Заказу № ".$this->show_order_num($this->Order_num)." присвоен статус \"запуск в работу\".";
						$message .= "<br>Заказ № ".$this->show_order_num($this->Order_num)." перемещён во вкладку \"Заказы\".";
						echo '{"response":"OK","function":"echo_message","message_type":"successful_message","message":"'.base64_encode($message).'","function2":"reload_order_tbl"}';					
						return;
					}

					return;
				}



			// смена статуса склада
			protected function choose_statuslist_sklad_AJAX(){
				global $mysqli;
				$reload = 0;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`status_sklad` =  '".$_POST['value']."' ";
				// все статусы склада транслируются в статусы снабжения
				if($_POST['value'] == 'goods_shipped_for_client' || $_POST['value'] == 'goods_in_stock' || $_POST['value'] == 'ready_for_shipment'){
					$query .= " , `status_snab` =  '".$_POST['value']."'";				
					$reload = 1;
				}
				

				$query .= " WHERE  `id` ='".$_POST['row_id']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				// echo $query;
				
				// проверяем отгрузку заказа
				$this->check_shipped_order();

				// если нужно - обновляем контент на странице
				if($reload == 1){ 
					$json_answer = '{"response":"OK","function":"reload_order_tbl"}';	
				}else{
					$json_answer = '{"response":"OK"}';	
				}

				echo $json_answer;
				exit;
			}

			// редактирование ожидаемой даты поставки товара на склад
			protected function change_waits_products_div_input_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`date_delivery_product` =  '".$_POST['date']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// смена статуса снабжения
			protected function change_status_snab_AJAX(){
				$reload = 0;
				if($_POST['value'] == "not_adopted" || $_POST['value'] == "question"){
					// просим пользователя ввести описание по данной проблеме
					$this->create_the_comment("Пожалуйста, опишите Ваше действие.");
					$reload = 1;
				}
					
				global $mysqli;
				
				// меняем статус снабженца
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  ";
				$query .= "`status_snab` =  '".$_POST['value']."'";
				if($_POST['value'] == 'ready_for_shipment'){ // следствие снабжение -> склад
					$query .= " , `status_sklad` =  '".$_POST['value']."'";	
					$reload = 1;
				}

				if(isset($_POST['comment_text'])){
					$query .= ", `snab_comment` = '".base64_encode($_POST['comment_text'])."'";
					
					$user_name_arr = $this->get_manager_name_Database_Array($this->user_id);
					$user_name = (!empty($user_name_arr))?$user_name_arr['last_name'].' '.$user_name_arr['name']:'';
					$this->save_position_comment_Database($_POST['row_id'],$user_name,$_POST['comment_text']);
				}else{
					$query .= ", `snab_comment` = ''";
				}

				$query .= " WHERE  `id` ='".$_POST['row_id']."';";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);

				// сохраняем снабженца по заказу
				if(isset($_POST['order_id']) && $_POST['order_id'] != 'undefined' && $this->user_access = 8){
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`snab_id` =  '".$this->user_id."'
					WHERE  `id` ='".$_POST['order_id']."';";
					// echo $query;
					$reload = 1;
					$result = $mysqli->query($query) or die($mysqli->error);					
				}				
				
				// если нужно - обновляем контент на странице
				if($reload == 1){ 
					$json_answer = '{"response":"OK","function":"reload_order_tbl"}';	
				}else{
					$json_answer = '{"response":"OK"}';	
				}

				echo $json_answer;
				exit;
			}
			

			protected function replace_query_row_AJAX(){
				$method = $_GET['section'].'_Template';
				// echo $method;
				// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
				if(method_exists($this, $method)){
					ob_start();	
						$this->$method($_POST['os__rt_list_id']);
						$content = ob_get_contents();
					ob_get_clean();
					
					echo '{"response":"OK","html":"'.base64_encode($content).'"}';
					
					exit;
				}							
			}
			// получение формы выбора услуги для заказа
			protected function get_uslugi_list_Database_Html_steep_1_AJAX(){ // шаг 0: выбираем услугу
				$html = '<form>';
				$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
				// список услуг
				$html .= $this->get_uslugi_list_Database_Html();
				$html .= '<input type="hidden" name="id_uslugi" value="">';
				$html .= '<input type="hidden" name="performer" value="">';
				$html .= '<input type="hidden" name="service_name" value="">';
				$html .= '<input type="hidden" name="id_dop_data" value="'.$_POST['id_dop_data'].'">';
				// $html .= '<input type="hidden" name="quantity" value="">';
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_2">';
				$html .= '</form>';
				echo $html;
				exit;
			}
			// вывод формы дополнительных фопросов по добавляемой услуге
			// ШАГ 2: заполняем тираж, и доп поля (если назначены)
			protected function add_new_usluga_form_steep_2_AJAX(){ 
				$html = 'Услуга <strong>'.$_POST['service_name'].'</strong>';
				
				$html .= '<form>';
				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
				
				// добавляем новые поля по необходимости
				$html .= '<div>';
				$html .= 'Введите тираж:<br>';
				$html .= '<input type="text" name="quantity" value="">';
				$html .= '</div>';

				//$html .= $this->get_dop_inputs_for_services($_POST['id_uslugi'], $_POST['id_dop_data'])

				$html .= $this->get_empty_dop_inputs_form($_POST['id_uslugi']);

				$html .= '<div>';
				$html .= 'Введите ТЗ / комментарии к услуге:<br>';
				$html .= '<textarea name="tz"></textarea>';
				$html .= '</div>';


				// передаём название метода, который будет обрабатывать данную форму
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_3">';
				// чтобы узнать имя пользователя подключам класс Managers
				include_once './libs/php/classes/manager_class.php';
				$html .= '<input type="hidden" name="author_name_added_services" value="'.Manager::get_snab_name_for_query_String($_SESSION['access']['user_id']).'">';
				$html .= '<input type="hidden" name="author_id_added_services" value="'.$_SESSION['access']['user_id'].'">';
				$html .= '</form>';
				echo '{"response":"show_new_window","title":"Шаг 2: Заполните необходимые поля", "html":"'.base64_encode($html).'"}';
				exit;
			}

			// ШАГ 3: проверка данных, коррекция цены
			protected function add_new_usluga_form_steep_3_AJAX(){ 
				$this->TZ = $_POST['tz'];
				unset($_POST['tz']);
				unset($_POST['AJAX']);// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 

				global $mysqli;
				$html = '';
				// СОБИРАЕМ СКРЫТУЮ ФОРМУ
				$html .= '<form>';

				// перебираем POST массив для передачи их далее
				$f = 0;
				foreach ($_POST as $key => $value) {
					if($key!='dop_inputs'){
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}else if(!$f){
						foreach ($_POST['dop_inputs'] as $key1 => $value) {
							$html .= '<input type="hidden" name="dop_inputs['.$key1.']" value=\''.$value.'\'>';
							// $html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
						}
						$f++;	
					}else{

					}
				}

				
				// ВЫВОДИМ ВВЕДЁННУЮ ИНФОРМАЦИЮ ДЛЯ ПРОВЕРКИ
				$html .= '<table id="check_input_iformation">';
				$html .= '<tr><td>Услуга:</td><td>'.$_POST['service_name'].'</td></tr>';
				$html .= '<tr><td>Тираж:</td><td>'.$_POST['quantity'].'</td></tr>';
				// $this->Service_price_in = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_in']:$_POST['price_in'];
				$this->Service_price_in = $_POST['price_in'];
				// $this->Service_price_out = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_out']:$_POST['price_in'];
				$this->Service_price_out = 0; // для услуг добавленных в заказ показываем исходащую цену = 0, т.е. их сибистоимость вычитается из маржинальности
				
				
				$html .= '<tr><td>$ входящая '.(($_POST['for_how'] == "for_one")?'(за ед.)':'(за тир.)').':</td><td><span>'.(($this->user_access==1 || $this->user_access==8)?'<input type="text" name="price_in" value="'.$this->Service_price_in.'">':$this->Service_price_in).'</span>р.</td></tr>';
				

				$html .= '<tr><td>$ исходащая:</td><td><span>'.$this->Service_price_out.'</span>р</td></tr>';
				if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){// если есть доп поля
					// для представления пользователю информациии по доп полям в читабельном виде
					// получаем список всех полей dop_inputs
					$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
					$result = $mysqli->query($query) or die($mysqli->error);
					$iputs_all_arr = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$iputs_all_arr[$row['name_en']] = $row;
						}
					}

					$html .= '<tr><td colspan="2" style="text-align:center">Дополнительные поля</td></tr>';
					foreach ($_POST['dop_inputs'] as $key => $value) {
						$html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
					}				
				}

				$html .= '<table>';
				$html .= '<div>';
				$html .= 'Введите ТЗ / комментарии к услуге:<br>';
				$html .= '<textarea name="tz">'.$this->TZ.'</textarea>';
				$html .= '</div>';

				// передаём название метода, который будет обрабатывать данную форму
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_end">';

				$html .= '</form>';

				echo '{"response":"show_new_window","title":"Шаг 3: Проверка введённых данных","html":"'.base64_encode($html).'"}';
				exit;
				
			}
			// ОБРАБОТКА ДАННЫХ ИЗ ШАГ 3
			protected function add_new_usluga_end_AJAX(){
				
				global $mysqli;
				// ob_start();
				// echo '<pre>';
				// print_r($_POST['dop_inputs']);
				// echo '</pre>';
				    	
				// $content = ob_get_contents();
				// ob_get_clean();
				// $html =$content;
				// echo $html;
				$query ="INSERT INTO `".CAB_DOP_USLUGI."` SET ";
				$query .= "`dop_row_id` = '".$_POST['id_dop_data']."',";
				$query .= "`uslugi_id` = '".$_POST['id_uslugi']."',";
				$query .= "`glob_type` = 'extra',";
				$query .= "`quantity` = '".$_POST['quantity']."',";
				$query .= "`price_in` = '".$_POST['price_in']."',";
				$query .= "`price_out` = '".$_POST['price_out']."',";
				$query .= "`performer` = '".$_POST['performer']."',";

				$query .= "`for_how` = '".$_POST['for_how']."',";
				$query .= "`tz` = '".base64_encode($_POST['tz'])."',";
				// собираем JSON по доп полям
				if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){
					$json_arr = array();
					foreach ($_POST['dop_inputs'] as $key => $value) {
						$json_arr[$key] = base64_encode($value);
					}
					$query .= "`print_details_dop` = '".json_encode($json_arr)."',";
				}

				$query .= "`author_name_added_services` = '".$_POST['author_name_added_services']."',";
				$query .= "`author_id_added_services` = '".$_POST['author_id_added_services']."',";			
				$query .= "`change_log` = 'Добавил ".$_POST['author_name_added_services']." ".date("d.m.Y H:m:s")."'";



				$result = $mysqli->query($query) or die($mysqli->error);
				// запоминаем новый id
				$insert_id = $mysqli->insert_id;

				// собираем HTML
				$html = '<tr class="not_provided" data-id="'.$insert_id.'">
						<td></td>
						<td><span class="postfaktum_non_calculate">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_in">0</span></td>
						<td><span class="postfaktum_non_calculate">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_out">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>
						<td class="postfaktum"></td><td class="postfaktum added_postfactum">'.$_POST['service_name'].'</td>
						<td class="postfaktum added_postfactum"><span>'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']:'  -  ').'</span></td>
						<td class="postfaktum added_postfactum"><span class="service_price_in_postfactum">'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']*$_POST['price_in']:$_POST['price_in']).'</span>р</td>
						<td class="postfaktum"><span data-id="'.$insert_id.'" class="on_of">+</span></td><td></td></tr>';
				
				echo '{"response":"OK","function":"add_new_usluga_end","html":"'.base64_encode($html).'","function2":"reload_order_tbl"}';
				exit;
			}

			////////////////////////////////
			protected function get_size_Array_Database($art_id){
				// выгружает данные запроса в массив
				global $mysqli;
				$query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
				// echo $query;
				$arr = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				$this->info = 0;
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$arr[] = $row;
					}
				}
				return $arr;
			}
			protected function get_size_table_for_dop_tex_info($position, $enable_edit_zapas = 0){
				if($position['art_id'] == 0){return '';}
				// получаем размеры
				$size_arr = $this->get_size_Array_Database($position['art_id']);

				// выборка данных о введённых ранее размерах из строки JSON 
				if(trim($position['tirage_json']) == '{}'){
					$tirage_json[$size_arr[0]['id']]['dop'] = $position['zapas'];
					$tirage_json[$size_arr[0]['id']]['tir'] = $position['quantity'];

					global $mysqli;
				
					$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
						`tirage_json` =  '".json_encode($tirage_json)."' 
						, `zapas` = '".(int)$position['zapas']."'
						WHERE  `id` ='".(int)$position['id_dop_data']."';";

					$result = $mysqli->query($query) or die($mysqli->error);
					// echo '{"response":"OK"}';
				}else {
					$tirage_json = json_decode($position['tirage_json'], true);
				}


				$html = "";
				if(count($size_arr)==0){
					$html = "Размеры по данному артикулу отсутствуют. Обратитесь к администратору.";
					return $html;
				};

				$html .= '<div class="green_inform_block">Размеры:</div>';
				// собираем таблицу с размерами
				$html .= '
					<div class="size_card" id="edit_size_dop_tex_info">
					<div id="json_code_for_size">'.$position['tirage_json'].'</div>
					<table>
						<tr>
							<th>Размер</th>
							<th>тираж</th>
							<th>запас</th>
						</tr>
				';
				
				
				
				// перебираем строки размерной таблицы
				foreach ($size_arr as $size) {
					$tirage = (isset($tirage_json[$size['id']]['tir']))?$tirage_json[$size['id']]['tir']:0;
					$value_dop = (isset($tirage_json[$size['id']]['dop']))?$tirage_json[$size['id']]['dop']:0;
					//$no_edit_class = (($size['ostatok_free']=='0' && $summ_ostatok>=$summ_zakaz && $pod_zakaz!=1)?' input_disabled':'');
					$readonly = (($enable_edit_zapas == '0')?' disabled':'');
					
					// $html .= $this->print_arr($position);
					$html .= '
							<tr class="size_row_tbl">
								<td>'.$size['size'].'</td>
								<td>'.$tirage.'</td>
								<td><input type="text" data-dop="dop" data-id_dop_data="'.$position['id_dop_data'].'" class="val_tirage_dop" data-id_size="'.$size['id'].'"  value="'.$value_dop.'" '.$readonly.'></td>
							</tr>
					';
				}
				$html .= '</table></div>';
				if($enable_edit_zapas){
					$html .= '<div id="change_pz_npz">';
					// $html .= $position['print_z'];
						$html .= 'Печатать запас? &nbsp;';
						$html .= '<span class="btn_var_std '.(($position['print_z'] == 1)?'checked':'').'" data-id_dop_data="'.$position['id_dop_data'].'" name="pz">ПЗ</span>';
						$html .= '<span class="btn_var_std '.(($position['print_z'] == 0)?'checked':'').'" data-id_dop_data="'.$position['id_dop_data'].'" name="npz">НПЗ</span>';
					$html .= '</div>';	
				}
				

				// $html .= '
				// 	<div class="sevrice_button_size_table">
				// 		<span onclick="chenge_hidden_input_status(\'0\',this);" class="btn_var_std '.(($pod_zakaz==1)?'checked':'').'" name="order">под заказ</span>
				// 		<span onclick="chenge_hidden_input_status(\'1\',this);" class="btn_var_std '.(($pod_zakaz==0)?'checked':'').'" name="reserve">под резерв</span>
				// 	</div>
				// 	';

				return $html;

			}

			protected function get_size_table_read_AJAX(){
				$html = "";
				$position = $this->get_cab_position_Database($_POST['position_id']);
				$html .= $this->get_size_table_read($position[0]);

				echo '{"response":"replace_width" ,"html": "'.base64_encode($html).'"}';
				exit;

			}

			protected function get_size_table_read($position){
				$html = "";

				// $html .= $this->print_arr($position);
				if(!isset($position['art_id']) || isset($position['art_id']) && $position['art_id'] == 0){
					$html .= $this->print_arr($position);
					return $html;
				}
				// получаем размеры
				$size_arr = $this->get_size_Array_Database($position['art_id']);

				// $html .= $this->print_arr($size_arr);
				// выборка данных о введённых ранее размерах из строки JSON 
					
				if(trim($position['tirage_json']) == '{}'){
					$tirage_json[$size_arr[0]['id']]['dop'] = $position['zapas'];
					$tirage_json[$size_arr[0]['id']]['tir'] = $position['quantity'];

					global $mysqli;
				
					$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
						`tirage_json` =  '".json_encode($tirage_json)."' 
						, `zapas` = '".(int)$position['zapas']."'
						WHERE  `id` ='".(int)$position['id_dop_data']."';";

					$result = $mysqli->query($query) or die($mysqli->error);
					// echo '{"response":"OK"}';
				}else {
					$tirage_json = json_decode($position['tirage_json'], true);
				}
				// $html .= $position['tirage_json'];

				
				if(count($size_arr)==0){
					$html = "Размеры по данному артикулу отсутствуют. Обратитесь к администратору.";
					return $html;
				};

				//$html .= '<div class="green_inform_block">Размеры:</div>';
				// собираем таблицу с размерами
				$html .= '
					<div class="size_card" id="edit_size_dop_tex_info">
					<div id="json_code_for_size">'.$position['tirage_json'].'</div>
					
				';
				// перебираем строки размерной таблицы
				foreach ($size_arr as $size) {
					$tirage = (isset($tirage_json[$size['id']]['tir']))?$tirage_json[$size['id']]['tir']:0;
					$value_dop = (isset($tirage_json[$size['id']]['dop']))?$tirage_json[$size['id']]['dop']:0;
					$size_name = (trim($size['size']) != '')?$size['size']:'размер не указан';
					if($tirage != 0 || $value_dop != 0){
						$html .= '<div>';
							$html .= $size_name.': &nbsp;'.$tirage;
							if($this->user_access != 9){
								if($value_dop != 0){
									$html .= ' + '.$value_dop;
								}
							}
							$html .= ' шт.';
						$html .= '</div>';
					}

				}
				$html .= '</div>';
				
				return $html;

			}

			// сохранить размерную таблицу
			protected function save_change_pz_npz_AJAX(){
				global $mysqli;
				
				$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
					`print_z` =  '".$_POST['val']."' 
					WHERE  `id` ='".(int)$_POST['id_dop_data']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}
			// сохранить ПЗ / НПЗ
			protected function save_edit_size_dop_tex_info_AJAX(){
				global $mysqli;
				
				$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
					`tirage_json` =  '".$_POST['Json']."' 
					, `zapas` = '".(int)$_POST['zapas']."'
					WHERE  `id` ='".(int)$_POST['id_dop_data']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// контент для окна доп/тех инфо
			protected function get_dop_tex_info_AJAX(){
				$html = '';

				$enable_edit = ((isset($_POST['order_status']) && $_POST['order_status'] != 'in_work' && $_POST['order_status'] != 'paused'  && $_POST['order_status'] != 'cancelled' && $_POST['order_status'] != 'shipped'))?1:0;
				
				if($this->user_access != 5){
					$enable_edit = 1;
				}
				// запрет для буха
				if($this->user_access == 2){
					$enable_edit = 0;					
				}
				$readonly = ($enable_edit == 0)?' disabled':'';


				// получаем информацию по позиции
				// positions_rows_Database
				$position = $this->get_cab_position_Database($_POST['position_id']);
				// подгружаем форму по резерву
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Информация по позиции</div>';
				$html .= 'Резерв<br>';
				// $html .= $this->print_arr($position);
				$html .= '<input type="text" class="rezerv_info_input" name="rezerv_info" data-id="'.$_POST['position_id'].'" data-document_id="'.$_POST['document_id'].'" data-cab_dop_data_id="'.$_POST['id_dop_data'].'" value="'.base64_decode($position[0]['number_rezerv']).'">';
				$html .= '</div>';

				// выгрузка информации по печати запаса
				$html .= '<div class="container_form">';
					$html .= $this->get_size_table_for_dop_tex_info($position[0],$enable_edit);
				$html .= '</div>';

				// подгружаем форму по заполнению поля логотип для всех услуг
				
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Логотип (использовать поле при условии, что логотип клиента одинаковый для всех услуг)</div>';
					$html .= '<table id="save_logotip_for_all_services_tbl">';
					$html .= '<tr>';
						$html .= '<td>Название</td>';
						if ($enable_edit) {
							$html .= '<td colspan="2">Применить название для:</td>';
						}
					$html .= '</tr>';
					$html .= '<tr>';
							// собираем строку для передачи данных из POST массива в теги input
						$data_str = '';
						if(isset($_POST)){
							unset($_POST['AJAX']);
							foreach ($_POST as $key => $value) {
								$data_str .= ' data-'.$key.'="'.$value.'"';
							}
						}
							// добавляем кнопки
						$html .= '<td><input type="text" class="save_logotip_for_all_services" name="logotip" data-cab_dop_data_id="'.$_POST['id_dop_data'].'" value="" '.$readonly.'></td>';
						if ($enable_edit) {
							$html .= '<td><input type="button"  data-document_id="'.$_POST['document_id'].'" name="" '.$data_str.' id="save_logotip_for_all_position" value="Всех услуг в списке этой позиции"></td>';
							$html .= '<td><input type="button"  data-document_id="'.$_POST['document_id'].'" name="" '.$data_str.' id="save_logotip_for_all_order" value="Всех услуг в этом заказе"></td>';
						}
					$html .= '</tr></table>';
				$html .= '</div>';
				

				#######################################

				// подгружаем таблицу услуг
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Услуги</div>';		
						
				$this->uslugi = $this->get_order_dop_uslugi($_POST['id_dop_data']); 
				
				if(count($this->uslugi)){ // если услуги прикреплены
					/*
					// делаем запрос по услугам в базу
					// if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
					// 	$this->Services_list_arr = $this->get_all_services_Database();
					// }
					*/

					// собираем html форму
					$html .= '<table id="services_listing"><tr>';
					$html .= '<tr><th>Название услуги</th><th>Информация для заполнения</th></tr>';
					$html .= '<td id="services_listing_each"><ul>';
					
					// перебираем услуги и вы
					$first_right_content = '';// контент по первой услуге
					$n = 0; // порядковый номер
					foreach ($this->uslugi as $usluga) {
						$no_active = ($usluga['on_of']=='0')?' no_active':'';	// услуга отключена / включена из выполнения в окне финансовой детализации
						$this->Service = $usluga; // по сути строка из CAB_DOP_USLUGI			
						$html .= '<li  data-cab_dop_data_id="'.$_POST['id_dop_data'].'" data-uslugi_id="'.$usluga['uslugi_id'].'" data-order_status="'.$_POST['order_status'].'"  data-dop_usluga_id="'.$usluga['id'].'" data-id_tz="tz_id_'.$n.'" class="lili '.$usluga['for_how'].' '.(($n==0)?'checked':'').''.$no_active.'" data-id_dop_inputs="'.addslashes($usluga['print_details_dop']).'">'.$usluga['name'].'</li>';
						if($n == 0){
							// запоминаем тз по первой услуге
							$first_right_content .= $this->get_dop_inputs_for_services($usluga['uslugi_id'],$usluga['id'], $enable_edit);						
						}
						$n++;
					}
					$html .= '</ul></td>';
					// про

					// $html .= '<td id="content_dop_inputs_and_tz"><span class="title_dop_inputs_info">Выберите услугу</span></td>';
					$html .= '<td id="content_dop_inputs_and_tz">'.$first_right_content.'</td>';

					$html .= '</table>';
				}else{
					$html .= 'услуги не прикреплены.... и это оооочень странно. Обратитесь к Админу.';
				}

				$html .= '</div>';

				################################################

				// подгружаем комментарии для позиции 
				global $PositionComments;
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Переписка</div>';
				$html .= '</div>';
				$html .= '<div class="container_form">';
				
				$html .= $PositionComments -> get_comment_for_position_without_Out();
				$html .= '</div>';

				$html .= '<form><input type="hidden" name="AJAX" value="to_closed_this_window"></form>';
				

				// Вывод
				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				exit;
			}	

			protected function to_closed_this_window_AJAX(){
				echo '{"response":"OK"}';
				exit;
			}

			// включение отключение услуги
			protected function change_service_on_of_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`on_of` =  '".(int)$_POST['val']."' 
					WHERE  `id` ='".$_POST['id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK","function":"reload_order_tbl"}';
				exit;
			}

			// редактирование поля ТЗ к услуге
			protected function save_tz_info_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`tz` =  '".base64_encode($_POST['text'])."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// редактирование поля логотип к услуге
			protected function save_logotip_info_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`logotip` =  '".$_POST['text']."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// сохранение dop_inputs, поля хранятся в json 
			protected function save_dop_inputs_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`print_details_dop` =  '".$_POST['Json']."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			// сохранение поля резерв
			protected function save_rezerv_info_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`number_rezerv` =  '".base64_encode($_POST['text'])."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				exit;
			}

			protected function get_dop_inputs_for_services_AJAX(){
				// для вызова AJAX
				if(isset($_POST['uslugi_id'])){
					$html = $this->get_dop_inputs_for_services($_POST['uslugi_id'],$_POST['dop_usluga_id'],((isset($_POST['order_status']) && $_POST['order_status'] != 'in_work' && $_POST['order_status'] != 'paused'  && $_POST['order_status'] != 'cancelled' && $_POST['order_status'] != 'shipped'))?1:0);
				}else{
					return 'Укажите id услуги';
				}
				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				exit;
			}

			// ролучаем dop_inputs
			protected function get_dop_inputs_for_services($id, $dop_usluga_id, $enable_edit = 0){
				global $mysqli;

				// допуски на редактирование
				$enable_edit = ((isset($_POST['order_status']) && $_POST['order_status'] != 'in_work' && $_POST['order_status'] != 'paused'  && $_POST['order_status'] != 'cancelled' && $_POST['order_status'] != 'shipped'))?1:0;
				
				if($this->user_access != 5){
					$enable_edit = 1;
				}
				// запрет для буха
				if($this->user_access == 2){
					$enable_edit = 0;					
				}
				$readonly = ($enable_edit == 0)?' disabled':'';

				
				include_once(ROOT."/libs/php/classes/print_calculators_class.php");
				// запрашиваем информацию по ТЗ и , если нужно
				if(!isset($this->Service)){ // если нам ничего не известно по строке из CAB_DOP_USLUGI
					$query = "SELECT * FROM ".CAB_DOP_USLUGI." WHERE `id` = '".$dop_usluga_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					$this->Service = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->Service = $row;
						}
					}
				}
				
				// если у нас есть информация в поле $this->Service['print_details'] - декодируем её в читабельный вид
				if(trim($this->Service['print_details'])!=''){
					include_once './libs/php/classes/agreement_class.php';
					$this->Service['print_details_read'] = '<div><span>Данные из калькулятора:</span><br><div class="calculator_info">'.printCalculator::convert_print_details($this->Service['print_details']) .'</div></div>';
				}else{
					$this->Service['print_details_read'] = '';
				}

				// получаем инфу и настройки по данной услуге
				if(!isset($this->All_Services_arr)){
					$this->All_Services_arr = $this->get_all_services_names_Database();
				}


				$this->iputs_id_Str = isset($this->All_Services_arr[$id]['uslugi_dop_inputs_id'])?$this->All_Services_arr[$id]['uslugi_dop_inputs_id']:'';
				$this->Service_logotip_on = isset($this->All_Services_arr[$id]['logotip_on'])?$this->All_Services_arr[$id]['logotip_on']:'';
				$this->Service_show_status_film_photos = isset($this->All_Services_arr[$id]['show_status_film_photos'])?$this->All_Services_arr[$id]['show_status_film_photos']:'';						
				$this->maket_old_true_for_Service = isset($this->All_Services_arr[$id]['maket_old_true'])?$this->All_Services_arr[$id]['maket_old_true']:'';
						// echo $row['logotip_on'];




				//////////////////////////
				//	СЛЕДУЮЩИЕ 2 запроса В БЛИЖАЙШЕМ БУДУЩЕМ нужно сократить до одного !!!!!!!!!!!!
				//////////////////////////
				// запрашиваем список полей предназначенных для этой услуги
				$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
				$this->iputs_arr = array();
				if(trim($this->iputs_id_Str)!=''){
					$result = $mysqli->query($query) or die($mysqli->error);				
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->iputs_arr[] = $row;
						}
					}
				}

				// получаем список всех полей
				$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
				$result = $mysqli->query($query) or die($mysqli->error);
				$iputs_all_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$iputs_all_arr[] = $row;
					}
				}

				// получаем  json
				$this->print_details_dop_Json = (trim($this->Service['print_details_dop'])=="")?'{}':$this->Service['print_details_dop'];
				// декодируем json  в массив
				$this->print_details_dop = json_decode($this->print_details_dop_Json, true);


				// перебор полей указанных в услуге
				$html = '';
				// добавляем скрытую json строку для обработке в JS
				$html = '<div id="dop_input_json">'.$this->print_details_dop_Json.'</div>';
				
				//$html .= $this->print_arr($this->print_details_dop);

				// добавляем информацию из калькулятора.... если есть
				$html .= $this->Service['print_details_read'];
				foreach ($this->iputs_arr as $key => $input) {
					//echo $input['name_ru'];
					$html .= $input['name_ru'].':<br>';
					//if($input['name_en'] == "tip_pechati"){$html .= '<div>'.$this->print_details_dop[$input['name_en']].'</div>';}
					if($input['type']=="text"){
							if(isset($this->print_details_dop[$input['name_en']])){
								$text = $this->print_details_dop[$input['name_en']];
								$text = htmlspecialchars(base64_decode($text),ENT_QUOTES);
							}else{
								// $html .= '!= '.$input['name_en'];
								$text = '';
							}
							$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value=\''.$text.'\'" '.$readonly.'></div>';
							
					}else{
							$html .= 'данный тип поля пока что не предусмотрен';
					}
					// удаляем $this->print_details_dop[$input['name_en']]
					unset($this->print_details_dop[$input['name_en']]);				
				}

				
				//перебираем оставшиеся значения из json .... они могут остаться, 
				// если админы что-то наменяли и открепили доп поля от услуги 
				foreach ($iputs_all_arr as $key => $input) {
					if(isset($this->print_details_dop[$input['name_en']])){
						$html .= $input['name_ru'].' * <span class="delete_dop_input_for_admin">(было удалено Админом из списка обязательных полей для услуги)</span><br>';
						if($input['type']=="text"){
								$text = isset($this->print_details_dop[$input['name_en']])?$this->print_details_dop[$input['name_en']]:'';
								$text = htmlspecialchars(base64_decode($text),ENT_QUOTES);
								
								$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'" '.$readonly.'></div>';
						}else{
								$html .= 'данный тип поля пока что не предусмотрен';
						}	
					}
				}
				
				// подключаем поле логотип, если оно включено в админке или уже что-то содержит
				if($this->Service['logotip']!='' || trim($this->Service_logotip_on)=="on"){
					$html .='<div>Логотип<br><input type="text" class="save_logotip" name="logotip" value="'.addslashes($this->Service['logotip']).'" '.$readonly.'></div>';
				}

				// подключаем поле плёнки, если оно включено в админке 
				if(trim($this->Service_show_status_film_photos)=="on"){
					$html .='<div>Плёнки/Клише<br>';
					$html .= $this->get_statuslist_film_photos($this->Service['film_photos_status'],$this->Service['id']);
					// $html .='<textarea class="save_logotip" name="logotip">'.$this->Service['logotip'].'</textarea>';
					$html .='</div>';
				}

				// подключаем поле путь к макету
				if (trim($this->maket_old_true_for_Service)=="on") {
					$html .='<div>Путь к макету (к старому):<br>';
					$html .= '<div><input type="text" name="the_url_for_layout" placeholder="заполнить при необходимости" class="save_the_url_for_layout" value="'.base64_decode($this->Service['the_url_for_layout']).'" '.$readonly.'></div>';
					// $html .='<textarea class="save_logotip" name="logotip">'.$this->Service['logotip'].'</textarea>';
					$html .='</div>';
				}

				$html .='<div>Комментарии для исполнителя '.(isset($this->performer[$this->Service['performer']])?'"'.$this->performer[$this->Service['performer']].'"':'').'<br><textarea class="save_tz" name="tz" '.$readonly.'>'.str_replace('<br>', "", base64_decode($this->Service['tz'])).'</textarea></div>';

				return $html;
			}		

				/////////////////////////////////////////
				//	методы для работы AJAX -- START -- //
				/////////////////////////////////////////
					// получаем пустую форму с dop_inputs для прикрепляемой услуги
					protected function get_empty_dop_inputs_form($id){
						global $mysqli;
						// получаем id полей для этой услуги
						$query = "SELECT `name`,`uslugi_dop_inputs_id`,`price_in`,`price_out`,`for_how` FROM ".OUR_USLUGI_LIST." WHERE `id` = '".$id."'";
						$result = $mysqli->query($query) or die($mysqli->error);
						$this->iputs_id_Str = '0';
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$this->iputs_id_Str = $row['uslugi_dop_inputs_id'];
								$this->AddedServiceName = $row['name'];
								$this->Service = $row;
							}
						}

						// получаем массив dop_inputs
						$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
						$this->iputs_arr = array();
						if(trim($this->iputs_id_Str)!=''){
							$result = $mysqli->query($query) or die($mysqli->error);				
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									$this->iputs_arr[] = $row;
								}
							}
						}
						$html = '';


						// добавляем поля dop_inputs
						foreach ($this->iputs_arr as $key => $input) {
							//echo $input['name_ru'];
							$html .= $input['name_ru'].'<br>';
							if($input['type']=="text"){
								$html .= '<div><input type="'.$input['type'].'" name="dop_inputs['.$input['name_en'].']" placeholder="" value=""></div>';
							}
						}
						// добавляем скрытые поля 
						$html .= '<input type="hidden" value="'.$this->Service['price_in'].'"  name="price_in">';
						$html .= '<input type="hidden" value="'.$this->Service['price_out'].'"  name="price_out">'; // для услуг добавленных в заказ исходащая цена = 0, т.е. их сибистоимость вычитается из маржинальности
						$html .= '<input type="hidden" value="'.$this->Service['for_how'].'"  name="for_how">';


						return $html;
					}
					// возвращает список услуг для выбора
					protected function get_uslugi_list_Database_Html($id=0,$pad=30){	
						global $mysqli;
						$html = '';
						
						$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
						$result = $mysqli->query($query) or die($mysqli->error);
						

						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
								if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
									# Это услуги НЕ из КАЛЬКУЛЯТОРА
									// запрос на детей
									$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
									
									$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
									
									// присваиваем конечным услугам класс may_bee_checked
									$html.= '<div data-performer="'.$row['performer'].'" data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
								}else{
									# Это услуги из КАЛЬКУЛЯТОРА
									// запрос на детей
									$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));

									$price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
									// присваиваем конечным услугам класс may_bee_checked
									$html.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
								}
							}
						}
						return $html;
					}
				///////////////////////////////////////
				//	методы для работы AJAX -- END -- //
				///////////////////////////////////////
		
		//////////////////////////////////////////////////////////
		//   -----  END  -----  МЕТОДЫ AJAX  -----  END  -----  //
		//////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////////////
		//	-----  START  -----  General Template (общие для всех шаблоны)  -----  START  -----
		///////////////////////////////////////////////////////////////////////////////////////
			protected function history_Template(){
				echo $this->wrap_text_in_warning_message('Сюда попадают заказы, которые были закрыты и по которым была произведена выплата менеджеру.');
			}
			
		////////////////////////////////////////////////////////////////////////////////////
		//   -----  END  -----  General Template (общие для всех шаблоны) -----  END  -----
		////////////////////////////////////////////////////////////////////////////////////
		
		// подключение класса форм
		protected function connection_form_class(){
			if(!isset($this->FROM) || empty($this->FROM)){
				$this->FORM = new Forms();
			}
		}
			


		/*
			декодируем поле json для некаталога в читабельный вид
			получаем из json описания некаталожного товара всю содержащуюся там информацию
		*/		
		protected function decode_json_no_cat_to_html($position,$template_edit = 0){
			// $position - массив содержащий:
			// $position['no_cat_json']  - Json с описанием из формы
			// $position['type'] - тип товара для запроса по полям


			// подключаем класс форм
			$this->connection_form_class();

			// массив со списком разрешённых для вывода в письмо ключей
			// $this->FORM->send_info_enabled;

			// получаем json с описанием продукта
			$dop_info_no_cat = ($position['no_cat_json']!='')?json_decode($position['no_cat_json'], true):array();
			
			
			$html = '';
			// если у нас есть описание заявленного типа товара
			$names = $this->FORM->get_names_form_type($position['type']); // массив описания хранится в классе форм
			$html .= '<div class="get_top_funcional_byttun_for_user_Html">';
			switch ($template_edit) {
				case '1':
					$html .= '<div class="table" id="edit_tz_no_cat_for_order_width_snab_and_admin">';
					foreach ($this->FORM->send_info_enabled as $name_en_from_the_mold) {
						//$html .= $value.'<br>$dop_info_no_cat[$value] = '.$dop_info_no_cat[$value];
						if(!isset($dop_info_no_cat[$name_en_from_the_mold])){continue;}
						if(!isset($names[$name_en_from_the_mold]['name_ru'])){continue;}
						$html .= '<div class="row">';
							$html .= '<div class="cell">'.$names[$name_en_from_the_mold]['name_ru'].':</div>';
							$html .= '<div class="cell"><input type="text" name="'.$name_en_from_the_mold.'" value="'.$dop_info_no_cat[$name_en_from_the_mold].'"></div>';
						$html .= '</div>';									
					}
					$html .= '</div>';
					break;
				
				default:
					foreach ($this->FORM->send_info_enabled as $name_en_from_the_mold) {
					//$html .= $value.'<br>$dop_info_no_cat[$value] = '.$dop_info_no_cat[$value];
						if(!isset($dop_info_no_cat[$name_en_from_the_mold])){continue;}
						if(!isset($names[$name_en_from_the_mold]['name_ru'])){continue;}
						$html .= '
							<div>
								'.$names[$name_en_from_the_mold]['name_ru'].': &nbsp;
								'.$dop_info_no_cat[$name_en_from_the_mold].'<br>
							</div>
						';				
					}

					break;
			}
			
			$html .= '</div>';
			return $html;
		}

		// вывод кнопки доп/тех инфо, с подсветкой в случае наличия в ней сообщиений в переписке по позиции
		protected function grt_dop_teh_info($value,$document){
			// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
			// думаю есть смысл хранения в json 
			// обязательные поля:
			// {"comments":" ","technical_info":" ","maket":" "} ???? 

			// если есть информация
			$no_empty_class = (Comments_for_order_dop_data_class::check_the_empty_position_coment_Database($value['id']))?' no_empty':'';
			// выводим статус заказа, если есть
			$order_status = (isset($this->Order['global_status'])?$this->Order['global_status']:'');
			$html = '<td>
					<div class="dop_teh_info '.$no_empty_class.'" data-order_status="'.$order_status.'" data-id_dop_data="'.$this->id_dop_data.'" data-id="'.$value['id'].'" data-query_num="'.$this->query_num.'" data-document_id="'.$document['id'].'" data-position_item="'.$this->position['sequence_number'].'" data-order_num="'.$this->order_num.'" data-order_num_User="'.$this->order_num_for_User.'"  >доп/тех инфо</div>
					<div class="dop_teh_info_window_content"></div>
				</td>';

			return $html;
		}	

		// получаем контент для поля логотип
		protected function get_content_logotip($id_dop_data){
			// для полуяения поля логотип запрашиваем услуги
			$this->Services = $this-> get_order_dop_uslugi($id_dop_data);
			// перебираем поля и собираем контент по полю логотип
			$html = '';$n = 0;
			foreach ($this->Services as $num => $service) {
				if($service['logotip'] != ''){
					if($n){$html .= ', ';}
					$html .= $service['logotip'];
					$n++;
				}
			}
			if($html == ''){$html = '  -  ';}
			return $html;
		}

		// общет стоимости позиции
		protected function GET_PRICE_for_position($position){
			////////////////////////////////////
			//	Расчёт стоимости позиций START  
			////////////////////////////////////

				//ОБСЧЁТ ВАРИАНТОВ
				// получаем массив стоимости нанесения и доп услуг для данного варианта 
				$dop_usl = $this-> get_order_dop_uslugi($position['id_dop_data']);

				// выборка только массива стоимости печати
				$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
				// выборка только массива стоимости доп услуг
				$dop_usl_no_print = $this-> get_dop_uslugi_no_print_type($dop_usl);

				
				// стоимость товара
				// проверка на наличе скидки
				$price_out = $position['price_out'];
				if($position['discount'] <> 0){
					$price_out = (($price_out/100)*(100 + $position['discount']));
				}
				// echo $price_for_the_goods.'<br>';
				$price_for_the_goods = $price_out * ($position['quantity'] + $position['zapas']);
				
				$this->Price_for_the_goods = $this->money_format( $price_for_the_goods );
				
				// стоимость услуг печати
				$price_of_printing = $this -> calc_summ_dop_uslug($dop_usl_print,(($position['print_z']==1)?$position['quantity']+$position['zapas']:$position['quantity']));
				$this->Price_of_printing = $this->money_format( $price_of_printing );
				
				// стоимость услуг не относящихся к печати
				$price_of_no_printing = $this->calc_summ_dop_uslug($dop_usl_no_print,(($position['print_z']==1)?$position['quantity']+$position['zapas']:$position['quantity']));
				$this->Price_of_no_printing = $this->money_format( $price_of_no_printing );
				
				// общаяя цена позиции включает в себя стоимость услуг и товара
				$this->price_for_the_position = $price_for_the_goods + $price_of_printing + $price_of_no_printing;
				$this->Price_for_the_position = $this->money_format( $this->price_for_the_position );
					

			////////////////////////////////////
			//	Расчёт стоимости позиций END
			////////////////////////////////////
		}

		// денежный формат
		protected function money_format($number){
			return number_format($number, 2, '.', ' ');
		}

		// преобразует статус снабжения в читабельный вид
		protected function show_cirilic_name_status_snab($status_snab){
			// if(substr_count($status_snab, '_pause')){
			// 	$status_snab = 'На паузе';
			// }
				
			$status_snab = $this->POSITION_NO_CATALOG->get_name_group($status_snab);
			// echo '<pre>';
			// print_r($this->POSITION_NO_CATALOG->status_snab);
			// echo '</pre>';
						
			// if(isset($this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'])){
			// 	$status_snab = $this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'];
			// }else{
			// 	$status_snab;
			// }
			return $status_snab;
		}

		//	оборачивает в div warning_message
		protected function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}
		
		// определяем поставщика каталожной продукции по номеру артикула
		protected function get_supplier_name($article){
			$html = '';
			global $suppliers_names_by_prefix_for_get_name;		   
			$prefix = substr($article,0,2);
			if(isset($suppliers_names_by_prefix_for_get_name[$prefix])){	
				$html = $suppliers_names_by_prefix_for_get_name[$prefix];
			}else{
				$html = '<span class="greyText">не требуется</span>';
			}
			return $html;
		}

		// подсчет суммы доп услуг или печати
		// на вход подаётся результат работы get_dop_uslugi_print_type() 
		// или get_dop_uslugi_no_print_type
		public function calc_summ_dop_uslug($arr,$tir=0){ // 
			$summ = 0;
			
			// перебираем массив услуг
			foreach ($arr as $key => $value) {
				// если услуга не была добавлена кем либо в заказ, при добавлении добавляется id автора
				if(!isset($value['author_id_added_services']) || $value['author_id_added_services'] <= 0){
					$price_out = $value['price_out'];
					if($value['discount'] <> 0){
						$price_out = (($price_out/100)*(100 + $value['discount']));
					}
					// суммируем её к общей сумме позиции
					if($value['for_how']=="for_one"){
						$summ += ($price_out*$value['quantity']);					
					}else{
						$summ += $price_out;					
					}
				}
			}
			return $summ;
		}

		// выбираем данные о стоимости печати 
		//на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		protected function get_dop_uslugi_print_type($arr){
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']=='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}
		
		// выбираем данные о стоимости доп услуг не относящихся к печати
		// на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_no_print_type($arr){			
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']!='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}

		// выбираем данные о доп услугах для варианта расчёта
		public function get_query_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data`
			global $mysqli;
			$query = "SELECT `".RT_DOP_USLUGI."`.*,`os__our_uslugi`.`name` FROM `".RT_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".RT_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		// выбираем данные о доп услугах для позиции
		public function get_order_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			$where = 0;
			// ВНИМАНИЕ !!!!!!!!!!
			// данный метод используется (специально заточен) для просчёта стоимости заказа и запроса
			// нежелательно его модифицировать для других нужд !!!!!
			global $mysqli;

			// определяем таблицу прикреплённых услуг
			$tbl = (isset($_GET['section']) && $_GET['section'] == 'requests')?RT_DOP_USLUGI:CAB_DOP_USLUGI;

			// при условии что:
			//	- это запросы 
			//  - работает снабжение
			//  - subsection = history
			//  -> выгружаем данные из таблиц для СНАБ HISTORY
			$tbl = ($this->user_access == 8 && isset($_GET['section']) && $_GET['section'] == 'requests' && isset($_GET['subsection']) && $_GET['subsection'] == 'history')?DOP_USLUGI_HIST:$tbl;
			/*
			  при изменении в админке по услуге ответственного за услугу, это изменение коснется только 
			  тех услуг, которые были созданы после этого изменения
			  чтобы изменения брались сразу из услуг нужно дополнить запрос выборкой по полю ,`os__our_uslugi`.`performer`
			*/

			$query = "SELECT 
			`".$tbl."`.*,
			`".OUR_USLUGI_LIST."`.`name`";
			$query .= (isset($_GET['section']) && $_GET['section'] == 'requests')?"":",DATE_FORMAT(`".$tbl."`.`date_work`,'%d.%m.%Y %H:%i')  AS `date_work`";
			$query .= (isset($_GET['section']) && $_GET['section'] == 'requests')?"":", DATE_FORMAT(`".$tbl."`.`date_ready`,'%d.%m.%Y %H:%i')  AS `date_ready`";
			$query .= " FROM `".$tbl."` 
			LEFT JOIN  `".OUR_USLUGI_LIST."` ON  `".OUR_USLUGI_LIST."`.`id` = `".$tbl."`.`uslugi_id`"; 
			
			$query .= " ".(($where)?'AND':'WHERE')." `".$tbl."`.`dop_row_id` = '".$dop_row_id."' ";
			if($tbl == CAB_DOP_USLUGI){
				if(isset($_POST['AJAX']) && $_POST['AJAX']=='get_a_detailed_article_on_the_price_of_positions'){

				}else{
					$query .= " AND `".$tbl."`.`on_of` <> '0'";
				}
			}
			$where = 1;

			// фильрация по услугам
			if($this->filtres_services != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_services;
				$where = 1;
			}

		

			$query .= " ORDER BY `".OUR_USLUGI_LIST."`.`id` ASC";

			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if(isset($_GET['show_the_query'])){
				echo $query.'<br>';
			}

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;


					// для зказа нам понадобится дополнительная информация по статусам 
					// если мы не в запросе
					if(isset($_GET['section']) && $_GET['section'] != 'requests'){
						// сортируем услугу в массив $this->Position_status_list по подразделениям
						$er = $this->performer[ $row['performer']];
						
						$new_arr['performer_status'] = $row['performer_status']; 
						$new_arr['service_name'] = $row['name'];
						$new_arr['id'] = $row['uslugi_id'];
						$new_arr['performer'] = $row['performer'];
						$new_arr['performer_comment'] = $row['performer_comment'];
						$new_arr['id_dop_uslugi_row'] = $row['id'];
						
						$this->Position_status_list[  $er  ][] = $new_arr;
					}
				}
			}
			return $arr;
		}

		static function show_order_num($key){
			if($key != 0){
				$i = 6 - strlen($key);
				// echo $i.'    */';
				$str = '';
				for ($t=0; $t < $i ; $t++) { 
					$str .='0';		}
				return $str.$key;	
			}else{
				return '<span style="color:grey">не заведён</span>';
			}
			
		}

		static function show_query_num($key){
			if($key != 0){
				$i = 6 - strlen($key);
				// echo $i.'    */';
				$str = '';
				for ($t=0; $t < $i ; $t++) { 
					$str .='';		}
				return $str.$key;	
			}else{
				return '<span style="color:grey">не заведён</span>';
			}
			
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_Database($id,$no_edit=0){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($no_edit == 1){
						$name= '<a href="./?page='.$_GET['page'].(isset($_GET['section'])?'&section='.$_GET['section']:'').(isset($_GET['subsection'])?'&subsection='.$_GET['subsection']:'').'&client_id='.$row['id'].'"><div class="dop__info js--client_td" data-id="'.$row['id'].'">'.$row['company'].'</div></a>';
					}else{
						$name = '<div class="attach_the_client" js--client_td data-id="'.$row['id'].'">'.$row['company'].'</div>';
					}
					
				}
			}else{
				$name = '<div'.(($no_edit==0)?' class="attach_the_client js--client_td add"':' class="dop__info js--client_td"').' data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_for_query_Database($id, $no_edit=0){
			global $mysqli;		
			$display = (isset($_GET['client_id']) && (int)$_GET['client_id'] > 0)?' style="display:none"':'';	
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$href = './?page='.$_GET['page'].(isset($_GET['section'])?'&section='.$_GET['section']:'').(isset($_GET['subsection'])?'&subsection='.$_GET['subsection']:'').'&client_id='.$row['id'];
					$name = '<td'.$display.' '.(($no_edit==0)?' class="attach_the_client js--client_td"':' class="dop__info js--client_td filter_class" data-href="'.$href.'"').' data-id="'.$row['id'].'">'.$row['company'].'</td>';
				}
			}else{
				$name = '<td'.$display.' '.(($no_edit==0)?' class="attach_the_client js--client_td add"':' class="dop__info js--client_td"').' data-id="0">Прикрепить клиента</td>';
			}
			return $name;
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_simple_Database($id,$no_edit=0){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = ''.$row['company'];
				}
			}else{
				$name = 'Клиент не определён';
			}
			return $name;
		}

		// выводит имя клиента в заказе, по ссылке в url добавляется id клиента
		protected function get_client_name_link_Database($id){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = 'Клиент не прикреплён';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<a data-id="'.$row['id'].'" '.((!isset($_GET['client_id']) || (isset($_GET['client_id']) && $_GET['client_id']!=$row['id']))?'href="'.$this->change_one_get_URL('client_id').$row['id'].'"':'').'>'.$this->str_reduce($row['company'],45).'</a>';
				}
			}
			return $name;
		}

		// обрезаем строку по количеству символов ...
		private function str_reduce($string,$num){
			// $num - ограничение по количеству символов
			if(mb_strlen($string, 'utf-8')>$num){
				// убираем html 
				// $string = strip_tags($str);
				// обрезаем
				$string = mb_substr($string, 0, $num);
				// убедимся, что текст не заканчивается восклицательным знаком, запятой, точкой или тире
				$string = rtrim($string, "!,.-");
				// находим последний пробел, устраняем его и ставим троеточие
				$string = substr($string, 0, strrpos($string, ' '));
				$string = $string.'...';
			}
			return $string;
		}

		// производим подмену одного из значений GET в URL
		private function change_one_get_URL($name){
			$f = 0;
			$str = '';

			foreach ($_GET as $key => $value) {
				if($key!=$name){
					if($f == 0){$str .= '?';}else{$str .= '&';}
					$str .= $key.'='.$value; 
					$f++;
				}
			}
			if($f == 0){$str .= '?';}else{$str .= '&';}
			$str .= $name.'='; 
			return $str;
		}		

		// получаем имя менеджера
		protected 	function get_manager_name_Database_Html($id,$no_edit=0){
		    global $mysqli;
		    $String = '<div'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</div>';
		   	$arr = array();
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $arr[$key] = $val;
				}
		    }		    
		    if(count($arr)){
		    		$name = $arr['name']; 
		    	if($arr['last_name'] != ''){
		    		$name = mb_substr($name, 0,2);
					$name = ($name!='')?$name.'.':'';
		    	}
		    	$String = '<div'.(($no_edit==0)?' class="attach_the_manager"':' class="dop_grey_small_info"').' data-id="'.$arr['id'].'">'.$arr['last_name'].' '.$name.'</div>';
		    }
		    return $String;
		}

		// получаем имена менеджеров для запроса
		protected function get_all_manager_name_Database_Html($Query,$no_edit=0){
			if($Query['manager_id'] != 0){
				return $this-> get_manager_name_Database_Html($Query['manager_id'],$no_edit);
			}else if( $Query['dop_managers_id'] == ''){
				$String = '<div'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</div>';
				return $String;
			}



			global $mysqli;
		    // обработка ситуации, когда прикреплено несколько гепотетических кандидатов на обработку запроса
		    // но запрос не закреплён пока что ни за кем конкретно
		   	$managers_arr = array();
		    $query="SELECT `id`,`name`,`last_name` FROM `".MANAGERS_TBL."`  WHERE `id` IN (".$Query['dop_managers_id'].")";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    
		    if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
				    $managers_arr[$row['id']] = $row;
				}
		    }	 

		    if(count($managers_arr)){
		    	// добавляем возможность редактирования списка для администратора
		    	if($this->user_access == 1){
		    		$String = '<div class="dop_grey_small_info attach_the_manager_any" data-client_id="'.$Query['client_id'].'"  data-row_id="'.$Query['id'].'">';
		    		$String .= '<div class="managers_id_str" style="display:none">'.$Query['dop_managers_id'].'</div>'; 
		    		// $String = '<div class="dop_grey_small_info">'.$this->print_arr($Query);
		    	}else{
		    		$String = '<div class="dop_grey_small_info attach_the_manager_any_black">';	
		    	}
		    	
		    		
		    	foreach ($managers_arr as $key => $manager) {
		    		$name = $manager['name']; 
			    	if($manager['last_name'] != ''){
			    		$name = mb_substr($name, 0,2);
						$name = ($name!='')?$name.'.':'';
			    	}	

		    		$String .= ''.$manager['last_name'].' '.$manager['name'].'<br>';
		    	}
		    	
		    	$String .= '</div>';
		    }
		    return $String;
		}

		// сохраняем спискок прикреплённых менеджеров кандидатов
		protected function save_get_choose_curators_edit_AJAX(){
			$message = "просьба сообщить Админу. метод save_get_choose_curators_edit";
			$this->responseClass->addMessage($message,'system_message');
		}

		// получаем список прикрепленных менеджеров - кандидатов для нового клиента в кабинете
		protected function get_choose_curators_edit_AJAX(){
    		$html = $this->get_choose_curators_edit();

    		
			
			$message = "Внимание!!! При выборе одного пользователя, он автоматически назначается куратором клиента!";
			echo '{
				"response":"show_new_window",
				"title":"Выберите менеджера",
				"html":"'.base64_encode($html).'",
				
				"function2":"echo_message",
				"message_type":"successful_message",
				"message":"'.base64_encode($message).'","time":"10000"}';
				exit;
    	}

		// получаем информацию о менеджере 
		protected function get_manager_name_Database_Array($id){
			global $mysqli;
		   	$arr = array();
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
				   $arr = $row;
				}
		    }
		    return $arr;
		}

		// получаем имя менеджера
		protected 	function get_user_name_Database_Html($id){
		    global $mysqli;
		    $arr = array();
		    $String = '<span>неизвестно</span>';
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $arr[$key] = $val;
				}
		    }		    
		    if(count($arr)){
		    	$String = '<span class="greyText" data-id="'.$arr['id'].'">'.$arr['name'].' '.$arr['last_name'].'</span>';
		    }
		    return $String;
		}

		// получаем форму присвоения даты утверждения макета
		// в зависимости от уровня допуска для некоторых это календарь, а для менеджеров это кнопка
		protected function get_Position_approval_date($approval_date,$position_id){
			$html = '';
			if($this->user_access == 1){
				$html .= '<input type="text" class="approval_date" value="'.((strtotime($approval_date) != 0 && $approval_date!= "00.00.0000 00:00:00")?$approval_date:'').'" data-id="'.$position_id.'">';
			}else{
				if(trim($approval_date) == "" || $approval_date == "00.00.0000 00:00:00"){return '';}
				$timestamp = strtotime($approval_date);
				$date = date('d.m.Y',$timestamp);
				$time = date('H:i',$timestamp);$time = ($time!='00:00')?$time:'';
				$html .= (strtotime($approval_date) != 0)?'<span class="greyText">'.$date.'<br>'.$time.'</span>':'';	
			}
			return $html;
		}

		// вывод имени исполнителя для заказа
		protected function get_name_no_men_employee_Database_Html($id,$user_access = 0){
			$html = '<span data-access="'.$user_access.'" class="attach_no_men_employee">не назначен</span>';

			if(isset($id) && trim($id)!='' && trim($id)!=0){
			    global $mysqli;
			    $arr = array();
			    $query = "SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
			    if($user_access != 0){
			    	$query .= " AND `access` = '".$user_access."'";
			    }
			    $result = $mysqli->query($query)or die($mysqli->error);
			    if($result->num_rows>0){
					foreach($result->fetch_assoc() as $key => $val){
					   $arr[$key] = $val;
					}
			    }		    
			    if(count($arr)){
			    	$html = '<span data-id="'.$arr['id'].'">'.$arr['last_name'].' '.$arr['name'].'</span>';
			    }			    
			}

			return $html;
			
		}

		// получаем имя сотрудника по id, если он указан
		protected 	function get_name_employee_Database_Html($id,$no_edit=0){
			if(isset($id) && trim($id)!=''){
			    global $mysqli;
			    $String = '<span'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</span>';
			   	$arr = array();
			    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
			    $result = $mysqli->query($query)or die($mysqli->error);
			    if($result->num_rows>0){
					foreach($result->fetch_assoc() as $key => $val){
					   $arr[$key] = $val;
					}
			    }		    
			    if(count($arr)){
			    	$String = '<span data-id="'.$arr['id'].'">'.$arr['last_name'].' '.$arr['name'].'</span>';
			    }
			    return $String;
			}else{
				return 'не назначен';
			}
		}

		// проверка отгрузки заказа
		protected function check_shipped_order(){
			if(!isset($_POST['order_id']) || !isset($_POST['document_id'])){return;}

			// if($_POST['value'] != "goods_shipped_for_client")
			$order_id = (int)$_POST['order_id'];
			$document_id = (int)$_POST['document_id'];

			global $mysqli;
			// запрос 
			$query = "SELECT `id`,`status_sklad`,`status_snab` FROM `".CAB_ORDER_MAIN."`  WHERE `the_bill_id` IN (SELECT `id` FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$order_id."')";
			
			$shipped = 1;
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					// echo $row['status_sklad'];
					if($row['status_sklad'] != 'goods_shipped_for_client'){
						$shipped = 0;
					}
				}
			}
			// echo $query;

			if($shipped == 1){
				$this->db_edit_one_val(CAB_ORDER_ROWS,'global_status',$order_id,'shipped');
				$result = $mysqli->query($query) or die($mysqli->error);
				$message = 'Заказ перемещён в раздел "Отгруженные"';
				$json = '{"response":"OK","function2":"reload_order_tbl","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
				echo $json;
				exit;
			}
		}
		
		// фильтрация позиций ЗАПРОСОВ по горизонтальному меню
		protected function requests_Template_recuestas_main_rows_Database($id){
			// ФИЛЬТРАЦИЯ ПО ВЕРХНЕМУ МЕНЮ 
			switch ($_GET['subsection']) {
				case 'no_worcked_men': // не обработанные
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
					

				case 'in_work': // в работе у менеджера
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;				

				case 'denided_query':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND  `".RT_DOP_DATA."`.`row_status` LIKE '%red%'";
					break;
				case 'denied':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` = 'tz_is_not_correct' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				case 'paused':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE '%pause%' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				case 'calk_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE 'calculate_is_ready' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
				case 'send_to_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` IN ('on_calculation_snab','on_recalculation_snab') AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
				case 'in_work_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` IN ('in_calculation') AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
				case 'denied':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` IN ('tz_is_not_correct_on_recalculation','tz_is_not_correct') AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				default:
					$where = "WHERE `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".RT_MAIN_ROWS."`.`query_num` = '".$id."'  AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
			}


			global $mysqli;
			$query = "
				SELECT 
					`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
					`".RT_DOP_DATA."`.`quantity`,	
					`".RT_DOP_DATA."`.`price_out`,		
					`".RT_DOP_DATA."`.`print_z`,	
					`".RT_DOP_DATA."`.`zapas`,	
					`".RT_DOP_DATA."`.`row_status`,	
					`".RT_DOP_DATA."`.`status_snab`,	
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".RT_MAIN_ROWS."`.*,
					`".RT_LIST."`.`id` AS `request_id`,
					`".RT_LIST."`.`manager_id`,
					`".RT_LIST."`.`client_id`
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
					 ".$where."
					ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";
				// echo  $query.'<br><br>';
			
			$postion_arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$postion_arr[] = $row;
				}
			}
			return $postion_arr;
		}

		// получаем информацию из cab_dop_data
		protected function get_cab_position_Database($id){
			global $mysqli;
			$arr = array();

		    $query  = "SELECT *,
		    `".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
		    `".CAB_ORDER_MAIN."`.`number_rezerv` 
		    FROM `".CAB_ORDER_MAIN."`";
		    $query .= " INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`";
		    $query .= " WHERE `".CAB_ORDER_MAIN."`.`id` = '".(int)$id."'";
		   
		    $postion_arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$postion_arr[] = $row;
				}
			}
			// echo $query;
			return $postion_arr;
		}
		
		

		protected function get_all_services_names_Database(){
			global $mysqli;
			$arr = array();
			$query = "SELECT *
			 FROM `".OUR_USLUGI_LIST."`;";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}
			return $arr;
		}

		protected function get_a_detailed_article_on_the_price_of_positions_Html(){
			// получаем название всех услуг
			$this->Services_list = $this->get_all_services_names_Database();


			$html = '';


			// собираем шапку таблицы
			$html .= '<table id="a_detailed_article_on_the_price_of_positions">';
			$html .= '<tr class="no_calc">';
			$html .= '<td></td><td colspan="7">Рассчитанная стоимость заказа</td><td class="postfaktum"></td><td colspan="4" class="postfaktum">Фактическая входящая стоимость</td><td></td>';
			$html .= '</tr>';

			$html .= '<tr class="no_calc">';
				// рассчитано ранее
				$html .= '<th>п</th>';
				$html .= '<th>Артикул/номенклатура</th>';
				$html .= '<th>перечень товаров и услуг</th>';
				$html .= '<th>тираж</th>';
				$html .= '<th>$ входящая</th>';
				$html .= '<th>%</th>';
				$html .= '<th>$ исходящая</th>';
				$html .= '<th>прибыль</th>';
				// то, что получилось по факту
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th class="postfaktum">перечень товаров и услуг</th>';
				$html .= '<th class="postfaktum">тираж</th>';
				$html .= '<th class="postfaktum">$ входащая</th>';
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th>комментарии СНАБ</th>';
			$html .= '</tr>';


			//////////////////////////////////////////////////////////
			//	объявляем переменные для подсчёта итого по заказу   //
			//////////////////////////////////////////////////////////
			$this->GlobItogo_price_in = 0;	// входящая за заказ
			$this->GlobItogo_price_out = 0; // исходящая за заказ
			$this->GlobItogo_price_pribl = 0; // прибыль за заказ
			$this->GlobItogo_price_in_postfaktum = 0;
			$this->GlobAdded_postfactum_class = '';
			//////////////////////////////////
			//	перебор заказа по позициям  //
			//////////////////////////////////
			$this->GlobAdded_postfactum_class  = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
			foreach ($this->Positions_arr as $key => $position) {
				// считаем тираж для товара по позиции
				$this->PosGenTirage = $position['quantity']+$position['zapas'];


				//////////////////////////////////////////////////////////
				//	объявляем переменные для подсчёта итого по позиции  //
				//////////////////////////////////////////////////////////
				// сразу же записываем в них цены за тираж по товару
				$this->PositionItogo_price_in = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции то, что было рассчитано клиенту
				$this->PositionItogo_price_in_postfaktum = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции по факту то, что получилось
				$this->PositionItogo_price_out = $this->PosGenTirage*$position['price_out']; // исходящая по позиции
				$this->PositionItogo_price_pribl = $this->PositionItogo_price_out - $this->PositionItogo_price_in; // прибыль по позиции
				$this->PositionItogo_price_percent = $this->get_percent_Int($this->PositionItogo_price_in,$this->PositionItogo_price_out);

				// строка стоимости и описания товара
				$html .= '<tr class="tovar_provided" id="tovar_provided_'.($key+1).'">';
					// рассчитано ранее
					$rowspan = count($position['SERVICES'])+1;
					$html .= '<td rowspan="'.$rowspan.'">'.($key+1).'</td>';
					$html .= '<td rowspan="'.$rowspan.'">'.$position['name'].'</td>';
					$html .= '<td>товар</td>';
					$html .= '<td><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td><span class="service_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td><span>'.$this->PositionItogo_price_percent.'</span>%</td>';
					$html .= '<td><span class="service_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					$html .= '<td><span class="service_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					// то, что получилось по факту
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td class="postfaktum">'.$position['name'].'</td>';
					$html .= '<td class="postfaktum"><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td class="postfaktum"><span class="service_price_in_postfactum">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';



				$html_added = ''; // услуги добавленные в заказ
				$added_postfactum_class = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
				// перебираем прикреплённые услуги
				foreach ($position['SERVICES'] as $count => $service) {
					//////////////////////////////////////////////////////////
					//	объявляем переменные для подсчёта стоимости услуги  //
					//////////////////////////////////////////////////////////
					$this->Service_price_in = ($service['for_how']=='for_one')?$service['price_in']*$service['quantity']:$service['price_in'];// входящая  по услуге то, что было рассчитано клиенту
					$this->Service_price_out = $this->calc_summ_dop_uslug(array($service)); // исходящая по услуге
					$this->Service_price_pribl = $this->Service_price_out - $this->Service_price_in; // прибыль по услуге
					$this->Service_tir = ($service['for_how']=='for_one')?'<span>'.$service['quantity'].'</span>шт':'<span>  -  </span>'; // тираж по услуге
					$this->Service_Name = (isset($service['uslugi_id']) && $service['uslugi_id']>0)?$this->Services_list[$service['uslugi_id']]['name']:$service['uslugi_id']; // название услуги
					$this->Service_percent = $this->get_percent_Int($this->Service_price_in,$this->Service_price_out);

					/*	
						выводим кнопку отключения услуги только СНАБАМ, АДМИНАМ И АВТОРУ ДОБАВЛЕННОЙ УСЛУГИ, 
						а так же показываем кнопку у тех услуг которые не имеют автора - это услуги добавленные ещё в запросе
					*/
					if($service['author_id_added_services'] == 0 && $this->user_access !=2 || ($service['author_id_added_services'] == $this->user_id || $this->user_access == 1 || $this->user_access == 8)){
						$this->Service_swhitch_On_Of = ((int)$service['on_of'] == 1)?'<span  data-id="'.$service['id'].'" class="on_of">+</span>':'<span  data-id="'.$service['id'].'" class="on_of minus">-</span>';
					}else{
						$this->Service_swhitch_On_Of = '';
					}
					


					switch ((int)$service['author_id_added_services']) {
							case 0: // для услуг добавленных из запроса
								$html .= '<tr class="provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html .= '<td>'.$this->Service_Name.'</td>';
									$html .= '<td>'.$this->Service_tir.'</td>';
									$html .= '<td><span class="service_price_in">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td><span>'.$this->Service_percent.'</span>%</td>';
									$html .= '<td><span class="service_price_out">'.$this->Service_price_out.'</span>р</td>';
									$html .= '<td><span class="service_price_pribl">'.$this->Service_price_pribl.'</span>р</td>';
									// то, что получилось по факту
									$html .= '<td class="postfaktum"></td>';
									$html .= '<td class="postfaktum">'.$this->Service_Name.'</td>';
									$html .= '<td class="postfaktum">'.$this->Service_tir.'</td>';
									$html .= '<td class="postfaktum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html .= '<td></td>';
								$html .= '</tr>';

								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in += $this->Service_price_in;	// входящая  по позиции то, что было рассчитано клиенту
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								break;
							
							default:// если указан id того, кто добавил услугу, то услуга была добавлена в заказ
								$html_added .= '<tr class="not_provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html_added .= '<td></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_in">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_out">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>';
									// то, что получилось по факту
									$html_added .= '<td class="postfaktum"></td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_Name.'</td>';
									
									// редактор тиража для добавленных постфактум услуг 
									if(($this->user_access == 1 || $this->user_access == 8) && $service['for_how']=='for_one'){
										$html_added .= '<td class="postfaktum added_postfactum"><input type="text" value="'.$service['quantity'].'" data-id="'.$service['id'].'" data-quantity="'.$service['quantity'].'" class="change_tirage_for_postfactum_added_service"></td>';	
									}else{
										$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_tir.'</td>';
									}

									// редактор стоимости для добавленных постфактум услуг 
									if($this->user_access == 1 || $this->user_access == 8){
										$html_added .= '<td class="postfaktum added_postfactum"><span  class="service_price_in_postfactum" style="display:none">'.$this->Service_price_in.'</span><input type="text" value="'.$this->Service_price_in.'" data-id="'.$service['id'].'" data-quantity="'.$service['quantity'].'" data-for_how="'.$service['for_how'].'" class="change_price_in_for_postfactum_added_service">'.(($service['for_how']=='for_one')?'<br><span class="greyText">(за ед.)</span>':'').'</td>';	
									}else{
										$html_added .= '<td class="postfaktum added_postfactum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									}
									
									$html_added .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html_added .= '<td></td>';
								$html_added .= '</tr>';
								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								// добавляем класс подсветки цены
								$this->GlobAdded_postfactum_class = 'added_postfactum_class td_shine';
								$added_postfactum_class = 'added_postfactum_class td_shine';
								
								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								
								break;
						}						
				}
				// добавляем услуги добавленные в заказ
				$html .= $html_added;
				// строка с кнопкой добавления услуги
				// добавляем строку пробел
				$html .= '<tr class="itogo_for_position_probel no_calc">';
					$html .= '<td colspan="8"></td>';
					$html .= '<td colspan="5" class="postfaktum">';
						if($this->user_access != 2){
							$html .= '<input type="button" data-rowspan_id="tovar_provided_'.($key+1).'" data-id_dop_data="'.$position['id_dop_data'].'" class="add_service" name="add_service" value="Добавить">';	
						}						
					$html .= '</td>';
					$html .= '<td colspan="2"></td>';		
				$html .= '</tr>';

				// итого по позиции
				$html .= '<tr class="itogo_for_position">';
					$html .= '<td></td>';
					$html .= '<td>Итого по позиции</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="position_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					$html .= '<td colspan="3"  style="background-color:#C7C7C7;text-align:right;"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#C7C7C7;"><span class="'.$added_postfactum_class.'"><span  class="position_price_in_postfaktum">'.$this->PositionItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#C7C7C7;text-align:right;"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

				//////////////////////////
				//	обсчитываем ИТОГО за заказ
				//////////////////////////
				$this->GlobItogo_price_in += $this->PositionItogo_price_in;	// входящая за заказ
				$this->GlobItogo_price_out += $this->PositionItogo_price_out; // исходящая за заказ
				$this->GlobItogo_price_pribl += $this->PositionItogo_price_pribl; // прибыль за заказ
				$this->GlobItogo_price_in_postfaktum += $this->PositionItogo_price_in_postfaktum; // входящая по факту
			}

			// если имеем разницу в постфактум выводим её
			$this->GlobItogo_price_in_difference = $this->GlobItogo_price_in-$this->GlobItogo_price_in_postfaktum;
			// собираем html разницы фактической и предусмотренной в расчёте стоимости
			$this->GlobItogo_price_in_difference_Html = ($this->GlobItogo_price_in_difference!=0)?'<span>'.$this->GlobItogo_price_in_difference.'</span>р':'';

			// добавляем строку пробел
			$html .= '<tr class="itogo_for_position_probel no_calc">';
				$html .= '<td colspan="15"></td>';					
			$html .= '</tr>';
			// добавляем ИТОГО по заказу
			$html .= '<tr class="itogo_for_position no_calc" id="itogo_order">';
					$html .= '<td></td>';
					$html .= '<td>Итого по заказу</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span class="order_price_in">'.$this->GlobItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="order_price_out">'.$this->GlobItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_pribl">'.$this->GlobItogo_price_pribl.'</span>р<div class="minus">'.$this->GlobItogo_price_in_difference_Html.'</div></td>';
					$html .= '<td colspan="3"  style="background-color:#B1C370;text-align:right;
"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#B1C370;
"><span class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_in_postfactum">'.$this->GlobItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#B1C370;text-align:right;
"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

			$html .= '<table>';

			return $html;
		}

		// расчёт процента оплаты
		protected function get_percent_for_payment($price,$payment_price){
			if($payment_price == 0){return 0;}
			$percent = round($payment_price/($price/100),2);
			return $percent;
		}

		// подсчёт процентов наценки
		protected function get_percent_Int($price_in,$price_out){
			$per = ($price_in!= 0)?$price_in:0.09;
			$percent = round((($price_out-$price_in)*100/$per),2);
			return $percent;
		}

		// // получаем массив строк счетов/спецификаций по id заказа
		// protected function get_specification_arr($order_id){
		// 	global $mysqli;
		// 	$query = "SELECT `".CAB_BILL_TBL."`.*,
		// 			DATE_FORMAT(`".CAB_BILL_TBL."`.`date_specification_signed`,'%d.%m.%Y %H:%i:%s')  AS `date_specification_signed`,
		// 			DATE_FORMAT(`".CAB_BILL_TBL."`.`date_return_width_specification_signed`,'%d.%m.%Y %H:%i:%s')  AS `date_return_width_specification_signed`
		// 			 FROM `".CAB_BILL_TBL."` WHERE  `order_id` = '".$order_id."'";
		// 	$result = $mysqli->query($query) or die($mysqli->error);

		// 	$arr = array();
		// 	if($result->num_rows > 0){
		// 		while($row = $result->fetch_assoc()){
		// 			$arr[] = $row;
		// 		}
		// 	}
			
		// 	return $arr;
		// }

		

		// получаем список пользователей по номеру подразделения
		protected function get_production_userlist_Database(){
			if(empty($this->userlist)){
				global $mysqli;
				$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE  `access` = '".$this->group_access."'";
				$result = $mysqli->query($query) or die($mysqli->error);

				$this->userlist = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->userlist[$row['id']] = $row;
					}
				}
			}
			return $this->userlist;
		}

		// получаем спецификации к заказу
		protected function table_specificate_for_order_Database($id){
			global $mysqli;
			$query = "SELECT *,
			DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y ')  AS `create_time`,
			DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_create_the_bill`,'%d.%m.%Y ')  AS `date_create_the_bill`,
			DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date`,'%d.%m.%Y %H:%i:%s')  AS `shipping_date`,

			DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date_limit`,'%d.%m.%Y')  AS `shipping_date_limit`,
					
			DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`payment_date`,'%d.%m.%Y')  AS `payment_date`
				
			FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$id."'";
			
			$where = 1;
					
			// фильтрация по документам
			if($this->filtres_specificate != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_specificate;
				$where = 1;
			}
			// сортировка по документам
			if($this->filtres_specificate_sort != ''){
				$query .= " ".$this->filtres_specificate_sort;
			}


			//////////////////////////
			//	check the query
			//////////////////////////
				if(isset($_GET['show_the_query'])){
					echo '*** $query = '.$query.'<br>';	
				}
				


			$result = $mysqli->query($query) or die($mysqli->error);
			$spec_arr = array();		
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$spec_arr[] = $row;
				}
			}
			return $spec_arr;
		}	

		
		// запрос строк позиций по спецификации
		protected function positions_rows_Database($doc_id){
			
			$arr = array();
			global $mysqli;

			$query = "SELECT *, 
			`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`approval_date`,'%d.%m.%Y %H:%i:%s')  AS `approval_date`
			FROM `".CAB_ORDER_DOP_DATA."` 
			INNER JOIN ".CAB_ORDER_MAIN." ON `".CAB_ORDER_MAIN."`.`id` = `".CAB_ORDER_DOP_DATA."`.`row_id` 
			WHERE `".CAB_ORDER_MAIN."`.`the_bill_id` = '".$doc_id."'";
			// $query = "SELECT * FROM ".CAB_ORDER_MAIN." WHERE `order_num` = '".$order_id."'";
			$where = 1;

			// фильтрация для менеджера
			if($this->user_access == 5){
				if(isset($_GET['subsection']) && $_GET['subsection'] == 'order_in_work_snab'){
					$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_MAIN."`.`status_snab` = 'in_production'";	
					$where = 1;
				}								
			}			


			// дата утверждения (фильтр)
			if(isset($_GET['approval_date']) && trim($_GET['approval_date']) !=""){
				// echo 'Привет Мир =)';
				$query .= " ".(($where)?'AND':'WHERE')." DATE_FORMAT(`".CAB_ORDER_MAIN."`.`approval_date`,'%d.%m.%Y') = '".$_GET['approval_date']."'";
				$where = 1;
			}

			// поставщик (фильтр)
			if(isset($_GET['supplier']) && trim($_GET['supplier']) !=""){
				// echo 'Привет Мир =)';
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_MAIN."`.`art` LIKE '".(int)$_GET['supplier']."%'";
				$where = 1;
			}

			// статус снабжения (фильтр)
			if(isset($_GET['status_snab']) && trim($_GET['status_snab']) !=""){
				// echo 'Привет Мир =)';
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_MAIN."`.`status_snab` = '".$_GET['status_snab']."'";
				$where = 1;
			}

			// номер резерва (фильтр)
			if(isset($_GET['number_rezerv']) && trim($_GET['number_rezerv']) !=""){
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_MAIN."`.`number_rezerv` = '".$_GET['number_rezerv']."'";
				$where = 1;
			}

			
			// фильрация по позициям (указываем в последнюю очередь)
			if($this->filtres_position != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_position;
				$where = 1;
			}

			// сортировка
			if($this->filtres_position_sort != ''){
				$query .= "  ".$this->filtres_position_sort;
			}else{
				$query .= " ORDER BY `".CAB_ORDER_MAIN."`.`sequence_number` ASC";
			}
			
			// echo $query.'<br><br>';
			

			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		// считает % оплаты
		protected function calculation_percent_of_payment($price, $payment){
			// $price - стоимость
			// $payment - уже заплаченная сумма
			return ($price!=0)?round($payment*100/$price,2):'0.00';
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

		// ответ о неверном адресе
		protected function response_to_the_wrong_address($method_template){
			// отправляем сообщение об ошибке
			$this->error_message_for_incorrect_URL();
			// собиравем сообщение для пользоватедля
			$message = 'Вы не должны были попасть на данную страницу, но что-то пошло не так и Вы всё таки здесь!!!<br>';
			$message .= 'Через 12 секунд Вы будете переадресованы на стартовую страницу кабинета <br>в соответствии с Вашим уровнем доступа.<br>';
			$message .= 'Сообщение о данном происшествии уже отправлено разработчикам. Спасибо.';
			// при выгрузке данного дива на страницу JS переадресует пользователя через 5 секунд по указанной в div ссылке
			$message .= '<div id="js_location" data-time="12000"><a href="http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet().'">Перейти по ссылке</a></div>';
			// выводим сообщение
			echo $this->wrap_text_in_warning_message($message);
		}

		// оповещение об ошибке Разработчиков
		protected function error_message_for_incorrect_URL(){
			// получаем имя пользователя
			include_once './libs/php/classes/manager_class.php';
			$user_name = Manager::get_snab_name_for_query_String($this->user_id);
			
			$message = '';
			$message .= date('d-m-Y в H:i:s').'<br>';

			if(isset($_SESSION['come_back_in_own_profile'])){
				$user_name_real = Manager::get_snab_name_for_query_String($_SESSION['come_back_in_own_profile']);	
				$message .= 'Пользователь '.$user_name_real.', находясь под учётной записью: "'.$user_name.'" (ID: '.$this->user_id.', Access: '.$this->user_access.') перешёл по адресу http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' и наткнулся на ошибку.';
				$message .= '<br>адрес предыдущей страницы : http://'.$_SERVER['HTTP_REFERER'];
				$message .= '<br>Реальный уровень допуска пользователя: '.$this->get_user_access_Database_Int($_SESSION['come_back_in_own_profile']).'<br>';
				$message .= '<br>Реальный ID пользователя: '.$_SESSION['come_back_in_own_profile'];
				$message .= isset($_GET)?'<br>ID пользователя: '.$this->user_id.'':'';	
			}else{
				$message .= 'Пользователь '.$user_name.' перешёл по адресу http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' и наткнулся на ошибку.';
				$message .= '<br>адрес предыдущей страницы : http://'.$_SERVER['HTTP_REFERER'];
				$message .= '<br>Уровень допуска пользователя: '.$this->user_access.'';
				$message .= '<br>ID пользователя: '.$this->user_id.'<br>';
				$message .= isset($_GET)?'ID пользователя: '.$this->user_id.'<br>':'';	
			}
			
			// отправка сообщения
			$this->error_message($message);			
		}

		
		// отправка сообщения об ошибке
		protected function error_message($message,$subject = 'Error message' ,$from_email = 'os@apelburg.ru'){
			include_once './libs/php/classes/mail_class.php';
			$mailClass = new Mail();
			$mailClass->send('kapitonoval2012@gmail.com', $from_email, $subject, $message);
		}

		// // отправка jgjdtotybq
		// protected function error_message($message,$subject = 'Error message' ,$from_email = 'os@apelburg.ru'){
		// 	include_once './libs/php/classes/mail_class.php';
		// 	$mailClass = new Mail();
		// 	$mailClass->send('kapitonoval2012@gmail.com',$from_email,$subject,$message);	
		// }



		// запрашивает из базы допуски пользователя
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
		//	получение информации по спецификациям и договорам
		//////////////////////////
			// возвращает ссылку на спецификацию по строке заказа
			protected function get_document_link($document,$client_id,$create_time){
				$html = '';
				switch ($document['doc_type']) {
					// по оферте
					case 'oferta': 
						$html .= '<span>ОФ</span>&nbsp;';
						// если нет информации по документу
						if($document['doc_id'] == 0 ){
							$html .= 'не удалось получить данные оферты'; return $html;}

						// проеряем наличие номера
						if($document['doc_num'] != 0){
							$number = $document['doc_num']." от ".$document['date_create_the_bill'];
						}else{
							$number = 'не указан';
						}

						// ссылка на документ
						$html .= "<a target='_blank' href='?page=agreement&section=agreement_editor&client_id=".$client_id."&oferta_id=".$document['doc_id']."&dateDataObj={\"doc_type\":\"oferta\"}'>№ ".$number."</a>";	
						
						return $html;
						break;
					
					// по спецификации
					default: 
						$html .= '<span>СПФ</span>&nbsp;';
						// если нет информации по документу
						if($document['doc_num'] == 0){$html .= '№ не указан'; return $html;}

						// получаем информацию по договору
						$agrement_arr = $this->get_info_for_agreement_Database($document['doc_id']);
						if(empty($agrement_arr)){
							$html .= 'информация по договору не найдена';return $html;
						}
							
						$html .= "<a target='_blank' href='?page=agreement&section=agreement_editor&client_id=".$client_id."&agreement_id=".$document['doc_id']."&agreement_type=".$agrement_arr['type']."&open=specification&specification_num=".$document['specification_num']."&dateDataObj={\"doc_type\":\"spec\"}'>№ ".$document['specification_num']." от ".$create_time."</a>";
						return $html;
						break;
				}
				
			}

			// возвращает ссылку на договор по строке заказа
			protected function get_agreement_link($Specification,$client_id,$create_time){
                $html = '';
				// если нет информации по договору
				if($Specification['doc_id'] == 0 ){$html .= 'договор не указан'; return $html;}

				// получаем информацию по договору
				$agrement_arr = $this->get_info_for_agreement_Database($Specification['doc_id']);
				if(empty($agrement_arr)){return 'не найден';}
				// return $this->print_arr($agrement_arr);
				$html = '<a href="?page=agreement&section=agreement_editor&client_id='.$client_id.'&agreement_id='.$Specification['doc_id'].'&agreement_type='.$agrement_arr['type'].'&open=empty">'.$agrement_arr['agreement_num'].'</a>';
				return $html;
			}
	
			// запрос информации по договору
			protected function get_info_for_agreement_Database($agreement_id){
				global $mysqli;
				$agreement_info_arr = array();
				$query = "SELECT * FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id = '".$agreement_id."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
					
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$agreement_info_arr = $row;
					}
				}

				return $agreement_info_arr;
			}

			// запрос информации по оферте
			protected function get_info_for_offerta_Database($offerta_id){
				global $mysqli;
				$offerta_info_arr = array();
				$query = "SELECT * FROM `".OFFERTS_TBL."` WHERE id = '".$offerta_id."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
					
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$offerta_info_arr = $row;
					}
				}

				return $offerta_info_arr;
			}

			protected function hl(){
				echo "Hellow World =)";
			}


			/**
				 *	обсчёт даты сдачи в соответствии с вводными из спецификации
				 *	
				 *	@author  Алексей Капитонов
				 *	@version 17:52 08.10.2015
				*/

			// обработка просрочки оплаты
			protected function check_type_the_document_and_payment_date(){
				/*
					!!!!!!  для работы check_type_the_document_and_payment_date()
			
					1.  установить нулевые переменные в первых строка обхода спецификаций
					    спецификации должны храниться в Object вида :  $this->specificate
					2.  при переборе позиций установить 
						$this->get_position_approval_bigest_date();
						позиции должны храниться в Object вида :  $this->position
					
					$this->approval_date = 0;// timestamp старшей даты утверждения макета 
					$this->one_position_is_not_approval = 0; // флаг оповещает о неутвержденной позиции
	
				*/


				// классы HTML для подсветки ячеек таблиц и сигнализации о просроченных сроках по датам
				// $this->red_flag_date_limit = ' class_background_red limit';
				// $this->red_flag_date_shipping_date = ' class_background_red shipping_date';
				// $this->red_flag_date_date_approval = ' class_background_red date_approval';
				// $this->red_flag_date_work_days = ' class_background_red work_days';
				// $this->red_flag_date_payment = ' class_background_red payment_date';
				// $this->red_flag_date_shipping_date_redactor_id = ' class_background_yellow redactor_id';
				$this->red_flag_date_limit = '';
				$this->red_flag_date_shipping_date = '';
				$this->red_flag_date_date_approval = '';
				$this->red_flag_date_work_days = '';
				$this->red_flag_date_payment = '';
				

				// если = 1, заказ оплачен в достаточном размере
				$this->specificate['enabled_start_work']; 
					
				// дата оплаты
				$this->specificate['payment_date']; 
				// $this->specificate['payment_date_timestamp']; 

				// дата лимита по оплате
				$this->specificate['shipping_date_limit'];
				// $this->specificate['shipping_date_limit_timestamp'];

				$specificate_shipping_date_need_edit = 0;
				$this->work_days = '';
				$this->specificate_shipping_date = '';
				$this->specificate_shipping_date_timestamp = 0;
				// if($this->specificate['id'] == 112){
				// 	echo $this->specificate_shipping_date_timestamp.'<br>';
				// 	echo '$this->approval_date = '.$this->approval_date.'<br>';
				// 	echo '$this->one_position_is_not_approval = '.$this->one_position_is_not_approval.'<br>';
				// }
				switch ($this->specificate['date_type']) {
					case 'days': // ПО РАБОЧИМ ДНЯМ
						// если счёт оплачен в достаточном размере
						if($this->specificate['enabled_start_work'] == 1){
							if(!$this->one_position_is_not_approval){// если все позиции с утверждённым макетом
								if($this->approval_date > 0){ // проверяем наличие старшей даты утв. макета
											
									// высчитываем дату сдачи
									$this->specificate_shipping_date_timestamp = strtotime(goOnSomeWorkingDays(date('Y-m-d H:i:s',($this->approval_date)),($this->specificate['work_days']+2),'+'));
									$this->specificate_shipping_date = date('d.m.Y',$this->specificate_shipping_date_timestamp);
									
									// перезаписываем дату сдачи в случае её не совпадения
									if (strtotime($this->specificate['shipping_date']) !=  $this->specificate_shipping_date_timestamp) {
										$this->db_edit_one_val(CAB_BILL_AND_SPEC_TBL , 'shipping_date', $this->specificate['id'], date('Y-m-d',$this->specificate_shipping_date_timestamp) );										
									}									
								}
							}	
						}
								
						$this->work_days = $this->specificate['work_days'];
						break;
							
					default: // ПО ДАТЕ
						
						// 1. проверка на оплату

							// проверка при недостаточной оплате
							if($this->specificate['enabled_start_work'] != 1 ){
								// проверка на предупреждение
								// echo (strtotime($this->specificate['shipping_date_limit']).' - '.(time()+ 24*60*60)).' *** <br>';
								if (strtotime($this->specificate['shipping_date_limit']) - (strtotime(date('d.m.Y',time())) + 24*60*60) <= 0) {
									$this->red_flag_date_limit = ' class_background_orange limit';	
									$this->red_flag_date_payment = ' class_background_orange payment_date';
									$this->red_flag_date_shipping_date = ' class_background_orange shipping_date';
								}else if (strtotime($this->specificate['shipping_date_limit']) - strtotime(date('d.m.Y',time())) < 0 ) {
									echo $this->specificate['shipping_date_limit'] .' --- '.date('d.m.Y',time());
									$this->red_flag_date_limit = ' class_background_red limit';	
									$this->red_flag_date_payment = ' class_background_red payment_date';
									$this->red_flag_date_shipping_date = ' class_background_red shipping_date';
									$specificate_shipping_date_need_edit = 1;
								}
							} else if($this->specificate['enabled_start_work'] == 1 ){// проверка при достаточной оплате
								// проверка на просрочку оплате
								// echo strtotime($this->specificate['payment_date']).' - '.strtotime($this->specificate['shipping_date_limit']).' = '.(strtotime($this->specificate['shipping_date_limit'])-strtotime($this->specificate['payment_date'])).'<br>';
								if (strtotime($this->specificate['shipping_date_limit']) - strtotime($this->specificate['payment_date']) < 0) {
									$this->red_flag_date_limit = ' class_background_red limit';	
									$this->red_flag_date_payment = ' class_background_red payment_date';
									$this->red_flag_date_shipping_date = ' class_background_red shipping_date';
									$specificate_shipping_date_need_edit = 1;
								}
							}

						// 2. проверка на дату утверждения макета

							// если како-либо макет не утверждён 
							if($this->approval_date == 0){
								// на утверждение осталось менее суток
								if (strtotime($this->specificate['shipping_date_limit']) - time()+24*60*60 < 24*60*60) {
									$this->red_flag_date_limit = ' class_background_orange limit';	
									$this->red_flag_date_date_approval = ' class_background_orange date_approval';
									$this->red_flag_date_shipping_date = ' class_background_orange shipping_date';
								}else if (strtotime($this->specificate['shipping_date_limit']) - time() < 24*60*60) {// утверждение профакали
									$this->red_flag_date_limit = ' class_background_red limit';	
									$this->red_flag_date_date_approval = ' class_background_red date_approval';
									$this->red_flag_date_shipping_date = ' class_background_red shipping_date';
									$specificate_shipping_date_need_edit = 1;
								}
							}else if($this->approval_date > 0){
								// макет утвердили, но профакали дату
								if (strtotime($this->specificate['shipping_date_limit']) - $this->approval_date < 0) {
									$this->red_flag_date_limit = ' class_background_red limit';	
									$this->red_flag_date_date_approval = ' class_background_red date_approval';
									$this->red_flag_date_shipping_date = ' class_background_red shipping_date';
									$specificate_shipping_date_need_edit = 1;
								}
							}
						// высчитываем дату сдачи
						$this->specificate_shipping_date_timestamp = strtotime($this->specificate['shipping_date']);
						$this->specificate_shipping_date = date('d.m.Y',$this->specificate_shipping_date_timestamp);
						
						// открываем редактирование даты сдачи по дакументу
						// для снаба в критические моменты,
						// при этом данные в самом документе остаются неизменными
						// для админа на время тестирования редактирование разрешено всегда
													
						if($specificate_shipping_date_need_edit == 1 && (int)$this->user_access == 8 || (int)$this->user_access == 1){
							$this->specificate_shipping_date = '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate_shipping_date.'" data-id="'.$this->specificate['id'].'">';	
						}

						if($this->specificate['shipping_date_redactor_id'] != 0){
							$this->red_flag_date_shipping_date = ' class_background_yellow shipping_date';
							$this->specificate_shipping_date .= '<br>'.$this->get_user_name_Database_Html($this->specificate['shipping_date_redactor_id']);
						}


						
						break;
				}				
			}
			// вычисляет старшую дату утверждения макета
			protected function get_position_approval_bigest_date(){
				if($this->one_position_is_not_approval == 1){
					$this->approval_date = 0;
					return;
				}

				// флаг оповещения системы об неутвержденном макете на обной из позиций по документу
				if(strtotime($this->position['approval_date']) == 0){
					$this->one_position_is_not_approval = 1;	
					$this->approval_date = 0;
					return;
				}


				// старшая дата утверждения макета
				$this->approval_date = ($this->approval_date < strtotime($this->position['approval_date']))?strtotime($this->position['approval_date']):$this->approval_date;	
							
						
				
			}
			
			// вычисляет дату сдачи заказа
			protected function get_shipping_bigest_date_for_order(){
				
				
				/*	
					$this->one_specificate_is_not_approval // флаг просчета нового заказа
					$this->specificate_shipping_date     // дата сдачи по спецификации 00.00.0000
					$this->order_shipping_date_timestamp // дата сдачи заказа TIMESTAMP
					$this->order_shipping_date           // дата сдачи заказа 00.00.0000
				*/
				// if($this->Order['order_num'] == 3){
				// 	echo 'test'.'<br>';
				// 	echo '$this->specificate_shipping_date_timestamp = "'.$this->specificate_shipping_date_timestamp.'"<br>';
				// }

				// если один из прежде обсчитанных документов не имел даты сдачи, считать дальше нет смысла
				if(isset($this->one_specificate_is_not_approval) && $this->one_specificate_is_not_approval == 1 ){
					$this->order_shipping_date = '';
					$this->order_shipping_date_timestamp == 0;
					$this->one_specificate_is_not_approval = 1;
					return;
				}
				
				//echo $this->specificate_shipping_date.' -- '.$this->specificate_shipping_date_timestamp.' -- '.$this->one_specificate_is_not_approval.'<br>';
				// если документ не имеет даты сдачи, обнуляем дату и timestamp
				if($this->specificate_shipping_date_timestamp <= 0 && $this->specificate['date_type'] == 'date'){
					$this->order_shipping_date = '';
					$this->order_shipping_date_timestamp == 0;
					$this->one_specificate_is_not_approval = 1;
					return;
				}

				// echo $this->specificate_shipping_date.' -- '.$this->specificate_shipping_date_timestamp.' -- '.$this->one_specificate_is_not_approval.'<br>';


				// если дату еще не присваивали
				// echo $this->specificate_shipping_date;
				if($this->order_shipping_date_timestamp == 0){
					if($this->specificate_shipping_date_timestamp != 0){
						$this->order_shipping_date = date('d.m.Y',$this->specificate_shipping_date_timestamp);
						$this->order_shipping_date_timestamp = $this->specificate_shipping_date_timestamp;
						//echo '<strong>'.$this->specificate_shipping_date.'</strong> -- '.strtotime($this->specificate_shipping_date).'<br>';	
					}else{
						$this->order_shipping_date = '';
					$this->order_shipping_date_timestamp == 0;
					$this->one_specificate_is_not_approval = 1;
					}
					
				}else{
				// если дата была присвоена
					// и если дата не равна прежней
					if($this->order_shipping_date_timestamp != $this->specificate_shipping_date_timestamp){
						$this->order_shipping_date = '';
						$this->order_shipping_date_timestamp == 0;
						$this->one_specificate_is_not_approval = 1;
						return;
					}
				}
			}

		/**
		 *	стандартные методы работы с базой
		 *
		 *	@author  Алексей Капитонов
		 *	@version 13:60 09.10.2015
		*/
			// изменение значения одного значения в одной строке в какой либо таблице
			protected function db_edit_one_val($tbl_name , $col_name, $row_id, $val ){
				global $mysqli; // подключение к базе;
				$query ="UPDATE `".$tbl_name."` SET 
						`".$col_name."` = '".$val."'";
						$query .= " WHERE `id` = '".$row_id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
			}


		//////////////////////////
		//	ОБЩИЕ ШАБЛОНЫ
		//////////////////////////
			// html шаблон строки документа (МЕН/СНАБ/АДМИН)
				protected function get_order_specificate_Html_Template(){
					$this->rows_num++;
					$html = '';
					$html .= '<tr  class="specificate_rows" '.$this->open_close_tr_style.' data-id="'.$this->specificate['id'].'">';
						$html .= '<td colspan="5">';
							// спецификация
							// $html .= 'Спецификация '.$this->specificate_item;
							// ссылка на спецификацию
							$html .= ''.$this->get_document_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
							
							if($this->user_access != 2){
								// номер запроса
								$html .= '&nbsp;<span class="greyText"> (<a href="?page=client_folder&client_id='.$this->specificate['client_id'].'&query_num='.$this->specificate['query_num'].'" target="_blank" class="greyText">Запрос №: '.$this->specificate['query_num'].'</a>)</span>';
								// снабжение
								$html .= '&nbsp; <span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->specificate['snab_id'],8).'</span>';
							}							

							// дата лимита + % предоплаты, если работаем по дате
							if($this->specificate['date_type'] == 'date'){
								$html .= '<br> <span class="greyText '.$this->red_flag_date_limit.'" style="padding:5px">оплатить '.$this->specificate['prepayment'].'% и утвердить макет до: '.$this->specificate['shipping_date_limit'].'</span>';
							}else{
								//% предоплаты, если работаем по дате
								$html .= '<br> <span class="greyText '.$this->red_flag_date_limit.'" style="padding:5px">оплатить '.$this->specificate['prepayment'].'%</span>';
							}
							

						$html .='</td>';
						
						// номер счёта
						$html .= '<td>';
							$html .= 'сч: '.$this->specificate['number_the_bill'];
						$html .= '</td>';

						// сумма по спецификации
						$html .= '<td>';
							$html .= '<span>'.$this->money_format($this->price_specificate).'</span>';
						$html .= '</td>';
						
						// % оплаты
						$html .= '<td class="'.$this->red_flag_date_payment.'">';
							$html .= '<span class="greyText">оплачено: </span> '.$this->calculation_percent_of_payment($this->price_specificate, $this->specificate['payment_status']).' %'.(($this->specificate['payment_date']!='00.00.0000')?', <span class="greyText">от '.$this->specificate['payment_date'].'</span>':'');
						$html .= '</td>';

						$html .= '<td class="'.$this->red_flag_date_date_approval.'">';
							$html .= (($this->approval_date>0)?date('d.m.Y', $this->approval_date):'<!-- none -->').'<br>';
						$html .= '</td>';

						// срок по ДС
						// $html .= '<td contenteditable="true" class="deadline">'.$this->work_days.'</td>';
						
						//дата сдачи по документам
						$html .= '<td class="'.$this->red_flag_date_shipping_date.'">';
							if($this->work_days != ''){
								$html .= '<span class="greyText">срок ДС</span>&nbsp;';
								$html .= $this->work_days.'<br>';
							}
							// if($this->user_access == 1){
							// 	$html .= '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate_shipping_date.'" data-id="'.$this->specificate['id'].'">';

							// }else{
							$html .= $this->specificate_shipping_date.'<br>';
							// }
						$html .= '</td>';
						$html .= '<td><span class="greyText">Бухгалтерия</td>';
						$html .= '<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->specificate['buch_status'],0,$this->specificate).'</td>';
					$html .= '</tr>';
					return $html;
				}

				public function get_a_detailed_specifications($type_product, $no_cat_json){
					// получаем информацию по позиции
					$position['type'] = $type_product; // тип товара для запроса по полям (таблица os__rt_main_rows, колонка type)
					$position['no_cat_json']  = $no_cat_json; // Json с описанием из формы (таблица os__rt_dop_data, колонка no_cat_json)
				
					$html = '';
					$html .= $this->decode_json_no_cat_to_html($position);
					return $html;
				}

				protected function get_a_detailed_specifications_AJAX(){
					// получаем информацию по позиции
					$position = $this->get_cab_position_Database((int)$_POST['position_id']);
					$html = '';
					// $html .= $this->print_arr($position);
					// получаем описание
					$html .= $this->decode_json_no_cat_to_html($position[0]);

					echo '{"response":"replace_width","html":"'.base64_encode($html).'"}';
					exit;
				}
				protected function get_a_detailed_specifications_edit_true_AJAX(){
					// получаем информацию по позиции
					$position = $this->get_cab_position_Database((int)$_POST['position_id']);
					$html = '';
					// $html .= $this->print_arr($position);
					// получаем описание
					$html .= '<form>';
					$html .= $this->decode_json_no_cat_to_html($position[0],1);
					$html .= '<input type="hidden" name="AJAX" value="save_edit_tz_no_cat">';
					$html .= '<input type="hidden" name="position_id" value="'.$_POST['position_id'].'">';
					$html .= '</form>';
					echo '{"response":"show_new_window","html":"'.base64_encode($html).'","title":"Редактор ТЗ не каталог","width":"800px"}';
					exit;
				}

				// сохраняем отредактированное снабжением ТЗ для не каталожки
				protected function save_edit_tz_no_cat_AJAX(){
					$position_id = $_POST['position_id'];
					unset($_POST['position_id']);
					unset($_POST['AJAX']);
					// $json = json_encode($_POST);

					$json_str = '{';
					$n = 0;
					foreach ($_POST as $key => $value) {
						$json_str .= (($n > 0)?',':'').'"'.$key.'":"'.$value.'"';
						$n++;
					}
					$json_str .= '}';

					global $mysqli;
					$query ="UPDATE `".CAB_ORDER_DOP_DATA."` SET 
						`no_cat_json` = '".$json_str."'";
						
					$query .= " WHERE `id` = '".$position_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					
					// echo '{"response":"show_new_window","html":"'.base64_encode($query).'"}';
					echo '{"response":"OK"}';
					exit;
				}

				protected function command_for_edit_tz_for_no_cat_AJAX(){
					global $mysqli;
					$query ="UPDATE `".CAB_ORDER_MAIN."` SET 
						`flag_need_edit_tz_no_cat` = '".$_POST['value']."'";
						
					$query .= " WHERE `id` = '".$_POST['position_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					
					// echo '{"response":"show_new_window","html":"'.base64_encode($query).'"}';
					echo '{"response":"OK"}';
					exit;
				}




				// html шаблон вывода позиций  (МЕН/СНАБ/АДМИН)
				protected function get_order_specificate_position_Html_Template(){
					if($this->position['status_snab'] == 'question' || $this->position['status_snab'] == 'not_adopted'){
						$this->poused_and_question = 0;
					}
					$html = '';
					$html .= '<tr class="position-row position-row-production" id="position_row_'.$this->position['sequence_number'].'" data-cab_dop_data_id="'.$this->id_dop_data.'" data-id="'.$this->position['id'].'" '.$this->open_close_tr_style.'>';
					// порядковый номер позиции в заказе
					$html .= '<td style="width:15px"><span class="orders_info_punct">'.$this->position['sequence_number'].'п<br>('.(($this->Order['number_of_positions'] == 0)?'-':$this->Order['number_of_positions']).')</span></td>';
					// описание позиции
					$html .= '<td colspan="2" style="width:300px">';
					// комментарии
					// наименование товара
					$html .= '<div style="position:relative">';
					$html .= '<span class="art_and_name">'.$this->position['art'].'  '.$this->position['name'].'</span>';
								   
					// добавляем доп описание
					if($this->position['type'] == 'cat'){
						$html .= '<div>';
						$html .= '<input type="button" class="get_size_table_read" data-id_dop_data="'.$this->position['quantity'].'" data-position_id="'.$this->position['id'].'" value="Подробно" >';
						$html .= '</div>';
					}else{
						$disabled_command_edit_tz = ($this->user_access != 1 && $this->user_access != 8 && $this->user_access != 9)?'disabled ':'';
						$html .= '<div title="необходимо исправить ТЗ" class="'.$disabled_command_edit_tz.'command_for_edit_tz_for_no_cat '.(($this->position['flag_need_edit_tz_no_cat'] == 1)?'checked':'').'"  data-position_id="'.$this->position['id'].'"></div>';	

						if($this->user_access == 8 || $this->user_access == 1){
							$html .= '<div class="command_for_edit_tz_for_no_cat '.(($this->position['flag_need_edit_tz_no_cat'] == 1)?'checked':'').'"  data-position_id="'.$this->position['id'].'"></div>';	
							$html .= '<div class="edit_tz_for_no_cat" data-id_dop_data="'.$this->position['quantity'].'" data-position_id="'.$this->position['id'].'" ></div>';	
						}
						
						$html .= '<div>';
						$html .= '<input class="get_a_detailed_specifications" type="button" value="Подробно" data-position_id="'.$this->position['id'].'">';
						$html .= '</div>';
					}
					$html .= '</div>';

					$html .= '</td>';
					// тираж, запас, печатать/непечатать запас
					$html .= '<td>';
					$html .= '<div class="quantity">'.$this->position['quantity'].'</div>';
					$html .= '<div class="zapas">'.(($this->position['zapas']!=0 && trim($this->position['zapas'])!='')?'+'.$this->position['zapas']:'').'</div>';
					$html .= '<div class="print_z">'.(($this->position['zapas']!=0 && trim($this->position['zapas'])!='')?(($this->position['print_z']==0)?'НПЗ':'ПЗ'):'').'</div>';
					$html .= '</td>';
							
					// поставщик товара и номер резерва для каталожной продукции 
					$html .= '<td>';
					$number_rezerv = '<a href="'.$this->link_enter_to_filters('number_rezerv',$this->position['number_rezerv']).'">'.base64_decode($this->position['number_rezerv']).'</a>';
					$supplier_name = ($this->position['art']!="")?'<a href="'.$this->link_enter_to_filters('supplier',substr($this->position['art'], 0,2)).'">'.$this->get_supplier_name($this->position['art']).'</a>':'';
					$html .= '<div class="supplier">'.$supplier_name.'</div>
							<div class="number_rezerv">'.$number_rezerv.'</div>
							</td>';
					// подрядчк печати 
					$change_supplier = '';
					if($this->user_access == 1 || $this->user_access == 8){
						$change_supplier = 'change_supplier';
					}
					if($this->user_access == 5 && ($this->Order['global_status'] == 'in_operation' || $this->Order['global_status'] == 'being_prepared' )){
						$change_supplier = 'change_supplier';	
					}

					$html .= '<td class="'.$change_supplier.'"  data-id="'.$this->position['suppliers_id'].'" data-id_dop_data="'.$this->position['id_dop_data'].'">'.$this->position['suppliers_name'].'</td>';	
					
					
					// сумма за позицию включая стоимость услуг 
					$html .= '<td  data-order_id="'.$this->Order['id'].'" data-id="'.$this->position['id'].'" data-order_num_user="'.$this->order_num_for_User.'" data-order_num="'.$this->Order['order_num'].'" data-specificate_id="'.$this->specificate['id'].'" data-cab_dop_data_id="'.$this->position['id_dop_data'].'" class="price_for_the_position">'.$this->Price_for_the_position.'</td>';
					// всплывающее окно тех и доп инфо
					// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
					// думаю есть смысл хранения в json 
					// обязательные поля:
					// {"comments":" ","technical_info":" ","maket":" "}
					$html .= $this->grt_dop_teh_info($this->position,$this->specificate);
							  
					// дата утверждения макета
					$html .= '<td>';
						$html .= $this->get_Position_approval_date( $this->Position_approval_date = $this->position['approval_date'], $this->position['id'] );
					$html .= '</td>';

					// $html .= '<td><!--// срок по ДС по позиции --></td>';

					// дата сдачи
						 // тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
					$html .= '<td ><!--// дата сдачи по позиции --></td>';


					// получаем статусы участников заказа в две колонки: отдел - статус
					$html .= $this->position_status_list_Html($this->position);
					// фильтр для вкладки (менеджер)
					
					$html .= '</tr>'; 
					if (isset($_GET['subsection']) && $_GET['subsection'] == 'tpause_and_questions') {
						if($this->poused_and_question){$this->rows_num--; return ''; }	
					}
					return $html;
				}

		// шаблон html исполнителей по заказу
		protected function performer_table_for_order(){
			$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
			$html = '<table class="curator_on_request">';
				$html .= '<tr>';
					$html .= '<td>';
						$html .= '<span class="greyText">Заказ №: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
					$html .= '</td>';
					$html .= '<td>';
						$html .= '<span class="greyText">Клиент: </span>'.$this->get_client_name_link_Database($this->Order['client_id']).'';
					$html .= '</td>';
				$html .= '</tr>';	
				$html .= '<tr>';
					$html .= '<td>';
						$html .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
					$html .= '</td>';
					$html .= '<td>';
					if($this->user_access != 5){
						$html .= '<span class="greyText">менеджер: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
					}						
					$html .= '</td>';
				$html .= '</tr>';	
			$html .= '</table>';	
			return $html;
		}
		
		// шаблон html исполнителей по заказу
		protected function performer_table_standart_for_order(){
			$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
			// $html = '<table class="curator_on_request">';
				// $html .= '<tr>';
				$html = '<td colspan="2" class="filter_class" data-href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">';
					$html .= '<div>'.$this->order_num_for_User.'</div>';
				$html .= '</td>';

				$array_client = $this->get_client_name_Array_Database($this->Order['client_id']);
				$html .= '<td  colspan="1"  class="filter_class" data-href="'.$array_client['link'].'">';
					$html .= '<div>'.$array_client['name'].'</div>';
				$html .= '</td>';
				
				$html .= '<td colspan="3">';
					if($this->user_access != 5){
						$html .= '<div class="filter_class men_in_order" data-href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'"><span class="greyText">менеджер:'.$this->meneger_name_for_order.'</span></div>';
					}						
					$html .= '<div class=" men_in_order" data-href=""><span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span></div>';
				$html .= '</td>';
					// $html .= '<td>';
					// if($this->user_access != 5){
					// 	$html .= '<span class="greyText">менеджер: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
					// }						
					// $html .= '</td>';
				// $html .= '</tr>';	
			// $html .= '</table>';	
			return $html;
		}
		// шаблон html исполнителей по заказу
		protected function performer_table_standart_for_stock_order(){
			$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
			// $html = '<table class="curator_on_request">';
				// $html .= '<tr>';
				$html = '<td colspan="2" class="filter_class" data-href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">';
					$html .= '<div>'.$this->order_num_for_User.'</div>';
				$html .= '</td>';

				$array_client = $this->get_client_name_Array_Database($this->Order['client_id']);
				$html .= '<td  colspan="1"  class="filter_class" data-href="'.$array_client['link'].'">';
					$html .= '<div>'.$array_client['name'].'</div>';
				$html .= '</td>';
				
				$html .= '<td colspan="2">';
					if($this->user_access != 5){
						$html .= '<div class="filter_class men_in_order" data-href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'"><span class="greyText">менеджер:'.$this->meneger_name_for_order.'</span></div>';
					}						
					$html .= '<div class=" men_in_order" data-href=""><span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span></div>';
				$html .= '</td>';
					// $html .= '<td>';
					// if($this->user_access != 5){
					// 	$html .= '<span class="greyText">менеджер: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
					// }						
					// $html .= '</td>';
				// $html .= '</tr>';	
			// $html .= '</table>';	
			return $html;
		}
		// выводит имя клиента в заказе, по ссылке в url добавляется id клиента
		protected function get_client_name_Array_Database($id){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr['name'] = 'Клиент не прикреплён';
			$arr['link'] = '#';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr['link'] = ((!isset($_GET['client_id']) || (isset($_GET['client_id']) && $_GET['client_id']!=$row['id']))?$this->change_one_get_URL('client_id').$row['id'].'"':'');
					$arr['name'] = $this->str_reduce($row['company'],45);
				}
			}
			return $arr;
		}

		private function wrap_text_in_warning_message_post($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}

		// шаблон шапки главной таблицы
		protected function get_header_general_tbl(){
			$html = '<table id="general_panel_orders_tbl" class="order_tbl">';
				$html .= '<tr>';
					$html .= '<th colspan="1"></th>';
					$html .= '<th colspan="2">Номер</th>';
					$html .= '<th>Компания</th>';
					$html .= '<th colspan="3">Менеджер отдела</th>';
					$html .= '<th>Сумма</th>';
					$html .= '<th>Бух. учёт</th>';
					$html .= '<th>Комментарий</th>';
					$html .= '<th>Срок сдачи</th>';
					$html .= '<th  colspan="2">статус</th>';
				$html .= '</tr>';
			return $html;
		}

		// возвращает закрывающий тег главной таблицы
		protected function get_footer_tbl(){
			return '</table>';
		}

		// вывод пришедших данных в новом окне
		private function show_post_arr_in_new_window_ajax(){
			$html = $this->print_arr($_POST);
			echo '{"response":"show_new_window_simple", "html":"'.base64_encode($html).'","title":"метод '.$_POST['AJAX'].'_AJAX()","width":"600"}';

		}
		
		/**
		 *	создание запроса
		 *
		 *	@param 		AJAX
		 *	@return  	html
		 *	@see 		windows, forms
		 *	@author  	Алексей Капитонов
		 *	@version 	12:12 03.11.2015
		*/
			// первый запрос на создание нового запроса
			protected function create_new_query_AJAX(){
				include_once ('./libs/php/classes/client_class.php');
				$this->CLIENT = new Client;
				$this->CLIENT->button_new_query_wtidth_cabinet();
			}

			// прикрепляет клиента к запросу
			protected function attach_client_for_new_query_AJAX(){
				include_once ('./libs/php/classes/client_class.php');
				new Client;
				exit;
			}
			
			


		function __destruct() {			
		}


		
		
   	}

/**
* 
*/
class CabinetMainMenu extends Cabinet{
	
	function __construct($Query,$user){

		$this->user_access = $user['access'];
		$this->user_id = $user['id'];
		// echo '<pre>';
		// print_r($this->user_access);
		// echo '</pre>';
		$this->Query = $Query;
		$this->get_menu();
		// echo 'as2d';
	}

	// для менеджера
	private function menu_5(){
		// проверяем принадлежность запроса другому менеджеру
		if((int)$this->Query['manager_id']>0 && $this->Query['manager_id'] != $this->user_id){
			$this->Query['status'] = 'disabled';
		}

		// меняем набор комманд относительно статуса запроса
		switch ($this->Query['status']) {
			// архив
			case 'history':
				$this->menu_list[] = array(
					'in_work' => array(
						'name_ru' => 'Вернуть в работу',
						'name_en' => 'in_work',
						'ajax' => 'command_for_change_status_query'
						)
					);
				break;
			// на рассмотрении	
			case 'taken_into_operation':
				$this->menu_list[] = array(
					'in_work' => array(
						'name_ru' => 'Взять в работу',
						'name_en' => 'in_work',
						'ajax' => 'command_for_change_status_query'
						),
					'not_process' => array(
						'name_ru' => 'Отказаться',
						'name_en' => 'refused',
						'ajax' => 'command_refused'
						),
					'get_a_list_of_managers_to_be_attached_to_the_request' => array(
						'name_ru' => 'Сменить куратора заявки',
						'name_en' => 'get_a_list_of_managers_to_be_attached_to_the_request',
						'ajax' => 'get_a_list_of_managers_to_be_attached_to_the_request'
						)
					);
				break;
			// в работе
			case 'in_work':
				$this->menu_list[] = array(
					'history' => array(
						'name_ru' => 'Переместить в архив',
						'name_en' => 'history',
						'ajax' => 'command_for_change_status_query'
						),
					'get_a_list_of_managers_to_be_attached_to_the_request' => array(
						'name_ru' => 'Сменить куратора заявки',
						'name_en' => 'get_a_list_of_managers_to_be_attached_to_the_request',
						'ajax' => 'get_a_list_of_managers_to_be_attached_to_the_request'
						)
					);
				// вспомогательный список
				// $this->menu_list[] = array(
				// 	'not_process' => array(
				// 		'name_ru' => 'Назначить другого клиента',
				// 		'name_en' => 'get_client_sherch_form',
				// 		'ajax' => 'get_client_sherch_form'
				// 		),
				// 	);
				break;
			// не обработанные
			case 'not_process':
				$this->menu_list[] = array(
					'in_work' => array(
						'name_ru' => 'Взять в работу',
						'name_en' => 'in_work',
						'ajax' => 'command_for_change_status_query'
						),
					'taken_into_operation' => array(
						'name_ru' => 'Взять на рассмотрение<br><span class="dop_grey_small_info">(скрыть от других)</span>',
						'name_en' => 'taken_into_operation',
						'ajax' => 'command_taken_into_operation'
						),
					'not_process' => array(
						'name_ru' => 'Отказаться',
						'name_en' => 'refused',
						'ajax' => 'command_refused'
						)
					);
				if($this->Query['dop_managers_id'] == "" && $this->Query['manager_id'] == $this->user_id){
					$this->menu_list[] = array(
					
							'get_a_list_of_managers_to_be_attached_to_the_request' => array(
						'name_ru' => 'Сменить куратора заявки',
						'name_en' => 'get_a_list_of_managers_to_be_attached_to_the_request',
						'ajax' => 'get_a_list_of_managers_to_be_attached_to_the_request'
						)
					);
				}
				// echo '{"response":"OK","function":"echo_message","message_type":"system_message","message":"'.base64_encode($message).'"}';
				// exit;
				break;
				case 'disabled':
					$message = "Вы не можете изменять данный запрос. С ним работает другой менеджер.";
					echo '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
				exit;
					break;
			default:
				$message = "Нет доступных комманд";
				echo '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
				exit;
			break;
		}
	}

	
	// для admin
	private function menu_1(){
		// для админа всегда одни и те же комманды
		
		// основной список
		$this->menu_list[] = array(
					'in_work' => array(
						'name_ru' => 'В работе Sales',
						'name_en' => 'in_work',
						'ajax' => 'command_for_change_status_query'
						),
					'not_process' => array(
						'name_ru' => 'Не обработан менеджером',
						'name_en' => 'not_process',
						'ajax' => 'command_for_change_status_query'
						),
					'history' => array(
						'name_ru' => 'Переместить в архив',
						'name_en' => 'history',
						'ajax' => 'command_for_change_status_query'
						),
					'taken_into_operation' => array(
						'name_ru' => 'Взять на рассмотрение<br><span class="dop_grey_small_info">(скрыть от других)</span>',
						'name_en' => 'taken_into_operation',
						'ajax' => 'command_taken_into_operation'
						),
					'command_not_process_admin' => array(
						'name_ru' => 'Ожидает распределения',
						'name_en' => 'not_process_admin',
						'ajax' => 'command_not_process_admin'
						),
					);
		// вспомогательный список
		$this->menu_list[] = array(
					'not_process' => array(
						'name_ru' => 'Назначить клиента<br><span class="dop_grey_small_info">(Сменить клиента)</span>',
						'name_en' => 'get_client_sherch_form',
						'ajax' => 'get_client_sherch_form'
						),
					);

	}

	// default
	private function menu_default(){
		echo '{"response":"OK"}';
		exit;
	}

	// 
	private function get_menu(){
		// собираем название метода для меню
		$get_my_menu = 'menu_'.$this->user_access;

		// если меню для данного уровня допуска существует
		if(method_exists($this, $get_my_menu)){
			$this->menu = $this->$get_my_menu();
		}else{
			// если не существует - грузим default
			$this->menu = $this->menu_default();
		}		


		// echo 'asd';
		$html = '';
		$n = 0;
		$html .= '<div id="get_commands_men_for_query">';
		
		$first_val = '';
		$status_query_enablsed_arr = array();
			
		// $htm	
		$i = 0;
		foreach ($this->menu_list as $menu_arr) {
			$html .= ($i>0)?'<div style="padding-top:5px;border-bottom: 1px dashed #E0E0E0;"></div>':'';
			$html .= '<ul class="check_one_li_tag">';
				foreach ($menu_arr as $key => $menu_list) {
					$html .= '<li data-ajax="'.$menu_list['ajax'].'" 
						data-name_en="'.$menu_list['name_en'].'" '.(($n==0)?'class="checked"':'').'>
						'.$menu_list['name_ru'].'</li>';
					
					if($n==0){$first_val = $menu_list['name_en'];}
					$n++;
				}
			$html .= '</ul>';			
			$i++;
		}
		$html .= '</div>';


		$html .= '<form>';
			$html .= '<input type="hidden" name="query_status" value="'.$first_val.'">';
			$html .= '<input type="hidden" name="manager_id" value="'.$this->user_id.'">';	
			if(!isset($_POST['rt_list_id']) && isset($_POST['row_id'])){
				$_POST['rt_list_id'] = $_POST['row_id'];
			}
			if(!isset($_POST['row_id']) && isset($_POST['rt_list_id'])){
				$_POST['row_id'] = $_POST['rt_list_id'];
			}
			$html .= '<input type="hidden" name="AJAX" value="command_for_change_status_query">';	

			// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
			unset($_POST['AJAX']);
			// перебираем остальные значения для передачи их далее
			foreach ($_POST as $key => $value) {
				$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}

		$html .= '</form>';

				
		echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите действие:"}';
		
		exit;		
	
	}
}