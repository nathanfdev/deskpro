Orb.createNamespace('DeskPRO.User.WebsiteWidget');

DeskPRO.User.WebsiteWidget.OverlayWin = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function() {

	},

	initPage: function() {
		this.winNav = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#dp_overlay_navtabs').find('> li')
		});

		this._initSearch();
		this._initNewTicket();

		$('form.with-form-validator').each(function() {
			var v = new DeskPRO.Form.FormValidator($(this));
			$(this).data('form-validator-inst', v);
		});
	},

	//##################################################################################################################
	//# Search Bar
	//##################################################################################################################

	_initSearch: function() {
		var self = this;
		this.searchBox = $('#search_box');

		this.updateSearchCaller = new DeskPRO.TouchCaller({
			timeout: 250,
			callback: this.updateResults,
			context: this
		});

		this.searchBox.on('keyup', function(ev) {
			if (!$(this).val().trim()) {
				self.clearSearch();
			} else {
				self.updateSearchCaller.touch($(this).val().trim());
			}
		});
	},

	clearSearch: function() {
		$('#search_content_list').empty().hide();
		$('#new_content_list').show();
	},

	updateResults: function() {
		var self = this;
		var q = this.searchBox.val().trim();

		if (!q.length) {
			this.clearSearch();
			return;
		}

		$.ajax({
			url: BASE_URL + 'search/omnisearch/' + encodeURI(q),
			dataType: 'html',
			context: this,
			success: function(html) {
				var ul = $(html);
				var lis = ul.find('> li');

				$('#new_content_list').hide();
				$('#search_content_list').empty().append(lis).show();
			}
		});
	},

	//##################################################################################################################
	//# New Ticket
	//##################################################################################################################

	_initNewTicket: function() {
		var self = this;
		this.newTicketForm = $('#dp_newticket_form');
		this.newTicketForm.find('[required]').prop('required', false);

		this.depSelect = this.newTicketForm.find('select.department_id');
		this.departmentId = 0;

		var self = this;
		this.depSelect.on('change', function() {
			self.handleDepChange();
		});
		this.depSelect.data('original-name', this.depSelect.attr('name'));

		this.newTicketForm.on('submit', function(ev) {
			ev.preventDefault();
			var data = self.newTicketForm.find('select, input, textarea').serializeArray();

			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(data) {
					if (data.is_error) {
						Object.each(data.errors, function(v,k) {
							var find = '.dp-form-row-' + k.replace(/\./g, '_');
							console.log(find);
							self.newTicketForm.find(find).addClass('dp-error');
						});

						return;
					}

					self.newTicketForm.hide();
					$('#dp_newticket_done').show();
				}
			});
		});

		this.handleDepChange();
	},

	handleDepChange: function() {
		var wrapper = this.newTicketForm.find('.department_id_wrapper');

		var allSubs = $('.dp-sub-options', wrapper).hide();
		$('select', allSubs).attr('name', '');

		var depId = this.depSelect.val();
		var sub = $('.sub-options-' + depId, wrapper);

		if (!sub.length) {
			this.depSelect.attr('name', this.depSelect.data('original-name'));
			return;
		} else {
			this.depSelect.attr('name', '');
			$('select', sub).attr('name', this.depSelect.data('original-name'));
		}

		sub.show();
		var subDepId = $('select.department_id', sub);
	}
});
