<?php

/**
 * Class SuppliersApi
 *
 * @author  	Alexey Kapitonov
 * @version 	31.08.2016
 */
class SuppliersApi   extends aplStdAJAXMethod
{
    // для перевода всех приложений в режим разработки раскоментировать и установить FALSE
    protected   $production = false;

    protected 	$user_id        = 0;		// user id with base
    public 		$user           = array(); 	// authorised user info
    public 	    $user_access    = 0; 		// user right (int)

    public function __construct()
    {
        // подключаемся к базе
        $this->db();

        $this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

        // получаем права
        if ($this->user_id > 0){
            $this->user_access = $this->get_user_access_Database_Int($this->user_id);
        }

        // calls ajax methods from POST
        if(isset($_POST['AJAX'])){
            $this->_AJAX_($_POST['AJAX']);
            $this->responseClass->response['data']['access'] =  $this->user_access;
            $this->responseClass->response['data']['id'] =      $this->user_id;
        }

        // calls ajax methods from GET
        ## the data GET --- on debag time !!!
        if(isset($_GET['AJAX'])){
            $this->_AJAX_($_GET['AJAX']);
        }
    }

    /**
     * метод получения данных по артикулам
     */
    protected function get_actual_prices_AJAX(){
        $this->responseClass->addMessage('test_message');

    }

    /**
     * get user access
     *
     * @param $id
     * @return int
     */
    public function get_user_access_Database_Int($id){
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
}

?>