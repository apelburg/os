// JavaScript Document
window.onload = function(){
   rtCalculator.init_tbl('rt_tbl_head','rt_tbl_body');
}

// инициализация
/*if(window.addEventListener){
	window.addEventListener('load',tableDataManager.install,false);
}
else if(window.attachEvent){
	window.attachEvent('onload',tableDataManager.install);
}
else{
	var old_handler = window.onload;
	window.onload = function (){
		if(typeof old_handler == 'function') old_handler();
		tableDataManager.install();
	}
}
if(window.addEventListener) window.addEventListener('load',tableDataManager.install,false);
	else if(window.attachEvent) window.attachEvent('onload',tableDataManager.install);
	else window.onload = tableDataManager.install;
*/

$(window).on('beforeunload', function() {
    if(rtCalculator.changes_in_process) return 'У Вас есть не сохраненные данные, Вы можете их потерять';							  
});
	
window.onunload = function(){// пока с этим не ясно
   //alert(1);
}
print_r.count = 0;
function print_r(val/* array or object */){
	var str = scan(val)
	var win = window.open(null,'print_r'+(print_r.count++),'width=300,height=800',true);
	win.document.write(str);
	win.document.close();
	
	function scan(val){
		var str = '';
		for(var i in val){
			if(typeof val[i] != 'object') str += '[' + i + '] = [' + val[i] + ']<br>';
			if(typeof val[i] == 'object') str += '[' + i + '] => (' + scan(val[i]) + ')<br>';
		}
		return str;
	}
}

