<!--<?php echo __FILE__; ?> -- START-->

<!-- стили -->
<link href="./skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<link href="./skins/css/serviceCenter.css" rel="stylesheet" type="text/css">

<!-- библиотеки -->
<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>

<!-- сервисцентр -->
<script type="text/javascript" src="./libs/js/jquery.serviceCenter.js"></script>


<!-- старые скрипты -->
<script type="text/javascript" src="./libs/js/classes/printCalculatorClass.js"></script>
<script type="text/javascript" src="./libs/js/up_window_consructor.js"></script>
<script type="text/javascript" src="./libs/js/classes/rtCalculatorClass.js"></script>

<script type="text/javascript" src="libs/js/rt_position.js"></script>
<script type="text/javascript" src="./libs/js/rt_position_gen.js"></script>

<!-- стандартный обработчик -->
<!-- <script type="text/javascript" src="../libs/js/standard_response_handler.js"></script> -->
<!-- скрипт страницы -->
<script type="text/javascript" src="./libs/js/jsPositionUniverasal.js"></script>

<?php
	include_once (ROOT.'/libs/php/classes/rt_class.php');
	$query_num = (isset($_GET['query_num'])?$_GET['query_num']:0);
	$cont_face_data = RT::fetch_query_client_face($query_num);
	//print_r($cont_face_data);

	$cont_face = '<div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,\'clientManagerMenu\');">Контактное лицо: '.(($cont_face_data['id']==0)?'не установлено':$cont_face_data['details']['last_name'].' '.$cont_face_data['details']['name'].' '.$cont_face_data['details']['surname']).'</div>';


	$CALCULATOR_LEVELS = array('full'=>"Конечники",'ra'=>"Рекламщики");
	$calculator_level = ($POSITION->position['calculator_level']!='')?$POSITION->position['calculator_level']:'full';
	$calculator_level_ru = $CALCULATOR_LEVELS[ $calculator_level ];
