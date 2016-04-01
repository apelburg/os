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
		// поочерёдный вызов функции 
		if(data['function'] !== undefined){ 
			count = data['function'].length;
			for (var i = count - 1; i >= 0; i--) {
				window[data['function'][i]['function']](data['function'][i]);
			};
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
			if(height_window == '100%'){
				height_window = $(window).height();
			}
			width = width || '1000';
			title = title || '*** Название окна ***';
			var buttons = new Array();
			buttons.push({
			    text: 'OK',
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
				    	$('#dialog_gen_window_form').html('');
						$('#dialog_gen_window_form').dialog( "destroy" );				
						
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
					$('#dialog_gen_window_form_'+window_num+'').html('');
					$('#dialog_gen_window_form_'+window_num+'').dialog( "destroy" );
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

		// вывод системного сообщения 
		function echo_message(data){

			$("<li/>", {
			      "class": data.message_type,
			      "css":{"opacity":1,"top":0},
			      click: function(){
			          $(this).animate({opacity:0},'fast',function(){$(this).remove()});
			      }
			}).append(Base64.decode(data.message)).appendTo("#apl-notification_center").fadeIn('slow', 
		        function(){
		            var el = jQuery(this);
		            setTimeout(function(){
		                el.fadeOut('slow',
		                    function(){
		                        jQuery(this).remove();
		                    });
		            }, 7000);
		    });

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

$(document).ready(function() {
	/* отработка верхней части */
	//календарь для даты отгрузки
	// $('#datepicker1,#datepicker2').datetimepicker({
	// 	minDate:new Date(),
	// 	// disabledDates:['07.05.2015'],
	// 	timepicker:false,
	//  	dayOfWeekStart: 1,
	//  	onGenerate:function( ct ){
	// 		$(this).find('.xdsoft_date.xdsoft_weekend')
	// 			.addClass('xdsoft_disabled');
	// 		$(this).find('.xdsoft_date');
	// 	},
	// 	onSelectDate: function(ct){
	// 		//$('#datepicker1').removeAttr('readonly').removeClass('input_disabled');
	// 		$('#btn_date_var').click();
	// 	},
	//  	format:'d.m.Y',
	 	
	// });
	// // время для даты отгрузки
	// $('#timepicker1,#timepicker2').datetimepicker({
	//  datepicker:false,
	//  format:'H:i',
	//  // minTime:'9:00',
	//  // maxTime:'21:00'
	//  allowTimes:[
	// 	  '09:00', '10:00', '11:00', '12:00','13:00', '14:00','15:00', 
	// 	  '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
	//  ]
	// });	

	/* отработака индивидуальной части для каждого варианта*/
	//************************************************/
	//                ВРЕМЯ И ДАТА ОТГРУЗКИ
	//************************************************/
	// ДАТА
	create_datepicker_for_variant_cont();

	// ВРЕМЯ
	create_timepicker_for_variant_cont();
});





// меню манипуляций с вариантом
$(document).on('click', '.variant_status_sv', function(event) {
	$(this).parent().click();
	
	/**
	 *	пункты меню
	 */

	var li_1 = $('<li/>',{
		click: function(){
			window_preload_add();	
			copy_variant(false);
			window_preload_del();
		}
	}).append('Скопировать товар');

	var li_2 = $('<li/>',{
		click: function(){
			window_preload_add();	
			copy_variant(true);
			window_preload_del();
		}
	}).append('Скопировать товар + услуги');

	var li_3 = $('<li/>',{
		"class":"grey",
		click: function(){
			window_preload_add();		
			edit_variants('one','grey');
			window_preload_del();
		}
	}).append('Подумать, но не удалять');
	

	var li_4 = $('<li/>',{
		"class":"green",
		click: function(){	
			window_preload_add();		
			edit_variants('one','green');
			window_preload_del();
		}
	}).append('Использовать в КП');

	var li_5 = $('<li/>',{
		"class":"red",
		click: function(){
			window_preload_add();	
			edit_variants('one','red');
			window_preload_del();
		}
	}).append('Отказанный, нет в наличии');

	var li_6 = $('<li/>',{
		"class":"sgreen",
		click: function(){		
			window_preload_add();		
			edit_variants('one','sgreen');
			edit_variants('any','red');
			window_preload_del();
		}
	}).append('Окончательный - использовать в СПФ');
	
	var obj = $('<ul/>').append(li_1,li_2,li_3,li_4, li_5, li_6);

	get_position_menu_absolute(event,obj);
});


$(document).on('click', '.js-edit-type_specificate', function(event) {
	var value = $(this).val();
	var row_id = $(this).attr('data-id');
	switch(value){
		case 'none':
			$(this).parent().find('.type_specificate-info').hide();
			break;
		case 'date':
			$(this).parent().find('.type_specificate-info').show();
			$(this).parent().find('.type_specificate-info.rd').hide();
			break;
		default:
			$(this).parent().find('.type_specificate-info.rd').show();
			$(this).parent().find('.type_specificate-info.date').hide();
			break;
	}
	$.post('', {
		AJAX:"save_shipping_type",
		value:value,
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	});
});

// редактировать вриант
function edit_variants(anyone, row_status) { 
	var id_in = new Array();
	var id_in_row = '';
	//var anyone; // any, one
	//var row_status; // green, grey, red, sgreen
	// console.log(anyone);
	// console.log(row_status);
	var i = 0;

	var data_cont_id = $('#all_variants_menu .variant_name.checked').attr('data-cont_id');

	if(anyone=="one"){
		id_in[i] = $('#all_variants_menu .variant_name.checked').attr('data-id');

		
		$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass(row_status);
		// снимаем / ставим ограничения редактирования
		if(row_status == 'red' && !$('#'+data_cont_id).hasClass('archiv_opacity')){
			$('#'+data_cont_id).addClass('archiv_opacity');
		}else if(row_status != 'red' && $('#'+data_cont_id).hasClass('archiv_opacity')){
			$('#'+data_cont_id).removeClass('archiv_opacity');
		}
	}else{
		$('#all_variants_menu .variant_name').each(function(index, el) {

			// console.log($(this).hasClass('checked'));
			if($(this).hasClass('checked') || $(this).hasClass('show_archive')){
				return true;
			}else{
				id_in[i] = $(this).attr('data-id');i++;
				$(this).find('span').attr('class','variant_status_sv').addClass(row_status);
			}

			// alert($(this).attr('data-cont_id'))
			//снимаем / ставим ограничения редактирования
			data_cont_id = $(this).attr('data-cont_id');
			if(row_status == 'red' && !$('#'+data_cont_id).hasClass('archiv_opacity')){
				$('#'+data_cont_id).addClass('archiv_opacity');
			}else if(row_status != 'red' && $('#'+data_cont_id).hasClass('archiv_opacity')){
				$('#'+data_cont_id).removeClass('archiv_opacity');
			}
		});
	}
	//var color = row_status; 
	id_in_row = id_in.join(', '); // id_in


	$.post('', {
		//global_change: 'AJAX',
		AJAX: 'change_status_row',
		id_in:id_in_row,
		color:row_status

	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
		// console.log(data);
		// if(row_status == 'red'){window_reload();}
		standard_response_handler(data);
	},'json');
}
// копирование варианта
function copy_variant(services){
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	$.post('',{
		AJAX: 'new_variant',
		id:id,
		row_id:row_id,
		services:services
		
	}, function(data, textStatus, xhr) {
		if(data['response']=='OK'){
			// клонируем html вкладки текущего расчета
			var menu_li = $('#variants_name .variant_name.checked ').clone();
			// ставим название и на всякий подчищаем архивный класс, если он есть
			menu_li.html(data['num_row_for_name']+'<span class="variant_status_sv green"></span>').removeClass('show_archive');		
			// получаем id текущего пблока расчёта
			var id_div = menu_li.attr('data-cont_id');
			// меняем id для для работы вкладки с новым блоком 
			menu_li.attr('data-cont_id','variant_content_block_'+data['num_row']);
			// убираем класс "выбрано" со всех вкладок
			$('#variants_name .variant_name').removeClass('checked');
			// вставляем html
			$('#variants_name .variant_name:last-of-type').after(menu_li);
			// post запрос для названия вкладки и получения id для склонированного контента

			// клонируем html текущего расчёта со всеми данными
			var div_html = $('#'+id_div).clone();
			// id на новый
			div_html
			.attr('id','variant_content_block_'+data['num_row'])
			.attr('data-id',data['new_id']);
			// правим URL
			$.setUrlVal('varID_checked',data['new_id']);

			// подчищаем архивный класс, если есть
			div_html.removeClass('archiv_opacity');
			// скрываем все видимые блоки расчета
			$('#edit_variants_content .variant_content_block').css({'display':'none'})
			// вставляем html
			$('#edit_variants_content .variant_content_block:last-of-type').after(div_html);

			// убиваем календари 
			destroy_datetimepicker_for_variant_cont();
			// создаем календари для всех по новой
			create_datepicker_for_variant_cont();// ДАТА
			create_timepicker_for_variant_cont();// ВРЕМЯ
			window_reload();
			
		}
	},"json");
}





$(document).on('click', 'body', function(event) {
	if($("#position_menu_absolute").length){
		$("#position_menu_absolute").remove();		
	}
});


// меню
function get_position_menu_absolute(event,content){
	event.stopPropagation();
	if($("#position_menu_absolute").length){$("#position_menu_absolute").remove();}
	$("<div/>", {
			      "css":{"opacity":1,"top":(event.pageY-25),"left":(event.pageX-25)},
			      "id":"position_menu_absolute",
			      click: function(){
			          remove_position_menu_absolute();
			      }
			}).append(content).appendTo('body').fadeIn('slow');
	
	// клик вне элемента
	$(document).click( function(event){
    	if( $(event.target).closest("#position_menu_absolute").length ) 
    		return;
    	remove_position_menu_absolute();
    	event.stopPropagation();
    });
	


}
// удаление меню
function remove_position_menu_absolute(){
	$("#position_menu_absolute").animate({opacity:0},'fast',function(){$(this).remove()});
}



function destroy_datetimepicker_for_variant_cont(){
	$('#edit_variants_content .datepicker2').datetimepicker('destroy');
	$('#edit_variants_content .timepicker2').datetimepicker('destroy');
}

function create_timepicker_for_variant_cont(){
	$('#edit_variants_content .timepicker2').datetimepicker({
	 datepicker:false,
	 format:'H:i',
	 closeOnDateSelect:true,
	 onChangeDateTime: function(dp,$input){// событие выбора даты
			// // меняем html
			var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
			// $('#'+id_variant+' .fddtime_rd2').val('');
			// $('#'+id_variant+' .btn_var_std[name="std"]').removeClass('checked');

			// получение данных для отправки на сервер
			var id = $('#variants_name .variant_name.checked ').attr('data-id');
			var row_id = $('#claim_number').attr('data-order');	
			var time = $input.val()+':00';

			// alert($input.attr('class'));
			$.post('', {
				// global_change: 'AJAX',
				AJAX: 'change_variante_shipping_time',
				id: id,
				row_id: row_id,
				time: time
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},"json");
		},
	 // minTime:'9:00',
	 // maxTime:'21:00'
	 allowTimes:[
		  '00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00',
		  '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
	 ]
	});	
}


function create_datepicker_for_variant_cont(){
	$('#edit_variants_content .datepicker2').datetimepicker({
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
			// меняем html
			var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
			$('#'+id_variant+' .timepicker2').show().val(''); // показать поле время
			// $('#'+id_variant+' .fddtime_rd2').val('');
			// $('#'+id_variant+' .btn_var_std[name="std"]').removeClass('checked');		


			// получение данных для отправки на сервер
			var id = $('#variants_name .variant_name.checked ').attr('data-id');
			var row_id = $('#claim_number').attr('data-order');	
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				// global_change: 'AJAX',
				AJAX: 'change_variante_shipping_date',
				id: id,
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},"json");
		},
	 	format:'d.m.Y',
	 	
	});
}


// обработка кнопок ПЗ, НПЗ
$(document).on('click', '#edit_variants_content .tirage_buttons .btn_var_std', function(event) {
	// отработка html
	$('#edit_variants_content .tirage_buttons .btn_var_std').removeClass('checked');
	$(this).addClass('checked');
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	var pz = ($(this).html() == 'ПЗ')?1:0; // печатать \ не печатать запас
	// отправка данных на сервер
	$.post('', {
		// global_change: 'AJAX',
		AJAX: 'change_tirage_pz',
		id:id,
		row_id:row_id,
		pz: pz
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// отработка клика по быстрым кнопкам
$(document).on('click','#btn_make_std',function(){
	$(this).addClass('checked');
	$('#btn_make_var').removeClass('checked');
	$(this).parent().find('input').attr('readonly','true').addClass('input_disabled').val(10);
});
$(document).on('click','#btn_make_var',function(){
	$(this).addClass('checked');
	$('#btn_make_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});

$(document).on('click','#btn_date_std',function(){
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();
	// указать дату отгрузки
	// решить как будет производиться обсчет выходных дней и праздников
	// исключить выходные из подсчёта рабочих дней
	cmm = (curr_date+5) + "." + curr_month + "." + curr_year;	
	$(this).addClass('checked');
	$('#btn_date_var').removeClass('checked');
	$('#datepicker1').val(cmm).attr('readonly','true').addClass('input_disabled');
});
$(document).on('click','#btn_date_var',function(){	
	$(this).addClass('checked');
	$('#btn_date_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});


//
$(document).on('click','#menu_for_variants_status li',function() {
	var id_in = new Array();
	var id_in_row = '';
	var anyone = $(this).attr('data-anyone');
	var row_status = $(this).attr('class');
	// console.log(anyone);
	// console.log(row_status);
	var i = 0;

	if(anyone=="one"){
		id_in[i] = $('#all_variants_menu .variant_name.checked').attr('data-id');
		$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass(row_status);
	}else{
		$('#all_variants_menu .variant_name').each(function(index, el) {
			console.log($(this).hasClass('checked'));
			if($(this).hasClass('checked') || $(this).hasClass('show_archive')){
				return true;
			}else{
				id_in[i] = $(this).attr('data-id');i++;
				$(this).find('span').attr('class','variant_status_sv').addClass(row_status);
			}
		});
	}
	//var color = row_status; 
	id_in_row = id_in.join(', '); // id_in


	$.post('', {
		// global_change: 'AJAX',
		AJAX: 'change_status_row',
		id_in:id_in_row,
		color:row_status

	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		// console.log(data);
	},'json');

	// if(row_status=="green"){

	// 	if(anyone=="one"){
	// 		id_in[0] = $('#all_variants_menu .variant_name.checked').attr('data-id');
	// 		$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass(row_status);
	// 	}else{
	// 		$('#all_variants_menu .variant_name').each(function(index, el) {
	// 			if($(this).hasClass('checked')){return true;}
	// 			id_in[index] = $(this).attr('data-id');
	// 		});
	// 	}
	// }else if(row_status=="grey"){

	// 	if(anyone=="one"){
	// 		id_in[0] = $('#all_variants_menu .variant_name.checked').attr('data-id');
	// 		$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass(row_status);
	// 	}else{
	// 		$('#all_variants_menu .variant_name').each(function(index, el) {
	// 			if($(this).hasClass('checked')){return true;}
	// 			id_in[index] = $(this).attr('data-id');
	// 		});
	// 	}
	// }else{
		// if(anyone=="one"){
		// 	id_in[0] = $('#all_variants_menu .variant_name.checked').attr('data-id');
		// 	$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass(row_status);
		// }else{
		// 	$('#all_variants_menu .variant_name').each(function(index, el) {
		// 		if($(this).hasClass('checked')){return true;}
		// 		id_in[index] = $(this).attr('data-id');
		// 	});

		// }
	// }




});
/*
$(document).on('click','#choose_end_variant',function(){
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	// отправляем запрос на смену статуса варианта на Основной
	$.post('', 
		{
			global_change: 'AJAX',
			change_name: 'change_draft',
			id:id,
			row_id:row_id,
		}, function(data, textStatus, xhr) {
		if(data['response']!='1'){
			alert('что-то пошло не так.');
		}else{
			// присваиваем статус варианта Основной
			$('#variants_name .variant_name').each(function(index, el) {
				if(!$(this).hasClass('checked')){
					if($('#show_archive a').attr('data-true')!='1'){
						$(this).remove();
					}else{
						$(this).addClass('show_archive');
						$('#'+$(this).attr('data-cont_id')).addClass('archiv_opacity');

					}
				}
			});
			//$('#choose_end_variant').attr('data-back','1').html('Сделать черновиком');
		}
	},'json');
	
});
*/



// смена функциональной кнопки выбора основного варианта
function test_chenge_archive_list(){
	if($('#all_variants_menu .variant_name.checked').hasClass('show_archive')){
		$('#choose_end_variant').attr('id','extract_from_archive').find('.chenged_text').html('Извлечь расчёт из архива')
	}else{
		$('#extract_from_archive').attr('id','choose_end_variant').find('.chenged_text').html('Редактор вариантов');
	}
}

$(document).on('click','#extract_from_archive',function(){
	// отправляем запрос на смену статуса (на "не архив")
	
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	

	// отправка данных на сервер
	$.post('', 
		{
			// global_change: 'AJAX',
			AJAX: 'change_archiv',
			id:id,
			row_id:row_id
		}, function(data, textStatus, xhr) {
		if(data['response']!='1'){
			alert('что-то пошло не так.');
		}else{
			// меняем html получив положительный ответ
			var id_div = $('#all_variants_menu .variant_name.checked').removeClass('show_archive').attr('data-cont_id');
			$('#'+id_div).removeClass('archiv_opacity');
			$('#all_variants_menu .variant_name.checked span').attr('class','variant_status_sv').addClass('green');
			test_chenge_archive_list();	
			standard_response_handler(data);		
		}
	},'json');
});



// изменение входящей цены за единицу товара
// $(document).on('keyup','.row_tirage_in_one.price_in .edit_span',function(){
// 	var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');

// 	//цена входящая
// 	var price_in = Number($(this).html());
// 	//цена исходящая
// 	var price_out = Number($(id_variant+' .tirage_and_price_for_one .row_price_out_one.price_out span').html());
// 	//%
// 	var percent;
// 	percent = Math.ceil(((price_out-price_in)*100/price_in)*100)/100;
// 	// если продаем в убыток, делаем наценку 0 и входящую стоимость приравниваем к исходящей
// 	if(percent<0){
// 		price_in = price_out;
// 		$(this).html(price_in.toFixed(2));
// 		$(id_variant+' .tirage_and_price_for_one .percent_nacenki span').html(0);
// 	}else{
// 		$(id_variant+' .tirage_and_price_for_one .percent_nacenki span').html(percent);
// 	}

// 	// меняем исходящую цену за тираж
// 	var tirage = Number($(id_variant+' .tirage_var').val());
// 	var zapas = Number($(id_variant+' .dop_tirage_var').val());
// 	var pr = (zapas+tirage)*price_in;
// 	$(id_variant+' .row_tirage_in_gen.price_in span.price_in_all').html(pr.toFixed(2));


// 	// пересчёт таблицы с ценами и услугами
// 	recalculate_table_price_Itogo();
// 	// сохраняем входящую и исходящую цены за ед. товара
// 	save_price_in_out_for_one_price();
// });



function save_price_in_out_for_one_price(){
	
	var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	var dop_data_id = $('#variants_name .variant_name.checked ').attr('data-id');
	console.log(id_variant+' .calkulate_table .tirage_and_price_for_one .row_tirage_in_one.price_in span');
	console.log($(id_variant+' .calkulate_table .tirage_and_price_for_one .row_tirage_in_one.price_in span').length);
	var price_in = $(id_variant+' .calkulate_table .row_tirage_in_one.price_in .edit_span').html();
	var price_out = $(id_variant+' .calkulate_table .row_price_out_one.price_out .edit_span').html();
	$.post('', {
		// global_change: 'AJAX',
		AJAX: 'save_price_in_out_for_one_price',
		price_in:price_in,
		price_out:price_out,
		dop_data: dop_data_id
	}, function(data, textStatus, xhr) {
		// console.log(data);
		standard_response_handler(data);
	},'json');

}
// изменение исходящей цены за единицу товара
$(document).on('keyup','.row_price_out_one.price_out .edit_span',function(){
	var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');

	//цена исходящая
	var price_out = Number($(this).html());
	//цена входящая
	var price_in = Number($(id_variant+' .tirage_and_price_for_one .price_in span').html());
	//%
	var percent;

	percent = Math.ceil(((price_out-price_in)*100/price_in)*100)/100;
	// если продаем в убыток, делаем наценку 0 и входящую стоимость приравниваем к исходящей
	if(percent<0){
		price_out = price_in;
		$(this).html(price_out.toFixed(2));
		$(id_variant+' .tirage_and_price_for_one .percent_nacenki span').html(0);
	}else{
		$(id_variant+' .tirage_and_price_for_one .percent_nacenki span').html(percent);
	}

	// меняем исходящую цену за тираж
	var tirage = Number($(id_variant+' .tirage_var').val());
	var zapas = Number($(id_variant+' .dop_tirage_var').val());
	var pr = (zapas+tirage)*price_out;
	$(id_variant+' .row_price_out_gen.price_out span').html(pr.toFixed(2));

	recalculate_table_price_Itogo();
	// сохраняем входящую и исходящую цены за ед. товара
	save_price_in_out_for_one_price();
});
// изменение процента наценки за единицу товара 
$(document).on('keyup','.tirage_and_price_for_one .percent_nacenki span.edit_span',function(){
	//console.log($(this).html());
	var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');

	//round(((price_out-price_in)*100/$value['price_in']),2);
	//цена исходящая
	var price_out = Number($(id_variant+' .tirage_and_price_for_one .price_out span').html());
	//цена входящая
	var price_in = Number($(id_variant+' .tirage_and_price_for_one .price_in span').html());
	var percent = Number($(this).html());
	// минимальный процент наценки
	var min_percent = 0;
	if(percent<min_percent){$(this).html(min_percent);percent = min_percent;}
	var price_out = (price_in+percent*price_in/100);
	$(id_variant+' .tirage_and_price_for_one .price_out span').html(price_out.toFixed(2))


	// меняем исходящую цену за тираж
	var tirage = Number($(id_variant+' .tirage_var').val());
	var zapas = Number($(id_variant+' .dop_tirage_var').val());
	var pr = (zapas+tirage)*price_out;
	$(id_variant+' .row_price_out_gen.price_out span').html(pr.toFixed(2));
	// сохраняем входящую и исходящую цены за ед. товара
	save_price_in_out_for_one_price();
	// пересчитываем итого
	recalculate_table_price_Itogo();
});




// запрещаем ввод в общее поле информации
function disabled_general_quantyti_input( flag ){
	if(flag > 0){
		$('.tirage_var:visible,.dop_tirage_var:visible').addClass('disabled_input').attr('readonly','readonly');		
	}else{
		$('.tirage_var:visible,.dop_tirage_var:visible').removeClass('disabled_input').removeAttr('readonly');
	}
}

$(document).on('click', '.disabled_input', function(event) {
	var message ='В карточке содержится более одного размера.';
	echo_message_js(message,'error_message');
	 message ='Редактирование тиража и запаса возможно только в блоке размеров';
	echo_message_js(message,'system_message');
});

// функция сохранения размерной сетки
function save_all_table_size(){
	
		var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
		var id_size = new Array();
		var tirage = new Array();
		var var_id = new Array();
		var dop = new Array();
		var i = 0;
		var b = 0;
		$('.size_card:visible .val_tirage').each(function(index, el) {
			id_size[index] = $(this).attr('data-id_size');
			tirage[index] = Number($(this).val());
			var_id[index] = $(this).attr('data-var_id');
			dop[index] = Number($(this).parent().parent().find('.val_tirage_dop').val());

			if($('.size_card:visible').length > 0){
				if(tirage[index] > 0 || dop[index] > 0){
					i++;b++;
				}
			}
		});

		if($('.size_card:visible').length > 0){
			if(b > 1 || i > 1){
				disabled_general_quantyti_input( 1 );
			}else{
				disabled_general_quantyti_input( 0 );
			}
		}
		
		$.post('', {
			// global_change: 'AJAX',
			AJAX: 'size_in_var_all',
			val:tirage,
			key:id_size,
			dop:dop,
			id: var_id
		}, function(data, textStatus, xhr) {
			// console.log(data);
			standard_response_handler(data);
		},'json');
	
}

// расчитывает размер с наибольшим свободным местом
// возвращает объект (массив) с информацией о строке размера
function get_info_for_ost(){
	var id = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	var max = 0; // максимальное значение остатка, исключая полностью заполненные менеджером
	var ostatok = 0; // доступный остаток у поставщика по данному размеру
	var tir = 0; // введённый тираж
	var prigon = 0;
	var ind = 0;
	var tir_out = 0;
	var prigon_out = 0;

	$(id+' .size_card .ostatok_free').each(function(index, el) {
		ostatok = Number($(this).html());
		prigon = Number($(this).parent().find('.val_tirage_dop').val());
		tir = Number($(this).parent().find('.val_tirage').val());		

		if(ostatok>max && ostatok>(tir+prigon)){
			ind = index+1;
			tir_out = Number($(this).parent().find('.val_tirage').val());
			prigon_out = Number($(this).parent().find('.val_tirage_dop').val());
			max = Number($(this).html()); // свободно у поставщика максимально
		}
	});
	var arr = [max, tir_out, ostatok, prigon_out, ind];
	return arr;
}




/**************************************************************/
// РАСЧЕТ ПРИ ЗАГРУЗКЕ СТРАНИЦЫ
$(window).load(function() {
	calkulate_table_calc();
});

// ПЕРЕРАСЧЕТ ТАБЛИЦЫ calkulate_table (для товаров из каталога)

function calkulate_table_calc(){
	// console.log('start calkulate_table_calc()');
	// обсчёт стоимости тиража

	// получаем id активного блока
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	
	// подсчёт входящей стоимости за тираж
	var tir = Number($(id_active_variant+' .tirage_var').val());
	var zap = Number($(id_active_variant+' .dop_tirage_var').val());
	var price_for_one = Number($(id_active_variant+' .calkulate_table .tirage_and_price_for_one .row_tirage_in_one span').html());
	var price_in = price_for_one*(zap+tir);
	// console.log(price_in);
	$(id_active_variant+" table.calkulate_table tr.tirage_and_price_for_all td.row_tirage_in_gen span").html(Math.ceil((price_in)*100)/100)

	// подсчёт исходящей стоимости за тираж
	price_for_one = Number($(id_active_variant+' .calkulate_table .tirage_and_price_for_one .row_price_out_one span').html());
	var price_out = price_for_one*(zap+tir);
	$(id_active_variant+" table.calkulate_table tr.tirage_and_price_for_all td.row_price_out_gen span").html(Math.ceil((price_out)*100)/100)

	// подсчёт прибыли за тираж
	price_for_one = Number($(id_active_variant+' .calkulate_table .tirage_and_price_for_one .row_pribl_out_one span').html());
	var profit = price_for_one*(zap+tir);
	$(id_active_variant+" table.calkulate_table tr.tirage_and_price_for_all td.row_pribl_out_gen span").html(Math.ceil((profit)*100)/100)

	// console.log('calkulate_row_itogo();');
	recalculate_table_price_Itogo();
}

/**
 *	подсчет итого для KK
 *
 *	@author  		Алексей Капитонов
 *	@version 		16:07 17.12.2015
 */
function recalculate_table_price_Itogo(){
	console.log('func : recalculate_table_price_Itogo');
	// получаем id активного блока
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	// получаем тираж
	var tirage = Number($(' .tirage_var').val());
	// получаем запас
	var zapas = Number($(' .dop_tirage_var').val());
	// общий тираж
	var general_tirage = tirage + zapas;


	// объявляем переменные
	var price_in = 0;			// штука вход.
	var price_out = 0; 			// штука исх.
	var price_out_tir_in = 0;	// тираж вход.
	var price_out_tir_out = 0;	// тираж исх.
	var pribl = 0;				// маржа

	
	// ОБСЧИТЫВАЕМ ДАННЫЕ В СТРОКАХ .calkulate_table:visible

	var i = 1;
	//    price in
		price_in += Number($('.calkulate_table:visible tr.tirage_and_price_for_one td.row_tirage_in_one.price_in input').val());
		$('.calkulate_table:visible .row_tirage_in_gen.uslugi_class.price_in span').each(function(index, el){
			price_in += Number($(this).html());	
		});
		$('.calkulate_table:visible .row_tirage_in_gen.uslugi_class.price_in input').each(function(index, el){
			price_in += Number($(this).val());	
		});

	//    price out
		price_out += Number($('.calkulate_table:visible td.row_price_out_one.price_out input').val());
		$('.calkulate_table:visible .row_price_out_gen.uslugi_class.price_out_men span').each(function(index, el){
			price_out += Number($(this).html());	
		});
		$('.calkulate_table:visible .row_price_out_gen.uslugi_class.price_out_men input').each(function(index, el){
			price_out += Number($(this).val());	
		});



	//    pribl (маржа)
		pribl += Number($('.calkulate_table:visible .row_pribl_out_one.pribl span').html());
		$('.calkulate_table:visible .row_pribl_out_gen.uslugi_class.pribl span').each(function(index, el){
			pribl += Number($(this).html());	
			// console.log(Number($(this).html()));
		});

	//	var price_out_tir_out = 0;
		price_out_tir_out += Number($('.calkulate_table:visible .tirage_and_price_for_one .price_out_summ span.for_out').html());
		$('.calkulate_table:visible .calculate.calculate_usl .price_out_summ span.for_out').each(function(index, el){
			price_out_tir_out += Number($(this).html());	
			// console.log(Number($(this).html()));
		});
		
		// echo_message_js(price_out_tir_out, 'system_message', 10000);


	//	var price_out_tir_in = 0;
		
		price_out_tir_in += Number($('.calkulate_table:visible .price_out_summ span.for_in').html());
		$('.calkulate_table:visible .calculate.calculate_usl .price_out_summ span.for_in').each(function(index, el){
			price_out_tir_in += Number($(this).html());	
		});
		// echo_message_js(price_out_tir_in, 'system_message', 10000);



	
	// заполняем ИТОГО
	$('.calkulate_table:visible .variant_calc_itogo td:nth-of-type(6) span.for_in').html(money_format(price_out_tir_in));
	$('.calkulate_table:visible .variant_calc_itogo td:nth-of-type(6) span.for_out').html(money_format(price_out_tir_out));
	
	$('.calkulate_table:visible .variant_calc_itogo td:nth-of-type(3) span').html(money_format(price_in));
	// исходящая цена
	$('.calkulate_table:visible .variant_calc_itogo td:nth-of-type(5) span').html(money_format(price_out));
	// прибль
	$('.calkulate_table:visible .variant_calc_itogo td:nth-of-type(7) span').html(money_format(pribl));
}

// приведение к денежнлму формату
function money_format (num) {

	var new_num = Math.ceil((num)*100)/100;
    return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
    // return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}

// РАСЧЕТ ИТОГО
function calkulate_row_itogo(){
	// получаем id активного блока
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	
	// ОБХОДИМ ВСЕ СТОЛБЦЫ, ПЕРЕСЧИТЫВАЕМ ИТОГО
	var price_in = 0;
	var percent = 0;
	var per = 0;// для временного хранения
	var price_out = 0;
	var profit = 0;


	// цена входящая за тираж или услугу помноженную на тираж
	$(id_active_variant+' .calkulate_table .row_tirage_in_gen span').each(function(index, el) {
		price_in += Number($(this).html());
	});
	$(id_active_variant+' .variant_calc_itogo td:nth-of-type(2) span').html(Math.ceil((price_in)*100)/100);

	// проценты
	var i = 0;
	$(id_active_variant+' .calkulate_table .percent_nacenki span').each(function(index, el) {
		per = Number($(this).html());
		if(per!=0){
			percent += per;
			i++;
		}
	});
	
	$(id_active_variant+' .variant_calc_itogo td:nth-of-type(3) span').html(Math.ceil((percent/i)*100)/100)
	console.log((percent/i));




	// ИТОГО исходящая цена
	$(id_active_variant+' .calkulate_table .row_price_out_gen span').each(function(index, el) {
		price_out += Number($(this).html());
	});
	$(id_active_variant+' .variant_calc_itogo td:nth-of-type(4) span').html(Math.ceil((price_out)*100)/100);
	console.log(price_out);

	// ИТОГО профит
	$(id_active_variant+' .calkulate_table .row_pribl_out_gen span').each(function(index, el) {
		profit += Number($(this).html());
	});
	$(id_active_variant+' .variant_calc_itogo td:nth-of-type(5) span').html(Math.ceil((profit)*100)/100);
	console.log(profit);

} 


// обсчет и трансляция общего тиража и запаса в размерную таблицу
function chenge_the_general_input(){
	// получаем id активного блока
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	console.log(id_active_variant);
	// считаем тираж до изменения	
	var old_tirage = 0;
	$(id_active_variant +' .val_tirage').each(function(index, el) {
		old_tirage += Number($(this).val());
	});
	
	// получаем общий старый запас
	var old_zapas = 0;
	$(id_active_variant +' .val_tirage_dop').each(function(index, el) {
		old_zapas += Number($(this).val());
	});
	
	// получаем максимальный остаток
	var max_ostatok = 0;
	$(id_active_variant +' .ostatok_free').each(function(index, el) {
		max_ostatok += Number($(this).html());
	});

	// определяем значение общего поля тиража
	var general_tirage = Number($(id_active_variant+ ' .tirage_var').val());
	// определяем значение общего поля запаса
	var general_zapas = Number($(id_active_variant+ ' .dop_tirage_var').val());
	
	// определяем ограничиваем ли тираж и запас по остаткам
	var reserv =($(id_active_variant+' .size_card .sevrice_button_size_table span[name="reserve"]').hasClass('checked'))?1:0;
	// reserv = 1;

	//определяем что именно было изменено и в какую сторону +/-
	var raznost = 0;
	if(general_tirage != old_tirage){
		/*******************************/
		/*           TIRAGE            */
		/*******************************/		
		if(reserv){// ограничения по резерву
			// уменьшаем до максимально допустимого значения
			if(max_ostatok<(general_tirage+general_zapas)){
				general_tirage = max_ostatok - general_zapas;
				$(id_active_variant+ ' .tirage_var').val(general_tirage);
			}	
			// изменения коснулись тиража, кнопка резерв активирована
			if(general_tirage > old_tirage){//тираж увеличен
				raznost = general_tirage - old_tirage;
				/*******************************/
				/*             CALC            */
				/*******************************/
				var service_val = 0;

				while(raznost>0){
					var obj = get_info_for_ost(); //arr = [max, tir_out, ostatok, prigon_out, ind];
					// размер не занятых единиц тиража в строке размера
					var sv = obj[0] - obj[1] - obj[3]; 
					// индекс строки размерной сетки
					var ind = obj[4], 

					// обсчитываем тираж в размерной сетке по новой			
					old_tirage = 0;	
					$(id_active_variant + ' .val_tirage').each(function(index, el) {
						old_tirage += Number($(this).val());
					});
					// считаем разностть
					raznost = general_tirage - old_tirage;
					// выходим из цикла , если разность равна 0
					if(raznost==0) break;
					
					// запоминаем значение тиража в размерной сетке данного размера
					var object = $(id_active_variant+' .size_card table tr:nth-of-type('+(ind+1)+') td .val_tirage');
					service_val = Number(object.val());

					if(sv>=raznost){// если свободные единицы тиража больше или равны разности		
						object.val(service_val+raznost);
						console.log(obj[4] + ' - '+object.val());
					}else{				
						object.val(service_val+sv);
						console.log(obj[4] + ' - '+object.val());
					}
				}
				
				/*******************************/
				/*         END CALC            */
				/*******************************/

			}else{// тираж уменьшен
				raznost = old_tirage - general_tirage;				
				if(raznost==0){return false;}
				/*******************************/
				/*             CALC            */
				/*******************************/
				/*
				raznost - это разность между новым тиражом и тиражом из размерной сетки
				- то есть это число на которое нам необходимо понизить наш тираж
				*/ 
				
				// заведём переменные тиража в строке 
				var size_tir = 0;
				// переберём в цикле все размеры
				$(id_active_variant +' .val_tirage').each(function(index, el) {
					// запоминаем тираж в данном размере
					size_tir = Number($(this).val());
					// если тираж по данному размеру <1
					if(size_tir<1){return true;}
					// вычитаем из него наше число
					if(size_tir >= raznost){
						$(this).val(size_tir-raznost);
						raznost = 0;
					}else{
						raznost = (raznost - size_tir);
						$(this).val(0);
					}
					return ( raznost !== 0 );
				});
			}				
		}else{// кнопка резерв не активна
			export_gen_input_in_size_tbl();
		}
		
	}else{
		/*******************************/
		/*            ZAPAS            */
		/*******************************/
		if(reserv){// ограничения по резерву
			// уменьшаем до максимально допустимого значения
			if(max_ostatok<(general_tirage+general_zapas)){
				general_zapas = max_ostatok - general_tirage;
				$(id_active_variant+ ' .dop_tirage_var').val(general_zapas);
			}	
			// изменения коснулись запаса, кнопка резерв активирована
			if(general_zapas > old_zapas){//запас увеличен
				raznost = general_zapas - old_zapas;
				/*******************************/
				/*             CALC            */
				/*******************************/
				var service_val = 0;

				while(raznost>0){
					var obj = get_info_for_ost(); //arr = [max, tir_out, ostatok, prigon_out, ind];
					// размер не занятых единиц тиража в строке размера
					var sv = obj[0] - obj[1] - obj[3]; 
					// индекс строки размерной сетки
					var ind = obj[4], 

					// обсчитываем запас в размерной сетке по новой			
					old_zapas = 0;	
					$(id_active_variant + ' .val_tirage_dop').each(function(index, el) {
						old_zapas += Number($(this).val());
					});
					// считаем разностть
					raznost = general_zapas - old_zapas;
					// выходим из цикла , если разность равна 0
					if(raznost==0) break;
					
					// запоминаем значение запаса в размерной сетке данного размера
					var object = $(id_active_variant+' .size_card table tr:nth-of-type('+(ind+1)+') td .val_tirage_dop');
					service_val = Number(object.val());

					if(sv>=raznost){// если свободные единицы тиража больше или равны разности		
						object.val(service_val+raznost);
						console.log(obj[4] + ' - '+object.val());
					}else{				
						object.val(service_val+sv);
						console.log(obj[4] + ' - '+object.val());
					}
				}
				
				/*******************************/
				/*         END CALC            */
				/*******************************/

			}else{// запас уменьшен
				raznost = old_zapas - general_zapas;
				if(raznost==0){return false;}	
				/*******************************/
				/*             CALC            */
				/*******************************/
				/*
				raznost - это разность между новым запасом и запасом из размерной сетки
				- то есть это число на которое нам необходимо понизить наш запасом
				*/ 
				
				// заведём переменные запаса в строке 
				var size_zap = 0;
				// переберём в цикле все размеры
				$(id_active_variant +' .val_tirage_dop').each(function(index, el) {
					// запоминаем тираж в данном размере
					size_zap = Number($(this).val());
					// если запасом по данному размеру <1 переходим к следующей интерации
					if(size_zap<1){return true;}
					console.log(size_zap);
					// вычитаем из него наше число
					if(size_zap >= raznost){
						$(this).val(size_zap-raznost);
						raznost = 0;
					}else{
						raznost = (raznost - size_zap);
						$(this).val(0);
					}
					return ( raznost !== 0 );
				});
			}				
		}else{// кнопка резерв не активна
			export_gen_input_in_size_tbl();
		}

	}

	// вносим изменения тиража в таблицу
	// console.log(general_tirage);
	recalkulate_table(general_tirage);
	
	
	// пересчёт таблицы с ценами
	//calkulate_table_calc();
}

// вносим изменения тиража в таблицу
function recalkulate_table(general_tirage){
	// вносим изменения тиража в активную таблицу расчета
	var recalc_itogo = 0;
	$('.calkulate_table:visible tr').each(function(index, el) {
		if($(this).hasClass('tirage_and_price_for_one') || $(this).hasClass('calculate_usl')){
			if ($(this).find('td').length > 1 && $(this).find('td:nth-of-type(2) div').length == 0) {
				$(this).find('td:nth-of-type(2)').html(general_tirage);
				recalc_itogo = 1;
				// console.log($(this).find('td:nth-of-type(2)').html());
			}
		}
	});
	// если были внесены изменения
	if (recalc_itogo) {
		recalculate_services_and_prints(general_tirage);
		recalkulate_tovar();
		recalculate_table_price_Itogo();
	}
}
/**
 *	пересчет услуг и калькуляторов
 *  вызывает метод из rtClass, который запрашивает пересчет калькуляторов и услуг
 *  а так же меняет тираж в услуга
 *
 *	@param 		tirage - тираж 
 *	@author  	Алексей Капитонов
 *	@version 	17:21 18.12.2015
 */
function recalculate_services_and_prints(tirage){
	var calcExists = ($('.calkulate_table:visible .calculate.calculate_usl').length > 0)?true:false;
	var extraExists = ($('.calkulate_table:visible .calculate.calculate_usl.calculator_row').length > 0)?true:false;
	var dop_data_id = $('.variant_name.checked').attr('data-id');
	
	
	rtCalculator.makeQuantityCalculations('card',tirage,dop_data_id,calcExists,extraExists);

}

// пересчет каждой услуги относительно тиража
function recalculate_services(){
	// перебираем строки услуг и калькуляторов
	var prise_in = 0;
	var prise_out = 0;
	var quantity = 0;
	var price_in_obj = new Object();

	$('.calkulate_table:visible .calculate.calculate_usl').each(function(index, el) {
		// получаем тираж
		if($(this).find('td:nth-of-type(2) div').length > 0){
			quantity = Number($(this).find('td:nth-of-type(2) div').html());
		}else{
			quantity = Number($(this).find('td:nth-of-type(2)').html());
		}

		// получаем входящую цену
		price_in_obj = $(this).find('.row_tirage_in_gen.uslugi_class.price_in');
		if (price_in_obj.find('span').length > 0) {
			prise_in = Number(price_in_obj.find('span').html())
		}else{
			prise_in = Number(price_in_obj.find('input').val())
		}

		// получаем исходящую цену
		prise_out = $(this).find('.row_price_out_gen.uslugi_class.price_out_men input').val();
		
		$(this).find('.price_out_summ .for_out').html(money_format(prise_out*quantity));
		$(this).find('.price_out_summ .for_in').html(money_format(prise_in*quantity));
		$(this).find('.row_pribl_out_gen.uslugi_class.pribl span').html(money_format(prise_out*quantity - prise_in*quantity));

	});
	// recalculate_table_price_Itogo();
}

/**
 *	обработчик ответа на запрос на изменение тиража в РТ
 *  изменяет данные в расчётной таблице
 *  
 *	@author  Алексей Капитонов
 *	@version 18.12.2015
 */
function response_rtCalculator_makeQuantityCalculations(cell,row_id,response_obj){
	console.log(cell,row_id,response_obj);
	
	// при отрицательном отклике - выкидываем оповещение
	if(response_obj['extra']['result'] != 'ok' || response_obj['print']['result'] != 'ok'){
		echo_message_js('УПС... ;(','system_message',2500);
		var message = "Прикреплённые услуги не были пересчитаны";
  		echo_message_js(message,'error_message',2500);

		var message = "Страница будет перезагружена через 7 секунд.";
  		echo_message_js(message,'error_message',2500);
  		setTimeout(function(){window_reload();}, 7000);
  		return false;
	}
	if(response_obj['extra']['result'] == 'ok' && response_obj['print']['result'] == 'ok'){
		// сохраняем размерную таблицу
		save_all_table_size();
		// console.log(response_obj['new_sums_details']);
		// перебираем изменённые строки
		var id = $('.variant_name.checked').attr('data-id');

		for (i in response_obj['new_sums_details']) {
	  		// console.log(i+":");
			for (service_id in response_obj['new_sums_details'][i]) {
				// console.log(response_obj['new_sums_details'][i]['price_in']);
				// console.log(service_id);
				// console.log(id);

				//console.log("* * * "+service_id+":"+response_obj['new_sums_details'][i][service_id]);
				if($('#editable_from_rtClass_' + id + '_' + i + ' .row_tirage_in_gen.uslugi_class.price_in span').length == 1){
					$('#editable_from_rtClass_' + id + '_' + i + ' .row_tirage_in_gen.uslugi_class.price_in span').html(response_obj['new_sums_details'][i]['price_in']);	
				}else{
					$('#editable_from_rtClass_' + id + '_' + i + ' .row_tirage_in_gen.uslugi_class.price_in input').val(response_obj['new_sums_details'][i]['price_in']);
				}
				$('#editable_from_rtClass_' + id + '_' + i + ' .row_price_out_gen.uslugi_class.price_out_men input').val(response_obj['new_sums_details'][i]['price_out']);
			}
		}
		// пересчёт услуг в расчетной таблице 
		recalculate_services();
		recalculate_table_price_Itogo();		
		window.location.href = window.location.href;
	}	
}
function response_rtCalculator_makeQuantityCalculationsCancel(){
	window_reload();
}

/**
 *	пересчитывает стоимость товара относительно изменённого тиража
 *	изменяет данные в расчётной таблице
 *
 *	@author  Алексей Капитонов
 *	@version 17:19 18.12.2015
 */
function recalkulate_tovar(){
	var position_obj = $('.calkulate_table:visible tr.tirage_and_price_for_one');
	// изменяем значения относительно тиража
	// ПОЛУЧАЕМ ЗНАЧЕНИЯ
	// тираж
	var quantity = Number(position_obj.find('td:nth-of-type(2)').html());
	// входящая (штука)
	var price_in = Number(position_obj.find('td:nth-of-type(3) input').val());
	// входящая (штука)
	var discount = Number(position_obj.find('td:nth-of-type(4)').attr('data-val'));
	// исходящая (штука)
	var price_out = Number(position_obj.find('td:nth-of-type(5) input').val());
	
	// МЕНЯЕМ
	// входящая (сумма)
	position_obj.find('td:nth-of-type(6) .for_in').html(money_format(price_in * quantity));
	// исходящая (сумма)
	position_obj.find('td:nth-of-type(6) .for_out').html(money_format(price_out * quantity));
	// маржа (прибыль)
	position_obj.find('td:nth-of-type(7) span').html(money_format(price_out * quantity - price_in * quantity))

	// recalculate_table_price_Itogo();
}


// перенос содержимого общего тиража и запаса в разменую сетку
function export_gen_input_in_size_tbl(){
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	// определяем значение общего поля тиража
	var general_tirage = Number($(id_active_variant+ ' .tirage_var').val());
	// определяем значение общего поля запаса
	var general_zapas = Number($(id_active_variant+ ' .dop_tirage_var').val());
	

	//$(id_active_variant+' .size_card .val_tirage,'+id_active_variant+' .size_card .val_tirage_dop').val(0);
	
	// проверяем размерную сетку на заполнение
	
		// по умолчанию запоминаем первую строку размерной сетки
		var size_card_quantity_obj = $('.size_card:visible .val_tirage:first');
		var size_card_zapas_obj = $('.size_card:visible .val_tirage_dop:first');

		//если заполнена не первая
		$('.size_card:visible .val_tirage').each(function(index, el) {
			if(Number($(this).val()) > 0){
				size_card_quantity_obj = $(this);
				return false;
			}
		});
		$('.size_card:visible .val_tirage_dop').each(function(index, el) {
			if(Number($(this).val()) > 0){
				size_card_zapas_obj = $(this);
				return false;
			}
		});
	// // редактируем значения на странице
	// var size_card_quantity_obj = $('.size_card:visible .val_tirage:first');
	// var size_card_zapas_obj = $('.size_card:visible .val_tirage_dop:first');
	size_card_quantity_obj.val(general_tirage);
	size_card_zapas_obj.val(general_zapas);
}

// колькуляция и сохранение изменённых данных от тираже в таблице размеров
$(document).on('keyup','.val_tirage, .val_tirage_dop', function(){
	event.preventDefault();
	
	object = $(this);

	if ($('#tir_save_btn').length == 0) {
		var btn = $('<span/>',{
				'class':'',
				'html':'save',
				'id':'tir_save_btn',
				click:function(){
					if(object.hasClass('val_tirage_dop')){
						save_all_table_size();						
					}
					update_data_size_tbl(object);
					$(this).remove();
				}
			}).css({
					'background':'#86AE1F',
					'padding':'5px 8px',
					'cursor':'pointer',
					'color':'#fff'
				})
		$('.tirage_option_and_date_print:visible .tirage_buttons').append(btn);
	}
});

// ИЗМЕНЕНИЕ тиража ИЗ ОБЩЕГО input варианта
$(document).on('keyup','#edit_variants_content .tirage_var,#edit_variants_content .dop_tirage_var',function(){
	if ($('#tir_save_btn').length == 0) {
		var btn = $('<span/>',{
				'class':'',
				'html':'save',
				'id':'tir_save_btn',
				click:function(){
					chenge_the_general_input();
					$(this).remove();
				}
			}).css({
					'background':'#86AE1F',
					'padding':'5px 8px',
					'cursor':'pointer',
					'color':'#fff'
				})
		$('.tirage_option_and_date_print:visible .tirage_buttons').append(btn);
	}
});



function update_data_size_tbl(object){
	// console.log($(this).parent('div').html());
	var id = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	// $(id+' .size_card').

	// определяем максимальный тираж по данному размеру
	var max_tirage = Number(object.parent().parent().find('.ostatok_free').html());
	// запоминаем введённые данные
	var save_val = Number(object.val());
	// если введенные данные меньше нуля
	if(save_val<0){object.val(0);}

	if(object.attr('class') == 'val_tirage'){// редактируется тираж для размера
		// если считаем под резерв
		if($(id+' .size_card .btn_var_std[name="reserve"]').hasClass('checked')){
			// определяем введённый запас
			var zapas = Number(object.parent().parent().find('.val_tirage_dop').val());
			// определяем общее количество введенного в поле товара: сумму запаса и тиража
			var general_tirage_this_size = save_val + zapas;
			// определяем максимальный тираж по данному размеру сувенира
			max_tirage_size = max_tirage - zapas;
			// проверяем на привышения тиража по заданному размеру, 
			// если надо правим на максимально возможную цифру
			if(general_tirage_this_size > max_tirage){object.val(max_tirage_size);}
		}
		// определяем адрес общего поля тиража
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .tirage_var';
	}else{// редактируется запас для размера
		// если считаем под резерв
		if($(id+' .size_card .btn_var_std[name="reserve"]').hasClass('checked')){
			// определяем введённый тираж
			var tirage = Number(object.parent().parent().find('.val_tirage').val());
			// определяем общее количество введенного в поле товара: сумму запаса и тиража
			var general_tirage_this_size = save_val + tirage;
			// определяем максимальный тираж по данному размеру сувенира
			max_tirage_size = max_tirage - tirage;
			// проверяем на привышения тиража по заданному размеру, 
			// если надо правим на максимально возможную цифру
			if(general_tirage_this_size > max_tirage){object.val(max_tirage_size);}
		}
		// определяем адрес общего поля запаса
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .dop_tirage_var';	
	}

	/*
		С этого момента мы считаем, что в полях размерной сетки введены валидные данные, 
		которые не привышают доступное количество на складе
	*/
	
	// подсчитываем сумму всех полей редактируемого типа (тираж или запас)
	var summ = 0;
	$('#'+$('.variant_name.checked').attr('data-cont_id')+' .'+object.attr('class')).each(function(index, el) {
		summ += Number($(this).val());
	});
	// выводим сумму в input общего тиража
	$(id).val(summ);

	// вносим изменения тиража в таблицу
	if(object.attr('class') == 'val_tirage'){
		recalkulate_table(summ);	
	}
	


	// пересчёт таблицы цен
	calkulate_table_calc();
}


// переключение сервисных кнопок под резерв и под заказ
$(document).on('click','#edit_variants_content .variant_content_block:visible .size_card .sevrice_button_size_table span',function() {
	$('#edit_variants_content .variant_content_block:visible .size_card .sevrice_button_size_table span').removeClass('checked');
	$(this).addClass('checked');
	if($(this).attr('name')=="order"){
		$('#edit_variants_content .variant_content_block:visible .size_card input').removeAttr('readonly').removeClass('input_disabled');
	}else{
		$('#edit_variants_content .variant_content_block:visible .size_card tr').each(function(index, el) {
			if(Number($(this).find('.ostatok_free').html())==0){
				$(this).find('input').attr('readonly','readonly').addClass('input_disabled').val(0);
			}	
		});
	}
});


$(document).on('click', '#all_variants_menu li span', function(event) {
	// alert(654);
	// event.preventDefault();
	var variant_id = $(this).parent('data-id');
});


// изменеие информации по изготовлению в р/д в текущем варианте
function save_standart_day(){
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
	// $('#'+id_variant+' .timepicker2').hide(); // показать поле время
	// $('#'+id_variant+' .datepicker2, #'+id_variant+' .timepicker2').val('');
	var standart = $('#'+id_variant+' .fddtime_rd2[name="fddtime_rd2"]').val();
	// var time = $input.val()+':00';
	// alert($input.attr('class'));
	$.post('', {
		// global_change: 'AJAX',
		AJAX: 'save_standart_day',
		id: id,
		row_id: row_id,
		standart:standart
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},"json");
}

$(document).on('click','.btn_var_std[name="std"]',function(){	
	$(this).addClass('checked');
	$(this).parent().find('input').val(10);
	// сохраняемся и меняем html
	save_standart_day();
});


$(document).on('keyup','.fddtime_rd2',function(){
	// if($(this).val()!='10'){
	// 	$(this).prev().removeClass('checked');
	// }else{
	// 	if(!$(this).prev().hasClass('checked')){
	// 		$(this).prev().addClass('checked');
	// 	}		
	// }
	// сохраняемся и меняем html
	save_standart_day();
});



// отслеживание нажатий функциональных клавиш с клавиатуры
$(document).keydown(function(e) {
	if(e.keyCode == 27){//ESC	
	}	
	if(e.keyCode == 38){//вверх		
		// alert()
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .fddtime_rd2';
		if($(id).is( ":focus" )){			
			$(id).val(Number($(id).val())+1);
			$(id).setCursorPosition($(id).val().length);
		}		
	}
	if(e.keyCode == 40){//вниз		
		// alert()
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .fddtime_rd2';
		if($(id).is( ":focus" )){			
			$(id).val(Number($(id).val())-1);
			// $(id).setCursorPosition($(id).val().length);
		}	
	}
});



/**
 *	сохраняет данные в таблицу dop_data
 *	обрабатывается в файле libs/php/classes/rt_position_no_catalog_class.php
 * 
 *	@return  json
 *	@author  Алексей Капитонов
 *	@version 11:21 07.10.2015
 */
function save_dop_dop_usluga(obj){	
	// alert('привет, я неизвестная функция из строки 1508');
	console.log(obj);
	var data = {};
	obj.find('.calculate.calculate_usl.editing').each(function(index, el) {
		var dop_usl_id = $(this).attr('data-dop_uslugi_id');
		var price_out_snab = $(this).find('.row_price_out_gen.uslugi_class.price_out_snab span').html();
		var price_out = $(this).find('.row_price_out_gen.uslugi_class.price_out_men span').html();
		var price_in = Number($(this).find('.row_tirage_in_gen.uslugi_class.price_in span').html());
		var quantity = Number($('.variant_content_block:visible .tirage_var').val())+Number($('.variant_content_block:visible .dop_tirage_var').val());
		var for_how = $(this).attr('data-for_how');
		console.log(for_how);
		console.log(quantity);
		console.log(price_in);
		console.log(price_out);
		if(for_how == 'for_one'){
			price_out_snab = round_s(price_out_snab/quantity);
			price_out = round_s(price_out/quantity);
			price_in = round_s(price_in/quantity);			
		}

		// console.log(price_out_snab);
		// console.log(price_out);

		data[$(this).attr('data-dop_uslugi_id')] = {"price_out":price_out,"price_in":price_in,"price_out_snab":price_out_snab};		
		// $(this).removeClass('editing');
	});
	// console.log(data);
	$.post('', { 
			AJAX: 'save_new_price_dop_uslugi',
			data:data
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);

			// снимаем класс сохранения
			obj.find('.calculate.calculate_usl.editing').each(function(index, el) {
				$(this).removeClass('editing');
			});
	},'json');
}

$(document).ready(function() {
	$('#back_to_query').attr('href',$('#claim_number a').attr('href'));
});




