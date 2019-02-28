Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewPerson = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newperson';
		this.allowDupe = true;
	},

	initScope: function() {
		var self = this;
		this.$scope = DeskPRO_Window.$scope.$new();
		this.$q = DeskPRO_Window.$q;
		this.$timeout = DeskPRO_Window.$timeout;

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			self.wrapper.data('$ngControllerController', self);
			$compile(self.wrapper.contents())(self.$scope);
		}]);
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		this.initScope();

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
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

		this.getEl('set_password').on('change', function(){
			var $div = $(this).parent().next('div');
			$(this).prop('checked') ? $div.show() : $div.hide();
		});

		var $passwordInput = this.getEl('set_password_input');
		this.getEl('set_password_trigger').find('input[name="newperson[set_password_radio]"]').on('change', function(){
			$(this).prop('checked') && 'set' === $(this).val() ? $passwordInput.show() : $passwordInput.hide();
		});

        var wrapper = $(this.wrapper).find('.upload-vcard-wrap');

        console.log(wrapper);
        //console.log(this.page);

        DeskPRO_Window.util.fileupload(wrapper, {
            page: this.page,
            uploadTemplate: $('.template-upload', wrapper),
            downloadTemplate: $('.template-download', wrapper),
            formData: [{
                    name: 'is_image',
                    value: 0
            }],
            completed: function() {
                //var self = this;

                self.blobId = $('input.new_blob_id', this.wrapper).val();

                $('.files .in', wrapper).css({
                    'height': 'auto',
                    'margin': '10px',
                    'text-transform': 'capitalize'
                }).html("Loading . . .");

                $.ajax({
                    url: BASE_URL + 'agent/misc/parse-vcard/' + self.blobId,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'set-is-disabled',
                        is_disabled: 1
                    },
                    success: function(vCard) {
                        self.isVCard = true;
                        $('.files .in', wrapper).html("");
                        vCard = vCard[0].fields;

                        for(var prop in vCard) {
                            if (typeof vCard[prop] === 'object') {
                                // it seems to be an array
                                for(var prop2 in vCard[prop]) {
                                    if (typeof vCard[prop][prop2] === 'string') {
                                        $('.files .in', wrapper).append(prop2 + ": " + vCard[prop][prop2] + "<br/>");
                                    } else if(typeof vCard[prop][prop2] === 'object') {
                                        $('.files .in', wrapper).append("<hr/>");
                                        for (var prop3 in vCard[prop][prop2]) {
                                            if (typeof vCard[prop][prop2][prop3] === 'string') {
                                        $('.files .in', wrapper).append(prop3 + ": " + vCard[prop][prop2][prop3] + "<br/>");
                                            }
                                        }
                                    }
                                }
                            } else {
                                $('.result').append(prop + ": " + vCard[prop] + "<br/>");
                            }
                        }
                    }
                });
            }
        }).bind('fileuploadstart', function() {
                $('p.explain', wrapper).hide();
        }).bind('fileuploadadd', function() {
                $('.files', wrapper).empty();
                $('input[name=set_pic_opt]', wrapper).each(function() {
                        $(this).attr('checked', $(this).val() == 'newpic');
                })
        });

        el.bind('fileuploaddone', function(event, data) {
            for(name in data.result[0].fields) {
                $('[name$="['+name+']"]', el).val(data.result[0].fields[name]);
            }
        });
        el.bind('fileuploadstart', function(event, data) {
        });

		self.wrapper.find('.dpe_select').each(function() {
			// Label input is handled by labels class
			if (!$(this).hasClass('labels-input')) {
				DP.select($(this));
			}
		});

    this.customFieldsUpload = new DeskPRO.Agent.PageHelper.CustomFieldUpload(this.wrapper);
		this.ownObject(this.customFieldsUpload);
	},

	markForReload: function() {
		if (!this.markedForReload) {
			this.markedForReload = true;
			this.addEvent('deactivate', this.closeSelf.bind(this));
		}
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', [ev]);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {
		var self = this;
		//return true;
		if (self.isVCard) {
			$.ajax({
				url: BASE_URL + 'agent/people/new/save',
				type: 'POST',
				data: {isVCard: true, 'blobId': self.blobId},
				dataType: 'json',
				context: this,
				success: function(data) {
					return self.createCallback(data);
				}
			});
		} else {
			var formData = { custom_fields_definitions: self.$scope.custom_fields_definitions };
			$('input[type="text"], input[type="password"], input[type="hidden"], input:checked, select, textarea', this.form).each(function() {
				var name = $(this).attr('name');
				var val  = $(this).val();

				if (name && val !== null) {
					if (name.match(/.+\[\]$/)) {
						if (!formData[name]) {
							formData[name] = [];
						}

						if (Array.isArray(val)) {
							formData[name] = val;
						} else {
							formData[name].push(val);
						}
					} else {
						formData[name] = val;
					}
				}
			});

			$.ajax({
				url: BASE_URL + 'agent/people/new/save',
				type: 'POST',
				data: formData,
				dataType: 'json',
				context: this,
				success: function(data) {
					return self.createCallback(data);
				}
			});
		}
	},

        createCallback: function(data) {
            if (data.success) {
                if (this.getEl('org_id').val().length && this.fromCompanyTab) {
                        DeskPRO_Window.getMessageBroker().sendMessage('new-org-user', {
                                organization_id: this.getEl('org_id').val(),
                                person_id: data.person_id
                        });
                } else {
                        DeskPRO_Window.runPageRoute('person:' + BASE_URL + 'agent/people/' + data.person_id);
                }

                DeskPRO_Window.getMessageBroker().sendMessage('agent.person.added', { person_id: data.person_id });
                this.closeSelf();
        } else {
                var errorMessages = $('<div/>');
                errorMessages.append('<p>Please correct the following errors with your form:</p>');

                data.error_messages.forEach(function(msg) {
                        errorMessages.append('<div>&bull; ' + msg + '</div>');
                });
                DeskPRO_Window.showAlert(errorMessages, 'error');
        }
        },

	setOrganization: function(org_id, org_name) {
		this.getEl('org_id').val(org_id);
		this.getEl('org_name').val(org_name);
		this.getEl('org_pos').show();
		this.updateUi();
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
				if (!self.labelsInput && eventData.tabContent.hasClass('tab-properties') && self.getEl('labels_input')[0]) {
					self.labelsInput = new DeskPRO.UI.LabelsInput({
						type: 'tickets',
						input: self.getEl('labels_input')
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
