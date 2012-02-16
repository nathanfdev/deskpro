Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.FormUploadHandler = new Orb.Class({
	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		var self = this;
		var dropZone = this.el;
		if (this.el.data('drop-document') == '1') {
			dropZone = $(document);
		}

		this.el.fileupload({
			url: this.el.data('upload-to'),
			dropZone: dropZone,
			autoUpload: true,
			formData: {
				security_token: this.el.data('security-token')
			},
			start: function() {
				self.el.find('li.error').remove();
			},
			uploadTemplate: $('.dptpl-attach-upload', this.el),
			downloadTemplate: $('.dptpl-attach-download', this.el)
		});

		this.el.on('click', '.remove', function() {
			var li = $(this).closest('li.uploaded').fadeOut('fast', function() {
				li.remove();
			});
		});

		$('.dp-fallback', this.el).remove();
		$('.dp-good-upload', this.el).show();
	}
});
