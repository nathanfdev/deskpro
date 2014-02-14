Orb.createNamespace('DeskPRO.Agent.PageFragment.List');

DeskPRO.Agent.PageFragment.List.TicketList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
	},

	initPage: function(el) {
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
		this.$scope.tickets = eval(this.getEl('ticket_json').html());
		this.$scope.checked = {};
	}
});