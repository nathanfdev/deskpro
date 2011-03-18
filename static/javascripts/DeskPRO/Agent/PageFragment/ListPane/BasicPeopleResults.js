Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,
	overlay: null,
	appendUrl: null,

	actionsBarHelper: null,

	resultTypeName: 'basic',
	resultTypeId: 'general',

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = this.wrapper;

		this._initDisplayOptions();
		this._initInfiniteScroll();

		this.initFeaturesOnCollection(el, {
			routes: ['tr .with-route'],
			times: ['tr abbr.timeago']
		});

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));
	},

	destroyPage: function() {
		if (this.displayOptionsOverlay) {
			this.displayOptionsOverlay.destroy();
		}
	},

	//#########################################################################
	//# Display options
	//#########################################################################

	displayOptionsWrapper: null,
	displayOptionsOverlay: null,
	displayOptionsList: null,
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

		$('.close-trigger', overlay_wrapper).click((function() {
			this.displayOptionsOverlay.closeOverlay();
		}).bind(this));

		$('.save-trigger', overlay_wrapper).click((function() {
			this.saveDisplayOptions();
		}).bind(this));

		// Set default checked values based on table
		var self = this;
		$('.list thead th', this.contentWrapper).each(function() {
			$('li[data-field="'+$(this).data('field')+'"] input[type="checkbox"]', self.displayOptionsList).attr('checked', true);
		});
	},

	saveDisplayOptions: function() {

		$('.buttons .loading-off', this.displayOptionsWrapper).hide();
		$('.buttons .loading-on', this.displayOptionsWrapper).show();

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
	},


	//#########################################################################
	//# Infinite loading stuff
	//#########################################################################

	_initInfiniteScroll: function() {
		//console.log(this.contentWrapper.scrollTop()+50);
		//console.log(this._scrollInnerHeights() - this.contentWrapper.height());
		//console.log('-');
		this.contentWrapper.scroll((function() {
			if (this.contentWrapper.scrollTop()+50 >= this._scrollInnerHeights() - this.contentWrapper.height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},

	_scrollInnerHeights_cache: null,
	_scrollInnerHeights: function() {
		if (this._scrollInnerHeights_cache !== null) return this._scrollInnerHeights_cache;
		var h = 0;
		this.contentWrapper.children(':visible').each(function() {
			h += $(this).height();
		});

		this._scrollInnerHeights_cache = h;

		return h;
	},

	isLoadingNext: false,
	noMoreResults: false,
	nextSearchPage: function() {
		var last_page = parseInt($('.page-set:last', this.contentWrapper).data('page'));
		this.loadResultPage(last_page+1)
	},

	loadResultPage: function(page) {
		if (this.isLoadingNext|| this.noMoreResults) return;
		this.isLoadingNext = true;

		var loading = $('.loading-more', this.contentWrapper);
		loading.detach().appendTo(this.contentWrapper); // make sure its at the bottom
		loading.show();

		var url = this.getMetaData('pageUrl').replace('$page', page);
		if (this.appendUrl) {
			url += this.appendUrl;
		}

		$.ajax({
			cache: false,
			type: 'GET',
			url: url,
			context: this,
			dataType: 'json',
			success: function (data) {
				this._handleAjaxSuccess(data);
			}
		});
	},

	_handleAjaxSuccess: function(data) {

		this.isLoadingNext = false;
		$('.loading-more', this.contentWrapper).hide();

		if (data['no_more_results']) {
			this.noMoreResults = true;
			var nomore = $('.no-more-results', this.contentWrapper);
			nomore.detach().appendTo(this.contentWrapper); // make sure its at the bottom
			nomore.show();
			return;
		}

		this._scrollInnerHeights_cache = null;

		var html = data['html'];

		var el = $(html);
		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['abbr.timeago']
		});

		$('table.list', this.contentWrapper).append(el);

		if (this.loadFirst) {
			this.loadFirst = false;

			var a = $('td.subject:first a.with-route:first', el);
			if (a.length) {
				DeskPRO_Window.runPageRouteFromElement(a);
			}
		}
	}
});