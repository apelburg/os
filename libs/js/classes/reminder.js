// плагин для сворачивания окон 
// только для окнон напоминаний
(function($){
var _init = $.ui.dialog.prototype._init;
  $.ui.dialog.prototype._init = function() {
    _init.apply(this, arguments);   
    
    // работаем только по окнам Планеровщика
    if (this.uiDialogTitlebar.parent().hasClass('reminder_win')) {
      var dialog_element = this;
      var dialog_id = this.uiDialogTitlebar.next().attr('id');
      var client_id = this.uiDialogTitlebar.next().attr('data-client_id');
      var manager_id = this.uiDialogTitlebar.next().attr('data-manager_id');
      var window_type = this.uiDialogTitlebar.next().attr('data-window_type');
      var event_type = this.uiDialogTitlebar.next().attr('data-event_type');

      this.uiDialogTitlebar.append('<span style="cursor:pointer" id="' + dialog_id + 
      '-minbutton" class="ui-dialog-titlebar-minimize ui-corner-all">'+
      '<span class="ui-icon ui-icon-minusthick"></span></span>');    
      $('#dialog_window_minimized_container').append(
        '<div class="dialog_window_minimized ui-widget ui-state-default ui-corner-all minimize_'+window_type+'"'+
        ' data-client_id="'+client_id+'"'+
        ' data-event_type="'+event_type+'"'+
        ' data-manager_id="'+manager_id+'"'+
        ' data-window_type="'+window_type+'"'+
        ' id="' + 
        dialog_id + '_minimized">&nbsp;<!--' + this.uiDialogTitlebar.find('.ui-dialog-title').text() + 
        '--><span class="ui-icon ui-icon-newwin"></div>');     
      $('#' + dialog_id + '-minbutton').hover(function() {
        $(this).addClass('ui-state-hover');
      }, function() {
        $(this).removeClass('ui-state-hover');
      }).click(function() {
        dialog_element.close();
        $('#' + dialog_id + '_minimized').show();
        $.post('', {
          ajax_reminder: "window_set_minimize",
          client_id: $('#' + dialog_id + '_minimized').attr('data-client_id'),
          event_type: $('#' + dialog_id + '_minimized').attr('data-event_type'),
          manager_id: $('#' + dialog_id + '_minimized').attr('data-manager_id'),
          window_type: $('#' + dialog_id + '_minimized').attr('data-window_type'),
          window_set_minimize: '1'
        }, function(data, textStatus, xhr) { /* ответ */});
      });   
      $('#' + dialog_id + '_minimized').click(function() {
        $(this).hide();
        $.post('', {
          ajax_reminder: "window_set_minimize",
          client_id: $(this).attr('data-client_id'),
          event_type: $(this).attr('data-event_type'),
          manager_id: $(this).attr('data-manager_id'),
          window_type: $(this).attr('data-window_type'),
          window_set_minimize: '0'
        }, function(data, textStatus, xhr) { /* ответ */});
        dialog_element.open();
        console.log('открыто окно - #'+dialog_element.uiDialogTitlebar.next().attr('id'));
      });      
    }    
  }; 
})($);


$(document).ready(function() {
  var timer_load_window = $('#dialog_window_minimized_container').attr('data-pause_time')*1000;  
  if($('#dialog_window_minimized_container').attr('data-loading')=="1"){
    // console.log('первый старт через '+timer_load_window+' секунд');
    setTimeout(get_ui_window, timer_load_window);
  }

  // создаём окно информации
  add_info_window();  
});


$(document).on('click',function(e){
  // разрешаем стандартное контекстное меню браузера
  document.oncontextmenu = function() {return true;};
  // скрываем окно меню
  $('#cont-menu').remove();
});
$(window).scroll(function(){
  // разрешаем стандартное контекстное меню браузера
  document.oncontextmenu = function() {return true;};
  // скрываем окно меню
  $('#cont-menu').remove();
});

$(document).on('mousedown','.red_t .ui-dialog-titlebar,.need_approval_t .ui-dialog-titlebar,.green_t .ui-dialog-titlebar,.yellow_t .ui-dialog-titlebar,.need_new_event_t .ui-dialog-titlebar,.black_t .ui-dialog-titlebar',function(event){
  // запрет контекстного меню браузера
  document.oncontextmenu = function() {return false;};  
  // Блокируем всплывание события contextmenu
    event = event || window.event;
    event.cancelBubble = true;

  event.preventDefault();
  if(event.button == 2){   
    $('#cont-menu').remove();
    event.preventDefault(); 
    var offset = $(this).offset();
    var relativeX = (event.pageX - offset.left);
    var relativeY = (event.pageY - offset.top);
    var fixedX = $(this).parent().offset().left+relativeX;
    var fixedY = $(this).parent().offset().top+relativeY;
    // console.log("X: " + fixedX + "  Y: " + fixedY);
    // console.log("X: " + relativeX + "  Y: " + relativeY);
    // создание объекта
    var obj = $('<div/>',{
      id:'cont-menu',
      style: " top:"+fixedY+"px;left:"+fixedX+"px;"
    });

    var lang = ['Свернуть зелёные', 'Свернуть жёлтые', 'Свернуть красные','Свернуть напоминания', 'Свернуть ВСЕ окна'];
    var func = ['minimize_green()', 'minimize_yellow()', 'minimize_red()','minimize_need_new_event()','minimize_ui_window()'];
    var mylist = $('<ul/>');
    //Наполняем
    $.each(lang, function(index) {
    $('<li/>',{text:this,onclick:func[index]}).appendTo(mylist);
    });
    //добавляем меню в блок
    mylist.appendTo(obj);
    $('body').append(obj);
  
    event.stopPropagation();
    return false;
  } 

});



