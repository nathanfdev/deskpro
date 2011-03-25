Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.Tasks = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {
		this.slideHandler = new DeskPRO.Agent.WindowElement.MainMenuSlider({
			menuLi: this.buttonEl
		});
		
		this._updatePendingTasksCounts();
	},
	
	_updatePendingTasksCounts: function () {
		$.ajax({
			url: BASE_URL + 'agent/tasks/pending/count',
			context: this,
			success: function (data) {
				var index,
					innerIndex,
					$tasksElem;
				
				this.updateBadge(data.person.total + data.teams.total);
				
				for (index in data) {
					$tasksElem = $('#tasks_overview .' + index + '_tasks');
					
					for (innerIndex in data[index]) {
						$tasksElem.find('.' + innerIndex + '_tasks').text(
								'(' + data[index][innerIndex] + ')'
						);
					}
				}
			}
		});

	}

});