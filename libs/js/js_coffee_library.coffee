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
window.getDateNow = () ->
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
# Проверяет, присутствует ли в массиве значение
# @var value  - значение
# @var array - массив, в котором осуществляется поиск
#
# @return bool - возвращает false или true
###
window.in_array = (value, array) ->
  for n,i in array
    if(value == array[i])
      return true;
  return false;

###
# возвращяет текущую дату в читабельном формате
###
window.getDateTomorrow = () ->
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
window.round_money = (i_num) ->
  o_num = Number(i_num);
#  new_num = Math.round((num) * 100) / 100;
  return o_num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");

###
# возвращает маркер приблизительного значения
###
window.markApproximateVal = () ->
  $('<span/>',{
    html:'≈',
    css:{
      'color':'red',
      'paddingRight':'5px'
    }
  })

###
# преобразует проценты в соответствии с принятой конвенцией к виду 0.00
###
window.round_percent = (i_num) ->

  o_num = Number(i_num);

  if i_num < 0.01 && i_num > 0
    o_num = 0.01

  o_num = Math.floor(o_num*100)/100
  
  span = $('<span/>',{
    html:o_num
  }).data({
    percent : i_num
  })


  console.log " )))))))))))) -> ", i_num, o_num
  if Number(o_num) != Number(i_num)
    span.prepend(markApproximateVal())
  return span
###
# расчёт % оплаты счёта
###
window.calculatePercentPart = ( numberAll , numberPart) ->
    percent = (Number(numberPart) * 100 / Number(numberAll))
    percent = percent.toFixed(7)
#    percent = (Number(numberAll) / (Number(numberPart)/100))
    # проверка на деление на ноль ( чтобы не выводилось NaN )
    percent = 0 if Number(numberPart) == 0 || Number(numberAll) == 0
    return percent
###
# вырезаем символы недоступные в денежном формате
#
# изначально предназначена как обработчик на keyup
###
window.deleteNotMoneySymbols = ( value ) ->
  value.replace(/[/,]/gim, '.').replace(/[^-0-9/.]/gim, '').replace( /^([^\.]*\.)|\./g, '$1' )

###
# перевод строки в денежном формате в число
###
window.moneyString2Number = ( value ) ->
  Number(value)
  #  Number(value.replace(/[/,]/gim, '.').replace(/[^-0-9/.]/gim, '').replace( /^([^\.]*\.)|\./g, '$1' ))

###
# подсчет скидки
# @param      price_out - входящая цена
# @discount   discount - скидка
###
window.calc_price_with_discount = (price_out, discount) ->
  return (Number(price_out / 100) * (100 + Number(discount))).toFixed(2);


###
# транслитерация
###
window.cyrill_to_latin = (text)->
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
class window.sendAjax
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
class window.modalConfirm
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
class window.modalWindow
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
# прототип окна сообщения
###
class window.sendMessage
  defaults:
    ajax:'send_error_message'
    windowName:'Отправкить сообщение'
    message:''
  MessageMinLen:1
  options:{}

  constructor: (options = {},func = ()->) ->
    @options = {}

    @options = $.extend({}, @defaults, options)
    # запоминаем уровень допуска
    console.log @options


    @init(func)

