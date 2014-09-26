Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TaskList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'task-list';
	},

	initScope: function() {
		var self = this;
		var $scope = self.$scope = DeskPRO_Window.$scope.$new();
		self.$q = DeskPRO_Window.$q;
		self.$timeout = DeskPRO_Window.$timeout;

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			self.wrapper.data('$ngControllerController', self);
			$compile(self.wrapper.contents())(self.$scope);
		}]);
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		if (DeskPRO_Window.sections.tasks_section) {
			DeskPRO_Window.sections.tasks_section.doRelaodPage = false;
		}

		var control = new DeskPRO.Agent.PageHelper.TaskListControl(el, {
			menuVis:  this.getEl('menu_vis'),
			assignOb: this.getEl('assign_ob'),
			completeCountEl: this.getEl('complete_count')
		});

		control.addEvent('updateUi', function() {
			self.updateUi();
			$('.message-text textarea', el).each(function(){
				$(this).height($(this).prop('scrollHeight') + 25);
				console.info($(this).height(), $(this).prop('scrollHeight'));
			});
		});
	}
});
