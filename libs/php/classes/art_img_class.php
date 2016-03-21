<?php 
	/**
     *	новый класс на время подмены старого
     *
     *	@author  	Алексей Капитонов
     *	@version 	18:15 23.12.2015
     */
    class Art_Img_development extends aplStdAJAXMethod{
    	/*
    	1. расширить таблицу os__kp_main_rows [KP_MAIN_ROWS] на 2 ячейки в которые при заведении 
    	строки будет вписываться выбранное изображение и папка в которой его следует искать
    		- если изображение не выбрано ячейки останутся пустыми 
    		и алгоритм должен будет отработать по старой схеме
		*/

    	public $big;
		public $small;
    
		function __construct($main_id, $folder, $img, $art){
			$this->db();

			$this->big[0] = 	'http://'.$_SERVER['HTTP_HOST'].'/img/no_image.jpg';
			$this->small[0] = 'http://'.$_SERVER['HTTP_HOST'].'/img/no_image.jpg';

			// if(trim($folder) == '' || trim($img) == ''){
			// 	$this->getImg($art);
			// }else{
			// 	$this->getChoosenUploadImg($folder, $img);
			// }

			// если юзер выбирал какие-тоизображения намеренно
			if(count($this->checkChoosenImg($main_id))){
				foreach ($this->checkChoosenImg as $key => $value) {
					$this->getChoosenUploadImg($value['folder'], $value['img_name']);
				}				
			}else{
				$this->getImg($art);
			}		
		}

		// проверка наличия намеренно выбранных изображений
		private  function checkChoosenImg($main_id){
			$query = "SELECT * FROM `".KP_MAIN_ROWS_GALLERY."` WHERE `parent_id` = '".$main_id."';";
			$arr = array();
 			$this->checkChoosenImg = array();

 			$result = $this->mysqli->query($query) or die($this->mysqli->error);
 				
 			if($result->num_rows > 0){
				// echo $result->num_rows;
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;

					$this->checkChoosenImg[$row['id']] = $row;
				}
			}					
			return $arr;
		}

		

		// возвращает выбранные изображиния или no_image 
		// !!!! вместо no_image нужно нарисовать "изображение было удалено или DELETED"
		private function getChoosenUploadImg($folder, $img){
			// проверяем папку
			if($folder == 'img'){
				$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/'.$folder.'/';
 				$dir = $_SERVER['DOCUMENT_ROOT'].'/'.$folder.'/';				
			}else{
				$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder.'/';
 				$dir = ROOT.'/data/images/'.$folder.'/';
			}

			// проверка наличия изображения
			if($this->checkImgExists($dir.$img)){
				if (isset($this->small[0]) && $this->small[0] == 'http://'.$_SERVER['HTTP_HOST'].'/img/no_image.jpg' ) {
					unset($this->small[0]);
					unset($this->big[0]);
				}

				$this->small[] = $this->big[] = $global_dir.$img;
			}else{
				// если изображение не было найдено - скорее всего оно было удалено из галлереи
				$this->big[] = 	'http://'.$_SERVER['HTTP_HOST'].'/img/image_was_deleted.jpg';
				$this->small[] = 'http://'.$_SERVER['HTTP_HOST'].'/img/image_was_deleted.jpg';
			}
		}

		private function checkImgExists($path){
			if(@fopen($path, 'r')){//file_exists
				return true;
			}
			return false;
		}
		    
		private function getImg($art){
		    
			//$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =$art  GROUP BY size ORDER BY id";
			//$result = $mysqli->query($query)or die($mysqli->error);
			$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =?  GROUP BY size ORDER BY id";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$stmt->bind_param('s',$art) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			
			$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/img/';
 			// $dir = $_SERVER['DOCUMENT_ROOT'].'/img/';		
			
			if($result->num_rows>0){
			    while($row=$result->fetch_assoc()){
				    if($row['size']=='big') {
				    	$this->big[0] = $global_dir.(($row['name'] !='')? $row['name']:'no_image.jpg');
				    }
					if($row['size']=='small') {
						$this->small[0] = $global_dir.(($row['name'] !='')? $row['name']:'no_image.jpg');
					}
				}
			}
		}
    }

    class Art_Img{
	    public $big;
		public $small;
    
		function __construct($art){
			global $mysqli;
		    
			//$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =$art  GROUP BY size ORDER BY id";
			//$result = $mysqli->query($query)or die($mysqli->error);
			$query = "SELECT*FROM `".IMAGES_TBL."` WHERE `art` =?  GROUP BY size ORDER BY id";

			$stmt = $mysqli->prepare($query) or die($mysqli->error);
			$stmt->bind_param('s',$art) or die($mysqli->error);
			$stmt->execute() or die($mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			
			if($result->num_rows>0){
			    while($row=$result->fetch_assoc()){
				    if($row['size']=='big') $this->big = ($row['name'] !='')? $row['name']:'no_image.jpg';
					if($row['size']=='small') $this->small = ($row['name'] !='')? $row['name']:'no_image.jpg';
				}
			}
			else $this->big = $this->small = 'no_image.jpg';
		}
    } 

?>
