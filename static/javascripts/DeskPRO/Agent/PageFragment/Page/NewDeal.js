Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewDeal = new Orb.Class({

Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newdeal';
		this.allowDupe = true;
	},

        initPage: function(el) {
		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.parent(el);
                this._initDepartmentSection();
                $('button.submit-trigger', this.wrapper).click(this.submit.bind(this));


                var assignOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('assign_ob'),
			onClose: function(ob) {

				var agentId = parseInt(ob.getSelected('agents') || 0);
				//var agentTeamId = parseInt(ob.getSelected('teams') || 0);

				var obel = self.getEl('assign_ob');

				if (agentId && agentId != DESKPRO_PERSON_ID) {
					var val = 'agent:' + agentId;
					var text = $('.agent-label-' + agentId).first().text().trim();
				} else {
					var val = '';
					var text = 'Me';
				}

				$('input.input-agent', openForEl).val(val);
				
			}
		});




                $('.select-deal-type').change(function(){

                    var dealId = 0;
                    $.ajax({
				url: BASE_URL + 'agent/deals/' + dealId + '/ajax-save',
				data: { action: 'change-dealtype', deal_type_id: $(this).val() },
				type: 'POST',
				context: this,
				error: function() {

				},
				success: function(data) {
                                    $('.set-deal-stage').html(data.deal_stage);
				}
			});
                }) ;

        },

        submit: function() {
		var formData = this.form.serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/deals/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.success) {

					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/deal/' + data.deal_id);
					this.closeSelf();
				} else {
					alert('There was an error with the form');
				}
			}
		});
	},
        _initDepartmentSection: function() {

		//------------------------------
		// Assign ...
		//------------------------------

		var obEl = this.getEl('assign_ob');
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
	}




})