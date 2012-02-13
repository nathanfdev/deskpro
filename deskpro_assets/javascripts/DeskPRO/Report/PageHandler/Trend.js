Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Trend page handler
 */
DeskPRO.Report.PageHandler.Trend = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function() {

		// UI Overlay
		this.cloneOverlay = null;
		this.editOverlay = null;
	},

	// Init the page
	initPage: function() {
		var self = this;

		// Create the trend overlay
		this.cloneOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#clone_overlay_wrapper')
		});
		$('#clone_overlay_wrapper .close-overlay').on('click', function() {
			self.cloneOverlay.close();
		});

		this.editOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#edit_overlay_wrapper')
		});
		$('#edit_overlay_wrapper .close-overlay').on('click', function() {
			self.editOverlay.close();
		});

		$('.clone-trend').on('click', function() {
			var href = $(this).attr('href');
			// Show the overlay
			self.cloneOverlay.open();
			$('#clone_overlay_wrapper .overlay-loader').css('display', 'block');
			$.ajax({
				url: href,
				type: 'GET',
				success: function(data) {
					$('#clone_overlay_wrapper .overlay-content').html(data);
					$('#clone_overlay_wrapper .overlay-loader').css('display', 'none');
				}
			});

			return false;
		});

		$('.edit-trend').on('click', function() {
			var href = $(this).attr('href');
			// Show the overlay
			self.editOverlay.open();
			$('#edit_overlay_wrapper .overlay-loader').css('display', 'block');
			$.ajax({
				url: href,
				type: 'GET',
				success: function(data) {
					$('#edit_overlay_wrapper .overlay-content').html(data);
					$('#edit_overlay_wrapper .overlay-loader').css('display', 'none');
				}
			});

			return false;
		});
	},

});
