Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newticket';
		this.allowDupe = true;
	},

	initPage: function(el) {
		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.parent(el);

		this.form = $('form', this.wrapper).submit(function(ev) {
			ev.preventDefault();
		});

		this._initUserSection();
		this._initDepartmentSection();
		this._initSubjectSection();
		this._initMessageSection();
		this._initOtherSection();

		$('button.submit-trigger', this.wrapper).click(this.submit.bind(this));
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

		$.ajax({
			url: BASE_URL + 'agent/tickets/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.success) {

					if (data.comment_id) {
						DeskPRO_Window.getMessageBroker().sendMessage('agent-ui.comment-remove', {
							comment_id: data.comment_id,
							comment_type: data.comment_type
						});
					}

					DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);
					this.closeSelf();
				} else {
					alert('There was an error with the form');
				}
			}
		});
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

		this.getEl('me_btn').click((function(ev) {
			ev.preventDefault();

			var me = DeskPRO_Window.getAgentInfo(DESKPRO_PERSON_ID);
			this.getEl('usersearch').val(me.email);
			this.setUser(me.id);
		}).bind(this));

		this.getEl('usersearch').autocomplete({
			focus: true,
			delay: 300,
			minLength: 2,
			source: function(req, callback) {
				$.ajax({
					timeout: 8000,
					type: 'POST',
					url: BASE_URL + 'agent/people-search/search-quick',
					data: {term: req.term, format: 'json', limit: 20},
					dataType: 'json',
					context: this,
					success: function(data) {
						console.log(data);
						callback(data);
					},
					complete: function() {
						this.usersearchAjaxQuitCount = 0;
						this.usersearchAjax = null;
					}
				});
			},
			select: (function(ev, ui) {
				this.setUser(ui.item.value);

				ev.preventDefault();

				this.getEl('usersearch').val(ui.item.email);
			}).bind(this)
		});

		this.getEl('usersearch').blur((function() {
			if (!$('input.person_id', this.wrapper).length) {
				this.setUser(0);
			}
		}).bind(this));
		this.getEl('usersearch').keypress((function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				if (!parseInt(this.val('person_id').val())) {
					this.setUser(0);
				}
			}
		}).bind(this));
	},

	clearUser: function() {
		this.getEl('userinfo').hide().empty();
		this.getEl('new_userinfo').hide();
	},

	setUser: function(person_id) {

		this.getEl('user_section').removeClass('done');

		var data = [];

		person_id = parseInt(person_id) || 0;

		if (!person_id) {
			data.push({
				name: 'email_address',
				value: this.getEl('usersearch').val()
			});
			this.getEl('person_id').val(0);
		} else {
			this.getEl('person_id').val(person_id);
		}

		$.ajax({
			type: 'GET',
			url: BASE_URL + 'agent/tickets/new/get-person-row/' + person_id,
			data: data,
			dataType: 'html',
			context: this,
			success: function(html) {
				this.getEl('new_userinfo').hide();
				this.getEl('userinfo').empty().html(html).show();

				var person_id = parseInt($('.person_id', this.getEl('userinfo')).val());
				this.getEl('person_id').val(person_id);

				if (person_id) {
					this.getEl('user_section').addClass('done');
				}

				var self = this;
				$('button.more-fields', this.getEl('userinfo')).click(function() {
					self.getEl('user_section').addClass('more-on');
					$(this).remove();
				});
			}
		});
	},

	setGuestUser: function() {
		this.getEl('userinfo').hide();
		this.getEl('new_userinfo').show();
	},

	//#########################################################################
	//# Department Section
	//#########################################################################

	_initDepartmentSection: function() {
		var self = this;
		this.getEl('dep').change(function() {
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
				self.getEl('subject_section').addClass('done');
			}
		};

		this.getEl('subject').change(fn).blur(fn).keypress(fn);
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
				self.getEl('message_section').addClass('done');
			}
		};

		this.getEl('message').change(fn).blur(fn).keypress(fn);

		//------------------------------
		// Snippets Viewer
		//------------------------------

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: BASE_URL + 'agent/tickets/0/snippet-viewer',
			triggerElement: this.getEl('text_snippets_btn'),
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
						contentWrap.slideUp();
						navWrap.removeClass('on');
					} else {
						window.setTimeout(function() { contentWrap.slideDown() }, 20);
						navWrap.addClass('on');
					}
				}
			}).bind(this)
		});
		this.ownObject(this.otherTabs);

		// Agent selector
		this.assignAgentSelector = new DeskPRO.Agent.Widget.AgentSelector({
			agentList: $('#agent_selector_list'),
			showNone: true,
			multipleChoice: false,
			triggerElement: this.getEl('assign_agent_choose'),
			onSelectionChanged: (function(info) {
				var agentId = parseInt(info.selection);
				if (!info.selection) {
					this.getEl('assigned_agent').text('Unassigned');
					this.getEl('agent_id').val('0');
				} else {
					var agentInfo = DeskPRO_Window.getAgentInfo(agentId);
					this.getEl('assigned_agent').text(agentInfo.name);
					this.getEl('agent_id').val(agentId);
				}
			}).bind(this)
		});
		this.ownObject(this.assignAgentSelector);

		// Add CC's
		var self = this;
		$('.add-cc-trigger', this.wrapper).click(function() {
			var txt = self.getEl('add_cc_txt');
			var val = txt.val();
			var el = $('<li>' + val + '<input type="hidden" name="newticket[new_parts][]" value="'+val+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');

			$('.remove-trigger', el).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				el.remove();
			});

			el.appendTo(self.getEl('cc_list'));

			txt.val('');
		});

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

		this.wrapper.fileupload({
			url: BASE_URL + 'agent/misc/accept-upload',
			dropZone: this.wrapper,
			autoUpload: true,
			uploadTemplate: $('.template-upload', this.wrapper),
			downloadTemplate: $('.template-download', this.wrapper)
		});
	}
});









