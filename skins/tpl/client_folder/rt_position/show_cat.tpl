<!-- <?php echo __FILE__; ?> -- START-->
<link href="./skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>

<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>

<script type="text/javascript" src="libs/js/rt_position.js"></script>
<script type="text/javascript" src="./libs/js/rt_position_gen.js"></script>
<!-- <script type="text/javascript" src="../libs/js/jqGeneralScript.js"></script> -->


<script type="text/javascript" src="../libs/js/jsArticulus.js"></script>
<!--<span displayManaged="true" name="art" style="display:'.(isset($dispSetObj->art)?'none':'inline-block').'">арт.: <a href="/index.php?page=description&id='.@$id.'" target="_blank">'.@$pos_level['art'].'</a></span>-->
<script type="text/javascript">
    // uploudify 
$(document).ready(function() {    

    $("#uploadify").uploadify({
        method        : 'post',
        buttonText    : 'Добавить изображение...',
        formData      : {
            'timestamp' : '1430900188',
            'token'     : '5706ee40da63f684301236821796cd66',
            'article'   : '375190.80',
            'art_id'    : '32286',
            'add_image_ok'      : '1'
        },
        height        : 30,
        swf           : '../libs/php/uploadify.swf',
        uploader      : '',
        cancelImg     : 'skins/images/img_design/cancel.png',
        width         : 120,
        //auto          : false
        auto          : true,
        'onUploadSuccess' : function(file, data, response) {
            var img = jQuery.parseJSON(data);
            var dele = '<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'+HOST+'/admin/order_manager/?page=common&delete_img_from_base_by_id='+img.big_img_name+'|'+img.small_img_name+'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>';
            
            $('#articulusImagesPrevBigImg .carousel-wrapper .carousel-items').append('<div  class="carousel-block"><img class="articulusImagesMiniImg imagePr" alt="" height="60px" src="'+HOST+'/img/'+img.small_img_name+'" data-src_IMG_link="'+HOST+'/img/'+img.big_img_name+'">'+dele+'</div>')
            $("#status_r2")
                .addClass("success")
                .html('Файл ' + file.name + ' успешно загружен.')
                .fadeIn('fast')
                .delay(3000)
                .fadeOut('slow');
            //$("#upload_more_images").hide();
                
            },
                
        'width'    : 200
    });
});
</script>


<div id="order_art_edit">
	<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"><a href="?page=client_folder&query_num=<?php echo  $order_num; ?>&client_id=<?php echo $client_id; ?>"></a></li>
			<li id="claim_number" data-order="<?=$order_num_id;?>">Запрос № <?=$order_num;?></li>
			<li id="claim_date"><span>от <?=$order_num_date;?></span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>
			<li id="query_theme_block"><span>Тема:</span> <input id="query_theme_input" class="query_theme" data-id="<?=$Order['RT_LIST_ID'];?>" type="text" value="<?=$Order['theme']?>" onclick="fff(this,'Введите тему');"></li>
			<li style="float:right"><span data-rt_list_query_num="<?php  echo $order_num; ?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($order_num); ?> "></span></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul><!-- 
			<li title="порядковый номер позиции в заказе">Позиция № 1</li> -->
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span>Каталог</li>
			<li id="status_art_z"><div>Статус <span>В работе</span></div></li>
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">
			<div class="cell b_r" id="order_art_edit_left" >
				<!-- image block show.tpl -->
				<div id="articulusImages">
		            <?php echo $color_variants_block;$alt='';// $alt = altAndTitle($name); ?>
		            
		            <div id="articulusImagesBigImg">
		                <div class="showImagegallery"></div>
		                <img id='img_for_item_<?php echo '$id'; ?>' src='<?php echo $images_data['main_img_src']; ?>' itemprop="image"  alt='
		                <?php echo '$alt'; ?>' title="<?php echo '$h1'; ?>" style='max-width: 286px;
		max-height: 300px;'>
		            </div>
		            <div id="articulusImagesPrevBigImg"> 
		                <?php echo $images_data['previews_block']; ?>
		                <!-- загрузка изображения на сервер -->
		                <div id="status_r2" style="width:90%; display:none; margin-bottom:10px; margin-top:15px; background-color:#FF9091; color:#fff; text-align:center"></div>  
		                <div id="upload_more_images" style="width:100%; margin:15px 0; display:none">
		                    <form>
		                        <div id="queue"></div>
		                        <input id="uploadify" name="file_upload" type="file" multiple>
		                    </form>
		                    

		    			</div> 
		    			<!--// загрузка изображения на сервер -->               
		            </div>
		        </div>
		        <!-- // image block show.tpl -->
				<?php
					
				?>
			</div>
			<div class="cell" id="order_art_edit_centr">
				<div id="edit_option_content"  style="display:block">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<strong>Характеристики изделия:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell"><a target="_blank" href="<?php echo identify_supplier_href($articul['art']);  ?>">Артикул</a> <?=$link_of_the_site;?></div>
													<div class="cell"><?php echo $articul['art']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Номенклатура</div>
													<div class="cell"><?php echo $art_name; ?></div>
												</div>
												<div class="row">
													<div class="cell">Бренд</div>
													<div class="cell"><?php echo $articul['brand']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Резерв</div>
													<div class="cell">
														<input type="text" id="rezerv_save" data-id="<?php echo $info_main['id']; ?>" value="<?php echo base64_decode($info_main['number_rezerv']); ?>" placeholder="№ резерва">
														</div>
												</div>
											</div>
										</div>
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Цвет</div>
													<div class="cell"><?php echo $art_colors; ?></div>
												</div>
												<div class="row">
													<div class="cell">Материал</div>
													<div class="cell"><?php echo $art_materials; ?></div>
												</div>
												<div class="row">
													<div class="cell">вид нанесения</div>
													<div class="cell"><?php echo $print_names; ?></div>
												</div>
											</div>
										</div>
									</div>
								</div>								
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
										<?php echo $POSITION_GEN->POSITION_CATALOG->generate_variants_menu($variants_arr); ?>
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
<!-- <?php echo __FILE__; ?> -- END-->
