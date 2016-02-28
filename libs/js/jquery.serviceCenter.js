/**
 *	Service center scripts	
 *
 *	@author  	Alexey Kapitonov
 *	@version 	12:23 12.02.2016
 */
jQuery(document).ready(function($) {
	// $.SC_createShowWindowButton();
	//$('#js-win-sv').click();
});
jQuery(document).on('click', '#rt_tbl_body tr td.calc_btn span:first-child', function(event) {
	event.preventDefault();
	$.SC_sendAjax(event);
});

/**
 *	jquery plugin Total Commanders
 *
 *	@author  	Alexey Kapitonov	
 */
(function( $ ){

	var methods = {
		variants_rows : {}, 	// строки вариантов
		top_menu : {}, 			// меню тиражей
		checkbox_main : {}, 	// чек управления группой
		checkbox_var : {}, 		// чекбоксы вариантов расчёта
  	

		init : function( options ) {

			return this.each(function(){
				var $this = $(this);
				$this.addClass('totalCommander');

				// выделяем рабочие области
				methods.variants_tbody = 	$this.find('#js-main-service_center-variants-table tbody');
				methods.variants_rows = 	$this.find('#js-main-service_center-variants-table tbody tr');
				methods.top_menu = 			$this.find('#js-main-service_center-top_menu ul li');
				methods.checkbox_main = 	$this.find('#js-main-service_center-variants-table thead tr th:nth-of-type(2) div.js-psevdo_checkbox');
				methods.services_tbl =		$this.find('#js-main-service_center-variants-services-div-table table');
				methods.services_rows = 	$this.find('#js-main-service_center-variants-services-div-table table tr.service');

				// events chose variants rows
				methods.checkbox_main.bind("click.totalCommander", methods.checkbox_main_click );
				methods.variants_rows.find('.js-psevdo_checkbox').bind("click.totalCommander", methods.checkbox_change );
				methods.variants_rows.bind("click.totalCommander", methods.variants_rows_choose );
				methods.variants_rows.bind("dblclick.totalCommander", function(){
					event.preventDefault();
					$(this).find('td:nth-of-type(2) div.js-psevdo_checkbox').click();
				});


				// events services 
				methods.services_rows.find('.alarm_clock').bind("click.totalCommander", function(){
					$(this).toggleClass('checked');
				});

				// кнопка сбросить всё
				$('#sc_cancel_all').bind('click.totalCommander', methods.cancel_all_choosen_variants );


				$this.show();
			});

		},
		cancel_all_choosen_variants:function(){
			methods.variants_rows
				.removeClass('tr_checked')
				.find('td:nth-of-type(2).checked')
				.removeClass('checked');

			methods.variants_tbody.find('#default_var').addClass('tr_checked')

			methods.checkbox_main.parent().removeClass('checked-before').removeClass('checked');
			// обновление блока услуг
			methods.update_services_content();
		},
		// клик по строке 
		variants_rows_choose:function(e){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length == 0){
				var object= this;
				if(object.className == 'tr_checked'){
					// object.className = '';
				}else{
					methods.variants_rows.removeClass('tr_checked');
					object.className = 'tr_checked';
				}	
			}
			// обновление блока услуг
			methods.update_services_content();
			
		},
		// клик по чекбоксу в строке
		checkbox_change: function(e){
			var object= this.parentNode.parentNode;
			if(object.className == 'tr_checked' && this.parentNode.className == 'checked'){
				object.className = '';
				this.parentNode.className = '';
			}else{
				object.className = 'tr_checked';
				this.parentNode.className = 'checked';
			}
			methods.variants_rows.each(function(index, el) {
				if ($(this).hasClass('tr_checked')) {
					if(!$(this).find('td:nth-of-type(2)').hasClass('checked')){
						$(this).removeClass('tr_checked');
					}
				}
			});
			console.log(object)
			methods.checkbox_main_check();
		},
		// проверка и правка главного чекбокса
		checkbox_main_check: function(){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length == methods.variants_rows.length){
				methods.checkbox_main.parent().attr('class','checked');
			}else if(methods.variants_rows.find('td:nth-of-type(2).checked').length > 0){
				methods.checkbox_main.parent().attr('class','checked-before');
			}else{
				methods.checkbox_main.parent().removeAttr('class');
			}
			// обновление блока услуг
			methods.update_services_content();
		},
		// клик по главному checkbox
		checkbox_main_click : function(e){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length){
				// сбросить все выбранные checkbox
				methods.cancel_all_choosen_variants();
			}else{
				methods.variants_rows.addClass('tr_checked').find('td:nth-of-type(2)').addClass('checked');
				methods.checkbox_main.parent().addClass('checked');
			}
			// обновление блока услуг
			methods.update_services_content();
		},


		show : function( ) {
    		this.dialog('open');
    		// подгоняем ширину столбика тираж
    		$('#js-main-service_center-variants-table th:last-child').width($('#js-main-service_center-variants-services-div-table').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(1)').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(2)').innerWidth());
		},
		hide : function( ) {
    		this.dialog('close');
		},
		update_services_content : function( content ) {
    		// !!!
		}
	};

	$.fn.totalCommander = function( method ) {
    
		// логика вызова метода
		if ( methods[method] ) {
	        // если запрашиваемый метод существует, мы его вызываем
	        // все параметры, кроме имени метода прийдут в метод
	        // this так же перекочует в метод
	        return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));

	    } else if ( typeof method === 'object' || ! method ) {
	        // если первым параметром идет объект, либо совсем пусто
	        // выполняем метод init
	        return methods.init.apply( this, arguments );
	    } else {
	        // если ничего не получилось
		  echo_message_js( 'Метод с именем ' +  method + ' не существует для jQuery.tooltip' );
		} 
	};

})( jQuery );

