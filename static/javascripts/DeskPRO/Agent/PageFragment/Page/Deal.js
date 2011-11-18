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
                    url: BASE_URL + 'agent/deals/'+pageMeta.deal_id+'/'+selections.agents+'/set-agent-parts.json',
                    type: 'POST',
                    dataType: 'json',
                    data: postData
                });

            }
        });

        this.getEl('members_list').on('click', '.remove', function() {
            var row = $(this).closest('.member-row');
            var personId = row.data('person-id');
            if (!personId) {
                return;
            }

            row.fadeOut('fast');

            $.ajax({
                url: BASE_URL + 'agent/deals/' + pageMeta.deal_id + '/ajax-save',
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
                url: BASE_URL + 'agent/deals/' + pageMeta.deal_id + '/ajax-save',
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

        $('.select-deal-type').on('change', function(){

            var dealId = pageMeta.deal_id;
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

            var dealId = pageMeta.deal_id;
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

        var self = this;
        var el = this.getEl('org_assign_ob');
        this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
            element: el,
            trigger: this.getEl('org_assign_btn'),
            onSave: function(ob) {
                var selections = ob.getAllSelected();
                var agent_id = parseInt(selections.agents || 0);

                $('.reply-agent-team-ob').slideUp();

            }
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
                    'deal_id': pageMeta.deal_id
                    },
                context: this,
                success: function(html) {
                    if(html.success)
                        {
                            placeUserRow('');
                            //return false;
                        }
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
                url: BASE_URL + 'agent/deals/new/get-person-row/0',
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
                        'email': $('input.email').val(),
                        'name' : $('input.name').val(),
                        'deal_id': pageMeta.deal_id
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
    }
});
