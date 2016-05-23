
jQuery(document).ready(function($) {
	var art = $("#copy-button").attr('data-clipboard-text');

      $("#copy-button")
        .on("beforecopy copy aftercopy", function(e) {
          console.log("Direct binding - " + e.type + " - #" + e.target.id);
          //console.dir(e);
        })
        .on("copy", function(e) {
          e.clipboardData.clearData();
          e.clipboardData.setData("text/plain", art);
          e.preventDefault();
          $("#copy-button").blur();
          echo_message_js('Артикул успешно скопирован', 'successful_message');
        });

    });