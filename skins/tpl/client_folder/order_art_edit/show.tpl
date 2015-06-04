<link href="./skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>

<script type="text/javascript" src="libs/js/order_art_edit.js"></script>
<!-- <script type="text/javascript" src="../libs/js/jqGeneralScript.js"></script> -->
<script type="text/javascript" src="../libs/js/jquery.uploadify.min.js"></script>

<script type="text/javascript" src="../libs/js/jsArticulus.js"></script>

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
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number" data-order="<?php echo  $order_num_id; ?>">Запрос № <?php echo  $order_num; ?></li>
			<li id="claim_date"><span>от <?php echo $order_num_date; ?></span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="art_name_topic"><span>Тема:</span> <?php echo $articul['name']; ?></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<li title="порядковый номер позиции в заказе">Позиция № 1</li>
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
		                    
		                    <!--<a href="javascript:$('#uploadify').uploadifyUpload();">Загрузить файлы.</a>-->
		    			</div> 
		    			<!--// загрузка изображения на сервер -->               
		            </div>
		        </div>
		        <!-- // image block show.tpl -->
				<?php
					
				?>
			</div>
			<div class="cell" id="order_art_edit_centr">
				<div id="edit_option_content">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<strong>Характеристики изделия:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Артикул</div>
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
													<div class="cell"><?php echo $art_get_print_mode; ?></div>
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
								<td>
									<ul id="new_variant_UL">
										<li id="new_variant">&nbsp;</li>
									</ul>
								</td>
								<td>
									<ul id="all_variants_menu">
										<!-- вставка кнопок вариантов -->
										<?php echo $ARTICUL->generate_variants_menu($variants,$dop_enable); ?>
									</ul>
								</td>
								<td>
									<ul>
										<li id="choose_end_variant"><span class="chenged_text">Редактор вариантов</span>
											<div id="menu_for_variants_status">
												<div class="menu_for_variants_status_menu_name">Применить для текущего</div>
												<div>
													<ul>
														<li  data-anyone="one" class="green">
															<span class="traffic_lights_green">
																<span></span>
															</span>
															Назначить green
														</li>
														<li  data-anyone="one" class="grey">
															<span class="traffic_lights_grey">
																<span></span>
															</span>
															Назначить grey
														</li>
														<li  data-anyone="one" class="red">
															<span class="traffic_lights_red">
																<span></span>
															</span>
															Назначить red
														</li>
													</ul>
												</div>
												<div  class="menu_for_variants_status_menu_name">Применить для остальных</div>
												<div>
													<ul>
														<li data-anyone="any" class="green">
															<span class="traffic_lights_green">
																<span></span>
															</span>
															Назначить green
														</li>
														<li data-anyone="any" class="grey">
															<span class="traffic_lights_grey">
																<span></span>
															</span>
															Назначить grey
														</li>
														<li data-anyone="any" class="red">
															<span class="traffic_lights_red">
																<span></span>
															</span>
															Назначить red
														</li>
													</ul>
												</div>
											</div>
										</li>
										<li id="show_archive">
											<?php
												if(isset($_GET['show_archive'])){
													echo '<a data-true="1" href="'.str_replace('&show_archive', '', $_SERVER['REQUEST_URI']).'">Скрыть архив</a></li>';
												}else{
													echo '<a data-true="0" href="'.$_SERVER['REQUEST_URI'].'&show_archive">Показать архив </a></li>';

												}
											?>										
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

