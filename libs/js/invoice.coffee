###
# class Invoice
#
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:25:40
###


###
# Запуск модуля счета
###
$(document).ready(()->
  $('#js-main-invoice-table').invoice()
)

###
# возвращяет текущую дату в читабельном формате
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
# округляет и приводит числа к денежному формату
# строку преобразует в число
###
round_money = (num) ->
  num = Number(num);
  new_num = Math.ceil((num)*100)/100;
  return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");

###
# подсчет скидки
# @param      price_out - входящая цена
# @discount   discount - скидка
###
calc_price_with_discount = (price_out, discount) ->
  return Number(price_out/100) * (100 + Number(discount));

###
# транслитерация
###
cyrill_to_latin = (text)->
  arrru = ['Я','я','Ю','ю','Ч','ч','Ш','ш','Щ','щ','Ж','ж','А','а','Б','б','В','в','Г','г','Д','д','Е','е','Ё','ё','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н', 'О','о','П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ы','ы','Ь','ь','Ъ','ъ','Э','э',' ']

  arren = ['Ya','ya','Yu','yu','Ch','ch','Sh','sh','Sh','sh','Zh','zh','A','a','B','b','V','v','G','g','D','d','E','e','E','e','Z','z','I','i','J','j','K','k','L','l','M','m','N','n', 'O','o','P','p','R','r','S','s','T','t','U','u','F','f','H','h','C','c','Y','y','`','`','\'','\'','E', 'e', '_']

  for itm,i in arrru
    reg = new RegExp(arrru[i], "g");
    text = text.replace(reg, arren[i]);

  return text;

###
# прототип объекта ТТН
###
class ttnObj
  defaults:
    buch_id:0
    buch_name:0
    comments:''
    date:"00.00.0000"
    date_return:null
    delivery:""
    id:0
    invoice_id:0
    number:0
    position_id:0
    positions_num:0
    return:0
  options:{}

  constructor:(data = {})->

    for key,el of data
      @options[key] = el
    return $.extend({}, @defaults, @options)

###
# прототип объекта прихода
###
class ppRowObj
  defaults:
    id:0
    invoice_id:0
    invoice_number:0
    number:0
    date: getDateNow()
    price: 0
    percent: 0
    create: getDateNow()
    buch_id:0
    buch_name:'Default Name'
    edit:0
    del:0
  enterObj:{}
  options:{}

  constructor:(data = {})->
    if data.edit == undefined
      data.edit = 1
    for key,el of data
      @options[key] = el

    return $.extend({}, @defaults, @options)



