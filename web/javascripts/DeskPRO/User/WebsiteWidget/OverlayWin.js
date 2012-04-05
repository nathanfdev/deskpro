Orb.createNamespace('DeskPRO.User.WebsiteWidget');

DeskPRO.User.WebsiteWidget.OverlayWin = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function() {

	},

	initPage: function() {
		var self = this;

		$(".widget-descpro select,.file").uniform();

		$('.widget-descpro .btn-activity, .widget-descpro .textarea, .widget-descpro,.widget-descpro .widget-container,.widget-descpro .btn,.widget-descpro .txt').each(function() {
			//PIE.attach(this);
		});

		$('.with-handler[data-element-handler]').each(function() {
			var el = $(this);
			var className = el.data('element-handler');
			var classObj = Orb.getNamespacedObject(className);

			if (!classObj) {
				DP.console.error("Unknown portal handler `%s` on element %o", className, this);
				return;
			}

			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('portal_'));
			}

			var obj = new classObj({ el: el });
		});

		this.activeTabBody = null;

		this.winNav = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#dp_overlay_navtabs').find('> a'),
			activeClassname: 'active',
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
			var url = Orb.appendQueryData(origUrl, '_partial', 'overlayWidget');

			self.tellParent('showContentPage', url);
		});

		this.tellParent('ready');

		var lastHeight, currentHeight;
		lastHeight = $('#widget_deskpro').height();
		self.tellParent('requestHeight', { height: lastHeight+20 });

		window.setInterval(function() {
			currentHeight = $('#widget_deskpro').height();
			if (lastHeight != currentHeight) {
				self.tellParent('requestHeight', { height: currentHeight+20 });
			}
			lastHeight = currentHeight;
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

		$('.auth-popup').click(function(ev) {
			ev.preventDefault();
			window.open($(this).attr('href'),'dpauth','width=600,height=400,location=0,menubar=0,scrollbars=0,status=0,toolbar=0,resizable=0');

			window.DP_LOGIN_NOTIFY = function() {
				window.location.href = window.location.href;
			};
		});

		// Prevents default browser action of navigating to a dropped file
		// if a drop target isnt configured yet (ie no tab open to accept a file)
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		$(document).bind('dragover', function (e) {
			var timeout = window.dropZoneTimeout;
			if (!timeout) {
				$('body').addClass('file-drag-over');
			} else {
				clearTimeout(timeout);
			}

			window.dropZoneTimeout = setTimeout(function () {
				window.dropZoneTimeout = null;
				$('body').removeClass('file-drag-over');
			}, 100);
		});

		$('#cancel_related').on('click', function() { self.disableRelatedMode(); });

		var searchCollect = $('.search-collect');
		this.searchCollect = searchCollect;
		var collectToucher = new DeskPRO.TouchCaller({
			timeout: 200,
			callback: function() {
				self.enableRelatedMode();
			}
		});
		searchCollect.on('keypress', function() {
			collectToucher.touch();
		});
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


	//##################################################################################################################
	//# Search Bar
	//##################################################################################################################

	enableRelatedMode: function() {
		$('#left_pane').addClass('showing-related');
		this.updateResults();
	},

	disableRelatedMode: function() {
		$('#left_pane').removeClass('showing-related');
		this.updateResults();
	},

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

		var touchCaller = new DeskPRO.TouchCaller({
			timeout: 200,
			callback: function() {
				self.updateResults();
			}
		});

		this.searchBox.on('keypress', function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				ev.preventDefault();//dont enter enter key
				self.updateResults();
			} else {
				touchCaller.touch($(this).val().trim());
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

		if ($('#left_pane').hasClass('showing-related')) {
			q = [];
			this.searchCollect.each(function() {
				q.push($(this).val());
			});

			q = q.join(' ').trim();
		}

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
						self.newTicketForm.find('.error').removeClass('error');
						Object.each(data.errors, function(v,k) {
							var find = '.dp-form-row-' + k.replace(/\./g, '_');
							console.log(find);
							self.newTicketForm.find(find).addClass('error');
						});

						return;
					}

					self.newTicketForm.hide();
					$('#dp_newticket_done').show();
				}
			});
		});
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
						self.newTicketForm.find('.error').removeClass('error');
						Object.each(data.errors, function(v,k) {
							var find = '.dp-form-row-' + k.replace(/\./g, '_');
							console.log(find);
							self.newFeedbackForm.find(find).addClass('error');
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
	}
});
