<?php
class Cabinet_admin_class{
	function __construct(){
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	
	//////////////////////////
	//	ВЫЗОВ ФУНКЦИЙ AJAX
	//////////////////////////

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}


	private function add_new_comment_for_query_AJAX(){
			$this->save_query_comment_Database();
			$html = '<div class="comment table">';
					$html .= '<div class="row">';
						$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
						$html .= '</div>';
						$html .= '<div class="cell comment_text">';
						$html .= '<div class="create_time_message">'.$_POST['comment_text'].'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}
	
	private function save_query_comment_Database(){
		global $mysqli;
		$query ="INSERT INTO `".RT_LIST_COMMENTS."` SET
	             `user_id` = '".(int)$_POST['id']."',
	             `os__rt_list_id` = '".(int)$_POST['os__rt_list_id']."',
	             `user_name` = '".$_POST['name']."',
	             `comment_text` = '".$_POST['comment_text']."',
	            `create_time` = NOW()";
			$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}

	private function get_comment_for_query_AJAX(){
		global $mysqli;
		$html = '';	
		$comments = array();
		$query = "SELECT `".RT_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".RT_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".RT_LIST_COMMENTS."`  WHERE `os__rt_list_id` = '".(int)$_POST['os__rt_list_id']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}
			// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 

		$html .= '<div id="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name">'.$value['user_name'].'</div>';
					$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<div class="create_time_message">'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		}
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';


		
		$html .= '<form>';
		$html .= '<div class="comment table">';
			$html .= '<div class="row">';
				$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
					
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<textarea name="comment_text"></textarea>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
			$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_query"></input>';
			$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
			$html .= '<input name="os__rt_list_id" type="hidden" value="'.$_POST['os__rt_list_id'].'"></input>';
			$html .= '<button id="add_new_comment_button">Отправить</button>';
		$html .= '</form>';
		$html .= '</div>';

		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}


}



?>