function minimize_ui_window(){
  $('.reminder_win .ui-icon-minusthick').click();
}

function minimize_green(){
  $('.green_t .ui-icon-minusthick').click();
}

function minimize_yellow(){
  $('.yellow_t .ui-icon-minusthick').click();
}

function minimize_red(){
  $('.red_t .ui-icon-minusthick').click();
}

function minimize_need_new_event(){
  $('.need_new_event_t .ui-icon-minusthick').click();
}

$(document).on('click', '.leter_div', function(event) {
  $('.leter_div.check').removeClass('check');
  $(this).addClass('check');
  var value = $(this).attr('data-val');
  $(this).parent().find('input[name="time"]').val(value);
  /* Act on the event */
});

var warning_messages;
function get_ui_window(){
  console.log('get_ui_window')
  // убиваем старые окна
  if($('.reminder_win').length){
    $('.reminder_win').each(function(index, el) {
      $(this).find('.dialog_window').remove();
    });
    $('.dialog_window_minimized ').remove();
    $('.reminder_win').remove();

    $('.dialog_window').parent().remove();
  }


  if($('#num_window_yet').length==0){
    $('body').append('<div id="num_window_yet" data-window_num="1" style="position: fixed;bottom: 0px;right: 35px;color: red;background: #fff;padding: 6px 6px 6px 6px;border: 1px solid;border-color: #D3BABA;"></div>');
  }
  
  var col1 = 0;
  var col = 0;
  
  $.post('',{
      ajax_reminder:'get_alert_planer',
    }, function(data, textStatus, xhr) {  
      var response = jQuery.parseJSON(data);
      $.each(response, function(index, val) {        
          //console.log(index);          
          switch(index){
            case 'warnings':
              warning_messages = val;
              /*делаем что-то с сообщениями*/
              $.each(val, function(index, val) {
                // console.log(index);
                var type = index; // green, black
                // if(type=='last_update'){var last_update = val}
                // if(type=='was_shown'){var was_shown = val}
                var class_w = type+'_t reminder_win';
                $.each(val, function(index, val) {
                  var type_action = index;
                  // console.log(index);
                  var event_type = index;  
                  // console.log(val['type']);
                  $.each($(this), function(index, val) {
                    // console.log(val['client_name']);
                    var text = "ВНИМАНИЕ!!! ";  
                    var type_window2 = ((event_type=="звонок")?'phone':'go');
                    var href ='<strong>&laquo;'+ val['client_name']+'&raquo;</strong><br/>';
                    text = text + ((event_type=="звонок")?'ВЫ не совершали звонка клиенту':'У вас небыло встречи с клиентом') + '' + href; 
                    var win_minimized = val['win_minimized'];
                    // console.log(win_minimized);
                    var plan_id =  'none';
                    switch(type){
                        case 'black':
                          win_warning_type = 'Предупреждение';
                          // встречи по идее быть не должно, но на всякий случай предусмотрим
                          text = text + ' более ' + ((event_type=="звонок")?'50':'90+') + ' дней!';
                          break;
                        case 'red':
                          win_warning_type = 'Предупреждение';
                          text = text + ' более ' + ((event_type=="звонок")?'40':'90') + ' дней!';
                          break;
                        case 'yellow':
                          win_minimized = 1;
                          win_warning_type = 'Предупреждение';
                          text = text + ' более ' + ((event_type=="звонок")?'35':'80') + ' дней!';
                          break;
                        case 'green':
                          return true;
                          win_warning_type = 'Предупреждение';
                          text = text + ' более ' + ((event_type=="звонок")?'30':'75') + ' дней!';
                          break;
                        case 'need_new_event':
                          // *****
                          // убраны зеленые напоминалки по просьбе Славы
                          return true;
                          // *****
                          win_warning_type = 'Напоминание';
                          text = "Вы не запланировали " + ((event_type=="звонок")?'звонок<br>клиенту':'встречу<br>с клиентом')+" "+href+"";
                          break;                          
                        case 'need_approval':
                          text = '';
                          plan_id = val['plan_id'];
                          type_window2 = 'approval';
                          win_warning_type = 'Подтверждение';
                          var plan = (val['plan']!="")?'<div class="quote_t">'+val['plan']+'</div>':'<div class="quote_t_red">план отсутствует</div>';
                          var resultat = (val['result']!="")?'<div class="quote_t">'+val['result']+'</div>':'<div class="quote_t_red">комментарий отсутствует</div>';
                          var href2 = '<a  target="_blank" href="http://www.apelburg.ru/os/?page=client_folder&section=planner&client_id='+val['client_id']+'">&laquo;'+ val['client_name']+'&raquo;</a>';
                          // console.log(val['close_manager_id']+' '+val['close_manager_name']);
                          if(Number(val['close_manager_id'])!=0 && val['close_manager_id']!=val['manager_id']){
                            text += ''+
                            '<strong>'+val['close_manager_name']+
                            ' ' + val['close_manager_lastname']+
                            '</strong><br> выполнил(а) встречу с клиентом<br>(встречу заводил(а) <strong>'+
                            ' ' + val['manager_name']+
                            ' ' + val['manager_lastname']+
                            '</strong>)<br> ';                           
                          }else{
                            text = ''+
                            '<strong>'+val['manager_name']+
                            ' ' + val['manager_lastname']+
                            '</strong> выполнил(а) встречу с клиентом<br> ';
                          }
                          text += ''+href2+'<br>'+
                                  '<strong>Менеджер поставил план:</strong><br>'+
                                  plan+
                                  '<strong>Результат встречи:</strong><br>'+
                                  resultat;
                          break;
                        default:
                          win_warning_type = 'неизвесный тип окна';
                          text = "неизвесный тип окна";
                          break;
                    }
                    // console.log(event_type);
                    var next = 30;
                    $('#num_window_yet').attr('data-next',next).html(col-col1);
                    // console.log(col);
                    col++;
                    if(col<next){
                      col1++;
                      add_new_window(text,win_warning_type,class_w,val['client_id'],val['manager_id'],type,type_window2,win_minimized,event_type,plan_id);
                    }
                    
                    
                  });
                });
              });
              sort_ui_window();
              //разбиваем слои окон по их важности
              $('.need_approval_t').css({'z-index':'110'});
              $('.black_t').css({'z-index':'100'});
              $('.red_t').css({'z-index':'90'});
              $('.yellow_t').css({'z-index':'80'});
              $('.green_t').css({'z-index':'70'});
              $('.need_new_event_t').css({'z-index':'60'});
              if($('#num_window_yet').html()=='0'){
                $('#num_window_yet').hide();
                return 1;
              }else{
                $('#num_window_yet').show();
                return 1;
              }  
              break;
            case 'last_update':
              var last_update = val;
              var UNIX_TIMESTAMP = Math.round(new Date().getTime() / 1000);
              // прошло времени с последнего обновления
              var last_time = UNIX_TIMESTAMP - last_update;
              // инициируем повторный запуск функции
              // определяем установленный из PHP интервал
              var timer_load_window2 = $('#dialog_window_minimized_container').attr('data-update_interval');
              // определяем минимальный интервал в пол часа,
              // если указанный через PHP интервал больше получаса используем его
              var min_timer = 600;
              if(timer_load_window2!=0 && Number(timer_load_window2)>=min_timer){
                var timer = timer_load_window2*1000-last_time;
                // console.log('старт из html через '+timer+' милисекунд');
                setTimeout(get_ui_window, timer);
              }else{
                var timer = min_timer*1000 - last_time;
                // console.log('старт из js через '+timer+' милисекунд');
                setTimeout(get_ui_window, timer);
              }
              break;
            case 'was_shown':
              if(Number(val)==0){
                $.post('', {ajax_reminder:'session_was_shown'}, function(data, textStatus, xhr) {/**/});
              }
              break;
            default:
              /*не предусмотренная отдача*/
              break;
          }          
      });
    });
      
}

