###
# update invoise date
#
# @param     type
# @return    json 2
# @see       add json data in div#invoceData 
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:25:40
###
getInvoiceData = (type="new") ->
  $.ajax
    url: ""
    type: "POST"
    data:{AJAX:'get_data'}
    dataType: "json"
    error: (jqXHR, textStatus, errorThrown) ->
      echo_message_js "AJAX Error: #{textStatus}"
      return
    success: (data, textStatus, jqXHR) ->
      # console.log jqXHR
      standard_response_handler(jqXHR.responseJSON)
      # записываем json
      $('#invoceData').html jqXHR.responseText;
      # запускаем плагин
      $('#js-main-invoice-table').invoice() if type=='new'
  return true;    
      
###
# window onload function
###
$(document).ready(()->
  getInvoiceData()
)

###
# get date
###
getDateNow = () ->
  d = new Date();            
  dd = d.getDate()
  dd = '0' + dd if dd < 10  
  mm = d.getMonth()+1
  mm = '0' + mm if mm < 10 
  yy = d.getFullYear()
  # yy = d.getFullYear() % 100
  # yy = '0' + yy if yy < 10 
  return dd+'.'+mm+'.'+yy

###
# round and return money format any input string or number
###
round_money = (num) ->
  num = Number(num);
  new_num = Math.ceil((num)*100)/100;
  return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
###
# calculate price with discount
###
calc_price_with_discount = (price_out, discount) ->
  return Number(price_out/100) * (100 + Number(discount));

###
# send AJAX
###
class sendAjax
  defaults:
    AJAX:'test',
    options:{},
    func:()->

  response:{}

  constructor:(AJAX,options={},func=()->
    true)->
    data={
      AJAX:AJAX,
      options:options,
      func:func
    }

    # console.log 
    @options = $.extend({}, @defaults, data)  
    @sendAjax()

  # отправка запроса
  sendAjax:()->
    _this = @
    data = {
        AJAX:@options.AJAX
    }
    for k,v of @options.options
      # console.log k + " is " + v
      data[k] = v

    $.ajax
      url: ""
      type: "POST"
      data:data
      dataType: "json"
      error: (jqXHR, textStatus, errorThrown) ->
        echo_message_js "AJAX Error: #{textStatus}"
        return
      success: (data, textStatus, jqXHR) ->
        # console.log jqXHR.responseJSON
        # data1 = JSON.parse jqXHR.responseText
        # echo_message_js "Successful AJAX call: #{jqXHR.responseJSON}"
        _this.response = $.extend({}, _this.response, jqXHR.responseJSON)
        standard_response_handler(_this.response)
        # выполняемая функия ы случае успеха
        _this.options.func()

###
# model from window
#
# @param     data = {html='текст не был передан', title='имя окна не было передано', buttons={}}
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   18.04.2016 12:53:01
###
class modalWindow
  # window dop sittings - sitting from jQuery dialog plugin
  sittings:
    modal: true,
    autoOpen : true,
    closeOnEscape: false

  # main default options - default content
  defaults:
    title:'*** Название окна ***',
    width:'auto',
    height:'auto',
    html:'Текст в окне',
    buttons:[{
          text:   'OK',
          class:  'button_yes_or_no no',
          style:  'float:right;'
          click:  ()->
            $('#js-alert_union').dialog('destroy').remove();         
        }]

  constructor:(data = {},sittings={}) ->
    # get options
    @options = $.extend({}, @defaults, data) 
    # get sittings
    @sittings = $.extend({}, @sittings,sittings)
    # init
    @init()
  init:()->
    # html='текст не был передан', title='имя окна не было передано', buttons={}
    # убиваем такое окно, если оно есть
    if($('#js-alert_union').length > 0) 
      $('#js-alert_union').remove();
    
    # создаем новое
    $('body').append($('<div/>',{
      "id":'js-alert_union',
      "style":"height:45px;",
      'html':@options.html
    }));
      
    $('#js-alert_union').dialog({
        width: 'auto',
        height: 'auto',
        modal: @sittings.modal,
        title : @options.title,
        autoOpen : @sittings.autoOpen,
        closeOnEscape: @sittings.closeOnEscape
        # // buttons: buttons          
    }).parent();

    # console.info @options.buttons
    if(@options.buttons.length  > 0)
      buttons_html = $('<table></table>');
      for button_n,i in @options.buttons 
        button = $('<button/>',{
          text: button_n['text'],
          click: button_n['click']
          });
        if button_n['class']
          button.attr('class',button_n['class'])
        if button_n['style']
          button.attr('style',button_n['style'])
        if button_n['id']
          button.attr('id',button_n['id'])

        buttons_html.append(
          $('<td/>')
            .append(button)
            );  
    
    $('#js-alert_union').after($('<div/>',{
      'id':'js-alert_union_buttons',
      'class':'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'
        })
        .append(buttons_html));


  # show:()->
  #   console.log "show"
  # destroy:()->
  #   console.log "destroy"
      
