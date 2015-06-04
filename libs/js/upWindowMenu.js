// JavaScript Document
    document.addEventListener('contextmenu',closeAllMenuWindows,false);
	document.addEventListener('click',closeAllMenuWindows,false);
	function closeAllMenuWindows(){
		////alert(3);
		if(openCloseMenu.lastWindow) openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
		if(openCloseMenu.lastWindow) openCloseMenu.lastWindow = null;
		if(openCloseMenu.lastElement) openCloseMenu.lastElement = null;
		//if(openCloseMenu.lastElement) openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
	}
	
	openCloseMenu.lastWindow = null;
	openCloseMenu.lastElement = null;
	function openCloseMenu(e,type,params){
		var target = e.target || e.srcElement;
		target = target.parentNode;
		
		if(openCloseMenu.lastElement && openCloseMenu.lastElement === target){
			//alert(3);
			closeAllMenuWindows();
			return;
		}
		
		if(type == 'contextmenu') openCloseContextMenu(e,params.id,params.control_num);
		if(type == 'tableMenu') openCloseTableMenu(e);
		if(type == 'rtMenu') openCloseRtMenu(e);
		if(type == 'quickMenu') openCloseQuickMenu(e);
		if(type == 'clientManagerMenu') openClientManagerMenu(e);
		if(type == 'rtViewTypeMenu') openCloseRtViewTypeMenu(e);
		if(type == 'subjectsListViewTypeMenu') openCloseSubjectsListViewTypeMenu(e);
		if(type == 'subjectsList') subjectsList(e);
		
		

		
		
	}
	
	function openCloseRtMenu(e){
		
		var target = e.target || e.srcElement;
		
		target = target.parentNode;

		target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		/*if(openCloseMenu.lastElement === target ){ 
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement = null;
			return;
		}*/
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}
        //return;
		//target.style.position = 'relative';
	    
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		//div.id = "quickContextExtraWindow";
		div.style.width = "200px";
		div.style.top =  "19px";
		div.style.left = "-3px";
		div.style.display = "block";
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Нет в наличии'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Отказано'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
	    var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Установить единую:'));
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Дату сдачи'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Наценку'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
	    var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Сформировать:'));
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = makeComOffer;
		//a.setAttribute('stock',0);
		a.appendChild(document.createTextNode('Коммерческое предложение'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = makeOrder;
		a.appendChild(document.createTextNode('Окончательный заказ'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = '';
		a.appendChild(document.createTextNode('Спецификацию'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('action','delete');
		a.href = '#';
		a.onclick = rtRowsManager;
		a.appendChild(document.createTextNode('Удалить выделенные строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('action','delete');
		a.href = '#';
		a.onclick = rtCalculator.copy_rows;
		a.appendChild(document.createTextNode('Копировать выделенные строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('action','delete');
		a.href = '#';
		a.onclick = rtCalculator.insert_copied_rows;
		a.appendChild(document.createTextNode('Вставить скопированные строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
	  
		
		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
	}

	function openCloseTableMenu(e){
		
		var target = e.target || e.srcElement;
		
		target = target.parentNode;
		
		target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		/*if(openCloseMenu.lastElement === target ){ 
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement = null;
			return;
		}*/
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}

		target.style.position = 'relative';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		//div.id = "quickContextExtraWindow";
		div.style.width = "200px";
		div.style.top =  "19px";
		div.style.left = "-3px";
		div.style.display = "block";
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Нет в наличии'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Отказано'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
	    var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Создать КП:'));
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = makeComOffer;
		a.setAttribute('stock',0);
		a.appendChild(document.createTextNode('без остатков'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = makeComOffer;
		a.setAttribute('stock',1);
		a.appendChild(document.createTextNode('с остатками'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
	
	    var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Заказать:'));
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.onclick = setSamplesList;
		a.href = '#';
		a.appendChild(document.createTextNode('требуется образец'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('счет заказчику'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('отгрузочные документы'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('товар у поставщика'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
			
	    var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('action','delete');
		a.href = '#';
		a.onclick = rtRowsManager;
		a.appendChild(document.createTextNode('Удалить выделенные строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('Объединить нанесение'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
	}
	
	function openCloseSubjectsListViewTypeMenu(e){
		
		var target = e.target || e.srcElement;
		
		container = target.parentNode;
		
		//container.addEventListener('click',setMenuWindow,false);
		//function setMenuWindow(e){
		//	e.stopPropagation();
		//}
		container.addEventListener('click',function(e){e.stopPropagation();},false);
		/*if(openCloseMenu.lastElement === container ){ 
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement = null;
			return;
		}*/
		if(openCloseMenu.lastElement === container ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
		}
		//var pattern = new RegExp(/view=([^&]+)/);
		//var out = pattern.exec(location.search);
		//var cur_view = (out)?out[1]:(target.getAttribute('cur_view_type'))? target.getAttribute('cur_view_type'):'ordinary';
		var cur_view = target.getAttribute('cur_view_type')? target.getAttribute('cur_view_type'):'ordinary';
		//alert(cur_view);
		
		container.style.position = 'relative';
		//container.style.border = '#FF0000 solid 1px';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "130px";
		div.style.top =  "18px";
		div.style.left = "-68px";
		div.style.display = "block";
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		if(cur_view == 'short') a.className = "active";
		a.href = '?'+addOrReplaceGetOnURL('view=short','') ;
		a.appendChild(document.createTextNode('Краткий'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		/**/
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		if(cur_view == 'ordinary') a.className = "active";
		a.href = (cur_view == 'ordinary')? '#' : '?'+addOrReplaceGetOnURL('view=ordinary','');
		a.appendChild(document.createTextNode('Обычный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		if(cur_view == 'wide') a.className = "active";
		a.href = (cur_view == 'wide')? '#' : '?'+addOrReplaceGetOnURL('view=wide','');
		a.appendChild(document.createTextNode('Просторный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		if(cur_view == 'expanded') a.className = "active";
		a.href = (cur_view == 'expanded')? '#' : '?'+addOrReplaceGetOnURL('view=expanded','');
		a.appendChild(document.createTextNode('Расширенный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		container.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = container;
		
		e.stopPropagation();
		
	}
	
	function subjectsList(e){
		
		var target = e.target || e.srcElement;
		
		var parentElement = target.parentNode;
		
		parentElement.addEventListener('click',function(e){e.stopPropagation();},false);
	
		if(openCloseMenu.lastElement === parentElement ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
		}
		
		var child = parentElement.childNodes;
        //alert(child.length);
		for(var i =0 ; i < child.length; i++){
			if(child[i].nodeType == Node.ELEMENT_NODE && child[i].getAttribute('hidden_list')){
				var div = child[i].cloneNode(true);
			}
		}
		
		
		div.className = 'drop_down_list';
		div.style.position = 'absolute';
		div.style.display = 'block';
		div.style.minWidth = (parentElement.offsetWidth -1)+ 'px';
		
		
		parentElement.style.position = 'relative';
		parentElement.appendChild(div);
		
		
		var pos = get_pos(parentElement);
	    var	top = ((pos.top + parentElement.offsetHeight + div.offsetHeight - Geometry.getVerticalScroll()) <= Geometry.getViewportHeight())?  parentElement.offsetHeight - 1 : - div.offsetHeight + 1;
		var	left = ((pos.left + div.offsetWidth + 18 - Geometry.getHorizontalScroll()) <= Geometry.getViewportWidth())?  - 1 : Geometry.getViewportWidth() - (pos.left + div.offsetWidth + 18  - Geometry.getHorizontalScroll());
		// + 18 - для корректировки поставил, окончательно не разобрался почему не точно позиционируется, пришлось коррекировать
		div.style.top = top + 'px';
		div.style.left = left + 'px';
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = parentElement;
		
		e.stopPropagation();
		
		function get_pos(element){
			var pos = {top:0,left:0}
			var e = element;
			while(e){
				pos.top += e.offsetTop;
				pos.left += e.offsetLeft;
				e = e.offsetParent;
			}
			// прокручиваемые области
			for(e = element.parentNode; e && e != document.body; e = e.parentNode) if(e.scrollTop)pos.top -= e.scrollTop;
			return pos;
		}
	}
	function openCloseRtViewTypeMenu(e){
		
		var target = e.target || e.srcElement;
		
		target = target.parentNode;
		
		target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		/*if(openCloseMenu.lastElement === target ){ 
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement = null;
			return;
		}*/
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}

		target.style.position = 'relative';
		//target.style.border = '#FF0000 solid 1px';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "170px";
		div.style.top =  "18px";
		div.style.left = "-118px";
		div.style.display = "block";
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Интерфейс:'));
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('height',14);
		a.href = '#';
		a.onclick = setRtTypeView;
		a.appendChild(document.createTextNode('Компактный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('height',28);
		a.href = '#';
		a.onclick = setRtTypeView;
		setRtTypeView
		a.appendChild(document.createTextNode('Обычный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('height',28);
		a.setAttribute('max_size','true');
		a.href = '#';
		a.onclick = setRtTypeView;
		a.appendChild(document.createTextNode('Просторный'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Отображать:'));
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('show','all');
		a.href = '#';
		a.onclick = setRtTypeViewTwo;
		a.appendChild(document.createTextNode('Всё'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('show','text');
		a.href = '#';
		a.onclick = setRtTypeViewTwo;
		a.appendChild(document.createTextNode('Только текст'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('show','frame');
		a.href = '#';
		a.onclick = setRtTypeViewTwo;
		a.appendChild(document.createTextNode('Только фрейм'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
	}
	
	function openCloseQuickMenu(e){
		
		var target = e.target || e.srcElement;
		
		target = target.parentNode;
		
		target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		/*if(openCloseMenu.lastElement === target ){ 
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement = null;
			return;
		}*/
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}

		target.style.position = 'relative';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "170px";
		div.style.top =  "28px";
		div.style.left = "2px";
		div.style.display = "block";
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Тип товаров:'));
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2Arrow";
		var a = document.createElement('a');
		a.href = '#';
		a.setAttribute('type','article');
		a.setAttribute('action','add_order_at_the_end_rt');	
		a.onclick = openExtraContextWindow;
		a.appendChild(document.createTextNode('основной каталог'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2Arrow";
		var a = document.createElement('a');
		a.setAttribute('type','ordinary');
		a.setAttribute('action','add_order_at_the_end_rt');
		a.href = '#';
		a.onclick = openExtraContextWindow;
		a.appendChild(document.createTextNode('другой каталог'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2Arrow";
		var a = document.createElement('a');
		a.setAttribute('type','print');
		a.setAttribute('action','add_order_at_the_end_rt');
		a.href = '#';
		/*a.onclick = openExtraContextWindow;*/
		a.appendChild(document.createTextNode('полиграфия'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2Arrow";
		var a = document.createElement('a');
		a.setAttribute('type','print');
		a.setAttribute('action','add_order_at_the_end_rt');
		a.href = '#';
		/*a.onclick = openExtraContextWindow;*/
		a.appendChild(document.createTextNode('нанесение'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		/*
		   <div id="quickContextWindow" class="contextWindow" type="windowContainer" style="width:160px;top:-9px;left:8px;">
				<div class="cup">Тип товаров:</div>
				<div class="fence"></div>
				<div class="link2Arrow"><a href="#" onclick="return openExtraContextWindow(this,'article')">основной каталог</a></div>
				<div class="link2Arrow"><a href="#" onclick="return openExtraContextWindow(this,'ordinary')">другой каталог</a></div>
				<div class="link2Arrow"><a href="#" onclick="return openExtraContextWindow(this,'print')">полиграфия</a></div>
				<div class="link2Arrow"><a href="#" onclick="return openExtraContextWindow(this,'print')">нанесение</a></div>
				<div class="fence"></div>
				<div class="link1"><a href="#">услуги</a></div>
			</div>
		*/
	
	    var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.appendChild(document.createTextNode('услуги'));
		
		var span = document.createElement('span');
		span.className = "notWork";
		span.appendChild(document.createTextNode('x'));
		a.appendChild(span);
		
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);

		
		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
	}
	
	function openClientManagerMenu(e){
		var target = e.target || e.srcElement;
		
		//target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}
		

		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
	    var url = "?page=clients&get_client_cont_faces=" + target.getAttribute('client_id');
	    
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
	   
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				   ///////////////////////////////////////////
				   // обрабатываем ответ сервера
					
					var request_response = request.responseText;
				    // alert(request_response);
					building_menu(request_response);
			
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////
		
		e.stopPropagation();
		
	    function building_menu(data){
			var data_arr = data.split('{@}');
			
			relate_container = target.parentNode;
			relate_container.style.position = 'relative';
			//relate_container.style.border = '#FF0000 solid 1px';
			//relate_container.style.width = '100%';
			
			// 
			var container = document.createElement('div');
			container.className = "contextWindow";
			container.setAttribute('type','windowContainer');
			container.style.position = 'absoute';
			container.style.width = "170px";
			container.style.top =  "17px";
			//container.style.bottom =  "20px";
			container.style.left = "101px";
			//container.style.right = "0px";
			container.style.display = "block";
			
			for(var i = 0 ; i < data_arr.length ; i++){
				var details_arr = data_arr[i].split('{;}');
				
				var innerDiv = document.createElement('div');
				innerDiv.className = "link1";
				var a = document.createElement('a');
				a.style.color = "#000";
				a.setAttribute('manager_id',details_arr[0]);
				a.onclick = set_manager;
				a.appendChild(document.createTextNode(details_arr[1]));
				innerDiv.appendChild(a);
				container.appendChild(innerDiv);
				
			}
			
			
			relate_container.appendChild(container);
			
			openCloseMenu.lastWindow = container;
			openCloseMenu.lastElement = target;
		}
		
		function set_manager(e){
		    e = e || window.event;
			
			var target = e.target;
			
			
			var manager_id = target.getAttribute('manager_id');
			var row_id = openCloseMenu.lastElement.getAttribute('row_id');
			
			openCloseMenu.lastElement.innerHTML = 'контакт: ' + target.innerHTML;
			document.getElementById('row_' + row_id).setAttribute('client_manager_id',manager_id);
			
			//////////////////////////////////////////////////////////////////////////////////////////
		    /////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		    var request = HTTP.newRequest();
	        var url = "?page=clients&set_manager_for_order=" + manager_id + "&row_id=" + row_id + "&control_num=" + document.getElementById('calculate_tbl').getAttribute('control_num');
	    
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

					}
					else{
					  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
		}
	}
	function openCloseContextMenu(e,id,control_num){

		if (e.preventDefault){
			if(e.button != 2) return;
			e.preventDefault(); 
		}
		else{
			if(e.button != 0) return;
			e.returnValue= false; 
		}
		
		var target = e.target || e.srcElement;
		
		target.addEventListener('click',setMenuWindow,false);
		function setMenuWindow(e){
			e.stopPropagation();
		}
		
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastElement) openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF';
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
		}
		
		target.style.backgroundColor = '#C6E09F';
		target.style.position = 'relative';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "160px";
		div.style.top =  "0px";
		div.style.left = "20px";
		div.style.display = "block";
		
		var cup = document.createElement('div');
		cup.className = "cup";
		cup.appendChild(document.createTextNode('Добавить строку:'));
		div.appendChild(cup);
		
		
		//<div class="link2Arrow"><a href="#" onclick="return openExtraContextWindow(this,'article')">основной каталог</a></div>
		var innerDivsArr = [['основной каталог','article'],['другой каталог','ordinary'],['полиграфия',''],['разделитель','']];
		for( var i= 0 ; i < innerDivsArr.length; i++){
			var innerDiv = document.createElement('div');
			innerDiv.className = "link2Arrow";
			var a = document.createElement('a');
			a.setAttribute('type',innerDivsArr[i][1]);
			a.setAttribute('action','add_rows_to_rt');
			a.setAttribute('id',id);
			a.setAttribute('control_num',control_num);
			a.appendChild(document.createTextNode(innerDivsArr[i][0]));
			if(innerDivsArr[i][1]) a.onclick = openExtraContextWindow;
			else{
				var span = document.createElement('span');
		        span.className = "notWork";
				span.appendChild(document.createTextNode('x'));
		        a.appendChild(span);
			}
			
			innerDiv.appendChild(a);
			div.appendChild(innerDiv);
		}
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('control_num',control_num);
		a.setAttribute('id',id);
		a.onclick = copyRow;
		a.appendChild(document.createTextNode('Копировать строку'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('control_num',control_num);
		a.setAttribute('id',id);
		a.onclick = insertRow;
		a.appendChild(document.createTextNode('Вставить строку'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
	}
	
	openExtraContextWindow.lastWindow = null;
	openExtraContextWindow.lastElement = null;
	function openExtraContextWindow(e){
		e = e || window.event;
		
		var element = e.target;
		
		if(openExtraContextWindow.lastElement === element ) return false;
		
		var mainWindowContainer = retriveWindowContainer(element);
		if(!mainWindowContainer){ alert('"windowContainer" not defined'); return false; }
		
		if(openExtraContextWindow.lastWindow){
			openExtraContextWindow.lastWindow.parentNode.removeChild(openExtraContextWindow.lastWindow);
			openExtraContextWindow.lastWindow = null;
		}

		var type = element.getAttribute('type');
		var action = element.getAttribute('action');
		var id = (element.getAttribute('id'))?element.getAttribute('id'):'';
		var control_num = (element.getAttribute('control_num'))?element.getAttribute('control_num'):'';
		
		mainWindowContainer.appendChild(buildExtraContextWindow(getY(element,mainWindowContainer),action,type));
		
		function buildExtraContextWindow(top,action,type){
			var div = document.createElement('div');
			div.className = "contextWindow";
			div.id = "quickContextExtraWindow";
			div.style.width = "160px";
			div.style.top = (top - 36) + "px";//
			div.style.left = "160px";
			div.style.display = "block";

			
			var cup = document.createElement('div');
			cup.className = "cup";
			cup.appendChild(document.createTextNode('Количество товаров:'));
			div.appendChild(cup);
			
			
			var cell_values = [1,3,5,7,9,10];
			for( var i= 0 ; i < cell_values.length; i++){
			    var floatDiv = document.createElement('div');
				floatDiv.className = "specFloatLink";
				var a = document.createElement('a');
				a.href = '#';
				a.setAttribute('cell_value',cell_values[i]);
				a.onclick=function(){
					 show_processing_timer();
					 location.search = '?'+addOrReplaceGetOnURL( action+'=true&type_row='+type+'&num='+this.getAttribute('cell_value')+'&id='+id +'&control_num='+control_num);
				}
				a.appendChild(document.createTextNode(cell_values[i]));
				floatDiv.appendChild(a);
				div.appendChild(floatDiv);
			}
			openExtraContextWindow.lastWindow = div;
			return div;
	    }
		function retriveWindowContainer(e){
			while(e && e != document.body){
				 if(e.getAttribute && e.getAttribute('type') == 'windowContainer') return e;
				 e = e.parentNode;
			}
			return false;
	    }
		function getY(e,mainWindowContainer){
			var y = 0;
			while(e != mainWindowContainer){
				 y += e.offsetTop;
				 e = e.offsetParent;
			}
			return y;
	    }
		openExtraContextWindow.lastElement = element;
		return false;
		
	}
	function copyRow(e){
		
		e = e || window.event;
		var element = e.target;
		var control_num = element.getAttribute('control_num');
		var id = element.getAttribute('id');
		
		remember_row_id(id,control_num);
		closeAllMenuWindows();
	} 
	
	
	function insertRow(e){
		
		e = e || window.event;
		var element = e.target;
		
		var control_num = element.getAttribute('control_num');
		var id = element.getAttribute('id');
		location = '/os/?' + addOrReplaceGetOnURL('insert_copied_row=1&id='+id +'&control_num='+control_num);
	  
	}
	
	function remember_row_id(id,control_num){

		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
	    var url = "?page=clients&id=" + id + "&control_num=" + control_num + "&remember_row_id=1";
	  
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
	   
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				   ///////////////////////////////////////////
				   // обрабатываем ответ сервера
					
					var request_response = request.responseText;
					
					// выводим замечание об ощибке если есть
				    if(request_response != '') alert(request_response);
			
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////
	}
	
	function makeOrder(e){

		e = e || window.event;
		var element = e.target;
        
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор - должна быть нажата только одна зеленая кнопка (если больше или ни одна прерываемся)
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var nothing = true;
		var pos_id = false;
		var idsObj = {};
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			var flag ;
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else pos_id = false;
				}
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						nothing = false;
					}
				}
			}
		}
		
		// проверяем сколько зеленых кнопок светофора были нажатч и  в итоге были учтены
		var more_then_one = false;
		var less_then_one = false;
		for(var index in idsObj){
			var counter = 0;
			for(var index2 in idsObj[index]){
				counter++;
			}
			if(counter>1) more_then_one = true;
			if(counter==0) less_then_one = true;
		}
		
		//var conrtol_num = getControlNum();
        //console.log(JSON.stringify(idsObj));
        
		if(nothing || more_then_one || less_then_one){
			if(nothing) alert('не возможно создать заказ,\rвы не выбрали ни одной позиции');
			if(more_then_one) alert('не возможно создать заказ,\rдля позиции(ий) выбрано более одного варианта расчета');
			if(less_then_one) alert('не возможно создать заказ,\rдля позиции(ий) невыбрано ни одного варианта расчета');
			return;
		}
		
	    show_processing_timer();
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
	    // формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('make_order={"ids":'+JSON.stringify(idsObj)+',"client_id":"'+client_id+'","query_num":"'+query_num+'"}');
		// AJAX запрос
		make_ajax_request(url,callback);
		//alert(last_val);
		function callback(response){ 
		   
		    /*if(response == '1') location = OS_HOST+'?page=client_folder&section=business_offers&query_num='+query_num+'&client_id='+client_id;*/
		    console.log(response); 
			close_processing_timer(); closeAllMenuWindows();
		}	  

	}
	
	function makeComOffer(e){
		
		e = e || window.event;
		var element = e.target;
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
        if(!(idsObj = rtCalculator.get_active_rows())){
			alert('не возможно создать КП, вы не выбрали ни одной позиции');
			return;
		} 
		
		show_processing_timer();
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
	    // формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('make_com_offer={"ids":'+JSON.stringify(idsObj)+',"client_id":"'+client_id+'","query_num":"'+query_num+'"}');
		// AJAX запрос
		make_ajax_request(url,callback);
		//alert(last_val);
		function callback(response){ 
		   
		    if(response == '1') location = OS_HOST+'?page=client_folder&section=business_offers&query_num='+query_num+'&client_id='+client_id;
		    /*console.log(response);*/ 
			close_processing_timer(); closeAllMenuWindows();
		}	  
	}
	
	function makeComOfferOld(e){
		
		e = e || window.event;
		var element = e.target;
		
		var str_for_url = (getIdsOfCheckedRows('rt_tbl_body')).join(';');

		var order_data = getFirstRelatedOrderNumAndManagerName(str_for_url);
		var conrtol_num = getControlNum();

		if(str_for_url == ''){
			alert('вы не выбрали ни одной позиции');
			return;
		}
		var stock = element.getAttribute('stock');
		
		show_processing_timer();
		
		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////

		var request = HTTP.newRequest();
		var url = '/os/?' + addOrReplaceGetOnURL('ajax_make_com_offer=1&conrtol_num='+conrtol_num + "&order_num=" + order_data.order_num + "&client_manager_id=" +order_data.client_manager_id + "&stock=" + stock + "&data=" + str_for_url);
	   
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
					
				    if(request_response == '1') location='/os/?' + addOrReplaceGetOnURL('subsection=business_offers','ajax_make_com_offer&conrtol_num&stock=&data');
					
					// выводим замечание об ощибке если есть
			        if(request_response != '1') alert(request_response);
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////	  
	}
	
	function setSamplesList(e){
		
		e = e || window.event;
		var element = e.target;
		
		var str_for_url = (getIdsOfCheckedRows()).join(';');
		var conrtol_num = getControlNum();
		
		if(str_for_url == ''){
			alert('вы не выбрали ни одной позиции');
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
		var url = '/os/?' + addOrReplaceGetOnURL('ajax_set_samples_list=1&conrtol_num='+conrtol_num + '&data=' + str_for_url);
	   
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

					
				    if(request_response == '1') location='/os/?' + addOrReplaceGetOnURL('subsection=business_offers','ajax_make_com_offer&conrtol_num&stock=&data');
					
					// выводим замечание об ощибке если есть
			        alert(request_response);
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////	  
	}