// открыть ещё одно скрытое окно
function get_ui_one_window(){
  var col = 0;
  // определяем количество скрытых (не показанных) окон
  var num_1 = Number($('#num_window_yet').html());
  // если нечего открывать выходим из функции
  if($('#num_window_yet').html()=='0'){
    $('#num_window_yet').hide();
    return 1;
  }else{
    $('#num_window_yet').show();    
  }
  // вычитаем одно
  $('#num_window_yet').html((num_1-1));

  // определяем количество показанных окон
  var num_2 =Number($('#num_window_yet').attr('data-next'));
  // прибавляем 1 и сохраняем
  var data_next = num_2+1;
  $('#num_window_yet').attr('data-next',data_next);

  //$('#num_window_yet').attr('data-next',data_next);
  $.each(warning_messages, function(index, val) {          
      var type = index; // green, black
      var class_w = type+'_t  reminder_win';
      $.each(val, function(index, val) {
        var type_action = index;
        var event_type = index;  
        $.each($(this), function(index, val) {
          col++;
          if(col<num_2){
            return true;//переходим к следующей интерации
          }
                    var text = "ВНИМАНИЕ!!! ";  
                    var type_window2 = ((event_type=="звонок")?'phone':'go');
                    var href ='<strong>&laquo;'+ val['client_name']+'&raquo;</strong><br/>';

                    text = text + ((event_type=="звонок")?'ВЫ не совершали звонка клиенту':'У вас небыло встречи с клиентом') + '' + href; 
          var win_minimized = val['win_minimized'];
          var plan_id = 'none';
          switch(type){
              case 'black':
                win_warning_type = 'Предупреждение';    
                // встречи по идее быть не должно, но на всякий случай предусмотрим
                text = text + ' более ' + ((event_type=="звонок")?'50':'90+') + ' дней!';
                break;
              case 'red':
                win_warning_type = 'Предупреждение';
                text = text + ' более ' + ((event_type=="звонок")?'40':'90') + ' дней!';
                break;
              case 'yellow':
                win_warning_type = 'Предупреждение';
                text = text + ' более ' + ((event_type=="звонок")?'35':'80') + ' дней!';
                break;
              case 'green':
                return true;
                win_warning_type = 'Предупреждение';
                text = text + ' более ' + ((event_type=="звонок")?'30':'75') + ' дней!';
                break;
              case 'need_new_event':
                // *****
                // убраны зеленые напоминалки по просьбе Славы
                return true;
                // *****
                win_warning_type = 'Напоминание';
                          text = "Вы не запланировали ни " + ((event_type=="звонок")?'одного звонка':'одной встречи')+"<br>для клиента "+href+"";
                break;                          
              case 'need_approval':
                plan_id = val['plan_id'];
                type_window2 = 'approval';
                win_warning_type = 'Подтверждение';
                var plan = (val['plan']!="")?'<div class="quote_t">'+val['plan']+'</div>':'<div class="quote_t_red">план отсутствует</div>';
                var resultat = (val['result']!="")?'<div class="quote_t">'+val['result']+'</div>':'<div class="quote_t_red">комментарий отсутствует</div>';
                var href2 = '<a  target="_blank" href="http://www.apelburg.ru/os/?page=client_folder&section=planner&client_id='+val['client_id']+'">&laquo;'+ val['client_name']+'&raquo;</a>';
                text = '';
                if(Number(val['close_manager_id'])!=0 && val['close_manager_id']!=val['manager_id']){
                            text = ''+
                            '<strong>'+val['close_manager_name']+
                            ' ' + val['close_manager_lastname']+
                            '</strong><br> выполнил(а) встречу с клиентом<br>(встречу заводил(а) <strong>'+
                            ' ' + val['manager_name']+
                            ' ' + val['manager_lastname']+
                            '</strong>)<br> ';                            
                          }else{
                            text = ''+
                            '<strong>'+val['manager_name']+
                            ' ' + val['manager_lastname']+
                            '</strong> выполнил(а) встречу с клиентом<br> ';
                          }
                          text += ''+href2+'<br>'+
                                  '<strong>Менеджер поставил план:</strong><br>'+
                                  plan+
                                  '<strong>Результат встречи:</strong><br>'+
                                  resultat;
                break;
              default:
                text = "не извесный тип окна";
                break;
          }
          
          if(col==num_2){
            //console.log(col+' - '+num_2);
            add_new_window(text,win_warning_type,class_w,val['client_id'],val['manager_id'],type,type_window2,'1',event_type,plan_id);
          }else if(col>num_2){
            return false; // выходим из функции
          }              
          sort_ui_window();
        });
    });
  });
}

