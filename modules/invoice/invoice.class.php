<?php
/**
 * Class InvoiceNotify
 * класс оповещений
 */
class InvoiceNotify extends aplStdAJAXMethod
{
	public $from_email = 'invoice@apelburg.ru';

	function __construct()
	{
		$this->db();
	}



	/**
	 * тригер отправки сводки по почте в бухгалтерию
	 * по новым запросам счетов и УПД
	 * @param $mysqli
	 */
	public function triger_buch_message_CRON(){
		$message = '';
		# 1
		$text = 0;
		# проверка неотработанных счетов
		# при наличии строк счетов - сборка строк в одно сообщение
		$query = "select * FROM `".INVOICE_TBL."` WHERE `invoice_num` = '0'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);


		$invoice_rows = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$invoice_rows[] = $row;
			}
		}

		if (count($invoice_rows) > 0){
			$message .='<div>В ос есть счета ('.count($invoice_rows).' шт.) ожидающие присвоения им номера<div>';

			foreach ($invoice_rows as $invoice){
				$text = 1;
				$message .="<div>счет от менеджера: ".$row['manager_name']."</div>";
			}

			$message .= "<div>Для заполнения необходимой информации Вы можете пройти по <a href=\"http://www.apelburg.ru/os/?page=invoice&section=1\">ссылке</a></div>";
		}



		# 2
		# проверка неотработанных УДП
		# при наличии строк УДП - сборка строк в одно сообщение
		$query = "select * FROM `".INVOICE_TBL."` INNER JOIN `".INVOICE_TTN."` ON `".INVOICE_TTN."`.`invoice_id` = `".INVOICE_TBL."`.`id` WHERE `".INVOICE_TTN."`.`number` = '0'";


		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$ttn = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$ttn[] = $row;
			}
		}

		if (count($ttn) > 0){
			$message .='<div>В ос есть заявки на создание УПД ('.count($ttn).' шт.) <div>';

			foreach ($ttn as $ttn_row){
				$text = 1;
				$message .= "<div>Запрос УПД от менеджера: ".$ttn_row['manager_name']."</div>";
			}
			$message .= "<div>Для заполнения необходимой информации Вы можете пройти по <a href=\"http://www.apelburg.ru/os/?page=invoice&section=2\">ссылке</a></div>";
		}


		# 3
		# отправка собранного сообщени

		# по id должны отправляться сообщения, с приоритетом на ящик с доменом apelburg.ru
		# при его отсутствии напрямую на gmail
		if ($text > 0){
			// $message = '';
			$subject = 'Сводка из ОС';
			$userName = '';
			$href = '';
			# подгружаем шаблон
			ob_start();
			include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
			// include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
			$html = ob_get_contents();
			ob_get_clean();
			$this->sendMessageToId([81,92,39],'',$subject,$html);
			return $html;
		}

	}

	/**
	 * возвращает email по id юзера
	 * @param $id
	 * @param $mysqli
	 * @return array
	 */
	public function getUsersEmail($id){
		$query = "SELECT * FROM `".MANAGERS_TBL."`";

		if (is_array($id)){
			$query .= " WHERE `id` IN ('".implode("','",$id)."')";
		}else{
			$query .= " WHERE `id` = '$id'";
		}


		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		$emails = array();
		if($result->num_rows > 0){
			while($manager = $result->fetch_assoc()){
				#проверяем email
				if(filter_var($manager['email'], FILTER_VALIDATE_EMAIL)){
					$emails[] = $manager['email'];
				}else if(filter_var($manager['email_2'], FILTER_VALIDATE_EMAIL)){
					$emails[] = $manager['email_2'];
				}
			}
		}
		return $emails;
	}



	/**
	 * метод отправки сообщений по id пользователей
	 *
	 * @param $to
	 * @param $from
	 * @param $subject
	 * @param $message
	 * @return string
	 */
	private function sendMessage($to,$from,$subject,$message ){
		if ($from == ''){
			$from = $this->from_email;
		}
//		include_once 'mail_class.php';
//		$mail = new Mail();


		if (is_array($to)){
			foreach ($to as $email){

				return mail($email,
                    $subject,
                    $message,
                    "From: $from\r\n"
                    ."Content-type: text/html; charset=utf-8\r\n"
                    ."X-Mailer: PHP mail script"
				);
//				$mail->send($email,$from,$subject,$message,TRUE);
			}

		}else{
			return mail($to,
				$subject,
				$message,
				"From: $from\r\n"
				."Content-type: text/html; charset=utf-8\r\n"
				."X-Mailer: PHP mail script"
			);
//			return $mail->send($to,$from,$subject,$message,TRUE);
		}
	}

	public function sendMessageToId($id,$from,$subject,$message ){

        $userEmails = $this->getUsersEmail($id);
		return $this->sendMessage($userEmails,$from,$subject,$message );
	}
	/**
	 * тригер для крон
	 * переводит все проверенные и отгруженные заказы старше 10 дней в статус закрыто
	 *
	 *
	 * была написана процедура, но почему-то процедура переполняет стек на сервере
	 * необходимо копаться в настройках, пока не до этого
	 *
	 * $query = "CALL check_and_closed_invoice();";
	 *
	 * @param $mysqli
	 */
	public function check_and_closed_invoice_CRON(){

		# выбираем все счета, которые пора переводить в закрытые
		$query = "SELECT *,DATE_FORMAT(`".INVOICE_TBL."`.`invoice_create_date`,'%d.%m.%Y') as invoice_create_date FROM `".INVOICE_TBL."` ";

		# если статус счёта отгружен был выставлен более 10 дней назад
		$query .= " WHERE `shipped_date` < (NOW() - interval 10 day)";
		# если был зажат калькулятор
		$query .= " AND `flag_calc` > 0 ";
		# если заказ отгружен
		$query .= " AND `status` = 'отгружен' ";
		# если заказ ещё не закрыт
		$query .= " AND `closed` = 0 ";
        # если оферта, или подписанная спецификация возвращена
        $query .= " AND (`spf_number` = 'оф' OR `flag_spf_return` = 1)";

        # если сданы все ттн
        $query .= " AND `all_ttn_was_returned` = 1";




		$html = "";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$rows = array();

        if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$rows[]  = $row['id'];
                $manager_id = (int)$row['manager_id'];

				# сообщение менеджеру
                if ($manager_id > 0){


                    $subject = 'Cчёт № '.$row['invoice_num'].' был переведён во вкладку "закрытые"';
                    $userName = $row['manager_name'];
                    $message = 'Клиент '.$row['client_name'].'<br>';
                    $message .= 'Cчёт № '.$row['invoice_num'].' от '.$row["invoice_create_date"].' был переведён во вкладку "закрытые';
                    $href = 'http://www.apelburg.ru/os/?page=invoice&section=9&client_id='.$row['client_id'];
                    # подгружаем шаблон
                    ob_start();
                    // include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
                    include_once __DIR__.'/../../../skins/tpl/invoice/notifi_templates/create_invoice.tpl';
                    $html = ob_get_contents();
                    ob_get_clean();

                    $this->sendMessageToId($manager_id, '', $subject, $html);
                }

			}
		}
		$mess = $html;

		# если найдены такие счета
		if (count($rows) > 0){
			# переводим найденныйе счета в статус закрытые
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `closed` = '1'";
			$query .= ", `closed_date` = '".date('Y-m-d',time())."'";
			$query .= " WHERE `id` IN ('".implode("','",$rows)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

            $mess .= "Было закрыто ".count($rows)." счетов";
		}
		return $mess;
	}



}



/**
 * Class Invoice
 *
 *
 * @author  	Alexey Kapitonov
 * @version 	22.04.2016 12:03:08
 */
	class Invoice  extends aplStdAJAXMethod
	{
		# установив флаг на FALSE - вы отмените некоторые строгие ограничения и войдете в режим тестирования
//		protected 	$production = false;


		public 		$tabName = 'Счета';		// имя вкладки
		protected 	$user_access = 0; 		// user right (int)
		protected 	$user_id = 0;			// user id with base
		public 		$user = array(); 		// authorised user info
		
		public function __construct()
		{	
			// connectin to database
			$this->db();

			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			// geting rights
			if ($this->user_id > 0){
				$this->user_access = $this->get_user_access_Database_Int($this->user_id);
			}



			// calls ajax methods from POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			// calls ajax methods from GET
			## the data GET --- on debag time !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);		
			}
		}
		/*
		 $.post('', {
			AJAX: 'get_link',
			id: $(this).attr('data-rt_id')

			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		 */

		/**
		 * модуль я нашел ошибку
		 */
		protected function send__error_message_AJAX(){
			$message = '<div>от '.$this->getAuthUserName().'<div/>';
			$message .= "<div>http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]</div>";

			if (isset($_POST['message'])){
				$message .= '<div style="padding-top:15px;">'.$_POST['message'].'</div>';
			}

			$this->send__error_message($message);
			$this->responseClass->addMessage('Сообщение об ошибке отправлено. Мы благодарим Вас за вашу бдительность. =) ','system_message', 2000);
		}

		/**
		 * отправка сообщения по ошибке
		 *
		 * @param $message
		 */
		private function send__error_message($message){
			// объект с инфой по пользователю
//			$this->user;

			$subject = 'Я нашёл ошибку (счета)';

			// получаем  email отправителя сообщения об ошибке
			if(filter_var($this->user['email'], FILTER_VALIDATE_EMAIL)){
				$from = $this->user['email'];
			}else if(filter_var($this->user['email_2'], FILTER_VALIDATE_EMAIL)){
				$from = $this->user['email_2'];
			}

			if ($message != ''){
				// $message = '';

				$userName = '';
				$href = '';
				# подгружаем шаблон
				ob_start();
				include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';

				$html = ob_get_contents();
				ob_get_clean();


				$Invoice = new InvoiceNotify();
				$Invoice->sendMessageToId([42],$from,$subject,$html);
			}
		}



		/**
		 * buch the confirmation create ttn
		 */
		protected function confirm_create_ttn_AJAX(){
			$this->db();


			$query = "UPDATE `".INVOICE_TTN."` SET ";
			$query .= "`number` = '".(int)$_POST['number'] ."'";
			if(isset($_POST['date'])){
				$query .= ",`date` =  '".date("Y-m-d",strtotime($_POST['date']))."' ";
			}
			$query .= ",`buch_id` = '".$this->user_id."'";
			$query .= ",`buch_name` = '".$this->getAuthUserName()."'";
			$query .= " WHERE `id` = '".$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);



			# добавляем созданную ттн к количеству созданных
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `ttn_build` = (ttn_build + 1)";
			$query .= " WHERE `id` =?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();


			# сообщение менеджеру edit_ttn_status
			# сообщение на почтуconfirm_create_ttn
			$Invoice = new InvoiceNotify();
			$subject = 'Для счёта № '.$_POST['invoice_num'].' ('.$_POST['client_name'].') была создана УПД';
			$userName = $_POST['manager_name'];
			$message = $subject.' №'.$_POST['number'];
			$href = 'http://www.apelburg.ru/os/?page=invoice&section=6&client_id='.$_POST['client_id'];
			# подгружаем шаблон
			ob_start();
				// include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
				include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
				$html = ob_get_contents();
			ob_get_clean();

            if ($Invoice->sendMessageToId($_POST['manager_id'],'',$subject,$html)){
                $this->responseClass->addMessage('Менеджеру '.$userName.' было отправлено уведомление на почту.');
            }
		}


