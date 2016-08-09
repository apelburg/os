//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- start
//////////////////////////////////
	//стандартный обработчик ответа AJAX
	function standard_response_handler(data){
		if(data['response']=='show_new_window'){
			title = data['title'];// для генерации окна всегда должен передаваться title
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title,height,width);
			window_preload_del();
		}
		if(data['response']=='show_new_window_simple'){
			title = data['title'];// для генерации окна всегда должен передаваться title
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			show_simple_dialog_window(Base64.decode(data['html']),title,height,width);
			window_preload_del();
		}

		if(data['function'] !== undefined){ // вызов функции... если требуется
			
			if($.isArray(data['function'])){
				count = data['function'].length;
				for (var i = count - 1; i >= 0; i--) {
					window[data['function'][i]['function']](data['function'][i]);
				};
				window_preload_del();
			}else{
				window[data['function']](data);
			}
			
		}

		if(data['function2'] !== undefined){ // вызов функции 2... если требуется
			window[data['function2']](data);
			window_preload_del();
		}

		if(data['function3'] !== undefined){ // вызов функции 3... если требуется
			window[data['function3']](data);
			window_preload_del();
		}

		if(data['response'] != "OK"){ // вывод при ошибке
			console.log(data);
		}
		
		if(data['error']  !== undefined){ // на случай предусмотренной ошибки из PHP
			alert(data['error']);
		}
	}


	// показать анимацию загрузки траницы
	function window_preload_add(){
		if(!$('#preloader_window_block').length){
			var object = $('<div/>').attr('id','preloader_window_block'); object.appendTo('body')
		}	
	}
	// скрыть анимацию загрузки траницы
	function window_preload_del(){
		if($('#preloader_window_block').length){
			$('#preloader_window_block').remove();
		}	
	}

	//////////////////////////
	// ОКНА
	//////////////////////////
		// показать окно № 1
		function show_dialog_and_send_POST_window(html,title,height,width){
			height_window = height || 'auto';
			width = width || '1000';
			title = title || '*** Название окна ***';
			var buttons = new Array();
			buttons.push({
			    text: 'OK',
			    click: function() {
			    	var serialize = $('#dialog_gen_window_form form').serialize();
			    	var obj = $(this);
			    	$('#general_form_for_create_product .pad:hidden').remove();
				    $.post('', serialize, function(data, textStatus, xhr) {
				    	if(data['response'] != 'false'){
				    		obj.dialog( "destroy" );
				    	}
				    		
						standard_response_handler(data);
					},'json');				    	
			    }
			});

			if($('#dialog_gen_window_form').length==0){
				$('body').append('<div style="display:none" id="dialog_gen_window_form"></div>');
			}
			$('#dialog_gen_window_form').html(html);
			$('#dialog_gen_window_form').dialog({
		          width: width,
		          height: height_window,
		          modal: true,
		          title : title,
		          autoOpen : true,
		          buttons: buttons          
		        });
		}

		// показать окно № 2  
		// используется в случае, когда нужно 2(два) одновременно открытых окна
		function show_dialog_and_send_POST_window_2(html,title,height,width){
			height_window = height || 'auto';
			width = width || '1000';
			title = title || '*** Название окна ***';
			var buttons = new Array();
			buttons.push({
			    text: 'OK',
			    click: function() {
			    	var serialize = $('#dialog_gen_window_form2 form').serialize();
			    	
			    	$('#general_form_for_create_product .pad:hidden').remove();
				    $.post('', serialize, function(data, textStatus, xhr) {
				    	$('#dialog_gen_window_form2').html('');
						$('#dialog_gen_window_form2').dialog( "destroy" );				
						
						standard_response_handler(data);
					},'json');				    	
			    }
			});

			if($('#dialog_gen_window_form2').length==0){
				$('body').append('<div style="display:none" id="dialog_gen_window_form2"></div>');
			}
			$('#dialog_gen_window_form2').html(html);
			$('#dialog_gen_window_form2').dialog({
		          width: width,
		          height: height_window,
		          modal: true,
		          title : title,
		          autoOpen : true,
		          buttons: buttons          
		        });
		}

		// простое диалоговое окно с кнопкой закрыть
		function show_simple_dialog_window(html,title,height,width){
			var window_num = $('.ui-dialog').length;

			height_window = height || 'auto';
			width = width || '1000';
			title = title || '*** Название окна ***';
			var buttons = new Array();
			buttons.push({
			    text: 'Закрыть',
			    click: function() {
					// подчищаем за собой
					$(this).dialog("destroy");
			    }
			});			

			$('body').append('<div style="display:none" id="dialog_gen_window_form_'+window_num+'"></div>');			
			$('#dialog_gen_window_form_'+window_num+'').html(html);
			$('#dialog_gen_window_form_'+window_num+'').dialog({
		          width: width,
		          height: height_window,
		          modal: true,
		          title : title,
		          autoOpen : true,
		          buttons: buttons          
		        });
		}
			

	////////////////////////////////////////////////
	//	функции вызываемые из PHP  --- start ---  //
	////////////////////////////////////////////////

		// вывод сообщения из PHP в alert
		function php_message(data){
			alert(data.text);
		}

		// вывод системного сообщения 
		// function echo_message(data){

		// 	$("<li/>", {
		// 	      "class": data.message_type,
		// 	      "css":{"opacity":1,"top":0},
		// 	      click: function(){
		// 	          $(this).animate({opacity:0},'fast',function(){$(this).remove()});
		// 	      }
		// 	}).append(Base64.decode(data.message)).appendTo("#apl-notification_center").fadeIn('slow', 
		//         function(){
		//             var el = jQuery(this);
		//             setTimeout(function(){
		//                 el.fadeOut('slow',
		//                     function(){
		//                         jQuery(this).remove();
		//                     });
		//             }, 7000);
		//     });

		// }	

		function php_message_alert(data){
			console.log(data);
			alert(Base64.decode(data['message']));
		}
		// вывод сообщения из PHP в модальное окно
		function php_message_dialog(data){ // а оно еще нужно ???
			// show_simple_dialog_window(Base64.decode(data['message']),data['title']);
			show_simple_dialog_window('Необходимо переделать на стандартный выход.<br> Алексей',data['title']);
		}
		// перезагрузка окна
		function window_reload(data) {
			location.reload();
		}
		// переадресация из php
		function location_href(data){
			window.location.href = data.href;
		}

		$(document).keydown(function(e) {	
			if(e.keyCode == 27){//ESC		
				window_preload_del();
			}	
		});
//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- end
//////////////////////////////////

// комманды менеджера на "оригинал-макет готов"

$(document).on('click', '.js-modal_menu-maket_what', function(event) {
	event.preventDefault();
	var id_row = $(this).find('.greyText').attr('data-id');
	var position_id = $(this).attr('data-pisition_id');
	// alert(position_id);
	$.post('', {
		AJAX:"get_modal_menu_maket_what",
		id_row:id_row,
		position_id:position_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});
// комман
$(document).on('click', '.js-modal_menu-design_what', function(event) {
	event.preventDefault();
	var id_row = $(this).find('.greyText').attr('data-id');
	$.post('', {
		AJAX:"get_modal_menu_design_what",
		id_row:id_row
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});
// кнопка "Макет принят" для СНАБ
$(document).on('click', '.js-button-maket_is_adopted button', function(event) {
	event.preventDefault();
	var id_row = $(this).attr('data-id');
	var position_id = $(this).attr('data-position_id');
	var new_status = 'услуга выполнена';
	$(this).replaceWith('<span class="greyText">'+new_status+'</span>');
	$.post('', {
		AJAX:"choose_service_status",
		id_row:id_row,
		position_id:position_id,
		value:new_status
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// редактирование входящей стоимости услуги из окна фин. инфо
// edit_price_in_for_postfactum_service
$(document).on('keyup', '.change_tirage_for_postfactum_added_service', function(event) {
	event.preventDefault();
	var value = $(this).val();
	var row_id = $(this).attr('data-id');
	$(this).parent().next().find('.change_price_in_for_postfactum_added_service').attr('data-quantity',value);
	$.post('', {
		AJAX:'edit_price_in_for_postfactum_service',
		value:value,
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// редактирование тиража услуги из окна фин. инфо
// edit_quantity_for_postfactum_service
$(document).on('keyup', '.change_price_in_for_postfactum_added_service', function(event) {
	event.preventDefault();
	var quantity = Number($(this).attr('data-quantity'));
	var row_id = $(this).attr('data-id');
	var for_how = $(this).attr('data-for_how');
	var value = Number($(this).val());
	$(this).prev().html((for_how == 'for_one')?(value * quantity):value );
	recalculate_a_detailed_article_on_the_price_of_positions();
	$.post('', {
		AJAX:'edit_quantity_for_postfactum_service',
		value:value,
		row_id:row_id,
		quantity:quantity,
		for_how:for_how
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// показать / скрыть каталожные позиции 
$(document).on('click', '.click_me_and_show_catalog', function(event) {
	$(this).parent().parent().find('tr.cat_8').toggle('fast');
});

//календарь
$(document).ready(function() {
	// дата сдачи заказа
	$('.date_of_delivery_of_the_order').datetimepicker({
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
				AJAX: 'change_date_of_delivery_of_the_order',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);

			},'json');
		},
	 	format:'d.m.Y',	 	
	});

	// дата сдачи товара по спецификации
	$('.date_of_delivery_of_the_specificate').datetimepicker({
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
			var row_id = $input.attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'date_of_delivery_of_the_specificate',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);

			},'json');
		},
	 	format:'d.m.Y',	 	
	});

	// ожидаемая дата прихода продукции на склад
	$('.waits_products_div input').datetimepicker({
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
			var row_id = $input.parent().attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_waits_products_div_input',
				row_id: row_id, // cab_main_rows_id
				date: date // введённое значение
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		},
	 	format:'d.m.Y',	 	
	});	

	// дата утверждения макета
	$('.approval_date').datetimepicker({
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
			var row_id = $input.attr('data-id');
			var dop_data_id = $input.parent().parent().attr('data-cab_dop_data_id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_approval_date',
				row_id: row_id,
				date: date,
				dop_data_id:dop_data_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);

			},'json');
		},
	 	format:'d.m.Y',	 	
	});
});


// Изменение глобального статуса ЗАКАЗА / ПРЕДЗАКАЗА
$(document).on('change', '.choose_statuslist_order_and_paperwork', function(event) {
	var row_id = $(this).parent().parent().attr('data-id'); //main_rows_id
	var value = $(this).val();
	var obj = $(this).parent().parent();
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_order_and_paperwork',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		// replace_query_row_obj(obj);
		reload_paperwork_tbl();
	},'json');
});

// Изменение глобального статуса ЗАКАЗА / ПРЕДЗАКАЗА
$(document).on('change', '.choose_statuslist_sklad', function(event) {
	var row_id = $(this).attr('data-id'); //main_rows_id
	var value = $(this).val();
	var document_id = $(this).attr('data-document_id');
	var order_id = $(this).attr('data-order_id');
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_sklad',
		row_id:row_id,
		value:value,
		order_id:order_id,
		document_id:document_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});



// запуск в работу заказа
$(document).on('click', '.in_operation', function(event) {
	var row_id = $(this).parent().parent().attr('data-id'); //main_rows_id
	var obj = $(this).parent().parent();
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_order_and_paperwork',
		row_id:row_id,
		value:'in_work'
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		// replace_query_row_obj(obj);
		reload_paperwork_tbl();
	},'json');
});



