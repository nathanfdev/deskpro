Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'people-list';
		this.wrapper = null;
		this.contentWrapper = null;
		this.overlay = null;
		this.appendUrl = null;
		this.actionsBarHelper = null;
		this.resultTypeName = 'filter';
		this.resultTypeId = 0;
	},

	initPage: function(el) {

		var self = this;

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		this.resultTypeId = this.meta.cache_id || 0;

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'people-filter',
			resultId: this.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});
		this.ownObject(this.selectionBar);

		$('.detail-view-trigger', this.wrapper).on('click', (function() {
			this.switchViewType('list');
		}).bind(this));

		this.massActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
			menuElement: $('.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var itemEl = $(info.itemEl);
				var menuEl = itemEl.parent();
				var action = itemEl.data('action');

				if (menuEl.is('.submenu')) {
					action = menuEl.data('action');
				}

				var postData = self.selectionBar.getCheckedFormValues('ids');
				var removeFromList = false;

				switch (action) {
					case 'delete':

						break;

					case 'add-to-organization':
						var id = itemEl.data('organization-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'organization_id',
							value: id
						});
						break;

					case 'del-from-organization':

						break;

					case 'add-to-usergroup':
						var id = itemEl.data('usergroup-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'usergroup_id',
							value: id
						});
						break;

					case 'del-from-usergroup':
						var id = itemEl.data('usergroup-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'usergroup_id',
							value: id
						});
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
						} else {
							self.selectionBar.getChecked().each(function() {
								var name = $('.subject', $(this).parent());
								DeskPRO_Window.util.showSavePuff(name);
							});
						}

						self.selectionBar.checkNone();
					}
				});
			}
		});
		this.ownObject(this.massActionsMenu);

		this.enableHighlightOpenRows('person', 'person_id', 'article.person-');

		var opt = {
			resultIds: this.meta.peopleResultIds,
			perPage: this.meta.perPage || 50
		};
		if (this.meta.viewType == 'list') {
			opt.resultRowSelector = 'tr.row-item';
			opt.resultsContainer = $('.table-result-list table', el);
			opt.navEl = $('.bottom-action-bar', el);
		}
		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
		this.ownObject(this.resultsHelper);

		// We dont need them anymore, and resultsHelper
		// has its own strucutred array anyway,
		// since it could be large we can delete it from memory
		delete this.meta.peopleResultIds;
	},

	destroyPage: function() {

	},

	switchViewType: function(view_type) {

		var new_url = this.meta.viewTypeUrl.replace('$view_type', view_type);

		if (view_type == 'list') {

			var w = $(window).width() - 100;
			var h = $(window).height() - 100;

			var contentEl = $('<div>Loading...</div>');
			contentEl.width(w);
			contentEl.height(h);
			contentEl.css('overflow', 'auto');

			var  overlay = new DeskPRO.UI.Overlay({
				contentElement: contentEl,
				destroyOnClose: true,
				customClassname: 'no-padding',
				maxWidth: w,
				maxHeight: h
			});
			overlay.openOverlay();

			var pageReloader = function(new_url) {
				$.ajax({
					timeout: 20000,
					type: 'GET',
					url: new_url,
					dataType: 'html',
					success: function(html) {
						if (overlay.isDestroyed()) {
							return;
						}

						var page = DeskPRO_Window.createPageFragment(html, 'DeskPRO.Agent.PageFragment.ListPane.Basic');
						page.setMetaData('routeUrl', new_url);
						page.setMetaData('pageReloader', pageReloader);

						contentEl.html(page.html);
						page.fireEvent('render', [contentEl]);
						page.fireEvent('activate');
					}
				});
			}

			pageReloader(new_url);
			return;
		}

		DeskPRO_Window.loadListPane(new_url, null, function() {
			DeskPRO_Window.removePage(self);
		});
	},

	saveDisplayOptions: function() {

		$('.loading-off', this.displayOptionsWrapper).hide();
		$('.loading-on', this.displayOptionsWrapper).show();

		var data = [];
		var pref_name = 'prefs[agent.ui.people-'+ this.resultTypeName + '-display-fields.' + this.resultTypeId +'][]';

		$('input[type="checkbox"]:checked', this.displayOptionsList).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});


		// and the ordering
		data.push({
			name: 'prefs[agent.ui.people-'+ this.resultTypeName + '-order-by.' + this.resultTypeId +']',
			value: $('select[name="order_by"]', this.displayOptionsWrapper).val()
		});

		// We reload the same page which will have changes applied
		var url = this.getMetaData('refreshUrl');
		if (this.appendUrl) {
			url += this.appendUrl;
		}

		var self = this;

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: this.getMetaData('saveListPrefsUrl'),
			data: data,
			success: function() {

				DeskPRO_Window.loadListPane(url, null, function() {
					DeskPRO_Window.removePage(self);
				});

			}
		});
	}
});
