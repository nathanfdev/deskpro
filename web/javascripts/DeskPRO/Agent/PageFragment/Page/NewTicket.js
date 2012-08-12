Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newticket';
		this.allowDupe = true;
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.parent(el);

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
		});

		this._initUserSection();
		this._initMessageSection();
		this._initOtherSection();
		this._initCcSelection();

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		DeskPRO_Window.util.fileupload(this.wrapper, { page: this });

		this.wrapper.find('.pending-info').on('click', '.reset', function(ev) {
			ev.preventDefault();
			self._resetForX();
		});

		this.addEvent('deactivate', function() {
			this._resetForX();
		}, this);

		var messageEl = this.getEl('message');
		var subjectEl = this.getEl('subject');
		var appliedMsgTpl = null;

		messageEl.on('keydown', function() {
			messageEl.addClass('editted');
		});
		subjectEl.on('keydown', function() {
			subjectEl.addClass('editted');
		});
		this.getEl('message_template').on('change', function() {
			var id = $(this).val();

			if (appliedMsgTpl == id) {
				return;
			}

			if (!id) {
				if (!messageEl.hasClass('editted')) {
					messageEl.val('');
				}
				if (!subjectEl.hasClass('editted')) {
					subjectEl.val('');
				}
				return;
			}

			appliedMsgTpl = id;

			$.ajax({
				url: BASE_URL + 'agent/tickets/get-message-template/'+id+'.json',
				type: 'GET',
				cache: false,
				dataType: 'json',
				success: function(data) {
					if (messageEl.hasClass('editted')) {
						var msgCmp = data.message.replace(/(\r\n|\n|\r)/gm, " ");
						var valCmp = messageEl.val().replace(/(\r\n|\n|\r)/gm, " ");
						if (valCmp.indexOf(msgCmp) === -1) {
							messageEl.insertAtCaret(data.message);
						}
					} else {
						messageEl.val(data.message);
					}

					if (subjectEl.hasClass('editted')) {
						if (subjectEl.val().indexOf(data.subject) === -1) {
							subjectEl.insertAtCaret(data.subject);
						}
					} else {
						subjectEl.val(data.subject);
					}
				}
			});
		});

		// This is so the select2 box has proper width for the longest template title
		var w = this.getEl('message_template').width() + 55;
		if (w > 350) w = 350;
		this.getEl('message_template').css('width', w);
		this.getEl('message_template_holder').css({
			visibility: 'visible',
			display: 'none'
		});

		window.setTimeout(function() {
			if (self.OBJ_DESTROYED) return;

			self.wrapper.find('select').each(function() {
				DP.select($(this));
			});
			self.updateUi();
		}, 300);

		var depSel = this.getEl('dep');

		var ticketReader = {
			getCategoryId: function() {
				var catId = self.getEl('cat').val();
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				var catId = self.getEl('pri').val();
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				var cat = self.getEl('prod');
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflow: function() {
				var catId = self.getEl('work').val();
				return parseInt(catId) || 0;
			}
		};

		var tplHolder = this.getEl('message_template_holder');
		var tplSel = this.getEl('message_template');
		var tplSelOrig = this.getEl('message_template_orig');

		var fieldDisplayFetch = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(ticketReader);
		function updateFields() {
			$('.ticket-field', self.wrapper).hide();
			var fieldDisplay = fieldDisplayFetch.getFields(depSel.val());

			Object.each(fieldDisplay, function(fields, section) {
				Array.each(fields, function(f) {
					if (f.field_type == 'ticket_field') {
						var classname = 'ticket-field-' + f.field_id;
					} else {
						var classname = f.field_type;
					}

					$('.' + classname, self.wrapper).not('.error-message').show();
				});
			});

			var depId = depSel.val();
			var opts = tplSelOrig.find('option.department_' + depId +', option.department_0').clone();
			if (opts[0]) {
				tplSel.empty();
				tplSel.append('<option value="0">Blank</option>');
				tplSel.append(opts);
				tplHolder.show();

				if (opts.length == 1) {
					tplSel.select2('val', tplSel.find('option').eq(1).val());
					tplSel.trigger('change');
				}
			} else {
				tplSel.empty();
				tplHolder.hide();
			}

			self.updateUi();
		};

		depSel.on('change', function(ev) {
			updateFields();
		});

		$('.ticket-field select', this.wrapper).on('change', function() {
			updateFields();
		});

		updateFields();
	},

	markForReload: function() {
		if (!this.markedForReload) {
			this.markedForReload = true;
			this.addEvent('deactivate', this.closeSelf.bind(this));
		}
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', ev);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {

		var formData = this.form.serializeArray();

		$('div.error.section', this.wrapper).removeClass('error');
		$('.error-message-on', this.wrapper).removeClass('error-message-on');

		this.wrapper.parent().addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/tickets/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			complete: function() {
				this.wrapper.parent().removeClass('loading');
			},
			success: function(data) {
				if (data.error) {
					if (data.is_dupe) {
						DeskPRO_Window.showConfirm('The ticket you tried to submit is an exact duplicate of an existing ticket. This new ticket was not saved.', function() {
							DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.dupe_ticket_id)
						}, function() {}, 'View Existing Ticket', 'hidden');
					} else {
						Array.each(data.error_codes, function(code) {
							this.showErrorCode(code);
						}, this);
						this.updateUi();
					}
				}

				if (data.ticket_id) {
					if (data.comment_id) {
						DeskPRO_Window.getMessageBroker().sendMessage('agent-ui.comment-remove', {
							comment_id: data.comment_id,
							comment_type: data.comment_type
						});
					}

					DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);
					this.closeSelf();
				}
			}
		});
	},

	showErrorCode: function(code) {
		$('.' + code + '.error-message', this.wrapper).addClass('error-message-on');
		switch (code) {
			case 'person_id':
			case 'person_no_user':
			case 'person_email_address':
				$('div.user-section.section', this.wrapper).addClass('error');
				break;

			case 'subject':
				$('div.subject-section.section', this.wrapper).addClass('error');
				break;

			case 'message':
				$('div.message-section.section', this.wrapper).addClass('error');
				break;
		}
	},

	clearErrorCode: function(code) {
		$('.' + code + '.error-message', this.wrapper).removeClass('error-message-on');
		switch (code) {
			case 'person_id':
			case 'person_no_user':
			case 'person_email_address':
				$('div.user-section.section', this.wrapper).removeClass('error');
				break;

			case 'subject':
				$('div.subject-section.section', this.wrapper).removeClass('error');
				break;

			case 'message':
				$('div.message-section.section', this.wrapper).removeClass('error');
				break;
		}

		this.updateUi();
	},

	setNewByComment: function(data) {
		this.getEl('message').val(data.message);
		this.getEl('for_comment_type').val(data.content_type);
		this.getEl('for_comment_id').val(data.comment_id);
		$('.pending-info.comment', this.wrapper).show();

		this.getEl('comment_title').text(data.name + " (" + data.email + ")");
		this.getEl('comment_object_link').data('route', 'page:' + data.object_url).text(data.object_title);

		this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
		this.getEl('usersearch').val(data.email_address);

		this.getEl('user_section').hide();
		this.getEl('user_searchbox').find('input.person-id').val(data.person_id);

		this.setUser(data.person_id);

		if (data.status == 'validating') {
			$('option[value="approve"]', this.getEl('comment_action')).hide();
		} else {
			$('option[value="approve"]', this.getEl('comment_action')).show();
		}

		this.updateUi();
	},

	setNewByChat: function(data) {
		var self = this;
		this.getEl('for_chat_id').val(data.chat_id);
		this.getEl('chat_title').text(data.chat_title);
		$('.pending-info.chat', this.wrapper).show();

		if (data.person_id) {
			this.setUser(data.person_id, data.session_id);
			this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
			this.getEl('user_section').hide();
		} else {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/tickets/new/get-person-row/0',
				data: { 'email': data.email },
				dataType: 'html',
				context: this,
				success: function(html) {
					self.placeUserRow(html);
					self.updateUi();
				}
			});
		}

		this.updateUi();
	},

	setNewByPerson: function(data) {
		this.getEl('person_title').text(data.name + " (" + data.email + ")");
		$('.pending-info.person', this.wrapper).show();
		this.setUser(data.person_id);
		this.getEl('user_section').hide();
		this.getEl('user_searchbox').find('input.person-id').val(data.person_id);

		this.updateUi();
	},

	_resetForX: function() {
		this.wrapper.find('.pending-info').hide();
		this.getEl('user_section').show();

		this.getEl('for_chat_id').val('');
		this.getEl('for_comment_type').val('');
		this.getEl('for_comment_id').val('');

		this.updateUi();
	},

	//#########################################################################
	//# User Section
	//#########################################################################

	_initUserSection: function() {
		var self = this;
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		rechooseBtn.on('click', function(ev) {
			ev.preventDefault(); // default would be submitting the ticket form
			showUserChoice();
		});

		var showUserChoice = function() {
			userfields.empty();
			userfields.hide();
			searchbox.show();
			rechooseBtn.hide();
			self.loadSnippetsViewer();
			self.updateUi();
		};

		var placeUserRow = function(html) {
			self.placeUserRow(html);
		};

		searchbox.bind('personsearchboxclick', function(ev, personId, name, email, sb) {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/tickets/new/get-person-row/' + personId,
				dataType: 'html',
				context: this,
				success: function(html) {
					self.clearErrorCode('person_id');
					self.clearErrorCode('person_email_address');
					self.clearErrorCode('person_no_user');

					$('input.person-id', searchbox).val(personId);
					placeUserRow(html);
					self.loadSnippetsViewer();
					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});
		searchbox.bind('personsearchboxclicknew personsearchenter', function(ev, term, sb) {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/tickets/new/get-person-row/0',
				data: { 'email': term },
				dataType: 'html',
				context: this,
				success: function(html) {
					placeUserRow(html);

					if (term.indexOf('@') !== -1) {
						$('input.email', userfields).val(term);
					} else {
						$('input.name', userfields).val(term);
					}

					var personId = self.getEl('user_choice').find('.set_userid').val();

					if (personId) {
						self.clearErrorCode('person_id');
						self.clearErrorCode('person_email_address');
						self.clearErrorCode('person_no_user');

						$('input.person-id', self.getEl('user_searchbox')).val(personId);
						self.loadSnippetsViewer();
					}

					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});
	},

	setUser: function(person_id, session_id) {
		var self = this;
		$.ajax({
			type: 'GET',
			url: BASE_URL + 'agent/tickets/new/get-person-row/0',
			data: { 'person_id': person_id, 'session_id': session_id },
			dataType: 'html',
			context: this,
			success: function(html) {
				this.placeUserRow(html);
			}
		});
	},

	placeUserRow: function(html) {
		var self = this;
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		userfields.empty();
		userfields.html(html);

		rechooseBtn.show();
		searchbox.hide();
		userfields.show();

		var e = $('input.email', userfields);
		if (e.length) {
			var fnCheck = function() {
				if (e.val().length && e.val().indexOf('@') !== -1) {
					self.clearErrorCode('person_email_address');
					self.clearErrorCode('person_no_user');
				}
			}
			fnCheck();
			e.on('change', fnCheck);
		}

		this.updateUi();
	},

	//#########################################################################
	//# CC Selection
	//#########################################################################

	_initCcSelection: function() {
		var self = this;
		var ccbox = this.getEl('user_ccbox');

		ccbox.bind('personsearchboxclick', function(ev, personId, name, email, sb) {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/people/' + personId + '/basic.json',
				dataType: 'json',
				context: this,
				success: function(data) {
					var html = [];
					html.push('<li>');
						html.push('<em class="remove"></em>');
						html.push('<a data-route="page:'+data.url+'">' + data.contact_name + '</a>');
						html.push('<input type="hidden" name="newticket[add_cc_person][]" value="'+personId+'" />');
					html.push('</li>');

					html = html.join('');
					self.getEl('cc_list').append(html);
					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});
		ccbox.bind('personsearchboxclicknew personsearchenter', function(ev, term, sb) {

			var rowid = Orb.uuid();

			var html = [];
			html.push('<li>');
				html.push('<em class="remove"></em>');
				html.push('<input type="text" class="name" name="newticket[add_cc_newperson]['+rowid+'][name]" placeholder="Enter a full name" />');
				html.push('<input type="text" class="email" name="newticket[add_cc_newperson]['+rowid+'][email]" placeholder="Enter an email address" />');
			html.push('</li>');

			html = $(html.join(''));

			if (term.indexOf('@') !== -1) {
				$('input.email', html).val(term);
			} else {
				$('input.name', html).val(term);
			}

			self.getEl('cc_list').append(html);
			self.updateUi();

			sb.close();
			sb.reset();
		});

		this.getEl('cc_list').on('click', 'em.remove', function() {
			$(this).closest('li').remove();
			self.updateUi();
		});
	},

	//#########################################################################
	//# Message Section
	//#########################################################################

	_initMessageSection: function() {
		var self = this;
		this.getEl('text_snippets_btn').on('click', function(ev) {
			ev.preventDefault();
			self.openSnippetsViewer();
		});

		this.loadSnippetsViewer();

		// Make the size of the message box based off of the height of the window
		var h = $(window).height();
		this.getEl('message').css('height', Math.max(h - 500, 200));
	},

	loadSnippetsViewer: function() {

		if (this.snippetsViewer) {
			this.snippetsViewer.destroy();
		}

		var url = BASE_URL + 'agent/tickets/0/snippet-viewer';

		var person_id = parseInt(this.getEl('person_id').val());
		if (person_id) {
			url += '?person_id=' + person_id;
		}

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: url,
			positionMode: this.meta.isPopover ? 'over' : 'side',
			onSnippetClick: function(info) {
				var val = self.getEl('message').val();
				if (val.length) {
					val += " ";
				}
				val += info.snippet;
				self.getEl('message').val(val);
			}
		});
	},

	openSnippetsViewer: function() {
		this.snippetsViewer.open();
	},

	//#########################################################################
	//# Other Section
	//#########################################################################

	_initOtherSection: function() {
		var self = this;

		this.otherTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('other_props_tabs')),
			context: this.getEl('other_props_tabs_content'),
			autoSelectFirst: false,
			onTabSwitch: function(eventData) {
				if (!self.labelsInput && eventData.tabContent.hasClass('tab-properties')) {
					self.labelsInput = new DeskPRO.UI.LabelsInput({
						type: 'tickets',
						textarea: $(".ticket-tags input", eventData.tabContent)
					});
					self.ownObject(self.labelsInput);
				}

				self.updateUi();
			},
			onTabClick: (function(ev) {
				var contentWrap = this.getEl('other_props_tabs_content');
				var navWrap = this.getEl('other_props_tabs_wrap');
				var tab = ev.tabEl;

				// Toggle content state if we're clicking for the first time,
				// or re-clicking a tab
				if (!$('.on', navWrap).length || tab.is('.on')) {
					if (contentWrap.is(':visible')) {
						contentWrap.hide();
						navWrap.removeClass('on');
					} else {
						contentWrap.show();
						navWrap.addClass('on');
					}
				}

				self.updateUi();
			}).bind(this)
		});
		this.ownObject(this.otherTabs);

		// Add CC's
		var self = this;
		$('.add-cc-trigger', this.wrapper).on('click', function() {
			var txt = self.getEl('add_cc_txt');
			var val = txt.val();
			var el = $('<li>' + val + '<input type="hidden" name="newticket[new_parts][]" value="'+val+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');

			$('.remove-trigger', el).on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				el.remove();
				self.updateUi();
			});

			el.appendTo(self.getEl('cc_list'));
			self.updateUi();

			txt.val('');
		});

		this.getEl('add_cc_txt').tokenField();

		// Attachments
		var list = $('.file-list', this.wrapper);
		$('input', list[0]).live('click', function() {
			var el = $(this);
			var li = el.parent();
			if (el.is(':checked')) {
				li.removeClass('unchecked');
			} else {
				li.addClass('unchecked');
			}
			self.updateUi();
		});
	}
});