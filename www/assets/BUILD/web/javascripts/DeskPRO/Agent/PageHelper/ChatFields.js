Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Handles updating display based on department and rules
 */
DeskPRO.Agent.PageHelper.ChatFields = new Orb.Class({
	initialize: function(page) {
		var self = this;
		this.page = page;
		this.display = this.page.getEl('field_holders').find('.field-holders-table');
		this.mode = 'view';

		this.page.getEl('department_id').on('change', function() {
			self.updateDisplay();
		});

		this.page.getEl('field_edit_start').on('click', function(ev) {
			ev.preventDefault();
			self.openEditMode();
		});

		self.page.getEl('field_edit_cancel').on('click', function(ev) {
			ev.preventDefault();
			self.closeEditMode();
		});

		self.page.getEl('field_edit_save').on('click', function(ev) {
			self.page.getEl('field_edit_cancel').hide();
			self.page.getEl('field_edit_save').hide();
			self.page.getEl('field_edit_start').hide();
			self.page.getEl('field_edit_controls').addClass('loading');

			self.page.getEl('field_errors').hide().removeClass('on');

			self.saveChanges();
		});
	},

	openEditMode: function() {
    var self = this;
		this.mode = 'edit';

		this.display.addClass('mode-edit-on');
		this.page.getEl('field_edit_start').hide();
		this.page.getEl('field_edit_cancel').show();
		this.page.getEl('field_edit_save').show();
		this.page.getEl('field_edit_controls').removeClass('loading');
		this.page.getEl('field_holders').find('.errors-section').show();

		this.display.find('select[multiple]').each(function() {
			var min = $(this).width() + 30;
			var parent = $(this).closest('td').find('> div').first().width();
			if (parent) {
				min = Math.max(min, Math.ceil(parent / 1.75));
			}
			$(this).width(min);
		});
		DP.select(this.display.find('select'));

		this.updateDisplay();

		$('.Date.customfield input', this.display).each(function() {
			$(this).datetimepicker({
				format: 'YYYY-MM-DD',
				widgetParent: $(this).parent().css('position', 'relative'),
				icons: {
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

		$('.DateTime.customfield input', this.display).each(function(){
			$(this).datetimepicker({
				format: 'YYYY-MM-DD HH:mm',
				widgetParent: $(this).parent().css('position', 'relative'),
				icons: {
					time: 'far fa-clock',
					date: 'far fa-calendar',
					up: 'fas fa-chevron-up',
					down: 'fas fa-chevron-down',
					previous: 'fas fa-chevron-left',
					next: 'fas fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

    $('.File.customfield input', this.display).each(function() {
      var $el = $(this);

      if (!$('.mode-edit [data-blob-id='+$el.val()+']', self.display).length) {
        var $removeBtn = $('<em class="remove-attach-trigger"></em>');
        var $editWrapper = $('<div data-blob-id="'+$el.val()+'" />');
        var $fileLink = $(self.display).find('.mode-display [data-blob-id='+$el.val()+']').clone();

        $editWrapper.append($('<label>'+$('<div />').append($fileLink).html()+'</label>'));
        $editWrapper.append($removeBtn);
        $editWrapper.insertAfter($el);

        $removeBtn.on('click', function() {
          $el.remove();
          $editWrapper.remove();

          self.customFieldsUpload.updateVisibility();
        });
      }
    });

    this.customFieldsUpload = new DeskPRO.Agent.PageHelper.CustomFieldUpload(this.display);
		this.page.ownObject(this.customFieldsUpload);

		// Make sure field tab is selected
		this.page.getEl('fields_display_main_wrap_tab').click();
	},

	closeEditMode: function() {
		this.mode = 'view';

		this.display.removeClass('mode-edit-on');
		this.page.getEl('field_edit_save').hide();
		this.page.getEl('field_edit_cancel').hide();
		this.page.getEl('field_edit_start').show();
		this.page.getEl('field_edit_controls').removeClass('loading');
		this.page.getEl('field_holders').find('.errors-section').hide();
		this.updateDisplay();
	},

	updateDisplay: function() {
		if (this.mode == 'view') {
			this.updateDisplay_view();
		} else {
			this.updateDisplay_modify();
		}
	},

	updateDisplay_modify: function() {
		this.display.find('tbody.item .mode-display').hide();
		this.display.find('tbody.item .mode-edit').show();
	},

	updateDisplay_view: function() {
		this.display.find('tbody.item .mode-display').show();
		this.display.find('tbody.item .mode-edit').hide();
	},

	saveChanges: function() {
		var data = this.page.getEl('field_holders').find('input, select, textarea').serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/chat/' + this.page.meta.conversation_id + '/save-fields',
			type: 'POST',
			dataType: 'html',
			data: data,
			context: this,
			complete: function() {
				if (!this.display.hasClass('error')) {
					this.page.getEl('field_edit_cancel').hide();
					this.page.getEl('field_edit_save').hide();
					this.page.getEl('field_edit_start').show();
				}
				this.page.getEl('field_edit_controls').removeClass('loading');
			},
			success: function(new_holders) {
				this.replaceHolders(new_holders);
			}
		});
	},

	replaceHolders: function(html) {
		this.display.parent().html(html);
		this.display = this.page.getEl('field_holders').find('.field-holders-table');

		if (this.display.hasClass('error')) {
			this.openEditMode();
		} else {
			this.mode = 'view';
			this.updateDisplay();
		}
	}
});
