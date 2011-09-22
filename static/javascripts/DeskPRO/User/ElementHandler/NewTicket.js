Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewTicket = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.titleTxt = $('#newticket_ticket_subject');
		this.messageTxt = $('#newticket_ticket_message');

		this.ticketForm = $('#dp_newticket_form');

		this._initSuggestionsBox();
		this._initFields();
		this._initLoginForm(this.el);
		this._initPreticketStatus();
	},

	//#########################################################################
	//# Suggestions
	//#########################################################################

	_initSuggestionsBox: function() {
		this.suggestionsBox = $('.dp-related-search', this.el);
		this.resultsEl = $('.results', this.suggestionsBox);
		this.moreLink = $('.more-link', this.suggestionsBox);
		this.lastSuggestions = null;
		this.lastString = null;
		this.notAnsweredResults = [];

		this.hasStartedSearch = false;

		this.moreLink.click((function(ev) {
			this.moreLink.hide();
			$('li', this.resultsEl).show();
		}).bind(this));

		this.suggestionsUrl = this.el.data('suggestions-url');

		this.sugTitleTimer = null;
		this.sugMessageTimer = null;

		this.titleTxt.keypress((function() {
			if (!this.hasStartedSearch) return;
			if (this.sugTitleTimer) return;
			this.sugTitleTimer = this.updateSuggestions.delay(400, this);
		}).bind(this));

		this.titleTxt.blur((function() {
			this.hasStartedSearch = true;
			this.updateSuggestions();
		}).bind(this));

		this.messageTxt.keypress((function() {
			if (!this.hasStartedSearch) return;
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

		if (this.lastSearchString && this.lastSearchString == content) {
			return;
		}

		this.lastSearchString = content;

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

				if (this.lastSuggestions && this.lastSuggestions == html) {
					return;
				}

				this.lastSuggestions = html;

				this.resultsEl.empty().html(html);

				// Make sure unsolved results dont reappear
				if (this.notAnsweredResults.length) {
					var x;
					for (x = 0; x < this.notAnsweredResults.length; x++) {
						$('li.' + this.notAnsweredResults[x], this.resultsEl).remove();
					}
				}

				if (!$('li:first', this.resultsEl).length) {
					this.suggestionsBox.hide();
					this.lastSuggestions = null;
				} else {

					if (!this.moreLink.data('has-mored')) {
						this.moreLink.data('has-mored', true);
						var count = $('li', this.resultsEl).length;

						if (count > 6) {
							var remainCount = count - 6;
							$('.count', this.moreLink).text(remainCount);
							this.moreLink.show();

							$('li', this.resultsEl).slice(5).hide();
						} else {
							this.moreLink.hide();
						}
					} else {
						this.moreLink.hide();
					}

					var self = this;
					$('li a[href]', this.suggestionsBox).click(function(ev) {
						ev.preventDefault();
						self.openSuggestedContent($(this));
					});

					this.suggestionsBox.show();
				}
			}
		});
	},

	openSuggestedContent: function(aEl) {

		var origUrl = aEl.attr('href');
		var url = Orb.appendQueryData(origUrl, '_partial', 'overlay');
		var contentType = aEl.data('content-type');
		var contentId = aEl.data('content-id');
		var self = this;

		var overlay = new DeskPRO.User.SuggestedContentOverlay({
			template: $('#dp_related_overlay_tpl').get(0).innerHTML,
			url: url,
			pageUrl: origUrl,
			contentType: aEl.data('content-type'),
			contentId: aEl.data('content-id'),
			destroyOnClose: true,
			openNear: $('#dp_newticket_related_container'),
			onInit: (function(overlayEl, controls, overlay) {
				// As soon as they click we subimt the request to record it
				$('.dp-set-answered', controls).click(function(ev) {
					ev.preventDefault();
					self.setTicketSolvedAjax(contentType, contentId);
				});

				// But we still send them through the redirect, so they
				// can visit the article quickly without waiting for the save to return
				$('.dp-answererd', controls).click(function(ev) {
					ev.preventDefault();
					var type = $(this).data('type');

					if (type == 'close') {
						self.setTicketSolvedRedirect(origUrl, contentType, contentId);
					} else {
						overlay.close();
					}
				});

				$('.dp-not-answered', controls).click(function(ev) {
					ev.preventDefault();
					self.setTicketSolvedAjax(contentType, contentId, true);

					aEl.parent().addClass('not-answered');
					self.notAnsweredResults.push(contentType + '-' + contentId);
					overlay.close();
				});
			}).bind(this)
		});
		overlay.open();
	},

	setTicketSolvedAjax: function(content_type, content_id, setUnsolved) {
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
		return;

		this.loginWrapper    = $('.dp-inline-login', context);
		this.passwordRow     = $('.dp-inline-login-pass', context);
		this.nonloginWrapper = $('.dp-inline-non-login', context);
		this.loginBtn        = $('.dp-login-trigger', context);

		$('#dp_inline_login_email').name('newticket[ticket][person]');

		$('.dp-newticket-login-open', context).click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (!this.loginWrapper.is('.open')) {
				this.openLogin();
			} else {
				this.closeLogin();
			}
		}).bind(this));

		this.loginBtn.click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			this.processLogin();
		}).bind(this));
	},

	openLogin: function() {
		this.loginWrapper.addClass('open');
		this.passwordRow.slideDown('fast');
		this.nonloginWrapper.animate({ opacity: '0.4', duration: 'fast' });
	},

	closeLogin: function() {
		this.passwordRow.slideUp('fast', (function() {
			this.loginWrapper.removeClass('open');
		}).bind(this));
		this.nonloginWrapper.animate({ opacity: '1', duration: 'fast' });
	},

	processLogin: function() {
		var postData = [];
		postData.push({
			name: 'email',
			value: $('#dp_inline_login_email').val()
		});
		postData.push({
			name: 'password',
			value: $('#dp_inline_login_pass').val()
		});

		$.ajax({
			url: BASE_URL + 'tickets/new/login',
			type: 'POST',
			data: postData,
			dataType: 'json',
			context: this,
			success: function(data) {
				var newEl = $(data.html);
				if (data.person_id) {
					$('#dp_inline_login_row').replaceWith(newEl);
					this.nonloginWrapper.css({ opacity: '1'});
				} else {
					$('#dp_newticket_login_row').replaceWith(newEl);
					$('.dp-newticket-login-pass', newEl).show();
				}

				if (data.name) {
					$('#newticket_person_name').val(data.name);
				}

				if (data.sections_replace) {
					Object.each(data.sections_replace, function(html, id) {
						$('#' + id).empty().replaceWith(html);
					});
				}

				this._initLoginForm(newEl);
			}
		})
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
