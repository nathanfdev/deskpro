Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.RecentTabs = new Orb.Class({
	initialize: function() {
		var self = this;
		this.maxSize = 20;
		this.recentTabIds = {};
		this.recent  = [];
		this.recentPendingSync = [];
		this.length  = 0;
		this.isOpen  = false;

		this.inputBound = $('#dp_omniinput');
		this.inputBound.on('focus keyup', function(ev) {
			var val = $.trim($(this).val());

			if (!val) {
				ev.stopImmediatePropagation();
				ev.preventDefault();
				ev.stopPropagation();
				if (window.DP_OMNI_QUICK_SEARCH) {
					window.DP_OMNI_QUICK_SEARCH.clearAll();
				}
				self.open();
			} else {
				self.close();
			}
		});
		this.inputBound.on('blur', function(ev) {
			window.setTimeout(function() {self.close();}, 150);
		});

		this.reloadRecentTabs();
	},

	reloadRecentTabs: function() {
		$.ajax({
			url: BASE_URL + 'agent/ui/load-recent-tabs.json',
			type: 'GET',
			dataType: 'JSON',
			context: this,
			success: function(data) {
				// Any tabs opened before the last list was re-loaded
				var readd = false;
				if (this.recent.length) {
					readd = this.recent;
				}

				this.recent = data;

				// Regen tab IDs lookup map
				Array.each(data, function(item) {
					this.recentTabIds[item[0] + '-' + item[1]] = true;
				}, this);

				if (readd) {
					Array.each(readd, function(item) {
						this.add(item[0], item[1], item[2], item[3], item[4]);
					}, this);
				}
			}
		});
	},

	_initUi: function() {
		if (this._hasInitUi) return;
		this._hasInitUi = true;

		this.wrapperEl = $('#dp_recent_list');
		this.wrapperEl.detach().appendTo('body');

		this.rowTpl = DeskPRO_Window.util.getPlainTpl('#dp_recent_list_row');

		DeskPRO_Window.initInterfaceLayerEvents(this.wrapperEl);
	},

	open: function() {
		if (!this.length) return;
		if (this.isOpen) return;
		this._initUi();

		var off = 22;
		this.wrapperEl.css({
			left: parseInt(this.inputBound.offset().left) - off,
			width: this.inputBound.outerWidth() + off
		});
		var list = this.wrapperEl.find('ul.result-list');
		list.empty();

		Array.each(this.recent, function(item) {
			var row = this.rowTpl;
			row = row.replace(/\{TYPE\}/g, item[0]);
			row = row.replace(/\{ID\}/g, item[1]);
			row = row.replace(/\{TITLE\}/g, Orb.escapeHtml(item[2]));

			var row = $(row);
			row.data('route', 'page:' + item[3]);
			row.attr('data-route', 'page:' + item[3]);

			list.append(row);
		}, this);

		this.wrapperEl.show();
		this.isOpen = true;
	},

	close: function() {
		if (!this.isOpen) return;

		this.wrapperEl.hide();
		this.isOpen = false;
	},

	setMaxSize: function(maxSize) {
		this.maxSize = maxSize;
	},

	add: function(type, id, title, url) {

		var ts = (new Date()).getTime() / 1000;
		var idString = type + '-' + id, idx = null;

		// If we already have the tab, remove it so it will be
		// re-added to the front of the array
		if (this.recentTabIds[idString]) {
			delete this.recentTabIds[idString];
			Array.each(this.recent, function(item, i) {
				if ((item[0] + '-' + item[1]) == idString) {
					idx = i;
					return false;
				}
			});

			if (idx !== null) {
				this.recent.splice(idx, 1);
			}
		}

		this.recent.unshift([type, id, title, url, ts]);
		this.recentTabIds[idString] = true;

		while (this.recent.length > this.maxSize) {
			this.recent.pop();
		}

		this.recentPendingSync.unshift([type, id, title, url, ts]);

		this.length = this.recent.length;
	},

	getAll: function() {
		return this.recent;
	},

	clear: function() {
		this.recent = [];
		this.length = 0;
	},

	getLast: function() {
		if (this.recent.length) {
			return this.recent[0];
		}

		return null;
	},

	getFirst: function() {
		if (this.recent.length) {
			return this.recent[this.recent.length-1];
		}

		return null;
	}
});