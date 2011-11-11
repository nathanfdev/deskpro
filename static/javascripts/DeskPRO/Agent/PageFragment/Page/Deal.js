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
