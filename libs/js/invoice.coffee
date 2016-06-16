###
# class Invoice
#
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:25:40
###


###
# возвращяет текущую дату в читабельном формате
###
getDateNow = () ->
  d = new Date();
  dd = d.getDate()
  dd = '0' + dd if dd < 10
  mm = d.getMonth() + 1
  mm = '0' + mm if mm < 10
  yy = d.getFullYear()
  # yy = d.getFullYear() % 100
  # yy = '0' + yy if yy < 10
  return dd + '.' + mm + '.' + yy

###
# возвращяет текущую дату в читабельном формате
###
getDateTomorrow = () ->
  d = new Date();
  d.setDate(d.getDate() + 1);
  dd = d.getDate()
  dd = '0' + dd if dd < 10
  mm = d.getMonth() + 1
  mm = '0' + mm if mm < 10
  yy = d.getFullYear()
  # yy = d.getFullYear() % 100
  # yy = '0' + yy if yy < 10
  return dd + '.' + mm + '.' + yy

###
# округляет и приводит числа к денежному формату
# строку преобразует в число
###
round_money = (num) ->
  num = Number(num);
  new_num = Math.ceil((num) * 100) / 100;
  return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");

###
# подсчет скидки
# @param      price_out - входящая цена
# @discount   discount - скидка
###
calc_price_with_discount = (price_out, discount) ->
  return Number(price_out / 100) * (100 + Number(discount));

###
# транслитерация
###
cyrill_to_latin = (text)->
  arrru = ['Я', 'я', 'Ю', 'ю', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ', 'Ж', 'ж', 'А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д',
    'Е', 'е', 'Ё', 'ё', 'З', 'з', 'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р',
    'р', 'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ы', 'ы', 'Ь', 'ь', 'Ъ', 'ъ', 'Э', 'э', ' ']

  arren = ['Ya', 'ya', 'Yu', 'yu', 'Ch', 'ch', 'Sh', 'sh', 'Sh', 'sh', 'Zh', 'zh', 'A', 'a', 'B', 'b', 'V', 'v', 'G',
    'g', 'D', 'd', 'E', 'e', 'E', 'e', 'Z', 'z', 'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o',
    'P', 'p', 'R', 'r', 'S', 's', 'T', 't', 'U', 'u', 'F', 'f', 'H', 'h', 'C', 'c', 'Y', 'y', '`', '`', '\'', '\'', 'E',
    'e', '_']

  for itm,i in arrru
    reg = new RegExp(arrru[i], "g");
    text = text.replace(reg, arren[i]);

  return text;

###
# метод отправки запроса AJAX
###
class sendAjax
  defaults:
    AJAX: 'test',
    options: {}
  func: ()->
    true

  response: {}

  constructor: (ajaxName, options = {}, func = ()-> true)->
    @href = window.location.href
    if options.url
      @href = options.url
      options.url = undefined

    opt = {
      AJAX: ajaxName,
      options: options,
    }
    @func = func


    # console.log
    @options = $.extend({}, @defaults, opt)
    @sendAjax()

# отправка запроса
  sendAjax: ()->
    _this = @
    data = {
      AJAX: @options.AJAX
    }
    for k,v of @options.options
# console.log k + " is " + v
      data[k] = v


    $.ajax
      url: @href
      type: "POST"
      data: data
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
        _this.func(_this.response)

###
# прототип окна Confirm
#
# @version   21.04.2016 11:20:30
###
class modalConfirm
  defaults:
    title: 'Подтвердите действие',
    html: 'Вы уверены',

  constructor: (data = {},
    func = ()->,
    func2 = ()->)->
# get options
    _this = @
    @options = $.extend({}, @defaults, data)
    @options.buttons = [{
      text: 'Да',
      class: 'button_yes_or_no no',
      style: 'float:right;'
      click: ()->
        func()
        $(_this.selfObj.winDiv).dialog('close').dialog('destroy').remove()
    }, {
      text: 'Нет, Спасибо.',
      class: 'button_yes_or_no no',
      style: 'float:right;',
      click: ()->
        func2()
        $(_this.selfObj.winDiv).dialog('close').dialog('destroy').remove()
#          $(this).parent().parent().parent().parent().parent().prev().dialog('destroy').remove();
    }]

    @selfObj = new modalWindow(@options, {single: false})

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
    autoOpen: true,
    closeOnEscape: false,
    single: true
    close: (event, ui)->
      true
    beforeClose: (event, ui)->
      true

# main default options - default content
  defaults:
    id: 'js-alert_union'
    title: '*** Название окна ***',
    width: 'auto',
    height: 'auto',
    html: 'Текст в окне',
    buttons: []

  constructor: (data = {}, sittings = {}) ->
# get options
    @options = $.extend({}, @defaults, data)
    # get sittings
    @sittings = $.extend({}, @sittings, sittings)
    # console.warn @sittings.single,sittings
    if @options.maxWidth && @options.maxWidth.indexOf('%') + 1
      @options.maxWidth = $(window).width() / 100 * Number(@options.maxWidth.substring(@options.maxWidth.length - 1, 0));
    if @options.maxHeight && @options.maxHeight.indexOf('%') + 1
      @options.maxHeight = $(window).height() / 100 * Number(@options.maxHeight.substring(@options.maxHeight.length - 1, 0));

    # init
    @init()

  destroy: ()->
    @winDiv.dialog('close').dialog('destroy').remove()
  init: ()->
    _this = @
    # html='текст не был передан', title='имя окна не было передано', buttons={}

    # убиваем такое окно, если оно есть
    if(@sittings.single)

      if($('#js-alert_union').length > 0)
        $('#js-alert_union').remove();

      # создаем новое
      $('body').append(@winDiv = $('<div/>', {
        "id": @defaults.id,
        "style": "height:45px;",
        'html': @options.html,
        "class": "js-alert_union",
      }));

    else
      len = $('.js-alert_union').length
      # создаем новое
      @defaults.id = @defaults.id + len
      $('body').append(@winDiv = $('<div/>', {
        "id": @defaults.id,
        "style": "height:45px;",
        'html': @options.html,
        "class": "js-alert_union",
      }));


    self = @winDiv.dialog({
      width: @options.width,
      height: @options.height,
      modal: @sittings.modal,
      title: @options.title,
      autoOpen: @sittings.autoOpen,
      closeOnEscape: @sittings.closeOnEscape,
      beforeClose: (event, ui)->
        _this.sittings.beforeClose(event, ui)
      close: (event, ui)->
        _this.sittings.close(event, ui)

# // buttons: buttons
    }).parent();
    if(@options.buttons.length == 0)
      @options.buttons.push({
        text: 'Закрыть',
        class: 'button_yes_or_no no',
        style: 'float:right;',
        click: ()->
          $('#' + _this.defaults.id).dialog('close').dialog('destroy').remove()
      })
    @winDiv.dialog("option", "buttons",
      buttons:
        text: 'Закрыть',
        class: 'button_yes_or_no no',
        style: 'float:right;',
        click: ()->
          $('#' + _this.defaults.id).dialog('close').dialog('destroy').remove()
    )
    @winDiv.dialog("option", "maxHeight", @options.maxHeight) if @options.maxHeight
    @winDiv.dialog("option", "maxWidth", @options.maxWidth) if @options.maxWidth


    # replace standart buttons
    buttons_html = $('<table/>').append(tr = $('<tr/>'));
    for button_n,i in @options.buttons
      button = $('<button/>', {
        html: button_n['text'],
        click: button_n['click'],
      });


      if button_n['class']
        button.attr('class', button_n['class'])
      if button_n['style']
        button.attr('style', button_n['style'])
      if button_n['id']
        button.attr('id', button_n['id'])

      tr.append(
        td = $('<td/>')
        .append(button)
      );
      #      console.log button_n.data
      if button_n.data != undefined
#        console.log button_n.data
        button.data(button_n.data)
      if(i > 0)
        td.css('textAlign', 'right');


    self.find('.ui-dialog-buttonpane').html(@buttonDiv = $('<div/>', {
#      'id':'js-alert_union_buttons',
      'class': 'js-alert_union_buttons ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'
    }).append(buttons_html))


###
# прототип объекта прихода
###
class paymentRowObj
  defaults:
    id: 0
    invoice_id: 0
    invoice_number: ''
    number: ""
    date: getDateNow()
    price: '0.00'
    percent: '0.00'
    create: getDateNow()
    buch_id: '0.00'
    buch_name: 'Default Name'
    edit: '0.00'
    del: '0.00'
  enterObj: {}
  options: {}

  constructor: (data = {})->
    @options = {}
    if data.edit == undefined
      data.edit = 1
    for key,el of data
      @options[key] = el
    return $.extend({}, @defaults, @options)

###
# прототип объекта счёт
###
class paymentObj
  defaults:
    id: 0
    invoice_query_date: '00.00.0000'
    manager_id: 0
    manager_name: 'Менеджер'
    price_in: 0
    costs: 0
    percent_costs: 0
    price_out: 0
    price_out_payment: 0
    percent_payment: 0

    invoice_create_date: '00.00.0000'
    invoice_num: ''
    client_id: 0
    client_name: 'Имя клиента'
    client_requisit_id: 0
    client_requisit_name: 'Реквизиты'
    price_costs_all: 0
    status: ''
    agreement_id: 0




  enterObj: {}
  options: {}

  constructor: (data = {})->
    if data.edit == undefined
      data.edit = 1
    for key,el of data
      @options[key] = el

    return $.extend({}, @defaults, @options)

###
# прототип html окна реквизитов
###
class requesitContent
  defaults:
    id: 0
  enterObj: {}
  options: {}
  access: 0


  constructor: (data)->
    @options = $.extend({}, @defaults, data)

    return @init()

  init: ()->
    self = @
    console.info self
    console.log @options.company?.length, @options.company
    tbl = $('<table/>', {'css': {'width': '100%'}});

    ##
    tbl.append(tr = $('<tr/>'))
    span = $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    tr.append($('<td/>', {'colspan': 2}).append(div = $('<div/>', {
      'style': 'border-bottom:1px solid #cecece;font-size:18px;',
      'html': span
    })))
    if @options.company?.length
      div.html(@options.company)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Полное наименование'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.comp_full_name?.length
      td.html(@options.comp_full_name)


    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'ИНН'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.inn?.length
      td.html(@options.inn)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'КПП'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.kpp?.length
      td.html(@options.kpp)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td/>', {'colspan': 2}).append($('<div/>', {
      'html': 'Адрес и телефон',
      'style': 'border-bottom:1px solid #cecece;font-size:18px;'
    })))

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Юридический адрес'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.legal_address?.length
      td.html(@options.legal_address)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Фактический адрес'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.postal_address?.length
      td.html(@options.postal_address)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Телефоны'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))

    if @options.phone1?.length
      td.html(@options.phone1)
    if @options.phone1?.length
      td.append(' ' + @options.phone2)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td/>', {'colspan': 2}).append($('<div/>', {
      'html': 'Банковские реквизиты',
      'style': 'border-bottom:1px solid #cecece;font-size:18px;'
    })))

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'БАНК'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.bank?.length || +@options.bank_address?.length
      td.html(@options.postal_address)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Р/С'
    }))
    tr.append($('<td>', {
      'html': @options.r_account?.length?'<span style="color:#D8D3D3">Информация отсутствует</span>':@options.r_account,
    }))

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'КОРР/С'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.cor_account?.length
      td.html(@options.cor_account)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'БИК'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.bik?.length
      td.html(@options.bik)
    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'ОГРН'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.ogrn?.length
      td.html(@options.ogrn)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'ОКПО'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.okpo?.length
      td.html(@options.okpo)

    ##
    tbl.append(tr = $('<tr/>'))
    tr.append($('<td>', {
      'html': 'Доп. инфо'
    }))
    tr.append(td = $('<td>', {
      'html': $('<span/>', {'css': {'color': '#D8D3D3'}, 'html': 'Информация отсутствует'})
    }))
    if @options.dop_info?.length
      td.html(@options.dop_info)


    return tbl



###
# прототип объекта расходы
###
class costsRowObj
  defaults:
    id: 0
    invoice_id: 0
    invoice_number: ''
    number: ''
    date: '00.00.0000'
    price: 0
    percent: 0
    create: getDateNow()
    buch_id: 0
    buch_name: 'Default Name'
    edit: 0
    del: 0
    pay_id: 0
    pay_date: ''
    pay_price: '0.00'
    pay_percent: '0.00'
    pay_buch_id: 0
    pay_buch_name: '',
    flag_ice: 0



  enterObj: {}
  options: {}

  constructor: (data = {})->
    @options = {}

    if data.edit == undefined
      data.edit = 1
    for key,el of data
      if el != null
        @options[key] = el

    return $.extend({}, @defaults, @options)


###
# прототип html строки расходы
###
class costsRow
  enterObj: {}
  options: {}
  access: 0
  constructor: (rData, i, access = 0, windowObj, data_row, rowspan = 1)->
    data = rData[i]
    if data.edit == undefined
      data.edit = 1

    @access = access

    for key,el of data
      @options[key] = el

    @options = data

    @calculateHeader(rData, i, access = 0, windowObj, data_row)


    return @init(data, rData, i, windowObj, data_row, rowspan)

  init: (data, rData, i, windowObj, data_row, rowspan)->
#    if Number(@options.del) == 0 and Number(@options.edit) > 0 and (Number(@access) == 1 or Number(@access) == 2)
# (data.number == "" || Number(data.price) == 0) - проверка на заполненность номера платежки и суммы, если заполнено - выводим нередактируемую версию
    if Number(@options.del) == 0 and ( Number(@access) == 1 or Number(@access) == 2 or Number(@access) == 5 )
      console.log "редактируется", rowspan
      return @createEditingObj(data, rData, i, windowObj, data_row, rowspan)
    else
      console.log "НЕЕЕ редактируется", rowspan
      return @createSimpleRow(data, rData, i, windowObj, data_row, rowspan)


  # подсчёт 1го процента от общей стоимости
  calculateHeader: (rData, i, access = 0, windowObj, data_row)->
    onePercent = Number(data_row.price_out) / 100

  # ячейки оплаты
  createEditingObjPayments: (data, rData, i, windowObj, data_row, rowspan, tr)->
    _this = @
    # дата оплаты
    cl = 'mayBeEdit'
    if _this.access == 5
      cl = ''
    tr.append(td1 = $('<td/>', {
      'html': @options.pay_date
      'class': 'date '+cl,
      click: ()->
        # ограничения для менеджера
        if _this.access == 5
          return false
        if($(this).find('input').length == 0)
          $(this).html(input = $('<input/>', {
            'type': 'text'
            'val': $(this).html(),
            change: ()->
              _this.options.pay_date = $(this).val()
          }))
          $(this).addClass('tdInputHere')
          input.datetimepicker({
            timepicker: false,
            dayOfWeekStart: 1,
            onSelectDate: (ct, $i)->
              $i.blur();

            onGenerate: (ct)->
              $(this).find('.xdsoft_date.xdsoft_weekend')
              .addClass('xdsoft_disabled');
              $(this).find('.xdsoft_date');

            closeOnDateSelect: true,
            format: 'd.m.Y'
          });
          input.focus().blur(()->
            t = $(this)
            _this.options.pay_date = $(this).val()
            new sendAjax 'save_costs_payment_date', {id: _this.options.pay_id, date: _this.options.pay_date}, ()->
              t.parent().removeClass('tdInputHere')
              t.replaceWith(_this.options.pay_date)
          )
    }))
    # сумма оплаты
    cl = 'mayBeEdit'
    if _this.access == 5
      cl = ''
    tr.append(td2 = $('<td/>', {
      'html': @options.pay_price,
      'class': cl,
      click: ()->
        # ограничения для менеджера
        if _this.access == 5
          return false
        if($(this).find('input').length == 0)
          $(this).html(input = $('<input/>', {
            'type': 'text',
            'val': $(this).html(),
            keyup: ()->
              $(this).val($(this).val().replace(/[^-0-9/.]/gim, ''))

              per = round_money(Number($(this).val()) * 100 / Number(_this.options.price))

              # проверка на деление на ноль ( чтобы не выводилось NaN )
              per = round_money(0) if Number(_this.options.price) == 0
              # проверка на деление на ноль ( чтобы не выводилось NaN )
              per = round_money(0) if Number($(this).val()) == 0

              # правим дату на сегодня
              _this.options.pay_date = getDateNow();
              td1.html(_this.options.pay_date).addClass('redTD')

              _this.percentSpan.html(round_money(per))
            focus: ()->
              if(Number($(this).val()) == 0)
                # если 0.00 подм  еняем на пусто
                $(this).val('')
              else
                # выделение
                focusedElement = $(this)
                setTimeout(()->
                  focusedElement.select()
                , 50)
            change: ()->
              _this.options.pay_price = $(this).val()
          }))

          # добавление класса-селектора редактирования
          $(this).addClass('tdInputHere')

          input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
            input = $(this)
            if (Number($(this).val()) == 0)
              _this.options.pay_price = '0.00'
            else
              _this.options.pay_price = round_money($(this).val())

            per = round_money(Number(_this.options.pay_price) * 100 / Number(_this.options.price))

            # проверка на деление на ноль ( чтобы не выводилось NaN )
            per = round_money(0) if Number(_this.options.price) == 0
            # проверка на деление на ноль ( чтобы не выводилось NaN )
            per = round_money(0) if Number(_this.options.pay_price) == 0


            new sendAjax 'save_costs_payment_row', {
              id: _this.options.pay_id,
              price: _this.options.pay_price,
              percent: per
            }, ()->
              thisTd = input.parent()
              # убираем класс-селектор редактирования
              thisTd.removeClass('tdInputHere')
              # подмена input на контент
              thisTd.html(_this.options.pay_price)
              # обновляем значение % в строке таблицы расходов
              _this.percentSpan.html(per)
              _this.options.pay_percent = per
              rData[i].pay_percent = per


              # подсветка несоответствия процента
              # вычисляем первую строку - строку счёта


              tr.attr('id', 'myGroupRowDelete')
              eachTrFirst = $(windowObj.$el).find('#myGroupRowDelete').attr('id', '')

              eachTrFirst.data(_this.options)

              while eachTrFirst.hasClass('subRow')
                eachTrFirst = eachTrFirst.prev()

              # подсёт оплаченного процента
              percent = Number eachTrFirst.data().pay_percent
              eachTr = eachTrFirst.next()

              console.info Number eachTrFirst.data().pay_percent

              while eachTr.hasClass('subRow')
                console.info Number eachTr.data().pay_percent
                percent += Number eachTr.data().pay_percent
                eachTr = eachTr.next()

              
              
              # сохраняем собщий процент оплаты по счёту
              if percent != Number(eachTrFirst.data().percent)
                console.log " != не равно !!!!"
                # правим информацию в шапке окна
                data_row.costs = 0
  
                thisTr = thisTd.parent()
                thisTr.data(_this.options)
                thisTr.parent().find('tr').each(()->
                  da = $(this).data()
                  if Number(da.del) == 0
                    data_row.costs += Number(da.pay_price)
                )
                data_row.costs = round_money(data_row.costs)
                windowObj.updateHead(data_row)
                _this.options.pay_date = getDateNow();

                new sendAjax "save_payment_costs", {
                  invoice_id:data_row.id,
                  pay_date:_this.options.pay_date,
                  pay_id:_this.options.pay_id,
                  costs:data_row.costs,
                  id:_this.options.id,
                  percent: percent}, ()->

                  $('#js-main-invoice-table').invoice('reflesh', data_row)

                
                  console.log " != не равно !!!!"
                  # дата платежа обновлена, убираем выделение
                  td1.html(_this.options.pay_date).stop().animate({backgroundColor:"transparent"}, 200, null,()->
                    $(this).removeClass('redTD').attr('style','')
                  )


                  # сохраняем данные

                  data = eachTrFirst.data()
                  data.percent = percent
                  eachTr = eachTrFirst.next()
                  # подсвечиваем проценты при условии оплаты не 100%
                  if percent != 100
                    eachTrFirst.find('.percent_payment_inf').addClass('warning')
                  else
                    eachTrFirst.find('.percent_payment_inf').removeClass('warning')

                  while eachTr.hasClass('subRow')
                    # сохраняем данные
                    data = eachTr.data()
                    data.percent = percent
                    eachTr.data(data)
                    # подсвечиваем проценты при условии оплаты не 100%
                    if percent != 100
                      eachTr.find('.percent_payment_inf').addClass('warning')
                    else
                      eachTr.find('.percent_payment_inf').removeClass('warning')
                    eachTr = eachTr.next()
          )
    }))
    tr.append(td3 = $('<td/>', {'class': 'percent_payment_inf'})
      .append(@percentSpan = $('<span/>', {'class': 'percentSpan', 'html': @options.pay_percent}))
      .append($('<span/>', {'html': "%"}))
    )

    td3.addClass('warning') if Number(tr.data().percent) != 100
    if rowspan >= 1
      if rowspan > 1
        td1.addClass('noBorderBottm')
        td2.addClass('noBorderBottm')
        td2.addClass('noBorderBottm')
    # пункт 1 есть везде
    button2 = []
    # КНОПКА 1
    button2.push({
      'name': 'добавить оплату',
      'class': '',
      click: (e)->
        eachTr = tr
        while eachTr.hasClass('subRow')
          eachTr = eachTr.prev()
        r = eachTr.find('td[rowspan]')
        if(r)
          r_old = Number(eachTr.find('td[rowspan]').eq(0).attr('rowspan'))
          eachTr.find('td[rowspan]').attr('rowspan', (r_old + 1))

        new sendAjax('new_costs_payment_row', {parent_id: _this.options.id}, (response)->
          newData = $.extend({}, _this.options, response.data)
          # rData,i,access=0,windowObj,data_row, rowspan = 1
          tr.after(new costsRow([newData], 0, _this.access, windowObj, data_row, 0))
        )
    })


    # пункт 2 только для добавленных строк оплаты
    if tr.hasClass('subRow')
      button2.push({
        'name': 'удалить оплату',
        'class': '',
        click: (e)->
          eachTr = tr
          while eachTr.hasClass('subRow')
            eachTr = eachTr.prev()
          r = eachTr.find('td[rowspan]')
          if(r)
            r_old = Number(eachTr.find('td[rowspan]').eq(0).attr('rowspan'))
            eachTr.find('td[rowspan]').attr('rowspan', (r_old - 1))

          new sendAjax('delete_costs_payment', {id: _this.options.pay_id}, ()->
            tr.remove()
          )


      })

    # добавляем на ячейки оплаты меню
    # ограничения для менеджера
    if @access != 5
      td1.menuRightClick({'buttons': button2})
      td2.menuRightClick({'buttons': button2})
      td3.menuRightClick({'buttons': button2})
