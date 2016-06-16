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
		public 	$user_access = 0; 		// user right (int)
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
		/////////////////////////
		//	Учёт
		/////////////////////////

		/**
		 * сохранение бонуса
		 */
		protected function save_accruals_val_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_ACCRUALS,$_POST['id'],$_POST['key'],$_POST['val']);
		}

		/**
		 * меню со списком менеджеров
		 */
		protected function get_managers_tabs_AJAX(){
			$query = "SELECT id AS `index` ,last_name ";
			$query .= ", CONCAT(last_name,
            ' ',
            CASE
                WHEN name IS NULL THEN ''
                ELSE CONCAT(SUBSTRING(name, 1, 1), '.')
            	END) AS 'name'";
			$query .= " FROM `".MANAGERS_TBL."` WHERE status = '1' AND access = 5";

			if($this->user_access == 5){
				$query .= " AND id = '$this->user_id'";
			}


			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$managers = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$managers[] = $row;
				}
			}


			$this->responseClass->response['data'] = $managers;
		}

		# создание строки расчета
		protected function create_new_accruals_calc_AJAX(){
			$query = "INSERT INTO `".ACCOUNTING_ACCRUALS."` SET ";
			// $query .= "`id` = '',";
			// дата создания заявки


			$query = "INSERT INTO `".ACCOUNTING_ACCRUALS."` SET ";

			$query .= " `".$_POST['key']."`=? ";

			$query .= ", `manager_id`=? ";
			$query .= ", `year`=? ";
			$query .= ", `month`=? ";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('siii',$_POST['val'],$_GET['manager_id'],$_GET['year'],$_GET['month_number']) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);
		}

		/**
		 * главный запрос данных
		 */
		protected function get_data_AJAX(){
			$this->responseClass->response['data']['access'] = $this->user_access;
			// запрос строк закрытых за месяц счетов
			if (!isset($_GET['manager_id']) || !isset($_GET['year'])|| !isset($_GET['month_number'])){
				return;
			}
			$this->responseClass->response['data']['bill_closed'] = $this->get_data_bill_closed($_GET['manager_id'],$_GET['year'],$_GET['month_number']);
			// запрос рассчитанных начислений
			$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);
		}

		/**
		 * запрос расчёта ЗП
		 *
		 * @param $manager_id
		 * @param $year
		 * @param $month
		 * @return array
		 */
		private function get_data_accruals($manager_id,$year,$month){
			$query = "SELECT t.id, t.manager_id, tb.i ";
			$query .= ",
				CASE tb.i
					WHEN 1 THEN t.salary
					WHEN 2 THEN t.premium
					WHEN 3 THEN t.pension
					WHEN 4 THEN t.bonus
				END AS money,
				CASE tb.i
					WHEN 1 THEN t.salary_r_fl
					WHEN 2 THEN t.premium_r_fl
					WHEN 3 THEN t.pension_r_fl
				END AS flag_r,
				CASE tb.i
					WHEN 1 THEN t.salary_r
					WHEN 2 THEN t.premium_r
					WHEN 3 THEN t.pension_r        
				END AS r
				FROM
					`".ACCOUNTING_ACCRUALS."` AS t,
					(SELECT 1 i UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) tb";
				
			$query .= ' WHERE t.manager_id=?';
			$query .= ' AND t.year=?';
			$query .= ' AND t.month=?';

			// echo $query;
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('iss',$manager_id,$year,$month) or die($this->mysqli->error);
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
		 * вычисляет строки закрытых за месяц счетов
		 * @return array
		 */
		private function get_data_bill_closed($manager_id,$year,$month){
			$query = "SELECT *";
			$query .= " , DATE_FORMAT(`".INVOICE_TBL."`.`closed_date`,'%d.%m.%Y') as closed_date ";
			$query .= ", RIGHT(CONCAT('00000000' , (invoice_num)),6) as invoice_num";
			$query .= " , (price_out_payment - costs) as profit";
			$query .= " ,CASE
                WHEN price_out_payment = 0 THEN '0.00'
                ELSE ROUND(((price_out_payment - costs) / price_out_payment * 100),2)
            END AS 'pr'";
			$query .= " FROM `".INVOICE_TBL."`";

			$query .= " WHERE `manager_id`=?";
			$query .= " AND YEAR(closed_date)=?";
			$query .= " AND MONTH(closed_date)=?";

//			echo $query;
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('iss',$manager_id,$year,$month) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

			$data_bill_closed = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data_bill_closed[] = $row;
				}
			}
			return $data_bill_closed;
		}

		/////////////////////////
		//	Настройки
		/////////////////////////


		/**
		 * получаем данные из таблицы пенсиий
		 */
		protected function get_pension_tbl_data_AJAX(){
			$this->responseClass->response['data'] = $this->get_all_tbl(ACCOUNTING_PENSION);
		}

		/**
		 * получаем данные из таблицы расчёта зп менам по конечникам
		 */
		protected function get_zp_kon_data_AJAX(){
			$this->responseClass->response['data'] = $this->get_all_tbl(ACCOUNTING_ZP_KON, array(), array('name'=>'profit_start','type'=>"ASC"));
		}

		/**
		 * получаем данные из таблицы расчёта зп менам по рекламщикам
		 */
		protected function get_zp_rek_data_AJAX(){
			$this->responseClass->response['data'] = $this->get_all_tbl(ACCOUNTING_ZP_REK, array(), array('name'=>'profit_start','type'=>"ASC"));
		}

		/**
		 * удаленгие строки из таблицы пенсий
		 */
		protected function delete_pension_row_AJAX(){
			$this->delete_row_from_table(ACCOUNTING_PENSION,$_POST['id']);
		}
		/**
		 * удаление строки из таблицы рекламщкики
		 */
		protected function delete_zp_men_rek_row_AJAX(){
			$this->delete_row_from_table(ACCOUNTING_ZP_REK,$_POST['id']);
		}
		/**
		 * удаление строки из таблицы конечники
		 */
		protected function delete_zp_men_kon_row_AJAX(){
			$this->delete_row_from_table(ACCOUNTING_ZP_KON,$_POST['id']);
		}

		/**
		 * выбор активной строки для рассчёта пенсий в таблице пенсий
		 */
		protected function check_other_pension_row_AJAX(){
			// выбираем новую строку
			$this->update_one_val_in_one_row(ACCOUNTING_PENSION,$_POST['new_id'],'checked','1');
			// снимаем выбор со старой строки
			$this->update_one_val_in_one_row(ACCOUNTING_PENSION,$_POST['prev_id'],'checked','0');
		}

		/**
		 * сохранение данных из таблицы пенсий
		 */
		protected function savePensionData_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_PENSION,$_POST['id'],$_POST['key'],date('Y-m-d',strtotime($_POST['val'])));
		}

		/**
		 * сохранение данных из таблицы рекламщики
		 */
		protected function saveRecData_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_ZP_REK,$_POST['id'],$_POST['key'],$_POST['val']);
		}
		/**
		 * сохранение данных из таблицы конечники
		 */
		protected function saveKonData_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_ZP_KON,$_POST['id'],$_POST['key'],$_POST['val']);
		}

		/**
		 * создание строки в таблице пенсий
		 */
		protected function create_pension_row_AJAX(){
			$this->insert_empty_row(ACCOUNTING_PENSION,array('date'=>'00.00.0000'));
		}
		/**
		 * создание строки в таблице рекламщики
		 */
		protected function create_men_zp_rec_row_AJAX(){
			$this->insert_empty_row(ACCOUNTING_ZP_REK,array('date'=>'00.00.0000'));
		}

		/**
		 * создание строки в таблице конечники
		 */
		protected function create_men_zp_kon_row_AJAX(){
			$this->insert_empty_row(ACCOUNTING_ZP_KON,array('date'=>'00.00.0000'));
		}


		/**
		 * удаляет строку из таблицы
		 * @param table $
		 * @param $id
		 */
		private function delete_row_from_table($table,$id){
			$query = "DELETE FROM `".$table."` WHERE `id`=?";
			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

			$stmt->bind_param('i',$id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}

		/**
		 * возвращает массив данных по всей таблице
		 * @param $table
		 * @return array
		 */
		private function get_all_tbl($table,$where = array(),$sort = array('name'=>'','type'=>"ASC")){
			$query = "SELECT *,DATE_FORMAT(`".$table."`.`date`,'%d.%m.%Y') as date FROM `".$table."`";

			$w = 0;
			foreach ($where as $key => $ask){
				$query .= ($w==0)?' WHERE ':' AND ';
				$query .= ",`$key`='$ask'";
				$w++;
			}
			if ($sort['name'] != ''){
				$query .= " ORDER BY `".$table."`.`".$sort['name']."` ".$sort['type'];
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
		 * обновление одного значения в одной таблице
		 * @param $table
		 * @param $id
		 * @param $key
		 * @param $val
		 */
		private function update_one_val_in_one_row($table,$id,$key,$val){
			switch ($key){
				case 'date':
					$type = 's';
					break;
				default:
					$type = 'd';
					break;
			}

			$query = "UPDATE `".$table."` SET ";
			$query .= " `".addslashes($key)."`=?";
			$type .= 'i';
			$query .= " WHERE `id` =?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param($type,$val, $id) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
		}


		/**
		 * создание пустой строки в базе
		 * @param $table
		 * @param array $data
		 */
		private function insert_empty_row($table,$data = array()){
			$query = "INSERT INTO `".$table."` SET ";
			$i = 0;
			foreach ($data as $key => $val){
				$query .= ($i>0)?',':'';
				$query .= "`$key` = '$val'";
				$i++;
			}
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$this->responseClass->response['data'] = array('id'=>$this->mysqli->insert_id);
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