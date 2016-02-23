
// $(document).on('keyup', '#rezerv_save', function(event) {
// 	// event.preventDefault();
// 	console.log($(this).val());
// 	timing_save_input('reserv_save',$(this));
// });

$(document).on('keyup', '#rezerv_save', function(event) {
	 timing_save_input('rezerv_save_new',$(this));
});

function rezerv_save_new(obj){

	var row_id = obj.attr('data-id');
	$.post('', {
	    AJAX:'reserv_save',
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




/**
  *	КОД ОПЩИЙ ДЛЯ КАРТОЧКИ КАТАЛОГА И НЕ КАТАЛОГА
  *
  */


$(document).on('click', '.add_usl', function(event) {
	// полечаем скидку
	var discount = Number($('.percent_nacenki.js--calculate_tbl-edit_percent:visible').attr('data-val'));

	var for_all = ($(this).hasClass('all'))?1:0;
	$.post('', 
		{
			AJAX:"get_uslugi_list_Database_Html",
			client_id:$(this).attr('data-client_id'),
			query_num:$(this).attr('data-query_num'),
			for_all:for_all,
			discount:discount

		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		// if(data['response']){
			// show_dialog_and_send_POST_window2(data,'Выберите услугу', $(window).height());
		// }		
	},'json');
	
});

//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');

	// добавляем DIV
	if($('#js-add-comment').length){
		$('#js-add-comment').remove();
	}
	var obj = $('<div></div>',{
		"id":"js-add-comment"
	}).css({'paddingLeft':$(this).css('paddingLeft'),'paddingRight':"42px"});
	$(this).after(obj);

	// добавляем поля
	if(Number($(this).attr('data-id')) == 103){
		// название
		var input_name = $('<input>',{
			"name": 	"other_name",
			"type": 	"text", 
			"placeholder": 	"Название услуги", 
		}).css({'width':'100%'});
		var div = $('<div>').css({'paddingBottom':"0","paddingTop":"5px"});
		input_name = div.append(input_name);

		// цена входящая
		var input_price_in = $('<input>',{
			"name": 	"price_in",
			"type": 	"text", 
			"placeholder": 	"Цена входящая", 
		}).css({'width':'46%'});
		div = $('<div>').css({'paddingBottom':"0","paddingTop":"5px"});
		div.append(input_price_in).append('<span> р. &nbsp;</span>');


		// цена исходящая
		var input_price_out = $('<input>',{
			"name": 	"price_out",
			"type": 	"text", 
			"placeholder": 	"Цена исходящая", 
		}).css({'width':'46%'});
		// div = $('<div>').css({'paddingBottom':"0","paddingTop":"5px"});
		input_price = div.append(input_price_out).append('<span> р. &nbsp;</span>');

		// ТЗ
		var textarea = $('<textarea></textarea>',{
			"name": 		"comment",
			"placeholder": 	"ТЗ/Комментарии к услуге"
		}).css({'minHeight':'auto','height':'52px'});
		// для всех кроме услуги НЕТ В СПИСКЕ
		$('#js-add-comment')
		.append(input_name).append('<br>')
		.append(input_price)
		.append(textarea)
		.find('div:first-child input').focus();
	}else{
		// ТЗ
		var textarea = $('<textarea></textarea>',{
			"name": 		"comment",
			"placeholder": 	"ТЗ/Комментарии к услуге"
		}).css({'minHeight':'auto','height':'52px'});
		// для всех кроме услуги НЕТ В СПИСКЕ
		$('#js-add-comment')
		.append(textarea)
		.find('textarea').focus();
	}
	



	var id,dop_row_id,quantity;
	// для каталожной и некаталожной карточки продукции основные данные ищем по разному
	// if($('#dialog_gen_window_form form input[name="type_product"]').val() != 'cat'){
		id = $(this).attr('data-id');
		dop_row_id = $('.variant_content_block:visible').attr('data-id')
		// dop_row_id = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked').attr('data-id');
		// получим тираж
		quantity = $('.variant_content_block:visible .tirage_var').val();
		
		// quantity = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html();
	// }else{
		// var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
		// id = $(this).attr('data-id');
		// console.log($(id_variant).attr('data-id'));
		// dop_row_id = $('#variants_name .variant_name.checked ').attr('data-id');
		// получим тираж
		// quantity = $(id_variant+' .tirage_var').val();
	// }

	// console.log(quantity);
	$('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});

// округление
function round_s(int_r){
	return Math.ceil((int_r)*100)/100;
}

function save_empty_tz_text_AJAX(data){
	var id = $('#'+data.increment_id).parent().parent().attr('id');
	$('#'+data.increment_id).attr('class','tz_text_new').removeAttr('id');
	$('#'+id+' td:first-child .greyText').html(Base64.decode(data['html']));
}
function save_tz_text_AJAX(data){
	var id = $('#'+data.increment_id).parent().parent().attr('id');
	$('#'+data.increment_id).attr('class','tz_text_edit').removeAttr('id');
	$('#'+id+' td:first-child .greyText').html(Base64.decode(data['html']));	
}

// показать окно
function show_dialog_and_send_POST_window2(html,title,height,width){
	height = height || 'auto';
	width = width || 'auto';
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {


				if(data['response']=='show_new_window'){
					title = data['title'];// для генерации окна всегда должен передаваться title
					show_dialog_and_send_POST_window2(data['html'],title);
				}else{
					$('#dialog_gen_window_form').dialog( "destroy" );
					// меняем иконку добавления ТЗ на редактировать
					if(data['name'] == 'save_tz_text_AJAX'){
						$('#'+data.increment_id).attr('class','tz_text_edit').removeAttr('id');
					}
					if(data['function'] == 'window_reload'){ // вызов функции... если требуется
						location.reload();
					}



					if(data['name'] == 'chose_supplier_end'){
						$('#chose_supplier_id').removeAttr('id');

					}

					//### добавляем услугу в карточке НЕ каталожного товара
					if(data['name'] == 'add_uslugu_no_cat'){ // если нужно добавить услугу
						
						// ADD USLUGA start *** старт ***
						var added=1; // флаг, который сигнализирует, что HTML добавлен
						var add_html = Base64.decode(data['html']); // html новой услуги
						var parent_id_new_usl = Number(data['parent_id']); // parent_id овой услуги
						
						// поищем по всем группам уже существующих услуг
						// если найдется подходящая - добаляем html
						$('.calkulate_table:visible .group_usl_name').each(function(index, el) {
							
							if(Number($(el).attr('data-usl_id'))==parent_id_new_usl){
								$(el).after(add_html);
								added=0;
							}
						}); 

						// если услуга не найдена добаляем в конец
						if(added){
							$('#variant_info_' +$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table .variant_calc_itogo').prev().prev().before(add_html);
						}
						// пересчитываем Итого
						recalculate_table_price_Itogo()
						// ADD USLUGA end *** конец ***
						
					}
					
					//### добавляем услугу в карточке каталожного товара
					if(data['name'] == 'add_uslugu_cat'){
						
						// ADD USLUGA start *** старт ***
						var added=1; // флаг, который сигнализирует, что HTML добавлен
						var add_html = Base64.decode(data['html']); // html новой услуги
						var parent_id_new_usl = Number(data['parent_id']); // parent_id овой услуги
						
						console.log('154');
						// поищем по всем группам уже существующих услуг
						// если найдется подходящая - добаляем html
						$('.calkulate_table:visible .group_usl_name').each(function(index, el) {
							console.log('654');
							if(Number($(el).attr('data-usl_id'))==parent_id_new_usl){
								$(el).after(add_html);
								added=0;
							}
						}); 

						// если услуга не найдена добаляем в конец
						if(added){
							$('.calkulate_table:visible .variant_calc_itogo').prev().prev().before(add_html);
						}
						// пересчитываем Итого
						recalculate_table_price_Itogo()
						// ADD USLUGA end *** конец ***
						
					}
				}
			},'json');				    	
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}
	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: $(window).width(),
          height: $(window).height(),
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });
}


function chose_supplier_end(){
	$('#chose_supplier_id').removeAttr('id');
}

// Удаление услуги
$(document).on('click', '.del_row_variants', function(event) {
	/* Act on the event */
	console.log('клик на удаление услуги');
	console.log($(this).parent().parent().prev().find('th').length);
	console.log($(this).parent().parent().next().find('th').length);
	
	// подсчёт ИТОГО
	if(confirm('Вы уверены, что хотите удалить услугу?')){
		//если это последняя услуга в своём разделе, удаляем имя раздела
		if($(this).parent().parent().next().find('th').length){
			if($(this).parent().parent().prev().find('th').length){
				$(this).parent().parent().prev().remove();
			}
		}

		var dop_uslugi_id = $(this).parent().parent().attr('data-dop_uslugi_id');
		$(this).parent().parent().remove();
		
		$.post('', 
			{
				AJAX: 'delete_usl_of_variant',
				uslugi_id: dop_uslugi_id
			}, function(data, textStatus, xhr) {
			console.log(data);
		});


		recalculate_table_price_Itogo();		
	}
	
	
});

// $(document).on('click', '.calc_icon_chose', function(event) {
// 	// снимаем выделение с остальных услуг
// 	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');

// 	alert('хотим калькулятор\n '+$(this).attr('data-client_id').html()+', type = '+$(this).attr('data-type')+', id = '+$(this).attr('data-id'));
// });


// редактируем входящую цену за услугу
$(document).on('keyup', '.row_tirage_in_gen.uslugi_class.price_in span', function(event) {
	var pr_in = Number($(this).html());
	var pr_out_men = Number($(this).parent().parent().find('.row_price_out_gen.uslugi_class.price_out_men span').html());
	if(pr_out_men<pr_in){
		$(this).parent().parent().find('.row_price_out_gen.uslugi_class.price_out_men span').html(pr_in)
	}


	// считаем прибыль
	calc_usl_pribl($(this).parent().parent());

	// считаем %
	calc_usl_percent($(this).parent().parent());

	// подсчёт ИТОГО
	recalculate_table_price_Itogo();
	

	// добавляем маркер к строке которое мы отредактировали
	$(this).parent().parent().addClass('editing');
	// сохраняем значения тиража в dop_uslugi
	time_to_save('save_dop_dop_usluga',$('.calkulate_table:visible'));
});

//пересчитать прибыль для услуги
function calc_usl_pribl(obj){// на вход подаётся строка услуги
	var price_in = Number(obj.find('.row_tirage_in_gen.uslugi_class.price_in span').html());
	var price_out = Number(obj.find('.row_price_out_gen.uslugi_class.price_out_men span').html());
	obj.find('.row_pribl_out_gen.uslugi_class.pribl span').html(round_s(price_out-price_in));
}

//пересчитать % наценки для услуги
function calc_usl_percent(obj){// на вход подаётся строка услуги
	var price_in = Number(obj.find('.row_tirage_in_gen.uslugi_class.price_in span').html());
	var price_out = Number(obj.find('.row_price_out_gen.uslugi_class.price_out_men span').html());
	obj.find('.row_tirage_in_gen.uslugi_class.percent_usl span').html(percent_calc(price_out,price_in));
}
function percent_calc(price_out,price_in){
	return Math.ceil(((price_out-price_in)*100/price_in)*100)/100;
}

// SAVE DATA
/*
атрибут data-save_enabled="" в теге table активной таблицы calkulate_table
сигнализирует о том можно ли приступать скрипту к сохранению
если нет, то скрипт проверяет теге table активной таблицы calkulate_table наличие атрибута
с названием data-*имя функции сохранения*, этот атрибут может быть только в ТРЁХ состаяниях:
1. data-*имя функции сохранения*="true"
	ФУНКЦИЯ СОХРАНЕНИЯ ПОСТАВЛЕНА В ОЧЕРЕДЬ
	запрос на сохранение будет автоматически повтарен через 2 сек
2. data-*имя функции сохранения*=""
	можно приступать к сохранению, сохранения из этой функции на странице уже были

3. data-*имя функции сохранения* - не существует 
	можно приступать к сохранению, сохранений из этой функции на странице еще не было
*/

function time_to_save(fancName,obj){


	console.log(obj.attr('data-save_enabled'));
	//если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
	if(obj.attr('data-save_enabled')!="false"){
		// обнуляем очередь
		if(obj.hasClass(fancName)){obj.removeClass(fancName);}
		// console.log(obj);

		// console.log('re '+obj.find('.row_tirage_in_gen.price_in span').html());
		console.log(fancName);
		window[fancName](obj);

		// пишем запрет на save
		obj.attr('data-save_enabled','false');
		// снимаем запрет на через n времени
		var time = 2000;
		
		setTimeout(function(){obj.attr("data-save_enabled","")}, time);				
	}else{// стоит запрет, проверяем очередь по сейву данной функции
		
		if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
			// стоит очередь, значит мимо... всё и так сохранится
		}else{
			// не стоит в очереди, значит ставим
			obj.addClass(fancName);

			// вызываем эту же функцию через n времени всех очередей
			var time = 2000;
			$('.calkulate_table.'+fancName).each(function(index, el) {
				console.log($(this).find('.row_tirage_in_gen.price_in span').html());
				
				setTimeout(function(){time_to_save(fancName,$('.calkulate_table.'+fancName).eq(index));}, time);	
			});
			
		}		
	}
}