// меняем статус снабжения к позиции
/*
	СНАБЖЕНИЕ РАБОТАЕТ ПО ПОЗИЦИЯМ
*/
$(document).on('change', '.choose_statuslist_snab', function(event) {
	var row_id = $(this).attr('data-id'); //main_rows_id
	var value = $(this).val();
	
	// пишем исключение показа и скрытия ожидаемой даты поставки в зависимости от выбранного статуса
	if( value == "waits_products"){
		$(this).parent().find('.waits_products_div').show('fast');
	}else{
		$(this).parent().find('.waits_products_div').hide('fast');
		$(this).parent().parent().find('.waits_products_div input').val('');
	}

	var order_id = $(this).attr('data-order_id');
	// отправляем запрос
	$.post('', {
		AJAX:'change_status_snab',
		row_id:row_id,
		value:value,
		order_id:order_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// редактирование срока по ДС
$(document).on('keyup', '.deadline', function(event) {
	var value = $(this).html();
	var row_id = $(this).parent().attr('data-id');
	$.post('', {
		AJAX:'change_deadline_value',
		row_id:row_id,
		value:value

	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
	});
	check_loading_ajax();
});

// сохраняем поле ОПЛАЧЕНО
$(document).on('change','.buch_status_select select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		var obj = $(this).parent().parent();
		// window_preload_add();
		$.post('', {
			AJAX:'buch_status_select',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			// console.log(data);
			standard_response_handler(data);
		},'json');
	});

// схраняем статус заказа
$(document).on('change','.select_global_status select',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = $(this).val();
	var obj = $(this).parent().parent();
	window_preload_add();
	$.post('', {
		AJAX:'select_global_status',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
		replace_query_row_obj(obj);
	});
});



// свернуть/развернуть строку ЗАПРОСА
$(document).on('click','#cabinet_general_content .cabinett_row_hide',function() {	
	// if($(this).hasClass('show')){
	// 	$(this).parent().attr('rowspan','2').parent().next().show();
	// 	$(this).removeClass('show');


	// 	var order_id = $(this).parent().parent().attr('data-id');
	// 	// сохраняем положение раскрытого/скрытого заказа
	// 	$.post('',{
	// 		AJAX: 'open_close_order',
	// 		order_id: order_id,
	// 		open_close: '1'
	// 	}, function(data, textStatus, xhr) {
	// 		standard_response_handler(data);
	// 	},'json');
	// }else{
	// 	$(this).parent().attr('rowspan','1').parent().next().hide();
	// 	$(this).addClass('show');
	// 	var order_id = $(this).parent().parent().attr('data-id');
	// 	// сохраняем положение раскрытого/скрытого заказа
	// 	$.post('',{
	// 		AJAX: 'open_close_order',
	// 		order_id: order_id,
	// 		open_close: '0'
	// 	}, function(data, textStatus, xhr) {
	// 		standard_response_handler(data);
	// 	},'json');
	// }	
	// замена класса строки заказа / запроса
	if( $(this).parent().parent().hasClass('row_open') ){
		$(this).parent().parent().removeClass('row_open').addClass('row_close');
	}else if( $(this).parent().parent().hasClass('row_close') ){
		$(this).parent().parent().removeClass('row_close').addClass('row_open');
	}

	if($(this).hasClass('show')){ // если поле скрыто
		tbl_row_open($(this));
	}else{ // если поле открыто
		tbl_row_close($(this));
	}
});

// свернуть/развернуть строку ЗАКАЗА
$(document).on('click','#cabinet_general_content .cabinett_row_hide_orders',function() {	
	// замена класса строки заказа / запроса
	if( $(this).parent().parent().hasClass('row_open') ){
		$(this).parent().parent().removeClass('row_open').addClass('row_close');
	}else if( $(this).parent().parent().hasClass('row_close') ){
		$(this).parent().parent().removeClass('row_close').addClass('row_open');
	}

	if($(this).hasClass('show')){ // если поле скрыто
		tbl_row_open($(this));
	}else{ // если поле открыто
		tbl_row_close($(this));
	}	
});


// раскрыть строку заказа
function tbl_row_open(obj){
	var order_id = obj.parent().parent().attr('data-id');
	obj.removeClass('show');
	// запоминаем значение rowspan
	var rowspan = Number(obj.parent().attr('data-rowspan'));

	obj.parent().attr('rowspan',rowspan);

	// скрываем все строки
	obj = obj.parent().parent().next('tr');
	for (var i = 0; i < rowspan-1; i++) {
		obj.show();
		obj = obj.next('tr');
	};

	// сохраняем положение раскрытого заказа
	$.post('',{
		AJAX: 'open_close_order',
		order_id: order_id,
		open_close: '1'
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');

	
}

// свернуть строку заказа
function tbl_row_close(obj){
	var order_id = obj.parent().parent().attr('data-id');
	obj.addClass('show');
	// запоминаем значение rowspan
	var rowspan = Number(obj.parent().attr('rowspan'));
	// ставим rowspan 1, сохраняем заначение в тег
	obj.parent().attr('rowspan','1').attr('data-rowspan',rowspan);
	// скрываем все строки
	obj = obj.parent().parent().next('tr');
	for (var i = 0; i < rowspan-1; i++) {
		obj.hide();
		obj = obj.next('tr');
		console.log(obj.next('tr').html());
	};

	// сохраняем положение свёрнутого заказа
	$.post('',{
		AJAX: 'open_close_order',
		order_id: order_id,
		open_close: '0'
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');

	
}





//////////////////////////
//	БУХГАЛТЕРИЯ START
//////////////////////////
// $(document).on('keyup','.invoice_num:focus',function(){
// 	// записываем id строки позиции
// 	var row_id = $(this).parent().attr('data-id');
// 	var value = $(this).html();
	
// 	$.post('', {
// 		AJAX:'change_invoce_num',
// 		row_id:row_id,
// 		value:value
// 	}, function(data, textStatus, xhr) {
// 		console.log(data);
// 	});
// })

$(document).on('click', '#cabinet_general_content table tr td.buh_uchet', function(event) {
	var order_id = $(this).parent().attr('data-id');	
	get_buh_uchet_window(order_id);
});

$(document).on('click', '#replace_the_dialog_window', function(event) {
	var order_id = $(this).attr('data-id');	
	$('#'+$(this).parent().parent().attr('id')).remove();
	get_buh_uchet_window(order_id);
});



// function get_buh_uchet_window(order_id){
// 	window_preload_add();
// 	$.post('', {
// 		AJAX:'get_window_buh_uchet',
// 		order_id:order_id
// 	}, function(data, textStatus, xhr) {
// 		standard_response_handler(data);	
		
// 		//////////////////////////
// 		//	дата подписания спецификации
// 		//////////////////////////
// 			$('.date_specification_signed').datetimepicker({
// 				// minDate:new Date(),
// 				timepicker:false,
// 			 	dayOfWeekStart: 1,
// 				closeOnDateSelect:true,
// 				onChangeDateTime: function(dp,$input){// событие выбора даты
// 					// получение данных для отправки на сервер
// 					var row_id = $input.attr('data-id');
// 					var date = $input.val();

// 					$input.blur();
// 					//alert($input.attr('class'));
// 					$.post('', {
// 						// AJAX: 'change_payment_date',
// 						AJAX: 'change_date_specification_signed',
// 						row_id: row_id,
// 						date: date
// 					}, function(data, textStatus, xhr) {
// 						standard_response_handler(data);
// 					},'json');
// 				},
// 			 	format:'d.m.Y',
// 			});

// 		//////////////////////////
// 		//	смена даты выставления счёта
// 		//////////////////////////
// 			$('.date_create_the_bill').datetimepicker({
// 				// minDate:new Date(),
// 				timepicker:false,
// 			 	dayOfWeekStart: 1,
// 				closeOnDateSelect:true,
// 				onChangeDateTime: function(dp,$input){// событие выбора даты
// 					// получение данных для отправки на сервер
// 					var id_row = $input.attr('data-id');
// 					var date = $input.val();

// 					$input.blur();
// 					//alert($input.attr('class'));
// 					$.post('', {
// 						// AJAX: 'change_payment_date',
// 						AJAX: 'change_date_create_the_bill',
// 						id_row: id_row,
// 						date: date
// 					}, function(data, textStatus, xhr) {
// 						standard_response_handler(data);
// 					},'json');
// 				},
// 			 	format:'d.m.Y',
// 			});

// 		//////////////////////////
// 		//	дата возврата подписанной спецификации
// 		//////////////////////////
// 			$('.date_return_width_specification_signed').datetimepicker({
// 				// minDate:new Date(),
// 				timepicker:false,
// 			 	dayOfWeekStart: 1,
// 				closeOnDateSelect:true,
// 				onChangeDateTime: function(dp,$input){// событие выбора даты
// 					// получение данных для отправки на сервер
// 					var row_id = $input.attr('data-id');
// 					var date = $input.val();
// 					$input.blur();
// 					//alert($input.attr('class'));
// 					$.post('', {
// 						// AJAX: 'change_payment_date',
// 						AJAX: 'change_date_return_width_specification_signed',
// 						row_id: row_id,
// 						date: date
// 					}, function(data, textStatus, xhr) {
// 						standard_response_handler(data);
// 					},'json');
// 				},
// 			 	format:'d.m.Y',
// 			});	
// 	},'json');
// }




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
});
// // выводим окно с комментариями по удалению при клике на надпись Удалён
// $(document).on('click', '.why_this_doc_deleted', function(event) {
// 	var html = 'Причина удаления:<br>'+$(this).parent().find('.deleted_note').html();
// 	show_simple_dialog_window(html,'Информация об удалении.','auto', 350);
// });


//////////////////////////
//	сохранение номера счёта с таймингом  
//////////////////////////
	$(document).on('keyup', '.number_the_bill', function(event) {
		 timing_save_input('number_the_bill',$(this))
	});

	function number_the_bill(obj){// на вход принимает object input
	    var row_id = obj.attr('data-id');
	    $.post('', {
	        AJAX:'change_number_the_bill',
	        row_id:row_id,
	        value:obj.val()
	    }, function(data, textStatus, xhr) {
	    	standard_response_handler(data);
	        if(data['response']=="OK"){
	            // php возвращает json в виде {"response":"OK"}
	            // если ответ OK - снимаем класс saved
	            obj.removeClass('saved');
	        }else{
	            console.log('Данные не были сохранены.');
	        }
	    },'json');
	}

//////////////////////////
//	сохранение номера счёта-оферты с таймингом  
//////////////////////////
	$(document).on('keyup', '.number_the_bill_oferta', function(event) {
		$('#tabs ul li.check_the_anather_spec.checked a').html('№ '+$(this).val()+' от '+$(this).parent().find('.date_create_the_bill_oferta').val());
		// сохранение
		timing_save_input('number_the_bill_oferta',$(this))
	});

	function number_the_bill_oferta(obj){// на вход принимает object input
	    var row_id = obj.attr('data-id');
	    var doc_id = obj.attr('data-doc_id');
	    $.post('', {
	        AJAX:'change_number_the_bill_offerta',
	        row_id:row_id,
	        doc_id:doc_id,
	        value:obj.val()
	    }, function(data, textStatus, xhr) {
	    	standard_response_handler(data);
	        if(data['response']=="OK"){
	            // php возвращает json в виде {"response":"OK"}
	            // если ответ OK - снимаем класс saved
	            obj.removeClass('saved');
	        }else{
	            console.log('Данные не были сохранены.');
	        }
	    },'json');
	}
//////////////////////////
//	сохранение суммы счёта с таймингом  
//////////////////////////
	$(document).on('keyup', '.for_price_the_bill', function(event) {
		 timing_save_input('for_price_the_bill',$(this))
	});

	function for_price_the_bill(obj){// на вход принимает object input
	    var row_id = obj.attr('data-id');
	    $.post('', {
	        AJAX:'change_for_price_the_bill',
	        row_id:row_id,
	        value:obj.val()
	    }, function(data, textStatus, xhr) {
	    	standard_response_handler(data);
	        if(data['response']=="OK"){
	            // php возвращает json в виде {"response":"OK"}
	            // если ответ OK - снимаем класс saved
	            obj.removeClass('saved');
	        }else{
	            console.log('Данные не были сохранены.');
	        }
	    },'json');
	}

// вызов окна редактирования комментариев для счёта
$(document).on('click', '.buch_comments,.tz_text_new', function(event) {
	var onlyread = ($(this).hasClass('only_read'))?1:0;

	var row_id = $(this).attr('data-id');
	$.post('', {
		AJAX: 'get_the_comment_width_the_bill',
		row_id:row_id,
		onlyread:onlyread
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// удаление счёта
$(document).on('click', '.button.usl_del', function(event) {
	var row_id = $(this).attr('data-id');
	var obj = $(this).parent().parent();
	obj.find('.buch_comments').addClass('only_read');
	$(this).remove();
	obj.find('input').each(function(index, el) {
		var inp_val = $(this).val();
		$(this).replaceWith(inp_val);
	});
	obj.find('td').eq(1).append('<span class="why_this_doc_deleted">Удален</span>');

	obj.addClass('deleted');
	$.post('', {
		AJAX:'delete_the_bill',
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);

	},'json');
});

// // добавление счёта в бух учёте
// $(document).on('click', '#add_the_bill_link span', function(event) {
// 	var obj = $(this);
// 	var order_id = $(this).parent().attr('data-id');
// 	$.post('', {
// 		AJAX:'get_listing_type_the_bill',
// 		order_id: order_id,
// 		get_html_row_the_bill: 1
// 	}, function(data, textStatus, xhr) {
// 		standard_response_handler(data);
// 	},'json');
// });

// function add_new_bill_in_window(data){
// 	$('#container_from_the_bill table').append(Base64.decode(data['html']));
// }



//////////////////////////
//	БУХГАЛТЕРИЯ END
//////////////////////////

//////////////////////////
//	СНАБ START
//////////////////////////
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
//////////////////////////
//	СНАБ END
//////////////////////////
	

//////////////////////////
//	АДМИН start
//////////////////////////

// запрос - прикрепить / сменить менеджера
$(document).on('click', '.attach_the_manager,.attach_the_manager_any', function(event) {
	event.stopImmediatePropagation();
	var client_id = Number($(this).parent().parent().parent().find('.js--client_td').attr('data-id'));
	var manager_id = Number($(this).attr('data-id'));
	var rt_list_id = Number($(this).parent().parent().parent().attr('data-id'));
	$.post('', {
		AJAX:'get_a_list_of_managers_to_be_attached_to_the_request',
		client_id:client_id,
		manager_id:manager_id,
		rt_list_id:rt_list_id
	}, function(data, textStatus, xhr) {
		// show_dialog_and_send_POST_window(data,'Выбрать менеджера');
		standard_response_handler(data);
	},'json');
});

// запрос - назначить несколько менеджеров
// $(document).on('click', '.attach_the_manager_any', function(event) {
// 	var client_id = Number($(this).attr('data-client_id'));
// 	var row_id = Number($(this).attr('data-row_id'));
// 	var managers_id_str = $(this).find('.managers_id_str').html();
// 	$.post('', {
// 		AJAX:'get_choose_curators_edit',
// 		client_id:client_id,
// 		row_id:row_id,
// 		managers_id_str:managers_id_str

// 	}, function(data, textStatus, xhr) {
// 		// show_dialog_and_send_POST_window(data,'Выбрать менеджера');
// 		standard_response_handler(data);
// 	},'json');
// });


// запрос - прикрепить / сменить клиента
// вывод спика для выбора клиента, который будет прикреплён к запросу
$(document).on('click', '.attach_the_client', function(event) {
	var manager_id = Number($(this).parent().find('.attach_the_manager').attr('data-id'));
	var client_id = Number($(this).attr('data-id'));
	var rt_list_id = Number($(this).parent().attr('data-id'));
	var obj = $(this).parent();
	$.post('', {
		// AJAX:'get_a_list_of_clients_to_be_attached_to_the_request',
		AJAX:'get_client_sherch_form',
		client_id:client_id,
		manager_id:manager_id,
		rt_list_id:rt_list_id
	}, function(data, textStatus, xhr) {
		// show_dialog_and_send_POST_window(data,'Выбрать клиента',750);
		standard_response_handler(data);
		// replace_query_row_obj(obj);
		// location.reload();
	},'json');
});



// $(document).on('keydown','#js--window_client_sherch_form',function(event){
//     if(event.keyCode == 13) {
        
//     }
// });
				$(document).keydown(function(event) {
					if(event.keyCode == 13) {
						if( $("#js--window_client_sherch_form input").is(":focus") ){
							event.preventDefault();
					        search_and_show_client_list();
					        return false;
						}

						// если открыто окно поиска клиентов
						if($('#chose_client_tbl .checked').length>0){
							
							$('#chose_client_tbl').parent().parent().find('.ui-dialog-buttonpane .ui-dialog-buttonset button').click();
						}
					}
				});






//поиск по клиенту
// $(document).on('keyup', '#js--window_client_sherch_form input', function(event) {
// 	event.preventDefault();
// 	search_and_show_client_list();
// });


function search_and_show_client_list(){
	var search_name = $('#js--window_client_sherch_form input[name="client_name_search"]').val();
	// if(search_name == 'all' || search_name == 'все' || ($('#js--window_client_sherch_form input[name="client_id"]').length > 0 && Number($('#js--window_client_sherch_form input[name="client_id"]').val()) > 0 )){
		var serialize = $('#js--window_client_sherch_form').serialize();
		$.post('', serialize
			//{
			// AJAX:$('#js--window_client_sherch_form input[name="AJAX"]').val(),
			// client_id:$('#js--window_client_sherch_form input[name="client_id"]').val(),
			// client_name_search:$(this).val()
		//}
		, function(data, textStatus, xhr) {
			if(data['response'] == 'OK'){
				if($('#chose_client_tbl').length > 0){
					$('#chose_client_tbl').replaceWith(Base64.decode(data['html']));
					console.log(Base64.decode(data['html']));
				}else{
					console.log('654654654qqqqq');
					$('#js--window_client_sherch_form').after(Base64.decode(data['html']))
				}
				
			}				    		
			standard_response_handler(data);
		},'json');		
	// }
}

// отработка клика по таблице выбора менеджера для прикрепления к запросу
$(document).on('click', '#chose_manager_tbl table tr td', function(event) {
	if($(this).html()!=''){
		$('#chose_manager_tbl tr td').removeClass('checked');
		$(this).addClass('checked');
		// rt_list_id_
		var rt_list_id = $(this).parent().parent().parent().parent().find('input[name$="rt_list_id"]').val();
		// $('#rt_list_id_'+rt_list_id + ' .attach_the_manager').html($(this).html()).attr('data-id',$(this).attr('data-id'));
		var manager_id = $(this).attr('data-id');//alert($(this).parent().parent().parent().parent().html());
		$(this).parent().parent().parent().parent().find('input[name$="manager_id"]').val(manager_id);
	}
});

// отработка клика по таблице выбора менеджера для прикрепления к запросу
$(document).on('click', '#chose_client_tbl table tr td', function(event) {
	if($(this).html()!=''){
		$('#chose_client_tbl tr td').removeClass('checked');
		$(this).addClass('checked');
		// rt_list_id_
		var rt_list_id = $(this).parent().parent().parent().parent().find('input[name$="rt_list_id"]').val();
		// $('#rt_list_id_'+rt_list_id + ' .attach_the_client').html($(this).html()).attr('data-id',$(this).attr('data-id'));
		var manager_id = $(this).attr('data-id');//alert($(this).parent().parent().parent().parent().html());
		$(this).parent().parent().parent().parent().find('input[name$="client_id"]').val(manager_id);
	}
});

//////////////////////////
//	АДМИН end
//////////////////////////

function change_attache_manager(data){
	var id_row = '#rt_list_id_'+data['rt_list_id'];
	$(id_row).find('.attach_the_manager').attr('data-id',data['manager_id']).html(data['manager_name']);
	// if ($('#dialog_gen_window_form').length) {
	// 	$('#dialog_gen_window_form').remove();
	// };
}


//////////////////////////
//	МЕНЕДЖЕР start
//////////////////////////

// принять запрос в обработку
$(document).on('click', '.take_in_operation', function(event) {
	// var obj = $(this);
	// var obj_row = $(this).parent().parent();
	var rt_list_id = $(this).parent().parent().attr('data-id');
	var client_id = $(this).attr('data-client_id');
	var manager_id = $(this).attr('data-manager_id');

	$.post('', {
		AJAX: 'take_in_operation',
		rt_list_id:rt_list_id,
		manager_id:manager_id,
		client_id:client_id		
		}, function(data, textStatus, xhr) {
			if(data['response'] != 'OK'){
				alert(data);
			}else{
				standard_response_handler(data)
				// replace_query_row_obj(obj_row);
			}
	},'json');
	return false;
});



// взять в работу запрос

$(document).on('click', '.get_in_work', function(event) {
	event.preventDefault();
	var obj = $(this);
	if(Number($(this).parent().parent().find('.attach_the_client').attr('data-id')) == 0){
		echo_message_js('Сначала необходимо прикрепить клиента.','error_message')
	}else{
		var row_id = $(this).parent().parent().attr('data-id');
		$.post('', {
			AJAX: 'get_in_work',
			row_id:row_id
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
			// if(data['response'] == 'OK'){
			// // показываем что сменили статус и удаляем строку из дом модели
			// 	obj.html('в работе').delay(3000).parent().parent().addClass('remove_this_row').next().addClass('remove_this_row').parent().parent().find('.remove_this_row').remove();
				
			// }
		},'json');
	}
	return false;
});




//////////////////////////
//	МЕНЕДЖЕР end
//////////////////////////
// показать загрузку траницы


// запрос на обновление строки
// с отредактированными данными.... 
// сделано ВРЕМЕННО в целях экономии времени на проверку и смену всех данных в строке пооочерёдно
function replace_query_row_obj(obj){
	window_preload_add();
	var os__rt_list_id = obj.attr('data-id');
	// запоминаем rowspan
	// console.log('65654546');

	var rowspan = obj.find('.show_hide').attr('rowspan');
	// console.log(obj.find('.show_hide').attr('rowspan'));
	// console.log(obj);
	// console.log(rowspan);
	$.post('', {
		AJAX: 'replace_query_row',
		os__rt_list_id: os__rt_list_id,
		rowspan:rowspan
	}, function(data, textStatus, xhr) {
		if(data['response'] == 'OK'){
			
			// console.log(Base64.decode(data['html'])+' ++++++++++++++++++' + obj.html());
			obj.html(Base64.decode(data['html']));
			window_preload_del();
		}else{
			alert('что-то пошло не так');
			window_preload_del();
		}
	},'json');
}



function del_id_chose_supplier_id(data){
	$('#chose_supplier_id').removeAttr('id');
}
//////////////////////////
//	НАЗНАЧЕНИЕ ПОСТАВЩИКА 	
//////////////////////////
$(document).on('dblclick', '.change_supplier', function(event) {
	$(this).attr('id', 'chose_supplier_id');
	chose_supplier($(this));
});

function chose_supplier(obj){
	$.post('', {
		AJAX:'chose_supplier',
		id_dop_data: $('#chose_supplier_id').attr('data-id_dop_data'),
		already_chosen: $('#chose_supplier_id').attr('data-id'),
		suppliers_name:$('#chose_supplier_id').html()
	}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выбирите поставщика',$(window).height()/100*90);
	});
}

$(document).on('click', '#chose_supplier_tbl tr td', function(event) {
	if($(this).hasClass('checked')){
		$(this).removeClass('checked');
	}else{
		$(this).addClass('checked');
	}

	var arr_id = new Array();
	var arr_name = new Array();
	$('#chose_supplier_tbl tr td.checked').each(function(index, el) {
		arr_id.push($(this).attr('data-id'));
		arr_name.push($(this).html());
	});

	var str_id = arr_id.join(',');
	var str_name = arr_name.join(', ');
	console.log(str_id);

	$('#chose_supplier_tbl').parent().find('input[name="dop_data_id"]').val($('#chose_supplier_id').parent().attr('data-id'));
	$('#chose_supplier_tbl').parent().find('input[name="suppliers_id"]').val(str_id);
	$('#chose_supplier_tbl').parent().find('input[name="suppliers_name"]').val(str_name);

	$('#chose_supplier_id').html(str_name);
	$('#chose_supplier_id').attr('data-id',str_id);

});

//////////////////////////
//	ДОП/ТЕХ ИНФО
//////////////////////////
$(document).on('click', '.dop_teh_info', function(event) {
	var query_num = Number($(this).attr('data-query_num'));
	var order_num = Number($(this).attr('data-order_num'));
	var order_num_user = $(this).attr('data-order_num_user');
	var position_id = Number($(this).attr('data-id'));
	var document_id = Number($(this).attr('data-document_id'));
	var position_item = Number($(this).attr('data-position_item'));
	var id_dop_data = $(this).attr('data-id_dop_data');
	var order_status = $(this).attr('data-order_status');
	var title = 'Заказ ' + order_num_user
				+' / позиция ' + position_item +' / '
				+ $(this).parent().parent().find('.art_and_name').html()
				+' - техническая дополнительная информация';

	$.post('', {
		AJAX: 'get_dop_tex_info',
		query_num:query_num,
		order_num:order_num,
		position_id:position_id,
		id_dop_data:id_dop_data,
		document_id:document_id,
		order_status:order_status
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){			
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
		}else{
			alert('Что-то пошло не так');	
		}
	},'json');
});

// редактирование статуса пленки / клише
$(document).on('change', '.statuslist_film_photos', function(event) {
	var row_id = $(this).attr('data-id');
	var value = $(this).val();
	$.post('', {
		AJAX:'choose_statuslist_film_photos',
		value:value,
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	/* Act on the event */
});


// редактирование/ЗАПОЛНЕНИЕ dop_inputs
$(document).on('click', '#services_listing_each .lili', function(event) {
	if(!$(this).hasClass('no_active')){
		// console.log($(this).attr('data-uslugi_id'));
		// id услуги
		var uslugi_id = $(this).attr('data-uslugi_id');
		// id строки 
		var dop_usluga_id = $(this).attr('data-dop_usluga_id');

		var order_status = $(this).attr('data-order_status');
		

		$('#services_listing_each .lili').removeClass('checked');
		$(this).addClass('checked');
		window_preload_add();
		
		$.post('', {
			AJAX:'get_dop_inputs_for_services',
			uslugi_id: uslugi_id,
			dop_usluga_id: dop_usluga_id,
			order_status:order_status
		}, function(data, textStatus, xhr) {
			window_preload_del();
			if(data['response']=="OK"){
				console.log(Base64.decode(data['html']));
				$('#content_dop_inputs_and_tz').html(Base64.decode(data['html']));
			}else{
				alert('Что-то плошло не так...');
			}
		},'json');
	}else{
		console.log($(this).attr('data-uslugi_id'));
		var uslugi_id = $(this).attr('data-uslugi_id');
		var dop_usluga_id = $(this).attr('data-dop_usluga_id');
		$('#services_listing_each .lili').removeClass('checked');
		$(this).addClass('checked');

		$('#content_dop_inputs_and_tz').html('Услуга была отключена из работы<br> для её редактирования необходимо снова включить её в финансовой детализации');
	}
});

// редактирование поля резерв в доп тех инфо
$(document).on('keyup','#dialog_gen_window_form .rezerv_info_input', function(event) {
	
	var row_id = $(this).attr('data-id');

	$.post('', {
		AJAX:'save_rezerv_info',
		row_id: row_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование поля ТЗ по услуге к позиции заказа
$(document).on('keyup','#dialog_gen_window_form .save_tz', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_tz_info',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование поля ТЗ по услуге к позиции заказа
$(document).on('keyup','#dialog_gen_window_form .save_logotip', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_logotip_info',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// сохранения поля путь к макету
$(document).on('keyup','#dialog_gen_window_form .save_the_url_for_layout', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_the_url_for_layout',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование dop_inputs
$(document).on('keyup','#dialog_gen_window_form .dop_inputs', function(event) {
	var name_en = $(this).attr('name');
	var val = $(this).val();
	
	var Json = $('#dop_input_json').html();
	var json_object = JSON.parse(Json);

	json_object[name_en] = Base64.encode(val);
	if(val.trim()==""){
		delete json_object[name_en];
	}

	Json = JSON.stringify(json_object);

	$('#dop_input_json').html(Json)
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');
	console.log(cab_dop_usluga_id);
	$.post('', {
		AJAX:'save_dop_inputs',
		cab_dop_usluga_id: cab_dop_usluga_id,
		Json : Json
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование размерной сетки в доп. тех. инфо
$(document).on('keyup','#edit_size_dop_tex_info .val_tirage_dop', function(event) {
	var val = $(this).val();
	var size_id = $(this).attr('data-id_size');
	var id_dop_data = $(this).attr('data-id_dop_data');
	// var Json = $('#json_code_for_size').html();
	// if($('#json_code_for_size').html() != "{}"){
		
		
	
	// }else{
	// 	// var json_object = new Array();
		
	// }

		var zapas = 0;
		var zapas_one = 0;
		// var new_obj = new Array();
		var tir = 0;

		var Json = '{';
		$('#edit_size_dop_tex_info input').each(function(index, el) {
			zapas += Number($(this).val());

			zapas_one = Number($(this).val());

			tir = Number($(this).parent().prev().html()) ;
			if(index>0){
				Json += ',';	
			}
			Json += '"'+$(this).attr('data-id_size')+'":{"dop":"'+zapas_one+'","tir":"'+tir+'"}';

		});
		Json += '}';

		$('#json_code_for_size').html(Json);

	// console.log(json_object);
	// if(val.trim()==""){
		
	// }

	



	// console.log(zapas);

	$.post('', {
		AJAX:'save_edit_size_dop_tex_info',
		Json : Json,
		id_dop_data: id_dop_data,
		zapas:zapas
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');

});

// выозвращает размер
$(document).on('click', '.get_size_table_read', function(event) {
	event.preventDefault();
	var id_dop_data = $(this).attr('data-id_dop_data');
	var position_id = $(this).attr('data-position_id');
	var obj = $(this);
	$.post('', {
		AJAX:'get_size_table_read',
		id_dop_data: id_dop_data,
		position_id:position_id
	}, function(data, textStatus, xhr) {
		if(data['response'] = 'replace_width'){
			// alert(Base64.decode(data['html']));
			obj.replaceWith(Base64.decode(data['html']));
		}
	},'json');
});
// возвращает детализацию
$(document).on('click', '.get_a_detailed_specifications', function(event) {
	event.preventDefault();
	var position_id = $(this).attr('data-position_id');
	var obj = $(this);
	$.post('', {
		AJAX:'get_a_detailed_specifications',
		position_id:position_id
	}, function(data, textStatus, xhr) {
		if(data['response'] = 'replace_width'){
			// alert(Base64.decode(data['html']));
			obj.replaceWith(Base64.decode(data['html']));
		}
	},'json');
});

// возвращает детализацию описания не каталожного товара для редактирования
$(document).on('click', '.edit_tz_for_no_cat', function(event) {
	event.preventDefault();
	var position_id = $(this).attr('data-position_id');
	var obj = $(this);
	$.post('', {
		AJAX:'get_a_detailed_specifications_edit_true',
		position_id:position_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// вкл. / откл. Индикатора "Внимание ТЗ нужно поправить"
$(document).on('click', '.command_for_edit_tz_for_no_cat', function(event) {
	event.preventDefault();
	if(!$(this).hasClass('disabled')){
		$(this).toggleClass('checked');
		var value = 0;
		if($(this).hasClass('checked')){
			value = 1;
		}
		var position_id = $(this).attr('data-position_id');
		
		$.post('', {
			AJAX:'command_for_edit_tz_for_no_cat',
			position_id:position_id,
			value:value
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	}
});


// ПЗ / НПЗ
$(document).on('click', '#change_pz_npz .btn_var_std', function(event) {
	event.preventDefault();
	var val = 0
	var id_dop_data = $(this).attr('data-id_dop_data');
	$('#change_pz_npz .btn_var_std').removeClass('checked');
	$(this).addClass('checked');

	if($(this).attr('name') == 'pz'){
		val =1;		
	}
	
	$.post('', {
		AJAX:'save_change_pz_npz',
		val : val,
		id_dop_data: id_dop_data
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');	
});


// применить логотип ко всем услугам по позиции
$(document).on('click', '#save_logotip_for_all_position', function(event) {
	$.post('', {

		AJAX: 'save_logotip_for_all_position',
		position_id: $(this).attr('data-position_id'),
		id_dop_data: $(this).attr('data-id_dop_data'),
		logotip : $('#save_logotip_for_all_services_tbl .save_logotip_for_all_services').val()
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// применить логотип ко всем услугам по документу
$(document).on('click', '#save_logotip_for_all_order', function(event) {
	var position_id = $(this).attr('data-position_id');
	// alert($(this).attr('data-position_id'));
	$.post('', {
		AJAX: 'save_logotip_for_all_order',
		position_id: position_id,
		document_id: $(this).attr('data-document_id'),
		id_dop_data: $(this).attr('data-id_dop_data'),
		logotip : $('#save_logotip_for_all_services_tbl .save_logotip_for_all_services').val(),
		order_num: $(this).attr('data-order_num')

	}, function(data, textStatus, xhr) {

		standard_response_handler(data);
	},'json');
});

///////////////////////////////////////////////
//	статус сохранения отредактированного поля
///////////////////////////////////////////////
function check_loading_ajax(){
		window.l++;
		console.log(jQuery.active);
		if(jQuery.active>0){
			if($('#alert_saving_status').length==0){
				$('body').append('<div style="'
					+'position:fixed;'
					+'float:left;'
					+'font-family: arial,sans-serif;'
					+'left:50%; '
					+'z-index:110; '
					+'top:100px; '
					+'margin-left:-100px; '
					+'background-color:#F9EDBE;'
					+'border:1px solid #F0C36D; '
					+'padding:7px 15px; '
					+'font-size:12px" id="alert_saving_status"><div id="ll">Данные сохраняются...</div><div id="lll" style="text-align:center"></div><div id="lll1"><div id="lll2" style="width:0%;background: #F0C36D; height:5px; border:0"></div></div></div>');	
				$('#alert_saving_status').stop(true, true).fadeIn('fast');
			}else{
				$('#alert_saving_status').fadeIn('fast');			
			}
			var p = jQuery.active;
			var q = window.l / 100;
			var per = Math.ceil((100-p/q));
			$('#lll').html(per +' %');
			$('#lll2').width(per+'%');
			setTimeout(check_loading_ajax, 300);
			return false;
		}else{
			
			$('#ll').html('Данные успешно сохранены.')
			$('#lll').html('100 %');
			$('#lll2').width('100%');		
			$('#alert_saving_status').delay(1000).animate({opacity:0},700,function(){$(this).remove()});
			
			//setTimeout($('#alert_saving_status').fadeOut('fast').remove(), 3000)	
			window.l = 0;
			return true;	
		}
	};
	$(document).ready(function(){
	window.l = 0;
	window.onbeforeunload = function () {return ((check_loading_ajax()==false) ? "Измененные данные не сохранены. Закрыть страницу?" : null);}
	});


////////////////////////////////
//	детализация по списку услуг
////////////////////////////////

$(document).on('dblclick', '#general_panel_orders_tbl tr td.price_for_the_position', function(event) {
	var dop_data_id = $(this).attr('data-cab_dop_data_id');
	var specificate_id = $(this).attr('data-specificate_id');
	var id = $(this).attr('data-id');
	var order_num_user = $(this).attr('data-order_num_user');
	var order_num = $(this).attr('data-order_num');
	var order_id = $(this).attr('data-order_id');


	$.post('', {
		AJAX: 'get_a_detailed_article_on_the_price_of_positions',
		dop_data_id: dop_data_id,
		id:id,
		order_num:order_num,
		order_id:order_id,
		specificate_id:specificate_id,
		order_num_user:order_num_user
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// включение/отключение услуг
$(document).on('click', '#a_detailed_article_on_the_price_of_positions .on_of', function(event) {
	
	var id = $(this).attr('data-id');
	var obj = $(this);
	var val = 0;

	if ($(this).hasClass('minus')) {
		if (confirm('Внимание!!! Вы собираетесь включить услугу.\nВключённые улуги отображаются в отделах работающих по этим услугам.')) {
		val = 1;
		$(this).removeClass('minus').html('+');
		$(this).parent().parent().removeClass('no_calc');

		recalculate_a_detailed_article_on_the_price_of_positions(); // пересчитываем таблицу
		$.post('', {
			AJAX: 'change_service_on_of',
			id: id,
			val: val
		}, function(data, textStatus, xhr) {
			if(data['function'] !== undefined){ // на всякий
				window[data['function']](data);
			}
			if(data['response'] == "OK"){
				
			}else{
				alert('Что-то пошло не так');
			}
		},'json');
	}
	}else{
		if (confirm('Внимание!!! Вы собираетесь отключить услугу.\nОтключённые улуги не отображаются в отделах работающих по этим услугам.')) {
		val = 0;
		$(this).addClass('minus').html('-');
		$(this).parent().parent().addClass('no_calc');

		recalculate_a_detailed_article_on_the_price_of_positions(); // пересчитываем таблицу
		$.post('', {
			AJAX: 'change_service_on_of',
			id: id,
			val: val
		}, function(data, textStatus, xhr) {
			if(data['function'] !== undefined){ // на всякий
				window[data['function']](data);
			}
			if(data['response'] == "OK"){
				
			}else{
				alert('Что-то пошло не так');
			}
		},'json');
		}
		
	}

	
});

///////////////////////////////////////////
//	пересчёт окна финансовые расчёты
//////////////////////////////////////////
function recalculate_a_detailed_article_on_the_price_of_positions(){
	if ($('#a_detailed_article_on_the_price_of_positions tr').length) {
		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости заказа
		///////////////////////////////////////////////////////
		var Order_price_in = 0; // стоимость входящая
		var Order_price_out = 0; // стоимость исходящая
		var Order_price_pribl = 0; // прибыль 
		var Order_price_in_postfactum = 0; // стоимость входащаяя постфактум

		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости позиции
		///////////////////////////////////////////////////////
		var Position_price_in = 0; // стоимость входящая
		var Position_price_out = 0; // стоимость исходящая
		var Position_price_pribl = 0; // прибыль 
		var Position_price_in_postfactum = 0; // стоимость входащаяя постфактум

		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости товаров и услуг
		///////////////////////////////////////////////////////
		var Service_price_in = 0; // стоимость входящая
		var Service_price_out = 0; // стоимость исходящая
		var Service_price_pribl = 0; // прибыль 
		var Service_price_in_postfactum = 0; // стоимость входащаяя постфактум

		//////////////////////////////////////////////////////////
		//	флаги подсветки непредусмотренных потерь по стоимости
		///////////////////////////////////////////////////////////
		var order_not_provided = 0; 
		var position_not_provided = 0;
		var service_not_provided = 0;

		$('#a_detailed_article_on_the_price_of_positions tr').each(function(index, el) {
			// перебираем все строки, которые относятся к товарам и услугам, а так же к ИТОГО по позициям
			if(!$(this).hasClass('no_calc')){
				if (!$(this).hasClass('itogo_for_position')){


					service_not_provided = 0;
					///////////////////////////////////////
					//	перебираем строки товаров и услуг
					////////////////////////////////////////
					
					Service_price_in = Number($(this).find('.service_price_in').html()); // стоимость входящая
					Service_price_out = Number($(this).find('.service_price_out').html()); // стоимость исходящая
					Service_price_pribl = Number($(this).find('.service_price_pribl').html()); // прибыль 
					Service_price_in_postfactum = Number($(this).find('.service_price_in_postfactum').html()); // стоимость входащаяя постфактум
					// console.log(index);
					// console.log(Service_price_in);
					// console.log(Service_price_out);
					// console.log(Service_price_pribl);
					// console.log(Service_price_in_postfactum);
					// console.log('-------------------');
					////////////////////////////////////////////////////////////////
					//	суммируем стоимость услуги или товара к стоимости позиции
					////////////////////////////////////////////////////////////////
					Position_price_in += Service_price_in; // стоимость входящая
					Position_price_out += Service_price_out; // стоимость исходящая
					Position_price_pribl += Service_price_pribl; // прибыль 
					Position_price_in_postfactum += Service_price_in_postfactum; // стоимость входащаяя постфактум


					// НЕ предусмотренная услуга
					if ($(this).hasClass('not_provided')) {
						// устанавливаем флаги
						order_not_provided = 1;
						position_not_provided = 1;
						service_not_provided = 1;
					}
				}
				// console.log(order_not_provided);
				// если достигли итоговой стоимости по позиции - добавляем стоимость позиции к стоимости заказа и 
				//обнуляем переменные содержащие стоимость позиции для общёта следующих позиции
				if ($(this).hasClass('itogo_for_position')){
					///////////////////////////////////////////
					//	суммируем стоимость позиции к заказу
					///////////////////////////////////////////
					Order_price_in += Position_price_in; // стоимость входящая
					Order_price_out += Position_price_out; // стоимость исходящая
					Order_price_pribl += Position_price_pribl; // прибыль 
					Order_price_in_postfactum += Position_price_in_postfactum; // стоимость входащаяя постфактум
					// console.log(Position_price_in);
					// console.log(Position_price_out);
					// console.log(Position_price_pribl);
					// console.log(Position_price_in_postfactum);
					// console.log($(this).find('.position_price_in_postfaktum').length);
					// console.log('-------------------');

					//////////////////////////
					// правим стоимость позиции	
					//////////////////////////
					$(this).find('.position_price_in').html(Position_price_in);// стоимость входящая
					$(this).find('.position_price_out').html(Position_price_out);// стоимость исходящая
					$(this).find('.position_price_pribl').html(Position_price_pribl);// прибыль 
					$(this).find('.position_price_in_postfaktum').html(Position_price_in_postfactum);// стоимость входащаяя постфактум

					//////////////////////////
					//	обнуляем переменные со стоимостью позиции
					//////////////////////////
					Position_price_in = 0; // стоимость входящая
					Position_price_out = 0; // стоимость исходящая
					Position_price_pribl = 0; // прибыль 
					Position_price_in_postfactum = 0; // стоимость входащаяя постфактум

					// при наличии в расчёте непредусмотренных услуг подсвечиваем Итого позиции
					// в противном случае убираем подсветку
					if(position_not_provided){
						$(this).find('.td_shine').each(function(index, el) {
							if(!$(this).hasClass('added_postfactum_class')){
								$(this).addClass('added_postfactum_class');
							}				
						});
					}else{
						$(this).find('.added_postfactum_class').removeClass('added_postfactum_class');
					}
					// обнуляем флаг позиции 
					position_not_provided = 0;



				}


			}
		});

		// правим стоимость заказа
		$('#itogo_order .order_price_in').html(Order_price_in);// стоимость входящая
		$('#itogo_order .order_price_out').html(Order_price_out);// стоимость исходящая
		$('#itogo_order .order_price_pribl').html(Order_price_pribl + (Order_price_in - Order_price_in_postfactum));// прибыль
		$('#itogo_order .order_price_in_postfactum').html(Order_price_in_postfactum);// стоимость входащаяя постфактум
		$('#itogo_order .added_postfactum_class .minus span').html(Order_price_in - Order_price_in_postfactum); // разница постфактум

		// при наличии в расчёте непредусмотренных услуг подсвечиваем Итого заказа
		// в противном случае убираем подсветку
		if(order_not_provided){
			$('#itogo_order .td_shine').each(function(index, el) {
				if(!$(this).hasClass('added_postfactum_class')){
					$(this).addClass('added_postfactum_class');
				}				
			});
		}else{
			$('#itogo_order .added_postfactum_class').removeClass('added_postfactum_class');
		}
		//////////////////////////
		//	определяем разницу постфактум
		//////////////////////////
		if(Order_price_in_postfactum != Order_price_in){
			$('#itogo_order .minus').html('<span>'+(Order_price_in - Order_price_in_postfactum)+'</span>р');
		}else{
			$('#itogo_order .minus').html('');
		}

	}
}

// добавление услуги в финансовые расчёты
$(document).on('click', '.add_service', function(event) {
	var id_dop_data = $(this).attr('data-id_dop_data');
	$(this).attr('id','liuhjadbwefbkelwqfeqwfqw');
	$.post('', 
		{
			AJAX:"get_uslugi_list_Database_Html_steep_1",
			id_dop_data:id_dop_data			
		}, function(data, textStatus, xhr) {

		show_dialog_and_send_POST_window(data,'Шаг 1: Выберите услугу', 800);
		
	});
});

function add_new_usluga_end(data){
	var id_row = $('#liuhjadbwefbkelwqfeqwfqw').attr('data-rowspan_id');
	var rowspan = Number($('#'+id_row+' td').eq(0).attr('rowspan'))+1;
	$('#'+id_row+'').find(':first-child').attr('rowspan',rowspan).next().attr('rowspan',rowspan);
	// $('#'+id_row+'').firstChild(1).attr('rowspan',rowspan)
	$('#liuhjadbwefbkelwqfeqwfqw').parent().parent().before(Base64.decode(data['html']));
	$('#liuhjadbwefbkelwqfeqwfqw').removeAttr('id');
	recalculate_a_detailed_article_on_the_price_of_positions();
}


//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');
	var id = $(this).attr('data-id');
	var service_name = $(this).find('.name_text').html();

	// группа исполнителей допущенная к изменению статусов
	var performer = $(this).attr('data-performer');
	
	// console.log(quantity);
	// $('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="performer"]').val(performer);

	$('#dialog_gen_window_form form input[name="service_name"]').val(service_name);
	// $('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});

// обработчик изменения статуса услуги
$(document).on('change', '.get_statuslist_uslugi', function(event) {
	var id_row = $(this).attr('data-id');
	var position_id = $(this).attr('data-position_id');
	var question = $(this).find('option:selected').attr('data-question');
	var value = $(this).val();
	$.post('', {
		AJAX: 'choose_service_status',
		id_row: id_row,
		position_id:position_id,
		question:question,
		value:value 
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});




//////////////////////////
//	ПР-ВО
//////////////////////////
$(document).on('click', '.js-modal--tz-prodaction', function(event) {
	$.post('', {
		AJAX: 'get_dialog_tz_for_production',
		row_id:$(this).attr('data-id')
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			title = data['title'];// для генерации окна всегда должен передаваться title
			show_simple_dialog_window(Base64.decode(data['html']),title);
		}else{
			alert('Упс. Что-то пошло не так...');
		}
	},'json');
});

// $(document).on('click', '.position-row-production__service-name', function(event) {
// 	event.preventDefault();
// 	var href = $(this).attr('data-href');
// 	window.location.href = href;		
// });


//////////////////////////
//	запуск услуги в работу 
//////////////////////////
$(document).on('click', '.start_statuslist_uslugi', function(event) {
	var id = $(this).attr('data-id');
	$.post('', {
		AJAX: 'start_services_in_processed',
		id:id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// выставление даты работы над услугой
// дата сдачи заказа
$(document).ready(function($) {
	$('.show-backlight .calendar_date_work').datetimepicker({
		minDate:new Date(),

		// disabledDates:['07.05.2015'],
		timepicker:true,
	 	dayOfWeekStart: 1,
	 	allowTimes:[
		  '9:00', '10:00', '11:00', 
		  '12:00', '13:00', '14:00', '15:00', '16:00',
		  '17:00', '18:00', '19:00', '20:00'
		 ],
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		// closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_date_work_of_service',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
			check_loading_ajax();
		},
	 	format:'d.m.Y H:i',	 	
	});
});

$(document).ready(function($) {
	$('.show-backlight .calendar_date_ready').datetimepicker({
		minDate:new Date(),

		// disabledDates:['07.05.2015'],
		timepicker:true,
	 	dayOfWeekStart: 1,
	 	allowTimes:[
		  '9:00', '10:00', '11:00', 
		  '12:00', '13:00', '14:00', '15:00', '16:00',
		  '17:00', '18:00', '19:00', '20:00'
		 ],
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		// closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_date_ready_of_service',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
			check_loading_ajax();
		},
	 	format:'d.m.Y H:i',	 	
	});
});

// select станок
$(document).on('change', '.machine_type', function(event) {
	var row_id = $(this).attr('data-id');
	var value = $(this).val();
	$.post('', {
		AJAX: 'change_machine_type',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});

// взять в работу услугу
$(document).on('click', '.get_in_work_service', function(event) {
	if(confirm('Вы уверены?')){
	var order_id = $(this).attr('data-order_id');
	// сохраняем ID строки
	var row_id = $(this).attr('data-service_id');
	// id пользователя, взяшего услугу в работу
	var user_id = $(this).attr('data_user_ID');

	// меняем html
	$(this).replaceWith('<span data-id="'+user_id+'">'+$(this).attr('data-user_name')+'</span>');
	

	$.post('', {
		AJAX: 'get_in_work_service',
		row_id:row_id,
		user_id:user_id,
		order_id:order_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
	}
});

// назначить исполнителя услуги
$(document).on('change', '.production_userlist', function(event) {
	var row_id = $(this).attr('data-row_id');
	var order_id = $(this).attr('data-order_id');
	var user_id = $(this).val();
	$.post('', {
		AJAX: 'get_in_work_service',
		row_id:row_id,
		user_id:user_id,
		order_id:order_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});

//////////////////////////////////////////
//	меняем % готовности услуги --- start
//////////////////////////////////////////
$(document).on('keyup', '.percentage_of_readiness', function(event) {
	 timing_save_input('change_percentage_of_readiness',$(this))
});
function change_percentage_of_readiness(obj){// на вход принимает object input
    var row_id = obj.attr('data-service_id');
    $.post('', {
        AJAX:'change_percentage_of_readiness',
        row_id:row_id,
        value:obj.html()
    }, function(data, textStatus, xhr) {
    	standard_response_handler(data);
        if(data['response']=="OK"){
            // php возвращает json в виде {"response":"OK"}
            // если ответ OK - снимаем класс saved
            obj.removeClass('saved');
        }else{
            console.log('Данные не были сохранены.');
        }
    },'json');
}
function timing_save_input(fancName,obj){
    //если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
    if(!obj.hasClass('saved')){
        window[fancName](obj);
        obj.addClass('saved');                  
    }else{// стоит запрет, проверяем очередь по сейву данной функции        
        if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
            // стоит очередь, значит мимо... всё и так сохранится
        }else{
            // не стоит в очереди, значит ставим
            obj.addClass(fancName);
            // вызываем эту же функцию через n времени всех очередей
            var time = 2000;
            $('.'+fancName).each(function(index, el) {
                console.log($(this).html());
                
                setTimeout(function(){timing_save_input(fancName,$('.'+fancName).eq(index));// обнуляем очередь
        if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time); 
            });         
        }       
    }
}

//////////////////////////////////////////
//	меняем % готовности услуги --- end
//////////////////////////////////////////

// нажатие кнопки макет утверждён
$(document).on('click', '.set_approval_date', function(event) {
	// получаем текущую дату
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();

	var date  = curr_date + "." + curr_month + "." + curr_year;
	// alert(date);

	

	// получаем id позиции
	var row_id = $(this).attr('data-id');
	
	// получаем dop_data_id
	var dop_data_id = $(this).parent().parent().attr('data-cab_dop_data_id');
	// меняем html
	$(this).replaceWith('<span class="greyText">'+date+'</span>');
	// закрываем экран от дальнейшего редактирования
	window_preload_add();
	// отправляем запрос
	$.post('', {
		AJAX: 'change_approval_date',
		row_id: row_id,
		date: date,
		dop_data_id: dop_data_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// редирект страницы при выгрузке div для редиректа
$(window).load(function() {
	if($('#js_location').length > 0){
		var href = $('#js_location a').attr('href');
		// alert($('#js_location a').attr('href'));
		setTimeout(function(){ 
			window.location.href = href;
		}, Number($('#js_location').attr('data-time')));
	}
});


/////////////////////////////
//	выставить счёт  -- start
/////////////////////////////

	// запрос на перевыставить счёт и выставить доп.счёт
	$(document).on('click', '.buch_status_select', function(event) {
		// если нет кнопки запроса счёта
		// alert($(this).find('input.query_the_bill').length);
		if($(this).find('input.query_the_bill').length==0 && $(this).find('input.query_the_bill_oferta').length==0 && $(this).find('select').length==0){
			var order_id = $(this).parent().attr('data-id');
			var AJAX = 'get_commands_men_for_buch';

			$.post('', {
				AJAX:AJAX,
				order_id: order_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
				// show_dialog_and_send_POST_window(Base64.decode(data['html']),data['title'],'auto',230);	
			},'json');
		}
	});

	// кнопка выставить счёт
	$(document).on('click', '.buch_status_select input.query_the_bill', function(event) {
		event.preventDefault();
		var specificate_row_id = $(this).parent().parent().attr('data-id');

		var AJAX = 'order_a_new_account';
		$.post('', {
			AJAX:AJAX,
			status_buch:'request_expense',
			type_the_bill:'the_bill',
			specificate_row_id: specificate_row_id
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
			// show_dialog_and_send_POST_window(Base64.decode(data['html']),data['title'],'auto',230);
			
		},'json');
	});

	// кнопка запросить счёт-оферту
	$(document).on('click', '.buch_status_select input.query_the_bill_oferta', function(event) {
		event.preventDefault();
		var specificate_row_id = $(this).parent().parent().attr('data-id');
		var AJAX = 'order_a_new_account';
		$.post('', {
			AJAX:AJAX,
			status_buch:'get_the_bill_oferta',
			type_the_bill:'oferta',
			specificate_row_id: specificate_row_id
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
			// show_dialog_and_send_POST_window(Base64.decode(data['html']),data['title'],'auto',230);
			
		},'json');
	});

	// подсветка выбранного пункта
	$(document).on('click', '#dialog_gen_window_form .check_one_li_tag li', function(event) {
		$('#dialog_gen_window_form .check_one_li_tag li.checked').removeClass('checked');
		$(this).addClass('checked');
	});


	$(document).on('click', '#get_listing_type_the_bill li', function(event) {
		$('#dialog_gen_window_form input[name="type_the_bill"]').val($(this).attr('data-name_en'));
	});

	$(document).on('click', '#get_commands_men_for_buch li', function(event) {
		$('#dialog_gen_window_form input[name="status_buch"]').val($(this).attr('data-name_en'));
	});

	$(document).on('click', '#get_commands_men_for_query li', function(event) {
		$('#dialog_gen_window_form input[name="query_status"]').val($(this).attr('data-name_en'));
		$('#dialog_gen_window_form input[name="AJAX"]').val($(this).attr('data-ajax'));
	});

	$(document).on('click', '#get_commands_men_for_order li', function(event) {
		$('#dialog_gen_window_form input[name="status_order"]').val($(this).attr('data-name_en'));
	});

	$(document).on('click', '#get_commands_men_for_design li', function(event) {
		$('#dialog_gen_window_form input[name="status_design"]').val($(this).attr('data-name_en'));
	});

	// выбор нескольких кураторов
	$(document).on('click', '#chose_many_curators_tbl td', function(event) {
		event.preventDefault();
		if($(this).html() != ''){
			$(this).toggleClass('checked');
		}


		var Json = $('#json_manager_arr').html();
		var json_object = JSON.parse(Json);
		var user_id = Number($(this).attr('data-id'));
		
		
		if(!$(this).hasClass('checked')){
			delete json_object[user_id];
		}else{
			json_object[user_id] = user_id;
		}

		Json = JSON.stringify(json_object);

		// console.log(json_str);
		$('#json_manager_arr').html(Json);
		$('#json_manager_arr_val').val(Json);
	});



/////////////////////////////
//	выставить счёт  -- start
/////////////////////////////



// показать скрыть checkbox в спецификациях
$(document).on('click', '#create_the_order', function(event) {
	var message = 'сначала выберите Имя Клиента в столбике "Компания"';
	if($(this).hasClass('disabled')){
		$("<li/>", {
			      "class": 'error_message',
			      "css":{"opacity":1,"top":0},
			      click: function(){
			          $(this).animate({opacity:0},'fast',function(){$(this).remove()});
			      }
			}).append(message).appendTo("#apl-notification_center").fadeIn('slow', 
		        function(){
		            var el = jQuery(this);
		            setTimeout(function(){
		                el.fadeOut('slow',
		                    function(){
		                        jQuery(this).remove();
		                    });
		            }, 7000);
		    });
		return false;
	}
	$('#cabinet_general_content_row .check_show_me').each(function(index, el) {
		if($(this).hasClass('show')){
			$('#cabinet_general_content').css({"paddingBottom":0});
			$(this).removeClass('show');
			$('#create_the_order').removeClass('checked').html('Создать заказ');
			$('#export_in_order_div').animate({opacity:0},'fast').hide();
		}else{
			$(this).addClass('show');
			$('#cabinet_general_content').css({"paddingBottom":105});
			$('#create_the_order').addClass('checked').html('Скрыть выбор');
			$('#export_in_order_div').animate({opacity:1},'fast').show();
		}		
	});
});



// выбор спецификаций для формирования заказа
$(document).on('click', '#create_in_order_button', function(event) {
	var checked_spec_id = '';
	var n = 0;
	var manager_id = 0;
	$('.masterBtnContainer input:checkbox:checked').each(function(index, el) {

		if(n>0){
			checked_spec_id += ",'"+$(this).parent().attr('data-id')+"'";
		}else{
			manager_id = $(this).parent().attr('data-manager_id');
			checked_spec_id += "'"+$(this).parent().attr('data-id')+"'";
		}
		
		n++;
	});
	if(n==0){
		alert('Для создания заказа, необходимо выбрать хотя бы одну спецификацию.');
	}else{
		if(confirm('Вы уверены, что хотите объединить '+n+' спецификацию(ий) в заказ?')){
			$('#create_the_order').click();
			$.post('', {
				AJAX: 'create_new_order',
				manager_id:manager_id,
				checked_spec_id:checked_spec_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		}		
	}
});

// прикрепление спецификации к существующему заказу
$(document).on('click', '#add_for_other_order', function(event) {
	var checked_spec_id = '';
	var n = 0;
	$('.masterBtnContainer input:checkbox:checked').each(function(index, el) {
		if(n>0){
			checked_spec_id += ",'"+$(this).parent().attr('data-id')+"'";
		}else{
			checked_spec_id += "'"+$(this).parent().attr('data-id')+"'";
		}
		
		n++;
	});
	if(n==0){
		alert('Для создания заказа, необходимо выбрать хотя бы одну спецификацию.');
	}else{
		if(confirm('Вы уверены, что хотите объединить '+n+' спецификацию(ий) в заказ?')){
			$('#create_the_order').click();
			$.post('', {
				AJAX: 'attach_the_specification_for_other_order',
				checked_spec_id:checked_spec_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		}		
	}
});




// запрос бух учета по спецификации
$(document).on('click', '.buh_uchet_for_spec', function(event) {
	window_preload_add();
	var spec_id = $(this).attr('data-id');
	$.post('', {
		AJAX: 'get_buh_uchet_for_spec',
		spec_id:spec_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);		
	},'json');
});

// запрос бух учета по спецификации
$(document).on('click', '.buh_uchet_for_order', function(event) {
	window_preload_add();	
	var order_num = $(this).attr('data-id');
	$.post('', {
		AJAX: 'get_buh_uchet_for_order',
		order_num: order_num
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);		
	},'json');
});



// вкладки спецификаций в окне бух. учёт
$(document).on('click', '#tabs ul li', function(event) {
	var id = $(this).attr('data-id');
	console.log('#'+id);
	$('.spec_div').each(function(index, el) {
		if($(this).attr('id') != id){
			$(this).css({"display":"none"});
		}		
	});
	$('#tabs ul li').removeClass('checked');
	$(this).addClass('checked');

	$('#tabs #'+id).css({"display":"block"});
});

// показать кнопку счёт выставлен в окне бух. учёт
$(document).on('keyup', '.buh_window input', function(event) {
	event.preventDefault();
	if ($(this).parent().find('.the_bill_is_ready').length) {
		$(this).parent().find('.the_bill_is_ready').show();
	}
	/* Act on the event */
});

// нажатие на кнопку счёт выставлен
$(document).on('click', '.the_bill_is_ready', function(event) {
	event.preventDefault();
	var row_id = $(this).attr('data-id');
	$(this).hide();
	$.post('', {
		AJAX: 'the_bill_is_ready',
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// перезагрузка главной таблицы в paperwork
function reload_paperwork_tbl(){
	window_preload_add();
	$('#cabinet_general_content_row').load(' #cabinet_general_content_row',function(){
		window_preload_del();
	});
}

// перезагрузка таблицы заказов
function reload_order_tbl(data){
	if( data.timeout === undefined ){
			if($('#general_panel_orders_tbl').length){
				window_preload_add();
				$('#general_panel_orders_tbl').load(' #general_panel_orders_tbl',function(){
					window_preload_del();
					// подсветка строки при наведении на ячейки запроса, которые вызывают меню
					$('#general_panel_orders_tbl .order_head_row td.show_main_menu').hover(function(event){
						$(this).parent().addClass('hover_class');
					},function(event){
						$(this).parent().removeClass('hover_class');
					});
				});	
			}else{
				reload_paperwork_tbl();
			}
	}else{
		setTimeout(function(){
			if($('#general_panel_orders_tbl').length){
				window_preload_add();
				$('#general_panel_orders_tbl').load(' #general_panel_orders_tbl',function(){
					window_preload_del();
				});	
			}else{
				reload_paperwork_tbl();
			}
		}, data.timeout)

	}	
	
}
// перезагрузка таблицы предзаказа
function reload_paperwork_tbl(){
	if($('#cabinet_general_content_row').length){
		window_preload_add();
		$('#cabinet_general_content_row').load(' #cabinet_general_content_row',function(){
			window_preload_del();
			// подсветка строки при наведении на ячейки запроса, которые вызывают меню
			$('#general_panel_orders_tbl .order_head_row td.show_main_menu').hover(function(event){
				$(this).parent().addClass('hover_class');
			},function(event){
				$(this).parent().removeClass('hover_class');
			});
		});	
	}else{
		reload_order_tbl();
	}
		
}

//////////////////////////
//	PP
//////////////////////////
	//////////////////////////
	// добавить ПП
	//////////////////////////
		$(document).on('click', '#add_pp', function(event) {
			event.preventDefault();
			var row_id = $(this).parent().attr('data-specification_id');
			$.post('', {
				AJAX: 'create_row_pp',
				row_id:row_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');	
		});

		function show_new_row_pp(data){
			// находим активную вкладку спецификации
			var obj = $('.spec_div:visible'); 
			$('.buh_window.add_pp').css({"display":"block"});
			var new_pp = obj.find('.document_pp_hidden').clone();
			new_pp = new_pp.removeClass('document_pp_hidden').addClass('document_pp').attr('data-id',data['id']);
			obj.find('.buh_window.add_pp').append(new_pp);
			windows_on(data);
		}
	//////////////////////////
	//	удалить ПП
	//////////////////////////
		$(document).on('click', '.del_pp', function(event) {
			var obj = $(this).parent();
			event.preventDefault();
			var row_id = $(this).parent().attr('data-id'); // id для строки ПП

			// проверяем на заполненность
			var deleting = 1;
			$(this).parent().find('input').each(function(index, el) {
				
				if($(this).val() != ''){
					deleting = 0;
				}		
			});

			if(deleting==0){
				// confirm(message)
				if(confirm('В полях ПП содержится информация. Вы уверены, что хотите удалить строку ПП.')){
					
					obj.remove();
					if($('.spec_div:visible .document_pp').length == 0){
						$('.spec_div:visible .buh_window.add_pp').css({"display":"none"});
					}
					$.post('', {
						AJAX:'delete_PP',
						row_id:row_id
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					});
				}
			}else{
				
				obj.remove();
				if($('.spec_div:visible .document_pp').length == 0){
					$('.spec_div:visible .buh_window.add_pp').css({"display":"none"});
				}
				$.post('', {
					AJAX:'delete_PP',
					row_id:row_id
				}, function(data, textStatus, xhr) {
					standard_response_handler(data);
				});			
			}
		});
	//////////////////////////
	//	редактор ПП
	//////////////////////////
		// сохранение номера
		$(document).on('keyup', '.document_pp .number_pp', function(event) {
			 timing_save_input('edit_numper_pp',$(this))
		});
		function edit_numper_pp(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'pp_edit_number',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}
		// сохранение размера внесенной суммы
		$(document).on('keyup', '.document_pp .price_from_pyment', function(event) {
			 timing_save_input('price_from_pyment_pp',$(this))
		});
		function price_from_pyment_pp(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    var date = obj.prev().prev().val();
		    var specification_id = obj.parent().parent().attr('data-specification_id');
		    $.post('', {
		        AJAX:'pp_edit_payment_summ',
		        row_id:row_id,
		        value:obj.val(),
		        date: date,
		        specification_id:specification_id
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}

		// сохранение размера
		$(document).on('keyup', '.document_pp .comments', function(event) {
			 timing_save_input('pp_edit_comments',$(this))
		});
		function pp_edit_comments(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'pp_edit_comments',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}

//////////////////////////
//	PKO
//////////////////////////
	//////////////////////////
	// добавить ПКО
	//////////////////////////
		$(document).on('click', '#add_pko', function(event) {
			event.preventDefault();
			var row_id = $(this).parent().attr('data-specification_id');
			$.post('', {
				AJAX: 'create_row_pko',
				row_id:row_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');	
		});

		function show_new_row_pko(data){
			// находим активную вкладку спецификации
			var obj = $('.spec_div:visible'); 
			$('.buh_window.add_pko').css({"display":"block"});
			var new_pko = obj.find('.document_pko_hidden').clone();
			new_pko = new_pko.removeClass('document_pko_hidden').addClass('document_pko').attr('data-id',data['id']);
			obj.find('.buh_window.add_pko').append(new_pko);
			windows_on(data);
		}
	//////////////////////////
	//	редактор ПКО
	//////////////////////////
		// сохранение номера
		$(document).on('keyup', '.document_pko .number_pko', function(event) {
			 timing_save_input('edit_numper_pko',$(this))
		});
		function edit_numper_pko(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'pko_edit_number',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}
		// сохранение размера внесенной суммы
		$(document).on('keyup', '.document_pko .price_from_pyment', function(event) {
			 timing_save_input('price_from_pyment_pko',$(this))
		});

		function price_from_pyment_pko(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    var date = obj.prev().prev().val();
		    var specification_id = obj.parent().parent().attr('data-specification_id');
		    $.post('', {
		        AJAX:'pko_edit_payment_summ',
		        row_id:row_id,
		        date:date,
		        value:obj.val(),
		        specification_id:specification_id
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}

		// сохранение размера
		$(document).on('keyup', '.document_pko .comments', function(event) {
			 timing_save_input('pko_edit_comments',$(this))
		});
		function pko_edit_comments(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'pko_edit_comments',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}
	//////////////////////////
	//	удалить ПКО
	//////////////////////////
		$(document).on('click', '.del_pko', function(event) {
			var obj = $(this).parent();
			event.preventDefault();
			var row_id = $(this).parent().attr('data-id'); // id для строки ПП

			// проверяем на заполненность
			var deleting = 1;
			$(this).parent().find('input').each(function(index, el) {
				
				if($(this).val() != ''){
					deleting = 0;
				}		
			});

			if(deleting==0){
				// confirm(message)
				if(confirm('В полях ПKO содержится информация. Вы уверены, что хотите удалить строку ПKO.')){
					obj.remove();
					if($('.spec_div:visible .document_pko').length == 0){
						$('.spec_div:visible .buh_window.add_pko').css({"display":"none"});
					}

					$.post('', {
						AJAX:'delete_PKO',
						row_id:row_id
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					});
				}
			}else{
				obj.remove();
				if($('.spec_div:visible .document_pko').length == 0){
					$('.spec_div:visible .buh_window.add_pko').css({"display":"none"});
				}

				$.post('', {
					AJAX:'delete_PKO',
					row_id:row_id
				}, function(data, textStatus, xhr) {
					standard_response_handler(data);
				});			
			}
		});

//////////////////////////
//	TTN
//////////////////////////
	//////////////////////////
	// добавить ТТН
	//////////////////////////
		$(document).on('click', '#add_ttn', function(event) {
			event.preventDefault();
			var row_id = $(this).parent().attr('data-specification_id');
			$.post('', {
				AJAX: 'create_row_ttn',
				row_id:row_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');	
		});

		function show_new_row_ttn(data){
			// находим активную вкладку спецификации
			var obj = $('.spec_div:visible'); 
			$('.buh_window.add_ttn').css({"display":"block"});
			var new_ttn = obj.find('.document_ttn_hidden').clone();
			new_ttn = new_ttn.removeClass('document_ttn_hidden').addClass('document_ttn').attr('data-id',data['id']);
			obj.find('.buh_window.add_ttn').append(new_ttn);
			windows_on(data);
		}
	//////////////////////////
	//	редактор ТТН
	//////////////////////////
		// сохранение номера
		$(document).on('keyup', '.document_ttn .number_ttn', function(event) {
			 timing_save_input('edit_numper_ttn',$(this))
		});
		function edit_numper_ttn(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'ttn_edit_number',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}
		

		// сохранение размера
		$(document).on('keyup', '.document_ttn .comments', function(event) {
			 timing_save_input('ttn_edit_comments',$(this))
		});
		function ttn_edit_comments(obj){// на вход принимает object input
		    var row_id = obj.parent().attr('data-id');
		    $.post('', {
		        AJAX:'ttn_edit_comments',
		        row_id:row_id,
		        value:obj.val()
		    }, function(data, textStatus, xhr) {
		    	standard_response_handler(data);
		        if(data['response']=="OK"){
		            // php возвращает json в виде {"response":"OK"}
		            // если ответ OK - снимаем класс saved
		            obj.removeClass('saved');
		        }else{
		            console.log('Данные не были сохранены.');
		        }
		    },'json');
		}
	//////////////////////////
	//	удалить ТТН
	//////////////////////////
		$(document).on('click', '.del_ttn', function(event) {
			var obj = $(this).parent();
			event.preventDefault();
			var row_id = $(this).parent().attr('data-id'); // id для строки ПП

			// проверяем на заполненность
			var deleting = 1;
			$(this).parent().find('input').each(function(index, el) {
				
				if($(this).val() != ''){
					deleting = 0;
				}		
			});

			if(deleting==0){
				// confirm(message)
				if(confirm('В полях ТТН содержится информация. Вы уверены, что хотите удалить строку ТТН.')){
					obj.remove();
					if($('.spec_div:visible .document_pko').length == 0){
						$('.spec_div:visible .buh_window.add_pko').css({"display":"none"});
					}

					$.post('', {
						AJAX:'delete_TTN',
						row_id:row_id
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					});
				}
			}else{
				obj.remove();
				if($('.spec_div:visible .document_pko').length == 0){
					$('.spec_div:visible .buh_window.add_pko').css({"display":"none"});
				}

				$.post('', {
					AJAX:'delete_TTN',
					row_id:row_id
				}, function(data, textStatus, xhr) {
					standard_response_handler(data);
				});			
			}
		});

// включение датапикеров для доптехинфо
function windows_on(data){
		//////////////////////////
		//	дата подписания спецификации
		//////////////////////////
			$('.date_specification_signed').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var row_id = $input.attr('data-id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'change_date_specification_signed',
						row_id: row_id,
						date: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});
		//////////////////////////
		//	дата оплаты по ПП
		//////////////////////////
			$('.document_pp .payment_date').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var spec_id = $input.parent().parent().attr('data-specification_id');
					var row_id = $input.parent().attr('data-id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'pp_edit_date',
						row_id: row_id,
						spec_id:spec_id,
						value: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});
		//////////////////////////
		//	дата оплаты по ПКО
		//////////////////////////
			$('.document_pko .payment_date').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var row_id = $input.parent().attr('data-id');
					var spec_id = $input.parent().parent().attr('data-specification_id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'pko_edit_date',
						row_id: row_id,
						spec_id:spec_id,
						value: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});
		//////////////////////////
		//	дата TTN
		//////////////////////////
			$('.document_ttn .ttn_date').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var row_id = $input.parent().attr('data-id');
					var spec_id = $input.parent().parent().attr('data-specification_id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'ttn_edit_date',
						row_id: row_id,
						spec_id:spec_id,
						value: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});
		//////////////////////////
		//	дата возврата TTN
		//////////////////////////
			$('.document_ttn .date_return').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var row_id = $input.parent().attr('data-id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'ttn_edit_date_return',
						row_id: row_id,
						value: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});

		//////////////////////////
		//	смена даты выставления счёта
		//////////////////////////
			$('.date_create_the_bill').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var id_row = $input.attr('data-id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'change_date_create_the_bill',
						id_row: id_row,
						date: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});
		//////////////////////////
		//	смена даты выставления счёта
		//////////////////////////
			$('.date_create_the_bill_oferta').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					$('#tabs ul li.check_the_anather_spec.checked a').html('№ '+$input.prev().prev().val()+' от '+$input.val());
					var id_row = $input.attr('data-id');
					var doc_id = $input.attr('data-doc_id');
					var date = $input.val();

					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'change_date_create_the_bill_oferta',
						id_row: id_row,
						doc_id:doc_id,
						date: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});

		//////////////////////////
		//	дата возврата подписанной спецификации
		//////////////////////////
			$('.date_return_width_specification_signed').datetimepicker({
				// minDate:new Date(),
				timepicker:false,
			 	dayOfWeekStart: 1,
				closeOnDateSelect:true,
				onChangeDateTime: function(dp,$input){// событие выбора даты
					// получение данных для отправки на сервер
					var row_id = $input.attr('data-id');
					var date = $input.val();
					$input.blur();
					//alert($input.attr('class'));
					$.post('', {
						// AJAX: 'change_payment_date',
						AJAX: 'change_date_return_width_specification_signed',
						row_id: row_id,
						date: date
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				},
			 	format:'d.m.Y',
			});	
}
// запрос на изменение статуса заказа в макет без оплаты
$(document).on('click', '.order_status_chenge', function(event) {
	event.preventDefault();
	if($(this).find('select').length == 0 && $(this).find('input').length == 0){
		var order_id = $(this).parent().attr('data-id');
		var status_now = $(this).find('span').attr('data-status_now');
		$.post('', {
			AJAX: 'get_commands_for_order_status',
			order_id:order_id,
			status_now:status_now
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	}	
});


// запрос на изменение статуса запроса
// $(document).on('click', '.query_status', function(event) {
// 	event.preventDefault();
// 	var row_id = $(this).parent().attr('data-id');
// 	var client_id = $(this).prev().prev().prev().prev().prev().prev().prev().attr('data-id');
// 	var query_num = Number($(this).prev().prev().prev().prev().prev().prev().prev().prev().find('a').html());
// 	$.post('', {
// 		AJAX: 'get_command_for_change_status_query',
// 		row_id:row_id,
// 		client_id:client_id,
// 		query_num:query_num
// 	}, function(data, textStatus, xhr) {
// 		standard_response_handler(data);
// 	},'json');
// });

// клик по фильтру
$(document).on('click', '.filter_class', function(event) {
	event.preventDefault();
	window.location.href = $(this).attr('data-href');
});

// новый запрос
$(document).on('click', '#create_new_query', function(event) {
	event.preventDefault();
	var message = 'Вы уверены, что хотите завести новый запрос?';
	if(confirm(message)){
		$.post('', {
			AJAX: 'create_new_query'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');	
	}
	

});

function scroll_width_checked_client(){
	// $("#dialog_gen_window_form").scrollTo("#checked_client", 200);
	var container = $('#dialog_gen_window_form');
    var scrollTo = $('#dialog_gen_window_form .checked_client');

	container.scrollTop(
	    scrollTo.offset().top - container.offset().top + container.scrollTop() - 15
	);

	// Or you can animate the scrolling:
	// container.animate({
	//     scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()-15
	// },5000);​
}



jQuery.fn.scrollTo = function(elem, speed) { 
	console.log($(this).scrollTop());
	console.log($(this).offset().top);
	console.log($(elem).offset().top);

    $(this).animate({
        scrollTop:  $(this).scrollTop() + $(this).offset().top + $(elem).offset().top 
    }, speed == undefined ? 1000 : speed); 
    return this; 
};


$(document).on('click', '#general_panel_orders_tbl tr td.rt_href_click', function(event) {
	event.preventDefault();
	var a = $(this).find('a');
	if (a.attr('onclick')) {
		var js_message = 'Чтобы перейти в РТ необходимо прикрепить клиента!!!';
		echo_message_js(js_message,'system_message');
	}else{
		window.location.href = $(this).find('a').attr('href');
	}
	
});

$(document).ready(function(event) {
	$('#general_panel_orders_tbl .order_head_row td.show_main_menu').hover(function(event){
		$(this).parent().addClass('hover_class');
	},function(event){
		$(this).parent().removeClass('hover_class');
	})
})

$(document).on('click','.show_main_menu',function(){
	event.preventDefault();
	var row_id = $(this).parent().attr('data-id');
	var client_id = $(this).parent().find('td:nth-of-type(3)').attr('data-id');
	// var query_num = Number($(this).prev().prev().prev().prev().prev().prev().prev().prev().find('a').html());
	var query_num = Number($(this).parent().find('td:nth-of-type(2) a').html());

	$.post('', {
		AJAX: 'get_command_for_change_status_query',
		row_id:row_id,
		client_id:client_id,
		query_num:query_num
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});
