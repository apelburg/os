/**
 *  Скрипт универсальной карточки товара
 *
 *  @author  Алексей Капитонов
 *  @version 16:13 14.12.2015
 */

 function togleImageGallery(button){
  // если процесс уже выпоняется, просто выходим из функции 
  if($('#articulusImages').hasClass('used_by_another_process')){return false;}
  // сохраняем положение
  
  var hidden = 1;
  // лочим на время выполнения процесса, чтобы избежать ошибки анимации
  $('#articulusImages').addClass('used_by_another_process');
  if ($('#articulusImages').hasClass('hidden')) {
    $(button).removeClass('hidden');
    // раскрываем
    $('#articulusImages').removeClass('hidden').css({"display":"block"}).animate({width:$('#articulusImages').attr('data-width'),opacity:1},800).parent().animate({width:'277px',opacity:1},800, function(){
      // снимаем блокировку
      $('#articulusImages').removeClass('used_by_another_process');
    });
  }else{
    
    // скрываем
    $('#articulusImages').addClass('hidden').attr('data-width', $('#articulusImages').innerWidth()).animate({width:'0'},800).parent().animate({width:'0px',opacity:0},800, function(){
      $(button).addClass('hidden');
      // снимаем блокировку
      $('#articulusImages').css({"display":"none"}).removeClass('used_by_another_process');
    });
    hidden = 0;

  }
  $.post('', {
    AJAX: 'save_image_open_close',
    id_row: $('#ja--image-gallety-togle').attr('data-id'),
    val:hidden
  }, function(data, textStatus, xhr) {
    standard_response_handler(data);
  },'json');
}

// клик по исходящей цене (цена из каталога)
$(document).on('click', '.row_price_out_one.price_out', function(event) {
  if($(this).find('input').attr('disabled') == 'disabled'){
    var message = "Чтобы редактировать цену, воспользуйтесь инструментом наценки";
    echo_message_js(message,'system_message',800);
  }
});

// вешаем редактор описания (названия) каталожного товара
$(document).on('click', '#js--edit-description', function(event) {
  event.preventDefault();
  if($(this).find('input').length == 0){

    var val = $(this).html();
    $(this).attr('data-old',val);

    var width_input = $(this).innerWidth();

    var input = $('<input/>',{
      'value':val,
      'type':'text',
        click:function(){
          event.preventDefault();
        },
        focus:function(){
          event.preventDefault();
        },
        blur:function(){
          if($(this).val() == val){
            js_edit_description_replace_back();
            return;
          }
          // сохранение 
          var row_id = $(this).parent().attr('data-id');
          var value = $(this).val();
            $.post('', {
              AJAX:'update_article_description_name',
              name:Base64.encode(value),
              row_id:row_id
            }, function(data, textStatus, xhr) {
              standard_response_handler(data);
            },'json');
          // возвращаем прежний вид таблице
          $(this).parent().html($(this).val());
        }
    }).css({'width':width_input});

    $(this).html(input).find('input').focus() 
  }  
});
// вешаем клик на артикул
$(document).on('click', '#js--edit-article', function(event) {
  event.preventDefault();
  if($(this).find('input').length == 0){

    var val = $(this).html();
    $(this).attr('data-old',val);
    var width_input = $(this).innerWidth();

    var input = $('<input/>',{
      'value':val,
      'type':'text',
        click:function(){
          event.preventDefault();
        },
        focus:function(){
          event.preventDefault();
        },
        blur:function(){
          if($(this).val() == val){
            js_edit_article_replace_back();
            return;
          }
          // сохранение 
          var row_id = $(this).parent().attr('data-id');
          var value = $(this).val();
            $.post('', {
              AJAX:'search_and_replace_article',
              art:value,
              row_id:row_id
            }, function(data, textStatus, xhr) {
              standard_response_handler(data);
            },'json');
          // возвращаем прежний вид таблице
          $(this).parent().html($(this).val());
        }
    }).css({'width':width_input});

    input.autocomplete({
      minLength: 2,
      source: function(request, response){
        console.log(request)
        $.ajax({
          type: "POST",
          dataType: "json",
            data:{
                AJAX: 'shearch_article_autocomlete', // показать 
                search: request.term // поисковая фраза
            },
          success: function( data ) {
            response( data );
          }
        });
      },
      select: function( event, ui ) {
        input.val(ui.item.value).blur();
      // input.blur();
      }    
    });

    input.data( "ui-autocomplete" )._renderItem = function( ul, item ) { // для jquery-ui 1.10+
      var img = $('<img/>',{
        'src':'http://www.apelburg.ru/'+item.img
      }).css({
        'maxWidth':'50px',
        'maxHeight':'50px'
      });
      var table = $('<table/>');
      var tr = $('<tr/>');
      var td1 = $('<td/>').css({'width':'50px','height':'50px','textAlign':'center'}).append(img);
      var td2 = $('<td/>').append(item.label);

      table.append(tr.append(td1).append(td2))

      


      return $("<li></li>")
      .data("ui-autocomplete-item", item) // для jquery-ui 1.10+
      .append(table)
      //.append( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
      // .append(  )
      .appendTo(ul);
    };

    $(this).html(input).find('input').focus() 
  }  
});