###
# прототип html строки прихода
###
class ppRow
  defaults:
    id:0
    invoice_id:0
    invoice_number:0
    number:0
    date: getDateNow()
    price: 0
    percent: 0
    create: getDateNow()
    buch_id:0
    buch_name:'Default Name'
    edit:0
    del:0
  enterObj:{}
  options:{}
  access:0


  constructor:(rData,i,access=0,paymentWindowObj,data_row)->
    data = rData[i]
    if data.edit == undefined
      data.edit = 1

    @access = access

    for key,el of data
      @options[key] = el

    @options = data

    @calculatePaymentWindowHeader(rData,i,access=0,paymentWindowObj,data_row)



    return @init(data,rData,i,paymentWindowObj,data_row)
    
  init:(data,rData,i,paymentWindowObj,data_row)->
    #    if Number(@options.del) == 0 and Number(@options.edit) > 0 and (Number(@access) == 1 or Number(@access) == 2)
    # (data.number == "" || Number(data.price) == 0) - проверка на заполненность номера платежки и суммы, если заполнено - выводим нередактируемую версию
    if Number(@options.del) == 0 and Number(@options.edit) > 0 and (Number(@access) == 1 or Number(@access) == 2) and (@options.number == "" || Number(@options.number) == 0)
      return @createEditingObj(data,rData,i,paymentWindowObj,data_row)
    else
      return @createSimpleRow(data,rData,i,paymentWindowObj,data_row)


  # подсчёт 1го процента от общей стоимости
  calculatePaymentWindowHeader:(rData,i,access=0,paymentWindowObj,data_row)->
    onePercent = Number(data_row.price_out)/100

  # строка с возможностью редактирования
  createEditingObj:(data,rData,i,paymentWindowObj,data_row)->
    _this = @
    tr = $('<tr/>').data(data)
      # номер платежки
      .append($('<td/>',{
        'html':@options.number,
        click:()->
          if($(this).find('input').length == 0)
            $(this).html(input = $('<input/>',{
              'type':'text',
              'val':$(this).html(),
              change:()->
                _this.options.number = $(this).val()
              }))
            $(this).addClass('tdInputHere')
            input.css('textAlign',$(this).css('textAlign')).focus().blur(()->
                t = $(this)
                _this.options.number = $(this).val()
                new sendAjax 'save_payment_row',{id:_this.options.id,number:_this.options.number}, ()->
                  t.parent().removeClass('tdInputHere')
                  t.replaceWith(_this.options.number)
              )
      }))
      # дата
      .append($('<td/>',{
        'html':@options.date,
        'class':'date',
        click:()->
          if($(this).find('input').length == 0)
            $(this).html(input = $('<input/>',{
              'type':'text',
              'val':$(this).html(),
              change:()->
                _this.options.date = $(this).val()
            }))
            $(this).addClass('tdInputHere')
            input.datetimepicker({
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
            input.focus().blur(()->
              t = $(this)
              _this.options.date = $(this).val()
              new sendAjax 'save_payment_row',{id:_this.options.id,date:_this.options.date}, ()->
                t.parent().removeClass('tdInputHere')
                t.replaceWith(_this.options.date)

            )
      }))
      # денежный формат
      .append($('<td/>',{
        'html':@options.price,
        click:()->
          if($(this).find('input').length == 0)
  #          html =
            $(this).html(input = $('<input/>',{
              'type':'text',
              'val':$(this).html(),
              keyup:()->
                $(this).val($(this).val().replace(/[^-0-9/.]/gim,''))

                per = round_money(Number($(this).val())*100/Number(data_row.price_out))
                _this.percentSpan.html(per)
              focus:()->
                if(Number($(this).val()) == 0)
                  # если 0.00 подменяем на пусто
                  $(this).val('')
                 else
                  # выделение
                  focusedElement = $(this)
                  setTimeout(()->
                    focusedElement.select()
                  , 50)
              change:()->
                _this.options.price = $(this).val()
            }))
            $(this).addClass('tdInputHere')
            input.css('textAlign',$(this).css('textAlign')).focus().blur(()->
              input = $(this)
              if (Number($(this).val()) == 0)
                _this.options.price =  '0.00'
              else
                _this.options.price =  round_money($(this).val())

              per = round_money(Number(_this.options.price)*100/Number(data_row.price_out))
              new sendAjax 'save_payment_row',{id:_this.options.id, price:_this.options.price, percent:per}, ()->
                input.parent().removeClass('tdInputHere')
                input.replaceWith(_this.options.price)

                # обновляем значение % в строке таблицы пп в окне
                _this.percentSpan.html(per)

                _this.options.percent = per
                console.log _this.options.percent
                rData[i].percent = per
                tr.data(data)
                console.log rData[i].percent = per
                console.log data.percent = per
                # обновляем информацию по строке счёта
                paymentWindowObj.updateHeaderPercent(data_row)
            )
        }))
      .append($('<td/>')
        .append(@percentSpan = $('<span/>',{'html':@options.percent}))
        .append($('<span/>',{'html':"%"}))
        )
      .append($('<td/>')
        .append($('<div/>',{'html':@options.buch_name}))
        .append($('<div/>',{'html':@options.create}))
        )
      .append(delTd = $('<td/>'))
      .data(@options)
    @paymentDel(delTd,rData,i,data,paymentWindowObj,data_row)
    tr.addClass('deleted') if Number(@options.del)>0
    return tr
  createSimpleRow:(data,rData,i,paymentWindowObj,data_row)->
    tr = $('<tr/>').data(data)
      .append($('<td/>',{'html':@options.number}))
      .append($('<td/>',{'html':@options.date}))
      .append($('<td/>',{'html':@options.price}))
      .append($('<td/>')
        .append($('<span/>',{'html':@options.percent}))
        .append($('<span/>',{'html':"%"}))
        )
      .append($('<td/>')
        .append($('<div/>',{'html':@options.buch_name}))
        .append($('<div/>',{'html':@options.create}))
        )
      .append(td_del = $('<td/>'))
      .data(@options)

    if Number(@access) == 1 && Number(rData[i].del) > 0
      @realPaymentDel(td_del,rData,i,data)
    else if Number(rData[i].del) == 0
      @paymentDel(td_del,rData,i,data,paymentWindowObj,data_row)
    tr.addClass('deleted') if Number(@options.del)>0
    return tr
  # навешивает onClick событие помечает строку как удалённую
  paymentDel:(tdObj,rData,i,data,paymentWindowObj,data_row)->
    _this = @
    tdObj.addClass('ppDel').click((e)->
      td = $(this)
      row = td.parent()
      new sendAjax 'save_payment_row',{id:_this.options.id,del:1}, ()->
        rData[i].del = 1

        # правим данные в кнопке
        button_changed = $('#js--how_del_payment_button')
        button_changed.data().num = Number(button_changed.data().num)+1
        button_changed.text('Показать удалённые('+button_changed.data().num+')')

        # присваеваем строке класс удаленной строки
        row.addClass('deleted').data(_this.options)
        # поменяем td с евентами на новый чистый td
        td.replaceWith(td = $('<td/>'))
        # если админ вешаем удаление на onClick
        if _this.access == 1
          _this.realPaymentDel(td,rData,i,data)

        # если не зажата кнопка показать удалённые - подчищаем dom и выходим
        if button_changed.hasClass('no')

          pause = 0
          pause = 2000 if @access == 1
          row.delay(pause).fadeOut(1000, ()->
            $(this).remove()
            paymentWindowObj.updateHeaderPercent(data_row)
          )
    )
  # навешивает onClick событие удаляет из базы
  realPaymentDel:(tdObj,rData,i,data)->
    tdObj.addClass('ppDel').click((e)->
      td = $(this)
      row = td.parent()
      confirmObj = new modalConfirm({html:'Данная запись будет удалена безвозвратно.<br>Продолжить?'},()->
        new sendAjax 'delete_payment',{id:data.id}, ()->
          row.delay(200).fadeOut(700, ()->
            $(this).remove()
          )

          rData[i] = undefined
          delete rData[i]
          rData.splice(i,1);
          #              confirmObj.buttonsTab.
          button_changed = $('#js--how_del_payment_button')
          button_changed.data().num = Number(button_changed.data().num)-1
          button_changed.text('Показать удалённые('+button_changed.data().num+')')
          if button_changed.data().num == 0
            button_changed.addClass('no')
      )
    )


###
# метод отправки запроса AJAX
###
class sendAjax
  defaults:
    AJAX:'test',
    options:{},
    func:()->

  response:{}

  constructor:(ajaxName,options={},func=()->true)->
    data={
        AJAX:ajaxName,
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
      url: window.location.href
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
        _this.options.func(_this.response)

###
# прототип окна Confirm
#
# @version   21.04.2016 11:20:30
###
class modalConfirm
  defaults:
    title: 'Подтвердите действие',
    html:'Вы уверены',

  constructor:(data = {},func = ()->)->
    # get options
    _this = @
    @options = $.extend({}, @defaults, data)
    @options.buttons = [{
        text:   'Да',
        class:  'button_yes_or_no no',
        style:  'float:right;'
        click:  ()->
          func()
          $(_this.selfObj.winDiv).dialog('destroy').remove()
      },{
        text:   'Нет, Спасибо.',
        class:  'button_yes_or_no no',
        style:  'float:right;',
        click:  ()->
          $(_this.selfObj.winDiv).dialog('destroy').remove()
#          $(this).parent().parent().parent().parent().parent().prev().dialog('destroy').remove();
      }]

    @selfObj = new modalWindow(@options,{single:false})


###
# прототип окна
#
# @param     data = {html='текст не был передан', title='имя окна не было передано', buttons={}}
# @version   18.04.2016 12:53:01
###
class modalWindow
  # window dop sittings - sitting from jQuery dialog plugin
  sittings:
    modal: true,
    autoOpen : true,
    closeOnEscape: false,
    single:true

  # main default options - default content
  defaults:
    id:'js-alert_union'
    title:'*** Название окна ***',
    width:'auto',
    height:'auto',
    html:'Текст в окне',
    buttons:[]

  constructor:(data = {},sittings={}) ->
    # get options
    @options = $.extend({}, @defaults, data)
    # get sittings
    @sittings = $.extend({}, @sittings,sittings)

    if @options.maxWidth && @options.maxWidth .indexOf('%') + 1
      @options.maxWidth  = $(window).width()/100*Number(@options.maxWidth .substring(@options.maxWidth .length-1,0));
    if @options.maxHeight && @options.maxHeight.indexOf('%') + 1
      @options.maxHeight = $(window).height()/100*Number(@options.maxHeight.substring(@options.maxHeight.length-1,0));

    # init
    @init()

  destroy:()->
    @winDiv.dialog('destroy').remove()
  init:()->
    _this = @
    # html='текст не был передан', title='имя окна не было передано', buttons={}
    # убиваем такое окно, если оно есть
    if(@sittings.single)
      if($('#js-alert_union').length > 0)
        $('#js-alert_union').remove();

      # создаем новое
      $('body').append(@winDiv = $('<div/>',{
        "id":@defaults.id,
        "style":"height:45px;",
        'html':@options.html,
        "class":"js-alert_union",
      }));
    else
      len = $('.js-alert_union').length
      # создаем новое
      @defaults.id = @defaults.id+len
      $('body').append(@winDiv = $('<div/>',{
        "id":@defaults.id,
        "style":"height:45px;",
        'html':@options.html,
        "class":"js-alert_union",
      }));


    self = @winDiv.dialog({
        width: @options.width,
        height: @options.height,
        modal: @sittings.modal,
        title : @options.title,
        autoOpen : @sittings.autoOpen,
        closeOnEscape: @sittings.closeOnEscape,

        # // buttons: buttons
    }).parent();
    if(@options.buttons.length == 0)
      @options.buttons.push({
          text: 'Закрыть',
          class: 'button_yes_or_no no',
          style: 'float:right;',
          click: ()->
            $('#'+_this.defaults.id).dialog('destroy').remove()
      })
    @winDiv.dialog("option", "buttons",
      buttons:
        text: 'Закрыть',
        class: 'button_yes_or_no no',
        style: 'float:right;',
        click: ()->
          $('#'+_this.defaults.id).dialog('destroy').remove()

    )
    @winDiv.dialog("option", "maxHeight", @options.maxHeight) if @options.maxHeight
    @winDiv.dialog("option", "maxWidth", @options.maxWidth) if @options.maxWidth


    # replace standart buttons
    buttons_html = $('<table/>').append(tr = $('<tr/>'));
    for button_n,i in @options.buttons
      button = $('<button/>',{
        html: button_n['text'],
        click:button_n['click'],
      });


      if button_n['class']
        button.attr('class',button_n['class'])
      if button_n['style']
        button.attr('style',button_n['style'])
      if button_n['id']
        button.attr('id',button_n['id'])

      tr.append(
        td = $('<td/>')
          .append(button)
          );
#      console.log button_n.data
      if button_n.data != undefined
#        console.log button_n.data
        button.data(button_n.data)
      if(i>0)
        td.css('textAlign','right');


    self.find('.ui-dialog-buttonpane').html(@buttonDiv = $('<div/>',{
#      'id':'js-alert_union_buttons',
      'class':'js-alert_union_buttons ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'
        }).append(buttons_html))

    # $('#js-alert_union').after();



###
# прототип окна приходов
###
class paymentWindow
  saveObj:{}
  defaults:
    id:0,
    number:'0000',
    type:"new"
  countDelRow:0
  accces:0
  head:
    price:{}
    r_percent:{}
    conditions:{}

  #  price:0      # price from invoice
  #  conditions:0 # percent payment for start the work
  #  r_percent:0  # real

  constructor: (obj, data_row, responseData, access) ->
    # запоминаем уровень допуска
    @access = access
    # сохраняем информацию по строке
    @options = data_row
    # сборка окна счёта
    @init(obj, data_row, responseData)
  
  # собираем окно счёт
  init:(obj, data_row, responseData)->
    _this = @
    # запрос данных
    if(responseData!= undefined)
      ###
      # создание контейнера
      ###
      main_div = $('<div/>')
      ###
      # добавление шапки окна
      ###
      main_div.prepend(@createHead(data_row))
      ###
      # добавляем таблицу
      ###
      main_div.append(@bodyRows = @createTable(responseData,0,data_row))
      ###
      # создание окна
      ###
      @myObj = new modalWindow({
        html:main_div,
        width:'1000px',
        maxHeight:'100%',
        title:'Приходы по счёту',
        buttons: @getButtons(obj,data_row,responseData)
      },{
        closeOnEscape:true
      })
      @$el = @myObj.options.html[0]
      $(@$el).parent().css('padding','0')

  updatePaymenContent:(button, responseData,data_row)->
    if button.hasClass('showed')
      button.removeClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 0, data_row))
    else
      button.addClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 1, data_row))
  # сборка таблицы
  createTable:(responseData, showDell = 0, data_row)->
    tbl = $('<table>',{'id':'js--payment-window--body_info-table'})
        .append(tr = $('<tr/>'))

    tr.append($('<th/>',{'html':'№ платёжки'}))
      .append($('<th/>',{'html':'дата ПП'}))
      .append($('<th/>',{'html':'платёж на сумму'}))
      .append($('<th/>',{'html':'% оплаты'}))
      .append($('<th/>',{'html':'платёж внесён','colspan':'2'}))
    # перебор строк ПП
    for payment,i in responseData
      if Number(payment.del) > 0 && showDell == 0
        @countDelRow = @countDelRow+1
      else
        responseData[i] = new ppRowObj(responseData[i])
        tbl.append(new ppRow(responseData,i,@access,@,data_row))

    return tbl

  # добавляем обработчик для на элементы поиска по счетам
  addHandlerForInputSearch:(inputSearch,buttonSearch)->
    ###
    # inputSearch
    ###
    _this = @
    inputSearch.autocomplete({
      minLength: 2,
      source: (request, response)->
#        console.log(request)
        $.ajax({
          type: "POST",
          dataType: "json",
          data:
            AJAX: 'shearch_invoice_autocomlete', # показать
            search: request.term # поисковая фраза
          success: (data) ->
            response(data);
        })
      select: ( event, ui ) ->
        inputSearch.attr('data-id',ui.item.desc)
        if(event.keyCode != 13)
          ####
          # исключение на кнопку enter
          #
          # т.к. при клике enter при выборе из выпадающего списка
          # keyCode тоже равен 13
          # чтобы не отрабатывало дважды при клике на enter на фокусе
          ####
          if(inputSearch.is(':focus'))
            buttonSearch.click();
        return false;

    })
    inputSearch.data( "ui-autocomplete" )._renderItem = ( ul, item )->
      ul.css('z-index',Number($(_this.$el).parent().parent().css( "z-index" ))+1)
      return $("<li></li>",{
        click:(e)->
          inputSearch.attr('data-id',0)
      })
        .data("ui-autocomplete-item", item) # для jquery-ui 1.10+
        #.append( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
        .append( item.label )
        .appendTo(ul);
        # console.log ul

    inputSearch.keydown((e)->
      if(e.keyCode == 13)#enter
        if(inputSearch.is(':focus'))#
          buttonSearch.click()
          inputSearch.attr('data-id',0)
          return false;
        #отправка поиска на enter
    )


    ###
    # buttonSearch
    ###
    buttonSearch.click((e)->
      send = {
        invoice_num:inputSearch.val()
        id:0
      }
      if inputSearch.attr('data-id') && Number(inputSearch.attr('data-id')) > 0 && inputSearch.attr('data-id')!= undefined
        send.id = inputSearch.attr('data-id')


      new sendAjax 'getInvoceRow',send, (responseRow)->
        console.log "запрос на выгрузку '"+inputSearch.val(),responseRow
        if responseRow.data.length == 1
          echo_message_js  " найдено полное соответствие "+responseRow.data,'successful_message'
          console.log " найдено полное соответствие ",responseRow.data
    )




  # шапка таблицы
  createHead:(data_row)->
    _this = @
    # общий контейнер
    head_info = $('<div>',{id:'head_info'});
    table = $('<table>',{id:'js--payment-window--head_info-table'});

    ###
    # строка 1
    ###
    tr = $('<tr/>').append($('<td/>',{'colspan':'2'}).append($('<span/>',{'html':'номер счёта','class':'span-greyText'}))).append($('<td/>')).append($('<td/>'))
    table.append(tr)

    ###
    # строка 2
    ###
    tr = $('<tr/>')
    # поиск
    tr.append($('<td/>').append(inputSearch = $('<input/>',{'type':'text','id':'js--payment-window--search-pp-input','val':data_row.invoice_num})))
    tr.append($('<td/>').append(buttonSearch = $('<button/>',{'id':'js--payment-window--search-pp-button'})))

    # добавляем обработчик для на элементы поиска по счетам
    @addHandlerForInputSearch(inputSearch, buttonSearch)


    # table.append(tr)
    div1 = $('<div/>')
      .append($('<span/>',{'html':'Счёт','class':'span-boldText'}))
      .append($('<span/>',{'html':' № ', 'class':'span-greyText span-boldText'}))
      .append($('<span/>',{'html':data_row.invoice_num,'class':'span-boldText'}))
      .append($('<span/>',{'html':' от ', 'class':'span-greyText'}).css('paddingLeft','10px'))
      .append($('<span/>',{'html':data_row.invoice_create_date}).css('paddingLeft','10px'))
      .append($('<span/>',{'html':' на сумму ', 'class':'span-greyText'}).css('paddingLeft','10px'))
      .append(@head.price = $('<span/>',{'html':data_row.price_out}).css('paddingLeft','10px'))

    div2 = $('<div/>')
      .append($('<span/>',{'html':data_row.manager_name,'data-id':data_row.manager_id}))
      .append($('<span/>',{'html':' '+data_row.client_name,'data-id':data_row.client_id}).css('paddingLeft','28px'))

    tr.append($('<td/>').append(div1).append(div2))
    div1 = $('<div/>')
      .append($('<span/>',{'html':'оплачен:', 'class':'span-greyText'}))
      .append(@head.r_percent = $('<span/>',{'html':data_row.percent_payment}))
      .append('%')
    div2 = $('<div/>')
      .append($('<span/>',{'html':'условия:', 'class':'span-greyText'}))
      .append(@head.conditions = $('<span/>',{'html':data_row.conditions}))
      .append('%')
    tr.append($('<td/>').append(div1).append(div2))


    # span_ttn_number = $('<span/>')


