// JQuery
/*START окно запроса доставки*/
function edit_addres(i){
	$(i).parent().find('.edit_address').width($(i).innerWidth());
	$(i).parent().find('.edit_address').toggle( "fast", function() {
// Animation complete.
});
	}
/*START выделение и перенос информации с группы чекбокс */
/*выделение / сброс всего*/
function check_all_for_request(td,page,sorti){
	
	if(td.checked){$('#form_for_driver_body').html('');
		$('.sample_num_rows_check input[type="checkbox"]').each(function(index, element) {
            if($(this).attr('disabled')!='disabled'){
				$(this).prop('checked', true);
			}
        });	
		if(page=='received'){//из за появившихся маркеров, на странице "Полученные" корректируем местополежение инпутов в DOM модели 
			$('.sample_content_tables table tbody tr td:nth-of-type(3) input[type="checkbox"]').each(function() {
				//alert($(this).attr('name'));          
				greate_row_driver_list($(this),page,sorti);
			});
		}else{
			$('.sample_content_tables table tbody tr td:nth-of-type(2) input[type="checkbox"]').each(function() {
				//alert($(this).attr('name'));          
				greate_row_driver_list($(this),page,sorti);
			});
		}
	}else{
		$('.sample_num_rows_check input[type="checkbox"]').prop('checked', '');
		$('#form_for_driver_body').html('');
	}
}
/*выделение / сброс группы*/
function check_on_for_request(td,page,sorti){
	var id = td.parentNode.parentNode.parentNode.parentNode.id;
	var input ='#'+id+' .sample_num_rows_check input[type="checkbox"]';
	if(td.checked){
		$(input).each(function(index, element) {
            if($(this).attr('disabled')!='disabled'){
				$(this).prop('checked', true);
			}
        });	
		$(input).each(function(num) {
			if(num==1){
				var id_t = '#driver_table_'+$(this).parent().children('.suplier_hidden').val();
				$(id_t).remove();
			}            
        });
		$(input).each(function(num) {
			if(num>0){
				greate_row_driver_list($(this),page,sorti);	
			}            
        });
	}else{
		$(input).prop('checked', '');
		$(input).each(function(num) {
			if(num==1){
				var id_t = '#driver_table_'+$(this).parent().children('.suplier_hidden').val();
				$(id_t).remove();
			}            
        });
		$(input).each(function(num) {
			if(num>0){
				greate_row_driver_list($(this),page,sorti);	
			}            
        });
	}
	exit;
}
/*END выделение и перенос информации с группы чекбокс */


