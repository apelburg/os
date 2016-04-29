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
	noticeQueryBlocked.counter = 0;
	function noticeQueryBlocked(){
		noticeQueryBlocked.counter++;
		if(noticeQueryBlocked.counter>4){
			noticeQueryBlocked.counter = 0;
			return;
		}
		if(noticeQueryBlocked.counter>1) return;
	
		$('#noticeQueryBlocked').remove();
		
		var text = 'Заявку можно редактировать (изменять), только когда она имеет статус "в работе" (находится в разделе "в работе").<br><br>На данный момент вы можете поменять статус заявки в общем списке заявок.<br><br>';
		var div = $('<div>'+text+'</div>');
		div.id = 'noticeQueryBlocked';
		$(div).dialog({width:500});
		$(div).dialog('open');
		if(!noticeQueryBlocked.counter) noticeQueryBlocked.counter = 0;
		noticeQueryBlocked.counter++;
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
	
	var SELEBRATIONS = { "2015": '1.1|2.1|3.1|4.1|5.1|6.1|7.1|8.1|9.1|23.2|9.3|1.5|4.5|9.5|11.5|12.6|4.11',
	                     "2016": '1.1|2.1|3.1|4.1|5.1|6.1|7.1|8.1|9.1|10.1|22.2|23.2|7.3|8.3|1.5|2.5|3.5|9.5|13.6|4.11'};
	
	function goOnNumWorkingDays(begin/*агрумент должен быть в формате "0000-00-00 00:00:00"*/,days_num/*число рабочих дней*/,direct/*+/-(вперед или назад)*/){
		// функция принимает указанную дату и возвращает новую дату наступающую через указанное количество рабочих дней
		// пример вызова goOnNumWorkingDays("2015-01-07 02:01:01",11,'+');
		var error = [];
		var secondsInDay = 1000*60*60*24;
		var date = new Date(begin);
		var out_date = date.getTime();
	
		 while(days_num>0){
			 
			if(direct=='-') out_date-=secondsInDay;
            if(direct=='+') out_date+=secondsInDay;
			
			var subDate = new Date(out_date);

		    dayInWeek = subDate.getDay();
			console.log(subDate.toString());
		    // если суббота или воскресенье
		    if(dayInWeek == 6 || dayInWeek == 0) continue;
			console.log(dayInWeek);

		    var year = subDate.getFullYear();
			var dayMonth = subDate.getDate()+'.'+(subDate.getMonth()+1);
		    // если праздничный день
			if(!SELEBRATIONS[year])  error.push('не установлен календарь праздничных дней на '+year+' год.');
		    if(SELEBRATIONS[year] && SELEBRATIONS[year].indexOf(dayMonth)>=0) continue;

		    days_num--;
	    }
		if(error.length>0) alert(error.join("\r"));

		var date = new Date(out_date);

		//alert(getTimeStamp(date));
	    return  getTimeStamp(date);
	}

	function getTimeStamp(date) {
   
         return (  date.getFullYear() + '-'
			  + (((date.getMonth()+1) < 10)? ("0" + (date.getMonth()+1)) : ((date.getMonth()+1))) + '-'
			  + ((date.getDate() < 10)? ("0" + date.getDate()) : (date.getDate())) + ' '
			  + ((date.getHours() < 10)? ("0" + date.getHours()) : (date.getHours())) + ':'
			  + ((date.getMinutes() < 10)? ("0" + date.getMinutes()) : (date.getMinutes())) + ':'
			  + ((date.getSeconds() < 10)? ("0" + date.getSeconds()): (date.getSeconds())));
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
	
	function drop_radio_buttons(element,attr,attr_value){ 
	   var inputs_arr = document.getElementsByTagName('input');
	   for(var i=0;i<inputs_arr.length;i++){
		   if(inputs_arr[i].type == 'radio'){
			   if(attr)
			   {
				 if(inputs_arr[i].getAttribute(attr) && inputs_arr[i].getAttribute(attr)==attr_value)  inputs_arr[i].checked=false; 
			   }
			   else inputs_arr[i].checked=false;
		   }
	   }
	   element.checked=true;
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
			// console.log(onClickMasterBtn.status);
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
		console.log('resetMasterBtn',data);
		var status = false;
		if(data[0]==0){
			var idsSting = getIdsOfAllRows(target_id);
			status = true;
		}
		else if(data[0]==data[1]) var idsSting = getIdsOfAllRows(target_id);
		else var idsSting = (getIdsOfCheckedRows(target_id)).join(';');
        
		console.log('idsSting',idsSting);

		
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('set_masterBtn_status={"ids":"'+idsSting+'","status":"'+Number(status)+'"}');
		
		console.log('url',url);
		
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
					div.innerHTML +='<div class="checkbox"><input type="checkbox" style="display: block;" onclick="dropDownManagerList.check_if_all(this,1);" value="'+ vals[0] + '" '+  checked + '></div><div class="clear_div"></div>';
					
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
	
	function validate_fieds(element){
	  //var agreement_exists = document.getElementById('agreement_exists').checked;
	  //if(!agreement_exists) return;
	  var validate_error = [];
	  if(document.getElementById('existent_agreement_num'))  var existent_agreement_num = document.getElementById('existent_agreement_num').value;
	 if(document.getElementById('existent_client_agreement_num')) var existent_client_agreement_num = document.getElementById('existent_client_agreement_num').value;
	  if(document.getElementById('existent_agreement_date')) var existent_agreement_date = document.getElementById('existent_agreement_date').value;
	  if(document.getElementById('existent_agreement_expire_date')) var existent_agreement_expire_date = document.getElementById('existent_agreement_expire_date').value;
	  
	  if(typeof element.form.requisit_id === 'undefined') validate_error.push('нет реквизитов клиента');
	  if(typeof element.form.our_firm_id === 'undefined') validate_error.push('нет реквизитов нашей фирмы');
	  
	  if(existent_agreement_num){
		  var pattern = /^[\d]{1,3}\/[\d]{4}$/;
		  if(!existent_agreement_num.match(pattern)) validate_error.push('номер существующего (неклиентского) договора должен быть в формате 000/0000, и содержать только цифры');
		  if(parseInt(existent_agreement_num.slice(0,existent_agreement_num.indexOf('/')))>104) validate_error.push('цифра в номере существующего (неклиентского), перед знаком / не может превышать 104');
		  if(parseInt(existent_agreement_num.slice(0,existent_agreement_num.indexOf('/')))==0) validate_error.push('цифра в номере существующего (неклиентского), перед знаком / не может быть равна 0');
	  }
	  if(existent_client_agreement_num){
		  var pattern = /^[\d\w\/\-]{3,9}$/;
		  if(!existent_client_agreement_num.match(pattern)) validate_error.push('номер клиентского договора может должен быть длинной от 3 до 9 символов, содержать только цифры ,/ ,- и символы латиниского алфавита');
	  }
	  var pattern = /^\d{2}.\d{2}.\d{4}$/;
	  if(existent_agreement_date && !existent_agreement_date.match(pattern))  validate_error.push('дата подписания не соответствуюет формату 00.00.0000');
	  if(existent_agreement_expire_date && !existent_agreement_expire_date.match(pattern))  validate_error.push('дата истечения не соответствуюет формату 00.00.0000');

	  if(validate_error.length > 0){
		  alert(validate_error.join("\r\n"));
		  return false;
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
	
	function agreement_section(element){
	   var id = element.options[element.selectedIndex].value; 
	   var tbl = document.getElementById('agreement_list_tbl');
	   var arr = tbl.getElementsByTagName('tr');
	   //alert(tr_list.length);
	   for( var i=0 ; i < arr.length ; i++){
		   if(arr[i].getAttribute('agreement_id')){
			   if(arr[i].getAttribute('agreement_id') == id ){
				   arr[i].hidden = false;
				   var agreement_num = arr[i].getElementsByTagName('agreement_num');
				   //if(agreement_num && agreement_num.length >0) document.getElementById('cup_agreement').innerHTML = agreement_num[1].innerHTML + 'Договор: ДС №'+agreement_num[0].innerHTML + ' от ' + agreement_num[2].innerHTML;
				   
			   }
			   else arr[i].hidden = true;
		   }
	    }
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
		if($('#bg_modal_window').length>0){
			$('#bg_modal_window,.html_modal_window').remove();
		}


		$('body').append('<div id="bg_modal_window"></div><div class="html_modal_window"><form method="post"><div class="html_modal_window_head">'+ head_text +'<div class="html_modal_window_head_close">x</div></div><div class="html_modal_window_body">'+ html +'</div><div class="html_modal_window_buttons">'+ html_buttons +'</div></form></div>');
		if(typeof buttons !=="undefined" && buttons.replace(/\s+/g, '') != ""){
			//console.log("."+buttons);
			$("."+buttons+"_bw").css('display','block');
			//добавляем в форму инпут с названием кнопки, т.к. кнопки у нас span
			$(".html_modal_window form").append('<input type="hidden" name="button_name" value="'+ buttons +'" >');			
		}
		$(".html_modal_window form").append('<input type="hidden" name="AJAX" value="'+ form_name +'" >');
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
	
	function show_client_list_by_manager_for_planner(manager_id){
		
		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
		var url = '?page=planner&manager_id=' + manager_id + '&show_client_list_by_manager=1';
		
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
		
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				     ///////////////////////////////////////////
			         

					 var request_response = request.responseText;
					 //alert(request_response);
					 var common_arr = request_response.split('{@#@#@}');
					 var items_arr = common_arr[0].split('{@}');
					 var items2_arr = common_arr[1].split('{@}');
					 
					 
					 up_window_consructor.setWindowDimentions(500,900);
					 var arr = up_window_consructor.windowBilder('JFEIWERF8e0r8qFHDKI94u9r8');
					 
					 // строим таблицу
					 var cols_num = 3;
					 var rows_num = (items_arr.length%cols_num != 0 )? parseInt(items_arr.length/cols_num)+ 1 : parseInt(items_arr.length/cols_num);
					 var table_for_items = document.createElement("table");	
					 table_for_items.style.width ='100%';
					 table_for_items.style.height ='100%';
					 table_for_items.style.borderCollapse ='collapse';
					 table_for_items.style.border ='#000000 solid 0px';
					 	
					 
					 //alert(items_arr.length + ' _ ' + rows_num);
					 var counter = 0;
					 for(var i = 0 ; i < rows_num ; i++){	 
						  /*

						   */
						   var tr_for_items = document.createElement("tr");
						   for(var j = 0 ; j < cols_num ; j++){
							   
							    var a = document.createElement("a");
							   // вставляем полученный пукт в ссылку
							   var index = i + rows_num*j;
							   
							   a.innerHTML = (items2_arr[index])? items2_arr[index]:'';
							   a.setAttribute('value',items_arr[index])
							   a.style.cursor = 'pointer';
							   a.style.margin = '0px 5px 0px 5px';
							   a.onclick = function(){
								   show_planner_window(this.getAttribute('value'));
								   arr[0].parentNode.removeChild(arr[0]);
								   arr[1].parentNode.removeChild(arr[1]);
							   }
								   
							   
							   
							   
							   var td_for_items = document.createElement("td");
					           if(j != cols_num-1){
								     td_for_items.style.borderRight ='#999999 solid 1px';
									 td_for_items.style.width = (100/cols_num) + '%';
							   }
							   td_for_items.style.borderBottom ='#AAAAAA dotted 1px';
					           
							   td_for_items.style.height ='22px';
							   td_for_items.appendChild(a);
						       tr_for_items.appendChild(td_for_items);
						 
						   }
						   
						   
						   table_for_items.appendChild(tr_for_items);
					 }
					 // последний пустой ряд
					 var tr_for_items = document.createElement("tr");
					 for(var j = 0 ; j < cols_num ; j++){		   
						   var td_for_items = document.createElement("td");
						   if(j != cols_num-1){ 
						       td_for_items.style.borderRight ='#999999 solid 1px';
						       td_for_items.style.width = (100/cols_num)+ '%';
						   }
						   tr_for_items.appendChild(td_for_items);		   
					 }
					 table_for_items.appendChild(tr_for_items);
					 
					 arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(table_for_items);
					 
					 arr[1].appendChild(arr[2]);
					 arr[1].className = 'planner_window';
					 document.body.appendChild(arr[0]);
					 document.body.appendChild(arr[1]);

					 
		           

			    }
			    else{
				    alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		    }
		}	
   }
   show_planner_window.in_process = false;
   function show_planner_window(client_id){
	    if(show_planner_window.in_process) return;
	    else show_planner_window.in_process = true;
		
	   if(document.getElementById("QEFEF9740WE")) document.getElementById("QEFEF9740WE").parentNode.removeChild(document.getElementById("QEFEF9740WE"));
	   
		
		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
	    var url = '?page=clients&client_id=' + client_id + '&subquery_for_planner_window=1';
		
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
		
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				     ///////////////////////////////////////////
			         

					 var request_response = request.responseText;
					 //alert(request_response);
					 var cont_faces_arr = request_response.split('{@}');
					 // создаем всплывающее окно
					 up_window_consructor.setWindowDimentions(330,800)
					 var arr = up_window_consructor.windowBilder('QEFEF9740WE');
					 
					 ///////////////////////////////////////////////////////
					 // содержимое сплывающего окна
					 ///////////////////////////////////////////////////////
					 
					 //элемент форма
					 var form = document.createElement("form");
					 form.method = "POST";
					 form.action = location.href;
					
					 // верхний div
                     var div1 = document.createElement("div");
					 div1.style.height ='140px';
					 div1.style.border ='#000000 solid 0px';
					 
					 var div_float_left = document.createElement("div"); // плавающий div контейнер
					 div_float_left.style.float ='left';
					 div_float_left.style.margin ='15px 0px 0px 5px';
					 //div_float_left.style.width ='200px';
					 //div_float_left.style.border ='#000000 solid 1px';
					 
					 // тип записи
					 var plan_types_arr = ['звонок','встреча','заявка'];
					 var select_element = document.createElement("select");
					 select_element.name ='form_data[plan_type]';
					 select_element.style.width = "140px";
					 select_element.style.border = "#CCCCCC solid 1px";
					 for( var i=0; i < plan_types_arr.length ;i++){
						 var option = document.createElement("option");
						 option.value = plan_types_arr[i];
						 option.innerHTML = plan_types_arr[i];
						 select_element.appendChild(option);
						 
					 }
					 var plantype_headline_div = document.createElement("div");
					 plantype_headline_div.innerHTML = '&nbsp;Тип задачи';
					 var plantype_select_div = document.createElement("div");
					 plantype_select_div.appendChild(select_element);
					 
					 div_float_left.appendChild(plantype_headline_div);
					 div_float_left.appendChild(plantype_select_div);
					
					 
					 // контактные лица
					 var select_element2 = document.createElement("select");
					 select_element2.name ='form_data[cont_face]';
					 select_element2.style.width = "140px";
					 select_element2.style.border = "#CCCCCC solid 1px";
					 for( var i=0; i < cont_faces_arr.length ;i++){
						 var option = document.createElement("option");
						 option.value = cont_faces_arr[i];
						 option.innerHTML = cont_faces_arr[i];
						 select_element2.appendChild(option);
						 
					 }
					 var contfaces_headline_div = document.createElement("div");
					 contfaces_headline_div.style.marginTop = "6px";
					 contfaces_headline_div.innerHTML = '&nbsp;Контактные лица';
					 var contfaces_select_div = document.createElement("div");
					 contfaces_select_div.appendChild(select_element2);
					 
					 div_float_left.appendChild(contfaces_headline_div);
					 div_float_left.appendChild(contfaces_select_div);
					 
					 
					  // календарь
					 var div_float_right2 = document.createElement("div"); // плавающий div контейнер
					 div_float_right2.style.float ='right';
					 div_float_right2.style.margin ='10px 0px 0px 0px';
	                 div_float_right2.style.width ='250px';
					 div_float_right2.style.border ='#000000 solid 0px';
					 
					 calendar_consturctor.setContextDay();
                     var calendar = calendar_consturctor.calendarTableBilder('calendar_table');
                     calendar[0].style.width = '180px';
                     calendar[0].appendChild(calendar[1]);
                     calendar[0].appendChild(calendar[2]);
					 calendar[0].appendChild(calendar[3]);
                     div_float_right2.appendChild(calendar[0]);
					 
					 // time table
					 var div_float_right3 = document.createElement("div"); // плавающий div контейнер
					 div_float_right3.style.float ='right';
					 div_float_right3.style.margin ='10px 0px 0px 0px';
	                 div_float_right3.style.width ='220px';
					 div_float_right3.style.border ='#000000 solid 0px';
					 				 
					 var time_table = calendar_consturctor.timeTable('time_table');
					 time_table[0].style.width = '180px';
					 time_table[0].style.margin ='0px 0px 0px 0px';
                     time_table[0].appendChild(time_table[1]);
                     time_table[0].appendChild(time_table[2]);
					 time_table[3].style.margin ='2px 0px 0px 0px';
					 div_float_right3.appendChild(time_table[3]);
                     div_float_right3.appendChild(time_table[0]);
					 
					 //кнопки ok? reset и отменить
					 var div_float_right1 = document.createElement("div"); // плавающий div контейнер
					 div_float_right1.style.float ='right';
					 div_float_right1.style.margin ='10px 0px 0px 0px';
	                 div_float_right1.style.width ='98px';
					 div_float_right1.style.border ='#000000 solid 0px';
					 
					 //var button_ok = document.createElement("button"); 
					 var button_ok = document.createElement("input"); 
					 button_ok.type = 'submit';
					 button_ok.name = 'set_plan';
					 //button_ok.innerHTML = 'ok';
					 button_ok.value = 'ok';
					 button_ok.style.height = '30px';
					 button_ok.style.width = '90px';
					 button_ok.style.backgroundColor = "rgb(122, 189, 121)";
					 button_ok.style.border = "#555 solid 1px";
					 button_ok.style.borderRadius ='2px';
					 button_ok.style.cursor = 'pointer';
					 
					 var button_ok_div = document.createElement("div");
					 button_ok_div.style.marginBottom = '15px';
					 button_ok_div.appendChild(button_ok);
					 
					 div_float_right1.appendChild(button_ok_div);
					 
					 
					// var button_reset = document.createElement("button"); 
					 var button_reset = document.createElement("input"); 
					 button_reset.type = 'reset';
					// button_reset.innerHTML = 'очистить';
					 button_reset.value = 'очистить';
					 button_reset.style.height = '30px';
					 button_reset.style.width = '90px';
					 button_reset.style.cursor = 'pointer';
					 
					 var button_reset_div = document.createElement("div");
					 button_reset_div.style.marginBottom = '15px';
					 button_reset_div.appendChild(button_reset);
					 
					 
					 
					 div_float_right1.appendChild(button_reset_div);
					 
					 
					 //var button_escape = document.createElement("button"); 
					 var button_escape = document.createElement("input"); 
					 button_escape.type = 'button';
					 //button_escape.innerHTML = 'отменить';
					 button_escape.value = 'отменить';
					 button_escape.style.height = '30px';
					 button_escape.style.width = '90px'
					 button_escape.style.cursor = 'pointer';
					 button_escape.onclick = up_window_consructor.closeWindow;
					 
					 var button_escape_div = document.createElement("div");
					 button_escape_div.appendChild(button_escape);
					 
					 div_float_right1.appendChild(button_escape_div);
					 
					 // объедняем(добавляем) элементы входящие в div1
					 div1.appendChild(div_float_left);
					 div1.appendChild(div_float_right1);
					 div1.appendChild(div_float_right2);
					 div1.appendChild(div_float_right3);
					 
					 
					 
					 // div с текстовым полем
					 var div2 = document.createElement("div");
					 div2.style.textAlign ='center';
					 div2.style.border ='#000000 solid 0px';
					 
					 var textarea = document.createElement("textarea");
					 textarea.name ='form_data[plan]';
					 textarea.style.height ='92px';
					 textarea.style.width ='744px';
					 textarea.style.border ='#CCCCCC solid 1px';
					 textarea.style.backgroundColor ='#F6F6F6';
					 textarea.style.fontFamily ='Arial';
					 div2.appendChild(textarea);
					 
					 // дополниетльное поле содержащее cilent_id
					 
					 var client_id_input = document.createElement("input"); 
					 client_id_input.type = 'hidden';
					 client_id_input.name ='form_data[client_id]';
					 client_id_input.value = client_id;
					 div2.appendChild(client_id_input);

					 
					 ///////////////////////////////////////////////////////
					 // end содержимое сплывающего окна
					 ///////////////////////////////////////////////////////
					 
					 // добавляем содержимое в таблицу в форму а затем в таблицу окна
					 form.appendChild(div1);
					 form.appendChild(div2);
					 arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(form);
					 
					 // добавляем таблицу в окно
					 arr[1].appendChild(arr[2]);
					 arr[1].className = 'planner_window';
					 document.body.appendChild(arr[0]);
					 document.body.appendChild(arr[1]);
					 
		             show_planner_window.in_process = false;
                     
			    }
			    else{
				    alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		    }
		}	
   }
   show_planner_window_for_editing.in_process = false;
   function show_planner_window_for_editing(client_id,id){
	   
	    if(show_planner_window_for_editing.in_process) return;
	    else show_planner_window_for_editing.in_process = true;
		
		if(!check_existence_element_by_ID("exec_date_input_" + id)) return;
	    if(!check_existence_element_by_ID("type_" + id)) return;
	    if(!check_existence_element_by_ID("cont_face_" + id)) return;
	    if(!check_existence_element_by_ID("plan_" + id)) return;

	    if(document.getElementById("2Q2EFEF9740WE")) document.getElementById("2Q2EFEF9740WE").parentNode.removeChild(document.getElementById("2Q2EFEF9740WE"));

	   
	    // снимаем данные с ряда
		var preset_exec_date = document.getElementById("exec_date_input_" + id).value;
	    var preset_type = document.getElementById("type_" + id).innerHTML;
	    var preset_cont_face = document.getElementById("cont_face_" + id).innerHTML;
	    var preset_plan = document.getElementById("plan_" + id).innerHTML;
		
		var preset_exec_time = preset_exec_date.slice(preset_exec_date.indexOf(" ")+1,preset_exec_date.indexOf(" ")+6);
		preset_exec_time = preset_exec_time.replace(':','.');
		var preset_exec_day =  preset_exec_date.slice(0,preset_exec_date.indexOf(" "));
		preset_exec_day_arr = preset_exec_day.split('-'); 
		for(var val in preset_exec_day_arr) preset_exec_day_arr[val] = parseInt(preset_exec_day_arr[val]);
		preset_exec_day_arr[1]--;
		//preset_exec_day_arr = new Array(2011,2,3); 
		
		//////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
	    var url = '?page=clients&client_id=' + client_id + '&subquery_for_planner_window=1';
		
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
		
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				     ///////////////////////////////////////////

					
					 var request_response = request.responseText;
					 //alert(request_response);
					 var cont_faces_arr = request_response.split('{@}');
					 // создаем всплывающее окно
					 up_window_consructor.setWindowDimentions(330,800)
					 var arr = up_window_consructor.windowBilder('2Q2EFEF9740WE');
					 
					 ///////////////////////////////////////////////////////
					 // содержимое сплывающего окна
					 ///////////////////////////////////////////////////////
					 
					 //элемент форма
					 var form = document.createElement("form");
					 form.method = "POST";
					 form.action = location.href;
					 
					 // верхний div
                     var div1 = document.createElement("div");
					 div1.style.height ='140px';
					 div1.style.border ='#000000 solid 0px';
					 
					 var div_float_left = document.createElement("div"); // плавающий div контейнер
					 div_float_left.style.float ='left';
					 div_float_left.style.margin ='15px 0px 0px 5px';
					 //div_float_left.style.width ='200px';
					 //div_float_left.style.border ='#000000 solid 1px';
					 
					 // тип записи
					 var plan_types_arr = ['звонок','встреча','заявка'];
					 var select_element = document.createElement("select");
					 select_element.name ='form_data[plan_type]';
					 select_element.style.width = "140px";
					 select_element.style.border = "#CCCCCC solid 1px";
					 for( var i=0; i < plan_types_arr.length ;i++){
						 var option = document.createElement("option");
						 option.value = plan_types_arr[i];
						 option.innerHTML = plan_types_arr[i];
						 select_element.appendChild(option);
						 
						 ///////////////////////////////////////////
						 
						 if(plan_types_arr[i] == preset_type) option.selected = true;
						 
						 /////////////////////////////////////////////
						 
						 
					 }
					 var plantype_headline_div = document.createElement("div");
					 plantype_headline_div.innerHTML = '&nbsp;Тип задачи';
					 var plantype_select_div = document.createElement("div");
					 plantype_select_div.appendChild(select_element);
					 
					 ///////////////////////////////////////////
					 
					 var id_input = document.createElement("input"); 
					 id_input.type = 'hidden';
					 id_input.name = 'form_data[id]';
					 id_input.value = id;
						 
					 /////////////////////////////////////////////
					 
					 div_float_left.appendChild(plantype_headline_div);
					 div_float_left.appendChild(plantype_select_div);
					 div_float_left.appendChild(id_input);
					
					 
					 // контактные лица
					 var select_element2 = document.createElement("select");
					 select_element2.name ='form_data[cont_face]';
					 select_element2.style.width = "140px";
					 select_element2.style.border = "#CCCCCC solid 1px";
					 for( var i=0; i < cont_faces_arr.length ;i++){
						 var option = document.createElement("option");
						 option.value = cont_faces_arr[i];
						 option.innerHTML = cont_faces_arr[i];
						 select_element2.appendChild(option);
						 
						 ///////////////////////////////////////////
						 
						 if(cont_faces_arr[i] == preset_cont_face) option.selected = true;
						 
						 /////////////////////////////////////////////
						 
					 }
					 var contfaces_headline_div = document.createElement("div");
					 contfaces_headline_div.innerHTML = '&nbsp;Контактные лица';
					 contfaces_headline_div.style.marginTop = "6px";
					 var contfaces_select_div = document.createElement("div");
					 contfaces_select_div.appendChild(select_element2);
					 
					 div_float_left.appendChild(contfaces_headline_div);
					 div_float_left.appendChild(contfaces_select_div);
					 
					 
					  // календарь
					 var div_float_right2 = document.createElement("div"); // плавающий div контейнер
					 div_float_right2.style.float ='right';
					 div_float_right2.style.margin ='10px 0px 0px 0px';
	                 div_float_right2.style.width ='250px';
					 div_float_right2.style.border ='#000000 solid 0px';
					 
					 // calendar_consturctor.setContextDay();					 
					 ///////////////////////////////////////////
						 
					 calendar_consturctor.setContextDay(preset_exec_day_arr,true);
						 
					 /////////////////////////////////////////////
                     var calendar = calendar_consturctor.calendarTableBilder('calendar_table');
                     calendar[0].style.width = '180px';
                     calendar[0].appendChild(calendar[1]);
                     calendar[0].appendChild(calendar[2]);
					 calendar[0].appendChild(calendar[3]);
                     div_float_right2.appendChild(calendar[0]);
					 
					 // time table
					 var div_float_right3 = document.createElement("div"); // плавающий div контейнер
					 div_float_right3.style.float ='right';
					 div_float_right3.style.margin ='10px 0px 0px 0px';
	                 div_float_right3.style.width ='220px';
					 div_float_right3.style.border ='#000000 solid 0px';
					 				 
					 var time_table = calendar_consturctor.timeTable('time_table',preset_exec_time);
					 time_table[0].style.width = '180px';
					 time_table[0].style.margin ='0px 0px 0px 0px';
                     time_table[0].appendChild(time_table[1]);
                     time_table[0].appendChild(time_table[2]);
					 time_table[3].style.margin ='2px 0px 0px 0px';
					 div_float_right3.appendChild(time_table[3]);
                     div_float_right3.appendChild(time_table[0]);
					 
					 //кнопки ok? reset и отменить
					 var div_float_right1 = document.createElement("div"); // плавающий div контейнер
					 div_float_right1.style.float ='right';
					 div_float_right1.style.margin ='10px 0px 0px 0px';
	                 div_float_right1.style.width ='98px';
					 div_float_right1.style.border ='#000000 solid 0px';
					 
					 //var button_ok = document.createElement("button"); 
					 var button_ok = document.createElement("input"); 
					 button_ok.type = 'submit';
					 button_ok.name = 'edit_plan';
					 //button_ok.innerHTML = 'ok';
					 button_ok.value = 'ok';
					 button_ok.style.height = '30px';
					 button_ok.style.width = '90px';
					 button_ok.style.backgroundColor = "rgb(122, 189, 121)";
					 button_ok.style.border = "#555 solid 1px";
					 button_ok.style.borderRadius ='2px';
					 button_ok.style.cursor = 'pointer';
					 
					 var button_ok_div = document.createElement("div");
					 button_ok_div.style.marginBottom = '15px';
					 button_ok_div.appendChild(button_ok);
					 
					 div_float_right1.appendChild(button_ok_div);
					 
					 
					 ////////////////..
 
					 var button_reset = document.createElement("input"); 
					 button_reset.type = 'reset';
					 button_reset.value = 'очистить';
					 button_reset.style.height = '30px';
					 button_reset.style.width = '90px';
					 button_reset.style.cursor = 'pointer';
					 
					 var button_reset_div = document.createElement("div");
					 button_reset_div.style.marginBottom = '15px';
					 button_reset_div.appendChild(button_reset);
					 
					 
					 
					 div_float_right1.appendChild(button_reset_div);
					 
					 
					 //var button_escape = document.createElement("button"); 
					 var button_escape = document.createElement("input"); 
					 button_escape.type = 'button';
					 //button_escape.innerHTML = 'отменить';
					 button_escape.value = 'отменить';
					 button_escape.style.height = '30px';
					 button_escape.style.width = '90px'
					 button_escape.style.cursor = 'pointer';
					 button_escape.onclick = up_window_consructor.closeWindow;
					
					 
					 var button_escape_div = document.createElement("div");
					 button_escape_div.appendChild(button_escape);
					 
					 div_float_right1.appendChild(button_escape_div);
					 
					 // объедняем(добавляем) элементы входящие в div1
					 div1.appendChild(div_float_left);
					 div1.appendChild(div_float_right1);
					 div1.appendChild(div_float_right2);
					 div1.appendChild(div_float_right3);
					 
					 
					 
					 // div с текстовым полем
					 var div2 = document.createElement("div");
					 div2.style.textAlign ='center';
					 div2.style.border ='#000000 solid 0px';
					 
					 var textarea = document.createElement("textarea");
					 textarea.name ='form_data[plan]';
					 textarea.style.height ='92px';
					 textarea.style.width ='744px';
					 textarea.style.border ='#CCCCCC solid 1px';
					 textarea.style.backgroundColor ='#F6F6F6';
					 textarea.style.fontFamily ='Arial';
					 div2.appendChild(textarea);
					 
					 ///////////////////////////////////////////
						 
					 textarea.value = preset_plan;
						 
					 /////////////////////////////////////////////
					 
					 // дополниетльное поле содержащее cilent_id
					 
					 var client_id_input = document.createElement("input"); 
					 client_id_input.type = 'hidden';
					 client_id_input.name ='form_data[client_id]';
					 client_id_input.value = client_id;
					 div2.appendChild(client_id_input);

					 
					 ///////////////////////////////////////////////////////
					 // end содержимое сплывающего окна
					 ///////////////////////////////////////////////////////
					 
					 // добавляем содержимое в таблицу в форму а затем в таблицу окна
					 form.appendChild(div1);
					 form.appendChild(div2);
					 arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(form);
					 
					 // добавляем таблицу в окно
					 arr[1].appendChild(arr[2]);
					 arr[1].className = 'planner_window';
					 document.body.appendChild(arr[0]);
					 document.body.appendChild(arr[1]);
		             show_planner_window_for_editing.in_process = false;

			    }
			    else{
				    alert("Нет интернет-соединения с сервером, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		    }
		}	
   }
   
   function send_select_data_to_rt_from_basket_new_os(){
		//данные клиента
		var client_input = document.getElementById('client_input');
		var client_data = client_input.value;
		var client_double_input = document.getElementById('client_input_double');
		var client_double_data = client_double_input.value;
		
	    
		if(client_data == '' || client_data == 'нет вариантов'){
			client_input.style.backgroundColor = "#CCFF33";
			client_input.focus();
			alert('Вы не указали клиента!!!');
		    return;
		}

		//данные менеджера
		var manager_login_input = document.getElementById('manager_login');
		var manager_login = manager_login_input.value;
		
		if(manager_login == '' || manager_login == 'нет вариантов' || client_data != client_double_data ){
	        show_search_result_list(client_input,'CLIENT_TBL','comp_full_name{@}company','company{@}comp_full_name{@}dop_param','Компания{@}Юр. лицо{@}Конт. лицо{@}Менеджер','/admin/order_manager/');
		    return;
     	}
		
		// проверяем есть ли одинаковые артикулы в корзине, чтобы понять надо сливать артикулы или нет 
		// а также проверяем какие чекбоксы выделены, это понадобится для определения какие артикулы сливать 
		//
		var checkboxesInfo = check_checkboxes();
	    console.log(checkboxesInfo);
		
	    if(checkboxesInfo.similarArts){
			// если открываем окно первый раз вызываем метод выбирающий все артикулы  
			if(typeof send_select_data_to_rt_from_basket_new_os.chose_all_checkboxes_applied ==='undefined'){
				chosen_all_checkboxes();
				send_select_data_to_rt_from_basket_new_os.chose_all_checkboxes_applied = true;
		    }
			
			var text = 'В корзине есть повторяющиеся артикулы. Хотите ли Вы, чтобы при переносе товара из корзины в ОС повторяющиеся артикулы были объеденены в одину позицию? Если да нажмите кнопку ОК. Если вы хотите чтобы часть артикулов была объеденена, при этом другие не объеденены, уберите галочки у тех артикулов которые не нужно объединять. ';
			var dialog = $('<div>'+text+'</div>');
			
			function withUnion(){
			    var checkboxesInfo = check_checkboxes();
				send(client_data,manager_login,checkboxesInfo.dopInfo); 
			}
			function withoutUnion(){ 
				$(':checkbox[name=marker_for_item]').prop('checked', false);
				var checkboxesInfo = check_checkboxes();
				send(client_data,manager_login,checkboxesInfo.dopInfo); 
			}
			$(dialog).dialog({width: 700 , title:"создание заявки в ОС", buttons:[ 
													{text: "Объединить выбранные артикулы",click: withUnion},
													{text: "Ничего не объединять",click: withoutUnion}
													]});
			$(dialog).dialog('open');
			return;
		}
		//console.log(dopInfo);
		//return;
		send(client_data,manager_login,checkboxesInfo.dopInfo);
		
		function check_checkboxes(){
			var dopInfo = {};
			var artIds = [];
			var similarArts = false;
			$('input[name=marker_for_item]').each(function(index){ 
														var art_id = this.getAttribute("art_id");
														// checking on similar articles
														//if(artIds.join(',').indexOf(art_id)>=0) similarArts = true;
														if(in_array(art_id,artIds)) similarArts = true;
														if(!similarArts) artIds.push(art_id);
														dopInfo[this.value] = {art_id:art_id,chkd:Number(this.checked)};
														
												  });
			return {similarArts:similarArts,dopInfo:dopInfo};
		}
		
		function in_array(needle, haystack, strict) {	
			// Checks if a value exists in an array
			// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		
			var found = false, key, strict = !!strict;
		
			for (key in haystack) {
				if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
					found = true;
					break;
				}
			}
		
			return found;
		}
		
		function send(client_data,manager_login,dopInfo){
			
			// в данной версии у нас есть имя клиента и логин менеджера, отправляем их, но это затем надо заменить на айдишники 
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////
			
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
			var request = HTTP.newRequest();
			//var url =  HOST+"/os/?add_data_to_rt_from_basket=1&client_data=" + encodeURIComponent(client_data).replace(regexp,"+") +  "&dop_info=" + JSON.stringify(dopInfo) +  "&manager_login=" + encodeURIComponent(manager_login).replace(regexp,"+");
			
			
			var url =  HOST+"/os/";
			//alert(url);
			var pairs = "add_data_to_rt_from_basket=1&client_data=" + encodeURIComponent(client_data).replace(regexp,"+") +  "&dop_info=" + JSON.stringify(dopInfo) +  "&manager_login=" + encodeURIComponent(manager_login).replace(regexp,"+");
			// return;
			// производим запрос
			//request.open("GET", url, true);
			//request.send(null);
			//alert(pairs);
			request.open("POST", url); 
	        request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	        request.send(pairs);
	
			request.onreadystatechange = function(){ // создаем обработчик события
			   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
				   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
					  ///////////////////////////////////////////
					  // обрабатываем ответ сервера
					  //  alert(22);
					  var request_response = request.responseText;
					   // alert(request_response);
					    console.log(request_response);
					  
					  //return;
					 
					  var responseObj = JSON.parse(request_response);
					  //console.log(responseObj);
					  if(responseObj[0] == 0){
							location = HOST+ '/os/?page=cabinet&section=requests&subsection='+responseObj[2]+'&client_id=' + responseObj[1];
					  }else if(responseObj['response'].length > 0){
					  	standard_response_handler(responseObj);
					  }else alert(responseObj[1]); /**/
					 
				   }
				   else{
					  //alert("AJAX запрос не выполнен");
				   }
			   }
		   }
		   //////////////////////////////////////////////////////////////////////////////////////////
		}
	}
   
    function print_agreement(){
	   //

	   var step = 2;
	   var counter = 1;
	   var timer = setInterval(up,25);
	   
	   function up(){
	       document.getElementById('agreement_blank').style.marginTop = -step*counter + 'px';
		   document.getElementById('agreement_tools_plank').style.marginTop = -step*counter + 'px';
		   counter++;
		   if(counter >= 25){
			   document.getElementById('agreement_tools_plank').style.display = 'none';
			   clearInterval(timer);
			   setTimeout(window.print,400);
			   setTimeout(down,800);
		   }
	   }
	   function down(){
		   document.getElementById('agreement_tools_plank').style.display = 'block';
		   
		   var down_timer = setInterval(set_down,25);
		   function set_down(){
	
			   document.getElementById('agreement_blank').style.marginTop = -step*counter + 'px'; 
			   document.getElementById('agreement_tools_plank').style.marginTop = -step*counter + 'px';
			   counter--;
			   if(counter <= 0){
				   clearInterval(down_timer);
			   }
		   }
	   }
	   
   }
   
   function set_plan_making_result(row_id,event_type){
	   if(document.getElementById("QBRJF9740WE")) document.getElementById("QBRJF9740WE").parentNode.removeChild(document.getElementById("QBRJF9740WE"));
	   
	   // создаем всплывающее окно
	   up_window_consructor.setWindowDimentions(290,800)
	   var arr = up_window_consructor.windowBilder('QBRJF9740WE');
	 
	   ///////////////////////////////////////////////////////
	   // содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   //элемент форма
	   var form = document.createElement("form");
	   form.method = "POST";
	   form.action = location;
	 
	   // верхний div
	   var div1 = document.createElement("div");
	   div1.style.height ='100px';
	   div1.style.border ='#000000 solid 0px';
	 
	   var div_float_left = document.createElement("div"); // плавающий div контейнер
	   div_float_left.style.float ='left';
	   div_float_left.style.margin ='15px 0px 0px 5px';
	   //div_float_left.style.border ='#000000 solid 1px';
	   
	   // скрытый input передающий тип (записи) события 
	   var event_type_input = document.createElement("input");
	   event_type_input.type = 'hidden';
	   event_type_input.name = 'form_data[event_type]';
	   event_type_input.value = event_type;
	   div_float_left.appendChild(event_type_input);
	   
	   // Эмоциональная оценка
	   var emotion_headline_div = document.createElement("div");
	   emotion_headline_div.innerHTML = '&nbsp;Эмоциональная оценка';

	   var plan_types_arr = [1,2,3,4,5];
	   var emotion_btn_arr = [];
	   var mark_input = document.createElement("input");
	   mark_input.type = "hidden";
	   mark_input.value = 1;
	   mark_input.name ='form_data[emotion_mark]';
	   var emotion_container = document.createElement("div");
	   for( var i=0; i < plan_types_arr.length ;i++){
		   var emotion_btn = document.createElement("div");
		   emotion_btn.style.padding = '5px 25px';
		   emotion_btn.style.fontSize = '15px';
		   emotion_btn.style.float = 'left';
		   emotion_btn.style.cursor = 'pointer';
	       emotion_btn.style.backgroundColor = (plan_types_arr[i]==1)?"rgb(255, 174, 0)":"rgb(122, 189, 121)";
	       emotion_btn.style.border = "#555 solid 1px";
	       emotion_btn.style.borderRadius ='3px';
		   emotion_btn.setAttribute('value',plan_types_arr[i]);
           emotion_btn.onclick = function(){
			   mark_input.value = this.getAttribute('value');
			   for(var btn in emotion_btn_arr) emotion_btn_arr[btn].style.backgroundColor = "rgb(122, 189, 121)"; 
			   this.style.backgroundColor = "rgb(255, 174, 0)";
		   }
		   emotion_btn.innerHTML = plan_types_arr[i];
		   emotion_btn_arr.push(emotion_btn);
		   emotion_container.appendChild(emotion_btn);
		 
	   }
	 
	   div_float_left.appendChild(emotion_headline_div);
	   div_float_left.appendChild(emotion_container);
	   div_float_left.appendChild(mark_input);
	   
	
	   //кнопки ok reset и отменить
	   var div_float_right1 = document.createElement("div"); // плавающий div контейнер
	   div_float_right1.style.float ='right';
	   div_float_right1.style.margin ='10px 0px 5px 0px';
	   div_float_right1.style.width ='98px';
	   div_float_right1.style.border ='#000000 solid 0px';
	   
	   //var button_ok = document.createElement("button"); 
	   var button_ok = document.createElement("input"); 
	   button_ok.type = 'submit';
	   button_ok.name = 'set_result_for_plan';
	   //button_ok.innerHTML = 'ok';
	   button_ok.value = 'ok';
	   button_ok.style.height = '30px';
	   button_ok.style.width = '90px';
	   button_ok.style.backgroundColor = "rgb(122, 189, 121)";
	   button_ok.style.border = "#555 solid 1px";
	   button_ok.style.borderRadius ='2px';
	   button_ok.style.cursor = 'pointer';
		 
	   var button_ok_div = document.createElement("div");
	   button_ok_div.style.marginBottom = '15px';
	   button_ok_div.appendChild(button_ok);
	 
	   div_float_right1.appendChild(button_ok_div);
	 
	 
	   // var button_reset = document.createElement("button"); 
	   var button_reset = document.createElement("input"); 
	   button_reset.type = 'reset';
	   // button_reset.innerHTML = 'очистить';
	   button_reset.value = 'очистить';
	   button_reset.style.height = '30px';
	   button_reset.style.width = '90px';
	   button_reset.style.cursor = 'pointer';
	 
	   var button_reset_div = document.createElement("div");
	   button_reset_div.style.marginBottom = '15px';
	   button_reset_div.appendChild(button_reset);
	 
	 
	 
	 div_float_right1.appendChild(button_reset_div);
		 
		 
	 //var button_escape = document.createElement("button"); 
	 var button_escape = document.createElement("input"); 
	 button_escape.type = 'button';
	 //button_escape.innerHTML = 'отменить';
	 button_escape.value = 'отменить';
	 button_escape.style.height = '30px';
	 button_escape.style.width = '90px'
	 button_escape.style.cursor = 'pointer';
	 button_escape.onclick = up_window_consructor.closeWindow;
	 
	 var button_escape_div = document.createElement("div");
	 button_escape_div.appendChild(button_escape);
	 
	 div_float_right1.appendChild(button_escape_div);
	 
	   div_float_right1.appendChild(button_escape_div);
	 
	   // объедняем(добавляем) элементы входящие в div1
	   div1.appendChild(div_float_left);
	   div1.appendChild(div_float_right1);
	 
	 
	 
	   // div с текстовым полем
	   var div2 = document.createElement("div");
	   div2.style.textAlign ='center';
	   div2.style.border ='#000000 solid 0px';
	 
	   var textarea = document.createElement("textarea");
	   textarea.name ='form_data[result]';
	   textarea.style.height ='67px';
	   textarea.style.width ='744px';
	   textarea.style.border ='#CCCCCC solid 1px';
	   div2.appendChild(textarea);
	 
	   // дополниетльное поле содержащее row_id
	 
	   var row_id_input = document.createElement("input"); 
	   row_id_input.type = 'hidden';
	   row_id_input.name ='form_data[row_id]';
	   row_id_input.value = row_id;
	   div2.appendChild(row_id_input);

	 
	   ///////////////////////////////////////////////////////
	   // end содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   // добавляем содержимое в таблицу в форму а затем в таблицу окна
	   form.appendChild(div1);
	   form.appendChild(div2);
	   arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(form);
	 
	   // добавляем таблицу в окно
	   arr[1].appendChild(arr[2]);
	   arr[1].className = 'planner_window';
	   document.body.appendChild(arr[0]);
	   document.body.appendChild(arr[1]);

   }
     function show_discount_window(element,row_id,client_id){
	    if(document.getElementById("BNODYUF0WE38")) document.getElementById("BNODYUF0WE38").parentNode.removeChild(document.getElementById("BNODYUF0WE38"));
	
	   // создаем всплывающее окно
	   up_window_consructor.setWindowDimentions(310,425)
	   
	   var arr = up_window_consructor.windowBilder('BNODYUF0WE38');
	   
	   var price_td = rtCalculator.certainTd(element,'price_out');
	   if(price_td == false){
		   alert('не удалось определить цену');
		   return;
	   }
	   var cur_price = price_td.innerHTML; 
	    
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
	   
	   //поле client_id
	   var input_client_id = document.createElement("input"); 
	   input_client_id.type = 'hidden';
	   input_client_id.name = 'form_data[client_id]';
	   input_client_id.value = client_id;
	   
	   //поле cur_price
	   var input_cur_price = document.createElement("input"); 
	   input_cur_price.type = 'hidden';
	   input_cur_price.name = 'form_data[cur_price]';
	   input_cur_price.value = cur_price;
	   
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
	   div1.appendChild(input_client_id);
	   div1.appendChild(input_cur_price);
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
	   input_radio1.name = 'form_data[which_rows]';
	   input_radio1.value = 'one_row';
	   input_radio1.checked = 'true';
	   var label1 = document.createElement("label"); 
	   label1.style.cursor ='pointer';
	   label1.appendChild(input_radio1);
	   label1.appendChild(document.createTextNode("данная позиция"));
	   label1.appendChild(document.createElement("br"));
	   
	   var input_radio2 = document.createElement("input"); 
	   input_radio2.type = 'radio';
	   input_radio2.name = 'form_data[which_rows]';
	   input_radio2.value = 'all_in_pos';
	   var label2 = document.createElement("label");
	   label2.style.cursor ='pointer';
	   label2.appendChild(input_radio2);
	   label2.appendChild(document.createTextNode("все расчеты в позиции"));
	   label2.appendChild(document.createElement("br"));
	   
	   
	   var input_radio3 = document.createElement("input"); 
	   input_radio3.type = 'radio';
	   input_radio3.name = 'form_data[which_rows]';
	   input_radio3.value = 'all_in_query';
	   var label3 = document.createElement("label"); 
	   label3.style.cursor ='pointer';
	   label3.appendChild(input_radio3);
	   label3.appendChild(document.createTextNode("все позиции в заявке"));
	   label3.appendChild(document.createElement("br"));
	   
	 /*  var input_radio2 = document.createElement("input"); 
	   input_radio2.type = 'radio';
	   input_radio2.name = 'form_data[which_rows]';
	   input_radio2.value = 'all';
	   var label2 = document.createElement("label");
	   label2.style.cursor ='pointer';
	   label2.appendChild(input_radio2);
	   label2.appendChild(document.createTextNode("весь заказ"));
	   label2.appendChild(document.createElement("br"));
	   
	   var input_radio3 = document.createElement("input"); 
	   input_radio3.type = 'radio';
	   input_radio3.name = 'form_data[which_rows]';
	   input_radio3.value = 'all_goods';
	   var label3 = document.createElement("label"); 
	   label3.style.cursor ='pointer';
	   label3.appendChild(input_radio3);
	   label3.appendChild(document.createTextNode("все сувениры"));
	   label3.appendChild(document.createElement("br"));
	   
	   
	   var input_radio4 = document.createElement("input"); 
	   input_radio4.type = 'radio';
	   input_radio4.name = 'form_data[which_rows]';
	   input_radio4.value = 'all_print';
	   var label4 = document.createElement("label"); 
	   label4.style.cursor ='pointer';
	   label4.appendChild(input_radio4);
	   label4.appendChild(document.createTextNode("всё нанесение"));
	   label4.appendChild(document.createElement("br"));
*/

       // объединяем div4
	   div3.appendChild(label1);
	   div3.appendChild(label2);
	   div3.appendChild(label3);
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

	/**
	 *	выход
	 *		
	 *	@author  	Алексей Капитонов
	 *	@version 	12:29 14.01.2016
	 */
	 function autorisation_qute(){
	//alert(HOST);
	event.preventDefault();

		$.post(location.protocol+'//'+location.hostname+'/',
			{ 
			out: 1
			},
			function(data){
				//alert(data)
				if(data=="output_is_performed"){					
					 window.location.href = location.protocol+'//'+location.hostname+'/';
				}
			});	
	}
	// окно примечания к варианту
	$(document).on('click', '.comment_div', function(event) {
		event.preventDefault();
		var href = $(this).attr('data-href');

		$.post(href, {
			href: href,
			AJAX: 'get_dop_men_text_save',
			row_id: $(this).attr('data-id')
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	});

	// сохраняем информацию из поля примечаний с задержкой
	$(document).on('keyup', '.dop_men_text textarea,#dop_men_text textarea', function(event) {
	   timing_save_input('dop_men_text_save',$(this));
	});

	// сохраняем информацию из поля примечаний
	function dop_men_text_save(obj){

	  var row_id = obj.attr('data-id');
	  var href = '';
	  if(obj.attr('data-href') != ''){
	  	href = obj.attr('data-href');
	  }
	  $.post(href, {
	      AJAX:'dop_men_text_save',
	      row_id:row_id,
	      value:Base64.encode(obj.val())
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


// $('#authentication_menu_div').hover(function(){},function(){$(this).fadeOut('show')})

jQuery(function($){
	$(document).mouseup(function (e){ // событие клика по веб-документу
		var div = $("#authentication_plank_tbl"); // тут указываем ID элемента
		if (!div.is(e.target) // если клик был не по нашему блоку
		    && div.has(e.target).length === 0) { // и не по его дочерним элементам
			$('#authentication_menu_div').hide(); // скрываем его
		}
	});
});

 function show_hide_div_and_checkbox(element,id){
	//alert(element.checked);
	var div = document.getElementById(id);
	if(element.checked) div.style.display = 'block';
	else div.style.display = 'none';
	//return false;
}