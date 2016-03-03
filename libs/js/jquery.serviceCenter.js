/*
	
	// перезагрузка РТ без перезагрузки окна
	$.SC_reload_RT_content();


	// вызов калькулятора
	add_services_from_calculator


*/



function round_money(num){
	num = Number(num);
	var new_num = Math.ceil((num)*100)/100;
    return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
    // return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
}


// ГЛОБАЛЬНЫЕ ЗАГЛУШКИ
$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.service  td.comment', function(event) {
	echo_message_js('Вызов окна комментариев');
});

$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.service  td:last-child', function(event) {
	echo_message_js('Удалить услугу');
});

$(document).on('click', '#js-main-service_center-variants-services-div-table table tr.itogo  td:last-child', function(event) {
	echo_message_js('Удалить все прикреплённые услуги');
});


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
	// console.log(event)
	// console.log($(this))
	//event.stopPropagation();
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
				methods.top_menu_div = 			$this.find('#js-main-service_center-top_menu ul');
				methods.top_menu = 			$this.find('#js-main-service_center-top_menu ul li');
				methods.checkbox_main = 	$this.find('#js-main-service_center-variants-table thead tr th:nth-of-type(2) div.js-psevdo_checkbox');
				
				methods.services_tbl =		$this.find('#js-main-service_center-variants-services-div-table table');

				// кнопка сбросить все 
				methods.btn_cancel_all = 	$('#sc_cancel_all');
				// кнопка Добавить услугу
				methods.btn_calculators = 	$('#sc_add_service');

				// получаем объект зависимостей услуг от вариантов
				methods.depending_on_the_services_and_options = jQuery.parseJSON($this.find('#js-depending_on_the_services_and_options').html());

				// events chose variants rows
				methods.checkbox_main.bind("click.totalCommander", methods.checkbox_main_click );
				methods.variants_rows.find('.js-psevdo_checkbox').bind("click.totalCommander", methods.checkbox_change );
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
							methods.variants_tbody.find('tr#dop_data_'+methods.depending_on_the_services_and_options[serv_id[k]]).addClass('tr_checked').find('td:nth-of-type(2)').addClass('checked');

						}
						// обновляем контент услуг
						methods.update_services_content();
						// инициализируем работу нижней части окна
						methods.services_init();
					}					
				});

				// сохраняем ИТОГО
				methods.services_itogo_row = methods.services_tbl.find('tr.itogo');
				methods.services_itogo_row.find('.price.discount input').bind('keyup', function(event) {
					methods.save_main_discount($(this).val());
				});

				// инициализируем работу нижней части окна
				// methods.services_init();

				// кнопка сбросить всё
				methods.btn_cancel_all.bind('click.totalCommander', methods.cancel_all_choosen_variants );
				// добавить услугу
				methods.btn_calculators.bind('click.totalCommander', methods.add_services_from_calculator );

				methods.show();

				// загрузка контента default
				methods.variants_tbody.find('.default_var').click()

				// подсчитывает стоимость в окне
				methods.calc_price();
			});

		},
		// сохранение главной скидки
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
			methods.services_variants_rows.each(function(index, el) {
				methods.change_obj['varians'][i] = [];
				methods.change_obj['varians'][i++][$(this).attr('data-dop_data_id')] = value;
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
			i = 0;
			methods.services_rows.each(function(index, el) {
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
			echo_message_js('1) Пишем запрос сохранения общей скидки для всех строк');
			echo_message_js('2) описать изменения json в верхней таблице, ведь оттуда берутся данные и при перезагрузке они вернуться к неверному значению');
			
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

			// подсчитывает стоимость в окне
			methods.calc_price();
		},
		// расчет исходящей стоимости относительноdiscount
		calc_price_width_discount:function (price_out, discount) {
			 return Number(price_out/100) * (100 + Number(discount));
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

		services_init:function(){			
			
			methods.services_variants_rows =	methods.services_tbl.find('tr.variant');
			methods.services_rows  = 			methods.services_tbl.find('tr.service');
			
			// // events services 
			// methods.services_rows.find('.alarm_clock').bind("click.totalCommander", function(){
			// 	$(this).toggleClass('checked');
			// 	echo_message_js( 'будильник' );
			// });
		},
		// нажатие на кнопку калькуля/тора
		add_services_from_calculator:function(){
			var i = 0;
			methods.dataObj = []; 					// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
			methods.dataObj['action'] = 'new'; 		// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
			methods.dataObj['type'] = [];			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
			methods.dataObj['usluga_id'] = [];		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
			methods.dataObj['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
			methods.dataObj['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж

			// собираем id строк вариантов
			methods.variants_tbody.find('tr.tr_checked').each(function(index, el) {
				methods.dataObj['dop_data_ids'][index] = $(this).attr('data-dop_row_id') ;
				methods.dataObj['quantity'][index] = $(this).attr('data-quantity') ;
				i++;
			});

			if(i>1){
				methods.dataObj['type'] = 'union';
			}

			// вызов калькулятора
			printCalculator.startCalculator(methods.dataObj);			
		},
		// редактирование услуги
		edit_the_service_of_the_calculator:function(obj){
			var i = 0;
			methods.dataObj = []; 					// {action: string value, type: string value, usluga_id: string value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
			methods.dataObj['action'] = 'update'; 		// [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
			methods.dataObj['type'] = [];			// [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
			methods.dataObj['usluga_id'] = obj.parent().attr('data-dop_uslugi_id');		// [необязательный] - строка, нужен когда тыкаем по существующему нанесению
			methods.dataObj['dop_data_ids'] = [];	// [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
			methods.dataObj['quantity'] = [];		// [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж

			// собираем id строк вариантов
			methods.variants_tbody.find('tr.tr_checked').each(function(index, el) {
				methods.dataObj['dop_data_ids'][index] = $(this).attr('data-dop_row_id') ;
				methods.dataObj['quantity'][index] = $(this).attr('data-quantity') ;
				i++;
			});

			if(i>1){
				methods.dataObj['type'] = 'union';
			}

			console.info(methods.dataObj)
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

			methods.variants_tbody.find('.default_var').addClass('tr_checked')

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
				.removeClass('checked');
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
		checkbox_change: function(e){
			var object = this.parentNode.parentNode;

			if((object.className == 'tr_checked default_var' || object.className == 'default_var tr_checked') && this.parentNode.className == 'checked' ){
				// console.log(1);
				object.className = 'default_var';
				this.parentNode.className = '';
			}else if(object.className == 'tr_checked' && this.parentNode.className == 'checked'){
				// console.log(2);
				object.className = '';
				this.parentNode.className = '';
			}else if(object.className == 'default_var' && this.parentNode.className == 'checked'){
				// console.log(3);
				object.className = 'tr_checked default_var';
				this.parentNode.className = 'checked';
			}else if(object.className == 'default_var' && this.parentNode.className == ''){
				// console.log(4);
				object.className = 'tr_checked default_var';
				this.parentNode.className = 'checked';
			}else if((object.className == 'tr_checked default_var' || object.className == 'default_var tr_checked') && this.parentNode.className == ''){
				// console.log(5);
				object.className = 'tr_checked default_var';
				this.parentNode.className = 'checked';
			}else{
				// console.log(6);
				object.className = 'tr_checked';
				this.parentNode.className = 'checked';
			}
			console.log('object.className = %s, this.parentNode.className = %s',object.className,this.parentNode.className)
			methods.top_menu_options = [];
			methods.variants_rows.each(function(index, el) {
				if ($(this).hasClass('tr_checked')) {
					if(!$(this).find('td:nth-of-type(2)').hasClass('checked')){
						$(this).removeClass('tr_checked');
					}else{
						methods.top_menu_options.push($(this).attr('data-dop_row_id'));
					}
				}
				
			});
			console.log('li#list_'+methods.top_menu_options.join('_'))
			if(methods.top_menu_div.find('li#list_'+methods.top_menu_options.join('_')).length){
							
					methods.top_menu_div.removeClass('checked').find('li#list_'+methods.top_menu_options.join('_')).addClass('checked');
				}else{
					if(!methods.top_menu_div.removeClass('checked').find('li').eq(0).hasClass('checked')){
						methods.top_menu_div.removeClass('checked').find('li').eq(0).addClass('checked');	
					}
				}
			// console.log(object)
			methods.checkbox_main_check();
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
    		$('#js-main-service_center-variants-table th:last-child').width($('#js-main-service_center-variants-services-div-table').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(1)').innerWidth() - $('#js-main-service_center-variants-services-div-table td:nth-of-type(2)').innerWidth());

    		// меняем высоту, если она превышает допустимую
    		var top_block_height = 190;
    		setTimeout(function(){
				if ($( "#js-main-service_center-variants-div-table" ).height() > top_block_height) {
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
		hide : function( ) {
    		$('#js-main_service_center').dialog('destroy');
    		$('#js-main_service_center').remove()
    		this.remove();
		},
		update_services_content : function( content ) {
    		// подчищаем данные в таблице услуг
    		methods.services_tbl.find('.variant').remove();
    		methods.services_tbl.find('.service').remove();
    		methods.services_tbl.find('.service_th');

    		var spacer = methods.services_tbl.find('.spacer').clone();
    		var object = {};

    		var services = {};

    		var service_row = '';


    		methods.variants_rows.each(function(index, el) {
    			if($(this).hasClass('tr_checked')){
    			var variant = jQuery.parseJSON( $(this).find('td.js-variant_info div').html() );
    			var variant_row = $('<tr/>',{'class':'variant','data-dop_data_id':variant.id});

    			variant_row.append($('<td/>',{"colspan":"2"}));
    			
    			var span = $('<span/>',{'html': $(this).find('td').eq(2).text()});
    			var span2 = $('<span/>',{'html': $(this).find('td').eq(4).text()});
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
					

    				var service = jQuery.parseJSON( $(this).find('td.js-variant_services_json div').html() );
    				// console.log(service.length);
    				if(service.length){
    					methods.services_tbl.find('.service_th').show().removeClass('js-service_spacer').before(variant_row);		
    				}else{
    					methods.services_tbl.find('.service_th').show().addClass('js-service_spacer').before(variant_row);	
    				}
    				
    				// перебор услуг к варианту
    				for (var i = service.length-1; i >= 0; i--) {
    					// return true;
    					var td = '';
    					if(service[i].for_how == 'for_all'){
    						service[i].quantity = 1;
    					}
    					service_row = $('<tr/>',{'class':'service','data-dop_uslugi_id':service[i].id,'data-dop_data_id':service[i].dop_row_id});
						
						service_row.append( $('<td/>',{text:(i+1)}));

						// иконка будильник
						var div = $('<div/>',{'class':'alarm_clock'}).css({'float':'left','width':'100%','height':'100%'});
						var check_alarm = ''; var alarm_notify = '';
						var print_details = service[i].print_details;
						// if (print_details) {
						// 	// console.log(print_details)
						// };
						if(print_details && print_details.dop_params && print_details.dop_params.coeffs && print_details.dop_params.coeffs.summ){
							// console.log(print_details.dop_params.coeffs.summ)
							if(print_details.dop_params.coeffs.summ['72 hours']){
								div.click(function(event) {
									$(this).notify('72 часа',{ position:"right",className:'total_12px' });
								}).addClass('checked');	
							}else if(print_details.dop_params.coeffs.summ['48 hours']){
								div.click(function(event) {
									$(this).notify('48 часа',{ position:"right",className:'total_12px' });
								}).addClass('checked');	
							}else if(print_details.dop_params.coeffs.summ['24 hours']){
								div.click(function(event) {
									$(this).notify('24 часа',{ position:"right",className:'total_12px' });
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
								click:function(){
									methods.edit_the_service_of_the_calculator($(this));
								}
							}));	
						}else{
							service_row.append($('<td/>',{
								'colspan':'3',
								'class':'service_name',
								'text':service[i].service_name,
								click:function(){
									methods.edit_the_service_of_the_calculator($(this));
								}
							}));
						}
						
						// ОПИСАНИЕ УСЛУГИ
						if (print_details && print_details.dop_params) { // из калькулятора
							if (print_details.place_type = "Дежурная услуга") {

							}else{

							}
							// цвета печати
							service_row.append($('<td/>',{'class':'note_title','text':'Грудь'}));
							// площадь
							service_row.append($('<td/>',{'class':'note_title','text':'синий, чёрный, подложка'}));
							// место печати
							service_row.append($('<td/>',{'class':'note_title','text':'до 1260 см2 (А3)'}));
							// console.log(service[i]);
						}else{ // из списка доп услуг
							// console.log(service[i]);
							service_row.append($('<td/>',{'colspan':'3'}));
						}
						

						service_row.append($('<td/>',{'class':'comment is_full'}));

						// тираж в услуге
						if(service[i].united_calculations && service[i].united_calculations !== null){
							var td = $('<td/>',{
								'data-id_s':service[i].united_calculations,
								'class':'service_group',
								'text':service[i].quantity+' шт',
								'on':{
									mouseenter:function(){
										// получаем id dop_data для данной группы
										var serv_id = $(this).attr('data-id_s').split(',');
										// вычисляем id кнопки группы
										var id_group = serv_id.join('_');
										// снимаем подсветку кнопки
										$('#list_'+id_group).addClass('led');

										// добавляем сласс для подсветки строк группы
										for(var k = 0, length1 = serv_id.length; k < length1; k++){
											$('#dop_data_'+methods.depending_on_the_services_and_options[serv_id[k]]+' td').addClass('hover_group_class');
										}
									},
									mouseleave:function(){
										// получаем id dop_data для данной группы
										var serv_id = $(this).attr('data-id_s').split(',');
										// вычисляем id кнопки группы
										var id_group = serv_id.join('_');
										// снимаем подсветку кнопки
										methods.top_menu_div.find('li#list_'+id_group).removeClass('led');

										// снимаем подсветку группы
										for(var k = 0, length1 = serv_id.length; k < length1; k++){
											$('#dop_data_' + methods.depending_on_the_services_and_options[serv_id[k]]+' td').removeClass('hover_group_class');
										}
									}
								},
								'click':function(){
									// получаем id dop_data для данной группы
									var serv_id = $(this).attr('data-id_s').split(',');
									// вычисляем id кнопки группы
									var id_group = serv_id.join('_');
									// снимаем подсветку кнопки
									methods.top_menu_div.find('li#list_'+id_group).click();
								}
							})
							
							service_row.append(td);
						}else {
							service_row.append($('<td/>',{'text':service[i].quantity+' шт'}));
						}

						
						// цена входящая
						service_row.append($('<td/>',{'class':'price price_in'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(service[i].price_in)+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(service[i].price_in*service[i].quantity)+'</span>р</div>'})));
						// цена без скидки
						service_row.append($('<td/>',{'class':'price price_out'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(service[i].price_out)+'</span>р</div>'})).append($('<div/>',{'html':'<div  class="for_all"><span>'+round_money(service[i].price_out*service[i].quantity)+'</span>р</div>'})));
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
						service_row.append($('<td/>',{'class':'price price_out_width_discount'}).append($('<div/>',{'html':'<div class="for_one"><span>'+round_money(methods.calc_price_width_discount(service[i].price_out, service[i].discount))+'</span>р</div>'})).append($('<div/>',{'html':'<div class="for_all"><span>'+round_money(methods.calc_price_width_discount(service[i].price_out*service[i].quantity, service[i].discount))+'</span>р</div>'})));

						service_row.append($('<td/>'));
					// service_row += '</tr>';
					// console.log(service[i]);
    					methods.services_tbl.find('.service_th').show().after(service_row);
    				}
    			}
    		});
			
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
		if($('#js-main_service_center').length == 0){
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
    }
});
