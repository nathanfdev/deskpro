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

		$('select.sub_department_id', this.el).change(function(){
			self.setDepartment($(this).val());
		});

		$('.with-sub-options:not(.department_id_wrapper)', this.el).each(function() {
			var parentSel = $('.parent-option', this);
			var wrapper = this;

			parentSel.change(function() {
				var val = $(this).val();
				var sub = $('.sub-options-' + val, wrapper);

				$('.sub-options', wrapper).hide();
				sub.show();
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
		$('.sub-options', wrapper).hide();

		var depId = this.depSelect.val();
		var sub = $('.sub-options-' + depId, wrapper);

		if (!sub.length) {
			this.setDepartment(depId);
			return;
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