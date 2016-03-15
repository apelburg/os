/*
	
	// перезагрузка РТ без перезагрузки окна
	$.SC_reload_RT_content();


	// вызов калькулятора
	calculator_add_services
	
	/**
	 *  добавление услуг в Тотал 
	 *
	 *  @param 		adsObj = ['2245','6543'] - id table rows rt_dop_uslugi
	 *  @example 	$('#js-main_service_center').totalCommander('add_services',adsObj);
	 */
	/**
	 *  обновление окна Тотал 
	 *
	 *  @param 		adsObj = ['2245','6543'] - id table rows rt_dop_uslugi
	 *  @example 	$('#js-main_service_center').totalCommander('update_total_window');
	 */

	 
	 /*
*/

/*
	ЧТО ОСТАЛОСЬ:
	1) сохранение общей скидки в группе и нет ( READY )
	2) изменение комментариев - в группе и нет
	3) выгрузка валидного описания услуги из калькулятора
	4) добавление и удаление в связанный тираж других вариантов 
	5) запрет объединения вариантов
	6) удаление услуг в группе и нет ( READY )
	7) полное обновление информации в окне Тотал и чтоб после все работало (в основном после калькулятора) 
	   или предоставление методов добавления услуг в объект ТОТАЛ
	8) метод редактирования json услуг для часных случаев   ( READY )
	(нужен в случае, когда окно нельзя обновить, скажем при редактировании информации по услугам, их удалении)
*/



function round_money(num){
	num = Number(num);
	var new_num = Math.ceil((num)*100)/100;
    return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
}

/**
 *	Service center scripts	
 *
 *	@author  	Alexey Kapitonov
 *	@version 	12:23 12.02.2016
 */

// запуск из РТ
jQuery(document).on('click', '#rt_tbl_body tr td.calc_btn span:first-child', function(event) {
	$.post('', {
			AJAX: 	'get_service_center',
			row_id: $(this).parent().parent().attr("row_id")
		}, function(data, textStatus, xhr) {
			if(data['myFunc'] !== undefined && data['myFunc'] == 'show_SC'){
				$.SC_createWindow(Base64.decode(data['html']));	
			}				
			standard_response_handler(data);
		},'json');
});

// запуск из Карточки
jQuery(document).on('click', '.open_service_center', function(event) {
	$.post(window.location.href+'&query_num='+$(this).attr("data-query_num"), {
			AJAX: 	'get_service_center',
			row_id: $(this).attr("data-row_id")
		}, function(data, textStatus, xhr) {
			if(data['myFunc'] !== undefined && data['myFunc'] == 'show_SC'){
				$.SC_createWindow(Base64.decode(data['html']));	
			}				
			standard_response_handler(data);
		},'json');
});

/**
 *	jquery plugin Total Commanders
 *
 *	@author  	Alexey Kapitonov	
 */
