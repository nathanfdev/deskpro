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
                this._initUserSection();

                $('button.submit-trigger', this.wrapper).click(this.submit.bind(this));
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

            var self = this;
            var el = this.getEl('agent_assign_ob');
            this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
            element: el,
            trigger: this.getEl('assign_btn'),
            onSave: function(ob) {
                var selections = ob.getAllSelected();
                var agent_id = parseInt(selections.agents || 0);
                
                var label = $('.agent-label-' + agent_id, ob.getElement()).first().text().trim();

                var value = selections.agents;
                if (value == 0) {
                    label = 'Unassigned';
                }
                
                self.getEl('agent_id').val(agent_id);
		self.getEl('agent_label').text(label);


                $('.reply-agent-team-ob').slideUp();

            }
        });

	},

        _initUserSection: function() {
		var self = this;
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		rechooseBtn.click(function() {
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

        placeUserRow: function(html) {
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		userfields.empty();
		userfields.html(html);

		rechooseBtn.show();
		searchbox.hide();
		userfields.show();
	}


})