function js_edit_article_replace_back(){
  $('#js--edit-article').html($('#js--edit-article').attr('data-old'))
}
function js_edit_description_replace_back(){
  $('#js--edit-description').html($('#js--edit-description').attr('data-old'))
}


// кнопка переключатель цены в таблице расчета
$(document).on('click', '.js--button-out_ptice_for_tirage', function(event) {
  event.preventDefault();
  if($(this).hasClass('for_out')){
    $(this).removeClass('for_out').addClass('for_in').find('div').html('входящая<br>(сумма)');
    $('.calkulate_table:visible td:nth-of-type(6)').removeClass('for_out').addClass('for_in');
  }else{
    $(this).addClass('for_out').removeClass('for_in').find('div').html('исходящая<br>(сумма)');
    $('.calkulate_table:visible td:nth-of-type(6)').removeClass('for_in').addClass('for_out');
  }
  
});



function edit_calcPriceOut_readoly(){
  var message = "Чтобы редактировать исходящую цену, воспользуйтесь калькулятором";
  echo_message_js(message,'system_message',800);
}


// сохранение входящей стоимости за товар
$(document).on('keyup', '.tirage_and_price_for_one .row_tirage_in_one.price_in input', function(event) {
  // $(this).val()
  recalkulate_tovar();
  
  $.post('', {
    // global_change: 'AJAX',
    AJAX: 'save_price_in_out_for_one_price',
    price_in:$(this).val(),
    price_out:$(this).parent().parent().find('.row_price_out_one.price_out input').val(),
    dop_data: $('.variant_name.checked').attr('data-id')
  }, function(data, textStatus, xhr) {
    // console.log(data);
    recalculate_table_price_Itogo();
    standard_response_handler(data);
  },'json');
});

// сохранение входящей стоимости на прикрепленную доп. услугу
$(document).on('keyup', '.row_tirage_in_gen.uslugi_class.price_in input', function(event) {
  // пересчет услуг
  recalculate_services();
  
  $.post('', {
    // global_change: 'AJAX',
    AJAX: 'save_service_price_in',
    price_in:$(this).val(),
    dop_uslugi_id:$(this).parent().parent().attr('data-dop_uslugi_id')
    // dop_data: $('.variant_name.checked').attr('data-id')
  }, function(data, textStatus, xhr) {
    standard_response_handler(data);
    // пересчет итого
    recalculate_table_price_Itogo();    
  },'json');
});

// сохранение исходящей стоимости на прикрепленную доп. услугу
$(document).on('keyup', '.row_price_out_gen.uslugi_class.price_out_men input', function(event) {
  // пересчёт услуг
  recalculate_services();
  
  
  $.post('', {
    // global_change: 'AJAX',
    AJAX: 'save_service_price_out',
    price_out:$(this).val(),
    dop_uslugi_id:$(this).parent().parent().attr('data-dop_uslugi_id')
    // dop_data: $('.variant_name.checked').attr('data-id')
  }, function(data, textStatus, xhr) {
    standard_response_handler(data);
    // пересчет итого
    recalculate_table_price_Itogo();    
  },'json');
});