function sort_ui_window(){
  window.x1 = ($(window).width() / 2)-250;
  window.y1 = ($(window).height() / 2)-50;
  window.alpha = 1; // шаг в градусах;
  $('.ui-dialog.reminder_win').each(function(index, el) {
    var R = ++index*2; // радиус окружности        
    //var pi = (Math.P); // число ПИ
    window.alpha = window.alpha + 30; // шаг в градусах;
    var alpha = window.alpha;        
    var y_new = R * Math.sin(alpha);
    var x_new = R * Math.cos(alpha);
    var x = x_new+window.x1;
    var y = y_new+window.y1;
    //var okrugnost = 2+pi*Math.pow(R,2);  //формула окружности
    $(this).animate({'top': y,'left': x},'fast');
  });
}

function get_new_id_window(){
  var div_count = Number($('#num_window_yet').attr('data-window_num')) + 1;
  $('#num_window_yet').attr('data-window_num',div_count);
  var div_id = 'dialog_window_' + div_count;
  return div_id;
}
function add_new_window(cont,title,class_d,client_id,manager_id,type_w,type_window2,win_minimized,event_type,plan_id){
  var div_id = get_new_id_window();
  var div_title = title;
  var div_content = cont;
  var window_width = 500;
  var window_height = 'auto';
  // создание кнопок
  var buttons = new Array();
      

    if(type_w!="need_approval"){
    if(type_w!="black"){
      buttons.push({
      text: 'Отложить',
      css:{'float':'left'},
      click: function() {
        var open_win_top = $(this).offset().top+33;
        var open_win_left = $(this).offset().left+20;
        // СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ ВЫБОРА ОТСРОЧКИ ОПОВЕЩЕНИЯ
        var parent_window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
        var div_id = get_new_id_window();
        var div_title = 'Отложить на...';

        // собираем форму LETER 
        // ОТПРАВКА ДАННЫХ ОТЛОЖИТЬ
        var obj_form = $('<form/>',{id:'leter_id','style':'min-width:200px'});      
        $('<input/>',{type:'hidden',name:'ajax_reminder',val:'remaind_after'}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'client_id',val:client_id}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'manager_id',val:manager_id}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'window_type',val:type_w}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'event_type',val:event_type}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'button',val:'remaind_after'}).appendTo(obj_form);
        $('<input/>',{type:'hidden',name:'time',val:'0'}).appendTo(obj_form);
        switch(type_window2){
          case 'phone':
            // 1,2,3 часа и 1,2,3 дня
            $('<div/>',{class:'leter_div',html:'1 час'}).attr('data-val',(60*60)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'2 часа'}).attr('data-val',(60*60*2)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'3 часа'}).attr('data-val',(60*60*3)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'1 день'}).attr('data-val',(60*60*24)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'2 дня'}).attr('data-val',(60*60*24*2)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'3 дня'}).attr('data-val',(60*60*24*3)).appendTo(obj_form);
            break;
          case 'go':
            // 1,2,3 дня
            $('<div/>',{class:'leter_div',html:'1 день'}).attr('data-val',(60*60*24)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'2 дня'}).attr('data-val',(60*60*24*2)).appendTo(obj_form);
            $('<div/>',{class:'leter_div',html:'3 дня'}).attr('data-val',(60*60*24*3)).appendTo(obj_form);
            break;
          default:
            break;
        }
        
        // console.log('добавил див для окна #'+div_id);
        $('body').append('<div class="dialog_window" id="' + div_id + '"></div>');
        $('#'+div_id).html(obj_form);
        //var div_content = '<form id="leter_id"></form>';

        // кнопки для окна отложить
        var buttons = new Array();

        buttons.push({
          text: 'Отмена',
          click: function() {
            // закрыть окно выбора отсрочки
            var window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
            $('#'+window_id_close).remove();
            $('#' + div_id).parent().remove();
            $('#leter_id').remove();
          }
        });

        buttons.push({
          text: 'ОК',
          css:{'float':'right'},
          click: function() {    
            // проверяем введена ли дата отсрочки
            var time_reminder = $('#'+div_id+' form input[name="time"]').val();
            if(time_reminder!="" && time_reminder!=0){
              $.post('', $('#'+div_id+' form').serialize(), function(data, textStatus, xhr) {
                if(data['response']=='1'){
                  
                  $('#' + div_id).dialog('destroy');
                  $('#' + div_id).remove();
                  $('#' + div_id+'_minimized').remove();

                  // if($('#' + div_id+'_minimized').length==0){
                  //   console.log('окно уничтожено #' + div_id+'_minimized');
                  // }
                  
                  $('#'+parent_window_id_close).dialog('destroy');
                  $('#'+parent_window_id_close).remove();
                  $('#'+parent_window_id_close+'_minimized').remove();

                  // if($('#'+parent_window_id_close+'_minimized').length==0){
                  //   console.log('окно уничтожено #'+parent_window_id_close+'_minimized');
                  // }
                  
                  get_ui_one_window();
                }
              },'json');
              
            }else{
              alert("Введите время отсрочки напоминания пожалуйста");
            }
          }
        });                    
        
        // console.log('создан диалог для окна- '+'#' + div_id);
        var dialog = $('#' + div_id).dialog({
          width: 'auto',
          height: 'auto',
          modal: true,
          title : div_title,
          autoOpen : true,
          css:{'top':'10px'},
          buttons: buttons,
          open:function() {
            // скрываем крестик
            // $(this).parents(".ui-dialog:first").find("a").remove();
            $(this).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove();
            $('.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset').css({'width':'94%'});
          }
        });
        dialog.parent().css({'top':open_win_top,'left':open_win_left});
        $('#' + div_id).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove()
        // $('#' + div_id).dialog('close');
      }
      });
    }
    buttons.push({
    text: '   ОК   ',
    // css:{'width':'100px','float':'right'},
    css:{'float':'right'},
    dialogClass: "alert",
    "class": 'okButtonClass',
    click: function() {
      // $.post('', {
      //   ajax_reminder:'OK',
      //   client_id:client_id,
      //   event_type:event_type,
      //   manager_id:manager_id,
      //   window_type:type_w,
      //   button:"OK"
      // }, function(data, textStatus, xhr) {
      //   // ответ с сервера должен быть 1
      //   if(data['response']=='1'){
      //     // скрываем ОКНО
      //     $('#' + div_id).dialog('close');
      //     $('#' + div_id).parent().remove();
      //     $('#' + div_id).remove();
      //     $('#' + div_id+'_minimized').remove();
      //     // добавляем в видемую часть ещё одно окно          
      //     // открываем новую вкладку с историей планера по данному клиенту
      //     //window.open('http://www.apelburg.ru/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=planner&client_id='+client_id+'&subsub_razdel=history','_blank')
      //   }
      // },'json');
      //alert('Предупреждение из диалогового окна: ' + div_title);
    }
    });
    }else{   
    // START --// ОБРАБОТКА ОКОН need_approval   
      window_width = 600;
      window_height = 250;
        buttons.push({
        text: 'Отложить',
        css:{'float':'left'},
        click: function() {
          var open_win_top = $(this).offset().top+33;
          var open_win_left = $(this).offset().left+20;
          ///-- START --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ ВЫБОРА ОТСРОЧКИ ОПОВЕЩЕНИЯ
          var parent_window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
          var div_id = get_new_id_window();
          var div_title = 'Отложить на...';

          // собираем форму LETER 
          // ОТПРАВКА ДАННЫХ ОТЛОЖИТЬ
          var obj_form = $('<form/>',{id:'leter_id','style':'min-width:200px'});      
          $('<input/>',{type:'hidden',name:'ajax_reminder',val:'approval_remaind_after'}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'client_id',val:client_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'manager_id',val:manager_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'window_type',val:type_w}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'event_type',val:event_type}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'button',val:'remaind_after'}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'time',val:'0'}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'plan_id',val:plan_id}).appendTo(obj_form);
          
          // 1,2,3 часа и 1,2,3 дня
          $('<div/>',{class:'leter_div',html:'1 час'}).attr('data-val',(60*60)).appendTo(obj_form);
          $('<div/>',{class:'leter_div',html:'2 часа'}).attr('data-val',(60*60*2)).appendTo(obj_form);
          $('<div/>',{class:'leter_div',html:'3 часа'}).attr('data-val',(60*60*3)).appendTo(obj_form);
          $('<div/>',{class:'leter_div',html:'1 день'}).attr('data-val',(60*60*24)).appendTo(obj_form);
          $('<div/>',{class:'leter_div',html:'2 дня'}).attr('data-val',(60*60*24*2)).appendTo(obj_form);
          $('<div/>',{class:'leter_div',html:'3 дня'}).attr('data-val',(60*60*24*3)).appendTo(obj_form);
          
          // console.log('добавил див для окна #'+div_id);
          $('body').append('<div class="dialog_window" id="' + div_id + '"></div>');
          $('#'+div_id).html(obj_form);
          //var div_content = '<form id="leter_id"></form>';

          // кнопки для окна отложить
          var buttons = new Array();

          buttons.push({
            text: 'Отмена',
            click: function() {
              // закрыть окно выбора отсрочки
              var window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
              $('#'+window_id_close).remove();
              $('#' + div_id).parent().remove();
              $('#leter_id').remove();
            }
          });

          buttons.push({
            text: 'ОК',
            // css:{'width':'100px','float':'right'},
            css:{'float':'right'},
            click: function() {    
              // проверяем введена ли дата отсрочки
              var time_reminder = $('#'+div_id+' form input[name="time"]').val();
              if(time_reminder!="" && time_reminder!=0){
                $.post('', $('#'+div_id+' form').serialize(), function(data, textStatus, xhr) {
                  if(data['response']=='1'){
                    
                    $('#' + div_id).dialog('destroy');
                    $('#' + div_id).remove();
                    $('#' + div_id+'_minimized').remove();

                    // if($('#' + div_id+'_minimized').length==0){
                    //   console.log('окно уничтожено #' + div_id+'_minimized');
                    // }
                    
                    $('#'+parent_window_id_close).dialog('destroy');
                    $('#'+parent_window_id_close).remove();
                    $('#'+parent_window_id_close+'_minimized').remove();

                    // if($('#'+parent_window_id_close+'_minimized').length==0){
                    //   console.log('окно уничтожено #'+parent_window_id_close+'_minimized');
                    // }
                    
                    get_ui_one_window();
                  }
                },'json');
                
              }else{
                alert("Введите время отсрочки напоминания пожалуйста");
              }
            }
          });                    
          
          // console.log('создан диалог для окна- '+'#' + div_id);
          var dialog = $('#' + div_id).dialog({
            width: 'auto',
            height: 'auto',
            modal: true,
            title : div_title,
            autoOpen : true,
            css:{'top':'10px'},
            buttons: buttons,
            open:function() {
              // скрываем крестик
              // $(this).parents(".ui-dialog:first").find("a").remove();
              $(this).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove();
              $('.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset').css({'width':'94%'});
            }
          });
          dialog.parent().css({'top':open_win_top,'left':open_win_left});
          $('#' + div_id).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove()
          ///-- END --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ ВЫБОРА ОТСРОЧКИ ОПОВЕЩЕНИЯ
        }
        });
        buttons.push({
        text: 'Подтвердить',
        css:{'float':'right'},
        click: function() {
          var open_win_top = $(this).offset().top+33;
          var open_win_left = $(this).offset().left+20;
          ///-- START --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ КОМЕНТИРОВАНИЯ И ПОДТВЕРЖДЕНИЯ ДЕЙСТВИЯ
          var parent_window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
          var div_id = get_new_id_window();
          var div_title = 'Подтвердить';

          // собираем форму ПОДТВЕРЖДЕНИЯ
          // ОТПРАВКА ДАННЫХ ПОДТВЕРДИТЬ
          var obj_form = $('<form/>',{id:'leter_id','style':'min-width:200px'});      
          $('<input/>',{type:'hidden',name:'ajax_reminder',val:'approval_result'}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'client_id',val:client_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'manager_id',val:manager_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'window_type',val:type_w}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'event_type',val:event_type}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'button',val:'remaind_after'}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'plan_id',val:plan_id}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'status',val:'approved'}).appendTo(obj_form);

          $('<textarea/>',{name:'comment',placeholder:'комментарий...'}).css({'width':'100%','height':'100px'}).appendTo(obj_form);
          
          
          // console.log('добавил див для окна #'+div_id);
          $('body').append('<div class="dialog_window" id="' + div_id + '"></div>');
          $('#'+div_id).html(obj_form);
          //var div_content = '<form id="leter_id"></form>';

          // кнопки для окна отложить
          var buttons = new Array();

          buttons.push({
            text: 'Отмена',
            click: function() {
              // закрыть окно выбора отсрочки
              var window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
              $('#'+window_id_close).remove();
              $('#' + div_id).parent().remove();
              $('#leter_id').remove();
            }
          });

          buttons.push({
            text: 'ОК',
            // css:{'width':'100px','float':'right'},
            css:{'float':'right'},
            click: function() {    
              // проверяем введена ли дата отсрочки
                $.post('', $('#'+div_id+' form').serialize(), function(data, textStatus, xhr) {
                  if(data['response']=='1'){
                    
                    $('#' + div_id).dialog('destroy');
                    $('#' + div_id).remove();
                    $('#' + div_id+'_minimized').remove();

                    // if($('#' + div_id+'_minimized').length==0){
                    //   console.log('окно уничтожено #' + div_id+'_minimized');
                    // }
                    
                    $('#'+parent_window_id_close).dialog('destroy');
                    $('#'+parent_window_id_close).remove();
                    $('#'+parent_window_id_close+'_minimized').remove();

                    // if($('#'+parent_window_id_close+'_minimized').length==0){
                    //   console.log('окно уничтожено #'+parent_window_id_close+'_minimized');
                    // }
                    
                    get_ui_one_window();
                  }
                },'json');
            }
          });                    
          
          // console.log('создан диалог для окна- '+'#' + div_id);
          var dialog = $('#' + div_id).dialog({
            width: 400,
            height: 'auto',
            modal: true,
            title : div_title,
            autoOpen : true,
            css:{'top':'10px'},
            buttons: buttons,
            open:function() {
              // скрываем крестик
              // $(this).parents(".ui-dialog:first").find("a").remove();
              $(this).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove();
              $('.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset').css({'width':'94%'});
            }
          });
          dialog.parent().css({'top':open_win_top,'left':open_win_left});
          $('#' + div_id).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove()
          ///-- END --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ КОМЕНТИРОВАНИЯ И ПОДТВЕРЖДЕНИЯ ДЕЙСТВИЯ
        }
        });

        buttons.push({
        text: 'Отклонить',
        css:{'float':'right'},        
        click: function() {
          var open_win_top = $(this).offset().top+33;
          var open_win_left = $(this).offset().left+20;
          ///-- START --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ КОМЕНТИРОВАНИЯ И ОТКЛОНЕНИЯ ЗАЯВКИ НА ПОДТВЕРЖДЕНИЕ
          var parent_window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
          var div_id = get_new_id_window();
          var div_title = 'Отклонить';

          // собираем форму ОТКЛОНИТЬ
          // ОТПРАВКА ДАННЫХ ОТКЛОНИТЬ
          var obj_form = $('<form/>',{id:'leter_id','style':'min-width:200px'});      
          $('<input/>',{type:'hidden',name:'ajax_reminder',val:'approval_result'}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'client_id',val:client_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'manager_id',val:manager_id}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'window_type',val:type_w}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'event_type',val:event_type}).appendTo(obj_form);
          // $('<input/>',{type:'hidden',name:'button',val:'remaind_after'}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'plan_id',val:plan_id}).appendTo(obj_form);
          $('<input/>',{type:'hidden',name:'status',val:'no_approved'}).appendTo(obj_form);

          $('<textarea/>',{name:'comment',placeholder:'комментарий...'}).css({'width':'100%','height':'100px'}).appendTo(obj_form);
          
          
          // console.log('добавил див для окна #'+div_id);
          $('body').append('<div class="dialog_window" id="' + div_id + '"></div>');
          $('#'+div_id).html(obj_form);
          //var div_content = '<form id="leter_id"></form>';

          // кнопки для окна отложить
          var buttons = new Array();

          buttons.push({
            text: 'Отмена',
            click: function() {
              // закрыть окно выбора отсрочки
              var window_id_close = $(this).parent().find('.ui-dialog-content').attr('id');
              $('#'+window_id_close).remove();
              $('#' + div_id).parent().remove();
              $('#leter_id').remove();
            }
          });

          buttons.push({
            text: 'ОК',
            // css:{'width':'100px','float':'right'},
            css:{'float':'right'},
            click: function() {    
              // проверяем введена ли дата отсрочки
                $.post('', $('#'+div_id+' form').serialize(), function(data, textStatus, xhr) {
                  if(data['response']=='1'){
                    
                    $('#' + div_id).dialog('destroy');
                    $('#' + div_id).remove();
                    $('#' + div_id+'_minimized').remove();

                    // if($('#' + div_id+'_minimized').length==0){
                    //   console.log('окно уничтожено #' + div_id+'_minimized');
                    // }
                    
                    $('#'+parent_window_id_close).dialog('destroy');
                    $('#'+parent_window_id_close).remove();
                    $('#'+parent_window_id_close+'_minimized').remove();

                    // if($('#'+parent_window_id_close+'_minimized').length==0){
                    //   console.log('окно уничтожено #'+parent_window_id_close+'_minimized');
                    // }
                    
                    get_ui_one_window();
                  }
                },'json');
            }
          });                    
          
          // console.log('создан диалог для окна- '+'#' + div_id);
          var dialog = $('#' + div_id).dialog({
            width: 400,
            height: 'auto',
            modal: true,
            title : div_title,
            autoOpen : true,
            css:{'top':'10px'},
            buttons: buttons,
            open:function() {
              // скрываем крестик
              // $(this).parents(".ui-dialog:first").find("a").remove();
              $(this).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove();
              $('.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset').css({'width':'94%'});
            }
          });
          dialog.parent().css({'top':open_win_top,'left':open_win_left});
          $('#' + div_id).parent(".ui-dialog:first").find(".ui-dialog-titlebar-close,.ui-dialog-titlebar-minimize").remove()
          ///-- END --/// СОЗДАЁМ ДОПОЛНИТЕЛЬНОЕ ОКНО ДЛЯ КОМЕНТИРОВАНИЯ И ОТКЛОНЕНИЯ ЗАЯВКИ НА ПОДТВЕРЖДЕНИЕ
        }
        });
        function comment_and_submit(){

        }
        
    }
    // добавляем окно в DOM
    $('body').append('<div class="dialog_window"'+
        ' data-client_id="'+client_id+'"'+
        ' data-event_type="'+event_type+'"'+
        ' data-manager_id="'+manager_id+'"'+
        ' data-ajax_reminder="window_set_minimize"'+
        ' data-window_type="'+type_w+'"'+
        ' id="' + div_id + '">' + div_content + '</div>');
    // обявление окна
    var dialog = $('#' + div_id).dialog({
    width: window_width,
    height: window_height,
    title : div_title,
    autoOpen : ((typeof win_minimized !=="undefined")?false:true), // автоматическое открытие
    dialogClass: class_d,
    buttons: buttons,
    open:function() {
      // снимаем фокус с элементов
      $('#' + div_id+' a').blur();

      // скрываем крестик
      $(this).prev().append('<span class="reminder_info"> ? </span>');
      $(this).parents(".ui-dialog:first").find(".ui-dialog-titlebar-close").remove();
      $('.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset').css({'width':'98%'});
          //window.open('http://www.apelburg.ru/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=planner&client_id='+client_id+'&subsub_razdel=history','_blank')
      var href = 'http://www.apelburg.ru/os/?page=client_folder&section=planner&client_id='+client_id+'&subsub_razdel=history';
      $(this).parents(".ui-dialog:first").find('.okButtonClass').replaceWith('<a data-id="" onclick=\'delete_this_win("'+div_id+'","'+client_id+'","'+event_type+'","'+manager_id+'","'+type_w+'")\' type="button" target="_blank" href="'+href+'" dialogclass="alert" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" style=" float: right;margin: 7px 0;"><span class="ui-button-text">   ОК   </span></a>');
    }
  });
    // console.log(win_minimized);
    // скрываем скрытые окна
      if(typeof win_minimized !=="undefined"){
        //$('#'+div_id).parent().hide();
        $('#'+div_id+'_minimized').show();        
      }

}
function delete_this_win(e,client_id,event_type,manager_id,type_w){
  $.post('', {
    ajax_reminder:'OK',
    client_id:client_id,
    event_type:event_type,
    manager_id:manager_id,
    window_type:type_w,
    button:"OK"
  }, function(data, textStatus, xhr) {
    if(data['response']=='1'){
      // скрываем ОКНО
      console.log(e);
      $('#'+e).dialog('destroy');
      $('#'+e+'_minimized').remove();
      $('#'+e).remove();
      get_ui_one_window();
    }
  },'json');
  
}

