// JavaScript Document

    var calculatingTableEmulator = {
		tbl_model:null,
		max_avail_num:99999999,// перескакивает курсор
	    initialization:function(){
			    var tbl = document.getElementById("calculate_tbl");	
				for(var m = tbl.getElementsByTagName("tr")[0], rows = [] ; m != null ; m = m.nextSibling ){
					//if( m.nodeType == Node.ELEMENT_NODE ) rows.push(m);
					//if( m instanceof HTMLTableRowElement )  rows.push(m);// не правильно 
					if( m.tagName == "TR" )  rows.push(m); //rows[j++] = m; //j = 0 ,
					
				}
                
				for(var i = 0 , j = 0 , tbl_model = []; i < rows.length ; i++ ){
					if(rows[i].getAttribute("hidden_num")){
						var row_id = rows[i].id.slice(4);
						var row_num = parseInt(rows[i].getAttribute("hidden_num"));
						var control_num = parseInt(rows[i].getAttribute("control_num"));
						var row_type = (rows[i].getAttribute("type"))? rows[i].getAttribute("type"): false ;
						if (row_type == 'calculating_row'){
							var data_arr = {}
							var inputs = rows[i].getElementsByTagName("input");
							for(var s = 0 ; s < inputs.length ; s++ ){
								if(inputs[s].getAttribute("calculating_type")){
									data_arr[inputs[s].getAttribute("calculating_type")] = inputs[s].value;
									inputs[s].onkeyup = calculatingTableEmulator.calculating; 
								}
							}
							
					        tbl_model[row_num]= data_arr;
							tbl_model[row_num].type = 'calculating_row';
							tbl_model[row_num].row_id = row_id;
							tbl_model[row_num].control_num = control_num;
							tbl_model[row_num].use_in_calculation = rows[i].getAttribute("use_in_calculation");
							
						}
						else tbl_model[row_num]= {'type':row_type};
					}
				}
				calculatingTableEmulator.tbl_model = tbl_model;
		}
		,
		calculating:function(){
			
			var row_num = this.getAttribute("hidden_num");
			var row = calculatingTableEmulator.tbl_model[row_num];
		
			var current_data = {};
			current_data['coming_price_summ'] = row['quantity']*row['coming_price'];
			current_data['price_summ'] = row['quantity']*row['price'];
			current_data['delta'] = current_data['price_summ'] - current_data['coming_price_summ'];
			
			var new_data = {};
			var new_data_to_change_in_db = {};
			// меняем значение с текущего на новое, теперь массив содержит новое значение
			var field_type = this.getAttribute("calculating_type");
			var field_value = parseFloat(this.value) || 0;
			//var field_value = (field_value > calculatingTableEmulator.max_avail_num)? calculatingTableEmulator.max_avail_num : field_value ;
			//this.value = field_value;
			//this.value = (field_value).toFixed(2);
			row[field_type] = field_value;
			new_data_to_change_in_db['type'] = field_type;
			new_data_to_change_in_db['value'] = field_value;
			new_data['coming_price_summ'] = row['quantity']*row['coming_price'];
			new_data['price_summ'] = row['quantity']*row['price'];
			new_data['delta'] = new_data['price_summ'] - new_data['coming_price_summ'];
			
			
			var difference = null;
			if(row.use_in_calculation == 'on'){
				difference = {};
				// разница текущих и новых значений
				difference['coming_summ'] = new_data['coming_price_summ'] - current_data['coming_price_summ'];
				difference['summ'] = new_data['price_summ'] - current_data['price_summ'];
				difference['delta'] = new_data['delta'] - current_data['delta'];
			}
			calculatingTableEmulator.result_setting_bd(row['row_id'],row['control_num'],new_data_to_change_in_db,row_num,new_data,difference);
			
			//alert(this.getAttribute("calculating_type")+' '+ this.getAttribute("hidden_num")+' '+ this.value+' '+ calculatingTableEmulator.tbl_model[this.getAttribute("hidden_num")].price+' '+current_data['coming_price_summ']+' '+current_data['price_summ']+' '+current_data['delta']+' '+ new_data['delta']);
		}
		,
		switching_calculation:function(element){
			//alert(element.getAttribute("hidden_num"));
			var sign = element.innerHTML;
			
			var row_num = element.getAttribute("hidden_num");
			var row = calculatingTableEmulator.tbl_model[row_num];
			
			var new_data = false;
			var difference = {};
			difference['coming_summ'] = row['quantity']*row['coming_price'];
			difference['summ'] =  row['quantity']*row['price'];
			difference['delta'] = difference['summ'] - difference['coming_summ'];
			
			difference['coming_summ'] = (sign == '+')? -difference['coming_summ']: difference['coming_summ'];
			difference['summ'] =  (sign == '+')? -difference['summ']: difference['summ'];
			difference['delta'] = (sign == '+')? -difference['delta']: difference['delta'];
			
			var status = (sign == '+')?'':'on';
			row.use_in_calculation = status;
			
			this.switching_calculation_marker_setting_bd(row['row_id'],status,row['control_num'],row_num,new_data,difference);
			
			
			if(sign == '+'){
				//element.parentNode.style.backgroundColor = '#CCC';
				element.className = 'marker_summ';
				element.innerHTML = '-';
			}
			else{
				//element.parentNode.style.backgroundColor = '#FF0000';
				element.className = 'marker_summ_on';
				element.innerHTML = '+';
			}
			
			
			return false;
		}
		,
		result_setting_bd:function(row_id,control_num,data,row_num,new_data,difference){

			
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
			var request = HTTP.newRequest();
			var url = "/test/?page=clients&section=client_folder&reset_calculation_data_in_db=true&row_id=" + row_id + "&data[" + data['type'] + "]=" + data['value'] + "&control_num=" + control_num;
		
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
						//alert(request_response);
				        calculatingTableEmulator.result_setting_html(row_num,new_data,difference);
					   //alert("AJAX запрос выполнен");
					 
					}
					else{
					  //alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
			
			//////////////////////////////////////////////////////////////////////////////////////////
		}
		,
		switching_calculation_marker_setting_bd:function(row_id,status,control_num,row_num,new_data,difference){

			
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
			var request = HTTP.newRequest();
			var url = "/test/?page=clients&section=client_folder&reset_switching_calculation_marker_in_db=true&row_id=" + row_id + "&status=" + status +  "&control_num=" + control_num;
		
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
						//alert(request_response);
				        calculatingTableEmulator.result_setting_html(row_num,new_data,difference);
					    //alert("AJAX запрос выполнен");
					 
					}
					else{
					  //alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
			
			//////////////////////////////////////////////////////////////////////////////////////////
		}
		,
		result_setting_html:function(row_num,new_data,difference){

			//print_r(new_data);
			//print_r(difference);
			if(new_data){
			for(var prop in new_data){
				document.getElementById(prop+row_num).innerHTML = new_data[prop].toFixed(2);
			}
			}
			//alert(row_num + ' ' +  this.tbl_model.length);
			
			if(difference){
			// ищем итоговую строку
				for(var i = row_num; i < this.tbl_model.length ; i++){
					if(this.tbl_model[i]['type'] == 'itog_row'){
						for(var prop in difference){
							document.getElementById('itog_'+prop+i).innerHTML = (parseFloat(document.getElementById('itog_'+prop+i).innerHTML) + parseFloat(difference[prop])).toFixed(2);
						}
					}
					if(this.tbl_model[i]['type'] == 'order_row'){
						break;//alert('order_row' + i);
					}
					
				}
			}
		}
	}
	
	// инициализация
	if(window.addEventListener) window.addEventListener('load',calculatingTableEmulator.initialization,false);
	else if(window.attachEvent) window.attachEvent('onload',calculatingTableEmulator.initialization);
	else window.onload = calculatingTableEmulator.initialization;
	