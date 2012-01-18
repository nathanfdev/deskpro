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
		$('#permgroup_table').find(':checkbox').on('change', (function() {
			if (!this.suppressChange) {
				this.updatePermissionsGrid();
			}
		}).bind(this));

		this.updatePermissionsGrid();

		this.toolsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#tools_menu_trigger'),
			menuElement: $('#tools_menu')
		});
		this.vacationOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '#vacation_overlay_trigger',
			contentElement: '#vacation_overlay'
		});
		this.deleteOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '#delete_overlay_trigger',
			contentElement: '#delete_overlay'
		});

		this._pageLoaded = true;
	},

	getUsergroupIds: function() {
		var ids = [];

		this.usergroupChecks.filter(':checked').each(function() {
			ids.push(parseInt($(this).val()));
		});

		return ids;
	},

	updatePermrowEnabled: function(row, isVis) {
		var has = false;

		if ($('input.override-perm', row).is(':checked')) {
			has = true;
		}

		if (!has) {
			$('input.in-use', row).each(function() {
				if ($(this).val() == '1') {
					has = true;
				}
			});
		}

		if (has) {
			row.addClass('on');

			var ef = row.find('.effective');

			if (!ef.hasClass('effective-on')) {
				ef.addClass('effective-on');
				if (this._pageLoaded && isVis) {
					this.effectiveChanged.push(ef);
				}
			}
		} else {
			row.removeClass('on');

			var ef = row.find('.effective');

			if (ef.hasClass('effective-on')) {
				ef.removeClass('effective-on');
				if (this._pageLoaded && isVis) {
					ef.stop().css("background-color", '#FFF97E').animate({backgroundColor: '#EBEBEB'}, 350);
				}
			}
		}
	},

	updatePermissionsGrid: function() {
		var self = this;
		var ug_ids = this.getUsergroupIds();

		$('#permgroup_table').find('.ug-perm-val').each(function() {
			var ug_id = parseInt($(this).data('ug-id'));
			if (ug_ids.indexOf(ug_id) === -1) {
				$(this).removeClass('in-use');
			} else {
				$(this).addClass('in-use');
			}
		});

		$('#permgroup_table tr.permrow').each(function() {
			var vis = $(this).is(':visible');
			self.updatePermrowEnabled($(this), vis);
		});

		this.suppressChange = true;
		this.processDependencies();
		this.suppressChange = false;

		if (this.effectiveChanged && this.effectiveChanged.length) {
			for (var i = 0; i < this.effectiveChanged.length; i++) {
				if (this.effectiveChanged[i].closest('tr').hasClass('on')) {
					this.effectiveChanged[i].stop().css("background-color", '#FFF97E').animate({backgroundColor: '#EBEBEB'}, 350);
				}
			}
		}
		this.effectiveChanged = [];
	},

	/**
	 * Goes through all permissions who show "yes" and make sure they meet dependencies
	 * that affect them.
	 */
	processDependencies: function() {
		var self = this;
		var ons = $('#permgroup_table tr.on.permrow');
		ons.each(function() {
			var deps = self.traceDependencies($(this));

			var pass = true;
			for (var i = 0; i < deps.length; i++) {
				row = $('tr.perm-' + deps[i] + '.on');
				if (!row.length) {
					pass = false;
					break;
				}
			}

			if (!pass) {
				var row = $(this);
				row.removeClass('on');
				row.find('.effective').removeClass('effective-on');
				row.find('.jquery-checkbox-checked').removeClass('jquery-checkbox-checked')
				row.find('.onoff-slider').prop('checked', false);
			}
		});
	},

	traceDependencies: function(row) {
		var all = [];
		while (1) {
			var depends_on = row.data('depends-on');
			if (!depends_on) {
				break;
			}

			all.push(depends_on);
			row = $('tr.perm-' + depends_on);
		}

		return all;
	}
});
