var printCalculator = {
   startCalculator:function (dataObejct){
	   //dataObejct = [dataObejct];
	   //alert(JSON.stringify(dataObejct));
	   //return;
       for(var ind in dataObejct){
			var dataObj = dataObejct[ind];
			// dataObj = {action: string value, type: string value,art_id: array [1345,3322,2330], usluga_id: массив value, dop_data_ids: array [0,1,2], quantity: array [100,100,200]}
			//  action [обязательный] - строка, возможные значения - "new" (при вызове из кнопки), "update" (при вызове из существующего расчета), "attach" (при добавлении в расчет), "detach" (при отделении от расчета) 
			//  type [необязательный] - строка, возможные значения - "union" (когда нужно создать объединенный тираж) 
			//  usluga_id [необязательный] - массив, нужен когда тыкаем по существующему нанесению, либо добавляем в тираж или выводим из тиража
			//  art_id [необязательный] - массив id-шников артикулов, (может не быть артикула)
			//  dop_data_ids [необязательный] - массив, нужен когда тыкаем по кнопке "Добавить услугу"
			//  quantity [необязательный] - массив, должен содержать значения тиражей из dop_data, нужен когда делается объединенный тираж
			//  calculator_type [необязательный] - строка, возможные значения - "auto" , "manual", "fee"
			
			console.log('---- Центр услуг вызов калькулятора -----',dataObj);
			
			dataObj.action = dataObj.action || 'new';
			dataObj.type = dataObj.type ||  false;
			dataObj.usluga_id = dataObj.usluga_id ||  false;
			dataObj.dop_data_ids = dataObj.dop_data_ids ||  false;
			dataObj.quantity = dataObj.quantity ||  false;
			dataObj.calculator_type = dataObj.calculator_type ||  false;
			dataObj.art_id = dataObj.art_id || [0];
			
			// !!!!!!!!!!!!!!!!!!!   ПОЛУЧИТЬ ДАННАЕ ОБ id АРТИКУЛА
			// !!!!!!!!!!!!!!!!!!!   ПОЛУЧИТЬ ДАННАЕ ОБ discount
			//printCalculator.discount = Number($('.percent_nacenki.js--calculate_tbl-edit_percent:visible').attr('data-val'));
			printCalculator.discount = 0;
			printCalculator.creator_id = $('*[user_id]').attr('user_id');

			// NEW
			if(dataObj.action=='new'){
				// дейстие - новое нанесение 
				// 1. сделать запрос на сервер для получения дефолтных параметров калькулятора
				
				if(dataObj.dop_data_ids){
					if(typeof dataObj.dop_data_ids != 'object'){ echo_message_js('переменная dataObj.dop_data_ids должна быть массивом');return;}
					if(dataObj.dop_data_ids.length == 0){ echo_message_js('вы не выбрали варианты расчетов');return;}
					// список id расчетов, надо передать в калькулятор
					
				}
				
				var quantity= parseInt(dataObj.quantity[0]);
				
				// если тип - объединенный тираж 
				if(dataObj.type && dataObj.type=='union'){
					 // внести в объект калькулятора метку о том что тираж сборный
					 // сложить тиражы всех расчетов входящих в объединенный тираж и передать в калькулятор
					 //dataObj.quantity;
					 var quantity=0;
					 for(var i in dataObj.quantity) { quantity += parseInt(dataObj.quantity[i]); }
				}
				delete dataObj;
	
				// НЕДОСТАЕТ - ПОЛУЧИТЬ ДАННыЕ ОБ id АРТИКУЛА чтобы подгрузить правильный калькулятор
				printCalculator.dataObj_toEvokeCalculator = {"art_id":dataObj.art_id[0],distribution_type:dataObj.type,"dop_data_ids":dataObj.dop_data_ids,"quantity":quantity,"quantity_details":dataObj.quantity,"action":dataObj.action};
				printCalculator.dataObj_toEvokeCalculator.creator_id =  printCalculator.creator_id;
				printCalculator.evoke_calculator();
	
			}
			
			// UPDATE
			if(dataObj.action=='update'){
				// дейстие - вызов из существующего нанесения
				// 1. сделать запрос на сервер для получения дефолтных параметров калькулятора и деталей нанесения из которого сделан вызов
				//alert(2);
				printCalculator.evoke_calculator_directly({"art_id":dataObj.art_id[0],"dop_data_ids":dataObj.dop_data_ids[0],"dop_uslugi_id":dataObj.usluga_id[0],"quantity":dataObj.quantity,"action":dataObj.action});
				delete dataObj;
			}
			
			// ATTACH
			if(dataObj.action=='attach'){
				// дейстие - добавление нанесения в объединенный тираж или распределение существующего нанесения
				
				// если Ручной или Держурная услуга просто вызываем калькулятор и затем открываем его с правильним тиражем(объединенный или нет)
				// если Автоматический надо сделать фоновый перерасчет на основе правильного тиража, если будут ошибки при расчете выкинуть
				// стандартные окна
				if(dataObj.usluga_id){
					if(typeof dataObj.usluga_id != 'object'){ echo_message_js('переменная dataObj.usluga_id должна быть массивом');return;}
					if(dataObj.usluga_id.length == 0){ echo_message_js('вы не выбрали варианты расчетов');return;}
					// список usluga_id, надо передать в калькулятор				
				}
				
	
				if(dataObj.calculator_type=='manual' || dataObj.calculator_type=='fee'){// если Ручной или Держурная услуга вызываем калькулятор c новым тиражем
					// пересчитываем новый тираж
					 var quantity=0;
					 for(var i in dataObj.quantity) { quantity += parseInt(dataObj.quantity[i]); }
					 
					 // вызываем калькулятор с новым тиражом
					 // дополнительно надо передать информацию что это добавление в тираж, и id добавляемого расчета
					 printCalculator.evoke_calculator_directly({"art_id":dataObj.art_id[0],"id_for_attachment":dataObj.dop_data_ids[0],"dop_uslugi_id":dataObj.usluga_id[0],"action":dataObj.action,"attachment_quantity":dataObj.quantity[0]});//dataObj.usluga_id[0]
					 
					
				}
				
				if(dataObj.calculator_type=='auto'){// если калькулятор атоматический 
				    var newDataObj= {};
				    for(var prop in dataObj){
					   //alert(prop+'-'+dataObj[prop]);
					   newDataObj[prop] = dataObj[prop];
				    }
				
				     // отправляем прямой запрос без открытия калькулятора на стороне клиента
					var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&save_calculator_result=1&details='+JSON.stringify(newDataObj),'section');
                    //  alert(url);
		            printCalculator.send_ajax(url,callback);
					
                   

					function callback(response){ 
						alert(response);
						// console.log(response);
						//location.reload();
					}
					
				}
				//delete dataObj;
				
			}
			
			// DETACH
			if(dataObj.action=='detach'){
				// дейстие - новое нанесение 
				// 1. сделать запрос на сервер для получения дефолтных параметров калькулятора
					
				if(dataObj.type && dataObj.type=='union'){
					 // тип - объединенный тираж 
					 // внести в объект калькулятора метку о том что тираж сборный
					 // внести в объект массив содержащий id расчетов включенных в тираж
					 // сложить тиражы всех расчетов входящив в объединенный тираж и передать в калькулятор
				}
			}
			
		}// запустить калькулятор
	}
	,
	evoke_calculator_directly: function(data){
	    //console.log(data);
		//printCalculator.discount = Number($('.percent_nacenki.js--calculate_tbl-edit_percent:visible').attr('data-val'));
		printCalculator.discount = 0;
		printCalculator.creator_id = $('*[user_id]').attr('user_id');
		
		if(data.dop_uslugi_id){
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&fetch_data_for_dop_uslugi_row='+data.dop_uslugi_id,'section');
			// alert(url);
			printCalculator.send_ajax(url,callback);
			function callback(response){ 
			    //alert(response);				
				
				var data_AboutPrintsArr = JSON.parse(response);
				
				data_AboutPrintsArr.print_details =JSON.parse(data_AboutPrintsArr.print_details);
				
                console.log('=evoke_calculator_directly=',response);

                printCalculator.type = (data_AboutPrintsArr.print_details.calculator_type)? data_AboutPrintsArr.print_details.calculator_type:'auto';
				printCalculator.currentCalculationData = {};
				printCalculator.currentCalculationData[printCalculator.type] = [];
				printCalculator.currentCalculationData[printCalculator.type][0] = data_AboutPrintsArr;
				
				
				printCalculator.currentCalculationData[printCalculator.type][0].dop_uslugi_id =  data_AboutPrintsArr.id;
			    if(typeof data.action !== 'undefined')  printCalculator.currentCalculationData[printCalculator.type][0].action = data.action;


			    if(typeof printCalculator.currentCalculationData[printCalculator.type].id !== 'undefined')
				
				if(typeof printCalculator.currentCalculationData[printCalculator.type].id !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].id;
				if(typeof printCalculator.currentCalculationData[printCalculator.type].type !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].type;
			
				printCalculator.dataObj_toEvokeCalculator = {};
				printCalculator.dataObj_toEvokeCalculator = data; //{"art_id":15431,"dop_data_row_id":3,"quantity":1};
				
				// здесь 0 устанавливается именно в виде строки, если установить числом то не будет работать
				// из-за проверки этого значения в начале build_print_calculator 
				// в условиии if(printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id)
				printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id = "0";
				printCalculator.dataObj_toEvokeCalculator.creator_id =  printCalculator.creator_id;
				
				// если это объединенный тираж 
				if(data_AboutPrintsArr.print_details.distribution_type && data_AboutPrintsArr.print_details.distribution_type == 'union'){
					if(data_AboutPrintsArr.print_details.calculator_type){
						// если  ручной или дежурный калькулятор
				        // получаем цены соответсвующие полному объему объединенного тиража
						if(data_AboutPrintsArr.print_details.calculator_type == 'manual' || data_AboutPrintsArr.print_details.calculator_type == 'free'){
							if(data_AboutPrintsArr.print_details.union_price){
							   printCalculator.currentCalculationData[printCalculator.type][0].price_in = data_AboutPrintsArr.print_details.union_price.in;
							   printCalculator.currentCalculationData[printCalculator.type][0].price_out = data_AboutPrintsArr.print_details.union_price.out;
							}
						}
						// если автоматический или ручной  калькулятор
				        // получаем количество иходя из объединенного тиража
						if(data_AboutPrintsArr.print_details.calculator_type == 'auto' || data_AboutPrintsArr.print_details.calculator_type == 'manual'){
							
							if(data_AboutPrintsArr.print_details.quantity_details){
								printCalculator.dataObj_toEvokeCalculator.quantity = 0;
								for(var i in data_AboutPrintsArr.print_details.quantity_details) { printCalculator.dataObj_toEvokeCalculator.quantity += parseInt(data_AboutPrintsArr.print_details.quantity_details[i]); }
								 printCalculator.currentCalculationData[printCalculator.type][0].quantity = printCalculator.dataObj_toEvokeCalculator.quantity;
							}
						}
						
						// если добавление тиража 
					    if(data.action=='attach'){
							printCalculator.currentCalculationData[printCalculator.type][0].id_for_attachment = data.id_for_attachment;
							printCalculator.currentCalculationData[printCalculator.type][0].attachment_quantity = data.attachment_quantity;
						}
					}
				}
				
				
				
				console.log('evoke_calculator_directly2',printCalculator.dataObj_toEvokeCalculator);
				console.log('evoke_calculator_directly3',printCalculator.currentCalculationData);
				printCalculator.evoke_calculator();
			  
				
			}
		}
		else{
			printCalculator.dataObj_toEvokeCalculator = data; //{"art_id":15431,"dop_data_row_id":3,"quantity":1};
			printCalculator.dataObj_toEvokeCalculator.creator_id =  printCalculator.creator_id;
		    printCalculator.evoke_calculator();
		}
		
	}
	,
	start_calculator:function(dataObj){
		//console.log(dataObj);
		
		if(dataObj.calculator_type == 'print'){
			// скидка 
			printCalculator.discount = dataObj.discount;
			
			// ДВА ЭТАПА
			// 1. отправляем запрос проверяющий есть ли расчеты дополнительных услуг для этого расчета
			//    если есть получаем в ответ массив с данными если нет получаем пустой массив
			// 2. если полученный массив имел данные выводим предварительное окно с указанием имеющихся
			//    расчетов дополнительных услуг, если массив был пустой вызываем метод запускающий калькулятор
			
			// этап 1
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&fetch_dop_uslugi_for_row='+dataObj.dop_data_row_id,'section');
			printCalculator.send_ajax(url,callback);
			function callback(response){ 
			    // alert(response);
				// этап 1
				if(typeof data_AboutPrintsArr !== 'undefined') delete data_AboutPrintsArr;
				var data_AboutPrintsArr = JSON.parse(response);
				printCalculator.dataObj_toEvokeCalculator = {"art_id":dataObj.art_id,"dop_data_row_id":dataObj.dop_data_row_id,"quantity":dataObj.quantity,"cell":dataObj.cell,"creator_id":dataObj.creator_id};
				
				if(data_AboutPrintsArr.length == 0){
					
					// запускаем калькулятор
					printCalculator.evoke_calculator();
				}
				else{
					// запускаем панель
					printCalculator.launch_dop_uslugi_panel(data_AboutPrintsArr);
				}
			}
		}
		else if(dataObj.calculator_type == 'extra'){
			/**
			 *	запрос окна добавления доп услуги
			 *  @author  Алексей	
			 *	@versoin 18:44 МСК 27.09.2015 		
			 */
			$.post(location.href+'&id='+dataObj.cell.parentNode.getAttribute("pos_id"), 
				{
					AJAX:"get_uslugi_list_Database_Html",
					quantity:dataObj.quantity,
					dop_row_id:dataObj.dop_data_row_id,
					art_id:dataObj.art_id,
					for_all:1,
					discount:dataObj.discount

				}, function(data, textStatus, xhr) {
					standard_response_handler(data);			
			},'json');
		}	
	}
	,
	cleanOutCalculator:function(){
		for(var prop in printCalculator){
			 //alert(typeof printCalculator[prop]);
			 if(typeof printCalculator[prop] != 'function')  delete printCalculator[prop];
		}
		$("#calculatorDialogBox").remove();
	}
	,
	launch_dop_uslugi_panel:function(data_AboutPrintsArr){
		console.log('>>> launch_dop_uslugi_panel start');
		console.log(data_AboutPrintsArr);
		console.log('<<< launch_dop_uslugi_panel end');
		//return;
		var box = document.createElement('DIV');
		box.id = "calculatorDopUslugiBox";
		//box.style.width = '300px';
		box.style.display = "none";
		
		var tbl = document.createElement('TABLE');
		tbl.style.borderCollapse = 'Collapse';
		tbl.style.width = '100%';
		var tr = document.createElement('TR');
		printCalculator.uslugi_panel_print_details = [];
		
		printCalculator.currentCalculationData = [];
		for(var i = 0; i < data_AboutPrintsArr.length; i++){
			var tr =  tr.cloneNode(false);
			var td = document.createElement('TD');
			td.style.border = '#CCC solid 1px';
			td.style.padding = '2px 4px';

            // сохраняем данные в общей переменной
			// предварительно трансофорировав сериализованную строку из свойства "print_details" в объект
			var print_details = JSON.parse(data_AboutPrintsArr[i].print_details);
			data_AboutPrintsArr[i].print_details = print_details;
			
			printCalculator.currentCalculationData[i] = data_AboutPrintsArr[i];
			printCalculator.currentCalculationData[i].dop_uslugi_id = data_AboutPrintsArr[i].id;

			if(typeof printCalculator.currentCalculationData[i].id !== 'undefined') delete printCalculator.currentCalculationData[i].id;
			if(typeof printCalculator.currentCalculationData[i].type !== 'undefined') delete printCalculator.currentCalculationData[i].type;

			
			td.innerHTML = i+1;
			
			//// console.log(printCalculator.currentCalculationData[printCalculator.type][i]);
			tr.appendChild(td);
			
			// тип нанесения
			var td =  td.cloneNode(false);
			td.innerHTML = printCalculator.currentCalculationData[i].print_details.print_type;
			td.setAttribute('index',i);
			td.setAttribute('dop_uslugi_id',printCalculator.currentCalculationData[i].dop_uslugi_id);
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			tr.appendChild(td);
			td.onclick = function(){ 
				// запускаем калькулятор для конкретного нанесения
				$("#calculatorDopUslugiBox").remove();
				// добавляем в передаваемые данные данные индекс массива printCalculator.currentCalculationData содержащего
				// данные конкретного(этого)нанесения
				printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id = this.getAttribute('index');
				printCalculator.dataObj_toEvokeCalculator.dop_uslugi_id = this.getAttribute('dop_uslugi_id');
				// запускаем
				printCalculator.evoke_calculator();
			}
			
			// место нанесения
			var td =  td.cloneNode(false);
			td.style.textDecoration = 'none';
			td.innerHTML = printCalculator.currentCalculationData[i].print_details.place_type;
			tr.appendChild(td);
			
			// сумма
			var summ = printCalculator.currentCalculationData[i].price_out*printCalculator.currentCalculationData[i].quantity;
			var td =  td.cloneNode(false);
			td.style.width = '100px';
			td.style.textAlign = 'right';
			td.innerHTML = (Math.round(summ * 100) / 100).toFixed(2) +' р.';
			tr.appendChild(td);
			
			// скидка
			var discount = printCalculator.currentCalculationData[i].discount;
			var td =  td.cloneNode(false);
			td.style.width = '34px';
			td.innerHTML = discount +'%';
			tr.appendChild(td);
			
			// сумма со скидкой
			var itogo = (discount != 0 )? (summ/100)*(100 + parseInt(discount)) : summ;
			var td =  td.cloneNode(false);
			td.style.width = '100px';
			td.style.textAlign = 'right';
			td.innerHTML = (Math.round(itogo * 100) / 100 ).toFixed(2) +' р.';
			tr.appendChild(td);
			
			var td =  td.cloneNode(false);
			td.style.width = '100px';
			td.innerHTML = 'Удалить нанесение';
			td.style.textDecoration = 'underline';
			td.style.textAlign = 'left';
			td.style.cursor = 'pointer';
			td.setAttribute('usluga_id',data_AboutPrintsArr[i].dop_uslugi_id);
			td.onclick = function(){ 
			
				// отправляем запрос на удаление для текущего нанесения
				var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&delete_prints_for_row='+printCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&usluga_id='+this.getAttribute('usluga_id'),'section');
				printCalculator.send_ajax(url,callback);
			
				function callback(response){ 
				    // alert(response);
					$("#calculatorDopUslugiBox").remove();
					location.reload();
				}
			}
			
			tr.appendChild(td);
			tbl.appendChild(tr);
			
		}
		box.appendChild(tbl);
		
		
		var BtnsDiv = document.createElement('DIV');
		BtnsDiv.className = 'BtnsDiv';
	    // кнопка добавления нового нанесения
		var addNewPrintBtn = document.createElement('DIV');
		addNewPrintBtn.id = 'calculatorAddNewPrintBtn';
		addNewPrintBtn.innerHTML = 'Добавить еще место';
		addNewPrintBtn.onclick =  function(){ 
		    $("#calculatorDopUslugiBox").remove();
		    printCalculator.evoke_calculator();
	    };
		BtnsDiv.appendChild(addNewPrintBtn);

		
		// кнопка удаления всех нанесений
		var deleteAllPrinstBtn = document.createElement('DIV');
		deleteAllPrinstBtn.id = 'calculatorDeleteAllPrinstBtn';
		deleteAllPrinstBtn.innerHTML = 'Удалить все места печати';
		deleteAllPrinstBtn.onclick =  function(){ 
		    $("#calculatorDeleteAllPrinstBtn").remove();
			
			// отправляем запрос на удаление всех нанесений для текущего расчета
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&delete_prints_for_row='+printCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&all=true','section');
		    printCalculator.send_ajax(url,callback);
		
			function callback(response){ 
				$("#calculatorDopUslugiBox").remove();
				location.reload();
			}
	    };
		BtnsDiv.appendChild(deleteAllPrinstBtn);
		box.appendChild(BtnsDiv);
		
		
		
		document.body.appendChild(box);
		// открываем панель
		$("#calculatorDopUslugiBox").dialog({autoOpen: false, position:{ at: "top+35%", of: window } ,title: "Печать для этой позиции",modal:true,width: 730,close: function() {$(this).remove(); printCalculator.cleanOutCalculator();}});
		$("#calculatorDopUslugiBox").dialog("open");
	}
	,
	evoke_calculator:function(){

			// устанавливаем уровень цен      
			if(typeof printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id  === 'undefined'){
				printCalculator.level = (document.getElementById('calcLevelStorage'))? document.getElementById('calcLevelStorage').value:'full';
			}
			else{
				if(typeof printCalculator.currentCalculationData[printCalculator.type][printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id].print_details.level !== 'undefined'){
				     printCalculator.level = printCalculator.currentCalculationData[printCalculator.type][printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id].print_details.level;
				}
				else printCalculator.level = 'full';
			}

			printCalculator.levelsRU = {'full':' уровень - "Конечные клиенты"','ra':', уровень - "Рекламные Агентства"'};/**/
		
		// отправляем запрос чтобы получить описание параметров дефолтных параметров калькулятора для данного ариткула
	    var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&grab_calculator_data={"art_id":"'+printCalculator.dataObj_toEvokeCalculator.art_id+'","type":"print","level":"'+printCalculator.level+'"}','section');
		printCalculator.send_ajax(url,callback);
		//alert(url);
		function callback(response_calculatorParamsData){
			// alert(response_calculatorParamsData);
			// return;
			if(typeof printCalculator.calculatorParamsObj !== 'undefined') delete printCalculator.calculatorParamsObj;
	
            printCalculator.calculatorParamsObj = JSON.parse(response_calculatorParamsData);
			
			console.log('>>> ИЗНАЧАЛЬНЫЕ ПАРАМЕТРЫ КАЛЬКУЛЯТОРА (ПО ДЕФОЛТУ) то из чего мы выбираем при первом открытии');
	   	    console.log(' printCalculator.calculatorParamsObj',printCalculator.calculatorParamsObj);
		    console.log('<<< ИЗНАЧАЛЬНЫЕ ПАРАМЕТРЫ КАЛЬКУЛЯТОРА (ПО ДЕФОЛТУ)');
			
			// строим калькулятор
			printCalculator.build_print_calculator();

		}
	}
	,
	changeMenu:function(cell){
		
		// получаем объект кнопок и проходим по нему в цикле
		var divsArr = cell.parentNode.getElementsByTagName('DIV');
		var ln = divsArr.length;
		for(var i = 0;i < divsArr.length;i++){ 
			if(divsArr[i].getAttribute('calc_type')) divsArr[i].className = "calculatorMenuCell pointer";
		}
		cell.className += " deepGreyBg";
		// проходим по id контейнеров калькудяторов

        printCalculator.type = cell.getAttribute('calc_type');
		
		// если переходим в закладку auto то удаляем (если существуюет )manualCalcBox
		if(printCalculator.type=='auto' && document.getElementById("manualCalcBox")){
			//alert(1);
		    document.getElementById("manualCalcBox").parentNode.removeChild(document.getElementById("manualCalcBox"));
		}
		
		for(var type in printCalculator.calcTypes){
			// alert(type+' - '+cur_type);
			if(type==printCalculator.type){
				if(document.getElementById(type+'CalcBox')) document.getElementById(type+'CalcBox').style.display = 'block';
			}
			else{
				if(document.getElementById(type+'CalcBox')) document.getElementById(type+'CalcBox').style.display = 'none';
			}
		}
	}
	,
	build_print_calculator:function(){
		
		console.log('>>> build_print_calculator');
	   	console.log(printCalculator.calculatorParamsObj);
		console.log('<<< build_print_calculator');
		
		console.log('<<< typeof'+typeof printCalculator.type);

		if(typeof printCalculator.type === 'undefined') printCalculator.type = 'auto';
		
		var calcTypes = {auto:{title:'Автоматический'},manual:{title:'Ручной'},free:{title:'Дежурная услуга'}};
		printCalculator.calcTypes = calcTypes;
		
		// общее окно-контейнер
		var dialogBox = document.createElement('DIV');
		dialogBox.id = "calculatorDialogBox";
		dialogBox.className = "calculatorDialogBox";
		dialogBox.style.display = "none";
		
		// строим верхнее меню (переключатели между типами калькуляторов) //
		var menuContainer = document.createElement('DIV');
		menuContainer.className = "menuContainer";
		var btnTpl = document.createElement('DIV');
		btnTpl.className = "calculatorMenuCell pointer";
		
		for(var type in calcTypes){
			var btn = btnTpl.cloneNode(true);
			btn.innerHTML = calcTypes[type].title;
			btn.id = 'topMenuBtn'+type;
			btn.setAttribute('calc_type',type);
		    btn.onclick = function(){ 
		    	
			    printCalculator.changeMenu(this);
				printCalculator['load_'+this.getAttribute('calc_type')+'_calc'](this.getAttribute('calc_type')); 
			}
			if(type==printCalculator.type) $(btn).addClass('deepGreyBg');
			menuContainer.appendChild(btn);
		}
		var infoField = document.createElement('DIV');
		infoField.id = "quantityInfoField";
		infoField.className = "quantityInfoField";
		if(printCalculator.type!='free') infoField.innerHTML = printCalculator.dataObj_toEvokeCalculator.quantity+' шт.';
		//alert(printCalculator.dataObj_toEvokeCalculator.quantity);
		//infoField.innerHTML = "Тираж  шт.";
		menuContainer.appendChild(infoField);
		dialogBox.appendChild(menuContainer);
		// конец меню //
		
		// контейнер для калькуляторов
		printCalculator.commonContainer = document.createElement('DIV');
	    dialogBox.appendChild(printCalculator.commonContainer);
		
		console.log('>>> build_print_calculator2'+printCalculator.type);
		// загружаем текуший калькулятор
		printCalculator['load_'+printCalculator.type+'_calc'](printCalculator.type);
	  
		console.log('>>> build_print_calculator3');
		// открываем окно с калькулятором
		document.body.appendChild(dialogBox);
		if(printCalculator.type=='manual' || printCalculator.type=='free'){
			// запускаем обсчет
			printCalculator.noneAutoCalcProcessing($('#itogDisplayTbl input[name=price_in]')[0]);
			printCalculator.noneAutoCalcProcessing($('#itogDisplayTbl input[name=price_out]')[0]);
		}
		
		$(dialogBox).dialog({autoOpen: false, position:{ at: "top+25%", of: window } ,title: "Расчет нанесения логотипа "+printCalculator.levelsRU[printCalculator.level],modal:true,width: 680,close: function() { this.remove(); printCalculator.cleanOutCalculator(); }});
		$(dialogBox).dialog("open");
	}
	,
	load_auto_calc:function(type){
		// alert('load_'+printCalculator.type+'_calc');
		printCalculator.type = type;

		$('#quantityInfoField').show();
		
		var boxId = printCalculator.type+'CalcBox';
		if(document.getElementById(boxId) === null){

			// если калькулятор был вызван для существующего нанесения пересохраняем данные для конкретного нанесения 
			// иначе готовим структуру для сохранения данных при создании калькулятора
			if(printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id){
				//console.log('-1-',printCalculator.currentCalculationData);
				var currentCalculationData =  printCalculator.currentCalculationData[printCalculator.type][printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
				printCalculator.currentCalculationData = {};	
				printCalculator.currentCalculationData[printCalculator.type] = currentCalculationData;	
				printCalculator.currentCalculationData[printCalculator.type].dop_data_row_id = printCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
				printCalculator.currentCalculationData[printCalculator.type].creator_id = printCalculator.dataObj_toEvokeCalculator.creator_id;
	            delete printCalculator.currentCalculationData[printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
				if(typeof printCalculator.currentCalculationData[printCalculator.type].dop_row_id !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].dop_row_id;
				if(printCalculator.currentCalculationData['auto']){
					if(typeof printCalculator.currentCalculationData[printCalculator.type].price_in !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].price_in;
				    if(typeof printCalculator.currentCalculationData[printCalculator.type].price_out !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].price_out;
				}
				printCalculator.currentCalculationData[printCalculator.type]['total_price_in'] = 0;
				printCalculator.currentCalculationData[printCalculator.type]['total_price_out'] = 0;
			}
			else if(printCalculator.type == 'manual' && printCalculator.currentCalculationData['auto']){
				 if(printCalculator.currentCalculationData['manual']){
					 // если при переключении между вкладками место нанесения и тип нанесения не изменились, пересохраняем данные 
					 // ручного расчета (данные о цене)
					 if(printCalculator.currentCalculationData['manual'].print_details.print_id == printCalculator.currentCalculationData['auto'].print_details.print_id && printCalculator.currentCalculationData['manual'].print_details.place_id == printCalculator.currentCalculationData['auto'].print_details.place_id){
						 //alert(1);
						 var temp_data = {'price_in':printCalculator.currentCalculationData['manual'].price_in,'price_out':printCalculator.currentCalculationData['manual'].price_out,'total_price_out':printCalculator.currentCalculationData['manual'].total_price_out,'total_price_in':printCalculator.currentCalculationData['manual'].total_price_in}
					 }
				 }
				  
				 // присваиваем значение (клона объекта)
				 printCalculator.currentCalculationData[type] =  $.extend(true, {},printCalculator.currentCalculationData['auto']);
				 printCalculator.currentCalculationData[type]['price_in'] = (temp_data)? temp_data['price_in']: 0 ;
				 printCalculator.currentCalculationData[type]['price_out'] =(temp_data)? temp_data['price_out']: 0 ;
				 printCalculator.currentCalculationData[type]['total_price_in'] =(temp_data)? temp_data['total_price_in']: 0 ;
				 printCalculator.currentCalculationData[type]['total_price_out'] =(temp_data)? temp_data['total_price_out']: 0 ;
			}
			else{
				printCalculator.currentCalculationData = {};	
				printCalculator.currentCalculationData[printCalculator.type] =  {};	
				printCalculator.currentCalculationData[printCalculator.type].quantity = printCalculator.dataObj_toEvokeCalculator.quantity;
				printCalculator.currentCalculationData[printCalculator.type].creator_id = printCalculator.dataObj_toEvokeCalculator.creator_id;
				printCalculator.currentCalculationData[printCalculator.type].print_details = {};
				printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type = printCalculator.dataObj_toEvokeCalculator.distribution_type;
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids = printCalculator.dataObj_toEvokeCalculator.dop_data_ids;
				
				printCalculator.currentCalculationData[printCalculator.type].action = printCalculator.dataObj_toEvokeCalculator.action;
				printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details = printCalculator.dataObj_toEvokeCalculator.quantity_details;
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params = {};
		        //printCalculator.currentCalculationData[printCalculator.type].discount = (typeof printCalculator.discount !== 'undefined')? printCalculator.discount:0;
				if(typeof printCalculator.discount !== 'undefined'){
					printCalculator.currentCalculationData[printCalculator.type].discount = printCalculator.discount;
				}
				else printCalculator.currentCalculationData[printCalculator.type].discount = 0;
			}
			
			if(printCalculator.dataObj_toEvokeCalculator.dop_uslugi_id) printCalculator.currentCalculationData[printCalculator.type].dop_uslugi_id = printCalculator.dataObj_toEvokeCalculator.dop_uslugi_id;
			
			console.log('>>> ДАННЫЕ КОНКРЕТНОГО ТЕКУЩЕГО РАСЧЕТА (если это новый расчет то это просто подготовленный для заполнения праметрами скелет, если это открыт существующий расчет то тогда он содержит его праметры)');
			console.log(' printCalculator.currentCalculationData',printCalculator.currentCalculationData);
			console.log('<<< ДАННЫЕ КОНКРЕТНОГО ТЕКУЩЕГО РАСЧЕТА');
			// console.log('>>> ИЗНАЧАЛЬНЫЕ ПАРАМЕТРЫ КАЛЬКУЛЯТОРА (ПО ДЕФОЛТУ) то из чего мы выбираем при первом открытии', printCalculator.calculatorParamsObj,'<<<  ИЗНАЧАЛЬНЫЕ ПАРАМЕТРЫ КАЛЬКУЛЯТОРА (ПО ДЕФОЛТУ)');
			// строим интерфейс калькулятора

       
			// контейнер для главного окна калькулятора
			var mainCalculatorBox = document.createElement('DIV');
			mainCalculatorBox.className = "mainCalculatorBox";
			mainCalculatorBox.id = boxId;
			
			//
			var box = document.createElement('DIV');
			box.className = "calculatorBodyBox";
			box.id = printCalculator.type+"CalcBodyBox";
			
			var printPlaceSelectDiv = document.createElement('DIV');
			printPlaceSelectDiv.className = "printPlaceSelectDiv";
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Место: ';
			
			var clear_div = document.createElement('DIV');
			clear_div.className = "clear_div";
			
			printPlaceSelectDiv.appendChild(title);
			// строим select выбора мест нанесений
			var printPlaceSelect = document.createElement('SELECT');
			var labelPlaces = document.createElement('LABEL');
			labelPlaces.className = 'select_label';
			
			for(var id in printCalculator.calculatorParamsObj.places){
				// если это заново запускаемый калькулятор сохраняем id первого места нанесения 
				if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.place_id === 'undefined') printCalculator.currentCalculationData[printCalculator.type].print_details.place_id = id;
			   
				var option = document.createElement('OPTION');
				option.setAttribute("value",id);
				option.appendChild(document.createTextNode(printCalculator.calculatorParamsObj.places[id].name));
				printPlaceSelect.appendChild(option);
				
				if(printCalculator.currentCalculationData[printCalculator.type].print_details.place_id==id) option.setAttribute("selected",true);
				//// console.log(i + printCalculator.dataObj_toEvokeCalculator.places[i].name);
			}
			//currPlace_id = 1;
			printPlaceSelect.onchange = function(){
				//alert(printCalculator.type);
				
				
				if(document.getElementById(printCalculator.type+"CalcBlockA")){
					document.getElementById(printCalculator.type+"CalcBlockA").parentNode.removeChild(document.getElementById(printCalculator.type+"CalcBlockA"));
				}
				if(document.getElementById(printCalculator.type+"CalcItogDisplay")) document.getElementById(printCalculator.type+"CalcItogDisplay").innerHTML = '';
				//alert(this.options[this.selectedIndex].value);
				console.log('printPlaceSelect.onchange',printCalculator.currentCalculationData[printCalculator.type]);
				//
				var dop_data_ids = distribution_type = quantity_details = false;
				if(typeof printCalculator.currentCalculationData[printCalculator.type] !== 'undefined'){
					if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details !== 'undefined'){
						if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids !== 'undefined'){
							dop_data_ids = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids;
						}
						if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type !== 'undefined'){
							distribution_type = printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type;
						}
						if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details !== 'undefined'){
							quantity_details = printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details;
						}
					}
				}
				if(typeof printCalculator.currentCalculationData[printCalculator.type] === 'undefined')printCalculator.currentCalculationData[printCalculator.type] = {};
				
				printCalculator.currentCalculationData[printCalculator.type].print_details = {};
				if(dop_data_ids) printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids = dop_data_ids;
				if(distribution_type) printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type = distribution_type;
				if(quantity_details) printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details = quantity_details;
				
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params = {};
				// определяем id места нанесения
				printCalculator.currentCalculationData[printCalculator.type].print_details.place_id = parseInt(this.options[this.selectedIndex].value);
				// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.place_id);
				// создаем новый block_A
				 var block_A = printCalculator.buildBlockA();
				
				document.getElementById(printCalculator.type+"CalcBodyBox").appendChild(block_A);
				if(printCalculator.type=='auto' && printCalculator.makeProcessingFlag) printCalculator.makeProcessing();
				else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
			}
			console.log(printPlaceSelect);
			var elementsBox = document.createElement('DIV');
			elementsBox.className = "calculatorElementsBox";
	
			labelPlaces.appendChild(printPlaceSelect);
			elementsBox.appendChild(labelPlaces);
			printPlaceSelectDiv.appendChild(elementsBox);
			printPlaceSelectDiv.appendChild(clear_div.cloneNode(true));
			mainCalculatorBox.appendChild(printPlaceSelectDiv);
			
			// создаем блок block_A который будет содеражать в себе select выбора типа нанесения
			// и блок block_B содержащий в себе все остальные элементы интерфейса
			if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.place_id !== 'undefined' ){
				// если остался print_id от предыдущего запуска калькулятора удаляем его
				var block_A = printCalculator.buildBlockA();
				box.appendChild(block_A);
			}
			else{
				box.appendChild(document.createTextNode("Ошибка: не определен ID места нанесения "));
				
			}
			/**/
			mainCalculatorBox.appendChild(box);
		    
			printCalculator.currentCalculationData[type].mainCalculatorBox = mainCalculatorBox;
			printCalculator.commonContainer.appendChild(mainCalculatorBox);
			if(printCalculator.type=='auto' && printCalculator.makeProcessingFlag) printCalculator.makeProcessing();
			else if(printCalculator.type=='manual' && printCalculator.makeProcessingFlag) printCalculator.showCalcItogDisplay();
			
		}
		
	}
	,
	load_manual_calc:function(type){
		printCalculator.type = type;
		printCalculator.load_auto_calc(type);
	}
	,
	load_free_calc:function(type){

		printCalculator.type = type;
		// alert('load_'+printCalculator.type+'_calc');
		
		$('#quantityInfoField').hide();
		// если его еще нет строим контейнер калькулятора
		var boxId = printCalculator.type+'CalcBox';
		if(document.getElementById(boxId) === null){
			
			
			
			
			
			if(typeof printCalculator.currentCalculationData === 'undefined') printCalculator.currentCalculationData = {};
			
			if(printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id){

				var currentCalculationData =  printCalculator.currentCalculationData[type][printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
			
				printCalculator.currentCalculationData[type] = currentCalculationData;	
				printCalculator.currentCalculationData[type].dop_data_row_id = printCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
			    printCalculator.currentCalculationData[type].creator_id = printCalculator.dataObj_toEvokeCalculator.creator_id;
				printCalculator.currentCalculationData[type].action = printCalculator.dataObj_toEvokeCalculator.action;
				printCalculator.currentCalculationData[type].quantity = parseInt(currentCalculationData.print_details.quantity);
				printCalculator.currentCalculationData[type]['price_in'] = parseFloat(currentCalculationData.price_in);
				printCalculator.currentCalculationData[type]['price_out'] = parseFloat(currentCalculationData.price_out);
				printCalculator.currentCalculationData[type].discount =  parseFloat(currentCalculationData.discount);
				printCalculator.currentCalculationData[type]['total_price_in'] = 0;
				printCalculator.currentCalculationData[type]['total_price_out'] = 0;
				if(printCalculator.currentCalculationData[type].tz && printCalculator.currentCalculationData[type].tz!=''){
					// ВООБЩЕТО НАВЕРНО лучще переделать систему и кодировать сразу здесь чтобы не было путаницы и данные 
					// всегда отправлялись на сервер в кодировке
					printCalculator.currentCalculationData[type].tz = Base64.decode(printCalculator.currentCalculationData[type].tz);
					printCalculator.currentCalculationData[type].print_details.comment = printCalculator.currentCalculationData[type].tz;
					var comment = printCalculator.currentCalculationData[type].tz;
				}
				printCalculator.currentCalculationData[type].print_details.commentForClient = Base64.decode(printCalculator.currentCalculationData[type].print_details.commentForClient);
				
	           // delete printCalculator.currentCalculationData[printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
				delete printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id;
				// console.log('===+++===',printCalculator.currentCalculationData[type]);
				
			}
			else{
			
				printCalculator.currentCalculationData[type] = {};
				printCalculator.currentCalculationData[type].print_details = {};
				printCalculator.currentCalculationData[type].print_details.place_type = 'Дежурная услуга';
				printCalculator.currentCalculationData[type].print_details.print_type = '';
				printCalculator.currentCalculationData[type].print_details.comment = '';
				printCalculator.currentCalculationData[type].print_details.commentForClient = '';
				printCalculator.currentCalculationData[type].print_details.supplier = '';
				// если что можно пересохранить в .print_details в методе saveCalculatorResult
				printCalculator.currentCalculationData[type].print_details.distribution_type = printCalculator.dataObj_toEvokeCalculator.distribution_type;
				printCalculator.currentCalculationData[type].print_details.quantity_details = printCalculator.dataObj_toEvokeCalculator.quantity_details;
				printCalculator.currentCalculationData[type].print_details.dop_data_ids = printCalculator.dataObj_toEvokeCalculator.dop_data_ids;
				printCalculator.currentCalculationData[type].creator_id = printCalculator.dataObj_toEvokeCalculator.creator_id;
				printCalculator.currentCalculationData[type].action = printCalculator.dataObj_toEvokeCalculator.action;
				printCalculator.currentCalculationData[type].discount = (typeof printCalculator.discount !== 'undefined')? printCalculator.discount:0;
				printCalculator.currentCalculationData[type].quantity = printCalculator.dataObj_toEvokeCalculator.quantity;
				printCalculator.currentCalculationData[type]['price_in'] = 0;
				printCalculator.currentCalculationData[type]['price_out'] = 0;
				printCalculator.currentCalculationData[type]['total_price_in'] = 0;
				printCalculator.currentCalculationData[type]['total_price_out'] = 0;
			}
			
			if(printCalculator.dataObj_toEvokeCalculator.dop_uslugi_id) printCalculator.currentCalculationData[printCalculator.type].dop_uslugi_id = printCalculator.dataObj_toEvokeCalculator.dop_uslugi_id;
			
			console.log('777',printCalculator.currentCalculationData[type]);
			
			var calcBox = document.createElement('DIV');
			calcBox.id = boxId;
			calcBox.className = boxId;
			
			// шаблоны
			var plankTpl = document.createElement('DIV');
			plankTpl.className = "dopParametrsTitle";
			var inputTpl = document.createElement('INPUT');
			inputTpl.className = "input";
			var textareaTpl = document.createElement('TEXTAREA');
			
			// первый блок
			var plank = plankTpl.cloneNode(true);
		    plank.innerHTML = 'название услуги';
		    calcBox.appendChild(plank);
			
			var input = inputTpl.cloneNode(true);
			input.name = 'print_type';
			input.value = printCalculator.currentCalculationData[type].print_details.print_type;
			input.onchange = function(){
				printCalculator.currentCalculationData[printCalculator.type].print_details[this.name] = this.value;
			}
			input.className = 'usluga';
		    calcBox.appendChild(input);
			
			var input = inputTpl.cloneNode(true);
		    input.value = printCalculator.currentCalculationData[type].quantity;
			input.onfocus = function(){
				if(this.value == '0') this.value ='';
			}
			input.onkeyup = function(){
			   if(this.value != '' && this.value != '&nbsp;'){ 
					var result = printCalculator.correctToInt(this,this.value);
					if(result != 0) printCalculator.setCaretToPos(this,result);
				}
				printCalculator.currentCalculationData[printCalculator.type].quantity = this.value;
				printCalculator.noneAutoCalcProcessing($('#itogDisplayTbl input[name=price_in]')[0]);
				printCalculator.noneAutoCalcProcessing($('#itogDisplayTbl input[name=price_out]')[0]);
			}
			input.name = 'quantity';
			input.className = 'quantity';
			var label =  document.createElement('LABEL');
			label.className = 'quantity_label';
			label.appendChild(input);
		    calcBox.appendChild(label);
			
			// второй блок
			var plank = plankTpl.cloneNode(true);
		    plank.innerHTML = 'подробное описание для клиента';
		    calcBox.appendChild(plank);
			
			var textarea = textareaTpl.cloneNode(true);
			textarea.className = 'commentForClient';
			textarea.name = 'commentForClient';
			
			var commentForClient = printCalculator.currentCalculationData[type].print_details.commentForClient;
			textarea.value = commentForClient.replace(/<br \/>/g,"\r");

			textarea.onchange = function(){
				printCalculator.currentCalculationData[printCalculator.type].print_details[this.name] = this.value;
			}
		    calcBox.appendChild(textarea);
			             
			// третий блок
			var plank = plankTpl.cloneNode(true);
		    plank.innerHTML = '<div class="sub_cap left">внутренний комментарий</div><div class="sub_cap right">поставщик</div><div class="clear"></div>';
		    calcBox.appendChild(plank);
			
			var textarea = textareaTpl.cloneNode(true);
			textarea.className = 'comment';
			textarea.name = 'comment';
			textarea.value = printCalculator.currentCalculationData[type].print_details.comment;
			textarea.onchange = function(){
				printCalculator.currentCalculationData[printCalculator.type].print_details[this.name] = this.value;
			}
		    calcBox.appendChild(textarea);
			
			var input = inputTpl.cloneNode(true);
			input.name = 'supplier';
			input.className = 'supplier';
			input.value = printCalculator.currentCalculationData[type].print_details.supplier;
			input.onchange = function(){
				printCalculator.currentCalculationData[printCalculator.type].print_details[this.name] = this.value;
			}
		    calcBox.appendChild(input);
			             
			var div = document.createElement('DIV');
		    div.style.clear = 'both';
		    calcBox.appendChild(div);
			
			// добавляем контейнер в калькулятор
			printCalculator.currentCalculationData[type].mainCalculatorBox = calcBox;
			printCalculator.commonContainer.appendChild(calcBox);
			                
		}
		printCalculator.showCalcItogDisplay();		
	}
	,
	buildBlockA:function(){
		
		// метод строит блок block_A и взависимости от ситуации
		// или ( строит и вставляет в него block_B ) или ( не делает этого )
		// block_A содержит селект с выбором типа нанесения
		
		var block_A = document.createElement('DIV');
		block_A.id = printCalculator.type+"CalcBlockA";
		block_A.className = 'printCalculatorBlockA';
		
       
		// вызваем метод строящий  select для типов нанеснения
		// передаем ему id первого места нанесения из printPlaceSelect
		// он возвращает select и id типа нанесения первого в списке select
		var printTypesSelect = printCalculator.build_print_types_select();
		var labelTypes = document.createElement('LABEL');
		labelTypes.className = 'select_label';  
		var elementsBox = document.createElement('DIV');
		elementsBox.className = "calculatorElementsBox";
		
		var printTypesSelectDiv = document.createElement('DIV');
		printTypesSelectDiv.className = "printTypesSelectDiv";

        var title = document.createElement('DIV');
		title.className = "calculatorTitle";
		title.innerHTML = 'Вид: ';
	    var clear_div = document.createElement('DIV');
		clear_div.className = "clear_div";
		
		printTypesSelectDiv.appendChild(title);
		labelTypes.appendChild(printTypesSelect);
		elementsBox.appendChild(labelTypes);
		printTypesSelectDiv.appendChild(elementsBox);
		printTypesSelectDiv.appendChild(clear_div);
		block_A.appendChild(printTypesSelectDiv);
		
		
		// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.print_id);
		// если мы имеем конкретное типа нанесения (тоесть оно не равно 0) тогда строим калькулятор дальше
		// вызываем метод строящий блок В калькулятора и вставляем его в тело калькулятора
		if(printCalculator.currentCalculationData[printCalculator.type].print_details.print_id != 0){	
		    var block_B = printCalculator.buildBlockB();
		    block_A.appendChild(block_B);
		}

		return block_A;
	}
	,
    build_print_types_select:function(){
		
		// строит и возвращает select для типов нанеснения
		
		var printTypesSelect = document.createElement('SELECT');
		
		var counter = 0;
		// проходим по массиву содержащему id и названия типов нанесения соответствующих данному месту нанесения
		for(var id in printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData[printCalculator.type].print_details.place_id].prints){
			// если это заново запускаемый калькулятор сохраняем id первого  нанесения 
			if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.print_id === 'undefined') printCalculator.currentCalculationData[printCalculator.type].print_details.print_id = id;
			counter++;
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData[printCalculator.type].print_details.place_id].prints[id]));
            printTypesSelect.appendChild(option);
			//// console.log(i + data_obj.places[i].name);
			if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.print_id !== 'undefined'){
			    if(printCalculator.currentCalculationData[printCalculator.type].print_details.print_id==id) option.setAttribute("selected",true);
			}
		}
		
		// если типов нанесения было больше чем одно вставляем в начало Selectа option ' -- выберите вариант -- '
		// и устанавливаем значение описывающиее id типа нанесения в 0
		// id типа нанесения передается в вызывающий метод для дальнейшего построения калькулятора 
		if(counter>1){
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -выберите вариант- '));
			printTypesSelect.insertBefore(option, printTypesSelect.firstChild); 
            
			// если это не был вызов калькулятора для конкретного существующего расчета 
			// ставим выбранным  option ' -- выберите вариант -- '
			// и устанавливаем print_id = 0
			if(printCalculator.type == 'auto'){
				if(typeof printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id === 'undefined'){
					option.setAttribute("selected",true);
					printCalculator.currentCalculationData[printCalculator.type].print_details.print_id = 0;
				}
				// удаляем больше он нам не понадобится 
				delete printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id;
			}
		}

		
		// обработчик события onchange
		printTypesSelect.onchange = function(){
			
			if(document.getElementById(printCalculator.type + 'CalcBlockB'))document.getElementById(printCalculator.type + 'CalcBlockB').parentNode.removeChild(document.getElementById(printCalculator.type + 'CalcBlockB'));
			if(document.getElementById(printCalculator.type+"CalcItogDisplay")) document.getElementById(printCalculator.type+"CalcItogDisplay").innerHTML = '';
			var place_id  = printCalculator.currentCalculationData[printCalculator.type].print_details.place_id;
			
			
			var dop_data_ids = distribution_type = quantity_details = false;
			if(typeof printCalculator.currentCalculationData[printCalculator.type] !== 'undefined'){
				if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details !== 'undefined'){
					if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids !== 'undefined'){
						dop_data_ids = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids;
					}
					if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type !== 'undefined'){
						distribution_type = printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type;
					}
					if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details !== 'undefined'){
						quantity_details = printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details;
					}
				}
			}
			if(typeof printCalculator.currentCalculationData[printCalculator.type] === 'undefined')printCalculator.currentCalculationData[printCalculator.type] = {};
			
			
			printCalculator.currentCalculationData[printCalculator.type].print_details = {};
			if(dop_data_ids) printCalculator.currentCalculationData[printCalculator.type].print_details.dop_data_ids = dop_data_ids;
			if(distribution_type) printCalculator.currentCalculationData[printCalculator.type].print_details.distribution_type = distribution_type;
			if(quantity_details) printCalculator.currentCalculationData[printCalculator.type].print_details.quantity_details = quantity_details;
			printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params = {};
			printCalculator.currentCalculationData[printCalculator.type].print_details.place_id = place_id;
			printCalculator.currentCalculationData[printCalculator.type].print_details.print_id = this.options[this.selectedIndex].value;
			var block_B = printCalculator.buildBlockB();
			
			document.getElementById(printCalculator.type+"CalcBlockA").appendChild(block_B);
			// метод осуществляющий итоговый расчет 
		    // и помещающий итоговые данные в сторку ИТОГО
		    if(printCalculator.type=='auto') printCalculator.makeProcessing();
			else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
			
		}
		if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.print_id === 'undefined'){
		    var printTypesSelect = document.createElement('DIV');
			printTypesSelect.appendChild(document.createTextNode("Ошибка: не определен ID типа нанесения "));
			
		}
		
		return printTypesSelect;
	}
	,
	buildBlockB:function(){
		
		var blockB = document.createElement('DIV');
		blockB.id = printCalculator.type + 'CalcBlockB';
		blockB.className = 'printCalculatorBlockB';
		var br = document.createElement('BR');
		// выбираем данные выбранного нанесения и выводим их в калькулятор
		var currRrintParams = printCalculator.setCurrPrintParams();

		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(currRrintParams);
	
		
		// если был построен blockB то по окончании вывода калькулятора в поток можно запускать 
		// printCalculator.makeProcessing() и подгружать Итоговые суммы
		printCalculator.makeProcessingFlag = true; 
		
		return blockB;
	}
	,
	setCurrPrintParams:function(){
		
        // console.log('>>> setCurrPrintParams');
		// console.log(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id]);
		// console.log('<<< setCurrPrintParams');
		
		var clear_div = document.createElement('DIV');
	    clear_div.className = "clear_div";
		
		var printParamsBox = document.createElement('DIV');
		
		// определяем переменную содержащую массив данных относящихся к текущему типу нанесения
		var CurrPrintTypeData = printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id];
		
		// select для возможных цветов, в принипе этот селект даже должен быть не для цветов а для любого параметра 
		// который определяется по вертикали в таблице прайса 
        //                  [цвет] => Array   
        //                        ([белый] => Array([percentage] => 1.00 )
		//						   [серебро] => Array([percentage] => 1.20 ))

	  
		
        if(CurrPrintTypeData['y_price_param']){
		    // содержит всё название подарздела контейнеры для селектов ЦМИКов и т.д.
			var YPriceParamDivContainer = document.createElement('DIV');
            YPriceParamDivContainer.className = 'YPriceParamDivContainer';
			
			// содержит селекты в обертках
			var YPriceParamDiv = document.createElement('DIV');
            YPriceParamDiv.id = 'YPriceParamDiv';
			YPriceParamDiv.className = 'YPriceParamDiv';
			
			// содержит ЦМИКи
			var YPriceParamCMYKdiv = document.createElement('DIV');
			YPriceParamCMYKdiv.className = 'YPriceParamCMYKdiv';
			
			// содержит поле ввода ЦМИКа
			var YPriceParamCMYK = document.createElement('DIV');
			YPriceParamCMYK.className = 'YPriceParamCMYK hidden';
			YPriceParamCMYK.setAttribute("contenteditable",true);
			YPriceParamCMYK.onblur =  printCalculator.onblurCMYK;
			
			var YPriceParamSelect = document.createElement('SELECT');
			var labelYPriceParams = document.createElement('LABEL');
			labelYPriceParams.className = 'select_label';  
			var YPriceParamSelectWrap =  document.createElement('DIV');
			// метод onchangeYPriceParamSelect пикрепляется к Селекту здесь и пикрепляется к добавляемым селектам ниже в специальном цикле 
			YPriceParamSelect.onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
			// добавляем теги OPTION
			for(var color in CurrPrintTypeData['y_price_param']){
				
				var option = document.createElement('OPTION');
				option.setAttribute("value",CurrPrintTypeData['y_price_param'][color]['percentage']);
				option.setAttribute("item_id",CurrPrintTypeData['y_price_param'][color]['item_id']);
				option.appendChild(document.createTextNode(color));
				YPriceParamSelect.appendChild(option);
			}
			
			// добавляем поле Выбрать в начало селекта
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -- нет цвета -- '));
			YPriceParamSelect.insertBefore(option, YPriceParamSelect.firstChild); 
			
			// ссылка добавить цвет
			var addYPriceParamLink = document.createElement('A');
			addYPriceParamLink.href = '#';
			addYPriceParamLink.id = 'calculatoraddYPriceParamLink';
			addYPriceParamLink.innerHTML = 'добавить цвет';
			addYPriceParamLink.onclick =  function(){
				// если количество селектов меньше рядов в таблице прайса то можем добавлять новый 
				// если сравнялось или больше добавлять не можем
				if(YPriceParamDiv.getElementsByTagName('SELECT').length < printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0].length-1){
					var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
	
					// навешиваем обработчик события селекту, потому что при YPriceParamSelect.cloneNode(true); он слетает
					YPriceParamSelectClone.onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
					
					var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode();
					var labelYPriceParamsClone = labelYPriceParams.cloneNode();
					//labelYPriceParams
					labelYPriceParamsClone.appendChild(YPriceParamSelectClone);
					YPriceParamSelectWrapClone.appendChild(labelYPriceParamsClone);
					YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					
					var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
					YPriceParamCMYKсlone.innerHTML = '';
					YPriceParamCMYKсlone.className += ' hidden';	
					YPriceParamCMYKсlone.onblur = printCalculator.onblurCMYK;
					YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);		
				}
				if(YPriceParamDiv.getElementsByTagName('SELECT').length >= printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0].length-1){
					// скрываем ссылку добавления если она есть
				   this.className += ' hidden';	
				}
				return false;
			}
			
			// добавляем один или несколько селектов в калькулятор в зависимости от того был он вызван 
			// для уже существующего расчета или для нового расчета 
			if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam !== 'undefined'){
				for(var i = 0;i < printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.length; i++){ 
				     // Select
				     var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
					 var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode(true);
					 var labelYPriceParamsClone = labelYPriceParams.cloneNode(true);
					 var optionsArr = YPriceParamSelectClone.getElementsByTagName("OPTION");
					 //var optionsArr = YPriceParamSelectClone.options;
					 for(var j in optionsArr){
						if(optionsArr[j] && optionsArr[j].nodeType ==1 && optionsArr[j].nodeName == 'OPTION'){ 
							if(optionsArr[j].getAttribute("item_id") && optionsArr[j].getAttribute("item_id") == printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].id) optionsArr[j].setAttribute("selected",true);
						}
					 }
					 
					 //YPriceParamSelectClone.options[parseInt(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].id)].setAttribute("selected",true);
					 // Select
					 labelYPriceParamsClone.appendChild(YPriceParamSelectClone);
				     YPriceParamSelectWrapClone.appendChild(labelYPriceParamsClone);
					 //labelYPriceParams
					 YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					 // CMYK
					 var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
                     if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].cmyk){
						  // делаем перекодировку, заменяем полученные в кодировке данные на раскодированные потому что потом
						  // их надо будет отправлять на сервер, а они отправляются на сервер не закодированными
						  // ВООБЩЕТО НАВЕРНО лучще переделать систему и кодировать сразу здесь чтобы не было путаницы и данные 
						  // всегда отправлялись на сервер в кодировке
						  printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].cmyk = Base64.decode(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].cmyk);
	                      YPriceParamCMYKсlone.innerHTML = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].cmyk;
						  YPriceParamCMYKсlone.className = 'YPriceParamCMYK';
					 }
					 YPriceParamCMYKсlone.onblur = printCalculator.onblurCMYK;
				     YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);
				}
				var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
				// навешиваем обработчики события каждому селекту, потому что при YPriceParamSelect.cloneNode(true); они слетают
				for(var i in selectsArr){
					selectsArr[i].onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
				}
			}
			else{
				// Select
				YPriceParamSelectWrap.appendChild(YPriceParamSelect);
				labelYPriceParams.appendChild(YPriceParamSelectWrap);
				YPriceParamDiv.appendChild(labelYPriceParams);
				// CMYK
				YPriceParamCMYKdiv.appendChild(YPriceParamCMYK);
				
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam = [];
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.push({'id':0,'coeff':1});
			}
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Цвет: ';
			
			
			var elementsBox = document.createElement('DIV');
		    elementsBox.className = "calculatorElementsBox";
		 
			
			elementsBox.appendChild(YPriceParamDiv);
			// если количество селектов меньше рядов в таблице прайса то добавляем сслыку добавления новых селектов
			if(YPriceParamDiv.getElementsByTagName('SELECT').length < printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0].length-1) elementsBox.appendChild(addYPriceParamLink);
			
			YPriceParamDivContainer.appendChild(title);
			YPriceParamDivContainer.appendChild(elementsBox);
			YPriceParamDivContainer.appendChild(YPriceParamCMYKdiv);
			YPriceParamDivContainer.appendChild(clear_div.cloneNode(true));
			printParamsBox.appendChild(YPriceParamDivContainer);
			
		}
		
		
		console.log('wwwwwwwwwwwwwwwwwwwwwww');
		console.log('place_id',printCalculator.currentCalculationData[printCalculator.type].print_details.place_id);
		console.log(CurrPrintTypeData);
		
		var place_id_for_sizes = (printCalculator.currentCalculationData[printCalculator.type].print_details.place_id =='-1')?0:printCalculator.currentCalculationData[printCalculator.type].print_details.place_id;
		
		if(CurrPrintTypeData['sizes'] && CurrPrintTypeData['sizes'][place_id_for_sizes]){
			// собираем данные для расчета
			// площади нанесения
			// if(typeof printCalculator.dataForProcessing['coefficients'] === 'undefined') printCalculator.dataForProcessing['coefficients']={};
			// if(typeof printCalculator.dataForProcessing['dop_params'] === 'undefined') printCalculator.dataForProcessing['dop_params']={};
			// printCalculator.dataForProcessing['coefficients']['sizes'] = [];
			// printCalculator.dataForProcessing['dop_params']['sizes'] = [];
			
			var printSizesSelect = document.createElement('SELECT');
			var labelSizes = document.createElement('LABEL');
			labelSizes.className = 'select_label';
			var printSizesSelectDiv  = document.createElement('DIV');
			printSizesSelectDiv.className = "printSizesSelectDiv";
			
			printSizesSelect.onchange = function(){
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes[0] = {'id':this.options[this.selectedIndex].getAttribute('item_id'),'coeff':this.options[this.selectedIndex].value,'val':this.options[this.selectedIndex].getAttribute('val'),'type':this.options[this.selectedIndex].getAttribute('type'),'target':this.options[this.selectedIndex].getAttribute('target')}
				
				if(printCalculator.type=='auto') printCalculator.makeProcessing();
				else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
			}
			for(var id in CurrPrintTypeData['sizes'][place_id_for_sizes]){
				if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes === 'undefined'){
					printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes = [];
					printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes[0] = {'id':CurrPrintTypeData['sizes'][place_id_for_sizes][id]['item_id'],'coeff':CurrPrintTypeData['sizes'][place_id_for_sizes][id]['percentage'],'val':CurrPrintTypeData['sizes'][place_id_for_sizes][id]['val'],'type':CurrPrintTypeData['sizes'][place_id_for_sizes][id]['type'],'target':CurrPrintTypeData['sizes'][place_id_for_sizes][id]['target']};
				}
				
				
				var option = document.createElement('OPTION');


				option.setAttribute("value",CurrPrintTypeData['sizes'][place_id_for_sizes][id]['percentage']);
				// id значения (размер нанесения) который будет сохранен в базу данных и по нему будет отстроен калькулятор 
				// в случае вызова по кокретному нанесению
				option.setAttribute("item_id",CurrPrintTypeData['sizes'][place_id_for_sizes][id]['item_id']);
				option.setAttribute("val",CurrPrintTypeData['sizes'][place_id_for_sizes][id]['val']);
				option.setAttribute("type",CurrPrintTypeData['sizes'][place_id_for_sizes][id]['type']);
				option.setAttribute("target",CurrPrintTypeData['sizes'][place_id_for_sizes][id]['target']);
				
				
				option.appendChild(document.createTextNode(CurrPrintTypeData['sizes'][place_id_for_sizes][id]['size']));
				printSizesSelect.appendChild(option);
				
				if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes && printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.sizes[0].id == CurrPrintTypeData['sizes'][place_id_for_sizes][id]['item_id']){
					option.setAttribute("selected",true);
					
				}
				
			}
			
			
			
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Площадь: ';
			
			
			var elementsBox = document.createElement('DIV');
		    elementsBox.className = "calculatorElementsBox";
		 
			labelSizes.appendChild(printSizesSelect);
			elementsBox.appendChild(labelSizes);
			
			printSizesSelectDiv.appendChild(title);
			printSizesSelectDiv.appendChild(elementsBox);
			printSizesSelectDiv.appendChild(clear_div.cloneNode(true));
			
			
			printParamsBox.appendChild(printSizesSelectDiv);
			
		}
		
		printParamsBox.appendChild(clear_div.cloneNode(true));
		
		// работаем с таблицами цен 
		
		
		// входящяя цена 
		if(CurrPrintTypeData['priceIn_tbl']){
			printCalculator.price_tblIn = printCalculator.build_priceTbl(CurrPrintTypeData['priceIn_tbl'],'in');
		    // printParamsBox.appendChild(printCalculator.price_tblIn);
		}
		else echo_message_js('отсутствует прайс входящих цен','system_message',5800);
		// исходящяя цена 
		if(CurrPrintTypeData['priceOut_tbl']){
			printCalculator.price_tblOut = printCalculator.build_priceTbl(CurrPrintTypeData['priceOut_tbl'],'out');
		    // printParamsBox.appendChild(printCalculator.price_tblOut);
		}
		else echo_message_js('отсутствует прайс исходящих цен','system_message',5800);
		
		console.log('>>> после определения Xindex');
		console.log(printCalculator.currentCalculationData[printCalculator.type]);
		console.log('<<< после определения Xindex');
		// return;
		
		// select для возможных площадей нанесения
		// [sizes] => Array
        //                ( [1] => Array   - ключ id места нанесения 
        //                        ([0] => Array ([print_id] => 13[size] => до 630 см2 (А4)[percentage] => 1.00)
		//						   [1] => Array ([print_id] => 13[size] => до 1260 см2 (А3)[percentage] => 1.50))
		//				  )
		
		var dopParamsArr = [];

		if(CurrPrintTypeData['coeffs']){
			for(var target in CurrPrintTypeData['coeffs']){
				for(var type in CurrPrintTypeData['coeffs'][target]){
					var data = CurrPrintTypeData['coeffs'][target][type];
					// console.log('..');
					// console.log(type);
					// console.log(data);
					//printParamsBox.appendChild(document.createTextNode(data.data[0].coeff));
					//printParamsBox.appendChild(br.cloneNode(true));
					if(data.optional==1){
						// printParamsBox.appendChild(printCalculator.makeCommonSelect('coeffs',target,type,data));
						dopParamsArr.push(printCalculator.makeCommonSelect('coeffs',target,type,data));
					}
				    else{// если применяется по умолчанию
					    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs === 'undefined')
						          printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs = {};
					    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs[target] === 'undefined')
								printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs[target] = {};
						if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs[target][type] === 'undefined'){
								printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs[target][type] = [];
								for(var index in data.data){
									printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.coeffs[target][type].push({"value": parseFloat(data.data[index].coeff),"id": data.data[index].item_id});
								}
						}
						/*;*/
						
					}
				}
				
			}
		}

		if(CurrPrintTypeData['additions']){
			for(var target in CurrPrintTypeData['additions']){
				for(var type in CurrPrintTypeData['additions'][target]){
					var data = CurrPrintTypeData['additions'][target][type];
					// console.log('..');
					// console.log(data);
					//printParamsBox.appendChild(document.createTextNode(data.data[0].coeff));
					//printParamsBox.appendChild(br.cloneNode(true));

					if(data.optional==1){
						//printParamsBox.appendChild(printCalculator.makeCommonSelect('additions',target,type,data));
						dopParamsArr.push(printCalculator.makeCommonSelect('additions',target,type,data));
					}
					else{// если применяется по умолчанию
					    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions === 'undefined')
						          printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions = {};
					    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions[target] === 'undefined')
								printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions[target] = {};
						if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions[target][type] === 'undefined')
								printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions[target][type] = [];
						
						// console.log(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions);
						for(var index in data.data){
							printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.additions[target][type].push({"value": parseFloat(data.data[index].value),"id": data.data[index].item_id});
						}
						/*;*/
						
					}
				}
				
			}
		}
		
		if(dopParamsArr.length>0){
			
			var dopParametrsTitle = document.createElement('DIV');
		    dopParametrsTitle.className = "dopParametrsTitle";
		    dopParametrsTitle.innerHTML = 'Дополнительно:';
		    printParamsBox.appendChild(dopParametrsTitle);
			
			
			var tbl = document.createElement('TABLE');
		    tbl.className = 'dopParametrsTbl';
		    var tr = document.createElement('TR');
		    var td = document.createElement('TD');
			
			for(var i = 0;i < dopParamsArr.length;i++){ 
			    if(0 || (i)%2 == 0)  var trClone = tr.cloneNode(true);
				trClone.appendChild(dopParamsArr[i]);
				if((i+1)%2 == 0) tbl.appendChild(trClone);
			}
			if(9%2 != 0){
				for(var i = 0;i < 3;i++) trClone.appendChild(td.cloneNode(true));
				tbl.appendChild(trClone);
				
			}
			printParamsBox.appendChild(tbl);
		}
		else{
		   var empytDiv = document.createElement('DIV');
		   empytDiv.style.height = '50px';
		   printParamsBox.appendChild(empytDiv);	
		}
		
		
	    var dopParametrsTitle = document.createElement('DIV');
		dopParametrsTitle.className = "dopParametrsTitle";
		dopParametrsTitle.innerHTML = 'Комментарий по нанесению';
		printParamsBox.appendChild(dopParametrsTitle);

		var textarea = document.createElement('TEXTAREA');
		textarea.className = "commentsTextArea";
		if(printCalculator.currentCalculationData[printCalculator.type].tz && printCalculator.currentCalculationData[printCalculator.type].tz!=''){
			// ВООБЩЕТО НАВЕРНО лучще переделать систему и кодировать сразу здесь чтобы не было путаницы и данные 
			// всегда отправлялись на сервер в кодировке
			printCalculator.currentCalculationData[printCalculator.type].tz = Base64.decode(printCalculator.currentCalculationData[printCalculator.type].tz);
			printCalculator.currentCalculationData[printCalculator.type].print_details.comment = printCalculator.currentCalculationData[printCalculator.type].tz;
			var comment = printCalculator.currentCalculationData[printCalculator.type].tz;
			textarea.value= comment.replace(/<br \/>/g,"\r");
		}
		else if(printCalculator.type=='manual') textarea.value= (printCalculator.currentCalculationData[printCalculator.type].print_details.comment)?printCalculator.currentCalculationData[printCalculator.type].print_details.comment:'';
		
		textarea.onchange = function(){  printCalculator.currentCalculationData[printCalculator.type].print_details.comment = this.value; }
		printParamsBox.appendChild(textarea);
		
		return printParamsBox;
	} 
	,
	makeCommonSelect:function(glob_type,target,type,data){
		// ламерский способ зделать клон объекта
		var data = JSON.parse(JSON.stringify(data));
		
		// складываем элементы в ячейки будующей таблицы
		var docFragment = document.createDocumentFragment();
		var td = document.createElement('TD');	
		//var div = document.createElement('DIV');
		var tdClone = td.cloneNode();
		tdClone.className = 'title';
		tdClone.appendChild(document.createTextNode(data.data[0].title));
		docFragment.appendChild(tdClone);
		
		var commonSelect = document.createElement('SELECT');
		var labelCommon = document.createElement('LABEL');
		labelCommon.className = 'select_label'; 
		var option = document.createElement('OPTION');
		option.innerHTML = 'стандартно';
		option.setAttribute("item_id",0);
		commonSelect.appendChild(option);
		
		commonSelect.onchange = function(){
			var item_id = this.options[this.selectedIndex].getAttribute('item_id');
			if(item_id != 0){
				if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type] === 'undefined')
					  printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type] = {};
			    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target] === 'undefined')
					printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target] = {};
			        // ячейку каждый раз создаем заново тем самым очищая её 
					// потому что если в ней уже есть какие-то данные, то новые данные от селекта с большим количеством значений
					// добавятся дополнительно вместо того чтобы замениться
					printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type] = [];
				
				var obj = {"value": parseFloat(this.options[this.selectedIndex].value),"id": this.options[this.selectedIndex].getAttribute('item_id')};
                if(this.options[this.selectedIndex].getAttribute('multi')){
					var nextTr = printCalculator.nextTag(this.parentNode);
					obj.multi = parseInt(nextTr.getElementsByTagName('INPUT')[0].value);
				}
				printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type].push(obj);
				
				if(printCalculator.type=='auto') printCalculator.makeProcessing();
				else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
				
			}
			else{
				if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type]){

					delete printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type];
					if(printCalculator.type=='auto') printCalculator.makeProcessing();
					else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
				}
			}

		}
		
		
		// ОБРАБОТКА МУЛЬТИЗНАЧНЫХ ПАРАМЕТРОВ (МУЛЬТИЗНАЧНЫЕ СЕЛЕКТЫ)
		// Удаляем первый элемент массива потому что он используется только в качестве заголовка параметра 
		// нам надо чтобы он не попал в селект
		if(data.data.length>1) data.data.shift();
		
		for(var index in data.data){
			var option = document.createElement('OPTION');
			option.innerHTML = (data.data.length>1)? data.data[index].title :'Применить';
			if(data.data[index].coeff) option.value = data.data[index].coeff;
			if(data.data[index].value) option.value = data.data[index].value;
			if(data.data[index].item_id) option.setAttribute("item_id",data.data[index].item_id);
			if(data.multi==1) option.setAttribute("multi",1);
			
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type] && printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target] && printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type]){
			     if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type][0].id==data.data[index].item_id) option.setAttribute("selected",true);
			}
			
			commonSelect.appendChild(option);
		}
		
		var tdClone = td.cloneNode();

		labelCommon.appendChild(commonSelect);
		tdClone.appendChild(labelCommon);
		tdClone.className = 'select';
		docFragment.appendChild(tdClone);
		
		if(data.multi==1){
			 var input_field =  document.createElement('INPUT');
			 input_field.value = 1;
			 if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type] && printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target] && printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type]){
			     if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type][0].multi) input_field.value = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type][0].multi;
			}
			 
			 
			 input_field.onkeyup = function(){
				 if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type][0].multi !== 'undefined'){
					printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type][target][type][0].multi = this.value;
				    if(printCalculator.type=='auto') printCalculator.makeProcessing();
					else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
				 }
			 }
		}
		
		var tdClone = td.cloneNode();
		tdClone.className = 'multi';
		if(input_field) tdClone.appendChild(input_field);
		docFragment.appendChild(tdClone);
		
		return docFragment;
	}
	,
	makeProcessing:function(){
		
		if(typeof printCalculator.makeProcessingFlag !== 'undefined') delete printCalculator.makeProcessingFlag;
		
		// обращаемся к ряду таблицы цен, 
		// по значению параметра printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblYindex
		// и выбираем нужную ячейку 
		// по значению параметра printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex
		console.log('>>> ДАННЫЕ КОНКРЕТНОГО ТЕКУЩЕГО РАСЧЕТА перед расчетом в методе makeProcessing');
		console.log(' printCalculator.currentCalculationData[printCalculator.type]',printCalculator.currentCalculationData[printCalculator.type]);
		console.log('<<< ДАННЫЕ КОНКРЕТНОГО ТЕКУЩЕГО РАСЧЕТА');
		// return;
		// снимаем значение price с таблицы прайса
		//console.log('>>> priceOut_tbl <<<');
		//console.log( printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0] );
		//alert(printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex);
		var priceOut_tblYindex = (typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam !== 'undefined')? printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.length:1;
		var priceIn_tblYindex = (typeof printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam !== 'undefined')? printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.length:1;
		
		var price_out =printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0][priceOut_tblYindex][printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex];
		var price_in =printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceIn_tbl[0][priceIn_tblYindex][printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex];
		// alert('out '+price_out+' - in '+price_in);
		// если полученная цена оказалась равна 0 то значит стоимость не  указана
	    if(parseFloat(price_out) == 0 || parseFloat(price_in) == 0){
			//alert(price_out+' '+price_in);
			var sourse_tbls = ['priceIn_tbl','priceOut_tbl'];
			for(index in sourse_tbls){
			    var sourse_tblXindex = sourse_tbls[index]+'Xindex';
				// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.print_id); 
				// alert(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex']);
			    // alert(printCalculator.currentCalculationData[printCalculator.type].print_details[sourse_tblXindex]);
				// если это последние ряды прайс значит это лимит
				if(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex'] == printCalculator.currentCalculationData[printCalculator.type].print_details[sourse_tblXindex]){
	
					var limimt =parseInt(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id][sourse_tbls[index]][0][0][printCalculator.currentCalculationData[printCalculator.type].print_details[sourse_tblXindex]]);
					
					printCalculator.cancelSaveReslut = true;
					var caution = 'Цена не может быть расчитана, достигнут лимит тиража в '+limimt+' шт.\rтребуется индивидуальный расчет';
					break;
				}
				else{//иначе это индивидуальный расчет cancelCalculator
				    //if(typeof printCalculator.cancelSaveReslut === 'undefined')
					printCalculator.cancelSaveReslut = true;
					var caution = 'Рассчет с данными параметрами не может быть произведен \rтребуется индивидуальный расчет';
				}
			}
		}
					
		if(printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice){
			price_out = price_out*printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantOutPrice/printCalculator.currentCalculationData[printCalculator.type].quantity;
		}
		if(printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice){
			price_in = price_in*printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantInPrice/printCalculator.currentCalculationData[printCalculator.type].quantity;
		}
		
		
		if(printCalculator.cancelSaveReslut){
			alert(caution);
			delete printCalculator.cancelSaveReslut;
			printCalculator.changeMenu(document.getElementById('topMenuBtnmanual'));
			printCalculator['load_manual_calc']('manual');
			
		}
		//console.log('>>> YPriceParam.length  --   priceIn_tblXindex  priceOut_tblXindex  --  price_in  price_out <<<');
		//console.log( printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.length + ' -- '+printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex + ' '+ printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex+' -- '+ price_in + ' '+ price_out );
		
		// КОЭФФИЦИЕНТЫ И НАДБАВКИ
		 
		var square_coeff = 1;
		var Y_coeff = price_coeff = summ_coeff = 0;
		var price_coeff_list  = summ_coefficient_list = '';
		var price_addition = summ_addition = 0;
		var price_additions_list = summ_additions_list = '';
		
		// КОЭФФИЦИЕНТЫ НА ПРАЙС
		// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
		// перебираем printCalculator.currentCalculationData[printCalculator.type].print_details.
		// в нем содержатся коэффициенты по Y параметру таблицы прайса и по размеру нанесения
		for(glob_type in printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params){
			if(glob_type=='YPriceParam'){
				var set = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type];
				for(var i = 0;i < set.length;i++){ 
				    // подстраховка
				    if(set[i].coeff == 0) set[i].coeff = 1;
					Y_coeff += (set[i].coeff-1);
					price_coeff_list += glob_type +' - '+ set[i].coeff+', ';
				}
			}
			if(glob_type=='sizes'){
				var set = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type];
				for(var i = 0;i < set.length;i++){ 
				    if(set[i].type == 'coeff'){
						// подстраховка
						if(set[i].val == 0) set[i].val = 1;
						if(set[i].target == 'price') square_coeff = set[i].val;// будет расчитан отдельно от остальных коэф-ов прайса
						if(set[i].target == 'summ') summ_coeff += set[i].val-1;// будет расчитан также как остальные коэф-ты суммы
						
						if(set[i].target == 'price') price_coeff_list += glob_type +' - '+ set[i].val+', ';
						if(set[i].target == 'summ') summ_coeff_list += glob_type +' - '+ set[i].val+', ';
					}
					if(set[i].type == 'addition'){
						
						if(set[i].target == 'price') price_addition += parseFloat(set[i].val);
						if(set[i].target == 'summ') summ_addition +=  parseFloat(set[i].val);
						// alert(price_addition+' - '+summ_addition)
						
						if(set[i].target == 'price') price_additions_list += glob_type +' - '+ set[i].val+', ';
						if(set[i].target == 'summ') summ_additions_list += glob_type +' - '+ set[i].val+', ';
					}
				}
			}
			if(glob_type=='coeffs'){
				var data = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type];
				for(target in data){
					
					for(var type in data[target]){
						var set = data[target][type];
						for(var i = 0;i < set.length;i++){ 
						    // подстраховка
							if(set[i].value == 0) set[i].value = 1;
							if(set[i].multi && set[i].multi == 0) set[i].multi = 1;
							
							if(target=='price'){
								price_coeff += (set[i].multi)?  (set[i].value-1)*set[i].multi : set[i].value-1;
								price_coeff_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
							if(target=='summ'){
								summ_coeff += (set[i].multi)?  (set[i].value-1)*set[i].multi : set[i].value-1;
								summ_coefficient_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
						}								
					}
				}
			}
		}
		//// console.log('price coefficient');
		//// console.log(price_coeff);
		//// console.log(price_coeff_list);
		//// console.log('summ coefficient');
		//// console.log(summ_coeff);
		//// console.log(summ_coefficient_list);
		
		
		// перебираем printCalculator.currentCalculationData[printCalculator.type].print_details.
	
		
	
	
	    // НАДБАВКИ НА ИТОГОВУЮ СУММУ
		// перебираем printCalculator.currentCalculationData[printCalculator.type].print_details.
		for(glob_type in printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params){
			if(glob_type=='additions'){
				var data = printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params[glob_type];
				for(target in data){
					
					for(var type in data[target]){
						var set = data[target][type];
						for(var i = 0;i < set.length;i++){ 
						    // подстраховка
							if(set[i].multi && set[i].multi == 0) set[i].multi = 1;
							
							if(target=='price'){
								price_addition += (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								price_additions_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
							if(target=='summ'){
								summ_addition += (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								summ_additions_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
						}								
					}
				}
			}
		}
		//// console.log('price additions');
		//// console.log(price_addition);
		//// console.log(price_additions_list);
		//// console.log('summ additions');
		//// console.log(summ_addition);
		//// console.log(summ_additions_list);
		
		//console.log('price additions');
		//console.log(printCalculator.currentCalculationData[printCalculator.type]);

		
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//   
		//  CXEMA  - total_price = (((БЦ1*ПЛкф)+БЦ1*ЦВкф+БЦ2*ОПкф+НБпр)*КОЛ-ВО)+НБсумм+СУММА1*ОПкф(сумм)
		// 
		//  Коэффициэнт учитывается как Коэфф-1 т.е коэфф 1.2 = (1.2-1) = 0.2
		//  ПЛкф - коэффициент площади из поля площадь в калькуляторе
		//  ЦВкф - коэффициэнт цвета из поля выбора цвета в калькуляторе
		//  ОПкф - коэффициэнт опции из поля “дополнительно” в калькуляторе
		//  НБ - надбавка из поля “дополнительно” в калькуляторе
		//  пр или сумм - область действия надбавки или коэффициента- прайс или сумма
		
		//  ИТОГОВАЯ CXEMA  -
		//        var base_price1 = price;
		//        var base_price2 = price*square_coeff;
		//        var summ1 = (base_price2 + base_price1*Y_coeff + base_price2*price_coeff + price_addition)*quantity;
		//        var total_price = summ1 + sum_additions + summ1*summ_coeff
		
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
		var quantity = printCalculator.currentCalculationData[printCalculator.type].quantity;

		var base_price_for_Y = price_in/priceIn_tblYindex;
		var base_price2 = price_in*square_coeff;
		var summ1 = (base_price2 + base_price_for_Y*Y_coeff + base_price2*price_coeff + price_addition)*quantity;
		var total_price_in = summ1 + summ_addition + summ1*summ_coeff;
		//alert(' price_in '+ price_in +' priceIn_tblYindex '+ priceIn_tblYindex +' base_price_for_Y '+base_price_for_Y  +' Y_coeff '+Y_coeff+' base_price_for_Y*Y_coeff '+base_price_for_Y*Y_coeff );
		
		var base_price_for_Y = price_out/priceOut_tblYindex;;
		var base_price2 = price_out*square_coeff;
		var summ1 = (base_price2 + base_price_for_Y*Y_coeff + base_price2*price_coeff + price_addition)*quantity;
		var total_price_out = summ1 + summ_addition + summ1*summ_coeff;
		//alert(' price_out '+ price_out +' priceOut_tblYindex '+ priceOut_tblYindex +' base_price_for_Y '+base_price_for_Y  +' Y_coeff '+Y_coeff+' base_price_for_Y*Y_coeff '+base_price_for_Y*Y_coeff );
		
		
		
		//var total_price_out = ((((price_out*price_coeff)+price_addition)*printCalculator.currentCalculationData[printCalculator.type].quantity)*summ_coeff)+summ_addition;
		//var total_price_in  = ((((price_in*price_coeff)+price_addition)*printCalculator.currentCalculationData[printCalculator.type].quantity)*summ_coeff)+summ_addition;
		
		total_price_out = Math.round(total_price_out * 100) / 100 ;
		total_price_in = Math.round(total_price_in * 100) / 100 ;
		
	    printCalculator.currentCalculationData[printCalculator.type].price_out = Math.round(total_price_out/printCalculator.currentCalculationData[printCalculator.type].quantity * 100) / 100;
		printCalculator.currentCalculationData[printCalculator.type].price_in  = Math.round(total_price_in/printCalculator.currentCalculationData[printCalculator.type].quantity * 100) / 100;
		
		printCalculator.currentCalculationData[printCalculator.type].total_price_out = Math.round((printCalculator.currentCalculationData[printCalculator.type].price_out*printCalculator.currentCalculationData[printCalculator.type].quantity) * 100) / 100 ;
		printCalculator.currentCalculationData[printCalculator.type].total_price_in = Math.round((printCalculator.currentCalculationData[printCalculator.type].price_in*printCalculator.currentCalculationData[printCalculator.type].quantity) * 100) / 100 ;

		printCalculator.showCalcItogDisplay();
	
	}
	,
	noneAutoCalcProcessing:function(cell){ 

		var name = cell.name;
		var value = cell.value;
		var tr = $(cell).parents('tr');
		var quantity = printCalculator.currentCalculationData[printCalculator.type].quantity;
		var discount = printCalculator.currentCalculationData[printCalculator.type].discount;
 
		printCalculator.currentCalculationData[printCalculator.type][name] = parseFloat(value);
		printCalculator.currentCalculationData[printCalculator.type]['total_'+name] = value*quantity;
		$(tr).find( "td[total_cell=total_"+name+"]").html(((printCalculator.currentCalculationData[printCalculator.type]['total_'+name]).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р');

		if(name == 'price_out'){
			
			var discount_price_out =(discount != 0 )? (printCalculator.currentCalculationData[printCalculator.type].price_out/100)*(100 + parseInt(discount)) : printCalculator.currentCalculationData[printCalculator.type].price_out;
			var discount_itog =(printCalculator.currentCalculationData[printCalculator.type].discount != 0 )? (printCalculator.currentCalculationData[printCalculator.type].total_price_out/100)*(100 + parseInt(printCalculator.currentCalculationData[printCalculator.type].discount)) : printCalculator.currentCalculationData[printCalculator.type].total_price_out;


			$(tr).find( "td[total_cell="+name+"_discount]").html(((discount_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р');

			$(tr).find( "td[total_cell=total_"+name+"_discount]").html(((discount_itog).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р');
		}/**/

	}
	,
	setCaretToPos:function (input, pos) {
		var selectionStart = pos; 
		var selectionEnd = pos; 
		
	    if (input.setSelectionRange) {
			input.focus();
			input.setSelectionRange(selectionStart, selectionEnd);
	    }
	    else if (input.createTextRange) {
			var range = input.createTextRange();
			range.collapse(true);
			range.moveEnd('character', selectionEnd);
			range.moveStart('character', selectionStart);
			range.select();
	    }
	}
	,
	correctToInt:function (cell,str){// корректировка значений вводимых пользователем в поле ввода типа Integer
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
		if(wrong_input){
			if(cell.nodeName == 'INPUT') cell.value = str;
			else cell.innerHTML = str;
		}  
		return pos;
	}
	,
	correctToFloat:function(cell,str){// корректировка значений вводимых пользователем в поле ввода типа Float
		var wrong_input = false;
		var pos = 0;
		
		// если строка содержит запятую меняем её на точку
		var pattern = /,/; 
		if(str.match(pattern)){ wrong_input = true;  pos =  str.indexOf(',')+1; str =  str.replace(',','.');}
		
		// если строка содержит что-то кроме цифры или точки 
		var pattern = /[^\d\.]+/; 
		var result = pattern.exec(str);
		if(result !== null){ 
			wrong_input = true;
			if(str.length==1){ 
				// если это единственный символ в строке - заменяем его на 0
			     wrong_input = true; str = 0; pos = 0;
			}
			else{
				// если это не единственный символ в строке - вырезаем его
				var substr_arr = str.split(result[0]);
				pos =  substr_arr[0].length;
				str =  substr_arr[0] + substr_arr[1];
			}
			
			
		}

		
		//  если после точки введено менее или более 2 цифр исправляем до 2-х
		// ЗДЕСЬ НУЖНО РЕШИТЬ ВОПРОС УСТАНОВКИ КУРСОРА В НУЖНОЕ МЕСТО ПОКА ПЕРЕНОСИТСЯ В КОНЕЦ
		//var pattern = /^\d+\.\d{2}$/; 
	 
		// если величина числа больше допустимого - обрезаем его
		if(str.length>12){ wrong_input = true;  str = '100000000.00'; pos = 12;}
     
		// если был выявлен некорректный ввод исправляем содержимое ячейки 
		if(wrong_input){
			if(cell.nodeName == 'INPUT') cell.value = str;
			else cell.innerHTML = str;
		}
		
		//alert(pos);
		return pos; 
	}
	,
	showCalcItogDisplay:function(){

		var total_str  = '';
		var total_details  = '';
	    if(typeof printCalculator.cancelSaveReslut === 'undefined'){
			var total_tbl = document.createElement('TABLE');
			total_tbl.className = 'itogDisplayTbl';
			total_tbl.id = 'itogDisplayTbl';
			var tr = document.createElement('TR');
			var td = document.createElement('TD');
			var input = document.createElement('INPUT');
			
			TRclone = tr.cloneNode(true);
			TRclone.className = 'attic';
			tdClone = td.cloneNode(true);
			// tdClone.innerHTML = '';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute("colspan","2");
			tdClone.innerHTML = 'без скидки';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = '';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute("colspan","2");
			tdClone.innerHTML = 'со скидкой';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
			
			
			TRclone = tr.cloneNode(true);
			TRclone.className = 'head';
			tdClone = td.cloneNode(true);
			// tdClone.innerHTML = '';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'штука';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'тираж';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'скидка';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'штука';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'тираж';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
		
			
			TRclone = tr.cloneNode(true);
			tdClone = td.cloneNode(true);
			tdClone .className = 'title';
			tdClone.innerHTML = 'Входящая:';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			if(printCalculator.type=='auto')  tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			else{
				inputClone = input.cloneNode(true);
				inputClone.name = 'price_in';
				inputClone.value = printCalculator.currentCalculationData[printCalculator.type]['price_in'];
				inputClone.onfocus = function(){
					if(this.value == '0') this.value ='';
				}
				inputClone.onkeyup = function(){
                    if(this.value != '' && this.value != '&nbsp;'){ 
						var result = printCalculator.correctToFloat(this,this.value);
						if(result != 0) printCalculator.setCaretToPos(this,result);
					}
					printCalculator.noneAutoCalcProcessing(this);
				}
				tdClone.appendChild(inputClone);
			}
	
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute('total_cell','total_price_in');
			if(printCalculator.type=='auto')  tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].total_price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			else{
				tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].total_price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			}
			// под скидки первый ряд
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			//tdClone.innerHTML = '-';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			//tdClone.innerHTML = '-';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
			//tdClone.innerHTML = '-';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);

			TRclone = tr.cloneNode(true);
			tdClone = td.cloneNode(true);
			tdClone .className = 'title';
			tdClone.innerHTML = 'Исходящая:';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
		
			if(printCalculator.type=='auto') tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			else{
				inputClone = input.cloneNode(true);
				inputClone.name = 'price_out';
				inputClone.value = printCalculator.currentCalculationData[printCalculator.type]['price_out'];
				inputClone.onfocus = function(){
					if(this.value == '0') this.value ='';
				}
				inputClone.onkeyup = function(){
					if(this.value != '' && this.value != '&nbsp;'){ 
						var result = printCalculator.correctToFloat(this,this.value);
						if(result != 0) printCalculator.setCaretToPos(this,result);
					}
					printCalculator.noneAutoCalcProcessing(this);
				}
				tdClone.appendChild(inputClone);
			}

			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute('total_cell','total_price_out');
			if(printCalculator.type=='auto') tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].total_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			else{
				tdClone.innerHTML = ((printCalculator.currentCalculationData[printCalculator.type].total_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			}
			TRclone.appendChild(tdClone);
			// под сидки второй ряд
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = printCalculator.currentCalculationData[printCalculator.type].discount+'%';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute('total_cell','price_out_discount');
			// alert(34);
			var discount_price_out =(printCalculator.currentCalculationData[printCalculator.type].discount != 0 )? (printCalculator.currentCalculationData[printCalculator.type].price_out/100)*(100 + parseInt(printCalculator.currentCalculationData[printCalculator.type].discount)) : parseInt(printCalculator.currentCalculationData[printCalculator.type].price_out);
 
			tdClone.innerHTML = ((discount_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';

			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.setAttribute('total_cell','total_price_out_discount');

			var discount_itog =(printCalculator.currentCalculationData[printCalculator.type].discount != 0 )? (printCalculator.currentCalculationData[printCalculator.type].total_price_out/100)*(100 + parseInt(printCalculator.currentCalculationData[printCalculator.type].discount)) : printCalculator.currentCalculationData[printCalculator.type].total_price_out;
			tdClone.innerHTML = ((discount_itog).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);

		}
		else{
			alert(caution);
	
			delete printCalculator.cancelSaveReslut;
		/*	if(typeof document.getElementById("calculatorsaveResultBtn") !== 'undefined') document.getElementById("calculatorsaveResultBtn").style.display = 'none';
			*/
		}

		 //console.log('>>> total_str <<<');
		// console.log('in  - '+(printCalculator.currentCalculationData[printCalculator.type].price_in).toFixed(2)+' '+(total_price_in).toFixed(2)+' out - '+(printCalculator.currentCalculationData[printCalculator.type].price_out).toFixed(2)+' '+(total_price_out).toFixed(2));   
		printCalculator.total_details = document.createElement('div');	
		printCalculator.total_details.className ="calculatorTotalDetails";

		/*var total_details = '<span style="color:#FF6633;">коэффициэнты прайса:</span> '+price_coeff_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки прайса:</span> '+price_additions_list+'<br>';
		total_details += '<span style="color:#FF6633;">коэффициэнты суммы:</span> '+summ_coefficient_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки суммы:</span> '+summ_additions_list+'<br>';
        printCalculator.total_details.innerHTML = total_details;*/
		if(document.getElementById("showProcessingDetailsBoxTotalDetails")){
			document.getElementById("showProcessingDetailsBoxTotalDetails").innerHTML = '';
			document.getElementById("showProcessingDetailsBoxTotalDetails").appendChild(printCalculator.total_details);
		}

		if(total_tbl){
					
			// дисплей итоговых подсчетов
			if(document.getElementById(printCalculator.type+"CalcItogDisplay")){
				printCalculatorItogDisplay = document.getElementById(printCalculator.type+"CalcItogDisplay");
				printCalculatorItogDisplay.innerHTML = '';
			}
			else{
				var printCalculatorItogDisplay = document.createElement('DIV');
			    printCalculatorItogDisplay.id = printCalculator.type+"CalcItogDisplay";
			}
			var dopParametrsTitle = document.createElement('DIV');
			dopParametrsTitle.className = "dopParametrsTitle";
			dopParametrsTitle.innerHTML = 'Цена';
			printCalculatorItogDisplay.appendChild(dopParametrsTitle);
				
			printCalculatorItogDisplay.appendChild(total_tbl);
			
			var BtnsDiv = document.createElement('DIV');
			BtnsDiv.className = 'BtnsDiv';
			BtnsDiv.id = 'calculatorsaveResultPlank';
			
			if(printCalculator.type == 'auto' || printCalculator.type == 'free'){
				var showDopInfoBtn = document.createElement('DIV');
				showDopInfoBtn.className = 'showDopInfoBlockBtn';
				if(printCalculator.type == 'auto'){
					showDopInfoBtn.innerHTML = 'Включить вкладку прайс';
				    showDopInfoBtn.onclick =  printCalculator.showProcessingDetails;
					
					BtnsDiv.appendChild(showDopInfoBtn);
				}
				/*if(printCalculator.type == 'free'){
					showDopInfoBtn.innerHTML = 'Вспомогательная информация';
				    showDopInfoBtn.onclick =  printCalculator.showDopInfoBlock;
				}*/
				
				
			}

			var saveBtn = document.createElement('DIV');
			saveBtn.className = 'ovalBtn';
			saveBtn.innerHTML = 'Сохранить расчет';
			saveBtn.onclick =  printCalculator.saveCalculatorResult;
			BtnsDiv.appendChild(saveBtn);
			
			var ConfirmBtn = document.createElement('DIV');
			ConfirmBtn.className = 'confirmBtnCalc';
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.need_confirmation) ConfirmBtn.className = 'confirmBtnCalc redbg';
			ConfirmBtn.innerHTML = '!'; 
			ConfirmBtn.onclick =  function(){ printCalculator.makeResultConfirmation(ConfirmBtn);}
			
			
			var ConfirmSpan = document.createElement('SPAN');
			ConfirmSpan.className = 'confirmSpanCalc';
			ConfirmSpan.innerHTML = 'подтверждаю цены, снять маркер'; 
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.need_confirmation) ConfirmBtn.appendChild(ConfirmSpan);
			BtnsDiv.appendChild(ConfirmBtn);
			
			printCalculatorItogDisplay.appendChild(BtnsDiv);
			printCalculator.currentCalculationData[printCalculator.type].mainCalculatorBox.appendChild(printCalculatorItogDisplay);
		}
	}
	,
	makeResultConfirmation:function(btn){
		if(printCalculator.currentCalculationData[printCalculator.type].print_details.need_confirmation){
			delete printCalculator.currentCalculationData[printCalculator.type].print_details.need_confirmation;
			btn.getElementsByTagName('SPAN')[0].className = 'confirmSpanCalcHidden';
			btn.className = 'confirmBtnCalc';
		}
		else{
			printCalculator.currentCalculationData[printCalculator.type].print_details.need_confirmation = true;
			btn.getElementsByTagName('SPAN')[0].className = 'confirmSpanCalc';
			btn.className = 'confirmBtnCalc redbg';
		}
	}
	,
	showProcessingDetails:function(){

		var box = document.createElement('DIV');
		box.id = "showProcessingDetailsBox";
		//box.style.width = '300px';
		box.style.display = "none";
		var cap = document.createElement('DIV');
		cap.className = 'cap';
		cap.innerHTML = 'Входящий прайс';
		box.appendChild(cap);
		box.appendChild(printCalculator.price_tblIn);
		var cap = document.createElement('DIV');
		cap.className = 'cap';
		cap.innerHTML = 'Исходящий прайс';
		box.appendChild(cap);
		box.appendChild(printCalculator.price_tblOut);
		var total_details = document.createElement('DIV');
		total_details.id = "showProcessingDetailsBoxTotalDetails";
		
		total_details.appendChild(printCalculator.total_details);
		box.appendChild(total_details);
		document.body.appendChild(box);
		
		$("#showProcessingDetailsBox").dialog({autoOpen: false, position:{ at: "top+35%", of: window } ,title: "Детали расчета",width: 490,close: function() {this.remove();}});
		$("#showProcessingDetailsBox").dialog("open");
		 //
        //
	}
	,
	showDopInfoBlock:function(){
	    
		// если данные по вспомогательной инофрмации еще не загруженные и окно не создано отправляем запрос и создаем окно
		if(!printCalculator.DopInfoBlock && !printCalculator.showDopInfoBlock_InProcess){
			alert(1);
			printCalculator.showDopInfoBlock_InProcess = true;
			// формируем url для AJAX запроса
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&grab_dop_info=1','section');
			printCalculator.send_ajax(url,callback);
			
			function callback(response){ 
				alert(response);
				// console.log(response);
				printCalculator.showDopInfoBlock_InProcess = false;
			}
		}
		else{
			alert(2);
		}
	}
	,
	saveCalculatorResult:function(){
		// в этом методе две задачи 
		// 1. отправить данные на сервер
		// 2. закрыть калькулятор
		// console.log(printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData[printCalculator.type].print_details.place_id]); 
		// корректируем объект с информацией удаляем не нужные для сохранение данные, добавляем нужные
		
		// ПРОВЕРЯЕМ ВЫБРАН ЛИ ЦВЕТ если не выдаем окно и отменяем сохранение данных расчета
		// информация о цвете хранится в массиве printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam
		// если этот массив есть значит в теле калькулятора был выведен селект для выбора цвета ,
		// по умолчнанию в массив добавляется элемент с id равным 0 который заменяется на элементы с реальными id 
		// в процессе выбора вариантов из селекта
		// ЗНАЧИТ ЕСЛИ ЕСТЬ массив YPriceParam но его размер равен 0 или в нем один элемент с id равным 0, то параметр из селекта
		// выбран не был 
		
		if(printCalculator.type=='auto' || printCalculator.type=='manual'){
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam){
				if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.length==0 || printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[0].id==0){
					echo_message_js("Расчет не может быть произведен - Не выбран цвет!",'system_message',3800);
					return;
				}
			}
		    printCalculator.currentCalculationData[printCalculator.type].print_details.place_type =  printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData[printCalculator.type].print_details.place_id].name;
		    printCalculator.currentCalculationData[printCalculator.type].print_details.print_type =  printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData[printCalculator.type].print_details.place_id].prints[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id];
		}
		if(printCalculator.type=='free'){
			var unfilled = false;
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.print_type.replace(/^\s\s*/, '').replace(/\s\s*$/, '') ==''){
				echo_message_js("Заполните поле \"Название услуги\"",'system_message',3800);
				unfilled = true;
			}
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.comment.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
				echo_message_js("Заполните поле \"Bнутренний комментарий\"",'system_message',3800);
				unfilled = true;
			}
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.commentForClient.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
				echo_message_js("Заполните поле \"Подробное описание для клиента\"",'system_message',3800);
				unfilled = true;
			}
			if(printCalculator.currentCalculationData[printCalculator.type].print_details.supplier.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
				echo_message_js("Заполните поле \"Поставщик\"",'system_message',3800);
				unfilled = true;
			}
			if(typeof printCalculator.currentCalculationData[printCalculator.type].quantity == 'string' && printCalculator.currentCalculationData[printCalculator.type].quantity.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
				echo_message_js("Заполните поле \"Тираж\"",'system_message',3800);
				unfilled = true;
			}
			if(unfilled) return;
		}

		printCalculator.currentCalculationData[printCalculator.type].print_details.calculator_type = printCalculator.type;
		printCalculator.currentCalculationData[printCalculator.type].print_details.level = printCalculator.level;
		printCalculator.currentCalculationData[printCalculator.type].print_details.discount = printCalculator.currentCalculationData[printCalculator.type].discount;
		printCalculator.currentCalculationData[printCalculator.type].print_details.creator_id = printCalculator.currentCalculationData[printCalculator.type].creator_id;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].glob_type !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].glob_type;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].dop_row_id !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].dop_row_id;

		if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tbl !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tbl;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tbl !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tbl;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].total_price_in !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].total_price_in;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].total_price_out !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].total_price_out;
		if(typeof printCalculator.currentCalculationData[printCalculator.type].mainCalculatorBox !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].mainCalculatorBox;
		if(typeof printCalculator.price_tblIn !== 'undefined') delete printCalculator.price_tblIn;
		if(typeof printCalculator.price_tblOut !== 'undefined') delete printCalculator.price_tblOut;
		if(typeof printCalculator.total_details !== 'undefined') delete printCalculator.total_details;

		
		if(typeof printCalculator.dataObj_toEvokeCalculator !== 'undefined') delete printCalculator.dataObj_toEvokeCalculator;
		
		
		// console.log('>>> saveCalculatorResult --');
		// console.log(printCalculator.currentCalculationData[printCalculator.type]);
        // console.log('<<< saveCalculatorResult --');
		
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&save_calculator_result=1&details='+JSON.stringify(printCalculator.currentCalculationData[printCalculator.type]),'section');
		
		//alert(url);
		document.getElementById("calculatorsaveResultPlank").style.visibility ='hidden';
		printCalculator.send_ajax(url,callback);

		function callback(response){ 
		     alert(response);
			// alert(printCalculator.currentCalculationData[printCalculator.type].action);
			// console.log(response);
		    // location.reload();
			if(printCalculator.currentCalculationData[printCalculator.type].action == 'update'){
				printCalculator.cleanOutCalculator();
				$('#js-main_service_center').totalCommander('update_total_window');
			}
			if(printCalculator.currentCalculationData[printCalculator.type].action == 'new'){
				//alert(response);
				try {  var adsObj = JSON.parse(response); }
				catch (e) { 
					alert('неправильный формат данных in printCalculator.saveCalculatorResult() ошибка JSON.parse(response)');
					return;
				}
                printCalculator.cleanOutCalculator();
                $('#js-main_service_center').totalCommander('add_services',adsObj);
				//location.reload();
			}
		}
		
	}
	,
	onblurCMYK:function(e){ 
	    e = e || window.event;
	    // устанавливаем текущюю ячейку
	    cur_cell = e.target || e.srcElement;
		var container = cur_cell.parentNode;
		var cellsArr = container.getElementsByTagName("DIV");
		for( var i = 0; i < cellsArr.length; i++){
			if(cellsArr[i] == cur_cell) break;
		}
		printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam[i].cmyk = cur_cell.innerHTML;
		// alert(print_r(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam));
		// console.log('--2--');
		// console.log(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam);
	}
	,
	onchangeYPriceParamSelect:function(YPriceParamDiv,YPriceParamCMYKdiv){
		// здесь нам надо пройти по всем селектам в YPriceParamDiv и собрать данные о выбранных полях
		// чтобы сохранить их в dataForProcessing а затем запустить printCalculator.makeProcessing();
		
		// затираем данные по цветам которые были до этого
		// ниже мы пройдем по существующим селектам проверим на то что они выбраны и поместим данные в этот объект заново, в том числе надо
		// добавить данные по CMYK
		if(printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam) printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam = [];
		
		var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
		var CMYKsArr = YPriceParamCMYKdiv.getElementsByTagName("DIV");
		
		// ЗДЕСЬ НЕ ПРАВИЛЬНЫЙ ПРОХОД, здесь идти по СИБЛИНГАМ потому-что в цикле используется удаление
		var ln = selectsArr.length;
		for( var i = 0; i < ln; i++){
			var value = selectsArr[i].options[selectsArr[i].selectedIndex].value;
			var item_id = selectsArr[i].options[selectsArr[i].selectedIndex].getAttribute('item_id');
			// если value != 0(0 равно вспомогательное значение "стандартно"), значит выбор в селекте сделан 
			// добавляем его в dataForProcessing
			if(value && value != 0){
				if(typeof CMYKsArr[i] !== 'undefined') printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.push({'id':item_id,'coeff':value,'cmyk':CMYKsArr[i].innerHTML}); 
				else   printCalculator.currentCalculationData[printCalculator.type].print_details.dop_params.YPriceParam.push({'id':item_id,'coeff':value}); 
				CMYKsArr[i].className = 'YPriceParamCMYK';
			}
			// если value == 0(0 равно вспомогательное значение "стандартно"), значит выбор в селекте не сделан
			// удаляем этот селект
			// if(value == 0) selectsArr[i].parentNode.parentNode.removeChild(selectsArr[i].parentNode);
			if(value == 0 && selectsArr.length>1){
				// удаление div содержащий label и select
				selectsArr[i].parentNode.parentNode.parentNode.removeChild(selectsArr[i].parentNode.parentNode);
				CMYKsArr[i].parentNode.removeChild(CMYKsArr[i]);
			}
		}
		
		// если количество селектов меньше количества рядов в прайсе открываем ссылку для добавления новых селектов (она могла быть скрыта)
		if(YPriceParamDiv.getElementsByTagName('SELECT').length <printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData[printCalculator.type].print_details.print_id].priceOut_tbl[0].length-1){
		   document.getElementById('calculatoraddYPriceParamLink').className = '';
		}
		
		// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblYindex);
		if(printCalculator.type=='auto') printCalculator.makeProcessing();
		else if(printCalculator.type=='manual') printCalculator.showCalcItogDisplay();
	}
	,
	build_priceTbl:function(tbl,type){
	 
			// строим таблицу с ценами
			var tbl = tbl[0];
			var tbl_html = document.createElement('TABLE');
			tbl_html.className = "calculatorPriceTbl";
			
			for(var row in tbl){
				var tr = document.createElement('TR');
				var td = document.createElement('TD');
				
				// создаем 1-ую колонку каждого ряда
				// если это первый ряд (указывающий на количество) первую колонку оставляем пустой
				// иначе вносим комбинацию данных значение параметра и его тип например (1 цвет)
				td.innerHTML = (row == 0)? '':tbl[row]['param_val']+' '+tbl[row]['param_type'];
				tr.appendChild(td);
				
				// перебераем ячейки таблицы озаглавленные 1,2,3,4 и т.д.
				
				/*if(row == 0){
					var maxColInex = 1;
					for(var counter = 1 ;typeof tbl[row][counter] !== 'undefined' ; counter++){
						if(parseFloat(tbl[row][counter])==0) break;
						maxColInex = counter;
					}
				}*/
				// перебераем ячейки таблицы озаглавленные 1,2,3,4 и т.д.
				for(var counter = 1 ;typeof tbl[row][counter] !== 'undefined'; counter++){
					var td = document.createElement('TD');  
					
					// если это первый ряд (указывающий на количество) преобразуем значения до Integer и добавляем тип параметра ( например 100 шт.)
					// иначе ввносим в ячейку оригинальное Float значение обозначающее стоимость 
					if(row == 0) var val = parseInt(tbl[row][counter])+' '+tbl[row]['param_type'];
					else var val = tbl[row][counter];
	
					
					td.innerHTML = val;
					tr.appendChild(td);
					
					
			        // собираем данные для расчета
			        // для определения текущей цены
					// этап 1 - определяем в какой диапазон входит количество товара, 
					// исходя из этого получаем индекс соответсвующей колонки таблицы цен
					if(row == 0){
						// если значение ячейки меньше или равно значения параметра quantity, значит мы еще не вышли из диапазона, значение сохраняем
						if(type=='out'){
							if(printCalculator.currentCalculationData[printCalculator.type].quantity < parseFloat(tbl[row][1])){
								var priceOut_tblXindex = 1;
								printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice = true;
								printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantOutPrice = parseInt(tbl[row][1]);
							}
						    else if(printCalculator.currentCalculationData[printCalculator.type].quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceOut_tblXindex = counter;
								if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice;
								if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantOutPrice !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantOutPrice;
							}
						}
						if(type=='in'){
							if(printCalculator.currentCalculationData[printCalculator.type].quantity < parseFloat(tbl[row][1])){
								var priceIn_tblXindex = 1;
								printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantInPrice = true;
								printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantInPrice = parseInt(tbl[row][1]);
							}
						    else if(printCalculator.currentCalculationData[printCalculator.type].quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceIn_tblXindex = counter;
								if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantInPrice !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantInPrice;
								if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantInPrice !== 'undefined') delete printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantInPrice;
							}
						}
						
						//// console.log(parseInt(tbl[row][counter])+' '+printCalculator.currentCalculationData[printCalculator.type].quantity);
					}
					
					
				}
				
				tbl_html.appendChild(tr);
				
			}
			// собираем данные для расчета
			// для определения текущей цены
			if(type=='out'){
				if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex=== 'undefined'){ 
				    // alert('out>'+priceOut_tblXindex);
					printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex = priceOut_tblXindex;
					// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.priceOut_tblXindex);
				}
			}
			else if(type=='in'){
			    if(typeof printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex=== 'undefined'){ 
				     // alert('in>'+priceIn_tblXindex);
					printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex = priceIn_tblXindex;
					// alert(printCalculator.currentCalculationData[printCalculator.type].print_details.priceIn_tblXindex);
				}
			}
				
			//// console.log(printCalculator.dataForProcessing['price']);
			if(printCalculator.type =='auto' && printCalculator.currentCalculationData[printCalculator.type].print_details.lackOfQuantOutPrice){
				var text = "Минимальный тираж для данного вида нанесения - "+printCalculator.currentCalculationData[printCalculator.type].print_details.minQuantOutPrice+"шт стоимость будет расчитана как для минимального тиража"
				echo_message_js(text,'system_message',4800);
			}
			return tbl_html;
			
	}
	,
	nextTag:function(node){ 
	   var node = node.nextSibling; 
	   return (node && node.nodeType!=1) ? this.nextTag(node) : node; 
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
}