//		protected function test_send_message_AJAX(){
//            $Invoice = new InvoiceNotify();
//            $userName = "мое";
//            if ($Invoice->sendMessageToId([42],'',$subject="тема",$html = "письмо тестера array")){
//                $this->responseClass->addMessage('Менеджеру '.$userName.' было отправлено уведомление на почту.array');
//            }
//            if ($Invoice->sendMessageToId(42,'',$subject="тема",$html = "письмо тестера id")){
//                $this->responseClass->addMessage('Менеджеру '.$userName.' было отправлено уведомление на почту.id');
//            }
//        }

		/**
		 * оповещаем менеджера обизменениях в приходах по счёту
		 */
		protected function payment_window_is_editable_AJAX(){
			$Invoice = new InvoiceNotify();
			$subject = 'Внесены изменения в приходы по счёту № '.$_POST['invoice_num'].' ('.$_POST['client_name'].')';
			
			$userName = $_POST['manager_name'];
			$message= 'Клиент '.$_POST['client_name'].'<br>';
			$message .= 'в приходы по счёту № '.$_POST['invoice_num'].' ('.$_POST['client_name'].') были внесены изменения<br>';
			$message .= 'Сумма счета: '.$_POST['price_out'].'р.<br>';
			$message .= 'Cумма оплаты на данный момент составляет: '.$_POST['price_out_payment'].' р.<br>';
			$message .= 'что составляет '.round((int)$_POST['percent_payment'], 2).'% от суммы счёта';
			$href = 'http://www.apelburg.ru/os/?page=invoice&section=0&client_id='.$_POST['client_id'];
			# подгружаем шаблон

			ob_start();
                // include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
                include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
                $html = ob_get_contents();
			ob_get_clean();

            if ($Invoice->sendMessageToId($_POST['manager_id'],'',$subject,$html)){
                $this->responseClass->addMessage('Менеджеру '.$userName.' было отправлено уведомление на почту.');
            }

		}

		/**
		 * автокоплит поиска счёта.
         * отфильтрован по `closed` <= 1
         * если `closed` > 1 то счёт аннулирован или удален
		 */
		protected function shearch_invoice_autocomlete_AJAX(){

			$query="SELECT id, RIGHT(CONCAT('0000000', (invoice_num)),8) as `invoice_num` FROM `".INVOICE_TBL."`  WHERE `invoice_num` LIKE ?  AND `closed` <= 0 ";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

            $searchInt = (int)$_POST['search'];
            $search = '%'.$searchInt.'%';
			$stmt->bind_param('s', $search) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$response = array();

			$i=0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					// $response[] = $row['company'];
					$response[$i]['label'] = $row['invoice_num'];
					$response[$i]['value'] = $row['invoice_num'];
					$response[$i]['href'] = '#';
//					$response[$i]['href'] = $_SERVER['REQUEST_URI'].'&client_id='.$row['id'];
					$response[$i++]['desc'] = $row['id'];
				}
			}
