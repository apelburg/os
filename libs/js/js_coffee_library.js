// Generated by CoffeeScript 1.9.3

/*
 * class Invoice
 *
 * @author    Alexey Kapitonov
 * @email     kapitonoval2012@gmail.com
 * @version   13.04.2016 16:25:40
 */


/*
 * возвращяет текущую дату в читабельном формате
 */

(function() {
  window.getDateNow = function() {
    var d, dd, mm, yy;
    d = new Date();
    dd = d.getDate();
    if (dd < 10) {
      dd = '0' + dd;
    }
    mm = d.getMonth() + 1;
    if (mm < 10) {
      mm = '0' + mm;
    }
    yy = d.getFullYear();
    return dd + '.' + mm + '.' + yy;
  };


  /*
   * возвращяет текущую дату в читабельном формате
   */

  window.getDateTomorrow = function() {
    var d, dd, mm, yy;
    d = new Date();
    d.setDate(d.getDate() + 1);
    dd = d.getDate();
    if (dd < 10) {
      dd = '0' + dd;
    }
    mm = d.getMonth() + 1;
    if (mm < 10) {
      mm = '0' + mm;
    }
    yy = d.getFullYear();
    return dd + '.' + mm + '.' + yy;
  };


  /*
   * округляет и приводит числа к денежному формату
   * строку преобразует в число
   */

  window.round_money = function(i_num) {
    var o_num;
    o_num = Number(i_num);
    return o_num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
  };


  /*
   * возвращает маркер приблизительного значения
   */

  window.markApproximateVal = function() {
    return $('<span/>', {
      html: '≈',
      css: {
        'color': 'red',
        'paddingRight': '5px'
      }
    });
  };


  /*
   * преобразует проценты в соответствии с принятой конвенцией к виду 0.00
   */

  window.round_percent = function(i_num) {
    var o_num, span;
    o_num = Number(i_num);
    if (i_num < 0.01 && i_num > 0) {
      o_num = 0.01;
    }
    o_num = Math.floor(o_num * 100) / 100;
    span = $('<span/>', {
      html: o_num
    }).data({
      percent: i_num
    });
    console.log(" )))))))))))) -> ", i_num, o_num);
    if (Number(o_num) !== Number(i_num)) {
      span.prepend(markApproximateVal());
    }
    return span;
  };


  /*
   * расчёт % оплаты счёта
   */

  window.calculatePercentPart = function(numberAll, numberPart) {
    var percent;
    percent = Number(numberPart) * 100 / Number(numberAll);
    percent = percent.toFixed(7);
    if (Number(numberPart) === 0 || Number(numberAll) === 0) {
      percent = 0;
    }
    return percent;
  };


  /*
   * вырезаем символы недоступные в денежном формате
   *
   * изначально предназначена как обработчик на keyup
   */

  window.deleteNotMoneySymbols = function(value) {
    return value.replace(/[\/,]/gim, '.').replace(/[^-0-9\/.]/gim, '').replace(/^([^\.]*\.)|\./g, '$1');
  };


  /*
   * перевод строки в денежном формате в число
   */

  window.moneyString2Number = function(value) {
    return Number(value);
  };


  /*
   * подсчет скидки
   * @param      price_out - входящая цена
   * @discount   discount - скидка
   */

  window.calc_price_with_discount = function(price_out, discount) {
    return (Number(price_out / 100) * (100 + Number(discount))).toFixed(2);
  };


  /*
   * транслитерация
   */

  window.cyrill_to_latin = function(text) {
    var arren, arrru, i, itm, j, len1, reg;
    arrru = ['Я', 'я', 'Ю', 'ю', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ', 'Ж', 'ж', 'А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё', 'З', 'з', 'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ы', 'ы', 'Ь', 'ь', 'Ъ', 'ъ', 'Э', 'э', ' '];
    arren = ['Ya', 'ya', 'Yu', 'yu', 'Ch', 'ch', 'Sh', 'sh', 'Sh', 'sh', 'Zh', 'zh', 'A', 'a', 'B', 'b', 'V', 'v', 'G', 'g', 'D', 'd', 'E', 'e', 'E', 'e', 'Z', 'z', 'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'R', 'r', 'S', 's', 'T', 't', 'U', 'u', 'F', 'f', 'H', 'h', 'C', 'c', 'Y', 'y', '`', '`', '\'', '\'', 'E', 'e', '_'];
    for (i = j = 0, len1 = arrru.length; j < len1; i = ++j) {
      itm = arrru[i];
      reg = new RegExp(arrru[i], "g");
      text = text.replace(reg, arren[i]);
    }
    return text;
  };


  /*
   * метод отправки запроса AJAX
   */

  window.sendAjax = (function() {
    sendAjax.prototype.defaults = {
      AJAX: 'test',
      options: {}
    };

    sendAjax.prototype.func = function() {
      return true;
    };

    sendAjax.prototype.response = {};

    function sendAjax(ajaxName, options, func) {
      var opt;
      if (options == null) {
        options = {};
      }
      if (func == null) {
        func = function() {
          return true;
        };
      }
      this.href = window.location.href;
      if (options.url) {
        this.href = options.url;
        options.url = void 0;
      }
      opt = {
        AJAX: ajaxName,
        options: options
      };
      this.func = func;
      this.options = $.extend({}, this.defaults, opt);
      this.sendAjax();
    }

    sendAjax.prototype.sendAjax = function() {
      var _this, data, k, ref, v;
      _this = this;
      data = {
        AJAX: this.options.AJAX
      };
      ref = this.options.options;
      for (k in ref) {
        v = ref[k];
        data[k] = v;
      }
      return $.ajax({
        url: this.href,
        type: "POST",
        data: data,
        dataType: "json",
        error: function(jqXHR, textStatus, errorThrown) {
          echo_message_js("AJAX Error: " + textStatus);
        },
        success: function(data, textStatus, jqXHR) {
          _this.response = $.extend({}, _this.response, jqXHR.responseJSON);
          standard_response_handler(_this.response);
          return _this.func(_this.response);
        }
      });
    };

    return sendAjax;

  })();


  /*
   * прототип окна Confirm
   *
   * @version   21.04.2016 11:20:30
   */

  window.modalConfirm = (function() {
    modalConfirm.prototype.defaults = {
      title: 'Подтвердите действие',
      html: 'Вы уверены'
    };

    function modalConfirm(data, func, func2) {
      var _this;
      if (data == null) {
        data = {};
      }
      if (func == null) {
        func = function() {};
      }
      if (func2 == null) {
        func2 = function() {};
      }
      _this = this;
      this.options = $.extend({}, this.defaults, data);
      this.options.buttons = [
        {
          text: 'Да',
          "class": 'button_yes_or_no no',
          style: 'float:right;',
          click: function() {
            func();
            return $(_this.selfObj.winDiv).dialog('close').dialog('destroy').remove();
          }
        }, {
          text: 'Нет, Спасибо.',
          "class": 'button_yes_or_no no',
          style: 'float:right;',
          click: function() {
            func2();
            return $(_this.selfObj.winDiv).dialog('close').dialog('destroy').remove();
          }
        }
      ];
      this.selfObj = new modalWindow(this.options, {
        single: false
      });
    }

    return modalConfirm;

  })();


  /*
   * прототип окна
   *
   * @param     data = {html='текст не был передан', title='имя окна не было передано', buttons={}}
   * @version   18.04.2016 12:53:01
   */

  window.modalWindow = (function() {
    modalWindow.prototype.sittings = {
      modal: true,
      autoOpen: true,
      closeOnEscape: false,
      single: true,
      close: function(event, ui) {
        return true;
      },
      beforeClose: function(event, ui) {
        return true;
      }
    };

    modalWindow.prototype.defaults = {
      id: 'js-alert_union',
      title: '*** Название окна ***',
      width: 'auto',
      height: 'auto',
      html: 'Текст в окне',
      buttons: []
    };

    function modalWindow(data, sittings) {
      if (data == null) {
        data = {};
      }
      if (sittings == null) {
        sittings = {};
      }
      this.options = $.extend({}, this.defaults, data);
      this.sittings = $.extend({}, this.sittings, sittings);
      if (this.options.maxWidth && this.options.maxWidth.indexOf('%') + 1) {
        this.options.maxWidth = $(window).width() / 100 * Number(this.options.maxWidth.substring(this.options.maxWidth.length - 1, 0));
      }
      if (this.options.maxHeight && this.options.maxHeight.indexOf('%') + 1) {
        this.options.maxHeight = $(window).height() / 100 * Number(this.options.maxHeight.substring(this.options.maxHeight.length - 1, 0));
      }
      this.init();
    }

    modalWindow.prototype.destroy = function() {
      return this.winDiv.dialog('close').dialog('destroy').remove();
    };

    modalWindow.prototype.init = function() {
      var _this, button, button_n, buttons_html, i, j, len, len1, ref, self, td, tr;
      _this = this;
      if (this.sittings.single) {
        if ($('#js-alert_union').length > 0) {
          $('#js-alert_union').remove();
        }
        $('body').append(this.winDiv = $('<div/>', {
          "id": this.defaults.id,
          "style": "height:45px;",
          'html': this.options.html,
          "class": "js-alert_union"
        }));
      } else {
        len = $('.js-alert_union').length;
        this.defaults.id = this.defaults.id + len;
        $('body').append(this.winDiv = $('<div/>', {
          "id": this.defaults.id,
          "style": "height:45px;",
          'html': this.options.html,
          "class": "js-alert_union"
        }));
      }
      self = this.winDiv.dialog({
        width: this.options.width,
        height: this.options.height,
        modal: this.sittings.modal,
        title: this.options.title,
        autoOpen: this.sittings.autoOpen,
        closeOnEscape: this.sittings.closeOnEscape,
        beforeClose: function(event, ui) {
          return _this.sittings.beforeClose(event, ui);
        },
        close: function(event, ui) {
          return _this.sittings.close(event, ui);
        }
      }).parent();
      if (this.options.buttons.length === 0) {
        this.options.buttons.push({
          text: 'Закрыть',
          "class": 'button_yes_or_no no',
          style: 'float:right;',
          click: function() {
            return $('#' + _this.defaults.id).dialog('close').dialog('destroy').remove();
          }
        });
      }
      this.winDiv.dialog("option", "buttons", {
        buttons: {
          text: 'Закрыть',
          "class": 'button_yes_or_no no',
          style: 'float:right;',
          click: function() {
            return $('#' + _this.defaults.id).dialog('close').dialog('destroy').remove();
          }
        }
      });
      if (this.options.maxHeight) {
        this.winDiv.dialog("option", "maxHeight", this.options.maxHeight);
      }
      if (this.options.maxWidth) {
        this.winDiv.dialog("option", "maxWidth", this.options.maxWidth);
      }
      buttons_html = $('<table/>').append(tr = $('<tr/>'));
      ref = this.options.buttons;
      for (i = j = 0, len1 = ref.length; j < len1; i = ++j) {
        button_n = ref[i];
        button = $('<button/>', {
          html: button_n['text'],
          click: button_n['click']
        });
        if (button_n['class']) {
          button.attr('class', button_n['class']);
        }
        if (button_n['style']) {
          button.attr('style', button_n['style']);
        }
        if (button_n['id']) {
          button.attr('id', button_n['id']);
        }
        tr.append(td = $('<td/>').append(button));
        if (button_n.data !== void 0) {
          button.data(button_n.data);
        }
        if (i > 0) {
          td.css('textAlign', 'right');
        }
      }
      return self.find('.ui-dialog-buttonpane').html(this.buttonDiv = $('<div/>', {
        'class': 'js-alert_union_buttons ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'
      }).append(buttons_html));
    };

    return modalWindow;

  })();


  /*
   * прототип окна сообщения
   */

  window.sendMessage = (function() {
    sendMessage.prototype.defaults = {
      ajax: 'send_error_message',
      windowName: 'Отправкить сообщение',
      message: ''
    };

    sendMessage.prototype.MessageMinLen = 1;

    sendMessage.prototype.options = {};

    function sendMessage(options, func) {
      if (options == null) {
        options = {};
      }
      if (func == null) {
        func = function() {};
      }
      this.options = {};
      this.options = $.extend({}, this.defaults, options);
      console.log(this.options);
      this.init(func);
    }

    sendMessage.prototype.init = function(func) {

      /*
       * создание контейнера
       */
      this.main_div = $('<div/>', {
        'id': 'dialog_gen_window_form',
        'class': 'add_new_comment',
        css: {
          'padding': '15px'
        }
      });
      this.main_div.append(this.main_form = this.getForm());

      /*
       * создание окна
       */
      this.myObj = new modalWindow({
        html: this.main_div,
        maxHeight: '100%',
        width: '800px',
        title: this.options.windowName,
        buttons: this.getButtons(func)
      }, {
        closeOnEscape: true,
        single: true
      });
      this.$el = this.myObj.options.html[0];
      return $(this.$el).parent().css('padding', '0');
    };

    sendMessage.prototype.getForm = function() {
      var cell2, main, self, textarea, tr;
      self = this;
      main = $('<div/>', {
        'class': 'comment table'
      });
      main.append(tr = $('<div/>', {
        'class': 'row'
      }));
      cell2 = $('<div/>', {
        'class': 'cell comment_text'
      });
      cell2.append(textarea = $('<textarea/>', {
        'name': 'comment_text',
        val: this.options.message,
        keyup: function() {
          if ($(this).val().length > self.MessageMinLen) {
            console.log($(this).val().length);
            return $(self.myObj.buttonDiv).find("#js--send_comment").removeClass('no');
          } else {
            console.log($(this).val().length);
            return $(self.myObj.buttonDiv).find("#js--send_comment").addClass('no');
          }
        }
      }));
      tr.append(cell2);
      return $('<div/>', {
        'class': 'add_new_comment'
      }).append(main);
    };

    sendMessage.prototype.getButtons = function(func) {
      var buttons, dop_class, self;
      if (func == null) {
        func = function() {};
      }
      self = this;
      this.saveObj = {};
      buttons = [];
      buttons.push({
        text: 'Закрыть',
        "class": 'button_yes_or_no no',
        click: function() {
          return self.destroy();
        }
      });
      dop_class = ' no';
      if (this.options.message.length > self.MessageMinLen) {
        dop_class = ' yes';
      }
      buttons.push({
        text: 'Отправить',
        "class": 'button_yes_or_no' + dop_class,
        id: 'js--send_comment',
        click: function() {
          var comment;
          comment = self.main_form.find('textarea').val();
          if (comment.length <= self.MessageMinLen) {
            return echo_message_js("Сообщение должно быть не короче " + self.MessageMinLen + " символов");
          } else {
            func();
            return new sendAjax(self.options.ajax, {
              message: comment
            }, function() {
              return self.destroy();
            });
          }
        }
      });
      return buttons;
    };

    sendMessage.prototype.destroy = function() {
      return $(this.$el).parent().dialog('close').dialog('destroy').remove();
    };

    return sendMessage;

  })();


  /*
   * прототип окна сбора статистики
   * costsWindow
   */

  window.getStatisticForm = (function() {
    getStatisticForm.prototype.defaults = {
      windowName: 'Сбор статистики',
      dialogMessage: 'Пожалуйста укажите причину ваших действий',
      message: ' '
    };

    getStatisticForm.prototype.MessageMinLen = 2;

    getStatisticForm.prototype.options = {};

    function getStatisticForm(statName, options, trueFunc, falseFunc) {
      var self;
      if (statName == null) {
        statName = 'default';
      }
      if (options == null) {
        options = {};
      }
      if (trueFunc == null) {
        trueFunc = function() {};
      }
      if (falseFunc == null) {
        falseFunc = function() {};
      }
      this.statName = statName;
      this.options = {};
      self = this;
      new sendAjax('get_stats_questions', {
        name: this.statName
      }, function(response) {
        self.statData = response.data.stats;
        self.options = $.extend({}, self.defaults, options);
        return self.init(trueFunc, falseFunc);
      });
    }

    getStatisticForm.prototype.getStatisticFrom = function() {
      var j, len1, ref, row;
      this.tatisticFrom = $('<div/>');
      ref = this.statData;
      for (j = 0, len1 = ref.length; j < len1; j++) {
        row = ref[j];
        this.tatisticFrom.append(this.statRow(row));
      }
      return this.tatisticFrom;
    };

    getStatisticForm.prototype.statRow = function(data) {
      var html, inp, self;
      console.log(data);
      self = this;
      html = $('<div/>');
      html.append(inp = $('<input/>', {
        type: 'checkbox'
      }).data(data));
      html.append($('<label/>', {
        html: data.name,
        click: function() {
          inp.click();
          return self.validate();
        }
      }));
      return html;
    };

    getStatisticForm.prototype.init = function(trueFunc, falseFunc) {

      /*
       * создание контейнера
       */
      this.main_div = $('<div/>', {
        'id': 'dialog_gen_window_form',
        'class': 'add_new_comment',
        css: {
          'padding': '15px'
        }
      });
      this.main_div.append($('<div/>', {
        html: this.options.dialogMessage,
        css: {
          'padding': '5px 5px 10px 5px'
        }
      }));
      this.main_div.append(this.getStatisticFrom());
      this.main_div.append(this.main_form = this.getForm());

      /*
       * создание окна
       */
      this.myObj = new modalWindow({
        html: this.main_div,
        maxHeight: '100%',
        width: '800px',
        title: this.options.windowName,
        buttons: this.getButtons(trueFunc, falseFunc)
      }, {
        closeOnEscape: true,
        single: true
      });
      this.$el = this.myObj.options.html[0];
      return $(this.$el).parent().css('padding', '0');
    };

    getStatisticForm.prototype.validate = function() {
      if (this.checkCheckbox() && this.checkText()) {
        return $(this.myObj.buttonDiv).find("#js--send_comment").removeClass('no');
      } else {
        return $(this.myObj.buttonDiv).find("#js--send_comment").addClass('no');
      }
    };

    getStatisticForm.prototype.checkCheckbox = function() {
      if (this.tatisticFrom.find('input[type="checkbox"]:checked').length > 0) {
        return true;
      } else {
        return false;
      }
    };

    getStatisticForm.prototype.checkText = function() {
      if (this.textarea.val().length > this.MessageMinLen) {
        return true;
      } else {
        return false;
      }
    };

    getStatisticForm.prototype.getForm = function() {
      var cell2, main, self, tr;
      self = this;
      main = $('<div/>', {
        'class': 'comment table'
      });
      main.append(tr = $('<div/>', {
        'class': 'row'
      }));
      cell2 = $('<div/>', {
        'class': 'cell comment_text'
      });
      cell2.append(this.textarea = $('<textarea/>', {
        'name': 'comment_text',
        val: this.options.message,
        keyup: function() {
          return self.validate();
        }
      }));
      tr.append(cell2);
      return $('<div/>', {
        'class': 'add_new_comment'
      }).append(main);
    };

    getStatisticForm.prototype.getStatistic = function() {
      var arr;
      arr = [];
      this.tatisticFrom.find('input[type="checkbox"]:checked').each(function() {
        return arr.push($(this).data().id);
      });
      return arr;
    };

    getStatisticForm.prototype.getButtons = function(trueFunc, falseFunc) {
      var buttons, dop_class, self;
      self = this;
      this.saveObj = {};
      buttons = [];
      buttons.push({
        text: 'Закрыть',
        "class": 'button_yes_or_no no',
        click: function() {
          falseFunc();
          return self.destroy();
        }
      });
      dop_class = ' no';
      if (this.options.message.length > self.MessageMinLen) {
        dop_class = ' yes';
      }
      buttons.push({
        text: 'Отправить',
        "class": 'button_yes_or_no' + dop_class,
        id: 'js--send_comment',
        click: function() {
          var stats;
          if (self.checkCheckbox() && self.checkText()) {
            stats = {
              name: self.statName,
              message: self.textarea.val(),
              statistics: self.getStatistic()
            };
            new sendAjax('save_stats_answer', stats);
            trueFunc(stats);
            self.destroy();
            return;
          }
          if (!self.checkCheckbox()) {
            echo_message_js("Необходимо выбрать хотя бы один пункт, нам очень нужна обратная связь.", 'error_message', 1000);
          }
          if (!self.checkText()) {
            return echo_message_js("Комментарий должен быть не короче " + self.MessageMinLen + " символов", 'error_message', 1000);
          }
        }
      });
      return buttons;
    };

    getStatisticForm.prototype.destroy = function() {
      return $(this.$el).parent().dialog('close').dialog('destroy').remove();
    };

    return getStatisticForm;

  })();

}).call(this);

//# sourceMappingURL=js_coffee_library.js.map