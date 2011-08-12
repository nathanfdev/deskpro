Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.MergeTicket = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			ticketId: 0,
			destroyOnClose: false
		};

		this.setOptions(options);

		this.ticketId = this.options.ticketId;

		this.overlay = null;
	},

	_initOverlay: function() {
		if (this.overlay) return this.overlay;

		var data = [];

		Array.each(DeskPRO_Window.getTabWatcher().findTabType('ticket'), function(tab) {
			var tid = tab.page.getMetaData('ticket_id');
			if (tid && tid != this.ticketId) {
				data.push({
					name: 'open_ticket_ids[]',
					value: tid
				});
			}
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/ticket/merge',
				data: data
			}
		});

		this.overlay.addEvent('ajaxDone', this._initElements.bind(this));
	},

	_initElements: function() {

	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		this.overlay.close();
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});