# добавляем обработчик для на элементы поиска по счетам
  supplierSearch: (td)->
    _this = @

    td.click(()->
      if($(this).find('input').length == 0)
        $(this).html(inputSearch = $('<input/>', {
          'type': 'text',
          'val': $(this).html(),
          keyup: ()->
            console.log Number $(this).attr('data-id')
          change: ()->
#            _this.options.number = $(this).val()
          blur: ()->
            t = $(this)
            name = $(this).val()
            id = Number $(this).attr('data-id')
            console.log id

            if(_this.options.supplier_id != id && id > 0)
              _this.options.supplier_name = name
              _this.options.supplier_id = id
              new sendAjax 'save_supplier_name', {
                id: _this.options.id,
                supplier_name: _this.options.supplier_name,
                supplier_id: id
              }, ()->
                t.parent().removeClass('tdInputHere').attr('data-id', id)
                t.replaceWith(_this.options.supplier_name)
            else if ( !id ) && name != '' && name != _this.options.supplier_name

              new modalConfirm({html: 'Такого имени не было найдено в системе, Вы хотите добавить нового поставщика'}, ()->
                obj = $('<div/>', {'id': 'window--new_supplier'})
                obj.append(nickName = $('<input/>', {
                  'type': 'text',
                  'name': 'nick_name',
                  'val': name,
                  'placeholder': 'Сокращённое название'
                }))
                obj.append(fullName = $('<input/>', {
                  'type': 'text',
                  'name': 'full_name',
                  'placeholder': 'Полное название'
                }))
                obj.append(dop_info = $('<textarea/>', {
                  'name': 'dop_info',
                  'placeholder': 'Дополнительная информацияе'
                }))

                buttons = [{
                  text: 'Отмена',
                  class: 'button_yes_or_no no',
                  style: 'float:right;'
                  click: ()->
                    t.parent().removeClass('tdInputHere').attr('data-id', _this.options.supplier_id)
                    t.replaceWith(_this.options.supplier_name)
                    $(_this.supplier_window.winDiv).dialog('close').dialog('destroy').remove()
                }, {
                  text: 'Создать',
                  class: 'button_yes_or_no yes',
                  style: 'float:right;',
                  click: ()->
                    if nickName.val() != ''
                      new sendAjax('create_new_supplier', {
                        nick_name: nickName.val(),
                        full_name: fullName.val(),
                        dop_info: dop_info.val()
                      }, (response)->
                        new sendAjax 'save_supplier_name', {
                          id: _this.options.id,
                          supplier_name: nickName.val(),
                          supplier_id: id
                        }, ()->
                          t.parent().removeClass('tdInputHere').attr('data-id', response.supplier_id)
                          _this.options.supplier_name = nickName.val()
                          t.replaceWith(_this.options.supplier_name)
                          echo_message_js "создание"
                          $(_this.supplier_window.winDiv).dialog('close').dialog('destroy').remove()
                      )
                }]


                _this.supplier_window = new modalWindow({
                  html: obj,
                  title: 'Создать поставщика',
                  buttons: buttons
                }, {single: false})
              , ()->
                t.parent().removeClass('tdInputHere')
                t.replaceWith(_this.options.supplier_name)
              )
            else
              t.parent().removeClass('tdInputHere')
              t.replaceWith(_this.options.supplier_name)
        }))
        $(this).addClass('tdInputHere')

        inputSearch.css('textAlign', $(this).css('textAlign')).focus()

        inputSearch.autocomplete({
          minLength: 2,
          source: (request, response)->
            $.ajax({
              type: "POST",
              dataType: "json",
              data:
                AJAX: 'shearch_supplier_autocomlete', # показать
                search: request.term # поисковая фраза
              success: (data) ->
                response(data);
            })
          select: (event, ui) ->
            inputSearch.attr('data-id', ui.item.desc).val(ui.item.label).blur()
            return false;

        })
        inputSearch.data("ui-autocomplete")._renderItem = (ul, item)->
          ul.css('z-index', Number($(_this.$el).parent().parent().css("z-index")) + 1)
          return $("<li></li>", {
            click: (e)->
              inputSearch.attr('data-id', 0)
          })
          .data("ui-autocomplete-item", item)# для jquery-ui 1.10+
#.appe nd( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
          .append(item.label)
          .appendTo(ul);
        # console.log ul

        inputSearch.keydown((e)->
          if(e.keyCode == 13)#enter
            if(inputSearch.is(':focus'))#
              inputSearch.attr('data-id', 0)
              inputSearch.blur()
              return false;
#отправка поиска на enter
        )
    )


  # строка с возможностью редактирования
  createEditingObj: (data, rData, i, windowObj, data_row, rowspan)->
    _this = @
    tr = $('<tr/>', {'id': 'c_' + data.id}).data(data)
    if rowspan == 0
      tr.addClass('subRow')
    if rowspan == 1
      tr.addClass('singleRow')

    if rowspan > 1
      tr.addClass('firstGroupRow')

    if rowspan >= 1
      # поставщик
      tr.append(td = $('<td/>', {'rowspan': rowspan,'class':'mayBeEdit', 'html': @options.supplier_name, 'data-id': @options.supplier_id}))
      @supplierSearch(td)

      editClass = ''
      if rowspan == 1 && @access != 5
        editClass = 'mayBeEdit'
      # номер счёта
      tr.append($('<td/>', {
        'rowspan': rowspan,
        'html': @options.number,
        'class': editClass,
        click: ()->
          if _this.access == 5
            return false
          if($(this).find('input').length == 0 && Number($(this).attr('rowspan')) == 1)
            $(this).html(input = $('<input/>', {
              'type': 'text',
              'val': $(this).html(),
              change: ()->
                _this.options.number = $(this).val()
            }))
            $(this).addClass('tdInputHere')
            input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
              t = $(this)
              _this.options.number = $(this).val()
              new sendAjax 'save_costs_row', {id: _this.options.id, number: _this.options.number}, ()->
                t.parent().removeClass('tdInputHere')
                t.replaceWith(_this.options.number)
            )
      }))


      if rowspan == 1
        editClass = 'mayBeEdit'
      if @access == 5
        # дата счёта
        tr.append($('<td/>', {
            'rowspan': rowspan,
            'html': @options.date,
            'class': 'date '
        }))
      else
        # дата счёта
        tr.append($('<td/>', {
          'rowspan': rowspan,
          'html': @options.date,
          'class': 'date '+editClass,
          click: ()->
            if @access == 5
              return false
            if($(this).find('input').length == 0 && Number($(this).attr('rowspan')) == 1)
              $(this).html(input = $('<input/>', {
                'type': 'text',
                'val': $(this).html(),
                change: ()->
                  _this.options.date = $(this).val()
              }))
              $(this).addClass('tdInputHere')
              input.datetimepicker({

                timepicker: false,
                dayOfWeekStart: 1,
                onSelectDate: (ct, $i)->
                  $i.blur();

                onGenerate: (ct)->
                  $(this).find('.xdsoft_date.xdsoft_weekend')
                  .addClass('xdsoft_disabled');
                  $(this).find('.xdsoft_date');

                closeOnDateSelect: true,
                format: 'd.m.Y'
              });
              input.focus().blur(()->
                t = $(this)
                _this.options.date = $(this).val()
                new sendAjax 'save_costs_row', {id: _this.options.id, date: _this.options.date}, ()->
                  t.parent().removeClass('tdInputHere')
                  t.replaceWith(_this.options.date)
              )
        }))
      # сумма счёта (денежный формат)
      tr.append(td2 = $('<td/>', {
        'rowspan': rowspan,
        'html': @options.price,
        'class': editClass,
        click: ()->
          # если существует несколько строк оплаты для одного счёта - редактироание запрещено !!!
          if($(this).find('input').length == 0 && Number($(this).attr('rowspan')) == 1)
            $(this).html(input = $('<input/>', {
              'type': 'text',
              'val': $(this).html(),
              keyup: ()->
                $(this).val($(this).val().replace(/[^-0-9/.]/gim, ''))

                per = round_money(Number(_this.options.pay_price) * 100 / Number($(this).val()))

                # проверка на деление на ноль ( чтобы не выводилось NaN )
                per = round_money(0) if Number(_this.options.pay_price) == 0
                # проверка на деление на ноль ( чтобы не выводилось NaN )
                per = round_money(0) if Number($(this).val()) == 0

                _this.percentSpan.html(per)

              focus: ()->
                if(Number($(this).val()) == 0)
# если 0.00 подменяем на пусто
                  $(this).val('')
                else
# выделение
                  focusedElement = $(this)
                  setTimeout(()->
                    focusedElement.select()
                  , 50)
              change: ()->
                _this.options.price = $(this).val()
            }))

            $(this).addClass('tdInputHere')
            input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
              input = $(this)
              if (Number($(this).val()) == 0)
                _this.options.price = '0.00'
              else
                _this.options.price = round_money($(this).val())



              data_row.costs_supplier_bill = 0
              thisTd = input.parent()
              thisTr = thisTd.parent()
              thisTr.data(_this.options)
              thisTr.parent().find('tr.singleRow,tr.firstGroupRow').each(()->
                da = $(this).data()
                if Number(da.del) == 0
                  data_row.costs_supplier_bill += Number(da.price)
              )

              per = round_money(Number(_this.options.pay_price) * 100 / Number(_this.options.price))
              # проверка на деление на ноль ( чтобы не выводилось NaN )
              per = round_money(0) if Number(_this.options.pay_price) == 0
              # проверка на деление на ноль ( чтобы не выводилось NaN )
              per = round_money(0) if Number(_this.options.price) == 0


              # запрос на сохранение данных
              new sendAjax('save_costs_row', {id: _this.options.id, price: _this.options.price}, ()->
                input.parent().removeClass('tdInputHere')
                input.replaceWith(_this.options.price)

              )
              new sendAjax('save_costs_payment_percent', {id: _this.options.pay_id, percent: per,invoice_id:data_row.id,costs_supplier_bill:data_row.costs_supplier_bill}, ()->
                _this.percentSpan.html(per)
                windowObj.updateHead(data_row)
                data_row.costs_supplier_bill = round_money(data_row.costs_supplier_bill)
                $('#js-main-invoice-table').invoice('reflesh', data_row)
              )
            )
      }))

    # ячейки оплаты по счету
    @createEditingObjPayments(data, rData, i, windowObj, data_row, rowspan, tr)


    if rowspan >= 1
      # глаз
      td = $('<td/>', {
        'rowspan': rowspan,
        'class': 'ice',

        click: ()->
          # ограничения для менеджера
          if @access == 5
            return false

          if $(this).hasClass('checked')
            _this.options.flag_ice = 0;
            $(this).removeClass('checked')
          else
            _this.options.flag_ice = 1;
            $(this).addClass('checked')
          # проверка всех глаз на зелёный
          tbl = $(this).parent().parent()
          console.log tbl.find('tr td.ice').length, tbl.find('tr td.ice.checked').length
          if (tbl.find('tr td.ice').length == tbl.find('tr td.ice.checked').length and _this.options.flag_ice == 1)
            flag_ice = 1
          else
            flag_ice = 0

          if Number(windowObj.options.flag_ice) != flag_ice
            windowObj.options.flag_ice = flag_ice
            new sendAjax("edit_flag_ice", {id: windowObj.options.id, val: windowObj.options.flag_ice}, ()->
              $('#js-main-invoice-table').invoice('reflesh', windowObj.options)
            )


          # сохраняем значение флага
          new sendAjax 'edit_glag_ice_costs_pay', {id: _this.options.id, val: _this.options.flag_ice}


      })
      console.warn @options.flag_ice
      td.addClass('checked') if Number(_this.options.flag_ice) > 0
      tr.append(td)
      # автор
      tr.append($('<td/>', {'rowspan': rowspan})
        .append($('<div/>', {'html': @options.buch_name}))
        .append($('<div/>', {'html': @options.create}))
      )
      tr.append(delTd = $('<td/>', {'rowspan': rowspan}))
      @costsDel(delTd, rData, i, data, windowObj, data_row)

    #    console.log tr.find('td:nth-of-type(1)').attr('rowspan')


    tr.data(@options)
    tr.addClass('deleted') if Number(@options.del) > 0
    return tr
  createSimpleRow: (data, rData, i, windowObj, data_row, rowspan)->
    tr = $('<tr/>').data(data)
    if rowspan == 0
      tr.addClass('subRow')
    if rowspan == 1
      tr.addClass('singleRow')
    if rowspan > 1
      tr.addClass('firstGroupRow')
    if rowspan >= 1
      tr.append(td = $('<td/>', {'rowspan': rowspan, 'html': @options.supplier_name, 'data-id': @options.supplier_id}))
      tr.append($('<td/>', {'html': @options.number, 'rowspan': rowspan}))
      tr.append($('<td/>', {'html': @options.date, 'rowspan': rowspan}))
      tr.append($('<td/>', {'html': @options.price, 'rowspan': rowspan}))

    # дата оплаты
    tr.append($('<td/>', {'html': @options.pay_date, 'data-t': '654'}))
    # сумма оплаты
    tr.append($('<td/>', {'html': @options.pay_price}))
    # процент
    tr.append($('<td/>')
      .append($('<span/>', {'html': @options.pay_percent}))
      .append($('<span/>', {'html': "%"}))
    )

    if rowspan >= 1
# глаз
      tr.append($('<td/>', {'rowspan': rowspan, 'class': 'ice'}))
      # автор, дата создания строки
      tr.append($('<td/>', {'rowspan': rowspan})
        .append($('<div/>', {'html': @options.buch_name}))
        .append($('<div/>', {'html': @options.create}))
      )
      # удаление
      tr.append(td_del = $('<td/>', {'rowspan': rowspan}))
      if Number(@access) == 1 && Number(rData[i].del) > 0
        @realCostsDel(td_del, rData, i, data, windowObj)
      else if Number(rData[i].del) == 0
        @costsDel(td_del, rData, i, data, windowObj, data_row)

    tr.data(@options)


    tr.addClass('deleted') if Number(@options.del) > 0
    return tr
# навешивает onClick событие помечает строку как удалённую
  costsDel: (tdObj, rData, i, data, windowObj, data_row)->
    _this = @
    tdObj.addClass('ppDel').click((e)->
      td = $(this)


      new sendAjax 'save_costs_row', {id: _this.options.id, del: 1}, ()->

# правим данные в кнопке
        button_changed = $('#js--how_del_payment_button')
        button_changed.data().num = Number(button_changed.data().num) + 1
        button_changed.text('Показать удалённые(' + button_changed.data().num + ')')
        button_changed.addClass('lightGreen') if button_changed.data().num > 0

        # присваеваем строке класс удаленной строки
        rowspan = Number(td.attr('rowspan'))
        td.parent().attr('id', 'myGroupRowDelete')
        row = $(windowObj.$el).find('#myGroupRowDelete').attr('id', '').addClass('deleted').data(_this.options)

        # помечаем удаленные строки
        n = i
        while rowspan > 0
          rData[n].del = 1
          row.addClass('deleted').data(rData[n])
          row = row.next()
          rowspan = rowspan - 1
          n++
        # поменяем td с евентами на новый чистый td
        td.replaceWith(td = $('<td/>', {'rowspan': Number(td.attr('rowspan'))}))
        # если админ вешаем удаление на onClick
        if _this.access == 1
          _this.realCostsDel(td, rData, i, data, windowObj)


        # если не зажата кнопка показать удалённые - подчищаем dom
        if button_changed.hasClass('no')
          pause = 0
          pause = 2000 if @access == 1
          rowspan = Number(td.attr('rowspan'))
          td.parent().attr('id', 'myGroupRowDelete')
          row = $(windowObj.$el).find('#myGroupRowDelete')
          while rowspan > 0
# console.log row
            row.addClass('Delete')
            row.delay(pause).fadeOut(700, ()->
              $(this).delay(2000).remove()
            )
            row = row.next()
            rowspan = rowspan - 1
        # пересчёт итого по расходам
        windowObj.calculateCosts()
    )
# навешивает onClick событие удаляет из базы
  realCostsDel: (tdObj, rData, i, data, windowObj)->
    tdObj.addClass('ppDel').click((e)->
      td = $(this)


      confirmObj = new modalConfirm({html: 'Данная запись будет удалена безвозвратно.<br>Продолжить?'}, ()->
        new sendAjax 'delete_costs', {id: data.id}, ()->
          rowspan = Number(td.attr('rowspan'))
          td.parent().attr('id', 'myGroupRowDelete')
          row = $(windowObj.$el).find('#myGroupRowDelete')
          while rowspan > 0
# console.log row
            row.addClass('Delete')
            row.delay(200).fadeOut(700, ()->
              $(this).delay(2000).remove()
            )
            row = row.next()
            rowspan = rowspan - 1

          rData[i] = undefined
          delete rData[i]
          rData.splice(i, 1);
          #              confirmObj.buttonsTab.
          button_changed = $('#js--how_del_payment_button')
          button_changed.data().num = Number(button_changed.data().num) - 1
          button_changed.text('Показать удалённые(' + button_changed.data().num + ')')
          if button_changed.data().num == 0
            button_changed.addClass('no lightGreen')
      )
    )
