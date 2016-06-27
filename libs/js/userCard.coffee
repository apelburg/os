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
# Модуль учёт -> настройки
###
(($, window) ->
  class userOptions
    defaults:
      date:'00.00.0000'
    constructor: (el, options) ->
      @$el = $(el)
      @elID = @$el.attr('id')

      # информация по данному менеджеру распечатывается в скрытый DIV
      # т.к. уже производится запрос данных из бызы,
      # так было экономичнее, незачем запрашивать одну и ту же инфу дважды
      options = jQuery.parseJSON(@$el.find('#edit_new_os_dop_param_json').html())

      if options == null
        $(el).html($('<span/>',{
          css:{
            'color':'rgb(255, 130, 130);'
          },
          html:'блок редактирования доп. инфо учёта не доступен в режиме создания пользователей'
        }))
        return
      else
        # блок загрузки
        $(el).html($('<div/>',{
          id:'preloader_block'
        }))

      @options = $.extend({}, @defaults, options)
      console.log @options
      ###
      # добавление полей
      ###

      self = @

      new sendAjax('get_compensations_row',{
        user_id:self.options.id,
        url:'http://'+window.location.hostname+'/os/?page=user_api'
      },(response)->
        self.compensation = response.data
        self.init();
      )




    init:()->
      $('#'+@elID).html('')
#      $('#'+@elID).append($('<div/>',{
#        'html':'В разработке',
#        css:{
#          color:'red'
#        }
#      }))
      $('#'+@elID).append(tbl = $('<table/>',{'id':'userOptionsModule'}))
      # общие данные
      tbl.append(@general_tbl())
      # зарплата
      tbl.append(@salary_tbl())
      # Ежемесячные компенсации
      tbl.append(@compensation_tbl())

    # создание строки компенсайции
    create_compensation_row:(data)->
      tr = $('<tr/>').data(data)
      tr.append($('<td/>',{html:data.name}))
      tr.append($('<td/>',{html:data.val}))
      tr.append($('<td/>',{
        class:'delete_td',
        'html':'x',
        click:()->
          new sendAjax('delete_compensation_row',{
            id:data.id,
            url:'http://'+window.location.hostname+'/os/?page=user_api'
          },()->
            tr.remove()
          )
      }))
      tr

    #  блок строк компенсаций
    compensation_tbl:()->
      self = @
      tbl = []

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Ежемесячные компенсации '}))
      tr.append($('<td/>'))
      tr.append($('<td/>'))

      data = [{
        id:0
        name:'Телефон'
        val:850.00
      },{
        id:1
        name:'Прожёр'
        val:600.00
      },{
        id:2
        name:'Проезд'
        val:575.50
      }]

      console.log @compensation

      # перебор строк компенсаций
      for rowData,i in @compensation
        tbl.push(@create_compensation_row(rowData))



      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>').append($('<button/>',{
        'html':"Добавить",
        click:()->

      })))
      tr.append($('<td/>'))
      tr.append($('<td/>'))

      return tbl

    # блок зарплаты
    salary_tbl:()->
      self = @
      tbl = []
      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Выплаты на карту'}))
      tr.append($('<td/>'))
      tr.append($('<td/>'))

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Аванс'}))
      tr.append($('<td/>').append(inp = $('<input/>',{
        val:round_money(@options.avans),
        focus:()->
          t = $(this)
          if(Number($(this).val()) == 0)
            # если 0.00 подм  еняем на пусто
            $(this).val('')
          else
            # выделение

            setTimeout(()->
              t.select()
            , 50)
        blur:()->
          if Number($(this).val()) != Number(self.options.avans)
            self.options.avans = round_money($(this).val())
            new sendAjax('save_avans',{id:self.options.id,val:self.options.avans,url:'http://'+window.location.hostname+'/os/?page=user_api'})
            $(this).val(self.options.avans)
          else
            $(this).val(self.options.avans)

      })))
      tr.append($('<td/>'))


      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'ЗП'}))
      tr.append($('<td/>').append($('<input/>',{
        val:round_money(@options.salary),
        focus:()->
          t = $(this)
          if(Number($(this).val()) == 0)
            # если 0.00 подм  еняем на пусто
            $(this).val('')
          else
            # выделение

            setTimeout(()->
              t.select()
            , 50)
        blur:()->
          if Number($(this).val()) != Number(self.options.avans)
            self.options.avans = round_money($(this).val())
            $(this).val(self.options.avans)
            new sendAjax('save_salary',{id:self.options.id,val:self.options.avans,url:'http://'+window.location.hostname+'/os/?page=user_api'})

          else
            $(this).val(self.options.avans)
      })))
      tr.append($('<td/>'))

      tbl
    general_tbl:()->
      self = @
      tbl = []

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Общие данные'}))
      tr.append($('<td/>'))
      tr.append($('<td/>'))

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Дата приёма на работу'}))
      tr.append($('<td/>').append(inp = $('<input/>',{
        type: 'text',
        val: @options.date_start_wock
        blur:()->
          new sendAjax('save_date_work_start',{id:self.options.id,date:$(this).val(),url:'http://'+window.location.hostname+'/os/?page=user_api'})
      })))

      inp.datetimepicker({
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

      tr.append($('<td/>'))

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Статус'}))
      tr.append($('<td/>').append(sel = $('<select/>')))
      sel.change(()->
        new sendAjax('save_status',{id:self.options.id,val:$(this).val(),url:'http://'+window.location.hostname+'/os/?page=user_api'})
      )
      tr.append($('<td/>'))

      sel.append(opt = $('<option/>',{'value':'1','html':'Работает'}))
      opt.attr('selected', 'true') if Number(@options.status) == 1
      sel.append(opt = $('<option/>',{'value':'0','html':'Уволен'}))
      opt.attr('selected', 'true') if Number(@options.status) == 0
      tr.append($('<td/>'))

      tbl.push(tr = $('<tr/>'))
      tr.append($('<td/>',{html:'Менеджер'}))
      tr.append($('<td/>').append(sel = $('<select/>')))
      sel.change(()->
        new sendAjax('save_manager_type',{id:self.options.id,val:$(this).val(),url:'http://'+window.location.hostname+'/os/?page=user_api'})
      )
      sel.append(opt = $('<option/>',{'value':'1','html':'Рекламных агенств'}))
      opt.attr('selected', 'true') if Number(@options.manager) == 1
      sel.append(opt = $('<option/>',{'value':'2','html':'конечных клиентов'}))
      opt.attr('selected', 'true') if Number(@options.manager) == 2
      sel.append(opt = $('<option/>',{'value':'3','html':'бюджетник (исп/ср)'}))
      opt.attr('selected', 'true') if Number(@options.manager) == 3
      tr.append($('<td/>'))
      tbl





  $.fn.extend userOptions: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('userOptions')

      if !data
        $this.data 'userOptions', (data = new userOptions(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window

