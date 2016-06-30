<?php
/**
 * Class UserApi
 *
 * класс редактирования данных пользователя
 * написан в виде API для модуля редактирования дополнительных данных
 * необходимых для работы модуля учёт
 *
 * в последствии можно будет расширить под модуль пользователей в новой OS
 *
 *
 *
 * @author  	Alexey Kapitonov
 * @version 	10:15 14/06/2016
 */
class UserApi  extends aplStdAJAXMethod
{
	/**
	 * @var array|int
	 */
	protected 	$user_access = 0; 		// user right (int)
	protected 	$user_id = 0;			// user id with base
	public 		$User = array(); 		// authorised user info

	/**
	 * UserApi constructor.
	 */
	public function __construct()
	{
		// connectin to database
		$this->db();

		$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

		$this->User = $this->getUserDatabase($this->user_id);

		$this->user_access = $this->getRights();

		// calls ajax methods from POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		// calls ajax methods from GET
		## the data GET --- on debag time !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}

		# т.к. временно этот класс выполняет функцию ТОЛЬКО API
		# - при отсутствии обнаружения AJAX запросов выходим
		exit('{"user_api":false}');
	}

	/**
	 * @return array
	 */
	private function getRights()
	{
		if ($this->User['id'] > 0){
			$this->user_access = $this->User['access'];
		}
		return $this->user_access;
	}

	/**
	 * удаление строки компенсаций
	 */
	protected function delete_compensation_row_AJAX(){
		$this->delete_row_from_table(COMPENSATION_TBL,$_POST['id']);
	}

	/**
	 * создание строки компенсаций
	 */
	protected function create_compensation_row_AJAX(){
		$query = "INSERT INTO `".COMPENSATION_TBL."` SET ";
		$query .= " `user_id`=?";
		$query .= ", `name`=?";
		$query .= ", `val`=?";

		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('isd',$_POST['user_id'],$_POST['name'], $_POST['val']) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();
		$this->responseClass->response['data'] = array(
			'id'=>$this->mysqli->insert_id,
			'name'=>$_POST['name'],
			'val'=>$_POST['val']
		);
	}

	/**
	 * получаем данные из таблицы пенсиий
	 */
	protected function get_compensations_row_AJAX(){
		$this->responseClass->response['data'] = $this->get_all_tbl_simple(COMPENSATION_TBL,array('user_id'=>(int)$_POST['user_id']));
	}

	/**
	 * сохраняет дату трудоустройства сотрудника
	 */
	protected function save_date_work_start_AJAX(){
		$query = "UPDATE `".MANAGERS_TBL."` SET ";
		$query .= " `date_start_wock`=?";
		$date = date('Y-m-d',strtotime($_POST['date']));
		$query .= " WHERE `id` =?";

		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('si', $date, $_POST['id'] ) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();
	}

	/**
	 * статус
	 */
	protected function save_status_AJAX(){
		$this->update_one_val_in_one_row(MANAGERS_TBL,$_POST['id'],'status',$_POST['val']);
	}
	/**
	 * зарплата
	 */
	protected function save_salary_AJAX(){
		$this->update_one_val_in_one_row(MANAGERS_TBL,$_POST['id'],'salary',$_POST['val']);
	}
	/**
	 * аванс
	 */
	protected function save_avans_AJAX(){
		$this->update_one_val_in_one_row(MANAGERS_TBL,$_POST['id'],'avans',$_POST['val']);
	}

	/**
	 * тип менеджера
	 */
	protected function save_manager_type_AJAX(){
		$this->update_one_val_in_one_row(MANAGERS_TBL,$_POST['id'],'manager',$_POST['val']);
	}


	/**
	 * создание строки в таблице пенсий
	 */
	protected function create_pension_row_AJAX(){
		$this->insert_empty_row(ACCOUNTING_PENSION,array('date'=>'00.00.0000'));
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
				$this->User = $row;
			}
		}
		return $int;
	}
}



?>