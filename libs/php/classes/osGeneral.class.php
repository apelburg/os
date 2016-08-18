<?php

include_once '../libs/php/classes/aplStdClass.php';

/**
 * Class osGeneral
 *
 * содержит глобальные AJAX запросы по всей ОС
 * наследует методы aplStdAJAXMethod
 *
 */
class osGeneral extends aplStdAJAXMethod{
//    protected $production = false;

    // пример JS запроса
    /*
       $.post('', {
       AJAX: 'testRequest',
       id: 155654

       }, function(data, textStatus, xhr) {
           standard_response_handler(data);
       },'json')
    */

    /**
     * запрос вопросов для блока статистики
     */
    protected function get_stats_questions_AJAX(){
        include_once $_SERVER['DOCUMENT_ROOT'].'/os/modules/statistics/main.class.php';
        $Statistic = new Statistics($_POST['name']);
        $this->responseClass->response['data']['stats'] = $Statistic->getQuestions();
    }

    /**
     * сохраняем ответы на вопросы по статистике
     */
    protected function save_stats_answer_AJAX(){
        include_once $_SERVER['DOCUMENT_ROOT'].'/os/modules/statistics/main.class.php';
        $Statistic = new Statistics($_POST['name']);

        $this->responseClass->response['data']['stats'] = $Statistic->setResults($_POST['statistics'],$_POST['message']);
    }

    /**
     *	 client search
     *
     *	@author  	Alexey Kapitonov
     *	@version 	00:56 23.01.2016
     */
    protected function shearch_client_autocomlete_AJAX(){

        $query="SELECT * FROM `".CLIENTS_TBL."`  WHERE `company` LIKE ?";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $search = '%'.$_POST['search'].'%';
        $stmt->bind_param('s', $search) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();

        $response = array();

        $i=0;
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                // $response[] = $row['company'];
                $response[$i]['label'] = $row['company'];
                $response[$i]['value'] = $row['company'];
                $response[$i]['href'] = $_SERVER['REQUEST_URI'].'&client_id='.$row['id'];
                $response[$i++]['desc'] = $row['id'];
            }
        }
        echo json_encode($response);
        exit;
    }
    /**
     *	 supplier search
     *
     *	@author  	Alexey Kapitonov
     *	@version 	00:56 23.01.2016
     */
    protected function shearch_supplier_autocomlete_AJAX(){
        $query="SELECT * FROM `".SUPPLIERS_TBL."`  WHERE `nickName` LIKE ?";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $search = '%'.$_POST['search'].'%';
        $stmt->bind_param('s', $search) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();
        $response = array();

        $i=0;
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                // $response[] = $row['company'];
                $response[$i]['label'] = $row['nickName'];
                $response[$i]['value'] = $row['nickName'];
                $response[$i]['href'] = $_SERVER['REQUEST_URI'].'&supplier_id='.$row['id'];
                $response[$i++]['desc'] = $row['id'];
            }
        }
        echo json_encode($response);
        exit;
    }
    /**
     *	поиск по реквизитам поставщика
     *
     *	@author  	Alexey Kapitonov
     *	@version 	12:03 01.07.2016
     */
    protected function shearch_supplier_requsit_autocomlete_AJAX(){
        $query="SELECT * FROM `".SUPPLIER_REQUISITES_TBL."`  WHERE `company` LIKE ? OR `comp_full_name` LIKE ? ";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $search = '%'.$_POST['search'].'%';
        $stmt->bind_param('ss', $search,$search) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();
        $response = array();

        $i=0;
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                // $response[] = $row['company'];
                $response[$i]['label'] = $row['company'];
                $response[$i]['value'] = $row['company'];
                $response[$i]['href'] = $_SERVER['REQUEST_URI'].'&supplier_id='.$row['id'];
                $response[$i++]['desc'] = $row['id'];
            }
        }
        echo json_encode($response);
        exit;
    }

}

?>