//			echo $this->printArr($response);
			echo json_encode($response);
			exit;
		}



		/**
		 * return data
		 */
		protected function get_data_AJAX(){
			$response = array(
				'access' => $this->user_access,
				'data' => $this->get_data()
				);
			echo json_encode($response);
			exit;
		}
		/**
		 * получение данных для склада
		 */
		protected  function get_data_sklad_AJAX(){
			$response = array(
				'access' => $this->user_access,
				'data' => $this->get_data_sklad()
			);
			echo json_encode($response);
			exit;
		}

		/**
		 * create new ttn
		 *
		 */
		protected function create_new_ttn_AJAX(){
			$message  = '<b>Method:</b> '.__METHOD__.'<br>';
			$message .= $this->printArr($_POST);
            $this->prod__message($message);
			// $this->responseClass->addMessage($message,'system_message');
			
			$query = "INSERT INTO `".INVOICE_TTN."` SET ";
			    // $query .= "`id` = '',";
			    // дата создания заявки
				$query .= "`date` = '".date('Y-m-d',strtotime($_POST['date']))."'";

			    $query .= ",`position_id` = '".$_POST['positions']."'";
			    $query .= ",`positions_num` = '".$_POST['position_numbers']."'";
			    $query .= ",`delivery` = '".$_POST['delivery']."'";
				$query .= ",`date_shipment` = '".date('Y-m-d',strtotime($_POST['date_shipment']))."'";
			    $query .= ",`invoice_id` = '".$_POST['invoise_id']."'";

			$message .= '<br>'.$query;
			
//			echo $query;
			// $query = "SELECT * FROM `".INVOICE_TTN."` WHERE `invoice_id` IN ('".implode("','",$id_s)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);


			$insert_id = $this->mysqli->insert_id;				
			$query = "UPDATE `".INVOICE_ROWS."` SET ";
			$query .= "`ttn_id` = '".$insert_id ."'";

			$query .= " WHERE `id` IN (".$_POST['positions'].")";
			// $this->responseClass->addSimpleWindow($message.'<br>'.$_POST['positions'],'Создание TTN');
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			// обновляем  данные по заказанному в ттн количеству позиций
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `positions_in_ttn` = '".(int)$_POST['positions_in_ttn'] ."'";
			$query .= ", `ttn_query` = (ttn_query + 1) ";
			$query .= " WHERE `id` = '".$_POST['invoise_id']."' ";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);


			// сборка возвращаемого объекта для апдейта строки
			$data = array(
				'id' => $insert_id,
				'date'=>$_POST['date'],
				'position_id' => $_POST['positions'],
			    'positions_num' => $_POST['position_numbers'],
			    'delivery' => $_POST['delivery'],
			    'invoice_id' => $_POST['invoise_id'],
				);

			$this->responseClass->response['data'] = $data;
		}

        /**
         * присваиваем номер ТТН
         */
		protected function update_ttn_number_AJAX(){
			$this->update_ttn_number((int)$_POST['val'], (int)$_POST['id']);
		}

        /**
         * присваиваем номер ТТН
         *
         * @param int $number
         * @param int $id
         */
		private function update_ttn_number($number = 0, $id = 0){
            $query = "UPDATE `".INVOICE_TTN."` SET ";
            $query .= "`number` = '".$number ."'";
            $query .= " WHERE `id` = '".$id."'";
            $result = $this->mysqli->query($query) or die($this->mysqli->error);
            # отладка в режиме разработчика
            $this->prod__window($query.'<br>'.$this->printArr($_POST), 'отладка');
        }

		/**
		 * ТТН возвращена в подписанном виде / отмена возврата ттн
		 **/
		protected function ttn_was_returned_AJAX(){
            $this->ttn_was_returned((int)$_POST['val'], $this->getUserId(), $this->getAuthUserName(), (int)$_POST['id'] );
		}

        /**
         * ТТН возвращена в подписанном виде / отмена возврата ттн
         *
         * @param $return
         * @param $userId
         * @param $userName
         * @param $id
         */
		private function ttn_was_returned($return, $userId, $userName, $id ){
            $date = date('Y-m-d',time());
		    $query = "UPDATE `".INVOICE_TTN."` SET ";
            $query .= "`return` =?";
            $query .= ",`buch_id` =?";
            $query .= ",`buch_name` =?";
            $query .= ",`date_return` =?";
            $query .= " WHERE `id` =?";

            $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
            $stmt->bind_param('iissi',$return, $userId, $userName, $date, $id) or die($this->mysqli->error);
            $stmt->execute() or die($this->mysqli->error);
            $result = $stmt->get_result();
            $stmt->close();

            # отладка в режиме разработчика
            $this->prod__window($query.'<br>'.$this->printArr($_POST), 'отладка');
        }

        /**
         * @return int
         */
        public function getUserId()
        {
            return $this->user_id;
        }
		

        /**
         * get user full name
         *
         */
        private function getAuthUserName(){
			$name = '';
			if($this->user['last_name'] != ''){
				$name = $this->user['last_name'];
			}
			if($this->user['name'] != ''){
				if($this->user['last_name'] != ''){
					if($this->user['name']!=''){
						$name .= ' '.mb_substr($this->user['name'],0,2).'.';	
					}
				}else{
					$name .= $this->user['name'];
				}				
			}
        	return $name;
        }

		/**
		 * сохраняет комментарии
		 */
		protected function save_invoice_comment_AJAX(){


			# заносим данные в таблицу
			$userName = $this->getAuthUserName();
			$query = "INSERT INTO `".INVOICE_COMMENTS."` SET ";
			$query .= "`invoice_id` =?";
			$query .= ",`user_id` =?";
			$query .= ",`user_name` =?";
			$query .= ", `create_time` = NOW()";
			$query .= ", `comment_text` =?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('iiss',$_POST['invoice_id'],$this->user_id,$userName,$_POST['comment']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();


//			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// возвращаем полученные данные
			$this->responseClass->response['data'] = array(
				'comment_id'=>$this->mysqli->insert_id,
				'user_id'=>$this->user_id,
				'user_name'=>$userName,
				'create_time'=>date('d.m.Y H:i',time()),

			);

			# правка в главной таблице счетов
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `comments_num` =?";
			$query .= " WHERE `id` =?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('ii',$_POST['comments_num'],$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}



		/**
         * присваиваем номер счёта
		 */
		protected function confirm_create_bill_AJAX(){
			$i = 0;
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			if(isset($_POST['date'])){
				$query .= (($i>0)?',':'')." `invoice_create_date` = '".date('Y-m-d',strtotime($_POST['date']))."'";$i++;

				if(isset($_POST['doc_type']) && isset($_POST['doc_id']) && $_POST['doc_type'] != 'spec'){
					$this->save_specification_number( $_POST['number'], date('Y-m-d',strtotime($_POST['date'])), $_POST['doc_id'] );
				}
			}
			if(isset($_POST['number'])){
				$query .= (($i>0)?',':'')." `invoice_num` = '".$_POST['number']."'";$i++;
			}

			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
//			$this->responseClass->addSimpleWindow($this->printArr($_POST).'<br>'.$query.'<br>'.$i,'');
			if ($i>0){
				$result = $this->mysqli->query($query) or die($this->mysqli->error);

				# сообщение на почтуconfirm_create_ttn
				$Invoice = new InvoiceNotify();
				$subject = 'Для клиента '.$_POST['client_name'].' был заведён счёт';
				$userName = $_POST['manager_name'];
				$message= 'Для клиента '.$_POST['client_name'].' заведён новый счет';
				$href = 'http://www.apelburg.ru/os/?page=invoice&section=2&client_id='.$_POST['client_id'];
				# подгружаем шаблон
				ob_start();
				// include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
				include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
				$html = ob_get_contents();
				ob_get_clean();
				$Invoice->sendMessageToId($_POST['manager_id'],'',$subject,$html);

			}else{
				$this->responseClass->addMessage('Вы не указали данные для сохранения');


			}
		}

        /**
         *тест отправки сообщения на почту по id юзера
         */
		protected function test_message_template_AJAX(){
			$Invoice = new InvoiceNotify();
			# подгружаем шаблон
			ob_start();

			$subject = '';
			$userName = $this->getAuthUserName();
			$message= 'Привет мир';
			$href = '#';

			$subject = '';
			$userName = 'Лапочка тест';
			$message= 'Привет мир';
			$href = '#';
			// include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
			include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';

			$subject = '';
			$userName = 'Лапочка тест';
			$message= 'Привет мир';
			$href = '#';
			$html = ob_get_contents();
			ob_get_clean();

			$options['width'] = '100%';
			$options['height'] = 500;
			$this->responseClass->addSimpleWindow($html,'',$options);

			$Invoice->sendMessageToId([42],'','Для клиента Имя клиента был заведён счёт',$html);
		}

//		/**
//		 * save status
//		 *
//		 */
//		protected function save_shipped_status_AJAX(){
//			$query = "UPDATE `".INVOICE_TBL."` SET ";
//			$query .= " `status`=?";
//			$query .= " WHERE `id`=?";
//
//			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
//			$stmt->bind_param('si',$_POST['status'],$_POST['id']) or die($this->mysqli->error);
//			$stmt->execute() or die($this->mysqli->error);
//			$result = $stmt->get_result();
//			$stmt->close();
//		}

		protected function getInvoceRow_AJAX(){
			if (isset($_POST['invoice_num'])){
				$search['invoice_num'] = $_POST['invoice_num'];
			}
			if (isset($_POST['id'])){
				$search['id'] = $_POST['id'];
			}


			$this->responseClass->response['data'] = $this->get_data($search);
			switch ($InvCount = count($this->responseClass->response['data'])){
				case 1:
					return;
					break;
				case 0:
					$this->responseClass->response['data'] = [];
					$this->responseClass->addMessage('Такого номера счёта не существует');
					break;
				default:

					$this->responseClass->addMessage('Вы не полностью ввели номер счёта, найдено '.$InvCount.' совпадений.');
					$this->responseClass->response['data'] = [];
					break;
			}
		}

		/**
		 * get data rows
		 *
		 * @param int $id
		 * @return array
		 */
		private function get_data($curSearch = array('invoice_num'=>'','id'=>0)){
			$w = 0;
			//  получаем информацию по строкам
			$query = "SELECT * ";
			$query .= ", invoice_num as invoice_num_old";
			$query .= ", RIGHT(CONCAT('0000000' , (invoice_num)),8) as invoice_num";
			$query .= " , DATE_FORMAT(`".INVOICE_TBL."`.`invoice_create_date`,'%d.%m.%Y') as invoice_create_date ";
			$query .= " , DATE_FORMAT(`".INVOICE_TBL."`.`spf_return_date`,'%d.%m.%Y') as spf_return_date ";

			$query .= " FROM `".INVOICE_TBL."` ";
			// $query = "  SORT BY `id` DESC";
			if($this->user_access == 5){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `manager_id` = '".$this->user_id."' ";
				$w++;
			}
//			echo $query;


			if((int)$curSearch['id'] > 0){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `id` = '".$curSearch['id']."' ";
				$w++;
			}else if( $curSearch['invoice_num'] != '' ){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `invoice_num` = '".$curSearch['invoice_num']."' ";
				$w++;
			}else{
                // если мы в клиенте
                if(isset($_GET['client_id'])){
                    $query .= ($w>0?' AND ':' WHERE ');
                    $query .= " `client_id` = '".(int)$_GET['client_id']."' ";
                    $w++;
                }

                // если мы в клиенте
                if(isset($_GET['manager_id'])){
                    $query .= ($w>0?' AND ':' WHERE ');
                    $query .= " `manager_id` = '".(int)$_GET['manager_id']."' ";
                    $w++;
                }

				// если мы не используем поиск
				// правила выборки счетов по вкладкам
				if (isset($_GET['section'])){

					if($_GET['section'] != 0 && $_GET['section'] != 9 && $_GET['section'] != 14 && $_GET['section'] != 15){
						$query .= ($w>0?' AND ':' WHERE ');
						$query .= " `closed` = '0'";
						$w++;
					}


					switch ((int)$_GET['section']){
                        // все
					    case 0:
                            $query .= ($w>0?' AND ':' WHERE ');
                            $query .= " `closed` <= 1";
                            $w++;

                            break;
						// Запрос
						case 1:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " (`invoice_num` = '0' OR `invoice_create_date` = '0000-00-00')";
							$w++;

							break;
						// Готовые
						case 2:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `invoice_num` <> '0' AND `price_out_payment` =  0";
							$w++;
							$query .= " AND `invoice_create_date` <> '0000-00-00' ";
							break;
						// Част. оплаченные
						case 3:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `price_out_payment` >  0  AND `price_out_payment` <  `price_out`";
							$w++;
							break;
						// Оплаченные
						case 4:
							$query .= ($w>0?' AND ':' WHERE ');
//							$query .= " `price_out_payment` >=  `price_out` ";
							$query .= " `price_out_payment` >  0 ";
							$query .= " AND `status` <>  'отгружен' ";
							$w++;
							break;
						// Запрос ТТН
						case 5:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `ttn_query` >  0 ";
							$w++;
							break;
						// Готовые ТТН
						case 6:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `ttn_build` >  0 ";
							$w++;
							break;
						// Част. отгрузка
						case 7:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `status` =  'частично отгружен' ";
							$w++;
							break;
						// Отгрузка
						case 8:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `status` =  'отгружен' ";
							$w++;
							break;
						// Закрытые
						case 9:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `closed` = 1";
							$w++;
							break;
						// аннулирован
						case 14:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `closed` = '2'";
							$w++;
							break;
						// удален бухгалтерией
						case 15:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `closed` = '3'";
							$w++;
							break;
						// все остальные
						default:
							break;
					}
				}

				# сортировка по номеру счёта
				if($_GET['section'] != 1 /*&& $_GET['section'] != 9 */&& $_GET['section'] != 14 && $_GET['section'] != 15){
					$query.= " ORDER BY `invoice_num_old` DESC";
				}
			}

//            echo $query;


			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->data =$this->depending['id']= array();

			$data_id_s = array();
			$i = 0;

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->data[$i] = $row;
					$this->data[$i]['ttn'] = array();
					$data_id_s[] = $row['id'] ;
					// зависимости в id
					$this->depending['id'][$row['id']] = $i++;
				}
			}
			// запрос ттн
			$this->get_ttn_rows($data_id_s,$curSearch);
