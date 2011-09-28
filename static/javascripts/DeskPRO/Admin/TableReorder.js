Orb.createNamespace('DeskPRO.Admin');

/**
 * Adds drag+drop reordering to table rows
 */
DeskPRO.Admin.TableReorder = new Orb.Class({

	initialize: function(table) {
		var self = this;

		this.table = table;
		this.updateUrl = this.table.data('reorder-save-url');

		this.table.sortable({
			items: 'tbody',
			handle: 'tr.depth-0',
			placeholder: {
				element: function() {
					return $('<tbody class="placeholder"><tr><td colspan="100">&nbsp;</td></tr></tbody>');
				},
				update: function() {
					return;
				}
			},
			helper: function(event, element) {
				var t = self.table.clone(false);
				t.empty();
				t.append(element.clone());
				t.addClass('dragging');

				$('tr td:not(.title)', t).remove();
				t.css('width', 300);
				return t;
			}
		});

		$('tbody', this.table).each(function() {
			var tbody = $(this);
			tbody.sortable({
				items: 'tr.depth-1',
				placeholder: {
					element: function() {
						return $('<tr class="placeholder"><td colspan="100">&nbsp;</td></tr>');
					},
					update: function() {
						return;
					}
				},
				helper: function(event, element) {
					var t = self.table.clone(false);
					t.empty();
					t.append(element.clone());
					t.addClass('dragging');
					$('tr td:not(.title)', t).remove();
					t.css('width', 300);
					return t;
				}
			});
		});
	}
});
