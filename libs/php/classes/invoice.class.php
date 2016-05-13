<?php

/**
 * Class Invoice
 *
 *
 * @author  	Alexey Kapitonov
 * @version 	22.04.2016 12:03:08
 */
	class Invoice  extends aplStdAJAXMethod
	{	
		protected $user_access = 0; 	// user right (int)
		protected $user_id = 0;			// user id with base
		public $user = array(); 		// authorised user info
		
		function __construct()
		{	
			// connectin to database
			$this->db();

			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;
			// geting rights
			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
			
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
			// $this->responseClass->addSimpleWindow($query.'<br>'.$this->printArr($_POST),'отладка');		
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 * invoice_autocomlete
		 */
		protected function shearch_invoice_autocomlete_AJAX(){

			$query="SELECT * FROM `".INVOICE_TBL."`  WHERE `invoice_num` LIKE ?";
//			$query="SELECT * FROM `".CLIENTS_TBL."`  WHERE `company` LIKE ?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$search = '%'.$_POST['search'].'%';
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
		 * create new ttn
		 *
		 */
		protected function create_new_ttn_AJAX(){
			$message  = '<b>Method:</b> '.__METHOD__.'<br>';
			$message .= $this->printArr($_POST);
			// $this->responseClass->addMessage($message,'system_message');
			
			$query = "INSERT INTO `".INVOICE_TTN."` SET ";
			    // $query .= "`id` = '',";
			    // дата создания заявки
			    $query .= "`date` = NOW()";
			    $query .= ",`position_id` = '".$_POST['positions']."'";
			    $query .= ",`positions_num` = '".$_POST['position_numbers']."'";
			    $query .= ",`delivery` = '".$_POST['delivery']."'";
			    $query .= ",`invoice_id` = '".$_POST['invoise_id']."'";

			$message .= '<br>'.$query;
			

			// $query = "SELECT * FROM `".INVOICE_TTN."` WHERE `invoice_id` IN ('".implode("','",$id_s)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			$insert_id = $this->mysqli->insert_id;				
			$query = "UPDATE `".INVOICE_ROWS."` SET ";
			$query .= "`ttn_id` = '".$insert_id ."'";
			  	
			$query .= " WHERE `id` IN (".$_POST['positions'].")";		
			// $this->responseClass->addSimpleWindow($message.'<br>'.$_POST['positions'],'Создание TTN');		
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			// сборка возвращаемого объекта для апдейта строки
			$data = array(
				'id' => $insert_id,
				'date'=>date("d.m.Y"),
				'position_id' => $_POST['positions'],
			    'positions_num' => $_POST['position_numbers'],
			    'delivery' => $_POST['delivery'],
			    'invoice_id' => $_POST['invoise_id'],
				);

			$this->responseClass->response['data'] = $data; 

		}
		/**
		 * insert ttn number
		 *
		 */
		protected function update_ttn_number_AJAX(){
			$query = "UPDATE `".INVOICE_TTN."` SET ";
			$query .= "`number` = '".(int)$_POST['val'] ."'";
			  	
			$query .= " WHERE `id` = '".$_POST['id']."'";		
			// $this->responseClass->addSimpleWindow($query.'<br>'.$this->printArr($_POST),'отладка');		
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 * return assigned ttn
		 *
		 */
		protected function ttn_was_returned_AJAX(){
			$query = "UPDATE `".INVOICE_TTN."` SET ";
			$query .= "`return` = '".(int)$_POST['val'] ."'";
			$query .= ",`date_return` = NOW()";
			$query .= ",`buch_id` = '".$this->user_id."'";
			$query .= ",`buch_name` = '".$this->getAuthUserName()."'";
			  	
			$query .= " WHERE `id` = '".$_POST['id']."'";		
			// $this->responseClass->addSimpleWindow($query.'<br>'.$this->printArr($_POST),'отладка');		
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
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
         * save invoice number	
         *
         */
        protected function confirm_create_bill_AJAX(){
        	$i = 0;
        	$query = "UPDATE `".INVOICE_TBL."` SET ";
        	if(isset($_POST['date'])){
				$query .= (($i>0)?',':'')." `invoice_create_date` = '".date('Y-m-d',strtotime($_POST['date']))."'";$i++;
        	}
			if(isset($_POST['number'])){
				$query .= (($i>0)?',':'')." `invoice_num` = '".$_POST['number']."'";$i++;
			}

			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
			if ($i>0){
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
			}else{
				$this->responseClass->addMessage('Вы не указали данные для сохранения');
			}
        }

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
			$query = "SELECT *,DATE_FORMAT(`".INVOICE_TBL."`.`invoice_create_date`,'%d.%m.%Y') as invoice_create_date FROM `".INVOICE_TBL."`";
			// $query = "  SORT BY `id` DESC";
			if($this->user_access != 1 && $this->user_access != 2){
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
				// если мы не используем поиск
				// правила выборки счетов по вкладкам
				if (isset($_GET['section'])){
					switch ((int)$_GET['section']){
						// Запрос
						case 1:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " (`invoice_num` = '0' OR `invoice_create_date` = '0000-00-00')";
							$w++;

							break;
						// Готовые
						case 2:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `invoice_num` <> '0' ";
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
							$query .= " `price_out_payment` >=  `price_out` ";
							$w++;
							break;
						// Запрос ТТН
						case 5:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `ttn_query` >=  0 ";
							$w++;
							break;
						// Готовые ТТН
						case 6:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `ttn_build` >=  0 ";
							$w++;
							break;
						// Част. отгрузка
						case 7:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `shipped` <>  `need_shipping` ";
							$w++;
							break;
						// Отгрузка
						case 8:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `shipped` >  0 ";
							$w++;
							break;
						// Закрытые
						case 9:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `shipped_date` > (NOW() - interval 10 day) AND `flag_calc` > 0 ";
							$w++;
							break;
						// все остальные
						default:
							break;
					}
				}

			}

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
			return $this->data;
		}

		/**
		 * get ttn from id
		 *
		 */
		protected function get_ttn_AJAX(){
			$query = "SELECT * FROM `".INVOICE_ROWS."` WHERE `invoice_id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
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
			// $this->responseClass->addSimpleWindow($this->printArr($data),'Создание TTN');
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
			$query .= ", `date` = NOW()";
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
				'del'=>0,
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
			$query .= ", `date` = NOW()";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('iis',$parent_id,$this->user_id,$userName) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			return $this->mysqli->insert_id;
		}

//

		/**
		/**
		 *	save price and percent in costs payment
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
		}
		protected function delete_payment_AJAX(){
			$query = "DELETE FROM `" . INVOICE_PP . "`";
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->responseClass->response['response'] = "noClose";
			$this->responseClass->addMessage('Запись была удалена.','successful_message');
		}

		/**
		 * get pp from invoice id
		 */
		protected function get_payment_AJAX(){
			$query = "SELECT *, DATE_FORMAT(`create`,'%d.%m.%Y %H:%i')  AS `create`, DATE_FORMAT(`date`,'%d.%m.%Y')  AS `date` FROM `".INVOICE_PP."` WHERE `invoice_id` = '".(int)$_POST['id']."'";
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

		protected function save_costs_payment_date_AJAX(){
			$query = "UPDATE `" . INVOICE_COSTS_PAY . "` SET ";
			$query .= " `date`=?";

			$query .= " WHERE `id`=?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$date = date("Y-m-d",strtotime($_POST['date']));

			$stmt->bind_param('si',$date,$_POST['id']) or die($this->mysqli->error);


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
		 * получаем данные по расходам
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
			$query .= "LEFT JOIN 
			";
			$query .= " ".INVOICE_COSTS_PAY." ON ".INVOICE_COSTS_PAY.".parent_id = ".INVOICE_COSTS.".id 
			";
			$query .= " WHERE ".INVOICE_COSTS.".invoice_id = '".(int)$_POST['id']."'";

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
		 * get ttn rows
		 *
		 * @param $id_s
		 */
		private function get_ttn_rows($id_s,$curSearch = array('invoice_num'=>'','id'=>0)){
			// if(count)
			if(count($id_s) == 0){
				return;
			}
			$query = "SELECT *,DATE_FORMAT(`".INVOICE_TTN."`.`date`,'%d.%m.%Y')  AS `date` FROM `".INVOICE_TTN."` WHERE `invoice_id` IN ('".implode("','",$id_s)."')";
			$w = 1;

			if((int)$curSearch['id'] == 0 && $curSearch['invoice_num'] == ''){
				// если мы не используем поиск
				// правила выборки счетов по вкладкам
				if (isset($_GET['section'])){
					switch ((int)$_GET['section']){
						// Запрос ТТН
						case 5:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `number` = '' ";
							$w++;
							break;
						// Готовые ТТН
						case 6:
							$query .= ($w>0?' AND ':' WHERE ');
							$query .= " `number` <> '' ";
							$w++;
							break;
						default:
							break;
					}
				}

			}





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
			$message  = 'Тут будут обработаны данные<br>';
			$message  .= 'и заведена строка в счетах';
			$message .= $this->printArr($_POST);
			// $this->responseClass->addMessage('create_invoice PHP.');
			$options['width'] = 1200;
			$options['height'] = 500;
			

			if(!isset($_POST['doc']) || $_POST['doc'] == ''){
				$this->responseClass->addMessage('Не получен тип документа');return;
			}

			switch ($_POST['doc']) {
				// спецификация
				case 'spec':
					//  сбор данных для заведения в базе строки запроса
					$data['agreement_id'] = $_POST['agreement_id'];
					$data['doc_type'] = $_POST['doc'];
					// номер спецификации к данному договору
					$data['doc_num'] = $_POST['specification_num'];
					$data['doc_id'] = 0;
					// проверка на существования запроса по данному документу
					// if($this->check_invoice($data['doc_type'],$data['doc_id'],$data['doc_num'])){
					// 	$this->responseClass->addMessage('Дла данного документа счёт уже запрошен.');
					// 	return;
					// }
					// получаем данные по спецификации
					$positions = $this->getSpecificationRows($data['agreement_id'], $data['doc_num']);
					$agr = $this->getAgreement($data['agreement_id']);
					
					// $message .= $this->printArr($agr);
					$message .= $this->printArr($positions);
					// $this->responseClass->addSimpleWindow($message,'Создание счета',$options);
					// return
					$data['client_id'] = $agr[0]['client_id'];
					$data['requisit_id'] = $agr[0]['client_requisit_id'];
					$data['price_in'] = $this->getPriceIn($positions);
					$data['price_out'] = $this->getPriceOut($positions);
					$this->responseClass->addMessage('Счёт запрошен','successful_message');
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
						$this->responseClass->addMessage('Дла данного документа номер уже запрошен.');
						return;
					}


					// получаем данные по спецификации
					$Oferta = $this->getOferta($data['doc_id']);
					$positions = $this->getOfertaRows($data['doc_id']);
					
					
					// $message .= $this->printArr($Oferta);
					// $message .= $this->printArr($positions);

					$data['client_id'] = $Oferta[0]['client_id'];
					$data['requisit_id'] = $Oferta[0]['client_requisit_id'];
					$data['price_in'] = $this->getPriceIn($positions);
					$data['price_out'] = $this->getPriceOut($positions);

					$this->responseClass->addMessage('Номер счёта запрошен','successful_message');
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
			$num = ($price_out / 100) * (100 + $discount);
			return $num;
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
		 *	edit spf_return
		 *
		 */
		protected function edit_flag_spf_return_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_spf_return` = '".(int)$_POST['val']."'";
			  	
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
			$query = "SELECT * FROM `".OFFERTS_TBL."`";
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
				// $price += $row['quantity']*$row['price'];
				$price += $this->calc_price_width_discount($row['summ'], $row['discount']);
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
			return 'метод получения названия реквизитов';
		}

		/**
		 * get client name
		 *
		 * @param $client_id
		 * @return string
		 */
		private function getCLientName($client_id){
			return 'метод получения названия клиента';
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
				// $query .= "`status` = '',";

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