

class window.galleryWindow
  positionId:   0
  # максимальное количество изображений, котрое можно выбрать
  maxChooseImg: 3

  constructor:(positionId = 0)->
    @positionId = positionId

    self = @
    new sendAjax('getGalleryContent',{ id:@positionId }, ( response )->
      self.setData( response.data )
      self.init()
      self.initUploadify()
    )

  init:()->
    self = @
    # окна создания записи компенсации
    @windowGallery = new modalWindow({
      html: @getContent( @positionId ),
      width:1100,
      maxHeight: '100%',
      maxWidth: '90%',
      title: 'Галлерея изображений (выбор изображений для позиции)',
      buttons: [
        {
          text: 'Загрузить',
          class: 'button_yes_or_no',
          id: self.data.token,
          click: ()->
            self.destroy()
        },{
          text: 'Сохранить',
          class: 'button_yes_or_no no',
          click: ()->
            self.destroy()
        }
      ]
    }, {
      closeOnEscape: true,
      single: true,
      close: (event, ui) ->
        true
    })
  setData: (data) ->
    @data = data

  getContent:()->
    @contentDiv = $('<div/>',{
      id:"rt-gallery-images",
    })
    @contentDiv.append($('<ul/>'))

    @appendImgToGallery( @data )

  appendImgToGallery:(data) ->
    ul = @contentDiv.find('ul')

    for n,i in data.images
      ul.append(@getImage(n))

    return @contentDiv

  initUploadify:()->
    self = @
    # добавляем месть для вставки индикаторов загрузки
    @contentDiv.append($('<div/>',{
      id:'galleryWindowQueue'
    }))

    $('#'+@data.token).uploadify({
      'formData'     : {
        'AJAX'			: 'add_new_files_in_kp_gallery',
        'timestamp' 	: @data.timestamp,
        'token'     	: @data.token,
        'gnom'      	: 'sdfdsfdsf',
        'id'        	: @data.id,
        'folder_name'	: @data.folder_name
      },
      'simUploadLimit' : 10,
      'buttonText': 'Загрузить',

      'width'     : 250,
      'swf'      	: '../libs/php/uploadify.swf',
      'uploader' 	: '',
      'multi'     : true,
      'queueID'   : 'galleryWindowQueue',

      'onUploadSuccess' : ( file, data ) ->
        # alert('The file ' + file.name + ' uploaded successfully.');
        # подключаем стандартный обработчик ответа
        response = jQuery.parseJSON(data)

        self.appendImgToGallery(response.data)
        self.scrollBottom()
        standard_response_handler(response);

    });

  getImage:(imgData)->


    img = $('<img/>',{
      src:imgData.img_link_global
    })

    li = $('<li/>',{
      class:'rt-gallery-cont',
      click:()->
        self.chooseImg($(@))
    })

    self = @
    if imgData.img_folder != 'img'
      li.append($('<div/>',{
        class:'delete_upload_img',
        html:'x',
        click:()->
          event.preventDefault()
          event.stopPropagation()
          # удаление загруженного изображения
          self.deleteImg($(@))
      }))

    if Number(imgData.checked) > 0
      li.addClass('checked')

    li.append(img)
  chooseImg:(obj)->
    if obj.hasClass('checked')
      obj.removeClass('checked')
    else
      if @contentDiv.find('li.rt-gallery-cont.checked').length < 3
        obj.addClass('checked')
      else
        echo_message_js('В КП разрешено загружать не более ' + @maxChooseImg + ' изображений', 'system_message' ,2000);



  deleteImg:(obj)->


    obj.parent().hide(300,()->
      $(@).remove()
      echo_message_js('удалить изображение','successful_message',100)
    );

  scrollBottom:()->
    @contentDiv.stop().animate({"scrollTop":99999 },"slow");


  destroy:()->
    console.log @windowGallery
    $(@windowGallery.winDiv[0]).dialog('close').dialog('destroy').remove()



