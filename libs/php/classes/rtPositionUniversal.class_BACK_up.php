<?php


// расширение для класса позиции
class rtPositionUniversal extends Position_general_Class
{	
	public $position;

	function __construct(){
		// подключаемся к базе
		$this->db();
		
		// получаем позицию
		$this->getPosition((isset($_GET['id']))?$_GET['id']:'none');

		// echo '<pre>';
		// print_r($this->position);
		// echo '</pre>';
			
	}

	// покдключение к базе
	// в дальнейшем подключим по уму
	protected function db(){
		if(!isset($this->mysqli)){
			global $mysqli;
			$this->mysqli = $mysqli;	
		}		
	}


	public function getPosition($id){
		if(empty($this->position)){
			$this->position = $this->getPositionDatabase($id);		
		}
		return $this->position;
	}


	private function getPositionDatabase($id){
		

		// чеерез get параметр id мы получаем id 1 из строк запроса
		// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
		$query = "SELECT `".RT_LIST."`.*,`".RT_LIST."`.`id` AS `RT_LIST_ID`, `".RT_MAIN_ROWS."`.*, DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`
		  FROM `".RT_MAIN_ROWS."`
		  INNER JOIN `".RT_LIST."`
		  ON `".RT_LIST."`.`query_num` = `".RT_MAIN_ROWS."`.`query_num`

		   WHERE `".RT_MAIN_ROWS."`.`id` = '".$id."'";
		// echo $query;
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		
		$position = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$position = $row;
			}
		}
		return $position;
	}


	public function getImage(){		
		$this->images =  new Images();
		return $this->images->getImageHtml();
	}



}


// класс расширение для добавления модуля изображений
class Images extends rtPositionUniversal
{	
	function __construct($id = 0){
		// подключаем базу на всякий
		$this->db();
	}

	// проверка на наличие изображений
	private function checkImageExist(){
		return true;
	}


	/**
	 *	старые функции 
	 *  перенесено из new_veiw.php
	 *  
	 *	@author  	Андрей
	 *	@version 	17:27 14.12.2015
	 */
	private function checkImgExists($path,$no_image_name = NULL ){
	    $mime = getExtension($path);
		
		// если вдруг есть пробел заменяем его на '%20'
		if(strpos($path,' ') !== false){
		   $path = str_replace(' ','%20',$path);
		}
		if(@fopen($path, 'r')){//file_exists
			$img_src = $path;	
		}
		else{
		    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image';
			$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name.'.'.$mime;
		} 
		return $img_src;
	}

	/**
	 *	старые функции 
	 *  поверхностный рефакторинг 
	 *  
	 *	@param 		articul
	 *  @return  	array()
	 *	@author  	Алексей Капитонов
	 *	@version 	17:27 14.12.2015
	 */
	public function fetchImagesForArt($art){
		if(!$art || $art=='0'){return array();}
		
		// global $mysqli;

		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='big' ORDER BY  id ASC";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		
		// основная картинка
		$i=0;


		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){

				$big_images_id[] = $row['id'];
				if(!isset($main_img_src)) {
					$main_img_src = checkImgExists( APELBURG_HOST.'/img/'.$row['name']);
				}

				if(mysql_num_rows($result)>1){
					$big_images[] = $row['name'];
				}
			}
		}else{
			$main_img_src = checkImgExists(APELBURG_HOST.'/img/no_image.jpg');
		}

		
		// вычисляем превьющки
		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='small' ORDER BY  id ASC";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		$counter = 0;
		$counter2 = 0;
		$counter3 = 0;

		// если артикул имеет больше одной картинки строим панель с превьюшками
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				
				$deleting_img = '';
				if(isset($_SESSION['access']['access']) &&  ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3)){
					$deleting_img = '<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$big_images[$counter3++].'|'.$row['name'].'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>';
				}			

				$previews_block[] = '<div  class="carousel-block">
							<img class="articulusImagesMiniImg imagePr" alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$row['name']).'" data-src_IMG_link="'.APELBURG_HOST.'/img/'.$big_images[$counter++].'">
							'.$deleting_img.'
							</div>';
			   $i++;
			}
		}

		if(isset($_SESSION['access']['access']) && ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3)){
			$previews_block[] = '<div  class="carousel-block" id="image_add"><img class="articulusImagesMiniImg imagePr" alt="" src="'.APELBURG_HOST.'/skins/images/general/add_image_d.png" data-src_IMG_link="'.APELBURG_HOST.'/skins/images/general/add_image_d.png"></div>';	
			$i++;	
		}
		if(isset($i) && $i>0){
			$string	= implode('',$previews_block);
			$html = '<div class="carousel shadow" style="">'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s2.png)"></a>'.PHP_EOL:'';
			$html .= '<div class="carousel-wrapper">'.PHP_EOL;
			$html .= '<div class="carousel-items">'.PHP_EOL;	
			$html .= $string;
			$html .= '</div>'.PHP_EOL;
			$html .= '</div>'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s22.png); float:right; margin-top:-70px"></a>'.PHP_EOL:'';
			$html .= '</div>'.PHP_EOL;
			$previews_block = $html;
		}else{
			$previews_block = '<div>нет дополнительных картинок</div>';
		}
		return array('main_img_src' => $main_img_src,'previews_block' => $previews_block);
	}

	// вывод блока изображений
	public function getImageHtml(){
		parent::__construct();
		$images_data = $this->fetchImagesForArt($this->position['art']);
		// if($this->checkImageExist()){
			// шаблон изображений
			ob_start();
			echo '<pre>';
			print_r($images_data);
			echo '</pre>';
			echo '<pre>';
			print_r($this->position);
			echo '</pre>';
				
			// echo 65451231564310;
			include_once __DIR__.'/../../../skins/tpl/client_folder/rt_position/images_block.tpl';
			$content = ob_get_contents();
			ob_get_clean();
			return $content;
		// }else{
		// 	return '';
		// }
	}
}

// класс вариантов прикрепленнных к услуге
class Variant extends rtPositionUniversal
{
	public $variants;
	function __construct($id = 0){

		$this->service[] = 'Hellow World, $id = "'.$id.'"';
		return $this->service;
	}

	protected function getService($id = 0){
		$this->service = new Service($id);
		return $this->service;
	}
}


// класс услуг прикрепленных к варианту
class Service extends Variant
{
	public $service;
	function __construct($id = 0){

		$this->service[] = 'Hellow World, $id = "'.$id.'"';
		return $this->service;
	}
}