function greate_row_driver_list(i, page, sorti){
	
	if(page=='received'){//СТРАНИЦА ПОЛУЧЕННЫЕ
	
		if(sorti=='client'){
		var driver_addres = $(i).parent().children('.client_addres_hidden').val(); //адрес клиента
		var id = $(i).parent().children('.client_hidden').val(); //id поставщика
		var nick_name = $(i).parent().children('.client_nickName_hidden').val();
		var id2 = 'driver_table_client_'+id;
		}else{
		var driver_addres = $(i).parent().children('.supplier_addres').val(); //адрес поставщика
		var id = $(i).parent().children('.suplier_hidden').val(); //id поставщика
		var nick_name = $(i).parent().children('.suplier_nickName_hidden').val();
		var id2 = 'driver_table_'+id;
		}
		var z = 0;	
		var under_pledge_supplier = $(i).parent().parent().find('td:nth-of-type(8) span').text();//залог поставщику
		var under_pledge_client = $(i).parent().parent().find('td:nth-of-type(9) span').text();//залог с клиента
		if($(i).prop('checked')){
			/*проверяем есть ли в доставке этот поставщик.... если нет - создаем форму*/
					
			$('#form_for_driver_body > table').each(function() {
				if($(this).attr('id')==id2){
					z=z+1;
				}			
			});
			if(z>0){			
					/***********************/
					//создаем строку при наличии таблицы
					$('<tr id="'+$(i).attr('name')+'" class="row_for_table_driver" ><input type="hidden" name="tovar_id[]" value="'+$(i).attr('name')+'"><td style="border-right:none">3</td><td>'+$(i).parent().parent().children('td:nth-of-type(5)').text()+'</td><td>'+$(i).parent().parent().children('td:nth-of-type(6)').text()+'</td><td align="center">1</td><td align="right"><input type="text" onchange="calc_all(this, 5)" value="'+under_pledge_supplier+'" name="under_pledge_supplier[]"/> р</td><td align="right"><input type="text" onchange="calc_all(this, 6)" value="'+under_pledge_client+'" name="under_pledge_client[]"/> р</td></tr>').insertAfter('#'+id2+' tbody tr.selector_row');
									
					/*выполняем расчет порядкового номера*/
					$('.form_for_driver_table').each(function() {
						$(this).find('.row_for_table_driver').each(function(i) {
							var number = i + 1;
							$(this).find('td:first').text(number);						
						});
					});
					/*/считаем*/
					$('span.itogo_left').each(function() {
						$(this).parent().parent().parent().find('.row_for_table_driver td input[type="text"]').each(function() {
							$(this).change();
						});
						
					});
					/***********************/
					
				}else{
					//создаем таблицу при ее отсутствии
					$('#form_for_driver_body').append('<table class="form_for_driver_table" id="'+id2+'"><tbody></tbody></table>');
					//вставляем строку с названием поставщика
					
					$('#'+id2+' tbody').append('<tr><td style="font-style: italic; font-weight:bold" colspan="6">'+nick_name+'<span style="float:right; margin-right:2%;" class="itogo_right">итого поставщику: <span  class="summ_all">0,00</span> р.</span><span style="float:right; margin-right:18%;" class="itogo_left">итого поставщику: <span  class="summ_all">0,00</span> р.</span></td></tr>')
					//вставляем c названиями столбцов
					$('#'+id2+' tbody').append('<tr style="color:#7E7E7E; text-align:center" class="selector_row"><td colspan="2">артикул</td><td width="40%">описание</td><td>шт.</td><td width="18%">залог-поставщик</td><td width="18%">залог-клиент</td></tr>');
					
					/***********************/
					//вставляем в созданную таблицу строку
					$('<tr id="'+$(i).attr('name')+'" class="row_for_table_driver"><td style="border-right:none">3</td><td>'+$(i).parent().parent().children('td:nth-of-type(5)').text()+'<input type="hidden" name="tovar_id[]" value="'+$(i).attr('name')+'"></td><td>'+$(i).parent().parent().children('td:nth-of-type(6)').text()+'</td><td align="center">1</td><td align="right"><input type="text" onchange="calc_all(this, 5)" value="'+under_pledge_supplier+'" name="under_pledge_supplier[]"/> р</td><td align="right"><input type="text" onchange="calc_all(this, 6)" value="'+under_pledge_client+'" name="under_pledge_supplier[]"/> р</td></tr>').insertAfter('#'+id2+' tbody tr.selector_row');
									
					/*выполняем расчет порядкового номера*/
					$('.form_for_driver_table').each(function() {
						$(this).find('.row_for_table_driver').each(function(i) {
							var number = i + 1;
							$(this).find('td:first').text(number);						
						});
					});
					/***********************/
							
					//вставляем 2 строки с формой для карты курьера
					$('#'+id2+' > tbody:last').append('<tr height="7px"><td colspan="6"><table width="100%"><TR><td>поездка</td><td align="center" colspan="2">дата</td><td align="center">время</td><td align="right"></td><td align="right"></td></TR><TR><td>откуда -> куда</td><td>ближайшая</td><td align="center">необходимая</td><td align="center">после</td><td align="right"></td> <td align="right"></td></TR><TR><td><input type="hidden" value="'+nick_name+'" name="route_for_driver"><span style="border:1px dotted grey; padding:2px 5px 2px 5px;background-color: #f2f2f2;" onclick="edit_addres(this)">апл-склад -> '+nick_name+'</span><br><textarea type="text" class="edit_address" style="display:none; position:absolute" >'+driver_addres+'</textarea></td><td><div class="nearest_travel_date"></div></td><td align="center"><input type="text" value="" id="datepic'+id+'" style="width:80px; " name="date_route_for_driver"></td><td align="center" class="timing"><input type="text" value="15:00" style="width:45px" onClick="show_time_div(this)"  name="after_route_for_driver"></td><td align="center"><input type="checkbox" name="samples_already_collected" style="display:none" id="flag_'+id+'"><a class="c_on" onclick="send_sample_print(this)">Распечатать накладную</a></td><td align="center"><div class="check_button_sample_get" onclick="send_driver_form(this, \''+page+'\')">доставить</div></td></TR></table></td></tr>');
					
					$('#'+id2+' > tbody:last').append('<tr><td colspan="6"><table width="100%"><TR><td width="10%">примечания</td><td><input type="text" value="" style="width:100%" name="info_for_driver"></td></TR></table></td></tr>');
					$.datepicker.regional['ru'] = {//настройки датапикера должны инициализироваться под каждым пикиром, т.е. тут
						closeText: 'Закрыть',
						prevText: '&#x3c;Пред',
						nextText: 'След&#x3e;',
						currentText: 'Сегодня',
						monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
						'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
						monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
						'Июл','Авг','Сен','Окт','Ноя','Дек'],
						dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
						dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
						dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
						dateFormat: 'dd.mm.yy',
						firstDay: 1,
						minDate: "1",
						isRTL: false
						};
						$.datepicker.setDefaults($.datepicker.regional['ru']);
					$('#datepic'+id).datepicker();
					$('#'+id2+' > tbody tr .timing').append('<div class="time"><div style="color:#FF6A00" onClick="this.parentNode.style.display=\'none\'">&nbsp;&nbsp;&nbsp;&nbsp;X&nbsp;&nbsp;&nbsp;&nbsp;</div><div onClick="get_for_input(this)">12:00</div> <div onClick="get_for_input(this)">16:00</div> <div onClick="get_for_input(this)">09:00</div> <div onClick="get_for_input(this)">13:00</div> <div onClick="get_for_input(this)">17:00</div> <div onClick="get_for_input(this)">10:00</div> <div onClick="get_for_input(this)">14:00</div> <div onClick="get_for_input(this)">18:00</div> <div onClick="get_for_input(this)">11:00</div> <div onClick="get_for_input(this)">15:00</div> <div onClick="get_for_input(this)">19:00</div></div>');
				}
				
				/*запрос в карте курьера ближайшей даты поездки к поставщику*/
				
				$.post("modules/samples/functions_samples.php",//PHP обработчик запроса
				{ 		
					nearest_travel_date: 'yes',	
					supplier_id: id
				
				},
				function(data){
					if(data){//при положительном ответе PHP скрипта
					
					    $('#'+id2+' > tbody tr .nearest_travel_date').html(data);//ближайшая дата поездки до поставщика в таблице						
					}
				});
			
			/*добавлляем строку*/
					
		}else{
			/*срабатывает при снятии галки с checkbox*/
			/*удаляет таблицу по ее id при наличии 1 строки и удаляет строку по id товара при количестве строк более 1*/		
			if($('#'+id2+' .row_for_table_driver').length>1){
				/***********************/
				//удаляем строку
				$('#'+$(i).attr('name')).remove();
				/*выполняем расчет порядкового номера*/
				$('.form_for_driver_table').each(function() {
					$(this).find('.row_for_table_driver').each(function(i) {
						var number = i + 1;
						$(this).find('td:first').text(number);						
					});
				});
				/***********************/
			}else{
				//удаляем таблицу
				$('#'+id2).remove();
				}
			
		}
	}else{
		var driver_addres = $(i).parent().children('.supplier_addres').val(); //адрес поставщика
		var id = $(i).parent().children('.suplier_hidden').val(); //id поставщика
		var nick_name = $(i).parent().children('.suplier_nickName_hidden').val(); //id поставщика
		var z = 0;
		var id2 = 'driver_table_'+id;
		var under_pledge_supplier = $(i).parent().parent().find('td:nth-of-type(7) span').text();//залог поставщику
		var under_pledge_client = $(i).parent().parent().find('td:nth-of-type(8) span').text();//залог с клиента
		
		if($(i).prop('checked')){
			/*проверяем есть ли в доставке этот поставщик.... если нет - создаем форму*/
			
			$('#form_for_driver_body > table').each(function() {
				if($(this).attr('id')==id2)if($(this).attr('id')==id2){
					z=z+1;
				}			
			});
			if(z>0){			
					/***********************/
					//создаем строку при наличии таблицы
					$('<tr id="'+$(i).attr('name')+'" class="row_for_table_driver" ><input type="hidden" name="tovar_id[]" value="'+$(i).attr('name')+'"><td style="border-right:none">3</td><td>'+$(i).parent().parent().children('td:nth-of-type(4)').text()+'</td><td>'+$(i).parent().parent().children('td:nth-of-type(5)').text()+'</td><td align="center">1</td><td align="right"><input type="text" onchange="calc_all(this, 5)" value="'+under_pledge_supplier+'" name="under_pledge_supplier[]"/> р</td><td align="right"><input type="text" onchange="calc_all(this, 6)" value="'+under_pledge_client+'" name="under_pledge_client[]"/> р</td></tr>').insertAfter('#'+id2+' tbody tr.selector_row');
									
					/*выполняем расчет порядкового номера*/
					$('.form_for_driver_table').each(function() {
						$(this).find('.row_for_table_driver').each(function(i) {
							var number = i + 1;
							$(this).find('td:first').text(number);						
						});
					});
					/*/считаем*/
					$('span.itogo_left').each(function() {
						$(this).parent().parent().parent().find('.row_for_table_driver td input[type="text"]').each(function() {
							$(this).change();
						});
						
					});
					/***********************/
					
				}else{
					//создаем таблицу при ее отсутствии
					$('#form_for_driver_body').append('<table class="form_for_driver_table" id="'+id2+'"><tbody></tbody></table>');
					//вставляем строку с названием поставщика
					
					$('#'+id2+' tbody').append('<tr><td style="font-style: italic; font-weight:bold" colspan="6">'+nick_name+'<span style="float:right; margin-right:2%;" class="itogo_right">итого поставщику: <span  class="summ_all">0,00</span> р.</span><span style="float:right; margin-right:18%;" class="itogo_left">итого поставщику: <span  class="summ_all">0,00</span> р.</span></td></tr>')
					//вставляем c названиями столбцов
					$('#'+id2+' tbody').append('<tr style="color:#7E7E7E; text-align:center" class="selector_row"><td colspan="2">артикул</td><td width="40%">описание</td><td>шт.</td><td width="18%">залог-поставщик</td><td width="18%">залог-клиент</td></tr>');
					
					/***********************/
					//вставляем в созданную таблицу строку
					$('<tr id="'+$(i).attr('name')+'" class="row_for_table_driver"><td style="border-right:none">3</td><td>'+$(i).parent().parent().children('td:nth-of-type(4)').text()+'<input type="hidden" name="tovar_id[]" value="'+$(i).attr('name')+'"></td><td>'+$(i).parent().parent().children('td:nth-of-type(5)').text()+'</td><td align="center">1</td><td align="right"><input type="text" onchange="calc_all(this, 5)" value="'+under_pledge_supplier+'" name="under_pledge_supplier[]"/> р</td><td align="right"><input type="text" onchange="calc_all(this, 6)" value="'+under_pledge_client+'" name="under_pledge_supplier[]"/> р</td></tr>').insertAfter('#'+id2+' tbody tr.selector_row');
									
					/*выполняем расчет порядкового номера*/
					$('.form_for_driver_table').each(function() {
						$(this).find('.row_for_table_driver').each(function(i) {
							var number = i + 1;
							$(this).find('td:first').text(number);						
						});
					});
					/***********************/
									
					//вставляем 2 строки с формой для карты курьера
						$('#'+id2+' > tbody:last').append('<tr height="7px"><td colspan="6"><table width="100%"><TR><td>поездка</td><td align="center" colspan="2">дата</td><td align="center">время</td><td align="right"></td><td align="right"></td></TR><TR><td>откуда -> куда</td><td>ближайшая</td><td align="center">необходимая</td><td align="center">после</td><td align="right"></td> <td align="right"></td></TR><TR><td><input type="hidden" value="'+nick_name+'" name="route_for_driver"><span style="border:1px dotted grey; padding:2px 5px 2px 5px;background-color: #f2f2f2;" onclick="edit_addres(this);">апл-склад -> '+nick_name+'  </span><br><textarea type="text" class="edit_address" style="display:none; position:absolute" >'+driver_addres+'</textarea></td><td><div class="nearest_travel_date"></div></td><td align="center"><input type="text" value="" id="datepic'+id+'" style="width:80px; " name="date_route_for_driver"></td><td align="center" class="timing"><input type="text" value="15:00" style="width:45px" onClick="show_time_div(this)"  name="after_route_for_driver"></td><td align="center"><input type="checkbox" name="samples_already_collected" style="display:none" id="flag_'+id+'"><span class="check_button_sample_on" onClick="check_flag_alredy_collected(this)">образцы собраны</span></td><td align="center"><div class="check_button_sample_get" onclick="send_driver_form(this, \''+page+'\')">забрать</div></td></TR></table></td></tr>');
					
					$('#'+id2+' > tbody:last').append('<tr><td colspan="6"><table width="100%"><TR><td width="10%">примечания</td><td><input type="text" value="" style="width:100%" name="info_for_driver"></td></TR></table></td></tr>');
					$.datepicker.regional['ru'] = {//настройки датапикера должны инициализироваться под каждым пикиром, т.е. тут
						closeText: 'Закрыть',
						prevText: '&#x3c;Пред',
						nextText: 'След&#x3e;',
						currentText: 'Сегодня',
						monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
						'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
						monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
						'Июл','Авг','Сен','Окт','Ноя','Дек'],
						dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
						dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
						dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
						dateFormat: 'dd.mm.yy',
						firstDay: 1,
						minDate: "1",
						isRTL: false
						};
						$.datepicker.setDefaults($.datepicker.regional['ru']);
					$('#datepic'+id).datepicker();
					$('#'+id2+' > tbody tr .timing').append('<div class="time"><div style="color:#FF6A00" onClick="this.parentNode.style.display=\'none\'">&nbsp;&nbsp;&nbsp;&nbsp;X&nbsp;&nbsp;&nbsp;&nbsp;</div><div onClick="get_for_input(this)">12:00</div> <div onClick="get_for_input(this)">16:00</div> <div onClick="get_for_input(this)">09:00</div> <div onClick="get_for_input(this)">13:00</div> <div onClick="get_for_input(this)">17:00</div> <div onClick="get_for_input(this)">10:00</div> <div onClick="get_for_input(this)">14:00</div> <div onClick="get_for_input(this)">18:00</div> <div onClick="get_for_input(this)">11:00</div> <div onClick="get_for_input(this)">15:00</div> <div onClick="get_for_input(this)">19:00</div></div>');
				}
			
			/*добавлляем строку*/
					
		}else{
			/*срабатывает при снятии галки с checkbox*/
			/*удаляет таблицу по ее id при наличии 1 строки и удаляет строку по id товара при количестве строк более 1*/		
			if($('#'+id2+' .row_for_table_driver').length>1){
				/***********************/
				//удаляем строку
				$('#'+$(i).attr('name')).remove();
				/*выполняем расчет порядкового номера*/
				$('.form_for_driver_table').each(function() {
					$(this).find('.row_for_table_driver').each(function(i) {
						var number = i + 1;
						$(this).find('td:first').text(number);						
					});
				});
				/***********************/
			}else{
				//удаляем таблицу
				$('#'+id2).remove();
				}
			
		}
	}
}
/*---------- START ----- обсчет итого -----*/
function calc_all(i,hand){
	var summ_all = 0;
		$(i).parent().parent().parent().find('tr td:nth-of-type('+hand+') input[type="text"]').each(function() {
			s = $(this).val();
			s = s.replace(',', '');//выбираем запятые для правильного сложения
			summ_all = Number(summ_all) + Number(s);
            
        });
		$(i).val(accounting.formatMoney($(i).val(),[symbol = ""])); 
		if(hand==5){ 
			$(i).parent().parent().parent().find('.itogo_left .summ_all').html(accounting.formatMoney(summ_all,[symbol = ""]));//вывод итого
		}else{
			$(i).parent().parent().parent().find('.itogo_right .summ_all').html(accounting.formatMoney(summ_all,[symbol = ""]));//вывод итого
		}
}
/*---------- END ----- обсчет итого -----*/

