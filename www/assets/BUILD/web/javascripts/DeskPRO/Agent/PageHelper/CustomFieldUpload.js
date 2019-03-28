Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.CustomFieldUpload = new Orb.Class({
  initialize: function (page) {
    this.page = page;
    this.updateVisibility();
  },

  initUploadBtn: function ($customFieldForm) {
    var self = this;
    if ($customFieldForm.find('input[type="file"]').length) {
      return;
    }

    var $uploadBtn = $('<div class="custom-field-attach"><a href="javascript:void(null);" unselectable="on">Attach<input type="file" class="file" name="file-upload"></a></div>');
    var $dropzone = $('<div class="drop-file-zone"><h1>'+DeskPRO_Window.getTranslate().phrase('agent.general.drop_here_to_attach_file')+'</h1></div>');

    $customFieldForm.addClass('has-init');
    $customFieldForm.append($uploadBtn);
    $customFieldForm.append($dropzone);

    var $input = $($customFieldForm.find('[data-prototype]').data('prototype'));
    var inputName = $input.attr('name') ? $input.attr('name').replace('[__name__]', '[]') : null;

    DeskPRO_Window.util.fileupload($uploadBtn, {
      dropZone: $dropzone,
      uploadTemplate: $('.template-upload'),
      downloadTemplate: $('.template-download')
    });
    $customFieldForm.bind('fileuploaddone', function(e, data) {
      $customFieldForm.find('.uploading').remove();

      data.result && data.result.forEach(function(el, i){
        $customFieldForm.append();

        var $el = $('<input type="hidden" name="'+inputName+'" value="'+el.blob_id+'" />');
        var $removeBtn = $('<em class="remove-attach-trigger"></em>');
        var $editWrapper = $('<div class="edit-wrapper" data-blob-id="'+el.blob_id+'" class="edit-wrapper" />');

        $editWrapper.append($('<label><a target="_blank" href="'+el.download_url+'">'+el.filename+' ('+el.filesize_readable+')</a></label>'));
        $editWrapper.append($removeBtn);
        $editWrapper.insertAfter($el);

        $removeBtn.on('click', function() {
          $el.remove();
          $editWrapper.remove();

          self.updateVisibility();
        });

        $el.insertBefore($uploadBtn);
        $editWrapper.insertBefore($uploadBtn);
        self.updateVisibility();
      });
    });
    $customFieldForm.bind('fileuploadsend', function(e, data) {
      data.files.forEach(function (file) {
        $('<div class="uploading">'+file.name+' ('+DeskPRO_Window.getTranslate().phrase('agent.general.saving')+')</div>').insertBefore($uploadBtn);
      });
    });
  },

  updateVisibility: function () {
    var self = this;
    $(this.page).find('.File.form.customfield').each(function() {
      var $customFieldForm = $(this);
      var $collectionForm = $customFieldForm.find('[data-prototype]');

      if (!$collectionForm.data('multiple') && $customFieldForm.find('input[type="hidden"]').length) {
        $customFieldForm.find('.custom-field-attach').fileupload('destroy');
        $customFieldForm.find('.custom-field-attach').remove();
        $customFieldForm.find('.drop-file-zone').remove();
      } else {
        self.initUploadBtn($customFieldForm);
      }
    });
  },

  destroy: function () {
    $(this.page).find('.File.form.customfield.has-init').each(function() {
      var $customFieldForm = $(this);
      var $collectionForm = $customFieldForm.find('[data-prototype]');

      if (!$collectionForm.data('multiple') && $customFieldForm.find('input[type="hidden"]').length) {
        $customFieldForm.find('.custom-field-attach').fileupload('destroy');
      } else {
        try {
          $customFieldForm.fileupload('destroy');
        } catch (e) {}
      }
    });
  }
});
