<!-- <?php echo __FILE__; ?> -- START-->
<link href="./skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>

<!--//пригодится тут в будущем <script type="text/javascript" src="libs/js/forms__js.js"></script> -->
<!-- 
<script type="text/javascript" src="libs/js/order_art_edit.js"></script>


<script type="text/javascript" src="../libs/js/jsArticulus.js"></script> -->

<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>




<script type="text/javascript" src="./libs/js/rt_position_no_cat.js"></script>
<script type="text/javascript" src="./libs/js/rt_position_gen.js"></script>
<script type="text/javascript" src="./libs/js/standard_response_handler.js"></script>


<div id="order_art_edit">
	<div id="info_string_on_query" data-id="<?=$Order['RT_LIST_ID']?>">
		<ul>
			<li id="back_to_string_of_claim"><a href="?page=client_folder&query_num=<?php echo  $order_num; ?>&client_id=<?php echo $client_id; ?>"></a></li>
			<li id="claim_number" data-order="<?php echo  $order_num_id; ?>">Запрос № <?php echo  $order_num; ?></li>
			<li id="claim_date"><span>от <?php echo $order_num_date; ?></span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="query_theme_block"><span>Тема:</span>
				<input id="query_theme_input" class="query_theme" data-id="<?=$Order['RT_LIST_ID'];?>" type="text" value="<?=$Order['theme']?>" onclick="fff(this,'Введите тему');"></li>
		
			<li style="float:right"><span data-rt_list_query_num="<?php  echo $order_num; ?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($order_num); ?> "></span></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<!-- <li title="порядковый номер позиции в заказе">Позиция № 1 (Это нужно????)</li> -->
			<!-- <li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span><?php if(isset($POSITION_GEN->FORM->arr_type_product[$type_product]['name'])){echo $POSITION_GEN->FORM->arr_type_product[$type_product]['name'];} ?></li> -->
			<!-- <li><span>доп инфо: </span>Тендер</li> -->
			<li><span>снабжение: </span><?php echo Manager::get_snab_name_for_query_String($snab_id); ?></li>
			<!-- <li><span>статус позиции: </span>Расчитано</li> -->
			<?php
				// получаем кнопки
				//echo $POSITION_NO_CAT->get_top_funcional_byttun_for_user_Html();
			?>
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">			
			<div class="cell" id="order_art_edit_centr">
				<!--общая информация по позиции и комментарии к этой инфе -->
				
				<!--варианты расчётов от снабжения по позиции -->
				<div id="edit_variants_content">

					
					<?php

					echo $POSITION_GEN->POSITION_NO_CATALOG->get_all_on_calculation_Html($type_product);

					?>
					
				</div>

				<!-- <div id="edit_option_content">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<strong>Запрос, полная информация:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<?php 
												//echo $POSITION_GEN->POSITION_NO_CATALOG->dop_info_no_cat_Html($dop_info_no_cat,$type_product);
											?>
										</div>
										
									</div>
								</div>								
							</div>
							<div class="cell left_bord">			
								<div id="inform_for_variant_head">Вариант № <span id="inform_for_variant_number">1</span>, характеристика изделия:</div>
								<div id="inform_for_variant"></div>
											
							</div>
						</div>
					</div>
				</div> -->
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>
<!-- <?php echo __FILE__; ?> -- END-->
