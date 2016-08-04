//////////////////////////
//	uploadify
//////////////////////////

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

