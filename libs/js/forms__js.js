
//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- start
//////////////////////////////////
	//стандартный обработчик ответа AJAX
	function standard_response_handler(data){
		if(data['response']=='show_form_moderate_window'){
			// ОБРАБОТКА НЕКАТАЛОЖНОГО ТОВАРА
			// вызов формы заведения не каталожного товара и генерации вариантов
			show_dialog(Base64.decode(data['html']));
			// объявляем работу датапикера для полей даты
			create_datepicker_for_variant_cont();
		}
		if(data['response']=='show_new_window'){
			title = data['title'];// для генерации окна всегда должен передаваться title
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			var button_name = (data['button_name'] !== undefined)?data['button_name']:'OK';
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title,height,width,button_name);
		}
		if(data['response']=='show_new_window_2'){
			title = data['title'];// для генерации окна всегда должен передаваться title
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			show_dialog_and_send_POST_window_2(Base64.decode(data['html']),title,height,width);
		}
		if(data['response']=='show_new_window_simple'){
			title = data['title'];// для генерации окна всегда должен передаваться title
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			show_simple_dialog_window(Base64.decode(data['html']),title,height,width);
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
		if(data['response'] != "OK"){ // вывод при ошибке
			console.log(data);
		}
		if(data['error']  !== undefined){ // на случай предусмотренной ошибки из PHP
			alert(data['error']);
		}
		window_preload_del();
	}



	//////////////////////////
	// ОКНА
	//////////////////////////
		// показать окно № 1
		function show_dialog_and_send_POST_window(html,title,height,width, button_name){
			height_window = height || 'auto';
			if(height_window == '100%'){
				height_window = $(window).height();
			}
			button_name = button_name || 'OK';
			width = width || '1000';
			title = title || '*** Название окна ***';
			var buttons = new Array();
			buttons.push({
			    text: button_name,
			    click: function() {
			    	var serialize = $('#dialog_gen_window_form form').serialize();
			    	
			    	$('#general_form_for_create_product .pad:hidden').remove();
				    $.post('', serialize, function(data, textStatus, xhr) {
				    	
				    	
						$('#dialog_gen_window_form').html('');
						$('#dialog_gen_window_form').dialog( "destroy" );				
						
						standard_response_handler(data);

					},'json');				    	
			    }
			});

			if($('#dialog_gen_window_form').length==0){
				$('body').append('<div id="dialog_gen_window_form"></div>');
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
				$('body').append('<div id="dialog_gen_window_form2"></div>');
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
					$( this ).dialog( "close" ).dialog( "destroy" );
					$('#dialog_gen_window_form_'+window_num+'').remove();
					// $('#dialog_gen_window_form_'+window_num+'').dialog( "destroy" );
			    }
			});			

			$('body').append('<div id="dialog_gen_window_form_'+window_num+'"></div>');			
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
//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- end
//////////////////////////////////

// radio button

// jQuery(document).ready(function($) {
// 	alert('hellow Workd');
// });

function create_datepicker_for_variant_cont(){
	$('#date_1').datetimepicker({
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
	 	format:'d.m.Y',
	 	
	});

	$('#date_2').datetimepicker({
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
	 	format:'d.m.Y',
	 	
	});
}




$(document).on('change', '.one_row_for_this_type input[type="radio"]', function(event) {
	$(this).parent().find('div.pad').hide('fast');
	$(this).parent().find('div.pad').find('input[type="checkbox"],input[type="radio"]').prop('checked',false);
	if($(this).next().next().next().hasClass('pad')){
		$(this).next().next().next().show('fast');
		$(this).next().next().next().find('input[type="checkbox"],input[type="radio"]').prop('checked',false);
	}
	
});

// checkbox
$(document).on('change', '.one_row_for_this_type input[type="checkbox"]', function(event) {
	if($(this).prop('checked')){
		if($(this).next().next().next().hasClass('pad')){
			// показываем доп меню если есть
			$(this).next().next().next().show('fast');
		}	
	}else{
		if($(this).next().next().next().hasClass('pad')){
			//скрываем доп меню если есть
			$(this).next().next().next().hide('fast');
			// отменяем выбранное
			$(this).next().next().next().find('input').prop('checked',false);
			// скрываем доп внутренние опции
			$(this).next().next().next().find('.pad').hide('fast');
		}	
	}	
});
$(document).on('click', '.answer_table .delete_user_val', function(event) {
	$(this).parent().parent().remove();
});

