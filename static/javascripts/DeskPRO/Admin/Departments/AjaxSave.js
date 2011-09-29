Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.Departments.AjaxSave = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		this.el.delegate('.set-tickets-state, .set-chat-state', 'change', function(ev) {
			var tr = $(this).closest('tr');
			self.saveFeatureState(tr.data('department-id'));
		});
	},

	registerChildHandler: function(handler, handlerName, el) {
		switch (handlerName) {
			case 'agent_selector':
				handler.addEvent('updated', this.saveAgentPermissions.bind(this));
				break;
			case 'usergroup_selector':
				handler.addEvent('updated', this.saveUsergroupPermissions.bind(this));
				break;
		}
	},

	saveFeatureState: function(department_id) {
		var tr = $('tr.department-' + department_id, this.el);

		var postData = [];
		postData.push({
			name: 'chat',
			value: $(':checkbox.set-chat-state', tr).is(':checked') ? 1 : 0
		});
		postData.push({
			name: 'tickets',
			value: $(':checkbox.set-tickets-state', tr).is(':checked') ? 1 : 0
		});

		var url = BASE_URL + 'admin/departments/' + department_id + '/save-feature-state.json';

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: postData
		});
	},

	saveAgentPermissions: function(department_id, agent_ids, agent_team_ids) {
		var url = BASE_URL + 'admin/departments/' + department_id + '/save-agents.json';

		var postData = [];
		Array.each(agent_ids, function(id) {
			postData.push({
				name: 'agent_ids[]',
				value: id
			});
		});
		Array.each(agent_team_ids, function(id) {
			postData.push({
				name: 'agent_team_ids[]',
				value: id
			});
		});

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: postData
		});
	},

	saveUsergroupPermissions: function(department_id, usergroup_ids) {
		var url = BASE_URL + 'admin/departments/' + department_id + '/save-usergroups.json';

		var postData = [];
		Array.each(usergroup_ids, function(id) {
			postData.push({
				name: 'usergroup_ids[]',
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
