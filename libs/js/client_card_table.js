//
//      НАЗВАНИЕ КОМПАНИИ
//
//МЕНЯЕМ НАЗВАНИЕ КОМПАНИИ
$(document).on('dblclick', '#chenge_name_company', function(event) {
    var name_window = $(this).parent().prev().html();
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '';
    var type = $(this).attr('data-editType');
    var name = $(this).attr('name');
    if(type != "textarea"){
        html = '<input type="'+ type +'" name="'+name+'" onkeyup="$(\'#chenge_name_company\').html($(this).val());" value="'+$(this).html()+'">';
    }else{
        html = '<textarea type="'+ type +'" name="'+name+'"> '+$(this).text()+'</textarea>';
    }    
    var id_row = ($(this).attr('data-idRow') != "")?$(this).attr('data-idRow'):'none';
    var tbl = ($(this).attr('data-tableName') != "")?$(this).attr('data-tableName'):'none';
    var buttons = $(this).attr('data-button-name-window');
    buttons = (buttons!="")?buttons:'';
    new_html_modal_window(html,name_window,buttons,'chenge_name_company', id_row, tbl);
    $('.html_modal_window_body input:nth-of-type(1)').focus();
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
    //new_html_modal_window(html,name_window,buttons,'chenge_name_company', id_row, tbl);
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
                        new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');

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
                        ajax_standart_window:"delete_dop_cont_row"

                    }, function(data, textStatus, xhr) {
                        if(data=="OK"){
                            $('.deleting_row').remove();
                            d_elem.dialog( "close" );                            
                        }else{
                            $('.deleting_row').removeClass('deleting_row');
                            d_elem.dialog( "close" );
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                        }
                    });
                    
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
                                $('.new_row_dop_iformation').removeClass('.new_row_dop_iformation');
                                dialog_w.dialog( "close" );
                                $('#bg_modal_window,.html_modal_window').remove();
                            }else{
                                //сообщаем, что что-то пошло не так
                                new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                            }
                        });  
                    }else{
                        new_html_modal_window('Необходимо указать тип записи!!!','Предупреждение об ошибке','','', '', '');
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
    var prepend_s = $(this).parent().parent();
    
    var html = '<img src="http://www.os1.ru/os/skins/images/img_design/preloader.gif" >'; 
    var tbl = 'CLIENT_ADRES_TBL';
    new_html_modal_window(html,'Добавить адрес','greate','add_new_adress_row', '', tbl);
    $.post('', {ajax_standart_window: 'new_adress_row' }, function(data, textStatus, xhr) {
        $('.html_modal_window_body').html(data);  
        $('.html_modal_window form input:nth-of-type(1)').focus();
        //выравниваем окно
        $('.html_modal_window').animate({marginTop:$('.html_modal_window').innerHeight()/2*(-1)},200);
    });
    //добавляем поле для передачи parent_id
    $(".html_modal_window form").append('<input type="hidden" name="parent_id" value="'+  $('#chenge_name_company').attr('data-idrow') +'" >');
    //ОБРАБОТКА КЛИКОВ НА ЗЕЛЕНЫЕ КНОПКИ СТАНДАРТНОГО МОДАЛЬНОГО ОКНА
    $('.ok_bw, .send_bw, .greate_bw, .save_bw').click( function(event) {
        //отправляем, если это создание новой строки адреса
        if($('.html_modal_window form input[name="ajax_standart_window"]').val()=="add_new_adress_row"){
            var str = $('.html_modal_window form').serialize();
            $.post('', str, function(data, textStatus, xhr) {
                if (!isNaN(data)){//если вернулось число
                    //скорее всего вернулся id
                    //копируем соседнее поле
                    var html2 = '<tr><td>Адрес</td><td><div class="edit_row edit_adress_row del_text" data-tableName="CLIENT_ADRES_TBL" data-editType="input" data-adress-id="'+data+'" data-button-name-window="save">'+get_adress_info_form()+'</div></td></tr>';
                    prepend_s.before(html2);
                    $('#bg_modal_window,.html_modal_window').remove();
                }else{
                    //сообщаем, что что-то пошло не так
                    new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                }
            });   
        }
    });
});

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
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
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


// РЕДАКТИРОВАНИЕ АДРЕСА
$(document).on('dblclick', '.edit_adress_row', function(event) {
    var name_window = $(this).parent().prev().html();
    var element = $(this);
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '<img src="http://www.os1.ru/os/skins/images/img_design/preloader.gif" >';  
    var id_row = ($(this).attr('data-adress-id') != "")?$(this).attr('data-adress-id'):'none';
    var tbl = 'CLIENT_ADRES_TBL';
    var buttons = $(this).attr('data-button-name-window');
    //вызываем окно редактирования адреса
    new_html_modal_window(html,name_window,buttons,'edit_adress_row', id_row, tbl);

    
    //получаем контент для редактирования адреса
    $.post('', {ajax_standart_window: 'get_adres', id_row: id_row }, function(data, textStatus, xhr) {
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
    $('.html_modal_window form input[name="adress_type"]').val($(this).attr('data-type'));
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
    new_html_modal_window('Вы уверены, что хотите удалить данную запись? ','Подтвердите действие','ok','','','');   
    $('.ok_bw').click(function(event) {
        $.post('', {
            ajax_standart_window: 'delete_adress_row',
            id_row : id_row ,
            tbl : tbl
        }, function(data, textStatus, xhr) {            
            if(data['response']=='1'){
                $('#bg_modal_window,.html_modal_window').remove();
                del_div.parent().parent().remove();
            }else{
                new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
            }
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
    $.post('',{ajax_standart_window:"show_cont_face_in_json",id : id},function(data){
        if(data[0]['id'] != id){
            // если id не соответствуют, значит ошибка
            // сообщаем об ошибке
            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');  
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
                                new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
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
                    $.post('', {id: id,ajax_standart_window:"delete_cont_face_row"}, function(data, textStatus, xhr) {
                        if(data['response']=='1'){
                            // $('.deleting_row').removeClass('deleting_row');
                            $('#delete_cont_f_row'+id).prev().remove();
                            $('#delete_cont_f_row'+id).remove();
                            $( this ).dialog( "close" );
                        }else{
                            $('#delete_cont_f_row'+id).removeAttr('id');
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
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
                                $.post('', {ajax_standart_window:"get_empty_cont_face"}, function(data, textStatus, xhr) {
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
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
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