// кнопка добавления вариантов
$(document).on('click', '.btn_add_var', function(event) {
	// клонируем объект
	var obj = $(this).parent().prev().clone();
	// получаем класс
	var get_class_clone_obj = obj.attr('data-type');
	// подсчитываем количесвто уже созданных классов
	var num_var = $('#general_form_for_create_product .'+get_class_clone_obj).length;
	// получаем порядковый номер создаваемого нами варианта
	var num_var_new = num_var+1;
	// меняем название для опознания варианта
	
	// console.log('num_var = '+num_var);
	if($(this).parent().prev().find('strong number').length == 0){ // если 
		var text = $('.'+get_class_clone_obj).find('strong').html();
		$(this).parent().prev().find('strong').html(text+' <number>(Вариант '+num_var+')</number>');
		obj.find('strong').html(text+' <number>(Вариант '+(Number(num_var)+1)+')</number>');
	}else{
		obj.find('strong number').html('(Вариант '+(Number(num_var)+1)+')');
	}
	
	

	

	obj.find('input[type="checkbox"],input[type="radio"],input[type="text"],select').each(function(index, el) {
		// var new_name = $(this).attr('name')+'_'+num_var_new;
		// получаем новый id
		var new_id = $(this).attr('id')+'_'+num_var_new;
		// подставляем id
		$(this).attr('id',new_id);//.attr('name',new_name);
		
		// получаем старое имя
		var str = $(this).attr('name');

		str = str.replace('['+(num_var-1)+']', '['+num_var+']');
		console.log(str);
		$(this).attr('name',str)
		
		//console.log($(this).next().get(0).tagName);
		if($(this).next().get(0).tagName == 'LABEL'){
			$(this).next().attr('for',new_id);
		}
	});
	$(this).parent().before(obj);
});

// кнопка снятия выделения со всего списка
$(document).on('click', '.cancel_selection', function(event) {
	var obj = $(this).parent().prev();
	obj.find('.pad').css({'display':'none'});
	obj.find('input').each(function(index, el) {
		$(this).prop('checked', false);
	});

});

// кнопка добавить своё значение
$(document).on('click', '.btn_add_val', function(event) {
	var obj = $(this).parent().prev();
	var obj_input = obj.find('input').first();

	var type_new_obj = obj_input.attr('type');
	var name = obj_input.attr('name');

	var input_text = '<input class="change_form_inputs" type="text" value="" id="eded_input"><br>';
	var append = '<input type="'+type_new_obj+'" name="'+name+'">'+input_text;
	// console.log();
	$(this).parent().prev().append(append);
	$('#eded_input').prev().click();
	$('#eded_input').focus().removeAttr('id');
});

// сохранение значения input в value списка
$(document).on('keyup', '.change_form_inputs', function(event) {
	$(this).prev().val($(this).val()); 
	/* Act on the event */
});


//отправка запроса на получение формы
$(document).on('click','#create_new_position',function(e) {	
	// if(e.keyCode == 192){//ESC	
		$.post('', {
			AJAX:'to_chose_the_type_product_form'
		}, function(data, textStatus, xhr) {
			// вызов окна выбора типа продукции
			// show_dialog_chosen_type_product(data);
			standard_response_handler(data);			
			//alert(data);
		},'json');
	// }
});

// создание диалогового окна с выбором типа продукции
function show_dialog_chosen_type_product(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				// если ответ от серера положительный
				if(data['response']=="OK"){
					// отфильтровываем каталожный товар
					if(data['type']=="cat"){
						// ОБРАБОТКА КАТАЛОЖНОГО ТОВАРА
						show_dialog_and_send_POST_window(Base64.decode(data['html']),'Введите № артикула',$(window).height());

					}else{
						// ОБРАБОТКА НЕКАТАЛОЖНОГО ТОВАРА
						// вызов формы заведения не каталожного товара и генерации вариантов
						show_dialog(Base64.decode(data['html']));						
					}
				}else{
					alert('Что-то пошло не так');
				}
			},'json');				    	
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}
	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: '1000',
          height: 'auto',
          modal: true,
          title : 'Выберите тип продукции',
          autoOpen : true,
          buttons: buttons          
        });

}


