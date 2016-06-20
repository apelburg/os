###
# class Invoice
#
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   06.06.2016 11:03
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
# прототип объекта данных строки таблицы пенсий
###
class pensionTrObj
  defaults:
    id: 0
    date:     '00.00.0000'
    n_0_2:    '0.00'
    n_2_3:    '0.00'
    n_3_5:    '0.00'
    n_5_7:    '0.00'
    n_7_10:   '0.00'
    n_10_15:  '0.00'
    n_15_99:  '0.00'
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
# прототип редактируемой ячейки в таблице
###
class tdEditRow
  # type:'money'
  # money,int,date
  constructor:(val,key,type = 'int', saveFunc = ()-> true)->

    td = $('<td/>', {
      'html': val,
      'class':'mayBeEdit',

      click: ()->
        if($(this).find('input').length == 0)
#          w = $(this).width($(this).width())
          $(this).html(input = $('<input/>', {
            'type': 'text',
            'val': $(this).html(),
            keyup:(e)->
              # выход из режима редактирования на ENTER
              if (e.keyCode == 13)
                $(this).blur()

            change: ()->
              data = $(this).parent().data()
              data[key] =  $(this).val()
              $(this).parent().data(data)

            # выделение не пустого значения
            focus: ()->
              if(Number($(this).val()) == 0)
                # если 0.00 подм  еняем на пусто
                $(this).val('')
              else
                # выделение
                t = $(this)
                setTimeout(()->
                  t.select()
                , 50)
          }))


          if type == 'date'
            input.addClass('date')

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

          $(this).addClass('tdInputHere')
          input.css('textAlign', $(this).css('textAlign')).focus().blur(()->
            # получаем значения по строке таблицы
            data = $(this).parent().parent().data()
            # форматируем НОЛЬ для чисел
            if (Number($(this).val()) == 0 || isNaN($(this).val()) && type == 'int')
              $(this).val(0)
            
            # форматируем Денежный формат
            if (type == 'money')
              if isNaN($(this).val())
                $(this).val(data[key])
              else
                $(this).val(round_money(Number($(this).val())))


            # проверка на изменения данных
            if (data[key] !=  $(this).val())
              # обновляем данные в DOM
              data[key] =  $(this).val()
              $(this).parent().parent().data(data)
              # запрос на обновление данных на сервере
              saveFunc(key,data)

            # правим html -возврат в сисходное состояние
            $(this).parent().removeClass('tdInputHere')
            $(this).replaceWith(data[key])
          )
    })

    if type == 'date'
      td.addClass('date')
    return td
###
# таблица пенсии
###
class createPensionTbl
  constructor:(data)->
    tbl = $('<table/>',{id:'js-options-tbl'});
    tbl.append(@penciaTrHead())


    for n,i in data
      tbl.append(@penciaTrSimple(new pensionTrObj(data[i])))
    tbl.append(@penciaTrFooter())


    tblCase = $('<div/>').css({'width':"1020px"})
