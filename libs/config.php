<?php

    define('ROOT', $_SERVER['DOCUMENT_ROOT'].'/os');
	define('HOST', 'http://'.$_SERVER['HTTP_HOST'].'/os');
	define('APELBURG_HOST', 'http://www.apelburg.ru');
	
	define('GIFTS_MENU_TBL','menu_for_gifts_new');
	define('GIFTS_MENU_OLD_TBL','menu_for_gifts');
	define('BASE_ARTS_CATS_RELATION','new__base__articles_categories_relation');
    define('ACTIVITY_LOG','activity_log');
	define('STAT_SEARCH','statistics_search');// таблица статистики поисковых запросов на сайте
    define('HOLIDAY_BTN', 'menu_for_gifts_new_holiday_button'); // список праздников для отображения в кнопке в шапке
    
    // names base tables
	// define('BASE_TBL','base');
	define('BASE_OLD_TBL','base');
	define('BASE_TBL','new__base');
	define('IMAGES_TBL','new__base_images');

	define('BASE_DOP_PARAMS_TBL','new__base__dop_params');

	define('BASE_PRINT_MODE_TBL','new__base__print_mode');
	define('BASE__PRINT_PLACES_TYPES_TBL','new__base__print_place_types');
	define('BASE__ART_PRINT_PLACES_REL_TBL','new__base__articles_print_places_relation'); // таблица связей , артикул - места нанесения
	//define('BASE__PRINT_PLACES_PRINT_TYPES_REL_TBL','new__base__print_places_print_types_relation'); // таблица связей , места нанесения - типы нанесения
	define('BASE__DOP_PARAMS_FOR_PRINT_TYPES_TBL','new__base__dop_params_for_print_types'); // таблица содержащая дополнительные параметры для расчета нанесения (такие как цвет нанесения)
	
	define('BASE_COLORS_TBL','new__base_colors');

	define('BASE_MATERIALS_TBL','new__base_material');

	// define('FILTERS_PRESET_TBL','new__filters_preset');
	
	//
	define('BASE__CALCULATORS_PRICE_TABLES_TBL','os__calculators_price_tables');
	define('BASE__CALCULATORS_Y_PRICE_PARAMS','os__calculators_y_price_params'); 
	define('BASE__PRICE_COMMENTS_TBL','os__calculators_prices_comments'); 
	define('BASE__CALCULATORS_COEFFS','os__calculators_coeffs'); 
	define('BASE__CALCULATORS_ADDITIONS','os__calculators_additions'); 
	define('BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL','os__calculators_print_types_sizes_places_relation'); // таблица связей , типы нанесения - размеры нанесения - места нанесения

    define("CLIENTS_TBL","os__client_list"); // таблица клиентов
    define("CLIENT_PERSON_REQ_TBL","os__clients_persons_for_requisites"); // список должностей для лиц емеющих право подписи
	define("CLIENT_REQUISITES_TBL","os__clients_requisites"); // таблица реквизитов клиентов
	define("CLIENT_REQUISITES_MANAGMENT_FACES_TBL","os__clients_requisites_management");// таблица лиц (контрагентов) имеющих право подписи 
													
	define("CLIENT_CONT_FACES_TBL","os__client_cont_faces_relation"); // таблица контактных лиц клиентов  
	define("CONT_FACES_CONTACT_INFO_TBL", 'os__contact_information'); // таблица контактной информации для контактных лиц (ВСЕХ КОНТАКТНЫХ ЛИЦ ИЗ ОС) и их компаний
	
	define("CLIENT_ADRES_TBL", 'os__addres_tbl');//таблица адресов // !!!!! ЗАМЕНИТЬ НАЗВАНИЕ КОНСТАНТЫ НА ADRES_TBL

	define("INVOICE_TBL","os__invoice_list"); // счета
	define("INVOICE_ROWS",'os__invoice_rows'); // строки позиций
	define("INVOICE_TTN",'os__invoice_TTN'); // строки ттн для таблицы счета
	define("INVOICE_PP",'os__invoice_PP'); // строки приходов по счетам
	define("INVOICE_COSTS",'os__invoice_costs');// счета от поставщиков
	define("INVOICE_COSTS_PAY",'os__invoice_costs_payment');// оплаты поставщикам
	define("INVOICE_COMMENTS","os__invoice_comments"); // таблица комментариев по счетам

	define("ACCOUNTING_PENSION","os__accounting_options_pension"); // 					таблица настроек пенсии
	define("ACCOUNTING_ZP_KON","os__accounting_options_kon"); // 						таблица настроек расчета ЗП менам по конечникам
	define("ACCOUNTING_ZP_REK","os__accounting_options_rek"); // 						таблица настроек расчета ЗП менам по рекламщикам
	define("ACCOUNTING_ACCRUALS","os__accounting_accruals"); // 						таблица рассчитанных начислений по зп
	define("ACCOUNTING_ACCRUALS_COMP","os__accounting_accruals_compensation_imp"); //	таблица зафиксированных компенсаций
	define("ACCOUNTING_ACCRUALS_DOP","os__accounting_accruals_compensation_dop"); //	таблица дополнителоьных компенсаций
	define("ACCOUNTING_ACCRUALS_PAY","os__accounting_payment"); //	таблица выплат по зарплате


	define("SUPPLIERS_TBL", "os__supplier_list"); // таблица поставщиков
	define("SUPPLIERS_ACTIVITIES_TBL", "os__suppliers_activities"); // таблица видов деятельности поставщиков
	define("SUPPLIERS_CONT_FACES_TBL", "os__supplier_cont_faces_relation"); // таблица контактных лиц поставщиков
	define("SUPPLIERS_RATINGS_TBL", "os__suppliers_rating"); // таблица контактных лиц поставщиков
	define("RELATE_SUPPLIERS_ACTIVITIES_TBL", "os__supplier_activity_relation"); // таблица соотношения клиентов и видов деятельности
	define("MANAGERS_TBL", "os__manager_list"); // таблица менеджеров
	define("COMPENSATION_TBL","os__manager_compensation"); // таблица компенсаций для работников компаниии
	define("MANAGERS_DOP_INFO_TBL","os__manager_dop_info"); // таблица менеджеров c дополнительной информацией
	define("PERSONAL_MANAGERS_GROUPS_TBL","os__personal_managers_groups"); // группы для страницы на сайте - "персональный менеджер"
	
	define("DEPARTMENTS_TBL","os__departments_tbl"); // таблица отделов
	define("RELATE_CLIENT_MANAGER_TBL","os__client_manager_relation"); // таблица соотношения клиентов и менеджеров
	define("RELATE_ORDER_MANAGER_CLIENT_TBL","os__order_manager_client_relation"); // таблица соотношения заказов менеджеров и клиентов 
	//define("RELATE_MANAGERS_BY_DEPARTMENTS_TBL","os__relate_managers_by_departments_tbl"); // таблица отношения менеджеров к отделам
	// define("CLIENT_ORDERS_TBL","orders"); // старая таблица заказов

	define("CLIENT_ORDERS_TBL","os__orders"); // таблица заказов
	
	
	// define("CLIENT_HISTORY", "os__log_client"); // история по изменениям клиента
	define("LOG_GENARAL", "os_log_general"); // общий лог
	define("LOG_CLIENT", "os__log_client"); // история по изменениям клиента
	define("LOG_SUPPLIER", "os__log_supplier"); // история по изменениям клиента

	define("CLIENT_ORDERS_TABLE_PART_TBL","orders_table_part"); // табличная часть заказов
	define("CALCULATE_TBL","os__orders_calculate_table"); // расчетная таблица
	define("CALCULATE_TBL_PROTOCOL","os__orders_calculate_insert_delete_protocol"); // протокол добавления, удаления строк из РТ 
	
	define("INVOICES_TBL","os__invoices_for_pay"); // таблица счетов
	define("INVOICES_TBL2","os__invoices_for_pay2"); // таблица счетов
	
	define("OUR_FIRMS_TBL","os__our_firms"); //
	define("OUR_FIRMS_MANAGEMENT_TBL","os__our_firms_management"); // таблица руководителей наших фирм
	define("OUR_AGREEMENTS_TBL","os__agreements"); // таблица типов договоров
	define("GENERATED_AGREEMENTS_TBL","os__generated_agreements"); // таблица созданных договоров
	define("GENERATED_SPECIFICATIONS_TBL","os__generated_specifications"); // таблица созданных договоров
	define("OFFERTS_TBL","os__offerts"); // таблица созданных договоров
	define("OFFERTS_ROWS_TBL","os__offerts_rows"); // таблица созданных договоров
	define("PLANNER","os__planner"); // 
	define("REMAINDER_PROTOCOL","os__remainder_protocol"); // 
	
	// заказы в кабинете
	define("CAB_ORDER_ROWS", "os__cab_orders_list");  // таблица заказов
	define("CAB_LIST_COMMENTS", "os__cab_orders_list_comments"); // таблица комментариев к заказу
	define("CAB_DOP_DATA_LIST_COMMENTS", "os__cab_orders_dop_data_comments"); // таблица комментариев к позициям заказа
	define("CAB_ORDER_MAIN","os__cab_order_main_rows"); // таблица запрошенных позиций (артикулов)
	define("CAB_ORDER_DOP_DATA","os__cab_orders_dop_data"); // таблица вариантов просчёта
	define("CAB_DOP_USLUGI", "os__cab_dop_uslugi"); // таблица доп услуг
	define("CAB_DOP_USLUGI_DOP_INPUTS", "os__our_uslugi_dop_inputs"); // список доп. полей для заполнения
	define("CAB_BILL_AND_SPEC_TBL", "os__cab_bill_and_specificate_tbl"); // таблица выставленных счетов / спецификаций
	define("CAB_PYMENT_ORDER", "os__cab_PP"); // таблица платёжных поручений по счётам
	define("CAB_PYMENT_PKO", "os__cab_PKO"); // таблица ПКО
	define("CAB_TTN", "os__cab_TTN"); // товарно-транспортные накладные


	// новая РТ
	define("RT_LIST", "os__rt_list"); // таблица запросов	
	define("RT_LIST_COMMENTS","os__rt_list_comments"); // 
	define("RT_MAIN_ROWS","os__rt_main_rows"); // 
	define("RT_MAIN_ROWS_GALLERY","os__rt_main_rows_gallery"); // 
	define("RT_DOP_DATA","os__rt_dop_data"); // 
	define("RT_DOP_USLUGI","os__rt_dop_uslugi"); // 
	define("RT_ART_SIZE","os__rt_art_sizes"); // 

	// АРХИВ ВАРИАНТОВ В РТ НЕ УЧАВСТВУЕТ
	define("DOP_USLUGI_HIST", 'os__dop_uslugi_history');
	define("DOP_DATA_HIST", 'os__dop_data_history');

	// новые таблицы для РТ
	define("FORM_ROWS_LISTS", "form_rows_for_lists"); // поля для генератора форм  OLD
	define("FORM_INPUTS", "os__forms_input"); // поля для генератора форм 
	// define("FORM_GROUP_INPUTS", "os__forms_group_inputs"); // поля для генератора форм 
	define("FORM_GROUPS", "os__forms_tovar_group"); // группы товаров
	define("FROM_SECTIONS", "os__forms_tovar_group_sections"); // секции группы товаров

	define('USLUGI_STATUS_LIST', 'os__our_uslugi_status_list'); // таблица статусов этапов выполнения для услуг
	define("OUR_USLUGI_LIST","os__our_uslugi"); // Список всех услуг с ценами

	
	define("KP_LIST","os__kp_list"); // КП
	define("KP_MAIN_ROWS","os__kp_main_rows"); // ряды позиций КП
	define("KP_MAIN_ROWS_GALLERY","os__kp_main_rows_gallery"); // ряды позиций КП
	define("KP_DOP_DATA","os__kp_dop_data"); // ряды расчетов КП
	define("KP_DOP_USLUGI","os__kp_dop_uslugi"); // ряды даннх об услугах КП
	
	define("COM_PRED_LIST_OLD","os__com_pred_list_old"); // КП
	define("LAST_COM_PRED_NUM","os__last_com_pred_num"); // последний номер КП
    
	
	$client_id = (isset($_GET['client_id']))? $_GET['client_id'] : false ;
	
	$suppliers_data_by_prefix = array( 15 => array('name'=>'интерпрезент','link'=>'http://www.happygifts.ru/catalog_new/search/?q='),
	                                   26 => array('name'=>'оазис','link'=>'http://www.oasiscatalog.com/search?q='),
							           37 => array('name'=>'проект','link'=>'http://www.gifts.ru/search?text='),
									   59 => array('name'=>'макрос','link'=>'http://cabinet.makroseuro.ru/catalogue/search/?keyword='),
									  'e_'=> array('name'=>'ебазар','link'=>'http://ebazaar.ru/catalog/?q='),
									  'm_' => array('name'=>'', 'link'=>'#'),
									  'FF' => array('name'=>'Апельбург', 'link'=>'#')
									   );
									   
	//
	$print_mode_names = array( 'tampoo' => 'тампопечать',
	                           'textil_shelk' => 'шелкография',
							   'tisnenie' => 'тиснение',
							   'other' => 'другое',
							   'tampoo_ra1' => 'тампопечать РА 1',
							   'textil_shelk_ra1' => 'шелкография РА 1',
							   'tisnenie_ra1' => 'тиснение РА 1');
							   
	
	$month_day_name_arr = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	
	$num_word_transfer_arr = array(
	              array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
	              array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
				  array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
	              array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
				  array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
		          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот')
				  );
	$num_word_transfer_razriad_arr = array('','','','тысяч','','','миллионов','','','миллиардов');
    	
	$desjatichn_word_transfer_arr = array('десять  один'=>'одиннадцать',
	                                       'десять  два'=>'двенадцать',
										   'десять  три'=>'тринадцать',
										   'десять  четыре'=>'четырнадцать',
										   'десять  пять'=>'пятнадцать',
										   'десять  шесть'=>'шестнадцать',
										   'десять  семь'=>'семьнадцать',
										   'десять  восемь'=>'восемьнадцать',
										   'десять  девять'=>'девятьнадцать');
    $change_word_ending_arr_I = array('один  рублей'=>'один  рубль',
	                                       'два  рублей'=>'два  рубля',
										   'три  рублей'=>'три  рубля',
										   'четыре  рублей'=>'четыре  рубля');
    $change_word_ending_arr_II = array('один  тысяч'=>'одна  тысяча',
	                                       'два  тысяч'=>'две  тысячи',
										   'три  тысяч'=>'три  тысячи',
										   'четыре  тысяч'=>'четыре  тысячи');
	
	$change_word_ending_arr_III = array('один миллионов'=>'один  миллион',
	                                       'два миллионов'=>'два  миллиона',
										   'три миллионов'=>'три  миллиона',
										   'четыре миллионов'=>'четыре  миллиона');
   $change_word_ending_arr_IV = array('один миллиардов'=>'один  миллиард',
	                                       'два миллиардов'=>'два  миллиарда',
										   'три миллиардов'=>'три  миллиарда',
										   'четыре миллиардов'=>'четыре  миллиарда');
									
	//print_r($m_arr);
	// include_once('mysql.php');
	if(isset($_GET['page']) && $_GET['page']=="samples"){
	$img_catalog = "../img/";
	$query = "SELECT * FROM `base_images`";
        $result = mysql_query($query,$db);
        if(!$result)exit(mysql_error());
        if(mysql_num_rows($result) > 0){
        		while($item = mysql_fetch_assoc($result)){
        			$img_arr[$item['art']][$item['size']] = $item['name'];  
                }
        }
	}

	$global_performer_type = array(
		'supplier' => 'Поставщик',
		'delivery' => 'Доставка',
		'pr_vo' => 'Пр-во',
		'0' => 'Не указан',
		'' => 'Не указан' 
		);

	// исполнитель услуг по правам
    $gen_performer = array(
		'1' => 'Админ',
		'2' => 'Бухгалтерия',
		'4' => 'Производство',
		'5' => 'Менеджер',
		'6' => 'Водитель',
		'7' => 'Склад',
		'8' => 'Снабжение',
		'9' => 'Дизайнер' 
		);
	// статусы пользователей по позициям
	$STATUS_LIST = array(
		'0'=> array(), 
		'1'=> array(), // админ
		'2'=> array(
			0 => 'счёт выставлен',
			1 => 'ждём оплаты',
			2 => 'оплачен',//дата в таблицу
			3 => 'частично оплачен',//дата в таблицу			
			4 => 'приходник на залог',
			5 => 'аннулирован',			
			6 => 'приходник на залог',
			7 => 'возврат залога клиенту',
			8 => 'возврат денег по счёту',
			9 => 'огрузочные приняты (подписанные)'
			), // бух
		'4'=> array(
			0 => 'распаковка',
			1 => 'распаковано',
			2 => 'трансферы отпечатаны',
			3 => 'в печати',
			4 => 'напечатано __%',
			5 => 'упаковка',
			6 => 'готово производство'
		), // пр-во
		'5'=> array(
			0 => 'дизайн утвержден',
			1 => 'согласование МАКЕТА с клиентом',
			2 => 'согласование ДИЗАЙНА с клиентом',
			3 => 'в работе'
			), //мен
		'6'=> array(
			0 => 'в очереди',
			1 => 'в работе',
			2 => 'доставлено',
			), // водитель
		'7'=> array(
			0 => 'на складе',
			1 => 'отгружено'
			), // склад
		'8'=> array(	// выкуп продукции есть следствие
			0 => 'ожидает счет от поставщика',
			1 => 'Продукция выкуплена',
			2 => 'Ожидаем отправку постащика',
			3 => 'Продукция ожидается :'
			), // снаб
		'9'=> array(			
			0 => 'дизайн готов',// НУЖНО СОГЛАСОВАНИЕ
			1 => 'макет готов', // НУЖНО СОГЛАСОВАНИЕ
			2 => 'верстка готова', 
			3 => 'ожидает обработки',
			4 => 'задача принята ожидает',
			5 => 'Пленки отправлены',
			6 => 'в работе',
			7 => 'Клише заказано 00,00,00',
			9 => 'ожидает соглосования',
			10 => 'задача не принята',
			11 => 'очередь № __',
			12 => 'новая правка на дизайн'
			
			// подразумевается, что очередь выставляется автоматичеки 
			//(очередь 1, очередь 2, очередь 3 .. и т.д.)
			) // диз
	);


# ОПИСАНИЕ ТИПОВ ТОВАРОВ
//cat  - каталог
//pol - полиграфия листовая
//pol_many - полиграфия многолистовая
// calendar - икалендарь
//packing - упаковка картон
//packing_other - упаковка другая
//ext - сувениры под заказ
//ext_cl - сувениры клиента

$suppliers_names_by_prefix_for_get_name = array( 
	15 => 'Интерпрезент',
	26 => 'Оазис',
	37 =>  'Проект 111',
	59 =>  'Макрос',
	61 =>  'XINDAO',
	'e_' =>  'Ебазар',
	'm_' => 'Макрос',
	'FF' => 'Апельбург'
);
$title = "Онлайн сервис";