//			echo $query;
			return $this->data;


		}

		/**
		 * положить счёт в корзину
		 */
		protected function delete_to_basket_invoice_AJAX(){
			# в корзину кладут бухи и админы
			if($this->user_access != 1 && $this->user_access != 2){
				$this->responseClass->addMessage('У вас не достаточно прав для совершения данного действия.','error_message',2000);
				if ($this->prod__check()){return;}
			}
			$this->responseClass->addMessage('Счёт успешно удалён ','successful_message',2000);

			$this->change_closed_status((int)$_POST['id'],3);
		}

		/**
		 * аннулировать
		 */
		protected function repeal_invoice_AJAX(){
			# бух и админ
			if($this->user_access != 1 && $this->user_access != 2 && $this->user_access != 5){
				$this->responseClass->addMessage('У вас не достаточно прав для совершения данного действия.','error_message',2000);
				if ($this->prod__check()){return;}
			}

			$this->responseClass->addMessage('Счёт успешно аннулирован','successful_message',1000);
			$this->change_closed_status((int)$_POST['id'],2);
		}

		/**
		 * возврат счёта из закрытых в работу
		 */
		protected function remove_from_closed_AJAX(){
			# бух и админ
			if($this->user_access != 1 && $this->user_access != 2){
				$this->responseClass->addMessage('У вас не достаточно прав для совершения данного действия.','error_message',2000);
				if ($this->prod__check()){return;}
			}


			$this->change_closed_status((int)$_POST['id'],0);
			$this->responseClass->addMessage('Счёт возвращён в работу','successful_message',1000);
		}

		/**
		 При регистрации оплаты поставщику ООО "ХХХ" (в оплату затрат по счету №ХХХ для клиента ХХХ), произошел отказ по причине отсутствия данного юр. лицо ООО"ХХХ" в системе.
		 Пожалуйста, внесите данные юридического лица ООО"ХХХ" в карточку необходимого поставщика.
		 */

		/**
		 * запрос буха на создание реквизитов
		 * отправляет запрос на почту ВСЕМ СНАБАМ
		 */
		protected function query_get_new_requisit_AJAX(){
			$where = [
				'access' => '8'
			];

			# опрашиваем базу по наличию учеток СНАБОВ
			$snab = $this->get_all_tbl_simple(MANAGERS_TBL,$where);

			# если найдены снабы
			if (count($snab) > 0){
				$ids = [];
				foreach ($snab as $user){
					$ids[] = $user['id'];
				}
				$Invoice = new InvoiceNotify();
				# рассылаем
				ob_start();
				$message = nl2br($_POST['message']);

				$href = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				# кнопка перехода в раздел поставщиков

//				$message .= $href;
				$message .= '<div id="button_div" style="padding: 45px 0 25px 0;"><a style="font-family: Arial;
                            font-weight: normal;font-size: 16px;color: #ffffff;text-decoration: none;white-space: nowrap;border-radius: 3px;display: inline-block;text-align: center;margin-top: 0px;margin-left: 0px;
                            padding-top: 11px;
                            padding-right: 14px;
                            padding-bottom: 9px;
                            padding-left: 12px;
                            background-color: #95B45A;" href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=suppliers&section=suppliers_list">В раздел поставщиков</a></div>';
				$userName = "";



				include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
				$html = ob_get_contents();
				ob_get_clean();

				//$Invoice->sendMessageToId($ids,'',"Запрос на создание реквизитов поставщика",$_POST['message']);
				$Invoice->sendMessageToId([62, 40],'',"Запрос на создание реквизитов поставщика",$html);

				# вывод данных в режиме разработчика
				$this->prod__window($this->printArr($ids).'<br>'.$this->printArr($Invoice->getUsersEmail($ids)));


				$message = "Ваш запрос направлен в отдел снабжения";
				$this->responseClass->addMessage($message,'successful_message',1000);
			}
		}


		/**
		 * полное удаление счёта из базы
		 */
		protected function delete_invoice_row_AJAX(){
			# админ
			if($this->user_access != 1){
				$this->responseClass->addMessage('У вас не достаточно прав для совершения данного действия.','error_message',2000);
				if ($this->prod__check()){return;}
			}


			# удаление самого счёта
			$query = "DELETE FROM `".INVOICE_TBL."` WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			# удаление платёжных поручений по счёту
			$query = "DELETE FROM `".INVOICE_PP."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			# удаление строк позиций
			$query = "DELETE FROM `".INVOICE_ROWS."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			# удаление запрошенных ттн
			$query = "DELETE FROM `".INVOICE_TTN."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			# удаление комментариев
			$query = "DELETE FROM `".INVOICE_COMMENTS."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();


			# выборка данных по счетам от поставщиков
			$query = "SELECT * FROM `".INVOICE_COSTS."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$ids = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$ids[] = $row['id'];
				}
			}
			# если строки от поставщиков были найдены
			if(count($ids)){
				# удаление данных по счетам от поставщиков
				$query = "DELETE FROM `".INVOICE_COSTS."` WHERE `id` IN ('".implode("','",$ids)."')";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);

				$query = "DELETE FROM `".INVOICE_COSTS_PAY."` WHERE `parent_id` IN ('".implode("','",$ids)."')";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
			}

			$this->responseClass->addMessage('Все данные по счёту были полность удалены','successful_message',2000);
		}

		/**
		 * принудительное закрытие заказа
		 * только для админов
		 */
		protected function closed_invoice_row_AJAX(){
			# админ
			if($this->user_access != 1){
				$this->responseClass->addMessage('У вас не достаточно прав для совершения данного действия.','error_message',2000);
				if ($this->prod__check()){return;}
			}

			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `closed`='1'";
			$query .= ", `closed_date`=?";
			$query .= " WHERE `id` =?";

			$date = date('Y-m-d',strtotime($_POST['date']));

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('si',$date, $_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$this->responseClass->addMessage('Счёт закрыт','successful_message',1000);
		}


		/**
		 * смена статуса корзины
		 *
		 * @param $id
		 * @param $status
		 */
		private function change_closed_status($id = 0, $status = 0){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `closed`=?";
			$query .= " WHERE `id` =?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('ii',$status, $id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}


		/**
		 * запрос из базы строк для приложения склад
		 *
		 * @param int $id
		 * @return array
		 */
		private function get_data_sklad($curSearch = array('invoice_num'=>'','id'=>0)){
			$w = 0;

			$additional_conditions = '';
			if((int)$curSearch['id'] == 0 && $curSearch['invoice_num'] == '') {
				// если мы не используем поиск
				// вычисляем дни просрочки
				$additional_conditions = "CASE ";
				// если профакали - подсвечиваем красным
				$additional_conditions .= " WHEN TO_DAYS(NOW()) - TO_DAYS(`".INVOICE_TTN."`.`date_shipment`) > 0 THEN '#FFAA91'";
				// если сегодня - зелёным
				$additional_conditions .= " WHEN TO_DAYS(NOW()) - TO_DAYS(`".INVOICE_TTN."`.`date_shipment`) = 0 THEN '#B3D073'";
				// если завтра - желтым
				$additional_conditions .= " WHEN TO_DAYS(NOW()) - TO_DAYS(`".INVOICE_TTN."`.`date_shipment`) = -1 THEN '#F3E6AA'";
				$additional_conditions .= " END as ttn_shipment_date_color, ";

			}
			//  получаем информацию по строкам
			$query = "SELECT $additional_conditions`".INVOICE_TTN."`.*,`".INVOICE_TBL."`.*,DATE_FORMAT(`".INVOICE_TBL."`.`invoice_create_date`,'%d.%m.%Y') as invoice_create_date";

			$query .= ",`".INVOICE_TTN."`.`id` as ttn_id ";
			$query .= ",`".INVOICE_TTN."`.`position_id`";
			$query .= ",`".INVOICE_TTN."`.`positions_num`";
			$query .= ",`".INVOICE_TBL."`.`positions_num` as invoice_positions_num";
			$query .= ",`".INVOICE_TTN."`.`number` ";
			$query .= ", DATE_FORMAT(`".INVOICE_TTN."`.`date`,'%d.%m.%Y') as ttn_date ";
			$query .= ", DATE_FORMAT(`".INVOICE_TTN."`.`date_return`,'%d.%m.%Y') as date_return ";
			$query .= ",`".INVOICE_TTN."`.`return` ";
			$query .= ",`".INVOICE_TTN."`.`comments` ";
			$query .= ",`".INVOICE_TTN."`.`delivery` ";
			$query .= ",`".INVOICE_TTN."`.`buch_id` ";
			$query .= ",`".INVOICE_TTN."`.`buch_name` ";
			$query .= ", DATE_FORMAT(`".INVOICE_TTN."`.`date_shipment`,'%d.%m.%Y') as date_shipment ";
			$query .= ",`".INVOICE_TTN."`.`shipment_employee`";
			$query .= ",`".INVOICE_TTN."`.`shipment_status`";
			$query .= ",`".INVOICE_TTN."`.`shipment_employee_id`";
			$query .= ", DATE_FORMAT(`".INVOICE_TTN."`.`shipment_status_last_edit`,'%d.%m.%Y %H:%i') as shipment_status_last_edit ";


			$query .= " FROM `".INVOICE_TBL."`";
			$query .= " INNER JOIN `".INVOICE_TTN."` ON `".INVOICE_TTN."`.`invoice_id` = `".INVOICE_TBL."`.`id`";
			// $query = "  SORT BY `id` DESC";
			if($this->user_access == 5){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `manager_id` = '".$this->user_id."' ";
				$w++;

			}

			$query .= ($w>0?' AND ':' WHERE ');
			$query .= " `".INVOICE_TTN."`.`number` <> '0' ";
			$w++;
//			echo $query;


			if((int)$curSearch['id'] > 0){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `".INVOICE_TBL."`.`id` = '".$curSearch['id']."' ";
				$w++;
			}else if( $curSearch['invoice_num'] != '' ){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `".INVOICE_TBL."`.`invoice_num` = '".$curSearch['invoice_num']."' ";
				$w++;
			}else{
				// если мы в клиенте
				if(isset($_GET['client_id'])){
					$query .= ($w>0?' AND ':' WHERE ');
					$query .= " `client_id` = '".(int)$_GET['client_id']."' ";
					$w++;
				}
                // если мы в клиенте
                if(isset($_GET['manager_id'])){
                    $query .= ($w>0?' AND ':' WHERE ');
                    $query .= " `manager_id` = '".(int)$_GET['manager_id']."' ";
                    $w++;
                }

				if (isset($_GET['section'])){
					switch ((int)$_GET['section']){

						// Част. отгрузка
						case 7:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TBL."`.`status` =  'частично отгружен' ";
							$w++;
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TBL."`.`status` <> 'отгружен' ";
							$w++;
							break;
						// Отгрузка
						case 8:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TBL."`.`status` =  'отгружен' ";
							$w++;
							break;

						case 11:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TTN."`.`delivery` =  'no_delivery' ";
							$w++;
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TBL."`.`status` <> 'отгружен' ";
							$w++;
							break;

						case 12:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TTN."`.`delivery` =  'our_delivery' ";
							$w++;
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `".INVOICE_TBL."`.`status` <> 'отгружен' ";
							$w++;
							break;
						// все остальные
						default:
							break;
					}
					$query .= ($w>0?' AND ':' WHERE ');
					$query .= " `".INVOICE_TBL."`.`closed` = '0'";
					$w++;

				}
			}
			if (isset($_GET['date_start']) && isset($_GET['date_end'])) {
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `".INVOICE_TTN."`.`date_shipment` >= '".date('Y-m-d', strtotime($_GET['date_start']))."' ";
				$query .= " AND `".INVOICE_TTN."`.`date_shipment` <= '".date('Y-m-d', strtotime($_GET['date_end']))."' ";
				$w++;
			}else if (isset($_GET['date_start'])){
				$query .= ($w>0?' AND ':' WHERE ');
				$query .= " `".INVOICE_TTN."`.`date_shipment` = '".date('Y-m-d', strtotime($_GET['date_start']))."'";
				$w++;
			}

			$query .= " ORDER BY `".INVOICE_TTN."`.`date_shipment` ASC";
			//	echo $query;
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->data =$this->depending['id']= array();

			$data_id_s = array();
			$i = 0;

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->data[$i] = $row;
					$data_id_s[] = $row['id'] ;
					// зависимости в id
					$this->depending['id'][$row['id']] = $i++;
				}
			}
			return $this->data;
		}



		/**
		 * тестовый запрос тригера из браузера
		 * удалить после тестирования
		 */
		protected function ppp9898_AJAX(){
			$this->responseClass->addSimpleWindow('test');
			//$this->responseClass->addSimpleWindow($this->triger_check_and_closed_invoice_CRON());
		}

		protected function get_manager_name_AJAX(){
			$Notify = new InvoiceNotify();


			 $this->responseClass->addSimpleWindow($Notify->check_and_closed_invoice_CRON());
		}
		/**
		 * проверка глобального статуса заказа
		 */
		protected function check_shipment_global_status_AJAX(){
			$positions_num = (int)$_POST['positions_num'];
			// $positions_in_ttn = (int)$_POST['positions_in_ttn'];

			$data['status'] = 'не отгружен'; 		// статус по умолчанию
			$shipment_ttn_num = 0; 					// количество отгруженных ттн
			$ttn_num = 0; 							// общее количество ттн
//			$count_positions_in_ttn = 0; 			// количество раскиданных по ттн позиций
			$count_positions_in_ttn_shipment = 0; 	// количество отгруженных позиций

			# получаем список ttn
			$ttn_arr = $this->get_ttn_row($_POST['invoice_id']);
			# перебор ттн
			foreach ($ttn_arr as $val){
			    // вырезаем пробелы если они есть
                $val['positions_num'] = str_replace(" ","",$val['positions_num']);

                $count = count(explode(',',$val['positions_num']));
//				$count_positions_in_ttn += $count;
				$ttn_num++;

				if ($val['shipment_status']>0){
					$data['status'] = 'частично отгружен';
					$shipment_ttn_num++;
					# подсчитываем количество отгруженных позиций
					$count_positions_in_ttn_shipment += $count;
				}
			}

			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= " `status`=?";
			// если количество позиций в отгруженных ттн соответствует общему количеству позиций в счёте
			// меняем статус на отгружен
			if($positions_num == $count_positions_in_ttn_shipment){
				$data['status'] = 'отгружен';
				$query .= ", `shipped_date`=NOW()";
			}




			$query .= " WHERE `id` =?";
			$status = $data['status'];


			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('si',$status, $_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$this->responseClass->response['data'] = $data;


		}

		/**
		 * получаем комментарии по выбранному счёту
		 */
		protected function get_comments_module_AJAX(){
			$query = "SELECT * ";
			$query .= ", DATE_FORMAT(`create_time`,'%d.%m.%Y %H:%i')  AS `create_time`";

			$query .= "FROM `".INVOICE_COMMENTS."` WHERE `invoice_id`=?";


			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			$this->responseClass->response['data'] = $data;
		}


		/**
		 * get ttn from id
		 *
		 */
		protected function get_ttn_AJAX(){

			$data = $this->get_invoice_rows($_POST['id']);

			// возвращаем полученные данные
			$this->responseClass->response['data'] = $data; 

		}

		/**
		 * запрос позиций по id счёта
		 * @param $id
		 * @return mixed
		 */
		private function get_invoice_rows($id){
			$query = "SELECT * FROM `".INVOICE_ROWS."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}

			return $data;
		}


		/**
		 * запрос ttn по id счёта
		 * @param $id
		 * @return mixed
		 */
		private function get_ttn_row($id){
			$query = "SELECT * FROM `".INVOICE_TTN."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			return $data;
		}

		/**
		 * create payment
		 *
		 */
		protected  function create_payment_AJAX(){
			$userName = $this->getAuthUserName();
			$query = "INSERT INTO `".INVOICE_PP."` SET ";
			$query .= "`invoice_id` =?";
			$query .= ",`buch_id` =?";
			$query .= ",`buch_name` =?";
			$query .= ", `date` = NOW()";
			$query .= ", `lasttouch` = NOW()";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('iis',$_POST['id'],$this->user_id,$userName) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();


//			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// возвращаем полученные данные
			$this->responseClass->response['data'] = array(
				'id'=>$this->mysqli->insert_id,
				'buch_id'=>$this->user_id,
				'buch_name'=>$userName,
				'create'=>date('d.m.Y H:i',time()),
				'del'=>0,
				'edit'=>1
			);
			// $this->responseClass->addSimpleWindow($this->printArr($data),'Создание TTN');
		}
		/**
		 * занесение строки (счета) расходов по поставщикам
		 */
		protected  function create_costs_AJAX(){
			$userName = $this->getAuthUserName();
			// создание строки счёта от поставщика
			$query = "INSERT INTO `".INVOICE_COSTS."` SET ";
			$query .= "`invoice_id` =?";
			$query .= ",`buch_id` =?";
			$query .= ",`buch_name` =?";
			$query .= ", `lasttouch` = NOW()";


			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);


			$stmt->bind_param('iis',$_POST['id'],$this->user_id,$userName) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$parent_id = $this->mysqli->insert_id;

			$insert_costs__pay_id = $insert_costs_id = $this->create_coasts_pay_row($parent_id,$userName);

			// возвращаем полученные данные
			$this->responseClass->response['data'] = array(
				'id'=>$parent_id,
				'buch_id'=>$this->user_id,
				'buch_name'=>$userName,
				'create'=>date('d.m.Y H:i',time()),
				'date'=>'00.00.0000',
				'pay_date'=>'00.00.0000',
				'del'=>0,
				'price'=>'0.00',
				'edit'=>1,
				'pay_id'=>$insert_costs__pay_id
			);
		}

		/**
		 * удаление строки оплаты поставщику
		 */
		protected function delete_costs_payment_AJAX(){
			$query = "DELETE FROM `".INVOICE_COSTS_PAY."` WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

		}

		/**
		 * создание строки оплаты поставщику некоторой суммы
		 */
		protected  function new_costs_payment_row_AJAX(){
			$userName = $this->getAuthUserName();
			$insert_costs__pay_id = $this->create_coasts_pay_row($_POST['parent_id'],$userName);

			// возвращаем полученные данные
			$this->responseClass->response['data'] = array(
				'id'=>(int)$_POST['parent_id'],
				'buch_id'=>$this->user_id,
				'buch_name'=>$userName,
				'create'=>date('d.m.Y H:i',time()),
				'del'=>0,
				'edit'=>1,
				'pay_id'=>$insert_costs__pay_id
			);
		}


		private function create_coasts_pay_row($parent_id,$userName){
			$query = "INSERT INTO `".INVOICE_COSTS_PAY."` SET ";
			$query .= "`parent_id` =?";
			$query .= ",`buch_id` =?";
			$query .= ",`buch_name` =?";
			$query .= ", `lasttouch` = NOW()";
			$query .= ", `date` = '0000-00-00'";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('iis',$parent_id,$this->user_id,$userName) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			return $this->mysqli->insert_id;
		}



		/**
		 *	сохраняет проценты оплаты и сумму оплаты по счёту поставщика
		 */
		protected function save_costs_payment_row_AJAX()
		{
			$query = "UPDATE `" . INVOICE_COSTS_PAY . "` SET ";
			$query .= " `price`=?";
			$query .= ", `percent`=?";
			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('ddi',$_POST['price'],$_POST['percent'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

        /**
         * сохраняем сумму, которая была оплачена поставщику по счёту
         */
		protected function save_invoice_costs_payment_AJAX(){
            $query = "UPDATE `" . INVOICE_TBL . "` SET ";
            $query .= " `costs`=?";
            $query .= " WHERE `id`=?";

            $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

            $stmt->bind_param('di',$_POST['cost'], $_POST['invoice_id']) or die($this->mysqli->error);
            $stmt->execute() or die($this->mysqli->error);
            $result = $stmt->get_result();
            $stmt->close();
        }




		protected function edit_ttn_status_AJAX(){
			$query = "UPDATE `" . INVOICE_TTN . "` SET ";
			$query .= " `shipment_employee`=?";
			$query .= ", `shipment_employee_id`=?";
			$query .= ", `shipment_status`=?";
			$query .= ", `shipment_status_last_edit`= NOW()";
			$query .= " WHERE `id`=?";

			$user_name = $this->getAuthUserName();

			$this->responseClass->response['data'] = ['when_ho'=>$user_name.'; '.date('d.m.Y H:i',time())];

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('sisi',$user_name,$this->user_id,$_POST['shipment_status'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			# сообщение менеджеру edit_ttn_status
			# сообщение на почтуconfirm_create_ttn
			$Invoice = new InvoiceNotify();
			$subject = 'Для счёта № '.$_POST['invoice_num'].' ('.$_POST['client_name'].') был изменён статус отгрузки по УПД №'.$_POST['number'];
			$userName = $_POST['manager_name'];
			$message = 'по УПД №'.$_POST['number'].' был изменён статус отгрузки на '.(($_POST['shipment_status']>0)?'отгружен':'не отгружен');
			$href = 'http://www.apelburg.ru/os/?page=invoice&section=7&client_id='.$_POST['client_id'];
			# подгружаем шаблон
			ob_start();
			// include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
			include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
			$html = ob_get_contents();
			ob_get_clean();
			$Invoice->sendMessageToId($_POST['manager_id'],'',$subject,$html);
		}


		/**
		 * редактирование информации по отгрузке для отдельных артикулов
		 */
		protected function not_shipped_edit_AJAX(){
			$query = "UPDATE `" . INVOICE_ROWS . "` SET ";
			$query .= " `not_shipped`=?";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('ii',$_POST['not_shipped'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();


			$query = "UPDATE `" . INVOICE_TBL . "` SET ";
			$query .= " `positions_num` = (positions_num - 1) ";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}


		/**
		 *	save percent in costs
		 */
		protected function save_costs_payment_percent_AJAX(){
			$query = "UPDATE `" . INVOICE_COSTS_PAY . "` SET ";
			$query .= " `percent`=?";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('di',$_POST['percent'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			# сохраняем общую сумму выставленных нам поставщиками счётов в строку счёта
            $this->costs_supplier_bill($_POST['costs_supplier_bill'],$_POST['invoice_id']);
		}


		protected function costs_supplier_bill_AJAX(){
            # сохраняем общую сумму выставленных нам поставщиками счётов в строку счёта
            $this->costs_supplier_bill($_POST['costs_supplier_bill'],$_POST['invoice_id']);
        }

        /**
         * сохраняем общую сумму выставленных нам поставщиками счётов в строку счёта
         *
         * @param float $money
         * @param int $id
         */
        private function costs_supplier_bill($money = 0.0 , $id = 0 ){
            if ((int)$id > 0){
                $this->db();
                $query = "UPDATE `" . INVOICE_TBL . "` SET ";
                $query .= " `costs_supplier_bill`=?";
                $query .= " WHERE `id`=?";
                $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
                $stmt->bind_param('di',$money, $id) or die($this->mysqli->error);
                $stmt->execute() or die($this->mysqli->error);
                $result = $stmt->get_result();
                $stmt->close();
            }
        }



		/**
		 * edit flag ice cost payment
		 * пометка счёта как проверенный
		 */
		protected function edit_glag_ice_costs_pay_AJAX(){
			$query = "UPDATE `".INVOICE_COSTS."` SET ";
			$query .= "`flag_ice`=?";
			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('ii',$_POST['val'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}


		/**
		 * update percent payment from invoice
		 */
		protected function save_percent_from_invoice_AJAX()
		{
			$query = "UPDATE `" . INVOICE_TBL . "` SET ";
			$query .= " `percent_payment`=?";
			$query .= ", `price_out_payment`=?";

			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('ddi',$_POST['percent_payment'],$_POST['price_out_payment'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}
		/**
		 * update percent costs from invoice
		 */
		protected function save_percent_costs_invoice_AJAX()
		{

			$query = "UPDATE `" . INVOICE_TBL . "` SET ";
			$query .= " `percent_payment`=?";
			$query .= ", `price_out_payment`=?";

			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('ddi',$_POST['percent_payment'],$_POST['price_out_payment'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}


		/**
		 * update payment rows
		 *
		 */
		protected  function  save_payment_row_AJAX()
		{
			$query = "UPDATE `" . INVOICE_PP . "` SET ";
			$i = 0;
			$mess = '';
			$myReturn = 1;
			foreach ($_POST as $key => $val) {

				if ($key != 'id' && $key != 'edit' && $key != 'AJAX' && $key != 'date') {
//					$mess .= " $key => $val;";
					$query .= (($i > 0) ? ',' : '') . "`" . $key . "` = '" . $val . "'";
					$i++;
					$myReturn = 0;
				} else if ($key == 'date') {
//					$mess .= " $key => $val;";
					$query .= (($i > 0) ? ',' : '') . "`" . $key . "` = '" . date('Y-m-d', strtotime($val)) . "'";
					$i++;
					$myReturn = 0;
				} else {
//					$this->responseClass->addSimpleWindow($this->printArr($_POST).$query,'tester info');

				}


			}
			if ($myReturn > 0){
				$this->responseClass->addSimpleWindow($mess . '<br>' . $this->printArr($_POST) . $query, 'tester info');
				return;
			}
			$query .= ", `lasttouch` = NOW()";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

            $this->prod__window($query);
		}


        /**
         * удаление строки оплаты
         */
		protected function delete_payment_AJAX(){
		    $this->delete_row_from_table(INVOICE_PP, (int)$_POST['id']);
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->responseClass->response['response'] = "noClose";
			$this->responseClass->addMessage('Запись была удалена.','successful_message');
		}



		/**
		 * get pp from invoice id
		 */
		protected function get_payment_AJAX(){
			$query = "SELECT *, DATE_FORMAT(`create`,'%d.%m.%Y %H:%i')  AS `create`, DATE_FORMAT(`date`,'%d.%m.%Y')  AS `date` FROM `".INVOICE_PP."` ";
			$query .= " WHERE `invoice_id` =?";
			if(isset($_POST['not_deleted_row'])){
				$query .= " AND `del` = 0";
			}
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$types = 'i';


			$stmt->bind_param($types,$_POST['id']) or die($this->mysqli->error);

			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}

			// возвращаем полученные данные
			$this->responseClass->response['data'] = $data;
//			$this->responseClass->addSimpleWindow($this->printArr($data),'Создание TTN');
		}

		/**
		 * update costs rows
		 *
		 */
		protected  function  save_costs_row_AJAX()
		{
			$query = "UPDATE `" . INVOICE_COSTS . "` SET ";
			$i = 0;
			$mess = '';
			$myReturn = 1;
			foreach ($_POST as $key => $val) {

				if ($key != 'id' && $key != 'edit' && $key != 'AJAX' && $key != 'date') {
					//					$mess .= " $key => $val;";
					$query .= (($i > 0) ? ',' : '') . "`" . $key . "` = '" . $val . "'";
					$i++;
					$myReturn = 0;
				} else if ($key == 'date') {
					//					$mess .= " $key => $val;";
					$query .= (($i > 0) ? ',' : '') . "`" . $key . "` = '" . date('Y-m-d', strtotime($val)) . "'";
					$i++;
					$myReturn = 0;
				} else {
					//					$this->responseClass->addSimpleWindow($this->printArr($_POST).$query,'tester info');

				}


			}
			if ($myReturn > 0){
				$this->responseClass->addSimpleWindow($mess . '<br>' . $this->printArr($_POST) . $query, 'tester info');
				return;
			}
			$query .= ", `lasttouch` = NOW()";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);


		}

		protected function save_payment_costs_AJAX(){
			// сохраняем расходы по счетам поставщиков (оплаченные нами)
			$query = "UPDATE `" . INVOICE_TBL . "` SET ";
			$query .= " `costs`=?";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error.' / * / '.$query);
			$stmt->bind_param('si',$_POST['costs'],$_POST['invoice_id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error.' / * / '.$query);
			$result = $stmt->get_result();
			$stmt->close();



			$query = "UPDATE `" . INVOICE_COSTS . "` SET ";
			$query .= " `percent`=?";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error.' / * / '.$query);
			$stmt->bind_param('si',$_POST['percent'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error.' / * / '.$query);
			$result = $stmt->get_result();
			$stmt->close();

			$this->save_costs_payment_date($_POST['pay_id'],$_POST['pay_date']);

		}

		protected function save_costs_payment_date_AJAX(){
			$this->save_costs_payment_date($_POST['id'],$_POST['date']);
		}
		protected function save_costs_payment_date($id,$date){
			$query = "UPDATE `" . INVOICE_COSTS_PAY . "` SET ";
			$query .= " `date`=?";
			$query .= " WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$date = date("Y-m-d",strtotime($date));
			$stmt->bind_param('si',$date,$id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

		protected function delete_costs_AJAX(){

			$query = "DELETE FROM `" . INVOICE_COSTS . "`";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->responseClass->response['response'] = "noClose";


			$query = "SELECT id FROM `".INVOICE_COSTS_PAY."` WHERE parent_id = '".(int)$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$ids = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$ids[] = $row['id'];
				}
			}
			$query = "DELETE FROM `" . INVOICE_COSTS . "`";
			$query .= " WHERE `id` IN ('".implode("','",$ids)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			$this->responseClass->addMessage('Запись была удалена.','successful_message');
		}
		/**
		 * получаем данные для окна расходы
		 */
		protected function get_costs_AJAX(){
			$query = " SELECT `".INVOICE_COSTS."`.*, DATE_FORMAT(`".INVOICE_COSTS."`.`create`,'%d.%m.%Y %H:%i')  AS `create`, DATE_FORMAT(`".INVOICE_COSTS."`.`date`,'%d.%m.%Y')  AS `date`,
				`".INVOICE_COSTS_PAY."`.id AS pay_id,
				`".INVOICE_COSTS_PAY."`.price AS pay_price,
				`".INVOICE_COSTS_PAY."`.percent AS pay_percent,
				DATE_FORMAT(`".INVOICE_COSTS_PAY."`.date,'%d.%m.%Y') AS pay_date,
				`".INVOICE_COSTS_PAY."`.buch_id AS pay_buch_id,
				`".INVOICE_COSTS_PAY."`.buch_name AS pay_buch_name
  				FROM ".INVOICE_COSTS." ";
			$query .= "LEFT JOIN ";
			$query .= " ".INVOICE_COSTS_PAY." ON ".INVOICE_COSTS_PAY.".parent_id = ".INVOICE_COSTS.".id";
			$query .= " WHERE ".INVOICE_COSTS.".invoice_id = '".(int)$_POST['id']."'";

			if(isset($_POST['not_deleted_row'])){
				$query .= " AND `".INVOICE_COSTS."`.`del` = 0 ";
				$query .= " AND `".INVOICE_COSTS_PAY."`.`percent` <> '100.00' ";
			}

			//			echo $query;
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			// возвращаем полученные данные
			$this->responseClass->response['data'] = $data;
			// $this->responseClass->addSimpleWindow($this->printArr($data),'Создание TTN');
		}

		/**
		 * получаем данные для всплывающей подсказки по расходам
		 */
		protected function get_costs_qtip_AJAX(){
			$query = "SELECT `".INVOICE_COSTS."`.*
			 ,`".INVOICE_COSTS."`.`price` as price1
			 
			 , (`".INVOICE_COSTS."`.`price` - (SELECT SUM(`".INVOICE_COSTS_PAY."`.`price`) FROM `".INVOICE_COSTS_PAY."` WHERE `parent_id` = `".INVOICE_COSTS."`.`id`)) as price
			 
			 
			  
			 FROM ".INVOICE_COSTS." ";

			$query .= " WHERE ".INVOICE_COSTS.".`invoice_id` =? ";

			$query .= " AND `".INVOICE_COSTS."`.`del` = 0 ";
			$query .= " AND `".INVOICE_COSTS."`.`percent` <> 100 ";



			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error.' *** '.$query);

			$stmt->bind_param('i', $_POST['id']) or die($this->mysqli->error.' *** '.$query);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();




			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			// возвращаем полученные данные
			$this->responseClass->response['data'] = $data;
		}

		/**
		 * get ttn rows
		 *
		 * @param $id_s
		 */
		private function get_ttn_rows($id_s,$curSearch = array('invoice_num'=>'','id'=>0)){
			// if(count)
			$additional_conditions = '';
			if(count($id_s) == 0){
				return;
			}
			if((int)$curSearch['id'] == 0 && $curSearch['invoice_num'] == ''){
				// если мы не используем поиск
				// правила выборки счетов по вкладкам
				if (isset($_GET['section'])){
					switch ((int)$_GET['section']){
						// Запрос ТТН
						case 5:

							break;
						// Готовые ТТН
						case 6:
//
							$additional_conditions = "CASE 
										WHEN `".INVOICE_TTN."`.shipment_status = 1 
										   THEN 'greyTtnRow' 
										   ELSE 'blackTtnRow'
								   END as ttn_bgcolor_class, ";

							$additional_conditions .= "CASE 
										WHEN `".INVOICE_TTN."`.shipment_status = 1 
										   THEN 1 
										   ELSE 0
								   END as ttn_lok, ";
							break;
							// отгруженные ттн
						case 7:
							$additional_conditions = "CASE 
										WHEN `".INVOICE_TTN."`.shipment_status = 1 
										   THEN 'greyTtnRow' 
										   ELSE 'blackTtnRow'
								   END as ttn_bgcolor_class, ";

							$additional_conditions .= "CASE 
										WHEN `".INVOICE_TTN."`.shipment_status = 1 
										   THEN 0 
										   ELSE 1
								   END as ttn_lok, ";

							break;
						default:
							break;
					}
				}

			}

			$query = "SELECT $additional_conditions `".INVOICE_TTN."`.*,DATE_FORMAT(`".INVOICE_TTN."`.`date`,'%d.%m.%Y')  AS `date`, DATE_FORMAT(`".INVOICE_TTN."`.`date_return`,'%d.%m.%Y') as date_return ,DATE_FORMAT(`date_shipment`,'%d.%m.%Y ')  AS `date_shipment` FROM `".INVOICE_TTN."` WHERE `invoice_id` IN ('".implode("','",$id_s)."')";
			$w = 1;

			if((int)$curSearch['id'] == 0 && $curSearch['invoice_num'] == ''){
				// если мы не используем поиск
				// правила выборки счетов по вкладкам
				if (isset($_GET['section'])){
					switch ((int)$_GET['section']){
						// Запрос ТТН
						case 5:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `number` = '0' AND shipment_status <> 1";
							$w++;
							break;
						// Готовые ТТН
						case 6:
//							$query .= ($w>0?' AND ':' WHERE ');
//							$query .= " `number` <> '0' AND shipment_status <> 1";
							$w++;
							break;

						case 7:
//							$query .= ($w>0?' AND ':' WHERE ');
//							$query .= "  shipment_status = 1";
							$w++;
							break;
						default:
							break;
					}
				}

			}
//			echo $query;
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->data[$this->depending['id'][$row['invoice_id']]]['ttn'][] = $row;
				}
			}

			// если мы не используем поиск
			if((int)$curSearch['id'] == 0 && $curSearch['invoice_num'] == ''){
				// если мы выбираем по ТТН
				if (isset($_GET['section']) && ((int)$_GET['section'] == 5 || (int)$_GET['section'] == 6)){
					foreach ($this->data as $key => $val){
						if(!isset($this->data[$key]['ttn']) || count($this->data[$key]['ttn']) == 0){
							// в этих разделах нас интересуют ттн, поэтому строки счетов без ттн удаляются
							unset($this->depending['id'][$val['id']]);
							unset($this->data[$key]);
						}
					}

					// сортируем массив, присваиваем новые ключи по порядку
					$i = 0;
					foreach ($this->data as $key => $val){
						$this->data[$i] = $val;
						if($i != $key ){
							unset($this->data[$key]);
						}
						$i++;
					}
				}
			}
		}

		/**
		 * update and save main discount
		 *
		 */
		protected function create_invoice_AJAX(){
			/*
				define("GENERATED_AGREEMENTS_TBL","os__generated_agreements"); // таблица созданных договоров
				define("GENERATED_SPECIFICATIONS_TBL","os__generated_specifications"); // таблица созданных спецификаций и строк в них
				define("OFFERTS_TBL","os__offerts"); // таблица созданных оферт
				define("OFFERTS_ROWS_TBL","os__offerts_rows"); // таблица строк в офертах
			 */
			//$message  = 'Тут будут обработаны данные<br>';
			//$message  .= 'и заведена строка в счетах';
			//$message .= $this->printArr($_POST);
			// $this->responseClass->addMessage('create_invoice PHP.');
			$options['width'] = 1200;
			$options['height'] = 500;
			

			if(!isset($_POST['doc']) || $_POST['doc'] == ''){
				$this->responseClass->addMessage('Не получен тип документа');return;
			}
			//if($_SESSION['access']['access'] != 1){
			//	$this->responseClass->addMessage('Данный модуль находится в режиме тестирования и временно не доступен.');return;
			//}

			switch ($_POST['doc']) {
				// спецификация
				case 'spec':
					//  сбор данных для заведения в базе строки запроса
					$data['agreement_id'] = $_POST['agreement_id'];
					$data['doc_type'] = $_POST['doc'];
					// номер спецификации к данному договору
					$data['doc_num'] = $_POST['specification_num'];
					$data['doc_id'] = $data['agreement_id'];



					// проверка на существования запроса по данному документу
					if($this->check_invoice($data['doc_type'],$data['doc_id'],$data['doc_num'])){
					 	$this->responseClass->addMessage('Для данного документа счёт уже запрошен.','error_message',1);
						if ($this->prod__check()){return;}
					}

					// получаем данные по спецификации
					$positions = $this->getSpecificationRows($data['agreement_id'], $data['doc_num']);
					$agr = $this->getAgreement($data['agreement_id']);

//					$message .= $this->printArr($positions);
//					$this->responseClass->addSimpleWindow($message);
//					return;

					// $message .= $this->printArr($agr);
//					$message .= $this->printArr($positions);
					// $this->responseClass->addSimpleWindow($message,'Создание счета',$options);
					// return

					$data['client_id'] = $agr[0]['client_id'];
					$data['positions_num'] = count($positions);
					$data['requisit_id'] = $agr[0]['client_requisit_id'];
					$data['price_in'] = $this->getPriceIn($positions);
					$data['price_out'] = $this->getPriceOut($positions);
					$data['conditions'] = $positions[0]['prepayment'];
					$this->responseClass->addMessage('Счёт запрошен','successful_message',1);

					break;
				// оферта
				case 'oferta':					
					//  сбор данных для заведения в базе строки запроса
					$data['agreement_id'] = 0;
					$data['doc_type'] = $_POST['doc'];
					// номер спецификации к данному договору
					$data['doc_num'] = 0;
					$data['doc_id'] = $_POST['oferta_id'];

					// проверка на существования запроса по данному документу
					if($this->check_invoice($data['doc_type'],$data['doc_id'],$data['doc_num'])){
						$this->responseClass->addMessage('Для данного документа номер уже запрошен.','error_message',1);
						if ($this->prod__check()){return;}
					}


					// получаем данные по спецификации
					$Oferta = $this->getOferta($data['doc_id']);
					$positions = $this->getOfertaRows($data['doc_id']);

					$data['conditions'] = $Oferta[0]['prepayment'];
//					 $message .= $this->printArr($Oferta);
//
//					$message .= $this->printArr($positions);
//					$this->responseClass->addSimpleWindow($message);
//					return;

					// $message .= $this->printArr($positions);
					$data['positions_num'] = count($positions);
					$data['client_id'] = $Oferta[0]['client_id'];
					$data['requisit_id'] = $Oferta[0]['client_requisit_id'];
					$data['price_in'] = $this->getPriceIn($positions);
//					$this->prod__window($this->printArr($positions));

					$data['price_out'] = $this->getPriceOut($positions);

					$this->responseClass->addMessage('Номер счёта запрошен', 'successful_message',1);
					break;
				
				default:
					$this->responseClass->addMessage('неизвестный тип документа');
					break;
			}

			// заводим строку счет
			$invoce_id = $this->createInoceRow($data);
			// заводим строки позиций к счёту
			$this->createPositionRows($invoce_id, $positions);


			// заводим строки позиций к документу

			// $this->responseClass->addSimpleWindow($message,'Создание счета',$options);
		}

		private function calc_price_width_discount($price_out, $discount){
			$num = $price_out / 100 * (100 + $discount);
			return round($num,2);
		}

		/**
		 * сохранение номера оферты
		 * @param $invoice_num
		 * @param $id
		 */
		private function save_specification_number($invoice_num,$date ,$id){


			$query = "UPDATE `" . OFFERTS_TBL . "` SET ";
			$query .= " `num`=?";
			$query .= ", `date_time`=?";
			$query .= " WHERE `id`=?";


			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('ssi',$invoice_num,$date ,$id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

		}

		/**
		 * создание поставщи ка
		 */
		protected function create_new_supplier_AJAX(){

			$query ="INSERT INTO `".SUPPLIERS_TBL."` SET";
			$query .= " `nickName`=?";
			$query .= ", `fullName`=?";
			$query .= ", `dop_info`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('sss',$_POST['nick_name'],$_POST['full_name'],$_POST['dop_info']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();




			$data = array(
				'supplier_id' => $this->mysqli->insert_id
			);

			$this->responseClass->response['data'] = $data;
		}


		/**
		 *	check invoice
		 *
		 * @param $doc_type
		 * @param $doc_id
		 * @param $doc_num
		 * @return bool
		 */
		private function check_invoice($doc_type, $doc_id, $doc_num){
			$query = "SELECT count(*) as count FROM `".INVOICE_TBL."` ";
			$query .= "WHERE `doc_type` = '".$doc_type."'";
			$query .= " AND `doc_id` = '".$doc_id."'";
			$query .= " AND `doc_num` = '".$doc_num."'";
			$query .= " AND `closed` <= '1'";

			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($row['count']>0) return true;
				}
			}
			return false;
		}

		/**
		 * create Position Rows
		 *
		 * @param $invoce_id
		 * @param $positions_data
		 */
		private function createPositionRows($invoce_id, $positions_data){
			foreach($positions_data as $data){
				$query = "INSERT INTO `".INVOICE_ROWS."` SET ";
			    // $query .= "`id` = '',";
			    // дата создания заявки
			    $query .= "`invoice_id` = '".$invoce_id."'";
			    // id автора
			    $query .= ", `name` = '".$data['name']."' ";
			    $query .= ", `quantity` = '".$data['quantity']."' ";
			    $query .= ", `price_in` = '".$data['price_in']."' ";
			    $query .= ", `price` = '".$data['price']."' ";
			    $query .= ", `summ` = '".$data['summ']."' ";
			    $query .= ", `discount` = '".$data['discount']."' ";
				$query .= ", `flag_ttn` = '0'";
				
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
			}
		}

		/**
		 * save supplier name
		 */
		protected function save_supplier_name_AJAX(){
			$query = "UPDATE `" . INVOICE_COSTS . "` SET ";
			$query .= " `supplier_name`=?";
			$query .= ", `supplier_id`=?";
			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('sii',$_POST['supplier_name'],$_POST['supplier_id'],$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

		protected  function edit_date_shipment_ttn_AJAX(){
			$query = "UPDATE `" . INVOICE_TTN . "` SET ";
			$query .= " `date_shipment`=?";
			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$date = date("Y-m-d",strtotime($_POST['date_shipment']));
			$stmt->bind_param('si', $date, $_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}
		/**
		 *	edit spf_return
		 *
		 */
		protected function edit_flag_spf_return_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_spf_return` = '".(int)$_POST['val']."'";

			if ($_POST['val'] == 0){
				$query .= ", `spf_return_date` = NULL";
			}else{
				$query .= ", `spf_return_date` = NOW()";
			}
			  	
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 *	edit flag_calc
		 *
		 */
		protected function edit_flag_calc_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_calc` = '".(int)$_POST['val']."'";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 *	edit flag_ice
		 *
		 */
		protected function edit_flag_ice_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_ice` = '".(int)$_POST['val']."'";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}
		/**
		 *  save costs summ
		 */
		protected function save_costs_from_invoice_AJAX(){
			$query = "UPDATE `" . INVOICE_TBL . "` SET ";
			$query .= " `costs`=?";

			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('di',$_POST['costs'],$_POST['id']) or die($this->mysqli->error);


			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}
		/**
		 *	edit flag_1c
		 *
		 */
		protected function edit_flag_1c_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_1c` = '".(int)$_POST['val']."'";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}
		/**
		 *	edit edit_flag_flag
		 *
		 */
		protected function edit_flag_flag_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_flag` = '".(int)$_POST['val']."'";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 * get Oferta rows
		 *
		 * @param $id
		 * @return array
		 * @param 		id
		 */
		private function getOfertaRows($id){
			$query = "SELECT * FROM `".OFFERTS_ROWS_TBL."` WHERE `oferta_id` = '".$id."'";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			
			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			return $data;
		}

		/**
		 * get Oferta
		 *
		 * @param $id
		 * @return array
		 * @param 		id
		 */
		private function getOferta($id){
			$query = "SELECT * FROM `".OFFERTS_TBL."` WHERE id = '".$id."'";
			// if($this->user_access != 1 || $this->user_access != 2){
			// 	$query .= "WHERE `manager_id` = '".$this->user_id."' ";
			// }
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
			return $data;
		}

		/**
		 * calc price in
		 *
		 * @param $arr
		 * @return int
		 */
		private function getPriceIn($arr){
			$price = 0;
			foreach($arr as $row){
				$price += $row['quantity']*$row['price_in'];
			}
			return $price;
		}
		/**
		 * calc price out
		 *
		 * @param $arr
		 * @return float|int
		 */
		private function getPriceOut($arr){
			$price = 0;
			foreach($arr as $row){
				$price += round($this->calc_price_width_discount($row['price'], $row['discount']),2) * $row['quantity'];
			}
			return $price;
		}

		/**
		 * get requisits name
		 *
		 * @param $requisit_id
		 * @return string
		 * @see 		requisits name
		 */
		private function getRequisitsName($requisit_id){
			include_once('client_class.php');
			$requesit = Client::get_requesit($this->mysqli, $requisit_id);
			if (isset($requesit['comp_full_name'])){
				return $requesit['comp_full_name'];
			}

			return 'метод получения названия реквизитов реквизиты не найдены ID ='.$requisit_id;
		}

		/**
		 * окно просмотра реквизитов
		 */
		protected function show_requesit_AJAX() {
			include_once('client_class.php');
			$this->responseClass->response['data'] = Client::get_requesit($this->mysqli, (int)$_POST['id']);
		}

		/**
		 * get client name
		 *
		 * @param $client_id
		 * @return string
		 */
		private function getCLientName($id){
			include_once('client_class.php');
			return Client::get_client_name($id);
		}

		/**
		 * insert invoice row
		 *
		 * @param $add_data
		 * @return mixed
		 */
		private function createInoceRow($add_data){
			$query = "INSERT INTO `".INVOICE_TBL."` SET ";
			    // $query .= "`id` = '',";
			    // дата создания заявки
			    $query .= "`invoice_query_date` = NOW(),";
			    // id автора
			    $query .= "`manager_id` = '".$this->user_id."', ";
			    $query .= "`manager_name` = '".$this->getAuthUserName()."',";
			    $query .= "`price_in` = '".$add_data['price_in']."',";
			    $query .= "`price_out` = '".$add_data['price_out']."',";
			    $query .= "`price_out_payment` = '0',";
				$query .= "`positions_num` = '".$add_data['positions_num']."',";
			    // номер счёта
			    // $query .= "`invoice_num` = '0000',";
			    // дата заведения бухом
			    $query .= "`invoice_create_date` = '',";
			    
			    $query .= "`client_id` = '".$add_data['client_id']."',";
			    // имя клиента
			    $query .= "`client_name` = '".$this->getCLientName($add_data['client_id'])."', ";
			    $query .= "`client_requisit_id` = '".$add_data['requisit_id']."',";
			    $query .= "`client_requisit_name` = '".$this->getRequisitsName($add_data['requisit_id'])."',";				
				// оплачено
				$query .= "`price_costs_all` = '0.00',";
				// условия первоначальной оплаты
				$query .= "`conditions` = '".$add_data['conditions']."',";

				$query .= "`agreement_id` = '".$add_data['agreement_id']."',";
				$query .= "`doc_type` = '".$add_data['doc_type']."',";
				$query .= "`doc_num` = '".$add_data['doc_num']."',";
				$query .= "`doc_id` = '".$add_data['doc_id']."'";
				
				$result = $this->mysqli->query($query) or die($this->mysqli->error);      

				return $this->mysqli->insert_id;       	                
		}


		/**
		 * get agreement rows
		 *
		 * @param $agreement_id
		 * @return array
		 */
		private function getAgreement($agreement_id){
			$query = "SELECT * FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `id` = '".$agreement_id."' ";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$arr = [];
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		/**
		 * get specification rows
		 *
		 *
		 * @param $agreement_id
		 * @param $specification_num
		 * @return array
		 */
		private function getSpecificationRows($agreement_id,$specification_num){
			$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE `agreement_id` = '".$agreement_id."' AND `specification_num` = '".$specification_num."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$spec_arr = [];
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$spec_arr[] = $row;
				}
			}

			return $spec_arr;
		}

		/**
		 * get user access
		 *
		 * @param $id
		 * @return int
		 */
		private function get_user_access_Database_Int($id){
			$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$int = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$int = (int)$row['access'];
					$this->user = $row;
				}
			}
			return $int;
		}
}




?>