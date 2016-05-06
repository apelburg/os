$(document).on('click', '#drop_client_tbl', function(event) {
	$.post('', {
		AJAX: 'drop_old_client_tbl'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

$(document).on('click', '#create_client_tbl', function(event) {
	$.post('', {
		AJAX: 'create_client_tbl'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

$(document).on('click', '#copy_client_contact_info', function(event) {
	$.post('', {
		AJAX: 'copy_client_contact_info'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

$(document).on('click', '#delete_supplier_contact_info', function(event) {
	$.post('', {
		AJAX: 'delete_supplier_contact_info'
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});

$(document).on('click', '#copy_client_addres', function(event) {
	$.post('', {
		AJAX: 'copy_client_addres'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

$(document).on('click', '#copy_client_contact_info_contact_face', function(event) {
	$.post('', {
		AJAX: 'copy_client_contact_info_contact_face'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

// поставщики
$(document).on('click', '#copy_supplier_contact_info', function(event) {
	$.post('', {
		AJAX: 'copy_supplier_contact_info'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});
$(document).on('click', '#copy_supplier_addres', function(event) {
	$.post('', {
		AJAX: 'copy_supplier_addres'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});
$(document).on('click', '#copy_supplier_contact_info_contact_face', function(event) {
	$.post('', {
		AJAX: 'copy_supplier_contact_info_contact_face'
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
	},'json');
});

