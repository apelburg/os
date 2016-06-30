###
# Модуль юзер -> настройки
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
# контент для окна создания записи компенсации
          html = $('<div/>',{
            id:'user_window_compensations_form'
          })
          html.append($('<div/>').append(win_inp_name = $('<input/>',{
            placeholder:'название'
          })))
          html.append($('<div/>').append(win_inp_val = $('<input/>',{
            placeholder:'стоимость'
            val:round_money(0)
            focus:()->
              if(Number($(this).val()) == 0)
                $(this).val('')
              else
                t = $(this)
                setTimeout(()->

                  t.select()
                , 50)
            blur:()->
              $(this).val(round_money(Number($(this).val())))
          })))
          # окна создания записи компенсации
          self.win_window = new modalWindow({
            html: html,
            maxHeight: '100%',
            maxWidth: '90%',
            title: 'Завести строку компенсации',
            buttons: [
              {
                text: 'Закрыть',
                class: 'button_yes_or_no no',
                click: ()->
                  console.log self.win_window.winDiv[0]
                  $(self.win_window.winDiv[0]).dialog('close').dialog('destroy').remove()
              },{
                text: 'Создать',
                class: 'button_yes_or_no',
                click: ()->
                  new sendAjax('create_compensation_row',{
                      user_id:self.options.id,
                      name:win_inp_name.val(),
                      val:win_inp_val.val(),
                      url:'http://'+window.location.hostname+'/os/?page=user_api'},(response)->
                    tr.before(self.create_compensation_row(response.data))
                    $(self.win_window.winDiv[0]).dialog('close').dialog('destroy').remove()
                  )

              }
            ]
          }, {
            closeOnEscape: true,
            single: true,
            close: (event, ui) ->
              $('#quick_button_div .button').eq(1).removeClass('checked')
          })
      })))
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
          if Number($(this).val()) != Number(self.options.salary)
            self.options.salary = round_money($(this).val())
            $(this).val(self.options.salary)
            new sendAjax('save_salary',{id:self.options.id,val:self.options.salary,url:'http://'+window.location.hostname+'/os/?page=user_api'})

          else
            $(this).val(self.options.salary)
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

