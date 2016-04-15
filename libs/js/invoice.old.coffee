# A class-based template for jQuery plugins in Coffeescript
#
#     $('.target').myPlugin({ paramA: 'not-foo' });
#     $('.target').myPlugin('myMethod', 'Hello, world');
#
# Check out Alan Hogan's original jQuery plugin template:
# https://github.com/alanhogan/Coffeescript-jQuery-Plugin-Template
#
(($, window) ->

  # Define the plugin class
  class pppnvoice
    
    defaults:
      access: 0
      user_name: 'Default Name'
      data: {}
    pppnvoiceList:{}

    constructor: (el, options) ->
      @options = $.extend({}, @defaults, options)
      @$el = $(el)
    
    # инициализация
    init:(u='') ->
      # console.log @options
      # получаем данные
      @getFirstData(this)
      # собираем таблицу
      @generateTableRows(this)
    
    # собираем таблицу
    generateTableRows:(_this)->
      console.log @
      # console.log @options.hasOwnProperty(data)
      # options = @options
      # console.log @options.length
      # console.log row for row in @options.data
    
    getOptions:()->
      return @options.data
        
    getDataD:(u) ->      
      echo_message_js 'getDataD - '+u

    myfunc:(echo)->
      @$el.html(@options.paramA + ': ' + echo)
   
    # получение первичных данных
    getFirstData: (_this) ->
      data = {
          AJAX:'get_data'
      }
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
          myData = JSON.parse jqXHR.responseText
          # уровень доступа
          _this.settings.access = myData.access
          # счета
          console.log _this
          _this.settings.data = myData.data
          
      return response
    # создание строки
    createRow: () ->
      $.el.u {},
       $.el.i {}, "This is underlined italicized"


    # получение данных
    getData:(ajax_name,options={})->
      data.push(task) for task in options  
      data = {
          AJAX:ajax_name
      }
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
          # data1 = JSON.parse jqXHR.responseText
          # echo_message_js "Successful AJAX call: #{jqXHR.responseJSON}"
          console.log response
          standard_response_handler(jqXHR.responseJSON)
          

  # Define the plugin
  $.fn.extend pppnvoice: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('pppnvoice')

      if !data
        $this.data 'pppnvoice', (data = new pppnvoice(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)

) window.jQuery, window		





