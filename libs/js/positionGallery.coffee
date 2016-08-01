

class window.galleryWindow

  constructor:(positionId = 0)->
    @init(positionId)

  init:(positionId)->
    self = @
    # окна создания записи компенсации
    @window = new modalWindow({
      html: @getContent(positionId),
      maxHeight: '100%',
      maxWidth: '90%',
      title: 'Галлерея изображений (выбор изображений для позиции)',
      buttons: [
        {
          text: 'Загрузить',
          class: 'button_yes_or_no',
          click: ()->
            $(self.window.winDiv[0]).dialog('close').dialog('destroy').remove()
        },{
          text: 'Сохранить',
          class: 'button_yes_or_no no',
          click: ()->
            $(self.window.winDiv[0]).dialog('close').dialog('destroy').remove()
        }
      ]
    }, {
      closeOnEscape: true,
      single: true,
      close: (event, ui) ->
        true
    })
  getContent:()->
    $('<div/>',{
      html:"Картиночки =)"
    })
  destroy:()->

