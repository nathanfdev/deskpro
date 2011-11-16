Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Trend page handler
 */
DeskPRO.Report.PageHandler.Trend = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function() {

		// UI Overlay
		this.overlay = null;
	},

	// Init the page
	initPage: function() {
		var self = this;

		// Create the trend overlay
		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#overlay_wrapper')
		});
		$('#overlay_wrapper .close-overlay').on('click', function() {
			self.overlay.close();
		});

		$('.clone-trend').on('click', function() {
			var href = $(this).attr('href');
			$.ajax({
				url: href,
				type: 'GET',
				success: function(data) {
					self.openOverlay(data);
				}
			});

			return false;
		});
	},

	// Open the overlay loading in a template
	openOverlay: function(overlay_content) {

		$('.overlay-content').html(overlay_content);
		this.overlay.open();

	},

	// Clean the overlay and close it
	closeOverlay: function() {

		$('.overlay-content').html('');
		this.overlay.close();

	},

});
