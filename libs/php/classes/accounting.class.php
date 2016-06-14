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
			$this->update_one_val_in_one_row(ACCOUNTING_PENSION,$_POST['id'],$_POST['key'],$_POST['val']);
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