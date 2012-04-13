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
			row.appendTo(list.parents('article'));
            row.autoCompleteElement = new DeskPRO.Agent.ElementHandler.SimpleAutoComplete(row);
		});

		var getReplyController = function() {
			return $('.ticket-reply-form', self.el.data('replybox-container')).data('handler');
		};

		this.el.parents('article').on('click', '.remove-row-trigger', function(ev) {
			var row = $(this).parents('.addrow');
			var personId = row.data('person-id');
			var email = row.data('email-address');

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

				var trb = getReplyController();
				trb.removeCc(email);
			}

			row.fadeOut('fast', function() {
				row.remove();
			});
		});


        this.el.parents('article').on('click', '.cc-saverow-trigger', function(ev) {
            var row = $(this).parents('.addrow');
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

                    li.appendTo(list);

                    var email = li.data('email-address');
                    var trb = getReplyController();
                    trb.addCc(email);
                    row.remove();
                }
            });
        });
	},
});