var rtCalculator = {
    // алгоритм действия калькулятора таблицы РТ: 1. При при наступлении события window.onload() считываются данные таблицы и сохраняются 
	// в переменной 2. специальным методом необходимые поля ввода устанавливаются редактируемыми и на необходимые поля навешиваются 
	// обработчики событий (это делается после полного считывания первоначальных данных, потому что для проведения расчета необходимы, данные
	// ДО и после ввода данных в поля ввода) 3. при возникновении событий приводящих к изменению данных в полях ввода происходит перерасчет
	// данных по конкретному ряду - вычисление разницы данных по данному ряду, внесение новых значений в итоговые суммы ряда , внесение новых
	// значений в итоговые суммы таблицы
    tbl:false,
    tbl_model:false,
	tbl_total_row:false,
	previos_data:{},
	complite_count:0,
	dscntDisclaimerProtocol:{},
	sizeExistsDisclaimerProtocol:{},
	primary_val:false
	,
	init_tbl:function(head_tbl_id,body_tbl_id){// метод запускаемый при наступлении события window.onload()
	                          // вызывает методы:
							  // collect_data - для создания модели таблицы
							  // set_interactive_cells - для установки интерактивных полей таблицы ( поля ввода, переключатели, маркеры)
	    this.head_tbl = document.getElementById(head_tbl_id);
	    this.body_tbl = document.getElementById(body_tbl_id);
		//alert(this.tbl);
		this.collect_data();
		this.set_interactive_cells();
	}
	,
	collect_data:function(){
	    // метод считывающий данные таблицы РТ и сохраняющий их в свойство this.tbl_model 
	    this.tbl_model={};
		
		// считываем данные из head_tbl
		var trs_arr = ($(this.head_tbl).children('tbody').length>0)? $(this.head_tbl).children('tbody').children('tr'):$(this.head_tbl).children('tr');
		
		var ln = trs_arr.length;
		for(var i = 0;i < ln;i++){ 
		
		    if(!trs_arr[i].getAttribute('row_id')) continue;
			
			var row_id = trs_arr[i].getAttribute('row_id');
			
			if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {}; 
			
		    if(row_id=='total_row') this.tbl_total_row = trs_arr[i]; 
			
			
			var tds_arr = $(trs_arr[i]).children('td');
			//alert(row_id);
			var ln2 = tds_arr.length;
			for(var j = 0;j < ln2;j++){
				//if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
				if(tds_arr[j].hasAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					if(type == 'glob_counter' || type == 'dop_details' || type == 'master_btn' || type == 'name') continue;
				    this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
				}
			}/**/
	    }
		
		//  считываем данные из body_tbl
        var trs_arr = ($(this.body_tbl).children('tbody').length>0)? $(this.body_tbl).children('tbody').children('tr'):$(this.body_tbl).children('tr');
		
		var ln = trs_arr.length;
		for(var i = 0;i < ln;i++){ 
			// если ряд не имеет атрибута row_id пропускаем его
		    if(!trs_arr[i].getAttribute('row_id')){					
				continue;
		    }
		    $(trs_arr[i]).find('td.art_name .pos_plank ').on('contextmenu click',function(e) {
		    	
		    	if(e.button == 2){
		    		var lang = new Array('green','yellow','red','blue','violet','grey');

		    		var obj = $(this);
		    		var pos_id = $(this).parent().parent().attr('pos_id');
		    		$("#context-menu").remove();
		    		event.preventDefault();
		    		// Создаем меню:


				    var context = $('<div/>', {
						'class': 'context-menu',
						'id':'context-menu'
						// Присваиваем блоку наш css класс контекстного меню:
					}).css({
						left: event.pageX+'px',
						// Задаем позицию меню на X
						top: event.pageY+'px'
						// Задаем позицию меню по Y
					})

					var menu = $('<ul/>');

					for(var t in lang){
						
						var element = $('<li/>',{
							'class':'js-color-'+lang[t],
							'data-color':lang[t],
							
							click:function(){
								for(var i in lang){
									if(i!=t){
										obj.removeClass('js-color-'+lang[i]);
									}
								}
								obj.addClass($(this).attr('class'));
								var color = $(this).attr('data-color');
								$.post('', {
									AJAX: 'change_main_rows_color',
									row_id:pos_id,
									val:color
								}, function(data, textStatus, xhr) {
								},'json');

								$(this).parent().parent().remove();
							}
						});
						// Добавляем пункты меню:
						menu.append(element);
					}

					context.append(menu).appendTo('body') // Присоединяем наше меню к body документа:					
					.show('fast').css('marginLeft','-60px');


					// клик вне элемента
					$(document).click( function(event){
				    	if( $(event.target).closest("#context-menu").length ) 
				    		return;
				    	$(".context-menu").remove();
				    	event.stopPropagation();
				    });



		    		// document.oncontextmenu = function() {return false;};  
			        // echo_message_js('сейчас вылетить птичко )))');
					// return false;
			    }
		    });
			
			var row_id = trs_arr[i].getAttribute('row_id');
			
			//
			var pos_id = (trs_arr[i].getAttribute('pos_id'))? trs_arr[i].getAttribute('pos_id'):false;
			var parent_pos_id = (pos_id)? pos_id:((typeof parent_pos_id !=='undefined')?parent_pos_id:false);
			
			// row_id==0 у вспомогательных рядов их пропускаем
			if(row_id==0) continue; //trs_arr[i].style.backgroundColor = '#FFFF00';
			
			if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {}; 
			
			// заносим информацию об исключении ряда из расчета
			/*if(row_id!='total_row'){
			    var expel = !!parseInt(trs_arr[i].getAttribute('expel'));
			    this.tbl_model[row_id].dop_data={'expel':expel};
			}*/
			var tds_arr = $(trs_arr[i]).children('td');
			var ln2 = tds_arr.length;
			for(var j = 0;j < tds_arr.length;j++){
				
				if(parent_pos_id){
					// устанавливаем id родительского ряда id товарной позиции
					if(!this.tbl_model[row_id].dop_data) this.tbl_model[row_id].dop_data = {};
					this.tbl_model[row_id].dop_data.parent_pos_id = parent_pos_id; 
				}
				
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					if(type == 'glob_counter' || type == 'master_btn' || type == 'name') continue;
					
					if(type == 'item_summ_in' || type == 'item_summ_out' || type == 'uslugi_summ_in' || type == 'uslugi_summ_out' || type == 'total_summ_in' || type == 'total_summ_out' || type == 'margin'){
						this.tbl_model[row_id][type] = parseFloat(tds_arr[j].getElementsByTagName('div')[0].innerHTML); 
						
					}
					else this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
	
					if(tds_arr[j].getAttribute('expel')){
						if(!this.tbl_model[row_id].dop_data)this.tbl_model[row_id].dop_data = {};
						if(!this.tbl_model[row_id].dop_data.expel)this.tbl_model[row_id].dop_data.expel = {};
						var expel = !!parseInt(tds_arr[j].getAttribute('expel'));
						if(type=='total_summ_out'){
							this.tbl_model[row_id].dop_data.expel.main=expel;
						}
					}
					if(tds_arr[j].hasAttribute('svetofor')){
						if(!this.tbl_model[row_id].dop_data)this.tbl_model[row_id].dop_data = {};
						this.tbl_model[row_id].dop_data.svetofor = tds_arr[j].getAttribute('svetofor');
					}


					/*// если это ряд содержащий абсолютные ссуммы сохраняем постоянные ссылки на его ячейки , чтобы затем вносить в них изменения
					// КАК ТО НЕ ПОЛУЧИЛОСЬ
					if(row_id=='total_row'){
					    if(!this.tbl_model['total_row_links']) this.tbl_model['total_row_links'] = {};
					    this.tbl_model['total_row_links'][tds_arr[j].getAttribute('type')] = tds_arr_1[j];
					}
					*/
				}		
			}
		
		}
	    //print_r(this.tbl_model);
		return true;  
	},
	set_interactive_cells:function(){
	    // Этот метод устанавливает необходимым ячекам различные интерактивные свойства
		// и навешивает обработчики событий
		var trs_arr = ($(this.head_tbl).children('tbody').length>0)? $(this.head_tbl).children('tbody').children('tr'):$(this.head_tbl).children('tr');
		
		for(var i in trs_arr){
	        if(trs_arr[i].nodeName=='TR'){
				//// console.log(trs_arr[i]);
				var tds_arr = $(trs_arr[i]).children('td');
				for(var j in tds_arr){
					if(tds_arr[j].nodeName == 'TD'){
				        if(i == 0 && tds_arr[j].getAttribute('swiched_cols')){// swiched_cols взаимно переключаемые ряды (ед/тираж, вход/выход)
						    $(tds_arr[j]).mousedown(function(){rtCalculator.swich_cols(this,'show'); }).mouseup(function(){ rtCalculator.swich_cols(this,'hide');});
					    }
					}
			    }	
		    }
		}
		

		var trs_arr = ($(this.body_tbl).children('tbody').length>0)? $(this.body_tbl).children('tbody').children('tr'):$(this.body_tbl).children('tr');
		
		for(var i in trs_arr){
			if(trs_arr[i].getAttribute){
				var block = (trs_arr[i].hasAttribute("block") && trs_arr[i].getAttribute("block")=='1')?true:false;
				var tds_arr = $(trs_arr[i]).children('td');
				for(var j in tds_arr){
					if(tds_arr[j].getAttribute){
						if(trs_arr[i].getAttribute("pos_id") && trs_arr[i].getAttribute("pos_id")!='0'){
							if(tds_arr[j].getAttribute('type') && tds_arr[j].getAttribute('type')=='name'){
								console.log('show_good_preview_set_up');
								var art_link = tds_arr[j].getElementsByTagName('A')[0];
								if(art_link) $(art_link).hover( rtCalculator.show_good_preview, rtCalculator.hide_good_preview );
							}	
						}
						if(trs_arr[i].getAttribute("row_id") && trs_arr[i].getAttribute("row_id")!='0'){
							if(tds_arr[j].getAttribute('editable') && !block){
								//tds_arr[j].onkeyup = this.make_calculations;
								tds_arr[j].onclick = function(e){ 
								   e = e || window.event;
								   // устанавливаем текущюю ячейку и сохраняем изначальное значение
		                           var cell = e.target || e.srcElement;

								   var val = parseFloat(cell.innerHTML);
								   if(val == 0) cell.innerHTML ='';
								
								   
								   if(cell.hasAttribute('type')){
									   if(cell.getAttribute('type')=="item_price_out"){
										   if(cell.innerHTML == '0.00') cell.innerHTML ='';
										   // проверяем есть ли скидка в ячейке
										   if(parseInt(($($(cell).parents('tr')).find( "td[discount_fieid]" ).text()).slice(0,-1)) != 0){
											   
												 echo_message_js('<span style="text-transform:lowercase">Для редактирования исходящей стоимости обнулите скидку/наценку</span>','system_message'); 
												  
												
											}
									   }
								   }
								}
								tds_arr[j].onfocus = function(e){ 
								   e = e || window.event;
								   // устанавливаем текущюю ячейку и сохраняем изначальное значение
		                           rtCalculator.cur_cell = e.target || e.srcElement;
								   rtCalculator.primary_val = rtCalculator.cur_cell.innerHTML;
                                   // устанавливаем текущюю ячейку
								   rtCalculator.changes_in_process = true;
								   
                                   if(rtCalculator.cur_cell.getAttribute('type')!= 'quantity'){
									   $(rtCalculator.cur_cell).addClass('rt_cell_bg');
									   $(rtCalculator.cur_cell).next().addClass('rt_cell_bg_next');
								   }
								   if(rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   $(rtCalculator.cur_cell).addClass('rt_qcell_bg');
								   }
								}
								tds_arr[j].onkeydown = function(e){ 
								   e = e || window.event;
								   // устанавливаем текущюю ячейку и сохраняем изначальное значение
		                           var cur_cell = e.target || e.srcElement;
								   rtCalculator.previous_val = cur_cell.innerHTML;

								}
								tds_arr[j].onkeyup = function(e){
									
								   //if(!rtCalculator.cur_cell) location.reload();
								   if(rtCalculator.cur_cell  &&  rtCalculator.cur_cell.hasAttribute('type') && rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   // alert(1);
									   rtCalculator.checkQuantity();
								   }
								   else{
	
									   // alert(2);
									   rtCalculator.check();
									   // запускаем таймер по истечению которого вызываем функцию rtCalculator.complite_input
									   // отправляющую данные на сервер
									   //if(rtCalculator.cur_cell.getAttribute('type') && rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   if(!rtCalculator.complite_timer) rtCalculator.complite_timer = setTimeout(rtCalculator.complite_input,2000); 
								   }
								   
								}
								tds_arr[j].onblur = function(e){ 
								   e = e || window.event;
								   // устанавливаем текущюю
		                           var cell = e.target || e.srcElement;
									
								 
								   // если это не ячейка количества, вызываем complite_input() потому что такие ячейки обрабатываются
								   // по таймеру, для ячейки количества это не нужно потому что она обрабатывается при каждом keyup
								   if(rtCalculator.cur_cell.getAttribute('type')){ 
									   if(rtCalculator.cur_cell.getAttribute('type')!= 'quantity'){
										   var val = parseFloat(rtCalculator.cur_cell.innerHTML);
										   if(isNaN(val) || val==0) rtCalculator.cur_cell.innerHTML = '0.00';
										   rtCalculator.complite_input();
									   }
									   else{
										   var val = parseInt(rtCalculator.cur_cell.innerHTML);
										   if(isNaN(val) || val==0) rtCalculator.cur_cell.innerHTML = '0';
									   }
								   } 
								   if(rtCalculator.cur_cell.getAttribute('type')!= 'quantity'){
									   $(rtCalculator.cur_cell).removeClass('rt_cell_bg');
									   $(rtCalculator.cur_cell).next().removeClass('rt_cell_bg_next');
								   }
								   if(rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   $(rtCalculator.cur_cell).removeClass('rt_qcell_bg');
								   }
								}
								if(tds_arr[j].getAttribute('editable') =='true') tds_arr[j].setAttribute("contenteditable",true);
								tds_arr[j].style.outline="none";
							}
							if(tds_arr[j].getAttribute('expel')){
								//tds_arr[j].onclick = this.expel_value_from_calculation;
							}
							if(tds_arr[j].getAttribute('svetofor')){
								//// console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('img')[0]) $(tds_arr[j].getElementsByTagName('img')[0]).mouseenter(this.show_svetofor);
								
							}
							if(tds_arr[j].getAttribute('raschet_status')){
								$(tds_arr[j]).mouseenter(function(e){ statusTooltip.schedule(this)});
							}
							if(tds_arr[j].getAttribute('uslugi_btn')){
								//// console.log(j+' svetofor');
								if(!block){
									if(tds_arr[j].getElementsByTagName('span')[0]) tds_arr[j].getElementsByTagName('span')[0].onclick = this.launch_uslugi_panel;
									if(tds_arr[j].getElementsByTagName('span')[1]) tds_arr[j].getElementsByTagName('span')[1].onclick = this.launch_uslugi_panel_2;
								}
								if(tds_arr[j].getAttribute('print_exists_flag') == '1' || tds_arr[j].getAttribute('uslugi_exists_flag') == '1'){
									$(tds_arr[j]).mouseenter(function() {this.getElementsByTagName('div')[0].style.display = 'block';}).mouseleave(function() {this.getElementsByTagName('div')[0].style.display = 'none';});
								}
							}
						}
					}
				}
			}
		}
	}
	,
	show_good_preview:function(e){
	    e = e || window.event;
        var cursor_pos_left = e.clientX;
		var cur_cell = e.target || e.srcElement;
        //console.log('show_good_preview_start');
		rtCalculator.show_good_row_id = $(cur_cell).parents('tr').attr('pos_id');
		var art_id = $(cur_cell).parents('tr').attr('art_id');
		rtCalculator.good_preview_timer = setTimeout(show,200); 
		//console.log('show_good_preview',art_id);
		function show(){

			var container = document.getElementById('goodPreviewWin'+rtCalculator.show_good_row_id);
			if(!container){
				var container = document.createElement('DIV');
				container.id = 'goodPreviewWin'+rtCalculator.show_good_row_id;
				container.style.position = 'absolute';
				container.style.border = '#CCC solid 2px';
				
				var url = OS_HOST+'?' + addOrReplaceGetOnURL('show_good_preview=1&art_id='+art_id+'&main_row_id='+rtCalculator.show_good_row_id);
				//console.log('show_good_preview_url',url);
				rtCalculator.send_ajax(url,callback);
				
				function callback(response){
					//alert(response);
					//console.log('show_good_preview_response',response);
					var img = document.createElement('IMG');
					img.style.height = '90px';
					img.src = response;
					//img.src = 'http://www.apelburg.ru/img/no_image.jpg';
					container.appendChild(img);
				    document.body.appendChild(container);
				}
			}
			else{
			    container.style.display = 'block';
			}
			var pos = rtCalculator.getPos(cur_cell);
			
			container.style.top = (pos[0]+20)+"px";
			container.style.left = (typeof cursor_pos_left != 'undefined')? (cursor_pos_left+2)+"px" :(pos[1]+40)+"px";
			
		}
	}
	,
	hide_good_preview:function(e){
		if(rtCalculator.good_preview_timer) clearTimeout(rtCalculator.good_preview_timer);
		if(document.getElementById('goodPreviewWin'+rtCalculator.show_good_row_id)){
			document.getElementById('goodPreviewWin'+rtCalculator.show_good_row_id).style.display = 'none';
		}
	}
	,
	launch_uslugi_panel:function(e){
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		// метод срабатывающий первым ( изначально ) при клике по значку обозначаещему услуги в РТ
		
		//if(cell.parentNode.getAttribute('calc_btn') == 'print') alert('калькулятор нанесения логотипа');
		//if(cell.parentNode.getAttribute('calc_btn') == 'extra') alert('калькулятор доп. услуг');
		// определяем из какой ячейки сделан вызов калькулятора ( могут быть - нанесение или доп услуги)
		var calculator_type = cell.parentNode.getAttribute('calc_btn');
		
		// пользователь 
		var creator_id = $('*[user_id]').attr('user_id');

        // родительский тэг tr
		var trTag = cell.parentNode.parentNode;
		// id - артикула
		var art_id = trTag.getAttribute('art_id');
		// id - родительского ряда (ряда рассчета) (ряда в таблице os__rt_dop_data)
		var dop_data_row_id = trTag.getAttribute('row_id');
		//var discount =  ($($(cell).parents('tr')).find( "td[discount_fieid]" ).text()).slice(0,-1);
		var discount = ($(trTag).find( "td[discount_fieid]" ).text()).slice(0,-1);
		// определяем количество товара (берем данные из ячейки quantity данного ряда)
		var tdsArr = $(trTag).children('td');
		//alert(tdsArr);
		var ln = tdsArr.length;
		for(var i =0;i < ln;i++){
			if(tdsArr[i].getAttribute('type') && tdsArr[i].getAttribute('type')=='quantity'){
				var quantity = parseInt(tdsArr[i].innerHTML);
			} 
		}
		if(typeof quantity === 'undefined'){
			echo_message_js('Не удается получить данные о количестве товара!!!','system_message',3800);
			return;
		}
		if(quantity === 0){
		    echo_message_js('Расчет не возможен, тираж 0шт. !!!','system_message',3800);
			return;
		}
		
		
		// $.post('', {
		// 	AJAX: 'get_service_center'
		// }, function(data, textStatus, xhr) {
		// 	if(data['myFunc'] !== undefined && data['myFunc'] == 'show_SC'){
		// 		var buttons = new Array();
		// 		buttons.push({
		// 		    text: 'Список услуг',
		// 		    click: function() {
		// 				// подчищаем за собой
		// 				// $.notify("Вызов калькулятора",'info');
		// 				printCalculator.start_calculator({"calculator_type":"extra","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
		// 		    }
		// 		});	

		// 		buttons.push({
		// 		    text: 'Калькулятор',
		// 		    click: function() {
		// 				// подчищаем за собой
		// 				// $.notify("Вызов калькулятора",'info');
		// 				printCalculator.start_calculator({"calculator_type":"print","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
		// 		    }
		// 		});	


		// 		buttons.push({
		// 		    text: 'Закрыть',
		// 		    click: function() {
		// 				// подчищаем за собой
		// 				$(this).dialog("destroy");
		// 		    }
		// 		});	


		// 		show_SC(data,buttons);	
		// 	}				
		// 	standard_response_handler(data);
		// },'json');

		$.SC_sendAjax();return;
		var dialog = $('<div class="uslugi_panel"></div>');
		var btn1 = document.createElement('DIV');
		btn1.className = 'ovalBtn';
		btn1.innerHTML = 'Список услуг';
		btn1.onclick =  function(){ 
		     $(dialog).remove();
			 printCalculator.start_calculator({"calculator_type":"extra","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
	    };
		dialog.append(btn1);
	
		var btn2 = document.createElement('DIV');
		btn2.className = 'ovalBtn';
		btn2.innerHTML = 'Калькулятор';
		btn2.onclick =  function(){ 
		     $(dialog).remove();
			 printCalculator.start_calculator({"calculator_type":"print","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
	    };
		dialog.append(btn2);
		
		$('body').append(dialog);
		$(dialog).dialog({modal: true, width: 500,minHeight : 120 ,title: 'Выберите вид расчета',close: function() {$(this).remove();} });
		$(dialog).dialog('open');
	}
	,
	launch_uslugi_panel_2:function(e){
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		// метод срабатывающий первым ( изначально ) при клике по значку обозначаещему услуги в РТ
		
		//if(cell.parentNode.getAttribute('calc_btn') == 'print') alert('калькулятор нанесения логотипа');
		//if(cell.parentNode.getAttribute('calc_btn') == 'extra') alert('калькулятор доп. услуг');
		// определяем из какой ячейки сделан вызов калькулятора ( могут быть - нанесение или доп услуги)
		var calculator_type = cell.parentNode.getAttribute('calc_btn');
		
		// пользователь 
		var creator_id = $('*[user_id]').attr('user_id');

        // родительский тэг tr
		var trTag = cell.parentNode.parentNode;
		// id - артикула
		var art_id = trTag.getAttribute('art_id');
		// id - родительского ряда (ряда рассчета) (ряда в таблице os__rt_dop_data)
		var dop_data_row_id = trTag.getAttribute('row_id');
		//var discount =  ($($(cell).parents('tr')).find( "td[discount_fieid]" ).text()).slice(0,-1);
		var discount = ($(trTag).find( "td[discount_fieid]" ).text()).slice(0,-1);
		// определяем количество товара (берем данные из ячейки quantity данного ряда)
		var tdsArr = $(trTag).children('td');
		//alert(tdsArr);
		var ln = tdsArr.length;
		for(var i =0;i < ln;i++){
			if(tdsArr[i].getAttribute('type') && tdsArr[i].getAttribute('type')=='quantity'){
				var quantity = parseInt(tdsArr[i].innerHTML);
			} 
		}
		if(typeof quantity === 'undefined'){
			echo_message_js('Не удается получить данные о количестве товара!!!','system_message',3800);
			return;
		}
		if(quantity === 0){
		    echo_message_js('Расчет не возможен, тираж 0шт. !!!','system_message',3800);
			return;
		}
		
		var dialog = $('<div class="uslugi_panel"></div>');
		var btn1 = document.createElement('DIV');
		btn1.className = 'ovalBtn';
		btn1.innerHTML = 'Список услуг';
		btn1.onclick =  function(){ 
		     $(dialog).remove();
			 printCalculator.start_calculator({"calculator_type":"extra","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
	    };
		dialog.append(btn1);
	
		var btn2 = document.createElement('DIV');
		btn2.className = 'ovalBtn';
		btn2.innerHTML = 'Калькулятор';
		btn2.onclick =  function(){ 
		     $(dialog).remove();
			 printCalculator.start_calculator({"calculator_type":"print","cell":cell,"quantity":quantity,"art_id":art_id,"dop_data_row_id":dop_data_row_id,"discount":discount,"trTag":trTag,"creator_id":creator_id});	
	    };
		dialog.append(btn2);
		
		$('body').append(dialog);
		$(dialog).dialog({modal: true, width: 500,minHeight : 120 ,title: 'Выберите вид расчета',close: function() {$(this).remove();} });
		$(dialog).dialog('open');
	}
	,
	show_complite_saving_window:function(){
		echo_message_js('изменения сохранены','system_message',800);
	}
	,
	complite_input:function(){
		// метод срабатывает либо при событие onblur в ячейках ввода данных для расчета ( тем самым он срабатывает когда ввод данных завершен
		// либо при срабатывании таймера запускающегося при onkeyup в ячейке что позволяет отправлять данные из ячейки с некоторыим интервалом
		// используем этот момент для отправки измененных данных в базу данных на сервер для синхронизации изменений ) 
		
		if(rtCalculator.complite_timer){
			 clearTimeout(rtCalculator.complite_timer);
			 rtCalculator.complite_timer = null;
		}
		if(rtCalculator.complite_saving_window_timer){
			 clearTimeout(rtCalculator.complite_saving_window_timer);
			 rtCalculator.complite_saving_window_timer = null;
		}
		 
		 // console.log('№'+(++rtCalculator.complite_count));
		 // console.log(1);
		// получаем значение ячейки
		var type = rtCalculator.cur_cell.getAttribute('type');
		var last_val = rtCalculator.cur_cell.innerHTML;
		
		
		// сравниваем текущее значение с первоначальным, если они равны значит окончательные изменения не были произведены
		// в таком случае ничего не меняем в базе - прерываем дальнейшее выполнение
		if(rtCalculator.primary_val == last_val){
			rtCalculator.changes_in_process = false;
			return;
		}
		// console.log(rtCalculator.primary_val+' '+last_val);
		
		var row_id = rtCalculator.cur_cell.parentNode.getAttribute('row_id');
		var discount = (rtCalculator.tbl_model[row_id].discount)?rtCalculator.tbl_model[row_id].discount:0;

		
		if(type == 'item_price_out'){
			var prop = 'price_out';
			if(discount!=0){
				prop = 'discount';
				last_val = discount;
			}
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+row_id+'","discount":"0","prop":"price_out","val":"'+last_val+'"}');
		}
		else if(type == 'item_price_in'){
			var prop = 'price_in';
			// формируем url для AJAX запроса
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+row_id+'","prop":"'+prop +'","val":"'+last_val+'"}');
		}
		rtCalculator.send_ajax(url,callback);
	
		function callback(request_response){ 
		    //alert(request_response);
		    rtCalculator.changes_in_process = false;
			//echo_message_js('изменения сохранены','system_message',800);
			
			rtCalculator.complite_saving_window_timer = setTimeout(rtCalculator.show_complite_saving_window,2000); 
		    /*cell.className = cell.className.slice(0,cell.className.indexOf("active")-1);*/
			// console.log(2);
		}
	}
	,
	shDscntDisclaimer:function(cur_cell,row_id,discount){
		rtCalculator.dscntDisclaimerProtocol[row_id] = true;
		
		tooltip = document.createElement("div");  
        tooltip.style.position = "absolute";  
        tooltip.id = "dscntDisclaimer"+row_id; 
        tooltip.className = "rtDiscountTooltip"; 
		tooltip.innerHTML = "на ячейку установлена "+((discount<0)?"скидка":"наценка")+"<br>введеная цена будет не верна1"; 
		var pos = rtCalculator.getPos(cur_cell);
		tooltip.style.top = pos[0] + 2 +"px";
		//tooltip.style.left = pos[1] -200 +"px";
		document.body.appendChild(tooltip);
		tooltip.style.left = (pos[1] -tooltip.offsetWidth -55) +"px";
		
		var closeDscntTimer = setTimeout(closeDscntDisclaimer1,4000); 
		var closeDscntTimer = setTimeout(closeDscntDisclaimer2,8000); 
		function closeDscntDisclaimer1(){
		    if(document.getElementById('dscntDisclaimer'+row_id)) document.getElementById('dscntDisclaimer'+row_id).parentNode.removeChild(document.getElementById('dscntDisclaimer'+row_id));	
			
		}
		function closeDscntDisclaimer2(){
			delete rtCalculator.dscntDisclaimerProtocol[row_id];	
		}
	}
	,
	sizeExistsDisclaimer:function(cur_cell,row_id){
		rtCalculator.sizeExistsDisclaimerProtocol[row_id] = true;
		
		tooltip = document.createElement("div");  
        tooltip.style.position = "absolute";  
		tooltip.style.textAlign = "left";
        tooltip.id = "sizeExistsDisclaimer"+row_id; 
        tooltip.className = "rtDiscountTooltip"; 
		tooltip.innerHTML = "Изделие содержит размерный ряд<br>для изменения тиража<br>пройдите в карточку артикула"; 
		var pos = rtCalculator.getPos(cur_cell);
		tooltip.style.top = pos[0] - 10 +"px";
		//tooltip.style.left = pos[1] -200 +"px";
		document.body.appendChild(tooltip);
		tooltip.style.left = (pos[1] -tooltip.offsetWidth + 5) +"px";
		
		var closeDscntTimer = setTimeout(closeSizeExsDisclaimer1,6000); 
		var closeDscntTimer = setTimeout(closeSizeExsDisclaimer2,10000); 
		function closeSizeExsDisclaimer1(){
		    if(document.getElementById('sizeExistsDisclaimer'+row_id)) document.getElementById('sizeExistsDisclaimer'+row_id).parentNode.removeChild(document.getElementById('sizeExistsDisclaimer'+row_id));	
			
		}
		function closeSizeExsDisclaimer2(){
			delete rtCalculator.sizeExistsDisclaimerProtocol[row_id];	
		}
	}
	,
	getPos:function(element){
		 var y= 0;
		   var х = 0;
		   for(var e = element; e != null; e = e.offsetParent){ // Цикл по offsetParent
			  y += e.offsetTop;
			  х += e.offsetLeft;
		   }
		
		   for(e = element.parentNode; e && e != document.body; e = e.parentNode){
			  if(e.scrollTop) y -= e.scrollTop; 
		   }
		   return [y,х];
	}
	,
	checkQuantity:function(){// корректировка значений вводимых пользователем

		var cell = rtCalculator.cur_cell;
		var val = cell.innerHTML;
		var row_id = cell.parentNode.getAttribute('row_id');
		var cur_tr = cell.parentNode;
		var tds_arr = $(cur_tr).children('td');
		var printsExists = false;
		var extraExists = false;
		
		
		
		
		if(val != '' && val != '&nbsp;'){ 
		    var result = correctToInt(val);
		    if(result != 0) setCaretToPos2(cell,result);
		}
		
	    
		// проверяем есть ли в ряду расчеты нанесения
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('print_exists_flag') && tds_arr[j].getAttribute('print_exists_flag') == '1'){
			    printsExists = true;
			}
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('uslugi_exists_flag') && tds_arr[j].getAttribute('uslugi_exists_flag') == '1'){
				extraExists = true;
			}	
		}
        //////////////////////////////////// card rt
		// метод может вызываться также из карточки товара созначением - card
		//rtCalculator.makeQuantityCalculations('rt',cell.innerHTML,row_id,printsExists,extraExists,cell);
		
		// КНОПКА сохранения изменений в ячейке тиража
		// если ранее была опраделена ячека в которой происходили изменения тиража но она не равна текущей ячейке
		// значит пользователь перешел в другую ячеку - удаляем предыдущую кнопку сохранить и стираем её данные
		//if(typeof rtCalculator.saveQuantityTarget !== 'undefined' && rtCalculator.saveQuantityTarget != cell){
           // $(rtCalculator.saveQuantityBtn).remove();
			//delete rtCalculator.saveQuantityBtn;
		//}
		// если кнопка сохранить отсутсвует - создаем её
		//if(typeof rtCalculator.saveQuantityBtn === 'undefined'){

            //rtCalculator.saveQuantityTarget = cell;
			
			//rtCalculator.saveQuantityBtn = document.createElement('DIV');
			//rtCalculator.saveQuantityBtn.className = 'rtSaveQuantityBtn';
		    //rtCalculator.saveQuantityBtn.innerHTML = "save";
		    //rtCalculator.saveQuantityBtn.onclick = function(){ 
	   var nextTd = $(cell).next()[0];
	   
	   if($(nextTd).find("div.rtSaveQuantityBtn").length == 0){
			var saveQuantityBtn = document.createElement('DIV');
			saveQuantityBtn.className = 'rtSaveQuantityBtn';
		    saveQuantityBtn.innerHTML = "save";
		    saveQuantityBtn.onclick = function(){ 
			    //alert(this);
				//$(this).remove();
				this.parentNode.removeChild(this);
			    //delete rtCalculator.saveQuantityBtn;
				//////////////////////////////////// card rt //////////////////////////////////// 
		        // метод может вызываться также из карточки товара созначением - card
				rtCalculator.makeQuantityCalculations('rt',cell.innerHTML,row_id,printsExists,extraExists,cell);
			}
			// соседняя ячека таблицы, кнопку добавляем в неё
			
			//$(nextTd).append(rtCalculator.saveQuantityBtn); 
			$(nextTd).append(saveQuantityBtn); 
		}
		//}		
	
		
		
		
		
	
		function correctToInt(str){// корректировка значений вводимых пользователем в поле ввода типа Integer
		    var wrong_input = false;
            var pos = 0;
			
			// если строка содержит что-то кроме цифры или точки вырезаем этот символ
			var pattern = /[^\d]+/; 
			var result = pattern.exec(str);
		    if(result !== null){ 
			    wrong_input = true;
				var substr_arr = str.split(result[0]);
				pos =  substr_arr[0].length;
				str =  substr_arr[0] + substr_arr[1];
			    
		    }
			if(str.length>7){ wrong_input = true;  str = '1000000'; pos = 7;}
			
		    // если был выявлен некорректный ввод исправляем содержимое ячейки 
			if(wrong_input) cell.innerHTML = str ;  
			
			return pos;
		}

		function setCaretToPos2(el, pos) {
		    var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], pos);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}


		return true;//parseInt(str);
    }
	,
	check:function(){// корректировка значений вводимых пользователем
        
		var cell = rtCalculator.cur_cell;
		
		var val = cell.innerHTML;
		if(val != '' && val != '&nbsp;'){ 
		    var result = correctToFloat(val);
		    if(result != 0) setCaretToPos2(cell,result);
		}

		rtCalculator.save_previos_data(cell);
		rtCalculator.make_calculations(cell);
		
		//if(cell.innerHTML !='') var result = correctToInt(cell.innerHTML);
		
	    function correctToFloat(str){// корректировка значений вводимых пользователем в поле ввода типа Float
		    var wrong_input = false;
			var pos = 0;

			// если строка содержит запятую меняем её на точку
			var pattern = /,/; 
		    if(str.match(pattern)){ wrong_input = true;  pos =  str.indexOf(',')+1; str =  str.replace(',','.');}
			
            /*			
			var pattern = /^[^\d]+$/; 
			var result = pattern.exec(str);
		    if(result !== null){ wrong_input = true; str = '0'; pos = 0; }*/
			
			// если строка содержит что-то кроме цифры или точки 
			var pattern = /[^\d\.]+/; 
			var result = pattern.exec(str);
		    if(result !== null){ 
			    wrong_input = true;
				if(str.length==1){ 
				    // если это единственный символ в строке - заменяем его тем что было до его ввода
				   wrong_input = true; str =  rtCalculator.previous_val ; pos = 0;
				}
				else{
					// если это не единственный символ в строке - вырезаем его
					var substr_arr = str.split(result[0]);
					pos =  substr_arr[0].length;
					str =  substr_arr[0] + substr_arr[1];
				}
				
			    
		    }
			
		
			//alert(str);
			// если строка содержит более одной точки вырезаем оставляем только одну точку
		    /*	
		    var pattern = /\./g; 
			var counter = 0;
			var result;
			while ((result = pattern.exec(str)) !== null) {
			  if(counter++>0){
				  wrong_input = true;
				  str =  str.replace('.','');
				  pos =  str.indexOf('.');
			  }
			 
			}*/

			//if(str.length==1){ wrong_input = true;  str += '.00'; pos = 1;}
			
			//  если после точки введено менее или более 2 цифр исправляем до 2-х
			// ЗДЕСЬ НУЖНО РЕШИТЬ ВОПРОС УСТАНОВКИ КУРСОРА В НУЖНОЕ МЕСТО ПОКА ПЕРЕНОСИТСЯ В КОНЕЦ
			var pattern = /^\d+\.\d{2}$/; 
		   // if(!str.match(pattern)){ wrong_input = true;  str = parseFloat(str).toFixed(2); pos = str.length;}
			
			// если величина числа больше допустимого - обрезаем его
		    if(str.length>12){ wrong_input = true;  str = '100000000.00'; pos = 12;}
			
			// если величина числа больше допустимого - обрезаем его
		   
		//alert(str);
			// если был выявлен некорректный ввод исправляем содержимое ячейки 
			if(wrong_input) cell.innerHTML = str;
			
			//alert(pos);
			return pos; 
		}
		
		function setCaretToPos2(el, pos) {
		    var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], pos);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}


		return true;//parseInt(str);
    }
	,
	makeQuantityCalculations:function(source,quantity,row_id,printsExists,extraExists,cell){
	    if(printsExists || extraExists){// если есть нанесение или доп услуги то нужно отправлять запрос на сервер для обсчета нанесений в соответсвии с новым тиражом
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&change_quantity_and_calculators=1&quantity='+quantity+'&id='+row_id+'&print='+printsExists+'&extra='+extraExists+'&source='+source,'section');
			//alert(url);
		    rtCalculator.send_ajax(url,callbackPrintsExists);
		}
		else{// отправляем запрос на изменение только лишь значения тиража в базе данных 
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&change_quantity=1&quantity='+quantity+'&id='+row_id+'&source='+source,'section');
			//alert(url);
		    rtCalculator.send_ajax(url,callbackOnlyQuantity);
		}
						
		function callbackPrintsExists(response){
			
			// alert(response);
			
			try {  var response_obj = JSON.parse(response); }
			catch (e) { alert('неправильный формат данных in rtCalculator.makeQuantityCalculations() ошибка JSON.parse(response)'); }
			
			if(response_obj){
				
				if(response_obj.warning && response_obj.warning.calculators_checking){
					
					var notes = [];
					for( var prop in response_obj.warning.calculators_checking){
						
						if(prop == 'manual_calc_exists'){
							notes.push('в расчетах используется Ручной калькулятор');
						}
						if(prop == 'free_calc_exists'){
							notes.push('в расчетах используется калькулятор Дежурная услуга');
						}
						if(prop == 'united_calculations'){
							notes.push('есть Объединеные расчеты');
						}
						if(prop == 'lackOfQuantity'){
							var str ='';
							for(var index in response_obj.warning.calculators_checking.lackOfQuantityDetails){
								 str += (parseInt(index)+1)+'). '+response_obj.warning.calculators_checking.lackOfQuantityDetails[index].print_type+', мин тираж - '+response_obj.warning.calculators_checking.lackOfQuantityDetails[index].minQuantity+"<br>";  
							}
							notes.push('<br>Тираж меньше минимального тиража для нанесения(ий):<br>'+str+'стоимость будет пересчитана как для минимального тиража');
						}
						if(prop == 'outOfLimit'){
							var str ='';
							for(var index in response_obj.warning.calculators_checking.outOfLimitDetails){
								 str += (parseInt(index)+1)+'). '+response_obj.warning.calculators_checking.outOfLimitDetails[index].print_type+', лимит тиража - '+response_obj.warning.calculators_checking.outOfLimitDetails[index].limitValue+"<br>";  
							}
							notes.push('<br>Из-за превышения максимального тиража<br>автоматические калькуляторы в следующих расчетах будут переведены в ручной режим:<br>'+str);
						}
						if(prop == 'needIndividCalculation'){
							var str ='';
							for(var index in response_obj.warning.calculators_checking.needIndividCalculationDetails){
								 str += (parseInt(index)+1)+'). '+response_obj.warning.calculators_checking.needIndividCalculationDetails[index].print_type+"<br>";  
							}
							notes.push('Такой тираж не может быть установлен!!!<br>Потому что имеются нанесения для которых не возможно расчитать цену<br> - для этих нанесений требуется индивидуальный расчет :<br>'+str);
						}
					}
				}
				
				if(notes && notes.length>0){
				
					 ///var dialog = $('<div>Внимание :<br>'+ notes.join(', ')+'</div>');
					 var dialog = $('<div>проверь расчет ручного калькулятора.<br>'+ notes.join('<br>')+'</div>');
					 
					 $('body').append(dialog);
					 $(dialog).dialog({
									  modal: true, 
									  width: 500,
									  minHeight : 200 ,
									  closeOnEscape: false,
									  buttons: [{text: "Да",
												click: function(){
														// отправляем повторный запрос с маркером ignore_calculators_checking
														$(this).dialog("close");
														url += '&ignore_calculators_checking=1';
														rtCalculator.send_ajax(url,callbackPrintsExists);
														var art_id = $($(cell).parents('tr')[0]).attr('art_id');//
														
														var pos_plank = $($("tr[art_id="+art_id+"]")[0]).find( "div.pos_plank" );
														pos_plank.addClass('js-color-red');
														pos_plank.addClass('js--icon-alarm-services');
														pos_plank.html(pos_plank.html()+'<div class="confirmCalcTip">проверьте цены, введенные вручную</div>');
													
													}},
											   {text: "Отмена",
											   click: function(){
												       // возвращаемся к предыдущему состоянию
													   $(this).dialog("close");
													   if(source=='rt'){
													        //cell.innerHTML = response_obj.old_quantity;
													        cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
													   }
													   if(source=='card'){
														    response_rtCalculator_makeQuantityCalculationsCancel();
													   }
												   }}]
									});
					 $(dialog).dialog('open');
					 $('.ui-dialog-titlebar-close').click(function(){ 
													   if(source=='rt'){
													        cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
													   }
													   if(source=='card'){
														    response_rtCalculator_makeQuantityCalculationsCancel();
													   }
											       });
					 
					 return;
				}
				
				if(response_obj.warning && response_obj.warning=='size_exists'){
					// если найдено что позиция имеет какие-либо размеры изменение количества должно быть отменено
					// возвращаем в ячейку прежнее значение
					//alert(response_obj.warning);
					if(source=='rt'){
						rtCalculator.sizeExistsDisclaimer(cell,row_id);
						cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
						return;
					}
				}
				// rtCalculator.quantityCalculationsResponseFull($($("tr[row_id="+id+"]")[0]).find( "td[type=quantity]" )[0],id,response_obj[id]);
				if(source=='rt'){
					if(response_obj.united_calculations){
						rtCalculator.changes_in_process = false;
						location.reload();
					}
					else rtCalculator.quantityCalculationsResponseFull(cell,row_id,response_obj); 
				}
				if(source=='card')rtCalculator.cardQuantityCalculationsResponseFull(cell,row_id,response_obj);

				return;
			}
		}
		function callbackOnlyQuantity(response){
			
		    // alert(response);
			
			if(source=='rt')rtCalculator.quantityCalculationsResponse(cell,row_id,response);
			if(source=='card')rtCalculator.cardQuantityCalculationsResponse(cell,row_id,response);
			
		}
	}
	,
	show_warning_window:function(){
		if(typeof rtCalculator.show_warning_window_text !== 'undefined') echo_message_js(rtCalculator.show_warning_window_text,'rt_message',5400);
	}
	,
	cardQuantityCalculationsResponse:function(cell,row_id,response){
		// сохраняем размерную таблицу
		save_all_table_size();
	}
	,
	cardQuantityCalculationsResponseFull:function(cell,row_id,response_obj){
		response_rtCalculator_makeQuantityCalculations(cell,row_id,response_obj);
	}
	,
	quantityCalculationsResponseFull:function(cell,row_id,response_obj){
		    
		    rtCalculator.save_previos_data(cell);
			// Вносим изменения в hmlt
			
			rtCalculator.tbl_model[row_id]['quantity'] =  parseInt(cell.innerHTML) ;
			
			if(response_obj.itog_values){
				rtCalculator.tbl_model[row_id]["uslugi_price_in"] = parseFloat(response_obj.itog_values.price_in);
				rtCalculator.tbl_model[row_id]["uslugi_price_out"] = parseFloat(response_obj.itog_values.price_out);
				rtCalculator.tbl_model[row_id]["uslugi_summ_in"] = parseFloat(response_obj.itog_values.summ_in);
				rtCalculator.tbl_model[row_id]["uslugi_summ_out"] = parseFloat(response_obj.itog_values.summ_out);
			}
		
		    rtCalculator.make_calculations(cell);
			
			//echo_message_js('изменения сохранены','system_message',800);
			if(rtCalculator.complite_saving_window_timer){
				 clearTimeout(rtCalculator.complite_saving_window_timer);
				 rtCalculator.complite_saving_window_timer = null;
			}
			rtCalculator.complite_saving_window_timer = setTimeout(rtCalculator.show_complite_saving_window,2000);
			
			rtCalculator.changes_in_process = false;
	}
	,
	quantityCalculationsResponse:function(cell,row_id,response){
		
		try {  var response_obj = JSON.parse(response); }
		catch (e) {}
		if(response_obj){
			if(response_obj.warning || response_obj.warning=='size_exists'){
				// если найдено что позиция имеет какие-либо размеры изменение количества должно быть отменено
				// возвращаем в ячейку прежнее значение
				// alert(response_obj.warning);
				rtCalculator.sizeExistsDisclaimer(cell,row_id);
				cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
				return;
			}
		}
		
		//alert('callbackOnlyQuantity');
		
		rtCalculator.save_previos_data(cell);
		rtCalculator.make_calculations(cell);
		
		//echo_message_js('изменения сохранены','system_message',800);
		if(rtCalculator.complite_saving_window_timer){
			 clearTimeout(rtCalculator.complite_saving_window_timer);
			 rtCalculator.complite_saving_window_timer = null;
		}
		rtCalculator.complite_saving_window_timer = setTimeout(rtCalculator.show_complite_saving_window,2000);
		
		rtCalculator.changes_in_process = false;
	}
	,
	save_previos_data:function(cell){

		// получаем id ряда
		var row_id = cell.parentNode.getAttribute('row_id');
		var type = cell.getAttribute('type');

		// сохраняем текущиме данные до изменения ячеек
		for(var prop in rtCalculator.tbl_model[row_id]){
			rtCalculator.previos_data[prop] = rtCalculator.tbl_model[row_id][prop];
		}
	}
	,
    make_calculations:function(cell){

		// получаем id ряда
		var row_id = cell.parentNode.getAttribute('row_id');
		var type = cell.getAttribute('type');
		var value = cell.innerHTML;
		
		//**print_r(rtCalculator.tbl_model[row_id]);
	
		
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		value = (value == '' || value == '&nbsp;')?0:value;
		rtCalculator.tbl_model[row_id][type] = (type=='quantity')? parseInt(value):parseFloat(value);
		
		// производим пересчет ряда
		rtCalculator.calculate_row(row_id,type);
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rtCalculator.change_html(row_id);

	}
	,
	calculate_row:function(row_id,type){
	    // метод который рассчитывает итоговые суммы конкретного ряда таблицы и если ряд не исключен из итоговых расчетов
		// делает изменения в ряду содержащем абсолютные суммы total_row
		// методу передается id затронутого ряда таблицы, дальше метод выделят этот ряд в модели таблицы rtCalculator.tbl_model
		// и рассчитывает его
		var row = rtCalculator.tbl_model[row_id];
		
		row['item_summ_in'] = row['quantity']*row['item_price_in'];
		row['item_summ_out'] = row['quantity']*row['item_price_out'];
		
		row['total_price_in']  = row['item_price_in']  + row['uslugi_price_in'];
		row['total_price_out'] = row['item_price_out'] + row['uslugi_price_out'];
		
		row['total_summ_in']  = row['item_summ_in']  + row['uslugi_summ_in'];
		row['total_summ_out'] = row['item_summ_out'] + row['uslugi_summ_out'];
		
		//if(type=='item_price_out') rtCalculator.tbl_model[row_id]['discount'] = (row['discount']!=0)? ((row['item_price_out']!=0)?(Math.round(((row['item_price_out']*100/(rtCalculator.previos_data['item_price_out']*100/(100+row['discount'])))-100)* 100) / 100): 0): 0;
	
		row['delta'] = row['total_summ_out']-row['total_summ_in'];
		row['margin'] = (row['total_summ_out']>0 && row['total_summ_in']>0)?((row['total_summ_out']-row['total_summ_in'])/row['total_summ_out'])*100:0;
		
		//for(var prop in row){ rtCalculator.tbl_model[row_id][prop] = row[prop]; }
		console.log('previos_data---',rtCalculator.previos_data);

		if(!row['dop_data']['expel']['main'] && (row['dop_data']['svetofor']=='green' || row['dop_data']['svetofor']=='sgreen')){
			rtCalculator.tbl_model['total_row']['item_summ_in'] += row['item_summ_in'] - rtCalculator.previos_data['item_summ_in'];
			rtCalculator.tbl_model['total_row']['item_summ_out'] += row['item_summ_out'] - rtCalculator.previos_data['item_summ_out'];
			rtCalculator.tbl_model['total_row']['uslugi_summ_in']+= row['uslugi_summ_in'] - rtCalculator.previos_data['uslugi_summ_in'];
			rtCalculator.tbl_model['total_row']['uslugi_summ_out']+=row['uslugi_summ_out']-rtCalculator.previos_data['uslugi_summ_out'];
			rtCalculator.tbl_model['total_row']['total_summ_in'] += row['total_summ_in'] - rtCalculator.previos_data['total_summ_in'];
			rtCalculator.tbl_model['total_row']['total_summ_out']+= row['total_summ_out'] - rtCalculator.previos_data['total_summ_out'];
			rtCalculator.tbl_model['total_row']['delta'] +=  row['delta'] - rtCalculator.previos_data['delta'];
			
		}
	}
	,
	change_html:function(row_id){
	
	    // метод который вносит изменения (итоги рассчетов в таблицу HTML)
		// alert(row_id);
		// вычисляем текущий ряд
		var trs_arr = ($(this.body_tbl).children('tbody').length>0)? $(this.body_tbl).children('tbody').children('tr'):$(this.body_tbl).children('tr');
		
		var ln = trs_arr.length;
		for(var i = 0;i < ln;i++){
			if(trs_arr[i].hasAttribute && trs_arr[i].hasAttribute('row_id')){
				if(trs_arr[i].getAttribute('row_id') == row_id) var cur_tr = trs_arr[i];
			}
		}
		 console.log(rtCalculator.tbl_model);	
			
		// внесение изменений в затронутый ряд
		var tds_arr = $(cur_tr).children('td');
		var ln = tds_arr.length;
		for(var j = 0;j < ln; j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
				if(tds_arr[j]==rtCalculator.cur_cell) continue;
			   var type = tds_arr[j].getAttribute('type');
				
				console.log('-',type);
				if(type == 'glob_counter' || type == 'dop_details' || type == 'master_btn' || type == 'name' || type == 'svetofor') continue;
				
				if(type=='quantity') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type];
				else if(type=='print_exists_flag') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type]; 
				else if(type=='discount') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type]+'%';
	            else if(type=='item_price_in' || type=='item_price_out') tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2); 
				else if(type=='item_summ_in' || type=='item_summ_out'){
					 tds_arr[j].getElementsByTagName('div')[0].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'p';
				}
				else if(type=='uslugi_price_in' || type=='uslugi_price_out') tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2); 
				else if(type=='uslugi_summ_in' || type=='uslugi_summ_out'){
					 tds_arr[j].getElementsByTagName('div')[0].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'p';
				}
				else if(type=='total_price_in' || type=='total_price_out') tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2); 
				else if(type=='total_summ_in' || type=='total_summ_out'){
					 tds_arr[j].getElementsByTagName('div')[0].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'p';
				}
				else if(type=='delta'){
					 tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2);
				}
				else if(type=='margin'){
				     tds_arr[j].getElementsByTagName('div')[0].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'%';
				}
			}
		}
		console.log('total_row',rtCalculator.tbl_model['total_row']);
       
		// если ряд не исключен из рассчетов внoсим изменения в итоговый ряд
	   // if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['main']){
			var tds_arr = $(this.tbl_total_row).children('td');
			var ln2 = tds_arr.length;
			for(var j = 0;j < ln2;j++){
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					if(type == 'margin') continue;
					tds_arr[j].innerHTML = (rtCalculator.tbl_model['total_row'][type]).toFixed(2); 
				}
			}
		//}
		
	}
	,
	show_svetofor:function(e){ 
	   
	    e = e|| window.event;
		var img = e.target || e.srcElement;
		
		// проверяем не является ли изображение на котором был сделан клик тем же самым что было в последний раз если да проверяем не 
		// установленнна ли пауза, это все делается для того чтобы сделать некоторую паузу после закрытия всплывающего дива и 
		// его открытия по новой - важно при клике на маркер распологающийся над главной кнопкой.
	    if(rtCalculator.show_svetofor.last_img && rtCalculator.show_svetofor.last_img == img && rtCalculator.show_svetofor.pause) return; 
		rtCalculator.show_svetofor.last_img = img;
		//
		if(rtCalculator.show_svetofor.in_process) return; 
		rtCalculator.show_svetofor.in_process = true;
		
		var td = img.parentNode;

		// если еще не создана всплывающая планка с кнопками создаем её
		if(!rtCalculator.show_svetofor.plank){
			//// console.log('plank');
			var sourse_src = OS_HOST + '/skins/images/img_design/';
			
			var arr = ['red','green','grey','sgreen'];
            var plank = document.createElement('div');
			plank.className = 'svetofor_plank';
			$(plank).mouseleave(rtCalculator.hide_svetofor);
			
			for(var i = 0;i < arr.length;i++){ 
			   var img_btn = new Image();
			   img_btn.src = sourse_src + 'rt_svetofor_'+arr[i]+'.png';
			   img_btn.setAttribute("status",arr[i]);
			   img_btn.onclick = rtCalculator.change_svetofor;
			   plank.appendChild(img_btn);
			}
			// помещаем планку в переменную 
			rtCalculator.show_svetofor.plank = plank;
		}
	    $(td).mouseleave(rtCalculator.hide_svetofor);
		td.appendChild(rtCalculator.show_svetofor.plank);
	}
	,
	hide_svetofor:function(){ 
	
		if(rtCalculator.show_svetofor.plank.parentNode){// именно так - если у plank есть parentNode тоесть если он добавлен
		                                                //  куданибудь как Child то тогда его удаляем с помощью removeChild
			rtCalculator.show_svetofor.plank.parentNode.removeChild(rtCalculator.show_svetofor.plank);
		}
		if(rtCalculator.show_svetofor.in_process) rtCalculator.show_svetofor.in_process = false;
		
	}
	,
	change_svetofor:function(e){ 
	   
	    e = e|| window.event;
		var img_btn = e.target || e.srcElement;
		//// console.log(); 
		
		
        // ПОКА ОТКЛЮЧЕНО связано с состоянием интерфейса исключения рядов - поэтому слущаем его
		// if(rtCalculator.expel_value_from_calculation.in_process) return; 
		
		if(rtCalculator.change_svetofor.in_process) return; 
		rtCalculator.change_svetofor.in_process = true;
		
		var td = img_btn.parentNode.parentNode;
		var row_id = td.parentNode.getAttribute("row_id");
		var cur_status = td.getAttribute("svetofor");
		var new_status = img_btn.getAttribute("status");

        // проверяем не является ли новый статус sgreen если нет то проверяем не равен ли новый статус текущему статусу если да то
		// прекращаем выполнение метода потому что это ничего не меняет и не имеет смысла
		// но если новый статус равен sgreen то сверку не производим и продолжаем выполнение скрипта потому что sgreen воздействует
		// не только на свой ряд но и на другие в которых могли произоти какие то изменения
		if(new_status!= 'sgreen' && new_status == cur_status){
		    rtCalculator.change_svetofor.in_process = false;	
			return;
		}
		// alert(cur_status);
		
		if(new_status=='sgreen'){
			// собираем id остальных рядов относящихся к этой позиции для отправки на сервер чтобы отключить (нужные из них) в красный
			var parent_pos_id = rtCalculator.tbl_model[row_id].dop_data.parent_pos_id;
			// alert(parent_pos_id);
			// cобираем id-шники рядов относяшихся к данной товарной позиции
			var idsObj = {};
			var idsArr = [];
			for(var id in rtCalculator.tbl_model){
				if(rtCalculator.tbl_model[id].dop_data && rtCalculator.tbl_model[id].dop_data.parent_pos_id == parent_pos_id && id!=row_id){
					idsObj[id] = true;
					idsArr.push(id);
				}
			}
		}
		/* ДОДЕЛАТЬ 
		   - пересчет общей суммы для переназначенных
		*/
		
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_svetofor='+ new_status +'&id='+row_id+((idsArr && idsArr.length > 0)?'&idsArr='+JSON.stringify(idsArr):''));
		// alert(url);
		
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /*alert(response);*/
		   td.getElementsByTagName('img')[0].src = OS_HOST + '/skins/images/img_design/rt_svetofor_'+new_status+'.png';
		   td.setAttribute("svetofor",new_status);
			
			if(new_status=='green'){
				addToItog(row_id,cur_status);
				rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			if(new_status == 'grey' || new_status == 'red'){  
				subtractFromItog(row_id,cur_status);
				rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			if(new_status=='sgreen'){
				
				var tbl = document.getElementById('rt_tbl_body');
		        var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');
	
		        // обходим ряды таблицы
				// В РЕЗУЛЬТАТЕ ОБХОДА, суммы всех рядов позиции, кроме текущего устанавливаемого в sgreen, 
				// при соответсвии условиям вычитаются из Итого
				forMark:
			    for( var i= 0 ; i < trsArr.length; i++){
					// проверяем входит ли расчет в расчеты данной позиции, причем текущий устанавливаемый не входит в idsObj
				    if(trsArr[i].hasAttribute('row_id') && idsObj[trsArr[i].getAttribute('row_id')]){
						var r_id = trsArr[i].getAttribute('row_id');
						var tdsArr = $(trsArr[i]).children('td');
						// проверяем какое текущее значение у светофора ряда если grey то не трогаем ряд, пропускаем его
						for( var j= 0 ; j < tdsArr.length; j++){
							 if(tdsArr[j].hasAttribute('svetofor')){
								 if(tdsArr[j].getAttribute('svetofor')=="grey") continue forMark;
								 
								 tdsArr[j].getElementsByTagName('IMG')[0].src = OS_HOST + '/skins/images/img_design/rt_svetofor_red.png';
								 var r_cur_status = tdsArr[j].getAttribute("svetofor");
						         tdsArr[j].setAttribute('svetofor','red');
								 rtCalculator.tbl_model[r_id].dop_data.svetofor = 'red';
					             break;
				             }
						}
						// alert(r_id+ ' '+cur_status+ ' '+r_cur_status);
						subtractFromItog(r_id,r_cur_status);
					}
			    }
				//суммы текущего устанавливаемого в sgreen ряда, при соответвии условиям прибавляются к Итого
				addToItog(row_id,cur_status);
				rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			function addToItog(row_id,cur_status){
				// alert(cur_status);
				// РАСШИФРОВКА - если при новом статусе строка должна учитываться в Итого, при этом текущий статус grey || red 
				// (тоесть на данный момент не учитывается в Итого),и строка не исключена из расчета(тоесть должна учитываеться в Итого)
				// добавляем значения строки в Итого при этом надо учесть не исключены ли нанесения и доп услуги если да то их трогать 
				// не надо(потому что они уже не должны учитываются в  Итого
				if((cur_status == 'grey' || cur_status == 'red') && rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] != true ){
					// alert('add');
					rtCalculator.tbl_model['total_row']["item_summ_in"] += rtCalculator.tbl_model[row_id]["item_summ_in"];
					rtCalculator.tbl_model['total_row']["item_summ_out"] += rtCalculator.tbl_model[row_id]["item_summ_out"];
					rtCalculator.tbl_model['total_row']["uslugi_summ_in"] += rtCalculator.tbl_model[row_id]["uslugi_summ_in"];
					rtCalculator.tbl_model['total_row']["uslugi_summ_out"] += rtCalculator.tbl_model[row_id]["uslugi_summ_out"];
					rtCalculator.tbl_model['total_row']['total_summ_out'] += rtCalculator.tbl_model[row_id]['total_summ_out'];
					rtCalculator.tbl_model['total_row']['total_summ_in'] += rtCalculator.tbl_model[row_id]['total_summ_in']; 
					rtCalculator.tbl_model['total_row']['delta'] = rtCalculator.tbl_model['total_row']['total_summ_out'] - rtCalculator.tbl_model['total_row']['total_summ_in'];
					
				}
			}
			function subtractFromItog(row_id,cur_status){
				// alert(cur_status);
				// РАСШИФРОВКА - если при новом статусе строка не должна учитываться в Итого, при этом текущий статус  green || sgreen 
				// (тоесть на данный момент учитывается в Итого), и строка не исключена из расчета (тоесть на данный момент учитывается в Итого)
				// вычитаем значения строки из Итого при этом надо учесть не исключены ли нанесения и доп услуги если да то их трогать 
				// не надо(потому что они уже не учитываются в  Итого		 
				if((cur_status == 'green' || cur_status == 'sgreen') && rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] != true ){
					// alert('subtract');
					rtCalculator.tbl_model['total_row']["item_summ_in"] -= rtCalculator.tbl_model[row_id]["item_summ_in"];
					rtCalculator.tbl_model['total_row']["item_summ_out"] -= rtCalculator.tbl_model[row_id]["item_summ_out"];
					rtCalculator.tbl_model['total_row']["uslugi_summ_in"] -= rtCalculator.tbl_model[row_id]["uslugi_summ_in"];
					rtCalculator.tbl_model['total_row']["uslugi_summ_out"] -= rtCalculator.tbl_model[row_id]["uslugi_summ_out"];
					rtCalculator.tbl_model['total_row']['total_summ_in'] -= rtCalculator.tbl_model[row_id]['total_summ_in']; 
					rtCalculator.tbl_model['total_row']['total_summ_out'] -= rtCalculator.tbl_model[row_id]['total_summ_out'];
				    rtCalculator.tbl_model['total_row']['delta'] = rtCalculator.tbl_model['total_row']['total_summ_out'] - rtCalculator.tbl_model['total_row']['total_summ_in'];
					
				}
			}
			rtCalculator.change_html(row_id);/**/
			rtCalculator.change_svetofor.in_process = false;
		}
		
		rtCalculator.hide_svetofor();
		rtCalculator.show_svetofor.pause = true;
		setTimeout( pause, 300 );
		function pause(){ rtCalculator.show_svetofor.pause = false; }
	}
	,
	svetofor_display_relay:function(img_btn,certainRow){ 

		if(rtCalculator.svetofor_display_relay.in_process) return; 
		rtCalculator.svetofor_display_relay.in_process = true;

	    var status = img_btn.src.slice((img_btn.src.lastIndexOf('_')+1),img_btn.src.lastIndexOf('.'));
	    // alert(status);
		if(status =='on'){
		    var new_status = 'off';	
			var action = 'hide';
		}
		else{
			var new_status = 'on';	
			var action = 'show';
		}
		// alert(new_status+' - '+status);
		// определяем стартовый ряд
		if(certainRow) var start = img_btn.parentNode.parentNode;
		else{
			var start = rtCalculator.body_tbl.firstChild;
			if(start.nodeName == 'TBODY') start = start.firstChild;
		}
		//start = start.nextSibling;
		//alert(start.nodeName);
		
		var idsArr = [];
		// проходим по рядам таблицы и меняем отображение рядов
		fff:
		for( var tr = start ; tr != null ; tr = tr.nextSibling){ 
		     var target = false;
			 if(tr.getAttribute("pos_id")){
				 pos_row = tr;//
				 idsArr.push(tr.getAttribute("pos_id"));
			 }
			 // tr.style.backgroundColor = '#FF0000';
			 // if(tr.getAttribute("pos_id")) continue;
			 var tdsArr = $(tr).children('td');
			 for(var j in tdsArr){
				 if(tdsArr[j].nodeName == 'TD'){
					// tdsArr[j].style.backgroundColor = '#FFFF00';//
					 if(tdsArr[j].getAttribute("svetofor")){
						 if(tdsArr[j].getAttribute("svetofor") == 'red') target = true;
						 //break;
					 }
				 }
			 }
		     if(target){
				 // обрабатываем текущий ряд
			     var curClassArr = tr.className.split(' ');
				 // alert(cur_display);
				 var newClass = (action == 'show')?'':'hidden';
				 // if(!certainRow && cur_display == new_display) continue;
				 for(var j in curClassArr){
					 if(curClassArr[j] == newClass) continue fff;
				 }
				 
				 tr.className = newClass;
				 
				 // производим изменения атрибута rowspan в ряду позиции (pos_row) иначе таблицу перекорежит
				 // в зависимости от того скрываем или открываем, нужно уменьшить или увеличить row_span в pos_row
				 if(typeof pos_row !=='undefined'){
					 var val = (action == 'show')?1:-1;
					 
					 var tdsArr = $(pos_row).children('td');
					 for(var j in tdsArr){
						 if(tdsArr[j].nodeName == 'TD' && tdsArr[j].hasAttribute("rowspan")){
							row_span = parseInt(tdsArr[j].getAttribute("rowspan"))+val;
							tdsArr[j].setAttribute("rowspan",row_span);
							//alert(tdsArr[j].getAttribute("rowspan"));
						 }
						 if(tdsArr[j].nodeName == 'TD' && tdsArr[j].hasAttribute("svetofor_btn")){
							tdsArr[j].getElementsByTagName('IMG')[0].src = tdsArr[j].getElementsByTagName('IMG')[0].src.replace(status,new_status);
							//tdsArr[j].getElementsByTagName('IMG')[0].src.replace(status,new_status);
							//tdsArr[j].getElementsByTagName('IMG')[0].src = 'rt_svetofor_red.png';
						 }
					 }			 
				 }
			 }
			 //tr.style.backgroundColor = '#FF0000';//
			 img_btn.src = img_btn.src.replace(status,new_status);
			 if(certainRow && tr.nextSibling && tr.nextSibling.getAttribute("pos_id")) break;
			 
		}
		
		
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('svetofor_display_relay='+ new_status +'&ids='+idsArr.join("','"));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /*alert(response);*/
		    rtCalculator.svetofor_display_relay.in_process = false;
		}
		
		
		
	}
	,
	change_row_span:function(tr,val){ 
	    
	}
	,
	swich_cols:function(cell,action){ 
	    //alert(cell);
		//alert(action);
	  
		if(cell.nodeName=='SPAN') cell = cell.parentNode;
		var name =  cell.getAttribute("swiched_cols");
		
		var tds_arr = ($(rtCalculator.head_tbl).children('tbody').length>0)? $(rtCalculator.head_tbl).children('tbody').children('tr').children('td'):$(rtCalculator.head_tbl).children('tr').children('td');
		relay(tds_arr,name,action);
		
		var tds_arr = ($(rtCalculator.body_tbl).children('tbody').length>0)? $(rtCalculator.body_tbl).children('tbody').children('tr').children('td'):$(rtCalculator.body_tbl).children('tr').children('td');
		relay(tds_arr,name,action);
		
		function relay(tds_arr,name,action){
			for(var j in tds_arr){
				if(tds_arr[j].getAttribute){
					if(tds_arr[j].getAttribute('swiched_cols') && tds_arr[j].getAttribute('swiched_cols')==name){
						 var stat = parseInt(tds_arr[j].getAttribute("c_stat"));
                         if(stat=='1'){
							 if(action=='show') $(tds_arr[j]).addClass('hidden');
							 if(action=='hide') $(tds_arr[j]).removeClass('hidden');
						 }
						 if(stat=='0'){
							 if(action=='show') $(tds_arr[j]).removeClass('hidden');
							 if(action=='hide') $(tds_arr[j]).addClass('hidden');
						 }
					}
				}
			}
		}
	}
	,
	/*swich_cols_old_version:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		if(cell.nodeName=='SPAN') cell = cell.parentNode;
		
		var value =  cell.getAttribute("swiched_cols");

		var tds_arr = rtCalculator.head_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		var tds_arr = rtCalculator.body_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		function relay(tds_arr,value){
			for(var j in tds_arr){
				if(tds_arr[j].getAttribute){
					if(tds_arr[j].getAttribute('swiched_cols') && tds_arr[j].getAttribute('swiched_cols')==value){
						var stat = parseInt(tds_arr[j].getAttribute("c_stat"));
						var new_stat = (stat+1)%2;
						tds_arr[j].setAttribute("c_stat",new_stat);
						//// console.log(stat+' '+new_stat);
						var class_arr = tds_arr[j].className.split(' ');
						if(new_stat==1){
							var class_arr_clone = class_arr; 
							class_arr=[];
							for(var s in class_arr_clone) if(class_arr_clone[s]!='hidden')class_arr.push(class_arr_clone[s]);
						}
						else{
							class_arr.push('hidden');
						}
						tds_arr[j].className = class_arr.join(' ');
					}
				}
			}
		}
	}
	,*/
	get_active_rows:function(dop_params_obj){ 
	    
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор есть ли зеленые маркеры
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');

		var nothing = true;
		var pos_id = false;
		var idsObj = {};
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			var flag = false;
			
			// фильтруем по типу позиции (каталог, не каталог и т.п.) если равно указаному значению прерываем выполненин
			if(dop_params_obj && dop_params_obj.filter_glob_type_apart){
				if(trsArr[i].getAttribute('type') && trsArr[i].getAttribute('type')==dop_params_obj.filter_glob_type_apart){
					pos_id = false;
					continue;
				}
			}
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				console.log(pos_id);
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn'){
						if(inputs[j].checked != true) pos_id = false;
					}
					/*{
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else  pos_id = false;*/
				}
			}
			// console.log(pos_id);
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//// console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = $(trsArr[i]).children('td');
				var ln2 = tdsArr.length;
				for( var j= 0 ; j < ln2; j++){
					if(tdsArr[j].getAttribute('svetofor')){
						if(tdsArr[j].getAttribute('svetofor')=='green' || tdsArr[j].getAttribute('svetofor')=='sgreen'){
							if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
							idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
							nothing = false;
				        }
						if(dop_params_obj && dop_params_obj.svetofor_dop_val && tdsArr[j].getAttribute('svetofor')==dop_params_obj.svetofor_dop_val){
							if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
							idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
							nothing = false;
				        }
					}
				}
			}
		}
		return (nothing)? false : idsObj;
	}
	,
	get_active_main_rows:function(){ 
	    
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');
		var pos_id = false;
		var idsArr = [];
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						idsArr.push(pos_id); 
				    }
				}
			}
		}
		return (idsArr.length>0)? idsArr : false ;
	}
	,
	get_positions_num_in_query:function(){ 

		var counter=0;
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			// если это ряд позиции
			if(trsArr[i].getAttribute('pos_id')) counter++;
		}
		return counter;
	}
	,
	copy_rows:function(e){ 
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
		if(!(idsObj = rtCalculator.get_active_rows())){
            echo_message_js("Невозможно скопировать ряды, вы не выбрали ни одной позиции",'system_message',2000);
			return;
		} 
        
		show_processing_timer();
		/*console.log(idsObj); //return; */
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /* alert(response); //*/ rtCalculator.handler_for_copy_row_response(response); close_processing_timer();  closeAllMenuWindows(); }
	}
	,
	copy_row:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var pos_id = cell.getAttribute("pos_id");
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		if(!(idsObj = rtCalculator.get_active_rows_for_one_position(pos_id))){
            echo_message_js("не возможно скопировать позицию, она не содержит активных расчетов",'system_message',2000);
			return;
		} 
		
		show_processing_timer();
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj));
		rtCalculator.send_ajax(url,callback);
		function callback(response){/* alert(response); //*/  rtCalculator.handler_for_copy_row_response(response); close_processing_timer(); closeAllMenuWindows();  if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF';
		}
	}
	,
	handler_for_copy_row_response:function(response){
		try {  var dataObj = JSON.parse(response); }
		catch (e) { 
			alert('неправильный формат данных in calculatorClass.copy_row() ошибка JSON.parse(response)');
			return;
		}
		//console.log('--',dataObj);
		if(dataObj[0]=='united_calculations'){
			//console.log('-1-',dataObj[1]);
			/*for(var i in dataObj[1]){
				//console.log('--',$('#rt_tbl_body tr[row_id='+ dataObj[1][i] +']')[0]);
				//$('#rt_tbl_body tr[row_id='+ dataObj[1][i] +']')[0].style.border = '#000 1px dotted';
			}*/
			echo_message_js('в копируемые ряды входят расчеты которые содержат объединенные тиражи','system_message',5800);
		}
		
	}
	,
	get_active_rows_for_one_position:function(pos_id){ 
	    
		// обходим РТ 
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		var idsObj = {};
		var goAhead = false;
		var nothing = true;
		var trsArr = ($(this.body_tbl).children('tbody').length>0)? $(this.body_tbl).children('tbody').children('tr'):$(this.body_tbl).children('tr');
		
		var ln = trsArr.length;
		for(var i = 0;i < ln; i++){
		    // если ряд не имеет атрибута row_id пропускаем его
		    if(!trsArr[i].getAttribute('row_id')) continue;
			
			
			if(trsArr[i].getAttribute('pos_id')){
				if(goAhead && trsArr[i].getAttribute('pos_id') != pos_id){
					goAhead=false;
				}
				
				// если встречается ряд позиции из которого было вызвано событие устанавливаем флаг в true
				if(trsArr[i].getAttribute('pos_id') == pos_id){
					goAhead = true;
				}
			}
			if(goAhead){
				// работаем с рядом - ищем светофор 
				var tdsArr = $(trsArr[i]).children('td');
				var ln2 = tdsArr.length;
				for( var j= 0 ; j < ln2; j++){
					if(tdsArr[j].getAttribute('svetofor') && (tdsArr[j].getAttribute('svetofor')=='green' || tdsArr[j].getAttribute('svetofor')=='sgreen')){
						if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						nothing = false;
					}
				}
			}
		}
		return (nothing)? false : idsObj;
	}
	,
	insert_copied_rows:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		if(cell.getAttribute('pos_id')) var place_id = cell.getAttribute('pos_id');
		if(rtCalculator.body_tbl.getAttribute('query_num')) query_num =  rtCalculator.body_tbl.getAttribute('query_num');
		else{
			echo_message_js("не удалось определить номер заявки",'system_message',2000);
			return;
		}
		
		// show_processing_timer();
		//  
		// 1. Обращаемся к серверу, получаем данные из буфера(SESSIONS)
		// 2. Вставляем данные из буфера в базу данных на стороне сервера
		// 3. Получаем ответ об успешном действии
		// 4. Вносим изменения в HTML

		var url = OS_HOST+'?' + addOrReplaceGetOnURL('insert_copied_rows=1&query_num='+query_num+((typeof place_id != 'undefined')?'&place_id='+place_id:''));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ 
		    // alert(response);
           //  close_processing_timer(); 
			//closeAllMenuWindows();
			if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF';
			
			
			try {  var response_obj = JSON.parse(response); }
			catch (e) { 
				alert('неправильный формат данных in calculatorClass.insert_copied_rows() ошибка JSON.parse(response)');
				return;
			}
			// alert(data[0]);
			if(response_obj[0]==0){
				echo_message_js(response_obj[1],'system_message',2400);
				return;
			}/**/
			//location.reload();
			$.SC_reload_RT_content();
		}
	}
	,
	deleting:function(e){ 
	    
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
	    
		if(cell.getAttribute('pos_id')) var pos_id = cell.getAttribute('pos_id');
		if(cell.getAttribute('type')) var type = cell.getAttribute('type');
		
		var idsArr =[];
		
		// если есть pos_id то значит функция вызвана из контекстног меню - тоесть удаляем одну позицию
		// обходить ряды таблицы чтобы проверять мастер-кнопки не нужно 
        if(pos_id){
		    idsArr.push(pos_id);
		}
		else{// иначе обходим ряды таблицы
			 // определяем какие ряды были выделены (какие Мастер Кнопки были нажаты)
			if(!(idsArr = rtCalculator.get_active_main_rows())){
				if(type && type == 'prints') var target = 'нанесения';
				else if(type && type == 'uslugi') var target = 'услуги';
				else if(type && type == 'printsAndUslugi') var target = 'нанесения и доп услуги';
				else var target = 'ряды';
				echo_message_js('не возможно удалить '+target+', вы не выбрали ни одной позиции','system_message',2000);
				closeAllMenuWindows();
				return;
			} 
		}
		
		
		if(type && type == 'prints'){
			if(pos_id)  var sub_str = 'все услуги из выбранного ряда';
			else  var sub_str = 'все услуги из выбранных вами рядов';
		}
		else{
		    if(pos_id)  var sub_str = 'выбранный ряд';
			else  var sub_str = 'выбранные вами ряды';
		}
		
		var dialog = $('<div>программа удалит '+sub_str+'</div>');
					 
		$('body').append(dialog);
		$(dialog).dialog({
						  modal: true, 
						  width: 500,
						  minHeight : 200 ,
						  closeOnEscape: false,
						  buttons: [{text: "Да",
									click: function(){
											$(this).dialog("close");
										    closeAllMenuWindows();
											rtCalculator.deletingStep2(idsArr,type);
										}},
								   {text: "Отмена",
								   click: function(){
										   $(this).dialog("close");
										   closeAllMenuWindows();
			                               return;
									   }}]
						});
		$(dialog).dialog('open');	
	}
	,
	deletingStep2:function(idsArr,type){ 
		// alert(idsArr.join(';'));
		show_processing_timer();
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('deleting='+JSON.stringify(idsArr)+((typeof type !== 'undefined')?'&type='+type:''));
		rtCalculator.send_ajax(url,callbackForDeleting);
		
		function callbackForDeleting(response){ 
		
		    /* console.log(response);   */
			// alert(response); 

            close_processing_timer(); 
			closeAllMenuWindows();
			if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF';
			
			
			try {  var response_obj = JSON.parse(response); }
			catch (e) { 
				alert('неправильный формат данных in calculatorClass.deleting() ошибка JSON.parse(response)');
				return;
			}
			
			if(response_obj.warning && response_obj.warning.united_calculations){

					 ///var dialog = $('<div>Внимание :<br>'+ notes.join(', ')+'</div>');
					 var dialog = $('<div>удаляемые ряды содержат объединенные тиражи</div>');
					 
					 $('body').append(dialog);
					 $(dialog).dialog({
									  modal: true, 
									  width: 500,
									  minHeight : 200 ,
									  closeOnEscape: false,
									  buttons: [{text: "Да",
												click: function(){
														// отправляем повторный запрос с маркером ignore_calculators_checking
														//$(this).dialog("close");
														show_processing_timer();
														url += '&ignore_calculators_checking=1';
														rtCalculator.send_ajax(url,callbackForDeleting);
														
													}},
											   {text: "Отмена",
											   click: function(){
													   $(this).dialog("close");
												   }}]
									});
					 $(dialog).dialog('open');
					 
					 return;
			}
			
			// alert(data[0],data[1]);
			if(response_obj[0]==0){
				alert(data[1]);
				return;
			}
			//location.reload();
			$.SC_reload_RT_content();
		}
	}
	,
	makeSpecAndPreorder:function(e){

		e = e || window.event;
		var element = e.target;
        
		
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
		var doc_type = element.getAttribute('doc_type');
		if(client_id==''){ alert('не удалось определить клиента'); return;}
		if(query_num==''){ alert('не удалось номер заявки'); return; }
		
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор - должна быть нажата только одна зеленая кнопка (если больше или ни одна прерываемся)
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');

		
		var pos_id = false;
		var idsObj = {};
		var dopIdsArr = [];
		var unitedCalculationsIds = {};
		var unitedCalculationsFlag = false;
		var idsArr = [];
		var dopInfObj = {};
		var indexCounter = 0;
		// console.log('--СПФ-1-',unitedCalculationsIds);
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			var flag ;
			
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку
				var tdsArr = $(trsArr[i]).children('td');
				//console.log(tdsArr);
				var ln2 = tdsArr.length;
				for(var j =0; j < ln2; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						   var input = tdsArr[j].getElementsByTagName('input')[0];
						   if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
							   idsObj[pos_id] = []; 
										 
							}
							else pos_id = false;
						}
						if(type == 'name'){ 
						   // если ячейка содержит более одного елемента значит это каталожный товар и в первом елементе 
						   // размещен артикул, для остальных товаров в этом(единственном и последнем) элементе размешено имя
						   var article = (tdsArr[j].getElementsByTagName('DIV').length>1)? tdsArr[j].getElementsByTagName('DIV')[0].getElementsByTagName('A')[0].innerHTML:'';
						   var name = $(tdsArr[j].getElementsByTagName('DIV')[tdsArr[j].getElementsByTagName('DIV').length-1]).text();
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['name'] = article+' '+name;
						}
						if(type == 'glob_counter'){ 
						   var glob_counter = tdsArr[j].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['glob_counter'] = glob_counter;
						}/**/
					}
				}
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие, до нового ряда, 
			// позици на нажатие супер зеленой кнопки светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = $(trsArr[i]).children('td');  
				var ln3 = tdsArr.length;
				for( var j= 0 ; j < ln3; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='sgreen'){

						idsObj[pos_id].push(trsArr[i].getAttribute('row_id'));
						dopIdsArr.push(trsArr[i].getAttribute('row_id'));
						idsArr[indexCounter] = {pos_id:pos_id,row_id:trsArr[i].getAttribute('row_id')};
						indexCounter++;
					}
					if(tdsArr[j].getAttribute('type') && tdsArr[j].getAttribute('type')=='dop_details'){
						var dop_details = tdsArr[j].innerHTML;
						//alert();
						if(dop_details!=''){
						    var dop_details_obj = JSON.parse(dop_details);
							if(dop_details_obj.united_calculations){
								unitedCalculationsFlag = true;
						        unitedCalculationsIds[trsArr[i].getAttribute('row_id')] = dop_details_obj.united_calculations;
							}
						}
					}
	
				}
			}
			
		}
		
		// console.log('--СПФ--',idsObj);
		// console.log('--СПФ--',dopIdsArr);
		// console.log('--СПФ--',unitedCalculationsIds);
		
		// проверяем сколько зеленых кнопок светофора были нажаты и в итоге были учтены
		var nothing = true; // если вообще ни однин светофор не был суперзеленым
		var more_then_one = false; // если больше одной в ряду
		var less_then_one = false; // если вообще ни однин светофор не был суперзеленым
		var counter1 = 0;
		for(var index in idsObj){
            var counter2 = 0;
			nothing = false;
			for(var index2 in idsObj[index]){
				counter1++;
				counter2++;
			}
			if(counter2>1) more_then_one = true;
		}
		if(counter1==0) less_then_one = true;
		//var conrtol_num = getControlNum();
        //console.log(JSON.stringify(idsObj));
		//console.log(JSON.stringify(dopInfObj));
	    //return;
		
		if(nothing || more_then_one || less_then_one){
			if(nothing){ 
			    //alert('не возможно создать спецификацию,\rвы не выбрали ни одной товарной позиции\r\rнеобходимо:\r1). отметить нужный товар галочкой в мастер-кнопке\r2). выделить один расчет для данного товара, выставив суперзеную кнопку в светофоре');
			    var text = 'не возможно создать спецификацию, вы не выбрали ни одной товарной позиции<br>необходимо:<br>1). отметить нужный товар галочкой в мастер-кнопке<br>2). выделить один расчет для данного товара, выставив суперзеную кнопку в светофоре';
			    echo_message_js(text,'system_message',20000);
			}
			else if(more_then_one){
				var alertStrObj ={};
				var alertStrArr =[];
				for(var pos in idsObj){
					if(idsObj[pos].length >1) alertStrObj[dopInfObj[pos]['glob_counter']] = dopInfObj[pos]['glob_counter']+'). '+dopInfObj[pos]['name']+'\r';
				}
				for(var i in alertStrObj){
					alertStrArr.push(alertStrObj[i]);
				}
				var text = 'не возможно создать спецификацию,<br>выбрано более одного варианта расчета в рядах:<br>'+alertStrArr.join('');
			    echo_message_js(text,'system_message',20000);
			}
			else if(less_then_one){
				var text = 'не возможно создать спецификацию,<br>для выбранных товаров не выбрано ни одного варианта расчета<br>необходимо:<br>выделить один расчет для каждого выбранного товара выставив суперзеную кнопку в светофоре';
			    echo_message_js(text,'system_message',20000);
			}
			return;
		}
		
		// проверяем все ли расчеты связанные объединенными тиражами выбраны
		if(unitedCalculationsFlag){
			var break2 = false;
			for( var id in dopIdsArr){
				//alert(dopIdsArr[id]);
				if(unitedCalculationsIds[dopIdsArr[id]]){
					var ln2 = unitedCalculationsIds[dopIdsArr[id]].length;
					for( var j= 0 ; j < ln2; j++ ){
						
						if(dopIdsArr.join(',').indexOf(unitedCalculationsIds[dopIdsArr[id]][j])==-1){
							//alert('-'+unitedCalculationsIds[dopIdsArr[id]][j]);
							break2 = true;
							var dialog = $('<div>Вы создаете документ, в котором находятся тиражи из объединенной группы печати, при этом в документ вошла только часть такого тиража. Проверьте актуальный данные стоимости и тиражей.</div>');
							$(dialog).dialog({autoOpen: false ,title: 'Внимание!',modal:true,width: 600,buttons: [{text: "Да",click: function(){$(this).dialog("close");}}],close: function() {this.remove(); step1(doc_type);}});
							$(dialog).dialog("open");
							
							break;
						}
					}
					
				}
				if(break2) break;
			}
			if(!break2) step1(doc_type);
		}
		else{
			step1(doc_type);
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Работа с окном
		//////////////////////////////////////////////////////////////////////////////////
		function step1(doc_type){
			// получаем данные которые понадобятся при отображении окна
			$.ajax({
				   method: "POST",
				   url: location,
				   data: { AJAX: "getDataForSpecWindow", document_type: doc_type }
				})
				.done(function( response ) {
				    console.log(response);
					try {  var response_obj = JSON.parse(response); }
					catch (e) { 
						alert('неправильный формат данных in calculatorClass.deleting() ошибка JSON.parse(response)');
						return;
					}
					console.log(response_obj);
					step2(doc_type,response_obj);
					
				});

		}

		function step2(doc_type,optionalData){
	//alert(1);
			function launch_set_window(doc_type,id,content){
				// alert(doc_type);
				var title = (doc_type=='spec')?"Создание спецификации":"Создание счета-оферты";
				var box = document.createElement('DIV');
				box.id = id;
				box.style.display = "none";
				box.appendChild(content);
				document.body.appendChild(box);
				$("#"+id).dialog({autoOpen: false ,title: title,modal:true,width: 600,close: function() {this.remove();$("#"+id).remove();}});
				$("#"+id).dialog("open");
				$("#date_type").change(function() {
					if ($('#date_type').val() == 'days') {
						$('td#thselectchild').html('<div><div class="fieldCap">кол-во дней</div><input type="text" id="daysNumSetingForm" placeholder="введите цифры" name="form_data[datetime]"></div>');
					} else if ($('#date_type').val() == 'date') {
						$('td#thselectchild').html('<div class="dateBlock"><div class="fieldCap">дата сдачи</div><input type="text" id="datepicker1" placeholder="календарь"  style="width:140px" name="form_data[datetime]"></div>');	
										//$("#datepicker1").on('focus',"#datepicker1", function(){
					    $("#datepicker1").datetimepicker({
							format:'d.m.Y H:00',
							dayOfWeekStart: 1,
							startTime: new Date(0,0,0,15,0,0),
							minDate: new Date(optionalData['min_allowed_date']),
							closeOnDateSelect:true,
							onGenerate:function( ct ){
								$(this).find('.xdsoft_date.xdsoft_weekend').addClass('xdsoft_disabled');
								$(this).find('.xdsoft_date');
							},
							allowTimes:['00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00','15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00']
							,
							onChangeDateTime: function (date) {
					           console.log(date);
					           rtCalculator.setSecondDateWindow(date);
				            }
				        });
					}
				});
				

				$("#datepicker3").datepicker({ 
						dateFormat:'dd.mm.yy',
						dayOfWeekStart: 1,
						minDate:0
				});
				 
			
				$('.tags_select a').click(function() {
					$('#text_tag_input').val($(this).text());
					return false;
				});
			}
			
			var content = document.createElement('DIV');
			var winId ="specificationsPreWin222";
			var button = document.createElement('BUTTON');
			button.className="CommonRightBtn";

			var addresses = optionalData['addresses'];
			var ln = addresses.length
            var addresses_selects = [];
            addresses_selects.push('<option value="samo_vivoz">Самовывоз покупателем со склада: Санкт-Петербург, ул. Чугунная, д. 14, корп.1</option>');
			for (var i = 0; i < ln; i++) {
			 	addresses_selects.push('<option value="'+addresses[i]['str']+'">'+addresses[i]['str']+'</option>');
			}

			// параметры URL
			var params = {};
			if (location.search) {
			    var parts = location.search.substring(1).split('&');

			    for (var i = 0; i < parts.length; i++) {
			        var nv = parts[i].split('=');
			        if (!nv[0]) continue;
			        params[nv[0]] = nv[1] || true;
			    }
			}
			
            /*
            name="form_data[requisit_id]"
            doc_type: doc_type
			,client_id: client_id 						// id клента
			,query_num: query_num 						// id заявки
			//,agreement_id: value 						// id договора. (для спецификации)
			@@@,requisit_id: 'requisit_id' 				// id клиентских реквизитов.  (для оферты)
			@@@,our_firm_id: 'our_firm_id' 				// id наших реквизитов. (для оферты)
			@@@,signator_id: 'signator_id' 				// id подписанта
			@@@,data_type: data_type 						// тип даты
			@@@,datetime: datetime 						// дата отгрузки. если data_type==date то 0000-00-00 00:00:00, @@@если data_type==days то number
			@@@,final_date: "0000-00-00 00:00:00" 			// дата подписания
			@@@,prepayment: prepayment 					// предоплата
			@@@,short_description: short_description 		// краткое описание
			//,address: ['samo_vivoz'|value] 			// адрес доставки. если выбран самовывоз - 'samo_vivoz', иначе строка из базы и
			addOrReplaceGetOnURL('sendToSnab='+JSON.stringify(idsObj));
			
                
			*/
	       

			var our_firms = optionalData['our_firms'];
			rtCalculator.makeSpecAndPreorder_optionalData = optionalData;

            if(doc_type=='spec'){

                var existing_agreements = optionalData['existing_agreements'];
				var ln = existing_agreements.length
	            var existing_agreements_selects = [];
				for (var i = 0; i < ln; i++) {
					if(i==0) var ourFirstFirmId = existing_agreements[i]['our_requisit_id'];
				 	existing_agreements_selects.push('<option value="'+existing_agreements[i]['agreement_id']+'" our_requisit_id="'+existing_agreements[i]['our_requisit_id']+'">№ '+existing_agreements[i]['agreement_num']+' от '+existing_agreements[i]['date']+'('+existing_agreements[i]['our_comp']+' - '+existing_agreements[i]['client_comp']+')</option>');
				}

				existing_agreements_selects.push('<option value="new_agreement" our_requisit_id="new_agreement">Создать новый договор</option>');
  
            	var sub_part = '<tr bgcolor="#ccc">'
							+ '<td colspan="2" class="tdCap">Спецификация к договору номер</td>'
						+ '</tr>'
						+ '<tr>'
							+ '<td colspan="2" class="paddingTop">'
								+ '<select id="existing_agreements" style="width:400px" name="form_data[agreement_id]" onchange="rtCalculator.setOurFaximileFaces(this.options[this.selectedIndex].getAttribute(\'our_requisit_id\'))">'
									+ existing_agreements_selects.join('')
								+ '</select>'
							+ '</td>'
						+ '</tr>'
						+ '<tr>'
							+ '<td>'
							    + '<div>'
							    + '<div class="fieldCap">дата создания</div>' 
								+ '<input type="text" id="datepicker3" placeholder="календарь" name="form_data[date]" value="'+(((((new Date()).toJSON()).slice(0,10)).split("-")).reverse()).join('.')+'">'
								+ '</div>'
								+ ''
							+ '</td>'
							+ '<td>'
								+ '<div>'
							    + '<div class="fieldCap" class="tdCap">Лицо подписывающее договор:</div>' 
								+ '<select id="ourFaximileFace" style="width:230px" name="form_data[signator_id]"></select>'
								+ '</div>'
							+ '</td>'
						+ '</tr>';
            }
			if(doc_type=='oferta'){
	            
				var ln = our_firms.length
	            var our_firms_selects = [];
				for (var i = 0; i < ln; i++) {
					if(i==0) var ourFirstFirmId = our_firms[i]['id'];
				 	our_firms_selects.push('<option value="'+our_firms[i]['id']+'">'+our_firms[i]['name']+'</option>');
				}

	            var client_requisites = optionalData['client_requisites'];
				var ln = client_requisites.length
	            var client_requisites_selects = [];
				for (var i = 0; i < ln; i++) {
				 	client_requisites_selects.push('<option value="'+client_requisites[i]['id']+'">'+client_requisites[i]['name']+'</option>');
				}


				var sub_part = '<tr bgcolor="#ccc">'
							+ '<td class="tdCap">Юридическое лицо клиента</td>'
							+ '<td class="tdCap">Юридическое лицо Апельбург</td>'
						+ '</tr>'
						+ '<tr>'
							+ '<td class="paddingTop">'
								+ '<select id="clientjudface" style="width:230px" name="form_data[requisit_id]">'
									+ client_requisites_selects.join('')
								+ '</select>'
							+ '</td>'
							+ '<td class="paddingTop">'
								+ '<select id="apeljudface" style="width:230px" onchange="rtCalculator.setOurFaximileFaces(this.options[this.selectedIndex].value)" name="form_data[our_firm_id]">'
									+ our_firms_selects.join('')
								+ '</select>'
							+ '</td>'
						+ '</tr>'
						+ '<tr>'
							+ '<td>'
							+ '</td>'
							+ '<td>'
								+ '<div>'
							    + '<div class="fieldCap">Лицо подписывающее договор:</div>' 
								+ '<select id="ourFaximileFace" style="width:230px" name="form_data[signator_id]"></select>'
								+ '</div>'
							+ '</td>'
						+ '</tr>';
			}
//alert(3);
			content.innerHTML = '<form id="bizPapersSetingForm" method="POST" action="'+ OS_HOST+'?page=agreement&section=agreement_editor">'
			    + '<input type="hidden" id="" name="form_data[ids]" value="'+ encodeURIComponent(JSON.stringify(idsObj))+'">'
			    + '<input type="hidden" id="" name="form_data[doc_type]" value="'+doc_type+'">'
			    + '<input type="hidden" id="" name="form_data[query_num]" value="'+ params.query_num+'">'
                + '<input type="hidden" id="" name="form_data[client_id]" value="'+ params.client_id+'">'
			    + '<table class="makeSpecAndPreorderTbl">'
				+ '<tr bgcolor="#ccc">'
					+ '<td colspan="2" class="tdCap">Указать в оферте условия сдачи заказа</td>'
				+ '</tr>'
				+ '<tr>'
					+ '<td class="paddingTop">'
						+ '<select id="date_type" name="form_data[date_type]">'
							+ '<option selected="selected" value="days">По количеству рабочих дней</option>'
							+ '<option value="date">По конкретной дате</option>'
						+ '</select>'
					+ '</td>'
					+ '<td id="thselectchild">'
					    + '<div>'
					    + '<div class="fieldCap">кол-во дней</div>' 
						+ '<input type="text" id="daysNumSetingForm" placeholder="введите цифры" name="form_data[datetime]">'
						+ '</div>'
					+ '</td>'
				+ '</tr>'
				+  sub_part
				+ '<tr bgcolor="#ccc">'
					+ '<td class="tdCap">Указать условия предоплаты</td>'
					+ '<td class="tdCap">Указать условия доставки</td>'
				+ '</tr>'
				+ '<tr>'
					+ '<td class="paddingTop">'
						+ '<select id="prepay" name="form_data[prepayment]">'
							+ '<option value="30">30 %</option>'
							+ '<option value="50">50 %</option>'
							+ '<option value="70">70 %</option>'
							+ '<option selected="selected" value="100">100 %</option>'
						+ '</select>'
					+ '</td>'
					+ '<td class="paddingTop">'
						+ '<select id="delivery" style="width:230px" name="form_data[address]">'
							+ addresses_selects.join('')
						+ '</select>'
					+ '</td>'
				+ '</tr>'
				+ '<tr bgcolor="#ccc">'
					+ '<td colspan="2" class="tdCap">Описание темы документа</td>'
				+ '</tr>'
				+ '<tr>'
					+ '<td colspan="2">'
						+ '<input id="text_tag_input" type="text" style="width: 100%; box-sizing : border-box;" name="form_data[short_description]">'
						+ '<div class="tags_select">'
							+ '<a href="#" style="padding-right: 10px;">Сборные сувениры</a>'
							+ '<a href="#" style="padding-right: 10px;">Ежедневники</a>'
							+ '<a href="#" style="padding-right: 10px;">Ручки</a>'
							+ '<a href="#" style="padding-right: 10px;">Календари</a>'
							+ '<a href="#" style="padding-right: 10px;">Текстиль</a>'
						+ '</div>'
					+ '</td>'
				+ '</tr>'
			+ '</table></form>';

			var button1 = button.cloneNode();
			button1.onclick = function() {
				//alert(11);
                if($('#existing_agreements').length>0 && $('#existing_agreements').val =='new_agreement') return;

                // проверка обязательных окон
                var warnings = [];
                var pattern = /^\d{2}\.\d{2}\.\d{4} \d{2}\:\d{2}$/;
                var date_type = $('#date_type').val();

                if(date_type == 'date'){
                    var val_1 = $('#datepicker1').val();
                    if(val_1.trim() == '') warnings.push('поле "дата сдачи" не заполнено!!!');
                    else if(!pattern.test(val_1)) warnings.push('формат данных в поле "дата сдачи" не корректный, должен быть ДД.ММ.ГГГГ ЧЧ:ММ!!!');

                    var str = 'поле "лимит оплаты" не заполнено!!!';
                    if($('#datepicker2').length == 0){
                        // warnings.push(str);
                    }
                    else{
                        val_2 = $('#datepicker2').val();
                        if(val_2.trim() == '') warnings.push(str);
                        else if(!pattern.test(val_2)) warnings.push('формат данных в поле "лимит оплаты" не корректный, должен быть ДД.ММ.ГГГГ  ЧЧ:ММ!!!');
                    }
                }
                if(date_type == 'days'){
                	var pattern = /^\d+$/;
                	var val = $('#daysNumSetingForm').val();
                	if(val.trim() == '') warnings.push('поле "кол-во дней" не заполнено!!!');
                    else if(!pattern.test(val)) warnings.push('формат данных в поле "кол-во дней" не корректный, должен быть только цифры !!!');
                }

                if($('#datepicker3').length>0){
                	var val = $('#datepicker3').val();
                	var pattern = /^\d{2}\.\d{2}\.\d{4}$/;
                	if(val.trim() == '') alert('поле "дата создания" не заполнено!!!');
                	else if(!pattern.test(val)) warnings.push('формат данных в поле "дата создания" не корректный, должен быть ДД.ММ.ГГГГ !!!');             
                }/**/
                // alert(warnings.join("\r\n"));
                if(warnings.length > 0){
                    echo_message_js(warnings.join("<br>"),'system_message',5000);
                    return;
                } 
                $('#bizPapersSetingForm').submit();
				$("#"+winId).remove();
				return;
				
			}
			button1.innerHTML ="Создать";
			var button2 = button.cloneNode();
			button2.onclick= function(){ $("#"+winId).remove(); }
			button2.innerHTML ="Отмена";
			
			content.appendChild(button1);
			content.appendChild(button2);
			
			launch_set_window(doc_type,winId,content);
			rtCalculator.setOurFaximileFaces(ourFirstFirmId);
		}


	}
	,
	launchNewAgreementWindow:function(){
		//alert(1);
        var our_firms = rtCalculator.makeSpecAndPreorder_optionalData['our_firms'];
        var client_requisites = rtCalculator.makeSpecAndPreorder_optionalData['client_requisites'];

        var ln = our_firms.length
        var our_firms_list = [];
		for (var i = 0; i < ln; i++) {
			if(i==0) var ourFirstFirmId = our_firms[i]['id'];
		 	our_firms_list.push('<label><input type="radio" name="our_firms_list" value="'+our_firms[i]['id']+'" onclick="rtCalculator.checkExistingAgreements('+our_firms[i]['id']+')">'+our_firms[i]['name']+'</label>');
		}

		var ln = client_requisites.length
        var client_requisites_list = [];
		for (var i = 0; i < ln; i++) {
		 	client_requisites_list.push('<label><input type="radio" name="client_requisites_list" value="'+client_requisites[i]['id']+'">'+client_requisites[i]['name']+'</label>');
		}

    	var dialog = $("<div id='newAgreementWindow'></div>");
    	var content = $('<table id="newAgreementTbl" width="100%" class="newAgreementTbl" style="">'
				+ '<tr>'
					+ '<td>'+our_firms_list.join('<br>')+'</td>'
					+ '<td>'+client_requisites_list.join('<br>')+'</td>'
				+ '</tr>'
				+ '<tr>'
					+ '<td>Дата договора: <span id="newAgreementDate">'+(((((new Date()).toJSON()).slice(0,10)).split("-")).reverse()).join('.')+'</span></td>'
					+ '<td></td>'
				+ '</tr>'
				+ '</table>');


		var button = document.createElement('BUTTON');		
		button.className="CommonRightBtn";
		button.setAttribute('doc_type','spec');
		button.innerHTML ="Создать";
		button.onclick= function(){ 

			var our_requisites_id = $("#newAgreementTbl input[name=our_firms_list]:checked").val();
			var client_requisites_id = $("#newAgreementTbl input[name=client_requisites_list]:checked").val();
			var date = $("#newAgreementDate").text();
			//alert(date + ' - '+our_requisites_id+' - '+client_requisites_id);
			if(typeof our_requisites_id == 'undefined') echo_message_js('вы не выбрали реквизиты продавца','system_message',5000);
			if(typeof client_requisites_id == 'undefined') echo_message_js('вы не выбрали реквизиты клиента','system_message',5000);
            if(typeof our_requisites_id == 'undefined' || typeof client_requisites_id == 'undefined') return;


			$.ajax({
			   method: "POST",
			   url: location,
			   data: { AJAX: "save_new_agreement", our_requisites_id: our_requisites_id , client_requisites_id: client_requisites_id,date:date}
			})
			.done(function( response ) {
			    console.log(response);
			    location.reload();
			    $(dialog).remove();
			    rtCalculator.makeSpecAndPreorder()
				
			});
		}/**/

    	$(dialog).append(content);
    	$(dialog).append(button);
    	$('body').append(dialog);
		$(dialog).dialog({autoOpen: false ,title: 'Создание нового договора',modal:true,width: 600,close: function() {this.remove();}});
		$(dialog).dialog("open");
	
    }
    ,
	checkExistingAgreements:function(firm_id){
        // alert(firm_id);
        var existing_agreements = rtCalculator.makeSpecAndPreorder_optionalData['existing_agreements'];
	    var ln = existing_agreements.length
	    // проверяем на существование договоров между фирмами, если они существуют делаем выбор не возможным
	    $("#newAgreementTbl input[name=client_requisites_list]").prop( "disabled", false );
	    $("#newAgreementTbl input[name=client_requisites_list]").parent().css( "color", "black" );
        for (var i = 0; i < ln; i++) {
        	//alert((existing_agreements[i]['date']).slice(0,4) +'=='+(new Date()).getFullYear());
		 	if(existing_agreements[i]['our_requisit_id']==firm_id && (existing_agreements[i]['date']).slice(0,4) == (new Date()).getFullYear()){
		 		$("#newAgreementTbl input[name=client_requisites_list]").each(function(index){
		 			if(this.value == existing_agreements[i]['client_requisit_id']){
		 				this.disabled = true;
		 				$(this).parent().css( "color", "#DDD" );
		 				//$(this).click(function() { alert(1);/* echo_message_js("Договор нп "+(new Date()).getFullYear()+" год между этими фирмами уже создан",'system_message',5000);*/ });
		 			} 
		 		});
		 	}
		}
    }
	,
	setOurFaximileFaces:function(firm_id){
    	// alert(firm_id);
    	if(firm_id  =='new_agreement'){
    		rtCalculator.launchNewAgreementWindow();
    		return;
    	}
        var our_firms = rtCalculator.makeSpecAndPreorder_optionalData['our_firms'];
	    var ln = our_firms.length
        var our_firms_selects = [];
        for (var i = 0; i < ln; i++) {
		 	if(our_firms[i]['id']==firm_id){
		 		
		 		var managment = our_firms[i]['managment'];
			    var ln2 = managment.length
                var managment_selects = [];
		 		for (var j = 0; j < ln2; j++) {
				 	managment_selects.push('<option value="'+managment[j]['id']+'">'+managment[j]['name']+'</option>');
				}
		 	}
		}
		$("#ourFaximileFace").html(managment_selects.join(''));
    }
    ,
	setSecondDateWindow: function(date){
    	 //console.log($('#datepicker2'));

        if($('#datepicker2').length) return;
        
        $('td#thselectchild').append($('<div class="dateBlock"><div class="fieldCap">лимит оплат/подписи м-та</div><input type="text" id="datepicker2" placeholder="календарь" style="width:140px"  name="form_data[final_date]"></div>'));
        var dateObj = new Date(date);
        var dateStr = dateObj.toJSON();
        //console.log('--',dateStr);

        var date = dateStr.slice(0,10);
		var time = dateStr.slice(11,16);
		time = (time!='')?time:'22:00';
		datetime =date+' '+time+':00'
		//console.log('----',datetime);
		var pickerMaxDate = ((goOnNumWorkingDays(datetime,3,'-')).slice(0,10)).replace(/\-/g,'/');
        //console.log('--//--',pickerMaxDate);
	    
		$("#datepicker2").datetimepicker({ 
			format:'d.m.Y H:00',
			dayOfWeekStart: 1,
			minDate:0,
			maxDate:pickerMaxDate,
			closeOnDateSelect:true,
			allowTimes:['00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00','15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00']
		});
    }
	,
	makeSpecAndPreorderOld:function(e){

		e = e || window.event;
		var element = e.target;
        
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор - должна быть нажата только одна зеленая кнопка (если больше или ни одна прерываемся)
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');
		var nothing = true;
		var pos_id = false;
		var idsObj = {};
		var dopInfObj = {};
		
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			var flag ;
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				/*// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else pos_id = false;
				}*/
				var tdsArr = $(trsArr[i]).children('td');
				
				var ln2 = tdsArr.length;
				for(var j =0; j < ln2; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						   var input = tdsArr[j].getElementsByTagName('input')[0];
						   if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
										 idsObj[pos_id] = []; 
										 
							}
							else pos_id = false;
						}
						if(type == 'name'){ 
						   var article = tdsArr[j].getElementsByTagName('DIV')[0].getElementsByTagName('A')[0].innerHTML;
						   var name = tdsArr[j].getElementsByTagName('DIV')[tdsArr[j].getElementsByTagName('DIV').length-1].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['name'] = article+' '+name;
						}
						if(type == 'glob_counter'){ 
						   var glob_counter = tdsArr[j].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['glob_counter'] = glob_counter;
						}/**/
						
					}
				}
				
				
				
				
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 

				var tdsArr = $(trsArr[i]).children('td');
				var ln3 = tdsArr.length;
				for( var j= 0 ; j < ln3; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
						// idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						idsObj[pos_id].push(trsArr[i].getAttribute('row_id'));
						nothing = false;
					}
				}
			}
		}
		//console.log('--');
		//console.log(idsObj);
		
		// проверяем сколько зеленых кнопок светофора были нажаты и  в итоге были учтены
		var more_then_one = false;
		var less_then_one = false;
		var counter1 = 0;
		for(var index in idsObj){
            var counter2 = 0;
			for(var index2 in idsObj[index]){
				counter1++;
				counter2++;
			}
			
			if(counter1==0) less_then_one = true;
			if(counter2>1) more_then_one = true;
		}
		
		//var conrtol_num = getControlNum();
        //console.log(JSON.stringify(idsObj));
		//console.log(JSON.stringify(dopInfObj));
	    //return;
		
		if(nothing || more_then_one || less_then_one){
			if(nothing) alert('не возможно создать заказ,\rвы не выбрали ни одной позиции');
			else if(more_then_one){
				var alertStrObj ={};
				var alertStrArr =[];
				for(var pos in idsObj){
					if(idsObj[pos].length >1) alertStrObj[dopInfObj[pos]['glob_counter']] = dopInfObj[pos]['glob_counter']+'). '+dopInfObj[pos]['name']+'\r';
				}
				for(var i in alertStrObj){
					alertStrArr.push(alertStrObj[i]);
				}
				alert('не возможно создать заказ,\rвыбрано более одного варианта расчета в рядах:\r\n'+alertStrArr.join(''));
			}
			else if(less_then_one) alert('не возможно создать заказ,\rдля позиции(ий) невыбрано ни одного варианта расчета');
			return;
		}
		
	    show_processing_timer();
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
		if(client_id==''){
		   echo_message_js('не удалось определить клиента','system_message',2000);
		   return;
		}
		if(query_num==''){
		   echo_message_js('не удалось определить номер заявки','system_message',2000);
		   return;
		}
		

		location = "?page=agreement&section=presetting&client_id=" + client_id + "&ids=" +JSON.stringify(idsObj)+'&query_num='+query_num;
		
		
	    // формируем url для AJAX запроса
		/*var url = OS_HOST+'?' + addOrReplaceGetOnURL('makeSpecAndPreorder={"ids":'+JSON.stringify(idsObj)+',"client_id":"'+client_id+'","query_num":"'+query_num+'"}');
		// AJAX запрос
		make_ajax_request(url,callback);
		//alert(last_val);
		function callback(response){ 
		   
		    / *if(response == '1') location = OS_HOST+'?page=client_folder&section=business_offers&query_num='+query_num+'&client_id='+client_id;* /
		    console.log(response); 
			close_processing_timer(); closeAllMenuWindows();
		}	*/  

	}
	,
	sendToSnab:function(e){
		
		e = e || window.event;
		var element = e.target;
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
        if(!(idsObj = rtCalculator.get_active_rows({"filter_glob_type_apart":"cat"/*нам нужны будут товары не относящиеся к каталогу (снаб не отвечает за каталог)*/,"svetofor_dop_val":"grey"}))){
			alert('не возможно отправить в снаб - не выбраны товары или расчеты');
			return;
		} 
		/*console.log(idsObj);return;  */
		
		show_processing_timer();
		
	    // формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('sendToSnab='+JSON.stringify(idsObj));
		// AJAX запрос
		make_ajax_request(url,callback);
		function callback(response){ 
		    //alert(response);
		    if(response == '1') location.reload();
		    /*console.log(response);*/ 
			close_processing_timer(); closeAllMenuWindows();
		}	  
	}
	,
	show_discount_window:function(e){
	    
		e = e || window.event;
		var element = e.target;

		var its_rt = (element.hasAttribute('its_rt'))? true:false;
		var row_id = (its_rt)? element.parentNode.getAttribute('row_id'):$(element).parents("tr").attr("row_id");
		//alert(row_id);
		//alert(its_rt);
	    //alert(client_id);alert(row_id);
		
	    if(document.getElementById("BNODYUF0WE38")) document.getElementById("BNODYUF0WE38").parentNode.removeChild(document.getElementById("BNODYUF0WE38"));
	
	   // создаем всплывающее окно
	   up_window_consructor.setWindowDimentions(/*((its_rt)? 310:280)*/310,425)
	   
	   var arr = up_window_consructor.windowBilder('BNODYUF0WE38');
	   
	    
	   ///////////////////////////////////////////////////////
	   // содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   //элемент форма
	   var form = document.createElement("form");
	   form.method = "POST";
	   form.action = location;
	   
	   // div_float_left1
	   div_float_left1 = document.createElement("div");
	   div_float_left1.style.float ='left';
	   div_float_left1.style.margin ='10px 10px 15px 15px';
	   
	   div_float_left1.style.width ='250px';
	   div_float_left1.style.height ='200px';
	   // div_float_left1.style.border ='#BBBBBB solid 1px';
	   div_float_left1.style.borderRight ='#BBBBBB solid 1px';
	   
	   // div1
	   var div1 = document.createElement("div");
	   div1.style.marginRight ='25px';
	   div1.style.padding ='7px 5px 7px 5px';
	   div1.style.borderBottom ='#BBBBBB solid 1px';
	   
	   //поле row_id
	   var input_row_id = document.createElement("input"); 
	   input_row_id.type = 'hidden';
	   input_row_id.name = 'form_data[id]';
	   input_row_id.value = row_id;
	   
	   //поле ввода цены
	   var price_input = document.createElement("input"); 
	   //price_input.style.border ='#BBBBBB solid 1px';
	   price_input.type = 'text';
	   price_input.name = 'form_data[new_price]';
	   price_input.style.marginLeft ='10px';
	   price_input.style.width = '50px';
	   price_input.style.height = '16px';
	   
	   // объединяем div1
	   div1.appendChild(input_row_id);  
	   //div1.appendChild(input_client_id);
	   //div1.appendChild(input_cur_price);
	   div1.appendChild(document.createTextNode("установить стоимость "));
	   div1.appendChild(price_input);
	   
	   
	   // div2
	   var div2 = document.createElement("div");
	   div2.style.marginRight ='25px';
	   div2.style.padding ='7px 5px 7px 5px';
	   div2.style.borderBottom ='#BBBBBB solid 1px';
	   
	   
	   var dop_div1 = document.createElement("div");
	   dop_div1.style.float ='left';
	   dop_div1.appendChild(document.createTextNode("присвоить"));
	   
	   //переключатели radio buttons (скидка или наценка)
	   var radio_type_action1 = document.createElement("input"); 
	   radio_type_action1.type = 'radio';
	   radio_type_action1.name = 'form_data[type_action]';
	   radio_type_action1.value = 'discount';
	   radio_type_action1.checked = 'true';
	   var type_action_label1 = document.createElement("label"); 
	   type_action_label1.style.cursor ='pointer';
	   type_action_label1.appendChild(radio_type_action1);
	   type_action_label1.appendChild(document.createTextNode("скидку"));
	   type_action_label1.appendChild(document.createElement("br"));
	   
	   var radio_type_action2 = document.createElement("input"); 
	   radio_type_action2.type = 'radio';
	   radio_type_action2.name = 'form_data[type_action]';
	   radio_type_action2.value = 'markup';
	   var type_action_label2 = document.createElement("label"); 
	   type_action_label2.style.cursor ='pointer';
	   type_action_label2.appendChild(radio_type_action2);
	   type_action_label2.appendChild(document.createTextNode("наценку"));
	   type_action_label2.appendChild(document.createElement("br"));
	   
	   var dop_div2 = document.createElement("div");
	   dop_div2.style.float ='left';
	   dop_div2.appendChild(type_action_label1);
	   dop_div2.appendChild(type_action_label2);

      //поле ввода процентов
	   var persent_input = document.createElement("input"); 
	   persent_input.type = 'text';
	   persent_input.name = 'form_data[percent]';
	   persent_input.style.marginLeft ='3px';
	   persent_input.style.width = '50px';
	   persent_input.style.height = '16px';

       var dop_div3 = document.createElement("div");
	   dop_div3.style.float ='left';
	   dop_div3.style.marginLeft ='10px';
	   dop_div3.style.marginTop ='8px';
	   dop_div3.appendChild(persent_input);
	   
	   var dop_div4 = document.createElement("div");
	   dop_div4.style.clear ='both';

	   // объединяем div2
	   div2.appendChild(dop_div1);
	   div2.appendChild(dop_div2);
	   div2.appendChild(dop_div3);
	   div2.appendChild(dop_div4);


	   // div3
	   var div3 = document.createElement("div");
	   div3.style.marginRight ='25px';
	   div3.style.padding ='7px 5px 7px 5px';
	   //div3.style.borderBottom ='#BBBBBB solid 1px';
	   
	   //переключатели radio buttons (для каких рядов произвести действие)
	   var input_radio1 = document.createElement("input"); 
	   input_radio1.type = 'radio';
	   if(!its_rt) input_radio1.style.display = 'none';
	   input_radio1.name = 'form_data[which_rows]';
	   input_radio1.value = 'one_row';
	   input_radio1.checked = 'true';
	   var label1 = document.createElement("label"); 
	   label1.style.cursor ='pointer';
	   if(!its_rt) label1.style.display = 'none';
	   label1.appendChild(input_radio1);
	   label1.appendChild(document.createTextNode("на данный расчет"));
	   label1.appendChild(document.createElement("br"));
	   
	   div3.appendChild(label1);
	   
	   if(its_rt){
		   var input_radio2 = document.createElement("input"); 
		   input_radio2.type = 'radio';
		   input_radio2.name = 'form_data[which_rows]';
		   input_radio2.value = 'all_in_pos';
		   var label2 = document.createElement("label");
		   label2.style.cursor ='pointer';
		   label2.appendChild(input_radio2);
		   label2.appendChild(document.createTextNode("на все расчеты в позиции"));
		   label2.appendChild(document.createElement("br"));
		   
		   
		   var input_radio3 = document.createElement("input"); 
		   input_radio3.type = 'radio';
		   input_radio3.name = 'form_data[which_rows]';
		   input_radio3.value = 'all_in_query';
		   var label3 = document.createElement("label"); 
		   label3.style.cursor ='pointer';
		   label3.appendChild(input_radio3);
		   label3.appendChild(document.createTextNode("на все позиции в заявке"));
		   label3.appendChild(document.createElement("br"));
		   
		   div3.appendChild(label2);
	       div3.appendChild(label3);
	   }

       // объединяем div4
	  
	  
	   //div3.appendChild(label4);
	   
	   // div4
	   var div4 = document.createElement("div");
	   div4.style.marginRight ='25px';
	   div4.style.padding ='7px 5px 7px 5px';
	   div4.style.borderBottom ='#BBBBBB solid 1px';

	   
	   // checkbox
	   var input_checkbox = document.createElement("input"); 
	   input_checkbox.type = 'checkbox';
	   input_checkbox.style.display ='inline';
	   input_checkbox.name = 'form_data[drop_discont]';
	   var checkbox_label = document.createElement("label"); 
	   checkbox_label.style.cursor ='pointer';
	   checkbox_label.appendChild(input_checkbox);
	   checkbox_label.appendChild(document.createTextNode(" сбросить скидку/наценку"));
	   
	   // объединяем div3
	   div4.appendChild(checkbox_label);	   

       div_float_left1.appendChild(div1);
	   div_float_left1.appendChild(div2);
	   div_float_left1.appendChild(div4);
	   div_float_left1.appendChild(div3);
	  
       form.appendChild(div_float_left1);
	 
	   //кнопки ok reset и отменить
	   var div_float_right1 = document.createElement("div"); // плавающий div контейнер
	   div_float_right1.style.float ='right';
	   div_float_right1.style.margin ='10px 0px 0px 0px';
	   div_float_right1.style.width ='98px';
	   div_float_right1.style.border ='#000000 solid 0px';
	 
       //кнопкa ok
	   var button_ok = document.createElement("input"); 
	   button_ok.type = 'submit';
	   button_ok.name = 'set_discount';
	   button_ok.value = 'ok';
	   button_ok.style.width = '90px';
	 
	   var button_ok_div = document.createElement("div");
	   button_ok_div.appendChild(button_ok);
	 
	   div_float_right1.appendChild(button_ok_div);
	 
       //кнопкa reset
	   var button_reset = document.createElement("input"); 
	   button_reset.type = 'reset';
	   button_reset.value = 'очистить';
	   button_reset.style.width = '90px';
	 
	   var button_reset_div = document.createElement("div");
	   button_reset_div.appendChild(button_reset);
	 
	   div_float_right1.appendChild(button_reset_div);

	 
       //кнопкa отменить
	   var button_escape = document.createElement("input"); 
	   button_escape.type = 'button';
	   button_escape.value = 'отменить';
	   button_escape.style.width = '90px';
	   button_escape.onclick = up_window_consructor.closeWindow;
	 
	   var button_escape_div = document.createElement("div");
	   button_escape_div.appendChild(button_escape);
	 
	   div_float_right1.appendChild(button_escape_div);
	 

	   
	   ///////////////////////////////////////////////////////
	   // end содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   // добавляем содержимое в таблицу в форму а затем в таблицу окна
	   form.appendChild(div_float_right1);
	   //form.appendChild(div2);
	   arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(form);
	 
	   // добавляем таблицу в окно
	   arr[1].appendChild(arr[2]);
	   arr[1].className = 'discount_window';
	   document.body.appendChild(arr[0]);
	   document.body.appendChild(arr[1]);
	   
	   return false;
	   
	   
   }
	,
	setSvetoforStatusIn:function(e){

		e = e || window.event;
		var element = e.target;
		
        var status = element.getAttribute('status');
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = ($(tbl).children('tbody').length>0)? $(tbl).children('tbody').children('tr'):$(tbl).children('tr');
		
		var nothing = true;
		var idsArr = [];
		
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				var pos_id = trsArr[i].getAttribute('pos_id');
				
				var tdsArr = $(trsArr[i]).children('td');
				
				var ln2 = tdsArr.length;	
				for(var j =0; j < ln2; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						    var input = tdsArr[j].getElementsByTagName('input')[0];
						    if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						 
							   idsArr.push(pos_id); 
							   nothing = false;
		 
							}
						}
					}
				}
			}
		}

		if(nothing){
			echo_message_js('не возможно применить ярлык, не выбрано ни одной позиции','system_message',2000);
			return;
		}

	    show_processing_timer();
		//location = "?page=client_folder&set_svetofor_status=" + status + "&ids=" +JSON.stringify(idsArr);
		location = '?' + addOrReplaceGetOnURL('set_svetofor_status='+status + "&ids=" +JSON.stringify(idsArr));
		//alert('?' + addOrReplaceGetOnURL('set_svetofor_status='+status + "&ids=" +JSON.stringify(idsArr)));
	
	}
	,
	send_ajax:function(url,callback){
		
		
	    //////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
		
	   
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
	   
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				   ///////////////////////////////////////////
				   // обрабатываем ответ сервера

					
					var request_response = request.responseText;
				    //alert(request_response);
                    if(callback) callback(request_response);
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////	
	}
	,
	certainTd:function(node,type){ 
	   if(node==null)return false;
	   var node = node.nextSibling; 
	   return (node && node.nodeName=='TD' && node.getAttribute('type')  && node.getAttribute('type')==type) ? node : this.certainTd(node,type); 
	}
}