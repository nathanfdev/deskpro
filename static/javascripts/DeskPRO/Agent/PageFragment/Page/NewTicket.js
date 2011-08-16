Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	allowDupe: true,
	TYPENAME: 'newticket',

	initPage: function(el) {
		this.wrapper = el;
		this.parent(el);
		
		$('form', this.wrapper).submit(function(ev) {
			ev.preventDefault();
		});
		
		this._initUserSection();
		this._initDepartmentSection();
		this._initSubjectSection();
		this._initMessageSection();
		this._initOtherSection();
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
	},
	
	//#########################################################################
	//# Other Section
	//#########################################################################
	
	_initOtherSection: function() {
		var toggleProp = (function(prop) {
			var propDt = $('dt.prop-' + prop, this.getEl('other_props_input'));
			var propDd = $('dd.prop-' + prop, this.getEl('other_props_input'));
			
			if (propDt.is('.on')) {
				propDt.removeClass('on');
				propDd.removeClass('on');
			} else {
				propDt.addClass('on');
				propDd.addClass('on');
			}
		}).bind(this);
		
		$('li', this.getEl('other_props')).click(function() {
			toggleProp($(this).data('property'));
		});
		
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
				} else {
					var agentInfo = DeskPRO_Window.getAgentInfo(agentId);
					this.getEl('assigned_agent').text(agentInfo.name);
				}
			}).bind(this)
		});
	}
});