?>
<div class="cabinet_top_menu">
  <ul class="central_menu" >
    <li <?php if($POSITION->position['manager_id'] == '24' ){echo 'class="selected"';}?> >
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_wait_the_process&client_id=<?=$_GET['client_id'];?>">
        <div class="border">Ожидают распределения</div>
      </a>
    </li>
    <li <?php if($POSITION->position['status'] == 'not_process' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=no_worcked_men&client_id=<?=$_GET['client_id'];?>">
        <div class="border">Не обработанные МЕН</div>
      </a>
    </li>
    <li <?php if($POSITION->position['status'] == 'taken_into_operation' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_taken_into_operation&client_id=<?=$_GET['client_id'];?>">
        <div class="border">На рассмотрении</div>
      </a>
    </li>
    <li <?php if($POSITION->position['status'] == 'in_work' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_worcked_men&client_id=<?=$_GET['client_id'];?>">
        <div class="border">В работе Sales</div>
      </a>
    </li>
    <li <?php if($POSITION->position['status'] == 'history' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_history&client_id=<?=$_GET['client_id'];?>">
        <div class="border">История </div>
      </a>
    </li>
    <li>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_all&client_id=888">
        <div class="border">Все</div>
      </a>
    </li>
  </ul>
</div>

<div id="order_art_edit">
	<div id="info_string_on_query" user_id="<?php echo $user_id; ?>">
		<ul>
			<li style="opacity:0" id="back_to_string_of_claim"></li>
			<li id="claim_number" data-query_num="<?=$POSITION->position['query_num'];?>" data-order="<?=$POSITION->position['id'];?>">
				<a href="?page=client_folder&query_num=<?=$POSITION->position['query_num'];?>&client_id=<?php echo $client_id; ?>">Запрос № <?=$POSITION->position['query_num'];?></a></li>
			<li id="claim_date"><span>от <?=$POSITION->position['date_create'];?></span></li>
			
			<li id="query_theme_block"><span>Тема:</span> <input id="query_theme_input" class="query_theme" data-id="<?=$POSITION->position['RT_LIST_ID'];?>" type="text" value="<?=$POSITION->position['theme']?>" onclick="fff(this,'Введите тему');"></li>
			<li style="float:right;height: 100%;width: 40px;"><span data-rt_list_query_num="<?=$POSITION->position['query_num'];?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($POSITION->position['query_num']); ?> "></span></li>
			<li style="float:right"><?php  echo $cont_face; ?></li>
	    	<li style=""><div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,'calcLevelSwitcher');">Калькулятор: <?php  echo $calculator_level_ru; ?></div>
	      <input type="hidden" id="calcLevelStorage" value="<?php  echo $calculator_level; ?>"></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<li title="порядковый номер позиции в запросе"><span>Позиция № : </span><?=$POSITION->position['sort'];?></li>
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span><?=$product_type_RU;?></li>
						
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">
			<div class="cell b_r" id="order_art_edit_left" <?php 
				if($POSITION->position['show_img'] == 0){
					echo ' style="width: 0px; opacity: 0;"';
				}
				?>>
				
				<?php
					echo $POSITION->getImage();
				?>
			</div>
			<div class="cell<?php
			// echo $POSITION->user_access.' '.$POSITION->position['status'];
				if($POSITION->position['status'] != 'in_work' && $POSITION->user_access != 1){
					echo ' not_edit';
				}
			 ?>" id="order_art_edit_centr" >
				<div id="ja--image-gallety-togle" data-id="<?php echo $POSITION->position['id']; ?>" <?php 
				if($POSITION->position['show_img'] == 0){
					echo 'class="hidden"';
				}
				?>  onclick="togleImageGallery(this)">
					<span class="triangle"></span>
				</div>
				<div id="edit_option_content"  style="display:block">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<?php 
									echo $POSITION->getCharacteristics();					
								?>
							</div>
							<div class="cell">
								<!-- <form id="fddtime_form">
								<div class="table" id="fddtime">
									<div class="row">
										<div class="cell">
											<strong>Дата отгрузки:</strong>
										</div>
										<div class="cell">
											<span id="btn_date_var" class="btn_var_std">варианты</span>
											<span id="btn_date_std" class="btn_var_std">стандартно</span>
											<input type="text" name="date" id="datepicker1">
											<input type="text" name="time" id="timepicker1">
											<input type="hidden" name="status_time_delivery" id="status_time_delivery">
										</div>
									</div>
									<div  class="row">
										<div class="cell">
											<strong>Изготовление р/д:</strong>
										</div>
										<div class="cell">
											<span id="btn_make_var" class="btn_var_std">варианты</span>
											<span id="btn_make_std" class="btn_var_std">стандартно</span>
											<input type="text" name="rd" id="fddtime_rd" value="10"> р/д
											
										</div>
									</div>
								</div>
								</form> -->
								
								<!-- <div id="technical_assignment">Техническое задание</div> -->
								
							</div>
						</div>
					</div>
				</div>
				<div id="edit_variants_content">
					<div id="variants_name">
						<table>
							<tr>
								<!-- <td>
									<ul id="new_variant_UL">
										<li id="new_variant">&nbsp;</li>
									</ul>
								</td> -->
								<td>
									<ul id="all_variants_menu">
										<!-- вставка кнопок вариантов -->
										<?php echo $POSITION->Variants->generate_variants_menu($variants_arr); ?>
									</ul>
								</td>
								<td>
									<ul>
										<li id="show_archive">
											<?php
												if(isset($_GET['show_archive'])){
													echo '<a data-true="1" href="'.str_replace('&show_archive', '', $_SERVER['REQUEST_URI']).'">Скрыть отказанные</a>';
												}else{
													echo '<a data-true="0" href="'.$_SERVER['REQUEST_URI'].'&show_archive">Показать отказанные</a>';

												}
											?>	
										</li>									
									</ul>
								</td>
							</tr>
						</table>
						
					</div>
					<!-- вставка блоков вариантов -->
					<?php echo $variants_content; ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>
<!-- <?php echo __FILE__; ?> -- END