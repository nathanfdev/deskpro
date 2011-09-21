Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewTicket = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.titleTxt = $('#newticket_ticket_subject');
		this.messageTxt = $('#newticket_ticket_message');

		this._initSuggestionsBox();
		this._initFields();
	},

	//#########################################################################
	//# Suggestions
	//#########################################################################

	_initSuggestionsBox: function() {
		this.suggestionsBox = $('.suggestions-box:first', this.el);
		this.resultsEl = $('.results:first', this.suggestionsBox);

		this.suggestionsUrl = this.el.data('suggestions-url');

		this.sugTitleTimer = null;
		this.sugMessageTimer = null;

		this.titleTxt.keypress((function() {
			if (this.sugTitleTimer) return;
			this.sugTitleTimer = this.updateSuggestions.delay(400, this);
		}).bind(this));

		this.messageTxt.keypress((function() {
			if (this.sugMessageTimer) return;
			this.sugMessageTimer = this.updateSuggestions.delay(1200, this);
		}).bind(this));
	},

	updateSuggestions: function() {

		if (this.sugTitleTimer) {
			window.clearTimeout(this.sugTitleTimer);
			this.sugTitleTimer = null;
		}
		if (this.sugMessageTimer) {
			window.clearTimeout(this.sugMessageTimer);
			this.sugMessageTimer = null;
		}

		var content = (this.titleTxt.val().trim() + ' ' + this.messageTxt.val().trim()).trim();

		if (!content.length) {
			this.suggestionsBox.hide();
			return;
		}

		// Already set to repeat
		if (this.doSuggestResend) {
			return;
		}

		if (this.isSuggestActive) {
			this.doSuggestResend = true;
			return;
		}

		this.isSuggestActive = true;

		$.ajax({
			url: this.suggestionsUrl,
			dataType: 'html',
			data: {'content': content},
			context: this,
			success: function(html) {
				this.isSuggestActive = false;

				if (this.doSuggestResend) {
					this.doSuggestResend = false;
					this.updateSuggestions();
				}

				this.resultsEl.html(html);

				if (!$('li:first', this.resultsEl).length) {
					this.suggestionsBox.hide();
				} else {
					this.suggestionsBox.show();
				}
			}
		});
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

				var allSubs = $('.sub-options', wrapper).hide();
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
			$('.with-sub-options', this.el).each(function() {
				var sub = $('.sub-options', this);
				if (sub) {
					var parent = $('.parent-option');
					parent.attr('name', '');
				}
			});
		});
	},

	handleDepChange: function() {
		var wrapper = $('.department_id_wrapper', this.el);

		var allSubs = $('.sub-options', wrapper).hide();
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
	}

});