###
# model from ttn 
###
class invoiceTtn
  defaults:
    id:0,
    number:'0000',

    type:"new"

  constructor: (obj,data_row, data, accces, ttn ) ->
    if ttn != null
      @defaults = $.extend({}, @defaults, ttn )
    # запоминаем уровень допуска
    @access = accces
    # сохраняем объект
    @$obj_row = obj
    # сохраняем информацию по строке
    @options = data_row  
    # собираем окно ттн
    @init(data)  
    console.log accces
  # собираем контент
  init:(responseData)->
    _this = @      
    # запрос данных      
    if(responseData!= undefined)
      main_div = $('<div/>')
      
      main_div.append(@createHead())
      # добавляем таблицу
      main_div.append(@createTable(responseData))
      # выбор способа доставки
      main_div.append(@createDeliveryChoose())
      # ранее созданные ттн
      main_div.append(@alreadyWasСreated())

      # сборка сообщения
      message = main_div      
      # создание окна
      @myObj = new modalWindow({
          html:message,
          title:'Запрос ТТН',
          buttons: @getButtons()
        },{
          closeOnEscape:true
        })

      @$el = @myObj.options.html[0]

  # ранее созданные ттн
  alreadyWasСreated:()->
    # console.log 'alreadyWasСreated',@options
    content = $('<div/>',{
      'class':"ttn--already-was-created"
      });
    console.log @options.ttn.length
    
    if(@options.ttn && @options.ttn.length > 0)
      content.append($('<div/>',{
        'class':'ttn--already-was-created--head',
        'html':'Ранее оформленные ТТН:'
      }));

      # console.log @options
      for oldTtn in @options.ttn
        # console.warn oldTtn
        if oldTtn.positions_num != null
          # console.warn oldTtn.positions_num.split(',').length > 1
          if oldTtn.positions_num.split(',').length>1 
            end = 'и ' 
          else 
            end = 'я '
          positions = ' позици'+end
          positions = positions+oldTtn.positions_num
        else
          positions = ''
        number = oldTtn.number
        if oldTtn.number == null || oldTtn.number == undefined
          number = '<b>не выставлен</b>'
        content.append($('<div/>',{
          'html': '№'+number+' от '+oldTtn.date+positions
          }))
      # console.log(654)
  # выбор способа доставки
  createDeliveryChoose:()->
    car_div = $('<div/>',{id:'ttn_car_div'})
    car_div.append($('<div/>',{'html':'Доставка выбранных позиций','class':'ttn_car_div-head'}))
    
    li_clic = (event)->
      $(this).parent().find('li').removeClass('checked')
      $(this).addClass('checked')
    ul = $('<ul/>')
      .append($('<li/>',{click:li_clic})
        .append($('<div/>',{'class':'ttn-our_delivery'}))
        .append($('<div/>',{'class':'ttn-delivery-text','html':'Доставка'})))
      .append($('<li/>',{click:li_clic})
        .append($('<div/>',{'class':'ttn-no_delivery'}))
        .append($('<div/>',{'class':'ttn-delivery-text','html':'Самовывоз'})))
    div_car_body = $('<div/>',{'class':'ttn_car_div-body'}).append(ul)
    car_div.append(div_car_body)

  # сборка шапки АДМИН - БУХ
  createHeadAdmin:()->
    _this = @
    # добавляем имя клиента
    head_info = $('<div>',{id:'ttn_head_info'});
    head_info.append(
      $('<table>',{id:'ttn_head_info-table'})
        .append($('<tr/>')
          .append($('<td/>',{'html':@options.client_name,'class':'ttn_client_name'}))
          .append($('<td/>',{
            'html':@options.client_requisit_name,
            'class':'ttn_requisits',
            'click':()->
              echo_message_js('Вызов окна просмотра реквизитов')
              })))
        .append($('<tr/>')
          .append($('<td/>',{'html':"ТТН "}).append($('<input/>',{
            'val':_this.defaults.number,
            'class':'ttn_number_input'
            })))
          .append($('<td/>'))
          ))
    # console.log @options
    head_info
  spanDate : (val = getDateNow())->
    _this = @
    $('<span/>',{
      'class':'dateInput',
      'html':val,
      click:()->
        val = $(this).html()
        input = _this.inputDate(val)
        $(this).replaceWith(input)
        setTimeout(input.focus(),500)
      })
  spanInput : (val="&nbsp;")->
    _this = @
    $('<span/>',{
      'class':'spanInput',
      'html':val,
      click:()->
        val = $(this).html()
        input = _this.inputSpan(val)
        $(this).replaceWith(input)
        setTimeout(input.focus(),500)
      })
  inputSpan :(val)->
    _this = @
    $('<input/>',{
      val:val,
      blur:()->
        val = $(this).val()
        $(this).replaceWith(_this.spanInput(val))            
      })
  inputDate :(val)->
    _this = @
    $('<input/>',{
      val:val,
      blur:()->
        val = $(this).val()
        # console.log _this.spanDate(val)
        $(this).replaceWith(_this.spanDate(val))            
      }).datetimepicker({
          minDate:new Date(),
          timepicker:false,
          dayOfWeekStart: 1,
          onSelectDate:(ct,$i)->
            $i.blur();

          onGenerate:( ct )->
            $(this).find('.xdsoft_date.xdsoft_weekend')
              .addClass('xdsoft_disabled');
            $(this).find('.xdsoft_date');
          
          closeOnDateSelect:true,
          format:'d.m.Y'
        });
  # сборка шапки МЕНЕДЖЕР и остальные
  createHeadManager:()->
    # добавляем имя клиента
    # getDateNow()
    
    _this = @

    span_ttn = $('<span/>',{'html':"№ ТТН 0000 от "}).append(_this.spanDate())

    span_invoice = $('<span/>',{'html':"№ Счёта "+@options.invoice_num+" от " + @options.invoice_create_date;})

    head_info = $('<div>',{id:'ttn_head_info'});
    head_info.append(
      $('<table>',{id:'ttn_head_info-table'})
        .append($('<tr/>')
          .append($('<td/>',{'html':@options.client_name,'class':'ttn_client_name'}))
          .append($('<td/>',{
            'html':@options.client_requisit_name,
            'class':'ttn_requisits',
            'click':()->
              echo_message_js('Вызов окна просмотра реквизитов')
              })))
        .append($('<tr/>')
          .append($('<td/>').append(span_ttn).append(span_invoice))
          .append($('<td/>'))
          ))
    # console.log @options
    head_info

  # проверка прав и сборка шапки окна
  createHead:()->
    # console.warn @access
    # проверка уровня доступа и вызов соответствующей шапки
    switch @access
      when 1 then head_info = @createHeadAdmin()
      when 2 then head_info = @createHeadAdmin()
      when 5 then head_info = @createHeadManager()
      else
        head_info = @createHeadManager()

  # сборка таблицы менеджер и осталные
  createTableManager:(responseData)->
    _this = @      
    table = $('<table/>',{'id':'js-invoice--window--ttn-table'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))
    # чекбоксы
    main_checkbox = $('<input/>',{
      'type':'checkbox',
      change:(event)->
        input = $(this)
        td = $(this).parent()
        # клик по главному чекбоксу
        _this.clickMainCheckbox(table,td,input)
      })

    td = $('<th/>',{
        click:()->
          input  = $(this).find('input')
          td = $(this)
          # клик по главному чекбоксу
          _this.clickMainCheckbox(table,td,input)
        }).append(main_checkbox)
    tr.append(td)

    # Number
    td  = $('<th/>',{'text':'№'})
    tr.append(td)
    # Name
    td  = $('<th/>',{'html':'Наименование и <br>описание продукции'})
    tr.append(td)
    # Quantity
    td  = $('<th/>',{'html':'Количество<br>продукции'})
    tr.append(td)
    # Price for one
    td  = $('<th/>',{'html':'стоимость<br>за штуку'})
    tr.append(td)
    # Price for all
    td  = $('<th/>',{'html':'Общая<br>стоимость'})
    tr.append(td)

    # тогововая цена
    main_price = 0
    # НДС
    nds = 0 
    # порядковый номер строки товра/услуги
    i = 1 
    # перебираем позиции
    for position in responseData
      tr = $('<tr/>').data(position).attr('data-id',position.id)
      # чекбоксы
      if Number(position.ttn_id) == 0
        check = $('<input/>',{
          'type':'checkbox',
          change:(event)->
            event.preventDefault()
            event.stopPropagation()
            if $(this).prop('checked')
              $(this).prop('checked',false)
              $(this).parent().removeClass('checked')
            else
              $(this).prop('checked',true)
              $(this).parent().addClass('checked')

            _this.checkMainCheckbox(table)

          })        
        td  = $('<td/>',{
          click:()->
            input  = $(this).find('input')
            if input.prop('checked')
              input.prop('checked',false)
              $(this).removeClass('checked')
            else
              input.prop('checked',true)
              $(this).addClass('checked')
            _this.checkMainCheckbox(table)
          })
        td.append(check)
      else
        td  = $('<td/>')
        tr.addClass('ttn_created')
      tr.append(td)

      # Number
      td  = $('<td/>').append(i)
      tr.append(td)
      # Name
      td  = $('<td/>').append(position.name)
      tr.append(td)
      # Quantity
      td  = $('<td/>').append(position.quantity)
      tr.append(td)
      # Price for one
      pr_out = calc_price_with_discount(position.price, position.discount)
      td  = $('<td/>').append(round_money(pr_out)+' р.')
      tr.append(td)
      # Price for all      
      if position.quantity == 0 
        position.quantity = 1 
      main_price += pr_out*position.quantity
      nds += Number(round_money(pr_out*position.quantity/118*18))
      td  = $('<td/>').append(round_money(pr_out*position.quantity)+' р.')
      tr.append(td)
      i++
      table.append(tr)
    # ИТОГО
    table.append(tr = $('<tr/>'))
    td  = $('<th/>')
    tr.append(td)
    # текст
    td  = $('<th/>',{
      'colspan':'4',
      'html':'Итоговая сумма по данной спецификации (договору)'
      })
    tr.append(td)
    # Price for all
    td  = $('<th/>',{'html':round_money(main_price)+' р.'})
    tr.append(td)
    table.append(tr)
    # в том числе НДС
    table.append(tr = $('<tr/>'))
    td  = $('<th/>')
    tr.append(td)
    # Тескт
    td  = $('<th/>',{
      'colspan':'4',
      'html':'В т.ч. НДС 18%'
      })
    tr.append(td)
    # Price НДС
    td  = $('<th/>',{'html':round_money(nds)+' р.'})
    tr.append(td)
    table.append(tr)
    table
  # сборка таблицы Админ + бух
  createTableAdmin:(responseData)->
    _this = @      
    table = $('<table/>',{'id':'js-invoice--window--ttn-table'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))
    # чекбоксы
    main_checkbox = $('<input/>',{
      'type':'checkbox',
      change:(event)->
        input = $(this)
        td = $(this).parent()
        # клик по главному чекбоксу
        _this.clickMainCheckbox(table,td,input)
      })

    td = $('<th/>',{
        click:()->
          input  = $(this).find('input')
          td = $(this)
          # клик по главному чекбоксу
          _this.clickMainCheckbox(table,td,input)
        }).append(main_checkbox)
    tr.append(td)

    # Number
    td  = $('<th/>',{'text':'№'})
    tr.append(td)
    # Name
    td  = $('<th/>',{'html':'Наименование и <br>описание продукции'})
    tr.append(td)
    # Quantity
    td  = $('<th/>',{'html':'Количество<br>продукции'})
    tr.append(td)
    # Price for one
    td  = $('<th/>',{'html':'стоимость<br>за штуку'})
    tr.append(td)
    # Price for all
    td  = $('<th/>',{'html':'Общая<br>стоимость'})
    tr.append(td)

    # тогововая цена
    main_price = 0
    # НДС
    nds = 0 
    # порядковый номер строки товра/услуги
    i = 1 
    # перебираем позиции
    for position in responseData
      tr = $('<tr/>').data(position).attr('data-id',position.id)
      # чекбоксы
      if Number(position.ttn_id) == 0
        check = $('<input/>',{
          'type':'checkbox',
          change:(event)->
            event.preventDefault()
            event.stopPropagation()
            if $(this).prop('checked')
              $(this).prop('checked',false)
              $(this).parent().removeClass('checked')
            else
              $(this).prop('checked',true)
              $(this).parent().addClass('checked')

            _this.checkMainCheckbox(table)

          })        
        td  = $('<td/>',{
          click:()->
            input  = $(this).find('input')
            if input.prop('checked')
              input.prop('checked',false)
              $(this).removeClass('checked')
            else
              input.prop('checked',true)
              $(this).addClass('checked')
            _this.checkMainCheckbox(table)
          })
        td.append(check)
      else
        td  = $('<td/>')
        tr.addClass('ttn_created')
      tr.append(td)

      # Number
      td  = $('<td/>').append(i)
      tr.append(td)
      # Name
      td  = $('<td/>').append(position.name)
      tr.append(td)
      # Quantity
      td  = $('<td/>').append(position.quantity)
      tr.append(td)
      # Price for one
      pr_out = calc_price_with_discount(position.price, position.discount)
      td  = $('<td/>').append(round_money(pr_out)+' р.')
      tr.append(td)
      # Price for all        
      main_price += pr_out*position.quantity
      nds += Number(round_money(pr_out*position.quantity/118*18))
      td  = $('<td/>').append(round_money(pr_out*position.quantity)+' р.')
      tr.append(td)
      i++
      table.append(tr)
    # ИТОГО
    table.append(tr = $('<tr/>'))
    td  = $('<th/>')
    tr.append(td)
    # текст
    td  = $('<th/>',{
      'colspan':'4',
      'html':'Итоговая сумма по данной спецификации (договору)'
      })
    tr.append(td)
    # Price for all
    td  = $('<th/>',{'html':round_money(main_price)+' р.'})
    tr.append(td)
    table.append(tr)
    # в том числе НДС
    table.append(tr = $('<tr/>'))
    td  = $('<th/>')
    tr.append(td)
    # Тескт
    td  = $('<th/>',{
      'colspan':'4',
      'html':'В т.ч. НДС 18%'
      })
    tr.append(td)
    # Price НДС
    td  = $('<th/>',{'html':round_money(nds)+' р.'})
    tr.append(td)
    table.append(tr)
    table
    
  # проверка прав и сборка таблицы
  createTable:(responseData)->
    # проверка уровня доступа и вызов соответствующей шапки
    switch @options.access
      when 1 then tbl = @createTableAdmin(responseData)
      when 2 then tbl = @createTableAdmin(responseData)
      when 5 then tbl = @createTableManager(responseData)
      else
        tbl = @createTableManager(responseData)
    tbl

    
  # клик по главному чекбоксу
  clickMainCheckbox:(table,td,input)->
    if input.prop('checked') || !input.prop('checked') && input.hasClass('checked_no_full')
      input.prop('checked',false).removeClass('checked_no_full')
      td.removeClass('checked')
      table.find('td input').each((index,el)->
        $(this).prop('checked',false).parent().removeClass('checked')
        )
    else
      console.log this
      input.prop('checked',true)
      td.addClass('checked')
      table.find('td input').each((index,el)->
        $(this).prop('checked',true).parent().addClass('checked')
        )
  # проверка и поправка состояния главного чекбокса
  checkMainCheckbox:(table)->
    main_check = table.find('th input');
    # отработка главного checkbox
    if table.find('td input:checked').length == table.find('td input').length
      main_check.prop('checked',true).removeClass('checked_no_full')
      main_check.parent().addClass('checked')
    else if table.find('td input:checked').length > 0
      main_check.prop('checked',false).addClass('checked_no_full')
      main_check.parent().addClass('checked')
    else 
      main_check.prop('checked',false).removeClass('checked_no_full')
      main_check.parent().removeClass('checked')
  # запрос новой ттн (нажатие на кнопку запросить)
  queryNewTtn:(func)->
    _this = @
    console.log @$el
    options = []
    position_numbers = []

    # собираем информацию по выделенным позициям
    $(@$el).find('table td input').each((index,el)->
      if($(this).prop('checked'))
        position_numbers.push($(this).parent().next().html())  
        options.push($(this).parent().parent().data().id)
        return
        
      )    

    # проверка на выбор
    if options.length==0
      echo_message_js('Вы не выбрали ни одной позиции.','error_message')
      return false
    
    # снимаем показания выбора доставки
    delivery = []
    $(@$el).find('#ttn_car_div .ttn_car_div-body li').each((index,el)->
      if $(this).hasClass('checked')
        delivery.push($(this).find('div').eq(0).attr('class').split('-')[1])
      )
    # проверка выбора доставки
    if delivery.length == 0
      echo_message_js('Выберите способ доставки выбранных позиций.','error_message')
      return false  
    



    # отправляем запрос
    new sendAjax 'create_new_ttn',{invoise_id:@options.id,positions:options.join(','),position_numbers:position_numbers.join(','), delivery:delivery.join('')}, ()->
      # при положительном ответе
      
      # @$obj_row.updateTtnRows()
      if(delivery.join('') == 'our_delivery')
        @myObj = new modalWindow({
            html:'Открыть карту курьера в новой вкладке?',
            title:'Переход',
            buttons: [{
            text:   'Да',
            class:  'button_yes_or_no no',
            style:  'float:right;'
            click:  ()->
              window.open(window.location.origin+'/dostavka_new/', '_blank');  
              $('#js-alert_union').dialog('destroy').remove();           
          },{
            text:   'Нет, Спасибо.',
            class:  'button_yes_or_no no',
            style:  'float:right;'
            click:  ()->
              $('#js-alert_union').dialog('destroy').remove();           
          }]
          },{
            closeOnEscape:true
          })
      



      # убиваем окно
      # _this.destroy()

  # убиваем окно
  destroy:()->
    $(@$el).parent().dialog('destroy').remove()  

  getButtons:()->
    _this = @
    buttons = [{
          text: 'Отмена',
          class:  'button_yes_or_no no',
          click: ()->
            _this.destroy()
        },{
          text: 'Запросить',
          class:  'button_yes_or_no',
          click: ()->
            _this.queryNewTtn()
            
        }];
      