(function( $ ){

	var methods = {
		mainObj : {},		// содержит все варианты
		variants_rows : {}, 	// строки вариантов
		top_menu : {}, 			// меню тиражей
		checkbox_main : {}, 	// чек управления группой
  	

		init : function( options ) {

			return this.each(function(){
				var $this = $(this);

				$this.addClass('totalCommander');

				// выделяем рабочие области
				methods.variants_tbody = 	$this.find('#js-main-service_center-variants-table tbody');
				methods.variants_rows = 	$this.find('#js-main-service_center-variants-table tbody tr');
				methods.top_menu_div = 		$this.find('#js-main-service_center-top_menu ul');
				methods.top_menu = 			$this.find('#js-main-service_center-top_menu ul li');
				methods.checkbox_main = 	$this.find('#js-main-service_center-variants-table thead tr th:nth-of-type(2) div.js-psevdo_checkbox');
				
				methods.services_tbl =		$this.find('#js-main-service_center-variants-services-div-table table');

				// кнопка сбросить все 
				methods.btn_cancel_all = 	$('#sc_cancel_all');
				// кнопка Добавить услугу
				methods.btn_calculators = 	$('#sc_add_service');


				// собираем главный объект
				methods.variants_rows.each(function(index, el) {
					var dop_row_id = $(this).attr('data-dop_row_id');
					methods.mainObj[dop_row_id] = [];
					methods.mainObj[dop_row_id]['variant'] = [];
					// console.log($(this).find('.js-variant_services_json div').html())
					methods.mainObj[dop_row_id]['variant'] = jQuery.parseJSON($(this).find('.js-variant_info div').html());
					methods.mainObj[dop_row_id]['services'] = [];
					methods.mainObj[dop_row_id]['services'] = jQuery.parseJSON($(this).find('.js-variant_services_json div').html());
					// console.log(jQuery.parseJSON($(this).find('.js-variant_services_json div').html()))
				});

				// объект зависимостей услуг от вариантов
				methods.depending_on_the_services_and_options = jQuery.parseJSON($this.find('#js-depending_on_the_services_and_options').html());
				// объект зависимостей вариантов от услуг
				methods.depending_on_the_options_and_services = jQuery.parseJSON($this.find('#js-depending_on_the_options_and_services').html());

				// events chose variants rows
				methods.checkbox_main.bind("click.totalCommander", methods.checkbox_main_click );
				methods.variants_rows.find('.js-psevdo_checkbox').click(function(event) {
					methods.checkbox_change($(this));
				});
				methods.variants_rows.bind("click.totalCommander", methods.variants_rows_choose );
				methods.variants_rows.bind("dblclick.totalCommander", function(){
					event.preventDefault();
					$(this).find('td:nth-of-type(2) div.js-psevdo_checkbox').click();
				});

				// отработка верхнего меню
				methods.top_menu.bind('click',function(){		
					if($(this).find('div').html() == 'Артикулы'){
						methods.cancel_all_choosen_variants();
					}else{
						methods.top_menu.removeClass('checked');
						$(this).addClass('checked');
						// снимаем выделение отовсюду
						methods.variants_rows.removeClass('tr_checked').find('td:nth-of-type(2).checked').removeClass('checked');
						

						// получаем id dop_data для данной группы
						var serv_id = $(this).attr('data-var_id').split(',');


						for(var k = 0, length1 = serv_id.length; k < length1; k++){
							// console.log(methods.variants_tbody.find('tr#dop_data_'+serv_id[k]))
							methods.variants_tbody.find('tr#dop_data_'+serv_id[k]).addClass('tr_checked').find('td:nth-of-type(2)').addClass('checked');

						}
						// обновляем контент услуг относительно выбранных вариантов
						methods.update_services_content();
						// инициализируем работу нижней части окна
						methods.services_init();
						// поправка главного чекбокса группы
						methods.checkbox_main_check();
					}					
				});

				// сохраняем ИТОГО
				methods.services_itogo_row = methods.services_tbl.find('tr.itogo');
				methods.services_itogo_row.find('.delete_all_services').click(function(){
					var delete_service_ids = [];
					if(methods.top_menu_div.find('li.checked').attr('data-var_id') && methods.top_menu_div.find('li.checked').attr('data-var_id').split(',').length>1 ){
						var i = 0;
						// перебираем DOM услуг
						methods.services_rows.each(function(index, el) {
							// get group services
							if($(this).find('.service_group').length > 0){
								// получаем id группы услуг
								var service_id_arr = $(this).find('.service_group').attr('data-id_s').split(',');
								// записываем id для удаления
								for(var k = 0, length1 = service_id_arr.length; k < length1; k++){
									delete_service_ids[i] = [];	
									delete_service_ids[i++] = service_id_arr[k];	
								}
								
								$(this).remove();
							}else{

							}							
						});
						// echo_message_js('Мы в группе. Удалить все прикреплённые связанные услуги');
					}else{
						var i = 0;						
						var group_services_num = 0;
						// перебираем DOM услуг
						methods.services_rows.each(function(index, el) {
							// get no group services
							if($(this).find('.service_group').length == 0){								
								delete_service_ids[i] = [];	
								delete_service_ids[i++] = $(this).attr('data-dop_uslugi_id');	
								$(this).remove();
							}else{
								group_services_num++;
							}							
						});

						if(group_services_num > 0){
							echo_message_js('В варианте расчета имеются связанные услуги, для их удаления пройдите в соответствующий тираж.');	
						}
						
					}
					console.log(delete_service_ids)
					methods.delete_services(delete_service_ids);
					
				})
				methods.services_itogo_row.find('.price.discount input').bind('keyup', function(event) {
					methods.save_main_discount($(this).val());
				});

				// инициализируем работу нижней части окна
				// methods.services_init();

				// кнопка сбросить всё
				methods.btn_cancel_all.bind('click.totalCommander', methods.cancel_all_choosen_variants );
				// добавить услугу
				methods.btn_calculators.bind('click.totalCommander', methods.calculator_add_services );

				methods.show();

				// загрузка контента default
				console.log(options)
				if(options != 'update'){
					console.log('default_var click()')
					methods.variants_tbody.find('.default_var').click();
				}else{
					console.log('654654')
					// обновляем контент услуг относительно выбранных вариантов
						methods.update_services_content();
						// инициализируем работу нижней части окна
						// methods.services_init();
						// поправка главного чекбокса группы
						methods.checkbox_main_check();
				}

				// подсчитывает стоимость в окне
				// methods.calc_price();
			});

		},
		/**
		 *	полное обновление окна
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:31 11.03.2016
		 */
		update_total_window:function(){

			var updater_ids = [],i = 0;
			methods.variants_tbody.find('.tr_checked').each(function(index, el) {
				updater_ids[i++] = $(this).attr('data-dop_row_id');
			});


			$.post('', {
			AJAX: 	'update_service_center',
			row_id: methods.variants_tbody.find('.default_var').attr("data-dop_row_id"),// default_row_id
			checked_rows: updater_ids
			}, function(data, textStatus, xhr) {
				if(data['myFunc'] !== undefined && data['myFunc'] == 'update_SC'){
					// обновление
					if($('#js-main_service_center').length == 0){
						$('body').append($('<div/>',{
							"id":'js-main_service_center'
						}).html(Base64.decode(data['html'])));
					}else{
						$('#js-main_service_center').html(Base64.decode(data['html']))
					}
					// инициализация
					$('#js-main_service_center').totalCommander('init','update');
				}				
				standard_response_handler(data);
			},'json');
		},
		/**
		 *	добавление услуг по id
		 *
		 *	@param 		id_s - Object id_dop_uslugi
		 *	@return  	Alexey Kapitonov
		 *	@version 	16:41 09.03.2016
		 */
		add_services:function( id_s ){
			// запрос информации по услугам
			$.post('', {
				AJAX:'get_new_services',
				id_s:id_s
			}, function(data, textStatus, xhr) {
				var new_services = [];
				new_services = data;
			


				for(var dop_row_id in new_services) {
					for(var i = 0, length1 = new_services[dop_row_id].length; i < length1; i++){
						var service_id = new_services[dop_row_id][i].id;

						// добавляем информацию в главный объект
						var len = methods.mainObj[dop_row_id]['services'].length;
						methods.mainObj[dop_row_id]['services'][len] = [];
						methods.mainObj[dop_row_id]['services'][len] = new_services[dop_row_id][i];
						// получаем id строки варианта, в которой будем править JSON
						var tr_id = '#dop_data_'+dop_row_id;
						// правим JSON в DOM в соответствии с mainObject
						var html_row = methods.variants_tbody.find(tr_id);
						html_row.find('td.js-variant_services_json div').html(JSON.stringify(methods.mainObj[dop_row_id]['services']));
						
						// правим количество услуг
						html_row.find('td span.service').html(methods.mainObj[dop_row_id]['services'].length);

						// устанваливаем зависимости
						// объект зависимостей услуг от вариантов
						console.log(methods.depending_on_the_services_and_options)
						methods.depending_on_the_services_and_options[new_services[dop_row_id][i]] = dop_row_id;
						// объект зависимостей вариантов от услуг
						console.log(methods.depending_on_the_options_and_services)
						methods.depending_on_the_options_and_services[dop_row_id][methods.depending_on_the_options_and_services[dop_row_id].length] = new_services[dop_row_id][i];
					}
				}
				// console.log(new_services);
			},'json');
		},
		/**
		 *	редактирование JSON услуги
		 *
		 *	@param    	id строки в базе
		 *	@param    	row_type тип строки определяет где именно будем переписывать данные
		 *  в услуге или в варианте
		 *	@param    	key путь в объекте из json
		 *	@param    	value подставляемое занчение
		 *	@author  	Alexey Kapitonov
		 *	@version 	12:27 09.03.2016
		 */
		edit_service_json:function(id, row_type, key, value){
			console.warn( id, row_type, key, value );
			switch (row_type) {
				case 'service':
					console.log(id,id.length)
					// перебираем отправленную к нам группу строк по услугам (по их id)
					for(var i = 0, length1 = id.length; i < length1; i++){
						// будем копать в строках 
						var tr_id = '#dop_data_'+methods.depending_on_the_services_and_options[id[i]];
						// места хранения JSON
						// var variantObjJson = methods.variants_tbody.find(tr_id+' td.js-variant_info div');
						// var variantServicesObjJson = methods.variants_tbody.find(tr_id+' td.js-variant_services_json div');
						var dop_row_id = methods.depending_on_the_services_and_options[id[i]];
						var index = 0;
						var flag = false;
						// перебираем соответствия (должно быть найдено одно!)
						for(var k = 0, length2 = methods.mainObj[dop_row_id]['services'].length; k < length2; k++){
							console.log(methods.mainObj[dop_row_id]['services'][k], id[i])
							if (methods.mainObj[dop_row_id]['services'][k].id == id[i]) {
								index = k; flag = true;
							}
						}
						// если соответствия найдены
						if(flag){
							// вносим изменения в mainObj 
							// вносим изменения в jsonObj в DOM в соответствии с mainObj
							methods.mainObj[dop_row_id]['services'][index][key] = value;							
							methods.variants_tbody.find(tr_id+' td.js-variant_services_json div').html(JSON.stringify(methods.mainObj[dop_row_id]['services']));
						}
					}
					break;

				case 'variant':
					// обрабатываем массив id_dop_data
					for(var i = 0, length1 = id.length; i < length1; i++){
						// будем копать в строках 
						var tr_id = '#dop_data_'+id[i];
						// места хранения JSON
						var variantObjJson = methods.variants_tbody.find(tr_id+' td.js-variant_info div');
						
						var dop_row_id = methods.depending_on_the_services_and_options[id[i]];
						
						// вносим изменения в mainObj 
						// вносим изменения в jsonObj в DOM в соответствии с mainObj
						methods.mainObj[id[i]]['variant'][key] = value;							
						methods.variants_tbody.find(tr_id+' td.js-variant_info div').html(JSON.stringify(methods.mainObj[id[i]]['variant']));
						
					}
					break;
				default:
					// statements_def
					break;
			}
			// echo_message_js('json edit')
		},
		/**
		 *	удаление услуги	
		 *
		 *	@param 		obj - service_row
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:36 09.03.2016
		 */
		delete_service:function(obj){
			var service = obj.parent();
			var service_ids = [];
			if(service.find('.service_group').length > 0){
				service_ids = service.find('.service_group').attr('data-id_s').split(',');
			}else{
				service_ids = service.attr('data-dop_uslugi_id').split(',');
			}			
			service.remove();
			// удаляем услуги
			methods.delete_services(service_ids,service);				
		},
		/**
		 *	удаление услуг	
		 *
		 *	@param 		arr - service_id
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:36 09.03.2016
		 */
		delete_services:function(service_ids){
			var delete_all = 0;

			$.post('', {
				AJAX:'delete_services',
				service_ids:service_ids
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
				if(data['response'] == 'OK'){
					

					// удаляем из mainObj
					// перебираем отправленную к нам группу строк по услугам (по их id)
					for(var i = 0, length1 = service_ids.length; i < length1; i++){
						// будем копать в строках
						console.log(methods.depending_on_the_services_and_options[service_ids[i]],methods.depending_on_the_services_and_options) 
						var tr_id = '#dop_data_'+methods.depending_on_the_services_and_options[service_ids[i]];
						// места хранения JSON
						// var variantObjJson = methods.variants_tbody.find(tr_id+' td.js-variant_info div');
						// var variantServicesObjJson = methods.variants_tbody.find(tr_id+' td.js-variant_services_json div');
						var dop_row_id = methods.depending_on_the_services_and_options[service_ids[i]];
						var index1 = 0;
						var flag = false; var num = 0;
						// перебираем соответствия (должно быть найдено одно!)
						for(var k in methods.mainObj[dop_row_id]['services']) {
						// for(var k = 0, length2 = methods.mainObj[dop_row_id]['services'].length; k < length2; k++){
							console.log(methods.mainObj[dop_row_id]['services'][k], service_ids[i])
							// num++;
							if (methods.mainObj[dop_row_id]['services'][k].id == service_ids[i]) {
								index1 = k; flag = true;
								// num-1;
							}else {
								num++;
							}
						}
						// если соответствия найдены
						if(flag){
							// вносим изменения в mainObj 
							delete methods.mainObj[dop_row_id]['services'][index1];	
							methods.mainObj[dop_row_id]['services'].splice(index1, 1)
													
							// редактируем количество услуг в варианте
							// console.log(methods.variants_tbody.find(tr_id+' td:nth-of-type(7) span'))
							methods.variants_tbody.find(tr_id+' td:nth-of-type(7) span').html(num);
							console.log(num)
							// удаляем зависимости
							// 1
							delete methods.depending_on_the_services_and_options[service_ids[i]];
							// 2
							var f = false;var ind = 0;
							for(var d = 0, length3 = methods.depending_on_the_options_and_services[dop_row_id].length; d < length3; d++){
								if(methods.depending_on_the_options_and_services[dop_row_id][d].id == service_ids[i]){
									ind = d;f = true;
								}
							}
							if(ind){
								delete methods.depending_on_the_options_and_services[dop_row_id][d];
							}


							console.log('methods.services_rows.length >>> ', methods.services_rows.length)
							
							// вносим изменения в jsonObj в DOM в соответствии с mainObj
							methods.variants_tbody.find(tr_id+' td.js-variant_services_json div').html(JSON.stringify(methods.mainObj[dop_row_id]['services']));
							if (methods.mainObj[dop_row_id]['services'].length == 0) {
								delete_all = 1;
							};
						}


					}

					

					// редактируем JSON
					console.log('массив ID от строк dop_uslugi',service_ids)
					// обновляем контент услуг относительно выбранных вариантов
					methods.update_services_content();
					// инициализируем работу нижней части окна
					methods.services_init();

					
					// если мы находимся в группе и в ней были удалены все услуги
					if (methods.top_menu_div.find('.checked').index()>0 && methods.services_rows.length == 0) {
						// удаляем вкладку группы
						methods.top_menu_div.find('.checked').remove();
						methods.top_menu_div.find('li').eq(0).addClass('checked');
						// загрузка контента default
						methods.variants_tbody.find('.default_var').click();
						
						// подсчитывает стоимость в окне
						methods.calc_price();
						return false;
					}

					
				}				
				
			},'json');				
		},

		// сохранение общей скидки
		save_main_discount:function(value){
			console.info('меняем общую скидку на ', value );
			// console.group("Overlord");

			// var a = 1, b = "1";
			// console.assert(a === b, "A doesn't equal B");


			// перебор вариантов
			methods.change_obj = {
				varians: [],
				services: []
			}; 
			var i = 0;
			var savedVariantsIds = []; // содержит id_dop_data изменяемых строк
			// работаем с вариантами
			methods.services_variants_rows.each(function(index, el) {
				var dop_data_id = $(this).attr('data-dop_data_id');
				methods.change_obj['varians'][i] = [];
				savedVariantsIds[i] = dop_data_id;
				methods.change_obj['varians'][i++][dop_data_id] = value;
				

				

				// редактируем поле discount
				$(this).find('.price.discount input').val(value);

				// собираем данные
				methods.calc_row = {
					price_out: {
						for_one: 	$(this).find('.price.price_out .for_one span'),
						for_all: 	$(this).find('.price.price_out .for_all span')
					},
					price_out_width_discount: {
						for_one: 	$(this).find('.price.price_out_width_discount .for_one span'),
						for_all: 	$(this).find('.price.price_out_width_discount .for_all span'),
					},
				}


				// расчитываем скидку и правим DOM
				methods.calc_row.price_out_width_discount.for_one.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_one.html(), value)));
				methods.calc_row.price_out_width_discount.for_all.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_all.html(), value)));
			});
			
			// перебор услуг
			var k=0; i=0;
			var savedServicesIds = []; // содержит id_dop_data изменяемых строк
			methods.services_rows.each(function(index, el) {
					
				var id_s = [];
				if($(this).find('.service_group').length > 0){
					// работаем с группой
					id_s = $(this).find('.service_group').attr('data-id_s').split(',');
				}else{
					// работаем с одной услугой
					id_s[0] = $(this).attr('data-dop_uslugi_id');
				}

				for(var i = 0, length1 = id_s.length; i < length1; i++){
					savedServicesIds[k++] = id_s[i];
				}

				// правим и обсчитываем DOM
				methods.change_obj['services'][i] = [];
				methods.change_obj['services'][i++][$(this).attr('data-dop_uslugi_id')] = value;
				// редактируем поле discount
				$(this).find('.price.discount input').val(value);

				// собираем данные
				methods.calc_row = {
					price_out: {
						for_one: 	$(this).find('.price.price_out .for_one span'),
						for_all: 	$(this).find('.price.price_out .for_all span')
					},
					price_out_width_discount: {
						for_one: 	$(this).find('.price.price_out_width_discount .for_one span'),
						for_all: 	$(this).find('.price.price_out_width_discount .for_all span'),
					},
				}
				// расчитываем скидку и правим DOM
				methods.calc_row.price_out_width_discount.for_one.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_one.html(),value)));
				methods.calc_row.price_out_width_discount.for_all.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_all.html(),value)));
			});

			// подсчитывает стоимость в окне
			methods.calc_price('discount');
			
			console.warn('ДЕЛАТЬ ТУТ >>> ',methods.change_obj);
			// echo_message_js('1) Пишем запрос сохранения общей скидки для всех строк');
			// echo_message_js('2) описать изменения json в верхней таблице, ведь оттуда берутся данные и при перезагрузке они вернуться к неверному значению');
			

			// сохраняем mainObj и json по вариантам
			// console.log(savedVariantsIds,savedServicesIds);
			methods.edit_service_json(savedVariantsIds,'variant','discount',value);
			// сохраняем mainObj и json по услугам
			methods.edit_service_json(savedServicesIds,'service','discount',value);
			// сохраняем на сервере
			$.post('', {
				AJAX:'save_main_discount',
				dop_data_ids:savedVariantsIds,
				services_ids:savedServicesIds,
				value:value
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');	

		},
		// сохранение скидки для строки варианта или услуги
		save_discount:function(obj, id, type, value){
			
			$.post('', {
				AJAX: 'save_discount',
				row_id:id,
				type:type,
				value:value
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');

			methods.calc_row = {
				price_out: {
					for_one: 	obj.parent().prev().find('.for_one span'),
					for_all: 	obj.parent().prev().find('.for_all span')
				},
				price_out_width_discount: {
					for_one: 	obj.parent().next().find('.for_one span'),
					for_all: 	obj.parent().next().find('.for_all span'),
				},
			}

			// расчитываем скидку и правим DOM
			methods.calc_row.price_out_width_discount.for_one.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_one.html(),value)));
			methods.calc_row.price_out_width_discount.for_all.html(round_money( methods.calc_price_width_discount(methods.calc_row.price_out.for_all.html(),value)));

			// подсчитывает итого
			methods.calc_price();

			// правим json в верхнем окне
			switch (type) {
				case 'service':
					// service_id, key, value 
					if(obj.parent().parent().find('.service_group').length>0){
						// console.log(obj.parent().parent())
						var id_row = obj.parent().parent().find('.service_group').attr('data-id_s').split(',');
					}else{
						var id_row = [];
						id_row[0] = id;	
					}
					break;
				case 'variant':
					var id_row = [];
					id_row[0] = id;	
					break;
				default:
					// statements_def
					break;
			}

			// редактируем данные в json
			methods.edit_service_json(id_row,type,'discount',value);
		},
		// расчет исходящей стоимости относительноdiscount
		calc_price_width_discount:function (price_out, discount) {
			 return Number(price_out/100) * (100 + Number(discount));
		},
		/**
		 *	возвращает название группы
		 *
		 *	@param 		service_id
		 *	@return  	string
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:57 14.03.2016
		 */
		get_group_name:function(united_calculations){
			var service_arr = united_calculations.split(',');

			var dop_row_id = [];
			var r = 0;
			for(var i in service_arr) {
				if(methods.depending_on_the_services_and_options[service_arr[i]]){
					dop_row_id[r++] = methods.depending_on_the_services_and_options[service_arr[i]];	
				}else{
					return "";
				}				
			}
			return dop_row_id.join('_');
		},
		// пересчёт итого
		calc_price:function(no_discount){
			// return;
			methods.service_itogo = {
				num_rows: 0,				
				discount: 0,
				price_in: {
					for_one:0,
					for_all:0
				},
				price_out: {
					for_one:0,
					for_all:0
				},
				price_out_width_discount: {
					for_one:0,
					for_all:0
				},
			};

			methods.services_variants_rows.each(function(index, el) {
				// входящая за единицу товара
				methods.service_itogo.price_in.for_one += Number($(this).find('.price.price_in .for_one span').html());
				// входящая за все
				methods.service_itogo.price_in.for_all += Number($(this).find('.price.price_in .for_all span').html());
				// исходящая за единицу товара
				methods.service_itogo.price_out.for_one += Number($(this).find('.price.price_out .for_one span').html());
				// исходящая за все
				methods.service_itogo.price_out.for_all += Number($(this).find('.price.price_out .for_all span').html());

				// исходащая за единицу товара
				methods.service_itogo.price_out_width_discount.for_one += Number($(this).find('.price.price_out_width_discount .for_one span').html());
				// исходящая за все
				methods.service_itogo.price_out_width_discount.for_all += Number($(this).find('.price.price_out_width_discount .for_all span').html());
				// суммируем скидку
				methods.service_itogo.discount += Number($(this).find('.price.discount input').val());
				// console.log('discount = ',$(this).find('.price.discount span').html())
				// считаем количество обработанных строк
				methods.service_itogo.num_rows++;
			});
			methods.services_rows.each(function(index, el) {
				// входящая за единицу товара
				methods.service_itogo.price_in.for_one += Number($(this).find('.price.price_in .for_one span').html());
				// входящая за все
				methods.service_itogo.price_in.for_all += Number($(this).find('.price.price_in .for_all span').html());
				// исходящая за единицу товара
				methods.service_itogo.price_out.for_one += Number($(this).find('.price.price_out .for_one span').html());
				// исходящая за все
				methods.service_itogo.price_out.for_all += Number($(this).find('.price.price_out .for_all span').html());

				// исходащая за единицу товара
				methods.service_itogo.price_out_width_discount.for_one += Number($(this).find('.price.price_out_width_discount .for_one span').html());
				// исходящая за все
				methods.service_itogo.price_out_width_discount.for_all += Number($(this).find('.price.price_out_width_discount .for_all span').html());
				
				// суммируем скидку
				// console.log('discount = ',$(this).find('.price.discount span').html())
				methods.service_itogo.discount += Number($(this).find('.price.discount input').val());

				// считаем количество обработанных строк
				methods.service_itogo.num_rows++;
			});

			if(methods.service_itogo.discount == 0){
				methods.service_itogo.discount = '0.00';
			}else{
				methods.service_itogo.discount = round_money(methods.service_itogo.discount / methods.service_itogo.num_rows);
			}
			
			// console.log('methods.service_itogo = ', methods.service_itogo)
			// вывод данных в итого
			methods.services_itogo_row.find('.price.price_in .for_one span').html(round_money(methods.service_itogo.price_in.for_one));
			methods.services_itogo_row.find('.price.price_in .for_all span').html(round_money(methods.service_itogo.price_in.for_all));

			methods.services_itogo_row.find('.price.price_out .for_one span').html(round_money(methods.service_itogo.price_out.for_one));
			methods.services_itogo_row.find('.price.price_out .for_all span').html(round_money(methods.service_itogo.price_out.for_all));

			methods.services_itogo_row.find('.price.price_out_width_discount .for_one span').html(round_money(methods.service_itogo.price_out_width_discount.for_one));
			// console.log('methods.service_itogo.price_out_width_discount.for_all',methods.service_itogo.price_out_width_discount)
			methods.services_itogo_row.find('.price.price_out_width_discount .for_all span').html(round_money(methods.service_itogo.price_out_width_discount.for_all));

			no_discount = no_discount || 'auto';
			if(no_discount == 'auto'){
				methods.services_itogo_row.find('.price.discount input').val(round_money(methods.service_itogo.discount));	
			}
			

			// console.log(methods.services_itogo_row.find('.price.discount span').html())

			// console.info('methods.service_itogo',methods.service_itogo)
			// console.log( 'round_money = 65401232,0000355', round_money(65401232.0000355) )
		},
		// 
		services_init:function(){			
			
			methods.services_variants_rows =	methods.services_tbl.find('tr.variant');
			methods.services_rows  = 			methods.services_tbl.find('tr.service');
		},
		// создает простое модальное окно
		create_small_dialog:function(html, title, buttons){
			// проверяем 
			var html1 = (html !== undefined)?html:'текст не был передан';
			var title1 = (title !== undefined)?title:'имя окна не было передано';
			var new_buttons = (buttons !== undefined)?buttons:[];

			// убиваем такое окно, если оно есть
			if($('#js-alert_union').length > 0){
				$('#js-alert_union').remove();
			}
			// создаем новое
			$('body').append($('<div/>',{
			"id":'js-alert_union',
			"style":"height:45px;",
					'html':html1
					}));	
							
					
					$('#js-alert_union').dialog({
					    width: 'auto',
					    height: 'auto',
					    modal: true,
					    title : title1,
					    autoOpen : true,
					    beforeClose: function( event, ui ) {
					    	// перезагрузка RT
					    	// $.SC_reload_RT_content();
					    },
					    closeOnEscape: false
					    // buttons: buttons          
					}).parent();

					
					var buttons_html = $('<table></table>');

					var button;
					// console.log(new_buttons)
					for(var i = 0, length1 = new_buttons.length; i < length1; i++){
						button = $('<button/>',{
								text: new_buttons[i]['text'],
								click: new_buttons[i]['click']
							});
						if(new_buttons[i]['class'] !== undefined){
							button.attr('class',new_buttons[i]['class'])
						}
						if(new_buttons[i]['id'] !== undefined){
							button.attr('id',new_buttons[i]['id'])
						}
						buttons_html.append(
							$('<td/>')
							.append(button)
						);				
					}


					$('#js-alert_union').after($('<div/>',{'id':'js-alert_union_buttons','class':'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'}).append( buttons_html));
		},
		calculator_add_variant:function(obj){
			var i = 0,ind2 = 0;
			delete methods.dataObj;
			

			methods.dataObj = []; 
			methods.services_rows.each(function(index, el) {

				methods.dataObj[ind2] = []; 					// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
				methods.dataObj[ind2]['action'] = 'attach';	// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
				methods.dataObj[ind2]['type'] = '';			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
				methods.dataObj[ind2]['usluga_id'] = [];		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
				methods.dataObj[ind2]['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
				methods.dataObj[ind2]['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж
				methods.dataObj[ind2]['art_id'] = [];			// art_id - string	

				var row = methods.variants_tbody.find('tr#dop_data_'+obj.attr('data-dop_row_id'))
				methods.dataObj[ind2]['dop_data_ids'][0] = row.attr('data-dop_row_id') ;
				methods.dataObj[ind2]['quantity'][0] = row.attr('data-quantity') ;
				methods.dataObj[ind2]['art_id'][0] = row.attr('data-art_id') ;

				// собираем информацию по сгруппированным услугам
				var ind = 0 ;
				//console.log(methods.services_rows);
				
				methods.dataObj[ind2]['usluga_id'] = [];
				methods.dataObj[ind2]['usluga_id'] = $(this).find('.service_group').attr('data-id_s').split(',');
				methods.dataObj[ind2]['calculator_type'] = $(this).attr('data-calculator_type');
				ind2++;
			});
			// console.info('добавить вариант из группы >>>', methods.dataObj);
			// вызов калькулятора
			
			console.log(JSON.stringify(methods.dataObj),methods.dataObj)

			// printCalculator.startCalculator(methods.dataObj);
		},

		/*
			Добрый день, 

		*/
		
		calculator_remove_variant:function(){
			var i = 0,so = 0;
			methods.dataObjSuper = [];
			// собираем информацию по сгруппированным услугам
			var ind = 0 ;
			methods.dataObj = []; 
			methods.services_rows.each(function(index, el) {
				
									// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
				methods.dataObj[so]['action'] = 'detach'; 	// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
				methods.dataObj[so]['type'] = '';			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
				methods.dataObj[so]['usluga_id'] = '';		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
				methods.dataObj[so]['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
				methods.dataObj[so]['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж
				methods.dataObj[so]['art_id'] = [];			// art_id - string	

				// собираем id строк вариантов
				methods.variants_tbody.find('tr.tr_checked').each(function(index, el) {
					methods.dataObj[so]['dop_data_ids'][index] = $(this).attr('data-dop_row_id') ;
					methods.dataObj[so]['quantity'][index] = $(this).attr('data-quantity') ;
					methods.dataObj[so]['art_id'][index] = $(this).attr('data-art_id') ;
					i++;
				});

			
				methods.dataObj[so]['usluga_id'][ind] = [];
				methods.dataObj[so]['usluga_id'][ind] = $(this).find('.service_group').attr('data-id_s').split(',');
				methods.dataObj[so]['calculator_type'] = $(this).attr('data-calculator_type');

				so++;
				// console.log($(this).find('.service_group').attr('data-id_s').split(','));				
			});

			console.info('удалить вариант из группы >>>',methods.dataObj);
			// вызов калькулятора
			printCalculator.startCalculator(methods.dataObj);
		},
		// вызов калькулятора на кнопку "Добавить услугу"
		calculator_add_services:function(){
			var i = 0;
			methods.dataObj = []; 
			methods.dataObj[0] = []; 					// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
			methods.dataObj[0]['action'] = 'new'; 		// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
			methods.dataObj[0]['type'] = '';			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
			methods.dataObj[0]['usluga_id'] = [];		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
			methods.dataObj[0]['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
			methods.dataObj[0]['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж
			methods.dataObj[0]['art_id'] = [];			// art_id - string	

			// собираем id строк вариантов
			methods.variants_tbody.find('tr.tr_checked').each(function(index, el) {
				methods.dataObj[0]['dop_data_ids'][index] = $(this).attr('data-dop_row_id') ;
				methods.dataObj[0]['quantity'][index] = $(this).attr('data-quantity') ;
				methods.dataObj[0]['art_id'][index] = $(this).attr('data-art_id') ;
				i++;
			});

			// если выбрано несколько услуг 
			if(i>1){
				console.log('заглушка вызова объединенного тиража >> start');
				methods.calculator_type = "none";
				console.info('Будет произведён вызов калькулятора для разных макетов!!!');
				console.log('заглушка вызова объединенного тиража >> end');

				// если ещё не был выбран тип окна
				if( !methods.calculator_type ){				
					var html = 'Вы добавляете услугу для нескольких артикулов<br>Печать этих артикулов будет производиться с:';
					var title = 'Уточните условие';	
					var buttons = [];
					buttons.push({
					    text: 'одного макета',
					    class:'',
					    id:  '',
					    click: function() {
					    	methods.calculator_type = "union";
					    	methods.calculator_add_services();
					    	$('#js-alert_union').dialog('destroy').remove();  		    	
					    }
					});
					buttons.push({
					    text: 'разных макетов',
					    id:   '',
					    click: function() {
					    	methods.calculator_type = "none";
					    	methods.calculator_add_services();
					    	$('#js-alert_union').dialog('destroy').remove();  		    	
					    }
					});

					methods.create_small_dialog(html,title,buttons);
					
					return false;
				}else{
					if(methods.calculator_type == "union"){
						methods.dataObj[0]['type'] = methods.calculator_type;	
					}
					
					delete methods.calculator_type;
					console.info('ДОБАВЛЯЕМ НОВУЮ УСЛУГУ >>>',methods.dataObj);
					// вызов калькулятора
					printCalculator.startCalculator(methods.dataObj);	
				}
			}else{
				console.info('ДОБАВЛЯЕМ НОВУЮ УСЛУГУ >>>',methods.dataObj);
				// вызов калькулятора
				printCalculator.startCalculator(methods.dataObj);
			}

					
		},
		// клик по названию услуги в списке
		calculator_edit_the_service:function(obj){
			var i = 0;
			methods.dataObj = [];
			methods.dataObj[0] = []; 					// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
			methods.dataObj[0]['action'] = 'update'; 		// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
			methods.dataObj[0]['type'] = '';			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
			methods.dataObj[0]['usluga_id'] = [];		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
			methods.dataObj[0]['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
			methods.dataObj[0]['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж
			methods.dataObj[0]['art_id'] = [];			// art_id - string

			if(obj.parent().find('.service_group').length == 0){
				methods.dataObj[0]['usluga_id'] = obj.parent().attr('data-dop_uslugi_id').split(',');
			}else{
				methods.dataObj[0]['usluga_id'] = obj.parent().find('.service_group').attr('data-id_s').split(',');
			}
			// calculator_type
			methods.dataObj[0]['calculator_type'] = obj.parent().attr('data-calculator_type');
			methods.dataObj[0]['discount'] = Number(obj.parent().find('.price.discount input').val());

			// собираем id строк вариантов
			methods.variants_tbody.find('tr.tr_checked').each(function(index, el) {
				methods.dataObj[0]['dop_data_ids'][index] = $(this).attr('data-dop_row_id') ;
				methods.dataObj[0]['quantity'][index] = $(this).attr('data-quantity') ;
				methods.dataObj[0]['art_id'][index] = $(this).attr('data-art_id') ;
				i++;
			});

			if(i>1){
				methods.dataObj[0]['type'] = 'union';
			}

			// console.info('РЕДАКТИРУЕМ УСЛУГУ >>>',methods.dataObj);
			// вызов калькулятора
			printCalculator.startCalculator(methods.dataObj);		
		},
		// сбросить выбранные checkbox
		cancel_all_choosen_variants:function(){
			// верхнее меню, раздел артикулы
			methods.top_menu.removeClass('checked').eq(0).addClass('checked');

			methods.variants_rows
				.removeClass('tr_checked')
				.find('td:nth-of-type(2).checked')
				.removeClass('checked');

			methods.variants_tbody.find('.default_var').addClass('tr_checked').find('.hover_group_class').removeClass('.hover_group_class')

			methods.checkbox_main.parent().removeClass('checked-before').removeClass('checked');
			// обновление блока услуг
			methods.update_services_content();
			// скролл к default
			setTimeout(methods.scroll_to_default,500);
			// скрываем кнопку
			methods.btn_cancel_all.addClass('no_click');
		},
		// сбросить группу и перейти к определённой строке
		cancel_all_choosen_variants_and_chose_one_row:function(row){
			methods.variants_rows
				.removeClass('tr_checked')
				.find('td:nth-of-type(2).checked')
				.removeClass('checked')
				.parent().find('.hover_group_class').removeClass('hover_group_class');
			row.addClass('tr_checked');

			methods.checkbox_main.parent().removeClass('checked-before').removeClass('checked');
			// обновление блока услуг
			methods.update_services_content();
			// скролл к default
			setTimeout(methods.scroll_to_default,500);
			// скрываем кнопку
			methods.btn_cancel_all.addClass('no_click');
		},
		// клик по строке 
		variants_rows_choose:function(e){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length == 0){
				var object= this;
				if(object.className == 'tr_checked'){
					// object.className = '';
				}else{
					methods.variants_rows.removeClass('tr_checked');
					if (object.className == 'tr_checked default_var' || object.className == 'default_var') {
						object.className = 'tr_checked default_var';
					}else{
						object.className = 'tr_checked';
					}
					
				}	
			}
			// обновление блока услуг
			methods.update_services_content();
			
		},
		// клик по чекбоксу в строке
		checkbox_change: function(obj){

			// проверяем в группе ли мы
			if(methods.top_menu_div.find('li.checked').attr('data-var_id') && methods.top_menu_div.find('li.checked').attr('data-var_id').split(',').length>1 ){
				console.log('ставим заглушку');
				return false;


				// если отжали чекбокс
				if(obj.parent().parent().hasClass('tr_checked') && obj.parent().hasClass('checked')){
					var html = 'Удалить из связанного тиража '+methods.top_menu_div.find('li.checked div').html()+' эту позицию<br>и пересчитать стоимость печати?';
					var title = 'Уточните условие';	
					var go_calculator_methods = methods.calculator_remove_variant;		
				}else{
					var html = 'Добавить в связанный тираж '+methods.top_menu_div.find('li.checked div').html()+' эту позицию<br>и пересчитать стоимость печати?';
					var title = 'Уточните условие';	
					var go_calculator_methods = methods.calculator_add_variant;	
				}						
				// проверка ответ в окне
				if(!methods.confirm){
					
					var buttons = [];
					buttons.push({
					    text: 'Да',
					    class:  'button_yes_or_no yes',
					    click: function() {
					    	methods.confirm = "yes";
					    	methods.checkbox_change(obj);
					    	$('#js-alert_union').dialog('destroy').remove();  		    	
					    }
					});
					buttons.push({
					    text: 'Нет',
					    class:  'button_yes_or_no no',
					    click: function() {
					    	methods.confirm = "none";
					    	methods.checkbox_change(obj);
					    	$('#js-alert_union').dialog('destroy').remove();  		    	
					    }
					});
					methods.create_small_dialog(html,title,buttons);						
					return false;
				}else{
					// если ответ положительный
					if(methods.confirm == 'yes'){
						// меняем checkbox
						go_calculator_methods(obj.parent().parent());						
						change();
						
					}
					delete methods.confirm;						
				}	
								
			}else{
				change();
			}
			
			function change(){
				// мы в артикулах
				// отработка чекбокс
				if(obj.parent().parent().hasClass('tr_checked') && obj.parent().hasClass('checked')){
					obj.parent().removeClass('checked').parent().removeClass('tr_checked');
				}else{
					obj.parent().addClass('checked').parent().addClass('tr_checked');
					// перебираем остальные (отключение еподсветки без чекбокса)
					methods.variants_rows.each(function(index, el) {
						if($(this).hasClass('tr_checked') && !$(this).find('td').eq(1).hasClass('checked')){
							$(this).removeClass('tr_checked');
						}
					});
				}
				// поправка главного чекбокса группы
				methods.checkbox_main_check();
			}
		},
		// проверка и правка главного чекбокса
		checkbox_main_check: function(){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length == methods.variants_rows.length){
				methods.checkbox_main.parent().attr('class','checked');
				// показываем кнопку
				methods.btn_cancel_all.removeClass('no_click');
			}else if(methods.variants_rows.find('td:nth-of-type(2).checked').length > 0){
				methods.checkbox_main.parent().attr('class','checked-before');
				// показываем кнопку
				methods.btn_cancel_all.removeClass('no_click');
			}else{
				methods.checkbox_main.parent().removeAttr('class');
				// скрываем кнопку
				methods.btn_cancel_all.addClass('no_click');
			}
			// console.log(methods.btn_cancel_all);
			// обновление блока услуг
			methods.update_services_content();
		},
		// клик по главному checkbox
		checkbox_main_click : function(e){
			if(methods.variants_rows.find('td:nth-of-type(2).checked').length){
				// сбросить все выбранные checkbox
				methods.cancel_all_choosen_variants();
				// показываем кнопку
				methods.btn_cancel_all.addClass('no_click');
			}else{
				methods.variants_rows.addClass('tr_checked').find('td:nth-of-type(2)').addClass('checked');
				methods.checkbox_main.parent().addClass('checked');
				// скрываем кнопку
				methods.btn_cancel_all.removeClass('no_click');
			}
			// обновление блока услуг
			methods.update_services_content();
		},
		// редатирование комментария к услуге
		edit_service_comments:function(obj){
			console.log(obj);
			var id = obj.parent().attr('data-dop_uslugi_id').split(',');

			if (obj.next().attr('data-id_s')) {
				id = obj.next().attr('data-id_s').split(',');
			}

			var buttons = [];
			buttons.push({
			    text: 'Сохранить',
			    'class':'save',
			    click:function(){
			    	// закрываем окно при клике
			    	$('#js-alert_union').dialog('destroy').remove(); 
			    }
			});


			var i = 0;
			var service_id = id[i];
			var dop_row_id = obj.parent().attr('data-dop_data_id');
			var tr_id = '#dop_data_'+methods.depending_on_the_services_and_options[id[i]];
			// места хранения JSON
			var index = 0;
			var flag = false;
			// перебираем соответствия (должно быть найдено одно!)
			for(var k = 0, length2 = methods.mainObj[dop_row_id]['services'].length; k < length2; k++){
				console.log(methods.mainObj[dop_row_id]['services'][k], id[i])
				if (methods.mainObj[dop_row_id]['services'][k].id == id[i]) {
					index = k; flag = true;
				}
			}

			console.log(methods.mainObj[dop_row_id]['services'][index]);

			var html = $('<textarea/>',{
				'style':'width:250px;height:150px;',
				'val':Base64.decode(methods.mainObj[dop_row_id]['services'][index]['tz']),
				keyup:function(){
					var value = Base64.encode($(this).val());

					// устанавливаем зависимость иконки от заполнения 
					if(value != ""){
						obj.addClass('is_full');
					}else{
						obj.removeClass('is_full');
					}

					// редактируем данные json
					methods.edit_service_json(id, 'service', 'tz', value);
					$.post('', {
						AJAX:'save_coment_tz',
						'value':value,
						'ids':id,
					}, function(data, textStatus, xhr) {
						standard_response_handler(data);
					},'json');
				}
			})

			methods.create_small_dialog(html,'Редактор комментариев',buttons)
			console.log('Вызов окна комментариев');
		},
		// всплывающее окно м доп информацией по группе услуг
		get_service_notify:function(serv_id){
			// serv_id - объект с id строк услуг
			var content = $('<table>',{'class':"notify-table"});

			var tr = $('<tr/>');
			tr.append($('<th/>',{'text':'позиция','colspan':'2'}))
				.append($('<th/>',{'text':'артикул'}))
				.append($('<th/>',{'text':'номенклатура'}))
				.append($('<th/>',{'text':'тираж'}))


			content.append(tr);

			// var tr = $('<tr/>');
			// tr.append($('<th/>',{'text':serv_id,'colspan':'5'}))

			content.append(tr);

			var quantity_notify_all = 0;
			for(var k = 0, length1 = serv_id.length; k < length1; k++){
				var tr = $('<tr/>');
				// console.log(methods.variants_tbody.find('tr#dop_data_'+serv_id[k]))
				var variant_tr = methods.variants_tbody.find('tr#dop_data_'+methods.depending_on_the_services_and_options[serv_id[k]]);
				// var variant _info
				var quantity_notify = Number(variant_tr.find('td:nth-of-type(9) span').html());
				tr
				// номер позиции
				.append($('<td/>',{'text':variant_tr.find('td').eq(2).html()}))
				// номер варианта
				.append($('<td/>',{'text':variant_tr.find('td').eq(4).attr('data-no_short')}))
				// артикул
				.append($('<td/>',{'text':variant_tr.find('td').eq(5).html()}))
				// номенклатура
				.append($('<td/>',{'text':variant_tr.find('td').eq(7).html()}))
				// тираж							
				.append($('<td/>',{'text':quantity_notify+' шт'}))

				quantity_notify_all += quantity_notify;
				content.append(tr);
			}

			// итого
			var tr = $('<tr/>');
			tr.append($('<td/>',{'text':'ОБЪЕДИНЁННЫЙ ТИРАЖ','colspan':'4','style':'text-align:right'}))
				.append($('<td/>',{'text':quantity_notify_all+' шт'}))
			content.append(tr);

			// console.log('content >> ',quantity_notify_all)
			return content;
		},

		// скролл к дефаултному варианту
		scroll_to_default:function(){
			// подсчитываем скроллинг
			var destination = 0; var destination_v1 = 0; var flag = true;
			// console.log(methods.variants_rows)
			methods.variants_rows.each(function(index, el) {
				if(flag){
					destination += $(this).height();
					if($(this).find('td').eq(4).html() == 'в1'){
						destination_v1 = destination;
					}	
					// если достигли нужного элемента
					if($(this).hasClass('tr_checked')){
						// закрываем 
						flag = false;
					}
					// console.log(destination_v1)
				}
			});
			$("#js-main-service_center-variants-div-table div#wraper_classer").animate({scrollTop: destination_v1+'px'}, 200);
		},
		// показать окно
		show : function( ) {
    		$('#js-main_service_center').dialog('open');

    		// подгоняем ширину столбика тираж
    		var width = $('#js-main-service_center-variants-services-div-table').innerWidth() - 
    					$('#js-main-service_center-variants-services-div-table th:nth-of-type(1)').innerWidth() - 
    					$('#js-main-service_center-variants-services-div-table th:nth-of-type(2)').innerWidth() + 25
    		$('#js-main-service_center-variants-table th:last-child').width(width);

    		// меняем высоту, если она превышает допустимую
    		var top_block_height = ($(window).innerHeight()) / 3;
    		if($('#js-main-service_center-variants-table').innerHeight() < top_block_height){
    			top_block_height = $('#js-main-service_center-variants-table').innerHeight();
    		}
    		setTimeout(function(){
				if ($( "#js-main-service_center-variants-div-table" ).height() != top_block_height) {
					$( "#js-main-service_center-variants-div-table" ).animate({height:top_block_height+'px'},'1500', function(){
						// $(this).css({'overflowY':'scroll','overflowX':'hidden'});
					});		
				}
				methods.scroll_to_default();
			},500);

			

			// подключаем ресайз блока вариантов
			$( "#js-main-service_center-variants-div-table" ).resizable({
				
				stop:function(event, ui){
					var width = $('#js-main-service_center-variants-div-table').width();
					if(ui.size.height > $('#js-main-service_center-variants-table').innerHeight()){
						// $('#js-main-service_center-variants-div-table').css({'overflowY':'hidden','overflowX':'hidden'});
						$('#js-main-service_center-variants-table').width(width);
					}else{
						// $('#js-main-service_center-variants-div-table').css({'overflowY':'scroll','overflowX':'hidden'});
						$('#js-main-service_center-variants-table').width(width-16);
					}
				}
			});
			// $( "#js-main-service_center-variants-div-table .ui-resizable-handle" ).css('bottom','-57px');

		},
		// уничтожает окно
		hide : function( ) {
    		$('#js-main_service_center').dialog('destroy').remove();
    		this.remove();
		},
		// фильтр услуг
		filter_services_from_variants:function(service_arr){

			// выбираем объекты строк вариантов
			methods.checked_variants = methods.services_tbl.find('.variant');
			// ФИЛЬТР УСЛУГ для выгрузки 
			if (methods.checked_variants.length>1) {
				// выбираем только групперованые услуги
				var service = [];
				var k = 0;
				// var group_name = [
				// 	quantity : 0
				// ];
				
				// выбираем только услуги с группами
				for (var i = service_arr.length-1; i >= 0; i--) {
					if(service_arr[i].united_calculations || service_arr[i].united_calculations !== null){
						service[ k ] = [];
						service[ k++ ] = service_arr[i];
					}
				}

    			// console.log(methods.checked_variants_id)

				// получаем группы
				k = 0;
				service_arr = service; var service = [];

				

				// проверка сгрупированнных услуг на принадлежность 
				// к выбранной группе товаров(вариантов) 
				function checking_service(service){
					var g = true;
					var sirvices_rel  = service.united_calculations.split(',')
					for(var ArrVal in methods.checked_variants_id) {
						// console.log('methods.checked_variants_id[ArrVal]',methods.checked_variants_id[ArrVal],sirvices_rel)
						
						if (g == true){
							g = false;
							var flag = true;

							for(var i = 0, length1 = sirvices_rel.length; i < length1; i++){
								if(flag == true){
									if(methods.checked_variants_id[ArrVal]){
										for(var is = 0, length2 = methods.checked_variants_id[ArrVal].length; is < length2; is++){
											if(methods.checked_variants_id[ArrVal][is] == sirvices_rel[i]){
												g = true;
												flag = false;
												// console.log('search in '+methods.checked_variants_id[ArrVal][is]+' ** '+sirvices_rel[i], g, flag);
											}
										}
									}
								}
							}
						}						
					}
					// console.log(g)
					return g;
				}

				// перебираем и проверяем принадлежность к группе
				// расфасовываем по группам
				var services_arr2 = [],kk = 0;
				for (var i = service_arr.length-1; i >= 0; i--) {
					if(checking_service(service_arr[i])){
						var key = service_arr[i].united_calculations.split(',').join('_');
						if(!services_arr2[ key ]){
							// var key = service_arr[i].united_calculations.split(','),join('_');	
							services_arr2[ key ] = [];
							services_arr2[ key ][k] = [];
						}
						services_arr2[ key ][k++] = service_arr[i];	
						// service[ kk ] = [];
						// service[ kk++ ] = service_arr[i];
					}					
				}
				k=0;
				// console.log(services_arr2)
				for(var key in services_arr2) {
					var quantity = 0;
					service[ k ] = [];
					for(var i in services_arr2[key]) {
						quantity = Number(services_arr2[key][i].quantity)+quantity;
						service[ k ] = services_arr2[key][i];
					}
					service[ k++ ].quantity = quantity;
				}
				// console.log(service)
			}else{
				// выводим все
				var service = service_arr;
			}

			// если есть услуги для выгрузки - показываем текст шапки в таблице услуг
			if(service.length > 0){
				methods.services_tbl.find('.service_th.js-service_spacer').removeClass('js-service_spacer');
			}else{
				methods.services_tbl.find('.service_th').addClass('js-service_spacer');
			}

			// возвращаем объект со списком услуг
			return service;
		},
		// добавляет строки услуг в DOM
		create_service_row_from_variants:function(service){
			// console.log('create_service_row_from_variants -- > ',service)
			// ПЕРЕБОР УСЛУГ
    		for (var i = service.length-1; i >= 0; i--) {    			
    			// return true;
    			var td = '';
    			var check_alarm = ''; var alarm_notify = '';
				var print_details = service[i].print_details;

    			service[i].discount = Number(service[i].discount)
    			service[i].price_in = Number(service[i].price_in);
    			service[i].price_out = Number(service[i].price_out);
    			if(service[i].for_how == 'for_all'){
    				service[i].quantity = 1;
    			}else{
    				service[i].quantity = Number(service[i].quantity);
    			}

    			var calculator_type = '';
    			if(print_details && print_details.calculator_type){
    				calculator_type = print_details.calculator_type;	
    			}

    			service_row = $('<tr/>',{
	    			'class':'service',
	    			'data-calculator_type':calculator_type,
	    			'data-dop_uslugi_id':service[i].id,
	    			'data-dop_data_id':service[i].dop_row_id
	    		});
						
				service_row.append( $('<td/>',{text:(i+1)}));

				// иконка будильник
				var div = $('<div/>',{'class':'alarm_clock'}).css({'float':'left','width':'100%','height':'100%'});
				
				
				if(print_details && print_details.dop_params && print_details.dop_params.coeffs && print_details.dop_params.coeffs.summ){
					// console.log(print_details.dop_params.coeffs.summ)
					if(print_details.dop_params.coeffs.summ['72 hours']){
						div.click(function(event) {
							$(this).notify('72 часа',{ position:"right center",className:'total_12px' });
						}).addClass('checked');	
					}else if(print_details.dop_params.coeffs.summ['48 hours']){
						div.click(function(event) {
							$(this).notify('48 часа',{ position:"right center",className:'total_12px' });
						}).addClass('checked');	
					}else if(print_details.dop_params.coeffs.summ['24 hours']){
						div.click(function(event) {
							$(this).notify('24 часа',{ position:"right center",className:'total_12px' });
						}).addClass('checked');	
					}
					// console.log(print_details);	
				}
				service_row.append($('<td/>').append( div ));
						
				// название услуги из калькулятора
				if(print_details && print_details.print_type){
					service_row.append($('<td/>',{
						'colspan':'3',
						'class':'service_name',
						'text':print_details.print_type,
						// 'text':service[i].id+' >> '+service[i].united_calculations,
						click:function(){
							methods.calculator_edit_the_service($(this));
						}
					}));	
				}else{
					service_row.append($('<td/>',{
						'colspan':'3',
						'class':'service_name',
						'text':service[i].service_name,
						// 'text':service[i].id+' >> '+service[i].united_calculations,
						click:function(){
							methods.calculator_edit_the_service($(this));
						}
					}));
				}

				// ОПИСАНИЕ УСЛУГИ
				if (print_details != null) { // из калькулятора

					if (service[i].uslugi_id == "0") {

						service_row.append($('<td/>',{'colspan':'3','text':Base64.decode(print_details.comment)}));
					}else{
						// место печати
						var format = '';
						service_row.append($('<td/>',{'class':'note_title','text':service[i].desc.format}));
						// цвета
						var colors = '';
						service_row.append($('<td/>',{'class':'note_title','text':service[i].desc.colors}));
						// площадь
						var a_place_print = '';
						service_row.append($('<td/>',{'class':'note_title','text':service[i].desc.a_place_print}));
					}
				}else{ // из списка доп услуг
					service_row.append($('<td/>',{'colspan':'3'}));
				}
				
				// колонка комментариев
				service_row.append($('<td/>',{
					'class':'comment'+((service[i].tz=="")?'':' is_full'),
					click:function(){
						methods.edit_service_comments($(this));
					}
				}));

				// тираж в услуге
				if(service[i].united_calculations && service[i].united_calculations !== null){
					// получаем id dop_data для данной группы
					var serv_id = service[i].united_calculations.split(',');
					// вычисляем id кнопки группы
					var id_group = serv_id.join('_');
						
					// собираем notify с информацией по объединённому тиражу
					// var content = ;	

					var td = $('<td/>',{
						'data-id_s':service[i].united_calculations,
						'data-list_':methods.get_group_name(service[i].united_calculations),
						'class':'service_group',
						'on':{
							mouseenter:function(){
								// добавляем подсветку вкладки группы
								methods.top_menu_div.find('li#list_'+$(this).attr('data-list_')).addClass('led');
								// добавляем сласс для подсветки строк группы
								for(var k = 0, length1 = serv_id.length; k < length1; k++){
									$('#dop_data_'+methods.depending_on_the_services_and_options[serv_id[k]]+' td').addClass('hover_group_class');
								}
							},
							mouseleave:function(){
								// снимаем подсветку вкалдки группы
								methods.top_menu_div.find('li#list_'+$(this).attr('data-list_')).removeClass('led');
								// снимаем подсветку группы
								for(var k = 0, length1 = serv_id.length; k < length1; k++){
									$('#dop_data_' + methods.depending_on_the_services_and_options[serv_id[k]]+' td').removeClass('hover_group_class');
								}
							}
						},
						'click':function(){
							// снимаем подсветку кнопки
							// переходим в группу
							methods.top_menu_div.find('li#list_'+$(this).attr('data-list_')).removeClass('led').click();
						}
					}).append($('<span/>',{
						'data-id_s':service[i].united_calculations,
						'text':service[i].quantity+' шт',
						'on':{ 
							mouseenter:function(){
								// показываем дополнительную информацию
								$(this).notify(  methods.get_service_notify( $(this).attr('data-id_s').split(',') ),{ position:"top center",className:'total_10px' });
							}
						}
					}))
							
					service_row.append(td);
				}else {
					service_row.append($('<td/>',{'text':service[i].quantity+' шт'}));
				}
						
				// цена входящая
				service_row.append($('<td/>',{'class':'price price_in'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(service[i].price_in)+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(service[i].price_in*Number(service[i].quantity))+'</span>р</div>'})));
				// цена без скидки
				service_row.append($('<td/>',{'class':'price price_out'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(service[i].price_out)+'</span>р</div>'})).append($('<div/>',{'html':'<div  class="for_all"><span>'+round_money(service[i].price_out*Number(service[i].quantity))+'</span>р</div>'})));
				// скидка
				var input = $('<input/>',{
					'value': round_money(service[i].discount),
					'data-id':service[i].id,
					keyup:function(e){
						methods.save_discount($(this), $(this).attr('data-id'),'service', $(this).val())
					}
				});
				// console.log(service[i])
				service_row.append($('<td/>',{'class':'price discount'}).append(input).append('%'));
				// service_row.append($('<td/>',{'class':'price discount','html':'<span>'+round_money(service[i].discount)+'</span>%'}));
				// со скидкой (исходящая)
				service_row.append($('<td/>',{'class':'price price_out_width_discount'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(methods.calc_price_width_discount(service[i].price_out, service[i].discount))+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(methods.calc_price_width_discount(service[i].price_out*Number(service[i].quantity), service[i].discount))+'</span>р</div>'})));

				// удаление услуги
				if(service[i].united_calculations && service[i].united_calculations !== null ){
					// если мы не в группе по данной услуге, навешиваем переход в группу
					var id_group = service[i].united_calculations.split(',').join('_');
					if(methods.top_menu_div.find('li#list_'+methods.get_group_name(service[i].united_calculations)).hasClass('checked')){
						// удаление групповой услуги
						service_row.append($('<td/>',{
							click:function(e){
								console.log(654,'1')
								methods.delete_service($(this))
							}
						}));
					}else{
						// переход в группу
						service_row.append($('<td/>',{
							'data-id_s' : service[i].united_calculations,
							'data-list_': methods.get_group_name(service[i].united_calculations),
							click:function(){
								console.log(654,'2')
								methods.top_menu_div.find('li#list_'+$(this).attr('data-list_')).click();
							}
						}));
					}										
				}else{
					service_row.append($('<td/>',{
						click:function(e){
							methods.delete_service($(this))
						}
					}));
				}
				// добавляем строки услуг в DOM
    			methods.services_tbl.find('.service_th').show().after(service_row);
    		}
		},
		// обновляет информацию по услугам, относительно выбранных вариантов
		update_services_content : function( content ) {
    		// подчищаем данные в таблице услуг
    		methods.services_tbl.find('.variant').remove();
    		methods.services_tbl.find('.service').remove();
    		methods.services_tbl.find('.service_th');

    		var service_arr = []; 
    		var service_num = 0;
    		var key = 0;
    		methods.checked_variants_id = [];
    		methods.variants_rows.each(function(index, el) {

    			

    			if($(this).hasClass('tr_checked')){

    			var dop_row_id = Number($(this).attr('data-dop_row_id'));
				// var variant = methods.mainObj[dop_row_id]['variant'];
				// console.log(methods.mainObj[dop_row_id]);
    			var variant = jQuery.parseJSON( $(this).find('td.js-variant_info div').html() );
    			
    			// запоминаем выбранные строки
    			methods.checked_variants_id[Number(variant.id)] = [];
    			methods.checked_variants_id[Number(variant.id)] = methods.depending_on_the_options_and_services[Number(variant.id)];

    			var variant_row = $('<tr/>',{'class':'variant','data-dop_data_id':variant.id});

    			variant_row.append($('<td/>',{"colspan":"2"}));
    			
    			var span = $('<span/>',{'html': $(this).find('td').eq(2).text()});
    			var span2 = $('<span/>',{'html': $(this).find('td').eq(4).attr('data-no_short')});
    			var div = $('<div/>').append(span).append(span2);
    			variant_row.append($('<td/>').append(div));
				
				variant_row.append($('<td/>').append($(this).find('td').eq(5).text()));

				var span = $('<span/>',{'html': $(this).find('td span.service').html()});
				variant_row.append($('<td/>').append(span));
				
				// название
				var obj = $(this);
				// console.log(obj)
				var link = $('<a/>',{'html':$(this).find('td').eq(7).text()}).bind('click.totalCommander', function(event) {
					methods.cancel_all_choosen_variants_and_chose_one_row(obj);
					// если включена вкладка группы
					if (methods.top_menu_div.find('.checked').index()>0) {
						// удаляем вкладку группы
						methods.top_menu_div.find('.checked').removeClass('checked');
						methods.top_menu_div.find('li').eq(0).addClass('checked');
					}
				});

				variant_row.append($('<td/>',{'colspan':'4'}).append(link))

				variant_row.append($('<td/>',{'html':$(this).find('td').eq(8).text()}));
				
				// входащая
				variant_row.append($('<td/>',{'class':'price price_in'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(variant.price_in)+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(variant.price_in*variant.quantity)+'</span>р</div>'})));
				// без скидки (исходящая)
				variant_row.append($('<td/>',{'class':'price price_out'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(variant.price_out)+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(variant.price_out*variant.quantity)+'</span>р</div>'})));
				
				// скидка
				var input = $('<input/>',{
					'value': round_money(variant.discount),
					'data-id':variant.id,
					keyup:function(e){
						methods.save_discount($(this),$(this).attr('data-id'),'variant',$(this).val())
					}
				});
				variant_row.append($('<td/>',{'class':'price discount'}).append(input).append('%'));

				// со скидкой (исходящая)
				variant_row.append($('<td/>',{'class':'price price_out_width_discount'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(methods.calc_price_width_discount(variant.price_out, variant.discount))+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(methods.calc_price_width_discount(variant.price_out*variant.quantity, variant.discount))+'</span>р</div>'})));
				
				variant_row.append($('<td/>'));
					
					// var service = methods.mainObj[dop_row_id]['services'];
    				var service = jQuery.parseJSON( $(this).find('td.js-variant_services_json div').html() );
    				// console.log(service.length);
    				
    				methods.services_tbl.find('.service_th').show().addClass('js-service_spacer').before(variant_row);	
    				

    				for (var i = service.length-1; i >= 0; i--) {
    					service_arr[service_num] = [];
    					service_arr[service_num++] = service[i];
    				}  				
    				
    			}
    		});

			// console.log('test 1 >>',service_arr)

			var service = methods.filter_services_from_variants(service_arr);

			// console.log('test >>',service)
			// добавление строк услуг для выбранных вариантов
			methods.create_service_row_from_variants(service);
			
			// запоминаем данные услуг
			methods.services_init();
			// пересчёт итого
			methods.calc_price();
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
	// SC_createShowWindowButton : function(event){
	// 	if($('#js-win-sv').length) return true;
	// 	var obj = $('<div/>',{
	// 		"id" : "js-win-sv"	
	// 	}).click(function(event) {
	// 		$.SC_sendAjax(event);
	// 	});

	// 	$('body').append( obj );
	// },
	// запрос на вызов окна
	SC_sendAjax:function(obj){
		// event.preventDefault();
		// console.log(this)

		
	},
	// открытие окна Тотал
	SC_createWindow : function(html){
		// если окно вызывается впервые
		if($('#js-main_service_center').length > 0){
			$('#js-main_service_center').totalCommander('hide');
		}

			$('body').append($('<div/>',{
				"id":'js-main_service_center'
			}).html(html));
			
			var title = 'Центр услуг';			
			
			$('#js-main_service_center').dialog({
			        width: $(window).width()+10,
			        height: $(window).height(),
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
			

			
			// добавляем блок кнопок
			$.SC_createButton();
			// выравнивем колонку тираж в верхней таблице по нижней
			
			// console.log($.div_variants.removeAttr('id'))
			// инициализируем плагин
			$('#js-main_service_center').totalCommander();
		


		

		
		
	},
	// перезагрузка RT
	SC_reload_RT_content : function(){
		// установка прелоад
		window_preload_add();
		// обновление таблицы РТ
		if($('#rt_tbl_body').length>0){
			$('#scrolled_part_container').load(' #rt_tbl_body',function(){
				// запускаем РТ по новой
				// printCalculator;
				rtCalculator.init_tbl('rt_tbl_head','rt_tbl_body');
				// убираем прелоад
				window_preload_del();
			});
		}else{
			window_reload();
		}
	},
	// блок кнопок окна Тотал
	SC_createButton: function(){
		var buttons = new Array();
		buttons.push({
		    text: 'Сбросить все',
		    class:'no_click',
		    id:  'sc_cancel_all'
		});
		buttons.push({
		    text: 'Добавить услугу',
		    id: 	'sc_add_service',
		    // click: function() {
		    // 	$('#js-main_service_center').dialog('close');			    	
		    // }
		});
		buttons.push({
		    text: 'Закрыть',
		    id: 	'sc_close_window',
		    click: function() {
		    	$('#js-main_service_center').dialog('destroy').remove();	
		    	$.SC_reload_RT_content();	    		    	
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
    	$('#js-main_service_center').dialog('destroy').remove();
    	$.SC_reload_RT_content();
    }
});
