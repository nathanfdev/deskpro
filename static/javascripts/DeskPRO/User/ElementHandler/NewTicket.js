Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewTicket = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.titleTxt = $('#newticket_ticket_subject');
		this.messageTxt = $('#newticket_ticket_message');

		this.ticketForm = $('#dp_newticket_form');

		//this._initSuggestionsBox();
		this._initFields();
		this._initLoginForm(this.el);
		this._initPreticketStatus();
	},

	//#########################################################################
	//# Suggestions
	//#########################################################################

	_initSuggestionsBox: function() {
		this.inlineSuggestions = new DeskPRO.User.InlineSuggestions({
			elementWrapper: this.el,
			titleText: this.titleTxt,
			contentText: this.messageTxt,
			onResolved: this.setTicketSolvedAjax.bind(this),
			onResolvedRedirect: this.setTicketSolvedRedirect.bind(this),
			onNotResolved: this.setTicketUnsolvedAjax.bind(this)
		});
	},

	setTicketSolvedAjax: function(content_type, content_id) {
		this.setTicketSolvedStatusAjax(content_type, content_id, false);
	},
	setTicketUnsolvedAjax: function(content_type, content_id) {
		this.setTicketSolvedStatusAjax(content_type, content_id, true);
	},

	setTicketSolvedStatusAjax: function(content_type, content_id, setUnsolved) {
		var preticket_id = $('#dp_newticket_preticket_status_id').val();
		if (preticket_id > 0) {
			url = BASE_URL + 'tickets/new/content-solved-save.json?'
					+ 'preticket_status_id=' + escape(preticket_id) + '&'
					+ 'content_type=' + escape(content_type) + '&'
					+ 'content_id=' + escape(content_id);

			if (setUnsolved) {
				url += '&add_unsolved=1';
			}

			$.ajax({
				url: url,
				type: 'GET'
			});
		}
	},

	setTicketSolvedRedirect: function(url, content_type, content_id) {

		var preticket_id = $('#dp_newticket_preticket_status_id').val();
		if (preticket_id > 0) {
			url = BASE_URL + 'tickets/new/content-solved-redirect?'
				+ 'preticket_status_id=' + escape(preticket_id) + '&'
				+ 'content_type=' + escape(content_type) + '&'
				+ 'content_id=' + escape(content_id) + '&'
				+ 'url=' + escape(url);
		}

		window.location = url;
	},

	//#########################################################################
	//# Department and field stuff
	//#########################################################################

	_initFields: function() {
		this.depSelect = $('select.department_id', this.el);
		this.departmentId = 0;

		var self = this;
		this.depSelect.change(function() {
			self.handleDepChange();
		});
		this.depSelect.data('original-name', this.depSelect.attr('name'));

		$('select.sub_department_id', this.el).change(function(){
			self.setDepartment($(this).val());
		});

		$('.with-sub-options:not(.department_id_wrapper)', this.el).each(function() {
			var parentSel = $('.parent-option', this);
			parentSel.data('original-name', parentSel.attr('name'));

			var wrapper = this;

			parentSel.change(function() {
				var val = $(this).val();
				var sub = $('.sub-options-' + val, wrapper);

				var allSubs = $('.dp-sub-options', wrapper).hide();
				$('select', allSubs).attr('name', '');

				sub.show();

				if (sub.length) {
					// If there is a sub, zero out the parent name and give it to the child
					parentSel.attr('name', '');
					$('select', sub).attr('name', parentSel.data('original-name'));
				} else {
					// Otherwise make sure the parent has the proper name
					parentSel.attr('name', parentSel.data('original-name'));
				}
			});
		});

		$('form', this.el).submit(function(ev) {

			$('.sub-options:hidden', this.el).remove();

			// Just zero out the name of the parent, so
			// the child is always used
			$('.with.dp-sub-options', this.el).each(function() {
				var sub = $('.dp-sub-options', this);
				if (sub) {
					var parent = $('.parent-option');
					parent.attr('name', '');
				}
			});
		});
	},

	handleDepChange: function() {
		var wrapper = $('.department_id_wrapper', this.el);

		var allSubs = $('.dp-sub-options', wrapper).hide();
		$('select', allSubs).attr('name', '');

		var depId = this.depSelect.val();
		var sub = $('.sub-options-' + depId, wrapper);

		if (!sub.length) {
			this.depSelect.attr('name', this.depSelect.data('original-name'));
			this.setDepartment(depId);
			return;
		} else {
			this.depSelect.attr('name', '');
			$('select', sub).attr('name', this.depSelect.data('original-name'));
		}

		sub.show();
		var subDepId = $('select.department_id', sub);

		this.setDepartment(subDepId);
	},

	setDepartment: function(department_id) {
		this.clearAll();

		if (department_id == this.departmentId) {
			return;
		}

		this.departmentId = department_id;

		if (!window.DESKPRO_TICKET_DISPLAY) {
			return;
		}

		var activeDepId = this.departmentId;

		if (!window.DESKPRO_TICKET_DISPLAY[activeDepId]) {
			if (!window.DESKPRO_TICKET_CAT_PARENTS) {
				return;
			}

			while (true) {
				var activeDepId = window.DESKPRO_TICKET_CAT_PARENTS[activeDepId];
				if (!activeDepId) {
					return;
				}

				if (window.DESKPRO_TICKET_DISPLAY[activeDepId]) {
					return;
				}
			}
		}

		var depItems = window.DESKPRO_TICKET_DISPLAY[activeDepId];
		console.log('depItems %o', depItems);

		Array.each(depItems, function(item) {
			var itemId = this.getItemId(item);
			var itemEl = $('.' + itemId + ':first');
			itemEl.show();
		}, this);
	},

	clearAll: function() {
		$('.ticket-display-field').hide();
	},

	getItemId: function(item) {

		var itemId = item.item_type;
		if (item.item_id) {
			itemId += '_' + item.item_id;
		}

		return itemId;
	},


	//#########################################################################
	// In-page login form
	//#########################################################################

	_initLoginForm: function(context) {
		this.inlineLogin = new DeskPRO.User.InlineLoginForm({
			context: this.el
		});
	},

	//#########################################################################
	// Preticket status
	//#########################################################################

	_initPreticketStatus: function() {
		$('input, select', this.ticketForm).change((function() {
			this.updatePreticketStatus();
		}).bind(this));
	},

	updatePreticketStatus: function() {
		var formData = this.ticketForm.serializeArray();

		$.ajax({
			url: BASE_URL + 'tickets/new/save-status',
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(data) {
				$('#dp_newticket_preticket_status_id').val(data.preticket_status_id);
			}
		});
	}
});
