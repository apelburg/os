// JavaScript Document
 var aplCalculators = {
	 var_pattern: null,
	 //this.incoming_data_boxes[index].style.border = "#FF0000 solid 1px";
	 pattern:function(){
	 }
	 ,
	 set_common_vales:function(){
		 this.col= null;
	 	 this.common_itog_display= null;
	 	 this.common_price_per_item_display= null;
	 	 this.itog_displays= [];
	 	 this.price_per_item_displays= [];
	 	 this.incoming_data_container= null;
	 	 this.incoming_data_names= [];
	 	 this.incoming_data_vtypes= [];
	 	 this.incoming_data_boxes= [];
	 	 this.incoming_data_box_clone= [];
	 	 this.itog=[];
	 	 this.base_prices_tbl_x= [];
	 	 this.base_prices_tbl_y= [];
	 	 this.base_prices= [];
	 }
	 ,
	 reset_type:function(){
		 this.incoming_data_container.parentNode.replaceChild(this.incoming_data_container_clone,this.incoming_data_container)
		 this.set_common_vales();
	 }
	 ,
	 copy_incoming_data_box:function(element){
		 var new_box = element.parentNode.cloneNode(true);
		 this.incoming_data_container.insertBefore(new_box,this.incoming_data_boxes[this.incoming_data_boxes.length-1].nextSibling);

		 this.incoming_data_boxes = [];
		 this.define_incoming_data_boxes();
		 
	 }
	 ,
	 add_incoming_data_box:function(){
		 var new_box = this.incoming_data_box_clone.cloneNode(true);
		 this.incoming_data_container.insertBefore(new_box,this.incoming_data_boxes[this.incoming_data_boxes.length-1].nextSibling);

		 this.incoming_data_boxes = [];
		 this.define_incoming_data_boxes();
		 
	 }
	 ,
	 define_incoming_data_boxes:function(){
		 var items = this.incoming_data_container.childNodes;
		 for(var i = 0; i < items.length; i++){
			if(items[i].nodeType === 1 && items[i].nodeName.toUpperCase() == 'DIV'  && items[i].getAttribute('name')  && items[i].getAttribute('name') == 'incoming_data_box')  this.incoming_data_boxes.push(items[i]);
		 }
	 }
	 ,
	 set_type:function(type){
		 if(this.calculator_type && this.calculator_type == type) this.reset_type();
		 else this.set_common_vales();
		 
		 this.calculator_type = type;
		 this.incoming_data_tpls_container = document.getElementById('incoming_data_tpls_container');
		 var items = this.incoming_data_tpls_container.childNodes;
		 for(var index in items){
			 if(items[index].nodeType === 1 && items[index].nodeName.toUpperCase() == 'DIV'){
				if(items[index].getAttribute('name')  && items[index].getAttribute('name') == type){
					this.incoming_data_container = items[index];
					items[index].style.display = 'block';
				}
				else items[index].style.display = 'none';
			 }
		 }
		
		 this.incoming_data_container_clone = this.incoming_data_container.cloneNode(true);
		 
		 this.define_incoming_data_boxes();
		 
		 this.incoming_data_box_clone =  this.incoming_data_boxes[0].cloneNode(true);
		
	 }
	 ,
	 process_data:function(){// производит расчет стоимости
          // надо запускать если были изменено что-то кроме количества или цветности 
		  this.pull_incoming_data();
		  // надо запускать если были изменены количество или цветность 
		  this.pull_base_price();
	      this.itog = [];
	      for(var i = 0; i < this.incoming_data_vtypes.length; i++){
			  var operands = this.incoming_data_vtypes[i];
			  //alert (this.col+' '+ this.base_price+' '+ operands.price_add+' '+ operands.price_coeff+' '+ operands.summ_add+' '+ operands.summ_coeff);

			  this.itog[i] = (((this.col*((this.base_prices[i]+operands.price_add)*operands.price_coeff))+operands.summ_add)*operands.summ_coeff);
		  }
		  
		  //alert(this.itog[0]);
		  //alert(this.itog[1]);
		  this.define_itog_fields(); 
		  this.show_results();
	 }
	 ,
	 show_results:function(){// показывает итог расчетов 
	 
	      var itog_summ = 0;
		  for(var index in this.itog){
			  itog_summ+=this.itog[index];
		  }
		  this.common_itog_display.innerHTML = itog_summ;
		  this.common_price_per_item_display.innerHTML = itog_summ/this.col;
		  
		  for(var i = 0; i < this.itog.length; i++){
			  this.itog_displays[i].innerHTML = this.itog[i];
		      this.price_per_item_displays[i].innerHTML = this.itog[i]/this.col;
		  }
		  
	 }
	 ,
	 pull_incoming_data:function(){// снимает данные с калькулятора складываем данные в обьект
	 
	     this.tirag_and_itog_part_tbl = document.getElementById('tirag_and_itog_part_tbl');
		 var inputs =  this.tirag_and_itog_part_tbl.getElementsByTagName('input');
		 for(var i = 0 ; i < inputs.length; i++) if(inputs[i].nodeType === 1 && inputs[i].nodeName.toUpperCase() == 'INPUT'  && inputs[i].getAttribute('name')  && inputs[i].getAttribute('name') == 'col') this.col = inputs[i].value;
		 //alert(this.col);
		 
	     var items = this.incoming_data_container.childNodes;
		 var counter = 0;
		 for(var i = 0 ; i < items.length; i++){
			if(items[i].nodeType === 1 && items[i].nodeName.toUpperCase() == 'DIV'  && items[i].getAttribute('name')  && items[i].getAttribute('name') == 'incoming_data_box'){
				var inputs = items[i].getElementsByTagName('input');
				var names_obj = {};
				var vtypes_obj = {};
				vtypes_obj.price_add = 0;
				vtypes_obj.price_coeff = 1;
				vtypes_obj.summ_add = 0;
				vtypes_obj.summ_coeff = 1.0;
				for(var j = 0 ; j < inputs.length; j++){
			        if(inputs[j].nodeType === 1 && inputs[j].nodeName.toUpperCase() == 'INPUT'){
					    if(inputs[j].getAttribute('name')) names_obj[inputs[j].getAttribute('name')] =  inputs[j].value;
						if(inputs[j].getAttribute('vtype')){
							if(inputs[j].getAttribute('vtype') == 'summ_coeff'){

								vtypes_obj.summ_coeff *= parseFloat(inputs[j].value);
                                // возникает погрешность при расчете наверно здесь лучще использовать класс math	 
							}
							else vtypes_obj[inputs[j].getAttribute('vtype')] = inputs[j].value;
						}
						
				    }
					
		        }
				this.incoming_data_names[counter] = names_obj;
				this.incoming_data_vtypes[counter++] = vtypes_obj;
			}
		 }
		 //alert(this.incoming_data_boxes.length);
		 //alert(this.incoming_data_vtypes.length +' '+this.incoming_data_vtypes.length);
	
		 //for(var name in this.incoming_data_names[0]) alert(name + ' ' + this.incoming_data_names[0][name]);
		 //for(var vtype in this.incoming_data_vtypes[0]) alert(vtype + ' ' + this.incoming_data_vtypes[0][vtype]);
		 //for(var index in this.incoming_data_vtypes) for(var vtype in this.incoming_data_vtypes[index]) alert(vtype + ' ' + this.incoming_data_vtypes[index][vtype]);
		 //alert(this.incoming_data_vtypes[0]['color_num']);
	 }
	 ,
	 pull_base_price:function(){// снимает базовую стоимость 
	     //alert(1);
	     var items = this.incoming_data_container.childNodes;
		 for(var i = 0 ; i < items.length; i++){
			if(items[i].nodeType === 1 && items[i].nodeName.toUpperCase() == 'TABLE'  && items[i].getAttribute('name')  && items[i].getAttribute('name') == 'base_prices_tbl')  this.base_prices_tbl = items[i];
		 }

		 var trs = this.base_prices_tbl.getElementsByTagName('tr');

		 var first_row_tds = trs[0].getElementsByTagName('td');

		 for(var i = 0 ; i < this.incoming_data_vtypes.length; i++){
			 for(var j = 0 ; j < first_row_tds.length; j++){
				 if(this.col >= parseInt(first_row_tds[j].innerHTML)) this.base_prices_tbl_x[i] = j;
			 }
		 }
		 
		 for(var i = 0 ; i < this.incoming_data_vtypes.length; i++){		 
			 for(var j = 1 ; j < trs.length; j++){
				 var tds = trs[j].getElementsByTagName('td');
				 if(parseInt(tds[0].innerHTML) == parseInt(this.incoming_data_vtypes[i]['color_num'])){
					 this.base_prices_tbl_y[i] = j;
					 this.base_prices[i] =  parseFloat(tds[this.base_prices_tbl_x[i]].innerHTML);// не работает???!!! parseFloat() 
				 }
			 }
		 }
		 //alert(' x - ' + this.base_prices_tbl_x[0] + '   y - ' + this.base_prices_tbl_y[0] + '  ' +  this.base_prices[0]);
		 //alert(' x - ' + this.base_prices_tbl_x[1] + '   y - ' + this.base_prices_tbl_y[1] + '  ' +  this.base_prices[1]);
	 }	 
	 ,
	 define_itog_fields:function(){
	     var spans =  this.tirag_and_itog_part_tbl.getElementsByTagName('span');
		 for(var i = 0 ; i < spans.length; i++){
			 if(spans[i].nodeType === 1 && spans[i].nodeName.toUpperCase() == 'SPAN'  && spans[i].getAttribute('name')){                  if(spans[i].getAttribute('name') == 'common_itog_display') this.common_itog_display = spans[i];
			      if(spans[i].getAttribute('name') == 'common_price_per_item_display') this.common_price_per_item_display = spans[i];
			 }
		 }
		 //this.itog_displays

		 for(var i = 0 ; i < this.incoming_data_boxes.length; i++){
			  var spans =  this.incoming_data_boxes[i].getElementsByTagName('span');
			  for(var j = 0 ; j < spans.length; j++){
				 if(spans[j].nodeType === 1 && spans[j].nodeName.toUpperCase() == 'SPAN'  && spans[j].getAttribute('name')){                      if(spans[j].getAttribute('name') == 'itog_display') this.itog_displays[i] = spans[j];
					  if(spans[j].getAttribute('name') == 'price_per_item_display') this.price_per_item_displays[i] = spans[j];
				 }
			 }
		 }
		 //for(var index in this.itog_displays) this.itog_displays[index].style.border = "#FFFF00 solid 1px";//alert();
		 //for(var index in this.price_per_item_displays) alert(this.price_per_item_displays[index].innerHTML);
		 this.itog_displays[0].style.border = "#FFFF00 solid 1px";
		 if(this.itog_displays[1]) this.itog_displays[1].style.border = "#CCCCCC solid 1px";
		 this.incoming_data_boxes[0].style.border = "#FFFF00 solid 1px";
		 if(this.incoming_data_boxes[1]) this.incoming_data_boxes[1].style.border = "#CCCCCC solid 1px";
	 }
	 ,
	 show_box:function(){
		 //add_print_row(this,'.$rt_id.');
		 //alert(1); 
		 this.set_box();
	 }
	 ,
	 set_box:function(){
		 
		 this.box = document.createElement("div");	
		 this.box.className = 'calculator_box';
		 this.box.style.height = '500px';
		 this.box.style.width = '700px';
		 this.box.style.top = (Geometry.getViewportHeight()-400)/2 + 'px';
		 this.box.style.left = (Geometry.getViewportWidth()-700)/2 + 'px';
		 
		 
		 this.ajax_request(call_back);
		 
		 function call_back(response){
			 aplCalculators.box.innerHTML = response;
			 document.body.appendChild(aplCalculators.box);  
		 }
		 
		 
	 }
	 ,
	 close_box:function(){
		 this.box.parentNode.removeChild(this.box);
	 }
	 ,
	 ajax_request:function(call_back){
		 
		 var url =  location.pathname +'?show_calculator';
         var request = HTTP.newRequest();
	     request.open("GET", url, true);
	     request.send(null);
	   
		 request.onreadystatechange = function(){ // создаем обработчик события
		    if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			    if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				    ///// обрабатываем ответ сервера////////////
					var request_response = request.responseText;
				    call_back(request_response);
			        // alert(request_response);
			     }
			     else{
				  alert("AJAX запрос невыполнен");
			     }
		     }
	     }
	 }
 }