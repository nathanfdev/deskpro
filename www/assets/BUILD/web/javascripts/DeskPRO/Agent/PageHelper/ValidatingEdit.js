Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.ValidatingEdit = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		this.page = page;
		this.inAction = false;
		this.options = {
			typename: '',
			contentId: 0,
			singleType: ''
		};
		this.setOptions(options);

		this.triggers = {
			inAction:           false,
			approveTrigger:     $('button.approve-trigger', this.page.wrapper),
			disapproveTrigger:  $('button.disapprove-trigger', this.page.wrapper),
			disapproveTrigger2: $('button.disapprove2-trigger', this.page.wrapper),
			skipTrigger:        $('button.skip-trigger', this.page.wrapper)
		};

		this.triggers.approveTrigger.on('click', this.approveEdit.bind(this));
		this.triggers.disapproveTrigger.on('click', this.showDisapproveForm.bind(this));
		this.triggers.disapproveTrigger2.on('click', this.disapproveEdit.bind(this));
		this.triggers.skipTrigger.on('click', this.skipValidateEdit.bind(this));
	},

	//#################################################################
	//# Validation controls
	//#################################################################

	showDisapproveForm: function() {
		$('.validating-bar:first .options', this.page.wrapper).hide();
		$('.validating-bar:first .disapprove-form', this.page.wrapper).show();
	},

	approveEdit: function() {
		if (!this.triggers.inAction) {
			this.triggers.inAction = true;
			$.ajax({
				url: BASE_URL + 'agent/publish/content/approve/' + this.options.typename + '/' + this.options.contentId + '.json',
				type: 'POST',
				context: this,
				dataType: 'json',
				success: function() {
					DeskPRO_Window.getMessageBroker().sendMessage('publish.validating.list-remove', {
						typename: this.options.typename,
						contentId: this.options.contentId
					});

					if (DeskPRO_Window.sections.feedback_section) {
						DeskPRO_Window.sections.feedback_section.reload();
					}
					this.triggers.inAction = false;
					DeskPRO_Window.removePage(this.page);
					DeskPRO_Window.runPageRoute('page:' + this.page.meta.url);
				}
			});
		}

	},

	disapproveEdit: function() {

		var reason = $('.validating-bar .disapprove-reason', this.page.wrapper).val();
		if (!this.triggers.inAction) {
			this.triggers.inAction = true;
			$.ajax({
				url:      BASE_URL + 'agent/publish/content/disapprove/' + this.options.typename + '/' + this.options.contentId + '.json',
				type:     'POST',
				context:  this,
				data:     { reason: reason },
				dataType: 'json',
				success:  function (info) {
					if (info.next_url) {
						DeskPRO_Window.runPageRoute('page:' + info.next_url);
					}

					DeskPRO_Window.getMessageBroker().sendMessage('publish.validating.list-remove', {
						typename:  this.options.typename,
						contentId: this.options.contentId
					});

					if (DeskPRO_Window.sections.feedback_section) {
						DeskPRO_Window.sections.feedback_section.reload();
					}
					this.triggers.inAction = false;

					DeskPRO_Window.removePage(this.page);
				}
			});
		}
	},

	skipValidateEdit: function() {
		if (!this.triggers.inAction) {
			this.triggers.inAction = true;
			$.ajax({
				url:      BASE_URL + 'agent/publish/content/get-next-validating/' + this.options.typename + '/' + this.options.contentId + '.json',
				type:     'POST',
				context:  this,
				dataType: 'json',
				success:  function (info) {
					if (info.next_url) {
						DeskPRO_Window.runPageRoute('page:' + info.next_url);
					}

					DeskPRO_Window.getMessageBroker().sendMessage('publish.validating.list-remove', {
						typename:  this.options.typename,
						contentId: this.options.contentId
					});
					this.triggers.inAction = false;
					DeskPRO_Window.removePage(this.page);
				}
			});
		}
	}
});
