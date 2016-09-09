<?php


/**
 * класс API для коммерческих предложений (в дальнейшем КП)
 * отвечает за манипуляции над КП
 * (создание, удаление а так же остальные штуки, связанные с КП)
 *
 * @author  	Alexey Kapitonov
 * @version 	06.09.2016 10:30
 */
class Commercial_offer  extends osGeneral
{
    private $imgType   = [];
    private $totalInfo = [];

    /**
     * содержит список зажатых и отжатых кнопок - настройки по умолчанию
     * настройки, которые применяются при отсутствии сохранённых настроек у юзера
     *
     * @var array
     */
    private $DefaultOptions = [
        'button_1' => true,     // пример заполнения
        'button_2' => true,     // пример заполнения
        'button_3' => true,     // пример заполнения
        'button_4' => false,    // пример заполнения
    ];

//    для перевода всех приложений в режим разработки раскоментировать и установить FALSE
//    protected $production = false;

    public 	$user_access = 0; 		    // user right (int)
    protected 	$user_id = 0;			// user id with base
    public 		$user = array();

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->DefaultOptions;
    } 		// authorised user info



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
    //	Коммерческое предложение
    /////////////////////////


    /**
     * get user access
     *
     * @param $id
     * @return int
     */
    public function get_user_access_Database_Int($id){
        $query = "SELECT * FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
        $result = $this->mysqli->query($query) or die($this->mysqli->error);
        $access = 0;
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $access = (int)$row['access'];
                $this->user = $row;
            }
        }
        return $access;
    }

}

/**
 * Class Position
 */
class Position extends Commercial_offer
{
    private $galleryImages = [];
    private $positionRows = [];
    private $services = [];
    private $totalInfo = [];

    public function __construct(){}
}

/**
 * Class PositionRows
 */
class PositionRows extends Position
{
    public function __construct(){}
}

/**
 * Class Gallery
 */
class Gallery extends Position
{
    public function __construct(){}
}





?>