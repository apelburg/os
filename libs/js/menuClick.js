// Generated by CoffeeScript 1.9.3

/*
 *
 * right contexts menu
 *
 * @author    Alexey Kapitonov
 * @version   13.05.2016 14:20
 */

(function() {
  var slice = [].slice;

  (function($, window) {
    var menuRightClick;
    menuRightClick = (function() {
      menuRightClick.prototype.defaults = {
        buttons: [
          {
            'name': 'тестовый пункт по умолчанию',
            'class': '',
            click: function(e) {
              return echo_message_js(this.name);
            }
          }
        ]
      };

      function menuRightClick(el, options) {
        var self;
        this.options = $.extend({}, this.defaults, options);
        this.$el = $(el);
        self = this;
        this.$el.on('contextmenu click', function(e) {
          return self.rightClick(e);
        });
      }

      menuRightClick.prototype.rightClick = function(event) {
        var context, i, j, len, li, menu, n, ref, self;
        self = this;
        event.preventDefault();
        if (event.button === 2) {
          $("#context-menu").remove();
          context = $('<div/>', {
            'class': 'context-menu',
            'id': 'context-menu'
          }).css({
            left: event.pageX + 'px',
            top: (event.pageY - 15) + 'px'
          });
          menu = $('<ul/>');
          ref = this.options.buttons;
          for (i = j = 0, len = ref.length; j < len; i = ++j) {
            n = ref[i];
            menu.append(li = $('<li/>', {
              'class': this.options.buttons[i]["class"],
              'html': this.options.buttons[i].name,
              click: self.options.buttons[i].click
            }));
          }
          context.append(menu).appendTo('body').show('fast').css('marginLeft', '-20px');
          $(document).click(function(event) {
            $(".context-menu").remove();
            return event.stopPropagation();
          });
        }
      };

      return menuRightClick;

    })();
    return $.fn.extend({
      menuRightClick: function() {
        var args, option;
        option = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
        return this.each(function() {
          var $this, data;
          $this = $(this);
          data = $this.data('menuRightClick');
          if (!data) {
            $this.data('menuRightClick', (data = new menuRightClick(this, option)));
          }
          if (typeof option === 'string') {
            return data[option].apply(data, args);
          }
        });
      }
    });
  })(window.jQuery, window);

}).call(this);

//# sourceMappingURL=menuClick.js.map
