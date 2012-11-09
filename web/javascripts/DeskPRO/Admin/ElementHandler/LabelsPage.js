Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.LabelsPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		var newForm = $('#new_label_form');
		newForm.on('submit', function(ev) {
			ev.preventDefault();

			var url = newForm.attr('action');
			var formData = newForm.serializeArray();
			var label = $('input[type="text"]', newForm).val().trim();

			if (!label.length) {
				return;
			}

			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: formData,
				complete: function() {
					$('input[type="text"]', newForm).val('');
				},
				success: function(data) {
					if (data.errorMessage) {
						alert(data.errorMessage);
						return;
					}

					self.updateDefCounts(data.def_counts);

					var existing = $('#labels_list li[data-label="' + label + '"]');
					existing.remove();

					var row = $(data.row_html).hide();
					row.prependTo($('#labels_list')).fadeIn('fast');

					DeskPRO_Window.util.showSavePuff($('> div', row));
				}
			});
		});

		var deleteUrl = this.el.data('delete-url');
		$('#labels_list').on('click', '.delete-trigger', function() {
			if (!confirm('Are you sure you want to delete this label?')) {
				return;
			}

			var row = $(this).closest('li.label-row');
			var label = row.data('label');

			$.ajax({
				url: deleteUrl,
				type: 'POST',
				dataType: 'json',
				data: { label: label },
				success: function(data) {
					row.fadeOut('fast', function() {
						row.remove();
					});
					self.updateDefCounts(data.def_counts);
				}
			});
		});

		var renameUrl = this.el.data('rename-url');
		$('#labels_list').on('click', '.rename-trigger', function() {
			var newLabel = prompt('Enter a new label to rename to');
			if (newLabel) {
				newLabel = newLabel.trim();
			}

			if (!newLabel || !newLabel.length) {
				return;
			}

			var row = $(this).closest('li.label-row');
			var label = row.data('label');

			$.ajax({
				url: renameUrl,
				type: 'POST',
				dataType: 'json',
				data: { old_label: label, new_label: newLabel },
				success: function(data) {

					row.remove();

					var existing = $('#labels_list li[data-label="' + label + '"]');
					existing.remove();

					var row = $(data.row_html).hide();
					row.prependTo($('#labels_list')).fadeIn('fast');

					DeskPRO_Window.util.showSavePuff($('> div', row));
					self.updateDefCounts(data.def_counts);
				}
			});
		});
	},

	updateDefCounts: function(def_counts) {
		Object.each(def_counts, function(v,k) {
			if (k == "TOTAL") {
				k = 'all';
			}
			$('#labels_count_' + k).text(v);
		});
	}
});