// создание диалогового окна с формой заведения вариантов расчёта для снаба
function show_dialog(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var moderate = 0;
	    	$('#general_form_for_create_product .one_row_for_this_type').each(function(index, el) {
	    		
	    		if($(this).attr('data-moderate')=='1'){
	    			// moderate = 0;
	    			var moder = false;
	    			// проверка  nput[type="checkbox"],input[type="radio"
	    			$(this).find('input[type="checkbox"],input[type="radio"]').each(function(index, el) {		    				
		    			if($(this).prop('checked')){		    					
		    				moder = true;
		    			}
	    			});
	    			// проверка input[type="text"]
	    			$(this).find('input[type="text"]').each(function(index, el) {		    				
		    			if($(this).val()!=''){
		    				moder = true;
		    			}
	    			});

	    			// проверка textarea
	    			$(this).find('textarea').each(function(index, el) {		    				
		    			// alert($(this).val());
		    			if($(this).val()!=''){
		    				moder = true;
		    			}
	    			});

	    			if(moder===true){
	    				$(this).css({'border':'none'}).removeClass('disabled_moderation');	    				
	    			}else{
	    				$(this).css({'border':'1px solid red'}).addClass('disabled_moderation');
	    				moderate = 1;
	    			}
	    			// console.log(moder);
	    			
	    		
	    		}	

	    	});
	    	// console.log(moderate);
	    	if(moderate==0){
		    	// $('#general_form_for_create_product .pad:hidden').remove();
		    	// убираем всё лишнее
		    	// удаляем скрытые не заполненные поля
				$('#general_form_for_create_product .pad:hidden').remove();
				// убиваем не заполненные текстовые поля
				$('#general_form_for_create_product input[type="text"]').each(function(index, el) {
					if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
						$(this).remove();
					}
				});

					// убиваем не заполненные текстовые поля
				$('#general_form_for_create_product textarea').each(function(index, el) {
					if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
						$(this).remove();
					}
				});

				$('#general_form_for_create_product input[type="checkbox"]').each(function(index, el) {		    				
					if(!$(this).prop('checked') && $(this).parent().attr('data-moderate')==0){		    					
						$(this).remove();
					}
				});

				$('#general_form_for_create_productinput[type="radio"]').each(function(index, el) {		    				
					if(!$(this).prop('checked') && $(this).parent().attr('data-moderate')==0){		    					
						$(this).remove();
					}
				});
		    	$.post('', $('#general_form_for_create_product form').serialize(), function(data, textStatus, xhr) {
					// alert(data);
					// $('#dialog_gen_window_form').html(data)
					show_dialog_var(data);
					$('#general_form_for_create_product').remove();
				});
			}else{
				$("body,html").animate({scrollTop:($('.disabled_moderation').eq(0).offset().top)}, 800);
				return false;
				

				alert('Исправьте ошибки заполнения');
			}
			//general_form_for_create_product();	    	
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}
	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: '1000',
          height: 'auto',
          modal: true,
          title : 'Заполните форму',
          autoOpen : true,
          buttons: buttons          
        });

}

// убираем всё лишнее
function remove_all_unnecessary(){
	// удаляем скрытые не заполненные поля
	$('#general_form_for_create_product .pad:hidden').remove();
	// убиваем не заполненные текстовые поля
	$('#general_form_for_create_product input[type="text"]').each(function(index, el) {
		if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
			$(this).remove();
		}
	});

	// убиваем не заполненные текстовые поля
	$('#general_form_for_create_product textarea').each(function(index, el) {
		if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
			$(this).remove();
		}
	});

	$('#general_form_for_create_product input[type="checkbox"], #general_form_for_create_productinput[type="radio"]').each(function(index, el) {		    				
		if(!$(this).prop('checked') && $(this).parent().attr('data-moderate')==0){		    					
			moder = true;
		}
	});
}

