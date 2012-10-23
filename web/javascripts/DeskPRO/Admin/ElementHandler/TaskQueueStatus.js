Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TaskQueueStatus = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;
		var el = this.el;

		var taskId = el.data('task-id');
		if (!taskId) {
			return;
		}

		var interval;

		var updateStatus = function() {
			$.ajax({
				url: BASE_URL + 'admin/misc/check-task/' + taskId,
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					if (!data || !data.exists) {
						el.text('Task could not be found.');
						clearInterval(interval);
						return;
					}

					if (data.status == 'running') {
						el.text('Running... ' + data.run_status);
					} else if (data.status == 'completed') {
						el.text('Completed! (' + data.run_status + ')');
						clearInterval(interval);
					} else if (data.status == 'errored') {
						el.text('An error occurred: ' + data.error_text);
						clearInterval(interval);
					} else {
						el.text('Waiting to start...');
					}
				}
			});
		};

		updateStatus();
		setInterval(updateStatus, 10000);
	}
});
