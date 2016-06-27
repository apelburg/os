<?php

/**
 * Class CalculateMoneyBlock
 *
 * расчет автоматической ЗП
 *
 */
class CalculateMoneyBlock extends Accounting{
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


		return $old_row;
	}

	/**
	 * обновление старой строки начислений
	 *
	 * @param $id
	 * @return array
	 */
	private function update_old_row($id){
		# обновляем
		$data = $this->accruals_calc();
		$data['salary_r_fl']=0;
		$data['premium_r_fl']=0;
		$data['pension_r_fl']=0;

		$this->update__row(ACCOUNTING_ACCRUALS,$data,$id);
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


	# расчёт начислений
	private function accruals_calc(){
		$arr = [
			'salary'=>0,
			'premium'=> 0,
			'pension' => 0
		];
		# получаем данные по манагеру
		$this->manager_data = $this->getManager_data($this->manager_id);
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
//			echo $this->printArr($arr);
			if ($this->manager_data['date_start_wock'] != ''){
				$pension_rows = $this->get_all_tbl(ACCOUNTING_PENSION,array('checked'=>'1'));

				$time = time() - strtotime($this->manager_data['date_start_wock']);
				$month = floor($time/2628000); // 2628000
				$year = $month / 12;
				$s = 0;
//				echo $year.' / '.$month;
//							echo $this->printArr($pension_rows);
				foreach ($pension_rows as $row){
					if ($s > 0){break;}
					# перебор колонок
					foreach ($row as $key => $val){
						if ($s > 0){break;}
						# перебор по колонкам с префиксом n_
						if(substr($key,0,2) == 'n_'){

							$filter = explode("_",substr($key, 2));

							if ($filter[0] <= $year and $filter[1] >= $year){
//								echo $val;
								$arr['pension'] += $val;
								$s = 1;
								break;
							}
						}
					}
				}
			}
		}
//					echo $this->printArr($arr);
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
 * Class CreditBlock
 *
 * блок Кредит
 *
 * баботает с таблицей ACCOUNTING_ACCRUALS_CREDIT
 */
class CreditBlock extends Accounting{

	private $minus = 0;
	private $money = 0;
	private $fl_b = 0;
	private $fl_m = 0;
	private $date = 0;

	public function __construct($manager_id = 0,$month = 0,$year = 0){
		# подключение к БД
		$this->db();
		$this->manager_id  	= (int)$manager_id;
		$this->month 		= (int)$month;
		$this->year 		= (int)$year;

		$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

		// geting rights
		if ($this->user_id > 0){

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
		}

	}

	/**
	 * запрос данных
	 *
	 * @return array
	 */
	public function get_data(){
		$where['manager_id']  = $this->manager_id;
		return $this->check_and_del($this->get_all_tbl(ACCOUNTING_ACCRUALS_CREDIT,$where,array('name' => 'id', 'type' => "DESC")));
	}

	/**
	 * редактирование строки
	 *
	 * @return bool
	 */
	public  function edit_data(){
		$data = [];

		# список разрешённых к редактированию полей
		$edit_arr = $this->tpl__edit_data();



		if (isset($_POST['key'])){
			if (in_array($_POST['key'],$edit_arr)){
				$key = $_POST['key'];
				$this->$key = $_POST['val'];
				$data  = [$key => $this->$key];
			}
		}



		// если изменения были
		if (isset($_POST['id']) and $_POST['id'] > 0 and count($data) > 0){
			# update
			$id = (int)$_POST['id'];

			$this->update__row(ACCOUNTING_ACCRUALS_CREDIT, $data, $id);

			return true;
		}else{
			return false;
		}
	}

	/**
	 * создание строки
	 */
	public function create_data(){
		$this->minus = $_POST['minus'];
		$this->money = $_POST['money'];


		$data = $this->insert_empty_row(ACCOUNTING_ACCRUALS_CREDIT,$this->tpl__create_row_data());
		$date = date('d.m.Y',time());

		$data['date'] = $date;
		$data['fl_m'] = 0;
		$data['fl_b'] = 0;
		return $data;
	}


	/**
	 * шаблон разрешённых к редактированию полей
	 *
	 * @return array
	 */
	private function tpl__edit_data(){


		switch ($this->user_access){
			case 1:
				$enable = ['money','fl_b','fl_m'];
				break;
			case 2:
				$enable = ['money','fl_b'];
				break;
			case 5:
				$enable = ['fl_m'];
				break;
			default:
				$enable = [];
				break;
		}
		return $enable;
	}

	/**
	 * обновление данных
	 *
	 * @param $table
	 * @param array $data
	 * @param $id
	 */
	function update__row($table, $data = [], $id)
	{
		parent::update__row($table, $data, $id);
	}

	/**
	 * шаблон создания новой строки кредита
	 *
	 * @return array
	 */
	private function tpl__create_row_data(){
		return [
			'minus' => $this->minus,
			'money' => $this->money,
			'manager_id'=>$this->manager_id
		];
	}

	/**
	 * проверяет возвращён ли долг, при полном расчёте вычищает данные из базы
	 *
	 * возвращает актуальные данные
	 *
	 * @param $data
	 * @return array
	 */
	private function check_and_del($data){
		$summ = 0;
		$ids = [];
		$flad_no_del = true;

		foreach ($data as $row){
//			echo $this->printArr($row);
			$ids[] = $row['id'];

			if ($row['minus'] == 1){
				$row['money'] = $row['money']*(-1);
			}


			$summ += $row['money'];

			# если в блоке кредита есть неподтверждённые строки, удалять ничего нельзя
			if ($row['fl_b'] == 0 || $row['fl_m'] == 0){
				$flad_no_del = 0;
			}
		}


		if($summ == 0 && $flad_no_del == true){
			$this->delete_rows_from_table(ACCOUNTING_ACCRUALS_CREDIT,$ids);
			return [];
		}
		return $data;
	}

	/**
	 * удаляет строки по набору id
	 *
	 * @param $table
	 * @param $ids
	 */
	function delete_rows_from_table($table, $ids)
	{
		parent::delete_rows_from_table($table, $ids);
	}

	/**
	 * получаем информацию из базы
	 *
	 * @param $table
	 * @param array $data
	 * @return array
	 */
	public function insert_empty_row($table, $data = array())
	{
		return parent::insert_empty_row($table, $data);
	}

	/**
	 * получаем информацию из базы
	 * преобразует колонку date в читабельный вид
	 *
	 * @param $table
	 * @param array $where
	 * @param array $sort
	 * @return array
	 */
	function get_all_tbl($table, $where = array(), $sort = array('name' => '', 'type' => "ASC"))
	{
		return parent::get_all_tbl($table, $where, $sort);
	}

}
/**
 * Class PaymentBlock
 *
 */
class PaymentBlock extends Accounting{
	public $manager_id = 0;
	public $month = 0;
	public $year = 0;
	public $id = 0;

	# денежная информация
	private $oklad = 0;
	private $ovans_card = 0;
	private $ovans1 = 0;
	private $ovans2 = 0;
	private $ovans3 = 0;

	# флаги мен/бух

	private  $oklad_fl_m = 0;
	private  $oklad_fl_b = 0;
	private  $ovans_card_fl_m = 0;
	private  $ovans_card_fl_b = 0;
	private  $ovans1_fl_m = 0;
	private  $ovans1_fl_b = 0;
	private  $ovans2_fl_m = 0;
	private  $ovans2_fl_b = 0;
	private  $ovans3_fl_m = 0;
	private  $ovans3_fl_b = 0;




	public function __construct($manager_id = 0,$month = 0,$year = 0){
		# подключение к БД
		$this->db();
		$this->manager_id  	= (int)$manager_id;
		$this->month 		= (int)$month;
		$this->year 		= (int)$year;
	}

	/**
	 * проверка
	 *
	 * @return bool
	 */
	public function check_closed_pay(){
//		return $this->printArr($this);
		if($this->oklad_fl_m > 0
			&&	$this->ovans_card_fl_m > 0
			&&	$this->ovans1_fl_m > 0
			&&	$this->ovans2_fl_m > 0
			&&	$this->ovans3_fl_m > 0 ){
			return 1;
		}
		return 0;

	}


	/**
	 * объект для перезаписи
	 *
	 * @return array
	 */
	public function update_data_obj(){
		return [
			'oklad'=>$this->oklad,
			'ovans_card'=> $this->ovans_card
		];
	}


	/**
	 * редактируемые данные
	 *
	 * @return array
	 */
	public function user_insert_data_obj(){
		return [
			'ovans1'=>$this->ovans1,
			'ovans2'=>$this->ovans2,
			'ovans3'=>$this->ovans3
		];
	}

	/**
	 * объект для возврата пустого значения
	 *
	 * @return array
	 */
	private function simple_obj(){
		return [
			'id'=>$this->id,
			'oklad'=>$this->oklad,
			'oklad_fl_m' => $this->oklad_fl_m,
			'oklad_fl_b' => $this->oklad_fl_b,
			'ovans_card'=> $this->ovans_card,
			'ovans_card_fl_m' => $this->ovans_card_fl_m,
			'ovans_card_fl_b' => $this->ovans_card_fl_b,
			'ovans1'=>$this->ovans1,
			'ovans2'=>$this->ovans2,
			'ovans3'=>$this->ovans3,
			'ovans1_fl_m' => $this->ovans1_fl_m,
			'ovans1_fl_b' => $this->ovans1_fl_b,
			'ovans2_fl_m' => $this->ovans2_fl_m,
			'ovans2_fl_b' => $this->ovans2_fl_b,
			'ovans3_fl_m' => $this->ovans3_fl_m,
			'ovans3_fl_b' => $this->ovans3_fl_b,
		];
	}

	/**
	 * объект для создания записи
	 *
	 * @return array
	 */
	public function insert_data_obj(){
		return [
			'oklad'=>$this->oklad,
			'ovans_card'=> $this->ovans_card,
			'manager_id'=>$this->manager_id,
			'ovans1'=>$this->ovans1,
			'ovans2'=>$this->ovans2,
			'ovans3'=>$this->ovans3,
			'month'=>$this->month,
			'year'=>$this->year
		];
	}


	/**
	 * запрос строи выплат
	 * @param $id
	 */
	public function get_row($id = 0 ){
		# запрашиваем строку
		$where['manager_id']  = $this->manager_id;
		$where['month'] = $this->month;
		$where['year']  = $this->year;

		if ( $id > 0 ){
			$where['id']  = (int)$id;
		}

		$data = $this->get_all_tbl_simple(ACCOUNTING_ACCRUALS_PAY,$where);

		if (isset($data[0]['id'])){
			$this->id = $data[0]['id'];
			$this->ovans1 = $data[0]['ovans1'];
			$this->ovans2 = $data[0]['ovans2'];
			$this->ovans3 = $data[0]['ovans3'];
			$this->oklad_fl_m = $data[0]['oklad_fl_m'];
			$this->oklad_fl_b = $data[0]['oklad_fl_b'];
			$this->ovans_card_fl_m = $data[0]['ovans_card_fl_m'];
			$this->ovans_card_fl_b = $data[0]['ovans_card_fl_b'];
			$this->ovans1_fl_m = $data[0]['ovans1_fl_m'];
			$this->ovans1_fl_b = $data[0]['ovans1_fl_b'];
			$this->ovans2_fl_m = $data[0]['ovans2_fl_m'];
			$this->ovans2_fl_b = $data[0]['ovans2_fl_b'];
			$this->ovans3_fl_m = $data[0]['ovans3_fl_m'];
			$this->ovans3_fl_b = $data[0]['ovans3_fl_b'];

			$data = $data[0];
		}


		return $data;
	}


	/**
	 * @return array
	 */
	public function get_data(){
		# запрашиваем строку
		$data = $this->get_row();
		if (count($data) == 0){
			$data = $this->simple_obj();
			$data['id'] = 0;
		}
		return $data;
	}

	/**
	 * шаблон разрешённых к редактированию полей
	 *
	 * @param int $access
	 * @return array
	 */
	private function tpl__edit_data($access = 0){
		return [
			'ovans1',
			'ovans2',
			'ovans3',
			'ovans1_fl_m',
			'ovans1_fl_b',
			'ovans2_fl_m',
			'ovans2_fl_b',
			'ovans3_fl_m',
			'ovans3_fl_b',
			'oklad_fl_m',
			'oklad_fl_b',
			'ovans_card_fl_m',
			'ovans_card_fl_b',
			'go_to_credit'
		];
	}


	/**
	 * обновление по одному полю
	 *
	 * @return array
	 */
	public function updatePaymentsRow(){
		# список разрешённых к редактированию полей
		$edit_arr = $this->tpl__edit_data();

		$data = [];

		if (isset($_POST['key'])){
			if (in_array($_POST['key'],$edit_arr)){
				$this->get_row();

				$key = $_POST['key'];

				$this->$key = $_POST['val'];
				$data  = [$key => $this->$key];
			}
		}

		if (isset($_POST['id']) and $_POST['id'] > 0){
			# update
			$this->id = (int)$_POST['id'];

			$this->update__row(ACCOUNTING_ACCRUALS_PAY, $data, $this->id);

			return [];
		}else{
			# запрос актуальных данных по ЗП
			$this->manager_data = $this->getManager_data($this->manager_id);
			# внесение данных в объект
			$this->oklad = $this->manager_data['salary'];
			$this->ovans_card = $this->manager_data['avans'];

			# insert new
			$data = $this->insert_data_obj();
			$data = array_merge($data ,$this->insert_empty_row(ACCOUNTING_ACCRUALS_PAY, $data));
			return  $data;
		}
	}




	/**
	 * обновление данных по окладу и авансоваой части
	 *
	 * @param int $id
	 */
	public  function update_payment_tbl($id = 0){
		# запрос актуальных данных по ЗП
		$this->manager_data = $this->getManager_data($this->manager_id);
		# внесение данных в объект
		$this->oklad = $this->manager_data['salary'];
		$this->ovans_card = $this->manager_data['avans'];

		// обновление данных
		# проверка записи на существование


		if(count($this->get_row()) > 0){

			$this->update__row(ACCOUNTING_ACCRUALS_PAY,$this->update_data_obj(),$this->id);
			//			echo $this->printArr($this);
			$data = $this->simple_obj();


		}else{
			// создание новой строки расчета ЗП
			$data = $this->insert_data_obj();

			$data = array_merge($data,$this->insert_empty_row(ACCOUNTING_ACCRUALS_PAY,$data));
		}

		return $data;

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
	// для перевода всех приложений в режим разработки раскоментировать и установить FALSE
	protected $production = false;

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
			$this->responseClass->response['data']['access'] = $this->user_access;
			$this->responseClass->response['data']['id'] = $this->user_id;

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
		if ($this->user_access == 5 && $this->user_id != $_GET['manager_id']){
			$this->responseClass->addMessage('У Вас недостаточно прав для получения данной информации','error_message',1000);
			return;
		}

		# БЛОК ПРОВЕРКИ ограничений на пересчёт
		$date = new DateTime();
		$time_stump = $date->getTimestamp();


		# проверка наступления расчётного месяца
		if($time_stump < strtotime('01.'.$_GET['month_number'].'.'.$_GET['year'])){
			$this->responseClass->addMessage('Данный месяц ещё не наступил, расчёт запрещён','error_message',1000);
			if ($this->prod__check()){return;}
		}

		# по умолчанию ставим разрешение на пересчет ЗП до 15 дней по прошествию расчетного месяца, потом пересчёт закрыт для расчёта
		# делается это на случай чтобы случайно не пересчитали старые расчёты, относительно новых вводных, которые спустя скажем год могли измениться (ЗП, компенсации)
		# иначе вся статистика полетит куда подальше
		if(strtotime('01.'.$_GET['month_number'].'.'.$_GET['year']) - $time_stump > 1296000){
			$this->responseClass->addMessage('К сожалению расчётный период по данному месяцу уже завершён, обратитесь за помощтью к администратору.','error_message',1);
			if ($this->prod__check()){return;}
		}



		# передача в скрипт прав пользователя
		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;

		$Calc = new CalculateMoneyBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$Calc->calculate_and_update_accruals_tbl();


		$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);


		$this->responseClass->response['data']['compensation'] = [];
		if (isset($this->responseClass->response['data']['accruals'][0]['id'])){
			$this->responseClass->response['data']['compensation'] = $Calc->copy_rows_compensations($this->responseClass->response['data']['accruals'][0]['id']);
			$this->responseClass->response['data']['dop_compensation'] = $Calc->get_rows_dopCompensations($this->responseClass->response['data']['accruals'][0]['id']);
		}


		# запрос данных по выплатам за месяц
		$Payments = new PaymentBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['payments'] = $Payments->get_row();

		# запрос данных по кредату
		$Credit = new CreditBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['credit'] = $Credit->get_data();




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

		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;

		# запрос данных по выплатам за месяц
		$Payments = new PaymentBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['payments'] = $Payments->get_row();


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

		# проверка прав
		if ($this->user_access == 5 && $this->user_id != $_GET['manager_id']){
			$this->responseClass->addMessage('У Вас недостаточно прав для получения данной информации','error_message',1000);
			return;
		}

		$Calc = new CalculateMoneyBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);

		$this->responseClass->response['data']['bill_closed'] = $Calc->get_data_bill_closed();

		// запрос рассчитанных начислений
		$this->responseClass->response['data']['accruals'] = $this->get_data_accruals($_GET['manager_id'],$_GET['year'],$_GET['month_number']);



		$this->responseClass->response['data']['compensation'] = [];
		if (isset($this->responseClass->response['data']['accruals'][0]['id'])){
			$this->responseClass->response['data']['dop_compensation'] = $Calc->get_rows_dopCompensations($this->responseClass->response['data']['accruals'][0]['id']);
			$this->responseClass->response['data']['compensation'] = $Calc->get_rows_compensations($this->responseClass->response['data']['accruals'][0]['id']);
		}

		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;

		# запрос данных по выплатам за месяц
		$Payments = new PaymentBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['payments'] = $Payments->get_row();

		# запрос данных по кредату
		$Credit = new CreditBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['credit'] = $Credit->get_data();

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
	 * запрос данных по выплатам из базы
	 *
	 * @param $manager_id
	 * @param $year
	 * @param $month
	 * @return array
	 */
	private function get_data_payments($manager_id,$year,$month){
		$where['manager_id'] = (int)$manager_id;
		$where['year'] = (int)$year;
		$where['month'] = (int)$month;
		return $this->get_all_tbl_simple(ACCOUNTING_ACCRUALS_PAY,$where);
	}

	/**
	 * обновление (редактирование) информации по выплатам
	 */
	protected function update_payments_row_AJAX(){
		$Paymewnt = new PaymentBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);

		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;
		$this->responseClass->response['data']['payments'] = $Paymewnt->updatePaymentsRow();
	}

	/**
	 * редактирование блока кредит
	 */
	protected function update_credit_row_AJAX(){
		# запрос данных по кредату
		$Credit = new CreditBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;


		# выброс сообщения по ошибке
		if(!$Credit->edit_data()){
			$this->responseClass->response['errors']['update'] = false;

			$mess = "Что-то пошло не так! Данные не были изменены !";
			$this->responseClass->addMessage($mess,'error_message',1000);
		}
	}

	/**
	 * создание строки в блоке кредит
	 */
	protected function create_credit_row_AJAX(){
		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;

		$Credit = new CreditBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);
		$this->responseClass->response['data']['credit'] = $Credit->create_data();
	}


	/**
	 * пересчет данных по выплатам зарплаты
	 */
	protected function calculate_and_update_payment_tbl_AJAX(){
		# запрос данных по выплатам за месяц
		# проверка прав
		if ($this->user_access == 5 && $this->user_id != $_GET['manager_id']){
			$this->responseClass->addMessage('У Вас недостаточно прав для получения данной информации','error_message',1000);
			return;
		}


		$Paymewnt = new PaymentBlock($_GET['manager_id'],$_GET['month_number'],$_GET['year']);

		if (isset($_POST['id'])){
			$id = (int)$_POST['id'];
		}else{
			$id = 0;
		}
		$this->responseClass->response['data']['access'] = $this->user_access;
		$this->responseClass->response['data']['user_id'] = $this->user_id;
		$this->responseClass->response['data']['payments'] = $Paymewnt->update_payment_tbl($id);

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
	 * удаляет набор строк по массиву id
	 * @param table $
	 * @param $id
	 */
	protected function delete_rows_from_table($table,$ids){
		if(is_array($ids) && count($ids) > 0){
			$query = "DELETE FROM `".$table."` WHERE `id` IN ('".implode("','",$ids)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			return true;
		}
		return false;
	}



	/**
	 * апдейт старой строки с расчётом начислений
	 *
	 * @param $table
	 * @param array $data
	 * @param $id
	 */
	protected function update__row($table,$data = [],$id){
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

	/**
	 * возвращает массив данных по всей таблице
	 *
	 * @param $table
	 * @param array $where
	 * @param array $sort
	 * @return array
	 */
	protected function get_all_tbl($table,$where = array(),$sort = array('name'=>'','type'=>"ASC")){
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
	 * выборка данных по менеджеру
	 *
	 * @param $id
	 * @return array
	 */
	protected function getManager_data($id){
		$arr = $this->getUserArrDatabase([$id]);
		if(isset($arr[0])){
			return $arr[0];
		}else{
			return [];
		}
	}

	/**
	 * выборка данных из одной таблицы
	 *
	 * @param $table
	 * @param array $where
	 * @param array $sort
	 * @return array
	 */
	protected function get_all_tbl_simple($table,$where = array(),$sort = array('name'=>'','type'=>"ASC")){
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
	protected function insert_empty_row($table,$data = array()){
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
	public function get_user_access_Database_Int($id){
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