// изменение исходящей стоимости товара
$(document).on('keyup', '.row_price_out_one.price_out input', function(event) {
  chenge_price_out($(this));
});
function chenge_price_out(object){
  var row_id = $('.js--calculate_tbl-edit_percent:visible').attr('data-id');

  // дискаунт стоящий в html
  var discount = $('.js--calculate_tbl-edit_percent:visible').attr('data-val');
  // реальное значение исходящей цены за единицу товара хранящееся в базе
  var real_price_out = $('.js--calculate_tbl-edit_percent:visible').attr('data-real_price_out');

  // значение исх. цены введённое пользователем
  var input_price_out = object.val();


  // рассчитываем новую наценку
  var new_discount  = (input_price_out - real_price_out) / (real_price_out / 100); 
  if(discount != 0){
    // $('.js--calculate_tbl-edit_percent:visible span:nth-of-type(1)').html(((new_discount>0)?'+':'')+new_discount+ '%');  
    $('.js--calculate_tbl-edit_percent:visible span:nth-of-type(1)').html(new_discount+ '%');  
    // $('.js--calculate_tbl-edit_percent:visible span:nth-of-type(2)').html(real_price_out);
    price_out = real_price_out;
  }else{
    new_discount = 0;
    price_out = input_price_out;
    $('.js--calculate_tbl-edit_percent:visible span:nth-of-type(1)').html(new_discount);
    $('.js--calculate_tbl-edit_percent:visible span:nth-of-type(2)').html(input_price_out);
  }
  
  $.post('', {
    // global_change: 'AJAX',
    AJAX: 'variant_save_discount',
    discount:new_discount,
    price_out:price_out,
    row_id:row_id
  }, function(data, textStatus, xhr) {
    standard_response_handler(data);

  },'json');
  // пересчёт товара
  recalkulate_tovar();
  // пересчет итого
  recalculate_table_price_Itogo();   
}


// выбор вкладки варианта
$(document).on('click', '#variants_name .variant_name', function(){
  // меняем URL
  $.urlVar('varID_checked',$(this).attr('data-id'));
  // отработка показа / скрытия вариантов расчёта
  // при клике по кнопкам вариантов
  $('.variant_name').removeClass('checked');
  $(this).addClass('checked');  
  var id = $(this).attr('data-cont_id');
  $('.variant_content_block').css({'display':'none'});
  $('#'+id).css({'display':'block'});
  // смена функциональной кнопки / выбора основного варианта /
  test_chenge_archive_list();
  // расчет таблицы активного поля
  calkulate_table_calc();


  // если есть описание некаталога - копируем его вверх
  if( $('#js--characteristics-info').length > 0){
    $('#js--characteristics-info').html($('.variant_characteristics_and_delivery:visible').html());
    append_click();
  }
});
function append_click(){
  $('#js--characteristics-info .js--edit_true').click(function(event) {
      var table = $(this).parent().parent();

      if ($(this).find('input').length == 0) {
        var val = $(this).html();
        $(this).html($('<input/>',{
          'value':val,
          'type':'text',
            blur:function(){

              // сохранение 
              var variant_id = $('#all_variants_menu .variant_name.checked').attr('data-id');

              var value = $(this).val();
              // console.log($(this).parent())
              var name = $(this).parent().attr('data-type');

              var jsonObj = $.parseJSON($('#js--characteristics-info .js-json_info').html());

              if(jsonObj[name] && jsonObj[name] != value){
                jsonObj[name] = value;
                var json =  JSON.stringify(jsonObj);
                $('.js-json_info:visible').html( json );
                
                $.post('', {
                  AJAX:'save_dop_info_json',
                  main_row_id:$('#js--characteristics-info').attr('data-main_row_id'),
                  json:Base64.encode(json),
                  row_id:variant_id
                }, function(data, textStatus, xhr) {
                  standard_response_handler(data);
                },'json');
              }

              
              // возвращаем прежний вид таблице
              $(this).parent().html($(this).val());
            }

        })).find('input').focus()
      };
    });
}
$(document).ready(function($) {  
    $('#js--characteristics-info').html($('.variant_characteristics_and_delivery:visible').html());
    append_click();
});


/**
 * вызов окна галлереи
 * скрипт галлереи полность переписан, вызов боллее не требуется
 * на удаление
 */
// $(document).on('click', "#articulusImagesPrevBigImg #image_add",function(){
//   // echo_message_js('открыть галлерею','system_message');
//   $.post('', {
//         AJAX: 'getStdKpGalleryWindow',
//         id: $('#ja--image-gallety-togle').attr('data-id')
//       }, function(data, textStatus, xhr) {
//         standard_response_handler(data);
//       },'json');
// });