/*---------- END ----- окно запроса доставки -----*/

/*---------- START ----- функция продления времени жизни образца / изменение даты -----*/
function change_date_row(page, X, Y){
				$(function(){
						$.datepicker.regional['ru'] = {//настройки датапикера должны инициализироваться под каждым пикиром, т.е. тут
							closeText: 'Закрыть',
							showWeek: true,//недели
							show: 'both',
							prevText: '&#x3c;Пред',
							nextText: 'След&#x3e;',
							currentText: 'Сегодня',
							monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
							'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
							monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
							'Июл','Авг','Сен','Окт','Ноя','Дек'],
							dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
							dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
							dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
							dateFormat: 'dd.mm.yy',
							firstDay: 1,
							minDate: "1",
							isRTL: false
						};
						$.datepicker.setDefaults($.datepicker.regional['ru']);
					});
					$("#received_b2").datepicker('dialog',
						Date(),		
						function(dateText, inst){
							var POST_send_id = '';  //передаваемые ID
							var POST_send_date = dateText;	//выбранная дата из календаря
							var art='';				
							if($('.sample_content_tables table tbody  tr td input:checked').length==0){
							alert('Для выполнения операции необходимо выбрать минимум один образец');
							return false;
							}
							$('.sample_content_tables table tbody  tr td input:checked').each(function() {
								POST_send_id += $(this).attr('name');
								if(page=='received'){
									$(this).parent().parent().find('td:nth-of-type(12)').html('<span style="color:red">'+dateText+'</span>');          
								}else{
									$(this).parent().parent().find('td:nth-of-type(11)').html('<span style="color:red">'+dateText+'</span>');
								}
							});
							/*отправляем данные постом*/
							$.post("modules/samples/functions_samples.php",
							{
								sample_page: 'received',
								button: 'received_2',				 
								position: POST_send_id, 
								td: 'postponement_date',
								value: POST_send_date
							},
							function(data){
								if(data=='okey'){
								/* удаляем отмеченные строки из таблицы*/
												
								}else{					
									alert(data);
								}
							});
							/*конец отправки*/					
						},
						{ showButtonPanel: false },
						[X, Y]);
}
/*---------- END ----- функция продления времени жизни образца / изменение даты -----*/

