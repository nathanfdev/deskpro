Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TicketTriggersPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$('li.trigger-val').each(function() {
			var li = $(this);
			var name = $(this).data('trigger-name');

			var fnUpdate = function() {
				var postData = [];
				postData.push({name: 'name', value: name});

				if (name == 'base_urgency') {
					postData.push({ name: 'num', value: $('input[name="base_urgency"]', li).val() });
				} else {
					postData.push({ name: 'time', value: $('input[name="time"]', li).val() });
					postData.push({ name: 'scale', value: $('select[name="scale"]', li).val() });
				}

				$('.loading-icon-small-inline', li).show();
				$.ajax({
					url: BASE_URL + '/admin/tickets/business-rules/save-built-in.json',
					type: 'post',
					dataType: 'json',
					data: postData,
					complete: function() {
						$('.loading-icon-small-inline', li).hide();
					},
					success: function() {
						DeskPRO_Window.util.showSavePuff($('input', li).first());
					}
				});
			}

			$('select', li).on('change', fnUpdate);
			$('input', li).on('change', fnUpdate);
			$('input', li).on('keypress', function(ev) {
				// Enter
				if (ev.keyCode == 13) {
					ev.preventDefault();
					fnUpdate();
				}
			});
		});

		$('ul.trigger-set').sortable({
			items: '> li:not(.trigger-val)',
			containment: 'parent',
			update: function() {
				var postData = [];
				$('li.is-trigger').each(function() {
					var id = $(this).data('trigger-id');
					if (id) {
						postData.push({name: 'trigger_ids[]', value: id});
					}
				});

				$.ajax({
					url: UPDATE_ORDER_URL,
					type: 'POST',
					dataType: 'json',
					data: postData
				});
			}
		});
	}
});
