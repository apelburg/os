// Generated by CoffeeScript 1.9.3
(function() {
  window.galleryWindow = (function() {
    galleryWindow.prototype.positionId = 0;

    galleryWindow.prototype.maxChooseImg = 3;

    function galleryWindow(positionId) {
      var self;
      if (positionId == null) {
        positionId = 0;
      }
      window_preload_add();
      this.positionId = positionId;
      self = this;
      new sendAjax('get_gallery_content', {
        id: this.positionId
      }, function(response) {
        window_preload_del();
        self.setData(response.data);
        self.init();
        return self.initUploadify();
      });
    }

    galleryWindow.prototype.init = function() {
      var self;
      self = this;
      this.windowGallery = new modalWindow({
        html: this.getContent(),
        width: 900,
        maxHeight: '100%',
        maxWidth: '80%',
        title: 'Галерея изображений (выбор изображений для позиции)',
        buttons: [
          {
            text: 'Загрузить',
            "class": 'button_yes_or_no',
            id: self.data.token,
            click: function() {
              return self.destroy();
            }
          }, {
            text: 'Закрыть',
            "class": 'button_yes_or_no no',
            id: 'updateAndSaveGalleryData',
            click: function() {
              return self.destroy();
            }
          }
        ]
      }, {
        closeOnEscape: true,
        single: true,
        close: function() {
          return self.destroy();
        }
      });
      return $('body').append(this.getPreviewContainer());
    };

    galleryWindow.prototype.setData = function(data) {
      return this.data = data;
    };

    galleryWindow.prototype.getZIndexWindowGallery = function() {
      return this.windowGallery.winDiv.zIndex();
    };

    galleryWindow.prototype.getPreviewContainer = function() {
      var ul;
      this.contentDiv.append(this.previewDiv = $('<div/>', {
        id: 'galleryPreviewDiv',
        css: {
          'zIndex': this.getZIndexWindowGallery() + 1
        }
      }));
      this.previewDiv.append(ul = $('<ul/>'));
      this.appendImgToGalleryPreview(this.data);
      this.sortableStart(ul);
      return this.previewDiv;
    };

    galleryWindow.prototype.getContent = function() {
      var chooseObj;
      this.contentDiv = $('<div/>', {
        id: "rt-gallery-images"
      });
      this.contentDiv.append($('<ul/>'));
      this.appendImgToGallery(this.data);
      this.contentDiv.data(chooseObj = this.getChooseObj());
      this.savedData = chooseObj;
      return this.contentDiv;
    };

    galleryWindow.prototype.replacePreviewDelete = function(objLi, index) {
      var self;
      self = this;
      objLi.find('.delete_upload_img').remove();
      return objLi.prepend($('<div/>', {
        "class": 'delete_upload_img',
        html: 'x',
        click: function() {
          event.stopPropagation();
          return self.contentDiv.find('ul li').eq(index).click();
        }
      }));
    };

    galleryWindow.prototype.appendImgToGalleryPreview = function(data) {
      var i, index, j, k, len, len1, li, n, ref, ref1, ul;
      ul = this.previewDiv.find('ul');
      ref = data.images;
      for (index = j = 0, len = ref.length; j < len; index = ++j) {
        n = ref[index];
        if (Number(n['checked']) === 1) {
          ul.append(li = this.getImage(n));
          this.replacePreviewDelete(li, index);
        }
      }
      if (data.images.length === 0) {
        ref1 = this.data.no_images;
        for (i = k = 0, len1 = ref1.length; k < len1; i = ++k) {
          n = ref1[i];
          this.previewDiv.find('ul').append(this.getImage(n));
        }
      }
      return this.previewDiv;
    };

    galleryWindow.prototype.appendImgToGallery = function(data) {
      var i, j, len, n, ref, ul;
      ul = this.contentDiv.find('ul');
      ref = data.images;
      for (i = j = 0, len = ref.length; j < len; i = ++j) {
        n = ref[i];
        ul.append(this.getImage(n));
      }
      if (data.images.length === 0) {
        this.addNoImage();
      }
      return this.contentDiv;
    };

    galleryWindow.prototype.initUploadify = function() {
      var self;
      self = this;
      this.contentDiv.append($('<div/>', {
        id: 'galleryWindowQueue'
      }));
      return $('#' + this.data.token).uploadify({
        'formData': {
          'AJAX': 'add_new_files_in_kp_gallery',
          'timestamp': this.data.timestamp,
          'token': this.data.token,
          'section': $.urlVar('section'),
          'id': this.data.id,
          'folder_name': this.data.folder_name
        },
        'simUploadLimit': 10,
        'buttonText': 'Загрузить',
        'width': 250,
        'swf': 'http://' + window.location.hostname + '/libs/php/uploadify.swf',
        'uploader': window.location,
        'multi': true,
        'queueID': 'galleryWindowQueue',
        'onUploadSuccess': function(file, data) {
          var response;
          response = jQuery.parseJSON(data);
          self.appendImgToGallery(response.data);
          self.scrollBottom();
          self.removeNoImage();
          return standard_response_handler(response);
        }
      });
    };

    galleryWindow.prototype.removeNoImage = function() {
      var self;
      self = this;
      if (this.contentDiv.find('#galleryNoImageMarker').length > 0) {
        return this.contentDiv.find('#galleryNoImageMarker').hide(300, function() {
          $(this).remove();
          return self.contentDiv.find('ul li').eq(0).click();
        });
      }
    };

    galleryWindow.prototype.addNoImage = function() {
      var i, j, len, n, ref, results;
      if (this.contentDiv.find('#galleryNoImageMarker').length === 0) {
        ref = this.data.no_images;
        results = [];
        for (i = j = 0, len = ref.length; j < len; i = ++j) {
          n = ref[i];
          results.push(this.contentDiv.find('ul').append(this.getImage(n)));
        }
        return results;
      }
    };

    galleryWindow.prototype.getImage = function(imgData) {
      var img, li, self;
      img = $('<img/>', {
        src: imgData.img_link_global
      });
      li = $('<li/>', {
        "class": 'rt-gallery-cont',
        click: function() {
          return self.chooseImg($(this));
        }
      });
      li.data(imgData);
      self = this;
      if (imgData.img_folder !== 'img') {
        li.append($('<div/>', {
          "class": 'delete_upload_img',
          html: 'x',
          click: function() {
            event.stopPropagation();
            return self.deleteImg($(this));
          }
        }));
      }
      if (Number(imgData.checked) > 0) {
        li.addClass('checked');
      }
      if (imgData.img_name === "no_image.jpg") {
        li.attr('id', 'galleryNoImageMarker');
      }
      return li.append(img);
    };

    galleryWindow.prototype.chooseImg = function(obj) {
      var data;
      data = obj.data();
      if (obj.hasClass('checked')) {
        if (this.contentDiv.find('li.checked').length > 1) {
          obj.removeClass('checked');
          data.checked = 0;
          obj.data(data);
        } else {
          echo_message_js('Должно быть выбрано минимум одно изображение');
        }
      } else {
        if (this.contentDiv.find('li.rt-gallery-cont.checked').length < 3) {
          obj.addClass('checked');
          data.checked = 1;
          obj.data(data);
        } else {
          echo_message_js('В КП разрешено загружать не более ' + this.maxChooseImg + ' изображений', 'system_message', 2000);
        }
      }
      this.updateChoseObjToPreviewDiv();
      return this.checkEdit();
    };

    galleryWindow.prototype.updateChoseObjToPreviewDiv = function() {
      var data, i;
      data = [];
      data = this.data;
      data.images = [];
      i = 0;
      this.contentDiv.find('ul li').each(function() {
        return data.images[i++] = $(this).data();
      });
      this.previewDiv.find('ul').html('');
      this.appendImgToGalleryPreview(data);
      return this.sortableRefresh();
    };

    galleryWindow.prototype.deleteImg = function(obj) {
      var chooseObj, data, self;
      if (obj.parent().hasClass('checked')) {
        if (this.contentDiv.find('ul li.checked').length === 1) {
          echo_message_js('Удалить единственное выбранное изображение нельзя.');
          return;
        } else {
          this.contentDiv.data(chooseObj = this.getChooseObj());
          this.savedData = chooseObj;
        }
      }
      self = this;
      data = obj.parent().data();
      return obj.parent().hide(300, function() {
        $(this).remove();
        new sendAjax('delete_upload_image', {
          img_name: data.img_name,
          folder_name: self.data.folder_name
        });
        if (self.contentDiv.find('ul li').length === 0) {
          self.addNoImage();
        }
        return self.updateChoseObjToPreviewDiv();
      });
    };

    galleryWindow.prototype.sortableStart = function(ul) {
      var self;
      self = this;
      return ul.sortable({
        items: 'li',
        helper: "clone",
        deactivate: function() {
          var isCheckEdit;
          isCheckEdit = self.checkEdit();
          if (isCheckEdit === true) {
            return self.resortChooseImageInGalleryWindow();
          }
        }
      });
    };

    galleryWindow.prototype.resortChooseImageInGalleryWindow = function() {
      var self;
      self = this;
      console.log(self.previewDiv.find('ul li').length);
      return this.contentDiv.find('ul li.checked').each(function(index) {
        var data;
        console.log(self.previewDiv.find('ul li').eq(index).data());
        data = self.previewDiv.find('ul li').eq(index).data();
        return $(this).replaceWith(self.getImage(data));
      });
    };

    galleryWindow.prototype.sortableRefresh = function() {
      this.previewDiv.find('ul').sortable("destroy");
      return this.sortableStart(this.previewDiv.find('ul'));
    };

    galleryWindow.prototype.preloadWindow = function(type) {
      var preloadDiv;
      if (type == null) {
        type = 'add';
      }
      if (type === 'add') {
        preloadDiv = window_preload_add();
        preloadDiv.css({
          'zIndex': this.getZIndexWindowGallery() + 1
        });
        return preloadDiv;
      } else if (type === 'del') {
        window_preload_del();
      }
      return true;
    };

    galleryWindow.prototype.getChooseObj = function() {
      var chooseArr, i;
      chooseArr = {};
      i = 0;
      this.contentDiv.find('ul li.checked').each(function() {
        var data;
        data = $(this).data();
        chooseArr[i] = {};
        chooseArr[i]['img_name'] = data.img_name;
        return chooseArr[i++]['img_folder'] = data.img_folder;
      });
      return chooseArr;
    };

    galleryWindow.prototype.getChoosePreviewObj = function() {
      var chooseArr, i;
      chooseArr = {};
      i = 0;
      this.previewDiv.find('ul li').each(function() {
        var data;
        data = $(this).data();
        chooseArr[i] = {};
        chooseArr[i]['img_name'] = data.img_name;
        return chooseArr[i++]['img_folder'] = data.img_folder;
      });
      return chooseArr;
    };

    galleryWindow.prototype.checkEdit = function() {
      var button, chooseObj, chooseObjJson, chooseOldObj, chooseOldObjJson, self;
      chooseOldObj = this.contentDiv.data();
      chooseOldObjJson = JSON.stringify(chooseOldObj);
      chooseObj = this.getChoosePreviewObj();
      chooseObjJson = JSON.stringify(chooseObj);
      console.info("chooseOldObj = %O , chooseObj = %O", chooseOldObj, chooseObj);
      button = this.windowGallery.buttonDiv.find('#updateAndSaveGalleryData');
      self = this;
      if (chooseObjJson !== chooseOldObjJson) {
        button.removeClass('no').addClass('yes').html('Сохранить').unbind('click').click(function() {
          self.contentDiv.data(chooseObj);
          self.savedData = chooseObj;
          self.preloadWindow('add');
          return new sendAjax('save_edit_gallery', {
            chooseData: chooseObj,
            mainRowId: self.data.id
          }, function(response) {
            self.preloadWindow('del');
            if (response.response === "OK") {
              return button.removeClass('yes').addClass('no').html('Закрыть').unbind('click').click(function() {
                return self.destroy();
              });
            }
          });
        });
        return true;
      } else {
        self = this;
        button.removeClass('yes').addClass('no').html('Закрыть').unbind('click').click(function() {
          return self.destroy();
        });
        return false;
      }
    };

    galleryWindow.prototype.scrollBottom = function() {
      return this.contentDiv.stop().animate({
        "scrollTop": 99999
      }, "slow");
    };

    galleryWindow.prototype.updateWindow = function() {
      return window.location.href = window.location.href;
    };

    galleryWindow.prototype.updateContentInPage = function() {
      switch ($.urlVar('section')) {
        case "rt_position":
          return this.updateWindow();
        case "business_offers":
          return this.updateWindow();
        default:
          return true;
      }
    };

    galleryWindow.prototype.destroy = function() {
      this.updateContentInPage();
      this.previewDiv.remove();
      return $(this.windowGallery.winDiv[0]).dialog('close').dialog('destroy').remove();
    };

    return galleryWindow;

  })();

}).call(this);

//# sourceMappingURL=positionGallery.js.map
