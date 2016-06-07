<?php
/**
 * Class accounting
 *
 *
 * @author  	Alexey Kapitonov
 * @version 	06.06.2016 10:30
 */
	class Accounting  extends aplStdAJAXMethod
	{
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









		/**
		 * главный запрос данных
		 */
		protected function get_data_AJAX(){
			$response = array(
				'access' => $this->user_access,
				'tabs' => $this->get_tabs(),
				'data' => $this->get_data()
				);
			$this->responseClass->response['data'] = $response;
		}

		/**
		 * получаем данные из таблицы пенсиий
		 */
		protected function get_pension_tbl_data_AJAX(){
			$query = "SELECT *,  DATE_FORMAT(`".ACCOUNTING_PENSION."`.`date`,'%d.%m.%Y') as date FROM `".ACCOUNTING_PENSION."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$rows = array();
			if($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
			}
			$this->responseClass->response['data'] = $rows;
		}

		/**
		 * получаем данные из таблицы расчёта зп менам по конечникам
		 */
		protected function get_zp_kon_data_AJAX(){
			$query = "SELECT * FROM `".ACCOUNTING_ZP_KON."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$rows = array();
			if($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
			}
			$this->responseClass->response['data'] = $rows;
		}

		protected function delete_pension_row_AJAX(){
			$query = "DELETE FROM `".ACCOUNTING_PENSION."` WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('i',$_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

		/**
		 * сохранение данных из таблицы пенсий
		 */
		protected function savePensionData_AJAX(){
			switch ($_POST['key']){
				case 'date':
					$type = 's';
					break;
				default:
					$type = 'd';
					break;
			}

			$query = "UPDATE `".ACCOUNTING_PENSION."` SET ";
			$query .= " `".addslashes($_POST['key'])."`=?";
			$type .= 'i';
			$query .= " WHERE `id` =?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param($type,$_POST['val'], $_POST['id']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

		/**
		 * создание строки в таблице пенсий
		 */
		protected function create_pension_row_AJAX(){
			$query = "INSERT INTO `".ACCOUNTING_PENSION."` SET ";
			$query .= "`date` = '00.00.0000'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);


			$this->responseClass->response['data'] = array('id'=>$this->mysqli->insert_id);
		}

		/**
		 * получаем данные из таблицы расчёта зп менам по рекламщикам
		 */
		protected function get_zp_rek_data_AJAX(){
			$query = "SELECT * FROM `".ACCOUNTING_ZP_REK."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$rows = array();
			if($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
			}
			$this->responseClass->addMessage('зп для менов рекл','system_message',array('time'=>100));

			$this->responseClass->addMessage($this->printArr($rows),'system_message',array('time'=>7000));

			$this->responseClass->response['data'] = $rows;
		}


		/**
		 * данные по настройкам
		 */
		protected function get_options_AJAX(){
			$response = array(
				'access' => $this->user_access,
				'data' => $this->get_data()
			);
			echo json_encode($response);
			exit;
		}

		/**
		 * получаем верхнее меню
		 */
		private function get_tabs(){

			$tabs[] = array(
				'index'=> 0,
				'name'=>'Расчёт',
				'class'=>'calculation'
			);
			if($this->user_access != 5){
				$tabs[] = array(
					'index'=> 1,
					'name'=>'Настройки',
					'class'=>'options'
				);
			}
			return $tabs;
		}


		/**
		 * get data rows
		 *
		 */
		private function get_data(){
			# выбираем все счета, которые пора переводить в закрытые
			$query = "SELECT *";

			$query .= " ,MONTH(invoice_create_date) as month ";
			$query .= " ,YEAR (invoice_create_date) as year ";
			$query .= " ,DATE_FORMAT(`".INVOICE_TBL."`.`invoice_create_date`,'%d.%m.%Y') as invoice_create_date_formate";
			$query .= " FROM `".INVOICE_TBL."` ";

			# если заказ ещё не закрыт
//			$query .= " WHERE `closed` = 1 ";
			$query .= " WHERE id > 0 ";

			# выборка по месяцу
			if(!isset($_GET['month'])){
				$query .= " AND MONTH(invoice_create_date) = '5' ";
			}else{
				$query .= " AND MONTH(invoice_create_date) = '".(int)$_GET['month']."' ";
			}

			# выборка по менеджеру
			if(isset($_GET['manager_id'])){
				$query .= " AND `manager_id` = '".(int)$_GET['manager_id']."' ";
			}elseif ($this->user_access == 5){
				$query .= " AND `manager_id` = '".$this->user_access."' ";
			}

			# выборка по менеджеру
			if(isset($_GET['year'])){
				$query .= " AND YEAR (invoice_create_date) = '".(int)$_GET['year']."' ";
			}else{
				$query .= " AND YEAR (invoice_create_date) = '".date('Y')."' ";
			}


			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$rows = array();
			if($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
			}
			return $rows;
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