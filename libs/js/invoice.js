
var slice = [].slice;

(function($, window) {
  var theBill;
  theBill = (function() {
    theBill.prototype.defaults = {
      access: 0,
      user_name: 'Default Name',
      data: {}
    };

    function theBill(el, options) {
      this.options = $.extend({}, this.defaults, options);
      this.$el = $(el);
    }

    theBill.prototype.init = function(u) {
      return echo_message_js(u);
    };

    theBill.prototype.myMethod = function(echo) {
      return this.$el.html(this.options.paramA + ': ' + echo);
    };

    theBill.prototype.getData = function(ajax_name, options) {
      var data, i, len, task;
      if (options == null) {
        options = {};
      }
      for (i = 0, len = options.length; i < len; i++) {
        task = options[i];
        data.push(task);
      }
      data = {
        AJAX: ajax_name
      };
      return $.ajax({
        url: "",
        data: data,
        dataType: "json",
        error: function(jqXHR, textStatus, errorThrown) {
          echo_message_js("AJAX Error: " + textStatus);
        },
        success: function(data, textStatus, jqXHR) {
          $('body').append("Successful AJAX call: " + data);
          standart_response_handler(data);
        }
      });
    };

    return theBill;

  })();
  return $.fn.extend({
    theBill: function() {
      var args, option;
      option = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
      return this.each(function() {
        var $this, data;
        $this = $(this);
        data = $this.data('theBill');
        if (!data) {
          $this.data('theBill', (data = new theBill(this, option)));
        }
        if (typeof option === 'string') {
          return data[option].apply(data, args);
        }
      });
    }
  });
})(window.jQuery, window);

$(document).ready(function() {
  $('#js-main-invoice-table').theBill();
});
