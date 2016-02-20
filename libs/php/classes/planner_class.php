<?php 

    //echo date("Y-m-d H:i:s");
    // не менять $_SERVER['DOCUMENT_ROOT'].'/os/ на ROOT потому что к этому файлу обращается скрипт из крона
	require_once ($_SERVER['DOCUMENT_ROOT'].'/os/libs/mysqli.php');
	require_once ($_SERVER['DOCUMENT_ROOT'].'/os/libs/mysql.php');
	require_once ($_SERVER['DOCUMENT_ROOT'].'/libs/php/classes/aplStdClass.php');
	require_once ($_SERVER['DOCUMENT_ROOT'].'/os/libs/php/classes/client_class.php');
	class Planner{
        //     Задачи класса: 
		//     ПЕРВЫЙ ЭТАП: Выдавать сообщения об событиях в ПЛАНЕРЕ которые были просрочены (события типа звонок, встреча)
	    //     Выдавать сообщения о необходимости установки новых планов по событиям звонок и встреча. 
	    //     Осуществлять опцию переноса оповещения на срок выбираемый менеджером, помещать данные об оповещениях в сессии 
	    //     которые используются как место сохранения информации об оповещениях между перезагрузками страниц
		//		
		//     ВТОРОЙ ЭТАП: производить операции добавления, переноса, подтверждения
	    //     планов
	    //	  
		//    
		//      ЗАДАЧИ ВООБЩЕ 
		//     
	    //      ВОПРОСЫ - на какой максимальный срок может выставляться план по звонку - встрече?
		//              - работа напоминаний в выходные дни
	    //              - сортировать ли во всех выборках по manager_id?
		//
		//      МЫСЛИ 
		//       - запускать клиентский скрипт каждый час в том чилсе связано с тем что есть отсрочки на час 
		//       - last_remind_date  метка даты последнего напоминания
		//       - REMAINDER_PROTOCOL время от времени очищать от старых записей чтобы не делать лишних вычислений а то со временем
		//         там скопятся записи по всем клиентам
		//  ----------------------------------------------------------------------------------------------------------------------------------
		//      !!! - ОТСЛЕЖИВАНИЕ ОТСРОЧЕК заданных менеджером (1,2,3 часа, 1,2,3 дня)
		//      - отсрочка устанавливается с дифференцацией 1. на тип события 2. тип оповещения - для уровней ('green',  'yellow', 'red' ,'black')        //      единный тип expired_event и второй тип need_new_event в базу данных эта информация сохраняется в виде JSON объекта  пример
	    //      {"level":"expired_event","event_type":"встреча"} или {"level":"need_new_event","event_type":"встреча"}
		//      В ИТОГЕ острочка поставленная на нижнем уровне при поподании даты в зону вехнего уровня блокирует отображение окна верхнего уровня
		//  	при до момента окончания отсрочки, окно же нижнего уровня в этом случае больше не отображается
		//      - ** вначале рассматривался подход с сохранением в данных об отсрочке - точного уровня оповещения ('green','yellow','red','black')
		//      - но тогда получалось что отсрочка отслеживаясь по уровню оповещения не показывается а следующая по возрастанию начинает 
		//      показываться, а  затем когда заканчивается отсрочка она вновь показыватеся хотя уже зона другого уровня
		// ----------------------------------------------------------------------------------------------------------------------------------     
		//		
		//     
	    //  -   поле write_datetime - сейчас может указывать дату поздее чем дата плана если менеджер поставил план 
		//      на тот же день и не выбрал время в календаре (и оно осталось по умолчанию)
	    //  -  "начало периода" для любого вида события - завершения последнего события данного типа или момент самой 
		//      первой записи в таблице PLANNER данному клиенту со значением status=init 
	    //  -  "спокойный период" для любого вида события - период в течении которого не выдается никаких оповещений
		//      совпадает с периодом с момента "начало периода" и до момента первого оповещения (самый короткий период -(минус период оповещения)  		//     
		//  -   типы записей в поле status	в таблице PLANNER init - самая первая запись(план, событие) по клиенту по данному типу события,
		//      done - выполненый план, событие  . В нынешней таблице существует  885 записей со значением   status ='new' (ЧТО ЭТО ОБОЗНАЧАЕТ?)       
		// значение которое может быть переопределено скриптом в зависимости от возможных лимитов и результатов проверки базы данных
		// итог передается яваскрипту на клиенте в качестве метки, о том, что нужно делать AJAX запрос на сервер или нет
		// тем самым сервер избавлятся от необязательного запроса в том случае если данных для отображения нет, или есть какой то лимит
		static $make_loading =  TRUE;  // если изначально установть FALSE - оповещения не будут работать совсем ни для кого 
		// интервал времени через который скрипт обновляет данные об оповещения в $_SESSION['warnings']
		static $update_interval =  3600;  //60*60 1800 
		// пауза между загрузкой страницы и отправкой AJAX запроса
		static $pause_time = 5;  //   1
		// ограничение на количество элементов в итоговом массиве ( для отладки )
		static $limit_output_elements =  FALSE; //   
		// ограничение по отображению оповещений для менеджеров по id ( не отображать оповещения совсем )
		static $full_output_limit_for_id =  array(6,40,37,51,42); //   id - шники администраторов - 6,40,37,51,42,18
		// ограничение по отображению оповещений для менеджеров по id ( не отображать оповещения по какому либо из типов событий )
		static $parted_output_limit_for_id =  array(
		                                      'звонок'=>array() ,
		                                      'встреча'=>array(31) 
											  );//18,42
		// менеджеры совершающие функции контроля (утверждение результатов встеч)							  
		static $controllers_ids =  array(6,40,42,18); //
		// типы событий
	    static $event_types = array('traced' => array('звонок',  'встреча'),'other' => 'заметка' ); 
				
		// типы "тревожных" уровней оповещения
	    static $levels = array(  'green',  'yellow', 'red' ,'black' );
		// порядок сортировки всех видов оповещения при отдаче клиенту
		static $uotput_order = array( "need_approval", "black", "red", "yellow", "green", "need_new_event" );
		//static $uotput_order = array(  "need_new_event", "green", "yellow", "red" ,"black" );
		// срок в днях с которого начинаются периоды оповещения, по типам событий 
		static $levels_alarm_periods = array( 
		                       'green'  =>  array('звонок'=>30,'встреча'=>75 ) , 
							   'yellow' =>  array('звонок'=>35,'встреча'=>150 ) ,
							   'red'    =>  array('звонок'=>40,'встреча'=>180 ) ,
							   'black'  =>  array('звонок'=>50,'встреча'=>195 )
		                       );
		// содержит массив id-шников менеджеров которые должны вести планы (для CRON)
		static $traced_managers_ids = array();
		static $warnings = array();
		static $warnings_container = '';
		static $needs_approval = array();
		
		public static function init_warnings($remainder_user_id){
		    global $section;

			// проверям нет ли граничения на показ оповещений:
			//   - проверяем какая страница ОС открыта 
			if($section == "planner") self::$make_loading = FALSE;
			if($section == "agreement_editor") self::$make_loading = FALSE;
			
			
		    // При загрузке страницы скрипт проверяет на существование $_SESSION['warnings']['last_update'], если 
	        // такой переменной нет или она есть и time() больше или равно  $_SESSION['warnings']['last_update'] + 60*60 
			// (значит прошел час с момента последнего обновления данных в $_SESSION['warnings']) скрипт запускает 
			// Planner::check() и Planner::load_session() чтобы создать либо обновить данные в $_SESSION['warnings']
			// данная схема позволяет через каждый час выдавать менеджеру оповещения по новой, на основе обновленных
			// данных которые будут учитывать его действия в ответ на предупреждения, в тоже время все данные о состояниях
			// окон не сохраняются что позволяет заново развернуть все свернутые и закрытые окна если необходимость в 
			// оповещении по ним осталась актуальной
			if(self::$make_loading){
				if(!(!empty($_SESSION['warnings']['last_update']) && time() < (int)$_SESSION['warnings']['last_update'] + self::$update_interval)){
				     // проверяем нет ли полного ограничения на показ оповещений по id менеджера
					 // если нет собераем оповещения
					 if(!in_array($remainder_user_id,self::$full_output_limit_for_id)) self::check($remainder_user_id);
					 // если пользователь является лицом контролирующим одобрения для событий собераем оповещения о
					 // событиях ждущих одобрения
					 if(in_array($remainder_user_id,self::$controllers_ids)) self::check_who_needs_approval($remainder_user_id);
					 self::load_session();
					 $_SESSION['warnings']['last_update'] = time();
				}
				// проверяем $_SESSION['warnings']['warnings'] на существование и на наличие данных, если его нет указываем что отображать нечего
				if(!(!empty($_SESSION['warnings']['warnings']) && count($_SESSION['warnings']['warnings'])>0)) self::$make_loading = FALSE;
			} 
			
			self::$warnings_container = '<div id="dialog_window_minimized_container" data-loading="'.(int)self::$make_loading.'" data-pause_time="'.self::$pause_time.'" data-update_interval="'.self::$update_interval.'"></div>';   
			       	   
		}					   
		public static function remaind_counter($real_user_id){
		    global $mysqli;

		    // устанавливает в $_SESSION['warnings']['was_shown'] = 1 показывая яваскрипту что сессия показа была начата  
			// и яваскрипт не должен отправлять отчет об этом, ПРИМ. при первой загрузке каждой сессии $_SESSION['warnings']['was_shown'] = 0
			// что дает команду яваскрипту отправить отчет что сессия показа была начата, этот отчет приводит к вызову этого  
			// метода - remaind_counter()
			if(isset($_SESSION['warnings']['was_shown'])) $_SESSION['warnings']['was_shown'] = 1;
			
			// вносит данные (увеличивая на единицу зачение в таблице) в таблицу  REMAINDER_PROTOCOL в ряд с сегодняшней датой
			// тем самым ведет подсчет осуществленных сессий показов оповещений
		    
			$query = "UPDATE `".REMAINDER_PROTOCOL."` SET 
								   `counter` = `counter` + 1
								    WHERE `sourse` = 'REMAIND_COUNTER' AND `manager_id` = '".$real_user_id."' 
									AND `date_time` = '".date("Y-m-d")." 00:00:00'"; 
			//              
		    $result = $mysqli->query($query) or die($mysqli->error);
			//echo $result.' - '.  $mysqli->affected_rows.' - ';
			if($result &&  $mysqli->affected_rows == 0){ // значит записи по сегодняшнегу дню еще нет вносим её
			          $query = "INSERT `".REMAINDER_PROTOCOL."` SET 
			                       `sourse` = 'REMAIND_COUNTER',
								   `counter` = 1,
								   `client_id` = '',
								   `manager_id` = '".$real_user_id."',
								   `date_time` = '".date("Y-m-d")." 00:00:00'
								   "; 
					  $result = $mysqli->query($query) or die($mysqli->error);
					  //echo $result.' - '.  $mysqli->affected_rows.' - ';
			}
		}
		public static function get_num_of_remainds(){
		    // получает количество показов сессий оповещений для данного менеджера
		}
	    public static function load_session(){
		
		    // сортируем выдачу в нужном порядке
		    $itog_alarms_data_arr = array();	
			foreach(self::$uotput_order as $type){
			   if(isset(self::$warnings[$type])) $itog_alarms_data_arr[$type] =  self::$warnings[$type];
			}
			self::$warnings=$itog_alarms_data_arr;
			
		    // if(empty($_SESSION['warnings'])) Planner::load_session();
		    // меняет значение в поле num_of_remind
			if(isset($_SESSION['warnings'])) unset($_SESSION['warnings']);
			$_SESSION['warnings']['warnings'] = self::$warnings;
			$_SESSION['warnings']['last_update'] = date("Y-m-d H:i:s");
			$_SESSION['warnings']['was_shown'] = 0;
			//echo'-----------------<pre>';print_r($_SESSION['warnings']); echo'1<pre>';
		   
		}
		public static function check_who_needs_approval($remainder_user_id){
		    global $mysqli;
		    // ОБЩАЯ ЗАДАЧА проверка на наличие записей которые должны пройти одобрение руководителя (on_approval)
			// ПЕРВЫЙ ЭТАП - выбираем все существующие записи данного контроллера(руководителя) об отложенных планах требующих одобрения
			// ВТОРОЙ ЭТАП - выбираем все планы требующие одобрения и если они не отложенны выводим их
			$cur_time = time();
			
			// ПЕРВЫЙ ЭТАП 
			// смотрим записи с фильтром по полю manager_id - тоесть только те метки "отложить" которые сделал этот конкретный контроллер
			$query = "SELECT * FROM `".REMAINDER_PROTOCOL."` WHERE `sourse` = 'REMAIND_AFTER_APPROVAL' AND `manager_id` = '".$remainder_user_id."' "; 
		    $result = $mysqli->query($query) or die($mysqli->error);
			$delayed_remainds = array();// массив дат до которых отложенны решения по одобрению результатов встречи
		    if($result->num_rows > 0){
			    while($row = $result->fetch_assoc()){
				$remaind_details = json_decode($row['details']);
				//echo $row['details']; echo'<br>';
				//echo'<pre>';print_r($remaind_details); echo'<pre>';
				
			         $delayed_remainds[$remaind_details->plan_id] = $row['date_time'];
			    }
		    }/**/
			
			// ВТОРОЙ ЭТАП
			$query = "SELECT * FROM `".PLANNER."` WHERE `status` = 'on_approval'";// ORDER BY `id` DESC//echo $query;
	        
			$result = $mysqli->query($query) or die($mysqli->error);
				
			if($result->num_rows > 0){
			   // не менять $_SERVER['DOCUMENT_ROOT'].'/os/ на ROOT потому что к этому файлу обращается скрипт из крона
			   include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/manager_class.php");
			   
			   while($row = $result->fetch_assoc()){
			      if(!(isset($delayed_remainds[$row['id']]) &&  $cur_time < strtotime($delayed_remainds[$row['id']]))){
				      $client = new Client($row['client_id']);
					  // данные менеджера который инициировал событие
					  if(($manager = new Manager($row['manager_id']))==false){
					       $manager->last_name=$manager->name='';
					  }
					  if(!isset($manager->name))$manager->name='';
					  if(!isset($manager->last_name))$manager->last_name='';
					  // данные менеджера который закрывает событиe
					  if(($close_manager = new Manager($row['close_manager_id']))==false){
					       $close_manager->last_name=$close_manager->name='';
					  }
					  
					  $var1 = $manager->name;
					  $var2 = $manager->last_name;
					  $var3 = $close_manager->name;
					  $var4 = $close_manager->last_name;
					  
			          self::$needs_approval[] = array('plan_id'=>$row['id'],'plan'=>$row['plan'],'result'=>$row['result'],'manager_id'=>$row['manager_id'],'manager_name'=>$manager->name,'manager_lastname'=>$manager->last_name,'close_manager_id'=>$row['close_manager_id'],'close_manager_name'=>$close_manager->name,'close_manager_lastname'=>$close_manager->last_name,'client_id'=>$row['client_id'],'client_name'=>$client->name);
				  }
			   }
			}
			
			self::$warnings['need_approval']['data'] = self::$needs_approval;
			//array_unshift(self::$warnings,self::$needs_approval);
			//**--**
			//echo'<pre>needs_approval<br>';print_r(self::$needs_approval); echo'<pre>'; 
		}
	    public static function check($manager_id){
		    global $mysqli;
		    // print_r( self::$levels);
			// ЗАДАЧА 
			// скрипт проверяет время прошедшее с момента последнего выполненого действия(status=done) или с момента первой 
			// записи status=init и если время превышает лимит, передает даные обработчику раздающему предупрежедения и т.п.
			// также отслеживает были ли по истечению t количества времени выставленны новые планы после того как действия 
			// получило статус done или была сделана запись со статусом init
			// 
			// ЭТАПЫ
			//  1. Определяем клиентов по которым вообще нет записей ( чтобы затем внести по ним запись в таблицу со статусом init):
			//     получаем массив id всех клиентов относяшихся к менеджеру, затем делаем выборку по этому массиву (оператором IN)
			//     по каждому событию в отдельности, тем самым выясняя по каким событиям ведутся записи, сохраняем данные в многомерный
			//     с ключами равными именам отслеживаемых событий, затем сравниваем полученные подмассивы по array_diff - с массивом id
			//     всех клиентов менеджера в результате получаем многомерный массив id клентов по с разделением по событиям, по которым
			//     не ведутся записи
			//     -- предпринимаем НЕОБХОДИМЫЕ ДЕЙСТВИЯ
			//     
			//  2. Определяем события по которым будем показывать оповещения:
			//     нам надо сравнить период с момента последнего выполненого события данного типа или момента создания клиента  
			//     с "спокойным периодом" по данному типу события, если период превышен сохраням данные и работаем с ними дальше
			//     -- предпринимаем НЕОБХОДИМЫЕ ДЕЙСТВИЯ
			
			$cur_time = time();
			// этап  1.
		    // массив id-шников всех клиентов закрепленных за менеджером
			//**--**
			//echo $manager_id.'-------------';
			$clients_ids_arr = self::get_related_clients_ids($manager_id);
			//**--**
			//echo'<pre>clients_ids_arr<br>';print_r($clients_ids_arr); echo'<pre>';
			
			// Получаем данные об отложенных оповещениях
			// REMAINDER_PROTOCOL таблица содержащая данные о том на сколько перенесено какое-либо уведомление
			// вытаскиваем данные по менеджеру, если есть будем их потом сравнивать с текущим временем
			// поле details в таблице  REMAINDER_PROTOCOL имеет следующий вид - {"level":"value","event_type":"value"}
			// где level- тип оповещения ,event_type -тип записи в таблице PLANNER, пример - {"level":"expired_event","event_type":"звонок"} 
			$query = "SELECT * FROM `".REMAINDER_PROTOCOL."` WHERE `client_id` IN('".implode("','",$clients_ids_arr)."') AND `manager_id` = '".(int)$manager_id."'  AND `sourse` = 'REMAIND_AFTER' ORDER BY `date_time` ASC"; 
		    $result = $mysqli->query($query) or die($mysqli->error);
			$delayed_remainds = array();// массив дат до которых отложенны оповещения по конкретному кленту по конкретному событию
		    if($result->num_rows > 0){
			    
			    while($row = $result->fetch_assoc()){
				$remaind_details = json_decode($row['details']);
				//echo $row['details']; echo'<br>';
				//echo'<pre>';print_r($remaind_details); echo'<pre>';
				
			        if(isset($remaind_details->level,$remaind_details->event_type)) $delayed_remainds[$row['client_id']][$remaind_details->level][$remaind_details->event_type] = $row['date_time'];
			    }
		    }
			//**--**
			//echo'<pre>delayed_remainds<br>';echo $query;print_r($delayed_remainds); echo'<pre>';
			
			
			//  вычисляем по каким из всех клиентов менеджера не ведутся записи в таблице PLANNER с разделением на типы
			//  ( не берем строки со значением status=init эти записи вносит система, не менеджер )
			// массив клиентов по которым записи еще не велись 
			// по этим клиентам надо выводить сообщение что необходимо запланировать действие (несколько раз, затем создавать атоматически?)
			// и еще сделать запись со значением init (если она еще не сделана) от неё будет вестись отчет первого периода
			
			foreach(self::$event_types['traced'] as $type){
				$query = "SELECT `client_id` FROM `".PLANNER."` WHERE `client_id` IN('".implode("','",$clients_ids_arr)."') AND `manager_id` = '".(int)$manager_id."' AND `type` = '".$type."' GROUP BY `client_id`";
		
				//echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$traced_clients_ids_arr = array();
				if($result->num_rows > 0){
				   while($row = $result->fetch_assoc())  $traced_clients_ids_arr[] = $row['client_id'];
				   //**--**
				   //echo'<pre>traced_clients_ids_arr '.$result->num_rows.'  '.$type.'<br>';print_r($traced_clients_ids_arr); echo'<pre>';
				}
				$untraced_clients_ids_arr[$type] =  array_diff($clients_ids_arr,$traced_clients_ids_arr);
			}
			//**--**
		    //echo'<pre>untraced_clients_ids_arr<br>';print_r($untraced_clients_ids_arr); echo'<pre>';
			
		    if(!empty($untraced_clients_ids_arr)) self::set_init_row($manager_id,$untraced_clients_ids_arr);
			
	       
			
			// этап  2
			// Выбираем самые последние записи со значением поля  `status` = 'done' или 'init' 
			// для каждого клиента по отслеживаемым типам события
			
			// массив который будет содержать данные об уровнях оповещений
		    $alarms_data_arr = array();
			
			foreach(self::$event_types['traced'] as $type){
			
				$query = "SELECT *, MAX(exec_datetime) AS `max_exec_datetime` FROM `".PLANNER."`
									   WHERE `client_id` IN('".implode("','",$clients_ids_arr)."') AND `manager_id` = '".(int)$manager_id."'
										AND( `type` = '".$type."' AND ( `status` = 'done' OR `status` = 'init' ) )
										 GROUP BY `client_id`";// ORDER BY `id` DESC
		
				//echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				
				if($result->num_rows > 0){
				   while($row = $result->fetch_assoc()){
				   
					   // если есть ограничение по выводу оповещений определенного типа для данного менеджера, 
					   // не добавляем их в массив, пропуская дальнейшее выполнение цикла
					   if(in_array($manager_id,self::$parted_output_limit_for_id[$type])) continue;
					   
					   // проверяем есть ли отсрочка по данному кленту по данному типу события 
					   // если есть сравниваем с текущей датой, если текущая дата меньше не добавляем это событие в оповещения
					   // это событие в оповещения в данном случае пропускаем дальнейшее выполнение цикла
					   //echo $delayed_remainds[$row['client_id']]['expired_event'][$row['type']].'<br>';
					   if(isset($delayed_remainds[$row['client_id']]['expired_event'][$type]) && $cur_time < strtotime($delayed_remainds[$row['client_id']]['expired_event'][$type])){
							  continue; 
					   }
					   
					   // вычисляем количество дней прошедших с "начала периода"
					   $range = round(($cur_time - strtotime ($row['max_exec_datetime']))/ (24*60*60));
			
					   // передаем величину дней прошедших с "начала периода" в обработчик 
					   // определяющий есть ли превышение допустимой величины и до какого уровня
					   if($level = self::level_detector($range,$type)){
					       // ограничение на количество элементов в итоговом массиве ( для отладки )
						   if(self::$limit_output_elements){
						      	if(isset($alarms_data_arr[$level][$row['type']]) && count($alarms_data_arr[$level][$row['type']])>=self::$limit_output_elements)continue;	   
							}
						   /*
						   // вспомогательные данные (на время разработки);
						   if(isset($_GET['short'])){ 
						       $row['--max_exec_datetime'] = $row['max_exec_datetime'];
							   unset($row['max_exec_datetime']);
							   $row['--range'] = $range;
							   $output_row = array(
											   "type" => $row['type'],
											   "status" => $row['status'],
											   "client_id" => $row['client_id'],
											   "num_of_remind" => $row['num_of_remind'],
											   "--max_exec_datetime" => $row['--max_exec_datetime'],
											   "--range" => $row['--range']
											   ); 
						   }
						   else  $output_row = $row; 
						   */
						   $client = new Client($row['client_id']);
						   $output_row = array(
											   "client_id" => $row['client_id'],
											   "manager_id" => $row['manager_id'],
											   "client_name" => $client->name
											   ); 
									   
							
						   $alarms_data_arr[$level][$row['type']][] = $output_row;
	
					   } 
					}
				}
			}
			//**--**
	        // echo'<pre>alarms_data_arr<br>';print_r($alarms_data_arr); echo'<pre>';
			// Определяем по каким клиентам не поставлено новое событие после выполнения предыдущего или инициализации
			// для этого делаем сортировку под дате выполнения события, и складываем в двухуровневый массив с ключами client_id , type 
			// в итоге в массиве осядет запись максимально последняя по значению exec_datetime, затем мы проходим по массиву и проверяем -
			// имеет ли поле `status` значение 'done' или 'init' если да то значит это было последней записью соответсвенно новых событий
			// еще небыло запланированно  - отправляем этого клиента на оповещение
			
            foreach(self::$event_types['traced'] as $type) $sub_query_arr[] =  "`type` = '".$type."'";
			$query = "SELECT * FROM `".PLANNER."`
			                       WHERE `client_id` IN('".implode("','",$clients_ids_arr)."') AND `manager_id` = '".(int)$manager_id."'
			                        AND (".implode(" OR ",$sub_query_arr).")
									  ORDER BY exec_datetime ASC";  // echo $query;
			//echo $query;
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			$arr = array();
		    if($result->num_rows > 0){
			   while($row = $result->fetch_assoc()){
			       // проверяем есть ли отсрочка по данному кленту по данному типу события 
				   // если есть сравниваем с текущей датой, если текущая дата меньше не добавляем это событие в оповещения
				   // это событие в оповещения в данном случае пропускаем дальнейшее выполнение цикла
                   
				   if(isset($delayed_remainds[$row['client_id']]['need_new_event'][$row['type']]) && $cur_time < strtotime($delayed_remainds[$row['client_id']]['need_new_event'][$row['type']])){
					   continue; 
				   }

				   $arr[$row['client_id']][$row['type']] = array('exec_datetime' => $row['exec_datetime'],'status' => $row['status'],'client_id' => $row['client_id'],'manager_id' => $row['manager_id'],'num_of_remind' => $row['num_of_remind']);
			   }
			}
			//**--**
	        //echo'<pre>arr<br>';print_r($arr); echo'<pre>';

			foreach($arr as $client => $row){
			   foreach($row as $event_type => $data){
			        // если есть ограничение по выводу оповещений определенного типа для данного менеджера, не добавляем их в массив
				    if(in_array($manager_id,self::$parted_output_limit_for_id[$event_type])) continue;
			        // ограничение на количество элементов в итоговом массиве ( для отладки )
			        if(self::$limit_output_elements){
						 if(isset($alarms_data_arr['need_new_event'][$event_type]) && count($alarms_data_arr['need_new_event'][$event_type])>=self::$limit_output_elements)continue;	   
					}
			        if($data['status']=='done' || $data['status']=='init'){
					     // добавляем клиента в оповещения если с момента последнего выполненого действия или инициализации прошло 
						 // более $N дней (промежуток без оповещений) и менее периода установленного для КРАСНОГО 
						 // уровня опвещения данного типа
						 $N = 1;  // по идее должно быть больше 0 если подразумевается что подсказка должна отображаться не в первый день
						 $range = round( ($cur_time - strtotime($data['exec_datetime'])) / (24*60*60));
						 // echo $data['client_id']. '  '.$cur_time. '  '.$event_type. '  '.$data['status']. '  '.strtotime($data['exec_datetime']). '  '.self::$levels_alarm_periods['red'][$event_type].' - '. $range.'<br>' ;
						 if($range > $N && $range < self::$levels_alarm_periods['red'][$event_type]){
							 $client = new Client($data['client_id']);
						     $alarms_data_arr['need_new_event'][$event_type][] = array("client_id" => $data['client_id'],"client_name" => $client->name,"manager_id" => $data['manager_id']);
						 }
					}
				}
			}
			
			//echo '<pre>'; print_r($alarms_data_arr); echo '<pre>';

		    self::$warnings = $alarms_data_arr;
			
		}
		public static function push_OK($id,$level,$event_type){ // при клике менеджером на кнопку в "OK" окне
			// ДЕЙСТВИЯ: 
			// вырезаем данные из сессии по данному типу оповещения, типу события, id клиента чтобы окно по этим параметрам не всплывало 
			// больше при каждой перезагрузке до начала следующей (через час) сессии оповещений
			if(!empty($_SESSION['warnings']['warnings'][$level][$event_type])){
			    $arr = $_SESSION['warnings']['warnings'][$level][$event_type];
			    foreach($arr as $key => $val){
				    // если $level == ищем по id плана, остальное ищем по id клиента
					if($level == "need_approval"){
					    if($val['plan_id'] == $id)  unset($_SESSION['warnings']['warnings'][$level][$event_type][$key]);
					}
					else{
					    if($val['client_id'] == $id) unset($_SESSION['warnings']['warnings'][$level][$event_type][$key]);
					}
				    
				}
			}
			$_SESSION['warnings']['warnings'][$level][$event_type] = array_values($_SESSION['warnings']['warnings'][$level][$event_type]);
		}
		public static function window_set_minimize($client_id,$level,$event_type,$status){ // при клике менеджером на кнопку "СВЕРНУТЬ ОКНО"
			// ДЕЙСТВИЯ: 
			// добавляем в сессии в массив данных по данному типу оповещения, типу события, id клиента
			// метку что окно свернуто, чтобы оно открывалось в свернутом состоянии при перезагрузке
			if(!empty($_SESSION['warnings']['warnings'][$level][$event_type])){
			    $arr = $_SESSION['warnings']['warnings'][$level][$event_type];
				//print_r($arr);
			    foreach($arr as $key => $val){
				     if($val['client_id'] == $client_id){
					      if($status=='1') $_SESSION['warnings']['warnings'][$level][$event_type][$key]['win_minimized'] = 1;
						  else{
						      if(isset($_SESSION['warnings']['warnings'][$level][$event_type][$key]['win_minimized'])){
							     unset($_SESSION['warnings']['warnings'][$level][$event_type][$key]['win_minimized']);
							  }
						  }
					 }
				}
			}
		}
		public static function set_delay($remainder_user_id,$client_id,$level,$event_type,$range){ // при клике менеджером на кнопку "ОТЛОЖИТЬ"
		    global $mysqli;
		    //
			$remanind_details = '{"level":"'.((in_array($_POST['window_type'],Planner::$levels))? 'expired_event' : $_POST['window_type']).'","event_type":"'.$event_type.'"}';//need_new_event expired_event
			 
			$query = "INSERT INTO `".REMAINDER_PROTOCOL."` SET 
			                       `sourse` = 'REMAIND_AFTER',
								   `details` = '".$remanind_details."',
								   `client_id` = '".$client_id."',
								   `manager_id` = '".$remainder_user_id."',
								   `date_time` = '".(date("Y-m-d H:i:s", time() + $range))."'
								   "; 
			//              
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			// вырезаем данные из сессии по данному типу оповещения, типу события, id клиента
			self::push_OK($client_id,$level,$event_type);
			
		}
		public static function set_approval_delay($remainder_user_id,$client_id,$plan_id,$range){ // при клике контороллером на кнопку "ОТЛОЖИТЬ"
		    global $mysqli;
		    // записываем в базу данные об отложенном плане в качестве записывам контроллера чтобы потом по нему идентифицировать 
			// отложенные записи для каждого контроллера в отдельности
			$remanind_details = '{"plan_id":"'.(int)$plan_id.'"}';
			 
			$query = "INSERT INTO `".REMAINDER_PROTOCOL."` SET 
			                       `sourse` = 'REMAIND_AFTER_APPROVAL',
								   `details` = '".$remanind_details."',
								   `client_id` = '".$client_id."',
								   `manager_id` = '".$remainder_user_id."',
								   `date_time` = '".(date("Y-m-d H:i:s", time() + $range))."'
								   "; 
			//              
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			// вырезаем данные из сессии по данному типу оповещения, типу события, id плана
			self::push_OK($plan_id,'need_approval','data');
			
		}
		public static function set_approval_result($remainder_user_id,$plan_id,$status,$comment){ // при клике контороллером на кнопку "ОТЛОЖИТЬ"
		    global $mysqli;
		    //
			// не менять $_SERVER['DOCUMENT_ROOT'].'/os/ на ROOT потому что к этому файлу обращается скрипт из крона
			include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/manager_class.php");
		    $manager = new Manager($remainder_user_id);
			
			
			$comment = '<div><span class="mini_cap">'.$manager->name.' '.$manager->last_name.'</span><div>'.$comment.'</div></div>'; 
			$query = "UPDATE `".PLANNER."` SET 
			                       `status` = '".$status."',
								   `result` =  CONCAT(`result`,'".$comment."') WHERE `id` = '".(int)$plan_id."'";
			//             
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			// вырезаем данные из сессии по данному типу оповещения, типу события, id плана
			self::push_OK($plan_id,'need_approval','data');
			
		}
		public static function set_init_row($manager_id,$data_arr){ // список невыполненных задач
		    global $mysqli;
		    
			foreach($data_arr as $type => $clients_ids_arr){ 
			    foreach($clients_ids_arr as $client_id){
					$query = "INSERT INTO `".PLANNER."` SET  `write_datetime` = NOW(), `exec_datetime` = NOW(), `type` = '".$type."',`status` = 'init',`client_id` = '".$client_id."',`manager_id` = '".$manager_id."'";
					//echo  $query;  
					$mysqli->query($query) or die($mysqli->error);
				}
			}
		}
		public static function show_list($manager_id){ // список невыполненных задач
		    global $mysqli;
		    // массив id-шников клиентов закрепленных за менеджером
		    $clients_ids_arr = self::get_related_clients_ids($manager_id);
			
		    $query = "SELECT * FROM `".PLANNER."` WHERE `client_id` IN('".implode("','",$clients_ids_arr)."') AND `manager_id` = '".(int)$manager_id."'  AND  `status` <> 'done'";
		    $result = $mysqli->query($query) or die($mysqli->error);
			
		    if($result->num_rows > 0){
			
				// row_tpl - html шаблон ряда
				// не менять $_SERVER['DOCUMENT_ROOT'].'/os/ на ROOT потому что к этому файлу обращается скрипт из крона
				$row_tpl_name =  $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/planner/planner_table_rows.tpl';
				$fd = fopen($row_tpl_name,'r');
				$row_tpl = fread($fd,filesize($row_tpl_name));
				fclose($fd);
				ob_start(); 
			    while($row = $result->fetch_assoc()){
			        //echo '<pre>'; print_r($row); echo '<pre>';//echo  $row['id']."<br>"; 
					 extract($row,EXTR_PREFIX_ALL,"pl");
					$client_name = get_client_name($pl_client_id);
					
					$write_date_in_format = implode('.',array_reverse(explode('-',substr($pl_write_datetime,0,10))));
					$write_time_in_format = substr($pl_write_datetime,11,5);
					$remind_date_in_format = implode('.',array_reverse(explode('-',substr($pl_exec_datetime,0,10))));
					$remind_time_in_format = substr($pl_exec_datetime,11,5);
					
					// цветовое выделение рядов по отношению к текущей дате
					$current_date_in_number = intval(date('Ymd'));
					$remind_date_in_number = intval(str_replace('-','',substr($pl_remind_datetime,0,10)));
					
					if($current_date_in_number > $remind_date_in_number) $row_class_name = 'planner_rows_expired';
					elseif($current_date_in_number == $remind_date_in_number) $row_class_name = 'planner_rows_today';
					elseif(($current_date_in_number < $remind_date_in_number) && ($current_date_in_number+7 > $remind_date_in_number)) $row_class_name = 'planner_rows_nearweek';
					else $row_class_name = 'planner_rows';
				    eval('?>'.$row_tpl.'<?php '); 
			    }
				$palnner_rows = ob_get_contents();
	            ob_get_clean();
				// не менять $_SERVER['DOCUMENT_ROOT'].'/os/ на ROOT потому что к этому файлу обращается скрипт из крона
				require_once ($_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/planner/planner_table.tpl');
		    }
		}
		public static function get_related_clients_ids($manager_id){
		    global $mysqli;
			// получем массив id-шников закрепленных за менеджером клиентов
		    // исключая тех клентов рейтинг которых ниже значения 3 (тройка)
			$query = "SELECT rel_tbl.client_id client_id, client_tbl.rate rate FROM 
			                         `".RELATE_CLIENT_MANAGER_TBL."` rel_tbl
			                         LEFT JOIN 
			                         `".CLIENTS_TBL."` client_tbl
									 ON  rel_tbl.client_id = client_tbl.id 
									 WHERE rel_tbl.manager_id = '".(int)$manager_id."'
									 AND (client_tbl.rate <> '1' AND client_tbl.rate <> '2') ";

		    $result = $mysqli->query($query) or die($mysqli->error);
		    $clients_ids_arr = array();
		    if($result->num_rows > 0){
			   while($row = $result->fetch_assoc()){
			        if((int)$row['client_id'] != 0) $clients_ids_arr[] = (int)$row['client_id'];
			   }
		    }
			//echo '<pre>'; print_r($clients_ids_arr); echo '<pre>';
			if(count($clients_ids_arr)==0){
			      //echo 'ОШИБКА - У МЕНЕДЖЕРА НЕТ КЛИЕНТОВ<br>';
			}
			return $clients_ids_arr; 
		}	
		public static function level_detector($range,$event_type){
		    
			// определям есть ли превыщение допустимой величины и до какого уровня
			// то что  превысило "спокойный период" 
			if($range >= self::$levels_alarm_periods['black'][$event_type]){
				return 'black';
		    }
		    if($range >= self::$levels_alarm_periods['red'][$event_type]){
				return 'red';
		    }
		    else if($range >= self::$levels_alarm_periods['yellow'][$event_type]){
				return 'yellow';
		    }
		    else if($range >= self::$levels_alarm_periods['green'][$event_type]){
				return 'green';
		    }
			
			return false;
		}
		public static function show_periods_ranges(){
		
		    // Служебный метод - наглядно отображает информацию
			// по длительности периодов отслеживаемых типов событий
			
		    echo '<br>типы периодов и их длительность<br>' ;
			echo "<table border='0'>";
		    foreach(self::$event_types['traced'] as $event){
			   foreach(self::$levels as $level){
			     
					 echo "<tr><td>";
					 echo "тип события - <b>".$event."</b> &nbsp;&nbsp;&nbsp;";
					 echo "</td><td>";
					 echo "уровень - <b>".$level."</b> &nbsp;&nbsp;";
					 echo "</td><td>";
					 echo "момент начала оповещения - ".self::$levels_alarm_periods[$level][$event]."&nbsp;&nbsp;";
					 echo "</td><td></tr>";
				}
			}
			echo "</table>";
		}
		public static function get_traced_managers_ids(){
		    global $mysqli;
			// получем массив id-шников менеджеров которые должны вести планы (для CRON)
		    $query = "SELECT id FROM `".MANAGERS_TBL."` WHERE `access` = '5' OR `access` = '4' OR `access` = '1'";
		    $result = $mysqli->query($query) or die($mysqli->error);
		  
		    if($result->num_rows > 0){
			   while($row = $result->fetch_assoc()){
			       if(!in_array($row['id'],self::$full_output_limit_for_id)){
				       self::$traced_managers_ids[] = $row['id'];
				   }
			   }
		    }
			return self::$traced_managers_ids; 
		}	
	}
	/*< ? php 
	if(isset($_SESSION['access']['user_id']) && ($_SESSION['access']['access']==1)){
		
		echo '<div style="position:absolute;top:1800px;right:0px;font-size:11px;background-color:#FFFFFF;padding:10px;border:1px solid #CCC;"><pre>';
		//print_r($_SESSION['warnings']['warnings']);
		//echo "<br><br><br><br>";
		print_r($_SESSION['warnings']);
		echo  '</pre></div>';
	    
	}
	? >*/    

?>