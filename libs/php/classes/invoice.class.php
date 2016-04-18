<?php
	/**
	* 
	*/
	class Invoice  extends aplStdAJAXMethod
	{
		public $user = array(); // authorised user info
		function __construct()
		{
			$this->db();

			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
		
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## the data GET --- on debag time !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);		
			}
		}

		/**
		 *	return data
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version	11.04.2016 9:29:45 	
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
         *	get user full name
         *
         *	@author  	Alexey Kapitonov
         *	@version 	11.04.2016 15:20:48
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
		 *	get data rows
		 *
		 *	@return  	data rows
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 10:48:00
		 */
		private function get_data(){
			//  получаем информацию по строкам
			$query = "SELECT * FROM `".INVOICE_TBL."`";
			if($this->user_access != 1 && $this->user_access != 2){
				$query .= "WHERE `manager_id` = '".$this->user_id."' ";
			}
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$this->data = array();
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
			$this->get_ttn_rows($data_id_s);


			return $this->data;
		}

		/**
		 *	get ttn from id
		 *
		 *	@param 		invoice id
		 *	@return  	data
		 *	@author  	Alexey Kapitonov
		 *	@version 	15.04.2016 16:02:26
		 */
		protected function get_ttn_AJAX(){
			$query = "SELECT * FROM `".INVOICE_ROWS."` WHERE `invoice_id` = '".(int)$_POST['id']."'";
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
		 *	get ttn rows
		 *
		 *	@param 		get_ttn_rows - array, id
		 *	@author  	Alexey Kapitonov
		 *	@version 	13.04.2016 15:52:37
		 */
		private function get_ttn_rows($id_s){
			$query = "SELECT * FROM `".INVOICE_TTN."` WHERE `invoice_id` IN ('".implode("','",$id_s)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$data = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->data[$this->depending['id'][$row['invoice_id']]]['ttn'][] = $row;
				}
			}
		}

		/**
		 *	update and save main discount
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	08.04.2016 10:43:03
		 */
		/*
			define("GENERATED_AGREEMENTS_TBL","os__generated_agreements"); // таблица созданных договоров
			define("GENERATED_SPECIFICATIONS_TBL","os__generated_specifications"); // таблица созданных спецификаций и строк в них
			define("OFFERTS_TBL","os__offerts"); // таблица созданных оферт
			define("OFFERTS_ROWS_TBL","os__offerts_rows"); // таблица строк в офертах
		 */
		
		protected function create_invoice_AJAX(){
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
					if($this->check_invoice($data['doc_type'],$data['doc_id'],$data['doc_num'])){
						$this->responseClass->addMessage('Дла данного документа счёт уже запрошен.');
						return;
					}
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
		 *	@param 		doc_type
		 *	@param 		doc_id
		 *	@param 		doc_num
		 *	@return  	true/false
		 *	@author  	Alexey Kapitonov
		 *	@version 	13.04.2016 15:05:41
		 */		
		private function check_invoice($doc_type, $doc_id, $doc_num){
			$query = "SELECT count(*) as count FROM `".INVOICE_TBL."` ";
			$query .= "WHERE `doc_type` = '".$doc_type."'";
			$query .= " AND `doc_id` = '".$doc_id."'";
			$query .= " AND `doc_num` = '".$doc_num."'";

			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$count = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$count = $row['count'];
				}
			}
			// echo $count;
			if($count>0){
				return true;
			}
			return false;
		}

		/**
		 *	create Position Rows
		 *
		 *	@param 		invoce_id 
		 *	@return  	$data
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:59:33
		 */
		private function createPositionRows($invoce_id, $positions_data){
			foreach($positions_data as $data){
				$query = "INSERT INTO `".INVOICE_ROWS."` SET ";
			    // $query .= "`id` = '',";
			    // дата создания заявки
			    $query .= "`invoice_id` = '".$invoce_id."',";
			    // id автора
			    $query .= "`name` = '".$data['name']."', ";
			    $query .= "`quantity` = '".$data['quantity']."', ";
			    $query .= "`price_in` = '".$data['price_in']."', ";
			    $query .= "`price` = '".$data['price']."', ";
			    $query .= "`summ` = '".$data['summ']."', ";
			    $query .= "`discount` = '".$data['discount']."', ";
				$query .= "`flag_ttn` = '0'";
				
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
			}
		}
		/**
		 *	edit spf_return
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	13.04.2016 16:49:25
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
		 *	@author  	Alexey Kapitonov
		 *	@version 	12.04.2016 17:43:10
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
		 *	@author  	Alexey Kapitonov
		 *	@version 	12.04.2016 17:43:10
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
		 *	@author  	Alexey Kapitonov
		 *	@version 	12.04.2016 17:43:10
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
		 *	@author  	Alexey Kapitonov
		 *	@version 	12.04.2016 17:43:10
		 */
		protected function edit_flag_flag_AJAX(){
			$query = "UPDATE `".INVOICE_TBL."` SET ";
			$query .= "`flag_flag` = '".(int)$_POST['val']."'";
			  	
			$query .= " WHERE `id` = '".(int)$_POST['id']."'";				
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		 *	get Oferta rows
		 *
		 *	@param 		id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:37:10
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
		 *	get Oferta 
		 *
		 *	@param 		id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:37:10
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
		 *	calc price in
		 *
		 *	@param 		docement rows array()
		 *	@return  	number
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:18:25
		 */
		private function getPriceIn($arr){
			$price = 0;
			foreach($arr as $row){
				$price += $row['quantity']*$row['price_in'];
			}
			return $price;
		}
		/**
		 *	calc price out
		 *
		 *	@param 		docement rows array()
		 *	@return  	number
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:18:33
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
		 *	get requisits name
		 *
		 *	@param 		requisit_id
		 *	@return  	str
		 *	@see 		requisits name
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:16:57
		 */
		private function getRequisitsName($requisit_id){
			return 'метод получения названия реквизитов';
		}
		/**
		 *	get client name
		 *
		 *	@param 		client_id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:17:25
		 */
		private function getCLientName($client_id){
			return 'метод получения названия клиента';
		}

		/**
		 *	insert invoice row
		 *
		 *	@param 		add_data - информация для добавления
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 15:14:53
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
		 *	get agreement rows
		 *
		 *	@param 		agreement_id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 11:55:13
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
		 *	get specification rows
		 *
		 *	@param 		agreement_id
		 *	@param 		specification_num
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 11:50:07
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
		 *	get user acces
		 *
		 *	@param 		user_id
		 *	@return  	user acces - number
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:38 16.03.2016
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