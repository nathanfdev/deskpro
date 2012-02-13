Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.AgentGroupEditPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		$('#load_agent_perms_btn').on('click', function(ev) {
			ev.preventDefault();
			self.showLoadAgentPermsOb(ev);
		});

		this.toolsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#tools_menu_trigger'),
			menuElement: $('#tools_menu')
		});
		this.deleteOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '#delete_overlay_trigger',
			contentElement: '#delete_overlay'
		});
	},

	showLoadAgentPermsOb: function(ev) {
		var self = this;
		if (!this.agentob) {
			this.agentob = new DeskPRO.UI.OptionBox({
				element: $('#load_agent_perms_ob')
			});

			$('#load_agent_perms_ob button.save-trigger').on('click', function() {
				self.agentob.close();

				var agentId = parseInt(self.agentob.getSelected('agents'));

				if (agentId) {
					var url = $(this).data('fetch-url');
					url = url.replace(/\{person_id\}/, agentId);

					var old_text = $('#load_agent_perms_btn').text();
					$('#load_agent_perms_btn').text('...');
					$.ajax({
						url: url,
						dataType: 'json'
					}).success(function(data) {
						$('#load_agent_perms_btn').text(old_text);
						$('#permgroups input.permcheck').each(function() {
							var name = $(this).data('perm-name');
							if (data[name] == "1") {
								$(this).prop('checked', true);
							} else {
								$(this).prop('checked', false);
							}
						});
					});
				}
			});
		}

		this.agentob.open(ev);
	}
});