#    tblCase.append(tr = $('<tr/>'))
#    tr.append($('<td>',{html:tbl,css:{'width':'1000px','padding':'0'}})).append($('<td>'))

    return tblCase.append(tbl)


  penciaTrFooter:()->
    self = @

    tr = $('<tr/>',{
      class:'footer'
    })
    tr.append($('<td/>'))
    for num in [10..1]
      tr.append($('<td/>',{
        class:'mayBeEdit',
        click:()->
          t = $(this)
          new sendAjax('create_pension_row',{},(response)->
              obj = self.penciaTrSimple(new pensionTrObj(response.data))
              tr.before(obj)
              obj.find('td').eq( t.index() ).click()
          )
          return
      }))

    tr.append($('<td/>'))
    return tr

  penciaTrHead:()->
    tr = $('<tr/>',{class:'head'})
    tr.append($('<th/>'))
    tr.append($('<th/>',{
      html:'Исп'
    }))
    px = 86
    tr.append($('<th/>',{
      html:'действует с:',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'0 - 2',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'2 - 3',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'3 - 5',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'5 - 7',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'7 - 10',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'10 - 15',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'15 - 99',
#      css:{'width':px}
    }))
    tr.append($('<th/>'))
    return tr

  penciaTrSimple:(data)->
    tr = $('<tr/>',{class:'body'})
    if Number(data.checked) > 0
      tr.addClass('checked')

    tr.data(data)
    tr.append($('<td/>'))
    tr.append($('<td/>',{
      click:()->
        prevCheckData = []
        prevCheckData.id =0
        # изменяем предыдущий выбор на новый
        tr.parent().find('tr.checked').each ()->
          prevCheckTr = $(this)
          prevCheckData = prevCheckTr.data()
          prevCheckData.checked = 0
          prevCheckTr.data(prevCheckData).removeClass('checked')

        curData = tr.data()
        curData.checked = 1
        tr.data(curData).addClass('checked')

        new sendAjax('check_other_pension_row',{prev_id:prevCheckData.id, new_id :curData.id})

    }))
    tr.append(new tdEditRow(data.date,'date','date',@saveFunc))
    tr.append(new tdEditRow(data.n_0_2,'n_0_2','money',@saveFunc))
    tr.append(new tdEditRow(data.n_2_3,'n_2_3','money',@saveFunc))
    tr.append(new tdEditRow(data.n_3_5,'n_3_5','money',@saveFunc))
    tr.append(new tdEditRow(data.n_5_7,'n_5_7','money',@saveFunc))
    tr.append(new tdEditRow(data.n_7_10,'n_7_10','money',@saveFunc))
    tr.append(new tdEditRow(data.n_10_15,'n_10_15','money',@saveFunc))
    tr.append(new tdEditRow(data.n_15_99,'n_15_99','money',@saveFunc))

    # удаление строки
    tr.append($('<td/>',{
      class:"delete_row",
      click:()->
        if Number(tr.data().checked) > 0
          echo_message_js "Нельзя удалить выбранную строку.",'error_message'
          return false
        new modalConfirm({html: 'Вы уверены, что хотите удалить данную строку?'}, ()->
          new sendAjax('delete_pension_row',{id:data.id},()->
            tr.remove()
          )
        )
    }))
    return tr
  saveFunc:(key,allData)->
    new sendAjax('savePensionData',{id:allData.id,key:key,val:allData[key]},()->)


###
# прототип объекта данных строки таблицы мен рекламщики
###
class zpMenRekTrObj
  defaults:
    id:           0
    date:         '00.00.0000'
    profit_start: '0.00'
    profit_end:   '0.00'
    salary:       '0.00'
    premium:      '0.00'
    return:       '0.00'
    premium2:      '0.00'
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
# таблица зарплат мен рекламщики
###
class createZpMenRekTbl
  width:900

  constructor:(data)->
    tbl = $('<table/>',{id:'js-options-tbl','class':'zp_men_rek'});
    tbl.append(@trHead())


    for n,i in data
      tbl.append(@trSimple(new zpMenRekTrObj(data[i])))
    tbl.append(@trFooter())


    tblCase = $('<div/>').css({'width':@width})

    return tblCase.append(tbl)


  trFooter:()->
    self = @

    tr = $('<tr/>',{
      class:'footer'
    })
    for num in [6..1]
      tr.append($('<td/>',{
        class:'mayBeEdit',
        click:()->
          t = $(this)
          new sendAjax('create_men_zp_rec_row',{},(response)->
            obj = self.trSimple(new zpMenRekTrObj(response.data))
            tr.before(obj)
            obj.find('td').eq( t.index() ).click()
          )
          return
      }))

    tr.append($('<td/>'))
    return tr

  trHead:()->
    tr = $('<tr/>',{class:'head'})
    px = 250
    tr.append($('<th/>',{
      html:'прибыль от',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'прибыль до',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'оклдад',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'премия(%)',
    }))
    tr.append($('<th/>',{
      html:'оборот',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'премия(%)',
    }))
    tr.append($('<th/>'))
    return tr

  trSimple:(data)->
    tr = $('<tr/>',{class:'body'})
    if Number(data.checked) > 0
      tr.addClass('checked')

    tr.data(data)


    tr.append(new tdEditRow(data.profit_start,'profit_start','money',@saveFunc))
    tr.append(new tdEditRow(data.profit_end,'profit_end','money',@saveFunc))
    tr.append(new tdEditRow(data.salary,'salary','money',@saveFunc))
    tr.append(new tdEditRow(data.premium,'premium','money',@saveFunc))
    tr.append(new tdEditRow(data.return,'return','money',@saveFunc))
    tr.append(new tdEditRow(data.premium,'premium2','money',@saveFunc))

    # удаление строки
    tr.append($('<td/>',{
      class:"delete_row",
      click:()->
        if Number(tr.data().checked) > 0
          echo_message_js "Нельзя удалить выбранную строку.",'error_message'
          return false
        new modalConfirm({html: 'Вы уверены, что хотите удалить данную строку?'}, ()->
          new sendAjax('delete_zp_men_rek_row',{id:data.id},()->
            tr.remove()
          )
        )
    }))
    return tr
  saveFunc:(key,allData)->
    new sendAjax('saveRecData',{id:allData.id,key:key,val:allData[key]},()->)

