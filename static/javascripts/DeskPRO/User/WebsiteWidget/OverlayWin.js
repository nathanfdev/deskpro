Orb.createNamespace('DeskPRO.User.WebsiteWidget');

DeskPRO.User.WebsiteWidget.OverlayWin = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function() {
		this.tellParent('initialized');
	},

	initPage: function() {
		var self = this;

		this.activeTabBody = null;

		this.winNav = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#dp_overlay_navtabs').find('> li'),
			onTabSwitch: function(ev) {
				self.activeTabBody = ev.tabContent;
			}
		});

		this._initSearch();
		this._initNewTicket();
		this._initNewFeedback();
		this._initNewChat();

		$('form.with-form-validator').each(function() {
			var v = new DeskPRO.Form.FormValidator($(this));
			$(this).data('form-validator-inst', v);
		});

		$(document).on('click', '.view-item', function(ev) {
			ev.preventDefault();

			var origUrl = $(this).attr('href');
			var url = Orb.appendQueryData(origUrl, '_partial', 'overlay');

			self.showInlineContent(url);

			self.inlinePage.find('.dp-open-in-window').on('click', function() {
				window.open(origUrl);
			});
			self.inlinePage.find('.dp-not-answered').on('click', function() {
				self.hideInlinePage();
			});
			self.inlinePage.find('.dp-set-answered').on('click', function() {
				window.open(origUrl);
				self.tellParent('closeMe');
			});
		});

		this.tellParent('ready');

		var bodyTabHeight, lastBodyTabHeight;
		bodyTabHeight = lastBodyTabHeight = this.activeTabBody.height();

		window.setInterval(function() {
			lastBodyTabHeight = bodyTabHeight;
			bodyTabHeight = self.activeTabBody.height();

			if (lastBodyTabHeight != bodyTabHeight) {
				var fullHeight = self.activeTabBody.offset().top + bodyTabHeight;
				var gotHeight = self.tellParent('requestHeight', { height: fullHeight });
				if (gotHeight < fullHeight) {
					$('#right_pane_body').css('overflow', 'auto');
				} else {
					$('#right_pane_body').css('overflow', 'hidden');
				}
			}
		}, 80);

		// Sync name and email fields, and save them to cookies for next time too
		var names = $('input.name-field');
		var emails = $('input.email-field');

		if ($.cookie('dp_uname')) {
			names.val($.cookie('dp_uname'));
		}
		if ($.cookie('dp_uemail')) {
			names.val($.cookie('dp_uemail'));
		}

		names.on('change', function() {
			var val = $(this).val().trim();
			names.val(val);

			$.cookie('dp_uname', val);
		});

		emails.on('change', function() {
			var val = $(this).val().trim();
			emails.val(val);

			$.cookie('dp_uemail', val);
		});
	},


	/**
	 * Show an inline page
	 *
	 * @param {String} url
	 */
	showInlinePage: function(url) {
		var self = this;

		if (this.inlinePage) {
			this.inlinePage.remove();
			this.inlinePage = null;
		}

		if (!this.inlinePageFrame) {
			this.inlinePageWrap = $('<div id="dp_inline_page_wrap" />').hide().appendTo('body');
			this.inlinePageWrap.append('<span class="close"></span>');
			this.inlinePageWrap.find('span.close').on('click', function() {
				self.hideInlinePage();
			});
		}

		this.inlinePage = $('<iframe id="dp_inline_page_iframe" name="dp_inline_page_iframe" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="auto" width="100%" height="100%"></div>').appendTo(this.inlinePageWrap);

		this.inlinePageWrap.show();
		this.inlinePage.attr('src', url);
	},


	/**
	 *
	 * @param partialUrl
	 */
	showInlineContent: function(partialUrl, callback) {
		var self = this;
		if (this.inlinePage) {
			this.inlinePage.remove();
			this.inlinePage = null;
		}

		if (!this.inlinePageFrame) {
			this.inlinePageWrap = $('<div id="dp_inline_page_wrap" />').hide().appendTo('body');
			this.inlinePageWrap.append('<span class="close"></span>');
			this.inlinePageWrap.find('span.close').on('click', function() {
				self.hideInlinePage();
			});
		}

		this.inlinePage = $(document.getElementById('dp_content_overlay_tpl').innerHTML);
		this.inlinePage.appendTo(this.inlinePageWrap);

		$.ajax({
			url: partialUrl,
			dataType: 'html',
			context: this,
			success: function(html) {

				this.inlinePage.find('.dp-content-holder').html(html);

				if (callback) {
					callback(this.inlinePage);
				}
			}
		});

		this.inlinePageWrap.show();
	},


	/**
	 * Hide the inline page
	 */
	hideInlinePage: function() {
		this.inlinePageWrap.hide();
	},


	/**
	 * Pass a message up to the parent controller
	 *
	 * @param {String} messageId
	 * @param {Object} [data]
	 */
	tellParent: function(messageId, data) {
		data = data || null;

		if (window.parent.DpOverlayWidget) {
			console.log('[Sending] %s %o', messageId, data);
			return window.parent.DpOverlayWidget.childListen(messageId, data);
		} else {
			console.log('[Sending:No Comms] %s %o', messageId, data);
		}

		return null;
	},


	/**
	 * Recieves a message from the child inline frame. These are generally the messages about answering
	 * something as helpful etc.
	 *
	 * @param {String} messageId
	 * @param {Object} [data]
	 */
	listenInlineChild: function(messageId, data) {

	},


	//##################################################################################################################
	//# Search Bar
	//##################################################################################################################

	_initSearch: function() {
		var self = this;
		this.searchBox = $('#search_box');

		$('#search_box_go').on('click', function() {
			self.updateResults();
		});
		$('#search_box_clear').on('click', function() {
			self.clearSearch();
			self.searchBox.val('').focus();
		});
		this.searchBox.on('keypress', function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				ev.preventDefault();//dont enter enter key
				self.updateResults();
			} else {
				$('#search_box_clear').show();
			}
		});
	},

	clearSearch: function() {
		$('#search_content_list').empty().hide();
		$('#new_content_list').show();
		$('#search_box_clear').hide();

		if (this.searchAjax) {
			this.searchAjax.abort();
			this.searchAjax = null;
		}
	},

	updateResults: function() {
		var self = this;
		var q = this.searchBox.val().trim();

		if (!q.length) {
			this.clearSearch();
			return;
		}

		if (this.searchAjax) {
			this.searchAjax.abort();
			this.searchAjax = null;
		}

		$('#left_pane').addClass('loading');

		this.searchAjax = $.ajax({
			url: BASE_URL + 'search/omnisearch/' + encodeURI(q),
			dataType: 'html',
			context: this,
			complete: function() {
				$('#left_pane').removeClass('loading');
			},
			success: function(html) {
				var ul = $(html);
				ul.find('a').addClass('view-item');
				var lis = ul.find('> li');

				$('#new_content_list').hide();
				$('#search_content_list').empty().append(lis).show();

				var words = q.split(' ');
				words = words.filter(function(w) {
					if (w.length > 2) {
						return true;
					}
				});

				DeskPRO.WordHighlighter.highlight($('#search_content_list').get(0), words, true);
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
	},


	//##################################################################################################################
	//# New Feedback
	//##################################################################################################################

	_initNewFeedback: function() {
		var self = this;
		this.newFeedbackForm = $('#dp_newfeedback_form');
		this.newFeedbackForm.find('[required]').prop('required', false);

		this.feedbackCatSelect = this.newFeedbackForm.find('select.category_id');
		this.feedbackCatId = 0;

		var self = this;
		this.feedbackCatSelect.on('change', function() {
			self.handleFeedbackCatChange();
		});
		this.feedbackCatSelect.data('original-name', this.feedbackCatSelect.attr('name'));

		this.newFeedbackForm.on('submit', function(ev) {
			ev.preventDefault();
			var data = self.newFeedbackForm.find('select, input, textarea').serializeArray();

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
							self.newFeedbackForm.find(find).addClass('dp-error');
						});

						return;
					}

					self.newFeedbackForm.hide();
					$('#dp_newfeedback_done').show();
				}
			});
		});

		this.handleFeedbackCatChange();
	},

	handleFeedbackCatChange: function() {
		var wrapper = this.newFeedbackForm.find('.feedbackCat_id_wrapper');

		var allSubs = $('.dp-sub-options', wrapper).hide();
		$('select', allSubs).attr('name', '');

		var feedbackCatId = this.feedbackCatSelect.val();
		var sub = $('.sub-options-' + feedbackCatId, wrapper);

		if (!sub.length) {
			this.feedbackCatSelect.attr('name', this.feedbackCatSelect.data('original-name'));
			return;
		} else {
			this.feedbackCatSelect.attr('name', '');
			$('select', sub).attr('name', this.feedbackCatSelect.data('original-name'));
		}

		sub.show();
	},

	//##################################################################################################################
	//# New Chat
	//##################################################################################################################

	_initNewChat: function() {
		var self = this;
		this.newChatForm = $('#dp_newchat_form');
		this.newChatForm.find('[required]').prop('required', false);

		this.depSelect = this.newChatForm.find('select.department_id');
		this.departmentId = 0;

		var self = this;
		this.depSelect.on('change', function() {
			self.handleChatDepChange();
		});
		this.depSelect.data('original-name', this.depSelect.attr('name'));

		this.newChatForm.on('submit', function(ev) {
			ev.preventDefault();
			var data = {
				name: self.newChatForm.find('input[name="name"]').val(),
				email: self.newChatForm.find('input[name="email"]').val(),
				department_id: self.newChatForm.find('select[name="department_id"]').val()
			};

			self.tellParent('requestChat', data);

			$('#dp_newchat_form').hide();
			$('#dp_newchat_done').show();
		});

		this.handleChatDepChange();
	},

	handleChatDepChange: function() {
		var wrapper = this.newChatForm.find('.department_id_wrapper');

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
	}
});