###
# прототип окна расходов
#  costsWindow
###
class costsWindow
  saveObj: {}
  defaults:
    id: 0,
    number: '0000',
    type: "new"
  countDelRow: 0
  accces: 0
  head:
    price: {}
    r_percent: {}
    conditions: {}

  #  price:0      # price from invoice
  #  conditions:0 # percent payment for start the work
  #  r_percent:0  # real
  constructor: (data_row, responseData, access) ->
    # запоминаем уровень допуска
    @access = access
    # сохраняем информацию по строке
    @options = data_row
    # сборка окна счёта
    @init(data_row, responseData)

  # собираем окно счёт
  init: (data_row, responseData)->
    _this = @
    # запрос данных
    if(responseData != undefined)
      $('#quick_button_div .button').eq(1).addClass('checked')
      ###
      # создание контейнера
      ###
      @main_div = $('<div/>')
      ###
      # добавление шапки окна
      ###
      @main_div.prepend(@head = @createHead(data_row))
      ###
      # добавляем таблицу
      ###
      @main_div.append(@bodyRows = @createTable(responseData, 0, data_row))
      ###
      # создание окна
      ###
      @myObj = new modalWindow({
        html: @main_div,
        maxHeight: '100%',
        maxWidth: '90%',
        title: 'Расходы по счёту',
        buttons: @getButtons(data_row, responseData),
      }, {
        closeOnEscape: true,
        single: true,
        close: (event, ui) ->
          $('#quick_button_div .button').eq(1).removeClass('checked')
      })
      @$el = @myObj.options.html[0]
      $(@$el).parent().css('padding', '0')

  updateHead:(data_row)->
    @head = @createHead(data_row)
    @main_div.find('#head_info').replaceWith(@head)

  # подсчет расходов и правка в DOM
  calculateCosts: ()->
    # расходы
    table = $(@$el).find('#js--payment-window--body_info-table')
    c = 0
    table.find('tr').each((index)->
      if index > 0
        c += Number $(this).data().pay_price
    )
    if @options.costs != round_money(c)
      @options.costs = round_money(c)
      @head.costs.html(@options.costs)
      @calculateCostsSave()

      # прибыль ожидаемая
      @head.profit.html(round_money(Number(@options.price_out) - Number(@options.price_in)))

      # прибыль  фактическая
      profit_f = Number(@options.price_out) - Number(@options.costs)
      @head.profit_f.html(round_money(profit_f))

      # процент прибыли
      percent = round_money((@options.price_out_payment - Number(@options.costs)) / @options.price_out * 100)
      @head.r_percent.html(percent)
      if profit_f < 0
        @head.profit_f.parent().addClass('warning')
        @head.r_percent.parent().parent().parent().addClass('warning')
      else
        @head.profit_f.parent().removeClass('warning')
        @head.r_percent.parent().parent().parent().removeClass('warning')

  # пересчёт и сохранение расходов
  calculateCostsSave: ()->
    self = @
    new sendAjax('save_costs_from_invoice', {id: @options.id, costs: @options.costs}, ()->
      $('#js-main-invoice-table').invoice('reflesh', self.options)
    )

  updatePaymenContent: (button, responseData, data_row)->
    if button.hasClass('showed')
      button.removeClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 0, data_row))
    else
      button.addClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 1, data_row))
  # сборка таблицы
  createTable: (responseData, showDell = 0, data_row)->
    tbl = $('<table>', {'id': 'js--payment-window--body_info-table', 'class': 'costs'})
    .append(tr = $('<tr/>'))

    tr.append($('<th/>', {'html': 'поставщик'}))
    .append($('<th/>', {'html': '№ счёта'}))
    .append($('<th/>', {'html': 'дата счёта'}))
    .append($('<th/>', {'html': 'сумма счёта'}))
    .append($('<th/>', {'html': 'дата оплаты'}))
    .append($('<th/>', {'html': 'сумма оплаты'}))
    .append($('<th/>', {'html': '%'}))
    .append($('<th/>'))
    .append($('<th/>', {'html': 'счёт внесён'}))
    .append($('<th/>'))
    # перебор строк ПП
    id = 0
    for payment,i in responseData
      rowspan = 0
      if Number(payment.del) > 0 && showDell == 0
        @countDelRow = @countDelRow + 1
      else
        # подсчитываем следующие повторения по id
        if (id != responseData[i].id)
          id = responseData[i].id
          rowspan = 1
          k = i + 1

          while responseData[k] != undefined and responseData[k].id == id
            rowspan++
            k++
        responseData[i] = new costsRowObj(responseData[i])

        tbl.append(new costsRow(responseData, i, @access, @, data_row, rowspan))

    return tbl

  # добавляем обработчик для на элементы поиска по счетам
  addHandlerForInputSearch: (inputSearch, buttonSearch)->
    ###
    # inputSearch
    ###
    _this = @
    inputSearch.autocomplete({
      minLength: 2,
      source: (request, response)->
        # console.log(request)
        $.ajax({
          type: "POST",
          dataType: "json",
          data:
            AJAX: 'shearch_invoice_autocomlete', # показать
            search: request.term # поисковая фраза
          success: (data) ->
            response(data);
        })
      select: (event, ui) ->
        inputSearch.attr('data-id', ui.item.desc)
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
    inputSearch.data("ui-autocomplete")._renderItem = (ul, item)->
      ul.css('z-index', Number($(_this.$el).parent().parent().css("z-index")) + 1)
      return $("<li></li>", {
        click: (e)->
          inputSearch.attr('data-id', 0)
      })
      .data("ui-autocomplete-item", item)# для jquery-ui 1.10+
      #.append( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
      .append(item.label)
      .appendTo(ul);
      # console.log ul

    inputSearch.keydown((e)->
      if(e.keyCode == 13)#enter
        if(inputSearch.is(':focus'))#
          buttonSearch.click()
          inputSearch.attr('data-id', 0)
          return false;
          # отправка поиска на enter
    )


    ###
    # buttonSearch
    ###
    buttonSearch.click((e)->
      send = {
        invoice_num: inputSearch.val()
        id: 0
      }
      if inputSearch.attr('data-id') && Number(inputSearch.attr('data-id')) > 0 && inputSearch.attr('data-id') != undefined
        send.id = inputSearch.attr('data-id')


      new sendAjax 'getInvoceRow', send, (responseRow)->
        console.log "запрос на выгрузку '" + inputSearch.val(), responseRow
        if responseRow.data.length == 1
          console.log " найдено полное соответствие по счёту ", responseRow.data
          new sendAjax('get_costs', {'id': responseRow.data[0].id}, (response)->
            delete _this.$el
            $(_this.$el).remove()
            new costsWindow(responseRow.data[0], response.data, _this.access)
          )
    )
  # шапка таблицы
  createHead: (data_row)->
    _this = @
    # общий контейнер
    head_info = $('<div>', {id: 'head_info'});
    table = $('<table>', {id: 'js--payment-window--head_info-table'});

    ###
    # строка 1
    ###
    tr = $('<tr/>')
    .append($('<td/>', 'colspan': '2').append($('<span/>', {'html': 'номер счёта', 'class': 'span-greyText'})))
    .append($('<td/>', 'colspan': '2'))
    .append($('<td/>', {'colspan': '4'}))
    .append($('<td/>', {'colspan': '4'}).append($('<span/>', {'class': 'span-greyText', 'html': 'Прибыль сделки'})))

    table.append(tr)

    ###
    # строка 2
    ###
    tr = $('<tr/>')
    # поиск
    tr.append($('<td/>').append(inputSearch = $('<input/>', {
      'type': 'text',
      'id': 'js--payment-window--search-pp-input',
      'val': data_row.invoice_num
    })))
    tr.append($('<td/>').append(buttonSearch = $('<button/>', {'id': 'js--payment-window--search-pp-button'})))

    # добавляем обработчик для на элементы поиска по счетам
    @addHandlerForInputSearch(inputSearch, buttonSearch)


    # общая информация 1
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'Счёт', 'class': 'span-boldText'}))
    .append($('<span/>', {'html': ' № ', 'class': 'span-greyText span-boldText'}))
    .append($('<span/>', {'html': data_row.invoice_num, 'class': 'span-boldText'}))
    .append($('<span/>', {'html': 'от', 'class': 'span-greyText'}).css('paddingLeft', '10px'))
    div2 = $('<div/>')
    .append($('<span/>', {'html': data_row.manager_name, 'data-id': data_row.manager_id}))
    tr.append($('<td/>', {'class': 'wating-and-facting-left head-main-info-left'}).append(div1).append(div2))
    # общая информация 2
    div1 = $('<div/>')
    .append($('<span/>', {'html': data_row.invoice_create_date}).css('paddingLeft', '10px'))
    .append($('<span/>', {'html': ' на сумму ', 'class': 'span-greyText', css: {'paddingLeft': '10px'}}))
    .append(@head.price = $('<span/>', {'html': data_row.price_out}).css('paddingLeft', '10px'))
    div2 = $('<div/>')
    .append($('<span/>', {'html': data_row.client_name, 'data-id': data_row.client_id}))
    tr.append($('<td/>', {'class': 'head-main-info-right'}).append(div1).append(div2))

    # оплачен / условия
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'оплачен:', 'class': 'span-greyText'}))
    div2 = $('<div/>')
    .append($('<span/>', {'html': 'условия:', 'class': 'span-greyText'}))
    tr.append($('<td/>', {'class': 'wating-and-facting-left'}).append(div1).append(div2))
    # оплачен / условия
    div1 = $('<div/>')
    .append(@head.r_percent = $('<span/>', {'html': data_row.percent_payment}))
    .append('%')

    div2 = $('<div/>')
    .append(@head.conditions = $('<span/>', {'html': data_row.conditions+'% - '+(100-Number(data_row.conditions))+'%'}))

    tr.append($('<td/>', {'class': 'wating-and-facting-right'}).append(div1).append(div2))
    if Number(data_row.percent_payment) != 100
      div1.addClass('warning')
    if Number(data_row.conditions) > Number(data_row.percent_payment)
      div2.addClass('warning')


    # приходы / расходы
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'приходы:', 'class': 'span-greyText'}))
    div2 = $('<div/>')
    .append($('<span/>', {'html': 'расходы:', 'class': 'span-greyText'}))
    tr.append($('<td/>', {'class': 'wating-and-facting-left'}).append(div1).append(div2))
    # приходы / расходы
    div1 = $('<div/>')
    .append(@head.r_percent1 = $('<span/>', {'html': data_row.price_out_payment}))
    .append('р')
    div2 = $('<div/>')
    .append(@head.costs = $('<span/>', {'html': data_row.costs}))
    .append('р')
    tr.append($('<td/>', {'class': 'wating-and-facting-right'}).append(div1).append(div2))

    # прибыль
    # ожидаемая
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'ожидаемая:', 'class': 'span-greyText'}))
    # фактическая
    div2 = $('<div/>')
    .append($('<span/>', {'html': 'фактическая:', 'class': 'span-greyText'}))
    tr.append($('<td/>', {'class': 'wating-and-facting-left'}).append(div1).append(div2))

    # прибыль
    # ожидаемая
    console.log "прибыль сделки:  price_out = %d, costs_supplier_bill = %d",Number(data_row.price_out) , Number(data_row.costs_supplier_bill)

    pp = round_money( Number(data_row.price_out) - Number(data_row.costs_supplier_bill) )
    div1 = $('<div/>')
    .append(@head.profit = $('<span/>', {'html': pp}))
    .append('р')
    # фактическая
    fact = round_money(Number(data_row.price_out_payment) - Number(data_row.costs))
    div2 = $('<div/>')
    .append(@head.profit_f = $('<span/>', {'html': fact}))
    .append('р')

    tr.append($('<td/>', {'class': 'wating-and-facting-right bold'}).append(div1).append(div2))


    # калькулятор / % оплаты
    r_percent = round_money((Number(data_row.price_out_payment) - Number(data_row.costs)) / Number(data_row.price_out_payment) * 100)
    r_percent = '0.00' if Number(data_row.price_out_payment) == 0 || Number(data_row.costs) == 0
    
    console.log Number(data_row.price_out_payment) , Number(data_row.costs), r_percent
    console.log "# калькулятор / % оплаты:  price_out_payment = %d, costs = %d, r_percent = %s", Number(data_row.price_out_payment) , Number(data_row.costs), r_percent


    div1 = $('<div/>')
    .append($('<div/>', {'class': 'invoice-row--icons-calculator'}))
    .append($('<div/>').append(@head.r_percent = $('<span/>', {'html': r_percent})).append('%'))


    tr.append(td = $('<td/>', {
      'class': 'bold our_p'
    }).append(div1))

    if @access == 1 || @access == 2
      td.addClass('mayBeEdit').click(()->
        t = $(@)
        new modalConfirm "Вы уверены",()->
          if t.hasClass('checked')
            _this.options.flag_calc = 0;
            t.removeClass('checked')
          else
            _this.options.flag_calc = 1;
            t.addClass('checked')
  
          # сохраняем значение флага
          new sendAjax('edit_flag_calc', {id: _this.options.id, val: _this.options.flag_calc}, ()->
            $('#js-main-invoice-table').invoice('reflesh', _this.options)
          )
      )

    td.addClass('checked') if Number _this.options.flag_calc > 0

    if(Number(fact) < 0)
      @head.profit_f.parent().addClass('warning')
      @head.r_percent.parent().parent().parent().addClass('warning')

    # span_ttn_number = $('<span/>')


    #    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)


  createRow: (data_row, responseData)->
    _this = @
    new sendAjax('create_costs', {'id': data_row.id}, (response)->
      # добавляем информацию в главный объект
      len = responseData.length
      console.log response.data, new costsRowObj(response.data)
      responseData[len] = new costsRowObj(response.data)
      # добавляем строку в таблицу в окне
      $(_this.$el).find('#js--payment-window--body_info-table').append(new costsRow(responseData, len, _this.access, _this, data_row))
    )
  # подсчёт итого
  recalcInvoice: ()->
    # общий % оплаты
    percent_payment = 0
    # общая сумма оплаты
    price_out_payment = 0

    @bodyRows.find('tr').each((index)->
      if !$(this).hasClass('deleted') and index > 0
        data = $(this).data()
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

  getButtons: (data_row, responseData)->
    _this = @
    @saveObj = {}
    buttons = []
    #    if Number(data_row.invoice_num) <= 0 ||  data_row.invoice_create_date == '00.00.0000'
    if @access == 2
      buttons.push(
        text: 'Добавить счёт поставщика',
        class: 'button_yes_or_no yes add_payment_button',
        click: ()->
          _this.createRow(data_row, responseData)
          _this.calculateCosts()
      )
    if _this.countDelRow > 0
      className = 'button_yes_or_no no show_del_payment_button lightGreen'
    else
      className = 'button_yes_or_no no show_del_payment_button'
    buttons.push(
      text: 'Показать удалённые(' + _this.countDelRow + ')',
      class: className,
      id: 'js--how_del_payment_button',
      data: {
        num: _this.countDelRow
      },
      click: ()->
        if Number($(this).data('num')) > 0
          if($(this).hasClass('no'))
            $(this).removeClass('no').html('Скрыть удалённые (' + $(this).data('num') + ')')
          else
            $(this).addClass('no').html('Показать удалённые (' + $(this).data('num') + ')')
            if Number($(this).data('num')) > 0
              $(this).addClass('lightGreen')

        _this.updatePaymenContent($(this), responseData, data_row)
    )

    buttons.push(
      text: 'Закрыть',
      class: 'button_yes_or_no no',
      click: ()->
        _this.destroy()
    )

    return buttons

  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()



# $('#js-alert_union').after();
###
# прототип опубликованного комментариев
###
class commentsRow
  enterObj: {}
  options: {}
  access: 0


  constructor: (data, access)->
    console.log access
    @access = access
    @options = data
    console.log @access


    return @init()

  init: ()->
    main = $('<div/>', {'class': 'comment table'})

    #column 1
    console.log @options
    main.append(tr = $('<div/>', {'class': 'row'}))
    cell1 = $('<div/>', {'class': 'cell user_name_comments'})
    cell1.append($('<div/>', {
      'class': 'user_name',
      'html': @options.user_name
    })).append($('<div/>', {
      'class': 'create_time_message',
      'html': @options.create_time
    }))
    tr.append(cell1)

    cell2 = $('<div/>', {'class': 'cell comment_text'})
    cell2.append($('<div/>', {
      'html': @options.comment_text
    }))
    tr.append(cell2)
    return main


###
# прототип окна комментариев
#  costsWindow
###
class commentsWindow
  defaults:
    id: 0
  MessageMinLen:1

  constructor: (data_row, responseData, access) ->
# запоминаем уровень допуска
    @access = access
    # сохраняем информацию по строке
    @options = data_row
    @data = responseData

    @init(data_row,responseData)

# собираем окно счёт
  init: (data_row,responseData)->
# запрос данных
    ###
    # создание контейнера
    ###
    @main_div = $('<div/>', {
      'id': 'dialog_gen_window_form',
      'class':'add_new_comment',
      css:{
        'padding':'15px'
      }
    })
    ###
    # добавление шапки окна
    ###
    @main_div.append(@getContent(responseData))

    @main_div.append(@main_form = @getForm(data_row))


    ###
    # создание окна
    ###


    @myObj = new modalWindow({
      html: @main_div,
      maxHeight: '100%',
      width: '800px',
      title: 'Переписка по счёту № ' + @options.invoice_num + ' от ' + @options.invoice_create_date,
      buttons: @getButtons(data_row),
    }, {
      closeOnEscape: true,
      single: true,
      close: (event, ui) ->
        $('#js-main-invoice-table').invoice('reflesh', data_row)
    })
    @$el = @myObj.options.html[0]
    $(@$el).parent().css('padding', '0')

  getForm: (data_row)->
    self = @
    main = $('<div/>', {'class': 'comment table'})

    #column 1
    main.append(tr = $('<div/>', {'class': 'row'}))
    cell1 = $('<div/>', {'class': 'cell user_name_comments'})
    cell1.append($('<div/>', {
      'class': 'user_name',
      'html': 'Вы...'
    })).append($('<div/>', {
      'class': 'create_time_message',
      'html': getDateNow()
    }))
    tr.append(cell1)

    cell2 = $('<div/>', {'class': 'cell comment_text'})
    cell2.append(textarea = $('<textarea/>', {
      'name': 'comment_text',
      keyup:()->
        if $(this).val().length > self.MessageMinLen
          console.log $(this).val().length
          $(self.myObj.buttonDiv).find("#js--send_comment").removeClass('no')
        else
          console.log $(this).val().length
          $(self.myObj.buttonDiv).find("#js--send_comment").addClass('no')
    }))

    tr.append(cell2)


    $('<div/>',{'class':'add_new_comment'}).append(main)
  getContent: (responseData)->
    main = $('<div/>',{'class':'contaner_sm'})
    self = @
    for row,i in responseData
      main.append(new commentsRow(row, @access))
    main 



  getButtons: (data_row, responseData)->
    self = @
    @saveObj = {}
    buttons = []

    buttons.push(
      text: 'Закрыть',
      class: 'button_yes_or_no no',
      click: ()->
        self.destroy()
    )

    buttons.push(
      text: 'Отправить',
      class: 'button_yes_or_no no',
      id: 'js--send_comment'
      click: ()->
        comment = self.main_form.find('textarea').val()
        if comment.length <= self.MessageMinLen
          echo_message_js "Сообщение должно быть не короче "+self.MessageMinLen+" символов"
        else
          data_row.comments_num = Number(data_row.comments_num) + 1
          $('#js-main-invoice-table').invoice('reflesh', data_row)
          new sendAjax('save_invoice_comment',{invoice_id:data_row.id, comment:comment,comments_num:data_row.comments_num},(response)->
            response.data.comment_text = comment
            self.main_form.before(new commentsRow(response.data, @access))
          )
          # правим визуал кнопки отправить и очищаем поле с текстом
          self.main_form.find('textarea').val('')
          $(self.myObj.buttonDiv).find("#js--send_comment").addClass('no')

    )

    return buttons

  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()


###
# прототип html строки прихода
###
class paymentRow
  defaults:
    id: 0
    invoice_id: 0
    invoice_number: 0
    number: 0
    date: getDateNow()
    price: 0
    percent: 0
    create: getDateNow()
    buch_id: 0
    buch_name: 'Default Name'
    edit: 0
    del: 0
  enterObj: {}
  options: {}
  access: 0


  constructor: (rData, i, access = 0, paymentWindowObj, data_row)->
    data = rData[i]
    if data.edit == undefined
      data.edit = 1

    @access = access

    for key,el of data
      @options[key] = el

    @options = data

    @calculatePaymentWindowHeader(rData, i, access = 0, paymentWindowObj, data_row)


    return @init(data, rData, i, paymentWindowObj, data_row)

  init: (data, rData, i, paymentWindowObj, data_row)->
#    if Number(@options.del) == 0 and Number(@options.edit) > 0 and (Number(@access) == 1 or Number(@access) == 2)
# (data.number == "" || Number(data.price) == 0) - проверка на заполненность номера платежки и суммы, если заполнено - выводим нередактируемую версию
    if Number(@options.del) == 0 and Number(@options.edit) > 0 and (Number(@access) == 1 or Number(@access) == 2)
      return @createEditingObj(data, rData, i, paymentWindowObj, data_row)
    else
      return @createSimpleRow(data, rData, i, paymentWindowObj, data_row)


# подсчёт 1го процента от общей стоимости
  calculatePaymentWindowHeader: (rData, i, access = 0, paymentWindowObj, data_row)->
    onePercent = Number(data_row.price_out) / 100

# строка с возможностью редактирования
  createEditingObj: (data, rData, i, paymentWindowObj, data_row)->
    _this = @
    tr = $('<tr/>').data(data)
    # номер платежки
    .append($('<td/>', {
      'html': @options.number,
      'class':'mayBeEdit',
      click: ()->
        if($(this).find('input').length == 0)
          $(this).html(input = $('<input/>', {
            'type': 'text',
            'val': $(this).html(),
            change: ()->
              _this.options.number = $(this).val()
          }))
          $(this).addClass('tdInputHere')
          input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
            t = $(this)
            _this.options.number = $(this).val()
            paymentWindowObj.flag_edit++
            new sendAjax 'save_payment_row', {id: _this.options.id, number: _this.options.number}, ()->
              t.parent().removeClass('tdInputHere')
              t.replaceWith(_this.options.number)
          )
    }))
