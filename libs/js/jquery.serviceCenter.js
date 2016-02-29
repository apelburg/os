/**
 *	Service center scripts	
 *
 *	@author  	Alexey Kapitonov
 *	@version 	12:23 12.02.2016
 */
jQuery(document).ready(function($) {
	$.SC_createShowWindowButton();
	//$('#js-win-sv').click();
});




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
	SC_createShowWindowButton : function(){
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
			        autoOpen : true,
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
			$('#js-main-service_center-variants-table th:last-child').width($('#js-main-service_center-variants-services-div-table').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(1)').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(2)').innerWidth())
			
		}else{
			// если окно уже вызывалось - обновляем контент и открываем
			$('#js-main_service_center').html(html);
			$('#js-main_service_center').dialog('open')
		}
		
	},
	// перезагрузка RT
	SC_reload_RT_content : function(){
		// установка прелоад
		window_preload_add();
		$('#rt_tbl_body').load(' #rt_tbl_body',function(){
			// запускаем РТ по новой
			
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
		    id:  'sc_cancel_all',
		    click: function() {
		    	$('#js-main_service_center').dialog('close');			    	
		    }
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

// подкрашиваем выбранные строки в блоке вариантов
$(document).on('click', '#js-main-service_center-variants-table tr td', function(event) {
	$(this).parent().toggleClass('tr_checked');
});
// закрытие окна на esc
$(document).keyup(function (e) {
    if (e.keyCode == 27) {
    	$('#js-main_service_center').dialog('close')  
    }
});
// включение будильника
$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.service  td.alarm_clock', function(event) {
	$(this).toggleClass('checked');
});


