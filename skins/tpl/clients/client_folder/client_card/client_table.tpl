<style type="text/css">
    body .quick_bar_tbl td .quick_button_div{
      background: none;
    }
    .adress_note{ float: left; padding-top: 10px; color: rgb(176, 175, 175)}
    #requisits_button{display: block;float: right;padding: 3px 10px 2px 10px;background-color: #F3F5F5;border:1px solid #D0D7D8;position: absolute;right: 50%;margin-right: 20px; cursor: default;}
    #requisits_button:hover{ background-color: #E4E8E8;}
</style>
<script type="text/javascript" src="libs/js/client_card_table.js"></script>
<script type="text/javascript" src="libs/js/rate_client.js"></script>
<script type="text/javascript">


// ОКНО РЕКВИЗИТЫ 
$(document).on('click',' #requisits_button', function(){
    $('#requesites_form').dialog("open");
});
$(function() {
    $('#requesites_form').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Реквизиты',
        autoOpen : false,
        buttons: [
            {
                text: 'Добавить',
                click: function() {
                    $.post('', {ajax_standart_window: "create_requesit"}, function(data, textStatus, xhr) {
                      $('#create_requesit').html(data).dialog('open'); 
                    });     
                    
                    $( this ).dialog( "close" );                  
                }
            },
            {
                text: 'Закрыть',
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});


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
                    var post = $("#create_requisits_form").serialize();
                    //alert(post);
                    $.post('', post, function(data, textStatus, xhr) {
                       // new_html_modal_window(data,'данные','','', '', '');
                       if(data['response']=='1'){ 
                            $('#requesites_form table').append('<tr><td>'+($('#requesites_form table tr').length+1)+'. <a class="show_requesit" href="#" data-id="'+data['id_new_req']+'" title="'+data['company']+'">'+data['company']+'</a></td><td><img title="Редактор реквизитов" class="edit_this_req" data-id="'+data['id_new_req']+'" src="skins/images/img_design/edit.png" ><img title="Редактор реквизитов" class="delete_this_req" data-id="'+data['id_new_req']+'" src="skins/images/img_design/delete.png" ></td></tr>');
                        }else{
                          new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
                        }
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
    var title = $(this).attr('title');
    $.post('', {
        ajax_standart_window: "show_requesit",
        id:$(this).attr('data-id')
    }, function(data, textStatus, xhr) {
        $("#show_requesit").html(data);
        $("#show_requesit").dialog('option', 'title', title);
        $("#show_requesit").dialog("open");
    });    
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
                    $.post('', {ajax_standart_window:"delete_cont_requisits_row",id: id,tbl:tbl}, function(data, textStatus, xhr) {
                        if(data['response']!=1){
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
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

// УДАЛЕНИЕ РЕКВИЗИТОВ
$(document).on('click', '#requesites_form table tr td .edit_this_req', function(event) {
    var title = $(this).attr('title');
    // присвоим идентификатор для возможности отредактировать название
    $(this).parent().parent().find('a.show_requesit').attr('id','redaction_requsits_company');
    $.post('', {
        ajax_standart_window: "edit_requesit",
        id:$(this).attr('data-id')
    }, function(data, textStatus, xhr) {
        $("#edit_requesit").html(data);
        $("#edit_requesit").dialog('option', 'title', title);
        $("#edit_requesit").dialog("open");
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
                        new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
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
                        ajax_standart_window: "delete_requesit_row",
                        id:id_row
                    }, function(data, textStatus, xhr) {
                       if(data['response']!='1'){ 
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.<br>'+ data,'Предупреждение об ошибке','','', '', '');
                        }else{
                          $('#requesites_form table tr td:nth-of-type(2) img:nth-of-type(2)').each(function(index, el) {
                            if($(this).attr('data-id')==id_row){
                              $(this).parent().parent().remove();  
                            }
                          });
                          //убрать строку с названием реквизита из окна                          
                        } 
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
                             new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                          }
                      },'json');
                      $('#new_person_type_req form').trigger( 'reset' );
                      $( this ).dialog( "close" );
                    }else{
                      new_html_modal_window('Чтобы добавить новую должность поля не должны быть пустыми','Предупреждение об ошибке','','', '', '');
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


</script>


<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
<div id="requisits_button">Реквизиты</div>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td><strong><?php echo trim($client['company']); ?></strong></td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td><?php echo $clientRating; ?></td>
                    </tr>
                    <tr>
                        <td>Кураторы</td>
                        <td><?php echo $manager_names; ?></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><span  style="color:#f1f1f1">В разработке</span></td>
                    </tr>
                	<?php echo $client_address_s; ?>
                </table>

        	<td>
            	<?php echo $cont_company_phone; ?>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>