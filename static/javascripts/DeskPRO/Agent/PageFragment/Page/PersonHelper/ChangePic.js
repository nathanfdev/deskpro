Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.PersonHelper');

/**
 * Delete/spam things. Toggles visibility of status section, and notice bar,
 * and sens appropriate save ajax.
 */
DeskPRO.Agent.PageFragment.Page.PersonHelper.ChangePic = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			loadUrl: '',
			saveUrl: ''
		};

		this.setOptions(options);
		this.page = page;

		this.page.getEl('change_user_picture').click(this.open.bind(this));

		this.page.addEvent('destroy', this.destroy.bind(this));
	},

	_initOverlay: function() {
		if (this.overlay) {
			return;
		}

		this.wrapperEl = $('<div class="change-person-picture" />');
		this.wrapperEl.append('<div>Loading...</div>');

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapperEl
		});

		$.ajax({
			url: this.options.loadUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				this.wrapperEl.empty().append(html);
				this._initControls();
			}
		});
	},

	_initControls: function() {
		this.wrapperEl.fileupload({
			url: BASE_URL + 'agent/misc/accept-upload',
			dropZone: this.wrapperEl,
			autoUpload: true,
			uploadTemplate: $('.template-upload', this.wrapperEl),
			downloadTemplate: $('.template-download', this.wrapperEl)
		});

		$('.save-trigger', this.wrapperEl).click(this._doSave.bind(this));
	},

	_doSave: function() {
		var type = $('.set_pic_opt:checked', this.wrapperEl).val();

		var newImgSrc = null;
		var action = null;

		var formData = [];


		switch (type) {
			case 'nochange':
				this.close();
				return;

			case 'gravatar':
				formData.push({ name: 'action', value: 'delete-picture' });
				newImgSrc = $('img.pic-gravatar', this.wrapperEl).attr('src');
				break;

			case 'newpic':
				formData.push({ name: 'action', value: 'set-picture' });
				var blobId = $('input.new_blob_id', this.wrapperEl).val();

				if (!blobId) {
					return;
				}

				formData.push({ name: 'blob_id', value: blobId });
				newImgSrc = $('img.pic-new', this.wrapperEl).attr('src');

				break;

			default:
				return;
		}

		$.ajax({
			url: this.options.saveUrl,
			type: 'POST',
			dataType: 'json',
			data: formData
		});

		this.page.getEl('picture_display').attr('src', newImgSrc);

		this.close();
	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		if (this.overlay) {
			this.overlay.close();
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});