# дата
    .append($('<td/>', {
      'html': @options.date,
      'class': 'date',
      'class':'mayBeEdit',
      click: ()->
        if($(this).find('input').length == 0)
          if (_this.options.number == '0' || _this.options.number == '')
            echo_message_js('Сначала заполните № платёжки')
            return false
          $(this).html(input = $('<input/>', {
            'type': 'text',
            'val': $(this).html(),
            change: ()->
              _this.options.date = $(this).val()
          }))
          $(this).addClass('tdInputHere')
          input.datetimepicker({
            minDate: new Date(),
            timepicker: false,
            dayOfWeekStart: 1,
            onSelectDate: (ct, $i)->
              $i.blur();

            onGenerate: (ct)->
              $(this).find('.xdsoft_date.xdsoft_weekend')
              .addClass('xdsoft_disabled');
              $(this).find('.xdsoft_date');

            closeOnDateSelect: true,
            format: 'd.m.Y'
          });
          input.focus().blur(()->
            t = $(this)
            _this.options.date = $(this).val()
            paymentWindowObj.flag_edit++
            new sendAjax 'save_payment_row', {id: _this.options.id, date: _this.options.date}, ()->
              t.parent().removeClass('tdInputHere')
              t.replaceWith(_this.options.date)
          )
    }))
    # денежный формат
    tr.append($('<td/>', {
      'html': @options.price,
      'class':'mayBeEdit',
      click: ()->
        if($(this).find('input').length == 0)
          if (_this.options.number == '0' || _this.options.number == '')
            echo_message_js('Сначала заполните № платёжки')
            return false
#          html =
          $(this).html(input = $('<input/>', {
            'type': 'text',
            'val': $(this).html(),
            keyup: ()->
              $(this).val($(this).val().replace(/[^-0-9/.]/gim, ''))

              per = round_money(Number($(this).val()) * 100 / Number(data_row.price_out))
              _this.percentSpan.html(per)
            focus: ()->
              if(Number($(this).val()) == 0)
                  # если 0.00 подменяем на пусто
                $(this).val('')
              else
                # выделение
                focusedElement = $(this)
                setTimeout(()->
                  focusedElement.select()
                , 50)
            change: ()->
              _this.options.price = $(this).val()
          }))
          $(this).addClass('tdInputHere')
          input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
            input = $(this)
            if (Number($(this).val()) == 0)
              _this.options.price = '0.00'
            else
              _this.options.price = round_money($(this).val())

            per = round_money(Number(_this.options.price) * 100 / Number(data_row.price_out))
            paymentWindowObj.flag_edit++

            new sendAjax 'save_payment_row', {id: _this.options.id, price: _this.options.price, percent: per}, ()->
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
      .append(@percentSpan = $('<span/>', {'html': @options.percent}))
      .append($('<span/>', {'html': "%"}))
    )
    .append($('<td/>')
      .append($('<div/>', {'html': @options.buch_name}))
      .append($('<div/>', {'html': @options.create}))
    )
    .append(delTd = $('<td/>'))
    .data(@options)
    @paymentDel(delTd, rData, i, data, paymentWindowObj, data_row)
    tr.addClass('deleted') if Number(@options.del) > 0
    return tr
  createSimpleRow: (data, rData, i, paymentWindowObj, data_row)->
    tr = $('<tr/>').data(data)
    .append($('<td/>', {'html': @options.number}))
    .append($('<td/>', {'html': @options.date}))
    .append($('<td/>', {'html': @options.price}))
    .append($('<td/>')
      .append($('<span/>', {'html': @options.percent}))
      .append($('<span/>', {'html': "%"}))
    )
    .append($('<td/>')
      .append($('<div/>', {'html': @options.buch_name}))
      .append($('<div/>', {'html': @options.create}))
    )
    .append(td_del = $('<td/>'))
    .data(@options)

    if Number(@access) == 1 && Number(rData[i].del) > 0
      @realPaymentDel(td_del, rData, i, data)
    else if Number(rData[i].del) == 0
      @paymentDel(td_del, rData, i, data, paymentWindowObj, data_row)
    tr.addClass('deleted') if Number(@options.del) > 0
    return tr
# навешивает onClick событие помечает строку как удалённую
  paymentDel: (tdObj, rData, i, data, paymentWindowObj, data_row)->
    _this = @
    tdObj.addClass('ppDel').click((e)->
      td = $(this)
      row = td.parent()
      paymentWindowObj.flag_edit++
      new sendAjax 'save_payment_row', {id: _this.options.id, del: 1}, ()->
        rData[i].del = 1

        # правим данные в кнопке
        button_changed = $('#js--how_del_payment_button')
        button_changed.data().num = Number(button_changed.data().num) + 1
        button_changed.text('Показать удалённые(' + button_changed.data().num + ')')
        button_changed.addClass('lightGreen') if button_changed.data().num > 0

        # присваеваем строке класс удаленной строки
        row.addClass('deleted').data(_this.options)
        # поменяем td с евентами на новый чистый td
        td.replaceWith(td = $('<td/>'))
        # если админ вешаем удаление на onClick
        if _this.access == 1
          _this.realPaymentDel(td, rData, i, data)

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
  realPaymentDel: (tdObj, rData, i, data)->
    tdObj.addClass('ppDel').click((e)->
      td = $(this)
      row = td.parent()
      confirmObj = new modalConfirm({html: 'Данная запись будет удалена безвозвратно.<br>Продолжить?'}, ()->
        new sendAjax 'delete_payment', {id: data.id}, ()->
          row.delay(200).fadeOut(700, ()->
            $(this).remove()
          )

          rData[i] = undefined
          delete rData[i]
          rData.splice(i, 1);
          #              confirmObj.buttonsTab.
          button_changed = $('#js--how_del_payment_button')
          button_changed.data().num = Number(button_changed.data().num) - 1
          button_changed.text('Показать удалённые(' + button_changed.data().num + ')')
          if button_changed.data().num == 0
            button_changed.addClass('no lightGreen')
      )
    )


###
# прототип окна приходов
###
class paymentWindow
  saveObj: {}
  defaults:
    id: 0,
    number: '0000',
    type: "new"
  countDelRow: 0
  accces: 0
  head:
    price: {}
    r_percent: {}
    conditions: {}

#  price:0      # price from invoice
#  conditions:0 # percent payment for start the work
#  r_percent:0  # real

  constructor: (data_row, responseData, access) ->
    console.warn 'старт окна'
    # запоминаем уровень допуска
    @access = access
    # сохраняем информацию по строке
    console.log access
    @options = data_row
    # флаг редк
    @flag_edit = 0;
    # сборка окна счёта
    @init(data_row, responseData)

# собираем окно счёт
  init: (data_row, responseData)->
    _this = @
    $('#quick_button_div .button').eq(0).addClass('checked')
    # запрос данных
    if(responseData != undefined)
      ###
      # создание контейнера
      ###
      main_div = $('<div/>')
      ###
      # добавление шапки окна
      ###
      main_div.prepend(@createHead(data_row, responseData))
      ###
      # добавляем таблицу
      ###
      main_div.append(@bodyRows = @createTable(responseData, 0, data_row))

      ###
      # создание окна
      ###
      @myObj = new modalWindow({
        html: main_div,
        width: '1000px',
        maxHeight: '100%',
        title: 'Приходы по счёту',
        buttons: @getButtons(data_row, responseData)
      }, {
        closeOnEscape: true,
        close: (event, ui) ->
          if _this.flag_edit > 0
            new sendAjax('payment_window_is_editable',data_row)
          $('#quick_button_div .button').eq(0).removeClass('checked')
      })
      @$el = @myObj.options.html[0]


      $(@$el).parent().css('padding', '0')

  updatePaymenContent: (button, responseData, data_row)->
    if button.hasClass('showed')
      button.removeClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 0, data_row))
    else
      button.addClass('showed')
      $(@$el).find('#js--payment-window--body_info-table').replaceWith(@createTable(responseData, 1, data_row))
# сборка таблицы
  createTable: (responseData, showDell = 0, data_row)->
    tbl = $('<table>', {'id': 'js--payment-window--body_info-table'})
    .append(tr = $('<tr/>'))

    tr.append($('<th/>', {'html': '№ платёжки'}))
    .append($('<th/>', {'html': 'дата ПП'}))
    .append($('<th/>', {'html': 'платёж на сумму'}))
    .append($('<th/>', {'html': '% оплаты'}))
    .append($('<th/>', {'html': 'платёж внесён', 'colspan': '2'}))
    # перебор строк ПП
    for payment,i in responseData
      if Number(payment.del) > 0 && showDell == 0
        @countDelRow = @countDelRow + 1
      else
        responseData[i] = new paymentRowObj(responseData[i])
        tbl.append(new paymentRow(responseData, i, @access, @, data_row))

    return tbl

# добавляем обработчик для на элементы поиска по счетам
  addHandlerForInputSearch: (inputSearch, buttonSearch, data_row, responseData)->
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
      select: (event, ui) ->
        inputSearch.attr('data-id', ui.item.desc)
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
    inputSearch.data("ui-autocomplete")._renderItem = (ul, item)->
      ul.css('z-index', Number($(_this.$el).parent().parent().css("z-index")) + 1)
      return $("<li></li>", {
        click: (e)->
          inputSearch.attr('data-id', 0)
      })
      .data("ui-autocomplete-item", item)# для jquery-ui 1.10+
#.append( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
      .append(item.label)
      .appendTo(ul);
    # console.log ul

    inputSearch.keydown((e)->
      if(e.keyCode == 13)#enter
        if(inputSearch.is(':focus'))#
          buttonSearch.click()
          inputSearch.attr('data-id', 0)
          return false;
#отправка поиска на enter
    )


    ###
    # buttonSearch
    ###
    buttonSearch.click((e)->
      send = {
        invoice_num: inputSearch.val()
        id: 0
      }
      if inputSearch.attr('data-id') && Number(inputSearch.attr('data-id')) > 0 && inputSearch.attr('data-id') != undefined
        send.id = inputSearch.attr('data-id')


      new sendAjax('getInvoceRow', send, (responseRow)->
        console.log "запрос на выгрузку '" + inputSearch.val(), responseRow
        if responseRow.data.length == 1
          console.log " найдено полное соответствие по счёту ", responseRow.data
          new sendAjax('get_payment', {'id': responseRow.data[0].id}, (response)->
            new paymentWindow(responseRow.data[0], response.data, _this.access)
          )
      )
    )
# шапка таблицы
  createHead: (data_row, responseData)->
    _this = @
    # общий контейнер
    head_info = $('<div>', {id: 'head_info'});
    table = $('<table>', {id: 'js--payment-window--head_info-table'});

    ###
    # строка 1
    ###
    tr = $('<tr/>').append($('<td/>', {'colspan': '2'}).append($('<span/>', {
      'html': 'номер счёта',
      'class': 'span-greyText'
    }))).append($('<td/>')).append($('<td/>'))
    table.append(tr)

    ###
    # строка 2
    ###
    tr = $('<tr/>')
    # поиск
    tr.append($('<td/>').append(inputSearch = $('<input/>', {
      'type': 'text',
      'id': 'js--payment-window--search-pp-input',
      'val': data_row.invoice_num
    })))
    tr.append($('<td/>').append(buttonSearch = $('<button/>', {'id': 'js--payment-window--search-pp-button'})))

    # добавляем обработчик для на элементы поиска по счетам
    @addHandlerForInputSearch(inputSearch, buttonSearch, data_row, responseData)


    # table.append(tr)
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'Счёт', 'class': 'span-boldText'}))
    .append($('<span/>', {'html': ' № ', 'class': 'span-greyText span-boldText'}))
    .append($('<span/>', {'html': data_row.invoice_num, 'class': 'span-boldText'}))
    .append($('<span/>', {'html': ' от ', 'class': 'span-greyText'}).css('paddingLeft', '10px'))
    .append($('<span/>', {'html': data_row.invoice_create_date}).css('paddingLeft', '10px'))
    .append($('<span/>', {'html': ' на сумму ', 'class': 'span-greyText'}).css('paddingLeft', '10px'))
    .append(@head.price = $('<span/>', {'html': data_row.price_out}).css('paddingLeft', '10px'))

    div2 = $('<div/>')
    .append($('<span/>', {'html': data_row.manager_name, 'data-id': data_row.manager_id}))
    .append($('<span/>', {
      'html': ' ' + data_row.client_name,
      'data-id': data_row.client_id
    }).css('paddingLeft', '28px'))

    tr.append($('<td/>').append(div1).append(div2))
    div1 = $('<div/>')
    .append($('<span/>', {'html': 'оплачен:', 'class': 'span-greyText'}))
    .append(@head.r_percent = $('<span/>', {'html': data_row.percent_payment}))
    .append('%')
    div2 = $('<div/>')
    .append($('<span/>', {'html': 'условия:', 'class': 'span-greyText'}))
    .append(@head.conditions = $('<span/>', {'html': data_row.conditions+'% - '+(100-Number(data_row.conditions))+'%'}))
    tr.append($('<td/>').append(div1).append(div2))

    if Number(data_row.conditions) > Number(data_row.percent_payment)
      div2.addClass('warning')
    if Number(data_row.percent_payment) < 100
      div1.addClass('warning')

    # span_ttn_number = $('<span/>')


    #    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)

  createRow: (data_row, responseData)->
    _this = @
    new sendAjax('create_payment', {'id': data_row.id}, (response)->
      len = responseData.length
      console.warn response.data
      responseData[len] = new paymentRowObj(response.data)

      console.warn responseData[len], len
      # добавляем строку в таблицу в окне
      $(_this.$el).find('#js--payment-window--body_info-table').append(new paymentRow(responseData, len, _this.access, _this, data_row))
    )
# update и save
  updateHeaderPercent: (data_row)->
    _this = @
    oldPer = Number(@head.r_percent.html())
    recalcInvoice = @recalcInvoice();

    console.info oldPer, recalcInvoice.percent_payment
    #    if recalcInvoice.percent_payment != oldPer and oldPer > 0
    send = {}
    send.percent_payment = round_money(recalcInvoice.percent_payment)
    send.price_out_payment = round_money(recalcInvoice.price_out_payment)


    data_row.percent_payment = send.percent_payment
    _this.options.percent_payment = send.percent_payment
    data_row.price_out_payment = send.price_out_payment
    _this.options.price_out_payment = send.price_out_payment
    console.log data_row


    if send != undefined
      send.id = @options.id
      @head.r_percent.html(round_money(recalcInvoice.percent_payment))
      new sendAjax 'save_percent_from_invoice', send, ()->
        $('#js-main-invoice-table').invoice('reflesh', data_row)

