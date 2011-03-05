Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.Twitter = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {
		this._initInitialData();
	},

	_initInitialData: function() {
		// load counters
		$.ajax({
			url: BASE_URL + 'agent/twitter/pane/statuses',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#twitter_main_section .top-twitter-info').html(html);
			}
		});

		// load accounts
		$.ajax({
			url: BASE_URL + 'agent/twitter/pane/accounts',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#twitter_main_section .twitter-content').html(html);
			}
		});
	}
});
