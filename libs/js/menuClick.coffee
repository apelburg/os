###
#
# right contexts menu
#
# @author    Alexey Kapitonov
# @version   13.05.2016 14:20
###

(($, window) ->

# Define the plugin class
  class menuRightClick

    defaults:
      buttons: [
        {
          'name':'тестовый пункт по умолчанию',
          'class':'',
          click:(e)->
            echo_message_js this.name
        }
      ]

    constructor: (el, options) ->


      @options = $.extend({}, @defaults, options)
      @$el = $(el)

      self = @
      @$el.on('contextmenu click', (e)->
        self.rightClick(e)

      )


    # Additional plugin methods go here
    rightClick: (event) ->
      self = @
      event.preventDefault();

      if(event.button == 2)
        $("#context-menu").remove();

        # Создаем меню:
        context = $('<div/>', {
          'class': 'context-menu',
          'id':'context-menu'
        # Присваиваем блоку наш css класс контекстного меню:
        }).css({
          left: (event.pageX)+'px',
        # Задаем позицию меню на X
        top: (event.pageY-15)+'px'
        # Задаем позицию меню по Y
        })

        menu = $('<ul/>');
        for n,i in @options.buttons
          menu.append(li = $('<li/>',{
            'class':@options.buttons[i].class,
            'html':@options.buttons[i].name,
#            'css':{
#              'padding':'10px 15px'
#            }
            click:self.options.buttons[i].click
          }))

        context.append(menu).appendTo('body') # Присоединяем наше меню к body документа:
        .show('fast').css('marginLeft','-20px');


        $(document).click((event)->
#          if( $(event.target).closest("#context-menu").length )
#            return
          $(".context-menu").remove()
          event.stopPropagation()
        )
      return

  # Define the plugin
  $.fn.extend menuRightClick: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('menuRightClick')

      if !data
        $this.data 'menuRightClick', (data = new menuRightClick(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)

) window.jQuery, window