$(document).on('click','.reminder_info',function(){
  $.post('',{ajax_reminder:'show_help'},function(data){
    $('#info_window_for_managers2').html(data);
    $('#info_window_for_managers2').dialog('open');
    $('#info_window_for_managers').show();
  });  
});

// создание окна правил по модулю напоминалок
function add_info_window(){
  if($('#info_window_for_managers2').length == 0){
    $('body').append('<div id="info_window_for_managers2" style="display:none;"></div>');//.css({'display':'none'})

    var div_title = 'Уведомления менеджера от ОС (сроки звонков/встреч)';
    // создание кнопок
    var buttons = new Array();
    buttons.push({
      text: '   ОК   ',
      // css:{'width':'100px','float':'right'},
      css:{'float':'right'},
      dialogClass: "alert",
      click: function() {
        $(this).dialog('close');
        //alert('Предупреждение из диалогового окна: ' + div_title);
      }
    });  
    
    // обявление окна
    $('#info_window_for_managers2').dialog({
      width: 600,
      height: 600,
      modal: true,
      title : div_title,
      autoOpen : false, // автоматическое открытие запретить
      //dialogClass: class_d,
      buttons: buttons,
      open:function() {
        // вырезаем свёртывание
        $(this).parents(".ui-dialog:first").find(".ui-dialog-titlebar-minimize").remove();
      }
    });
  }
  
}
