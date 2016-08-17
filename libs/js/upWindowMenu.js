// JavaScript Document
    // по окончании нового варианта КП удалить функцию contextmenu и все что с ней связанно
	// удалить  insertRow, copyRow
	// удалить функцию openCloseTableMenu

    document.addEventListener('contextmenu',closeAllMenuWindows,false);
	document.addEventListener('contextmenuNew',closeAllMenuWindows,false);
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
		if(type == 'contextmenuNew') openCloseContextMenuNew(e,params.pos_id,params.control_num);
		if(type == 'tableMenu') openCloseTableMenu(e);
		if(type == 'rtMenu') openCloseRtMenu(e);
		if(type == 'quickMenu') openCloseQuickMenu(e);
		if(type == 'clientManagerMenu') openClientManagerMenu(e);
		if(type == 'calcLevelSwitcher') calcLevelSwitcher(e);
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
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "220px";
		div.style.top =  "19px";
		div.style.left = "-3px";
		div.style.display = "block";
		
		
		 var innerDiv = document.createElement('div');
		innerDiv.className = "cup";
		innerDiv.appendChild(document.createTextNode('Применить ярлык:'));
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('status','red');
		a.href = '#';
		a.appendChild(document.createTextNode('Нет в наличии'));
		a.onclick = rtCalculator.setSvetoforStatusIn;
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.setAttribute('status','red');
		a.href = '#';
		a.onclick = rtCalculator.setSvetoforStatusIn;
		a.appendChild(document.createTextNode('Отказано'));
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
		a.onclick = openExtraContextWindow2;
		a.appendChild(document.createTextNode('Дату сдачи'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		/*var innerDiv = document.createElement('div');
		innerDiv.className = "fence";
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = rtCalculator.sendToSnab;
		a.appendChild(document.createTextNode('Отправить в снаб'));
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
		div.appendChild(innerDiv);*/
		
		
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
		/*
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = makeOrder;
		a.appendChild(document.createTextNode('Окончательный заказ'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);*/
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = rtCalculator.makeSpecAndPreorder;
		a.appendChild(document.createTextNode('Спецификацию к договору'));
		a.setAttribute('doc_type','spec');
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link2";
		var a = document.createElement('a');
		a.href = '#';
		a.onclick = rtCalculator.makeSpecAndPreorder;
		a.appendChild(document.createTextNode('Счет оферту'));
		a.setAttribute('doc_type','oferta');
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
		a.onclick = rtCalculator.copy_rows;
		a.appendChild(document.createTextNode('Скопировать строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('type','rows');
		a.href = '#';
		a.onclick = rtCalculator.deleting;
		a.appendChild(document.createTextNode('Удалить строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('type','prints');
		a.href = '#';
		a.onclick = rtCalculator.deleting;
		a.appendChild(document.createTextNode('Удалить услуги'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
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

	function calcLevelSwitcher(e){
		var target = e.target || e.srcElement;

		function setMenuWindow(e){
			e.stopPropagation();
		}
		
		if(openCloseMenu.lastElement === target ) return;
		
		if(openCloseMenu.lastWindow){
			openCloseMenu.lastWindow.parentNode.removeChild(openCloseMenu.lastWindow);
			openCloseMenu.lastWindow = null;
			openCloseMenu.lastElement.style.backgroundColor = '#FFFFFF'
		}/**/
		
		var relate_container = target.parentNode;
		relate_container.style.position = 'relative';
		
		var container = document.createElement('div');
		container.className = "contextWindow";
		container.setAttribute('type','windowContainer');
		container.style.position = 'absoute';
		container.style.width = "151px";
		container.style.top =  "23px";
		//container.style.bottom =  "20px";
		container.style.left = "9px";
		//container.style.right = "0px";
		container.style.display = "block";
		
		var data_arr = [{level:'full',levelRu:'Конечники'},{level:'ra',levelRu:'Рекламщики'}];
		var ln = data_arr.length;
		
		
		for(var i = 0 ; i < ln ; i++){
	
			
			var innerDiv = document.createElement('div');
			innerDiv.className = "link1";
			var a = document.createElement('a');
			a.style.color = "#000";
			a.setAttribute('level',data_arr[i].level);
			a.onclick = set_level;
			a.appendChild(document.createTextNode(data_arr[i].levelRu));
			innerDiv.appendChild(a);
			container.appendChild(innerDiv);		
		}

		relate_container.appendChild(container);
		
		openCloseMenu.lastWindow = container;
		openCloseMenu.lastElement = target;

		e.stopPropagation();
		////////////////////////////////////////////////////////////////////////
		function set_level(e){
		    e = e || window.event;
			
			var target = e.target;
			var level = target.getAttribute('level');
			
			openCloseMenu.lastElement.innerHTML = 'Калькулятор: '+target.innerHTML;
			document.getElementById('calcLevelStorage').value = level;
			
			
			//////////////////////////////////////////////////////////////////////////////////////////
		    /////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		    var request = HTTP.newRequest();
			var url = '?'+addOrReplaceGetOnURL( 'setCalcualtorLevel='+level,'');
			 
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
						
						

					}
					else{
					  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
		}
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
					var dop_data = {};
					if(target.getAttribute('sourse')) dop_data.sourse = target.getAttribute('sourse');
					if(target.getAttribute('row_id')) dop_data.row_id = target.getAttribute('row_id');
					if(target.getAttribute('query_num')) dop_data.query_num = target.getAttribute('query_num');
					
					building_menu(request_response,dop_data);
			
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////
		
		e.stopPropagation();
		
	    function building_menu(data,dop_data){

			if(data !='') var data_arr = data.split('{@}');
			
			relate_container = target.parentNode;
			relate_container.style.position = 'relative';
			
			var container = document.createElement('div');
			container.className = "contextWindow";
			container.setAttribute('type','windowContainer');
			container.style.position = 'absoute';
			container.style.width = "250px";
			container.style.top =  "23px";
			//container.style.bottom =  "20px";
			//container.style.left = "101px";
			container.style.right = "0px";
			container.style.display = "block";
			if(data_arr && data_arr.length>0){
				for(var i = 0 ; i < data_arr.length ; i++){
					var details_arr = data_arr[i].split('{;}');
					
					var innerDiv = document.createElement('div');
					innerDiv.className = "link1";
					var a = document.createElement('a');
					a.style.color = "#000";
					a.setAttribute('manager_id',details_arr[0]);
					if(dop_data.sourse) a.setAttribute('sourse',dop_data.sourse);
					if(dop_data.row_id) a.setAttribute('row_id',dop_data.row_id);
					if(dop_data.query_num) a.setAttribute('query_num',dop_data.query_num);
					a.onclick = set_manager;
					a.appendChild(document.createTextNode(details_arr[1]));
					innerDiv.appendChild(a);
					container.appendChild(innerDiv);		
				}
			}
			else{
				var innerDiv = document.createElement('div');
				innerDiv.className = "link1";
				var a = document.createElement('a');
				a.innerHTML = "нет данных";
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
			//alert(target.getAttribute('sourse')+' '+target.getAttribute('row_id'));
			var row_id = (target.getAttribute('row_id'))? target.getAttribute('row_id') : false;
			var query_num = (target.getAttribute('query_num'))? target.getAttribute('query_num') : false;
			var sourse = target.getAttribute('sourse');
			
			if(sourse=='kp')  openCloseMenu.lastElement.innerHTML = target.innerHTML;
			if(sourse=='rt')  openCloseMenu.lastElement.innerHTML = 'Контактное лицо: '+target.innerHTML;
			//openCloseMenu.lastElement.innerHTML = 'контакт: ' + target.innerHTML;
			//document.getElementById('row_' + row_id).setAttribute('client_manager_id',manager_id);
			
			//////////////////////////////////////////////////////////////////////////////////////////
		    /////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		    var request = HTTP.newRequest();
	        //var url = "?page=clients&set_manager_for_order=" + manager_id + "&row_id=" + row_id + "&control_num=" + document.getElementById('calculate_tbl').getAttribute('control_num');
			if(sourse=='kp')  var url = "?page=client_folder&section=business_offers&set_recipient=" + manager_id + "&row_id=" + row_id;
	        if(sourse=='rt')  var url = "?page=client_folder&set_cont_face=" + manager_id + "&query_num=" + query_num;

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
	
	function openCloseContextMenuNew(e,pos_id,control_num){

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
		
		openCloseContextMenuNew.lastElement = target;
		
		target.style.backgroundColor = '#C6E09F';
		target.style.position = 'relative';
	
		// building menu
		var div = document.createElement('div');
		div.className = "contextWindow";
		div.setAttribute('type','windowContainer');
		//div.id = "quickContextExtraWindow";
		div.style.width = "210px";
		div.style.top =  "2px";
		div.style.left = "33px";
		div.style.display = "block";

		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.href = '#';
		a.setAttribute('type','rows');
		a.setAttribute('pos_id',pos_id);
		a.onclick = rtCalculator.deleting;
		a.appendChild(document.createTextNode('Удалить строку'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('type','prints');
		a.setAttribute('pos_id',pos_id);
		a.href = '#';
		a.onclick = rtCalculator.deleting;
		a.appendChild(document.createTextNode('Удалить услуги'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);

		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('pos_id',pos_id);
		a.onclick =  rtCalculator.copy_row;
		a.appendChild(document.createTextNode('Копировать строку'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('control_num',control_num);
		a.setAttribute('pos_id',pos_id);
		a.onclick = rtCalculator.insert_copied_rows;
		a.appendChild(document.createTextNode('Вставить скопированные строки'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);
		
		var innerDiv = document.createElement('div');
		innerDiv.className = "link1";
		var a = document.createElement('a');
		a.setAttribute('pos_id',pos_id);
		a.onclick = function(e){
			// снимаем выделение строки
			target.style.backgroundColor = '';
			// скрываем меню
			div.style.display = 'none';
			// вызываем галлерею
			new galleryWindow(Number($(this).attr('pos_id')));
		};
		a.appendChild(document.createTextNode('Галерея изображений'));
		innerDiv.appendChild(a);
		div.appendChild(innerDiv);

		target.appendChild(div);
		
		openCloseMenu.lastWindow = div;
	    openCloseMenu.lastElement = target;
		
		e.stopPropagation();
		
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
	
	openExtraContextWindow2.lastWindow = null;
	openExtraContextWindow2.lastElement = null;
	function openExtraContextWindow2(e){
		e = e || window.event;
		
		var element = e.target;
		var positions_num =  rtCalculator.get_positions_num_in_query();
		if(positions_num==0){
			echo_message_js('В заявке отсутствуют позиции','system_message',2000);
			return;
		}
		var idsArr = rtCalculator.get_active_main_rows();
		if(!idsArr){
			echo_message_js('невозможно установить дату сдачи, не выбрано ни одного расчета','system_message',2000);
			closeAllMenuWindows();
		    return;
		}
		if(idsArr.length==positions_num){ 
		    if(!confirm('Новая дата будет установлена на все позиции заказа')){
				closeAllMenuWindows();
				return;
			}
		}
		else{ 
		    if(!confirm('Новая дата будет установлена на '+idsArr.length+ ' позицию(ий) заказа')){
				closeAllMenuWindows();
				return;
			}
		}
		
		if(openExtraContextWindow2.lastElement === element ) return false;
		
		var mainWindowContainer = retriveWindowContainer(element);
		if(!mainWindowContainer){ alert('"windowContainer" not defined'); return false; }
		
		if(openExtraContextWindow2.lastWindow){
			openExtraContextWindow2.lastWindow.parentNode.removeChild(openExtraContextWindow2.lastWindow);
			openExtraContextWindow2.lastWindow = null;
		}

		var type = element.getAttribute('type');
		var action = element.getAttribute('action');
		//var id = (element.getAttribute('id'))?element.getAttribute('id'):'';

		
		mainWindowContainer.appendChild(buildExtraContextWindow(getY(element,mainWindowContainer),action,type));
		//calendar.calendarLaunchBtnContainer = document.getElementById("callCalendarButton");
		//calendar.show();
		
		function buildExtraContextWindow(top,action,type){
			var div = document.createElement('div');
			div.className = "contextWindow rtCalendarContextWindow";
			div.id = "quickContextExtraWindow";
			div.style.width = "425px";
			div.style.top = (top - 36) + "px";//
			div.style.left = "160px";
			div.style.display = "block";

			
			var div_float_right1 = document.createElement("div"); // плавающий div контейнер
			div_float_right1.style.float ='left';
			div_float_right1.style.margin ='10px 10px 0px 10px';
			div_float_right1.style.width ='190px';
			div_float_right1.style.border ='#000000 solid 0px';
			
		    /*var cup = document.createElement('div');
			cup.className = "cup";
			cup.appendChild(document.createTextNode('Выберите дату:'));*/
			calendar_consturctor.setContextDay();
			var calendar = calendar_consturctor.calendarTableBilder('calendar_table');
			calendar[0].style.width = '180px';
			calendar[0].appendChild(calendar[1]);
			calendar[0].appendChild(calendar[2]);
			calendar[0].appendChild(calendar[3]);
			div_float_right1.appendChild(calendar[0]);
			div.appendChild(div_float_right1);
			
			var div_float_right2 = document.createElement("div"); // плавающий div контейнер
			div_float_right2.style.float ='left';
		    div_float_right2.style.margin ='10px 0px 0px 0px';
			div_float_right2.style.width ='180px';
			div_float_right2.style.border ='#000000 solid 0px';
			var time_table = calendar_consturctor.timeTable('time_table');
			time_table[0].style.width = '180px';
			time_table[0].style.margin ='0px 0px 0px 0px';
			time_table[0].appendChild(time_table[1]);
			time_table[0].appendChild(time_table[2]);
			time_table[3].style.margin ='2px 0px 0px 0px';
			 
			div_float_right2.appendChild(time_table[3]);
			div_float_right2.appendChild(time_table[0]);
			div.appendChild(div_float_right2);
			 
			var clear_div = document.createElement('div'); 
		    clear_div.style.clear ='both';
			var button_div = document.createElement('div'); 
			button_div.style.textAlign ='center';
			button_div.style.margin ='10px 0px 0px 0px';
			button_div.style.border ='#000000 solid 0px';
			var button_ok = document.createElement("input"); 
			button_ok.type = 'submit';
			button_ok.name = 'set_plan';
			//button_ok.innerHTML = 'ok';
			button_ok.onclick = function(){ location = location.search + '&set_order_deadline=&date='+calendar[3].value+'&time='+time_table[2].value+'&ids='+JSON.stringify(idsArr);}
			button_ok.value = 'ok';
			button_ok.style.height = '30px';
			button_ok.style.width = '90px';
			button_ok.style.backgroundColor = "rgb(122, 189, 121)";
			button_ok.style.border = "#555 solid 1px";
			button_ok.style.borderRadius ='2px';
			button_ok.style.cursor = 'pointer';
			 
			 
			div.appendChild(clear_div);
			button_div.appendChild(button_ok);
		    div.appendChild(button_div);
			
			openExtraContextWindow2.lastWindow = div;
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
		openExtraContextWindow2.lastElement = element;
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
	
	function makeComOffer(e){
		
		e = e || window.event;
		var element = e.target;

		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
        if(!(idsObj = rtCalculator.get_active_rows())){
			echo_message_js('не возможно создать КП, не выбрано ни одного расчета','system_message',2000);
			return;
		} 
		/* console.log(idsObj);return; */
		show_processing_timer();
		var query_theme = (document.getElementById('query_theme_input').value == "Введите тему")?'':document.getElementById('query_theme_input').value;
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
		
		var make_com_offer_obj = {};
		make_com_offer_obj.ids = idsObj;
		make_com_offer_obj.client_id = client_id;
		make_com_offer_obj.query_num = query_num;
		make_com_offer_obj.query_theme = query_theme;
		
	    // формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('make_com_offer='+JSON.stringify(make_com_offer_obj));
		// alert(url);
		// AJAX запрос
		make_ajax_request(url,callback);
		function callback(response){ 
		    //alert(response);
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
			echo_message_js('вы не выбрали ни одной позиции','system_message',2000);
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
			echo_message_js('вы не выбрали ни одной позиции','system_message',2000);
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
	
	function makeOrder(e){

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
		// обходим ряды таблицы
		var ln = trsArr.length;
		for( var i= 0 ; i < ln; i++){
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
				var tdsArr = $(trsArr[i]).children('td');
				var ln = tdsArr.length;
				for( var j= 0 ; j < ln; j++){
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
			if(nothing) echo_message_js('не возможно создать заказ, вы не выбрали ни одной позиции','system_message',2000);
			if(more_then_one) echo_message_js('не возможно создать заказ, для позиции(ий) выбрано более одного варианта расчета','system_message',2000);
			if(less_then_one) echo_message_js('не возможно создать заказ, для позиции(ий) невыбрано ни одного варианта расчета','system_message',2000);
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