# собираем окно счёт
  init: (func)->
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

    @main_div.append(@main_form = @getForm())
    ###
    # создание окна
    ###
    @myObj = new modalWindow({
      html: @main_div,
      maxHeight: '100%',
      width: '800px',
      title: @options.windowName,
      buttons: @getButtons(func),
    }, {
      closeOnEscape: true,
      single: true
    })
    @$el = @myObj.options.html[0]
    $(@$el).parent().css('padding', '0')

  getForm: ()->
    self = @
    main = $('<div/>', {'class': 'comment table'})

    #column 1
    main.append(tr = $('<div/>', {'class': 'row'}))

    cell2 = $('<div/>', {'class': 'cell comment_text'})
    cell2.append(textarea = $('<textarea/>', {
      'name': 'comment_text',
      val: @options.message,
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

  getButtons: (func = ()->)->
    self = @
    @saveObj = {}
    buttons = []

    buttons.push(
      text: 'Закрыть',
      class: 'button_yes_or_no no',
      click: ()->
        self.destroy()
    )
    dop_class = ' no'
    dop_class = ' yes' if @options.message.length > self.MessageMinLen
    buttons.push(
      text: 'Отправить',
      class: 'button_yes_or_no'+dop_class,
      id: 'js--send_comment'
      click: ()->
        comment = self.main_form.find('textarea').val()
        if comment.length <= self.MessageMinLen
          echo_message_js "Сообщение должно быть не короче "+self.MessageMinLen+" символов"
        else
          func()
          new sendAjax(self.options.ajax ,{ message:comment},()->
            self.destroy()
          )
    )
    return buttons

  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()


###
# прототип окна сбора статистики
# costsWindow
###
class window.getStatisticForm
  defaults:
#    ajax:'statistics_collect'
    windowName:'Сбор статистики'
    dialogMessage:'Пожалуйста укажите причину ваших действий'
    message:' '
  MessageMinLen:2
  options:{}

  constructor: (statName = 'default',options = {},
    trueFunc = ()->,
    falseFunc=()->) ->
    @statName = statName
    @options = {}

    self = @
    new sendAjax('get_stats_questions',{name:@statName},(response)->
      self.statData = response.data.stats


      self.options = $.extend({}, self.defaults, options)
      # запоминаем уровень допуска


      self.init(trueFunc,falseFunc)
    )
  getStatisticFrom:()->


    @tatisticFrom = $('<div/>')

    for row in @statData

      @tatisticFrom.append(@statRow(row))

    @tatisticFrom

  statRow:(data)->
    console.log data
    self = @
    html = $('<div/>')

    html.append(inp = $('<input/>',{
      type:'checkbox'
    }).data(data))
    html.append($('<label/>',{
      html:data.name,
      click:()->
        inp.click()
        self.validate()
    }))

    html


  # собираем окно счёт
  init: (trueFunc,falseFunc)->
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
    @main_div.append( $('<div/>',{
      html:@options.dialogMessage
      css:{
        'padding':'5px 5px 10px 5px'
      }
    }))
    #блок статистикаи
    @main_div.append(@getStatisticFrom())

    # блок сообщения
    @main_div.append(@main_form = @getForm())
    ###
    # создание окна
    ###
    @myObj = new modalWindow({
      html: @main_div,
      maxHeight: '100%',
      width: '800px',
      title: @options.windowName,
      buttons: @getButtons(trueFunc,falseFunc),
    }, {
      closeOnEscape: true,
      single: true
    })
    @$el = @myObj.options.html[0]
    $(@$el).parent().css('padding', '0')

  validate:()->
    if @checkCheckbox() && @checkText()
      $(@myObj.buttonDiv).find("#js--send_comment").removeClass('no')

    else
      $(@myObj.buttonDiv).find("#js--send_comment").addClass('no')



  checkCheckbox:()->
    if @tatisticFrom.find('input[type="checkbox"]:checked').length > 0
      return true
    else
      return false
  checkText:()->
    if @textarea.val().length > @MessageMinLen
      return true
    else
      return false


  getForm: ()->
    self = @
    main = $('<div/>', {'class': 'comment table'})

    #column 1
    main.append(tr = $('<div/>', {'class': 'row'}))

    cell2 = $('<div/>', {'class': 'cell comment_text'})
    cell2.append(@textarea = $('<textarea/>', {
      'name': 'comment_text',
      val: @options.message,
      keyup:()->
        self.validate()
    }))
    tr.append(cell2)
    $('<div/>',{'class':'add_new_comment'}).append(main)

  getStatistic:()->
    arr = []
    @tatisticFrom.find('input[type="checkbox"]:checked').each(()->
      arr.push($(@).data().id)
    )
    arr
  getButtons: (trueFunc,falseFunc)->
    self = @
    @saveObj = {}
    buttons = []

    buttons.push(
      text: 'Закрыть',
      class: 'button_yes_or_no no',
      click: ()->
        falseFunc()
        self.destroy()
    )
    dop_class = ' no'
    dop_class = ' yes' if @options.message.length > self.MessageMinLen
    buttons.push(
      text: 'Отправить',
      class: 'button_yes_or_no'+dop_class,
      id: 'js--send_comment'
      click: ()->

        if self.checkCheckbox() && self.checkText()
          # отправляем статистику
          stats = {
            name: self.statName,
            message: self.textarea.val(),
            statistics: self.getStatistic()
          }
          new sendAjax('save_stats_answer',stats)

          trueFunc(stats)
          self.destroy()
          return

        if !self.checkCheckbox()
          echo_message_js "Необходимо выбрать хотя бы один пункт, нам очень нужна обратная связь.",'error_message',1000
        if !self.checkText()
          echo_message_js "Комментарий должен быть не короче "+self.MessageMinLen+" символов",'error_message',1000



    )
    return buttons

  destroy: ()->
    $(@$el).parent().dialog('close').dialog('destroy').remove()


###
# прототип окна Confirm
#
# @version   21.04.2016 11:20:30
###
class window.timingProgressbar
  timeLoad:         200
  timing:           0
  moduleIdDiv:      'progressDiv'
  percentComplete:  0

  constructor: (timing = 5)->
    @timing = Number(timing)

    @init()

  init:()->
    if($('#'+@moduleIdDiv).length > 0)
      echo_message_js('timingProgressbar already exists')

    window_preload_add()


    $('body').append(@progressBarDiv = $('<div/>',{
      id:@moduleIdDiv,
      css:{
        'z-index':  999999999,
        'width':    '80%',
        'left':     '10%',
        'float':    'left',
        'top':      '20%',
        'position': 'fixed',
        'height':    20
#        'boxShadow':'6px 2px 9px 2px rgba(0,0,0,0.33)'
      }
    }));
    

    @progressBarDiv.progressbar({
      value: @percentComplete
    });
#    console.log @percentComplete
    @i = 0

    self = @
    @timerT = setInterval(()->




      self.percentComplete = (100/self.timing) * self.i++ / (1000 / self.timeLoad)
#      console.log 'percentComplete',self.percentComplete
      self.progressBarDiv.progressbar('value', self.percentComplete);

      # останавливаем таймер по достижении 100 %
      if self.percentComplete > 100
        self.stopTimer()

    , @timeLoad);

  stopTimer:()->
    clearInterval(@timerT)



  destroy:()->
    @stopTimer()
    window_preload_del()
    @progressBarDiv.remove()










