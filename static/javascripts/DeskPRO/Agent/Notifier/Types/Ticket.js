Orb.createNamespace('DeskPRO.Agent.Notifier.Types');

DeskPRO.Agent.Notifier.Types.Ticket = new Class({
	Extends: DeskPRO.Agent.Notifier.Types.Abstract,

	sectionId: 'notify_list_tickets', //override

	items: {},

	_initMessageListeners: function() {

	},



	/**
	 * The summary line goes right into the button
	 */
	getSummary: function() {
		var count = Object.getLength(this.items);

		if (!count) return null;

		return count + ' new tickets';
	},



	/**
	 * Get lines
	 */
	updateLines: function() {
		var dep_tickets = {};

		Object.each(this.items, function(info, ticketId) {
			var depId = info.department_id;
			var subject = info.subject;

			if (!dep_tickets[depId]) dep_tickets[depId] = [];

			dep_tickets[depId].push([ticketId, subject]);
		});

		var lines = [];
		Object.each(dep_tickets, function(tickets, depId) {
			var ln = '';
			var count = 0;
			Array.each(tickets, function(info) {
				count++;
				if (count > 2) {
					return false;
				}

				ln += '<a>' + info.subject + '</a>, ';
			});

			if (count > 2) {
				ln += ' and ' + (count-2) + ' others ';
			}

			ln += 'in the <a>' + DeskPRO_Window.getDisplayName('department', depId) + '</a> department';

			lines.push(ln);
		});

		if (!lines.length) {
			this.sectionEl.hide();
			return;
		}

		lines = '<li>' + lines.join('</li><li>') + '</li>';
		this.sectionList.html(lines);
	}
});
