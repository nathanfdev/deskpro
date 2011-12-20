Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.Departments.AjaxSave = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		this.el.on('change', '.set-tickets-state, .set-chat-state', function(ev) {
			var tr = $(this).closest('tr');
			self.saveFeatureState(tr.data('department-id'));
		});
	},

	registerChildHandler: function(handler, handlerName, el) {
		switch (handlerName) {
			case 'agent_selector':
				handler.addEvent('updated', this.saveAgentPermissions.bind(this));
				break;
		}
	},

	saveFeatureState: function(department_id) {
		var tr = $('tr.department-' + department_id, this.el);

		var chat = $(':checkbox.set-chat-state', tr).is(':checked') ? 1 : 0;
		var tickets = $(':checkbox.set-tickets-state', tr).is(':checked') ? 1 : 0;

		if (chat) {
			$('.chat-enabled', tr).show();
			$('.chat-disabled', tr).hide();
		} else {
			$('.chat-enabled', tr).hide();
			$('.chat-disabled', tr).show();
		}

		if (tickets) {
			$('.tickets-enabled', tr).show();
			$('.tickets-disabled', tr).hide();
		} else {
			$('.tickets-enabled', tr).hide();
			$('.tickets-disabled', tr).show();
		}

		var postData = [];
		postData.push({
			name: 'chat',
			value: chat
		});
		postData.push({
			name: 'tickets',
			value: tickets
		});

		var url = BASE_URL + '/admin/departments/' + department_id + '/save-feature-state.json';

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: postData
		});
	},

	saveAgentPermissions: function(department_id, app, agent_ids) {
		var url = BASE_URL + '/admin/departments/' + department_id + '/save-agents.json';

		var postData = [];
		postData.push({name: 'app', value: app});
		Array.each(agent_ids, function(id) {
			postData.push({
				name: 'agent_ids[]',
				value: id
			});
		});

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: postData
		});
	}
});
