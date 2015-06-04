// JavaScript Document
    var kpManager = {
		createLetterWIndow:function (){
			
			    var box = document.createElement('div');
				box.id = "mailSendDialog";
				box.style.width = '900px';
				box.style.display = "none";
				
				box.innerHTML = Base64.decode(kpManager.details.main_window_tpl);
				// help button
				box.appendChild(help.btn('kp.sendLetter.window'));
		        document.body.appendChild(box);
				
				
				//инициализируем и навешиваем  поля с данными которые будут отправлены на сервер
				kpManager.contfaceSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.client_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.client_mails[i].mail+'">'+kpManager.details.client_mails[i].person+' '+kpManager.details.client_mails[i].mail+'</option>';
				}
				kpManager.contfaceSelect.innerHTML = options_arr.join("\r\n");
				document.getElementById('mailSelectTo').appendChild(kpManager.contfaceSelect);
				
				
				kpManager.managerMailsSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.manager_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.manager_mails[i].mail+'">'+kpManager.details.manager_mails[i]+'</option>';
				}
				kpManager.managerMailsSelect.innerHTML = options_arr.join("\r\n");
				document.getElementById('mailSelectFrom').appendChild(kpManager.managerMailsSelect);
				
				// поле темы письма
				kpManager.mailSubject = document.createElement('div');
				kpManager.mailSubject.innerHTML = "Тема письма";
				kpManager.mailSubject.className = 'mailSubject';
				kpManager.mailSubject.contentEditable = "true";
				document.getElementById('mailSubject').appendChild(kpManager.mailSubject);
				
				// поле для ввода текста письма
				kpManager.textarea = document.createElement('div');
				kpManager.textarea.className = 'mailTextarea';
				kpManager.textarea.contentEditable = "true";
				document.getElementById('mailMessage').appendChild(kpManager.textarea);
				
				// поле отображения прикрепленного файла
                document.getElementById('attachedKpFileInput').value = kpManager.details.kp_filename;
				document.getElementById('attachedKpFile').innerHTML = kpManager.details.kp_filename.slice(kpManager.details.kp_filename.lastIndexOf("/")+1);
				
				
				
				$("#mailSendDialog").dialog({autoOpen: false,title: "Отправить коммерческое предложение",modal:true,width: 900,close: function() {this.remove();$("#mailResponseDialog").remove();kpManager.current_message_tpl =false;}});
				$("#mailSendDialog").dialog("open");/**/
		}
		,
		sendKpByMail:function (id){
		    
			kpManager.kp_id = id;
			//alert(id+" "+client_id+" "+manager_id);
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&send_kp_by_mail='+kpManager.kp_id;
			
			make_ajax_request(url,call_back);
			function call_back(response){
				
				//alert(response);
			    var response_obj = JSON.parse(response);
				kpManager.details = response_obj;
                if(kpManager.details.message_tpls) for(var prop in kpManager.details.message_tpls)kpManager.details.message_tpls[prop] = Base64.decode(kpManager.details.message_tpls[prop]);
				
				//for (var prop in response_obj) alert ( prop+' '+response_obj[prop]);
				kpManager.createLetterWIndow();
				
				
				
			}
	
		},
		sendKpByMailFinalStep:function (){
			
	        // подготавливаем к отправке текст сообщения
		    var  message = kpManager.textarea.innerHTML;
			message = encodeURIComponent(message);
			message = Base64.encode(message);
			
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search;
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        
			// снимаем данные о прикрепленных файлах
			var inputs = document.getElementsByTagName("input");
			var attached_files_arr = [];
			for( var i = 0 ; i < inputs.length ; i++ ){
				if(inputs[i].type == 'checkbox'){
					if(inputs[i].name == 'attachedFile'){
						if(inputs[i].checked == true) attached_files_arr.push(inputs[i].value);//alert(inputs[i].value);
				    }
				}
			}

			var pairs = 'send_kp_by_mail_final_step=';
		    pairs += '{';
			
			pairs += '"kp_id":"'+kpManager.kp_id+'",';
			pairs += '"to":"'+kpManager.contfaceSelect.options[kpManager.contfaceSelect.selectedIndex].value+'",';
			pairs += '"from":"andrey@apelburg.ru",';
			pairs += '"subject":"'+kpManager.mailSubject.innerHTML+'",';
			pairs += '"message":"'+message+'"';
			if(attached_files_arr.length) pairs += ',"attached_files":["'+attached_files_arr.join('","')+'"]';
			pairs += '}';
		
			
			make_ajax_post_request(url,pairs,call_back);
			function call_back(response){
				 //alert(response);
				 
				 response = JSON.parse(response);
				 var div = document.createElement('div');
				 div.id = "mailResponseDialog";
				 div.style.textAlign = "center";
				 div.style.display = "none";
				 div.innerHTML = response[1];
				 document.body.appendChild(div);
				 
				 // если отправка прошла удачно удаляем окно редактирования сообщения и обнуляем значение kpManager.current_message_tpl
				 if(response[0]){
					 $("#mailSendDialog").remove();
					 kpManager.current_message_tpl =false;
				 }
				 
				 $("#mailResponseDialog").dialog({autoOpen:false ,title:"Результат отправки письма",width: 1200,close: function() {this.remove();}});
				 $("#mailResponseDialog").dialog("open");
		    }
		}
		,
		setMessageTpl:function (name){
			// если есть текущий установленный шаблон то копируем его состояние в массив хранящий шаблоны
			if(kpManager.current_message_tpl) kpManager.details.message_tpls[kpManager.current_message_tpl] = kpManager.textarea.innerHTML;
			// записываем имя текущего шаблона
			kpManager.current_message_tpl = name;
			kpManager.textarea.innerHTML = kpManager.details.message_tpls[name];
		}
		,
		kpToPrint:function (version,param){
			//php script lies in client_folder_controller father dont work
			if(version == 'old') var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&kp_to_print=old{@}'+param;
			
			//alert(url);
	
			make_ajax_request(url,call_back);
			function call_back(response){
				//alert(response);
				var div = document.createElement('div');
				div.style.position = 'absolute';
				div.style.top = '0px';
				div.style.right = '0px';
				div.style.left = '0px';
				div.style.height = '2000px';
				div.style.backgroundColor = '#FF0000';
				var inner_div = document.createElement('div');
				inner_div.style.margin = 'auto';
				inner_div.style.border = '3px solid #CCC';
				inner_div.style.width = '600px';
				inner_div.innerHTML = response;
				div.appendChild(inner_div);
				document.body.appendChild(div);
				window.open();
				//window.print();
				
			}
		}
	}