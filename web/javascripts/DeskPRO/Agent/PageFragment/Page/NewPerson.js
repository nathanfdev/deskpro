Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewPerson = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newperson';
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

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		this._initNameSection();
		this._initOtherSection();

		this.stateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'newperson',
			listenOn: this.getEl('newperson')
		});
		this.ownObject(this.stateSaver);

		this.getEl('org_searchbox').on('orgsearchboxclick orgsearchboxcreate', function() {
			self.getEl('org_pos').show();
			self.updateUi();
		}).on('orgsearchboxcleared', function() {
			self.getEl('org_pos').hide();
			self.updateUi();
		});

        DeskPRO_Window.util.fileupload(el, {
            uploadTemplate: $('.template-upload', el),
            downloadTemplate: $('.template-download', el),
            url: BASE_URL + 'agent/misc/parse-vcard'
        });

        el.bind('fileuploaddone', function(event, data) {
            for(name in data.result[0].fields) {
                $('[name$="['+name+']"]', el).val(data.result[0].fields[name]);
            }
        });
        el.bind('fileuploadstart', function(event, data) {
        });
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
			url: BASE_URL + 'agent/people/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.success) {
					if (this.getEl('org_id').val().length && this.fromCompanyTab) {
						DeskPRO_Window.getMessageBroker().sendMessage('new-org-user', {
							organization_id: this.orgSel.val(),
							person_id: data.person_id
						});
					} else {
						DeskPRO_Window.runPageRoute('person:' + BASE_URL + 'agent/people/' + data.person_id);
					}

					DeskPRO_Window.getMessageBroker().sendMessage('agent.person.added', { person_id: self.meta.person_id });
					this.closeSelf();
				} else {
					alert('There was an error with the form');
				}
			}
		});
	},

	setOrganization: function(org_id) {
		this.orgSel.val(org_id);
		this.fromCompanyTab = true;
	},

	setGuessTerm: function(term) {
		if (term.indexOf('@') !== -1) {
			this.getEl('email').val(term);
		} else {
			this.getEl('name').val(term);
		}
	},

	//#################################################################
	//# Name/email section
	//#################################################################

	_initNameSection: function() {

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
						type: 'people',
						fieldName: 'newperson[labels]',
						textarea: $(".tags-wrap input", eventData.tabContent),
						onChange: function() {
							self.stateSaver.triggerChange();
						}
					});
					self.ownObject(self.labelsInput);
				}
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
			}).bind(this)
		});
		this.ownObject(this.otherTabs);
	}
});
