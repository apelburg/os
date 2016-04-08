<?php
	/**
	* 
	*/
	class Invoice  extends aplStdAJAXMethod
	{
		
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
		 *	update and save main discount
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	08.04.2016 10:43:03
		 */
		protected function create_invoice_AJAX(){
			$message  = 'Тут будут обработаны данные<br>';
			$message  .= 'и заведена строка в счетах';
			$message .= $this->printArr($_POST);
			// $this->responseClass->addMessage('create_invoice PHP.');
			$options['width'] = 1200;
			$options['height'] = 500;
			$this->responseClass->addSimpleWindow($message,'Создание счета',$options);

			if(!isset($_POST['doc']) || $_POST['doc'] == ''){
				$this->responseClass->addMessage('Не получен тип документа');return;
			}

			switch ($_POST['doc']) {
				// спецификация
				case 'spec':
					$agreement_id = $_POST['agreement_id'];
					$spec_num = $_POST['specification_num'];

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