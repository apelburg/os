<?php

class Position_general_Class extends aplStdAJAXMethod{
	// тип продукта
	protected $type_product;

	// id юзера
	protected $user_id;

	// допуски пользователя
	protected $user_access;

	// права на редактирование поля определяются внутри 
	// некоторых функций 
	protected $edit_admin;
	protected $edit_men;
	protected $edit_snab;

	// id позиции
	protected $id_position;

	// экземпляр класса форм
	public $FORM;
	// экземпляр класса продукции каталог
	public $POSITION_CATALOG;



	
	function __construct(){
        $this->mysqli = $this->db();

		$this->user_id = $_SESSION['access']['user_id'];

		
		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		$this->id_position = isset($_GET['id'])?$_GET['id']:0;
		
		// экземпляр класса продукции каталог
		$this->POSITION_CATALOG = new Position_catalog($this->user_access);

		// экземпляр класса форм
		$this->FORM = new Forms();

        if(isset($_POST['AJAX'])){
            $this->_AJAX_($_POST['AJAX']);
            $this->responseClass->response['data']['access'] = $this->getUserAccess();
            $this->responseClass->response['data']['id'] = $this->getUserId();
        }

        // calls ajax methods from GET
        if(isset($_GET['AJAX'])){
            $this->_AJAX_($_GET['AJAX']);
            $this->responseClass->response['data']['access'] = $this->getUserAccess();
            $this->responseClass->response['data']['id'] = $this->getUserId();
        }
	}

	

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = (int)$user_id;
    }

    /**
     * @return int
     */
    public function getUserAccess()
    {
        return $this->user_access;
    }

    /**
     * @param int $user_access
     */
    public function setUserAccess($user_access)
    {
        $this->user_access = (int)$user_access;
    }

    /**
     * запрос прав пользователя
     *
     * @param $id
     * @return int
     */
    public function get_user_access_Database_Int($id){
        $this->db();
        $query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id = '".(int)$id."'";
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

    /**
     * сохранение информации по резерву
     */
    protected function reserv_save_AJAX(){
        $query = "UPDATE `".RT_MAIN_ROWS."` SET";
        $query .= "`number_rezerv` =  '".base64_encode($_POST['value'])."'";
        $query .= "WHERE  `id` ='".$_POST['row_id']."'";
        $result = $this->mysqli->query($query) or die($this->mysqli->error);
    }




	/////////////////  AJAX METHODs  ///////////////// 

	protected function save_tz_text_AJAX(){
		global $mysqli;
		$query = "UPDATE `".RT_DOP_USLUGI."` SET `tz`='".base64_encode($_POST['tz'])."' WHERE `id`='".$_POST['rt_dop_uslugi_id']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);

		// AJAX options			
		if(trim($_POST['tz'])==''){
			$function = 'save_empty_tz_text_AJAX';
			if(isset($_POST['increment_id'])){
				$options['increment_id'] = $_POST['increment_id'];
			}

		}else{
			$function = 'save_tz_text_AJAX';
			if(isset($_POST['increment_id'])){
				$options['increment_id'] = $_POST['increment_id'];
			}

		}	
		// AJAX options prepare
		
		$string__a = mb_substr($_POST['tz'], 0, 80);
		if($_POST['tz'] != $string__a)$string__a .= '...';
		$options['html'] = $string__a;
			
		$this->responseClass->addResponseFunction($function,$options);

	}

	// редактирование темы в запросе
	protected function save_query_theme_AJAX(){
		global $mysqli;
		$query = "UPDATE `".RT_LIST."` SET";
		$query .= " `theme` = '".$_POST['value']."'";
		$query .= " WHERE id='".(int)$_POST['row_id']."'";
		$result = $mysqli->query($query) or die($mysqli->error);
	}







	// форматируем денежный формат + округляем
	public function round_money($num){
		return number_format(round($num, 2), 2, '.', '');
	}
	// подсчёт процентов наценки
	public function get_percent_Int($price_in,$price_out){
		$per = ($price_in!= 0)?$price_in:0.09;
		$percent = round((($price_out-$price_in)*100/$per),2);
		return $percent;
	}

	// отдаёт $html распечатанного массива
	protected function printArr($arr){
		ob_start();
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
		$content = ob_get_contents();
		ob_get_clean();
		
		return $content;
	}

	/////////////////   AJAX  END   ///////////////// 

}