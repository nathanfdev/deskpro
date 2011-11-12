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
        this._initCustomFieldsEditor();


        var followersList = this.getEl('followers_list');
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

    _initCustomFieldsEditor: function() {


        var self = this;
    //		$('.save-trigger', this.custom_fields_edit).click((function() {
    //			var fieldEls = $(':input', self.custom_fields_edit);
    //			this._saveCustomFields(fieldEls);
    //		}).bind(this));
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
    }
});
