<?php
/**
 * Class Supplier
 *
 * @author  	Алексей Капитонов
 * @version 	10:39 30.06.2016
 */
class Supplier extends aplStdAJAXMethod{

	/**
	 * установив флаг на FALSE - вы отмените некоторые строгие ограничения
	 * и войдете в режим тестирования
	 *
	 * @var bool
	 */
	protected 	$production = true;

	/**
	 * содержит название и пути к изображениям для каждого типа контактных данных
	 *
	 * @var array
	 */
	static $array_img = array(
		'email' 	=>	'<img src="skins/images/img_design/social_icon1.png" >',
		'skype' 	=> 	'<img src="skins/images/img_design/social_icon2.png" >',
		'isq' 		=> 	'<img src="skins/images/img_design/social_icon3.png" >',
		'twitter' 	=> 	'<img src="skins/images/img_design/social_icon4.png" >',
		'fb' 		=> 	'<img src="skins/images/img_design/social_icon5.png" >',
		'vk' 		=> 	'<img src="skins/images/img_design/social_icon6.png" >',
		'other' 	=> 	'<img src="skins/images/img_design/social_icon7.png" >'
		);


	/**
	 * Supplier constructor.
	 * @param $id
     */
	public function __construct($id = 0) {

        $this->db();

        $this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

        $this->User = $this->getUserDatabase($this->user_id);

        $this->user_access = $this->getRights();


		## данные POST
		if(isset($_POST['AJAX'])){

			$this->user_last_name = $this->User['last_name'];
			$this->user_name = $this->User['name'];

            $this->_AJAX_($_POST['AJAX']);
		}


		if($id > 0){
			$this->get_object($id);
		}
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
     * получаем пользователя
     *
     * @param $id_arr
     * @return array
     */
    protected function getUserDatabase($id)
    {
        return parent::getUserDatabase($id);
    }

	protected function get__suppliers_persons(){
		global $mysqli;
		$query = "SELECT * FROM `".SUPPLIER_PERSON_REQ_TBL."`";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[$row['id']] = $row;
			}
		}
		return $arr;
	}
	private function edit_requsits_show_person($requisites_id){
		// array
		// лица (контрагенты) имеющие право подписи  для реквизитов в массиве
		global $mysqli;
		$arr = array();
		$query = "SELECT * FROM `".SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` = '".$requisites_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	private function edit_requsits_show_person_all($arr,$supplier_id){
		foreach ($arr as $key => $contact) {
			$get__clients_persons_for_requisites = $this->get__clients_persons_for_requisites($contact['post_id']);
			include('./skins/tpl/suppliers/edit_requsits_show_person.tpl');
		}
	}

	private function get__clients_persons_for_requisites($type){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_PERSON_REQ_TBL."`";
		$str = "<option value=\"0\">Выберите должность...</option>".PHP_EOL;
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$str .= "<option value=\"".$row['id']."\" ".(($type==$row['id'])?'selected':'').">".$row['position']."</option>".PHP_EOL;
			}
		}
		return $str;
	}
    /**
     * запрос на создание поставщика
     */
    protected function create_supplier_AJAX(){
//        $this->responseClass->addMessage('Добавление поставщика временно не доступно','error_message',1500);

        $nickName = $_POST['nickName'];
        $dop_info = $_POST['dop_info'];
        $fullName = $_POST['fullName'];


        if ($this->search_name($nickName) > 0 ) {
            $this->responseClass->addMessage('Сокращённое название данной организации уже содержится в базе ОС');
            return;
        }
        if ($this->search_name($fullName) > 0) {
            $this->responseClass->addMessage('Полное название данной организации уже содержится в базе ОС');
            return;
        }

        # заводим строку поставщика в базе
        $insert_id = $this->insert_supplier_in_database($nickName,$fullName,$dop_info);

        # отправляем юзера на страницу с поставщиком
        $option['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/os/?page=suppliers&section=suppliers_data&suppliers_id='.$insert_id;
        $this->responseClass->addResponseFunction('location_href',$option);


        $userName = "";
        # отправляем сообщение менеджерам
        include_once $_SERVER['DOCUMENT_ROOT'].'/os/libs/php/classes/invoice.class.php';
        $Invoice = new InvoiceNotify();
        $subject = 'В базу добавлен новый поставщик';


        $message = 'В базу поставщиков добавлен новый поставщик '.$nickName;
        $href = 'http://www.apelburg.ru/os/?page=suppliers&section=suppliers_data&suppliers_id='.$insert_id;
        # подгружаем шаблон
        ob_start();

        include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
        $html = ob_get_contents();
        ob_get_clean();

        return mail('sales@apelburg.ru',
//        return mail('kapitonoval2012@gmail.com',
            $subject,
            $html,
            "From: snab@apelburg.ru\r\n"
            ."Content-type: text/html; charset=utf-8\r\n"
            ."X-Mailer: PHP mail script"
        );




    }

    /**
     * заведение нового поставщика в базу
     *
     * @param $nickName
     * @param $fullName
     * @param string $dopInfo
     * @return mixed
     */
    private function insert_supplier_in_database($nickName,$fullName,$dopInfo = ''){
        $query = "INSERT INTO ".SUPPLIERS_TBL." SET ";
        $query .= " `nickName`=? ,";
        $query .= " `fullName`=? ,";
        $query .= " `dop_info`=?";
        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $stmt->bind_param('sss', $nickName,$fullName,$dopInfo) or die($this->mysqli->error);

        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();

        return $this->mysqli->insert_id;
    }

	protected function update_requisites_AJAX() {
		$query = "
				UPDATE  `" . SUPPLIER_REQUISITES_TBL . "` SET
				`supplier_id`='" . $_POST['client_id'] . "', 
				`company`='" . $_POST['company'] . "', 
				`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
				`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
				`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
				`inn`='" . $_POST['form_data']['inn'] . "', 
				`kpp`='" . $_POST['form_data']['kpp'] . "', 
				`bank`='" . $_POST['form_data']['bank'] . "', 
				`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
				`r_account`='" . $_POST['form_data']['r_account'] . "', 
				`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
				`ogrn`='" . $_POST['form_data']['ogrn'] . "', 
			    `bik`='" . $_POST['form_data']['bik'] . "', 
				`okpo`='" . $_POST['form_data']['okpo'] . "', 
				`dop_info`='" . $_POST['form_data']['dop_info'] . "' WHERE id = '" . $_POST['requesit_id'] . "';";


		$get__clients_persons_arr = $this->get__suppliers_persons();
		foreach ($_POST['form_data']['managment1'] as $key => $val) {
			if (trim($val['id']) != "") {
				$query.= "UPDATE  `" . SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
				$query.= "`requisites_id` =  '" . $val['requisites_id'] . "',";
				$query.= "`type` =  '" . $val['type'] . "',";
				$query.= "`post_id` =  '" . $val['post_id'] . "',";
				if(isset($get__clients_persons_arr[$val['post_id']])){
					$query.= "`position` =  '" . $get__clients_persons_arr[$val['post_id']]['position'] . "',";
					$query.= "`position_in_padeg` =  '" . $get__clients_persons_arr[$val['post_id']]['position_in_padeg'] . "',";
				}
				$query.= "`basic_doc` =  '" . $val['basic_doc'] . "',";
				$query.= "`name` =  '" . $val['name'] . "',";
				$query.= "`name_in_padeg` =  '" . $val['name_in_padeg'] . "',";
				$query.= "`acting` =  '" . $val['acting'] . "'";
				$query.= " WHERE  `id` ='" . $val['id'] . "'; ";
			}else {

				if($val['acting'] == 1){
					$query.= "UPDATE  `" . SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
					$query.= "`acting` =  '0'";
					$query.= " WHERE  `requisites_id` ='" . $val['requisites_id'] . "'; ";
				}

				$query.= "INSERT INTO  `" . SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL . "` SET ";
				$query.= "`requisites_id` =  '" . $val['requisites_id'] . "',
						`type` =  '" . $val['type'] . "',
						`post_id` =  '" . $val['post_id'] . "',
						`basic_doc` =  '" . $val['basic_doc'] . "',
						`name` =  '" . $val['name'] . "',
						`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
						`acting` =  '" . $val['acting'] . "';";
			}
		}
		$result = $this->mysqli->multi_query($query) or die($this->mysqli->error);



		$this->responseClass->addMessage("Реквизиты успешно обновлены",'successful_message',100);
	}
	/**
	 * окно редактирования существующих реквизитов
	 */
	protected function edit_requesit_AJAX() {
		$query = "SELECT * FROM `" . SUPPLIER_REQUISITES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
		$requesit = [];


		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$requesit = $row;
			}
		}

		// получаем контактные лица для реквизитов
		$supplier_id = $_GET['suppliers_id'];


		ob_start();
		include ('./skins/tpl/suppliers/edit_requsits.tpl');
		$html = ob_get_contents();
		ob_clean();
