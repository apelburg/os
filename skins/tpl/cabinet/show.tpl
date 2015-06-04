<!-- begin skins/tpl/cabinet/show.tpl -->  
<link href="./skins/css/cabinet.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript">
//календарь
$(document).ready(function() {
	

	$('.payment_date').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.parent().parent().attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_payment_date',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				console.log(data);
			});
		},
	 	format:'d.m.Y',
	 	
	});
});


// сохраняем поле ОПЛАЧЕНО
	$(document).on('change','.buch_status_select select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		
		$.post('', {
			AJAX:'buch_status_select',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});

	// схраняем статус заказа
	$(document).on('change','.select_global_status select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		
		$.post('', {
			AJAX:'select_global_status',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});


/*
// Cвернуть строку запроса
$(document).on('click','#cabinet_general_content .show_hide',function() {
	$(this).parent().hide();
	$(this).parent().prev().find('td:nth-of-type(1)').addClass('show');
});

// Развернуть строку запроса
$(document).on('click','#cabinet_general_content .cabinett_row_show',function() {
	$('.query_detail').css('display','none');
	//$(this).removeClass('show');
	$(this).parent().next().show('fast');
});

*/


//БУХГАЛТЕРИЯ START
$(document).on('keyup','.invoice_num:focus',function(){
	// записываем id строки позиции
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();
	
	$.post('', {
		AJAX:'change_invoce_num',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})


$(document).on('keyup','.payment_status_span:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = $(this).html();

	// подсчитываем процент оплаты
	var all_summ = Number($(this).parent().next().find('span').html());
	var percent = Number($(this).html())*100/all_summ;	
	$(this).parent().prev().find('span').html(percent.toFixed(2));

	$.post('', {
		AJAX:'change_payment_status',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})

$(document).on('keyup','.number_payment_list:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();

	$.post('', {
		AJAX:'number_payment_list',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})

// номер TTH
$(document).on('keyup','.change_ttn_number:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();

	$.post('', {
		AJAX:'change_ttn_number',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})
// отгружено
$(document).on('keyup','.change_delivery_tir:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = Number($(this).html());
	var max_tir = Number($(this).parent().next().html());
	if(max_tir<value){
		$(this).html(max_tir);
		value = max_tir;
	}

	$.post('', {
		AJAX:'change_delivery_tir',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})
//БУХГАЛТЕРИЯ END

//СНАБ START
	// схраняем статус снаб
	$(document).on('change','.status_snab select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		
		$.post('', {
			AJAX:'change_status_snab',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
		});
	});
//СНАБ END

</script>
<div class="table" id="cabinet">
	<div class="row">
		<div class="cell" id="cabinet_left_coll_menu">
			<div id="cabinet_top_menu1"></div>
			<ul id="cabinet_left_menu">
				<?php echo $menu_left; ?>
			</ul>
		</div>
		<div class="cell" id="cabinet_central_panel">
			<div id="cabinet_top_menu">
				<ul id="central_menu">
					<?php echo $menu_central; ?>
				</ul>
			</div>
			<div id="cabinet_general_content">
				
				<?php echo $content; ?> 				
				</table>
			</div>
		</div>
	</div>
</div>
<!-- end skins/tpl/cabinet/show.tpl -->
 
