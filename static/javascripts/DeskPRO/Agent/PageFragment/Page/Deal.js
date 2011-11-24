Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Deal = new Orb.Class({

    Extends: DeskPRO.Agent.PageFragment.Basic,

    initializeProperties: function() {
        this.parent();
        this.allowDupe = true;
        this.TYPENAME = 'deal';
    },

    initPage: function(el) {
        var self = this;
        this.wrapper = el;
        this._initLabels();
        this._initDisplayOptions();
        this._initAgentSection();        
        this._initAssignPersonSection();
        this._initAssignOrganizationSection();
        this._initUserSection();
        this._initOrgEdit();
        this._initAssignAgentSection();
        this._removePersonAndOrg();
        this._initStatusMenus();


        // Add new associated task for deal
        
        $('.create_deal_task_btn').on('click', function() {
                        $('form#newTaskForm input, form#newTaskForm select').val('');
                        DeskPRO_Window.newTaskLoader.toggle();
        });

        // Name is editable
		var name = $('h3.name.editable:first', el);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/deals/' + this.meta.deal_id + '/ajax-save'
			},
			triggers: '.edit-name-gear'
		});

		// Attach click to wrapper because
		// this same code is used on popout on ticket,
		// and clicks dont bubble to document click
		$(this.wrapper).on('click', function (ev) {
			editable.handleDocumentClick(ev);
		});

        $('.profile-box-container.tabbed', this.wrapper).each(function() {
			var simpleTabs = new DeskPRO.UI.SimpleTabs({
				triggerElements: '> header li',
				context: this
			});

			self.ownObject(simpleTabs);
		});

        $('.select-deal-type').on('change', function(){

            var dealId = self.meta.deal_id;
            if (!dealId) {
                return;
            }

            $.ajax({
                url: BASE_URL + 'agent/deals/' + dealId + '/ajax-save',
                data: {
                    action: 'change-dealtype',
                    deal_type_id: $(this).val()
                },
                type: 'POST',
                context: this,
                error: function() {
                //row.show();
                },
                success: function(data) {
                    $('.set-deal-stage').html(data.deal_stage);
                    DeskPRO_Window.sections.deals_section.refresh();
                }
            });
        }) ;


        $('.select-deal-stage').live('change', function(){

            var dealId = self.meta.deal_id;
            if (!dealId) {
                return;
            }

            $.ajax({
                url: BASE_URL + 'agent/deals/' + dealId + '/ajax-save',
                data: {
                    action: 'change-dealstage',
                    deal_stage_id: $(this).val()
                },
                type: 'POST',
                context: this,
                error: function() {
                //row.show();
                },
                success: function(data) {
                //$('.set-deal-stage').html(data.deal_stage);
                }
            });
        }) ;

    },

    _removePersonAndOrg: function(){
        //var self = this;
        this.getEl('members_list').on('click', '.remove', function() {
            var row = $(this).closest('.member-row');
            var personId = row.data('person-id');
            if (!personId) {
                return;
            }

            row.fadeOut('fast');

            $.ajax({
                url: BASE_URL + 'agent/deals/' + self.meta.deal_id + '/ajax-save',
                data: {
                    action: 'remove-person',
                    person_id: personId
                },
                type: 'POST',
                context: this,
                error: function() {
                    row.show();
                },
                success: function() {
                    row.remove();
                    DeskPRO_Window.util.modCountEl(self.getEl('members_count'), '-');
                    DeskPRO_Window.sections.deals_section.refresh();
                }
            });
        });


        this.getEl('organizations_list').on('click', '.remove', function() {
            var row = $(this).closest('.organization-row');
            var organizationId = row.data('organization-id');
            if (!organizationId) {
                return;
            }

            row.fadeOut('fast');

            $.ajax({
                url: BASE_URL + 'agent/deals/' + self.meta.deal_id + '/ajax-save',
                data: {
                    action: 'remove-organization',
                    organization_id: organizationId
                },
                type: 'POST',
                context: this,
                error: function() {
                    row.show();
                },
                success: function() {
                    row.remove();
                    DeskPRO_Window.util.modCountEl(self.getEl('members_count'), '-');
                    DeskPRO_Window.sections.deals_section.refresh();
                }
            });
        });

    },

    _initDisplayOptions: function() {
        this.displayOptionsList = $('.display-options:first ul.sortable-list', this.contentWrapper);
        var overlay_wrapper = this.displayOptionsWrapper = $('.display-options:first', this.contentWrapper);

        this.displayOptionsOverlay = new DeskPRO.UI.Overlay({
            contentElement: overlay_wrapper,
            triggerElement: $('.display-options-trigger', this.contentWrapper),
            onContentSet: function(eventData) {
                $('ul.sortable-list', eventData.wrapperEl).sortable({
                    'axis': 'y'
                });
            }
        });
        this.ownObject(this.displayOptionsOverlay);

    },

    _initAssignAgentSection: function(){

        var el = this.getEl('agent_assign_ob');
        this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
            element: el,
            trigger: this.getEl('assign_ob_trigger'),
            onSave: function(ob) {
                var selections = ob.getAllSelected();
                var agent_id = parseInt(selections.agents || 0);

                var postData = [];
                postData.push({
                    name: 'agent_part_ids[]',
                    value: selections.agents
                });
                var label = $('.agent-label-' + agent_id, ob.getElement()).first().text().trim();

                var value = selections.agents;

                var el = $('.prop-agent-id');
                if (value == "0") value = 0;
                if (value == 0) {
                    el.text('Unassigned');
                    el.css('background-image', '');
                } else {
                    var agentInfo = DeskPRO_Window.getAgentInfo(value);
                    el.text(label);
                    el.css('background-image', agentInfo.pictureUrlSizable.replace('{SIZE}', 20));
                }
                $('.reply-agent-team-ob').slideUp();
                $.ajax({
                    url: BASE_URL + 'agent/deals/'+self.meta.deal_id+'/'+selections.agents+'/set-agent-parts.json',
                    type: 'POST',
                    dataType: 'json',
                    data: postData,
                    success: function(data) {
                        DeskPRO_Window.sections.deals_section.refresh();
                    }
                });

            }
        });

    },
    _initAgentSection: function(){

        //------------------------------
        // Assign ...
        //------------------------------
        var self = this;
        var obEl = this.getEl('agent_assign_ob');
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

            }
        });
    },

    _initLabels: function() {

        // Tags
        this.labelsList = $(".deal-tags ul", this.wrapper);

        this.labelsInput = new DeskPRO.UI.LabelsInput({
            type: 'deal',
            list: this.labelsList,
            onChange: this.saveLabels.bind(this)
        });
        this.ownObject(this.labelsInput);
    },

    saveLabels: function() {
        if (this._saveLabelsTimeout) {
            window.clearTimeout(this._saveLabelsTimeout);
        }

        this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
    },

    _doSaveLabels: function() {
        var data = $(':input', this.labelsList).serializeArray();

        $.ajax({
            url: this.getMetaData('labelsSaveUrl'),
            type: 'POST',
            context: this,
            data: data,
            dataType: 'json',
            success: function(data) {

            }
        });
    },
    
    _initAssignPersonSection: function() {

        var newrow = $('li.newpersonrow', this.el);
		

        newrow.on('click', function() {
            $('.choose-user').toggle();
            $('.add-new-user-container').remove();
        });
    },

    _initAssignOrganizationSection: function() {
        var newrow = $('li.neworgrow', this.el);

        newrow.on('click', function() {
            $('.choose-org').toggle();
            $('.add-new-org-container').remove();
        });
    },
    
    _initUserSection: function() {
        var self = this;
        var searchbox = this.getEl('user_searchbox');
        var userfields = this.getEl('user_choice');
        var rechooseBtn = this.getEl('switch_user');
        
        var placeUserRow = function(html) {
            self.placeUserRow(html);
        };

        searchbox.bind('personsearchboxclick', function(ev, personId, name, email, sb) {
            $.ajax({
                type: 'GET',
                url: BASE_URL + 'agent/deals/new/set-person-row/' + personId,
                dataType: 'html',
                data: {
                    'deal_id': self.meta.deal_id
                },
                context: this,
                success: function(html) {
                    $('input.person-id', searchbox).val(personId);
                    placeUserRow(html);
                }
            });
            sb.close();
            sb.reset();
        });
        searchbox.bind('personsearchboxclicknew personsearchenter', function(ev, term, sb) {
            $.ajax({
                type: 'GET',
                url: BASE_URL + 'agent/deals/new/create-person-row/0',
                data: {
                    'email': term
                },
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
            
            $('.save-trigger').live('click', function(){
                $.ajax({
                    type: 'GET',
                    url: BASE_URL + 'agent/deals/new/set-person-row/0',
                    data: {
                        'email': $('.add-new-user-container input.email').val(),
                        'name' : $('.add-new-user-container input.name').val(),
                        'deal_id': self.meta.deal_id
                    },
                    dataType: 'html',
                    context: this,
                    success: function(html) {
                        $('.add-new-user-container').remove();
                        placeUserRow(html);
                    }
                });

                return false;
            });
            $('.cancel-trigger').live('click', function(){
                $('.add-new-user-container').remove();
            });

        });
    },

    placeUserRow: function(html) {
        var searchbox = this.getEl('user_searchbox');
        var userfields = this.getEl('user_choice');
        var newrow = $('li.newpersonrow', this.el);
        var chooseuser = $('.choose-user');
        var row = $(html);


        row.insertBefore(newrow);
        userfields.empty();
        chooseuser.hide();
        userfields.show();
    },
    _initOrgEdit: function() {
        var self = this;
        var searchbox = this.getEl('org_searchbox');
        var orgfields = this.getEl('org_choice');
        var rechooseBtn = this.getEl('switch_org');

        rechooseBtn.click(function() {
            showOrganizationChoice();
            $('.org-id').val(0);
            return false;
        });

        var showOrganizationChoice = function() {
            orgfields.empty();
            orgfields.hide();
            searchbox.show();
            rechooseBtn.hide();
        };

        var placeOrganizationRow = function(html) {
            self.placeOrganizationRow(html);
        };

        var orgEdit    = this.getEl('org_edit_wrap');

        //orgEnableBtn
        this.getEl('org_searchbox').bind('orgsearchboxclick', function(ev, orgId, name) {

            $('.org-id', self.getEl('org_edit_wrap')).val().trim();
            $.ajax({
                type: 'POST',
                url: BASE_URL + 'agent/deals/new/set-organization-row/'+orgId,
                dataType: 'html',
                data: {                    
                    'deal_id': self.meta.deal_id
                },
                context: this,
                success: function(html) {
                    placeOrganizationRow(html);
                    $('input.organization_name', orgfields).val(term);
                    self.El('orgselect').val('');
                }
            });


        }).bind('orgsearchboxcreate', function(ev, term, name) {

            $.ajax({
                type: 'GET',
                url: BASE_URL + 'agent/deals/new/create-organization-row/0',
                dataType: 'html',
                context: this,
                success: function(html) {
                    placeOrganizationRow(html);
                    $('input.organization_name', orgfields).val(term);
                }
            });
        }).bind('orgsearchreverted', function(ev, term, name) {

            });

        $('.save-org-trigger').live('click', function(){

            $.ajax({
                type: 'POST',
                url: BASE_URL + 'agent/deals/new/set-organization-row/0',
                dataType: 'html',
                data: {
                    'name': $('.add-new-org-container input.name').val(),
                    'deal_id': self.meta.deal_id
                },
                context: this,
                success: function(html) {
                    placeOrganizationRow(html);
                    $('.add-new-org-container').remove();
                }
            });


        });

        $('.cancel-org-trigger').live('click', function(){
            $('.add-new-org-container').remove();
        });
    },

    placeOrganizationRow: function(html) {
        var searchbox = this.getEl('org_searchbox');
        var orgfields = this.getEl('org_choice');
        var newrow = $('li.neworgrow', this.el);
        var chooseorg = $('.choose-org');
        var row = $(html);


        row.insertBefore(newrow);
        //userfields.empty();
        chooseorg.hide();
        //searchbox.hide();
        orgfields.show();
    },

    _initStatusMenus: function() {

		var self = this;

		// Status
		var statusOb = new DeskPRO.UI.OptionBoxRevertable({
			trigger: this.getEl('status_trigger'),
			element: this.getEl('status_ob'),
			onSave: function(ob) {
				var catEl = ob.getSelectedElements('status');
				var catId = catEl.data('item-id');
				var title = catEl.data('full-title');

				self.getEl('status_label').text(title);

				$.ajax({
					url: BASE_URL + 'agent/deals/' + self.meta.deal_id + '/ajax-save',
					type: 'POST',
					data: {action: 'change-status', status: catId},
					context: self,
					dataType: 'json',
                                        success: function(data) {
                                            DeskPRO_Window.sections.deals_section.refresh();
                                        }
				});
			}
		});
		
	}
});