#    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)

  createRow:(data_row,responseData)->
    _this = @
    @getData('create_payment',{'id':data_row.id},()->
      console.warn(_this.response.data)
      # добавляем информацию в главный объект
      len = responseData.length
      responseData[len] = new ppRowObj(_this.response.data)
      # добавляем строку в таблицу в окне
      $(_this.$el).find('#js--payment-window--body_info-table').append(new ppRow(responseData,len,_this.access,_this,data_row))

    )
  # update и save
  updateHeaderPercent:(data_row)->
    _this = @
    oldPer = Number(@head.r_percent.html())
    recalcInvoice = @recalcInvoice();

    console.info oldPer,recalcInvoice.percent_payment
#    if recalcInvoice.percent_payment != oldPer and oldPer > 0
    send = {}
    send.percent_payment = round_money(recalcInvoice.percent_payment)
    send.price_out_payment = round_money(recalcInvoice.price_out_payment)


    data_row.percent_payment        = send.percent_payment
    _this.options.percent_payment   = send.percent_payment
    data_row.price_out_payment      = send.price_out_payment
    _this.options.price_out_payment = send.price_out_payment
    console.log data_row


    if send != undefined
      send.id = @options.id
      @head.r_percent.html(round_money(recalcInvoice.percent_payment))
      new sendAjax 'save_percent_from_invoice', send, ()->
        $('#js-main-invoice-table').invoice('reflesh',data_row)

  # подсчёт итого
  recalcInvoice:()->
    console.log 'updateHeaderPercent'
    # общий % оплаты
    percent_payment = 0
    # общая сумма оплаты
    price_out_payment = 0

    @bodyRows.find('tr').each((index)->
      if !$(this).hasClass('deleted') and index > 0
        data = $(this).data()
        console.log data
        if data.percent != undefined
          percent_payment += Number(data.percent)
        if data.price != undefined
          price_out_payment += Number(data.price)
    )
    # формируем ответ
    {
      # общий % оплаты
      percent_payment: percent_payment
      # общая сумма оплаты
      price_out_payment: price_out_payment
    }


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
    return
  getButtons:(obj,data_row,responseData)->
    _this = @
    @saveObj = {}
    buttons = []