/*---------- START ----- обработка кнопок страницы -----*/
function submit_received_button(page, button){
	switch (page) {//страницы
		case 'received':
			switch (button) {//кнопки
		  		case 'received_2'://срок продлен
				change_date_row(page, 130, 70);	
			break
			case 'received_3'://возвращен клиентом
				/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				/*запрос*/
				//alert(art);
				$.post("modules/samples/functions_samples.php",
					{
					sample_page: page,
					button: button,				 
					position: art, 
					td: 'stage',
					value: '6' 
					},
					function(data){
						if(data=='okey'){
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().find('td:nth-of-type(1)').css({"background":"#03981D"});
							$("#request_samples_2 table tbody input:checked").attr('disabled','true');
							$("#request_samples_2 table tbody input:checked").prop('checked','');
							
						}else{				
							alert(data);
						}
					});
			break
			case 'received_4':
				/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				
				/*запрос*/
				//alert(art);
				$.post("modules/samples/functions_samples.php",
				{
				sample_page: page,
				button: button,				 
				position: art, 
				td: 'stage',
				value: '5' 
				},
				function(data){
					if(data=='okey'){
						/* удаляем отмеченные строки из таблицы*/
						$("#request_samples_2 table tbody input:checked").parent().parent().find('td:nth-of-type(1)').css({"background":"#8ecdf6"});
						$("#request_samples_2 table tbody input:checked").attr('disabled','true');
						$("#request_samples_2 table tbody input:checked").prop('checked','');
						
					}else{				
						alert(data);
					}
				});
			break
			default:
				alert('такого аргумента кнопки не описано\n функ. submit_received_button')
		}
		break
		case 'client_hand'://у клиента
			switch (button) {//кнопки
				case 'button_1':
					change_date_row(page, 8, 60);	
				break
				case 'received_5'://доставить
					/*сбор ID выбранных арт*/
					var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
					/*запрос*/
					//alert(art);
					$.post("modules/samples/functions_samples.php",
					{
						sample_page: page,
						button: button,				 
						position: art, 
						td: 'stage',
						value: '6' 
					},
					function(data){
						if(data=='okey'){
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().remove();
							/*удаляем таблицу при отсутствии строк в tbody*/
							$('.sample_content_tables').each(function() {
								if($(this).find('tr').length==1){$(this).remove();}
							});									
							/*обновляем нумерацию строк в таблице*/
							$('.sample_content_tables').each(function() {
								$(this).find('table tbody tr').each(function(i) {
									var number = i + 1;
									$(this).find('td:first').text(number);						
								});
							});
							/*----*/							
						}else{				
							alert(data);
						}
					});
				break
			default:
				alert('такого аргумента кнопки не описано\n функ. submit_received_button')
			}
		break
		case 'ordered':
			switch (button) {//кнопки
				case 'button_1':
				/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				/*запрос*/
				$.post("modules/samples/functions_samples.php",
					{	
						sample_page: page,
						button: button,				 
						position: art, 
						td: 'stage',
						value: '3'
					},
					function(data){
						if(data=='okey'){
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().remove();
							
							/*удаляем таблицу при отсутствии строк в tbody*/
									$('.sample_content_tables').each(function() {
										if($(this).find('tr').length==1){$(this).remove();}
									});
											
							/*обновляем нумерацию строк в таблице*/
							$('.sample_content_tables').each(function() {
								$(this).find('table tbody tr').each(function(i) {
									var number = i + 1;
									$(this).find('td:first').text(number);						
								});
							});
						}else{
							alert(data);
						}
					});
				break
				case 'button_2':
				/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				/*запрос*/
				$.post("modules/samples/functions_samples.php",
					{	
						sample_page: page,
						button: button,				 
						position: art, 
						td: 'stage',
						value: '1'
					},
					function(data){
						if(data=='okey'){
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().remove();
							
							/*удаляем таблицу при отсутствии строк в tbody*/
									$('.sample_content_tables').each(function() {
										if($(this).find('tr').length==1){$(this).remove();}
									});
											
							/*обновляем нумерацию строк в таблице*/
							$('.sample_content_tables').each(function() {
								$(this).find('table tbody tr').each(function(i) {
									var number = i + 1;
									$(this).find('td:first').text(number);						
								});
							});
						}else{
							alert(data);
						}
					});
				break
				default:
				alert('такого аргумента кнопки не описано\n функ. submit_received_button')
			}
		break
		case 'request':
			switch (button) {//кнопки
				case 'button_2':
				/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				/*запрос*/
				$.post("modules/samples/functions_samples.php",
					{	
						sample_page: page,
						button: button,				 
						position: art, 
						td: 'stage',
						value: '7'
					},
					function(data){
						if(data=='okey'){
							alert('отмеченные позиции были перемещены во вкладку "история"');
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().remove();
							
							/*удаляем таблицу при отсутствии строк в tbody*/
									$('.sample_content_tables').each(function() {
										if($(this).find('tr').length==1){$(this).remove();}
									});
											
							/*обновляем нумерацию строк в таблице*/
							$('.sample_content_tables').each(function() {
								$(this).find('table tbody tr').each(function(i) {
									var number = i + 1;
									$(this).find('td:first').text(number);						
								});
							});
						}else{
							alert(data);
						}
					});
				break
				case 'button_1':
					$('#bg').show();
				break
				default:
				alert('такого аргумента кнопки не описано\n функ. submit_received_button')
			}
		break
		case 'return':
			switch (button) {//кнопки
				case 'button_1':
					/*сбор ID выбранных арт*/
				var art='';
				$('.sample_content_tables').each(function(){
					$(this).find('table tbody').each(function(i) {
						$(this).find("input:checked").each(function() {
						art+=$(this).attr('name');
						});
					});
				});
				if(art==''){
				alert('Для выполнения операции необходимо выбрать минимум один образец');
				return false;
				}
				/*запрос*/
				$.post("modules/samples/functions_samples.php",
					{	
						sample_page: page,
						button: button,				 
						position: art, 
						td: 'stage',
						value: '3'
					},
					function(data){
						if(data=='okey'){
							alert('отмеченные позиции были перемещены во вкладку "полученные"');
							/* удаляем отмеченные строки из таблицы*/
							$("#request_samples_2 table tbody input:checked").parent().parent().remove();
							
							/*удаляем таблицу при отсутствии строк в tbody*/
									$('.sample_content_tables').each(function() {
										if($(this).find('tr').length==1){$(this).remove();}
									});
											
							
						}else{
							alert(data);
						}
					});
				break
				default:
				alert('такого аргумента кнопки не описано\n функ. submit_received_button')
			}
		break
		default:
			alert('такого аргумента страницы не описано\n функ. submit_received_button')
	}
}
/*---------- END ----- обработка кнопок страницы -----*/