//
//        $this->insert_empty_row(SUPPLIER_REQUISITES_TBL,['company'=>'НОВАЯ КОМПАНИЯ','supplier_id'=>(int)$_GET['suppliers_id']]);
//        $html = 'Создание новой пустой строки';
		$this->responseClass->response['data']['window'] = base64_encode($html);
	}
	/**
	 * запись данных из форрмы создания новых реквизитов
	 */
    protected function create_new_requisites_AJAX() {
        $query = "INSERT INTO `" . SUPPLIER_REQUISITES_TBL . "` SET id = '" . $_POST['requesit_id'] . "',";
        $query .= "`supplier_id`='" . (int)$_GET['suppliers_id'] . "', 
				`company`='" . $_POST['company'] . "', 
				`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
				`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
				`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
				`inn`='" . $_POST['form_data']['inn'] . "', 
				`kpp`='" . $_POST['form_data']['kpp'] . "', 
				`bank`='" . $_POST['form_data']['bank'] . "', 
				`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
				`r_account`='" . $_POST['form_data']['r_account'] . "', 
				`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
				`ogrn`='" . $_POST['form_data']['ogrn'] . "', 
			    `bik`='" . $_POST['form_data']['bik'] . "', 
				`okpo`='" . $_POST['form_data']['okpo'] . "', 
				`dop_info`='" . $_POST['form_data']['dop_info'] . "'
				";
        $result = $this->mysqli->query($query) or die($this->mysqli->error);

        // запоминаем id созданной записи
        $req_new_id = $this->mysqli->insert_id;

        if (isset($_POST['form_data']['managment1'])) {
            $query = "";
            foreach ($_POST['form_data']['managment1'] as $key => $val) {
                $query.= "INSERT INTO  `" . SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
				$query .= "`requisites_id` =  '" . $req_new_id . "',
						`type` =  '" . $val['type'] . "',
						`post_id` =  '" . $val['post_id'] . "',
						`basic_doc` =  '" . $val['basic_doc'] . "',
						`name` =  '" . $val['name'] . "',
						`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
						`acting` =  '" . $val['acting'] . "';";
            }

            $result = $this->mysqli->multi_query($query) or die($this->mysqli->error);
        }

        $this->responseClass->addMessage("Реквизиты успешно добавлены",'successful_message',100);
    }


    /**
     * окно заведения новых реквизитов
     */
    protected function create_requesit_AJAX() {
        ob_start();
        include ('./skins/tpl/suppliers/new_requsits.tpl');
        $html = ob_get_contents();
        ob_clean();
//
//        $this->insert_empty_row(SUPPLIER_REQUISITES_TBL,['company'=>'НОВАЯ КОМПАНИЯ','supplier_id'=>(int)$_GET['suppliers_id']]);
//        $html = 'Создание новой пустой строки';
        $this->responseClass->response['data']['window'] = base64_encode($html);
    }

    /**
	 * запрос списка реквизитов
	 */
	protected function get_requsit_data_AJAX(){
        $query = "SELECT * FROM `".SUPPLIER_REQUISITES_TBL."` WHERE `supplier_id` =? ";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $stmt->bind_param('i', $_GET['suppliers_id']) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();

        $data = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $data[] = $row;
            }
        }
        $this->responseClass->response['data']['access'] = $this->user_access;
        $this->responseClass->response['data']['id'] = $this->user_id;
		$this->responseClass->response['data']['requisites'] = $data;
	}

    /**
     * удаление реквизитов
     */
    protected function delete_requsit_row_AJAX(){
		# 1. удаление строки реквизитов
		$this->delete_row_from_table(SUPPLIER_REQUISITES_TBL, (int)$_POST['id']);

		# 2. удаление строк контактных лиц по реквизитам
		$query = "DELETE FROM ".SUPPLIER_REQUISITES_MANAGMENT_FACES_TBL." WHERE `id`= ?";
		$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
		$stmt->bind_param('i', $_POST['id']) or die($this->mysqli->error);
		$stmt->execute() or die($this->mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

        $this->responseClass->addMessage("Реквизиты успешно удалены",'successful_message',100);
        $this->logining('удалил реквизиты у поставщика');
    }

    /**
     * сохранение в лог
     *
     * @param string $message
     */
    private function logining($message = 'внес изменения в'){
        $tbl = "SUPPLIERS_TBL";
        $id_row = $_POST['id'];
        //-- START -- //  логирование
        $supplier_id = $_GET['suppliers_id'];
        $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название поставщика
        $user_n = $this->user_name.' '.$this->user_last_name;

        $text_history = $user_n.' '.$message.' '.$supplier_name_i;
        Supplier::history_edit_type($supplier_id, $this->user_id, $text_history ,'delete_supplier_cont_face',$tbl,$_POST,$id_row);
        //-- END -- //
    }



	/**
	 * редактирование дополнительной информации по поставщику
	 */
	protected function edit_client_dop_information_AJAX(){
        $this->logining('обновил информацию по поставщику');

		# пока что без папки поставщика
		/*$query = "UPDATE  `".SUPPLIERS_TBL."` SET
		`dop_info` =  '".$_POST['dop_info']."',
		`ftp_folder` =  '".$_POST['ftp_folder']."' WHERE  `id` ='".$_POST['id']."';";*/
		$query = "UPDATE  `".SUPPLIERS_TBL."` SET  
		`dop_info` =  '".$_POST['dop_info']."' WHERE  `id` ='".$_POST['id']."';";

		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		echo '{
	       "response":"1",
	       "text":"Данные успешно обновлены"
	      }';
		exit;
	}

	/**
	 * удалиление поле с доп. контактной информацией
	 */
	protected function delete_dop_cont_row_AJAX(){
		$id_row = $_POST['id'];
		$tbl = "CONT_FACES_CONTACT_INFO_TBL";
		//-- START -- //  логирование
		$supplier_id = $_GET['suppliers_id'];
		$user_n = $this->user_name.' '.$this->user_last_name;
	    $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента


	    $text_history = $user_n.' удалил поле с доп. контактной информацией (email,www, VK)  '.$supplier_name_i;

	    Supplier::history_delete_type($supplier_id, $this->user_id, $text_history ,'delete_supplier_cont_face',$tbl,$_POST,$id_row);
	    //-- END -- //

		$query = "DELETE FROM `".constant($tbl)."` WHERE `id` = '".$id_row."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		// сообщение
		$html = 'Данные удалены.';
		$this->responseClass->addMessage($html,'system_message');
	}

	/**
	  *	удаление общей адресной строки
	  *
	  *	@author  	Alexey Kapitonov
	  *	@version 	02:29 11.01.2016
	  */
	protected function delete_adress_row_AJAX(){
		$supplier_id = $_GET['suppliers_id'];
		$user_n = $this->user_name.' '.$this->user_last_name;
		$id_row = $_POST['id_row'];
		$tbl = $_POST['tbl'];
		//-- START -- //  логирование
	    $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента

	    $text_history = $user_n.' удалил поле адрес у поставщика '.$supplier_name_i;
	    Supplier::history_delete_type($supplier_id,$this->user_id, $text_history ,'delete_adress_row',$tbl,$_POST,$id_row);
	    //-- END -- //

		$query = "DELETE FROM ".constant($tbl)." WHERE `id`= '".$id_row."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		// сообщение
		$html = 'Данные удалены.';
		$this->responseClass->addMessage($html,'system_message');
	}

	/**
	  *	удаление контактного лица
	  *
	  *	@author  	Alexey Kapitonov
	  *	@version 	02:35 11.01.2016
	  */
	protected function delete_cont_face_row_AJAX(){
		$supplier_id = $_GET['suppliers_id'];
		$user_n = $this->user_name.' '.$this->user_last_name;

		$id_row = $_POST['id'];
		$tbl = "SUPPLIERS_CONT_FACES_TBL";
		//-- START -- //  логирование
	    $supplier_name_i = Supplier::get_supplier_name($supplier_id); // получаем название клиента

	    $text_history = $user_n.' удалил контактное лицо у поставщика '.$supplier_name_i;
	    Supplier::history_delete_type($supplier_id,$this->user_id, $text_history ,'delete_supplier_cont_face',$tbl,$_POST,$id_row);
	    //-- END -- //

		$query = "DELETE FROM ".constant($tbl)." WHERE `id`= '".$id_row."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		// echo $query;
		// сообщение
		$html = 'Контактное лицо удалено.';
		$this->responseClass->addMessage($html,'system_message');
	}


	/**
	  *	собираем объект поставщика
	  *
	  *	@param 		supplier_id
	  *	@author  	Алексей Капитонов
	  *	@version 	00:41 11.01.2016
	  */
	private function get_object($id){
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['nickName'];
			}
		}
		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("SUPPLIERS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
	}

	/**
	  *	получаем полный список поставщиков
	  *
	  *	@author  	Алексей Капитонов
	  *	@version 	00:40 11.01.2016
	  */
	static function get_all_suppliers_Database_Array(){
		global $mysqli;
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` GROUP BY `nickName` ASC";
		$result = $mysqli->query($query) or die($mysqli->error);
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}

		return $arr;

	}

	/**
	 * информация по контактным лицам
	 *
	 * @param $tbl
	 * @param $parent_id
	 * @return array
	 */
	public function get_contact_info($tbl,$parent_id){
		$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$contact = array('phone'=>'','other'=>'');//инициализируем массив
		$contacts = array();		
		$contact['phone'] = '<table class="table_phone_contact_information"></table>';
		$contact['other'] = '<table class="table_other_contact_information"></table>';
		
		//  
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
			$contact['phone'] = self::get_contact_row($contacts, 'phone',self::$array_img);
			$contact['other'] = self::get_contact_row($contacts, 'other',self::$array_img);
		}
		return $contact;
	}

	/**
	 * оповещение разработчиков об удалении поставщика
	 *
	 * @param $supplier_id
	 * @param $username
	 * @return int
	 */
	static function removal_request($supplier_id,$username){
		$mail = new Mail();
		$mail->add_bcc('kapitonoval2012@gmail.com');
		$to = 'premier22@yandex.ru';
		$from = 'Оналайн Сервис <online_service@apelburg.ru>';
		$subject = 'Заявка на удаление поставщика';
	    $message = 'Прошу удалить поставщика № '.$supplier_id.' '.$username.'';
		$out_data = $mail->send($to,$from,$subject,$message);
		return 1;		
	}

    /**
     * поиск поставщика по краткому и полному имени
     *
     * @param $name
     * @return mixed
     */
	private function search_name($name){
		$this->db();

		$query = "SELECT `id` FROM `".SUPPLIERS_TBL."` WHERE `fullName` = ? OR `nickName` =? ";

        $stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
        $stmt->bind_param('ss', $name, $name) or die($this->mysqli->error);
        $stmt->execute() or die($this->mysqli->error);
        $result = $stmt->get_result();
        $stmt->close();

		$row_cnt = $result->num_rows;
		return $row_cnt;
	}

	/**
	 * заведение нового поставщика
	 *
	 * @param $name
	 * @param string $fullname
	 * @param string $dop_info
	 * @return mixed
	 */
	static function create($name,$fullname = '',$dop_info = ''){
		global $mysqli;
		$query = "INSERT INTO `".SUPPLIERS_TBL."` SET";
		$query .= " `nickName` =?";		$type = 's';
		$query .= ", `fullName` =?";	$type .= 's';
		$query .= ", `dop_info` =?";	$type .= 's';

		$stmt = $mysqli->prepare($query) or die($mysqli->error);
		$stmt->bind_param($type, $name, $fullname, $dop_info) or die($mysqli->error);
		$stmt->execute() or die($mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

        $insert_id = $mysqli->insert_id;
//        $insert_id = 2;

        $userName = "";
        # отправляем сообщение менеджерам
        include_once $_SERVER['DOCUMENT_ROOT'].'/os/libs/php/classes/invoice.class.php';
        $Invoice = new InvoiceNotify();
        $subject = 'В базу добавлен новый поставщик';


        $message = 'В базу поставщиков добавлен новый поставщик '.$name;
        $href = 'http://www.apelburg.ru/os/?page=suppliers&section=suppliers_data&suppliers_id='.$insert_id;
        # подгружаем шаблон
        ob_start();
        // include_once '/var/www/admin/data/www/apelburg.ru/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
        include_once $_SERVER['DOCUMENT_ROOT'].'/os/skins/tpl/invoice/notifi_templates/create_invoice.tpl';
        $html = ob_get_contents();
        ob_get_clean();

        return mail('sales@apelburg.ru',
            $subject,
            $html,
            "From: snab@apelburg.ru\r\n"
            ."Content-type: text/html; charset=utf-8\r\n"
            ."X-Mailer: PHP mail script"
        );

		return $insert_id;
	}

	/**
	 * выды деятельности
	 *
	 * @param $supplier_id
	 * @return array
	 * @version 	00:21 11.01.2016
	 */
	static function get_activities($supplier_id){
		global $mysqli;
		$query = "
			SELECT  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` . * ,  `".SUPPLIERS_ACTIVITIES_TBL."`.`name` AS  `name` 
			FROM  `".SUPPLIERS_ACTIVITIES_TBL."` 
			INNER JOIN  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` ON  `".SUPPLIERS_ACTIVITIES_TBL."`.`id` =  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`activity_id` 
			WHERE  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`supplier_id` =?";

		$stmt = $mysqli->prepare($query) or die($mysqli->error);
		$stmt->bind_param('i', $supplier_id) or die($mysqli->error);
		$stmt->execute() or die($mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;		
	}

	/**
	 * адреса
	 *
	 * @param $id
	 * @return array
	 * @version 	01:23 12.01.2016
	 */
	static function get_addres($id){
		global $mysqli;
		$query = "SELECT * FROM  `".CLIENT_ADRES_TBL."` WHERE `parent_id` =? AND `table_name` = 'SUPPLIERS_TBL'";

		$stmt = $mysqli->prepare($query) or die($mysqli->error);
		$stmt->bind_param('i', $id) or die($mysqli->error);
		$stmt->execute() or die($mysqli->error);
		$result = $stmt->get_result();
		$stmt->close();

		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	/**
	 * контакты
	 *
	 * @param $contact_company
	 * @param $type
	 * @param $array_dop_contacts_img
	 * @return string|void
	 */
	static function get_contact_row($contact_company, $type, $array_dop_contacts_img){
		
		if(isset($type) && $type == "phone"){
			$i=1;
			$str = '<table class="table_phone_contact_information">';
			if(empty($contact_company)){return;}

			foreach($contact_company as $k=>$v){				
				if($v['type'] == $type){
					$str .= "<tr><td class='td_phone'>".$v['telephone_type']." ".$i."</td><td><div  class='del_text' data-adress-id=".$v['id'].">".$v['contact'].((trim($v['dop_phone'])!=0)?" доп.".$v['dop_phone']:'')."</div></td></tr>";	
					$i++;
				}
			}
			$str .= "</table>";	
			// echo $str;
			return $str;
		}else{

			$str = '<table class="table_other_contact_information">';
			foreach($contact_company as $k=>$v){
			if(isset($array_dop_contacts_img[trim($v['type'])])){
				$icon = $array_dop_contacts_img[trim($v['type'])];
			}else{
				@$icon = $array_dop_contacts_img['other'];
			}
				if($v['type'] != 'phone'){
					$str .= "<tr><td class='td_icons'>".$icon."</td><td><div   class='del_text' data-adress-id=".$v['id'].">".$v['contact']."<div></td></tr>";	
				}
			}
			$str .= "</table>";	
			return $str;		
		}			
	}

	/**
	 * рейтинг
	 *
	 * @param $supplier_id
	 * @return string
	 */
	static function get_reiting($supplier_id){
		global $mysqli;
		$html = '';
		// SUPPLIERS_RATINGS_TBL subject_id
		$query = "SELECT * FROM `".SUPPLIERS_RATINGS_TBL."` WHERE `subject_id` = '".(int)$supplier_id."'";
		// $query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$supplier_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$rate = 0;
		if($result->num_rows > 0){
			$sum = 0;
			$i = 0;			
			while($row = $result->fetch_assoc()){
				$sum = $row['rate'];
				$i++;
			}
			$rate = floor ($sum/$i);
		}		
		$arr[0] = array('5','0');
		$arr[1] = array('5','5');
		$arr[2] = array('5','10');
		$arr[3] = array('5','15');
		$arr[4] = array('5','20');
		$arr[5] = array('5','25');

		$html = '<div id="rate_1" data-id="'.$supplier_id.'">
			<input type="hidden" name="review_count" value="'.$arr[$rate]['0'].'" />
			<input type="hidden" name="review_rate" value="'.$arr[$rate]['1'].'" />
		</div>';
		return $html;
	}

	/**
	 * контактные лица
	 *
	 * @param $id
	 * @return array
	 */
	static function cont_faces($id){
		global $mysqli;
		$query = "SELECT * FROM `".SUPPLIERS_CONT_FACES_TBL."` WHERE `supplier_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			//получаем телефоны, email, vk и т.д.	
			/*$arr = self::get_contact_info("CLIENT_CONT_FACES_TBL",$id);
			
			$array['phone'] = (isset($arr['phone']))?$arr['phone']:''; 
			$array['other'] = (isset($arr['other']))?$arr['other']:'';*/
			return $array;
		}		
		return $array;
	}



	/**
	 * логирование истории (что, когда, где, чего, кто, зачем)
	 *
	 * @param $user_id
	 * @param $notice
	 * @param $type
	 * @param $supplier_id
	 * @return int
	 */
	static function history($user_id, $notice, $type, $supplier_id){
		global $mysqli;
		$query = "INSERT INTO `".LOG_SUPPLIER."` SET";
		$query .= " `user_id` = '".$user_id."'";
		$query .= ", `supplier_id` = '".$supplier_id."'";
		$query .= ", `user_nick` = (SELECT `nickname` FROM `".MANAGERS_TBL."` WHERE `id` = '".$user_id."')";
		$query .= ", `date` = CURRENT_TIMESTAMP";
		$query .= ", `type` = '".$type."'";
		$query .= ", `notice` = '".$notice."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);	
		return 1;
	}

	/**
	 * запись в лог отредактированных данных
	 * сохранение предыдущих значений и новых для возможности их восстановления
	 *
	 * @param $supplier_id
	 * @param $user_id
	 * @param $text
	 * @param $type
	 * @param $tbl
	 * @param $post
	 * @param int $id_row
	 * @return int
	 *
	 * @version 	00:28 11.01.2016
	 */
	static function history_edit_type($supplier_id, $user_id, $text ,$type,$tbl,$post,$id_row=0){
		global $mysqli;
		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id` = '" . $_POST['id'] . "'";
        $i=0;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value){
            if(isset($post[$key]) && trim($value)!=trim($post[$key])){
                if($i>0 && count($arr_adres)!=($i-1)){
                    $text.=",";
                }
                $i++;
                $text .= "поле ".$key ." изменено с ". $value." на ".$_POST[$key];
            }
        }
        if($i>0){
            self::history($user_id, $text ,$type,$supplier_id);
        }
        return 1;
	}

	/**
	 * запись в лог удаления каких-либо данных
	 *
	 * @param $supplier_id
	 * @param $user_id
	 * @param $text
	 * @param $type
	 * @param $tbl
	 * @param $post
	 * @param $id_row
	 * @return int
	 *
	 * @version 	00:30 11.01.2016
	 */
	static function history_delete_type($supplier_id, $user_id, $text ,$type,$tbl,$post,$id_row){
		global $mysqli;
		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $i=0;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value) {
            if($i!=0 || count($arr_adres)!=($i-1)){
                $text.=",";
            }
            $text .= "поле ".$key ." = ". $value;
            
        }
        self::history($user_id, $text ,$type, $supplier_id);
		return 1;
	}

	/**
	 * получение имени поставщика
	 *
	 * @param $id
	 * @return string
	 *
	 * @version 	00:30 11.01.2016
	 */
	static function get_supplier_name($id){
		global $mysqli;
		$name = "";
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$name = $row['nickName'];
			}
		}
		return $name;
	}

	/**
	 * запрашивает из базы допуски пользователя
	 * необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
	 *
	 * @param $id
	 * @return int
	 *
	 * @version 	00:31 11.01.2016
	 */
	private function get_user_access_Database_Int($id){
		$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);				
		$int = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$int = (int)$row['access'];
			}
		}
		return $int;
	}


}