# подсчёт итого
  recalcInvoice: ()->
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

  getButtons: (data_row, responseData)->
    _this = @
    @saveObj = {}
    buttons = []
    #    if Number(data_row.invoice_num) <= 0 ||  data_row.invoice_create_date == '00.00.0000'
    if @access == 2
      buttons.push(
        text: 'Добавить платеж',
        class: 'button_yes_or_no yes add_payment_button',
        click: ()->
          console.warn data_row
          _this.createRow(data_row, responseData)
      )
    if _this.countDelRow > 0
      className = 'button_yes_or_no no show_del_payment_button lightGreen'
    else
      className = 'button_yes_or_no no show_del_payment_button'
    buttons.push(
      text: 'Показать удалённые(' + _this.countDelRow + ')',
      class: className,
      id: 'js--how_del_payment_button',
      data: {
        num: _this.countDelRow
      },
      click: ()->
        if Number($(this).data('num')) > 0
          if($(this).hasClass('no'))
            $(this).removeClass('no').html('Скрыть удалённые (' + $(this).data('num') + ')')
          else
            $(this).addClass('no').html('Показать удалённые (' + $(this).data('num') + ')')
            $(this).addClass('lightGreen') if Number $(this).data('num') > 0


        _this.updatePaymenContent($(this), responseData, data_row)
    )
    buttons.push(
      text: 'Закрыть',
      class: 'button_yes_or_no yes',
      click: ()->
        _this.destroy()
    )

    return buttons

  editSaveObj: (key, value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return

# убиваем окно
  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()

###
# прототип объекта ТТН
###
class ttnObj
  defaults:
    buch_id: 0
    buch_name: 0
    comments: ''
    date: "00.00.0000"
    date_return: null
    delivery: ""
    id: 0
    invoice_id: 0
    number: 0
    position_id: 0
    positions_num: 0
    return: 0
  options: {}

  constructor: (data = {})->
    for key,el of data
      @options[key] = el
    return $.extend({}, @defaults, @options)

###
# прототип окна ТТН
###
class ttnWindow
  # checkbox number
  checkNumber: 0
  saveObj: {}
  defaults:
    id: 0,
    number: '0000',
    type: "new"

  constructor: (obj, data_row, data, accces, ttn) ->
    if ttn != null
      @defaults = $.extend({}, @defaults, ttn)
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
  init: (obj, data_row, responseData, accces, ttn)->
    _this = @
    # запрос данных
    if(responseData != undefined)
      ###
      # создание контейнера
      ###
      main_div = $('<div/>')
      ###
      # добавляем таблицу
      ###
      main_div.append(@contentTbl = @createTable(responseData, ttn,data_row))
      ###
      # добавление шапки окна
      ###
      main_div.prepend(@createHead(ttn))

      ###
      # вычисляем title окна
      ###
      @title = 'УПД'
      if ttn != undefined && ttn.number != undefined && (Number(ttn.number) > 0 || ttn.number!= '0')
        @title += ' № '+ttn.number
      else if ttn != undefined && @access == 5
        @title += ' запрошен'
      else
        @title = 'Запрос '+@title

      ###
      # выбор способа доставки
      ###
      # только для менеджеров и при условии что, есть что выбирать
      if @access == 5 && @checkNumber > 0 && ttn == undefined
        main_div.append(@createDeliveryChoose())

      ###
      # ранее созданные упд
      ###
      main_div.append(@alreadyWasСreated())

      ###
      # создание окна
      ###
      @myObj = new modalWindow({
        html: main_div,
        width: '1000px',
        maxHeight: '100%',
        height:($(window).height() - 60),
        title: @title,
        buttons: @getButtons(obj, data_row, ttn)
      }, {
        closeOnEscape: true
      })
      @$el = @myObj.options.html[0]

  editSaveObj: (key, value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return
# ранее созданные ттн
  alreadyWasСreated: ()->
    console.log "предыдущие ттн ---Ю"
    content = $('<div/>', {
      'class': "ttn--already-was-created"
    });

    if(@options.ttn && @options.ttn.length > 0)
      content.append($('<div/>', {
        'class': 'ttn--already-was-created--head',
        'html': 'Ранее оформленные ТТН:'
      }));
      content.append(tbl = $('<table/>', {
        'id': 'ttn--already-was-created--old-ttn-tbl'
      }))

      for oldTtn in @options.ttn
        tbl.prepend(@createTtnDiv(oldTtn))

# создаёт строки ранее созданных ттн
  createTtnDiv: (oldTtn)->
    self = @
    tr = $('<tr/>')

    if oldTtn.positions_num != null
      if oldTtn.positions_num.split(',').length > 1
        end = 'и '
      else
        end = 'я '
      positions = ' позици' + end
      positions = positions + oldTtn.positions_num
    else
      positions = ''

    number = oldTtn.number
    if oldTtn.number == null || oldTtn.number == undefined
      number = '<b>не выставлен</b>'
    tr.append($('<td/>', {
      'html': '№' + number
    }))

    tr.append($('<td/>', {
      'html': ' от ' + oldTtn.date
    }))
    tr.append($('<td/>', {
      'html': positions
    }))
    if oldTtn.delivery == 'our_delivery'
      delivery = 'Доставка'
    else
      delivery = 'Самовывоз'
    tr.append($('<td/>', {
      'html': delivery,
      'class': oldTtn.delivery
    }))


    tr.append(date_shipment_td = $('<td/>', {
      'html': oldTtn.date_shipment
    }))
    if @access == 1 || @access == 5
      tr.addClass('editDate')
      tr.click(()->
        content = $('<div/>').append($('<div/>', {
          'html': 'Укажите приблизительную дату отгрузки/доставки',

        })).append($('<div/>', {
          'css': {
            'padding': '5px',
            'margin': '10px 0 0 0'
          }
        }).append(date_shipment = $('<input/>', {
          'val': getDateNow(),
          'css': {
            'padding': '5px'
          }
        })))
        date_shipment.datetimepicker({
          minDate: new Date(),
          timepicker: false,
          dayOfWeekStart: 1,
          onSelectDate: (ct, $i)->
            $i.blur();

          onGenerate: (ct)->
            $(this).find('.xdsoft_date.xdsoft_weekend')
            .addClass('xdsoft_disabled');
            $(this).find('.xdsoft_date');

          closeOnDateSelect: true,
          format: 'd.m.Y'
        }).blur();

        wDate = new modalWindow({
          html: content,
          title: 'Укажите дату',
          buttons: [{
            text: 'Отмена',
            class: 'button_yes_or_no no',
            click: ()->
              $(wDate.winDiv).dialog('close').dialog('destroy').remove()
          }, {
            text: 'OK',
            class: 'button_yes_or_no yes',
            click: ()->
              $(wDate.winDiv).dialog('close').dialog('destroy').remove()
              # отправляем запрос
              new sendAjax 'edit_date_shipment_ttn', {id: oldTtn.id, date_shipment: date_shipment.val()}, (response)->
# при положительном ответе
                oldTtn.date_shipment = date_shipment.val()
                tr.replaceWith(self.createTtnDiv(oldTtn))
# перезагрузка строки счёта
# $('#js-main-invoice-table').invoice('reflesh',oldTtn.invoice_id)
          }]
        }, {single: false})
      )

    tr





  ###
  # выбор способа доставки
  ###
  createDeliveryChoose: ()->
    car_div = $('<div/>', {id: 'ttn_car_div'})
    car_div.append($('<div/>', {'html': 'Доставка выбранных позиций', 'class': 'ttn_car_div-head'}))

    li_clic = (event)->
      $(this).parent().find('li').removeClass('checked')
      $(this).addClass('checked')
    ul = $('<ul/>')
    .append($('<li/>', {click: li_clic})
    .append($('<div/>', {'class': 'ttn-our_delivery'}))
    .append($('<div/>', {'class': 'ttn-delivery-text', 'html': 'Доставка'})))
    .append($('<li/>', {click: li_clic})
    .append($('<div/>', {'class': 'ttn-no_delivery'}))
    .append($('<div/>', {'class': 'ttn-delivery-text', 'html': 'Самовывоз'})))
    div_car_body = $('<div/>', {'class': 'ttn_car_div-body'}).append(ul)
    car_div.append(div_car_body)



  spanDate: (val = getDateNow())->
    self = @
    $('<span/>', {
      'class': 'dateInput',
      'html': val,
      click: ()->
        val = $(this).html()
        input = self.inputDate(val)
        $(this).replaceWith(input)
        self.newTtnDate = val
        setTimeout(input.focus(), 500)
    })
  spanInput: (val = "&nbsp;")->
    self = @
    $('<span/>', {
      'class': 'spanInput',
      'html': val,
      click: ()->
        val = $(this).html()
        input = self.inputSpan(val)
        
        $(this).replaceWith(input)
        setTimeout(input.focus(), 500)
    })
  inputSpan: (val)->
    self = @
    $('<input/>', {
      val: val,
      blur: ()->
        val = $(this).val()
        $(this).replaceWith(self.spanInput(val))
    })
  inputDate: (val)->
    self = @
    $('<input/>', {
      val: val,
      blur: ()->
        val = $(this).val()
        self.newTtnDate = val
        $(this).replaceWith(self.spanDate(val))
    }).datetimepicker({

      timepicker: false,
      dayOfWeekStart: 1,
      onSelectDate: (ct, $i)->
        $i.blur();

      onGenerate: (ct)->
        $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
        $(this).find('.xdsoft_date');

      closeOnDateSelect: true,
      format: 'd.m.Y'
    });
  # сборка шапки АДМИН - БУХ
  createHeadAdmin: (ttn)->
    self = @
    ###
    # контейнер шапки окна ТТН
    ###
    head_info = $('<div>', {id: 'ttn_head_info'});
    ###
    # сборка таблицы с общей информации по ТТН
    ###
    table = $('<table>', {id: 'ttn_head_info-table'});


    ###
    # строка с информацией по клиенту
    ###
    tr = $('<tr/>')
    tr.append($('<td/>', {'html': @options.client_name, 'class': 'ttn_client_name'}))
    tr.append($('<td/>', {
      'html': @options.client_requisit_name,
      'class': 'ttn_requisits',
      click: ()->
        new sendAjax('show_requesit', {
          'id': self.options.client_requisit_id
        }, (response)->
          new modalWindow({
            html: new requesitContent(response.data),
            maxHeight: '100%',
            width: '650px',
            title: 'Реквизиты',
          }, {
            closeOnEscape: true,
            single: false
          })
        )
    }))
    table.append(tr)

    ###
    # если номер к данной ТТН не назначен - выводим строку
    # с формой назначения номера ТТН и даты от которой эта ТТН выставлена
    ###

    # span_ttn_number = $('<span/>')

    span_invoice = $('<span/>', {'html': "№ счёта " + @options.invoice_num + " от " + @options.invoice_create_date})
    td = $('<td/>', {'colspan': '2'})

    if Number(self.defaults.number) == 0
      input = $('<input/>', {
        'val': self.defaults.number,
        'data-val': self.defaults.number,
        'class': 'ttn_number_input',
        focus: ()->
          $(this).val('') if Number($(this).val()) == 0
          return
        blur: ()->
          $(this).val($(this).attr('data-val')) if Number($(this).val()) == 0
          return
        keyup: ()->
          if Number($(this).val()) >0 || ($(this).val()!='0' && $(this).val()!='' )
            $(self.myObj.buttonDiv).find('#create_ttn_button').removeClass('no')
          else
            $(self.myObj.buttonDiv).find('#create_ttn_button').addClass('no')

          self.editSaveObj('number', $(this).val(), self.defaults.number)
      })

      input_date = $('<input/>', {
        'val': self.defaults.date,
        'class': '',
        blur: ()->
          self.editSaveObj('date', $(this).val(), self.defaults.date)

      }).datetimepicker({
        minDate: new Date(),
        timepicker: false,
        dayOfWeekStart: 1,
        onSelectDate: (ct, $i)->
          $i.blur();

        onGenerate: (ct)->
          $(this).find('.xdsoft_date.xdsoft_weekend')
          .addClass('xdsoft_disabled');
          $(this).find('.xdsoft_date');

        closeOnDateSelect: true,
        format: 'd.m.Y'
      });

      # номер ттн
      td.append('№ ТТН ').append($('<span/>').append(input))
      # дата ттн
      td.append($('<span/>').append(input_date))

    # счёт
    td.append($('<span/>', {'html': "№ УПД " + ttn.number + " от "}).append(ttn.date)).append(span_invoice)
    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)

  # сборка шапки МЕНЕДЖЕР и остальные
  createHeadManager: (ttn)->
    self = @
    # ttn number & date
    if ttn == undefined
      span_ttn = $('<span/>', {'html': "№ УПД " + @defaults.number + " от "}).append(@spanDate())
    else
      console.warn ttn
      span_ttn = $('<span/>', {'html': "№ УПД " + ttn.number + " от "}).append(ttn.date)
    # invoice number & date
    span_invoice = $('<span/>', {'html': "№ счёта " + @options.invoice_num + " от " + @options.invoice_create_date})

    table = $('<table>', {id: 'ttn_head_info-table'})

    # client info row
    table.append(tr = $('<tr/>'))
    tr.append($('<td/>', {'html': @options.client_name, 'class': 'ttn_client_name'}))
    tr.append($('<td/>', {
      'html': @options.client_requisit_name,
      'class': 'ttn_requisits',
      click: ()->
        new sendAjax('show_requesit', {
          'id': self.options.client_requisit_id
        }, (response)->
          new modalWindow({
            html: new requesitContent(response.data),
            maxHeight: '100%',
            width: '650px',
            title: 'Реквизиты',
          }, {
            closeOnEscape: true,
            single: false
          })
        )
    }))
    # если есть что выбирать
    #    if @checkNumber > 0
    table.append(tr = $('<tr/>'))
    tr.append($('<td/>', {'colspan': '2'})
    # добавляем информацию по УДП
    .append(span_ttn)
    # добавляем информацию по счёту
    .append(span_invoice))


    # create and return conteiner
    $('<div>', {id: 'ttn_head_info'}).append(table)


# проверка прав и сборка шапки окна
  createHead: (ttn)->
# console.log  @access
# проверка уровня доступа и вызов соответствующей шапки
    switch @access
      when 1 then @createHeadAdmin(ttn)
      when 2 then @createHeadAdmin(ttn)
      else
        @createHeadManager(ttn)


# первый td в таблице позиций для менеджера
  getFirstTdCheck: (position, ttn)->
# чекбоксы
    self = @
    console.log ttn
    if Number(position.ttn_id) == 0 and Number(position.not_shipped) == 0 && ttn == undefined
      @checkNumber++
      check = $('<input/>', {
        'type': 'checkbox',
        change: (event)->
          event.preventDefault()
          event.stopPropagation()
          if $(this).prop('checked')
            $(this).prop('checked', false)
            $(this).parent().removeClass('checked')
          else
            $(this).prop('checked', true)
            $(this).parent().addClass('checked')

          self.checkMainCheckbox()
      })

      td = $('<td/>', {
        click: ()->
          input = $(this).find('input')
          if input.prop('checked')
            input.prop('checked', false)
            td.removeClass('checked')
          else
            input.prop('checked', true)
            td.addClass('checked')
          self.checkMainCheckbox()
      })
      td.append(check)
    else
      td = $('<td/>')
    return td
  tblAdminRow:(position, ttn )->
    self = @
    tr = $('<tr/>').data(position).attr('data-id', position.id)
    tr.append(td = $('<td/>'))
    if @access != 7
      if Number(self.defaults.id) == Number position.ttn_id
        tr.addClass('buh_style')

      else

#        if Number(position.ttn_id) > 0
#          tr.addClass('ttn_created')
    else
      arr = self.defaults.positions_num.split(',')
      che = false

      for n in arr

        if Number(n) == position.num
          che = true

      if che
        tr.addClass('checked buh_style thisTtn')
#      if Number(position.ttn_id) > 0
#        tr.addClass('ttn_created')




    # Number
    td = $('<td/>').append(position.num)
    tr.append(td)
    # Name
    td = $('<td/>').append(position.name)
    

    tr.append(td)
    # Quantity
    td = $('<td/>').append(position.quantity)
    tr.append(td)
    # Price for one
    pr_out = calc_price_with_discount(position.price, position.discount)
    td = $('<td/>').append(round_money(pr_out) + ' р.')
    tr.append(td)
    # Price for all
    @main_price += pr_out * position.quantity
    @nds += Number(round_money(pr_out * position.quantity / 118 * 18))
    td = $('<td/>').append(round_money(pr_out * position.quantity) + ' р.')
    tr.append(td)


  tblManRow: (position, ttn)->
    self = @
    tr = $('<tr/>').data(position).attr('data-id', position.id)

    # подкрашиваем строки для которых уже созданы ттн
    if Number(position.not_shipped) > 0
      tr.addClass('not_shipped')
    else if Number(position.ttn_id) > 0
      tr.addClass('ttn_created')
    console.info ttn
    tr.append(td = @getFirstTdCheck(position, ttn))

    # Number
    tr.append($('<td/>', {'html': position.num}))
    # Name
    tr.append(td_name = $('<td/>').append(position.name))

    # Quantity
    tr.append($('<td/>').append(position.quantity))
    # Price for one
    pr_out = calc_price_with_discount(position.price, position.discount)

    tr.append($('<td/>').append(round_money(pr_out) + ' р.'))
    # Price for all
    if position.quantity == 0
      position.quantity = 1
    @main_price += pr_out * position.quantity
    @nds += Number(round_money(pr_out * position.quantity / 118 * 18))


    tr.append($('<td/>').append(round_money(pr_out * position.quantity) + ' р.'))

  # сборка таблицы при клике на кнопку Запросить
  createTableManager: (responseData, ttn)->
    _this = @
    table = $('<table/>', {'id': 'js-invoice--window--ttn-table','class':'managerStyle'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))

    # чекбоксы
    main_checkbox = $('<input/>', {
      'type': 'checkbox',
      change: (event)->
        input = $(this)
        td = $(this).parent()
        # клик по главному чекбоксу
        _this.clickMainCheckbox(table, td, input)
    })

    td_main_check = $('<th/>', {
      click: ()->
        input = $(this).find('input')
        td = $(this)
        # клик по главному чекбоксу
        _this.clickMainCheckbox(table, td, input)
    })

    tr.append(td_main_check)


    # Number
    td = $('<th/>', {'text': '№'})
    tr.append(td)
    # Name
    td = $('<th/>', {'html': 'Наименование и <br>описание продукции'})
    tr.append(td)
    # Quantity
    td = $('<th/>', {'html': 'Количество<br>продукции'})
    tr.append(td)
    # Price for one
    td = $('<th/>', {'html': 'стоимость<br>за штуку'})
    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': 'Общая<br>стоимость'})
    tr.append(td)

    # тогововая цена
    @main_price = 0
    # НДС
    @nds = 0
    # порядковый номер строки товра/услуги
    @posNum = 1
    # перебираем позиции
    console.log 'до создания таблицы строк  -> ', ttn
    @checkNumber = 0
    for position,i in responseData
      responseData[i].num = @posNum++
      # добавляем строку для каждой позиции
      table.append(@tblManRow(responseData[i], ttn))


    if @checkNumber > 0
      td_main_check.append(main_checkbox)
    else
      td_main_check.width('20px')
    # ИТОГО
    table.append(tr = $('<tr/>'))
    td = $('<th/>')
    tr.append(td)
    # текст
    td = $('<th/>', {
      'colspan': '4',
      'html': 'Итоговая сумма по данной спецификации (договору)'
    })

    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': round_money(@main_price) + ' р.'})

    tr.append(td)
    table.append(tr)
    # в том числе НДС
    table.append(tr = $('<tr/>'))
    td = $('<th/>')

    tr.append(td)

    # Тескт
    td = $('<th/>', {
      'colspan': 4,
      'html': 'В т.ч. НДС 18%'
    })
    tr.append(td)
    # Price НДС
    td = $('<th/>', {'html': round_money(@nds) + ' р.'})
    tr.append(td)
    table.append(tr)
# сборка таблицы Админ + бух
  createTableAdmin: (responseData,ttn)->
# console.log 654
    _this = @
    table = $('<table/>', {'id': 'js-invoice--window--ttn-table','class':'adminStyle'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))

    tr.append($('<th/>').width('20px'))

    # Number
    td = $('<th/>', {'text': '№'})
    tr.append(td)
    # Name
    td = $('<th/>', {'html': 'Наименование и <br>описание продукции'})
    tr.append(td)
    # Quantity
    td = $('<th/>', {'html': 'Количество<br>продукции'})
    tr.append(td)
    # Price for one
    td = $('<th/>', {'html': 'стоимость<br>за штуку'})
    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': 'Общая<br>стоимость'})
    tr.append(td)

    # тогововая цена
    @main_price = 0
    # НДС
    @nds = 0
    # порядковый номер строки товра/услуги
    @posNum = 1
    # перебираем позиции
    for position,i in responseData
      responseData[i].num = @posNum++
      # добавляем строку для каждой позиции
      table.append(@tblAdminRow(responseData[i], ttn))


    # ИТОГО
    table.append(tr = $('<tr/>'))
    td = $('<th/>')
    tr.append(td)
    # текст
    td = $('<th/>', {
      'colspan': '4',
      'html': 'Итоговая сумма по данной спецификации (договору)'
    })
    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': round_money(@main_price) + ' р.'})
    tr.append(td)
    table.append(tr)
    # в том числе НДС
    table.append(tr = $('<tr/>'))
    td = $('<th/>')
    tr.append(td)
    # Тескт
    td = $('<th/>', {
      'colspan': '4',
      'html': 'В т.ч. НДС 18%'
    })
    tr.append(td)
    # Price НДС
    td = $('<th/>', {'html': round_money(@nds) + ' р.'})
    tr.append(td)
    table.append(tr)
    table



# проверка прав и сборка таблицы
  createTable: (responseData, ttn, data_row)->
    if ttn != undefined
      return @createTableAdmin(responseData, ttn)
    # проверка уровня доступа и вызов соответствующей шапки
    # console.log @access
    switch @access
      when 1 then @createTableAdmin(responseData, ttn)
      when 7 then @createTableAdmin(responseData, ttn)
      when 2 then @createTableAdmin(responseData, ttn)
      else
        @createTableManager(responseData, ttn, data_row)

# клик по главному чекбоксу
  clickMainCheckbox: (table, td, input)->
    if input.prop('checked') || !input.prop('checked') && input.hasClass('checked_no_full')
      input.prop('checked', false).removeClass('checked_no_full')
      td.removeClass('checked')
      table.find('td input').each((index, el)->
        $(this).prop('checked', false).parent().removeClass('checked')
      )
    else
# console.log this
      input.prop('checked', true)
      td.addClass('checked')
      table.find('td input').each((index, el)->
        $(this).prop('checked', true).parent().addClass('checked')
      )
# проверка и поправка состояния главного чекбокса
  checkMainCheckbox: ()->
    main_check = @contentTbl.find('th input');
    # отработка главного checkbox
    if @contentTbl.find('td input:checked').length == @contentTbl.find('td input').length
      main_check.prop('checked', true).removeClass('checked_no_full')
      main_check.parent().addClass('checked')
    else if @contentTbl.find('td input:checked').length > 0
      main_check.prop('checked', false).addClass('checked_no_full')
      main_check.parent().addClass('checked')
    else
      main_check.prop('checked', false).removeClass('checked_no_full')
      main_check.parent().removeClass('checked')

# запрос новой ттн (нажатие на кнопку запросить)
# менеджер хочет создать новую ттн
# в базу заводится строка ттн
  queryNewTtn: (obj, data_row, _this)->
    self = @
    console.log @newTtnDate
    options = []
    position_numbers = []

    # собираем информацию по выделенным позициям
    $(@$el).find('table td input').each((index, el)->
      if($(this).prop('checked'))
        position_numbers.push($(this).parent().next().html())
        options.push($(this).parent().parent().data().id)
        return
    )

    # проверка на выбор
    if options.length == 0
      echo_message_js('Вы не выбрали ни одной позиции.', 'error_message')
      return false

    # снимаем показания выбора доставки
    delivery = []
    $(@$el).find('#ttn_car_div .ttn_car_div-body li').each((index, el)->
      if $(this).hasClass('checked')
        delivery.push($(this).find('div').eq(0).attr('class').split('-')[1])
    )
    # проверка выбора доставки
    if delivery.length == 0
      echo_message_js('Выберите способ доставки выбранных позиций.', 'error_message')
      return false


    # если дата в ттн ранее сегодняшней, то по умолчанию приблизительную дату отгрузки устанавливаем сегодняшнюю дату
    self.newTtnDate = getDateNow()

    # переводит стандартную дату вида 25.05.2016 в timestamp
    date_formate = (myDate)->
      myDate=myDate.split(".");
      newDate= myDate[1]+"/"+myDate[0]+"/"+myDate[2];
      new Date(newDate).getTime()

    if date_formate(self.newTtnDate) < date_formate(getDateNow())
      ship_date = getDateNow()
      
    content = $('<div/>').append($('<div/>', {
      'html': 'Укажите приблизительную дату отгрузки/доставки',

    })).append($('<div/>', {
      'css': {
        'padding': '5px',
        'margin': '10px 0 0 0'
      }
    }).append(date_shipment = $('<input/>', {
      'val': self.newTtnDate,
      'css': {
        'padding': '5px'
      }
    })))

    date_shipment.datetimepicker({
      minDate: new Date(),
      timepicker: false,
      dayOfWeekStart: 1,
      onSelectDate: (ct, $i)->
        $i.blur();

      onGenerate: (ct)->
        $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
        $(this).find('.xdsoft_date');

      closeOnDateSelect: true,
      format: 'd.m.Y'
    }).blur();

    wDate = new modalWindow({
      html: content,
      title: 'Укажите дату',
      buttons: [{
        text: 'Отмена',
        class: 'button_yes_or_no no',
        click: ()->
          $(wDate.winDiv).dialog('close').dialog('destroy').remove()
      }, {
        text: 'OK',
        class: 'button_yes_or_no yes',
        click: ()->
          $(wDate.winDiv).dialog('close').dialog('destroy').remove()
          positions_in_ttn = Number(data_row.positions_in_ttn) + Number(options.length)
          # отправляем запрос
          new sendAjax 'create_new_ttn', {
            invoise_id: self.options.id,
            positions_in_ttn: positions_in_ttn,
            positions: options.join(','),
            position_numbers: position_numbers.join(','),
            date_shipment: date_shipment.val(),
            date:self.newTtnDate,
            delivery: delivery.join('')
          }, (response)->
# при положительном ответе
            self.destroy()
            # сервер должен вернуть информацию по новой ТТН
            if(response.data)
# если успешно вернул - записываем новую информацию в объект
              data_row.ttn[data_row.ttn.length] = new ttnObj(response.data);
              data_row.positions_in_ttn = positions_in_ttn
              # обновляем информацию в DOM
              $('#js-main-invoice-table').invoice('reflesh', data_row)
            # console.log

            if(delivery.join('') == 'our_delivery')
              new modalConfirm({html: 'Открыть карту курьера в новой вкладке?'}, ()->
                window.open(window.location.origin + '/dostavka_new/dostavka_podrobno.php?date=' + date_shipment.val(), '_blank');
              )
      }]
    }, {single: false})



# Вы уверены, что хотите установить флаг рекламации?
# убиваем окно
  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()

# delete window
# бух присваивает ттн номер и подтверждает создание ттн
  confirmAndCreateTtn: (obj, data_row)->
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
      $('#js-main-invoice-table').invoice('reflesh', data_row.id)
      # отправляем запрос
      @saveObj.invoice_id = data_row.id
      @saveObj.manager_name = data_row.manager_name
      @saveObj.manager_id = data_row.manager_id
      @saveObj.client_name = data_row.client_name
      @saveObj.client_id = data_row.client_id
      @saveObj.invoice_num = data_row.invoice_num

      new sendAjax 'confirm_create_ttn', @saveObj, ()->
        _this.destroy()
    else
      echo_message_js('Для создания ттн необходимо ввести её номер', 'error_message')
# delete window





  getButtons: (obj, data_row, ttn)->
    _this = @
    @saveObj = {}
    buttons = []
    firstButton = {
      text: 'Отмена',
      class: 'button_yes_or_no no',
      click: ()->
        _this.destroy()
    }
    buttons.push(firstButton)


    if @access == 2 || @access == 1
      if Number(@defaults.number) != undefined && Number(@defaults.number) == 0
        buttons.push(
          text: 'Создать',
          class: 'button_yes_or_no no',
          id:'create_ttn_button'
          click: ()->
            _this.confirmAndCreateTtn(obj, data_row)
        )


    else
      if @checkNumber > 0
        if @access == 5 && ttn == undefined
          buttons.push(
            text: 'Запросить',
            class: 'button_yes_or_no',
            click: ()->
              _this.queryNewTtn(obj, data_row)
          )
    buttons

###
# прототип окна счёта
# некоторые пользователи емеют право редактировать номер счёта и дату
###
class invoiceWindow
  saveObj: {}
  posNum: 1
  defaults:
    id: 0,
    number: '0000',
    type: "new"

  constructor: (obj, data_row, data, accces) ->
# запоминаем уровень допуска
    @access = accces
    # сохраняем информацию по строке
    @options = data_row
    # сборка окна счёта
    @init(obj, data_row, data, accces)
# собираем окно счёт
  init: (obj, data_row, responseData, accces)->

    ###
    # создание контейнера
    ###
    main_div = $('<div/>')

    ###
    # создание окна
    ###
    @myObj = new modalWindow({
      html: main_div,
      width: '1000px',
      maxHeight: '100%',
      height:($(window).height() - 60),
      title: 'Счёт',
      buttons: @getBillButtons(obj, data_row)
    }, {
      closeOnEscape: true
    })
    @$el = @myObj.options.html[0]

    ###
    # добавляем таблицу
    ###
    main_div.append(@createTable(responseData))
    ###
    # добавление шапки окна
    ###
    main_div.prepend(@createHead(data_row))




#      console.log

# сборка таблицы
  createTable: (responseData)->
    _this = @
    table = $('<table/>', {'id': 'js-invoice--window--ttn-table'})
    # шапка таблицы
    table.append(tr = $('<tr/>'))

    # Number
    td = $('<th/>', {'text': '№'})
    tr.append(td)
    # Name
    td = $('<th/>', {'html': 'Наименование и <br>описание продукции'})
    tr.append(td)
    # Quantity
    td = $('<th/>', {'html': 'Количество<br>продукции'})
    tr.append(td)
    # Price for one
    td = $('<th/>', {'html': 'стоимость<br>за штуку'})
    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': 'Общая<br>стоимость'})
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
      tr = $('<tr/>').data(position).attr('data-id', position.id)

      td = $('<td/>')
      # Number
      td = $('<td/>').append(i)
      tr.append(td)
      # Name
      border = '1px solid green'
      td = _this.createTS_copyContent(position, 'name', table)
      tr.append(td)
      # Quantity
      td = _this.createTS_copyContent(position, 'quantity', table)
      tr.append(td)

      # Price for one
      pr_out = calc_price_with_discount(position.price, position.discount)
      position.pr_out = round_money(pr_out) + ' р.';
      # td  = $('<td/>').append(round_money(pr_out)+' р.')
      td = _this.createTS_copyContent(position, 'pr_out', table)
      tr.append(td)
      # Price for all
      if position.quantity == 0
        position.quantity = 1

      main_price += pr_out * position.quantity

      nds += Number(round_money(pr_out * position.quantity / 118 * 18))
      position.main_price = round_money(pr_out * position.quantity) + ' р.'

      td = _this.createTS_copyContent(position, 'main_price', table)
      tr.append(td)
      i++
      table.append(tr)

    # ИТОГО
    table.append(tr = $('<tr/>'))
    td = $('<th/>')
    # tr.append(td)
    # текст
    td = $('<th/>', {
      'colspan': '4',
      'html': 'Итоговая сумма по данной спецификации (договору)'
    })
    tr.append(td)
    # Price for all
    td = $('<th/>', {'html': round_money(main_price) + ' р.'})
    tr.append(td)
    table.append(tr)
    # в том числе НДС
    table.append(tr = $('<tr/>'))
    td = $('<th/>')
    # tr.append(td)
    # Тескт
    td = $('<th/>', {
      'colspan': '4',
      'html': 'В т.ч. НДС 18%'
    })
    tr.append(td)
    # Price НДС
    td = $('<th/>', {'html': round_money(nds) + ' р.'})
    tr.append(td)
    table.append(tr)
    table

  ###
  # возвращает <td> с textarea и кнопкой копировать,
  # изменения в поле textarea не редактируют информацию,
  # textarea служит только для того, чтобы программно скопировать информацию из его тела
  ###
  createTS_copyContent: (position, key, table)->
    _this = @
    td = $('<td/>', {
      'class': 'myyClass1',
      on:
        click: ()->
# убиваем все textarea в таблице
          _this.updateTableTextarea($(this).parent().parent())

          # перед вставкой textarea обнуляем отступы в ячейке
          $(this).css('padding', '0').html(textarea)
          # получаем контент ячейки
          name = $(this).data().val
          # вставка textarea
          textarea = $('<textarea/>', {
            'val': name,
            'click': (event)->
              event.preventDefault()
              event.stopPropagation()

              return false
          })

          $(this).html(textarea).focus()
          # кнопка сохранить
          div = $('<div/>', {
            'class': 'myBlockBefore',
            'html': 'Копировать',
            click: (event)->
              $(this).parent().find('textarea').select();
              # event.preventDefault()
              try
# // Now that we've selected the anchor text, execute the copy command
                successful = document.execCommand('copy');

                # $('<input/>',{'val':td1.text()}).execCommand("Copy")
                msg = successful ? 'successful': 'unsuccessful';
                console.log('Copy email command was ' + msg);
# _this.updateTableTextarea(table)

              catch  error
                console.log error
# on:
#   mouseenter:()->
#     $(this).remove()
          }).css({
            'marginLeft': ($(this).innerWidth() - 159),
            'marginTop': -2
          })
          $(this).append(div)
        mouseleave: ()->
          _this.updateTableTextarea($(this).parent().parent())

    }).append($('<div/>', {'class': 'mmmmm', 'html': position[key]})).data('val', position[key])
    return td
# перебор всех строк и репласе всех textarea
  updateTableTextarea: (table) ->
    table.find('textarea').each(()->
      name = $(this).parent().data().val
      $(this).parent().attr('style', '').html($('<div/>', {'class': 'mmmmm', 'html': name}))
    )
    return
# шапка таблицы счёт
  createHead: (data_row)->
    _this = @
    ###
    # контейнер шапки окна ТТН
    ###
    head_info = $('<div>', {id: 'ttn_head_info'});
    ###
    # сборка таблицы с общей информации по ТТН
    ###
    table = $('<table>', {id: 'ttn_head_info-table'});

    ###
    # строка с информацией по клиенту
    ###
    tr = $('<tr/>')
    tr.append($('<td/>', {'html': @options.client_name, 'class': 'ttn_client_name'}))
    tr.append($('<td/>', {
      'html': @options.client_requisit_name,
      'class': 'ttn_requisits',
      click: ()->
        new sendAjax('show_requesit', {
          'id': _this.options.client_requisit_id
        }, (response)->
          new modalWindow({
            html: new requesitContent(response.data),
            maxHeight: '100%',
            width: '650px',
            title: 'Реквизиты',
          }, {
            closeOnEscape: true,
            single: false
          })
        )
    }))
    table.append(tr)

    ###
    # если номер к данной ТТН не назначен - выводим строку
    # с формой назначения номера ТТН и даты от которой эта ТТН выставлена
    ###

    # span_ttn_number = $('<span/>')


    td = $('<td/>', {'colspan': '2'})

    input_date = $('<input/>', {
      'val': @options.invoice_create_date,
      'id': 'insert_invoice_date',
      blur: ()->
        console.log " >>> "
        number = $(_this.$el).find('#insert_invoice_number')
        if (Number(number.val())!=0 || number.length > 1)  && $(this).val() != '00.00.0000'
          console.log _this.myObj.buttonDiv
          $(_this.myObj.buttonDiv).find('#create_bill_button').removeClass('no')
          _this.editSaveObj('date', $(this).val(), _this.options.invoice_create_date)
# blur: ()->


    }).datetimepicker({
      minDate: new Date(),
      timepicker: false,
      dayOfWeekStart: 1,
      onSelectDate: (ct, $i)->
        $i.blur();
      onGenerate: (ct)->
        $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
        $(this).find('.xdsoft_date');


      closeOnDateSelect: true,
      format: 'd.m.Y'
    });

    if (Number(@options.invoice_num) == 0 or @options.invoice_create_date == '00.00.0000' ) && @access == 2
      input = $('<input/>', {
        'val': @options.invoice_num,
        'data-val': @options.invoice_num,
        'class': 'ttn_number_input',
        'id': 'insert_invoice_number',
        focus: ()->
          if Number($(this).val())==0 && $(this).length <= 1
            $(this).val('')
            $(_this.myObj.buttonDiv).find('#create_bill_button').addClass('no')
        blur: ()->
          if Number($(this).val())==0 && $(this).length <= 1
            $(this).val($(this).attr('data-val'))
            $(_this.myObj.buttonDiv).find('#create_bill_button').addClass('no')
        keyup: ()->
          if((Number($(this).val())!=0 || $(this).length > 1) && input_date.val() != '00.00.0000')
            $(_this.myObj.buttonDiv).find('#create_bill_button').removeClass('no')
          else
            $(_this.myObj.buttonDiv).find('#create_bill_button').addClass('no')
          _this.editSaveObj('number', $(this).val(), _this.options.invoice_num)
      })


      # номер ттн
      td.append('№ Счёта ').append($('<span/>').append(input))
      # дата ттн
      td.append($('<span/>').append(input_date))

# счёт
    else
      span_invoice = $('<span/>', {'html': "№ Счёта " + @options.invoice_num + " от " + @options.invoice_create_date})
      td.append(span_invoice)


    tr = $('<tr/>').append(td)
    table.append(tr)
    ###
    # добавляем всё в контейнер и возвращаем
    ###
    head_info.append(table)
  getBillButtons: (obj, data_row)->
    _this = @
    @saveObj = {}
    buttons = []
    if Number(data_row.invoice_num) <= 0 || data_row.invoice_create_date == '00.00.0000'

      buttons.push(
        text: 'Отмена',
        class: 'button_yes_or_no no',
        click: ()->
          _this.destroy())

      buttons.push(
        text: 'Создать',
        class: 'button_yes_or_no no',
        id: 'create_bill_button',
        click: ()->
          _this.confirmAndCreateBill(obj, data_row)
      )
    buttons

  editSaveObj: (key, value, old_value)->
    if(old_value == value)
      delete @saveObj[key]
      @saveObj[key] = undefined
    else
      @saveObj[key] = value
    return
# бух присваивает номер и подтверждает создание счета
  confirmAndCreateBill: (obj, data_row)->
    _this = @
    console.log data_row
    @saveObj.id = data_row.id
    # check update ttn number
    reload = 0

    console.log @saveObj

    number = $(@$el).find('#insert_invoice_number')
    if number.length && (Number(number.val())!= 0 && number.val()!= '')
      reload++
      data_row.invoice_num = number.val()
    else
      echo_message_js('Вы не указали номер счёта', 'error_message')

    date = $(@$el).find('#insert_invoice_date')
    if date.length && date.val()!= '00.00.0000'
      reload++
      data_row.invoice_create_date = date.val()
    else
      echo_message_js('Вы не указали дату', 'error_message')

    if(reload == 2)
# обновляем информацию по строке
      obj.parent().data({}).data(data_row)
      # обновляем дом в строке
      $('#js-main-invoice-table').invoice('reflesh', data_row)
      # отправляем запрос
      @saveObj['doc_type'] = data_row.doc_type
      @saveObj['doc_id'] = data_row.doc_id
      @saveObj['manager_id'] = data_row.manager_id
      @saveObj['manager_name'] = data_row.manager_name
      @saveObj['client_name'] = data_row.client_name
      @saveObj['client_id'] = data_row.client_id


      new sendAjax 'confirm_create_bill', @saveObj, ()->
        _this.destroy()

# убиваем окно
  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()

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
    Prange: 50
    PminStart: 0
    Pmin: 0
    Pmax: 0

    tabMenu: []   # меню



    access_def: 0
    response_def: {}
    constructor: (el, options) ->
      self = @
      new sendAjax 'get_data', {}, (response)->
        self.options = $.extend({}, self.defaults, response)
        self.access = response.access
        self.$el = $(el)
        ###
        # добавление меню
        ###
        self.addMenu()

        self.init()

        self.quick_button_div = $('#quick_button_div')
        if self.access == 2 or self.access == 1
          self.quick_button_div.append($('<span/>', {
            'html': 'приходы',
            'class': 'button',
            click: (e)->
              # Окно с поиском по приходам","successful_message
              new paymentWindow(new paymentObj(), {})
          }))
          self.quick_button_div.append($('<apsn/>', {
            'html': 'расходы',
            'class': 'button',
            click: (e)->
              # Окно с поиском по приходам","successful_message
              new costsWindow(new paymentObj(), {})
          }))


# обновление одной строки
    reflesh: (id)->
      if typeof id is 'string'
        if $(@$el).find('#tt_' + id).length > 0
          data = $(@$el).find('#tt_' + id).data()
          return $(@$el).find('#tt_' + id).replaceWith(@createRow(data))
      else
        if $(@$el).find('#tt_' + id.id).length > 0
          data = $(@$el).find('#tt_' + id.id).data(id)
          return $(@$el).find('#tt_' + id.id).replaceWith(@createRow(id))
    addMenu: ()->
      _this = @
      @tabMenu = $('#js-menu-invoice ul')
      section = Number($.urlVar('section'))
      tabs = [
        {
          index: 0
          name: 'Все'
        }, {
          index: 1
          name: 'Запрос'
        }, {
          index: 2
          name: 'Готовые'
        }, {
          index: 3
          name: 'Част. оплаченные'
        }, {
          index: 4
          name: 'Оплаченные'
        }, {
          index: 5
          name: 'Запрос ТТН'
        }, {
          index: 6
          name: 'Готовые ТТН'
        }, {
          index: 7
          name: 'Част. отгрузка'
        }, {
          index: 8
          name: 'Отгрузка'
        }, {
          index: 9
          name: 'Закрытые'
        }
      ]
      for n,i in tabs
        @tabMenu.append(li = $('<li/>', {
          click: (e)->
# меняем URL
            $.urlVar('section', $(this).data('index'))
            # удаляем выделение со старого выбранного элемента
            _this.tabMenu.find('.selected').removeClass('selected')
            # выделяем текущий элемент
            $(this).addClass('selected')
            # обновляем информацию в таблице
            _this.updateTable()
        }))
        li.append(span = $('<span/>'))
        span.append($('<div/>', {'class': 'border', 'html': tabs[i].name}))
        li.data('index', tabs[i].index)
        if tabs[i].index == 0
          li.css({'float': 'right'})
        if tabs[i].index == section
          li.addClass('selected')
# обновление информации в таблице
    updateTable: () ->
      _this = @
      window_preload_add()
      new sendAjax 'get_data', {}, (response)->
        _this.options = $.extend({}, _this.defaults, response)
        _this.init()
        window_preload_del()
    greateHead: ()->
      thead = $('<thead/>');
      tr = $('<tr/>')
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': '№, дата'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': '1C'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'приходы по счету'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'заказ,<br>менеджер'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'class': 'flag'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'клиент: название и юр. лицо'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'расходы по счету'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'class': 'ice'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'прибыль'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'class': 'calculator'
      }))
      tr.append($('<th/>', {
        'colspan': 3,
        'html': 'ТТН'
      }))
      tr.append($('<th/>', {
        'colspan': 2,
        'html': 'СПФ'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'статус заказа'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'class': 'dindin'
      }))
      thead.append(tr)

      tr = $('<tr/>')
      tr.append($('<th/>', {
        'id': 'defttn1'
        'html': '№'
      }))
      tr.append($('<th/>', {
        'id': 'defttn2',
        'html': 'дата'
      }))
      tr.append($('<th/>', {
        'id': 'defttn3',
        'html': 'в-т'
      }))
      tr.append($('<th/>', {
        'html': '№'
      }))
      tr.append($('<th/>', {
        'html': 'в-т'
      }))
      thead.append(tr)
    updateHead: ()->
      @$el.find('thead').replaceWith(@greateHead)
    init: () ->
      _this = @
      @updateHead()
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
        @$el.find('tbody').append(@createRow(@options.data[i]))

      ###
      # кнопка показать ещё
      ###
      $('#js-main-invoice-show-rows').remove() if($('#js-main-invoice-show-rows').length > 0)

      $('#js-main-invoice-div').append(@showMore = $('<div/>', {
        'id': 'js-main-invoice-show-rows',
        'html': 'Показать ещё ' + @Pmax + '/' + len,
        'data-c': len
      }))
      @showMore.click(()->
        _this.addMoreRows()
      )

    addMoreRows: ()->
      text = 'Показать еще ' + @Pmax + '/' + @showMore.data().c
      Pmin = @Pmax
      @Pmin = @Pmax + 1
      @Pmax = @Pmax + @Prange
      if Number(@showMore.data().c) < @Pmax
        @Pmax = Number(@showMore.data().c)
        text = 'Показано строк ' + @Pmax + '/' + @showMore.data().c

      if Pmin == @Pmax
        return echo_message_js("На страницу уже выгружены все счета " + Pmin + " / " + @Pmax)

      for i in [@Pmin...@Pmax]
        @$el.find('tbody').append(@createRow(@options.data[i]))


      @showMore.html(text)
      # скрол к первой подгруженной строке
      t = $(window).scrollTop() + $(window).height() - 100
      $("html, body").animate({scrollTop: t}, 600)  if t > 0

    printOptions: ()->
      console.info @options.access
      console.info @options.data

