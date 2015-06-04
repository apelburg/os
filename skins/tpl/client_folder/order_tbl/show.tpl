<!-- begin skins/tpl/client_folder/rt/show.tpl --> 
<link href="<?php  echo HOST; ?>/skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/__rt_vremenno.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/checkboxes.css" rel="stylesheet" type="text/css">
<style type="text/css">
	.cabinet_general_content_row,.dop_usl_tbl{width: 100%; border-collapse: collapse;}
	.cabinet_general_content_row tr td,.cabinet_general_content_row tr th{border:1px solid #f4f4f4; padding: 5px; font-size: 12px}
	.cabinet_general_content_row tr td .dop_usl_tbl tr td{border:1px solid #DFDFDF; padding: 2px; font-size: 14px}
	.itogo{font-weight: bold; font-size: 16px; float: right;}
	.itogo_n{font-weight: bold; font-size: 14px; float: right;}
	.itogo_n_no_bold{float: right;}
	.cabinet_general_content_row tr td select{width: 150px}
	.cabinet_general_content_row tr td span{padding-right: 3px}
</style>

<script type="text/javascript">
	// сохраняем статус услуги
	$(document).on('change','.dop_usl_tbl select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().parent().attr('data-id');
		var value = $(this).val();
		
		$.post('', {
			AJAX:'change_status_uslugi',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});
	// схраняем статус заказа
	$(document).on('change','#status_oreder_chenge select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().attr('data-request_id');
		var value = $(this).val();
		
		$.post('', {
			AJAX:'change_status_order',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});

	//сохраняем статусы снабов и менеджеров
	$(document).on('change','#info_for_order_list .status_men select ,#info_for_order_list .status_snab select',function(){
		// записываем id строки позиции
		var row_id = $(this).parent().parent().attr('data-id_order_main_rows');
		var value = $(this).val();
		// в данном случае класс имеет название колонки в базе
		// поэтому менять название колонки или класса не рекомендуется
		var column = $(this).parent().attr('class');
		
		$.post('', {
			AJAX:'change_status_snab_and_men',
			row_id:row_id,
			value:value,
			column:column
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});


</script>
<div class="scrolled_tbl_container">
	<table class="cabinet_general_content_row" id="info_for_order_list">
		
		<?php
		echo get_uslugi(0);  //ВЫГРУЗИТ ВЕСЬ СПИСОК ДОСТУПНЫХ УСЛУГ
			echo $order_tbl;		
		?>		
	</table> 
</div>
<!-- end skins/tpl/client_folder/rt/show.tpl -->