function submitform(page){ 	   	
	document.forms[id].submit();	
}


function check_all(td){
	if(td.checked){
		$('.sample_num_rows_check input').prop('checked', true);
	}else{
		$('.sample_num_rows_check input').prop('checked', '');
	}
}
function comment_off(td){
	var id = td.parentNode.parentNode.parentNode.parentNode.id;
	var input ='#'+id+' .sample_note input[type="text"]';
	if($(td).attr('src')=='skins/images/img_design/reset_btn_plus.png'){
		
		$(td).attr('src','skins/images/img_design/reset_btn_minus.png');
		$(input).show();
		$('#'+id+' .sample_note').css('background','white');
	}else{		
		$(td).attr('src','skins/images/img_design/reset_btn_plus.png');
		$(input).hide();
		$('#'+id+' .sample_note').css('background','#D6D6D6');
	}
}
function check_on(td){
	var id = td.parentNode.parentNode.parentNode.parentNode.id;
	var input ='#'+id+' .sample_num_rows_check input[type="checkbox"]';
	if(td.checked){
		$(input).prop('checked', true);
	}else{
		$(input).prop('checked', '');
	}
	exit;
}
function show_time_div(i){	
		$(i).parent().children('div.time').show();	
	}

function get_for_input(i){
	$(i).parent().parent().children('input').val($(i).html())
	$(i).parent().hide();
}
/*----- START ----- печеть*/


