Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.AgentEditPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		this.el.on('submit', function(ev) {
			var errors = [];

			var f_name = $('input[name="agent[first_name]"]').val().trim();
			var l_name = $('input[name="agent[last_name]"]').val().trim();
			var email  = $('input[name="agent[email]"]').val().trim()

			if (!f_name.length) errors.push('Enter a first name');
			if (!l_name.length) errors.push('Enter a last name');
			if (!email.length) {
				errors.push('Enter an email address');
			} else if (!email.test(/^.+@.+\..+$/)) {
				errors.push('Enter a valid email address');
			}

			if (errors.length) {
				alert("Please correct the following errors and try again:\n - " + errors.join("\n - "));
				ev.preventDefault();
			}
		});


		this.usergroupChecks = $('#usergroup_checks :checkbox');

		$('#usergroup_checks').on('click', ':checkbox', this.updatePermissionsGrid.bind(this));
		$('#permgroups').on('change', ':checkbox', function(ev) {
			var check = $(this);
			var cell = check.closest('td');
			var row = cell.closest('tr');

			if (cell.is('.ugcol') && !check.is('.ignore-event')) {
				if (!confirm('You clicked a permission group button. Changing this permission will update the permission group, and will affect any other agents that are part of that group. Do you want to continue?')) {
					check.addClass('ignore-event');
					check.prop('checked', !check.prop('checked'));
					check.removeClass('ignore-event');
				}

				self.updatePermrowEnabled(row, true);

			} else if (cell.is('.ug-override')) {
				if (self.isPermrowEnabled(row)) {
					alert('You cannot remove this permission because the agent is part of a group that enables it. Disable the permission on the group, or remove the agent from the group.');
					check.prop('checked', true);
				}
			}
		});

		this.updatePermissionsGrid();
	},

	getUsergroupIds: function() {
		var ids = [];

		this.usergroupChecks.filter(':checked').each(function() {
			ids.push(parseInt($(this).val()));
		});

		return ids;
	},

	isPermrowEnabled: function(row) {
		return $('td.ugcol :checkbox:checked', row).filter(':visible').length;
	},

	updatePermrowEnabled: function(row, toggleWithNo) {
		var overrideCell = $('td.ug-override', row);
		if (this.isPermrowEnabled(row)) {
			$(':checkbox', overrideCell).prop('checked', true);
		} else {
			if (toggleWithNo) {
				$(':checkbox', overrideCell).prop('checked', false);
			} else {
				// no change usually because off doesnt matter
			}
		}
	},

	updatePermissionsGrid: function() {
		var self = this;
		var ug_ids = this.getUsergroupIds();

		$('#permgroups th.ugcol, #permgroups td.ugcol').each(function() {
			var ug_id = parseInt($(this).data('ug-id'));
			if (ug_ids.indexOf(ug_id) === -1) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});

		$('#permgroups tr.permrow').each(function() {
			self.updatePermrowEnabled(this);
		});
	}
});
