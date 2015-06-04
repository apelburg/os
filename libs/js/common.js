// JavaScript Document
    var OS_HOST = location.protocol+'//'+location.hostname+'/os/';
	
    var error_report = '';
	window.onerror = function(msg,url,line){
		error_report += msg + ' line:' + line + ' ' + url +'\r\n';
		return true;
	}
	
	// Фабрика создания AJAX объектов
	var HTTP = {}; 
	HTTP._factories = [
		function(){ return new XMLHttpRequest(); }, // создаем объект
		function(){ return new ActiveXObject("Msxml2.XMLHTTP"); }, // создаем объект
		function(){ return new ActiveXObject("Microsoft.XMLHTTP"); }// создаем объект
	]; 
	HTTP._factory = null ;
	HTTP.newRequest = function(){
		if(HTTP._factory != null) return HTTP._factory();
		
		for( var i = 0 ; i < HTTP._factories.length ; i++ ){
			try{
				var factory = HTTP._factories[i];
				var request = factory();
				if(request != null){
					HTTP._factory = factory; 
					return request;
				}
			}
			catch(e){
				continue;
			}
		}
		HTTP._factory = function(){
			throw new Error("Объект XMLHttpRequest не поддерживается");
		}
		HTTP._factory();
	}
	
	function make_ajax_request(url,call_back){
		
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
				    call_back(request_response);
			
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("AJAX запрос невыполнен");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////
	}
	
	function make_ajax_post_request(url,pairs,call_back){
		
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////

		var request = HTTP.newRequest();
	  
	    // производим запрос
	request.open("POST", url); 
	request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	request.send(pairs);
	   
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				   ///////////////////////////////////////////
				   // обрабатываем ответ сервера
					
					var request_response = request.responseText;
				    call_back(request_response);
			
				   //alert("AJAX запрос выполнен");
				 
			    }
			    else{
				  alert("AJAX запрос невыполнен");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////
	}
	
	var help = {
		btn:function(topic){

			var btn = document.createElement('div');
			//btn.className = 'helpBtn';
			btn.style.position = 'absolute';
			btn.style.top = '0px';
			btn.style.right = '0px';
			btn.style.cursor = 'pointer';
			btn.innerHTML = 'help';
			btn.setAttribute('topic',topic);
			btn.onclick = help.show;
			
			return btn;
		}
		,
		show:function(e){
			var btn = e.target || e.srcElement;
			var topic = btn.getAttribute('topic');
			
			var url = location.protocol +'//'+ location.hostname+'/os/?help='+topic;
			
			make_ajax_request(url,call_back);
			function call_back(response){
				//alert(response);
				
				var box = document.createElement('div');
				box.id = "helpDialog";
				box.innerHTML = response;
				document.body.appendChild(box);
				$("#helpDialog").dialog({autoOpen: false,width:800,title: "help", close:function(){$("#mailResponseDialog").remove();} });
			    $("#helpDialog").dialog("open");
			}
		}
	}
	
	function addOrReplaceGetOnURL(new_get,del_get){
 
        // данные из строки запроса
	    var pairs = location.search.slice(1);
		pairs = pairs.split('&');
		
		var pairs_obj = {} ;
		for(var i = 0 ; i < pairs.length; i++ ){
			 var param = pairs[i].slice(0,pairs[i].indexOf('='));
			 var value = pairs[i].slice(pairs[i].indexOf('=')+1);
			 
			 pairs_obj[param] = decodeURIComponent(value);
			 
		}
		
		// новые данные для замены существующих параметров или добавления новых
		if(new_get != ''){
			var new_pairs_obj = {} ;
			
		    // создаем массив новых данных
			var new_pairs = new_get.split('&');
		 	for(var i = 0 ; i < new_pairs.length; i++ ){
				var param = new_pairs[i].slice(0,new_pairs[i].indexOf('='));
			    var value = new_pairs[i].slice(new_pairs[i].indexOf('=')+1);
				
				new_pairs_obj[param] = value;
				
			}
			//
			for(var param in new_pairs_obj){
			    pairs_obj[param] = new_pairs_obj[param];	
			}
	 	}
		// параметры для удаления
		if(del_get){
			//alert(del_get);
			var del_get_params = del_get.split('&');
			// создаем параметров удаления
			var del_get_params_obj = {};
		 	for(var param in del_get_params){
				var del_param = del_get_params[param];
				//alert(del_param);
				del_get_params_obj[del_param] = 1;
			}
			//
  
			var counter = 0;
			for(var param in pairs_obj){
				if(del_get_params_obj[param]) delete(pairs_obj[param]);
		    }
		}

		//print_r($pairs_arr);
		var itog_pairs = [];
		var counter = 0;
		for(var param in pairs_obj){
				itog_pairs[counter++] = param + '=' + pairs_obj[param]; 	
		}
	    
		// 
		return itog_pairs.join('&');
		//alert(itog_pairs.join('&'));
    }
	
	function show_hide_div(id,mode){
		var div = document.getElementById(id);
		if(mode) div.style.display = mode;
		else div.style.display = (!div.style.display || div.style.display == 'none')?'block':'none' ;
		return false;
	}
	
	function change_href(a){
		var img = a.firstChild;
		var src = img.src;
		var path = src.slice(0,src.lastIndexOf('.'));
		var extention = src.slice(src.lastIndexOf('.'));
		//alert(path+ '_hover'+  extention);
		img.src = path+ '_hover'+  extention;
		a.onmouseout = function(){
			img.src = src;
		}
	}
	
	function print_r(val/* array or object */){
		var str = scan(val);
		var win = window.open(null,'print_r','width=300,height=800',true);
		win.document.write(str);
		win.document.close();
		
		function scan(val){
			var str = '';
			for(var i in val){
				if(typeof val[i] != 'object') str += '[' + i + '] = [' + val[i] + ']';
				if(typeof val[i] == 'object') str += '[' + i + '] => (' + scan(val[i]) + ')<br>';
			}
			return str;
		}
	}
	
	function rtRowsManager(e){
		
		var e = e || window.event;
		
		
		var action = e.target.getAttribute('action');
		
		var control_num = getControlNum();

		var id_nums_str = (getIdsOfCheckedRows()).join(';');
		if(!id_nums_str) return;
		
		if(action == 'delete'){ 
		    confirm_win(e.target/* element near which window will located */,e.target/* button launching window */,'right','Вы уверены?'/*srt to show */,make_action);
		    return;
		}
		else make_action(); 
		
		
		
		
		function make_action(){
			 show_processing_timer();
			 location .search = '?' + addOrReplaceGetOnURL('make_rows_changes_in_rt=true&control_num='+control_num+'&action='+action+'&id_nums_str='+id_nums_str);
		}
		
	   
	}
	
	function copy_order(element,id){
		 confirm_win(element/* element near which window will located */,element/* button launching window */,'top'/*srt = top or bottom or left or right or empty */,'скопировать заказ и вставить в начало таблицы?'/*srt to show */,callback);
		 
		 function callback(){
			 //alert(1);
			 show_processing_timer();
			 var common_data = getCalculateTblCommonData();
		     location.search = '?' + addOrReplaceGetOnURL('add_copied_order_to_rt=true&order_row_id='+id+'&control_num='+getCalculateTblCommonData().control_num);
		 }
	}
	
	function add_print_row(element,id){
		show_processing_timer();
		location.search = '?' + addOrReplaceGetOnURL('add_print_row=true&id='+id+'&control_num='+getCalculateTblCommonData().control_num);
			
	}
	
	function onClickMasterBtn(element,target_id,row_id){
		
		if(onClickMasterBtn.onProcessing){
			// возвращаем те значения которые в данный момент в обработке
			element.checked = !onClickMasterBtn.status;
		    return !onClickMasterBtn.status;
		}
		onClickMasterBtn.status = !!element.checked;
		onClickMasterBtn.onProcessing = true;
		

		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('set_masterBtn_status={"ids":"'+row_id+'","status":"'+Number(onClickMasterBtn.status)+'"}');
		make_ajax_request(url,callback);
		
		function callback(response){
			console.log(onClickMasterBtn.status);
			// устанавливаем значение чекбокса именно здесь потому что нам надо чтобы оно установилось после срабатывания ajax запроса
			// срабатывание установки назначения checked по умолчанию - отключено 
			element.checked = onClickMasterBtn.status;
			
			var data = (getCountOfCheckedAndAllCeckboxes(target_id)).split('|');
		    var class_name = 'reset_button';
		    if(data[0] == data[1]) class_name = 'reset_button on';
		    if(data[0] == 0) class_name = 'reset_button none';
		    document.getElementById("reset_master_button").className = class_name;/**/
			
			onClickMasterBtn.onProcessing = false;
			
		}

		// возвращаем значение обратное тому что отдает чекбокс при клике, тем самым устанавливая ему значение которое было
		// до клика (оставляем его не изменным) потому что нам надо чтобы чекбокс не менялся пока не пройдет ajax запрос
		element.checked = !onClickMasterBtn.status;
		return !onClickMasterBtn.status;
	}
	
	function onClickMasterBtnOld(element,id){
		if(onClickMasterBtn.onProcessing) return;
		onClickMasterBtn.onProcessing = true;
		
		masterBtnVizibility(element,'masterBtnContainer'+id);
		
		var url = '?page=clients&set_status_master_btn='+Number(element.checked)+'&id='+id+'&control_num='+getControlNum();
		make_ajax_request(url,call_back);
		function call_back(response){
			//alert(response);
			element.checked = !(!!element.checked);
			var data = (getCountOfCheckedAndAllCeckboxes()).split('|');
		    var class_name = 'reset_button';
		    if(data[0] == data[1]) class_name = 'reset_button on';
		    if(data[0] == 0) class_name = 'reset_button none';
		    document.getElementById("reset_master_button").className = class_name;
			
			onClickMasterBtn.onProcessing = false;
		}
	}
	
	function resetMasterBtn(element,target_id){
        if(resetMasterBtn.onProcessing) return;
		resetMasterBtn.onProcessing = true;
		var data = (getCountOfCheckedAndAllCeckboxes(target_id)).split('|');
		console.log(data);
		var status = false;
		if(data[0]==0){
			var idsSting = getIdsOfAllRows(target_id);
			status = true;
		}
		else if(data[0]==data[1]) var idsSting = getIdsOfAllRows(target_id);
		else var idsSting = (getIdsOfCheckedRows(target_id)).join(';');


		
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('set_masterBtn_status={"ids":"'+idsSting+'","status":"'+Number(status)+'"}');
		make_ajax_request(url,callback);

		function callback(response){
			console.log(response);
			var calculate_tbl = document.getElementById(target_id);
			var inputs = calculate_tbl.getElementsByTagName('input');
			for( var i= 0 ; i < inputs.length; i++){
			   if(inputs[i].type == 'checkbox'){
				   if(inputs[i].name == 'masterBtn'){
					   inputs[i].checked = status;
				   }
			   }
			}
			resetMasterBtn.onProcessing = false;
		}
	}
	function resetMasterBtnOld(element,target_id){
        if(resetMasterBtn.onProcessing) return;
		resetMasterBtn.onProcessing = true;
		var data = (getCountOfCheckedAndAllCeckboxes(target_id)).split('|');
		console.log(data);
		var status = false;
		var class_name1 = 'container';
		var class_name2 = 'reset_button none';
		if(data[0]==0){
			var idsSting = getIdsOfAllRows(target_id);
			status = true;
			class_name1 = '';
		    class_name2 = 'reset_button on';
		}
		else if(data[0]==data[1]) var idsSting = getIdsOfAllRows(target_id);
		else var idsSting = (getIdsOfCheckedRows()).join(';');



		var url = '?page=clients&set_status_master_btn='+Number(status)+'&id='+idsSting+'&control_num='+getControlNum();
		make_ajax_request(url,call_back);
		
		function call_back(response){
			
			var calculate_tbl = document.getElementById("calculate_tbl");
			var inputs = calculate_tbl.getElementsByTagName('input');
			for( var i= 0 ; i < inputs.length; i++){
			   if(inputs[i].type == 'checkbox'){
				   if(inputs[i].name == 'masterBtn'){
					   inputs[i].checked = status;
					   inputs[i].parentNode.className = class_name1;
				   }
			   }
			}
			element.className = class_name2;
			resetMasterBtn.onProcessing = false;
		}
	}
	function getIdsOfCheckedRows(element_id){
		var element = document.getElementById(element_id);
		var inputs = element.getElementsByTagName('input');
		var idsArr = [];
		for( var i= 0 ; i < inputs.length; i++){
		   if(inputs[i].type == 'checkbox'){
			   if(inputs[i].name == 'masterBtn'){
				   if(inputs[i].checked == true && inputs[i].getAttribute('rowIdNum') && inputs[i].getAttribute('rowIdNum') !='') idsArr.push(inputs[i].getAttribute('rowIdNum'));
			   }
		   }
		}
		//console.log(idsArr.join(';'));
		return idsArr;
		
	}
	
	function getIdsOfAllRows(target_id){
		var calculate_tbl = document.getElementById(target_id);
		var inputs = calculate_tbl.getElementsByTagName('input');
		var idsArr = [];
		for( var i= 0 ; i < inputs.length; i++){
		   if(inputs[i].type == 'checkbox'){
			   if(inputs[i].name == 'masterBtn' && inputs[i].getAttribute('rowIdNum') && inputs[i].getAttribute('rowIdNum') !=''){                    idsArr.push(inputs[i].getAttribute('rowIdNum'));
			   }
		   }
		}
		return idsArr.join(';');
		
	}
	
	function getCountOfCheckedAndAllCeckboxes(element_id){
		var calculate_tbl = document.getElementById(element_id);
		var inputs = calculate_tbl.getElementsByTagName('input');
		var all_checkboxes = 0;
		var checked_checkboxes = 0;
		for( var i= 0 ; i < inputs.length; i++){
		   if(inputs[i].type == 'checkbox'){
			   if(inputs[i].name == 'masterBtn'){
				   all_checkboxes++;
				   if(inputs[i].checked == true) checked_checkboxes++;
			   }
		   }
		}
		return checked_checkboxes+'|'+all_checkboxes;
		
	}
	
	function getValuesOfCheckedRows(container_id,name){
		var container = (!container_id)? document : document.getElementById(container_id);
		var inputs = container.getElementsByTagName('input');
		var valuesArr = [];
		for( var i= 0 ; i < inputs.length; i++){
		   if(inputs[i].type == 'checkbox'){
			   if(name){
				   if(inputs[i].name == name &&  inputs[i].checked == true) valuesArr.push(inputs[i].value);
			   }
			   else{
				   if(inputs[i].checked == true) valuesArr.push(inputs[i].value);
			   }
		   }
		}
		return valuesArr;
		
	}
	
	function getFirstRelatedOrderNumAndManagerName(str){
		var str_in_arr = str.split(";");
		var required_row = null;
		
		var calculate_tbl = document.getElementById("calculate_tbl");
		var rows = calculate_tbl.getElementsByTagName('tr');
		for( var i= 0 ; i < rows.length; i++){
			if(rows[i].getAttribute && rows[i].getAttribute('id') == 'row_'+str_in_arr[0]) required_row = rows[i];
		}
		
		if(!required_row) return {'order_num':false,'client_manager':false};
		
		var order_num = false;
		var client_manager_id = false;
		for( var n = required_row ; n != null ; n = n.previousSibling){
			if(n.getAttribute && n.getAttribute('type') == 'order_row'){
				order_num = n.getAttribute('order_num');
				client_manager_id = n.getAttribute('client_manager_id');
				break;
			}
		}
		return {'order_num':order_num,'client_manager_id':client_manager_id};
	}
	function getControlNum(){
		var calculate_tbl = document.getElementById("calculate_tbl");
		return calculate_tbl.getAttribute('control_num');
	}
	function getCalculateTblCommonData(){
		if(!getCalculateTblCommonData.data){
		    var calculate_tbl = document.getElementById("calculate_tbl");
		    getCalculateTblCommonData.data = {'control_num':calculate_tbl.getAttribute('control_num'),
			                                    'client_id':calculate_tbl.getAttribute('client_id')
			                                   /*'manager_id':calculate_tbl.getAttribute('manager_id')*/
			                                  }
		}
		return getCalculateTblCommonData.data;
	}
	
	function setRtTypeView(e,rows){
		
		var e = e || window.event;
		
		
		var calculate_tbl = document.getElementById("calculate_tbl");
		var height = e.target.getAttribute('height');
		var max_size = (e.target.getAttribute('max_size') && e.target.getAttribute('max_size') == 'true')? true : false ;
		
		var divs = calculate_tbl.getElementsByTagName('div');
	   //setRtTypeViewTwo(e,show)
		
		for( var i= 0 ; i < divs.length; i++){
		   if(divs[i].getAttribute('bd_field') && divs[i].getAttribute('bd_field') == 'name'){
			   if(max_size){
				   divs[i].style.overflow = 'visible';
				   divs[i].style.height = 'auto';
				   divs[i].style.minHeight = height + 'px';
			   }
			   else{
				   divs[i].style.overflow = 'hidden';
				   divs[i].style.height = height + 'px';
				   divs[i].style.minHeight = height + 'px';
			   }
			   
		   }
		}
	}
	
	function setRtTypeViewTwo(e,show){
		
		var e = e || window.event;
		
		
		var calculate_tbl = document.getElementById("calculate_tbl");
		var show  = show  || e.target.getAttribute('show');
		
		
		var divs = calculate_tbl.getElementsByTagName('div');
	    var tables = calculate_tbl.getElementsByTagName('table');
		
		if(show == 'all'){
			setVeiw(divs,'name','block');
			setVeiw(divs,'name_tail','block');
			setVeiw(tables,'extra_panel','block');
			setVeiw(divs,'extra_panel_tail','block');    
		}
		else if(show == 'frame'){
			setVeiw(divs,'name','none');
			setVeiw(divs,'name_tail','none');
			setVeiw(divs,'extra_panel_tail','block');
			setVeiw(tables,'extra_panel','block');
		}
		else if(show == 'text'){
			setVeiw(divs,'name','block');
			setVeiw(divs,'name_tail','block');;
			setVeiw(divs,'extra_panel_tail','none');
			setVeiw(tables,'extra_panel','none');
			
		}
		
		
		function  setVeiw(elements,type,display){
			for( var i= 0 ; i < elements.length; i++){
				if(elements[i].getAttribute('type') && elements[i].getAttribute('type') == type){
					elements[i].style.display = display;
				}
			}
		}
		
		/*for( var i= 0 ; i < divs.length; i++){
		   if(divs[i].getAttribute('bd_field') && divs[i].getAttribute('bd_field') == 'name'){
			   if(max_size){
				   divs[i].style.overflow = 'visible';
				   divs[i].style.height = 'auto';
				   divs[i].style.minHeight = height + 'px';
			   }
			   else{
				   divs[i].style.overflow = 'hidden';
				   divs[i].style.height = height + 'px';
				   divs[i].style.minHeight = height + 'px';
			   }
			   
		   }
		}*/
	}
	//window.addEventListener('load',show_processing_timer,false);
	window.addEventListener('load',function(){var img = new Image(); img.src ='http://'+ location.host+location.pathname+'skins/images/img_design/preloader.gif';},false);
	function show_processing_timer(){
		var viewportHeight = Geometry.getViewportHeight();
		var viewportWidth = Geometry.getViewportWidth();
		
		show_processing_timer.container = document.createElement('div');
		show_processing_timer.container.style.position = 'absolute';
		show_processing_timer.container.style.top = '0px';
		show_processing_timer.container.style.left = '0px';
		//div.style.height = Geometry.getDocumentHeight() + 'px';
		//div.style.width = Geometry.getDocumentWidth() + 'px';
		show_processing_timer.container.style.height = viewportHeight + 'px';
		show_processing_timer.container.style.width = viewportWidth + 'px';
        //div.style.backgroundColor = '#FCFCFC';
		//div.style.filter = "alpha(opacity=20)";
		//div.style.opacity = "0.20";
        
		
		var timer_container = document.createElement('div');
		timer_container.style.height = '66px';
		timer_container.style.width = '66px';
		timer_container.style.marginTop = (viewportHeight-66)/2+'px';
		timer_container.style.marginLeft = (viewportWidth-66)/2+'px';
		//timer_container.style.border = '#888888 solid 1px';
		//alert('http://'+ location.host+location.pathname+'skins/images/img_design/preloader.gif');
		var img = new Image();
		img.src ='http://'+ location.host+location.pathname+'skins/images/img_design/preloader.gif';

		timer_container.appendChild(img);
		show_processing_timer.container.appendChild(timer_container);
		document.body.appendChild(show_processing_timer.container);
	}
	function close_processing_timer(){
		show_processing_timer.container.parentNode.removeChild(show_processing_timer.container);
	}
	
	function do_search(element,params_to_clear){
		var params_to_clear = params_to_clear || '';
		var query_field = document.getElementById('search_query');
		var query = query_field.value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		if(!query){
			alert_win(query_field,element,'top','поле поиска не заполнено');
			return;
		}
		location.search  = '?'+addOrReplaceGetOnURL('search='+query,params_to_clear);
	}
	
	function rating_filter(element,rating){
		if(rating == 0){
			location.search  = '?'+addOrReplaceGetOnURL('','filter_by_rating');
			return false;
		}

		var storage  = document.getElementById('ratings_val_storage').value;
		if(storage == ''){
			location.search  = '?'+addOrReplaceGetOnURL('filter_by_rating='+rating,'');
		}
		else{
			var storage_arr = (storage.indexOf(',') != -1)? storage.split(',') : [storage] ;
			var new_storage_arr = [];
			var exist = false;
			for(var index in storage_arr){
				//alert(storage_arr[index]);
				if(parseInt(storage_arr[index]) == parseInt(rating)){
					exist = true;
					continue;
				}
				new_storage_arr.push(storage_arr[index]);
			}
			if(!exist) new_storage_arr.push(rating);
			if(new_storage_arr.length>0/* && new_storage_arr.length<5*/) location.search  = '?'+addOrReplaceGetOnURL('filter_by_rating='+new_storage_arr.join(','),'');
			else location.search  = '?'+addOrReplaceGetOnURL('','filter_by_rating');
		}
		return false;
	}
	
	window.addEventListener('click',
							      function(e){ 
									  if(alert_win.container){ 
										 if(e.target===alert_win.container || e.target===alert_win.launch_btn) e.stopPropogation();
										 delete_alert_win();
									  } 	
									  if(confirm_win.container){ 
										 if(e.target===confirm_win.container || e.target===confirm_win.launch_btn) e.stopPropogation();
										 delete_confirm_win();
									  } 
								  },false);
	
	function alert_win(element/* element near which window will located */,launch_btn/* button launching window */,position/*srt = top or bottom or left or right or empty */,msg/*srt to show */){
	    if(alert_win.container)  delete_alert_win(); 
		
        var pos = define_pos(element);
		var container = document.createElement('div');
		container.className = 'alert_window';
		//alert(pos[0] +' '+pos[1] );
		container.innerHTML = msg;
		document.body.appendChild(container);
		alert_win.container = container;
		alert_win.launch_btn = launch_btn;

		
		var offset = {};
		offset.top = ((pos[0]-container.offsetHeight)-Geometry.getVerticalScroll() >= 0)?pos[0] - container.offsetHeight:pos[0] + element.offsetHeight;
		offset.bottom = ((pos[0]+element.offsetHeight+container.offsetHeight)-Geometry.getVerticalScroll() >= window.innerHeight)?pos[0] - container.offsetHeight:pos[0] + element.offsetHeight;
		offset.left = (pos[1] - container.offsetWidth - Geometry.getHorizontalScroll() >= 0)?pos[1] - container.offsetWidth:pos[1] + element.offsetWidth ;
		offset.right = ((pos[1] + element.offsetWidth+container.offsetWidth)-Geometry.getHorizontalScroll() >= window.innerWidth)?pos[1] - container.offsetWidth:pos[1] + element.offsetWidth;
		
		
		var top = (position=='top'||position=='bottom')?offset[position]:pos[0];
		var left = (position=='left'||position=='right')?offset[position]:pos[1];
		container.style.top = top + 'px';
		container.style.left = left + 'px';//+ element.offsetWidth
		
		function define_pos(element){
			var e = element;
			var pos = [0,0];
			while(e){
				pos[0]+= e.offsetTop;
				pos[1]+= e.offsetLeft;
				e = e.offsetParent;
			}
			// прокручиваемые области
			for(e = element.parentNode; e && e != document.body; e = e.parentNode) if(e.scrollTop) pos[0] -= e.scrollTop;
			
			return pos;
		}
		
	}
	function delete_alert_win(){
	    if(alert_win.container) alert_win.container.parentNode.removeChild(alert_win.container);
		alert_win.container = null;
		alert_win.launch_btn = null;
	}
	
	function confirm_win(element/* element near which window will located */,launch_btn/* button launching window */,position/*srt = top or bottom or left or right or empty */,msg/*srt to show */,callback){
	    if(confirm_win.container)  delete_confirm_win(); 
		
		var return_val = false;
		
        var pos = define_pos(element);
		var container = document.createElement('div');
		container.className = 'alert_window noselect';
		//alert(pos[0] +' '+pos[1] );
		var div = document.createElement('div');
		div.className = 'text_div';
		div.innerHTML = msg;
		container.appendChild(div);
		
		
		var ok = document.createElement('div');
		ok.className = 'alert_window_btn';
		ok.innerHTML = 'да';
		ok.onclick = function(){ delete_confirm_win(); callback();}
		
		var cancel = document.createElement('div');
		cancel.className = 'alert_window_btn';
		cancel.innerHTML = 'нет';
		cancel.onclick = function(){ delete_confirm_win();}
		
		var btns_div = document.createElement('div');
		btns_div.className = 'btns_div';
		btns_div.appendChild(ok);
		btns_div.appendChild(cancel);
		container.appendChild(btns_div);
		
		document.body.appendChild(container);
		confirm_win.container = container;
		confirm_win.launch_btn = launch_btn;

		
		var offset = {};
		offset.top = ((pos[0]-container.offsetHeight)-Geometry.getVerticalScroll() >= 0)?pos[0] - container.offsetHeight:pos[0] + element.offsetHeight;
		offset.bottom = ((pos[0]+element.offsetHeight+container.offsetHeight)-Geometry.getVerticalScroll() >= window.innerHeight)?pos[0] - container.offsetHeight:pos[0] + element.offsetHeight;
		offset.left = (pos[1] - container.offsetWidth - Geometry.getHorizontalScroll() >= 0)?pos[1] - container.offsetWidth:pos[1] + element.offsetWidth ;
		offset.right = ((pos[1] + element.offsetWidth+container.offsetWidth)-Geometry.getHorizontalScroll() >= window.innerWidth)?pos[1] - container.offsetWidth:pos[1] + element.offsetWidth;
		
		
		var top = (position=='top'||position=='bottom')?offset[position]:pos[0];
		var left = (position=='left'||position=='right')?offset[position]:pos[1];
		container.style.top = top + 'px';
		container.style.left = left + 'px';//+ element.offsetWidth
		
		return return_val;
		
		function define_pos(element){
			var e = element;
			var pos = [0,0];
			while(e){
				pos[0]+= e.offsetTop;
				pos[1]+= e.offsetLeft;
				e = e.offsetParent;
			}
			// прокручиваемые области
			for(e = element.parentNode; e && e != document.body; e = e.parentNode) if(e.scrollTop) pos[0] -= e.scrollTop;
			
			return pos;
		}
	}
	
	function delete_confirm_win(){
	    if(confirm_win.container) confirm_win.container.parentNode.removeChild(confirm_win.container);
		confirm_win.container = null;
		confirm_win.launch_btn = null;
	}
	
	function clear_search_input(){
	    delete_alert_win();
		document.getElementById('search_query').value ='';
		if((location.search).indexOf('search') != -1) location.search  = '?'+addOrReplaceGetOnURL('','search');
		return false;
	}
	
    var dropDownManagerList = {
	    selected_in_previous:[],
		generate: function(node){
			if(dropDownManagerList.applied){
				this.close_list();
				return false;
			}
			//alert(1);
			var url = '?page=clients&section=clients_list&generate_manager_list';
			make_ajax_request(url,call_back);
			function call_back(response){
				//alert(response);
				if(response){
					
					var arr = response.split('[&]');

					if(location.search.indexOf('filter_by_range=') != -1){
						var pattern = new RegExp(/filter_by_range=([^&]+)/);
						var str = pattern.exec(location.search)[1];
						//alert(str);
						var selected_arr = str.split(',');
						for(var index in selected_arr) dropDownManagerList.selected_in_previous[selected_arr[index]] = true; 
						//alert();
					}
					
					var box = document.createElement('div');
					box.className = 'manager_list_container noselect';
					box.innerHTML = '<div class="list_row"><div class="name" onclick="dropDownManagerList.checked_all(2);">Все</div><div class="checkbox"><input type="checkbox" value="all" onclick="dropDownManagerList.checked_all(1);"></div><div class="clear_div"></div></div>';
					for(var i in arr){
						box.appendChild(build_row(arr[i]));
					}
					box.innerHTML = box.innerHTML+'<div class="apply" onclick="dropDownManagerList.send_request();">применить</div>';
					box.innerHTML = box.innerHTML+'<div class="apply bottom" onclick="dropDownManagerList.send_request();">применить</div>';
					
					node.parentNode.style.position = 'relative';
					node.parentNode.appendChild(box);
					dropDownManagerList.applied = true;
					dropDownManagerList.box = box;
					
				}
				else{
					
				}
				
				function build_row(data){
					var vals = data.split('[,]');
					var div = document.createElement('div');
					div.className = dropDownManagerList.selected_in_previous[vals[0]]?'list_row checked':'list_row';
					var checked = dropDownManagerList.selected_in_previous[vals[0]]?'checked':'';
					
					div.innerHTML = '<div class="name" onclick="dropDownManagerList.check_if_all(this.parentNode.getElementsByTagName(\'input\')[0],0);"> ' + vals[1]  + '</div>';
					div.innerHTML +='<div class="checkbox"><input type="checkbox" onclick="dropDownManagerList.check_if_all(this,1);" value="'+ vals[0] + '" '+  checked + '></div><div class="clear_div"></div>';
					
					return div;
				}
				
			}
			return false;		
		}
		,
		send_request: function(){
			//alert(1);
			var inputs = this.box.getElementsByTagName('input');
			var out = [];
			for(var i in inputs){
				if(inputs[i].type == 'checkbox' && inputs[i].checked == true && inputs[i].value !='' ){
					out.push(inputs[i].value);
					if(inputs[0].checked == true) break;
				}
			}
			this.close_list();
			if (out[0] == 'all' || out.length == 0) location.search  = '?'+addOrReplaceGetOnURL('','filter_by_range');
			else location.search  = '?'+addOrReplaceGetOnURL('filter_by_range='+out.join(','),'');
		}
		,
		close_list: function(){
			this.box.parentNode.removeChild(this.box);
			this.applied = false;
					//dropDownManagerList.box = box;
		}
		,
		checked_all: function(type){
			// ставим или убираем галочки во всех рядах и применяем к рядам соответсвующий стиль
			var inputs = this.box.getElementsByTagName('input');
			var state = (type == 1)?inputs[0].checked:!inputs[0].checked;
			var className = (state)?'list_row checked':'list_row';
			for(var i in inputs){
				if(inputs[i].type == 'checkbox'){
					inputs[i].checked = state;
				    inputs[i].parentNode.parentNode.className = className;
				}
			}
		}
		,
		check_if_all: function(cur_checkbox,type){
			//alert(cur_checkbox.checked);
			var inputs = this.box.getElementsByTagName('input');
			var state = (type == 1)?cur_checkbox.checked:!cur_checkbox.checked;
			var className = (state)?'list_row checked':'list_row';
			
			
			// проверяем все ли ряды выбраны, и если да, ставим галочку в чекбоксе "Все" и соответствующий стиль
			if(state == false){
			    inputs[0].checked = false;
				inputs[0].parentNode.parentNode.className = 'list_row';
			}
			if(state == true){
				var num_checked_checkboxes = 0;
				for(var i in inputs) if(inputs[i].type == 'checkbox' && inputs[i].checked == true) num_checked_checkboxes++;
				//alert(num_checked_checkboxes +' '+ (inputs.length-1));
				if(num_checked_checkboxes == (inputs.length-1)){
					inputs[0].checked = true;
					inputs[0].parentNode.parentNode.className = 'list_row checked';
			    }
			}
			// ставим галочку и стиль для текущего checkbox-а
			cur_checkbox.parentNode.parentNode.className = className;
			cur_checkbox.checked = state;
		}
		
		
	}
	
	function get_checked_ids_and_make_request(element){
	    var valuesArr = getValuesOfCheckedRows('','masterBtn');
		if(valuesArr.length == 0){
			alert_win(element.parentNode,element,'top'/*srt = top or bottom or left or right or empty */,'не выбранно ни одной позиции'/*srt to show */);
		    return false;
		}
		
		location.search = '?'+addOrReplaceGetOnURL('section=suppliers_list&filter_by_profies='+valuesArr.join(','),'search');
	}
	
	
    function open_close_overflow_container(btn,container,inner_element,scroll_container,min_height){
		if(open_close_overflow_container.on) return;
		
		open_close_overflow_container.on = true;
		var container = document.getElementById(container);
		var inner_element = document.getElementById(inner_element);
		var scroll_container =  document.getElementById(scroll_container) || false;
		
		min_height = min_height || 0;
		var max_height = inner_element.offsetHeight || 700;
		
		var cur_height = container.offsetHeight;
		
		var step =(cur_height <= min_height)? 20 : -13 ;
		var inteval = setInterval(open_close,2);
		
		function open_close(){
			
			if(container.offsetHeight + step < min_height || container.offsetHeight + step > max_height){
				
				var rest = (step>0)? -(max_height - container.offsetHeight): container.offsetHeight - min_height ;
				container.style.height = (step>0)? max_height + 'px': min_height + 'px';
				btn.className = (step>0)? (btn.className).replace(/open$/,'close') : (btn.className).replace(/close$/,'open');
				clearInterval(inteval);
				open_close_overflow_container.on = false;
				if(scroll_container) scroll_container.style.height =  (scroll_container.offsetHeight + rest) + 'px';
			}
			else{
				container.style.height = container.offsetHeight + step + 'px';
				if(scroll_container) scroll_container.style.height = (scroll_container.offsetHeight - step) + 'px';	
			}
		}
	}
	
	function masterBtnVizibility(element,id){
		var container = document.getElementById(id).className =(element.checked == true)?  '':'container';
	}

	//standart function OS	
	function new_html_modal_window(html,head_text,buttons,form_name,id,tbl){
		if(typeof html == 'object') html = html.outerHTML;
		var html_buttons = '<span class="grey_bw cancel_bw">Отмена</span><span class="green_bw save_bw">Сохранить</span><span class="green_bw send_bw">Отправить</span><span class="green_bw ok_bw">OK</span><span class="green_bw greate_bw">Создать</span>';
		if($('#bg_modal_window').length>0){$('#bg_modal_window,.html_modal_window').remove();}
		$('body').append('<div id="bg_modal_window"></div><div class="html_modal_window"><form method="post"><div class="html_modal_window_head">'+ head_text +'<div class="html_modal_window_head_close">x</div></div><div class="html_modal_window_body">'+ html +'</div><div class="html_modal_window_buttons">'+ html_buttons +'</div></form></div>');
		if(typeof buttons !=="undefined" && buttons.replace(/\s+/g, '') != ""){
			//console.log("."+buttons);
			$("."+buttons+"_bw").css('display','block');
			//добавляем в форму инпут с названием кнопки, т.к. кнопки у нас span
			$(".html_modal_window form").append('<input type="hidden" name="button_name" value="'+ buttons +'" >');			
		}
		$(".html_modal_window form").append('<input type="hidden" name="ajax_standart_window" value="'+ form_name +'" >');
		if(id!="none"){$(".html_modal_window form").append('<input type="hidden" name="id" value="'+ id +'" >');}
		if(id!="none"){$(".html_modal_window form").append('<input type="hidden" name="tbl" value="'+ tbl +'" >');}
		var he = ($(window).height()/2);
		var margin = $('.html_modal_window').innerHeight()/2*(-1);
		$('.html_modal_window').css({'top':he,'margin-top':margin,'display':'block'}).draggable({ handle : ".html_modal_window_head"});	
		return true;
	}
	//закрытие на ESC
	$(document).keydown(function(e) {	
		if(e.keyCode == 27){
			$('#bg_modal_window,.html_modal_window').remove();
		}
	});

	//закрытие стандартного окна на "крестик" и "отмена"
	$(document).on('click', '.html_modal_window_head_close,.cancel_bw', function(event) {
		$('#bg_modal_window,.html_modal_window').remove();
	});
	/*
	//закрытие стандартного окна при ответе об успешном выполнении запроса 
	//этот скрипт реализует отправку ajax запроса на текущую страницу при клике на любую зелёную кнопку форме
	$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
	    var str = $('.html_modal_window form').serialize();
	    $.post('', str, function(data, textStatus, xhr) {
	        if(data=="OK"){
	            $('#bg_modal_window,.html_modal_window').remove();
	        }
	    });
	});
	*/

	//статус сохранения отредактированного поля
	function check_loading_ajax(){
		// window.l++;
		// console.log(jQuery.active);
		// if(jQuery.active>0){
		// 	if($('#alert_saving_status').length==0){
		// 		$('body').append('<div style="position:fixed; float:left;font-family: arial,sans-serif; left:50%; top:100px; margin-left:-100px; background-color:#F9EDBE;border:1px solid #F0C36D; padding:7px 15px; font-size:12px" id="alert_saving_status"><div id="ll">Данные сохраняются...</div><div id="lll" style="text-align:center"></div><div id="lll1"><div id="lll2" style="width:0%;background: #F0C36D; height:5px; border:0"></div></div></div>');	
		// 		$('#alert_saving_status').stop(true, true).fadeIn('fast');
		// 	}else{
		// 		$('#alert_saving_status').fadeIn('fast');			
		// 	}
		// 	var p = jQuery.active;
		// 	var q = window.l / 100;
		// 	var per = Math.ceil((100-p/q));
		// 	$('#lll').html(per +' %');
		// 	$('#lll2').width(per+'%');
		// 	setTimeout(check_loading_ajax, 300);
		// 	return false;
		// }else{
			
		// 	// $('#ll').html('Данные успешно сохранены.')
		// 	$('#ll').html('Запрос успешно завершен.');
		// 	$('#lll').html('100 %');
		// 	$('#lll2').width('100%');		
		// 	$('#alert_saving_status').delay(1000).animate({opacity:0},700,function(){$(this).remove()});
			
		// 	//setTimeout($('#alert_saving_status').fadeOut('fast').remove(), 3000)	
		// 	window.l = 0;
		// 	return true;	
		// }
	};
	// $(document).ready(function(){
	// window.l = 0;
	// window.onbeforeunload = function () {return ((check_loading_ajax()==false) ? "Измененные данные не сохранены. Закрыть страницу?" : null);}
	// });
