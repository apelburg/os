

class window.galleryWindow
  positionId:   0
  # максимальное количество изображений, котрое можно выбрать
  maxChooseImg: 3

  constructor:(positionId = 0)->
    @positionId = positionId

    self = @
    new sendAjax('get_gallery_content',{ id:@positionId }, ( response )->
      self.setData( response.data )
      self.init()
      self.initUploadify()
    )

  init:()->
    self = @
    # окна создания записи компенсации
    @windowGallery = new modalWindow({
      html      : @getContent(),
      width     : 1100,
      maxHeight : '100%',
      maxWidth  : '90%',
      title     : 'Галерея изображений (выбор изображений для позиции)',
      buttons   : [
        {
          text  : 'Загрузить',
          class : 'button_yes_or_no',
          id    : self.data.token,
          click : ()->
            self.destroy()
        },{
          text  : 'Закрыть',
          class : 'button_yes_or_no no',
          id    : 'updateAndSaveGalleryData',
          click : ()->
            self.destroy()
        }
      ]
    }, {
      closeOnEscape: true,
      single: true,
      close: (event, ui) ->
        self.destroy()
    })



    $('body').append(@getPreviewContainer())

  setData: (data) ->
    @data = data

  getPreviewContainer:()->

    # добавляем блок отображения выбранных изображений
    @contentDiv.append( @previewDiv = $('<div/>',{
      id:'galleryPreviewDiv',
      css:{
        'zIndex':(@windowGallery.winDiv.zIndex() + 1)
      }
    }) )

    # добавляем список изображений

    @previewDiv.append( ul = $('<ul/>') )

    # добавляем изображения
    @appendImgToGalleryPreview( @data )

    # запуск сортировки
    @sortableStart(ul)


    @previewDiv


  getContent:()->
    # создаём главный контейнер окна
    @contentDiv = $('<div/>',{
      id:"rt-gallery-images",
    })

    # добавляем список изображений
    @contentDiv.append( $('<ul/>') )

    # добавляем изображения
    @appendImgToGallery( @data )

    # сохраняем первичные ( изначальные ) данные о выбранных изображениях в главный блок
    @contentDiv.data( @getChooseObj() )

    @contentDiv

  replacePreviewDelete:( objLi, index )->
    self = @
    objLi.find('.delete_upload_img').remove()
    objLi.prepend($('<div/>', {
        class: 'delete_upload_img',
        html: 'x',
        click: ()->
          # прекращаем дальнейшую передачу события
          event.stopPropagation()
          self.contentDiv.find('ul li').eq(index).click()

      }))

  # добавляем все выбранные изображения в окно preview
  appendImgToGalleryPreview:(data) ->
    console.log data

    ul = @previewDiv.find('ul')


    for n,index in data.images
      if Number(n['checked']) == 1
        ul.append( li = @getImage(n))
        @replacePreviewDelete( li, index )

    # если изображений нет - подстваляем no_images
    if data.images.length == 0
      for n,i in @data.no_images
        @previewDiv.find('ul').append(@getImage(n))

    return @previewDiv
  # добавляем изображения в главное окно галлереи
  appendImgToGallery:(data) ->
    ul = @contentDiv.find('ul')

    for n,i in data.images
      ul.append(@getImage(n))

    # если изображений нет - подстваляем no_images
    if data.images.length == 0
      @addNoImage()

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
        self.removeNoImage()

        standard_response_handler(response);

    });

  removeNoImage:()->
    self = @
    if @contentDiv.find('#galleryNoImageMarker').length > 0
      @contentDiv.find('#galleryNoImageMarker').hide(300,()->
        $(@).remove()
        self.contentDiv.find('ul li').eq(0).click()
      )

  addNoImage:()->
    if @contentDiv.find('#galleryNoImageMarker').length == 0

      for n,i in @data.no_images
        @contentDiv.find('ul').append(@getImage(n))



  getImage:(imgData)->
    console.log "imgData = %O",imgData
    img = $('<img/>',{
      src:imgData.img_link_global
    })


    li = $('<li/>',{
      class:'rt-gallery-cont',
      click:()->
        self.chooseImg( $(@) )

    })

    li.data(imgData)
    self = @

    # добавляем возможность удаления ранее загруженного изображения
    console.log "imgData = %O",imgData
    if imgData.img_folder != 'img'

      li.append( $('<div/>',{
        class:'delete_upload_img',
        html:'x',
        click:()->
          # прекращаем дальнейшую передачу события
          event.stopPropagation()
          # удаление загруженного изображения
          self.deleteImg($(this))
      }))

    # если выбрано данное изображение - добавляем класс checked
    if Number(imgData.checked) > 0
      li.addClass('checked')

    # если нет изображений, приходит no_image.jpg
    # чтобы его потом было просто найти- добавляем маркер
    if imgData.img_name == "no_image.jpg"
      li.attr('id','galleryNoImageMarker')
    li.append(img)

  # выбор изображения
  chooseImg:(obj)->
    data = obj.data()

    if obj.hasClass('checked')
      if @contentDiv.find('li.checked').length > 1
        obj.removeClass('checked')
        data.checked = 0
        obj.data(data)
      else
        echo_message_js('Должно быть выбрано минимум одно изображение')

    else
      if @contentDiv.find('li.rt-gallery-cont.checked').length < 3
        obj.addClass('checked')
        data.checked = 1
        obj.data(data)
      else
        echo_message_js('В КП разрешено загружать не более ' + @maxChooseImg + ' изображений', 'system_message' ,2000);

    # обновляем данные в левом столбце
    @updateChoseObjToPreviewDiv()
    # проверка на изменения
    @checkEdit()

  # перенос выбраннога изображения в начало списка
  updateChoseObjToPreviewDiv:(  )->
    self = @
    data = []
    data = @data
    data.images = []


    @contentDiv.find('ul li').each((e, i)->
      data.images[e] = $(@).data()
    )
    @previewDiv.find('ul').html('')
    # добавляем изображения
    @appendImgToGalleryPreview( data )

    @sortableRefresh()

  # удаление изображений загруженных ранее
  deleteImg:(obj)->
    self = @
    if obj.parent().hasClass('checked') 
      if @contentDiv.find('ul li.checked').length == 1
        echo_message_js('Удалить единственное выбранное изображение нельзя.')
        return
      else
        @contentDiv.data( @getChooseObj() )
        @checkEdit()

    data = obj.parent().data()

    obj.parent().hide(300,()->
      $(@).remove()



      console.log data
      new sendAjax('delete_upload_image',{
        img_name: data.img_name,
        folder_name: self.data.folder_name
      })
      if self.contentDiv.find('ul li').length == 0
        self.addNoImage()
      self.updateChoseObjToPreviewDiv()
    )

  # запуск метода сортировки
  sortableStart:( ul )->
    self = @
    ul.sortable({
      items:'li',
      helper: "clone",
      containment: "parent",
      sort:( event, ui )->
        # проверка на изменения
        self.checkEdit()
    })

  # обновление данных в методе сортирвки
  # применить после изменений с контентом внутри галереи
  sortableRefresh:()->
    @previewDiv.find('ul').sortable("destroy")
    @sortableStart( @previewDiv.find('ul') )




  # собирает и возвращает данные о выбранных изображениях
  getChooseObj:()->
    chooseArr = {}
    i = 0
    @contentDiv.find('ul li').each((e,index)->
      data = $(@).data()
      chooseArr[i] = {}
      chooseArr[i]['img_name']    = data.img_name
      chooseArr[i++]['img_folder'] = data.img_folder
    )
    chooseArr
  getChoosePreviewObj:()->
    chooseArr = {}
    i = 0
    @previewDiv.find('ul li').each((e,index)->
      data = $(@).data()
      chooseArr[i] = {}
      chooseArr[i]['img_name']    = data.img_name
      chooseArr[i++]['img_folder'] = data.img_folder
    )
    chooseArr
  

  # проверка на изменения в выборе и сортировке
  # изменение кнопки закрыть/сохранить
  checkEdit:()->
    # получаем изначальный выбор
    chooseOldObjJson = JSON.stringify( @contentDiv.data() )

    # собираем в объект выбранные изображения
    chooseObj     = @getChoosePreviewObj()
    chooseObjJson = JSON.stringify( chooseObj )

    # получаем кнопку закрыть / сохранить
    button = @windowGallery.buttonDiv.find('#updateAndSaveGalleryData')

    self = @
    # проверяем были ли произведены изменения в выборе или сортировке
    if chooseObjJson != chooseOldObjJson
      # если ДА - подкрашиваем кнопку сохранить и при клике на неё отправляем обновленные данные из JSON
      button.removeClass('no').addClass('yes').html('Сохранить').unbind('click').click(()->

        # отправляем запрос на сохранение изменённых данных
        new sendAjax('save_edit_gallery',{
          chooseData: chooseObj,
          mainRowId: self.data.id
        },(response)->
          if response.response == "OK"
            self.contentDiv.data( self.getChooseObj() )

            button.removeClass('yes').addClass('no').html('Закрыть').unbind('click').click(()->
              self.destroy()
            )
        )
      )

    else
      self = @
      # если нет - кнопка сохранить должна быть кнопкой закрыть !!!!
      button.removeClass('yes').addClass('no').html('Закрыть').unbind('click').click(()->
        self.destroy()
      )

  # скролл окна вниз
  scrollBottom:()->
    @contentDiv.stop().animate({"scrollTop":99999 },"slow");

  # уничтожить окно
  destroy:()->
    console.log @windowGallery
    @previewDiv.remove()
    $(@windowGallery.winDiv[0]).dialog('close').dialog('destroy').remove()
    



