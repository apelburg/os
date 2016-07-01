<?php

/**
 * Class Invoice_basket
 */
class Invoice_basket extends Statistics
{
	private $userName = '';
	private $module = 'invoice_basket';

	function __construct()
	{
		$this->db();

		$this->user_id = isset($_SESSION['access']['user_id']) ? $_SESSION['access']['user_id'] : 0;

		$this->User = $this->getUserDatabase($this->user_id);

		$this->user_access = $this->getRights();
	}


	/**
	 * получаем права пользователя
	 *
	 * @return array
	 */
	private function getRights()
	{
		if ($this->User['id'] > 0) {
			$this->user_access = $this->User['access'];
		}
		return $this->user_access;
	}

	/**
	 * обновляем имя пользователя и возвращаем его
	 *
	 * @return mixed|string
	 */
	public function getUserName()
	{
		if (isset($this->User)) {
			$this->userName = $this->User['last_name'] . ' ' . $this->User['name'];
		}
		return $this->userName;
	}


	/**
	 * получаем вопросы для опроса
	 * @return array
	 */
	public function getQuestions()
	{
		$query = "SELECT * FROM `os__statistics__" . $this->module . "_questions`";

		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$arr[] = $row;
			}
		}
		return $arr;
	}

	/**
	 * получаем статистику
	 */
	public function getResults()
	{
		return [];
	}

	/**
	 * запись статистики
	 *
	 * @param $results_id_str
	 * @param $message
	 */
	public function setResults($results_id_str, $message){

		$ids = [];
		foreach ($results_id_str as $answer) {
			$query = "INSERT INTO `os__statistics__" . $this->module . "_poll_results` SET";
			$query .= " `user_id` = ?";
			$query .= ", `user_name` = ?";
			$query .= ", `answer` = ?";
			$query .= ", `comment` = ?";


			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$this->getUserName();
			$stmt->bind_param('isss', $this->user_id, $this->userName, $answer, $message) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();

			$ids[] = $this->mysqli->insert_id;
			$stmt->close();
		}


		return $ids;
	}

}

?>

