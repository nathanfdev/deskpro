Orb.createNamespace('DeskPRO.Agent.PageFragment.MediaManagerPage');

DeskPRO.Agent.PageFragment.MediaManagerPage.Upload = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'mediawin_upload';
	},

	initPage: function(wrapper) {
		this.wrapper = wrapper;

		DeskPRO_Window.util.fileupload(wrapper, {
			page: this.page,
			saveMedia: 1,
			uploadTemplate: $('.template-upload', wrapper),
			downloadTemplate: $('.template-download', wrapper)
		}).bind('fileuploadstart', function() {
			wrapper.find('.upload-control').hide();
		}).bind('fileuploadadd', function(e,data) {
			$('.files', wrapper).empty();
		});

		wrapper.on('click', '.cancel-trigger', function(ev) {
			ev.preventDefault();

			wrapper.find('.upload-control').show();
			wrapper.find('.files').hide();
		});

		wrapper.on('click', '.insert-trigger', function(ev) {
			ev.preventDefault();

			if (!MEDIA_MANAGER_WINDOW.boundEditor) {
				return;
			}

			var btn = $(this);

			if (btn.data('is-image') == '1') {
				MEDIA_MANAGER_WINDOW.boundEditor.selection.setContent('<img src="' + btn.data('download-url') + '" />');
			} else {
				MEDIA_MANAGER_WINDOW.boundEditor.selection.setContent('<a href="' + btn.data('download-url') + '">' + btn.data('file-name') + '</a>');
			}

			MEDIA_MANAGER_WINDOW.close();
		});
	}
});