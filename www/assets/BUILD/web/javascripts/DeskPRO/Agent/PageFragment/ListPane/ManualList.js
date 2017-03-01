Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.ManualList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
	},

	initPage: function(el) {
		this.wrapper = el;

		// this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
		// 	prefId: 'manual-filter',
		// 	resultId: this.meta.resultId,
		// 	refreshUrl: this.meta.refreshUrl,
		// 	prefSaveResultId: '0'
		// });
		// this.ownObject(this.displayOptions);
    //
		// this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
    //
		// });
		// this.ownObject(this.selectionBar);

		this.listWrapper = $('section.manual-simple-list', this.wrapper);

    var $rElement = $('<div></div>').insertAfter(this.listWrapper);

    this.listWrapper.hide();

    window.AgentLegacyBundle.renderManualTopicsTree(
      $rElement.get(0),
      this.meta.manualId,
      900,
			this.openTopic
		);

		// this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
		// 	contentListEl: this.listWrapper
		// });
		// this.ownObject(this.relatedContentList);
    //
		// this.enableHighlightOpenRows('manual', 'manual_id', 'topic.manual-');
    //
		// this.listNav = new DeskPRO.Agent.PageHelper.ListNav(this);
    //
		// Manual editor
		this._initManualEditor();
	},

	_initManualEditor: function() {
		var self = this;
		var manualEl = this.getEl('tab_cat');
		if (!manualEl[0]) {
			return;
		}

		this.getEl('manualfoot').find('.manual-save-trigger').on('click', function(ev){
			Orb.cancelEvent(ev);

			var postData = manualEl.find('input, select').serializeArray();

			self.getEl('manualfoot').addClass('dp-loading-on');
			$.ajax({
				url: $(this).data('save-url'),
				data: postData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					self.getEl('manualfoot').removeClass('dp-loading-on');
				},
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
				}
			});
		});

		var delManual = this.getEl('del_manual');
		delManual.find('.manual-del-trigger').on('click', function(ev) {
			Orb.cancelEvent(ev);
			delManual.addClass('dp-loading-on');

			$.ajax({
				url: $(this).data('save-url'),
				type: 'POST',
				dataType: 'json',
				complete: function() {
					delManual.removeClass('dp-loading-on');
				},
				success: function(ret) {
					if (ret.error_code && ret.error_code == 'not_empty') {
						DeskPRO_Window.showAlert('The manual could not be deleted because it is not empty.');
						return;
					}

					DeskPRO_Window.sections.publish_section.reload();
					DeskPRO_Window.runPageRoute('listpane:' + BASE_URL + 'agent/kb/list/0');
				}
			});
		});

		allUg = manualEl.find('.ug-check');
		ugEveryone = allUg.filter('.ug-1');
		ugOther    = allUg.not('.ug-1');

		var updateChecks = function(checked) {
			if (checked) {
				ugOther.prop('checked', true);
				ugOther.prop('disabled', true);
			} else {
				ugOther.prop('disabled', false);
			}
		};

		ugEveryone.on('click', function() {
			updateChecks(this.checked);
		});
		updateChecks(ugEveryone.prop('checked'));
	},

	openTopic: function(manualTopicId) {
		console.log(manualTopicId);
    window.DeskPRO_Window.runPageRoute(`manuals:/agent/manuals/topic/${manualTopicId}`);
	}
});
