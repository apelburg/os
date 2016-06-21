<?php


/**
 * Class CalculateMoneyBlock
 *
 * расчет автоматической ЗП
 *
 */
class CalculateMoneyBlock extends aplStdAJAXMethod{
	public $manager_id = 0;
	public $month = 0;
	public $year = 0;



	private $salary = 0;
	private $premium = 0;
	private $pension = 0;


	public function __construct($manager_id = 0,$month = 0,$year = 0){
		# подключение к БД
		$this->db();

		$this->manager_id  	= (int)$manager_id;
		$this->month 		= (int)$month;
		$this->year 		= (int)$year;

	}

	public function calculate(){
		return [
			'salary'=>$this->salary,
			'premium'=> $this->premium,
			'pension' => $this->pension
		];
	}


	/**
	 * подсчёт прибыли по закрытм за месяц счетам
	 */
	private function get_profit(){
		$closed = $this->get_data_bill_closed();

		$this->profit = 0;
		$this->oborot = 0;
		foreach ($closed as $row){
			$this->profit += $row['profit'];
			$this->oborot +=$row['price_out_payment'];
		}

		return [
			'profit'=>$this->profit,
			'oborot'=>$this->oborot
		];
	}



	/**
	 * получаем информацию по менеджеру
	 *
	 * @param $id
	 * @return array
	 */
	private function get_manager_data($id){
		$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id=? ";

		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('i',$id) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();


		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				return $row;
			}
		}
		return [];
	}

	/**
	 * возвращает массив данных по всей таблице
	 *
	 * @param $table
	 * @param array $where
	 * @param array $sort
	 * @return array
	 */
	private function get_all_tbl($table,$where = array(),$sort = array('name'=>'','type'=>"ASC")){
		$query = "SELECT *,DATE_FORMAT(`".$table."`.`date`,'%d.%m.%Y') as date FROM `".$table."`";

		$w = 0;
		foreach ($where as $key => $ask){
			$query .= ($w==0)?' WHERE ':' AND ';
			$query .= " `$key`='$ask'";
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
	 * удаление строк компенсаций
	 * @param $id
	 */
	public function delete_rows_compensations($id){
		$query = "DELETE FROM `".ACCOUNTING_ACCRUALS_COMP."` WHERE `os__accounting_accruals_id`=?";
		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

		$stmt->bind_param('i',$id) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();
	}
	/**
	 * запрос строк компенсаций
	 * @param $id
	 */
	public function get_rows_compensations($id){
		$query = "SELECT * FROM `".ACCOUNTING_ACCRUALS_COMP."` WHERE `os__accounting_accruals_id`=?";
		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

		$stmt->bind_param('i',$id) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

		$rows = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/**
	 * запрос строк ДОП компенсаций
	 * @param $id
	 */
	public function get_rows_dopCompensations($id){
		$query = "SELECT * FROM `".ACCOUNTING_ACCRUALS_DOP."` WHERE `os__accounting_accruals_id`=?";
		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);

		$stmt->bind_param('i',$id) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

		$rows = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/**
	 * копирует строки компенсаций в расчет
	 *
	 * @param $id - id расчёта
	 */
	public function copy_rows_compensations($id){
		$this->delete_rows_compensations($id);
		$query = "INSERT";
		$query .= " INTO ".ACCOUNTING_ACCRUALS_COMP." ( os__accounting_accruals_id, date, money, name )";
		$query .="  SELECT  '".$id."', '".date('Y-m-d',time())."', ".COMPENSATION_TBL.".val, ".COMPENSATION_TBL.".name ";
		$query .="	FROM    ".COMPENSATION_TBL."";
		$query .="	WHERE   ".COMPENSATION_TBL.".user_id = '".$this->manager_id."' ";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		return $this->get_rows_compensations($id);

	}






	/**
	 * @return array
	 */
	public function calculate_and_update_accruals_tbl(){
		# БЛОК ПЕРЕСЧЁТА



		# запрашиваем строку
		$where['manager_id']  = $this->manager_id;
		$where['month'] = $this->month;
		$where['year']  = $this->year;
		
		$old_row = $this->get_all_tbl(ACCOUNTING_ACCRUALS,$where);


		if(count($old_row) == 0){
			# если строка не найдена
			# создание новой строки расчёта с расчитанными параметрами
			return $this->create_new_row();

		}else{
			# обновление старой строки расчёта с расчитанными параметрами

			return $this->update_old_row($old_row[0]['id']);
		}
	}

	/**
	 * создание пустой строки в базе
	 *
	 * @param $table
	 * @param array $data
	 * @return array
	 */
	private function insert_empty_row($table,$data = array()){
		$query = "INSERT INTO `".$table."` ";
		$i = 0;
		if (count($data)>0){
			$query .=" SET ";
		}
		foreach ($data as $key => $val){
			$query .= ($i>0)?',':'';
			$query .= "`$key` = '$val'";
			$i++;
		}
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		$data['id'] = $this->mysqli->insert_id;
		return $data;
	}

	/**
	 * обновление старой строки начислений
	 *
	 * @param $id
	 * @return array
	 */
	private function update_old_row($id){
		# обновляем
		$this->update__row(ACCOUNTING_ACCRUALS,$this->accruals_calc(),$id);
		# возвращаем обновленные данные
		return $this->get_all_tbl(ACCOUNTING_ACCRUALS,['id'=>$id]);
	}

	/**
	 * создание новой строки с расчётом начислений
	 *
	 * @return array
	 */
	private function create_new_row(){
		$data = $this->accruals_calc();
		$data['year'] = $this->year;
		$data['month'] = $this->month;
		$data['manager_id'] = $this->manager_id;
		return $this->insert_empty_row(ACCOUNTING_ACCRUALS,$data);
	}

	# апдейт старой строки с расчётом начислений
	private function update__row($table,$data = [],$id){
		$query = "UPDATE `".$table."` SET ";

		$i = 0;
		foreach ($data as $key => $val){
			$query .= ($i>0)?',':'';
			$query .= " `$key` = '$val'";
			$i++;
		}

		$query .= " WHERE `id` =?";

		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('i',$id ) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();
	}



	# расчёт начислений
	private function accruals_calc(){

		$arr = [
			'salary'=>0,
			'premium'=> 0,
			'pension' => 0
		];

		# получаем данные по манагеру
		$this->manager_data = $this->get_manager_data($this->manager_id);

		if($this->manager_data){
			# получаем прибыль
			$this->get_profit();

			# менеджер рекламных агенств
			if($this->manager_data['manager'] == 1){

				$tbl_zp = $this->get_all_tbl(ACCOUNTING_ZP_REK, array(), array('name'=>'profit_start','type'=>"ASC"));
				foreach ($tbl_zp as $row){
					if($row['profit_start'] <= $this->profit && $row['profit_end'] >= $this->profit){
						$arr['salary'] += $row['salary'];
						$arr['premium'] += round($this->profit/100*$row['premium'],0);
						if($row['return'] <= $this->oborot){
							$arr['premium'] += round($this->profit/100*$row['premium2'],0);
						}
						break;
					}
				}
			}

			# менеджер конечных клиентов
			if($this->manager_data['manager'] == 2){

				$tbl_zp = $this->get_all_tbl(ACCOUNTING_ZP_KON, array(), array('name'=>'profit_start','type'=>"ASC"));
				foreach ($tbl_zp as $row){
					if((int)$row['profit_start'] <= (int)$this->profit && (int)$row['profit_end'] >= (int)$this->profit){
						$arr['salary'] += $row['salary'];
						$arr['premium'] += round($this->profit/100*$row['premium'],0);
						break;
					}
				}
			}

			# расчет бонуса пенсии
			if ($this->manager_data['date_start_wock'] != ''){
				$pension_rows = $this->get_all_tbl(ACCOUNTING_PENSION,array('checked'=>'1'));

				$time = time() - strtotime($this->manager_data['date_start_wock']);

				$year = floor($time/31536000);

				$s = 0;
				foreach ($pension_rows as $row){
					if ($s > 0){break;}
					# перебор колонок
					foreach ($row as $key => $val){
						# перебор по колонкам с префиксом n_
						if(substr($key,0,2) == 'n_'){
							$filter = explode("_",substr($key, 0, -2));
							if ($filter[0] >= $year && $filter[1]<=$year){
								$arr['pension'] += $val;
								$s = 1;
								break;
							}
						}
					}
				}
			}
		}

		return $arr;
	}




	/**
	 * вычисляет строки закрытых за месяц счетов
	 * @return array
	 */
	public function get_data_bill_closed(){
		$query = "SELECT *";
		$query .= ", DATE_FORMAT(`".INVOICE_TBL."`.`closed_date`,'%d.%m.%Y') as closed_date ";
		$query .= ", RIGHT(CONCAT('00000000' , (invoice_num)),6) as invoice_num";
		$query .= ", (price_out_payment - costs) as profit";
		$query .= ", CASE
                WHEN price_out_payment = 0 THEN '0.00'
                ELSE ROUND(((price_out_payment - costs) / price_out_payment * 100),2)
            END AS 'pr'";
		$query .= " FROM `".INVOICE_TBL."`";

		$query .= " WHERE `manager_id`=?";
		$query .= " AND YEAR(closed_date)=?";
		$query .= " AND MONTH(closed_date)=?";

		//		 echo $query.' - '.$this->manager_id.' - '. $this->year.' - '. $this->month;
		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('iss',$this->manager_id, $this->year, $this->month ) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

		$data_bill_closed = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$data_bill_closed[] = $row;
			}
		}
//
//		print_r($this);
//		print_r($this->manager_id);


		return $data_bill_closed;

	}
}
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
		 * сохранение(редактирование) компенсаций импортированных в расчет
		 */
		protected function save_compensation_val_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_ACCRUALS_COMP,$_POST['id'],$_POST['key'],$_POST['val']);
		}


		/**
		 * удаление компенсаций создаваемых в расчёте
		 */
		protected function delete_dop_compensation_val_AJAX(){
			$this->delete_row_from_table(ACCOUNTING_ACCRUALS_DOP,$_POST['id']);
		}

		/**
		 * редактирование значений компенсаций создаваемых в расчёте
		 */
		protected function save_dop_compensation_val_AJAX(){
			$this->update_one_val_in_one_row(ACCOUNTING_ACCRUALS_DOP,$_POST['id'],$_POST['key'],$_POST['val']);
		}

		protected function create_dop_compensation_AJAX(){
			$data['os__accounting_accruals_id'] =(int)$_POST['id'];
			$data['name'] =$_POST['name'];
			$data['r'] =$_POST['val'];
			$data['flag_r'] =(int)$_POST['flag_r'];
			$this->responseClass->response['data'] = $this->insert_empty_row(ACCOUNTING_ACCRUALS_DOP,$data);
		}
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

		# пересчитываем и обновляем информацию по зп манагера
		protected function calculate_and_update_accruals_tbl_AJAX(){
			# проверка прав
			if ($this->user_access == 5 && $this->user_id != $this->manager_id){
				$this->responseClass->addMessage('У Вас недостаточно прав для получения данной информации','error_message');
				return;

			}

			# БЛОК ПРОВЕРКИ ограничений на пересчёт
			$date = new DateTime();
			$time_stump = $date->getTimestamp();


			# проверка наступления расчётного месяца

			# РАСКОММЕНТИРОВАТЬ ПЕРЕД ЗАПУСКОМ
//			if($time_stump < strtotime('01.'.$_GET['month_number'].'.'.$_GET['year'])){
//				$this->responseClass->addMessage('Данный месяц ещё не наступил, расчёт запрещён','error_message');
//				return;
//			}

			# по умолчанию ставим разрешение на пересчет ЗП до 15 дней по прошествию расчетного месяца, потом пересчёт закрыт для расчёта
			# делается это на случай чтобы случайно не пересчитали старые расчёты, относительно новых вводных, которые спустя скажем год могли измениться (ЗП, компенсации)
			# иначе вся статистика полетит куда подальше

			# РАСКОММЕНТИРОВАТЬ ПЕРЕД ЗАПУСКОМ
//			if(strtotime('01.'.$_GET['month_number'].'.'.$_GET['year']) - $time_stump > 1296000){
//				$this->responseClass->addMessage('К сожалению расчётный период по данному месяцу уже завершон, обратитесь за помощтью к администратору.','error_message');
//				return;
//			}

			$Calc = new CalculateMoneyBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
			$Calc->calculate_and_update_accruals_tbl();

			$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);


			$this->responseClass->response['data']['compensation'] = [];
			if (isset($this->responseClass->response['data']['accruals'][0]['id'])){
				$this->responseClass->response['data']['compensation'] = $Calc->copy_rows_compensations($this->responseClass->response['data']['accruals'][0]['id']);
				$this->responseClass->response['data']['dop_compensation'] = $Calc->get_rows_dopCompensations($this->responseClass->response['data']['accruals'][0]['id']);
			}
		}



		# создание строки расчета
		protected function create_new_accruals_calc_AJAX(){
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

			$this->responseClass->response['data']['compensation'] = [];
			if (isset($this->responseClass->response['data']['accruals'][0]['id'])){
				$Calc = new CalculateMoneyBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
				$this->responseClass->response['data']['compensation'] = $Calc->get_rows_compensations($this->responseClass->response['data']['accruals'][0]['id']);

				$this->responseClass->response['data']['dop_compensation'] = $Calc->get_rows_dopCompensations($this->responseClass->response['data']['accruals'][0]['id']);

			}
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

			$Calc = new CalculateMoneyBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
			$this->responseClass->response['data']['bill_closed'] = $Calc->get_data_bill_closed();
//			echo 654654;
			// запрос рассчитанных начислений
			$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);



			$this->responseClass->response['data']['compensation'] = [];
			if (isset($this->responseClass->response['data']['accruals'][0]['id'])){
				$this->responseClass->response['data']['dop_compensation'] = $Calc->get_rows_dopCompensations($this->responseClass->response['data']['accruals'][0]['id']);
				$this->responseClass->response['data']['compensation'] = $Calc->get_rows_compensations($this->responseClass->response['data']['accruals'][0]['id']);
			}

				$this->responseClass->response['data']['payments'] = $this->get_get_data_payments();

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

		private function get_get_data_payments(){
			return $this->get_all_tbl_simple(ACCOUNTING_ACCRUALS_PAY);
		}


		protected function calculate_and_update_payment_tbl_AJAX(){
			$this->get_get_data_payments();
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
			$val = date('Y-m-d',strtotime($_POST['val']));
			if(substr($_POST['key'],0,2) == 'n_'){$val = $_POST['val'];}
			$this->update_one_val_in_one_row(ACCOUNTING_PENSION,$_POST['id'],$_POST['key'],$val);
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
			$this->responseClass->response['data'] = $this->insert_empty_row(ACCOUNTING_PENSION,array('date'=>'00.00.0000'));
		}
		/**
		 * создание строки в таблице рекламщики
		 */
		protected function create_men_zp_rec_row_AJAX(){
			$this->responseClass->response['data'] = $this->insert_empty_row(ACCOUNTING_ZP_REK,array('date'=>'00.00.0000'));
		}

		/**
		 * создание строки в таблице конечники
		 */
		protected function create_men_zp_kon_row_AJAX(){
			$this->responseClass->response['data'] = $this->insert_empty_row(ACCOUNTING_ZP_KON,array('date'=>'00.00.0000'));
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
				$query .= " `$key`='$ask'";
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

		private function get_all_tbl_simple($table,$where = array(),$sort = array('name'=>'','type'=>"ASC")){
			$query = "SELECT * FROM `".$table."`";

			$w = 0;
			foreach ($where as $key => $ask){
				$query .= ($w==0)?' WHERE ':' AND ';
				$query .= " `$key`='$ask'";
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
			$query = "INSERT INTO `".$table."` ";
			$i = 0;
			if (count($data)>0){
				$query .=" SET ";
			}
			foreach ($data as $key => $val){
				$query .= ($i>0)?',':'';
				$query .= "`$key` = '$val'";
				$i++;
			}


			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			$data['id'] = $this->mysqli->insert_id;
			return $data;
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