/*----- END ----- печеть*/
/*---------- START ----- обработка и отправка формы транспортировки № 1 -----*/
function send_sample_print(i){
		var id_samples = '';
		$(i).parent().parent().parent().parent().parent().parent().parent().find('.row_for_table_driver').each(function() {
			id_samples += $(this).attr('id');
		});
		var linked = '/os/modules/samples/print_sample_list.php?id_samples='+id_samples;
		$(i).printPage({
		  url: linked,
		  attr: "href",
		  message:"Your document is being created"
		});
	}
function send_driver_form(i,page){
		var input_supplier = '';
		var input_client = '';
		var post_adress = $(i).parent().parent().find('textarea').val();
	if($(i).parent().parent().find('textarea').val()==''){//проверяем наличие адреса доставки
		alert('Пожалуйста, укажите адрес доставки');
		return false;
	}
	if($(i).parent().parent().find('.hasDatepicker').val()==''){//проверяем заполнение даты
		alert('укажите желаемую дату транспортировки');
		return false;
	}
	
		
		/*изменяем кнопку*/
	$(i).removeClass('check_button_sample_get');
	$(i).parent().css({"width":"100px"});
	$(i).html('');
	$(i).parent().parent().find('input[type="checkbox"]').attr("disabled", true);//лочим флаг 
	$(i).parent().parent().find('td:nth-of-type(5)').find('span').attr('onClick','');
//	$(i).parent().parent().find('td:nth-of-type(5)').append('<span class="check_button_sample_on">образцы собраны</span>')
	$(i).addClass('check_button_sample_get_animate');
	
	/*собираем список отправленных в php образцов*/
	var id_samples = '';
	$(i).parent().parent().parent().parent().parent().parent().parent().find('.row_for_table_driver').each(function() {
        id_samples += $(this).attr('id');
    });
	/*собираем список залога для поставщика*/
	var date_of_receipt = $(i).parent().parent().find('input.hasDatepicker').val();
	var time_of_receipt = $(i).parent().parent().find('input[name="after_route_for_driver"]').val();	
	var info_for_driver = $(i).parent().parent().parent().parent().parent().parent().parent().find('tr:last td:nth-of-type(2) input').val();
	$(i).parent().parent().parent().parent().parent().parent().parent().find('.row_for_table_driver td:nth-of-type(5) input').each(function(index, element) {
    	s = $(this).val();
		s = s.replace(',', '');//выбираем запятые для правильного сложения
		input_supplier += 'c_'+s;    
    });
	/*--------*/
	/*собираем список залога для клиента*/
	$(i).parent().parent().parent().parent().parent().parent().parent().find('.row_for_table_driver td:nth-of-type(5) input').each(function(index, element) {
    	s = $(this).val();
		s = s.replace(',', '');//выбираем запятые для правильного сложения
		input_client += 'c_'+s;    
    });
	/*--------*/
	if($(i).parent().parent().find('input[type="checkbox"]').prop('checked')==true){
		var flag = 'on';
	}else{
		var flag = 'off';
	}
	if(page=='received'){
	$.post("modules/samples/functions_samples.php",//PHP обработчик запроса
			{ 			
			stage: '4',
			post_adress: post_adress,
			send_driver_form: 'yes', //идентификатор запроса для проверки в php скрипте
			id_samples: id_samples, //строка с id образца вида c_15 (где число это id) требуется доп обработка в PHP
			samples_already_collected: flag,// флаг "образцы собраны"(перенаправляется/копируется КК)
			date_of_receipt: date_of_receipt, //дата получения образца, она же дата вывоза образца от поставщика указанная менеджером
			info_for_driver: info_for_driver, //поле дополнительной инфы для водителя(перенаправляется/копируется КК)
			input_client:input_client, //залоги клиента
			input_supplier:input_supplier, //залоги поставщику
			time_of_receipt: time_of_receipt //планируемое время вывоза образца от поставщика(перенаправляется/копируется КК)
			},
			function(data){
				if(data=='OK'){//при положительном ответе PHP скрипта
					/*изменяем кнопку*/				
					$(i).removeClass('check_button_sample_get_animate');
					$(i).html('поставлено в КК');
					$(i).attr('onClick','');
					$(i).addClass('already_send');
					
					
					if($('table.form_for_driver_table').length==$('div.already_send').length){ //если все формы отправлены
						/*перекрашиваем отмеченные строки из таблицы*/
						$("#request_samples_2 table tbody input:checked").parent().parent().find('td:nth-of-type(1)').css({'background':'#F2CE04'});
						
						
					}
					
					
				}else{
					alert(data);
				}
			});
			}else if(page=='return'){
				$.post("modules/samples/functions_samples.php",//PHP обработчик запроса
			{ 			
			stage: '7',
			post_adress:post_adress,
			send_driver_form: 'yes', //идентификатор запроса для проверки в php скрипте
			id_samples: id_samples, //строка с id образца вида c_15 (где число это id) требуется доп обработка в PHP
			samples_already_collected: flag,// флаг "образцы собраны"(перенаправляется/копируется КК)
			date_of_receipt: date_of_receipt, //дата получения образца, она же дата вывоза образца от поставщика указанная менеджером
			info_for_driver: info_for_driver, //поле дополнительной инфы для водителя(перенаправляется/копируется КК)
			input_client:input_client, //залоги клиента
			input_supplier:input_supplier, //залоги поставщику
			time_of_receipt: time_of_receipt //планируемое время вывоза образца от поставщика(перенаправляется/копируется КК)
			},
			function(data){
				if(data=='OK'){//при положительном ответе PHP скрипта
					/*изменяем кнопку*/				
					$(i).removeClass('check_button_sample_get_animate');
					$(i).html('поставлено в КК');
					$(i).attr('onClick','');
					$(i).addClass('already_send');
					
					
					if($('table.form_for_driver_table').length==$('div.already_send').length){ //если все формы отправлены
						/*перекрашиваем отмеченные строки из таблицы*/
						$("#request_samples_2 table tbody input:checked").parent().parent().remove();
						/*удаляем таблицу при отсутствии строк в tbody*/
						$('.sample_content_tables').each(function() {
							if($(this).find('tr').length==1){$(this).remove();}
						});
						
						/*обновляем нумерацию строк в таблице*/
						$('.sample_content_tables').each(function() {
							$(this).find('table tbody tr').each(function(i) {
								var number = i + 1;
								$(this).find('td:first').text(number);						
							});
						});
						
						$('#bg').hide();
						$('#form_for_driver_body').html();
						
					}
					
					
				}else{
					alert(data);
				}
			});
			}else{
				$.post("modules/samples/functions_samples.php",//PHP обработчик запроса
			{ 
			stage: '2',
			send_driver_form: 'yes', //идентификатор запроса для проверки в php скрипте
			id_samples: id_samples, //строка с id образца вида c_15 (где число это id) требуется доп обработка в PHP
			samples_already_collected: flag,// флаг "образцы собраны"(перенаправляется/копируется КК)
			date_of_receipt: date_of_receipt, //дата получения образца, она же дата вывоза образца от поставщика указанная менеджером
			info_for_driver: info_for_driver, //поле дополнительной инфы для водителя(перенаправляется/копируется КК)
			input_client:input_client, //залоги клиента
			input_supplier:input_supplier, //залоги поставщику
			time_of_receipt: time_of_receipt //планируемое время вывоза образца от поставщика(перенаправляется/копируется КК)
			},
			function(data){
				if(data=='OK'){//при положительном ответе PHP скрипта
					/*изменяем кнопку*/				
					$(i).removeClass('check_button_sample_get_animate');
					$(i).html('поставлено в КК');
					$(i).attr('onClick','');
					$(i).addClass('already_send');
					
					
					if($('table.form_for_driver_table').length==$('div.already_send').length){ //если все формы отправлены
						/* удаляем отмеченные строки из таблицы*/
						$("#request_samples_2 table tbody input:checked").parent().parent().remove();
						
						/*удаляем таблицу при отсутствии строк в tbody*/
						$('.sample_content_tables').each(function() {
							if($(this).find('tr').length==1){$(this).remove();}
						});
						
						/*обновляем нумерацию строк в таблице*/
						$('.sample_content_tables').each(function() {
							$(this).find('table tbody tr').each(function(i) {
								var number = i + 1;
								$(this).find('td:first').text(number);						
							});
						});
						
						if($("div.sample_content_tables table tr td.sample_num_rows").length==0){//если строк больше нет, редиректим дальше
							setTimeout(function(){//таймаут редирета во вкладку "заказанные"
								location.href = './?page=samples&sample_page=ordered';
							},2000);
						}else{//если строки остались - чистим и закрываем окно отпраки задачи в КК
							setTimeout(function(){//таймаут выполнения 2 сек. //время одупления юзера того что он сделал
								$('#form_for_driver_body').html('');
								$('#bg').hide();
							},2000);
						}
					}
					
					
				}else{
					alert(data);
				}
			});
			}
	
	
}
/*---------- END ----- обработка и отправка формы транспортировки № 1 -----*/