// создание диалогового окна с выбором заведённых вариантов
function show_dialog_var(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	
	    	// убираем всё лишнее
	    	// удаляем скрытые не заполненные поля
			$('#general_form_for_create_product .pad:hidden').remove();
			// убиваем не заполненные текстовые поля
			$('#general_form_for_create_product input[type="text"]').each(function(index, el) {
				if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
					$(this).remove();
				}
			});

				// убиваем не заполненные текстовые поля
			$('#general_form_for_create_product textarea').each(function(index, el) {
				if($(this).val()=='' && $(this).parent().attr('data-moderate')==0){
					$(this).remove();
				}
			});

			$('#general_form_for_create_product input[type="checkbox"]').each(function(index, el) {		    				
				if(!$(this).prop('checked') && $(this).parent().attr('data-moderate')==0){		    					
					$(this).remove();
				}
			});

			$('#general_form_for_create_productinput[type="radio"]').each(function(index, el) {		    				
				if(!$(this).prop('checked') && $(this).parent().attr('data-moderate')==0){		    					
					$(this).remove();
				}
			});

	    	var serialize = $('#dialog_gen_window_form form').serialize();
		    $.post('', serialize, function(data, textStatus, xhr) {
				
				//$('#dialog_gen_window_form').append(data);
				if(data=="OK"){
					location.reload();
				}else{
					alert(data);
				}
			});
				    	
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}
	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: '1000',
          height: 'auto',
          modal: true,
          title : 'Заполните форму',
          autoOpen : true,
          buttons: buttons          
        });

}

// // стандартный обработчик ответа AJAX
// function standard_response_handler(data){
// 	if(data['function'] !== undefined){ // вызов функции... если требуется
// 		window[data['function']](data);
// 	}
// 	if(data['response'] != "OK"){ // вывод при ошибке
// 		console.log(data);
// 	}
// 	if(data['error']  !== undefined){ // на случай предусмотренной ошибки из PHP
// 		alert(data['error']);
// 	}
// 	if(data['response']=='show_new_window'){
// 		title = data['title'];// для генерации окна всегда должен передаваться title
// 		show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
// 	}
// }


// показать окно № 1
// function show_dialog_and_send_POST_window(html,title,height,width){
// 	height_window = height || 'auto';
// 	width = width || '1000';
// 	title = title || '*** Название окна ***';
// 	var buttons = new Array();
// 	buttons.push({
// 	    text: 'OK',
// 	    click: function() {
// 	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
// 	    	$('#general_form_for_create_product .pad:hidden').remove();
// 		    $.post('', serialize, function(data, textStatus, xhr) {
// 		    	// если из PHP было передано название какой либо функции
// 		    	// выполняем её
// 		    	if(data['function'] !== undefined){
// 		    		window[data['function']](data);
// 		    	}

// 				if(data['response']=='show_new_window'){
// 					title = data['title'];// для генерации окна всегда должен передаваться title
// 					show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
// 				}else{
// 					// подчищаем за собой
// 					$('#dialog_gen_window_form').html('');
// 					$('#dialog_gen_window_form').dialog( "destroy" );
// 					// тут можно расположить какие либо действия в зависимости от ответа
// 					// с сервера					
// 				}
// 			},'json');				    	
// 	    }
// 	});

// 	if($('#dialog_gen_window_form').length==0){
// 		$('body').append('<div id="dialog_gen_window_form"></div>');
// 	}
// 	$('#dialog_gen_window_form').html(html);
// 	$('#dialog_gen_window_form').dialog({
//           width: width,
//           height: height_window,
//           modal: true,
//           title : title,
//           autoOpen : true,
//           buttons: buttons          
//         });

// }





