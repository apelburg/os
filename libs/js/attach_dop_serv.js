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
	var id = $(this).attr('data-id');
		
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	console.log('654');
});
