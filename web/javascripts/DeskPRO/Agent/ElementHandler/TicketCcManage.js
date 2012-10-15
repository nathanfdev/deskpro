Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.TicketCcManage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		var addUrl = this.el.data('add-url');
		var deleteUrl = this.el.data('delete-url');

		var list = $('ul', this.el).first();
		var newrow = $('li.newrow', this.el);
		var rowtpl = DeskPRO_Window.util.getPlainTpl($('.addrow-tpl', this.el));

		$(this.el.data('add-trigger')).on('click', function() {
			var row = $(rowtpl);
			row.find('.remove-row-trigger').on('click', function() {
				row.remove();
			});
			row.appendTo(list.closest('article'));
            row.autoCompleteElement = new DeskPRO.Agent.ElementHandler.SimpleAutoComplete(row);
		});

		this.el.find('ul').on('click', '.remove-row-trigger', function(ev) {
			var row = $(this).closest('li');
			var personId = row.data('person-id');
			var email = row.data('email-address');

			if (personId) {
				$.ajax({
					url: deleteUrl,
					type: 'POST',
					data: { person_id: personId },
					dataType: 'json',
					success: function(data) {
						self.el.closest('.tabViewDetailContent').find('ul.cc-row-list').each(function() {
							$(this).empty().html(data.cc_list || '');
						});
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


		this.el.closest('article').on('click', '.cc-saverow-trigger', function(ev) {
			var row = $(this).closest('.addrow');
			var email = $('input', row).val().trim();

			if (!email) {
				return;
			}

			row.addClass('loading');

			$.ajax({
				url: addUrl,
				type: 'POST',
				data: { email_address: email },
				dataType: 'json',
				complete: function() {
					row.remove();
				},
				success: function(data) {

					if (data.error) {
						if (data.error_code == 'invalid_email') {
							DeskPRO_Window.showAlert('Please enter a valid email address');
						}
						return;
					}

					self.el.closest('.tabViewDetailContent').find('ul.cc-row-list').each(function() {
						$(this).empty().html(data.cc_list || '');
					});
				}
			});
		});
	},
});