$(document).keydown(function(event) {
	// отработка поиска артикула по клику на Enter
	if(event.keyCode == 13) {
		if( $("#add_new_articul_in_rt").is(":focus") ){
			event.preventDefault();
			event.stopImmediatePropagation();

			if($('#choose_one_of_several_articles .checked').length == 1 && $('#choose_one_of_several_articles .checked').length == 1){
				$.post('', $('#dialog_gen_window_form form').serialize(),function(data, textStatus, xhr) {
					standard_response_handler(data);
				},'json');
			}
		}		
	}
	// выбор формы на Enter
	if(event.keyCode == 13) {
		if($('#get_form_Html_tbl').length){
			var send = false;
			$('#get_form_Html_tbl input[type="radio"]').each(function(index, el) {
				if($(this).prop("checked")){
					sent = true;
				}
			});
			if(sent){
				$.post('', $('#dialog_gen_window_form form').serialize(),function(data, textStatus, xhr) {
					standard_response_handler(data);
				},'json');
			}
		}
	}



	// TAB

	// if (event.keyCode == 9) { 
	// 	event.preventDefault(); 
	// 	if( $("#add_new_articul_in_rt").is(":focus") ){
	// 		if($('#choose_one_of_several_articles .checked').length == 1){
	// 			$("#add_new_articul_in_rt").blur();	
	// 			$('#choose_one_of_several_articles .checked').addClass('tab_click');

	// 		}
			
	// 	}
	// } 
});

// выбор одной из строк в найденных артикулах
// $(document).on('click', '#choose_one_of_several_articles tr td', function(event) {
// 	var art_id = $(this).parent().attr('data-art_id');
// 	var art = $(this).parent().attr('data-art');
// 	var art_name = $(this).parent().attr('data-art_name');
// 	// подставляем выбранные значения
// 	$('#information_block_of_articul input[name="art"]').val(art);
// 	$('#information_block_of_articul input[name="art_id"]').val(art_id);
// 	$('#information_block_of_articul input[name="art_name"]').val(art_name);
// 	$('#choose_one_of_several_articles tr.checked').removeClass('checked');
// 	$(this).parent().find('input[type="checkbox"]').prop('checked', true);
// 	$(this).parent().addClass('checked');
// });

$(document).on('click', '#choose_one_of_several_articles tr td', function(event) {
	// event.preventDefault();
	// event.stopImmediatePropagation();
	if($(this).find('input[type="checkbox"]').length > 0){return;}
	var tr = $(this).parent();
	var checkbox = tr.find('input');
	if(checkbox.prop('checked')){
		tr.removeClass('checked');
		checkbox.prop('checked',false);
	}else{		
		tr.addClass('checked');
		checkbox.prop('checked',true);
	}
});




// function treatment_choose_art_tbl(){
// 	$('#choose_one_of_several_articles tr').shifty({
// 		className:'checked', // класс выделенного элемента
// 		select:function(el){el.find('input').prop('checked',true);}, // выделение
// 		unselect:function(el){el.find('input').prop('checked',false);} // снятие выделения
// 	});
// };

function focus_art_form_search(){
	if($('#add_new_articul_in_rt').length > 0){
		$('#add_new_articul_in_rt').focus();
	}
}

// отправка формы при выборе
$(document).on('change', '#get_form_Html_tbl input[type="radio"]', function(event) {
	$.post('', $('#get_form_Html_tbl').parent().serialize(), function(data, textStatus, xhr) {
		$(this).parent().parent().parent().html('');
		$(this).parent().parent().parent().dialog( "destroy" );
		standard_response_handler(data);

	},'json');	
});


// отправка формы поиска артикула
$(document).on('keyup', '#add_new_articul_in_rt', function(event) {
	event.preventDefault();

	var art = $(this).val();
	$('#information_block_of_articul').addClass('loader');
	$.post('', {
		AJAX: 'check_exists_articul',
		art: art
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			$('#information_block_of_articul').removeClass('loader').html(Base64.decode(data['html']));
			standard_response_handler(data);
		}
	},'json');
});

$(document).on('change', '#choose_one_of_several_articles input[type="checkbox"]', function(event) {
	// event.preventDefault();
	// event.stopImmediatePropagation();
	// $(this).parent().click();
	console.log($(this).prop('checked'))
	if($(this).prop('checked')){
		$(this).parent().parent().parent().addClass('checked');
		
		//	$(this).prop('checked',false)
	}else{
		$(this).parent().parent().parent().removeClass('checked');
		
		// $(this).prop('checked',true)
	}
	/* Act on the event */
});


