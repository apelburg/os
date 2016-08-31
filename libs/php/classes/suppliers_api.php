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


//    private     $ip     = '46.19.190.26';
//    private     $port   = '8593';
    private     $ip     = '0.0.0.0';
    private     $port   = '8000';
    private     $format = 'json';


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
     * приводит данные запроса к API к надлежащему виду
     *
     * @param array $data
     * @return string
     */
    private function format_data_to_sending($data = []){
        // убираем дубли
        $data_new = [];

        foreach ($data as $key => $val ){
            if (is_array($val)){
                $data_new[$key] = [];
                $data_new[$key] = array_unique($val);
            }


        }
        return $data_new;
//        return json_encode($data_new,JSON_FORCE_OBJECT);
    }

    private function getUrl($data){
        return 'http://'.$this->ip.':'.$this->port.'/?format='.$this->format;
    }

    /**
     * метод получения данных по артикулам
     */
    protected function get_actual_prices_AJAX(){


        $data = $this->format_data_to_sending($_POST['data']);

        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
            CURLOPT_URL => $this->getUrl($data),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($myCurl);

        $responseJson = json_decode($response, true);
        curl_close($myCurl);
        $this->responseClass->addSimpleWindow($response.'<br>'.$this->getUrl($data));
        $this->responseClass->response['data'] = $responseJson;

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