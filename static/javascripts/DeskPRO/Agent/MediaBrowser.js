Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.MediaBrowser = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			wrapper: null,
			additionalDropZone: null
		};

		if (options) {
			this.setOptions(options);
		}

		var wrapper = this.wrapper = this.options.wrapper;
		this.tabs = new DeskPRO.UI.SimpleTabs({
			context: this.wrapper
		});

		var dropZone = $();
		dropZone = dropZone.add(this.wrapper);
		if (this.options.additionalDropZone) {
			dropZone = dropZone.add(this.options.additionalDropZone);
		}

		this.uploadForm = $('form.upload-form', this.wrapper);
		var self = this;
		this.uploadForm.fileUploadUI({
			singleFileUploads: false,
			cancelSelector: 'button.cancel-trigger',
			uploadTable: $('.files-list', this.wrapper),
			downloadTable: $('.files-list', this.wrapper),
			dropZone: dropZone,
			buildUploadRow: function(files, index, handler) {
				return $('<div class="uploading">' + files[index].name + ' <button class="dp-button x-small cancel-trigger">Cancel</button></div>');
			},
			buildDownloadRow: function (files, handler) {
				var html = [];
				Array.each(files, function(file) {

					html.push(file.row_html);
				});

				html = html.join('');
				var els = self.createDownloadRows(html);

				self.fireEvent('filesUploaded', [els]);

				return els;
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
			$('input.file-title').on('change', function() {
				self.saveFileChanges(el)
			});

			$('.remove-trigger', el).on('click', function() {
				$(el).remove();
			});

			$('.link-trigger', el).on('click', function() {
				self.fireEvent('addLinkCode', [$(this).data('code'), el]);
			});
			$('.image-trigger', el).on('click', function() {
				self.fireEvent('addImageCode', [$(this).data('code'), el]);
			});
			$('.image-edit-trigger', el).on('click', function() {
				self.openImageEditor(el);
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
	},

	openImageEditor: function(el) {

		var media_browser_el = el;
		var blob_id = $(el).data('blob-id');
		var media_browser = this;

		$.ajax({
			url: BASE_URL + 'agent/media-browser/image-editor/' + blob_id,
			type: 'GET',
			context: this,
			dataType: 'html',
			success: function(html) {
				var content = $(html);
				var img = $('img', content);

				var jcrop_update = function(c) {
					$('input[name="x"]', content).val(c.x);
					$('input[name="y"]', content).val(c.y);
					$('input[name="x2"]', content).val(c.x2);
					$('input[name="y2"]', content).val(c.y2);
					$('input[name="w"]', content).val(c.w);
					$('input[name="h"]', content).val(c.h);
				}
				var japi = img.Jcrop({
					onChange: jcrop_update,
					onSelect: jcrop_update,
					minSize: [5,5],
					setSelect: [0,0,45,45]
				});

				$('.scale-slider', content).slider({
					range: 'min',
					value: 100,
					min: 1,
					max: 300,
					step: 1,
					slide: function(event, ui) {
						$('input.scale', content).val(ui.value);
						$('.scale-slider-value', content).html(ui.value + '%');

						var j_img = $('.jcrop-holder img', content);

						if (!img.data('real-w')) {
							img.data('real-w', img.width());
							img.data('real-h', img.height());
						}

						var w = img.data('real-w') * (ui.value / 100);
						var h = img.data('real-h') * (ui.value / 100);

						img.width(w);
						img.height(h);
						j_img.width(w);
						j_img.height(h);
					}
				});

				var overlay = new DeskPRO.UI.Overlay({
					contentElement: content,
					destroyOnClose: true
				});

				$('button.save-trigger', content).on('click', function() {
					var data = $(':input', content).serializeArray();
					$.ajax({
						url: BASE_URL + 'agent/media-browser/save-image-editor/' + blob_id,
						type: 'POST',
						context: this,
						data: data,
						dataType: 'json',
						success: function(data) {
							overlay.closeOverlay();
							media_browser.fireEvent('addImageEditedCode', ['[attach:' + data.blob_id +']', media_browser_el]);
						}
					});
				});

				overlay.openOverlay();
			}
		});
	}
});
