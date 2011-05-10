Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.GeneralList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.initPopoutTriggers(this.contextEl);
		this.getMessageBroker().addMessageListener(this.contextEl.attr('id') + '.change', this.handleListChange.bind(this));
	},

	handleListChange: function(info) {
		var list = $('ul.item-list:first', this.contextEl);
		var exist = $(info.item_selector, list);

		var row = $(info.row_html);
		this.initPopoutTriggers(row);

		if (exist.length) {
			exist.replaceWith(row);
		} else {
			list.prepend(row);
		}
	}
});