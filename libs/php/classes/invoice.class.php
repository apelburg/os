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
			$query = "SELECT * FROM `".INVOICE_TBL."`";
			if($this->user_access != 1 && $this->user_access != 2){
				$query .= "WHERE `manager_id` = '".$this->user_id."' ";
			}
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

					break;
				// оферта
				case 'oferta':					
					//  сбор данных для заведения в базе строки запроса
					$data['agreement_id'] = 0;
					$data['doc_type'] = $_POST['doc'];
					// номер спецификации к данному договору
					$data['doc_num'] = 0;
					$data['doc_id'] = $_POST['oferta_id'];
					// получаем данные по спецификации
					$Oferta = $this->getOferta($data['doc_id']);
					$positions = $this->getOfertaRows($data['doc_id']);
					
					
					// $message .= $this->printArr($Oferta);
					// $message .= $this->printArr($positions);

					$data['client_id'] = $Oferta[0]['client_id'];
					$data['requisit_id'] = $Oferta[0]['client_requisit_id'];
					$data['price_in'] = $this->getPriceIn($positions);
					$data['price_out'] = $this->getPriceOut($positions);

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

			$this->responseClass->addSimpleWindow($message,'Создание счета',$options);
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
				$price += $row['quantity']*$row['price'];
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