Orb.createNamespace('DeskPRO.Agent.PageFragment.List');

DeskPRO.Agent.PageFragment.List.TicketList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
	},

	initPage: function() {
		var self = this;
		var attachPoint = this.getEl('ng_attach');
		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', '$rootScope', '$controller', function($compile, $rootScope) {
			self.$scope = $rootScope.$new();
			attachPoint.data('$ngControllerController', self);
			$compile(attachPoint.contents())(self.$scope);

			self.initScope();

			self.$scope.$apply();
		}]);

		this.addEvent('destroy', function() {
			if (self.$scope) {
				self.$scope.$destroy();
				self.$scope = null;
			}
		});
	},

	initScope: function() {
		var $scope = this.$scope, wrapperEl = this.wrapper;

		$scope.tickets = eval(this.getEl('ticket_json').html());
		$scope.checked = {};
		$scope.display_fields = this.meta.display_fields || [];
		$scope.openTickets = {};

		$scope.isFieldDisplayable = function(ticket) {
			return function(field) {
				switch (field) {
					case 'department':
						return !!ticket.department;
					case 'language':
						return !!ticket.language;
					case 'category':
						return !!ticket.category;
					case 'priority':
						return !!ticket.priority;
					case 'workflow':
						return !!ticket.workflow;
					case 'agent':
						return true;
					case 'agent':
						return true;
					default:
						return false;
				}
			};
		};

		this.addEvent('watchedTabAdded', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			$scope.openTickets[ticketId] = true;
			wrapperEl.find('.ticket-row-' + ticketId).addClass('open');
		});
		this.addEvent('watchedTabRemoved', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			$scope.openTickets[ticketId] = false;
			wrapperEl.find('.ticket-row-' + ticketId).removeClass('open');
		});
		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this, true);
	}
});