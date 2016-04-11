<?php
	/**
	* 
	*/
	class Invoice  extends aplStdAJAXMethod
	{
		// public $user_access = 0;
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
		 *	заведение строки счёта
		 *
		 *	@param 		$rows_data  - 
		 *	@param 		$client_id  - 	id - клиента
		 *	@param 		$query_num  - 	номер запроса
		 *	@param 		$doc_num    - 	номер документа
		 *	@param 		$doc_id		- 	id документа
		 *	@param 		$doc_type	- 	тип документа (спецификация или оферта)
		 *	@param 		$date_type	-	тип даты в документе - дата или рабочие дни
		 *	@param 		$shipping_date - дата отгрузки
		 *	@param 		$work_days	-	рабочие дни
		 *	@param  	$limit 		- 	дата сдачи
		 *	
		 *	@return  	
		 *	@see 		
		 *	@author  	Alexey Kapitonov
		 *	@version 	
		 */
		// создание заказа из запроса
        private function createInvoce($rows_data,$client_id,$query_num,$doc_num,$doc_id,$doc_type, $date_type, $shipping_date = '',$work_days = 0, $limit = '0000-00-00'){
            echo '<br><br><strong>$limit = </strong>'.$limit.'<br>'; 
            
            // подключаем класс для информации из калькулятора
        	
        	
            $user_id = $_SESSION['access']['user_id'];

            // убиваем пустые позиции
            foreach (json_decode($rows_data,true) as $key => $value) {
            	if(!empty($value)){
            		$positions_arr[] = $value;
            	}
            }
            	
            /////////////////////////////
            //  СОЗДАНИЕ СТРОКИ с информацией по группе товаров в спецификации -- START
            /////////////////////////////       

                // КОПИРУЕМ СТРОКУ ЗАКАЗА из таблицы запросов
                $query = "INSERT INTO `".CAB_BILL_AND_SPEC_TBL."` (`manager_id`, `client_id`, `snab_id`, `query_num` )
                    SELECT `manager_id`, `client_id`, `snab_id`, `query_num`
                    FROM `".RT_LIST."` 
                    WHERE  `query_num` = '".$query_num."';
                    ";

                    echo $query;
                // выполняем запрос
                $result = $this->mysqli->query($query) or die($this->mysqli->error);
                // получаем id нового заказа... он же номер
                $the_bill_id = $this->mysqli->insert_id; 
               
            /////////////////////////////
            //  СОЗДАНИЕ СТРОКИ с информацией по группе товаров в спецификации -- start
            /////////////////////////////

            //////////////////////////
            //	Запрашиваем информацию по спецификацииии или оферте-- start
            //////////////////////////
		    if($doc_type=='spec'){
                $query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE `agreement_id` = '".$doc_id."' AND `specification_num` = '".$doc_num."'";
				echo $query;
                $result = $this->mysqli->query($query) or die($this->mysqli->error);
				if($result->num_rows > 0){
					$row = $result->fetch_assoc();
					$prepayment = $row['prepayment'];
				}
			}
			if($doc_type=='oferta'){
			
			 $query = "SELECT * FROM `".OFFERTS_TBL."` WHERE `id` = '".$doc_id."'";
				echo $query;
                $result = $this->mysqli->query($query) or die($this->mysqli->error);
				if($result->num_rows > 0){
					$row = $result->fetch_assoc();
					$prepayment = $row['prepayment'];
				}
			}
			//////////////////////////
            //	Запрашиваем информацию по спец-ии или оферте -- end
            //////////////////////////
				
            ////////////////////////////////////
            //	Сохраняем данные о спецификации или оферте   -- start
            ////////////////////////////////////
				$query = "UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET ";
				$query .= " `specification_num` = '".(int)$doc_num."'";
				$query .= ", `create_time` = '".date('Y-m-d',time())."' ";
				$query .= ", `doc_num` = '".(int)$doc_num."'";
				$query .= ", `doc_type` = '".$doc_type."'";
				$query .= ", `date_type` = '".$date_type."'";
				$query .= ", `doc_id` = '".(int)$doc_id."'";
				$query .= ", `shipping_date` = '".$shipping_date."'"; // дата сдачи
				$query .= ", `work_days` = '".(int)$work_days."'"; // рабочие дни указываются в случае сроков по Р/Д
				$query .= ", `prepayment` = '".(int)$prepayment."'"; // % предоплаты для запуска заказа
				$query .= ", `shipping_date_limit` = '".$limit."'";
				$query .= " WHERE `id` = '".$the_bill_id."'";
				// выполняем запрос
				
                $result = $this->mysqli->query($query) or die($this->mysqli->error);
                ////////////////////
                // test query
                ////////////////////
    			// echo '<br><br>'.$query;
				// exit;
			////////////////////////////////////
            //	Сохраняем данные о спецификации или оферте   -- end
            ////////////////////////////////////

            // echo '<br>'.$order_num.'<br>';
            // перебираем принятые данные по позициям

            foreach ($positions_arr as $position) {
            	//////////////////////////
            	//	заведение позиций
            	//////////////////////////
	                $query = "INSERT INTO `".CAB_ORDER_MAIN."`  (`master_btn`,`type`,`art`,`art_id`,`name`,`number_rezerv`)
	                    SELECT `master_btn`,`type`,`art`,`art_id`,`name`,`number_rezerv`
	                    FROM `".RT_MAIN_ROWS."` 
	                    WHERE  `query_num` = '".$query_num."' 
	                    AND `id` = '".$position['pos_id']."';
	                ";

	                // выполняем запрос
	                $result = $this->mysqli->query($query) or die($this->mysqli->error);
	                // id новой позиции
	                $main_row_id = $this->mysqli->insert_id;
                	
	                // выбираем id строки расчёта
	                // КОПИРУЕМ СТРОКУ РАСЧЁТА (В ЗАКАЗЕ ОНА У НАС ДЛЯ КАЖДОГО ЗАКАЗА ТОЛЬКО 1)
	                $query = "INSERT INTO `" . CAB_ORDER_DOP_DATA . "`  (
	                    `row_id`,`expel`,`quantity`,`zapas`,`price_in`,`price_out`,`discount`,`tirage_json`,
	                    `print_z`,`shipping_time`,`shipping_date`,`no_cat_json`,`suppliers_name`,`suppliers_id`
	                    )
	                    SELECT `row_id`,`expel`,`quantity`,`zapas`,`price_in`,`price_out`,`discount`,`tirage_json`,
	                    `print_z`,`shipping_time`,`shipping_date`,`no_cat_json`,`suppliers_name`,`suppliers_id`
	                    FROM `".RT_DOP_DATA."` 
	                    WHERE  `id` = '".$position['row_id']."'
	                ";
	                $result = $this->mysqli->query($query) or die($this->mysqli->error);
	                
	                $dop_data_row_id = $this->mysqli->insert_id; // id нового расчёта... он же номер
                


                // правим row_id на полученный из созданной строки позиции
                $query = "UPDATE  `".CAB_ORDER_DOP_DATA."` 
                        SET  `row_id` =  '".$main_row_id."' 
                        WHERE  `id` ='".$dop_data_row_id."';";
                $result = $this->mysqli->query($query) or die($this->mysqli->error);
                
                // правим order_num на новый номер заказа
                $query = "UPDATE  `".CAB_ORDER_MAIN."` 
                        SET  `the_bill_id` =  '".$the_bill_id ."' 
                        WHERE  `id` ='".$main_row_id."';";
                $result = $this->mysqli->query($query) or die($this->mysqli->error);


                //////////////////////////////////////////////////////
                //    КОПИРУЕМ ДОП УСЛУГИ И УСЛУГИ ПЕЧАТИ -- start  //
                //////////////////////////////////////////////////////
                    // думаю в данном случае копировать не стоит,
                    // лучше сначала выбрать , преобразовать в PHP и вставить
                    // в противном случае при одновременном обращении нескольких менеджеров к данному скрипту
                    // данные о доп услугах для заказа могут быть потеряны
                    /*
                     данный вопрос решается в любом случае двумя запросами:
                     Вар. 1) копируем данные, замораживаем таблицу доп услуг и апдейтим родительский id
                     Вар. 2) выгружаем данные о доп услугах в PHP, и записывае в новую таблицу
                    */

                    
                    $query = "SELECT * FROM `".RT_DOP_USLUGI."` 
                        WHERE  `dop_row_id` = '".$position['row_id']."'";

                        // echo $position['row_id'].'<br><br><br><br>';
                    $arr_dop_uslugi = array();
                    $result = $this->mysqli->query($query) or die($this->mysqli->error);
                    
                    if($result->num_rows > 0){

                    	// echo $row.'<br><br><br>';
                        while($row = $result->fetch_assoc()){
                			 $query2 = "INSERT INTO `".CAB_DOP_USLUGI."` SET
						`dop_row_id` =  '".$dop_data_row_id."',
						`date_ready` = '0000-00-00',
						`date_send_out` = '0000-00-00',
						`uslugi_id` = '".$row['uslugi_id']."',
						`glob_type` = '".$row['glob_type']."',
						`type` = '".$row['type']."',
						`quantity` = '".$row['quantity']."',
						`price_in` = '".$row['price_in']."',
						`price_out` = '".$row['price_out']."',
						`for_how` = '".$row['for_how']."',
						`print_details_dop` = '".printCalculator::convert_print_details_to_dop_tech_info($row['print_details'])."',
						`tz` = '".$row['tz']."',				
						`performer` = '".$row['performer']."',
						`print_details` = '".$row['print_details']."';";
						// echo $query2.'<br><br>';exit;
						$this->mysqli->query($query2) or die($this->mysqli->error);	
                        }
                    	
                    }
                    
                //////////////////////////////////////////////////////
                //    КОПИРУЕМ ДОП УСЛУГИ И УСЛУГИ ПЕЧАТИ -- end    //
                //////////////////////////////////////////////////////
                

            }
        } 

		/**
		 *	get data rows
		 *
		 *	@return  	data rows
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 10:48:00
		 */
		private function get_data(){
			$query = "SELECT * FROM `".CAB_BILL_AND_SPEC_TBL."`";
			if($this->user_access != 1 || $this->user_access != 2){
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
					$agreement_id = $_POST['agreement_id'];
					$spec_num = $_POST['specification_num'];

					// получаем данные по спецификации
					$ret = $this->getSpecificationRows($agreement_id, $spec_num);
					$message .= $this->printArr($ret);
					break;
				// оферта
				case 'oferta':
					$oferta_id = $_POST['oferta_id'];
					# code...
					break;
				
				default:
					$this->responseClass->addMessage('неизвестный тип документа');
					break;
			}



			$this->responseClass->addSimpleWindow($message,'Создание счета',$options);

		}

		/**
		 *	get agreement rows
		 *
		 *	@param 		agreement_id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11.04.2016 11:55:13
		 */
		private function getAgreement($agreement_id){
			$query = "SELECT * FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `agreement_id` ";
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
			$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$int = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$int = (int)$row['access'];
				}
			}
			return $int;
		}
	}
?>