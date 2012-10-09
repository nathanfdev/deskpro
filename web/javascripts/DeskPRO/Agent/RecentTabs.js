Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.RecentTabs = new Orb.Class({
	initialize: function() {
		var self = this;
		this.maxSize = 20;
		this.recent  = [];
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
			row =row.replace(/\{TITLE\}/g, Orb.escapeHtml(item[2]));

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

		// Make sure its not already added
		var found = false;
		Array.each(this.recent, function(item) {
			if (type == item[0] && id == item[1]) {
				found = true;
				return true;
			}
		});

		if (found) {
			return;
		}

		this.recent.unshift([type, id, title, url]);

		while (this.recent.length > this.maxSize) {
			this.recent.pop();
		}

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