// ДОП УСЛУГИ
// %
$(document).on('keyup', '.row_tirage_in_gen.uslugi_class.percent_usl span', function(event) {
	// получаем реальные значения цены данной услуги, цены взяты из прайса
	// если эти значения равны, то услуга применяется к тиражу, если нет то к единице товара
	var min_price_real_for_one = Number($(this).parent().next().attr('data-real_min_price_for_one'));
	var min_price_real_for_all = Number($(this).parent().next().attr('data-real_min_price_for_all'));
	
	// цена от снаба (устанавливает снаб или админ)
	var min_price_snab = Number($(this).parent().next().find('span').html());
	
	// ввёденное значение процентов
	var enter_percent = Number($(this).html());

	// входящая цена
	var price_in = Number($(this).parent().prev().find('span').html());

	// если % меньше 0, то  % = 0
	if(enter_percent<0){
		enter_percent =0;
		$(this).html(enter_percent);
	}

	// если рабтает не мен, то замена исх. цены за услугу идёт и в поле менеджера и в поле снаба
	if($(this).parent().next().find('span').attr('contenteditable')=="true"){
		
		// высчитываем исходящую стоимость исходя из введённых процентов
		price_out_snab = price_out_men = round_s((100+enter_percent)*price_in/100);
		
		// если новая цена меньше цены по прайсу за тираж, то пересчитываем процент
		// и меняем его на минимально возможный (прайсовый)
		if(price_out_snab<min_price_real_for_all){
			enter_percent = percent_calc(min_price_real_for_all,price_in);
			$(this).html(enter_percent);
			price_out_snab = price_out_men = min_price_real_for_all;
		}
		// gпишем исходящие цены снаба и мена
		$(this).parent().next().find('span').html(price_out_snab).parent().next().find('span').html(price_out_men);
		// считаем прибыль
		calc_usl_pribl($(this).parent().parent());

	}else{ // работает мен

		// высчитываем исходящую стоимость исходя из введённых процентов
		price_out_men = round_s((100+enter_percent)*price_in/100);
		
		// если новая цена меньше цены цены от снаба, то пересчитываем процент
		// и меняем его на минимально возможный (от снаба)
		if(price_out_men<min_price_snab){
			enter_percent = percent_calc(min_price_snab,price_in);
			$(this).html(enter_percent);
			price_out_snab = price_out_men = min_price_snab;
		}
		// пишем исходящую цену мена
		$(this).parent().next().next().find('span').html(price_out_men);
		// считаем прибыль
		calc_usl_pribl($(this).parent().parent());
	}

	// подсчёт ИТОГО
	recalculate_table_price_Itogo();


	// добавляем маркер к строке которое мы отредактировали
	$(this).parent().parent().addClass('editing');
	// сохраняем значения тиража в dop_uslugi
	time_to_save('save_dop_dop_usluga',$('.calkulate_table:visible'));


});