#    if Number(data_row.invoice_num) <= 0 ||  data_row.invoice_create_date == '00.00.0000'
    if @access == 2
      buttons.push(
        text: 'Добавить платеж',
        class:  'button_yes_or_no yes add_payment_button',
        click: ()->
          console.warn data_row
          _this.createRow(data_row,responseData)
        )
    buttons.push(
      text: 'Показать удалённые('+_this.countDelRow+')',
      class:  'button_yes_or_no no show_del_payment_button',
      id:'js--how_del_payment_button',
      data:{
        num:_this.countDelRow
      },
      click: ()->
        if Number($(this).data('num')) > 0
          if($(this).hasClass('no'))
            $(this).removeClass('no').html('Скрыть удалённые ('+$(this).data('num')+')')
          else
            $(this).addClass('no').html('Показать удалённые ('+$(this).data('num')+')')

        _this.updatePaymenContent($(this),responseData,data_row)
      )
    buttons.push(
      text: 'Закрыть',
      class:'button_yes_or_no no',
      click:()->
        _this.destroy()
        #_this.confirmAndCreateBill(obj,data_row)
      )

    return buttons

  editSaveObj:(key,value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return
  # бух присваивает номер и подтверждает создание счета
  confirmAndCreateBill:(obj,data_row)->
    _this = @
    console.log data_row
    @saveObj.id = data_row.id
    # check update ttn number
    reload = false
    if @saveObj.number
      reload = true
      data_row.invoice_num = @saveObj.number
    if @saveObj.date
      reload = true
      data_row.invoice_create_date = @saveObj.date

    if(reload)
      # обновляем информацию по строке
      obj.parent().data({}).data(data_row)
      # обновляем дом в строке
      $('#js-main-invoice-table').invoice('reflesh',data_row.id)
      # отправляем запрос
      new sendAjax 'confirm_create_bill',@saveObj, ()->
        _this.destroy()
    else
      echo_message_js('Для создания счёта необходимо ввести его номер','error_message')
  # убиваем окно
  destroy:()->
    $(@$el).parent().dialog('destroy').remove()
###
# прототип окна счёта
# некоторые пользователи емеют право редактировать номер счёта и дату
###
class invoiceWindow
  saveObj:{}
  defaults:
    id:0,
    number:'0000',
    type:"new"

  constructor: (obj, data_row, data, accces) ->
    # запоминаем уровень допуска
    @access = accces
    # сохраняем информацию по строке
    @options = data_row
    # сборка окна счёта
    @init(obj, data_row, data, accces)
  # собираем окно счёт
  init:(obj, data_row, responseData, accces)->
    _this = @
    # запрос данных
    if(responseData!= undefined)
      ###
      # создание контейнера
      ###
      main_div = $('<div/>')
      ###
      # добавляем таблицу
      ###
      main_div.append(@createTable(responseData))
      ###
      # добавление шапки окна
      ###
      main_div.prepend(@createHead(data_row))

      ###
      # создание окна
      ###
      @myObj = new modalWindow({
        html:main_div,
        width:'1000px',
        maxHeight:'100%',
        title:'Счёт',
        buttons: @getBillButtons(obj,data_row)
      },{
        closeOnEscape:true
      })
      @$el = @myObj.options.html[0]

  # сборка таблицы
  createTable:(responseData)->
    _this = @
    table = $('<table/>',{'id':'js-invoice--window--ttn-table'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))

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
    @checkNumber = 0
    for position in responseData
      tr = $('<tr/>').data(position).attr('data-id',position.id)

      td  = $('<td/>')
      # Number
      td  = $('<td/>').append(i)
      tr.append(td)
      # Name
      border = '1px solid green'
      td  = _this.createTS_copyContent(position,'name',table)
      tr.append(td)
      # Quantity
      td  = _this.createTS_copyContent(position,'quantity',table)
      tr.append(td)

      # Price for one
      pr_out = calc_price_with_discount(position.price, position.discount)
      position.pr_out = round_money(pr_out)+' р.';
      # td  = $('<td/>').append(round_money(pr_out)+' р.')
      td  = _this.createTS_copyContent(position,'pr_out',table)
      tr.append(td)
      # Price for all
      if position.quantity == 0
        position.quantity = 1

      main_price += pr_out*position.quantity

      nds += Number(round_money(pr_out*position.quantity/118*18))
      position.main_price = round_money(pr_out*position.quantity)+' р.'

      td  = _this.createTS_copyContent(position,'main_price',table)
      tr.append(td)
      i++
      table.append(tr)

    # ИТОГО
    table.append(tr = $('<tr/>'))
    td  = $('<th/>')
    # tr.append(td)
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
    # tr.append(td)
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

  ###
  # возвращает <td> с textarea и кнопкой копировать,
  # изменения в поле textarea не редактируют информацию, 
  # textarea служит только для того, чтобы программно скопировать информацию из его тела
  ###
  createTS_copyContent:(position,key,table)->
    _this = @
    td  = $('<td/>',{
      'class':'myyClass1',
      on:
        click:()->
          # убиваем все textarea в таблице
          _this.updateTableTextarea($(this).parent().parent())

          # перед вставкой textarea обнуляем отступы в ячейке
          $(this).css('padding','0').html(textarea)
          # получаем контент ячейки
          name = $(this).data().val
          # вставка textarea
          textarea = $('<textarea/>',{
            'val':name,
            'click':(event)->
              event.preventDefault()
              event.stopPropagation()

              return false
          })

          $(this).html(textarea).focus()
          # кнопка сохранить
          div = $('<div/>',{
            'class':'myBlockBefore',
            'html':'Копировать',
            click:(event)->
              $(this).parent().find('textarea').select();
              # event.preventDefault()
              try
                # // Now that we've selected the anchor text, execute the copy command
                successful = document.execCommand('copy');

                # $('<input/>',{'val':td1.text()}).execCommand("Copy")
                msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copy email command was ' + msg);
                # _this.updateTableTextarea(table)

              catch  error
                console.log  error
            # on:
            #   mouseenter:()->
            #     $(this).remove()
          }).css({
            'marginLeft':($(this).innerWidth() - 159),
            'marginTop':-2
          })
          $(this).append(div)
        mouseleave:()->
          _this.updateTableTextarea($(this).parent().parent())

      }).append($('<div/>',{'class':'mmmmm','html':position[key]})).data('val',position[key])
    return td
  # перебор всех строк и репласе всех textarea
  updateTableTextarea:(table) ->
    table.find('textarea').each(()->
        name = $(this).parent().data().val
        $(this).parent().attr('style','').html($('<div/>',{'class':'mmmmm','html':name}))  
      )
    return
  # шапка таблицы счёт
  createHead:(data_row)->
    _this = @
    ###
    # контейнер шапки окна ТТН
    ###
    head_info = $('<div>',{id:'ttn_head_info'});
    ###
    # сборка таблицы с общей информации по ТТН
    ###
    table = $('<table>',{id:'ttn_head_info-table'});

    ###
    # строка с информацией по клиенту
    ###
    tr = $('<tr/>')
    tr.append($('<td/>',{'html':@options.client_name,'class':'ttn_client_name'}))
    tr.append($('<td/>',{
      'html':@options.client_requisit_name,
      'class':'ttn_requisits',
      'click':()->
        echo_message_js('Вызов окна просмотра реквизитов')
      }))
    table.append(tr)

    ###
    # если номер к данной ТТН не назначен - выводим строку
    # с формой назначения номера ТТН и даты от которой эта ТТН выставлена
    ###

    # span_ttn_number = $('<span/>')


    td = $('<td/>',{'colspan':'2'})

    if (Number(@options.invoice_num) == 0 or @options.invoice_create_date == '00.00.0000' ) && @access == 2
      input = $('<input/>',{
        'val':@options.invoice_num,
        'data-val':@options.invoice_num,
        'class':'ttn_number_input',
        focus:()->
          $(this).val('') if Number($(this).val()) == 0
          return
        blur:()->
          $(this).val($(this).attr('data-val')) if Number($(this).val()) == 0
          return
        keyup:()->
          _this.editSaveObj('number', $(this).val(), _this.options.invoice_num)
      })

      input_date = $('<input/>',{
        'val':@options.invoice_create_date,
        'class':'',
        blur:()->
          _this.editSaveObj('date', $(this).val(), _this.options.invoice_create_date)

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

      # номер ттн
      td.append('№ ТТН ').append($('<span/>').append(input))
      # дата ттн
      td.append($('<span/>').append(input_date))

    # счёт
    else
      span_invoice = $('<span/>',{'html':"№ Счёта "+@options.invoice_num+" от " + @options.invoice_create_date})
      td.append(span_invoice)


    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)
  getBillButtons:(obj,data_row)->
    _this = @
    @saveObj = {}
    if Number(data_row.invoice_num) <= 0 ||  data_row.invoice_create_date == '00.00.0000'
      buttons = [{
        text: 'Отмена',
        class:  'button_yes_or_no no',
        click: ()->
          _this.destroy()
      },{
        text: 'Создать',
        class:  'button_yes_or_no',
        click: ()->
          _this.confirmAndCreateBill(obj,data_row)
      }];

  editSaveObj:(key,value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return
  # бух присваивает номер и подтверждает создание счета
  confirmAndCreateBill:(obj,data_row)->
    _this = @
    console.log data_row
    @saveObj.id = data_row.id
    # check update ttn number
    reload = false
    if @saveObj.number
      reload = true
      data_row.invoice_num = @saveObj.number
    if @saveObj.date
      reload = true
      data_row.invoice_create_date = @saveObj.date

    if(reload)
      # обновляем информацию по строке
      obj.parent().data({}).data(data_row)
      # обновляем дом в строке
      $('#js-main-invoice-table').invoice('reflesh',data_row.id)
      # отправляем запрос
      new sendAjax 'confirm_create_bill',@saveObj, ()->
        _this.destroy()
    else
      echo_message_js('Для создания счёта необходимо ввести его номер','error_message')
  # убиваем окно
  destroy:()->
    $(@$el).parent().dialog('destroy').remove()


###
# прототип окна ТТН
###
class invoiceTtn
  # checkbox number
  checkNumber: 0
  saveObj:{}
  defaults:
    id:0,
    number:'0000',
    type:"new"

  constructor: (obj, data_row, data, accces, ttn ) ->
    if ttn != null
      @defaults = $.extend({}, @defaults, ttn )
      @defaults.number = '0000' if @defaults.number == null
    else
      ttn = {}
    # запоминаем уровень допуска
    @access = accces
    # сохраняем информацию по строке
    @options = data_row

    # собираем окно ттн
    @init(obj, data_row, data, accces, ttn)




  # собираем окно ттн
  init:(obj, data_row, responseData, accces, ttn)->
    _this = @
    # запрос данных
    if(responseData!= undefined)
      ###
      # создание контейнера
      ###
      main_div = $('<div/>')
      ###
      # добавляем таблицу
      ###
      main_div.append(@createTable(responseData))
      ###
      # добавление шапки окна
      ###
      main_div.prepend(@createHead(ttn))
      ###
      # выбор способа доставки
      ###
      # только для менеджеров и при условии что, есть что выбирать
      if @access == 5 && @checkNumber>0
        main_div.append(@createDeliveryChoose())
      ###
      # ранее созданные ттн
      ###
      main_div.append(@alreadyWasСreated())
      ###
      # создание окна
      ###
      @myObj = new modalWindow({
          html:main_div,
          width:'1000px',
          maxHeight:'100%',
          title:'Запрос ТТН',
          buttons: @getButtons(obj,data_row)
        },{
          closeOnEscape:true
        })
      @$el = @myObj.options.html[0]

  editSaveObj:(key,value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return
  # ранее созданные ттн
  alreadyWasСreated:()->
    # console.log 'alreadyWasСreated',@options
    content = $('<div/>',{
      'class':"ttn--already-was-created"
      });
    # console.log @options.ttn.length

    if(@options.ttn && @options.ttn.length > 0)
      content.append($('<div/>',{
        'class':'ttn--already-was-created--head',
        'html':'Ранее оформленные ТТН:'
      }));

      # console.log @options
      for oldTtn in @options.ttn
        if oldTtn.positions_num != null
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
      #
  ###
  # выбор способа доставки
  ###
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
  createHeadAdmin:(ttn)->
    _this = @
    ###
    # контейнер шапки окна ТТН
    ###
    head_info = $('<div>',{id:'ttn_head_info'});
    ###
    # сборка таблицы с общей информации по ТТН
    ###
    table = $('<table>',{id:'ttn_head_info-table'});

    ###
    # строка с информацией по клиенту
    ###
    tr = $('<tr/>')
    tr.append($('<td/>',{'html':@options.client_name,'class':'ttn_client_name'}))
    tr.append($('<td/>',{
      'html':@options.client_requisit_name,
      'class':'ttn_requisits',
      'click':()->
        echo_message_js('Вызов окна просмотра реквизитов')
      }))
    table.append(tr)

    ###
    # если номер к данной ТТН не назначен - выводим строку
    # с формой назначения номера ТТН и даты от которой эта ТТН выставлена
    ###

      # span_ttn_number = $('<span/>')

    span_invoice = $('<span/>',{'html':"№ Счёта "+@options.invoice_num+" от " + @options.invoice_create_date})
    td = $('<td/>',{'colspan':'2'})

    if Number(_this.defaults.number) == 0
      input = $('<input/>',{
        'val':_this.defaults.number,
        'data-val':_this.defaults.number,
        'class':'ttn_number_input',
        focus:()->
          $(this).val('') if Number($(this).val()) == 0
          return
        blur:()->
          $(this).val($(this).attr('data-val')) if Number($(this).val()) == 0
          return
        keyup:()->
          _this.editSaveObj('number', $(this).val(), _this.defaults.number)


        })
      input_date = $('<input/>',{
        'val':_this.defaults.date,
        'class':'',
        blur:()->
          _this.editSaveObj('date', $(this).val(), _this.defaults.date)

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

      # номер ттн
      td.append('№ Счёта ').append($('<span/>').append(input))
      # дата ттн
      td.append($('<span/>').append(input_date))

    # счёт
    td.append(span_invoice)
    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)

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
  createHeadManager:(ttn)->
    # ttn number & date
    span_ttn = $('<span/>',{'html':"№ ТТН "+@defaults.number+" от "}).append(@spanDate())
    # invoice number & date
    span_invoice = $('<span/>',{'html':"№ Счёта "+@options.invoice_num+" от " + @options.invoice_create_date})

    table = $('<table>',{id:'ttn_head_info-table'})

    # client info row
    table.append(tr = $('<tr/>'))
    tr.append($('<td/>',{'html':@options.client_name,'class':'ttn_client_name'}))
    tr.append($('<td/>',{
      'html':@options.client_requisit_name,
      'class':'ttn_requisits',
      'click':()->
        echo_message_js('Вызов окна просмотра реквизитов')
      }))
    # если есть что выбирать
    if @checkNumber > 0
      table.append(tr = $('<tr/>'))
      tr.append($('<td/>',{'colspan':'2'}).append(span_ttn).append(span_invoice))

    # create and return conteiner
    $('<div>',{id:'ttn_head_info'}).append(table)

  # проверка прав и сборка шапки окна
  createHead:(ttn)->
    # console.log  @access
    # проверка уровня доступа и вызов соответствующей шапки
    switch @access
      when 1 then @createHeadAdmin(ttn)
      when 2 then @createHeadAdmin(ttn)
      else
        @createHeadManager(ttn)

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

    td_main_check = $('<th/>',{
        click:()->
          input  = $(this).find('input')
          td = $(this)
          # клик по главному чекбоксу
          _this.clickMainCheckbox(table,td,input)
        })
    tr.append(td_main_check)

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
    @checkNumber = 0
    for position in responseData
      tr = $('<tr/>').data(position).attr('data-id',position.id)
      # чекбоксы
      if Number(position.ttn_id) == 0
        @checkNumber++
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
    if @checkNumber>0
      td_main_check.append(main_checkbox)
    else
      td_main_check.width('20px')
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
    # console.log 654
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
    })

    if@access != 1 && @access != 2
      td.append(main_checkbox)
    else
      td.width('20px')

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
      # if @defaults.buch_id != undefined && @defaults.buch_id == null

      if Number(_this.defaults.id) == Number position.ttn_id
        td  = $('<td/>').addClass('checked buh_style')
      else
        td  = $('<td/>')
        if Number(position.ttn_id) > 0
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
    # console.log @access
    switch @access
      when 1 then @createTableAdmin(responseData)
      when 2 then @createTableAdmin(responseData)
      # when 5 then tbl = @createTableManager(responseData)
      else
        @createTableManager(responseData)

  # клик по главному чекбоксу
  clickMainCheckbox:(table,td,input)->
    if input.prop('checked') || !input.prop('checked') && input.hasClass('checked_no_full')
      input.prop('checked',false).removeClass('checked_no_full')
      td.removeClass('checked')
      table.find('td input').each((index,el)->
        $(this).prop('checked',false).parent().removeClass('checked')
        )
    else
      # console.log this
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
      main_check.prop('checked', true).removeClass('checked_no_full')
      main_check.parent().addClass('checked')
    else if table.find('td input:checked').length > 0
      main_check.prop('checked',false).addClass('checked_no_full')
      main_check.parent().addClass('checked')
    else
      main_check.prop('checked',false).removeClass('checked_no_full')
      main_check.parent().removeClass('checked')

  # запрос новой ттн (нажатие на кнопку запросить)
  # менеджер хочет создать новую ттн
  # в базу заводится строка ттн
  queryNewTtn:(obj,data_row)->
    _this = @
    console.log obj
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
    new sendAjax 'create_new_ttn',{invoise_id:@options.id,positions:options.join(','),position_numbers:position_numbers.join(','), delivery:delivery.join('')}, (response)->
      # при положительном ответе
      _this.destroy()
      # сервер должен вернуть информацию по новой ТТН
      if(response.data)
        # если успешно вернул - записываем новую информацию в объект
        data_row.ttn[data_row.ttn.length] = new ttnObj(response.data);
        # обновляем информацию в DOM
        $('#js-main-invoice-table').invoice('reflesh',data_row.id)
        # console.log

      if(delivery.join('') == 'our_delivery')
        new modalConfirm({html:'Открыть карту курьера в новой вкладке?'},()->
          window.open(window.location.origin+'/dostavka_new/', '_blank');
          )

        # Вы уверены, что хотите установить флаг рекламации?
  # убиваем окно
  destroy:()->
    $(@$el).parent().dialog('destroy').remove()

  # delete window
  # бух присваивает ттн номер и подтверждает создание ттн
  confirmAndCreateTtn:(obj,data_row)->
    _this = @
    row_id = @options.id
#    console.warn @saveObj
    # check update ttn number
    if @saveObj.number
      @saveObj.id = @defaults.id

      # правика сохраняемых значений в главном объекте
      for el in data_row.ttn
        if el.id == @defaults.id
          # console.log el
          for key,l of @saveObj
            el[key] = l
          # console.log el

      # обновляем DOM
      $('#js-main-invoice-table').invoice('reflesh',data_row.id)
      # отправляем запрос
      new sendAjax 'confirm_create_ttn',@saveObj, ()->
        _this.destroy()
    else
      echo_message_js('Для создания ттн необходимо ввести её номер','error_message')
    # delete window





  getButtons:(obj,data_row)->
    _this = @
    @saveObj = {}
    if @access == 2 || @access == 1
      if Number(@defaults.number) != undefined && Number(@defaults.number) == 0
        buttons = [{
          text: 'Отмена',
          class:  'button_yes_or_no no',
          click: ()->
            _this.destroy()
        },{
          text: 'Создать',
          class:  'button_yes_or_no',
          click: ()->
            _this.confirmAndCreateTtn(obj,data_row)
        }];


    else
      if @checkNumber > 0
        buttons = [{
            text: 'Отмена',
            class:  'button_yes_or_no no',
            click: ()->
              _this.destroy()
          },{
            text: 'Запросить',
            class:  'button_yes_or_no',
            click: ()->
              _this.queryNewTtn(obj,data_row)
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
    showMore: []  # кнопка показать ещё
    Prange:50
    PminStart:0
    Pmin:0
    Pmax:0

    tabMenu: []   # меню



    access_def: 0
    response_def:{}
    constructor: (el, options) ->
      self = @
      new sendAjax 'get_data',{} , (response)->
#        console.warn response
        self.options = $.extend({}, self.defaults, response)
        self.access = response.access
        self.$el = $(el)
        ###
        # добавление меню
        ###
        self.addMenu()

        self.init()

        self.quick_button_div = $('#quick_button_div')
        console.log(self)
        if self.access == 2 or self.access == 1
          self.quick_button_div.append($('<span/>',{
            'html':'приходы',
            'class':'button',
            click:(e)->
              echo_message_js "Окно с поиском по приходам","successful_message"
              new modalWindow({
                html:"Окно с поиском по приходам","successful_message",
                width:'1000px',
                maxHeight:'80%',
                title:'Приходы по счёту'
              },{
                closeOnEscape:true
              })
          }))
          self.quick_button_div.append($('<apsn/>',{
            'html':'расходы',
            'class':'button',
            click:(e)->
              echo_message_js "Окно с поиском по расходам","successful_message"
              new modalWindow({
                html:"Окно с поиском по расходам","successful_message",
                width:'1000px',
                maxHeight:'80%',
                title:'Расходы по счёту'
              },{
                closeOnEscape:true
              })
          }))


    # обновление одной строки
    reflesh:(id)->
      if typeof id is 'string'
        if $(@$el).find('#tt_'+id).length >0
          data = $(@$el).find('#tt_'+id).data()
          return $(@$el).find('#tt_'+id).replaceWith(@createRow(data))
      else
        if $(@$el).find('#tt_'+id.id).length >0
          data = $(@$el).find('#tt_'+id.id).data(id)
          return $(@$el).find('#tt_'+id.id).replaceWith(@createRow(id))
    addMenu:()->
      _this = @
      @tabMenu = $('#js-menu-invoice ul')
      section = Number($.urlVar('section'))
      tabs = [
        'Все',
        'Запрос',
        'Готовые',
        'Част. оплаченные',
        'Оплаченные',
        'Запрос ТТН',
        'Готовые ТТН',
        'Част. отгрузка',
        'Отгрузка',
        'Закрытые'
      ]
      for n,i in tabs
        @tabMenu.append( li = $('<li/>',{
          click:(e)->
            # меняем URL
            $.urlVar('section',$(this).data('index'))
            # удаляем выделение со старого выбранного элемента
            _this.tabMenu.find('.selected').removeClass('selected')
            # выделяем текущий элемент
            $(this).addClass('selected')
            # обновляем информацию в таблице
            _this.updateTable()
        }) )
        li.append(span = $('<span/>'))
        span.append($('<div/>',{'class':'border','html': tabs[i]}))
        li.data('index',i)
        if i==0
          li.css({'float':'right'})
        if i==section
          li.addClass('selected')
    # обновление информации в таблице
    updateTable:() ->
      _this = @
      window_preload_add()
      new sendAjax 'get_data',{} , (response)->
        _this.options = $.extend({}, _this.defaults, response)
        _this.init()
        window_preload_del()

    init: () ->
      _this = @
      # очищаем старое поле
      @$el.find('tbody').html('')
      ###
      # выгрузка первой страницы
      ###
      len = Number(@options.data.length)
      @Pmax = @Prange
      @Pmin = @PminStart
#      console.log len,@Prange,@Pmax
      @Pmax = len if len < @Pmax

      for i in [@Pmin...@Pmax]
#        console.log @Pmin,@Pmax,i
##        if @options.data[i] != undefined
#        console.log @options.data[i]
        @$el.find('tbody').append(@createRow(@options.data[i]))

      ###
      # кнопка показать ещё
      ###
      $('#js-main-invoice-show-rows').remove() if($('#js-main-invoice-show-rows').length > 0)

      $('#js-main-invoice-div').append(@showMore = $('<div/>',{'id':'js-main-invoice-show-rows','html':'Показать ещё '+@Pmax+'/'+len,'data-c':len}))
      @showMore.click(()->
        _this.addMoreRows()
      )

    addMoreRows:()->
      text = 'Показать еще ' + @Pmax+'/'+@showMore.data().c
      Pmin = @Pmax
      @Pmin = @Pmax+1
      @Pmax = @Pmax+@Prange
      if Number(@showMore.data().c) < @Pmax
        @Pmax = Number(@showMore.data().c)
        text = 'Показано строк ' + @Pmax+'/'+@showMore.data().c

      if Pmin == @Pmax
        return echo_message_js("На страницу уже выгружены все счета "+ Pmin + " / " + @Pmax)

      for i in [@Pmin...@Pmax]
        @$el.find('tbody').append(@createRow(@options.data[i]))



      @showMore.html(text)
      # скрол к первой подгруженной строке
      t = $(window).scrollTop() + $(window).height() - 100
      $("html, body").animate({ scrollTop: t }, 600)  if t > 0

    printOptions:()->
      console.info @options.access
      console.info @options.data

    # обновление сонтента в таблице без запроса
    updateRows:()->
      @$el.find('tbody').html('')
      @init();


    getTtnRow:(row,ttn,i)->
      _this = @
      tr = $('<div/>',{
        'id':ttn.id,
        'class':'row'
        }).data(ttn)
      # определяем номер
      if ttn.number <= 0
        number = 'запрос'
      else
        number = ttn.number
      # определяем дату
      tr.append($('<div/>',{
        'class':'defttn1 cell',
        'html':number,
        click:()->
          t = $(this)
          # окно Запрос ТТН
          new sendAjax('get_ttn',{'id':row.id},(response)->
            # создаем экземпляр окна ттн
            new invoiceTtn(t, row, response.data, _this.options.access ,ttn) if response.data != undefined
            )
        }).width(_this.defttn[0]))

      tr.append($('<div/>',{
        'class':'defttn2 cell',
        'html':ttn.date,
        click:()->
          # окно Запрос ТТН
          t = $(this)
          new sendAjax('get_ttn',{'id':row.id},(response)->
            # создаем экземпляр окна ттн
            new invoiceTtn(t, row, response.data, _this.options.access ,ttn) if response.data != undefined
            )
        }).width(_this.defttn[1]))

      if (ttn.return != null && Number(ttn.return) == 1)
        check = ' checked'
      else
        check = ''

      divw = $('<div/>',{
        'class': 'defttn3 cell invoice-row--ttn--vt invoice-row--checkboxtd'+check,
        # 'html':ttn.id,
        'data-id':ttn.id
        click:()->
          if _this.options.access != 2
            $(this).prev().click()
            return false
          console.log

          # echo_message_js(ttn.id+' + '+$(this).attr('data-id'))
          if Number(ttn.return) == 0
            # вставляем подтверждение
            t = $(this)
            # new modalConfirm({html:'Это действие изменит в системе дату получения подписанных документов<br>Продолжить?'},()->
            ttn.return = ++ttn.return&1
            t.addClass('checked')
              # сохраняем значение флага
            # echo_message_js(row.ttn[i].id)
            new sendAjax 'ttn_was_returned',{id:row.ttn[i].id ,val:ttn.return}
              # echo_message_js('Да, я уверен.'+row.ttn[i].id)
              # )
            console.log
          else
            # ограничение на снятие
            # if _this.options.access != 1
              # echo_message_js('Отметку омеет право снять только администратор')
              # return false
            ttn.return = ++ttn.return&1
            $(this).removeClass('checked')
            new sendAjax 'ttn_was_returned',{id:row.ttn[i].id ,val:ttn.return}
      }).width(_this.defttn[2])


      tr.append(divw).data(ttn)

      tr

    ###
    # создание TD ттн
    ###
    getTdTtn:(row)->
      if @defttn == undefined
        @defttn = {
          0:$('#defttn1').width(),
          1:$('#defttn2').width(),
          2:$('#defttn3').width()
        }

      _this = @

      table = $('<div/>',{'class':'table','style':'width:100%'})
      #  перебор ТТН
      for ttn,i in row.ttn
        table.append(@getTtnRow(row,ttn,i))

      if row.ttn.length <= 0
        if(_this.options.access == 5)
          td = $('<td/>',{
            'colspan':'3',
            'class':'js-query-ttn',
            'html':'Запросить',
            click:()->
              # окно Запрос ТТН
              t = $(this)
              new sendAjax('get_ttn',{'id':row.id},(response)->
                # создаем экземпляр окна ттн
                new invoiceTtn(t, row, response.data, _this.options.access) if response.data != undefined
                )
            })
        else
          td = $('<td/>',{
            'colspan':'3',
            'class':'js-query-ttn'
            })
      else
        td = $('<td/>',{
          'colspan':'3',
          'class':'js-query-ttn-rows'
          }).append(table)

      td

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

      td = $('<td/>',{
        'class':'invoice-row--fist-td',
        click:()->
          t = $(@)
          new sendAjax('get_ttn',{'id':row.id},(response)->
            # создаем экземпляр окна ттн
            new invoiceWindow(t, row, response.data, _this.options.access )
            )
        })
        .append($('<div/>',{
          'class':'invoice-row--number',
          'html':'<span>'+row.invoice_num+'</span>  '+row.invoice_create_date

            }))
        .append($('<div/>',{
          'class':'invoice-row--type',
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
      td = $('<td/>',{
        click:(e)->
          t = $(@)
          new sendAjax('get_payment',{'id':row.id},(response)->
#            console.log "654"
            # создаем экземпляр окна ттн
            new paymentWindow(t, row, response.data, _this.options.access )
            )
        on:
          mouseenter:()->
            $(this).css('backgroundColor':'#f1f1f1')
          mouseleave:()->
            $(this).attr('style','')
        }).css('cursor','pointer')
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


          new modalConfirm({html:'Вы уверены, что хотите установить флаг рекламации?'},()->
            row.flag_flag = 1;
            t.addClass('checked')
            # сохраняем значение флага
            new sendAjax 'edit_flag_flag',{id:row.id,val:row.flag_flag}
            )

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

      td = @getTdTtn(row)


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


      return tr



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
    # показать ещё
    if $(this).scrollTop() == $(document).height() - $(window).height()
      if($('#js-main-invoice-show-rows').length > 0)
        $('#js-main-invoice-show-rows').stop().animate({'bottom':'15px'},100)
    else
      if($('#js-main-invoice-show-rows').length > 0)
        $('#js-main-invoice-show-rows').stop().animate({'bottom':'-45px'},100)

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
    $('#invoice-button-top').stop().animate({right:'15px',bottom:'15px',width:'40px',height:'40px','opacity':0.6},100)
  else
    if($('#js-main-invoice-table-clone').length > 0)
      $('#js-main-invoice-table-clone')
        # .stop( true, true )
        .css {'display':'none'}
    # скрываем кнопку вверх
    $('#invoice-button-top').stop().animate({right:'35px',bottom:'35px',width:'0px',height:'0px','opacity':0},100)



$(document).on 'click','#invoice-button-top', (event) ->
  $("html, body").animate({ scrollTop: 0 }, 600);

