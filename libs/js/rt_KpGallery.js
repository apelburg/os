//////////////////////////
//	uploadify
//////////////////////////
	function uploadify(data) {
		if(!$('#'+data['token']).length){
			setTimeout(uploadify(data), 1000);
			return;
		}
     // alert(data['timestamp']);
      $('#'+data['token']).uploadify({
        'formData'     : {
        	'AJAX'			: 'add_new_files_in_kp_gallery',
        	'timestamp' 	: data['timestamp'],
        	'token'     	: data['token'],
        	'gnom'      	: 'sdfdsfdsf',
        	'id'        	: data['id'],
        	'folder_name'	: data['folder_name']
        },
        'buttonText': 'Загрузить новое изображение',
        'width': 250,
        'swf'      	: '../libs/php/uploadify.swf',
        'uploader' 	: '',
        'multi'     : false,
        'onUploadSuccess' : function(file, data) {
            // alert('The file ' + file.name + ' uploaded successfully.');
            // подключаем стандартный обработчик ответа
            standard_response_handler(jQuery.parseJSON(data));
        }
      });
    }


    /**
     *	вызов перемещён в upWindowMenu.js
     *
     *	@author  	Алексей Капитонов
     *	@version 	16:01 21.12.2015
     */
	// запрос окна галлереи 
	// $(document).on('click', '.showImgGalleryWindow', function(event) {
	// 	var c = $(this).attr('data-control_num');
	// 	event.preventDefault();
	// 	$.post('', {
	// 		AJAX: 'getStdKpGalleryWindow',
	// 		id: $(this).attr('data-rt_id'),
	// 		control_num: $(this).attr('data-control_num'),
	// 		folder_name: $(this).attr('data-rt_folder_name')

	// 	}, function(data, textStatus, xhr) {
	// 		standard_response_handler(data);
	// 	},'json');
	// });

	// добавление изображений
	function rtGallery_add_img(data){
		$('#rt-gallery-images ul').append(Base64.decode(data['html']));
		
		rtGallery_scroll_bottom();// прокрутка скролла галлереи до инициализируем
	}

	// прокрутка скролла галлереи до инициализируем
	function rtGallery_scroll_bottom(){
		var block = $('#rt-gallery-images');
		$('#rt-gallery-images').animate({"scrollTop":99999 },"slow");
  		// block.scrollTop = block.scrollHeight;
	}

// возвращает JSON выбранных изображений
function get_json_checked_img() {
	
	var json = '[';
	
	$('#rt-gallery-images li.checked').each(function(index, el) {
		json += ((index > 0)?',':'')+'{"folder":"'+$(this).attr('data-folder')+'","img_name":"'+$(this).attr('data-file')+'"}';		
	});
	json += ']';
	return json;
}

$(document).on('dblclick', '#rt-gallery-images li', function(event) {
	event.preventDefault();
	if($(this).hasClass('checked')){
		$(this).removeClass('checked');
	}else{
		if($('#rt-gallery-images li.checked').length >= 4){
			var message = 'В КП не получиться загрузить более четырёх изображений';

			echo_message_js(message, 'system_message' ,25000);
			return false;
		}
		$(this).addClass('checked');
	}

	var id = $(this).parent().parent().parent().parent().find("input[name*='id']").val();

	// выбор изображения
	var folder_name = $(this).attr('data-folder');
	var img = $(this).attr('data-file');

	

	$('#data_JSON').val(get_json_checked_img());
	// $.parseJSON($('#data_JSON').val());

	// chooseKpPreview(img);
	// $.post('', {
		// AJAX 		:'chooseImgGallery',
		// folder_name :folder_name,
		// id 			:id,
		// img 		:img,
		// type 		:$(this).attr('data-type')
	// }, function(data, textStatus, xhr) {
		// standard_response_handler(data);
	// },'json');
	$('#data_folder_name').val(folder_name);
	$('#data_id').val(id);
	// $('#data_img').val(img);
	// $('#data_type').val($(this).attr('data-type'));
});

// выделение изображения избранного в КП в карточке артикула
function chooseKpPreview(img){
	if( $('#articulusImagesPrevBigImg').length ){
		$('#articulusImagesPrevBigImg .carousel-block').removeClass('kp_checked');
		$('#articulusImagesPrevBigImg .carousel-block img').each(function(index, el) {
			// echo_message_js(img+' = '+$(this).attr('data-file'), 'system_message',25000);
			if ($(this).attr('data-file') == img) {
				$(this).parent().addClass('kp_checked');
			};
		});
	}
}


$(document).on('click', 'li.rt-gallery-cont .delete_upload_img', function(event) {
	event.preventDefault();
	$(this).parent().removeClass('checked');
	$('#data_JSON').val(get_json_checked_img());
	echo_message_js('удалить', 'system_message' ,25000);
	// удаляем изображение	
	
	var img = $(this).parent().attr('data-file');
	var folder = $(this).parent().attr('data-folder');
	// alert(img);
	if( $('#data_delete_img').val() == ''){
		$('#data_delete_img').val(img);
	}else{
		$('#data_delete_img').val($('#data_delete_img').val()+','+img);
	}
	$('#data_delete_img_width_folder').val(folder);
	$(this).parent().hide('fast');


	
});
