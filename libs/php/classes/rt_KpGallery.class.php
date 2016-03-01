<?php
/**
 *	библиотека универсальных классов	
 * 		
 */

if ( isset($_SESSION['access']['user_id'])  && $_SESSION['access']['user_id'] == 42) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}
	/**
	 *	галлерея изображений для некаталожной продукции
	 *  для КП
	 * 		
	 *	@author  	Алексей Капитонов
	 *	@version 	07.12.2015 13:45
	 */
 	class rtKpGallery extends aplStdAJAXMethod{
 		private $user_access = 0;

 		function __construct(){
			parent::__construct();
			
			if(isset($_SESSION['access']['access']) && $_SESSION['access']['access'] > 0 ){
				$this->user_access = $_SESSION['access']['access'];
			}

			// подключаемся к базе
			$this->db();
 		}
 		//////////////////////////
		//	KP
		//////////////////////////
 			// сохранение главного изображения
 			protected function chooseImgGallery_AJAX(){
	 			//////////////////////////
	 			//	предупреждения для юзера
	 			//////////////////////////
	 				// если не получено название папки
	 				if(!isset($_POST['data']['id'])){
	 					$html = 'ID не указан';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}
	 				// если не получено название изоюбражения
	 				if(!isset($_POST['data']['img'])){
	 					$html = 'Image не указана';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}
	 				if(!isset($_POST['data']['type'])){
	 					$html = '<br>Не указан тип изображения';	
						$this->responseClass->addMessage($html,'error_message');
	 					return;
	 				}

	 			// удаление загруженных файлов
	 			if(isset($_POST['data']['delete_img']) && trim($_POST['data']['delete_img']) != ''){
	 				// 	$html = 'Присутствуют изображения на удаление';	
					// $this->responseClass->addMessage($html,'error_message');
	 				$img_arr = explode(",", $_POST['data']['delete_img']);
	 				foreach ($img_arr as $key => $value) {
	 					$dir = ROOT.'/data/images/'.$_POST['data']['delete_img_width_folder'].'/';
	 					// полный путь к файлу 
	 					$filename = $dir.''.$value;
	 					
	 					// если файл существует
						if (file_exists($filename)) {
							// удаляем файл
							   unlink($filename);
						}	 					
	 				}
					
					// если папка пуста - удаляем её
					// if(rmdir($dir)){
					// 	if($this->user_access){
					// 		$html = 'Т.к. изображений во временной папке не осталось, папка была удалена.';	
					// 		$this->responseClass->addMessage($html,'error_message');	
					// 	}	
						// // на всякий случай сохраняем папку
						// if($_POST['data']['folder_name'] != 'img' && trim($_POST['data']['folder_name']) != ''){
						// 	$_POST['data']['folder_name'] = 'img';					
						// }	
						// $_POST['data']['folder_name'] = 'img';					
					// }
	 			} 				

	 			// вычищаем предыдущие данные из базы
	 			if(trim($_POST['data']['json']) != trim($_POST['data']['json_old'])){
		 			$query = "DELETE FROM `".RT_MAIN_ROWS_GALLERY."` WHERE parent_id = ".(int)$_POST['data']['id'].";";
		 			$result = $this->mysqli->query($query) or die($this->mysqli->error);

		 			$json = json_decode($_POST['data']['json'], true);	 			
		 							
		 			
		 			foreach ($json as $key => $IMG) {
		 				$query = "INSERT INTO `".RT_MAIN_ROWS_GALLERY."` SET ";
		 				$query .= "`sort` = '".$key."'";
		 				$query .= ", `folder` = '".$IMG['folder']."'";
		 				$query .= ", `img_name` = '".$IMG['img_name']."'";
		 				$query .= ", `parent_id` = '".$_POST['data']['id']."'";
		 				$result = $this->mysqli->query($query) or die($this->mysqli->error);
		 			}
		 		}
	 			
				// $query .=" img_folder = '".$dir."'";
				// $query .=", img_folder_choosen_img = '".$img."'";
				// $result = $mysqli->query($query) or die($mysqli->error);


 				// сохраняем значение в базе
 				if( $_POST['data']['folder_name'] != 'img' ){
					$query = "UPDATE `".RT_MAIN_ROWS."` SET";
					// $query .=" img_folder_choosen_img = '".$_POST['data']['img']."'";
				
				
					$query .=" img_folder = '".$_POST['data']['folder_name']."'";	
					

					// $query .=", img_type = '".$_POST['data']['type']."'";	
					$query .=" WHERE `id` = ".(int)$_POST['data']['id'].";";
					
					$result = $this->mysqli->query($query) or die($this->mysqli->error);
				}	
				$html = 'OK';	
				$this->responseClass->addMessage($html,'successful_message','25000');
				// $this->responseClass->addResponseFunction('window_reload');
				return;
 			}
 			// НА УДАЛЕНИЕ 16:05 22.12.2015
 			// // отключение изображения
 			// protected function chooseNoImgGallery_AJAX(){
 			// 	$query = "UPDATE `".RT_MAIN_ROWS."` SET";
				// $query .=" img_folder_choosen_img = ''";
				// $query .=" WHERE `id` = ".(int)$_POST['id'].";";
				// $result = $this->mysqli->query($query) or die($this->mysqli->error);	
				// return;
 			// }
 			protected function copy_kp_gallery_rows_AJAX(){
 				exit;
				$query = "SELECT * FROM `".KP_MAIN_ROWS."`  ";
 				$arr = array();
 				$result = $this->mysqli->query($query);// or die($this->mysqli->error);
 				if(!$result){
						$message = $this->mysqli->error;
						$this->responseClass->addMessage($message,'error_message');
					}

 				if($result->num_rows > 0){
					// echo $result->num_rows;
					while($row = $result->fetch_assoc()){
						$arr[] = $row;
					}
				}	

				foreach ($arr as $key => $value) {
					
					$query = "INSERT INTO `".KP_MAIN_ROWS_GALLERY."` 
							   SET 
							   `parent_id` = '".$value['id']."',
							   `img_name` = '".$value['img']."',
							   `folder` = '".$value['img_folder']."'
							  ";
					$result = $this->mysqli->query($query);// or die();
					if(!$result){
						$message = $this->mysqli->error;
						$this->responseClass->addMessage($message,'error_message');
					}
				}




 				$html = 'OK';	
				$this->responseClass->addMessage($html,'successful_message','25000');
 			}


 			// запрос позиции
 			private function getPosition($id){
 				// запрос наличия выбранного изображения для данной строки
 				$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."' ";
 				$row = array();
 				$result = $this->mysqli->query($query) or die($this->mysqli->error);
 				
 				if($result->num_rows > 0){
					// echo $result->num_rows;
					while($row = $result->fetch_assoc()){
						return $row;
					}
				}					
				return $row;
 			}
 			// вставляет новую запись о выборанном изображении в базу
 			private function newSelectRow($dir, $img){
 			// 	global $mysqli;
 			// 	$query = "INSERT INTO `".RT_MAIN_ROWS."` SET";
				// $query .=" img_folder = '".$dir."'";
				// $query .=", img_folder_choosen_img = '".$img."'";
				// $result = $mysqli->query($query) or die($mysqli->error);
 			}
 			
 			// проверка наличия изображений для по RT_id
 			// при наличии изображения выбранного в галлерее возвращает его имя
 			// в противном случае false
 			static function checkTheFolder($RT_id, $name = ''){				
 				// echo method_get_name();
 				$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/admin/order_manager/data/images/'.$RT_id.'/';
 				$dir = ROOT.'/data/images/'.$RT_id.'/';
				// если папка не нейдена возвращаем false
				if (!is_dir($dir)) {
					return flase;
				}
				// если папка пуста возвращаем false
				$files = scandir($dir);
				if(count($files) <= 2){
					return flase;	
				}
				
				$query = "SELECT * FROM `".KP_GALLERY."` WHERE dir = '".$RT_id."' ";
				// echo $query;
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
				$img = '';
 				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$img = $row['img'];
					}
				}
				// если изображение не указано возвращаем false
				if($img == ''){
					return flase;
				}
				// по умолчанию возвращаем название выбранного изображения
				switch ($name) {
					case 'dir':
						$dir = ROOT.'/data/images/'.$RT_id.'/'.$img;
						return $dir;
						break;
					case 'global_dir':
					$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$RT_id.'/'.$img;	
						return $global_dir;
						break;
					
					default:
						return $img;
						break;
				}							
 			}
 			
 			/**
 			 *	получает изображения для артикула
 			 *
 			 *	@param 		$art - артикул
 			 *	@return  	array( index => image_name)
 			 *	@author  	Алексей Капитонов
 			 *	@version 	16:23 22.12.2015
 			 */
 			protected function getImagesForArt($art){
 				// объявляем массив изображений
 				$img = array();
				if(trim($art) != ''){
 					$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art='".$art."' ORDER BY id";
					$result = $this->mysqli->query($query) or die($this->mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$img[] = $row['name'];
						}
					}
 				}
				//	если изображений для артикула нет
				//  или артикул не существует
				if(count($img) == 0){
					$img[] = 'no_image.jpg';
				}
				return $img;
 			}

 			// собирает json выбранных изображений
 			protected function getJsonCheckedImg($rt_main_row_id){
 				if (!isset($this->checked_IMG)) {
 					$this->checked_IMG = $this->getCheckedImg($rt_main_row_id);
 				}

 				$json = '[';	

 				$json = "[";
 				foreach ($this->checked_IMG as $key => $value) {
 					$json .= (($key > 0)?',':'').'{"folder":"'.$value['folder'].'","img_name":"'.$value['img_name'].'"}';		
 				} 				
 				$json .= "]";
 				return $json;

 			}

 			// определяет по имени файлпа выбран он или нет
 			protected function copare_and_calculate_checked_files($rt_main_row_id, $file_name){
 				if (!isset($this->checked_IMG)) {
 					$this->checked_IMG = $this->getCheckedImg($rt_main_row_id);
 				}


 				foreach ($this->checked_IMG as $key => $value) {
 					if($file_name == $value['img_name']){
 						return "checked";
 					}
 				}
 				return "";
 			}
 			
 			/**
 			 *	получает изображения для позиции в РТ
 			 *
 			 *	@param 		$art - артикул
 			 *	@return  	array()
 			 *	@author  	Алексей Капитонов
 			 *	@version 	22.12.2015
 			 */
			protected function getImagesForPosition($rt_main_row){
				
				$images_compare = '';
				$folder = $rt_main_row['img_folder'];
				$checked = false;
				$html = '';				
				$html .= '<ul>';
				
				//////////////////////////
				//	изображения из карточки артикула
				//////////////////////////
					$img_art_arr = $this->getImagesForArt($rt_main_row['art']);
					$upload_dir = $_SERVER['DOCUMENT_ROOT'].'/img/';
					$global_link_dir = 'http://'.$_SERVER['HTTP_HOST'].'/img/';
					foreach ($img_art_arr as $key => $file_name) {
						$class_li = '';
						$path = $global_link_dir.$file_name; // собираем путь
						
						// if($file_name == $rt_main_row['img_folder_choosen_img'] || ($key == 0 && trim($rt_main_row['img_folder_choosen_img']) == '')){
						//   	$class_li = "checked";
						  	// $checked = true;
						// }
						$checked = $this->copare_and_calculate_checked_files($rt_main_row['id'], $file_name);
						
						$images_compare .= $file_name.' - '.$checked.'<br> ';

						$html .= $this->getImgLiHtml($path, $file_name, $checked, 'img','g_std');
					}
				//////////////////////////
				//	Загруженные изображения
				//////////////////////////
					$upload_dir = ROOT.'/data/images/'.$folder.'/';
					$global_link_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder.'/';
					// если директория (папка) существует
					if($folder != '' && is_dir($upload_dir)){			
						// сканируем директории.		
						$files = scandir($upload_dir);
						// перебираем содержимое директории
						for ($i = 0; $i < count($files); $i++) { // Перебираем все файлы
						    if (($files[$i] == ".") || ($files[$i] == "..")) { // Текущий каталог и родительский пропускаем
						    	continue;
						    }
							     
						    $path = $global_link_dir.$files[$i]; // собираем путь
						    $checked = $this->copare_and_calculate_checked_files($rt_main_row['id'], $files[$i]);
						    
						    $images_compare .= $files[$i].' - '.$checked.'<br> ';
						    
						    $html .= $this->getImgLiHtml($path, $files[$i], $checked, $folder, 'g_upload');
						}
					}
				$html .= '</ul>';
				return $html;
			}
			//////////////////////////
			//	из common.php
			//////////////////////////
				protected function transform_img_size($img,$limit_height,$limit_width){
			     	list($img_width, $img_height, $type, $attr) = (file_exists($img))? getimagesize($img): array($limit_width,$limit_height,'',''); 
					$limit_relate = $limit_height/$limit_width;
					$img_relate = $img_height/$img_width;
					if($limit_relate < $img_relate) $limit_width = $limit_height/$img_relate; 
					else $limit_height = $limit_width*$img_relate;
					return array($limit_height,$limit_width); 
				}
				protected function get_big_img_name($art){
			        global $db;				   
			        $query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art='".$art."' ORDER BY id";
				    $result = mysql_query($query,$db);
				    if($result && mysql_num_rows($result)>0){
					   $row = mysql_fetch_assoc($result);
				       $img = ($row['name'] !='')? $row['name']:'no_image.jpg';
				    }
				    else $img = 'no_image.jpg';
				    return $img;	
			    } 
			    protected function checkImgExists($path,$no_image_name = NULL ){
				    $mime = $this->getExtension($path);
					if(@fopen($path, 'r')){//file_exists
						$img_src = $path;	
					}
					else{
					    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image';
						$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name.'.'.$mime;
					} 
					return $img_src;
				}
				protected function getExtension($filename){
			        $path_info = pathinfo($filename);
			        return $path_info['extension'];
			    }
			// получаем изображение для артикула
			protected function getArtImg($item){
				$img_path = '../img/'.$this->get_big_img_name($item['art']);	
			    $img_src = $this->checkImgExists($img_path);
				// меняем размер изображения
			    $size_arr = $this->transform_img_size($img_src,226,300); // установленое здесь значение для высоты является оптимальным 
				                                                   // при выводе КП на печать (не съезжают ячейки таблицы)
				//$size_arr = array(230,300);
				//$size_arr = array(100,100);
			
				
				// вставляем изображение
				return '<img src="'.$img_src.'" height="'.$size_arr[0].'" width='.$img_src[1].'">';
			}
			// получаем html изображения
			protected function getImgLiHtml($path, $file = '',$li_class = '', $folder = '', $type){
				$html = '<li class="rt-gallery-cont '. $li_class .'" data-type="'.$type.'" data-folder="'.$folder.'" data-file="'.$file.'" >';
				if($folder != 'img'){
					$html .= '<div class="delete_upload_img">x</div>';	
				}				
				$html .= '<img src="'.$path.'" alt="" />'; // Вывод превью картинки
				$html .= '</li>';
				return $html;
			}
			// получаем контент из галлереи загруженных изображений
			protected function getImageGalleryContent($rt_main_row){
				// $folder_name = $rt_main_row['img_folder'];
				// $folder = ROOT.'/data/images/'.$folder_name.'/';
				
				// DEBUG
					// $html  = ''.$folder.'<br>'.$rt_id;
				$html = '';
				$html .= '<div id="rt-gallery-images">';
					$html .= $this->getImagesForPosition($rt_main_row);
				$html .= '</div>';
				return $html;
			}
			// проверка существования папки
			protected function checkFolderExist($folder){
				return is_dir(ROOT.'/data/images/'.$folder.'/');
			}

			// создание новой папки
			protected function createNewDir($rt_main_row_id){
				$dirName_1 = md5(time());
				
				$dirName = ROOT.'/data/images/'.$dirName_1.'/';
				
				if (!is_dir($dirName)) {
					//если папкb $dirName не существует
					// $dirName = __DIR__.'/../../../data/images/'.$dirName_1;
					mkdir($dirName,0777,true);
					
					global $mysqli; // пишем её название в базу
					$query = "UPDATE `".RT_MAIN_ROWS."` SET";
					$query .=" img_folder = '".$dirName_1."'";
					$query .=" WHERE `id` = ".$rt_main_row_id.";";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				return $dirName_1;
			}
			private function warpDopText($text){
				return '<div class="dop_text">'.$text.'</div>';
			}

			// собираем загруженные изображения
			protected function getCheckedImg($rt_main_row_id){
				$query = "SELECT * FROM `".RT_MAIN_ROWS_GALLERY."` WHERE parent_id = ".(int)$rt_main_row_id.";";
	 			$arr = array();
 				$result = $this->mysqli->query($query) or die($this->mysqli->error);
 				
 				if($result->num_rows > 0){
					// echo $result->num_rows;
					while($row = $result->fetch_assoc()){
						$arr[] = $row;
					}
				}					
				return $arr;
			}


			// собираем окно галлереи изображений для позиции
			protected function getStdKpGalleryWindow_AJAX(){
				if(!isset($_POST['id'])){
					$html = 'Отсутствует id.';
					$this->responseClass->addMessage($html,'error_message');
					return;
				}
				// $rt_id = $this->check_changes_to_rt_protocol($_POST['control_num'],$_POST['id']);
				$rt_id = (int)$_POST['id'];
				$rt_main_row = $this->getPosition($rt_id);
				$folder_name = $rt_main_row['img_folder'];
				
				
				// $html = $this->warpDopText($this->printArr($rt_row));
				// $global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder_name.'/';					
				// $html .= $this->warpDopText($global_dir);
				
				// $this->responseClass->addMessage($html,'system_message',25000);
				
				// проверка на существование папки
				if($folder_name == '' || !$this->checkFolderExist($folder_name)){
					// создаем новую папку
					$folder_name = $this->createNewDir($_POST['id']);
					// if( $this->user_access ){
						$html = 'Создана новая папка';
						$this->responseClass->addMessage($html,'system_message',25000);
					// }					
				}

				$win_DIV_ID = 'rt-gallery-DIV_'.$folder_name;
				$id = 'file_upload_'.md5(time());
				$html = '';
				// $html .= $this->printArr($rt_row);
				$html .= '<div id='.$win_DIV_ID.'>';
				// вывод изображений по позиции
				$html .= $this->getImageGalleryContent($rt_main_row);
				
				$timestamp = time();
				$token = md5('unique_salt' . $timestamp);
				// $html .= '<h1>Загрузка изображений</h1>';
				$html .= '<form>
							<div id="queue"></div>
							<input id="'.$token.'" data-folder_name="'.$folder_name.'" name="file_upload" type="file" multiple="true">
							<input id="data_folder_name" name="data[folder_name]" type="hidden" value="">
							<textarea  style="display:none" id="data_JSON" name="data[json]" type="text" >'.$this->getJsonCheckedImg($rt_id).'</textarea>
							<textarea style="display:none" id="data_JSON" name="data[json_old]" type="text" >'.$this->getJsonCheckedImg($rt_id).'</textarea>
							<input id="data_id" name="data[id]" type="hidden" value="">
							<input id="data_img" name="data[img]" type="hidden" value="">
							<input id="data_type" name="data[type]" type="hidden" value="">
							<input id="data_AJAX" name="AJAX" type="hidden" value="chooseImgGallery">
							<input id="data_delete_img" name="data[delete_img]" type="hidden" value="">
							<input id="data_delete_img_width_folder" name="data[delete_img_width_folder]" type="hidden" value="">
						</form>
						';
				// $html .= $this->printArr($_POST); // распечатка POST в окно
				$html .= '</div>';
				$options['width'] = 1200;
				$options['button_name'] = 'Сохранить';
				$title = 'Выберите изображение для позиции';
				$this->responseClass->addPostWindow($html,'Выберите изображение для позиции',$options);
				
				$message = 'Чтобы выбрать изображение для КП кликните по картинке затем нажмите на кнопку сохранить.';
				$this->responseClass->addMessage($message,'system_message');
				// запустим функцию JS и передадим ей новый id
				$options = array();
				$options['id'] = $rt_id;
				$options['folder_name'] = $folder_name;
				$options['timestamp'] = $timestamp;
    			$options['token'] = $token;
    			// выз
				$this->responseClass->addResponseFunction('uploadify',$options);
			}
			
			// добавление новых изображений для КП
			protected function add_new_files_in_kp_gallery_AJAX(){
				$firstImg = false;
				$folder_name = $_POST['folder_name'];
				$uploadDir = ROOT.'/data/images/'.$folder_name.'/';
				// echo $uploadDir;

				if (!is_dir($uploadDir)) {
					$folder_name = $this->createNewDir($_POST['id']);
					$uploadDir = ROOT.'/data/images/'.$folder_name.'/';
					
				}
				// меняем права на папку
				chmod($uploadDir, 0777);
				///var/www/admin/data/www/apelburg.ru/admin/order_manager/data/images/file_upload_97be41adc28fd2a828c8317cfb520029/
				
					
				// исключение на неполные данные
				if( !isset($_POST['id']) || trim($_POST['id']) == ''){
					$html = $this->printArr($_POST);
					$options['width'] = 1200;
					$options['height'] = 500;
					$html .= '<br>'.$uploadDir;
					$this->addSimpleWindow($html,'',$options);
					$html = 'Не указан путь сохранения';
						
					$this->responseClass->addMessage($html,'error_message');
					$this->responseClass->addMessage($uploadDir,'error_message', 15000);
					return;	
				}
				// разрешёные форматы файлов
				$fileTypes = array('jpg', 'jpeg', 'gif', 'png');
				if ($_FILES) {
					$verifyToken = md5('unique_salt' . $_POST['timestamp']);
					if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
						$tempFile   = $_FILES['Filedata']['tmp_name'];
						$uploadDir  = $uploadDir;
						//удаляем старые файлы в папке
						//removeFiles($uploadDir, $verifyToken);
						// проверка типа файла
						$fileParts = pathinfo($_FILES['Filedata']['name']);
						$extension = strtolower($fileParts['extension']);
						if (in_array($extension, $fileTypes)) {
							//устанавливаем имя файла
							$fileName = mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y"));
							//добавляем актуальный токен к файлу
							$fileName .= "_$verifyToken";
							$fileNameExtension = $fileName . ".$extension";
							$targetFile = $uploadDir . $fileNameExtension;
							
							// сохраняем файл
							move_uploaded_file($tempFile, $targetFile);
							//меняем атрибуты
							//chmod($targetFile, 0775);
							//die(json_encode($targetFile));
							$html = 'Изображение загружено';
						
							$this->responseClass->addMessage($html,'system_message');
							// добавляем загруженные изображения
							$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder_name.'/';
							$path = $global_dir.$fileName . ".$extension";
							$this->responseClass->addResponseFunction('rtGallery_add_img',array('id'=>$folder_name,'html'=>$this->getImgLiHtml($path,$fileName . ".$extension",(($firstImg)?'checked':''),$folder_name,'g_upload')));
						} else {
							// загрузка не удалась
							echo 'Invalid file type.';
						}
					}
				}
			}
}
		
	
?>