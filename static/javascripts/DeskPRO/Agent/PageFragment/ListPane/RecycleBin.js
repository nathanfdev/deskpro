Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.RecycleBin = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	TYPENAME: 'recyclebin',

	wrapper: null,
	contentWrapper: null,
	barWrapper: null,
	layout: null,
	overlay: null,
	appendUrl: null,

	actionsBarHelper: null,

	resultTypeName: 'basic',
	resultTypeId: 'general',

	changeManager: null,

	loadFirst: false,

	initPage: function(el) {

		this.wrapper = el;

		var self = this;
		$('.type-list', el).each(function() {
			self.initTypeList($(this));
		});

		$('time.timeago', this.wrapper).timeago();

	},

	initTypeList: function(listWrap) {
		var type = listWrap.data('load-name');

		var self = this;
		$('.list-load-more', listWrap).click(function() {
			self.loadMore(type);
		});
	},

	loadMore: function(loadName) {
		var wrap = $('.' + loadName + '-list.type-list', this.wrapper);
		var table = $('table:first', wrap);
		var loadBtn  = $('.list-load-more', wrap);

		var lastTbody = $('tbody:last', table);
		var nextPage = parseInt(lastTbody.data('page')) + 1;

		$.ajax({
			url: BASE_URL + 'agent/recycle-bin/' + loadName + '/' + nextPage,
			dataType: 'json',
			success: function(data) {
				if (data.no_more_results) {
					loadBtn.hide();
				}

				if (data.count < 1) {
					return;
				}

				var rows = $(data.htmls);
				$('time.timeago', rows).timeago();

				table.append(rows);
			}
		});
	}
});
