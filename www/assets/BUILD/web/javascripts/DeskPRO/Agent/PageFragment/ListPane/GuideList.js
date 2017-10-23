Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.GuideList = new Orb.Class({
	Extends:          DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
	},

	initPage: function(el) {
		this.wrapper = el;

    this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
      prefId: 'topic-filter',
      resultId: this.meta.resultId,
      refreshUrl: this.meta.refreshUrl,
      prefSaveResultId: '0'
    });
    this.ownObject(this.displayOptions);

		this.listWrapper = $('section.guide-simple-list', this.wrapper);

    var $rElement = $('<div></div>').insertAfter(this.listWrapper);

    this.listWrapper.hide();

    window.AgentLegacyBundle.renderTopicsTree(
      $rElement.get(0),
      this.meta.guideId,
      900,
			this.openTopic,
      this.meta.display_fields
		);

		this._initGuideEditor();
	},

	_initGuideEditor: function() {
		var self = this;
		var guideEl = this.getEl('tab_cat');
		if (!guideEl[0]) {
			return;
		}

    var tree = this.getEl('cattree');
    var treeData = tree.data('treedata');
    var treeSave = this.getEl('cattree_struct');
    tree.tree({
      data: treeData,
      dragAndDrop: true,
      onCanMoveTo: function(moved_node, target_node, position) {
				return (position !== 'inside');
      }
    });
    tree.bind('tree.move', function(event) {
      event.move_info.do_move();
      treeSave.val(tree.tree('toJson'));
    });

		this.getEl('guidefoot').find('.guide-save-trigger').on('click', function(ev){
			Orb.cancelEvent(ev);

			var postData = guideEl.find('input, select').serializeArray();

			self.getEl('guidefoot').addClass('dp-loading-on');
			$.ajax({
				url: $(this).data('save-url'),
				data: postData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					self.getEl('guidefoot').removeClass('dp-loading-on');
				},
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
				}
			});
		});

		var delGuide = this.getEl('del_guide');
		delGuide.find('.guide-del-trigger').on('click', function(ev) {
			Orb.cancelEvent(ev);
			delGuide.addClass('dp-loading-on');

			$.ajax({
				url: $(this).data('save-url'),
				type: 'POST',
				dataType: 'json',
				complete: function() {
					delGuide.removeClass('dp-loading-on');
				},
				success: function(ret) {
					if (ret.error_code && ret.error_code == 'not_empty') {
						DeskPRO_Window.showAlert('The guide could not be deleted because it is not empty.');
						return;
					}

					DeskPRO_Window.sections.publish_section.reload();
					DeskPRO_Window.runPageRoute('listpane:' + BASE_URL + 'agent/kb/list/0');
				}
			});
		});

		allUg = guideEl.find('.ug-check');
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

	openTopic: function(topicId) {
    window.DeskPRO_Window.runPageRoute("guides:/agent/guides/topic/" + topicId);
	}
});
