//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- start
//////////////////////////////////
	//стандартный обработчик ответа AJAX
	
	function standard_response_handler(data){
		if(data['response']=='show_new_window'){
			var title = (data['title'] !== undefined)?data['title']:'Отладочное окно';
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';
			var button_name = (data['button_name'] !== undefined)?data['button_name']:'OK';
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title,height,width,button_name);
			window_preload_del();
		}
		
		if(data['response']=='show_new_window_simple'){
			var title = (data['title'] !== undefined)?data['title']:'Отладочное окно';
			var height = (data['height'] !== undefined)?data['height']:'auto';
			var width = (data['width'] !== undefined)?data['width']:'auto';	
			show_simple_dialog_window(Base64.decode(data['html']),title,height,width);
			window_preload_del();
		}


		// поочерёдный вызов функции 
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
			    	var obj = $(this);
			    	// $('#general_form_for_create_product .pad:hidden').remove();
				    $.post('', serialize, function(data, textStatus, xhr) {
				    	if(data['response'] != 'false'){
				    		obj.dialog( "destroy" ).remove();
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
		function echo_message_NEW(data){
			var time = (data['time'] !== undefined)?data['time']:'7000';
			// var data.message_type = (data['message_type'] !== undefined)?data['message_type']:'successful_message';
			
			var message_type = 'success';
			if(data['message_type'] !== undefined){
				switch(data.message_type) {
					case 'successful_message':
						message_type = 'success';
						break;
					case 'system_message':
						message_type = 'warn';
						break;
					case 'error_message':
						message_type = 'error';
						break;
					default:
						message_type = 'info';
						break;
				}
			}
			$.notify(Base64.decode(data.message),message_type)


			// $("<li/>", {
			//       "class": data.message_type,
			//       "css":{"opacity":1,"top":0},
			//       click: function(){
			//           $(this).animate({opacity:0},'fast',function(){$(this).remove()});
			//       }
			// }).append(Base64.decode(data.message)).appendTo("#apl-notification_center").fadeIn('slow', 
		 //        function(){
		 //            var el = jQuery(this);
		 //            setTimeout(function(){
		 //                el.fadeOut('slow',
		 //                    function(){
		 //                        jQuery(this).remove();
		 //                    });
		 //            }, time);
		 //    });
		}	

		function echo_message(data){
			var time = (data['time'] !== undefined)?data['time']:'7000';
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
		            }, time);
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
			if( data.timeout === undefined ){
				location.reload();
			}else{
				setTimeout(function(){
					location.reload();
				}, data.timeout)

			}
		}
		// переадресация из php
		function location_href(data){
			if( data.timeout === undefined ){
				window.location.href = data.href;
			}else{
				setTimeout(function(){
					window.location.href = data.href;
				}, data.timeout)

			}
			
		}

		$(document).keydown(function(e) {	
			if(e.keyCode == 27){//ESC		
				window_preload_del();
			}	
		});

	function echo_message_js_NEW(text, message_type, timer){
		timer = timer || 7000;
		message_type = message_type || 'system_message';
		// if(data['message_type'] !== undefined){
			switch(message_type) {
				case 'successful_message':
					message_type = 'success';
					break;
				case 'system_message':
					message_type = 'warn';
					break;
				case 'error_message':
					message_type = 'error';
					break;
				default:
					message_type = 'info';
					break;
			}
		// }

		$.notify(text,{
			className: 		message_type,
  			clickToHide: 	timer,
		})
	}
	
	function echo_message_js(text, message_type, timer){
		message_type = message_type || 'system_message';
		timer = timer || 7000;
		$("<li/>", {
			"class": message_type,
			"css":{"opacity":1,"top":0},
			click: function(){
			    $(this).animate({opacity:0},'fast',function(){$(this).remove()});
			}
		}).append(text).appendTo("#apl-notification_center").fadeIn('slow', 
		    function(){
		    var el = jQuery(this);
		        setTimeout(function(){
		        el.fadeOut('slow',
			        function(){
		    	        jQuery(this).remove();
		        	});
		    }, timer);
		});
	}
//////////////////////////////////
//	СТАНДАРТНЫЕ ФУНКЦИИ  -- end
//////////////////////////////////