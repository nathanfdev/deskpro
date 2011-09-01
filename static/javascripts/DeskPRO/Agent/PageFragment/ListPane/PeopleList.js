Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleList = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	TYPENAME: 'people-list',

	wrapper: null,
	contentWrapper: null,
	overlay: null,
	appendUrl: null,

	actionsBarHelper: null,

	resultTypeName: 'filter',
	resultTypeId: 0,

	initPage: function(el) {

		var self = this;

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		this.resultTypeId = this.meta.cache_id || 0;

		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['abbr.timeago']
		});

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.contentWrapper.addClass('scroll-content').tinyscrollbar();

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'people-filter',
			resultId: this.resultTypeId,
			refreshUrl: this.meta.refreshUrl
		});

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});

		$('.detail-view-trigger', this.wrapper).click((function() {
			this.switchViewType('list');
		}).bind(this));
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