// редактирование цена мен
$(document).on('keyup', '.row_price_out_gen.uslugi_class.price_out_men span', function(event) {
	var price_out_snab = Number($(this).parent().prev().find('span').html());
	var price_out_men = Number($(this).html());

	// если мен указал цену меньше, чем указал снаб
	if(price_out_men<price_out_snab){
		// $(this).html(price_out_snab);
		$(this).css({'border':'1px solid red'});
	}else{
		$(this).css({'border':''});
	}

	// считаем прибыль
	calc_usl_pribl($(this).parent().parent());

	// считаем %
	calc_usl_percent($(this).parent().parent());

	// подсчёт ИТОГО
	recalculate_table_price_Itogo();

	// добавляем маркер к строке которое мы отредактировали
	$(this).parent().parent().addClass('editing');
	// сохраняем значения тиража в dop_uslugi
	time_to_save('save_dop_dop_usluga',$('.calkulate_table:visible'));
});


// ДОБАВЛЕНИЕ ИНФОРМАЦИИ В ТЗ ПО УСЛУГЕ
$(document).on('click', '.tz_text_new', function(event) {
	var id = 'incr'+Date.now();
	var text = '<form><textarea name="tz" data-id="'+id+'" class="tz_taxtarea_edit">'+$(this).parent().find('.tz_text_shablon').html()+'</textarea>';
	var ajax_name = '<input type="hidden" name="AJAX" value="save_tz_text">';
	ajax_name += '<input type="hidden" name="increment_id" value="'+id+'">';
	ajax_name += '<input type="hidden" name="rt_dop_uslugi_id" value="'+$(this).parent().parent().attr('data-dop_uslugi_id')+'"></form>';

	$(this).attr('id',id);
	// окно редактирования ТЗ
	// show_dialog_and_send_POST_window2(text+ajax_name,'ТЗ');
	show_dialog_and_send_POST_window(text+ajax_name,'ТЗ');
});

