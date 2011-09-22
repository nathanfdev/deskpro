Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.FormUploadHandler = new Orb.Class({
	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

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
			uploadTemplate: $('.dptpl-attach-upload', this.el),
			downloadTemplate: $('.dptpl-attach-download', this.el)
		});

		$('.dp-fallback', this.el).remove();
		$('.dp-good-upload', this.el).show();
	}
});
