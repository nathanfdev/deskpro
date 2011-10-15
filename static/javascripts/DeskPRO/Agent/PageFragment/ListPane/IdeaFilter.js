Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaFilter = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
		this.filterSearchForm = null;
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'idea-filter',
			resultId: this.meta.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});
		this.ownObject(this.selectionBar);

		var menuBtn = $('button.order-by-trigger:first', this.wrapper);
		this.orderByMenu = new DeskPRO.UI.Menu({
			triggerElement: menuBtn,
			menuElement: $('ul.order-by-menu:first', this.wrapper),
			onItemClicked: (function(info) {
				var item = $(info.itemEl);

				var prop = item.data('field')
				var label = item.text().trim();

				$('.label', menuBtn).text(label);

				var disOptWrap = this.displayOptions.getWrapperElement();
				var sel = $('select.sel-order-by', disOptWrap);
				$('option', sel).prop('selected', false);
				$('option.' + prop, sel).prop('selected', true);

				this.displayOptions.saveAndRefresh();

			}).bind(this)
		});
		this.ownObject(this.orderByMenu);

		this.listWrapper = $('section.idea-simple-list', this.wrapper);

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.relatedContentList);

		this.massActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
			menuElement: $('.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var itemEl = $(info.itemEl);
				var menuEl = itemEl.parent();
				var menuType = menuEl.data('menu-type');

				var postData = self.selectionBar.getCheckedFormValues('ids');
				var removeFromList = false;
				var action = '';

				switch (menuType) {
					case 'idea-status-menu':

						action = 'set-status';

						postData.push({
							name: 'status',
							value: itemEl.data('option-value')
						});
						break;

					case 'idea-category-menu':

						action = 'set-category';

						postData.push({
							name: 'category_id',
							value: itemEl.data('category-id')
						});

						break;

					case 'idea-massactions-menu':

						switch (itemEl.data('action')) {
							case 'delete':
								action = 'set-status';
								postData.push({
									name: 'status',
									value: 'hidden.deleted'
								});

								removeFromList = true;

								break;

							case 'spam':
								action = 'set-status';
								postData.push({
									name: 'status',
									value: 'hidden.spam'
								});

								removeFromList = true;

								break;
						}

						break;

					default:
						return;
						break;
				}

				$.ajax({
					url: BASE_URL + 'agent/ideas/filter/mass-actions/' + action,
					data: postData,
					type: 'POST',
					dataType: 'json',
					success: function(data) {
						if (removeFromList) {
							self.selectionBar.getChecked().parent().fadeOut('fast');
						}

						self.selectionBar.checkNone();
					}
				});
			}
		});
		this.ownObject(this.massActionsMenu);

		this.enableHighlightOpenRows('idea', 'idea_id', 'article.idea-');
	}
});
