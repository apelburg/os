// JavaScript Document

    var tableDataManager = {
		url: null,
		container: null,
		processing_timer_div: null,
		max_length: null,
		element: null,
	    install: function(){
				 
				 var all_tables = document.getElementsByTagName('table');
				 for(var i = 0; i < all_tables.length; i++)
				 {
					  if(all_tables[i].getAttribute('tbl') && all_tables[i].getAttribute('tbl') == 'managed') var table = all_tables[i];
				 }
				 //table.style.border = '#FF0000 solid 2px';
				 if(!table) alert('нет "managed" таблиц');
				 
				 var all_divs = table.getElementsByTagName('div');
				 for(var i = 0; i < all_divs.length; i++)
				 {
					 if(all_divs[i].getAttribute('managed') && all_divs[i].getAttribute('managed') == 'text'){
						 all_divs[i].onclick = tableDataManager.text_cell_handler;
						 all_divs[i].style.cursor = 'pointer';
						 //all_divs[i].style.border = '#FF0000 solid 2px';
						 
					 }
					 
					 //if(all_tds[key].getAttribute('managed') && all_tds[key].getAttribute('managed') == 'num')  all_tds[key].onclick = this.nun_cell_handler;
				 }
		}
		,
		text_cell_handler: function(){
			var element = this;
			tableDataManager.build_edit_field(element);
			
			//alert(this.innerHTML);
		}
		,
		build_edit_field: function(element){
			//alert(element);
			this.element = element;
			var container = document.createElement('div');
			var pos = tableDataManager.define_item_positon();
			container.style.position = 'absolute';
			container.style.top = pos[0] + 'px';
			container.style.left = pos[1] + 'px';
			
			var edit_field = document.createElement('div');
			edit_field.style.border = '#499BEF solid 2px';
			edit_field.style.padding = '4px';
			edit_field.style.backgroundColor = '#FFFFFF';
			edit_field.style.width = '100%';
		    edit_field.style.minHeight = '30px';
			edit_field.contentEditable = "true";
	        edit_field.innerHTML = this.element.innerHTML;
			if(this.element.getAttribute('max_length')){
				this.max_length = this.element.getAttribute('max_length');
				edit_field.onkeydown = function(){ 
				    var new_data = edit_field.innerHTML;
					if(new_data.length > tableDataManager.max_length){
						alert('максимально допустимое количество символов = ' + tableDataManager.max_length + '\r\n Вы ввели ' + new_data.length + ' символ!');
						edit_field.innerHTML = (edit_field.innerHTML).slice(0,30);
					}
			    }
			}
			
			
			
			// close button
			var close_btn = document.createElement('div');
			close_btn.style.border = '#000000 solid 1px';
			close_btn.style.backgroundColor = '#E2C759';
			close_btn.style.color = '#743813';
			close_btn.style.fontWeight = 'bold';
			close_btn.style.border = '#BA7113 solid 2px';
			close_btn.style.padding = '6px';
			close_btn.style.width = '70px';
			close_btn.innerHTML = 'сохранить';
			close_btn.style.cursor = 'pointer';
			close_btn.onclick = function(){ tableDataManager.save_data(container,edit_field); }
			
			container.appendChild(edit_field);
			container.appendChild(close_btn);
			document.body.appendChild(container);
			
			this.container = container;
		
			//var pos = tableDataManager.define_item_positon(element);
			//alert(Geometry.getVerticalScroll());//Geometry.getHorizontalScroll
			 
		}
		,
		save_data: function(container,edit_field){
			
			var new_data = edit_field.innerHTML;
            this.processing_timer(tableDataManager.container.style.top,tableDataManager.container.style.left);
			
			container.parentNode.removeChild(container);
			tableDataManager.container = null;
			this.element.innerHTML = new_data;
			
			// для измененения в базе данных
			var bd_row_id = (this.element.getAttribute('bd_row_id'))? this.element.getAttribute('bd_row_id') : false;
			var bd_field = (this.element.getAttribute('bd_field'))? this.element.getAttribute('bd_field') : false;
			// для измененения в файле
			var file_name = (this.element.getAttribute('file_name'))? this.element.getAttribute('file_name') : false;
			var file_exicution = (this.element.getAttribute('file_exicution'))? this.element.getAttribute('file_exicution') : false;
		
			var whenDone = (this.element.getAttribute('when_done'))? this.element.getAttribute('when_done') : false;
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////		
			
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        if(bd_row_id) var pair = "&id=" + bd_row_id + "&field_name=" + bd_field + "&field_val=" + encodeURIComponent(new_data).replace(regexp,"+");
			if(file_exicution) var pair = "&file_name=" + file_name + "&" + file_exicution + "=" + encodeURIComponent(new_data).replace(regexp,"+");

	        var request = HTTP.newRequest();
	  
			var url = this.url;
			//alert(url);
			//return;
			// производим запрос
			request.open("POST", url); 
			request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			request.send(pair);
			
			
		   
			request.onreadystatechange = function(){ // создаем обработчик события
			   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
				   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
					   ///////////////////////////////////////////
					   // обрабатываем ответ сервера
						
						var request_response = request.responseText;
						//alert(request_response);
						tableDataManager.stop_processing_timer();

						if(whenDone){
							if(tableDataManager[whenDone]) tableDataManager[whenDone]();
						}
				        tableDataManager.element = null;
					   //alert("AJAX запрос выполнен");
					 
					}
					else{
				      textRedactor.stop_processing_timer();
					  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
			
			//////////////////////////////////////////////////////////////////////////////////////////
		}
		,
		define_item_positon: function(){
			var element = this.element;
			var top = 0;
			var left = 0;
			while(element){
				top  += element.offsetTop;
				left += element.offsetLeft;
				element = element.offsetParent;
			}
			// прокручиваемые области
			for(e = this.element.parentNode; e && e != document.body; e = e.parentNode) if(e.scrollTop) top -= e.scrollTop;
			var pos = [];
			pos[0] = top;
			pos[1] = left;
			return pos;

		}
		,
		processing_timer: function(top,left){
			function show_timer(){
				var container = document.createElement('div');
				container.style.position = 'absolute';
				container.style.top = top;
				container.style.left = left;
				container.style.height = '40px';
				container.style.width = '40px';
			
				
				var img = new Image();
				img.src = '../../admin/order_manager/libs/js/img/loading.gif';
				
				container.appendChild(img);
				document.body.appendChild(container);
				tableDataManager.processing_timer_div = container;	
			}
			setTimeout(show_timer,5);
			

		}
		,
		stop_processing_timer: function(){
		    if(this.processing_timer_div) this.processing_timer_div.parentNode.removeChild(this.processing_timer_div);	

		}
		,
		set_color: function(){
		   if(this.element.innerHTML.replace(/^\s\s*/, '').replace(/\s\s*$/, '') != 'добавьте свой комментарий'){
			   this.element.style.color = '#000000';
			   this.element.style.fontStyle = 'normal';
		   }
		   else{
			   this.element.style.color = '#AAA';
			   this.element.style.fontStyle = 'italic';
		   }
		}
		
		
		
	}
	
	// инициализация	
	if(window.addEventListener) window.addEventListener('load',tableDataManager.install,false);
	else if(window.attachEvent) window.attachEvent('onload',tableDataManager.install);
	else window.onload = tableDataManager.install;
	