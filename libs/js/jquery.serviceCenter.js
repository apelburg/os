/**
 *	Service center scripts	
 *
 *	@author  	Alexey Kapitonov
 *	@version 	12:23 12.02.2016
 */
jQuery(document).ready(function($) {
	$.SC_createShowWindowButton();
});

$(document).on('click', '.pos_plank.cat a', function(event) {
	event.preventDefault();
	$.post('', {
		AJAX: 'get_service_center'
	}, function(data, textStatus, xhr) {
		if(data['myFunc'] !== undefined && data['myFunc'] == 'show_SC'){
			show_SC(data,buttons);	
		}				
		standard_response_handler(data);
	},'json');
});

$(document).on('dbclick', '.pos_plank.cat a', function(event) {
	event.preventDefault();
	window.location.href = $(this).attr('href');
});




function show_SC(data,buttons){ // необходимо передать кнопки
	var html = (data['html'] !== undefined)?Base64.decode(data['html']):'нет информации';
	var title = (data['title'] !== undefined)?data['title']:'Название окна';
	var height = (data['height'] !== undefined)?data['height']:'auto';
	if(height == '100%'){
		height = $(window).height()-2;
	}
	var width = (data['width'] !== undefined)?data['width']:'auto';	
	if(width == '100%'){
		width = $(window).width()- 2;
	}
	
		

	$('body').append('<div id="SC_window"></div>');
	$('#SC_window').html(html);
	$('#SC_window').dialog({
	    width: width,
	    height: height,
	    modal: true,
	    title : title,
	    autoOpen : true,
	    buttons: buttons          
	});
}

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
			        width: '1000px',
			        height: $(window).height()-100,
			        modal: true,
			        title : title,
			        autoOpen : true,
			        // buttons: buttons          
			    }).parent().css({'top':'0px'});

			$('#js-main_service_center').after('<div id="js-SC_buttons" class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"></div>')
			$('#js-SC_buttons').append( $.SC_createButton() )
		}else{
			// если окно уже вызывалось
			$('#js-main_service_center').html(html);
			$('#js-main_service_center').dialog('open')
		}
		
	},
	// кнопки окна Тотал
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
		return buttons_html;
	}
});



