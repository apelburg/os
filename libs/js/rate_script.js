/**
 * @author: Nazar Kaznadzey
 * @description: Flexible Rate Stars
 * @email: nazar.kaznadzey@gmail.com
 * @creation date: 3013-05-15
*/
$(document).ready(function() {
	var oRate = new Array();	
	oRate['image'] = 'skins/images/img_design/rate_stars.png';
	oRate['block_width'] = 100;
	oRate['block_height'] = 22;
	oRate['star_width'] = 19;
	oRate['star_height'] = 17;
	oRate['delimiter_width'] = 3;
	oRate['identificator_id'] = 'rate_';
	oRate['max_rate'] = 5;
	oRate['color_active'] = '#308819';
	oRate['color_rate'] = '#66AA54';
	oRate['color_not_active'] = '#f3f3f3';
	oRate['function'] = 'setRate';
	oRate['type'] = 'review';
	oRate['allow_voite'] = 1;
	
	//replace with default values, if object was not created
	if(!oRate)
		var oRate = new Array();
	
	if(!oRate['image']) oRate['image'] = oDRate['image'];
	if(!oRate['block_width']) oRate['block_width'] = oDRate['block_width'];
	if(!oRate['block_height']) oRate['block_height'] = oDRate['block_height'];
	if(!oRate['star_width']) oRate['star_width'] = oDRate['star_width'];
	if(!oRate['star_height']) oRate['star_height'] = oDRate['star_height'];
	if(!oRate['delimiter_width']) oRate['delimiter_width'] = oDRate['delimiter_width'];
	if(!oRate['identificator_id']) oRate['identificator_id'] = oDRate['identificator_id'];
	if(!oRate['max_rate']) oRate['max_rate'] = oDRate['max_rate'];
	if(!oRate['color_active']) oRate['color_active'] = oDRate['color_active'];
	if(!oRate['color_rate']) oRate['color_rate'] = oDRate['color_rate'];
	if(!oRate['color_not_active']) oRate['color_not_active'] = oDRate['color_not_active'];
	if(!oRate['type']) oRate['type'] = oDRate['type'];
	if(!oRate['allow_voite']) oRate['allow_voite'] = oDRate['allow_voite'];
	
	//search rate places
	$('[id^='+oRate['identificator_id']+']').each(function(){
		if($(this).attr('data-processed') == undefined)
		{
			//get palce id
			iId = $(this).attr('id').replace(oRate['identificator_id'], '');

			// id клиента
			iDataId = $(this).attr('data-id');
			//get vount of viotings
			iCount = parseInt($('[id='+oRate['identificator_id']+iId+'] input[name=review_count]').val());
			//get total rate for element
			iRate = parseInt($('[id='+oRate['identificator_id']+iId+'] input[name=review_rate]').val());
			if(!iCount || iCount == 0)
				iCount = 1;
				
			$(this).attr('data-processed', '1');
				
			iRateCount = iRate / iCount;
			iRateWidth = (iRateCount * oRate['block_width']) / oRate['max_rate'];
			
			sInnerId = 'not_active_'+iId;
			sRateId = 'rate_value_'+iId;
			sActiveId = 'active_'+iId;
			
			//create rate elements
			var i = document.createElement('div'), r = document.createElement('div'), a = document.createElement('div');
			i.id = sInnerId;
			r.id = sRateId;
			a.id = sActiveId;
			
			//create stars in satrs place
			$(this).html('').append(i).append(r).append(a).css({
				width: oRate['block_width'],
				height: oRate['block_height'],
				backgroundColor: oRate['color_not_active'],
				styleFloat: 'left',
				cssFloat: 'left'
			});
			
			i.style.width = oRate['block_width']+'px';
			i.style.height = oRate['block_height']+'px';
			i.style.position = 'absolute';
			i.style.zIndex = 50;
			i.style.backgroundImage = 'url('+oRate['image']+')';
			
			//draw with color. active stars
			r.style.styleFloat = 'left';
			r.style.cssFloat = 'left';
			r.style.height = oRate['block_height']+'px';
			r.style.width = iRateWidth+'px';
			r.style.backgroundColor = oRate['color_rate'];
			
			//if allow voiting
			if(oRate['allow_voite'] == 1)
			{
				i.style.cursor = 'pointer';
			
				a.style.styleFloat = 'left';
				a.style.cssFloat = 'left';
				a.style.height = oRate['block_height']+'px';
				a.style.backgroundColor = oRate['color_active'];
				a.style.position = 'absolute';
				a.style.zIndex = 10;
				
				$('#'+sInnerId).mousemove(function(e){
					iActiveId = $(this).attr('id').replace('not_active_', '');
					iActiveWidth = (e.pageX - $(this).offset().left).toFixed();
					$('#active_'+iActiveId).css('width', iActiveWidth+'px');
			 	});
		   	
		   	//set live rate stars
		   	$('#'+sInnerId).mouseout(function(){
		 			iActiveId = $(this).attr('id').replace('not_active_', '');
		   		$('#active_'+iActiveId).css('width', '0px');
		   	});
		   	
		   	//voiting click function
				$('#'+sInnerId).click(function(e){
				// alert((e.pageX - $(this).offset().left).toFixed());
		   		l = (e.pageX - $(this).offset().left);
		   		ur = parseInt((l * oRate['max_rate']) / oRate['block_width']) + 1;
		   		id = $(this).attr('id').replace('not_active_', '');
		   		setTimeout(oRate['function']+'('+ur+', '+id+', \''+oRate['type']+'\',\''+iDataId+'\')', 100);
		   			// iRate - значение скрытого инпута с именем review_rate 20
		   			// ur    - количество выбранных/введёных звёзд
		   			// iCount - значение скрытого инпута с именем review_count 5
				 	nrc = (iRate + ur) / (iCount + 1);
					nrw = (nrc * oRate['block_width']) / oRate['max_rate'];
				 	setTimeout(function(){
		   			// r.style.width = nrw+'px';		   			
		   			r.style.width = oRate['star_width']*ur+3+'px';
		   			console.log(ur);
		   			$.post('', {ajax_standart_window:"update_reiting_cont_face",id:iDataId,rate:ur}, function(data, textStatus, xhr) {
		   				if(data['response']!="1"){
		   					//сообщаем, что что-то пошло не так
                        	new_html_modal_window('Что-то пошло не так и данные не были сохранены. Пожалуйста, запомните порядок Ваших действий и опишите их в письме к разработчикам.',data['text'],'','', '', '');
		   				}
		   			},"json");
		   		}, 120);
		   	});
	   	}
	 	}
	});
});