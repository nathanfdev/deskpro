Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketCategoriesList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.initPopoutTriggers();
		DeskPRO_Window.getMessageBroker().addMessageListener('category.list.change', this.handleListChange.bind(this));
	},

	handleListChange: function(info) {
		var exist = $('li.'+info.typename+'-'+info.category_id, list);

		if (exist.length) {
			var row = $(info.row_html);
			exist.replaceWith(row);
		} else {
			var list = $('ul.item-list.category-list-'+info.parent_id+':first');

			if (list.length) {
				var row = $(info.row_html);
				list.append(row);
			} else {
				var row = $('<ul class="item-list category-list-'+info.category_id+'">'+info.row_html+'</ul>');
				$('div.cat-lists').prepend(row);
			}
		}

		this.initPopoutTriggers(row);
	}
});