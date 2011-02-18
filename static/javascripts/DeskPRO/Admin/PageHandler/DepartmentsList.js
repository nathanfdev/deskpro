Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.DepartmentsList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.initPopoutTriggers();
		DeskPRO_Window.getMessageBroker().addMessageListener('departments.list.change', this.handleListChange.bind(this));
	},

	handleListChange: function(info) {
		var exist = $('li.'+info.typename+'-'+info.department_id, list);

		if (exist.length) {
			var row = $(info.row_html);
			exist.replaceWith(row);
		} else {
			var list = $('ul.item-list.department-list-'+info.parent_id+':first');

			if (list.length) {
				var row = $(info.row_html);
				list.append(row);
			} else {
				var row = $('<ul class="item-list department-list-'+info.department_id+'">'+info.row_html+'</ul>');
				$('div.dep-lists').prepend(row);
			}
		}

		this.initPopoutTriggers(row);
	}
});