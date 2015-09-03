Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketProblem = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket-problem';

		this.resultTypeName = 'problem';
		this.resultTypeId = 0;
	},

	initPage: function(el) {
		alert('test');

		DeskPRO_Window.getMessageBroker().sendMessage('ticket-section.list-activated', { listType: 'problem', id: this.getMetaData('problem_id'), topGroupingOption: this.meta.topGroupingOption || null });
		this.resultTypeId = this.getMetaData('problem_id');
		this.parent(el);
	},

	activate: function() {
		if (this.getMetaData('problem_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('problem.view-activated', this.getMetaData('problem_id'));
		}
	},

	deactivate: function() {
		if (this.getMetaData('problem_id')) {
			DeskPRO_Window.getMessageBroker().sendMessage('problem.view-deactivated', this.getMetaData('problem_id'));
		}
	},

	updateProblemListForTicket: function(info) {
		if (!info.ticket_id || !info.problem_id) {
			return;
		}

		// run this for every problem change, as a ticket may have multiple problems
		// and the general status could change
		// todo: in the future we could possibly resolve this without always
		// refreshing if we look at the list and only update if there's a ticket
		// with this problem
		this.refreshProblemTicketList();
	},

	refreshProblemTicketList: function() {
		var self = this;

		if (this.isRefreshing) {
			return;
		}
		this.isRefreshing = true;

		setTimeout(function() {
			self.isRefreshing = false;
			DeskPRO_Window.loadListPane(self.meta.refreshUrl);
		}, 0);
	}
});