###
# таблица зарплат мен конечники
###
class createZpMenKonTbl
  width:900

  constructor:(data)->
    tbl = $('<table/>',{id:'js-options-tbl','class':'zp_men_kon'});
    tbl.append(@trHead())


    for n,i in data
      tbl.append(@trSimple(new zpMenRekTrObj(data[i])))
    tbl.append(@trFooter())


    tblCase = $('<div/>').css({'width':@width})

    return tblCase.append(tbl)


  trFooter:()->
    self = @

    tr = $('<tr/>',{
      class:'footer'
    })
    for num in [4..1]
      tr.append($('<td/>',{
        class:'mayBeEdit',
        click:()->
          t = $(this)
          new sendAjax('create_men_zp_kon_row',{},(response)->
            obj = self.trSimple(new zpMenRekTrObj(response.data))
            tr.before(obj)
            obj.find('td').eq( t.index() ).click()
          )
          return
      }))

    tr.append($('<td/>'))
    return tr

  trHead:()->
    tr = $('<tr/>',{class:'head'})
    px = 200
    tr.append($('<th/>',{
      html:'прибыль от',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'прибыль до',
      css:{'width':px}
    }))
    tr.append($('<th/>',{
      html:'оклдад',
      css:{'width':px}
    }))
#    tr.append($('<th/>',{
#      html:'оборот',
#      css:{'width':px}
#    }))
    tr.append($('<th/>',{
      html:'премия(%)',
    }))
    tr.append($('<th/>'))
    return tr

  trSimple:(data)->
    tr = $('<tr/>',{class:'body'})
    if Number(data.checked) > 0
      tr.addClass('checked')

    tr.data(data)


    tr.append(new tdEditRow(data.profit_start,'profit_start','money',@saveFunc))
    tr.append(new tdEditRow(data.profit_end,'profit_end','money',@saveFunc))
    tr.append(new tdEditRow(data.salary,'salary','money',@saveFunc))
    tr.append(new tdEditRow(data.premium,'premium','money',@saveFunc))
#    tr.append(new tdEditRow(data.return,'return','money',@saveFunc))

    # удаление строки
    tr.append($('<td/>',{
      class:"delete_row",
      click:()->
        if Number(tr.data().checked) > 0
          echo_message_js "Нельзя удалить выбранную строку.",'error_message'
          return false
        new modalConfirm({html: 'Вы уверены, что хотите удалить данную строку?'}, ()->
          new sendAjax('delete_zp_men_kon_row',{id:data.id},()->
            tr.remove()
          )
        )
    }))
    return tr
  saveFunc:(key,allData)->
    new sendAjax('saveKonData',{id:allData.id,key:key,val:allData[key]},()->)




