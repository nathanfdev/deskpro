Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.AgentChatHistory = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['abbr.timeago']
		});

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.contentWrapper.addClass('scroll-content').tinyscrollbar();
	},

	activate: function() {
		this.attachInfiniteScroll();
	},

	deactivate: function() {
		this.deattachInfiniteScroll();
	},

	//#########################################################################
	//# Infinite loading stuff
	//#########################################################################

	attachInfiniteScroll: function() {
		this._handleOnScroll_bound = this._handleOnScroll.bind(this);
		this.wrapper.parent().scroll(this._handleOnScroll_bound);
	},
	deattachInfiniteScroll: function() {
		if (this._handleOnScroll_bound) {
			this.wrapper.parent().unbind('scroll', this._handleOnScroll_bound);
			this._handleOnScroll_bound = null;
		}
	},

	_handleOnScroll_bound: null,
	_handleOnScroll: function() {
		var scrolling_area = this.wrapper.parent();
		var content_area = this.contentWrapper;

		if ((scrolling_area.scrollTop()+50+scrolling_area.height()) >= content_area.height()) {
			this.nextSearchPage();
		}
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
		}
	}
});