# обновление сонтента в таблице без запроса
    updateRows: ()->
      @$el.find('tbody').html('')
      @init();


    getTtnRow: (row, ttn, i)->
      _this = @
      tr = $('<div/>', {
        'id': ttn.id,
        'class': 'row'
      }).data(ttn)
      console.log ttn.ttn_lok
      if ttn.ttn_bgcolor_class != undefined
        tr.addClass(ttn.ttn_bgcolor_class)
      # определяем номер
      if ttn.number <= 0
        number = 'запрос'
      else
        number = ttn.number
      # определяем дату
      tr.append(d = $('<div/>', {
        'class': 'defttn1 cell',
        'html': number,
        click: ()->
          t = $(this)
          if Number(ttn.ttn_lok) == 0
            return false
          # окно Запрос ТТН
          new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
            new ttnWindow(t, row, response.data, _this.options.access, ttn) if response.data != undefined
          )
      }).width(_this.defttn[0]))
      if ttn.number <= 0
        d.addClass('redTD')
      tr.append($('<div/>', {
        'class': 'defttn2 cell',
        'html': ttn.date,
        click: ()->
# окно Запрос ТТН
          t = $(this)
          if Number(ttn.ttn_lok) == 1
            return false
          new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
            new ttnWindow(t, row, response.data, _this.options.access, ttn) if response.data != undefined
          )
      }).width(_this.defttn[1]))

      if (ttn.return != null && Number(ttn.return) == 1)
        check = ' checked'
      else
        check = ''

      #checkbox
      div22 = $('<div/>')


      divw = $('<div/>', {
        'html':div22
        'class': 'defttn3 cell invoice-row--ttn--vt invoice-row--checkboxtd' + check,
        # 'html':ttn.id,
        'data-id': ttn.id
        click: ()->
          if _this.options.access != 2
            $(this).prev().click()
            return false

          if Number(ttn.return) == 1
            # вставляем подтверждение
            t = $(this)
            ttn.return = 1
            ttn.date_return = getDateNow();
            t.addClass('checked')
          else
            ttn.return = 0
            ttn.date_return = getDateNow();
            $(this).removeClass('checked')

          new sendAjax 'ttn_was_returned', {id: row.ttn[i].id, val: ttn.return}
        on:
          mouseenter: ()->
            $(this).css( 'cursor': 'posinter')
            if !div22.hasClass('notify')
              div22.addClass('notify')
              setTimeout(()->
                if div22.hasClass('notify') && Number(ttn.return) > 0
                  div22.notify($('<div/>', {
                    'css': {'textAlign': 'left'},
                    'html': 'дата возврата:<br>' + ttn.date_return
                  }), {position: "right", className: 'invoice_12px', autoHide: false})
              , 1000)
          mouseleave: ()->
            $(this).find('div.notifyjs-wrapper').remove()
            $(this).attr('style', '').width(_this.defttn[2])
            div22.removeClass('notify')
      }).width(_this.defttn[2])


      if Number(ttn.shipment_status) == 1 && Number(ttn.number) > 0
        divw.addClass('redTD')
      tr.append(divw).data(ttn)

      tr

    ###
    # создание TD ттн
    ###
    getTdTtn: (row)->
