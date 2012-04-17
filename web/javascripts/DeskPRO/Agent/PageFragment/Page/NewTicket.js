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
			self.submit();
		});

		this._initUserSection();
		this._initDepartmentSection();
		this._initSubjectSection();
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
		this.getEl('for_chat_id').val(data.chat_id);
		this.getEl('chat_title').text(data.chat_title);
		$('.pending-info.chat', this.wrapper).show();

		if (data.person_id) {
			this.setUser(data.person_id, data.session_id);
			this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
			this.getEl('user_section').hide();
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

		rechooseBtn.on('click', function() {
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

					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});
	},

	setUser: function(person_id, session_id) {
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
						html.push('<input type="hidden" name="add_cc_person[]" value="'+personId+'" />');
					html.push('</li>');

					html = html.join('');
					self.getEl('cc_list').append(html);
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
				html.push('<input type="text" class="name" name="new_cc_person_name['+rowid+']" placeholder="Enter a full name" />');
				html.push('<input type="text" class="email" name="new_cc_person_email['+rowid+']" placeholder="Enter an email address" />');
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
	//# Department Section
	//#########################################################################

	_initDepartmentSection: function() {

		//------------------------------
		// Assign ...
		//------------------------------

		var obEl = this.getEl('agent_selector');
		this.assignAgentOptionBox = new DeskPRO.UI.OptionBox({
			element: obEl,
			trigger: this.getEl('assign_btn'),
			onClose: function(ob) {
				var selections = ob.getAllSelected();

				// Agent
				var agent_id = parseInt(selections.agents || 0);
				self.getEl('agent_id').val(agent_id);
				var label = $('.agent-label-' + agent_id, obEl).text().trim();
				self.getEl('agent_label').text(label);

				// Agent Team
				var agent_team_id = parseInt(selections.teams || 0);
				self.getEl('agent_team_id').val(agent_team_id);
				var label = $('.agent-team-label-' + agent_team_id, obEl).text().trim();
				self.getEl('agent_team_label').text(label);

				self.updateUi();
			}
		});

		var self = this;
		this.getEl('dep').on('change', function() {
			if (parseInt($(this).val())) {
				self.getEl('dep_section').addClass('done');
			} else {
				self.getEl('dep_section').removeClass('done');
			}

			self.updateUi();
		});
	},

	//#########################################################################
	//# Subject Section
	//#########################################################################

	_initSubjectSection: function() {
		var self = this;
		var fn = function() {
			if ($(this).val().trim() == '') {
				self.getEl('subject_section').removeClass('done');
			} else {
				self.clearErrorCode('subject');
				self.getEl('subject_section').addClass('done');
			}
		};

		this.getEl('subject').on('change', fn).on('blur', fn).on('keypress', fn);
	},

	//#########################################################################
	//# Message Section
	//#########################################################################

	_initMessageSection: function() {
		var self = this;
		var fn = function() {
			if ($(this).val().trim() == '') {
				self.getEl('message_section').removeClass('done');
			} else {
				self.clearErrorCode('message');
				self.getEl('message_section').addClass('done');
			}
		};

		this.getEl('message').on('change', fn).on('blur', fn).on('keypress', fn);
		this.getEl('text_snippets_btn').on('click', function(ev) {
			ev.preventDefault();
			self.openSnippetsViewer();
		});

		/*
		var fieldDisplayFetch = new DeskPRO.Agent.PageHelper.TicketFieldDisplay();
		function updateFields() {
			$('.fieldprop', self.wrapper).hide();
			var fieldDisplay = fieldDisplayFetch.getFields($('select.department_id', self.wrapper).val());

			Object.each(fieldDisplay, function(fields, section) {
				DP.console.log(fields);
				Array.each(fields, function(f) {
					DP.console.log(f);
					if (f.item_type == 'ticket_field') {
						var classname = 'ticket-field-' + f.item_id;
					} else {
						var classname = f.item_type;
					}

					$('.' + classname, self.wrapper).show();
				});
			});
		};
		*/

		this.wrapper.find('.fieldprop select').each(function() {
			var el = $(this);
			if (el.is('.has-init')) return;

			var ob = new DeskPRO.UI.OptionBoxBuilder({
				values: el,
				noValText: 'None',
				selectDefault: true,
				title: 'Choose an option'
			});
			el.addClass('has-init');
		});

		var depOb = new DeskPRO.UI.OptionBoxBuilder({
			values: this.getEl('dep'),
			noValText: 'None',
			title: 'Department',
			selectDefault: true,
			onClose: function() {
				//updateFields();
			}
		});

		var statusMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('status'),
			title: 'Status'
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









