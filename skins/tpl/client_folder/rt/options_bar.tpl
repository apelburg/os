<!-- begin skins/tpl/clients/client_details_field_general.tpl -->          
<div id="order_art_edit">
<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number">Запрос №<?php  echo $query_num; ?></li>
			<li id="claim_date"><span>от 12.11.15 19:38</span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="art_name_topic"><span>Тема:</span> <?php echo $theme; ?></li>
		</ul>
	</div>
	<div id="options_bar" style="background-color:#92b73e;">
		<ul>
			<li>Позиции № 1</li>
			<li>В работе select</li>
			<li>Каталожные</li>
            <li>Не принятые</li>
            <li>2 п</li>
            <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=business_offers&query_num=<?php  echo $query_num; ?>&client_id=1894">Коммерческие предложения</a></li>
		</ul>
	</div>
 </div>    
<!-- end skins/tpl/clients/client_details_field_general.tpl -->
 