//Обработка клика на превью картинки
$(document).on('click', "#articulusImagesPrevBigImg .carousel-block",function(){ 
  //alert($(this).attr('src'))
  if($(this).attr('id')!="image_add"){
    $('#articulusImagesPrevBigImg .carousel-block').removeAttr('style').removeClass('checked');
    $(this).css({'border':'3px solid #92B73E','border-radius':'5px'}).addClass('checked');
    $('#articulusImagesBigImg img').attr('src',$(this).find('img').attr('data-src_IMG_link'));
    return false;
  }
});


//Обработка клика на стрелку вправо
$(document).on('click', ".carousel-button-right",function(){ 
  event.preventDefault();
  // echo_message_js('Обработка клика на стрелку вправо', 'system_message');
  var carusel = $(this).parents('.carousel');
  right_carusel(carusel);
  return false;
});

//Обработка клика на стрелку влево
$(document).on('click',".carousel-button-left",function(){ 
  event.preventDefault();
  // echo_message_js('Обработка клика на стрелку влево', 'system_message');
  var carusel = $(this).parents('.carousel');
  left_carusel(carusel);
  return false;
});

function left_carusel(carusel){
   var block_width = $(carusel).find('.carousel-block').outerWidth();
   $(carusel).find(".carousel-items .carousel-block").eq(-1).clone().prependTo($(carusel).find(".carousel-items")); 
   $(carusel).find(".carousel-items").css({"left":"-"+block_width+"px"});
   $(carusel).find(".carousel-items .carousel-block").eq(-1).remove();    
   $(carusel).find(".carousel-items").animate({left: "0px"}, 200);   
}
function right_carusel(carusel){
   var block_width = $(carusel).find('.carousel-block').outerWidth();
   $(carusel).find(".carousel-items").animate({left: "-"+ block_width +"px"}, 200, function(){
    $(carusel).find(".carousel-items .carousel-block").eq(0).clone().appendTo($(carusel).find(".carousel-items")); 
      $(carusel).find(".carousel-items .carousel-block").eq(0).remove(); 
      $(carusel).find(".carousel-items").css({"left":"0px"}); 
   }); 
}

// запрет редактирования варианта
function variant_edit_lock(data){
  console.log(data);
}

// снятие запрета редактирования варианта
function variant_edit_unlock(data){
  console.log(data);
}


// оповещение подсказка при недоступном редактировании позиции
$(document).on('click', '#order_art_edit_centr.not_edit', function(event) {
  event.preventDefault();
  var message = 'Редактирование информации недоступно';
  echo_message_js(message,'error_message');
  message = 'Статус запроса должен быть &laquo;В работе&raquo;';
  message += '<br><span style="text-transform:lowercase">Для смены статуса перейдите в кабинет и кликните на статус запроса.</span>';
  echo_message_js(message,'system_message');
});

// оповещение о запрете редактироввания варианта из истории

$(document).on('click', '.variant_content_block.archiv_opacity', function(event) {
  event.preventDefault();
  var message = 'Редактирование информации недоступно';
  echo_message_js(message,'error_message');
  message = 'Статус варианта &laquo;история&raquo;';
  var variant = $('.variant_name.checked').clone()
  variant.find('span').remove();
  variant = variant.html();
  message += '<br><span style="text-transform:lowercase">Чтобы отредактировать данные измените статус варианта расчета кликнув на цветное поле во вкладке &laquo;'+variant+'&raquo;</span>';
  echo_message_js(message,'system_message');
});


$(document).on('click', '.no_edit_class_disc', function(event) {
  event.preventDefault();
  var message = '<span style="text-transform:lowercase">Для редактирования исходящей стоимости обнулите скидку/наценку</span>';
  echo_message_js(message,'system_message');
});

$(document).on('focus', '#edit_variants_content input', function(event) {
  event.preventDefault();
  if(Number($(this).val()) == 0){
    // if()
    $(this).attr('old_val',$(this).val());
    $(this).val('');
  }
});
$(document).on('blur', '#edit_variants_content input', function(event) {
  // event.preventDefault();
  // console.log(Number($(this).attr('old_val').length))
  if( Number($(this).val()) == 0 ){
    
    
    if($(this).attr('old_val') == ""){
      $(this).val('0.00');
    }else{
      $(this).val($(this).attr('old_val'));  
    }
  }
});
