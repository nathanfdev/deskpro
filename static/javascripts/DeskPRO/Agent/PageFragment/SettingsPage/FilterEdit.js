Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.FilterEdit = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_filter_edit';
	},

	initPage: function(el) {
		var self = this;
		this.el = el;

		var critTpl = this.getEl('criteria_tpl');
		var critList = this.getEl('criteria_list');

		var editor = new DeskPRO.Form.RuleBuilder(critTpl);
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).on('click', function() {
				new_row.remove();
			});
		});
		$('.add-term', critList).data('add-count', 0).on('click', function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('.search-terms', critList), basename);
		});

		var count = 0;
		var terms = this.meta.terms;
		if (terms) {
			Array.each(terms, function(info, x) {
				var basename = 'terms[initial_' + x + ']';
				editor.addNewRow($('.search-terms', critList), basename, {
					type: info.type,
					op: info.op,
					options: info.options
				});
			});
		}

		this.getEl('save_btn').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var form = self.getEl('form');
			var postData = form.serializeArray();

			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				data: postData,
				dataType: 'json',
				success: function() {
					$('#settingswin').trigger('dp_settings_filtersupdated');
					self.fragmentOverlay.close();
				}
			});
		});
	}
});
