Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.PersonHelper');

DeskPRO.Agent.PageFragment.Page.PersonHelper.UploadFile = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],
	initialize: function(page, options) {
		var self = this;

		this.options = {
			saveUrl: '',
			person_id: null,
			el: null
		};

		this.setOptions(options);

		this.el = options.el;
		this.deleteUrl = options.deleteUrl

		this.page = page;

		this.page.addEvent('destroy', this.destroy, this);

		this._initControls();
	},
	_initControls: function() {
		var self = this;
		var wrapper = this.el;

		DeskPRO_Window.util.fileupload(wrapper, {
			page: this.page,
			uploadTemplate: $('.template-upload', wrapper),
			downloadTemplate: $('.template-download', wrapper),
			formData: [{
					name: 'is_image',
					value: 0
				}],
			completed: function() {
				self.blobId = $('[name="blob_id"]', wrapper).val();
			}
		}).bind('fileuploadstart', function() {
			$('p.explain', wrapper).hide();
		}).bind('fileuploadadd', function() {
			$('.files', wrapper).empty();
			$('input[name=set_pic_opt]', wrapper).each(function() {
				$(this).attr('checked', $(this).val() == 'newpic');
			})
		});

		wrapper.on('click', '.save-trigger', function(e) {
			e.preventDefault();
			
			self._doSave(e);
		});
		
		wrapper.on('click', '.file-delete', function(e) {
			e.preventDefault();
			
			if (confirm('Are you sure?')) {
				var file_id = $(this).closest('.file-row').attr('id').split('_');
			
				file_id = file_id[2];
				
				$.ajax({
					url: self.deleteUrl,
					type: 'POST',
					dataType: 'json',
					data: {action: 'remove-file', file_id: file_id},
					success: function(data) {
						if (data.success) {
							$("#file_row_" + file_id).hide(function(){$(this).remove();});
						}
					}
				});
			}
			
			return false;
		});
		
		wrapper.on('click', '.file-edit', function(e) {
			e.preventDefault();
			
			var editable_row = $(this).closest('.file-row');
			
			$(this).closest('.file-row').addClass('editable');
			
			editable_row.addClass('editable');
			
			return false;
		});
		
		wrapper.on('click', '.file-edit-save', function(e) {
			e.preventDefault();
			
			var editable_row = $(this).closest('.file-row');
			
			var form = self.getEl('add_file_form');
			
			var file_id = $(this).closest('.file-row').attr('id').split('_');
			
			file_id = file_id[2];
			
			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				dataType: 'json',
				data: {file_id: file_id, note: editable_row.find('#file_note_input_' + file_id).val()},
				success: function(data) {
					if (data.success && data.html) {
						$("#file_row_" + file_id).replaceWith(data.html);
					}
				}
			});
			
			return false;
		});
		
		wrapper.on('blur', '.editable-note-input', function(e) {
			e.preventDefault();
			
			$(this).closest('.file-row').find('.file-edit-save').click();
		});
	},
	_doSave: function(e) {
		if (!this.blobId) {
			console.log('No blob ID');
			return false;
		}

		e.preventDefault();

		var wrapper = this.el;

		var self = this;

		var files = this.getEl('files_initial');

		var form = this.getEl('add_file_form');

		var note = form.find("#input_note").val();

		var formData = {
			note: note,
			blob_id: this.blobId
		};

		$.ajax({
			url: form.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: formData,
			success: function(data) {
				if (data.success && data.html) {
					files.find('.empty-notice').remove();
					files.append(data.html);
					self.activateFilesTab();
				}
			}
		});
	},
	activateFilesTab: function() {
		var data_tab = '#' + this.page.meta.baseId + '_files_tab';
		
		var row_count = this.getEl('files_initial').find('.file-row').length;
		
		$("li[data-tab-for='" + data_tab + "']").children('span.count').text(row_count);

		$("li[data-tab-for='" + data_tab + "']").click();
	},
	getEl: function(id) {
		var meta_id = '#' + this.page.meta.baseId + '_' + id;

		return $(meta_id);
	}
});