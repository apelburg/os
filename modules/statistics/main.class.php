<?php

class Statistics extends aplStdAJAXMethod
{
	function __construct($stats_name = '')
	{
		$this->connectToStats($stats_name);
	}

	/**
	 * подключение к статистике
	 *
	 * @param $stats_name
	 * @return bool
	 */
	public function connectToStats($stats_name){
		# попытка открыть файл с классом данной статистикаи
		if(fopen($_SERVER['DOCUMENT_ROOT']."/os/modules/statistics/$stats_name.class.php", "r")){
			include_once "$stats_name.class.php";
			$Class = ucfirst($stats_name);
			# запускаем класс статистики
			$this->stats = new $Class();
			return false;
		}
		return false;
	}
	
	public function setResults($results_id_str, $message){
		if (isset($this->stats)){
			return $this->stats->setResults($results_id_str, $message);
		}
		return [];
	}

	/**
	 * запрос вопросов по статистике
	 *
	 * @return array
	 */
	public function getQuestions(){
		if (isset($this->stats)){
			return $this->stats->getQuestions();
		}
		return [];
	}

	/**
	 * создание вопроса
	 *
	 * @param $stats_name
	 * @param $question
	 */
	public function createQuestions($stats_name, $question_name){

	}

	/**
	 * удаление вопроса
	 *
	 * @param $stats_name
	 * @param $id
	 */
	public function removeQuestions($stats_name, $id){

	}

	/**
	 * создание статистики
	 *
	 * @return bool
	 */
	function createStats($stats_name){
		/**
		 * в разработке
		 */
		return false;
	}

	/**
	 * удаление статистики
	 *
	 * @param $id
	 * @return int
	 */
	public function removeStats($stats_name){
		/**
		 * в разработке
		 */
		return false;
	}

	/**
	 * получение прав пользователя
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

