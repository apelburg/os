// JavaScript Document
    // для корректной работы скрипта желательно чтобы были установленны большинство размеров в одних и теж же ячейках head и body таблиц
    window.addEventListener('load',scrolledTableSizeFixing,false);
	
	function scrolledTableSizeFixing(){
		
		var tables = document.getElementsByTagName('table');
		// отыскиваем две части таблицы - верхнюю панель и прокручиваемую часть
		for(var i =0; i < tables.length ; i++ ){
			if(tables[i].getAttribute('scrolled') && tables[i].getAttribute('scrolled')=='head') var tbl_head = tables[i];
			if(tables[i].getAttribute('scrolled') && tables[i].getAttribute('scrolled')=='body') var tbl_body = tables[i];
		}
		// не выводим в рабочем режиме, выводится при пустом заказе
		// if(!tbl_head || !tbl_body){ alert('one of the assosiated pieces was found'); return;}
		
		
		// изначальная ширина верхней зафиксированной панели при загрузке
		headWidth = tbl_head.offsetWidth; 
		//alert(headWidth);
		
		// ячейки верхних рядов - верхней панели и прокручиваемой области
		var top_head_tds = (tbl_head.getElementsByTagName('tr')[0]).getElementsByTagName('td');
		var top_body_tds = (tbl_body.getElementsByTagName('tr')[0]).getElementsByTagName('td');
	
		if(top_head_tds.length != top_body_tds.length){ alert('num colls in head and body RT not equal'); return;}
		
		// присваиваем значению ширина ячеек тела таблицы, значения  ширины ячеек верхней панели
		// для корректировки присваеваемой ширины будем вычитать из offsetWidth 1(единицу)(предпологая что ширина border = 1px)
		// потому что offsetWidth вкючает в себя ширину border
		var correstion = 1;
		for(var i =0; i < top_head_tds.length ; i++ ){
			// скрытые ряды пропускаем (со значением offsetWidth == 0)
			if(top_head_tds[i].offsetWidth == 0) continue;
			// последний ряд пропускаем чтобы он выставился автоматически так как он упирается в полосу прокрутки
			//if(i==(top_head_tds.length-1)) continue;
			
			//
			//var correstion =(top_head_tds[i].className.indexOf('r_border')>-1)? 2:1;
	
			
			// корректируем ширину колонки на -10 потому что колонка имееет padding -10, 
			// из-за этого ширина клонки считается на 10 больше
			// в результате колонка в таблице получает ширину на 10 плюс свой padding и становится шире на 10
			if(i==(top_head_tds.length-1)) top_body_tds[i].style.width = (top_head_tds[i].offsetWidth -22) + "px";
			else/**/ if(top_head_tds[i].hasAttribute('type') && top_head_tds[i].getAttribute('type')=='quantity') top_body_tds[i].style.width =(top_head_tds[i].offsetWidth -correstion) - 10 + "px";
			else top_body_tds[i].style.width = (top_head_tds[i].offsetWidth -correstion) + "px";
			//top_body_tds[i].style.border =  "#FF0000 solid 1px";top_head_tds[i].style.border =  "#FF0000 solid 1px";
		
		}

		var container = document.getElementById('scrolled_part_container');
		var top = define_top(container);
		container.style.height = Geometry.getViewportHeight() - top + 'px';
		
		function define_top(element){
			var top = 0;
			while(element){
				top+= element.offsetTop;
				element = element.offsetParent;
			}
			return top;
		}
	}