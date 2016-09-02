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
//    protected   $production = false;

    protected 	$user_id        = 0;		// user id with base
    public 		$user           = array(); 	// authorised user info
    public 	    $user_access    = 0; 		// user right (int)


    private     $ip     = '46.19.190.26';
    private     $port   = '8593';

//    private     $ip     = '192.168.1.25';
//    private     $port   = '8000';

//    private     $ip     = '0.0.0.0';
//    private     $ip     = 'localhost';
//    private     $port   = '8000';
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
            if (is_array($val) /*&& $key == '37'*/){
                $data_new[$key] = [];
                $data_new[$key] = array_unique($this->crash_first_art($val));
            }
        }
        return $data_new;
//        return json_encode($data_new,JSON_FORCE_OBJECT);apt-get install
    }

    /**
     * тестовый метод для отладки вывода ошибок
     * добавляет несуществующий артикула, по которому API должен будет сгенерировать ошибку
     *
     * @param $arr
     * @return mixed
     */
    function crash_first_art($arr){
//        $arr[] = '6sdasad65456s';
        return $arr;
    }


    private function getUrl($supplier_key, $data){
        return 'http://'.$this->ip.':'.$this->port.'/?format='.$this->format.'&supplier='.$supplier_key.'&'.$data;
    }

    /**
     * сохранение новой информации о входящих ценах
     */
    protected function update_price_in_data_AJAX(){

//        $this->prod__window($this->printArr($_POST['data']));
        $update_type = (isset($_POST['update_type']))?$_POST['update_type']:'price_in';

        foreach ($_POST['data'] as $newData){
            $price_in =     $newData['price_in'];
            $price_out =    $newData['price_out'];
            $id =           $newData['id_dop_data'];

            $query = "UPDATE `".RT_DOP_DATA."` SET ";
            $query .= " price_in =? ";
            if ($update_type == 'all'){
                $query .= " , `price_out` =?";
                $query .= " WHERE `id` =?";
                $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
                $stmt->bind_param('ddi',$price_in,$price_out, $id ) or die($this->mysqli->error);
            }else{
                $query .= " WHERE `id` =?";
                $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
                $stmt->bind_param('di',$price_in, $id ) or die($this->mysqli->error);
            }


            $stmt->execute() or die($this->mysqli->error);
            $result = $stmt->get_result();

        }

        $this->responseClass->addMessage('Входящие цены успешно обновлены','successful_message',1000);

        $stmt->close();



    }

    private function convert_data_to_string($send_data){
//        return '["'.implode('","',$send_data).'"]';
        return implode('_',$send_data);
    }

    /**
     * метод получения данных по артикулам
     */
    protected function get_actual_prices_AJAX(){


        $data = $this->format_data_to_sending($_POST['data']);

        $responseJson = [];

        foreach ($data as $supplier_key => $send_data){
            $myCurl = curl_init();
            $url = $this->getUrl($supplier_key, 'art='.$this->convert_data_to_string($send_data));
            curl_setopt_array($myCurl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $responseApi = curl_exec($myCurl);
            curl_close($myCurl);

            $this->prod__window('--'.$url.'--<br>','system_message', 500000);

            $response[$supplier_key] = json_decode($responseApi, true);
        }


        # проверяем на ошибки
        $this->check_on_errors($response);

        $this->responseClass->response['data'] = $response;
    }


    /**
     * проверяет наличие сообщений об ошибках
     * вызывает метод добавления сообщений об ошибок
     */
    private function check_on_errors($data = []){
        if (is_array($data)){
            if (isset($data['errors'])){
                $this->add_message_to_user($data['errors']);
            }
            foreach ($data as $arr){
                if(is_array($arr)){
                    $this->check_on_errors($arr);
                }
            }
        }
    }

    /**
     * добавляет сообщение об ошибке для пользователя

     * @param array $error
     */
    private function add_message_to_user($error = []){
        if (isset($error['name']) && isset($error['text'])){

            $message = $error['name'].'<br><div style="font-size:12px; text-transform:lowercase">'.$error['text'].'</div>';

            $this->responseClass->addMessage($message,'error_message',10000);
        }
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