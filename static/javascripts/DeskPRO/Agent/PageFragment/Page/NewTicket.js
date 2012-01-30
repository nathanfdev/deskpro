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

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		DeskPRO_Window.util.fileupload(this.wrapper, { page: this });
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

		$.ajax({
			url: BASE_URL + 'agent/tickets/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.error) {
					Array.each(data.error_codes, function(code) {
						this.showErrorCode(code);
					}, this);
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
	},

	setNewByComment: function(data) {
		this.getEl('message').val(data.message);
		this.getEl('for_comment_type').val(data.content_type);
		this.getEl('for_comment_id').val(data.comment_id);
		$('.pending-info', this.wrapper).show();

		this.getEl('comment_object_link').data('route', 'page:' + data.object_url).text(data.object_title);

		this.getEl('usersearch').val(data.email_address);
		this.setUser(data.person_id);

		if (data.status == 'validating') {
			$('option[value="approve"]', this.getEl('comment_action')).hide();
		} else {
			$('option[value="approve"]', this.getEl('comment_action')).show();
		}
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
			}
		});

		var self = this;
		this.getEl('dep').on('change', function() {
			if (parseInt($(this).val())) {
				self.getEl('dep_section').addClass('done');
			} else {
				self.getEl('dep_section').removeClass('done');
			}
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

		var depOb = new DeskPRO.UI.OptionBoxBuilder({
			values: this.getEl('dep'),
			noValText: 'Choose a department',
			title: 'Department',
			onClose: function() {
				updateFields();
			}
		});

		var statusMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('status'),
			title: 'Status'
		});

		this.loadSnippetsViewer();

		// Tags
		this.labelsList = $(".ticket-tags textarea", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'tickets',
			textarea: this.labelsList
		});
		this.ownObject(this.labelsInput);

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

		this.otherTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('other_props_tabs')),
			context: this.getEl('other_props_tabs_content'),
			autoSelectFirst: false,
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
			});

			el.appendTo(self.getEl('cc_list'));

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
		});
	}
});









