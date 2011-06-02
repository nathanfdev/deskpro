Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.MediaBrowser = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			wrapper: null
		};

		if (options) {
			this.setOptions(options);
		}

		var wrapper = this.wrapper = this.options.wrapper;
		this.tabs = new DeskPRO.UI.SimpleTabs({
			context: this.wrapper
		});

		this.uploadForm = $('form.upload-form', this.wrapper);
		var self = this;
		this.uploadForm.fileUploadUI({
			singleFileUploads: false,
			cancelSelector: 'button.cancel-trigger',
			uploadTable: $('.files-list', this.wrapper),
			downloadTable: $('.files-list', this.wrapper),
			dropZone: this.wrapper,
			buildUploadRow: function(files, index, handler) {
				return $('<div class="uploading">' + files[index].name + ' <button class="dp-button x-small cancel-trigger">Cancel</button></div>');
			},
			buildDownloadRow: function (files, handler) {
				var html = [];
				Array.each(files, function(file) {
					html.push(file.row_html);
				});

				html = html.join('');
				return self.createDownloadRows(html);
			}
		}).bind('fileuploaddragover', function(e) {
			console.log(e);
			wrapper.addClass('file-drag-over');
		});
	},

	createDownloadRows: function(html) {
		var els = $(html);

		//var rows = $('.blob-row', els);
		var rows = els;
		var self = this;
		rows.each(function() {
			var el = this;
			// Tags
			$(".blob-tags ul", this).tagit({
				enableBackspace: false,
				fieldName: 'labels',
				onchange: function() { self.saveFileChanges(el) }
			});

			// Title
			$('input.file-title').change(function() {
				self.saveFileChanges(el)
			});
		});

		return els;
	},

	saveFileChanges: function(rowEl) {
		rowEl = $(rowEl);
		
		var data = $(':input', rowEl).serializeArray();
		var blob_id = rowEl.data('blob-id');

		$.ajax({
			url: BASE_URL + 'agent/media-browser/update-blob/' + blob_id,
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				
			}
		});
	}
});