###
# jQuery plagin Invoice
#
# @see       invoise table
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:26:46
###
(($, window) ->

  ###
  # Define the plugin class Invoice
  ###
  class invoice

    defaults:
      start: false
    
    access_def: 0
    response_def:{}

    constructor: (el, options) ->
      # console.log @options       
      @options = $.extend({}, @defaults, jQuery.parseJSON($('#invoceData').html()))

      @access = $.extend({}, @access_def, @options.access)
      @$el = $(el)

      @init()
      # @init() for i in [0...10]

    # Additional plugin methods go here
    myMethod: (echo) ->
      @$el.html(@options.paramA + ': ' + echo)
    # Additional plugin methods go here
    init: (echo) ->
      # echo_message_js(@options)
      for n in @options.data
        @createRow n

    printOptions:()->
      console.info @options.access
      console.info @options.data

    # обновление сонтента в таблице
    updateRows:()->
      @destroyRows();
      @init();

    # убиваем строки
    destroyRows:()->
      @$el.find('tbody').html('')

    # обновление JSON нужен ли ???
    updateData:()->
      console.log "updateData"
      $('#invoceData').html JSON.stringify(@options)

    ###
    # get data
    ###
    getData:(ajax_name,options={},func = ()->)->
      _this = @
      data = {
          AJAX:ajax_name
      }
      for k,v of options
        # console.log k + " is " + v
        data[k] = v 
      # console.log data
      response = {}
      $.ajax
        url: ""
        type: "POST"
        data:data
        dataType: "json"
        error: (jqXHR, textStatus, errorThrown) ->
          echo_message_js "AJAX Error: #{textStatus}"
          return
        success: (data, textStatus, jqXHR) ->
          # console.log jqXHR.responseJSON
          # data = JSON.parse jqXHR.responseText
          # echo_message_js "Successful AJAX call: #{jqXHR.responseText}"
          response = jqXHR.responseJSON
          _this.response = $.extend({}, _this.response_def, response)
          standard_response_handler(response)
          # update json
          func()
          # _this.updateData()
      return

    ###
    # create ttn
    ###
    # createTTN:(row)->
      
    getRowTtn:(row)->
      if @defttn == undefined 
        @defttn = {
          0:$('#defttn1').width(),
          1:$('#defttn2').width(),
          2:$('#defttn3').width()
        }

      _this = @
      # if(row.ttn.length == 0 && @options.access !=2)
      table1 = $('<div/>',{'class':'table','style':'width:100%'})
      tr = ''
      for ttn in row.ttn
        tr1 = $('<div/>',{
          'id':ttn.id,
          'class':'row'
          }).data(ttn)
        # определяем номер
        if ttn.number <= 0
          number = 'запрос'
        else
          number = ttn.number
        # определяем дату
        tr1.append($('<div/>',{
          'class':'defttn1 cell',
          'html':number,
          click:()->
            # окно Запрос ТТН
            _this.getData('get_ttn',{'id':row.id},()->
              # создаем экземпляр окна ттн
              new invoiceTtn($(this), row, _this.response.data, _this.options.access) if _this.response.data != undefined
              )
          }).width(_this.defttn[0]))

        tr1.append($('<div/>',{
          'class':'defttn2 cell',
          'html':ttn.date,
          click:()->
            # окно Запрос ТТН
            _this.getData('get_ttn',{'id':row.id},()->
              # создаем экземпляр окна ттн
              new invoiceTtn($(this), row, _this.response.data, _this.options.access) if _this.response.data != undefined
              )
          }).width(_this.defttn[1]))

        tr1.append($('<div/>',{
          # 'class':'',
          'class':'defttn3 cell invoice-row--ttn--vt invoice-row--checkboxtd'
          }).width(_this.defttn[2]))

        table1.append(tr1)

      console.warn table1

      if row.ttn.length <= 0
        td = $('<td/>',{
          'colspan':'3',
          'class':'js-query-ttn',
          'html':'Запросить',
          click:()->
            # окно Запрос ТТН
            _this.getData('get_ttn',{'id':row.id},()->
              # создаем экземпляр окна ттн
              new invoiceTtn($(this), row, _this.response.data, _this.options.access) if _this.response.data != undefined
              )
          }).append(table1)
      else
        td = $('<td/>',{
          'colspan':'3',
          'class':'js-query-ttn'
          }).append(table1)

      td
      # else
      #   td = $('<td/>',{
      #     'colspan':'3',
      #     'html':"Запросить",
      #     'class':'js-query-ttn',
      #     click:()->
      #       # окно Запрос ТТН
      #       _this.getData('get_ttn',{'id':row.id},()->
      #         # создаем экземпляр окна ттн
      #         new invoiceTtn($(this), row, _this.response.data,_this.options.access) if _this.response.data != undefined
      #         )
      #     })

    ###
    # create tr 
    ###
    createRow:(row)->
      _this = @
      # console.log 654
      tr = $('<tr/>',{
        id:'tt_'+row.id
        }).data(row);
      # номер, дата
      if row.doc_type=='spec'
        row.spf_num  = row.doc_num
        doc_type = 'счёт'
      else
        row.spf_num  = 'оф'
        doc_type = 'счёт - оферта'

      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--number',
          'html':'<span>'+row.invoice_num+'</span>  '+row.invoice_create_date
            }))
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd checked',
          'html': doc_type
            }))

      tr.append(td)

      # 1с
      td = $('<td/>',{'class':'invoice-row--checkboxtd'})
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd-div'
            }))
      td.click ()->
        if $(this).hasClass('checked')
          row.flag_1c = 0;
          $(this).removeClass('checked')
        else
          row.flag_1c = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        new sendAjax 'edit_flag_1c',{id:row.id,val:row.flag_1c}
        return

      td.addClass('checked') if Number row.flag_1c>0
      tr.append(td)
      # выручка, платежи
      td = $('<td/>') 
        .append($('<div/>',{
          'class':'invoice-row--price-profit',
          'html':round_money row.price_out
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-payment',
          'html': round_money row.price_out_payment
            }))
      tr.append(td)
      # заказ, менеджер
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--order-number'
          'html':row.invoice_num
            }))
        .append($('<div/>',{
          'class':'invoice-row--meneger--full-name',
          'html': row.manager_name
            }))
      tr.append(td)
      # флаг рекламация
      td = $('<td/>',{
        'class':'invoice-row--icons-flag'          
      })
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd-div'
            }))

      td.click ()->
        if $(this).hasClass('checked')
          if(Number(_this.options.access) != 1)
            console.log _this.options.access
            echo_message_js('Снять рекламацию может только администратор','error_message')
            return false;
          
          row.flag_flag = 0;
          $(this).removeClass('checked')
          # сохраняем значение флага
          new sendAjax 'edit_flag_flag',{id:row.id,val:row.flag_flag}
        else
          if(Number(_this.options.access) != 5 && Number(_this.options.access) != 1)
            echo_message_js('Рекламацию устанавливает только менеджер','error_message')
            return false;

          t = $(@)
          buttons = [{
            text: 'Да',
            class:  'button_yes_or_no',
            click: ()->
              row.flag_flag = 1;
              t.addClass('checked')
              # сохраняем значение флага
              new sendAjax 'edit_flag_flag',{id:row.id,val:row.flag_flag}
              $('#js-alert_union').dialog('destroy').remove();  
          },{
            text: 'Нет',
            class:  'button_yes_or_no yes',
            click: ()->
              $('#js-alert_union').dialog('destroy').remove();         
          }];
          message = 'Вы уверены, что хотите установить флаг рекламации?';
          # _this.createSmallDialog(message,'Подтверждение действия',buttons);  
          new modalWindow({
            html:message,
            title:'Подтверждение действия',
            buttons:buttons
            })
        return

      td.addClass('checked') if Number row.flag_flag>0
      tr.append(td)
      # клиент, юрлицо
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--client--name',
          'html':row.client_name
            }))
        .append($('<div/>',{
          'class':'invoice-row--client--requsits',
          'data-id':row.client_requisit_id,
          'html': row.client_requisit_name
            }))
      tr.append(td)
      # себестоимость
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--price-start',
          'html':row.price_in
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-our-pyment',
          'html': row.client_requisit_name
            }))
      tr.append(td)
      # глаз
      td = $('<td/>',{'class':'invoice-row--ice'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_ice = 0;
          $(this).removeClass('checked')
        else
          row.flag_ice = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        new sendAjax 'edit_flag_ice',{id:row.id,val:row.flag_ice}
        return

      td.addClass('checked') if Number row.flag_ice>0
      tr.append(td)
      # прибыль
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--price-our-profit',
          'html':round_money(row.price_out - row.price_in)
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-our-profit-percent',
          'html':round_money(((row.price_out - row.price_in)/row.price_out*100).toString())+'%'
          
          }))
      tr.append(td)
      # калькулятор
      td = $('<td/>',{'class':'invoice-row--icons-calculator'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_calc = 0;
          $(this).removeClass('checked')
        else
          row.flag_calc = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        new sendAjax 'edit_flag_calc',{id:row.id,val:row.flag_calc}
        return

      td.addClass('checked') if Number row.flag_calc>0
      tr.append(td)
      # ттн
      
      td = @getRowTtn(row)
      

      tr.append(td)

      # спф дата
      td = $('<td/>')
        .append($('<div/>').html(row.spf_num))
      tr.append(td)
      # спф checkbox
      td = $('<td/>',{'class':'invoice-row--ttn--vt invoice-row--checkboxtd'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_spf_return = 0;
          $(this).removeClass('checked')
        else
          row.flag_spf_return = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        new sendAjax 'edit_flag_spf_return',{id:row.id,val:row.flag_spf_return}
        return

      td.addClass('checked') if Number row.flag_spf_return>0
      tr.append(td)
      # статус
      td = $('<td/>')
      tr.append(td)
      # диндин
      td = $('<td/>')
      tr.append(td)
      

      @$el.find('tbody').append(tr)



  # Define the plugin
  $.fn.extend invoice: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('invoice')

      if !data
        $this.data 'invoice', (data = new invoice(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)

) window.jQuery, window

###
# cloned the head table and fixed this head on top in user window
#
# @see       top header
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:24:30
###
$(window).scroll ()->
  if $(this).scrollTop()>$('#js-main-invoice-table').offset().top
    # шапка таблицы
    if($('#js-main-invoice-table-clone').length == 0)
      el_cloned = $('#js-main-invoice-table thead');
      thead = el_cloned.clone();
      thead.find('tr').each((index) ->
        # console.log thead.find('tr').eq(index)
        thead.find('tr').eq(index).find('th').each((ind) ->
          # console.log el_cloned.find('tr').eq(index).find('th').eq(ind).width()

          thead.find('tr').eq(index).find('th').eq(ind).width(el_cloned.find('tr').eq(index).find('th').eq(ind).width()+1)
          return
        )
        return
      )
      div = $('<div/>',{'id':'js-main-invoice-table-clone'})
        .append($('<table/>').append(thead))
        .css({
          'position':'fixed',
          'top':'0',
          'left':'0',
          'width':'100%'
          })
      div.appendTo('body')
    else
      $('#js-main-invoice-table-clone')
        # .stop( true, true )
        .css {'display':'block'}
    # показываем кнопку вверх
    $('#invoice-button-top').show()
  else
    if($('#js-main-invoice-table-clone').length > 0)  
      $('#js-main-invoice-table-clone')
        # .stop( true, true )
        .css {'display':'none'}
    # скрываем кнопку вверх
    $('#invoice-button-top').hide()

  

$(document).on 'click','#invoice-button-top', (event) ->
  $("html, body").animate({ scrollTop: 0 }, 600);
  