# вызов окно Запрос ТТН
      windowClick = (event)->


      if @defttn == undefined
        @defttn = {
          0: $('#defttn1').width(),
          1: $('#defttn2').width(),
          2: $('#defttn3').width()
        }

      _this = @

      table = $('<div/>', {'class': 'table', 'style': 'width:100%'})

      #  перебор ТТН
      for ttn,i in row.ttn
        table.prepend(@getTtnRow(row, ttn, i))

      if row.ttn.length <= 0
        td = $('<td/>', {
          'colspan': '3',
          'class': 'js-query-ttn'
        })
      else
        td = $('<td/>', {
          'colspan': '3',
          'class': 'js-query-ttn-rows'
        }).append(table)

      if _this.options.access == 5
        console.log row
        console.log row.positions_num, row.positions_in_ttn
        if Number(row.positions_num) > Number(row.positions_in_ttn) && Number(row.positions_in_ttn) > 0
          td.append($('<div/>', {
            'html': 'Запросить',
            'class': 'js-query-ttn-div mayBeEdit',
            click: ()->
              t = $(this)
              if Number(row.invoice_num)==0 && row.invoice_create_date == '00.00.0000'
                new warnNotCreateInvoice()
                return false
              new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
                new ttnWindow(t, row, response.data, _this.options.access) if response.data != undefined
              )
          }))
        else if Number(row.positions_num) > Number(row.positions_in_ttn)
          console.log 654
          td = $('<td/>', {
            'colspan': '3',
            'class': 'js-query-ttn',
            'html': 'Запросить',
            click: (e)->
              t = $(this)
              if Number(row.invoice_num)==0 && row.invoice_create_date == '00.00.0000'
                new warnNotCreateInvoice()
                return false
              new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
                new ttnWindow(t, row, response.data, _this.options.access) if response.data != undefined
              )
          })


      td







    ###
    # create tr
    ###
    createRow: (row)->

      new invoiceRow(row, @access, @getTdTtn(row))


  # Define the plugin
  $.fn.extend invoice: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('invoice')

      if !data
        $this.data 'invoice', (data = new invoice(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window
###
# метод отправки запроса AJAX
###
class warnNotCreateInvoice
  constructor: (mess = "Внесите дату и номер счёта")->
#    echo_message_js()
    return new modalWindow({
      html: mess,
      title: 'Внимание'
    }, {
      single: false,
      closeOnEscape: true
    })

###
# прототип html строки таблицы счета
###
class invoiceRow
  enterObj: {}
  options: {}
  access: 0


  constructor: (data, access, ttn)->
    @access = access
    @options = data

    return @init(ttn)
  init: (ttn)->
    _this = @
    # console.log 654
    tr = $('<tr/>', {
      id: 'tt_' + @options.id
    }).data(@options);
    # номер, дата
    if @options.doc_type == 'spec'
      @options.spf_num = @options.doc_num
      doc_type = 'счёт'
    else
      @options.spf_num = 'оф'
      doc_type = 'счёт - оферта'

    td = $('<td/>', {
      'class': 'invoice-row--fist-td',
      click: ()->

        t = $(@)
        new sendAjax('get_ttn', {'id': _this.options.id}, (response)->
          # создаем экземпляр окна ттн
          new invoiceWindow(t, _this.options, response.data, _this.access)
        )
    })
    .append(d = $('<div/>', {
      'class': 'invoice-row--number',
    }))
    .append($('<div/>', {
      'class': 'invoice-row--type',
      'html': doc_type
    }))

    d.append($('<span/>',{
      'html':@options.invoice_num,
      'css':{
        'paddingRight':'10px',
        'fontSize':'14px'
      }
    })).append(@options.invoice_create_date)

    tr.append(td)

    # 1с
    td = $('<td/>', {
      'class': 'invoice-row--checkboxtd'
    }).append($('<div/>', {
      'class': 'invoice-row--checkboxtd-div'
    }))

    td.click ()->
      if _this.access == 2
        if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
          new warnNotCreateInvoice()
          return false
          
        if $(this).hasClass('checked')
          _this.options.flag_1c = 0;
          $('#js-main-invoice-table').invoice('reflesh', _this.options)
        else
          if (Number(_this.options.invoice_num) == 0 || _this.options.invoice_create_date == '00.00.0000'  )
            echo_message_js("введите номер счёта и дату")
            t = $(@)
            new sendAjax('get_ttn', {'id': _this.options.id}, (response)->
# создаем экземпляр окна ттн
              new invoiceWindow(t, _this.options, response.data, _this.access)
            )
          else
            _this.options.flag_1c = 1;
            $('#js-main-invoice-table').invoice('reflesh', _this.options)

        # сохраняем значение флага
        new sendAjax 'edit_flag_1c', {id: _this.options.id, val: _this.options.flag_1c}
        return

    td.addClass('checked') if Number @options.flag_1c > 0
    td.addClass('red1c') if(Number(@options.flag_1c) == 0 && (Number(@options.invoice_num) > 0 and @options.invoice_create_date != '00.00.0000'  ))

    tr.append(td)

    # приходы по счёту
    div1 = $('<div/>', {
      'class': 'invoice-row--price-profit',
      'html': round_money(@options.price_out)
    })

    div2 = $('<div/>', {
      'class': 'invoice-row--price-payment',
      'html': round_money(@options.price_out_payment)
    })
    # подкрашиваем красным если сумма по счётам не соответствует сумме оплат
    if Number(@options.price_out) != Number(@options.price_out_payment)
      div2.addClass('redText')
    td = $('<td/>', {
      'data-id': @options.id,
      click: (e)->
        if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
          new warnNotCreateInvoice()
          return false
        t = $(@)
        new sendAjax('get_payment', {'id': _this.options.id}, (response)->
# создаем экземпляр окна ттн
          console.log _this.access
          new paymentWindow(_this.options, response.data, _this.access)
        )
      on:
        mouseenter: ()->
          $(this).css('backgroundColor': '#f1f1f1', 'cursor': 'pointer')

          if !div2.hasClass('notify')
            div2.addClass('notify')

            setTimeout(()->
#              echo_message_js _this.options.id
              if div2.hasClass('notify')

                new sendAjax('get_payment', {'id': _this.options.id, 'not_deleted_row': 1}, (response)->
                  div2.notify(notifyContent = $('<div/>', {'html': 'нет оплаты'}), {
                    position: "right",
                    className: 'invoice_12px',
                    autoHide: false
                  })
                  if response.data.length > 0

                    tbl = $('<table/>', {'class': 'notify-table', 'id': 'invoice-row--price-payment-table'})
                    tbl.append(ptr = $('<tr/>'))
                    ptr.append($('<td/>', {'html': 'сумма'}))
                    ptr.append($('<td/>', {'html': '%'}))
                    ptr.append($('<td/>', {'html': '№'}))
                    ptr.append($('<td/>', {'html': 'дата'}))
                    for row,i in response.data
                      tbl.append(ptr = $('<tr/>'))
                      ptr.append($('<td/>', {'html': round_money(response.data[i].price)}))
                      ptr.append($('<td/>', {'html': round_money(response.data[i].percent) + '%'}))
                      ptr.append($('<td/>', {'html': response.data[i].number}))
                      ptr.append($('<td/>', {'html': response.data[i].date}))
                    notifyContent.html(tbl)

                    if Number(_this.options.conditions) > Number(_this.options.percent_payment)
                      dopText = $('<div/>', {'class': 'invoice-row--price-payment-dopText'})
                      dopText.append($('<div/>', {'style': 'text-align:left;color:red;', 'html': 'Внимание!'}))
                      dopText.append($('<div/>', {
                        'style': 'text-align:left;',
                        'html': 'Не выполнены условия договора:'
                      }))
                      dopText.append($('<div/>', {
                        'style': 'text-align:left;',
                        'html': 'график оплаты: ' + round_money(_this.options.conditions) + '-' + round_money(100 - Number(_this.options.conditions)) + '%'
                      }))
                      notifyContent.append(dopText)
                    if Number(_this.options.price_out) != Number(_this.options.price_out_payment)
                      dopText = $('<div/>', {'class': 'invoice-row--price-payment-dopText'})
                      dopText.append($('<div/>', {'style': 'text-align:left;color:red;', 'html': 'Внимание!'}))
                      dopText.append($('<div/>', {
                        'style': 'text-align:left;',
                        'html': 'Сумма по счёту не соответствует сумме оплаты'
                      }))
                      notifyContent.append(dopText)
                )
            , 1000)

        mouseleave: ()->

          $(this).find('div.notifyjs-wrapper').remove()
          $(this).attr('style', '')
          div2.removeClass('notify')

    }).css('cursor', 'pointer')
    td.append(div1)
    td.append(div2)
    tr.append(td)
    # заказ, менеджер
    td = $('<td/>')
    .append($('<div/>', {
      'class': 'invoice-row--order-number'
      'html': @options.invoice_num
    }))
    .append($('<div/>', {
      'class': 'invoice-row--meneger--full-name',
      'html': @options.manager_name
    }))
    tr.append(td)
    # флаг рекламация
    td = $('<td/>', {
      'class': 'invoice-row--icons-flag'
    })
    .append($('<div/>', {
      'class': 'invoice-row--checkboxtd-div'
    }))

    td.click ()->
      if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
        new warnNotCreateInvoice()
        return false
      if $(this).hasClass('checked')
        if(Number(_this.access) != 1)
          console.log _this.access
          echo_message_js('Снять рекламацию может только администратор', 'error_message')
          return false;

        _this.options.flag_flag = 0;
        $(this).removeClass('checked')
        # сохраняем значение флага
        new sendAjax 'edit_flag_flag', {id: _this.options.id, val: _this.options.flag_flag}
      else
        if(Number(_this.access) != 5 && Number(_this.access) != 1)
          echo_message_js('Рекламацию устанавливает только менеджер', 'error_message')
          return false;

        t = $(@)


        new modalConfirm({html: 'Вы уверены, что хотите установить флаг рекламации?'}, ()->
          _this.options.flag_flag = 1;
          t.addClass('checked')
          # сохраняем значение флага
          new sendAjax 'edit_flag_flag', {id: _this.options.id, val: _this.options.flag_flag}
        )

      return

    td.addClass('checked') if Number @options.flag_flag > 0
    tr.append(td)
    # клиент, юрлицо
    td = $('<td/>')
    .append($('<div/>', {
      'class': 'invoice-row--client--name',
      'html': @options.client_name,
      click: ()->
        window.open(
          'http://' + location.hostname + '/os/?page=clients&section=client_folder&subsection=client_card_table&client_id=' + _this.options.client_id,
          '_blank'
        );
    }))
    .append($('<div/>', {
      'class': 'invoice-row--client--requsits',
      'data-id': @options.client_requisit_id,
      'html': @options.client_requisit_name
    }))
    tr.append(td)

    # расходы по счёту
    div31 = $('<div/>', {
      'class': 'invoice-row--price-start',
      'html': _this.options.costs_supplier_bill
    })
    div3 = $('<div/>', {
      'class': 'invoice-row--price-our-pyment',
      'html': _this.options.costs
    })


    td = $('<td/>', {
      'class': 'mayBeEdit',
      click: (e)->
        if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
          new warnNotCreateInvoice()
          return false
        new sendAjax('get_costs', {'id': _this.options.id}, (response)->
          new costsWindow(_this.options, response.data, _this.access)
        )
      on:
        mouseenter: ()->
          t = $(this)
          #            div2.css('backgroundColor':'red')

          $(this).css('backgroundColor': '#f1f1f1', 'cursor': 'posinter')
          if !div3.hasClass('notify')
            div3.addClass('notify')

            setTimeout(()->
#              echo_message_js _this.options.id
              if div3.hasClass('notify')

                new sendAjax('get_costs_qtip', {'id': _this.options.id, 'not_deleted_row': 1}, (response)->
                  div3.notify(notifyContent = $('<div/>', {'html': 'нет оплаты'}), {
                    position: "right",
                    className: 'invoice_12px',
                    autoHide: false
                  })
                  if response.data.length > 0
                    tbl = $('<table/>', {'class': 'notify-table', 'id': 'invoice-row--price-payment-table'})
                    tbl.append(ptr = $('<tr/>'))
                    ptr.append($('<td/>', {'html': 'Поставщик'}))
                    ptr.append($('<td/>', {'html': 'Сумма долга'}))
                    for row,i in response.data
                      tbl.append(ptr = $('<tr/>'))
                      ptr.append($('<td/>', {'html': response.data[i].supplier_name}))
                      ptr.append($('<td/>', {'html': round_money(response.data[i].price)}))
                    notifyContent.html(tbl)
                  if Number(_this.options.costs) != Number(_this.options.costs_supplier_bill)
                    dopText = $('<div/>', {'class': 'invoice-row--price-payment-dopText'})
                    dopText.append($('<div/>', {'style': 'text-align:left;color:red;', 'html': 'Внимание!'}))
                    dopText.append($('<div/>', {
                      'style': 'text-align:left;',
                      'html': 'Сумма по счёту не соответствует сумме оплаты'
                    }))
                    notifyContent.append(dopText)
                )
            , 1000)

        mouseleave: ()->
          $(this).find('div.notifyjs-wrapper').remove()
          $(this).attr('style', '')
          div3.removeClass('notify')
    }).css('cursor', 'pointer')

    td.append(div31)
    td.append(div3)

    if Number(@options.costs) != Number(@options.costs_supplier_bill)
      div3.addClass('redText')


    tr.append(td)
    # глаз
    td = $('<td/>', {
      'class': 'invoice-row--ice mayBeEdit',
      click: (e)->
        if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
          new warnNotCreateInvoice()
          return false
        new sendAjax('get_costs', {'id': _this.options.id}, (response)->
          new costsWindow(_this.options, response.data, _this.access)
        )
    })


    td.addClass('checked') if Number @options.flag_ice > 0
    tr.append(td)
    # прибыль

    pr = round_money(((Number(@options.price_out_payment) - Number(@options.costs)) / Number(@options.price_out_payment) * 100).toString())


    if Number(@options.price_out_payment) == 0
      pr = '0.00'
    td = $('<td/>')
    .append($('<div/>', {
      'class': 'invoice-row--price-our-profit',
      'html': round_money(Number(@options.price_out_payment) - Number(@options.costs))
    }))
    .append($('<div/>', {
      'class': 'invoice-row--price-our-profit-percent',
      'html': pr + '%'

    }))
    tr.append(td)
    # калькулятор
    td = $('<td/>', {
      'class': 'invoice-row--icons-calculator mayBeEdit',
      click: (e)->
        if Number(_this.options.invoice_num)==0 && _this.options.invoice_create_date == '00.00.0000'
          new warnNotCreateInvoice()
          return false
        new sendAjax('get_costs', {'id': _this.options.id}, (response)->
          new costsWindow(_this.options, response.data, _this.access)
        )
    })


    td.addClass('checked') if Number @options.flag_calc > 0
    tr.append(td)
    # ттн
    tr.append(ttn)

    # спф дата
    td = $('<td/>', {
      'class': 'tdLink mayBeEdit',
      click: ()->
        if _this.options.doc_type == 'spec'
          window.open(
            'http://' + location.hostname + '/os/?page=agreement&section=agreement_editor&client_id=' + _this.options.client_id + '&agreement_id=' + _this.options.doc_id + '&agreement_type=long_term&open=specification&specification_num=' + _this.options.doc_num + '&dateDataObj={"doc_type":"spec"}',
            '_blank'
          );
        else
          window.open(
            'http://' + location.hostname + '/os/?page=agreement&section=agreement_editor&client_id=' + _this.options.client_id + '&oferta_id=' + _this.options.doc_id + '&dateDataObj={"doc_type":"oferta"}',
            '_blank'
          );

    })
    .append($('<div/>').html(@options.spf_num))

    tr.append(td)

    # спф checkbox
    div22 = $('<div/>')
    td = $('<td/>', {
      'class': 'invoice-row--ttn--vt invoice-row--checkboxtd',
      click: ()->
        if $(this).hasClass('checked')
          _this.options.flag_spf_return = 0;
          _this.options.spf_return_date = '';
          $(this).removeClass('checked')
        else
          _this.options.flag_spf_return = 1;
          _this.options.spf_return_date = getDateNow();
          $(this).addClass('checked')
        # сохраняем значение флага
        new sendAjax 'edit_flag_spf_return', {id: _this.options.id, val: _this.options.flag_spf_return}

      on:
        mouseenter: ()->
          t = $(this)
          #            div2.css('backgroundColor':'red')

          $(this).css('backgroundColor': '#f1f1f1', 'cursor': 'posinter')
          if !div22.hasClass('notify')
            div22.addClass('notify')
            setTimeout(()->
              if div22.hasClass('notify') && _this.options.spf_return_date && _this.options.spf_return_date != ''
                div22.notify(notifyContent = $('<div/>', {
                  'css': {'textAlign': 'left'},
                  'html': 'дата возврата:<br>' + _this.options.spf_return_date
                }), {position: "right", className: 'invoice_12px', autoHide: false})
            , 1000)
        mouseleave: ()->
          $(this).find('div.notifyjs-wrapper').remove()
          $(this).attr('style', '')
          div22.removeClass('notify')
    })

    td.append(div22)


    td.addClass('checked') if Number @options.flag_spf_return > 0
    tr.append(td)
    # статус
    td = $('<td/>', {'html': @options.status})
    tr.append(td)
    # диндин
    td = $('<td/>')
    tr.append(td)
    td.append(commentDiv = $('<div/>', {
      'class': 'invoice-row-icon invoice-row--comment',
      click: ()->
        new sendAjax('get_comments_module', {invoice_id: _this.options.id}, (response)->
          new commentsWindow(_this.options, response.data, _this.access)
        )
    }))
    # если заполнено
    if Number(@options.comments_num) > 0
      commentDiv.addClass('isfull')


    return tr

###
# jQuery plagin Sklad
#
# @see       sklad table
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   17.05.2016 16:44:46
###
(($, window) ->

  ###
  # Define the plugin class sklad
  ###
  class sklad

    defaults:
      start: false
    showMore: []  # кнопка показать ещё
    Prange: 50
    PminStart: 0
    Pmin: 0
    Pmax: 0

    tabMenu: []   # меню

    access_def: 0
    response_def: {}
    constructor: (el, options) ->
      self = @
      new sendAjax 'get_data_sklad', {url:window.location.href}, (response)->
        self.options = $.extend({}, self.defaults, response)
        self.access = response.access
        self.$el = $(el)
        ###
        # добавление меню
        ###
        self.addMenu()

        self.init()


# обновление одной строки
    reflesh: (id)->
      if id.ttn_id != undefined
        data = id
        id = data.ttn_id
        console.log "принят объект ", data.shipment_status
      else
        console.log "принят id "
        data = $(@$el).find('#sklad_row_' + id).data()

      if $(@$el).find('#sklad_row_' + id).length > 0
        if $(@$el).find('#sklad_row_' + id).hasClass('subRow')
          rowspan = 0
        else
          rowspan = Number($(@$el).find('#sklad_row_' + id + ' td').eq(1).attr('rowspan'))

        console.log "Новая информация по строке: ->>  ", data
        $(@$el).find('#sklad_row_' + id).replaceWith(new skladRow(data, @aceess, rowspan))
        #проверка статуса
        @checkGlobalStatus(data)



    checkGlobalStatus: (data)->
      if $(@$el).find('#sklad_row_' + data.ttn_id).length > 0

        row = $(@$el).find('#sklad_row_' + data.ttn_id)
        while row.hasClass('subRow')
          row = row.prev()

        firstRow = row
        dataFirstRow = firstRow.data()
        rowspan = 1

        shipment_status = Number(dataFirstRow.shipment_status)

        number_positions = dataFirstRow.positions_num.split(',').length
        # подсчёт строк
        while row.next() != undefined and row.next().hasClass('subRow')
          row = row.next()
          rData = row.data()
          number_positions += rData.positions_num.split(',').length
          shipment_status += Number(rData.shipment_status)
          rowspan++


        new sendAjax('check_shipment_global_status', {
          invoice_id: dataFirstRow.id,
          positions_num: Number(dataFirstRow.invoice_positions_num),
          positions_in_ttn: Number(dataFirstRow.positions_in_ttn)
        }, (response)->
          if dataFirstRow.status != response.data.status
            dataFirstRow.status = response.data.status

            rowNew = new skladRow(dataFirstRow, @aceess, rowspan)
            firstRow.replaceWith(rowNew)
        )




    getData: (id)->
      data = []
      if typeof id is 'string'
        if $(@$el).find('#sklad_row_' + id).length > 0
          data = $(@$el).find('#sklad_row_' + id).data()

      else
        if $(@$el).find('#sklad_row_' + id.id).length > 0
          data = $(@$el).find('#sklad_row_' + id.id).data(id)
      return data
    addMenu: ()->
      _this = @
      @tabMenu = $('#js-menu-invoice ul')
      section = Number($.urlVar('section'))
      tabs = [
        {
          index: 0
          name: 'Все'
        }, {
          index: 11
          name: 'На отгрузку самовывоз'
        }, {
          index: 12
          name: 'На отгрузку доставка'
        }, {
          index: 7
          name: 'Частично отгруженные'
        }, {
          index: 8
          name: 'Полностью отгруженные'
        }
      ]
      for n,i in tabs
        @tabMenu.append(li = $('<li/>', {
          click: (e)->
            # меняем URL
            $.urlVar('section', $(this).data('index'))
            # удаляем выделение со старого выбранного элемента
            _this.tabMenu.find('.selected').removeClass('selected')
            # выделяем текущий элемент
            $(this).addClass('selected')
            # обновляем информацию в таблице
            _this.updateTable()
        }))
        li.append(span = $('<span/>'))
        span.append($('<div/>', {'class': 'border', 'html': tabs[i].name}))
        li.data('index', tabs[i].index)
        if tabs[i].index == 0
          li.css({'float': 'right'})
        if tabs[i].index == section
          li.addClass('selected')
# обновление информации в таблице
    updateTable: () ->
      _this = @
      window_preload_add()
      new sendAjax 'get_data_sklad', {url:window.location.href}, (response)->
        _this.options = $.extend({}, _this.defaults, response)
        _this.init()
        window_preload_del()
    greateHead: ()->
      _this = @
      thead = $('<thead/>');
      tr = $('<tr/>')
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': '№ счёта, дата'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'заказ,<br>менеджер'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'клиент: название и юр. лицо'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'отгрузка'
      }))
      tr.append(thDate = $('<th/>', {
        'rowspan': 2,
        'class':'mayBeClick',
        'html': 'дата',
      }))
      button2 = []
      btn1 = {
        'name': 'Сегодня',
        'class': '',
        click: (e)->
          # меняем URL
          today = getDateNow()
          $.urlVar('date_start', today)
          $.urlVar('date_end', today)
          $('#js-main-invoice-table').sklad('updateTable')

      }
      btn2 = {
        'name': 'Завтра',
        'class': '',
        click: (e)->
          # меняем URL
          today = getDateTomorrow()
          $.urlVar('date_start', today)
          $.urlVar('date_end', today)
          $('#js-main-invoice-table').sklad('updateTable')
      }
      btn3 = {
        'name': 'Календарь',
        'class': 'js--get-calendar-filter',
        click: (e)->
          thDate.append(inp = $('<input/>',{'css':{
            'opacity':0,
            'float':'left',
            'width':'0px',
            'height':'0px',
            'padding':'0'
          }}))
          inp.daterangepicker({
            timePicker: false,
            locale: {
              format: 'MM.DD.YYYY'
            }
          },(start, end, label)->
            $.urlVar('date_start', start.format('DD.MM.YYYY'))
            $.urlVar('date_end', end.format('DD.MM.YYYY'))
            $('#js-main-invoice-table').sklad('updateTable')
            inp.remove()
          );
          inp.focus()
      }
      btn4 = {
      'name': 'Сбросить',
      'class': '',
      click: (e)->
        # меняем URL
        today = getDateNow()
        $.delUrlVal('date_start')
        $.delUrlVal('date_end')
        $('#js-main-invoice-table').sklad('updateTable')
      }
      button2.push(btn4)
      button2.push(btn1)
      button2.push(btn2)
      button2.push(btn3)

      thDate.menuRightClick({'buttons': button2})

      tr.append($('<th/>', {
        'colspan': 4,
        'html': 'Товарно-транспортные накладные'
      }))
      tr.append($('<th/>', {
        'rowspan': 2,
        'html': 'статус заказа'
      }))
      thead.append(tr)

      tr = $('<tr/>')
      tr.append($('<th/>', {
        'id': 'defttn1'
        'html': '№'
      }))
      tr.append($('<th/>', {
        'id': 'defttn2',
        'html': 'дата'
      }))
      tr.append($('<th/>', {
        'id': 'defttn3',
        'html': 'статус'
      }))
      tr.append($('<th/>', {
        'html': 'кем, когда'
      }))
      thead.append(tr)
    updateHead: ()->
      @$el.find('thead').replaceWith(@greateHead)
    init: () ->
      _this = @
      @$el.addClass('sklad')
      @updateHead()
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

      # перебор строк ттн, группировка ттн по строкам счетов
      id = 0
      for i in [@Pmin...@Pmax]
        rowspan = 0
        if id != @options.data[i].invoice_id
          rowspan = 1
          id = @options.data[i].invoice_id
          k = i + 1

          while @options.data[k] != undefined and @options.data[k].invoice_id == id
            rowspan++
            k++

        @$el.find('tbody').append(new skladRow(@options.data[i], @access, rowspan))



      ###
      # кнопка показать ещё
      ###
      $('#js-main-invoice-show-rows').remove() if($('#js-main-invoice-show-rows').length > 0)

      $('#js-main-invoice-div').append(@showMore = $('<div/>', {
        'id': 'js-main-invoice-show-rows',
        'html': 'Показать ещё ' + @Pmax + '/' + len,
        'data-c': len
      }))
      @showMore.click(()->
        _this.addMoreRows()
      )

    addMoreRows: ()->
      text = 'Показать еще ' + @Pmax + '/' + @showMore.data().c
      Pmin = @Pmax
      @Pmin = @Pmax + 1
      @Pmax = @Pmax + @Prange
      if Number(@showMore.data().c) < @Pmax
        @Pmax = Number(@showMore.data().c)
        text = 'Показано строк ' + @Pmax + '/' + @showMore.data().c

      if Pmin == @Pmax
        return echo_message_js("На страницу уже выгружены все счета " + Pmin + " / " + @Pmax)

      for i in [@Pmin...@Pmax]
        @$el.find('tbody').append(@createRow(@options.data[i]))


      @showMore.html(text)
      # скрол к первой подгруженной строке
      t = $(window).scrollTop() + $(window).height() - 100
      $("html, body").animate({scrollTop: t}, 600)  if t > 0


