####
# update invoise date
#
# @param     type
# @return    json 2
# @see       add json data in div#invoceData 
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:25:40
####
getInvoiceData = (type="new") ->
  $.ajax
    url: ""
    type: "POST"
    data:{AJAX:'get_data'}
    dataType: "json"
    error: (jqXHR, textStatus, errorThrown) ->
      echo_message_js "AJAX Error: #{textStatus}"
      return
    success: (data, textStatus, jqXHR) ->
      # console.log jqXHR
      standard_response_handler(jqXHR.responseJSON)
      # записываем json
      $('#invoceData').html jqXHR.responseText;
      # запускаем плагин
      $('#js-main-invoice-table').invoice() if type=='new'
  return true;    
      
  
$(document).ready(()->
  getInvoiceData()
)

round_money = (num) ->
  num = Number(num);
  new_num = Math.ceil((num)*100)/100;
  return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
calc_price_width_discount = (price_out, discount) ->
  return Number(price_out/100) * (100 + Number(discount));
    

###
# jQuery plagin Invoice
#
# @see       invoise table
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:26:46
###
(($, window) ->

  ###
  # Define the plugin class ttn
  ###
  class ttn
    defaults:
      number:0
    constructor: constructor: (el, options) ->
      @options = $.extend({}, @defaults, @options)
    init:()->
      console.log "init"
    show:()->
      console.log "show"
    destroy:()->
      console.log "destroy"


  ###
  # Define the plugin class Invoice
  ###
  class invoice

    defaults:
      start: false
    
    access_def: 0
    response_def:{}

    constructor: (el, options) ->
      # console.log @options       
      @options = $.extend({}, @defaults, jQuery.parseJSON($('#invoceData').html()))

      @access = $.extend({}, @access_def, @options.access)
      @$el = $(el)

      @init()
      # @init() for i in [0...10]

    # Additional plugin methods go here
    myMethod: (echo) ->
      @$el.html(@options.paramA + ': ' + echo)
    # Additional plugin methods go here
    init: (echo) ->
      # echo_message_js(@options)
      for n in @options.data
        @createRow n

    printOptions:()->
      console.info @options.access
      console.info @options.data

    # обновление сонтента в таблице
    updateRows:()->
      @destroyRows();
      @init();

    # убиваем строки
    destroyRows:()->
      @$el.find('tbody').html('')

    setData:(ajax_name,options={})->
      _this = @
      data = {
          AJAX:ajax_name
      }
      for k,v of options
        # console.log k + " is " + v
        data[k] = v 
      # console.log data
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
          response = jqXHR.responseJSON
          standard_response_handler(response)
          # update json
          # _this.updateData()

    updateData:()->
      console.log "updateData"
      $('#invoceData').html JSON.stringify(@options)

    ###
    # confirm dialog
    ###
    createSmallDialog: (html='текст не был передан', title='имя окна не было передано', buttons={})->
      
      # убиваем такое окно, если оно есть
      if($('#js-alert_union').length > 0) 
        $('#js-alert_union').remove();
      
      # создаем новое
      $('body').append($('<div/>',{
        "id":'js-alert_union',
        "style":"height:45px;",
        'html':html
      }));  
              
          
      $('#js-alert_union').dialog({
          width: 'auto',
          height: 'auto',
          modal: true,
          title : title,
          autoOpen : true,
          # beforeClose: ( event, ui ) ->

          closeOnEscape: false
          # // buttons: buttons          
      }).parent();

          
      buttons_html = $('<table></table>');
      for button_n,i in buttons 
        button = $('<button/>',{
          text: button_n['text'],
          click: button_n['click']
          });
        if button_n['class']
          button.attr('class',button_n['class'])
        if button_n['id']
          button.attr('id',button_n['id'])

        buttons_html.append(
          $('<td/>')
            .append(button)
            );  
      


      $('#js-alert_union').after($('<div/>',{
        'id':'js-alert_union_buttons',
        'class':'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'
          })
          .append( buttons_html));

    ###
    # get data
    ###
    getData:(ajax_name,options={},func = ()->)->
      _this = @
      data = {
          AJAX:ajax_name
      }
      for k,v of options
        # console.log k + " is " + v
        data[k] = v 
      # console.log data
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
          # data = JSON.parse jqXHR.responseText
          # echo_message_js "Successful AJAX call: #{jqXHR.responseText}"
          response = jqXHR.responseJSON
          _this.response = $.extend({}, _this.response_def, response)
          standard_response_handler(response)
          # update json
          func()
          # _this.updateData()
      return 
    ###
    # create ttn
    ###
    createTTN:(row)->
      _this = @
      buttons = [{
          text: 'Отмена',
          class:  'button_yes_or_no no',
          click: ()->
            $('#js-alert_union').dialog('destroy').remove();         
        },{
          text: 'Запросить',
          class:  'button_yes_or_no',
          click: ()->
            $('#js-alert_union').dialog('destroy').remove();  
        }];
      
      # запрос данных
      
      @getData('get_ttn',{'id':row.id},()->
        responseData = _this.response.data
        table = $('<table/>',{'id':'js-invoice--window--ttn-table'})
        i = 1



        if(responseData!= undefined)
          # шапка таблицы
          table.append(tr = $('<tr/>'))
          # чекбоксы
          check = $('<input/>',{
            'type':'checkbox'
            })
          td  = $('<th/>').append(check)
          tr.append(td)
          # Number
          td  = $('<th/>',{'text':'№'})
          tr.append(td)
          # Name
          td  = $('<th/>',{'html':'Наименование и <br>описание продукции'})
          tr.append(td)
          # Quantity
          td  = $('<th/>',{'html':'Количество<br>продукции'})
          tr.append(td)
          # Price for one
          td  = $('<th/>',{'html':'стоимость<br>за штуку'})
          tr.append(td)
          # Price for all
          td  = $('<th/>',{'html':'Общая<br>стоимость'})
          tr.append(td)

          main_price = 0
          nds = 0
          for position in responseData

            tr = $('<tr/>').data(position)
            # чекбоксы
            check = $('<input/>',{
              'type':'checkbox'
              })
            td  = $('<td/>').append(check)
            tr.append(td)

            # Number
            td  = $('<td/>').append(i)
            tr.append(td)
            # Name
            td  = $('<td/>').append(position.name)
            tr.append(td)
            # Quantity
            td  = $('<td/>').append(position.quantity)
            tr.append(td)
            # Price for one
            pr_out = calc_price_width_discount(position.price, position.discount)
            td  = $('<td/>').append(round_money(pr_out)+' р.')
            tr.append(td)
            # Price for all
            
            main_price += pr_out*position.quantity
            nds += Number(round_money(pr_out*position.quantity/118*18))
            td  = $('<td/>').append(round_money(pr_out*position.quantity)+' р.')
            tr.append(td)
            i++
            table.append(tr)
          # ИТОГО
          table.append(tr = $('<tr/>'))
          td  = $('<th/>')
          tr.append(td)
          # текст
          td  = $('<th/>',{
            'colspan':'4',
            'html':'Итоговая сумма по данной спецификации (договору)'
            })
          tr.append(td)
          # Price for all
          td  = $('<th/>',{'html':round_money(main_price)+' р.'})
          tr.append(td)
          table.append(tr)
          # в том числе НДС
          table.append(tr = $('<tr/>'))
          td  = $('<th/>')
          tr.append(td)
          # Тескт
          td  = $('<th/>',{
            'colspan':'4',
            'html':'В т.ч. НДС 18%'
            })
          tr.append(td)
          # Price НДС
          td  = $('<th/>',{'html':round_money(nds)+' р.'})
          tr.append(td)
          table.append(tr)


          message = $('<div/>').append(table)
          _this.createSmallDialog(message,'Запрос ТТН',buttons);
        )
      

    ###
    # create tr 
    ###
    createRow:(row)->
      _this = @
      console.log 654
      tr = $('<tr/>',{
        id:'tt_'+row.id
        }).data(row);
      # номер, дата
      if row.doc_type=='spec'
        row.spf_num  = row.doc_num
        doc_type = 'счёт'
      else
        row.spf_num  = 'оф'
        doc_type = 'счёт - оферта'

      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--number',
          'html':'<span>'+row.invoice_num+'</span>  '+row.invoice_create_date
            }))
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd checked',
          'html': doc_type
            }))

      tr.append(td)

      # 1с
      td = $('<td/>',{'class':'invoice-row--checkboxtd'})
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd-div'
            }))
      td.click ()->
        if $(this).hasClass('checked')
          row.flag_1c = 0;
          $(this).removeClass('checked')
        else
          row.flag_1c = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        _this.setData 'edit_flag_1c',{id:row.id,val:row.flag_1c}
        return

      td.addClass('checked') if Number row.flag_1c>0
      tr.append(td)
      # выручка, платежи
      td = $('<td/>') 
        .append($('<div/>',{
          'class':'invoice-row--price-profit',
          'html':round_money row.price_out
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-payment',
          'html': round_money row.price_out_payment
            }))
      tr.append(td)
      # заказ, менеджер
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--order-number'
          'html':row.invoice_num
            }))
        .append($('<div/>',{
          'class':'invoice-row--meneger--full-name',
          'html': row.manager_name
            }))
      tr.append(td)
      # флаг рекламация
      td = $('<td/>',{
        'class':'invoice-row--icons-flag'          
      })
        .append($('<div/>',{
          'class':'invoice-row--checkboxtd-div'
            }))

      td.click ()->
        if $(this).hasClass('checked')
          if(Number(_this.options.access) != 1)
            console.log _this.options.access
            echo_message_js('Снять рекламацию может только администратор','error_message')
            return false;
          
          row.flag_flag = 0;
          $(this).removeClass('checked')
          # сохраняем значение флага
          _this.setData 'edit_flag_flag',{id:row.id,val:row.flag_flag}
        else
          if(Number(_this.options.access) != 5 && Number(_this.options.access) != 1)
            echo_message_js('Рекламацию устанавливает только менеджер','error_message')
            return false;

          t = $(@)
          buttons = [{
            text: 'Да',
            class:  'button_yes_or_no',
            click: ()->
              row.flag_flag = 1;
              t.addClass('checked')
              # сохраняем значение флага
              _this.setData 'edit_flag_flag',{id:row.id,val:row.flag_flag}
              $('#js-alert_union').dialog('destroy').remove();  
          },{
            text: 'Нет',
            class:  'button_yes_or_no yes',
            click: ()->
              $('#js-alert_union').dialog('destroy').remove();         
          }];
          message = 'Вы уверены, что хотите установить флаг рекламации?';
          _this.createSmallDialog(message,'Подтверждение действия',buttons);  
        return

      td.addClass('checked') if Number row.flag_flag>0
      tr.append(td)
      # клиент, юрлицо
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--client--name',
          'html':row.client_name
            }))
        .append($('<div/>',{
          'class':'invoice-row--client--requsits',
          'data-id':row.client_requisit_id,
          'html': row.client_requisit_name
            }))
      tr.append(td)
      # себестоимость
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--price-start',
          'html':row.price_in
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-our-pyment',
          'html': row.client_requisit_name
            }))
      tr.append(td)
      # глаз
      td = $('<td/>',{'class':'invoice-row--ice'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_ice = 0;
          $(this).removeClass('checked')
        else
          row.flag_ice = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        _this.setData 'edit_flag_ice',{id:row.id,val:row.flag_ice}
        return

      td.addClass('checked') if Number row.flag_ice>0
      tr.append(td)
      # прибыль
      td = $('<td/>')
        .append($('<div/>',{
          'class':'invoice-row--price-our-profit',
          'html':round_money(row.price_out - row.price_in)
            }))
        .append($('<div/>',{
          'class':'invoice-row--price-our-profit-percent',
          'html':round_money(((row.price_out - row.price_in)/row.price_out*100).toString())+'%'
          
          }))
      tr.append(td)
      # калькулятор
      td = $('<td/>',{'class':'invoice-row--icons-calculator'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_calc = 0;
          $(this).removeClass('checked')
        else
          row.flag_calc = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        _this.setData 'edit_flag_calc',{id:row.id,val:row.flag_calc}
        return

      td.addClass('checked') if Number row.flag_calc>0
      tr.append(td)
      # ттн
      
      
      if(row.ttn.length == 0 && @options.access !=2)
        td = $('<td/>',{
        'colspan':'3',
        'html':"Запросить",
        'class':'js-query-ttn'
        click:()->

          # echo_message_js('Запрос ТТН','successful_message')
          # окно Запрос ТТН
          _this.createTTN(row)
        })
      else
        td = $('<td/>',{
        'colspan':'3'
        })

      tr.append(td)

      # спф дата
      td = $('<td/>')
        .append($('<div/>').html(row.spf_num))
      tr.append(td)
      # спф checkbox
      td = $('<td/>',{'class':'invoice-row--ttn--vt invoice-row--checkboxtd'})

      td.click ()->
        if $(this).hasClass('checked')
          row.flag_spf_return = 0;
          $(this).removeClass('checked')
        else
          row.flag_spf_return = 1;
          $(this).addClass('checked')

        # сохраняем значение флага
        _this.setData 'edit_flag_spf_return',{id:row.id,val:row.flag_spf_return}
        return

      td.addClass('checked') if Number row.flag_spf_return>0
      tr.append(td)
      # статус
      td = $('<td/>')
      tr.append(td)
      # диндин
      td = $('<td/>')
      tr.append(td)
      

      @$el.find('tbody').append(tr)



  # Define the plugin
  $.fn.extend invoice: (option, args...) ->
    @each ->
      $this = $(this)
      data = $this.data('invoice')

      if !data
        $this.data 'invoice', (data = new invoice(this, option))
      if typeof option == 'string'
        data[option].apply(data, args)

) window.jQuery, window

###
# cloned the head table and fixed this head on top in user window
#
# @see       top header
# @author    Alexey Kapitonov
# @email     kapitonoval2012@gmail.com
# @version   13.04.2016 16:24:30
###
$(window).scroll ()->
  if $(this).scrollTop()>$('#js-main-invoice-table').offset().top
    # шапка таблицы
    if($('#js-main-invoice-table-clone').length == 0)
      el_cloned = $('#js-main-invoice-table thead');
      thead = el_cloned.clone();
      thead.find('tr').each((index) ->
        # console.log thead.find('tr').eq(index)
        thead.find('tr').eq(index).find('th').each((ind) ->
          # console.log el_cloned.find('tr').eq(index).find('th').eq(ind).width()

          thead.find('tr').eq(index).find('th').eq(ind).width(el_cloned.find('tr').eq(index).find('th').eq(ind).width()+1)
          return
        )
        return
      )
      div = $('<div/>',{'id':'js-main-invoice-table-clone'})
        .append($('<table/>').append(thead))
        .css({
          'position':'fixed',
          'top':'0',
          'left':'0',
          'width':'100%'
          })
      div.appendTo('body')
    else
      $('#js-main-invoice-table-clone')
        # .stop( true, true )
        .css {'display':'block'}
    # показываем кнопку вверх
    $('#invoice-button-top').show()
  else
    if($('#js-main-invoice-table-clone').length > 0)  
      $('#js-main-invoice-table-clone')
        # .stop( true, true )
        .css {'display':'none'}
    # скрываем кнопку вверх
    $('#invoice-button-top').hide()

  

$(document).on 'click','#invoice-button-top', (event) ->
  $("html, body").animate({ scrollTop: 0 }, 600);
  
