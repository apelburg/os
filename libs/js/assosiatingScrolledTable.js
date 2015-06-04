// JavaScript Document

    window.addEventListener('load',assosiatingScrolledTable,false);
	
	function assosiatingScrolledTable(){
		//alert(1);
		var tables = document.getElementsByTagName('table');
		for(var i =0; i < tables.length ; i++ ){
			if(tables[i].getAttribute('scrolled') && tables[i].getAttribute('scrolled')=='header'){
				var tbl_header = tables[i];
				//alert(1);
			}
			if(tables[i].getAttribute('scrolled') && tables[i].getAttribute('scrolled')=='body'){
				//alert(2);
				var tbl_body = tables[i];
			}
		}
		if(!tbl_header || !tbl_body){
			//alert('no one of the assosiated pieces was found');
			//return;
		}
		else{
		
			tbl_header.style.width =tbl_body.offsetWidth  + 'px';
			var header_tr = tbl_header.getElementsByTagName('tr')[0];
			var body_tr = tbl_body.getElementsByTagName('tr')[0];
			//alert();
			var counter = 0;
			var header_tds = [];
			for(var n = header_tr.firstChild ; n != null ; n = n.nextSibling ){
				if(n.nodeType == Node.ELEMENT_NODE && n.tagName.toLowerCase() =='td'){//
					header_tds.push(n);
				}
				
			}
			
			var body_tds = [];
			for(var n = body_tr.firstChild ; n != null ; n = n.nextSibling ){
				if(n.nodeType == Node.ELEMENT_NODE && n.tagName.toLowerCase() =='td'){//
					body_tds.push(n);
				}
				
			}
			//alert(body_tds.length +' '+ header_tds.length);
			if(header_tds.length != body_tds.length){
				alert('num colls in header and body RT not equal');
				return;
			}
			else{
				for(var i =0; i < body_tds.length ; i++ ){
					header_tds[i].style.width = body_tds[i].offsetWidth + "px";
					//header_tds[i].innerHTML = "1";
					//body_tds[i].innerHTML = "2";
				}
			}
		}
		var container = document.getElementById('scroll_container');
		var top = define_top(container);
		container.style.height = Geometry.getViewportHeight() - top + 'px';
		//container.style.height = 400 + 'px';
		
		function define_top(element){
			var top = 0;
			while(element){
				top+= element.offsetTop;
				element = element.offsetParent;
			}
			return top;
		}
	}