// редактирование имени услуги "НЕТ В СПИСКЕ"
$(document).on('click', '.js-service-other-name', function(event) {

	event.preventDefault();
	$.post('', {
		AJAX: 	'get_window_service_other_name',
		id_row: $(this).parent().parent().attr('data-dop_uslugi_id'),
		tr_id:$(this).parent().parent().attr('id'),
		html: 	Base64.encode($(this).html())
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

// 
function change_service_name_in_html(data){
	// console.log(data.tr_id)
	// console.log($('#'+data.tr_id+' .js-service-other-name').length);
	$('#'+data.tr_id+' .js-service-other-name').html(Base64.decode(data.html));
}

$(document).on('keyup', '.tz_taxtarea_edit', function(event) {
	$('#'+$(this).attr('data-id')).parent().find('.tz_text').html($(this).val());
});

// РЕДАКТИРОВАНИЕ ИНФОРМАЦИИ В ТЗ ПО УСЛУГЕ
$(document).on('click', '.tz_text_edit', function(event) {
	var id = 'incr'+Date.now();
	var text = '<form><textarea name="tz" data-id="'+id+'" class="tz_taxtarea_edit">'+$(this).parent().find('.tz_text').html()+'</textarea>';
	var ajax_name = '<input type="hidden" name="AJAX" value="save_tz_text">';
	ajax_name += '<input type="hidden" name="increment_id" value="'+id+'">';
	ajax_name += '<input type="hidden" name="rt_dop_uslugi_id" value="'+$(this).parent().parent().attr('data-dop_uslugi_id')+'"></form>';

	$(this).attr('id',id);
	// окно редактирования ТЗ
	show_dialog_and_send_POST_window(text+ajax_name,'ТЗ');
});


// редактор темы
$(document).on('keyup', '#query_theme_block input', function(event) {
	timing_save_input('save_query_theme',$(this));
});



function save_query_theme(obj){
	var row_id = obj.attr('data-id');
	$.post('', {
	    AJAX:'save_query_theme',
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
