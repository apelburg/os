###
#
# right contexts menu
#
# @author    Alexey Kapitonov
# @version   13.05.2016 14:20
###

(($, window) ->

# Define the plugin class
  class menuClick

    defaults:
      buttons: [
        {
          'name':'тестовый пункт по умолчанию',
          'class':'',
          click:(e)->
            echo_message_js this.name
        }
      ],
      click:'rightClick'

    constructor: (el, options) ->
      @options = $.extend({}, @defaults, options)
      @$el = $(el)

      self = @
      @$el.on('contextmenu click', (e)->
        self[self.options.click](e)
      )


    # Additional plugin methods go here
    rightClick: (event) ->
      event.preventDefault();
      console.log event.button
      if(event.button == 2)
        @initMenu(event)
      return
    leftClick: (event) ->
      event.preventDefault();

      if(event.button == 0)
        @initMenu(event)
      return


    getLiObj:( list_item )->
      self = @
      func = list_item.click

      $('<li/>',{
        'class':list_item.class,
        'html':list_item.name,
#            'css':{
#              'padding':'10px 15px'
#            }
        click:()->
          func()
          self.context.remove()

      })
    initMenu:(event)->
      self = @
      if $("#context-menu").length > 0
        $("#context-menu").remove();

      # Создаем меню:
      @context = $('<div/>', {
        'class': 'context-menu',
        'id':'context-menu'
        click:(e)->
          e.stopPropagation()
      # Присваиваем блоку наш css класс контекстного меню:
      }).css({
        left: (event.pageX)+'px',
        # Задаем позицию меню на X
        top: (event.pageY-15)+'px'
        # Задаем позицию меню по Y
      })

      menu = $('<ul/>');
      # создаём элементы списка
      for list_item in @options.buttons
        @getLiObj(list_item).appendTo( menu )

      $(document).mouseup((e)->
        # если клик был не по нашему блоку
        # и не по его дочерним элементам
        if (!self.context.is(e.target) && self.context.has(e.target).length == 0)
          # скрываем меню
          self.context.remove()
      )

      @context.append(menu).appendTo('body') # Присоединяем наше меню к body документа:
      .show('fast').css('marginLeft','-20px');

  # Define the plugin
  $.fn.extend menuClick: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('menuClick')

      if !data
        $this.data 'menuClick', (data = new menuClick(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)

) window.jQuery, window