# обновление сонтента в таблице без запроса
    updateRows: ()->
      @$el.find('tbody').html('')
      @init();


    getTtnRow: (row, ttn, i)->
      _this = @
      tr = $('<div/>', {
        'id': ttn.id,
        'class': 'row'
      }).data(ttn)
      # определяем номер
      if ttn.number <= 0
        number = 'запрос'
      else
        number = ttn.number
      # определяем дату
      tr.append(d = $('<div/>', {
        'class': 'defttn1 cell',
        'html': number,
        click: ()->
          t = $(this)
          # окно Запрос ТТН
          new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
            new ttnWindow(t, row, response.data, _this.options.access, ttn) if response.data != undefined
          )
      }).width(_this.defttn[0]))
      if ttn.number <= 0
        d.addClass('redTD')
      tr.append($('<div/>', {
        'class': 'defttn2 cell',
        'html': ttn.date,
        click: ()->
# окно Запрос ТТН
          t = $(this)
          new sendAjax('get_ttn', {'id': row.id}, (response)->
# создаем экземпляр окна ттн
            new ttnWindow(t, row, response.data, _this.options.access, ttn) if response.data != undefined
          )
      }).width(_this.defttn[1]))

      if (ttn.return != null && Number(ttn.return) == 1)
        check = ' checked'
      else
        check = ''

      divw = $('<div/>', {
        'class': 'defttn3 cell invoice-row--ttn--vt invoice-row--checkboxtd' + check,
# 'html':ttn.id,
        'data-id': ttn.id
        click: ()->
          if _this.options.access != 2
            $(this).prev().click()
            return false
          console.log

          # echo_message_js(ttn.id+' + '+$(this).attr('data-id'))
          if Number(ttn.return) == 0
# вставляем подтверждение
            t = $(this)
            # new modalConfirm({html:'Это действие изменит в системе дату получения подписанных документов<br>Продолжить?'},()->
            ttn.return = ++ttn.return & 1
            t.addClass('checked')
            # сохраняем значение флага
            # echo_message_js(row.ttn[i].id)
            new sendAjax 'ttn_was_returned', {id: row.ttn[i].id, val: ttn.return}
            # echo_message_js('Да, я уверен.'+row.ttn[i].id)
            # )
            console.log
          else
# ограничение на снятие
# if _this.options.access != 1
# echo_message_js('Отметку омеет право снять только администратор')
# return false
            ttn.return = ++ttn.return & 1
            $(this).removeClass('checked')
            new sendAjax 'ttn_was_returned', {id: row.ttn[i].id, val: ttn.return}
      }).width(_this.defttn[2])


      tr.append(divw).data(ttn)

      tr


  # Define the plugin
  $.fn.extend sklad: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('sklad')

      if !data
        $this.data 'sklad', (data = new sklad(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window

###
# прототип html строки прихода
###
class skladRow
  defaults:
    id: 0
    invoice_id: 0
    invoice_number: 0
    number: 0
    date: getDateNow()
    price: 0
    percent: 0
    create: getDateNow()
    buch_id: 0
    buch_name: 'Default Name'
    edit: 0
    del: 0
  enterObj: {}
  options: {}
  access: 0


  constructor: (data, access, rowspan = 1)->
    if data.edit == undefined
      data.edit = 1

    @access = access

    for key,el of data
      @options[key] = el

    @options = data


    return @init(rowspan)
  firstRow: (rowspan)->
    self = @

    tr = $('<tr/>', {
      'class': 'firstRow',
      id: 'sklad_row_' + self.options.ttn_id
    }).data(self.options);


    # номер, дата
    if self.options.doc_type == 'spec'
      self.options.spf_num = self.options.doc_num
      doc_type = 'счёт'
    else
      self.options.spf_num = 'оф'
      doc_type = 'счёт - оферта'

    td = $('<td/>', {
      'rowspan': rowspan,
      'class': 'invoice-row--fist-td',
      click: ()->
        t = $(@)

        new sendAjax('get_ttn', {'id': self.options.id}, (response)->
# создаем экземпляр окна ттн
          new invoiceWindow(t, self.options, response.data, self.access)
        )
    })
    td.append($('<div/>', {
      'class': 'invoice-row--number',
      'html': '<span>' + self.options.invoice_num + '</span>  ' + self.options.invoice_create_date

    }))

    tr.append(td)
    # заказ, менеджер
    tr.append($('<td/>', {
      'rowspan': rowspan
    }).append($('<div/>', {
      'class': 'invoice-row--order-number'
      'html': self.options.invoice_num
    }))
    .append($('<div/>', {
      'class': 'invoice-row--meneger--full-name',
      'html': self.options.manager_name
    })))

    # клиент, юрлицо
    td = $('<td/>', {
      'rowspan': rowspan
    })
    .append($('<div/>', {
      'class': 'invoice-row--client--name',
      'html': self.options.client_name
    }))
    .append($('<div/>', {
      'class': 'invoice-row--client--requsits',
      'data-id': self.options.client_requisit_id,
      'html': self.options.client_requisit_name
    }))
    tr.append(td)

    
    tr.append(self.subRow(rowspan))
    tr.append($('<td/>', {
      'rowspan': rowspan,
      'html': self.options.status,
# 'click':()->
# echo_message_js "обновление TESt = "+self.options.id

    }))


  subRow: (rowspan)->
    self = @
    # console.log 654
    tr = []


    # отгрузка
    tr.push(delivery_td = $('<td/>'))
    if self.options.delivery == 'no_delivery'
      delivery_td.html('Самовывоз')
    else
      delivery_td.html('Доставка')


    # дата отгрузки
    console.log self.options.ttn_shipment_date_color, self.options.ttn_id
    tr.push(td_date = $('<td/>', {'html': self.options.date_shipment}))
    if self.options.ttn_shipment_date_color != undefined
      td_date.css({
        'background':self.options.ttn_shipment_date_color
      })
    # № ттн
    tr.push($('<td/>', {
      'html': self.options.number,
      'class': 'mayBeClick',
      click: ()->
# окно Запрос ТТН
        t = $(this)
        new sendAjax('get_ttn', {'id': self.options.id}, (response)->
# создаем экземпляр окна ттн
          new ttnWindow(t, self.options, response.data, self.access, self.options) if response.data != undefined
        )
    }))
    # дата из ттн
    tr.push($('<td/>', {'html': self.options.ttn_date}))
    # статус ттн
    status_str = 'Отгружено'
    if Number(self.options.shipment_status) == 0
      status_str = 'Не отгружено'
    tr.push(status_shipment = $('<td/>', {
      'html': status_str,
      'class': 'mayBeClick',
#      click:()->
#        echo_message_js "смена статуса отгрузки"

    }))


    # кем выставлен статус отгрузки
    tr.push(when_ho = $('<td/>'))
    if Number(self.options.shipment_employee_id) > 0
      when_ho.html(self.options.shipment_employee + '; ' + self.options.shipment_status_last_edit)
    button2 = []
    btn1 = {
      'name': 'Отгружено',
      'class': '',
      click: (e)->
        self.options.shipment_status = 1
        status_shipment.html($(this).html())
        console.log rowspan,self.options
        new sendAjax('edit_ttn_status', {
          'id': self.options.ttn_id,
          'invoice_num':self.options.invoice_num,
          'manager_name':self.options.manager_name,
          'manager_id':self.options.manager_id,
          'client_name':self.options.client_name,
          'client_id':self.options.client_id,
          'number':self.options.number,
          shipment_status: self.options.shipment_status
        }, (response)->
          when_ho.html(response.data.when_ho)
          console.log "отправлен объект ", self.options.shipment_status
          $('#js-main-invoice-table').sklad('reflesh', self.options)
        )
    }
    btn2 = {
      'name': 'Не отгружено',
      'class': '',
      click: (e)->
        console.log rowspan,self.options
        new modalConfirm({html: 'Уверены, что неотгружено?<br>продолжить?'}, ()->
          self.options.shipment_status = 0
          status_shipment.html($(this).html())
          new sendAjax('edit_ttn_status', {
            'id': self.options.ttn_id,
            'invoice_num':self.options.invoice_num,
            'manager_name':self.options.manager_name,
            'client_name':self.options.client_name,
            'manager_id':self.options.manager_id,
            'client_id':self.options.client_id,
            'number':self.options.number,
            shipment_status: self.options.shipment_status
          }, (response)->
            when_ho.html(response.data.when_ho)

            $('#js-main-invoice-table').sklad('reflesh', self.options)
          )
        )

    }

    button2.push(btn1)
    button2.push(btn2)

    status_shipment.menuRightClick({'buttons': button2})


    #    console.log tr
    # возвращаем либо строку дибо td
    if rowspan == 0
      tr = $('<tr/>', {
        'class': 'subRow',
        id: 'sklad_row_' + self.options.ttn_id
      }).data(self.options).append(tr);
    return tr
  init: (rowspan)->
    if rowspan >= 1
      return @firstRow(rowspan)
    else
      return @subRow(rowspan)


###
# cloned the head table and fixed this head on top in user window
#
# @see       top header
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:24:30
###
$(window).scroll ()->
  if $(this).scrollTop() > $('#js-main-invoice-table').offset().top
# показать ещё
    if $(this).scrollTop() == $(document).height() - $(window).height()
      if($('#js-main-invoice-show-rows').length > 0)
        $('#js-main-invoice-show-rows').stop().animate({'bottom': '15px'}, 100)
    else
      if($('#js-main-invoice-show-rows').length > 0)
        $('#js-main-invoice-show-rows').stop().animate({'bottom': '-45px'}, 100)

    # шапка таблицы
    if($('#js-main-invoice-table-clone').length == 0)
      el_cloned = $('#js-main-invoice-table thead');
      thead = el_cloned.clone();
      thead.find('tr').each((index) ->
# console.log thead.find('tr').eq(index)
        thead.find('tr').eq(index).find('th').each((ind) ->
# console.log el_cloned.find('tr').eq(index).find('th').eq(ind).width()
          thead.find('tr').eq(index).find('th').eq(ind).width(el_cloned.find('tr').eq(index).find('th').eq(ind).width() + 1)
          return
        )
        return
      )
      div = $('<div/>', {'id': 'js-main-invoice-table-clone'})
      .append($('<table/>').append(thead))
      .css({
        'position': 'fixed',
        'top': '0',
        'left': '0',
        'width': '100%'
      })
      div.appendTo('body')
      if ($('#js-main-invoice-table').hasClass('sklad'))
        div.addClass('sklad')
    else
      $('#js-main-invoice-table-clone')
# .stop( true, true )
      .css {'display': 'block'}
    # показываем кнопку вверх
    $('#invoice-button-top').stop().animate({
      right: '15px',
      bottom: '15px',
      width: '40px',
      height: '40px',
      'opacity': 0.6
    }, 100)
  else
    if($('#js-main-invoice-table-clone').length > 0)
      $('#js-main-invoice-table-clone')
# .stop( true, true )
      .css {'display': 'none'}
    # скрываем кнопку вверх
    $('#invoice-button-top').stop().animate({
      right: '35px',
      bottom: '35px',
      width: '0px',
      height: '0px',
      'opacity': 0
    }, 100)


$(document).on 'click', '#invoice-button-top', (event) ->
  $("html, body").animate({scrollTop: 0}, 600);