/*---------- START ----- включение влага "образцы собраны" в форме транспортировки -----*/
function check_flag_alredy_collected(i){
	if($(i).prev().prop('checked')==''){
		$(i).prev().prop('checked',true);
		$(i).css('background','#c4c4c4')
	}else{
		$(i).prev().prop('checked','');
		$(i).css('background','')
	}
}
/*---------- END ----- включение влага "образцы собраны" в форме транспортировки -----*/

/*---------- START ----- change_note -----*/
window.collla=0;
function change_note(w){	
	window.collla++;
	var quest = window.collla;
	setTimeout(function(){//таймаут 
	if(quest == window.collla){	
	
	if($(w).next().is('img')){//если изображение отсутствует
		$(w).parent().find('img').attr('src','skins/images/img_design/loading.gif');		
	}else{//если уже есть
		$(w).parent().append('<img src="skins/images/img_design/loading.gif" class="loading" style="float:right; position:absolute; right:20px;">');
	}
		var change_note = $(w).parent().parent().children('.sample_num_rows_check').find('input').attr('name');
		var text = $(w).val();
		$.post("modules/samples/functions_samples.php",
			{ 
			change_note: change_note,
			text: text
			},
			function(data){
				if(data=='okey'){
					$(w).next().attr("src","skins/images/img_design/ok.gif");
				}else{
					alert(data);
				}
			});
		}
	},1000);//1 секунды
}
/*---------- END ----- change_note -----*/