/**
 *	модуль окна Тотал
 *
 *	@param 		
 *	@return  	
 *	@see 		
 *	@author  	Alexey Kapitonov
 *	@version 	
 */
$.extend({
	// создание кнопки открытия окна Тотал 
	SC_createShowWindowButton : function(event){
		if($('#js-win-sv').length) return true;
		var obj = $('<div/>',{
			"id" : "js-win-sv"	
		}).click(function(event) {
			$.SC_sendAjax();
		});

		$('body').append( obj );
	},
	// запрос на вызов окна
	SC_sendAjax:function(){
		event.preventDefault();
		$.post('', {
			AJAX: 'get_service_center'
		}, function(data, textStatus, xhr) {
			if(data['myFunc'] !== undefined && data['myFunc'] == 'show_SC'){
				$.SC_createWindow(Base64.decode(data['html']));	
			}				
			standard_response_handler(data);
		},'json');
	},
	// открытие окна Тотал
	SC_createWindow : function(html){
		// если окно вызывается впервые
		if($('#js-main_service_center').length == 0){
			$('body').append($('<div/>',{
				"id":'js-main_service_center'
			}).html(html));
			
			var title = 'Центр услуг';			
			
			$('#js-main_service_center').dialog({
			        width: $('body').innerWidth(),
			        height: $(window).height()-100,
			        modal: true,
			        title : title,
			        autoOpen : false,
			        beforeClose: function( event, ui ) {
			        	// перезагрузка RT
			        	$.SC_reload_RT_content();
			        },
			        closeOnEscape: false
			        // buttons: buttons          
			    }).parent().css({'top':'0px'});
			

			// ресайз блока вариантов
			$( "#js-main-service_center-variants-div-table" ).resizable({
				stop:function(event, ui){
					var width = $('#js-main-service_center-variants-div-table').width();
					if(ui.size.height > $('#js-main-service_center-variants-table').innerHeight()){
						$('#js-main-service_center-variants-div-table').css({'overflowY':'hidden','overflowX':'hidden'});
						$('#js-main-service_center-variants-table').width(width);
					}else{
						$('#js-main-service_center-variants-div-table').css({'overflowY':'scroll','overflowX':'hidden'});
						$('#js-main-service_center-variants-table').width(width-16);
					}
				}
			});
			// добавляем блок кнопок
			$.SC_createButton();
			// выравнивем колонку тираж в верхней таблице по нижней
			
			// console.log($.div_variants.removeAttr('id'))
			// инициализируем плагин
			$('#js-main_service_center').totalCommander();
		}else{
			// если окно уже вызывалось - обновляем контент и открываем
			$('#js-main_service_center').totalCommander('show');
			// $('#js-main_service_center').totalCommander();
		}

		
		
	},
	// перезагрузка RT
	SC_reload_RT_content : function(){
		// установка прелоад
		window_preload_add();
		// обновление таблицы РТ
		$('#scrolled_part_container').load(' #rt_tbl_body',function(){
			// запускаем РТ по новой
			// printCalculator;
			rtCalculator.init_tbl('rt_tbl_head','rt_tbl_body');
			// убираем прелоад
			window_preload_del();
		});
	},
	// блок кнопок окна Тотал
	SC_createButton: function(){
		var buttons = new Array();
		buttons.push({
		    text: 'Сбросить все',
		    id:  'sc_cancel_all'
		});
		buttons.push({
		    text: 'Добавить услугу',
		    id: 	'sc_add_service',
		    click: function() {
		    	$('#js-main_service_center').dialog('close');			    	
		    }
		});
		buttons.push({
		    text: 'Закрыть',
		    id: 	'sc_close_window',
		    click: function() {
		    	$('#js-main_service_center').dialog('close');		    		    	
		    }
		});


		var buttons_html = $('<table></table>');

		var button;
		// console.log(buttons)
		for(var i = 0, length1 = buttons.length; i < length1; i++){
			button = $('<button/>',{
					text: buttons[i]['text'],
					click: buttons[i]['click']
				});
			if(buttons[i]['class'] !== undefined){
				button.attr('class',buttons[i]['class'])
			}
			if(buttons[i]['id'] !== undefined){
				button.attr('id',buttons[i]['id'])
			}
			buttons_html.append(
				$('<td/>')
				.append(button)
			);				
		}
		$('#js-main_service_center').after($('<div/>',{'id':'js-SC_buttons','class':'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'}).append( buttons_html));		
	}

});


// закрытие окна на esc
$(document).keyup(function (e) {
    if (e.keyCode == 27) {
    	$('#js-main_service_center').dialog('close')  
    }
});

$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.service  td.comment', function(event) {
	echo_message_js('Вызов окна комментариев');
});

$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.service  td:last-child', function(event) {
	echo_message_js('Удалить услугу');
});

$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.itogo  td:last-child', function(event) {
	echo_message_js('Удалить все прикреплённые услуги');
});


