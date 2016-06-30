// Generated by CoffeeScript 1.10.0

/*
 * Модуль юзер -> настройки
 */


/*
 * прототип объекта реквизиты
 */

(function() {
  var requesitContent, requesitEditWindow, requesitObj, requisitSimpleWindow,
    slice = [].slice;

  requesitObj = (function() {
    requesitObj.prototype["default"] = {
      id: 0,
      supplier_id: 0,
      company: '',
      comp_full_name: '',
      inn: '',
      kpp: '',
      postal_address: '',
      legal_address: '',
      phone1: '',
      phone2: '',
      bank: '',
      bank_address: '',
      r_account: '',
      cor_account: '',
      bik: '',
      ogrn: '',
      okpo: 0,
      dop_info: ''
    };

    requesitObj.prototype.enterObj = {};

    requesitObj.prototype.options = {};

    function requesitObj(data) {
      var el, key;
      if (data == null) {
        data = {};
      }
      this.options = {};
      if (data.edit === void 0) {
        data.edit = 1;
      }
      for (key in data) {
        el = data[key];
        if (el !== null) {
          this.options[key] = el;
        }
      }
      return $.extend({}, this.defaults, this.options);
    }

    return requesitObj;

  })();

  (function($, window) {
    var supplierRequisits;
    supplierRequisits = (function() {
      function supplierRequisits(el, options) {
        this.$el = $(el);
        this.init();
      }

      supplierRequisits.prototype.init = function() {
        var self;
        self = this;
        return this.$el.click(function() {
          return self.showRequsitWindow();
        });
      };

      supplierRequisits.prototype.showRequsitWindow = function() {
        var self;
        self = this;
        return new sendAjax("get_requsit_data", {}, function(response) {
          return new requisitSimpleWindow(response.data.requisites, Number(response.data.access));
        });
      };

      return supplierRequisits;

    })();
    return $.fn.extend({
      supplierRequisits: function() {
        var args, option;
        option = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
        return this.each(function() {
          var $this, data;
          $this = $(this);
          data = $this.data('supplierRequisits');
          if (!data) {
            $this.data('supplierRequisits', (data = new supplierRequisits(this, option)));
          }
          if (typeof option === 'string') {
            return data[option].apply(data, args);
          }
        });
      }
    });
  })(window.jQuery, window);


  /*
   * прототип html окна просмотра реквизитов
   */

  requesitContent = (function() {
    requesitContent.prototype.defaults = {
      id: 0
    };

    requesitContent.prototype.enterObj = {};

    requesitContent.prototype.options = {};

    requesitContent.prototype.access = 0;

    function requesitContent(data) {
      this.options = $.extend({}, this.defaults, data);
      return this.init();
    }

    requesitContent.prototype.init = function() {
      var div, ref, ref1, ref10, ref11, ref12, ref13, ref14, ref15, ref16, ref2, ref3, ref4, ref5, ref6, ref7, ref8, ref9, self, span, tbl, td, tr;
      self = this;
      console.info(self);
      console.log((ref = this.options.company) != null ? ref.length : void 0, this.options.company);
      tbl = $('<table/>', {
        'css': {
          'width': '100%'
        }
      });
      tbl.append(tr = $('<tr/>'));
      span = $('<span/>', {
        'css': {
          'color': '#D8D3D3'
        },
        'html': 'Информация отсутствует'
      });
      tr.append($('<td/>', {
        'colspan': 2
      }).append(div = $('<div/>', {
        'style': 'border-bottom:1px solid #cecece;font-size:18px;',
        'html': span
      })));
      if ((ref1 = this.options.company) != null ? ref1.length : void 0) {
        div.html(this.options.company);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Полное наименование'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref2 = this.options.comp_full_name) != null ? ref2.length : void 0) {
        td.html(this.options.comp_full_name);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'ИНН'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref3 = this.options.inn) != null ? ref3.length : void 0) {
        td.html(this.options.inn);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'КПП'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref4 = this.options.kpp) != null ? ref4.length : void 0) {
        td.html(this.options.kpp);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td/>', {
        'colspan': 2
      }).append($('<div/>', {
        'html': 'Адрес и телефон',
        'style': 'border-bottom:1px solid #cecece;font-size:18px;'
      })));
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Юридический адрес'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref5 = this.options.legal_address) != null ? ref5.length : void 0) {
        td.html(this.options.legal_address);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Фактический адрес'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref6 = this.options.postal_address) != null ? ref6.length : void 0) {
        td.html(this.options.postal_address);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Телефоны'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref7 = this.options.phone1) != null ? ref7.length : void 0) {
        td.html(this.options.phone1);
      }
      if ((ref8 = this.options.phone1) != null ? ref8.length : void 0) {
        td.append(' ' + this.options.phone2);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td/>', {
        'colspan': 2
      }).append($('<div/>', {
        'html': 'Банковские реквизиты',
        'style': 'border-bottom:1px solid #cecece;font-size:18px;'
      })));
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'БАНК'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if (((ref9 = this.options.bank) != null ? ref9.length : void 0) || +((ref10 = this.options.bank_address) != null ? ref10.length : void 0)) {
        td.html(this.options.postal_address);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Р/С'
      }));
      tr.append($('<td>', {
        'html': (ref11 = this.options.r_account) != null ? typeof ref11.length === "function" ? ref11.length({
          '<span style="color:#D8D3D3">Информация отсутствует</span>': this.options.r_account
        }) : void 0 : void 0
      }));
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'КОРР/С'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref12 = this.options.cor_account) != null ? ref12.length : void 0) {
        td.html(this.options.cor_account);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'БИК'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref13 = this.options.bik) != null ? ref13.length : void 0) {
        td.html(this.options.bik);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'ОГРН'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref14 = this.options.ogrn) != null ? ref14.length : void 0) {
        td.html(this.options.ogrn);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'ОКПО'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref15 = this.options.okpo) != null ? ref15.length : void 0) {
        td.html(this.options.okpo);
      }
      tbl.append(tr = $('<tr/>'));
      tr.append($('<td>', {
        'html': 'Доп. инфо'
      }));
      tr.append(td = $('<td>', {
        'html': $('<span/>', {
          'css': {
            'color': '#D8D3D3'
          },
          'html': 'Информация отсутствует'
        })
      }));
      if ((ref16 = this.options.dop_info) != null ? ref16.length : void 0) {
        td.html(this.options.dop_info);
      }
      return tbl;
    };

    return requesitContent;

  })();

  requisitSimpleWindow = (function() {
    function requisitSimpleWindow(data, access) {
      var buttons, html, i, len, num, rowData, self, tbl;
      if (data == null) {
        data = {};
      }
      this.access = access;
      self = this;
      html = $('<div/>', {
        id: 'requesites_form'
      });
      html.append(tbl = $('<table/>'));
      buttons = [
        {
          text: 'Отмена',
          "class": 'button_yes_or_no no',
          style: 'float:right;',
          click: function() {
            return $(self.$el.winDiv).dialog('close').dialog('destroy').remove();
          }
        }, {
          text: 'Создать',
          "class": 'button_yes_or_no yes',
          style: 'float:right;',
          click: function() {
            new sendAjax("create_requesit", {}, function(response) {
              var htmlWindow, mod;
              htmlWindow = Base64.decode(response.data.window);
              buttons = [
                {
                  text: 'Отмена',
                  "class": 'button_yes_or_no no',
                  style: 'float:right;',
                  click: function() {
                    return $(mod.winDiv).dialog('close').dialog('destroy').remove();
                  }
                }, {
                  text: 'Создать',
                  "class": 'button_yes_or_no yes',
                  style: 'float:right;',
                  click: function() {
                    var serialize;
                    serialize = $(mod.winDiv[0]).find('#create_requisits_form').serialize();
                    return $.post('', serialize, function(data, textStatus, xhr) {
                      if (data['response'] !== 'false') {
                        $(mod.winDiv).dialog('close').dialog('destroy').remove();
                        return standard_response_handler(data);
                      }
                    }, 'json');
                  }
                }
              ];
              return mod = new modalWindow({
                html: htmlWindow,
                title: "Новые реквизиты",
                buttons: buttons
              });
            });
            return $(self.$el.winDiv).dialog('close').dialog('destroy').remove();
          }
        }
      ];
      num = 1;
      for (i = 0, len = data.length; i < len; i++) {
        rowData = data[i];
        tbl.append(this.row(rowData, num++));
      }
      this.$el = new modalWindow({
        html: html,
        title: 'Создать поставщика',
        buttons: buttons
      }, {
        single: false
      });
    }

    requisitSimpleWindow.prototype.row = function(rowData, num) {
      var td, td2, tr;
      if (num == null) {
        num = 1;
      }
      tr = $('<tr/>');
      console.log(rowData);
      tr.append($('<td/>', {
        html: $('<span/>', {
          "class": 'show_requesit',
          html: num + '. ' + rowData.company
        }),
        click: function() {
          return new modalWindow({
            html: new requesitContent(rowData),
            maxHeight: '100%',
            width: '650px',
            title: 'Реквизиты'
          }, {
            closeOnEscape: true,
            single: false
          });
        }
      }));
      tr.append(td = $('<td/>'));
      if (1) {
        td.append($('<img/>', {
          title: 'Редактировать реквизиты',
          "class": 'edit_this_req',
          src: 'skins/images/img_design/edit.png'
        }));
      }
      tr.append(td2 = $('<td/>'));
      if (1) {
        td2.append($('<img/>', {
          title: 'Удалить реквизиты',
          "class": 'delete_this_req',
          src: 'skins/images/img_design/delete.png',
          click: function() {
            return new sendAjax("delete_requsit_row", {
              id: rowData.id
            }, function() {
              return tr.remove();
            });
          }
        }));
      }
      return tr;
    };

    return requisitSimpleWindow;

  })();

  requesitEditWindow = (function() {
    requesitEditWindow.prototype.access = 0;

    function requesitEditWindow(data, access) {
      var buttons, html, num, self, tbl;
      if (data == null) {
        data = {};
      }
      this.access = access;
      self = this;
      html = $('<form/>', {
        id: 'create_requisits_form'
      });
      html.append(tbl = $('<table/>'));
      buttons = [];
      buttons.push({
        text: 'Отмена',
        "class": 'button_yes_or_no no',
        style: 'float:right;',
        click: function() {
          return $(self.$el.winDiv).dialog('close').dialog('destroy').remove();
        }
      });
      if (Number(data.id) > 0) {
        buttons.push({
          text: 'Сохранить',
          "class": 'button_yes_or_no yes',
          style: 'float:right;',
          click: function() {
            new sendAjax("save_requesit", {});
            return $(self.$el.winDiv).dialog('close').dialog('destroy').remove();
          }
        });
      } else {
        buttons.push({
          text: 'Создать',
          "class": 'button_yes_or_no yes',
          style: 'float:right;',
          click: function() {
            new sendAjax("create_requesit", {});
            return $(self.$el.winDiv).dialog('close').dialog('destroy').remove();
          }
        });
      }
      num = 1;
      this.$el = new modalWindow({
        html: html,
        title: 'Создать поставщика',
        width: '100%',
        height: '100%',
        maxHeight: '100%',
        buttons: buttons
      }, {
        single: false
      });
    }

    requesitEditWindow.prototype.row = function(rowData, num) {
      var td, td2, tr;
      if (num == null) {
        num = 1;
      }
      tr = $('<tr/>');
      console.log(rowData);
      tr.append($('<td/>', {
        html: $('<span/>', {
          "class": 'show_requesit',
          html: num + '. ' + rowData.company
        }),
        click: function() {
          return new modalWindow({
            html: new requesitContent(rowData),
            maxHeight: '100%',
            width: '650px',
            title: 'Реквизиты'
          }, {
            closeOnEscape: true,
            single: false
          });
        }
      }));
      tr.append(td = $('<td/>'));
      if (1) {
        td.append($('<img/>', {
          title: 'Редактировать реквизиты',
          "class": 'edit_this_req',
          src: 'skins/images/img_design/edit.png'
        }));
      }
      tr.append(td2 = $('<td/>'));
      if (1) {
        td2.append($('<img/>', {
          title: 'Удалить реквизиты',
          "class": 'delete_this_req',
          src: 'skins/images/img_design/delete.png',
          click: function() {
            return new sendAjax("delete_requsit_row", {
              id: rowData.id
            }, function() {
              return tr.remove();
            });
          }
        }));
      }
      return tr;
    };

    return requesitEditWindow;

  })();

}).call(this);

//# sourceMappingURL=supplier.js.map
