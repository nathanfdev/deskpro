Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.FormUploadHandler = new Orb.Class({
	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {

		var self = this;
		var dropZone = this.el;
		if (this.el.data('drop-document') == '1') {
			dropZone = $(document);
		}

		var options = {
			url: this.el.data('upload-to'),
			dropZone: dropZone,
			autoUpload: true,
			formData: {
				security_token: this.el.data('security-token')
			}
		};

		this._handleOptions(options);

		this.el.fileupload(options);

		$('.dp-fallback', this.el).remove();
		$('.dp-good-upload', this.el).show();
	},

	_handleOptions: function(options) {
		var el = this.el;

		if (!options.namespace) {
			options.namespace = Orb.uuid();
		}

		if (!options.dropZone) {
			options.dropZone = $(el);
		}

		if (typeof options.autoUpload == 'undefined') {
			options.autoUpload = true;
		}

		if (options.uploadTemplate) {
			var setel = options.uploadTemplate;
		} else {
			var setel = $('.template-upload', el);
		}
		if (!setel.attr('id')) {
			var id = Orb.getUniqueId('up');
			setel.attr('id', id);
		} else {
			var id = setel.attr('id');
		}
		delete(options.uploadTemplate);
		options.uploadTemplateId = id;

		if (options.downloadTemplate) {
			var setel = options.downloadTemplate;
		} else {
			var setel = $('.template-download', el);
		}
		if (!setel.attr('id')) {
			var id = Orb.getUniqueId('up');
			setel.attr('id', id);
		} else {
			var id = setel.attr('id');
		}
		delete(options.downloadTemplate);
		options.downloadTemplateId = id;

		if (!options.filesContainer) {
			options.filesContainer = $(el).find('.files');
		}

		options.start = function() {
			// Dont stack error messes. Once you upload again, the old one disappears
			$(el).find('.error').remove();
		};

		$(el).on('click', '.remove-attach-trigger', function(ev) {
			// Ignore .delete as they may be items rendered with the page,
			// eg. the list handles delete of existing attachments on its own
			if ($(this).hasClass('delete')) {
				return;
			}
			ev.preventDefault();
			var el = $(this);
			el.closest('li').slideUp('fast', function() {
				el.remove();
			});
		});
	}
});