/*-------start resize table_header-----*/
$(window).resize(function () {
	if($('#table_1').length){
		var offset = $('#table_1').offset();
		
		$('#sample_content_head table').css({'margin-left':offset.left+1});		
		$('#sample_content_head table').width($('#table_1').width()+3)
		$('#sample_content_head table thead tr td').each(function(index, element) {
			var ru = index+1;
			if(ru==4){
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()+1)
			}else if(ru==5){
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()+3)
			}else{
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()).css({'padding':'2px 2px 2px 5px'});
			}			
		});
	}
	});
$(document).ready(function(e) {
	
	$('#scroll_container').height($(window).height()-$('table.quick_bar_tbl').height()-$('#sample_content_head').height()-$('#sample_menu_header').height()-$('table.main_menu_tbl').height()-10);	
	
	
	if($('#table_1').length){
		var offset = $('#table_1').offset();
		
		$('#sample_content_head table').css({'margin-left':offset.left+1});		
		$('#sample_content_head table').width($('#table_1').width()+3)
		$('#sample_content_head table thead tr td').each(function(index, element) {
			var ru = index+1;
			if(ru==4){
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()+1)
			}else if(ru==5){
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()+3)
			}else{
				$(this).width($('#table_1 tbody tr:last-child td:nth-of-type('+ru+')').width()).css({'padding':'2px 2px 2px 5px'});
			}			
		});
	}
});
/*-------end resize table_header-----*/

/*---------- START ----- открыть/скрыть доп блоки. обрабатывается 1 раз, сразу после перезагрузки страницы -----*/
$(document).ready(function(e) {
	
	
	window.sel=0;
	$('.sample_content_tables').each(function(e) {
		$(this).find('table tbody tr td.sample_note input').each(function(index, element) {
			//alert($(this).val())
            if($(this).val()!=''){		
				window.sel++	
			}			
		});
		//alert(window.sel);
			if(window.sel==0){
				$(this).find('table thead tr td img').attr('src','skins/images/img_design/reset_btn_plus.png');
				$(this).find('table tbody tr td.sample_note').css({'background':'#D6D6D6'});
				$(this).find('table tbody tr td.sample_note input[type="text"]').hide();
			}
			window.sel=0
    }); 
	$('.marker_sytatus').height($('.marker_sytatus').parent().height()+4)
	
});
/*---------- END ----- открыть/скрыть доп блоки -----*/

function img_show_togle(i){
	//$('div.sample_content_tables table tbody tr td img').hide();
	$(i).find('img').toggle( "fast", function() {
// Animation complete.
});
}