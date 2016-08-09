//закрытие стандартного окна при ответе об успешном выполнении запроса 
$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
    //не отправляем, если это создание новой строки адреса
    var vl = $('.html_modal_window form input[name="AJAX"]').val();
    //
    if(vl=="edit_adress_row" || vl == "chenge_name_company"|| vl == "chenge_fullname_company" ){
        var str = $('.html_modal_window form').serialize();
        $.post('', str, function(data, textStatus, xhr) {
            // console.log(data);
            // console.log(data['response']);
            if(data['response']=='1' || data['response']=='OK'){
                if(data['response']=='OK'){
                    standard_response_handler(data);
                }

                $(".html_modal_window_body").html(data['text']).delay(1000)
                        .fadeOut('slow',function(){$('#bg_modal_window,.html_modal_window').remove();});                
            }
        }, "json");
    }
});


//
//      НАЗВАНИЕ КОМПАНИИ
//
//МЕНЯЕМ НАЗВАНИЕ КОМПАНИИ
$(document).on('dblclick', '#chenge_name_company', function(event) {
    
    // запрос окна
    $.post('', {
        AJAX: 'getWindowChengeNameCompany',
        id:$(this).attr('data-idrow'),
        company:$(this).html(),
        tbl:$(this).attr('data-tablename')
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});


//
//      ТЕЛЕФОНЫ
//

// ДОБАВЛЕНИЕ НОВОГО ТЕЛЕФОНА
$(document).on('click','.add_new_row_phone', function(){
    var obj = $(this);
    // убираем отмеченную кнопку
    $('.add_new_row_phone1').removeClass('add_new_row_phone1');
    // отмечаем последнюю нажатую
    obj.addClass('add_new_row_phone1');
    var num_row = obj.parent().parent().parent().parent().prev().children().children('tr').length;
    // подставим в скрытые поля формы информацию, дополнительную информацию необходимую для запроса
    // инфу берем из data атрибутов кнопки .add_new_row_phone
    $('#add_new_phone form input[name="parent_tbl"]').val($(this).attr('data-parenttable'));
    $('#add_new_phone form input[name="client_id"]').val($(this).attr('data-parent-id'));
    //создадим окно
    $( "#add_new_phone" ).dialog("option",'rou_num',num_row);
    $( "#add_new_phone" ).dialog( "open");
    //new_html_modal_window_new(html,name_window,buttons,'chenge_name_company', id_row, tbl);
});

// ОКНО ДОБАВЛЕНИЯ НОВОГО ТЕЛЕФОНА
$(function() {
    $( "#add_new_phone" ).dialog({
        autoOpen: false,
        width: "auto",
        buttons: {
            Ok: function() {
                //#add_new_phone - скрытый див из dialog_windows.tpl
                var get_form = $('#add_new_phone form').serialize();
                // выбираем данные из полей формы
                var type_phone = $('#type_phone').val();
                var phone = $('#phone_numver').val();
                var dopPhone = $('#dop_phone_numver').val();
                dopPhone = (dopPhone != "")?' доп.' + dopPhone:'';
                // параметр переданный в окно при его инициализации (количество строк в таблице)
                var rou_num = $(this).dialog('option', 'rou_num')+1;

                $( this ).dialog( "close" );
                $.post('', get_form, function(data, textStatus, xhr) {
                    // console.log('data ='+data+'; textStatus ='+textStatus+'; xhr ='+ xhr);
                    if (!isNaN(data)){//если вернулось число
                        //скорее всего вернулся id
                        //вносим изменения в DOM 
                        var html = '<tr><td class="td_phone">' + type_phone + ' ' + rou_num + '</td><td><div class="del_text" data-adress-id="'+ data +'">' + phone +  dopPhone + '</div></td></tr>';
                        // если это первая строка
                        if(rou_num==1){
                            // значит таблицы не, создаём её и вставляем перед кнопкой    
                            html = '<table class="table_phone_contact_information">'+html+'</table>';
                            $( ".add_new_row_phone1").before(html);
                        }else{
                            // либо вставляем строку в уже существующую таблицу
                            $( ".add_new_row_phone1").parent().parent().parent().parent().prev().append(html);
                        }                       
                        
                        $( ".add_new_row_phone1").removeClass('add_new_row_phone1')
                        //очищаем форму
                        $('#add_new_phone form').trigger( 'reset' );
                    }else{
                        //сообщаем, что что-то пошло не так
                        new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');

                    }
                });
                
            }
        }
    });
});

// УДАЛЕНИЕ ТЕЛЕФОНОВ И ДОПОЛНИТЕЛЬНЫХ КОНТАКТНЫХ ДАННЫХ
$(document).on('click', '.table_phone_contact_information #del_text,.table_other_contact_information #del_text', function(event) {
$(this).parent().parent().addClass('deleting_row');
$('#delete_one_contact_row').dialog("option",'id',$(this).prev().attr('data-adress-id'));
$('#delete_one_contact_row').dialog("open");
});

// ОКНО ПОДТВЕРЖДЕНИЯ УДАЛЕНИЯ ТЕЛЕФОНОВ И ДОПОЛНИТЕЛЬНЫХ КОНТАКТНЫХ ДАННЫХ
$(function() {
    $('#delete_one_contact_row').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Предупреждение',
        autoOpen : false,
        buttons: [
            {
                text: 'Да',
                click: function() {
                    d_elem = $(this);
                    $.post('', {
                        id:$(this).dialog('option', 'id'),
                        AJAX:"delete_dop_cont_row"

                    }, function(data, textStatus, xhr) {
                        if(data['response']=="OK"){
                            $('.deleting_row').remove();
                            d_elem.dialog( "close" );   
                        }  
                        standard_response_handler(data);                       
                        // }else{
                        //     $('.deleting_row').removeClass('deleting_row');
                        //     d_elem.dialog( "close" );
                        //     new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                        // }
                    },'json');
                    
                }
            },
            {
                text: 'Отмена',
                click: function() {
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

//
//      email, www, VK
//

// ДОБАВЛЕНИЕ НОВОЙ СТРОКИ
$(document).on('click','.add_new_row_other',function(){
    $(this).parent().parent().parent().parent().prev().addClass('new_row_dop_iformation');
    $('#new_other_row_info form input[name="parent_tbl"]').val($(this).attr('data-parenttable'));
    $('#new_other_row_info form input[name="client_id"]').val($(this).attr('data-parent-id'));

    $('#new_other_row_info').dialog("open");
});

// ОКНО ДОБАВЛЕНИЯ НОВОЙ СТРОКИ ДОП ИНФО
$(function() {
    var array_img = new Object();     
    array_img["email"] = '<img src="skins/images/img_design/social_icon1.png" >';
    array_img["skype"] = '<img src="skins/images/img_design/social_icon2.png" >';
    array_img["isq"] = '<img src="skins/images/img_design/social_icon3.png" >';
    array_img["twitter"] = '<img src="skins/images/img_design/social_icon4.png" >';
    array_img["fb"] = '<img src="skins/images/img_design/social_icon5.png" >';
    array_img["vk"] = '<img src="skins/images/img_design/social_icon6.png" >';
    array_img["other"] = '<img src="skins/images/img_design/social_icon7.png" >';
    array_img["web_site"] = '<img src="skins/images/img_design/social_icon7.png" >';

    
    $('#new_other_row_info').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Добавление дополнительной информации о клиенте',
        autoOpen : false,
        buttons: [
            {
                text: 'ОК',
                click: function() {
                    var dialog_w = $( this );
                    var str = $('#new_other_row_info form').serialize();
                    if($('#new_other_row_infoType').val()){//проверяем на заполнение
                        $.post('', str, function(data, textStatus, xhr) {// отправляем запрос
                            if (!isNaN(data)){//если вернулось число id
                                //скорее всего вернулся id
              
                                //копируем соседнее поле
                                var html2 = '<tr><td class="td_icons">'+array_img[$('#new_other_row_infoType').val()]+'</td><td><div class="del_text" data-adress-id="'+data+'">'+$('#input_text').val()+'</div></td></tr>';
                                $('.new_row_dop_iformation').append(html2);
                                $('.new_row_dop_iformation').removeClass('new_row_dop_iformation');
                                dialog_w.dialog( "close" );

                                $('#bg_modal_window,.html_modal_window').remove();
                            }else{
                                //сообщаем, что что-то пошло не так
                                new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                            }
                        });  
                    }else{
                        new_html_modal_window_new('Необходимо указать тип записи!!!','Предупреждение об ошибке','','', '', '');
                    }
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );
                }
            },
            {
                text: 'Отмена',
                click: function() {
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }
            }
       ]
    });
});

//
//      АДРЕС  
//
// СОЗДАНИЕ НОВОГО АДРЕСА
$(document).on('click', '.button_add_new_row.adres_row', function(event) {
    $.post('', {
        AJAX: 'new_adress_row',
        tbl:$(this).attr('data-tbl'),
        parent_id:$('#chenge_name_company').attr('data-idrow')
    }, function(data, textStatus, xhr) {       
        standard_response_handler(data);
    },'json');
});

function edit_general_info(data){
    $("#edit_general_info").load(" #edit_general_info");
}



// РЕДАКТИРОВАНИЕ ДОПОЛНИТЕЛЬНОЙ ИНФОРМАЦИИ ПО КЛИЕНТУ
$(document).on('dblclick','#client_dop_information', function(){
    $('#client_dop_information_cont_w').dialog("open");  
});

// ИНИЦИАЛИЗАЦИЯ ОКНА " РЕДАКТИРОВАНИЯ ДОПОЛНИТЕЛЬНОЙ ИНФОРМАЦИИ ПО КЛИЕНТУ "
$(function(){
    $('#client_dop_information_cont_w').dialog({
        width: 600,
        height: 'auto',
        title: 'Добавление дополнительной информации о клиенте',
        autoOpen : false,
        buttons: [
            {
                text: 'ОК',
                click: function() { 
                    var obj_window = $( this );        
                    var serialize = $('#client_dop_information_cont_w form').serialize();
                    $.post('', serialize, function(data, textStatus, xhr) {
                        if(data['response']=='1'){
                            // сохранение выполнено
                            $('#client_dop_information tr:nth-of-type(1) td:nth-of-type(2)').html($('#client_dop_information_cont_w form textarea').val());
                            $('#client_dop_information tr:nth-of-type(2) td:nth-of-type(2)').html('Z:/'+$('#client_dop_information_cont_w form input[name="ftp_folder"]').val());
                            obj_window.dialog( "close" ); 
                        }else{
                            obj_window.dialog( "close" ); 
                            new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                        }   
                    },"json");           
                    // $('.deleting_row').removeClass('deleting_row');
                    
                }
            },
            {
                text: 'Отмена',
                click: function() {
                    // $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }
            }
       ]
    });
});


// РЕДАКТИРОВАНИЕ АДРЕСА _old
$(document).on('dblclick', '.edit_adress_row', function(event) {
    var name_window = $(this).parent().prev().html();
    var element = $(this);
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '<img src="./test/skins/images/img_design/preloader.gif" >';  
    var id_row = ($(this).attr('data-adress-id') != "")?$(this).attr('data-adress-id'):'none';
    var tbl = 'CLIENT_ADRES_TBL';
    var buttons = $(this).attr('data-button-name-window');
    //вызываем окно редактирования адреса
    new_html_modal_window_new(html,name_window,buttons,'edit_adress_row', id_row, tbl);

    
    //получаем контент для редактирования адреса
    $.post('', {
        AJAX: 'get_adres', 
        id_row: id_row 
    }, function(data, textStatus, xhr) {
        $('.html_modal_window_body').html(data);  
        $('.html_modal_window form input:nth-of-type(1)').focus();
        $('.html_modal_window').animate({marginTop:$('.html_modal_window').innerHeight()/2*(-1)},200);      
    });  
    //при клике на сохранить...возвращаем всё, что нередактировали на страницу
    $('.green_bw.save_bw').click(function() {
        element.html(get_adress_info_form());
    });
});

// ВЫБОР ТИПА АДРЕСА    адрес офиса/адрес доставки
$(document).on('click','.type_adress',function(event) {    
    $('#dialog_gen_window_form form input[name="adress_type"]').val($(this).attr('data-type'));
    $('.type_adress').removeClass('checked');
    $(this).addClass('checked');
});

// БЫСТРОЕ ЗАПОЛНЕНИЕ ПОЛЯ ГОРОД В ОКНАХ ЗАПОЛНЕНЯ АДРЕСА
$(document).on('click','.city_fast',function(event) {
    $(this).parent().next().find('input').val($(this).attr('data-city'));
});

// УДАЛЕНИЕ СТРОКИ С АДРЕСОМ КЛИЕНТА
$(document).on('click', '.edit_general_info #del_text', function(event) {
    var del_div = $(this).prev();
    var id_row = (del_div.attr('data-adress-id') != "")?del_div.attr('data-adress-id'):'none';
    var tbl = (del_div.attr('data-tablename') != "")?del_div.attr('data-tablename'):'none';
     console.log(tbl+'');
    new_html_modal_window_new('Вы уверены, что хотите удалить данную запись? ','Подтвердите действие','ok','','','');   
    $('.ok_bw').click(function(event) {
        $.post('', {
            AJAX: 'delete_adress_row',
            id_row : id_row ,
            tbl : tbl
        }, function(data, textStatus, xhr) {            
            if(data['response']=='OK'){
                $('#bg_modal_window,.html_modal_window').remove();
                del_div.parent().parent().remove();
            }
            standard_response_handler(data);
        }, "json");
    });
});

//выбираем информацию об адресе из полей формы и выдаем её в строке, для вставки в HTML
function get_adress_info_form(){
    var cont_str = "";
        var ind = $('.html_modal_window_body input[name="postal_code"]').val();     
        cont_str+= ind;

        var city = $('.html_modal_window_body input[name="city"]').val();
        if(city!=""){cont_str+=", ";}
        cont_str+= city;

        var street = $('.html_modal_window_body input[name="street"]').val();
        if(street!=""){cont_str+=", ";}
        cont_str+= street;

        var house_number = $('.html_modal_window_body input[name="house_number"]').val();
        house_number = (house_number!="")?'дом № '+house_number:'';
        if(house_number!=""){cont_str+=", ";}
        cont_str+= house_number;

        var korpus = $('.html_modal_window_body input[name="korpus"]').val();
        korpus = (korpus!="")?'кор. '+korpus:'';
        if(korpus!=""){cont_str+=", ";}
        cont_str+= korpus;

        var office = $('.html_modal_window_body input[name="office"]').val();  
        office = (office!="")?'офис '+office:'';
        if(office!=""){cont_str+=", ";} 
        cont_str+= office;

        var liter = $('.html_modal_window_body input[name="liter"]').val();  
        liter = (liter!="")?'литера '+liter:'';
        if(liter!=""){cont_str+=", ";} 
        cont_str+=liter;


        var bilding = $('.html_modal_window_body input[name="bilding"]').val();
        bilding = (bilding!="")?'строение '+bilding:'';
        cont_str+=bilding;

        cont_str += '<br><span class="adress_note">' + $('.html_modal_window_body textarea').val() + '</span>';
        return cont_str;
}

// ПОКАЗ КНОПКИ УДАЛИТЬ
$(document).on('mouseover', '.del_text', function(event) {
    // наведение
    var af = $(this).offset();
    var wi = $(this).innerWidth()+af.left-1;
    if($('#del_text').length==0){
        $(this).parent().append('<div id="del_text"></div>');
    }else{
        $('#del_text').remove(); 
        $(this).parent().append('<div id="del_text"></div>');   
    }    
    $('#del_text').css({'top':af.top,'left':wi});
});

// СОКРЫТИЕ КНОПКИ УДАЛИТЬ
$(document).on('mouseleave', '.edit_general_info tr td:nth-of-type(2),.table_phone_contact_information tr td:nth-of-type(2),.table_other_contact_information tr td:nth-of-type(2)', function(){
    if(!$("#del_text").is(":hover")){
        $('#del_text').fadeOut('fast'); 
    }
});

//
//      КОНТАКТНЫЕ ЛИЦА
//

// РЕДАКТИРОВАНИЕ ИНФОРМАЦИИ О КОНТАКТНОМ ЛИЦЕ
$(document).on('dblclick','.contact_face_tbl_edit',function(){  
    // добавляем класс редактирования
    $(this).attr('id','contact_face_tbl_edit_enable');  
    

    var id = $(this).attr('data-contface');
    //делаем запрос, получаем в JSON
    $.post('',{AJAX:"show_cont_face_in_json",id : id},function(data){
        if(data[0]['id'] != id){
            // если id не соответствуют, значит ошибка
            // сообщаем об ошибке
            new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');  
            $('#contact_face_edit_form').dialog('close');
        }else{
            var cont_face = data[0];
            // подставляем данные в форму
            $('#contact_face_edit_form input[name="id"]').val(id);
            $('#contact_face_edit_form input[name="last_name"]').val(cont_face['last_name']);
            $('#contact_face_edit_form input[name="name"]').val(cont_face['name']);
            $('#contact_face_edit_form input[name="surname"]').val(cont_face['surname']);
            $('#contact_face_edit_form input[name="position"]').val(cont_face['position']);
            $('#contact_face_edit_form input[name="department"]').val(cont_face['department']);
            $('#contact_face_edit_form input[name="note"]').val(cont_face['note']);
            // открываем окно
            $('#contact_face_edit_form').dialog('open');
        }
    }, "json");
});

// ИНИЦИАЛИЗАЦИЯ ОКНА РЕДАКТИРОВАНИЯ ИНФОРМАЦИИ О КОНТАКТНОМ ЛИЦЕ 
$(function(){
    $('#contact_face_edit_form').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Добавление дополнительной информации о клиенте',
        autoOpen : false,
        buttons: [
            {
            text: 'Сохранить',
                click: function() {
                    var str = $('#contact_face_edit_form form').serialize();
                        $.post('', str, function(data, textStatus, xhr) {// отправляем запрос
                            if(data['response']=='1'){
                                // ЗАНОСИМ ДАННЫЕ В html
                                // заносим имя
                                $('#contact_face_tbl_edit_enable tr:nth-of-type(1) td:nth-of-type(2)').html("<strong>"+$('#contact_face_edit_form input[name="last_name"]').val()+" "+$('#contact_face_edit_form input[name="name"]').val()+" "+$('#contact_face_edit_form input[name="surname"]').val()+"</strong>");
                                // заносим должность
                                $('#contact_face_tbl_edit_enable tr:nth-of-type(2) td:nth-of-type(2)').html($('#contact_face_edit_form input[name="position"]').val());
                                // заносим отдел
                                $('#contact_face_tbl_edit_enable tr:nth-of-type(3) td:nth-of-type(2)').html($('#contact_face_edit_form input[name="department"]').val());
                                // заносим примечания
                                $('#contact_face_tbl_edit_enable tr:nth-of-type(4) td:nth-of-type(2)').html($('#contact_face_edit_form input[name="note"]').val());

                                // убираем метку редактирования с таблицы
                                $('#contact_face_tbl_edit_enable').removeAttr('id');
                                // очищаем форму
                                $('#contact_face_edit_form form').trigger( 'reset' );
                            }else{
                                new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                            }
                        },'json');
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }
            },{
            text: 'Отмена',
                click: function() {
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }}
        ]
    });    
});

// УДАЛЕНИЕ КОНТАКТНОГО ЛИЦА
 $(document).on('click','.delete_contact_face_table_button',function(){
    var id = $(this).attr('data-contface');
    $('#deleteing_row_cont_face').dialog('option','id',id);
    $('#deleteing_row_cont_face').dialog('open');
    $(this).parent().next().attr('id','delete_cont_f_row'+id);
 });

// окно ПОДТВЕРЖДЕНИЯ УДАЛЕНИЯ КОНТАКТНОГО ЛИЦА
$(function(){
    $('#deleteing_row_cont_face').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Удалить контактное лицо',
        autoOpen : false,
        buttons: [
            {
            text: 'OK',
                click: function() {
                    var id = $(this).dialog('option', 'id');
                    $.post('', {
                        id: id,
                        AJAX:"delete_cont_face_row"
                    }, function(data, textStatus, xhr) {
                        standard_response_handler(data);

                        if(data['response']=='OK'){
                            // $('.deleting_row').removeClass('deleting_row');
                            $('#delete_cont_f_row'+id).prev().remove();
                            $('#delete_cont_f_row'+id).remove();
                            $( this ).dialog( "close" );
                        }

                    }, "json");
                    // $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );
                }
            },
            {
            text: 'Отмена',
                click: function() {
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }
            }
        ]
    });
});

// ДОБАВЛЕНИЕ НОВОГО КОНТАКТНОГО ЛИЦА
$(document).on('click','#add_contact_face_new_form .button_add_new_row',function(){
    $('#contact_face_new_form').dialog("open");
});

// ИНИЦИАЛИЗАЦИЯ ОКНА " ДОБАВЛЕНИЕ НОВОГО КОНТАКТНОГО ЛИЦА "
$(function(){
    $('#contact_face_new_form').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Добавить контактное лицо',
        autoOpen : false,
        buttons: [
            {
            text: 'Сохранить',
                click: function() {
                    var serialize = $('#contact_face_new_form form').serialize();
                    $.post('', serialize, function(data, textStatus, xhr) {
                        if(data['response']=='1'){
                            // ЗАНОСИМ ДАННЫЕ В html
                                // заносим имя
                                $.post('', {AJAX:"get_empty_cont_face"}, function(data, textStatus, xhr) {
                                    // убираем старые данные
                                    $('.client_contact_face_tables,.delete_contact_face_table').remove();
                                    // вставляем новые данные
                                    $('#add_contact_face_new_form').before(data);
                                    // очищаем форму
                                    $('#contact_face_new_form form').trigger( 'reset' );
                                    $( this ).dialog( "close" );
                                });
                                
                        }else{
                            $('#contact_face_tbl_edit_enable').remove();
                            new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
                        }
                    }, "json");
                    $( this ).dialog( "close" );
                }
            },
            {
            text: 'Отмена',
                click: function() {
                    $('.deleting_row').removeClass('deleting_row');
                    $( this ).dialog( "close" );

                }
            }
        ]
        });
});


