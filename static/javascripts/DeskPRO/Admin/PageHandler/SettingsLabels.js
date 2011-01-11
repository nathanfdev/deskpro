Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.SettingsLabels = new Class({
	
	Extends: DeskPRO.Admin.PageHandler.Basic,
	
	createLi: null,
	
	initPage: function() {
		this.createLi = $('li.create:first');
		$('button.save-trigger', this.createLi).click(this.saveLabel.bind(this));
		
		// Delete button for each label
		var self = this;
		$('ul.item-list').delegate('li.delete-trigger', 'click', function(ev) {
			ev.preventDefault();
			self.deleteLabel($(this).parent().parent().parent());
		});
	},
	
	saveLabel: function() {
		var label = $('input[name="label"]', this.createLi).val().trim();
		if (!label.length) {
			return;
		}
		
		$('button.save-trigger', this.createLi).html('...');
		
		$.ajax({
			url: this.getMetaData('newLabelUrl'),
			type: 'POST',
			context: this,
			data: {'label': label},
			dataType: 'json',
			success: function(data) {
				this._handleSaveLabelSuccess(data);
			}
		});
	},
	
	_handleSaveLabelSuccess: function(data) {
		
		$('button.save-trigger', this.createLi).html('Create New Label');
		
		if (data.errorMessage) {
			alert(data.errorMessage);
			return;
		}
		
		$(data.html).hide().insertAfter(this.createLi).slideDown();
		$('input[name="label"]', this.createLi).val('');
	},
	
	deleteLabel: function(li) {
		if (confirm('Are you sure you want to delete this label?')) {
			this._deleteLabel(li);
		}
	},
	
	_deleteLabel: function(li) {
		var label = li.data('label');
		$.ajax({
			url: this.getMetaData('delLabelUrl'),
			type: 'POST',
			context: this,
			data: {'label': label},
			dataType: 'json',
			success: function(data) {

			}
		});
		
		li.slideUp(function() {
			li.remove();
		});
	}
});