###
# вкладка настройки
###
(($, window) ->
  class accountingOptions
    defaults:
      start: false

    # меню 2
    tabs: [{
      index:0,
      name_en:'options'
      name:'Настройки'
    }]


    constructor: (el, options) ->
      @$el = $(el)
      self = @
      @tabs2level = [{
        index:0,
        name_en:'konechniki'
        name:'Конечники',
        click:()->
          new sendAjax('get_zp_kon_data',{options:'all_data'},(response)->
            self.constructMainContent(new createZpMenKonTbl(response.data))
          )
      },{
        index:1,
        name_en:'reklamshchiki'
        name:'Рекламщики',
        click:()->
          new sendAjax('get_zp_rek_data',{options:'all_data'},(response)->
            self.constructMainContent(new createZpMenRekTbl(response.data))
          )
      },{
        index:2,
        name_en:'pensiya'
        name:'Пенсия',
        click:()->
          new sendAjax('get_pension_tbl_data',{options:'all_data'},(response)->
            self.constructMainContent(new createPensionTbl(response.data))
          )
      }]

      @body = $(el).find('#js-main-accounting-div')

      @body.html('')
      ###
      # добавление меню
      ###
      @addMenu()

    constructMainContent:(content)->
#      echo_message_js "сборка ОПЦИИ",'error_message', 100
      if @body.find('#js-accounting-main-content-container').length > 0
        @$el.find('#js-accounting-main-content-container').remove()
      @body.append(div = $('<div/>',{'id':'js-accounting-main-content-container',html:''}))
      div.append(content)
      
    click:()->
      @mainTabHtml.click()


    addMenu: ()->

      self = @
      if @$el.find('#js-general-accounting-menu').length > 0
        ul = @$el.find('#js-general-accounting-menu ul')

      else
        ul = $('<ul/>',{'class':'central_menu'})
        @$el.prepend($('<div/>',{
          'id':'js-general-accounting-menu',
          'class':'cabinet_top_menu first_line',
          html:ul
        }))

      section = Number($.urlVar('section'))
      for n,i in @tabs
        ul.append(@mainTabHtml =  new mainMenuTab(n,section,ul,'section',()->
          self.addMenu2()
          $.delUrlVal('manager_id')
          $.delUrlVal('month_number')
          $.delUrlVal('year')
        ))

    addMenu2: ()->
      @body.html('')
      if @$el.find('#js-accounting-menu').length > 0
        ul = @$el.find('#js-accounting-menu ul')
        ul.html('')
      else
        ul = $('<ul/>',{'class':'central_menu'})
        ul.html('')
        @body.append($('<div/>',{
          'id':'js-accounting-menu',
          'class':'cabinet_top_menu',
          css:{
            'background':'#92b73e'
          },
          html:ul
        }))

      subsection = Number($.urlVar('subsection'))
      num = 0
      for n,i in @tabs2level
        tab = new mainMenuTab(n,subsection,ul,'subsection')
        ul.append(tab)
        if num == 0
          tab1 = tab
          num++
        if num == 0 && subsection == undefined
          tab1 = tab
          num++
        else if subsection != undefined
          console.log subsection, tab.data().index
          if Number(subsection) == Number(tab.data().index)
            tab1 = tab
      # выбор первого пункта
      tab1.click()



  $.fn.extend accountingOptions: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('accountingOptions')

      if !data
        $this.data 'accountingOptions', (data = new accountingOptions(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window


class accrualsObj
  constructor: (data = {})->
    defaults = [
      {
        i:1
        id:0
        money:'0.00'
        flag_r:0,
        r:'0.00'
      },{
        i:2
        id:0
        money:'0.00'
        flag_r:0,
        r:'0.00'
      },{
        i:3
        id:0
        money:'0.00'
        flag_r:0,
        r:'0.00'
      },{
        i:4
        id:0
        money:'0.00'
        flag_r:1,
        r:'0.00'
      }
    ]
    options = []
    for key,el of data
      defaults[key] = el


    return defaults
###
# вкладка учёт -> таблица начислений
###
class accruals_tbl
  width:300
  paddingBlock:6
  accruals_summ: 0

  constructor:(data,dopData = {})->
    @pribl = 0
    @dopOptions = dopData

    # сборка таблицы таблицы
    @tbl = $('<table/>',{id:'js-accruals-tbl','class':'accounting-tbl'});
    # сборка шапки
    @tbl.append(@trHead(data))
    
    # сборка основного блока начислений
    @tbl.append(@trBody(data))

    # сборка блока ежемесячных компенсаций
    # @tbl.append(@trOffset(data))

    # сборка блока дополнительных компенсаций
    # @tbl.append(@trOffsetDop(data))

    tblCase = $('<div/>').css({
      'float':'left'
      'width':@width
      'paddingRight':@paddingBlock
      'paddingBottom':@paddingBlock
    })
    @calcTbl()


    return tblCase.append(@tbl)

  # сборка блока ежемесячных компенсаций
  trOffset:()->
    html = []

  # сборка блока дополнительных компенсаций
  trOffsetDop:()->
    html = []

  # Шапка
  trHead:(data)->
    self = @
    @recalc_button = $('<button/>',{
      html:'',
      click:()->
        # пересчёт выгруженных данных
        new sendAjax("calculate_and_update_accruals_tbl",{id:data[0].id},()->
          self.calcTbl()
        )

        echo_message_js "отправка запроса на пересчёт всего блока"
    })

    tr = $('<tr/>',{class:'head'})
    tr.append($('<th/>',{html:'Начисления'}))
    tr.append(@accruals_summ = $('<th/>',{html:round_money(@pribl)}))
    tr.append($('<th/>',{html:@recalc_button}))
    tr.append($('<th/>',{html:''}))

  trBody:(data)->
    rows = []
    for i in [0..(data.length - 1)]
      rows.push(new row(data[i],@))

    rows




  trFooter:()->
    []
  # пересчёт выгруженных данных
  calcTbl:()->

    self = @
    pribl = 0
    @tbl.find('.body').each(()->
      data = $(this).data()
      if data.flag_r > 0 # если ручной режим
        pribl = Number(data.r) + pribl
      else
        pribl = Number(data.money) + pribl
    )
    self.accruals_summ.html(round_money(pribl))


class row
  constructor:(n,parentObj)->
    tr = $('<tr/>',{class:'body'}).data(n)
    if Number(n.i) == 1
      col = 'salary_r_fl'
      col1 = 'salary_r'
      tr.append($('<td/>',{html:'Оклад'}))
    if Number(n.i) == 2
#      n.type = ''
      col = 'premium_r_fl'
      col1 = 'premium_r'
      tr.append($('<td/>',{html:'Премия'}))
    if Number(n.i) == 3
      col = 'pension_r_fl'
      col1 = 'pension_r'
      tr.append($('<td/>',{html:'Пенсия'}))
    if Number(n.i) == 4
      col = 'none'
      col1 = 'bonus'
      tr.append($('<td/>',{html:'Бонус'}))


    if Number(n.i) == 4
      tr.append($('<td/>',{html:$('<input/>',{
        val:round_money(n.money)
        class:'mone'
        blur:()->
          t = $(this)
          if Number(n.id) != 0
            n.flag_r = 0
            n.money = Number(t.val())
            tr.data(n)
            parentObj.calcTbl()
            new sendAjax('save_accruals_val',{id:n.id,key:col1,val:n.money})
          else

            new sendAjax('create_new_accruals_calc',{
              key:col1,
              val:Number(t.val())
            },(response)->
              tr.parent().parent().replaceWith(new accruals_tbl(new accrualsObj(response.data.accruals), parentObj.dopOptions ))
            )
#
      })}))
      tr.append($('<td/>'))

    # вычисляем столбец
    else if Number(n.flag_r) > 0
      tr.append($('<td/>',{html:$('<input/>',{
        val:round_money(n.r)
        blur:()->
          if Number(n.id) != 0
            n.r = round_money(Number($(this).val()))
            tr.data(n)
            parentObj.calcTbl()
            $(this).val(n.r)
            new sendAjax('save_accruals_val',{id:n.id,key:col1,val:n.r})
          else
            new sendAjax('create_new_accruals_calc',{
              key:col1,
              val:Number(t.val())
            },(response)->
              tr.parent().parent().replaceWith(new accruals_tbl(new accrualsObj(response.data.accruals), parentObj.dopOptions ))
            )
      })}))
      tr.append($('<td/>',{html:$('<button/>',{
        html:'Р',
        class:'hand',
        click:()->
          if Number(n.id) != 0
            n.flag_r = 0

            new sendAjax('save_accruals_val',{
              id:n.id,
              key:col,val:n.flag_r
            })
            tr.replaceWith(new row(n,parentObj))
            parentObj.calcTbl()
          else
            new sendAjax('create_new_accruals_calc',{
              key:col,val:n.flag_r
            },(response)->
              tr.parent().parent().replaceWith(new accruals_tbl(new accrualsObj(response.data.accruals), parentObj.dopOptions ))
            )
      })}))
    else
      tr.append($('<td/>',{html:round_money(n.money)}))
      tr.append($('<td/>',{html:$('<button/>',{
        html:'А',
        click:()->
          n.flag_r = 1
          if Number(n.id) != 0
            new sendAjax('save_accruals_val',{
              id:n.id,
              key:col,
              val:n.flag_r
            })
            tr.replaceWith(new row(n,parentObj))
            parentObj.calcTbl()
          else
            new sendAjax('create_new_accruals_calc',{
              key:col,
              val:n.flag_r
            },(response)->
              tr.parent().parent().replaceWith(new accruals_tbl(new accrualsObj(response.data.accruals), parentObj.dopOptions ))
            )
      })}))

    tr.append($('<td/>'))
    return tr

###
# вкладка учёт -> таблица выплат
###
class payments_tbl
  paddingBlock:6
  accruals_summ: 0
  width:300

  constructor:(data = {})->
# сборка таблицы таблицы
    @tbl = $('<table/>',{id:'js-payments-tbl','class':'accounting-tbl'});
    # сборка шапки
    @tbl.append(@trHead())

    tblCase = $('<div/>').css({
      'float':'left'
      'width':@width
      'paddingRight':@paddingBlock
      'paddingBottom':@paddingBlock
    })

    return tblCase.append(@tbl)

# Шапка
  trHead:()->
    self = @
    @recalc_button = $('<button>',{
      html:'в кредит ->',
      click:()->
        self.calcTbl()
    })

    tr = $('<tr/>',{class:'head'})
    tr.append($('<th/>',{html:'Выплаты'}))
    tr.append(@accruals_summ = $('<th/>',{html:round_money(21000)}))
    tr.append($('<th/>',{html:@recalc_button}))
    tr.append($('<th/>',{html:''}))

  trFooter:()->
    []
  calcTbl:()->
    []


###
# вкладка учёт -> таблица кредит
###
class credit_tbl
  paddingBlock: 6
  accruals_summ: 0
  with:300

  constructor:(data = {})->
# сборка таблицы таблицы
    @tbl = $('<table/>',{id:'js-credit-tbl','class':'accounting-tbl'});
    # сборка шапки
    @tbl.append(@trHead())

    tblCase = $('<div/>').css({
      'float':'left',
      'paddingRight':@paddingBlock
      'paddingBottom':@paddingBlock
      'width':@with
    })

    return tblCase.append(@tbl)

# Шапка
  trHead:()->
    self = @
    @recalc_button = $('<button>',{
      html:'Расчёт',
      click:()->
        self.calcTbl()
    })

    tr = $('<tr/>',{class:'head'})
    tr.append($('<th/>',{html:'Кредит'}))
    tr.append(@accruals_summ = $('<th/>',{html:round_money(3000)}))
    tr.append($('<th/>',{html:@recalc_button}))
    tr.append($('<th/>',{html:''}))

  trFooter:()->
    []
  calcTbl:()->
    []




###
# вкладка учёт -> таблица закрытых счетов (за указанный месяц и год)
###
class create_bill_tbl
  width:405
  paddingBlock:6
  itogo:
    percent: 0
    price_out_payment: 0
    profit: 0

  constructor:(data = {})->
    # сборка таблицы таблицы
    tbl = $('<table/>',{id:'js-bill-tbl','class':'bill_tbl'});
    # сборка шапки
    tbl.append(head = @trHead())
    # добавляем в таблицу строки закрытых счетов
    tbl.append(@trBody(data))

    head.after(@trItogo())
    tblCase = $('<div/>').css({
      'width':@width
      'float': 'left',
      'paddingRight': @paddingBlock,
      'paddingBottom': @paddingBlock
    })


    return tblCase.append(tbl).data(@itogo)

  trFooter:()->
    true
  trItogo:()->
    tr = $('<tr/>',{class:'itog'})
    tr.append($('<td/>'))
#    tr.append($('<td/>'))
    tr.append($('<td/>',{html:round_money(@itogo.price_out_payment)}))
    tr.append($('<td/>',{html:round_money(@itogo.profit)}))
    tr.append($('<td/>',{html:round_money(@itogo.percent)+'%'}))
    return tr

  trHead:()->
    tr = $('<tr/>',{class:'head'})
    tr.append($('<th/>',{html:'№счёта, дата'}))
#    tr.append($('<th/>',{html:'заказа'}))
    tr.append($('<th/>',{html:'выручка'}))
    tr.append($('<th/>',{html:'прибыль'}))
    tr.append($('<th/>',{html:'%'}))
    return tr

  trBody:(data)->
    @itogo.percent  = 0
    @itogo.profit   = 0
    @itogo.price_out_payment = 0

    arr = []
    num = 1
    for n,i in data
      arr.push(new billTrPrototipe(n))

      @itogo.percent += Number(n.pr)
      @itogo.profit += Number(n.profit)
      @itogo.price_out_payment += Number(n.price_out_payment)

      num++

    @itogo.percent = @itogo.percent/num

    arr


###
# вкладка учёт -> строка таблицы закрытых счетов
###
class billTrPrototipe
  constructor:(data)->

    tr = $('<tr/>').data(data)
    tr.append($('<td/>',{html:data.invoice_num}).append($('<span/>',{class:'row_invoice_date',html:data.closed_date})))
    tr.append($('<td/>',{html:round_money(data.price_out_payment)}))
    tr.append($('<td/>',{html:round_money(data.profit)}))
    tr.append($('<td/>',{html:data.pr+'%'}))
    return tr


###
# вкладка учёт
###
(($, window) ->
  class accountingCalculation
    defaults:
      start: false

    tabs: [{
      index:1,
      name_en:'options'
      name:'Учёт'
    }]

    constructor: (el, options) ->
      @$el = $(el)
      @body = $(el).find('#js-main-accounting-div')
      @body.html('')
      ###
      # добавление меню
      ###
      @addMenu()

    addMenu: ()->
      self = @
      if @$el.find('#js-general-accounting-menu').length > 0
        ul = @$el.find('#js-general-accounting-menu ul')
      else
        ul = $('<ul/>',{'class':'central_menu'})
        @$el.prepend($('<div/>',{
          'id':'js-general-accounting-menu',
          'class':'cabinet_top_menu first_line',
          html:ul
        }))

      section = Number($.urlVar('section'))
      for n,i in @tabs
        ul.append(@mainTabHtml = new mainMenuTab(n,section,ul,'section',()->

          self.addMenu2()
          $.delUrlVal('subsection')
          self.addMenu3()
        ))

    addMenu2: ()->
      @body.html('')
      self = @
      if @$el.find('#js-accounting-menu-managers').length > 0
        ul = @$el.find('#js-accounting-menu-managers ul')
        ul.css('float':'left')
        ul.html('')
      else
        ul = $('<ul/>',{'class':'central_menu'}).css('float':'left')
        @body.append($('<div/>',{
          'id':'js-accounting-menu-managers',
          'class':'cabinet_top_menu first_line',
          html:ul
        }))

      manager_id = $.urlVar('manager_id')
      num = 0
      new sendAjax('get_managers_tabs',{},(response)->

        for n,i in response.data
          tab = new mainMenuTab(n,manager_id,ul,'manager_id',()->
            self.constructMainContent()
          )
          ul.append(tab)
          if num == 0 && manager_id == undefined
            tab1 = tab
            num++
          else if manager_id != undefined

            if Number(manager_id) == Number(tab.data().index)
              tab1 = tab
        # выбор первого пункта
        tab1.click()
      )
    addMenu3: ()->
      self = @
      if @body.find('#js-accounting-month-menu').length > 0
        ul = @$el.find('#js-accounting-month-menu ul')
        ul.css('float':'left')
        ul.html('')
      else
        ul = $('<ul/>',{'class':'central_menu'}).css('float':'left')
        @body.append($('<div/>',{
          'id':'js-accounting-month-menu',
          'class':'cabinet_top_menu first_line',
          html:ul
        }))

      month = [
        {
          index:1
          name:'Январь'
        },{
          index:2
          name:'Февраль'
        },{
          index:3
          name:'Март'
        },{
          index:4
          name:'Апрель'
        },{
          index:5
          name:'Май'
        },{
          index:6
          name:'Июнь'
        },{
          index:7
          name:'Июль'
        },{
          index:8
          name:'Август'
        },{
          index:9
          name:'Сентябрь'
        },{
          index:10
          name:'Октябрь'
        },{
          index:11
          name:'Ноябрь'
        },{
          index:12
          name:'Декабрь'
        }
      ]

      @yearTab()

      month_now = new Date().getMonth()

      month_number = $.urlVar('month_number')
      num = 0
      for n,i in month
        tab = new mainMenuTab(n,month_number,ul,'month_number',()->
          self.constructMainContent()
        )
        ul.append(tab)

        if month_number == undefined && month_now == Number(tab.data().index)
          tab1 = tab
        else if month_number != undefined
          if Number(month_number) == Number(tab.data().index)
            tab1 = tab

      # выбор первого пункта
      tab1.click()


      # добавляем поле год
      ul.append(@yearTab())


    yearTab:()->
      self = @
      year = new Date().getFullYear()
      l = year + 3
      oldYear = $.urlVar('year')

      if oldYear == undefined
        $.urlVar('year',year)
        oldYear = year


      option_obj = (num for num in [2015..l])


      li = $('<li/>')
      li.append(select = $('<select/>',{
        change:()->
          $.urlVar('year',$(this).val())
          self.constructMainContent()
      }))

      for Y,i in option_obj
        select.append(opt = $('<option/>', {
          html: Y,
          val: Y
        }))

        opt.attr('selected', 'true') if Number(Y) == Number(oldYear)

      return li


    constructMainContent:()->
      self = @
      content = $('<div/>',{
        id:'first_tab'
      })


      # первый блок
      new sendAjax "get_data",{},(response)->
        content.append(self.bill_tbl =  new create_bill_tbl(response.data.bill_closed))

        content.append( new accruals_tbl(new accrualsObj(response.data.accruals), self.bill_tbl.data()) )

        content.append( new payments_tbl(response.data) )

        content.append( new credit_tbl(response.data) )


#      echo_message_js "сборка УЧЁТ",'error_message', 100
      if @body.find('#js-accounting-main-content-container').length > 0
        @$el.find('#js-accounting-main-content-container').remove()

      @body.append(div = $('<div/>',{'id':'js-accounting-main-content-container',html:''}))
      div.append(content)
        



  # Define the plugin
  $.fn.extend accountingCalculation: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('accountingCalculation')

      if !data
        $this.data 'accountingCalculation', (data = new accountingCalculation(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window


###
# прототип html пункта главного меню
###
class mainMenuTab
  constructor: (tab, section,menu,key,func = ()-> true)->
    li = $('<li/>', {
      click: (e)->
        # меняем URL
        $.urlVar(key, tab.index)
        # удаляем выделение со старого выбранного элемента
        menu.find('.selected').removeClass('selected')

        # выделяем текущий элемент
        $(this).addClass('selected')
        # выполняем функцию ... если она была передана
        if tab.click != undefined
          tab.click()
        func()
    })
    li.append(span = $('<span/>'))
    span.append($('<div/>', {'class': 'border', 'html': tab.name}))
    li.data(tab)
    if tab.index == section
      li.addClass('selected')
    return li