//
// окно создания клиента
$(document).on('click','#create_new_client',function(){
    // $('#create_client').dialog('open');
    $.post('', {
        AJAX: 'get_form_the_create_client',
        options:'for_me'
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});
$(function(){
    $('#create_client').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Завести нового клиента',
        autoOpen : false,
        buttons: [
            {
            text: 'Сохранить',
                click: function() {
                    var post = $('#create_client form').serialize();
                    $.post('', post, function(data, textStatus, xhr) {
                        if(data['response']=='1'){
                            // all Okey
                            window.location = "http://"+location.hostname+"/os/?page=clients&section=client_folder&subsection=client_card_table&client_id="+data['id']+"&client_edit";
                            $( this ).dialog( "close" );
                        }else if(data['response']=='2'){
                            new_html_modal_window_new(data['text'],'Предупреждение об ошибке','','', '', '');                            
                        }else{
                            $('#delete_cont_f_row'+id).removeAttr('id');
                            new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
                            $( this ).dialog( "close" );
                        }
                    }, "json");

                    
                }
            },
            {
            text: 'Отмена',
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
        });
});

// окно удаления клиента
$(document).on('click','#client_delete',function(){
    $('#client_delete_div').dialog('option','id',$(this).attr('data-id'));
    $('#client_delete_div').dialog('open');
});

$(function(){
    $('#client_delete_div').dialog({
        width: 500,
        height: 300,
        title: 'Укажите причину отказа от клиента',
        autoOpen : false,
        buttons: [
            {
            text: 'Продолжить',
                click: function() {
                    //if($('#client_delete_div textarea').val().length>15){
                        var send = $('#client_delete_div form').serialize();
                        var id = $(this).dialog('option', 'id')
                        $.post('', send, function(data, textStatus, xhr) {
                            standard_response_handler(data);
                        }, "json");

                        $( this ).dialog( "close" );
                    // }else{
                    //     alert("Пожалуйста напишите хотя бы несколько строк о причине вашего отказа от данного клиента");
                    // }
                }
            },
            {
            text: 'Отмена',
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
        });
});


// ДОБАВИТЬ КУРАТОРА
$(document).on('click','#add_curator',function(){
    var id = 'dialog_window_'+$('dialog_window').length;
    $('body').append('<div class="dialog_window" id="'+id+'"></div>');
    // создание кнопок
    var buttons = new Array();
    buttons.push({
    text: 'OK',
    click: function() {
        // закрыть окно выбора  
        $('this').dialog('close');
        // собираем введённые данные и отправляем на сервер id в json
        var id_man = new Object();      
        $('#'+id+' span.enabled').each(function(index, val) {
           id_man[index] = $(this).attr('data-id');
        });  
        var json = JSON.stringify(id_man);
        // console.log(json);
        $.post('', {AJAX:'update_curator_list_for_client',managers_id: json}, function(data, textStatus, xhr) {
             standard_response_handler(data);            
    },'json'); 
        $('.curator_names').remove();
        $('#'+id+' span.enabled').each(function(index, val) {
            $('#add_curator').before($(this).clone().removeAttr('class').addClass('add_del_curator curator_names').append('<span class="del_curator">X</span>'));
        });
        $('#'+id).remove();
    }
  });

  buttons.push({
    text: 'Отмена',
    click: function() {
      // закрыть окно выбора  
      $('this').dialog('close');     
      $('#'+id).remove();
    }
  });
  
  $('#'+id).dialog({
        width: 600,
        height: 'auto',
        title: 'Добавление куратора',
        autoOpen : false,
        buttons: buttons
  });
 
  $.post('', {AJAX: "get_manager_lis_for_curator"}, function(data) {
    $('#'+id).html(data);
    $('#'+id).dialog("option", 'id', id);
    $('#'+id).dialog("open");
  });
  
  
});

// УДАЛИТЬ КУРАТОРА
$(document).on('click','.del_curator',function(){
  var id = $(this).parent().attr('data-id');
  $(this).parent().remove();
  $.post('', {
    AJAX: 'remove_curator',
    id:id
  }, function(data, textStatus, xhr) {
    standard_response_handler(data);
  },'json');
});

//ВЫБОР КУРТОРА... добавление к тегу класса выбора
$(document).on('click','.chose_curators',function(){
  if($(this).hasClass('enabled') && !$(this).hasClass('enabled_first')){
    $(this).removeClass('enabled');
  }else{
    $(this).addClass('enabled');
  }
});

//////////////////////////
//  Реквизиты
//////////////////////////
// ОКНО РЕКВИЗИТЫ 
$(document).on('click',' #requisits_button', function(){
    $.post('', {
        AJAX:"get_requisites",
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});


function get_requisites_window(data){
    var html = Base64.decode(data['html']);
    var window_num = $('.ui-dialog').length;
    var title = (data['title'] !== undefined)?data['title']:'Название окна';
    var height = (data['height'] !== undefined)?data['height']:'auto';
    var width = (data['width'] !== undefined)?data['width']:'auto';
    
    var buttons = new Array();
    buttons.push({
        text: 'Добавить',
        click: function() {
            $.post('', {AJAX: "create_requesit"}, function(data, textStatus, xhr) {
              $('#create_requesit').html(data).dialog('open'); 
            });
            $( this ).dialog( "close" );                 
        }
    }); 
    buttons.push({
        text: 'Закрыть',
        click: function() {
            $( this ).dialog( "destroy" );
        }
    });         

    $('body').append('<div style="display:none" id="dialog_gen_window_form_'+window_num+'"></div>');            
    $('#dialog_gen_window_form_'+window_num+'').html(html);
    $('#dialog_gen_window_form_'+window_num+'').dialog({
        width: width,
        height: height,
        modal: true,
        title : title,
        autoOpen : true,
        buttons: buttons          
    });
}


// показать окно с добавлением новых реквизитов
$(function() {
    $('#create_requesit').dialog({
        width: ($(window).width()-2),
        height: ($(window).height()-2),
        position: [0,0],
        autoOpen : false,
        draggable: false,
        title: 'Добавить реквизиты',
        modal:true,
        buttons: [
            {
                text: 'Сохранить',
                click: function() {
                    $.post('', $("#create_requisits_form").serialize() , function(data, textStatus, xhr) {
                       standard_response_handler(data)
                    },'json');

                    $('#create_requesit').html('');
                    $( this ).dialog( "close" );                  
                }
            },
            {
                text: 'Отмена',
                click: function() {
                    $('#create_requesit').html('');
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});


// 
// РЕКВИЗИТЫ
//

// ПОКАЗТЬ РЕКВИЗИТЫ
$(document).on('click', '#requesites_form a', function(event) {
    event.preventDefault();
    $.post('', {
        AJAX: "show_requesit",
        id:$(this).attr('data-id'),
        title:$(this).attr('title')
    }, function(data, textStatus, xhr) {
        // $("#show_requesit").html(data);
        // $("#show_requesit").dialog('option', 'title', title);
        // $("#show_requesit").dialog("open");
        standard_response_handler(data);
    },'json');    
});

// ИНИЦИАЛИЗАЦИЯ ОКНА ПОКАЗА РЕКВИЗИТОВ
$(function(){
    $("#show_requesit").dialog({
        // width: ($(window).width()-2),
        width: 800,
        // height: ($(window).height()-2),
        // position: [0,0],
        autoOpen : false,
        draggable: false,
        modal:true,

        buttons: [
            {
                text: 'Закрыть',
                click: function() { 
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

function add_new_management_element(container_name){
      // get container
      var container = document.getElementById(container_name);
      
      var div_arr = get_divs(container);

      var new_element = div_arr[div_arr.length-1].cloneNode(true);
      clean_fiedls(new_element);
      
      if(new_element.getElementsByTagName('delete_btn')[0]) new_element.getElementsByTagName('delete_btn')[0].parentNode.removeChild(new_element.getElementsByTagName('delete_btn')[0]);

      container.appendChild(new_element);
      
      return false; 
       
      // функция копирования строки контактов из старой ОС
      function get_divs(div){
          var nodes_arr = div.childNodes;
          for(var i=0;i<nodes_arr.length;i++){
              if((nodes_arr[i].nodeName).toLowerCase()=='div'){
                  if(!div_arr) var div_arr = [];
                  div_arr.push(nodes_arr[i]);
              }
          }          
          return div_arr;
      }     
      // функция копирования строки контактов из старой ОС 2
      function clean_fiedls(element){
          var input_arr = element.getElementsByTagName("input");
          var select = element.getElementsByTagName("select");
          for(var i=0;i<select.length;i++){ 
              select[i].name = (select[i].name).slice(0,(select[i].name).indexOf('][')+2) + (parseInt((select[i].name).slice((select[i].name).indexOf('][')+2))+1) + (select[i].name).slice((select[i].name).lastIndexOf(']['));
          }
          for(var i=0;i<input_arr.length;i++){ 
              input_arr[i].name = (input_arr[i].name).slice(0,(input_arr[i].name).indexOf('][')+2) + (parseInt((input_arr[i].name).slice((input_arr[i].name).indexOf('][')+2))+1) + (input_arr[i].name).slice((input_arr[i].name).lastIndexOf(']['));


              if(!input_arr[i].getAttribute("field_type")) input_arr[i].value = ''; 
              if(input_arr[i].getAttribute("field_type") && input_arr[i].getAttribute("field_type") == 'id') input_arr[i].value = '';
              if(input_arr[i].getAttribute("field_type") && input_arr[i].getAttribute("field_type") == 'acting'){
                  input_arr[i].name = 'acting';
                  input_arr[i].value = '';
                  input_arr[i].checked = false;
              }
          }
      }



    function drop_radio_buttons(elem){ 
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
   }
$(document).on('click','.radio_acting',function(){
    $('.acting_check').val('0');
    $(this).parent().find('.acting_check').val(1);
});

// УДАЛЕНИЕ КОНТАКТНОГО ЛИЦА ИЗ РЕКВИЗИТОВ
$(document).on('click', '.cont_faces_field_delete_btn', function(){
    var id = $(this).attr('data-id');
    var e =$(this);
    var tbl = $(this).attr('data-tbl');
    // показываем UI confirm
    $( "#dialog-confirm" ).dialog({
            resizable: false,
            height:160,
            modal: true,
            buttons: {
                "Удалить": function() {
                    e.parent().parent().parent().parent().parent().remove();
                    $.post('', {AJAX:"delete_cont_requisits_row",id: id,tbl:tbl}, function(data, textStatus, xhr) {
                        if(data['response']!=1){
                            new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                        }
                    },'json');
                    $( this ).dialog( "close" );
                },
                Отмена: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    
});

// РЕДАКТОР РЕКВИЗИТОВ
$(document).on('click', '#requesites_form table tr td .edit_this_req', function(event) {
    var title = $(this).attr('title');
    // присвоим идентификатор для возможности отредактировать название
    $(this).parent().parent().find('a.show_requesit').attr('id','redaction_requsits_company');
    $.post('', {
        AJAX: "edit_requesit",
        id: $(this).attr('data-id')

    }, function(data, textStatus, xhr) {
        $("#edit_requesit").html(data);
        // console.log(data);
        $("#edit_requesit").dialog('option', 'title', title);
        $("#edit_requesit").dialog("open");
        // standard_response_handler(data);
    });    
});

// ИНИЦИАЛИЗАЦИЯ ОКНА РЕДАКТОРА РЕКВИЗИТОВ
$(function(){
    $("#edit_requesit").dialog({
        width: ($(window).width()-2),
        height: ($(window).height()-2),
        position: [0,0],
        autoOpen : false,
        draggable: false,
        modal:true,

        buttons: [
            {
                text: 'Сохранить',
                click: function() {
                    var post = $("#requisits_edit_form").serialize();
                    $('#redaction_requsits_company').text($('#form_data_company').val());
                    $.post('', post, function(data, textStatus, xhr) {
                      if(data['response']=='1'){ 
                        // обновляем имя компании
                                                     
                      }else{
                        new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
                      }
                    },'json');
                    $("#edit_requesit").html('');
                    //удаляем более ненужный id
                    $('#redaction_requsits_company').removeAttr('id');
                    $( this ).dialog( "close" );
                }
            },
            {
                text: 'Отменить',
                click: function() { 
                    $("#edit_requesit").html('');
                    //удаляем более ненужный id
                    $('#redaction_requsits_company').removeAttr('id');
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

//  ОТКРЫВАЕМ ОКНО ПОДТВЕРЖДЕНИЯ ДЛЯ УДАЛЕНИЯ РЕКВИЗИТОВ
$(document).on('click', '#requesites_form table tr td:nth-of-type(2) img:nth-of-type(2)', function(event) {
  var id = $(this).attr('data-id');
  $("#dialog-confirm2").dialog('option', 'id', id);
  $("#dialog-confirm2").dialog('open');
});

// ИНИЦИАЛИЗАЦИЯ ОКНА УДАЛЕНИЯ РЕКВИЗИТОВ
$(function(){
    $("#dialog-confirm2").dialog({
        width: 600,
        autoOpen : false,
        modal:true,
        buttons: [
            {
                text: 'Подтвердить',
                click: function() {
                    //alert(post);
                    var id_row = $(this).dialog('option', 'id');
                    $.post('', {
                        AJAX: "delete_requesit_row",
                        id:id_row
                        }, function(data, textStatus, xhr) {
                            if(data['response']=='OK'){ 
                                //убрать строку с названием реквизита из окна      
                                $('#requesites_form table tr td:nth-of-type(2) img:nth-of-type(2)').each(function(index, el) {
                                    if($(this).attr('data-id')==id_row){
                                        $(this).parent().parent().remove();  
                                    }
                                }); 
                            }                              
                            standard_response_handler(data);
                         
                    },'json');
                    $( this ).dialog( "close" );
                }
            },
            {
                text: 'Отменить',
                click: function() { 
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

// ДОБАВЛЕНИЕ ДОЛЖНОСТИ В РЕКВИЗИТЫ

$(document).on('click','.new_person_type_req',function(){
  $('#new_person_type_req').dialog('open');
});
// ОКНО ДОБАВЛЕНИЕ ДОЛЖНОСТИ В РЕКВИЗИТЫ
$(function(){
    $('#new_person_type_req').dialog({
        width: 600,
        autoOpen : false,
        modal:true,
        buttons: [
            {
                text: 'Добавить',
                click: function() {
                    var position = $('#new_person_type_req form input[name="position"]').val();
                    var position_in_padeg = $('#new_person_type_req form input[name="position_in_padeg"]').val();
                    if(position!="" && position_in_padeg !=""){
                      var post = $('#new_person_type_req form').serialize();
                      $.post('', post, function(data, textStatus, xhr) {
                          if(data['response']==1){
                            $('#chief_fields_div select').each(function(index, el) {
                              $(el).append('<option value="'+data['id_new_row']+'">'+position+'</option>');                   
                            });
                          }else{
                             new_html_modal_window_new('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                          }
                      },'json');
                      $('#new_person_type_req form').trigger( 'reset' );
                      $( this ).dialog( "close" );
                    }else{
                      new_html_modal_window_new('Чтобы добавить новую должность поля не должны быть пустыми','Предупреждение об ошибке','','', '', '');
                    }
                }
            },
            {
                text: 'Отменить',
                click: function() { 
                  $('#new_person_type_req form').trigger( 'reset' );
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

//standart function OS  
    function new_html_modal_window_new(html,head_text,buttons,form_name,id,tbl){
        
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



    // поиск клиентов по клику на enter
    $(document).keydown(function(event) {
        if(event.keyCode == 13) {
            if( $("#search_query").is(":focus") ){
                event.preventDefault();
                $("#search_query").parent().next().click();
                return false;
            }           
        }
    });