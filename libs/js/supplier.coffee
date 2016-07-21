###
# Модуль юзер -> настройки
###

###
# прототип объекта реквизиты
###
class requesitObj
  default:
    id:0
    supplier_id:0
    company:''
    comp_full_name:''
    inn:''
    kpp:''
    postal_address:''
    legal_address:''
    phone1:''
    phone2:''
    bank:''
    bank_address:''
    r_account:''
    cor_account:''
    bik:''
    ogrn:''
    okpo:0
    dop_info:''
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




(($, window) ->
  class supplierRequisits

    constructor: (el, options) ->
      @$el = $(el)

      @init();

    init:()->
      self = @
      @$el.click(()->
        self.showRequsitWindow()
      )

    #  блок строк компенсаций
    showRequsitWindow:()->
      self = @
      new sendAjax("get_requsit_data",{},(response)->
        new requisitSimpleWindow(response.data.requisites,Number(response.data.access));
      )

  $.fn.extend supplierRequisits: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('supplierRequisits')

      if !data
        $this.data 'supplierRequisits', (data = new supplierRequisits(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)) window.jQuery, window


###
# прототип html окна просмотра реквизитов
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



class requisitSimpleWindow
  constructor:(data = {},access)->
    @access = access
    self = @

    html = $('<div/>',{
      id:'requesites_form'
    })
    html.append(tbl = $('<table/>'))

    buttons = [{
      text: 'Отмена',
      class: 'button_yes_or_no no',
      style: 'float:right;'
      click: ()->
        $(self.$el.winDiv).dialog('close').dialog('destroy').remove()
    }, {
      text: 'Создать',
      class: 'button_yes_or_no yes',
      style: 'float:right;',
      click: ()->
        # new sendAjax("create_requesit",{})
#        new requesitEditWindow(new requesitObj({}),access)
        new sendAjax("create_requesit",{},(response)->
          htmlWindow = Base64.decode(response.data.window)
          buttons = [{
            text: 'Отмена',
            class: 'button_yes_or_no no',
            style: 'float:right;'
            click: ()->
              $(mod.winDiv).dialog('close').dialog('destroy').remove()
          },{
            text: 'Создать',
            class: 'button_yes_or_no yes',
            style: 'float:right;'
            click: ()->
              serialize = $(mod.winDiv[0]).find('#create_requisits_form').serialize()

              $.post('', serialize, (data, textStatus, xhr) ->
                if(data['response'] != 'false')
                  $(mod.winDiv).dialog('close').dialog('destroy').remove()
                  standard_response_handler(data)
              ,'json');


          }]

          mod = new modalWindow({
            html:htmlWindow,
            title: "Новые реквизиты"
            buttons:buttons
          })
        )
        $(self.$el.winDiv).dialog('close').dialog('destroy').remove()

    }]

    num = 1
    for rowData in data
      tbl.append(@row(rowData, num++))

    @$el = new modalWindow({
      html: html,
      title: 'Создать поставщика',
      buttons: buttons
    }, {single: false})


  row:(rowData,num = 1)->
    tr = $('<tr/>').data(rowData)
    console.log rowData
    tr.append($('<td/>',{
      html:$('<span/>',{
        class:'show_requesit',
        html: num + '. ' + rowData.company
      }),
      click:()->
        new modalWindow({
          html: new requesitContent(rowData),
          maxHeight: '100%',
          width: '650px',
          title: 'Реквизиты',
        }, {
          closeOnEscape: true,
          single: false
        })
    }))

    tr.append(td = $('<td/>'))
    if 1
      td.append($('<img/>',{
        title:'Редактировать реквизиты',
        class:'edit_this_req',
        src:'skins/images/img_design/edit.png',
        click:()->
          new sendAjax("edit_requesit",{
            id:rowData.id
          },(response)->
            htmlWindow = Base64.decode(response.data.window)
            buttons = [{
              text: 'Отмена',
              class: 'button_yes_or_no no',
              style: 'float:right;'
              click: ()->
                $(mod.winDiv).dialog('close').dialog('destroy').remove()
            },{
              text: 'Сохранить',
              class: 'button_yes_or_no yes',
              style: 'float:right;'
              click: ()->
                serialize = $(mod.winDiv[0]).find('#requisits_edit_form').serialize()

                $.post('', serialize, (data, textStatus, xhr) ->
                  if(data['response'] != 'false')
                    $(mod.winDiv).dialog('close').dialog('destroy').remove()
                    standard_response_handler(data)
                ,'json');
            }]

            mod = new modalWindow({
              html:htmlWindow,
              title: "Редактировать реквизиты"
              buttons:buttons
            })
          )
      }))

    tr.append(td2 = $('<td/>'))
    if 1
      td2.append($('<img/>',{
        title:'Удалить реквизиты',
        class:'delete_this_req',
        src:'skins/images/img_design/delete.png',
        click:()->
          new sendAjax("delete_requsit_row",{id:rowData.id},()->
            tr.remove()
          )
      }))
    return tr

class requesitEditWindow
  access:0
  constructor:(data = {},access)->
    @access = access
    self = @

    html = $('<form/>',{
      id:'create_requisits_form'
    })
    html.append(tbl = $('<table/>'))

    buttons = []
    buttons.push({
      text: 'Отмена',
      class: 'button_yes_or_no no',
      style: 'float:right;'
      click: ()->
        $(self.$el.winDiv).dialog('close').dialog('destroy').remove()
    })
    if Number(data.id) > 0
      buttons.push({
        text: 'Сохранить',
        class: 'button_yes_or_no yes',
        style: 'float:right;',
        click: ()->
          new sendAjax("save_requesit",{})
          $(self.$el.winDiv).dialog('close').dialog('destroy').remove()
      })
    else
      buttons.push({
        text: 'Создать',
        class: 'button_yes_or_no yes',
        style: 'float:right;',
        click: ()->
          new sendAjax("create_requesit",{})
          $(self.$el.winDiv).dialog('close').dialog('destroy').remove()
      })

    num = 1
#    for rowData in data
#      tbl.append(@row(rowData, num++))

    @$el = new modalWindow({
      html: html,
      title: 'Создать поставщика',
      width:'100%',
      height:'100%',
      maxHeight:'100%',
      buttons: buttons
    }, {single: false})


  row:(rowData,num = 1)->
    tr = $('<tr/>')
    console.log rowData
    tr.append($('<td/>',{
      html:$('<span/>',{
        class:'show_requesit',
        html: num + '. ' + rowData.company
      }),
      click:()->
        new modalWindow({
          html: new requesitContent(rowData),
          maxHeight: '100%',
          width: '650px',
          title: 'Реквизиты',
        }, {
          closeOnEscape: true,
          single: false
        })
    }))

    tr.append(td = $('<td/>'))
    if 1
      td.append($('<img/>',{
        title:'Редактировать реквизиты',
        class:'edit_this_req',
        src:'skins/images/img_design/edit.png'
      }))

    tr.append(td2 = $('<td/>'))
    if 1
      td2.append($('<img/>',{
        title:'Удалить реквизиты',
        class:'delete_this_req',
        src:'skins/images/img_design/delete.png',
        click:()->
          new sendAjax("delete_requsit_row",{id:rowData.id},()->
            tr.remove()
          )
      }))
    return tr