$(document).on('click', '#choose_the_size tr td', function(event) {
	var size = $(this).parent().find('td').eq(0).html();
	var price = $(this).parent().find('span').html();

	// подставляем выбранные значения

	$('#dialog_gen_window_form input[name="chosen_size"]').val(size);
	$('#dialog_gen_window_form input[name="price_out"]').val(price);

	$('#choose_the_size tr.checked').removeClass('checked');
	$(this).parent().addClass('checked');

});


function window_reload(){
	location.reload();
}
//	15610440.34


//	обновить окно
$(document).on('click', '#replace_from_window button', function(event) {
	event.preventDefault();
	window_preload_add();
	var type_product = $(this).parent().attr('data-type');
	$.post('', {
		AJAX:'get_form_Html',
		type_product:type_product
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');

});



//////////////////////////
//	АДМИНИСТРИРОВАНИЕ ФОРМЫ
//////////////////////////


// добавить новое поле в корень группы
$(document).on('click', '.add_input_in_group.redactor_buttons', function(event) {
	// event.preventDefault();
	var id_row = $(this).attr('data-form_group_id');
	var type_product = $(this).attr('data-type_product');
	var name_group_en = $(this).attr('data-name_group_en');
	$.post('', {
		AJAX: 'get_form_width_add_input',
		type_product:type_product,
		row_id_group_inputs: id_row,
		name_group_inputs_en:name_group_en,
		parent_name:name_group_en
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		$('#cirillic_name_input').liTranslit({
			elAlias: $('#eng_name_input')
		});

	},'json');
});
// прикрепить новое поле к родителю
$(document).on('click', '.add_element.redactor_buttons', function(event) {
	event.preventDefault();
	var parent_id = $(this).attr('data-id');
	var parent_name = $(this).attr('data-name_en');
	var type_product = $(this).attr('data-type_product');
	var row_id_group_inputs = $(this).attr('data-group_inputs_row_id');
	$.post('', {
		AJAX: 'get_form_width_add_input',
		type_product:type_product,
		parent_id: parent_id,
		row_id_group_inputs:row_id_group_inputs,
		parent_name:parent_name
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		$('#cirillic_name_input').liTranslit({
			elAlias: $('#eng_name_input')
		});

	},'json');
});

// удалить поле
$(document).on('click', '.group_del.redactor_buttons', function(event) {
	event.preventDefault();
	if(confirm('Вы уверены ???')){
		var row_id = $(this).attr('data-id');
		
		// удаляем кнопку Ред.
		$(this).prev().remove();
		// удаляем кнопку Добавить.
		$(this).next().remove();
		// удаляем данную кнопку
		$(this).remove().removeClass('group_del').html('удалено').css({"border":"none"});


		$.post('', {
			AJAX: 'delete_input_width_form',
			row_id:row_id
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	}
});


// кнопка редактировать
$(document).on('click', '.group_edit.redactor_buttons', function(event) {
	event.preventDefault();
	var type_product = $('#general_form_for_create_product').attr('data-type_product');
	var row_id = $(this).attr('data-id');
	var parent_name = $(this).attr('data-name_en');
	$.post('', {
			AJAX: 'edit_input_width_form',
			row_id:row_id,
			parent_name:parent_name,
			type_product:type_product
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
});


function update_form(){
	var type_product = $('#general_form_for_create_product').attr('data-type_product');
	// var type_product = $(this).parent().attr('data-type');
	$.post('', {
		AJAX:'get_form_Html',
		type_product:type_product
	}, function(data, textStatus, xhr) {
		if(data['response'] == "show_form_moderate_window"){
			$('#general_form_for_create_product').replaceWith(Base64.decode(data['html']));
		}
	},'json');
}

// показываем настройки размера текста пояснений
$(document).on('click', '#the_small_text_on_label', function(event) {
	$('#change_the_font_size').toggle('fast');
});


$(document).on('click', '#add_auto_key', function(event) {
	var key = $(this).attr('data-key')
	$(this).prev().val(key);
});