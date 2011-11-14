Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketCcManage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {

		var addUrl = this.el.data('add-url');
		var deleteUrl = this.el.data('delete-url');

		var newrow = $('li.newrow', this.el);
		var rowtpl = DeskPRO_Window.util.getPlainTpl($('.addrow-tpl', this.el));

		newrow.on('click', function() {
			var row = $(rowtpl);
			row.insertBefore(newrow);
		});

		this.el.on('click', '.remove-row-trigger', function(ev) {
			var row = $(this).closest('li');
			var personId = row.data('person-id');

			if (personId) {
				$.ajax({
					url: deleteUrl,
					type: 'POST',
					data: { person_id: personId },
					success: function(html) {
						row.remove();
					},
					error: function() {
						row.show();
					}
				});

				row.fadeOut('fast');
			}

			row.fadeOut('fast', function() {
				row.remove();
			});
		});

		this.el.on('click', '.cc-saverow-trigger', function(ev) {
			var row = $(this).closest('li');
			var email = $('input', row).val().trim();

			if (!email) {
				return;
			}

			row.addClass('loading');

			$.ajax({
				url: addUrl,
				type: 'POST',
				data: { email_address: email },
				dataType: 'html',
				success: function(html) {
					var li = $(html);

					li.insertBefore(newrow);
					row.remove();
				}
			});
		});
	}
});
