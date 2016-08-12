<?php 
/*
в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Если метод работает с базой сначала указывается обравиатура Database 
и уже потом Тип возвращаемых данных






PS было бы неплохо взять взять это за правило 

*/
    class Forms{
		// id пользователя
    	private $user_id;


    	private $user_access;

    	// тип продукта с которым работает форма
    	private $type_product;
		
		// сюда будем сохранять id html элементов формы, чтобы иметь понятие какие id мы использовать уже не можем
     	// id в основной своей массе используются для label
     	private $id_closed =array(); 
     	
     	// html код отвечающий за удаление записи, 
     	// которую добаил менеджер для личного пользования
     	private $span_del = '<span class="delete_user_val">X</span>';

     	# ОПИСАНИЕ ТИПОВ ТОВАРОВ и ИХ ГРУПП
			//cat  - каталог
			//pol - полиграфия листовая
			//pol_many - полиграфия многолистовая
			// calendar - икалендарь
			//packing - упаковка картон
			//packing_other - упаковка другая
			//ext - сувениры под заказ
			//ext_cl - сувениры клиента

     	# true/false разрешаем или запрещаем работу класса с ними
     	public $arr_section_product = array(
     		'souvenirs' => array(
     			'name' => 'Сувениры',
     			'sections' => array(
     				'cat' => array(
						'name' => 'Продукция с сайта',
						'readonly' => false,
						'access' => true,
						'description' => 'которые есть на сайте Апельбург и имеют артикул, так же Вы можете добавить данный товар из корзины на сайте'
						),
					'ext' => array(
						'name' => 'Сувениры под заказ',
						'readonly' => false,
						'access' => true,
						'description' => 'Антистресс, Алкоголь, Ароматизатор, Аксессуары для мобильных, 
                                             Бандана, Банный набор, Бейсболка, Бокал, Бочонок, Браслет, Брелок, Брюки, Бутылка,
                                             Ваза, Варенье , Ветровка, Внешний аккумулятор, Воздушные шар, Вымпел, 
                                             Галстук, Георгиевская ленточка, 
                                             Держатель, Диск CD, Дисконтная карта, Драже мятное, 
                                             Ежедневник под заказ, Жилет, Зажигалка, Значок, Зонт, Игрушка, 
                                             Календарь сувенирный, Карамель, Карандаш, Карты игральные, Карта пластиковая, Каски строительные, Коврики для мыши, Коврики на торпеду, Косметичка, Костер, Косынка, Кофейный набор, Кружка, Кубарик-трансформер, 
                                             Ланч-бокс, Ланъярд, Леденец, Лента, Ложки, 
                                             Магнит, Мармелад, Масленка, Мат, Медаль, Мешочек, Миндаль, Миски, Модель, Монетница, Мышь, Мяч, 
                                             Наборы, Нанобокс, Носки, Обложки, 
                                             Пазл, Папка-уголок, Печенье, Планшет, Платок, Плед , Плитка шоколада, Подставка под горячее, Поло, Полотенце, Полиграфическая вставка, Полукомбинезон, Портмоне, Портсигар, 
                                             Рамка для номера, Рулетка, Ручка, Рюкзак, 
                                             Салфетка, Светоотражатель, Ситечко для чая , Скатерть, Скотч, Скрепка, Сланцы, Стакан, Стелла, Сумка, 
                                             Табличка, Толстовка, Универсальный внешний аккумулятор, 
                                             Фартук, Фишки,Флаг тканевый, Флажок сувенирный, Флешка, Фоторамка, Футболка, Чай, Чайник, Чайный набор, Часы наручные, Чехол, 
                                             Шампанское , Шар новогодний, Шарик воздушный, Шарфы, Шашки, Шнурок, Шоколад , 
                                             Электрочайник, CD диск, DVD-диск, PVC-флешка, PVC-шеврон'
						),

					'ext_cl' => array(
						'name' => 'Сувениры клиента',
						'readonly' => false,
						'access' => true,
						'description' => 'которые нам привозят только для нанесения. (Мы не покупаем товар - товар клиента)'
						)
     				)
     			),
     		'polygraphy' =>  array(
     			'name' => 'Полиграфия',
     			'sections' => array(
	     			'pol' => array(
						'name' => 'Листовая',
						'readonly' => false,
						'access' => true,
						'description' => 'Афиша, Баннер, Баннерный стенд, Бирка, Бланк, Буклет, 
                                        Визитка, Вставка, Выставочные стенды, Газета, Грамота, Диплом, Евробуклет, Карточка, Календарик карманный, Конверт, 
                                        Листовка, Мобильные панели, Мобильные стенды, Наклейка, Открытка, 
                                        Пакет, Папка, Папка вырубная, Плакат, Пресс волл, Приглашения, Путеводитель, 
                                        Разделители, Рекламный щит, Ростовые фигуры, Сертификат, Стикеры, 
                                        Фирменный бланк, Флаерс, Флажок бумажный, Фотография, Этикетка, PopUp, RollUp'
						),
					'pol_many' => array(
						'name' => 'Многолистовая',
						'readonly' => false,
						'access' => true,
						'description' => 'Альбом, Блок для записей, Блокнот, Брошюра, Каталог, Книга, Книжка, Кубарик, Нотная тетрадь, Планинги, Тетрадь'
						),
     				)
     			),
     		'calendars' =>  array(
     			'name' => 'Календари',
     			'sections' => array(
	     			'quarterly_calendar' => array(
						'name' => 'Квартальные',
						'readonly' => false, 
						'access' => true,
						'description' => 'Трио, Макси, Моно, Люкс Квадро, Рекламный, New Профи, New Футура, Гранд, Евростандарт, Максима, New Эксперт, Премиум, Классика, Спектрум 1, Спектрум 2, Квадро, Концепт, Имидж, Универсал, Рационал, Прагматика'
						),
					'wall_calendar' => array(
						'name' => 'Настенные',
						'readonly' => true,
						'access' => true,
						'description' => 'Календарь перекидной настенный, Календарь-плакат настенный'
						),
					'desktop_calendar' => array(
						'name' => 'Настольные',
						'readonly' => true,
						'access' => true,
						'description' => 'Календарь перекидной домик'
						)
     				)
     			),
     		'packing' =>  array(
     			'name' => 'Упаковка',
     			'sections' => array(
	     			'boxes' => array(
						'name' => 'коробки',
						'readonly' => true,
						'access' => true,
						'description' => 'Коробка “крышка-дно”, Коробка “шкатулка”, Коробка “самосборная”, Коробка “четырехклапанная”, Коробка “футляр”, Коробка “круглая”, Коробка “книга”, Коробка “пицца”, Коробка под бутылку, Тубус, Шубер'
						),
					'package' => array(
						'name' => 'Пакеты',
						'readonly' => true,
						'access' => true,
						'description' => 'Пакеты бумажные, Пакеты ПВД, Пакеты ПНД, Пакеты картонные, Пакеты имитлин, Пакеты эфалин'
						),
					'other_packing' => array(
						'name' => 'Остальное',
						'readonly' => true,
						'access' => true,
						'description' => 'Банка, Корзина, Короб, Коробка для вина, Коробка для чая, Посылочный ящик, Сундук, Футляр с выдвижной крышкой, из дерева, из шпона, из МДФ, из рейки, из ДВП, из ДСП, из ПВХ, из фетра, из платика, из спанбонда, из жести, из фанеры'
						)
     				)
     			)
     		);

     	
		

     	// перечисление разрешённых разделов полей 
     	// а так же некоторая необходимая для их обработки информация
     	// имя на кириллице
     	// доп описание по заполняемой форме
     	// наличие кнопки клонирования раздела формы
     	// наличие кнопки добавления своего варианта // копирует самый нижний imput
     	public $form_type = array(
     		'pol_many' => array(
     			'name_product'=>array(
     				'name'=>'Наименование',
     				'moderate'=>true,
     				'note'=>'укажите название изделия без дополнительных уточнений. Уточнения Вы можете добавить в поле Доп. наименование.',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false // кнопка отмены всех выбранных
     				),
     			'product_dop_text'=>array(
     				'name'=>'Доп. наименование',
     				'moderate'=>false,
     				'note'=>'краткое название, уточнение. Этот текст будет отображаться в сервисе сразу же за Наименованием. например: Брошюра для юных участников, где "для юных участников" - это доп. наименование',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'quantity'=>array(
     				'name'=>'Тираж',
     				'moderate'=>true,
     				'note'=>'укажите тираж изделий',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false
     				),
     			'format'=>array(
     				'name'=>'Формат',
     				'moderate'=>true,
     				'note'=>'единицы измерения - миллиметры.',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false
     				),
     			// 'material' =>array(
     			// 	'name'=>'Материал',
     			// 	'moderate'=>true,
     			// 	'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), название материала (Splendorgel-Сплендоргель)',
     			// 	'btn_add_var'=>false,
     			// 	'btn_add_val'=>false,
     			// 	'cancel_selection' =>false
     			// 	),
     			// 'plotnost' =>array(
     			// 	'name'=>'Плотность материала',
     			// 	'note'=>'плотность (130гр, 170гр,300гр и т.д.)',
     			// 	'moderate'=>true,
     			// 	'btn_add_var'=>false,
     			// 	'btn_add_val'=>false,
     			// 	'cancel_selection' =>false
     			// 	),
     			// 'type_print' =>array(
     			// 	'name'=>'Вид печати',
     			// 	'moderate'=>false,
     			// 	'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
     			// 	'btn_add_var'=>true,
     			// 	'btn_add_val'=>false,
     			// 	'cancel_selection' =>false
     			// 	),
     			// 'change_list' => array(
     			// 	'name'=>'Изменение листа',
     			// 	'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
     			// 	'moderate'=>false,
     			// 	'btn_add_var'=>true,
     			// 	'btn_add_val'=>true,
     			// 	'cancel_selection' => true
     			// 	),
     			// 'laminat' => array(
     			// 	'name'=>'Ламинат',
     			// 	'note'=>'укажите при необходимости вид обработки поверхности листа',
     			// 	'moderate'=>false,
     			// 	'btn_add_var'=>true,
     			// 	'btn_add_val'=>false,
     			// 	'cancel_selection' =>true
     			// 	),
     			// 'lak' => array(
     			// 	'name'=>'Лак',
     			// 	'note'=>'укажите при необходимости вид обработки поверхности листа',
     			// 	'moderate'=>false,
     			// 	'btn_add_var'=>true,
     			// 	'btn_add_val'=>false,
     			// 	'cancel_selection' =>true
     			// 	),
     			'date_calc_snab' => array(
     				'name'=>'Желаемый срок готовности рассчета',
     				'note'=>'стандартно, или до дата.',
     				'moderate'=>true,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'date_print' => array(
     				'name'=>'Срок сдачи заказа клиенту',
     				'note'=>'укажите дату если необходима конкретная дата отгрузки',
     				'moderate'=>true,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'how_mach' => array(
     				'name'=>'Бюджет',
     				'note'=>'',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'images' => array(
     				'name'=>'Путь к макету',
     				'note'=>'если есть картинка или фото',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'dop_info' => array(
     				'name'=>'Пояснения',
     				'note'=>'укажите дополнительную информацию, если такая имеется',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				)
    			),  
               'pol' => array( // поисание формы для полиграфической продукции
                    'name_product'=>array(
                         'name'=>'Наименование',
                         'moderate'=>true,
                         'note'=>'укажите название изделия',
                         'btn_add_var'=>false, // кнопка + вариант
                         'btn_add_val'=>true, // кнопка + значение
                         'cancel_selection' =>false // кнопка отмены всех выбранных
                         ),
                    'product_dop_text'=>array(
                         'name'=>'Доп. наименование',
                         'moderate'=>false,
                         'note'=>'Открытка для юных участников, где "для юных участников" - это доп. наименование',
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'quantity'=>array(
                         'name'=>'Тираж',
                         'moderate'=>true,
                         'note'=>'укажите тираж изделий',
                         'btn_add_var'=>false,
                         'btn_add_val'=>true,
                         'cancel_selection' =>false
                         ),
                    'format'=>array(
                         'name'=>'Формат готового изделия',
                         'moderate'=>true,
                         'note'=>'единицы измерения - миллиметры',
                         'btn_add_var'=>false,
                         'btn_add_val'=>true,
                         'cancel_selection' =>false
                         ),
                    'format_list'=>array(
                         'name'=>'Формат изделия в развороте',
                         'moderate'=>false,
                         'note'=>'если изделие имеет сложения. Единицы измерения - миллиметры',
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'material' =>array(
                         'name'=>'Материал',
                         'moderate'=>true,
                         'note'=>'картон или бумага / название / цвет. Например: Дизайнерская бумага, Sirio Pearl, Ice white - ледяной белый',
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'plotnost' =>array(
                         'name'=>'Плотность материала',
                         'note'=>'135 г/м², 180 г/м², 301 г/м² и т.д',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>true,
                         'cancel_selection' =>false
                         ),
                    'type_print' =>array(
                         'name'=>'Вид печати',
                         'moderate'=>false,
                         'note'=>'',
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'change_list' => array(
                         'name'=>'Изменение листа',
                         'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>true,
                         'cancel_selection' => true
                         ),
                    'laminat' => array(
                         'name'=>'Ламинат',
                         'note'=>'укажите при необходимости вид обработки поверхности листа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>true
                         ),
                    'lak' => array(
                         'name'=>'Лак',
                         'note'=>'укажите при необходимости вид обработки поверхности листа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>true
                         ),
                    'date_calc_snab' => array(
                         'name'=>'Желаемый срок готовности рассчета',
                         'note'=>'стандартно, или до дата.',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'date_print' => array(
                         'name'=>'Срок сдачи заказа клиенту',
                         'note'=>'укажите дату если необходима конкретная дата отгрузки',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'how_mach' => array(
                         'name'=>'Бюджет',
                         'note'=>'',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'images' => array(
                         'name'=>'Путь к макету',
                         'note'=>'если есть картинка или фото',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'dop_info' => array(
                         'name'=>'Пояснения',
                         'note'=>'укажите дополнительную информацию, если такая имеется',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         )
               ), 
               'quarterly_calendar' => array( // поисание формы для полиграфической продукции
                    'name_product'=>array(
                         'name'=>'Наименование',
                         'moderate'=>true,
                         'note'=>'укажите название изделия',
                         'btn_add_var'=>false, // кнопка + вариант
                         'btn_add_val'=>true, // кнопка + значение
                         'cancel_selection' =>false // кнопка отмены всех выбранных
                         ),
                    'product_dop_text'=>array(
                         'name'=>'Доп. наименование',
                         'moderate'=>false,
                         'note'=>'текст который будет виден в РТ сразу же за Намименованием. К примеру: Открытка № 1, где "№ 1" - это доп. наименование',
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'quantity'=>array(
                         'name'=>'Тираж',
                         'moderate'=>true,
                         'note'=>'укажите тираж изделий',
                         'btn_add_var'=>false,
                         'btn_add_val'=>true,
                         'cancel_selection' =>false
                         ),
                    'format'=>array(
                         'name'=>'Формат',
                         'moderate'=>true,
                         'note'=>'укажите формат (мм)',
                         'btn_add_var'=>false,
                         'btn_add_val'=>true,
                         'cancel_selection' =>false
                         ),
                    'material' =>array(
                         'name'=>'Материал',
                         'moderate'=>true,
                         'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), название материала (Splendorgel-Сплендоргель)',
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'plotnost' =>array(
                         'name'=>'Плотность материала',
                         'note'=>'плотность (130гр, 170гр,300гр и т.д.)',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'type_print' =>array(
                         'name'=>'Вид печати',
                         'moderate'=>false,
                         'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'change_list' => array(
                         'name'=>'Изменение листа',
                         'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>true,
                         'cancel_selection' => true
                         ),
                    'laminat' => array(
                         'name'=>'Ламинат',
                         'note'=>'укажите при необходимости вид обработки поверхности листа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>true
                         ),
                    'lak' => array(
                         'name'=>'Лак',
                         'note'=>'укажите при необходимости вид обработки поверхности листа',
                         'moderate'=>false,
                         'btn_add_var'=>true,
                         'btn_add_val'=>false,
                         'cancel_selection' =>true
                         ),
                    'date_calc_snab' => array(
                         'name'=>'Желаемый срок готовности рассчета',
                         'note'=>'стандартно, или до дата.',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'date_print' => array(
                         'name'=>'Срок сдачи заказа клиенту',
                         'note'=>'укажите дату если необходима конкретная дата отгрузки',
                         'moderate'=>true,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'how_mach' => array(
                         'name'=>'Бюджет',
                         'note'=>'',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'images' => array(
                         'name'=>'Путь к макету',
                         'note'=>'если есть картинка или фото',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         ),
                    'dop_info' => array(
                         'name'=>'Пояснения',
                         'note'=>'укажите дополнительную информацию, если такая имеется',
                         'moderate'=>false,
                         'btn_add_var'=>false,
                         'btn_add_val'=>false,
                         'cancel_selection' =>false
                         )
               )    
     	);


		function __construct(){
			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $_SESSION['access']['access'];

			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}
		}
		//////////////////////////
		//	methods_AJAX  -- start
		//////////////////////////
			########   вызов AJAX   ########
			private function _AJAX_($name){
				$method_AJAX = $name.'_AJAX';
				// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
				if(method_exists($this, $method_AJAX)){
					$this->$method_AJAX();
					exit;
				}					
			}

			// проверяем наличие артикула на сайте, выводим его описание при нахождении
			private function check_exists_articul_AJAX(){
				$html = '';
				if(strlen($_POST['art']) < 4){
					$html .= '<div class="inform_message red">Количество символов в артикуле должно быть не менее 4 (четырёх) символов.</div>';
					echo '{"response":"OK","html":"'.base64_encode($html).'"}';
					exit;
				}
                    $html .= '<form>';

				// делаем запрос в базу по артикулу
				$art_arr = $this->search_articule_Database($_POST['art']);

				
				// получаем количесвто найденных совпадений
				$count = count($art_arr);
				switch ($count) {
					case 1: // всё впорядке, мы нашли то, что искали
						$html .= '<div class="inform_message">Найдено <strong>одно</strong> совпадение</div>';
                              $html .= '<table id="choose_one_of_several_articles">';
                              $html .= '<tr>';
                              $html .= '<th>п</th>';
                              $html .= '<th>Арт.</th>';
                              $html .= '<th>Название</th>';
                              $html .= '<th>Поставщик</th>';
                              $html .= '<th>Апл</th>';
                              $html .= '</tr>';
                              $n = 1;

                              $html .= '<tr data-art_id="'.$art_arr[0]['id'].'"  data-art_name="'.$art_arr[0]['name'].'" data-art="'.$art_arr[0]['art'].'" class="checked">';
                              $html .= '<td>'.$n++.'</td>';
                              $html .= '<td>'.$art_arr[0]['art'].'</td>';
                              $html .= '<td>'.$art_arr[0]['name'].'</td>';
                              $html .= '<td>'.identify_supplier_by_prefix($art_arr[0]['art']).'</td>';
                              $html .= '<td><a target="_blank" href="http://www.apelburg.ru/description/'.$art_arr[0]['id'].'/">на сайт</a></td>';
                              $html .= '</tr>';

                              // добавляем полное описание артикула
                              if(trim($art_arr[0]['description']) != ''){
                                   $html .= '<tr>';
                                   $html .= '<td colspan="5">';
                                   $html .= '<div>'.$art_arr[0]['description'].'</div>';
                                   $html .= '</td>';
                                   $html .= '</tr>';     
                              }
                              

                              $html .= '</table>';
                              
                              // добавляем скрытые поля
                              $html .= '<input type="hidden" name="AJAX" value="insert_in_database_new_catalog_position">';
                              $html .= '<input type="hidden" name="art_id" value="'.$art_arr[0]['id'].'">';
                              $html .= '<input type="hidden" name="art" value="'.$art_arr[0]['art'].'">';
                              $html .= '<input type="hidden" name="art_name" value="'.$art_arr[0]['name'].'">';
                              $html .= '</form>';

						break;
					case 0: // мы ненашли ничего
						$html = '<div class="inform_message red">Такого артикула нет в базе. Попробуйте ввести другое значение.</div>';
						break;
					
					default: // мы нашли более одного совпадения
						$html .= '<div class="inform_message">Найдено <strong>'.$count.'</strong> совпадения(й). Пожалуйста уточните Ваш запрос.</div>';
						$html .= '<table id="choose_one_of_several_articles">';
						$html .= '<tr>';
						$html .= '<th>п</th>';
						$html .= '<th>Арт.</th>';
						$html .= '<th>Название</th>';
						$html .= '<th>Поставщик</th>';
						$html .= '<th>Апл</th>';
						$html .= '</tr>';
						$n = 1;

						foreach ($art_arr as $key => $articul) {
							$html .= '<tr data-art_id="'.$articul['id'].'" data-art_name="'.$articul['name'].'" data-art="'.$articul['art'].'" '.(($key==0)?'class="checked"':'').'>';
							$html .= '<td>'.$n++.'</td>';
							$html .= '<td>'.$articul['art'].'</td>';
							$html .= '<td>'.$articul['name'].'</td>';
							$html .= '<td>'.identify_supplier_by_prefix($articul['art']).'</td>';
							$html .= '<td><a target="_blank" href="http://www.apelburg.ru/description/'.$articul['id'].'/">на сайт</a></td>';
							$html .= '</tr>';
						}
						$html .= '</table>';

                              // добавляем скрытые поля
                              $html .= '<input type="hidden" name="AJAX" value="insert_in_database_new_catalog_position">';
                              $html .= '<input type="hidden" name="art_id" value="'.$art_arr[0]['id'].'">';
                              $html .= '<input type="hidden" name="art_name" value="'.$art_arr[0]['name'].'">';
                              $html .= '<input type="hidden" name="art" value="'.$art_arr[0]['art'].'">';
                              $html .= '</form>';
						break;
				}
                    

				echo '{"response":"OK","html":"'.base64_encode($html).'"}';

			}

               // добавление каталожного товара в РТ
               private function insert_in_database_new_catalog_position_AJAX(){
                    global $mysqli; 

                    if(!isset($_POST['chosen_size'])){
                         //////////////////////////
                         //  запрашиваем размеры к выбранному артикулу
                         //////////////////////////
                         $query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` where art_id = '".(int)$_POST['art_id']."'";
                         $size_arr = array();
                         $result = $mysqli->query($query) or die($mysqli->error);
                         if($result->num_rows > 0){
                              while($row = $result->fetch_assoc()){
                                   $size_arr[] = $row;
                              }
                         }

                         if(count($size_arr) > 1){ // если размеров более одного выводим новую форму с выбором размера
                              $html = '';
                              $html .= '<form>';
                              // выводим таблицу размеров

                              $html .= '<div class="inform_message">Выберите размер</div>';

                              $html .= '<table id="choose_the_size">';
                              $html .= '<tr><th colspan="2">'.$_POST['art_name'].'</th></tr>';
                              $html .= '<tr><th>Размер</th><th>Цена</th></tr>';
                              foreach ($size_arr as $key => $value) {
                                   $html .= '<tr>';
                                        $html .= '<td>';
                                             $html .= $value['size'];
                                        $html .= '</td>';
                                        $html .= '<td>';
                                             $html .= '<span>'.$value['price'].'</span> р.';
                                        $html .= '</td>';
                                   $html .= '</tr>';
                              }

                              $html .= '</table>';
                              $html .= '<input type="hidden" name="chosen_size" class="chosen_size" value="">';// размер
                              $html .= '<input type="hidden" name="price_out" class="price_out" value="">';// размер


                              // вывод скрытых полей переданных ранее
                              foreach ($_POST as $key => $value) {
                                   $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                              }
                              $html .= '</form>';


                              echo '{"response":"show_new_window" , "title":"Выберите размер","html":"'.base64_encode($html).'"}';
                              exit;

                         }else{
                              $this->price_out = $size_arr[0]['price'];
                         }

                    }else{
                         $this->price_out = $_POST['price_out'];
                    }

                    // echo '{"response":"none"}';

                    // echo $this->print_arr($this);

                    // echo $this->print_arr($_POST);

                    // exit;

                    //////////////////////////
                    //  осущевствляем проверку всех необходимых данных
                    //////////////////////////
                    if(isset($_GET['query_num'])){
                         $this->query_num = (int)$_GET['query_num'];
                    }else{
                         return 'не указан query_num';
                    }
                    //////////////////////////
                    //  Вставляем строку в main_rows
                    //////////////////////////
                    $this->sort_num = $this->get_sort_num();    

                    $query ="INSERT INTO `".RT_MAIN_ROWS."` SET
                         `query_num` = '".$this->query_num."',
                         `name` = '".trim($_POST['art_name'])."',
                         `date_create` = CURRENT_DATE(),
                         `art_id` = '".$_POST['art_id']."',
                         `art` = '".$_POST['art']."',
                         `type` = 'cat',
                         `sort` = '".$this->sort_num."'";                    
                   
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    $main_rows_id = $mysqli->insert_id;  

                    //////////////////////////
                    //  вставляем строку в dop_data
                    //////////////////////////
                     $query ="INSERT INTO `".RT_DOP_DATA."` SET
                         `row_id` = '".$main_rows_id."',                         
                         `price_out` = '".$this->price_out."',
                         `row_status` = 'green',

                         `glob_status` = 'в работе'";                    
                   
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    // $main_rows_id = $mysqli->insert_id; 


                    echo '{"response":"OK","function":"window_reload"}';

                    // echo $this->print_arr($_POST);
               }

			//search articule
			private function search_articule_Database($art){
				global $mysqli;
				$query = "SELECT * FROM `".BASE_TBL."` WHERE `art` LIKE '%".trim($art)."%';";
				$arr = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$arr[] = $row;
					}
				}
				return $arr;
			}



		//////////////////////////
		//	methods_AJAX  -- end
		//////////////////////////

          //////////////////////////
          //  methods
          //////////////////////////
     		// возвращает форму выбора заведения новой позиции в запрос
     		// осущевствляется выбор типа товара
     		# на вход подается номер запроса
     		public function to_chose_the_type_product_form_Html(){
     			# ОПИСАНИЕ ТИПОВ ТОВАРОВ
     			//cat  - каталог
     			//pol - полиграфия листовая
     			//pol_many - полиграфия многолистовая
     			// calendar - икалендарь
     			//packing - упаковка картон
     			//packing_other - упаковка другая
     			//ext - сувениры под заказ
     			//ext_cl - сувениры клиента
     			$html = '';
     			$html .= '<form>';
     			$html .= '<table id="get_form_Html_tbl">';
     			$html .= '<tr><th>Тип</th><th>Описание типа</th></tr>';
     			$i=0;
     			foreach ($this->arr_section_product as $section_product => $section_product_array) {
     				$html .= '<tr><td colspan="2"><div class="section_div">'.$section_product_array['name'].'</div></td></tr>'; // название раздела
     				
     				foreach ($section_product_array['sections'] as $key => $value) {
     					if($value['access']){

     						$readonly = ($value['readonly'])?'disabled':'';
     						$readonly_style = ($value['readonly'])?'style="color:grey"':'';

     						$html .= '<tr>';
     							$html .= '<td>';
     							$html .= '<input type="radio" name="type_product" id="type_product_'.$i.'" value="'.$key.'" '.$readonly.'><label '.$readonly_style.' for="type_product_'.$i.'">'.$value['name'].'</label>';
     							$html .= '</td>';
     							$html .= '<td>';
     							$html .= '<label '.$readonly_style.' for="type_product_'.$i.'">'.$value['description'].'</label>';
     							$html .= '</td>';
     						$html .= '</tr>';
     						$i++;
     					}

     				}
     			}

     			$html .= '</table>';			
     			
     			$html .= '<input type="hidden" name="AJAX" value="get_form_Html">';
     			$html .= '</form>';
     			return $html;
     		}

     		// возвращает форму для каталожной продукции
     		public function get_for_add_catalog_product(){
     			ob_start();	
     				
     				include_once './skins/tpl/client_folder/rt/add_new_position.tpl';

     				$html = ob_get_contents();
     			
     			ob_get_clean();
     			
     			return $html;
     		}

     		// возвращает html формы для заведения запроса на расчёт в отделе снабжения
     		public function get_product_form_Html($type_product){
     			
     			// если поля для запрошенного типа продукции описаны в классе
     			if(isset($this->form_type[$type_product]) && count($this->form_type[$type_product])!=0){
     				// получаем форму
     				$this->type_product = $type_product;

     				$form = self::get_form_Html($this->form_type[$this->type_product] , $type_product);
     				return $form;
     			}else{
     				// впротивном случае выводи ошибку
     				$error = "Такого типа продукции не предусмотрено. Обратитесь к администрации";
     				return $error;
     			}
     		}

     		// заносит новые варианты в базу, на вход принимает массив POST
     		public function insert_new_options_in_the_Database(){
     			$id_i = (isset($_GET['id'])?$_GET['id']:0);

     			// $query_num_i = (isset($this->POST['query_num']))?$_POST['query_num']:(isset($_GET['query_num'])?$_GET['query_num']:0);
     			$query_num_i =isset($_GET['query_num'])?$_GET['query_num']:0;

     			//type_product
     			$type_product = isset($_POST['type_product'])?$_POST['type_product']:0;

     			// проверяем наличие вариантов, если все впорядке идём дальше

     			if(!isset($_POST['json_variants']) || count($_POST['json_variants'])==0){return 'Не было создано ни одного варианта.';}
     			

     			// echo '<pre>';
     			// print_r($this->POST['json_general']);
     			// echo '</pre>';
     			

     			if($query_num_i!=0){
     				// если нам известен $query_num, то работа ведётся из РТ
     				
     				#/ получаем наименование и доп название позиции из Json
     				$arr = json_decode($_POST['json_general'],true);

     				
     				#/ заводим новую строку позиции и получаем её id
     				$new_position_id = $this->insert_new_main_row_Database($query_num_i,$arr,$type_product);
     				
     				#/ для каждой строки варианта заводим новую строку варианта с ценой равной нулю
     				
     				#/ Json
     				foreach ($_POST['json_variants'] as $key => $json_for_variant) {
     					// $str = json_decode(,true);
     					$this->insert_new_dop_data_row_Database($new_position_id,$json_for_variant);
     				}

     				// echo ;
     				echo 'OK';

     				return;

     			}else if($id_i){

     			// В ВЕРСИИ 1.0 ДЕИСТВИЯ С РЕДАКТИРОВАНИЕМ ВАРИАНТОВ ВНУТРИ ПОЗИЦИИ НЕ ПРЕДУСМОТРЕНЫ
     			return;

     				// если нам известен $id, то работа ведётся из позиции
     				#/ 1 выбираем json позиции и считываем его в массив1
     				#/ 2 считываем в массив2 новый json
     				#/ 3 свиреряем
     				#/ ? для каждой строки варианта заводим новую строку варианта с ценой равной нулю
     			}			
     			return 'неожиданный конец программы #0001';			
     		}

     		private function insert_new_dop_data_row_Database($new_position_id,$json_for_variant){
     			global $mysqli;	
     			// получаем информацию о тираже варианта
     			$arr = json_decode($json_for_variant,true);
     			$quantity = $arr['quantity'];

     			// исключаем информацию о тираже из json варианта
     			// unset($arr['quantity']);
     			//$json_for_variant = json_encode($arr);


     			// status_snab - присваиватся(по умолчанию) первый статус - on_calculation (на расчёт)
     			$query ="INSERT INTO `".RT_DOP_DATA."` SET
     				`row_id` = '".$new_position_id."',
     				`quantity` = '".$quantity."',
     				`price_in` = '0',
     				`price_out` = '0',
     				`create_date` = CURRENT_DATE(),
     				no_cat_json = '".addslashes($json_for_variant)."'";		 
     		    
     		    $result = $mysqli->query($query) or die($mysqli->error);
     			
     			return $mysqli->insert_id;
     		}
     		
     		private function get_sort_num(){
     			global $mysqli;
     			$query = "SELECT max(`sort`) AS `max_num` FROM `".RT_MAIN_ROWS."` WHERE `query_num` = '".(int)$_GET['query_num']."'";
     			$num = 0;
     			$result = $mysqli->query($query) or die($mysqli->error);
     			if($result->num_rows > 0){
     				while($row = $result->fetch_assoc()){
     					$num = $row['max_num']+1;
     				}
     			}
     			return $num;
     		}

     		private function insert_new_main_row_Database($query_num_i, $arr, $type_product){	
     			$this->sort_num = $this->get_sort_num();
     			// echo '<pre>';
     			// print_r($arr);
     			// echo '</pre>';
     			global $mysqli;	

     			$query ="INSERT INTO `".RT_MAIN_ROWS."` SET
     				`query_num` = '".$query_num_i."',
     				`name` = '".$arr['name_product'][0]." ".$arr['product_dop_text'][0]."',
     				`date_create` = CURRENT_DATE(),
     				`type` = '".$type_product."',
     				`sort` = '".$this->sort_num."',
     			    `dop_info_no_cat` = '".addslashes($_POST['json_general'])."'";				 
     		    
     		    $result = $mysqli->query($query) or die($mysqli->error);
     			
     			return $mysqli->insert_id;	
     		}

     		// обработка данных из формы
     		public function restructuring_of_the_entry_form($array_in,$type_product,$child = 0){
     			$html = '';
     			
     			// получаем массив описаний
     			$product_options = $this->form_type[$type_product];

     			//массив второстепенных описаний
     			$arr = $this->get_cirilic_names_keys_Database();
     			foreach ($arr as $key => $value) {
     				$all_name[$value['parent_name']] = array('name'=>$value['name_cirilic']); 
     			}		
     			// сливаем массив описаний из базы с основным массивом 
     			$product_options = array_merge($product_options,$all_name);
     			
     			// считаем количество возможных вариаций вариантов расчёта
     			
     			// объявляем массив
     			$array_for_table = array();
     			// перебираем входящие данные и пишем в массив

     			foreach ($array_in as $key => $value) {// перебор по полям
     				
     				// $value - всегда массивы, в противном случае это будет сервисная информация
     				if(!is_array($value)){continue;}

     				// собираем данные	
     				// название поля в кириллице
     				
     				foreach ($value as $k => $v) {// перебор по вариантам

     					$array_for_table[$key][]= implode('; ',$this->gg_Array($v,1,$product_options));
     					
     				}
     			}



     			$return = $this->greate_table_variants_Html($array_for_table,$product_options,$type_product);
     			

     			return $return;
               }

     		// выдаёт форму по типу продукции
     		public function get_form_Html($arr,$type_product){
     			// global $mysqli;
     			$html = '';
     			$html .= '<div id="general_form_for_create_product"><form>';
     			$html .= '<input type="hidden" name="AJAX" value="general_form_for_create_product">';
     			$html .= '<input type="hidden" name="type_product" value="'.$type_product.'">';
     			// перебираем массив разрешенных для данного типа товара полей
     			// echo '<pre>';
     			// print_r($arr);
     			// echo '</pre>';
     			foreach ($arr as $key => $value) {
     				$html .= '<div class="one_row_for_this_type '.$key.'" data-type="'.$key.'" data-moderate="'.$value['moderate'].'">';
     				
     				$moderate = ($value['moderate'])?'<span style="color:red; font-size:14px">*</span>':'';
     				// определяем имя поля
     				$html .= '<strong>'.$value['name'].' '.$moderate.'</strong><br>';

     				// доп описание по полю
     				$html .= ($value['note']!='')?'<div style="font-size:10px">'.$value['note'].'</div>':'';
     				
     				//для каждого поля запрашиваем форму
     				$html .= $this->generate_form_Html($this->get_form_Html_listing_Database_Array($type_product,$key),'',$type_product);
     				
     				// echo $html;				
     				// добавляем кнопки				
     				$html .= '</div>';	
     				$html .= '<div class="buttons_form">';
     				$html .= ($value['btn_add_var'])?'<span class="btn_add_var">+ вариант</span>':'';
     				$html .= ($value['btn_add_val'])?'<span class="btn_add_val">+ значение</span>':'';
     				$html .= ($value['cancel_selection'])?'<span class="cancel_selection">отменить</span>':'';
     				$html .= '</div>';

     			}	

     			$html .= '</form></div>';
     			return $html;
     		}

     		// возвращает таблицу всех возможных вариантов из множества, которое натыкал юзер
     		private function greate_table_variants_Html($arr,$product_options,$type_product){
     			
     			$arr = $this->delete_identical_variants_Array($arr);

     			// поучаем массив вариантов
     			$array = $this->greate_array_variants_Array($arr);

     			
     			// массив для сохранения предыдущего варианта при выводе строк вариантов
     			// нужен для выделения различий между каждым следующим вариантом
     			$prev_variant = array();

     			// перерабатываем его в таблицу
     			$html = '';
     			$html .= '<form>';
     			$html .= '<input type="hidden" name="AJAX" value="save_no_cat_variant">';
     			$html .= "<input type='hidden' name='json_general' value='".json_encode($arr)."'>";
     			$html .= "<input type='hidden' name='type_product' value='".$type_product."'>";
     			// $html .= '<div id="json_general" style="display:none">'.json_encode($arr).'</div>';
     			$html .= '<table class="answer_table">';
     			$html .= '<tr>';
     			$html .= '<th>№ варианта</th>';
     			$html .= '<th>Описание</th>';
     			$html .= '<th>удалить</th>';
     			$html .= '</tr>';

     			foreach ($array as $key => $variant) {

     				$html .= "<tr>";
     				$html .= '<td>'.($key+1);
     				// $html .= '<div class="json_hidden" style="display:none">'.json_encode($variant).'</div>';
     				$html .= "<input type='hidden' name='json_variants[]' value='".json_encode($variant)."'>";
     				$html .= '</td>';
     				$html .= '<td>';
     				foreach ($variant as $key1 => $value1) {
     					$bold = (isset($prev_variant[$key1]) && $prev_variant[$key1]!=$value1)?'bold':'normaol';
     					$html .= '<span style="font-weight:'.$bold.'">'.$product_options[$key1]['name'].'</span>: '.$value1.'<br>';
     				}
     				$html .= '</td>';
     				$html .= '<td><span class="delete_user_val">X</span></td>';
     						
     				$html .= '</tr>';

     				$prev_variant = $variant;
     			}
     			
     			$html .= '</table>';
     			$html .= '</form>';
     			return $html;
               }

     		// возвращает переработанный массив вариантов
     		private function greate_array_variants_Array($arr){
     			// подсчёт количества вариаций 
     			$count = 1;
     			foreach ($arr as $key => $value) {
     				$count = $count*count($value);
     			}		
     			
     			// создаем массив вариантов 
     			$n = 0;

     			// объявляем новый массив
     			$variants = array();

     			foreach ($arr as $key2 => $value2) {
     				
     				if ($n==0) {
     					$f=0;
     					foreach ($value2 as $key3 => $value3) {
     						for ($k=0; $k < $count/count($value2); $k++) { 
     							$variants[$f][$key2] = $value3;
     						$f++;		
     						}	
     					}
     					$n++;	
     				}else{
     					$f=0;
     					for ($k=0; $k < $count/count($value2); $k++) { 
     						foreach ($value2 as $key3 => $value3) {						
     							$variants[$f][$key2] = $value3;
     						$f++;		
     						}	//$f++;
     					}
     					$n=0;
     				}
     			}
     			return $variants;
     		}

     		//вычищаем дубли вариантов появившиеся из-за неверного заполнения формы
     		private function delete_identical_variants_Array($arr){
     			$new_arr = array();
     			foreach ($arr as $key => $value) {
     				$new_arr[$key][0] = $value[0];
     				foreach ($value as $key2 => $value2) {
     					$identical = 0;
     					foreach ($new_arr[$key] as $key3 => $value3) {
     						if($value3==$value2){// если такой уже есть 
     							$identical = 1;
     						}
     					}
     					if($identical==0){// если это не повтор
     						$new_arr[$key][] = $value2;
     					}

     				}
     			}
     			return $new_arr;
     		}
     		
     		// всомагательная функция обработки результатов выбора 
     		private function gg_Array($arr,$n=0,$product_options){
     			$html = array();
     			$i=0;$k=0;
     			foreach ($arr as $key1 => $val1) {// снимаем значения
     				if(is_numeric($key1)){
     					# если $key1 - число, то $val1 - то, что было выбрано или набрано
     					$html1 = $val1;

     					// прибавляем ключ
     					$html[(++$i)] = ($html1!='')?$html1.' ':' ';

     					$k=$i; //запоминаем ключ для сравнения
     				}else{
     					# если строка, то у предыдущего поля были дети и $val1 - массив
     					# кирилическое название детей хрнаится в базе
     					if(isset($product_options[$key1]['name']) && $product_options[$key1]['name']!=''){
     						$html[$i] .= $product_options[$key1]['name'].': '.implode(', ',$this->gg_Array($val1,0,$product_options));
     					}else{
     						//определяем нужен ли тут знак припинания и какой
     						$zn ='';
     						if($k!=$i){ //  это значит, что родитель всё ещё предыдйщий и нам нужна запятая
     							$zn = (($n>=0)?', ':'');
     						}else{
     							switch ($n) {// знаки присваивания для разных уровней вложенности
     								case 1: // уровень первый
     									$zn = ': ';
     									break;

     								case 0: // уровень второй
     									$zn = '-> ';
     									break;
     								
     								default: // третий и выше
     									$zn = '-> ';
     									break;
     							}
     							// $zn .= ' --$n='.$n.'--';
     							//$zn = (($n>0)?': ':'');
     						}
     						
     						$html[$i] .= $zn.implode(', ',$this->gg_Array($val1,(($n>0)?0:(-1)),$product_options));	
     						//$html[$i] .= $zn.implode(', ',$this->gg_Array($val1,0,$product_options));	
     						
     						$k++;
     						
     					}					
     				}
     				
     			}
     			// сначала метод работал с Html, потом стал работать с Array, название переменной осталось
     			return $html;
     		}
     		
     		// получает массив описаний всех полей (кроме списков)
     		private function get_cirilic_names_keys_Database(){
     			$query = "SELECT `parent_name`,`name_cirilic` FROM `".FORM_ROWS_LISTS."` WHERE type NOT LIKE('select') AND type NOT LIKE('checkbox');";
     			global $mysqli;			
     			$arr = array();
     			$result = $mysqli->query($query) or die($mysqli->error);
     			if($result->num_rows > 0){
     				while($row = $result->fetch_assoc()){
     					$arr[] = $row;
     				}
     			}
     			return $arr;
     		}
     		
     		// генератор id
     		private function generate_id_Strintg($name){
     			//$id = $val['parent_name'].'_'.($id_i++);
     			$this->id_closed[$name][] = true;

     			$id = $name.'_'.count($this->id_closed[$name]);
     			return $id;
     		}

     		// генерит html
     		private function generate_form_Html($arr,$parent='',$type_product){	
     			// echo '<pre>';
     			// print_r($arr);
     			// echo '</pre>';
     			$html = '';
     			$select = 0;

     			foreach ($arr as $k => $val){
     				// $p_name = '';
     				if($parent==''){
     					// если это группа checkbox, то 
     					// echo $this->form_type[$type_product][$val['parent_name']]['btn_add_var'];
     					// if($val['type']=='checkbox' && isset($this->form_type[$type_product][$val['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$val['parent_name']]['btn_add_var']){
     					if($val['type']=='checkbox' && isset($this->form_type[$type_product][$val['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$val['parent_name']]['btn_add_var']){
     						$p_name = $val['parent_name'].'[][]';
     					}else{
     						$p_name = $val['parent_name'].'[0][]';
     					}
     				}else{
     					$parent = (substr($parent, -2, 2)=='[]')?substr($parent,0,strlen($parent)-2):$parent;
     					
     					 if(!strstr($parent, "[0]")){
     					 	$parent = $parent.'[0]';
     					 }
     					$p_name = $parent.'['.$val['parent_name'].']'.'[]';
     				}
     				
     				$id = $this->generate_id_Strintg($val['parent_name']);


     				$html .= ($val['note']!='')?'<span style="font-size:10px">'.$val['note'].'</span><br>':'';
     				// $html .= $val['type'];
     				switch ($val['type']) {
     					case 'textarea':// если тип поля textarea
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($val['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$p_name.'">'.$val['val'].'</textarea><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$p_name.'">'.$val['val'].'</textarea>'.$this->span_del.'<br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					case 'text':// если тип поля text
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($val['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'">'.$this->span_del.'<br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					case 'select':// если тип поля select
     						if($select == 0){$html .= '<select name="'.$p_name.'">';$select =1;}
     						switch ($val['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" value="'.$val['val'].'">'.$val['val'].'</option><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" value="'.$val['val'].'">'.$val['val'].' '.$this->span_del.'</option><br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					
     					default:
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($val['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].'</label><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].' '.$this->span_del.'</label><br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     				}
     					
     				if($val['child']!=''){
     					$arr_child = $this->get_child_listing_Database_Array($val['child']);
     					$html .= '<div class="pad">'.$this->generate_form_Html($arr_child,$p_name,$type_product).'</div>';
     				}

     									
     			}
     			if($select > 0){$html .= '</select><br>';$select =0;}
     			return $html;
     		}

               // запрашивает из базы список вариантов для полей формы по отдельности
     		private function get_form_Html_listing_Database_Array($type_product,$input_name){
     			global $mysqli;			
     			$query = "SELECT * FROM `".FORM_ROWS_LISTS."` WHERE `type_product` = '".$type_product."' AND `parent_name` = '".$input_name."'";
     			$arr = array();
     			$result = $mysqli->query($query) or die($mysqli->error);
     			if($result->num_rows > 0){
     				while($row = $result->fetch_assoc()){
     					$arr[] = $row;
     				}
     			}
     			// echo $query;
     			// echo '<pre>';
     			// print_r($arr);
     			// echo '</pre>';
     				
     			return $arr;
     		}

     		// запрашивает из базы список CHILD для полей формы
     		private function get_child_listing_Database_Array($child){
     			global $mysqli;			
     			$query = "SELECT * FROM `".FORM_ROWS_LISTS."` WHERE `id` IN (".$child.")";
     			$arr = array();
     			$result = $mysqli->query($query) or die($mysqli->error);
     			if($result->num_rows > 0){
     				while($row = $result->fetch_assoc()){
     					$arr[] = $row;
     				}
     			}
     			return $arr;
     		}

          //////////////////////////
          //     SERVICE METHODS
          //////////////////////////
               // распечатать массив в переменную
               private function print_arr($arr){
                    ob_start();    
                         
                         echo '<pre>';
                         print_r($arr);
                         echo '</pre>';      

                         $html = ob_get_contents();
                    
                    ob_get_clean();

                    return $html;
               }

	}


?>