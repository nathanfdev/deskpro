Orb.createNamespace('DeskPRO.Report.Dashboard');

/**
 * Represent a dashboard widget
 */
DeskPRO.Report.Dashboard.Widget = new Orb.Class({

	initialize: function(dashboard, widget_id, options) {

		// Reference back to dashboard
		this.dashboard = dashboard;

		// Id of the widget
		this.widget_id = widget_id;

		// HTML element ID
		this.element_id = null;

		// State of the widget, can be in view or edit state
		this.is_edit_state = false;

		// The content associated with the widget
		this.content = null;

		// HTML element ID
		this.element_id = Orb.getUniqueId();

		// Show loader
		this.show_loader = false;

		this.stat_id = options.stat_id || '';
		this.title   = options.title || '';
	},

	// Update the widget data
	updateData: function(options) {

		this.stat_id = options.stat_id || '';
		this.title   = options.title || '';

	},

	// Setup the UI handlers for edit, close, etc
	addUIHandlers: function() {
		var self = this;

		// Save a reference to the widget
		this.$widget = $('#' + this.element_id);

		this.$widget.find('.edit').on('click', function() {

			// Show the edit overlay
			$('#overlay_wrapper .overlay-loader').css('display', 'block');
			$('#overlay_wrapper .overlay-title h4').html('Edit Dashboard Chart');
			self.dashboard.openOverlay('');

			$.ajax({
				url: DeskPRO_Window.getUrl('report_trend_dashboard_stat_edit', {dashboard_id: self.dashboard.dashboard_id, dashboard_stat_id: self.widget_id}),
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					// Set the edit content
					$('#overlay_wrapper .overlay-content').html(data.html);
					$('#overlay_wrapper .overlay-loader').css('display', 'none');

					var form = $('#dashboard_widget_edit_form');
					self.dashboard.saveEditWidget(form);
				}
			});

			return false;
		});

		this.$widget.find('.delete').on('click', function() {
			self.dashboard.deleteWidget(self.element_id);
			return false;
		});

		this.$widget.find('.toolbar-title').on('click', function() {
			self.dashboard.showChartFullscreen(self);
			return false;
		});

		this.showLoader();
	},

	// Update widget state, editable or viewable
	setEditable: function(editable) {

		if (editable) {
			// Switch widget to editable state
			this.updateToEditable();
		}
		else {
			// Switch widget to view state
			this.updateToViewable();
		}
	},

	// Update widget state to editable
	updateToEditable: function() {

		this.$widget.find('.grid-slot-toolbar .icons a').css('display', 'block');
		this.$widget.find('.grid-slot-toolbar').css('cursor', 'move');

		this.is_edit_state = true;
	},

	// Update widget state to viewable
	updateToViewable: function() {

		this.$widget.find('.grid-slot-toolbar .icons a').css('display', 'none');
		this.$widget.find('.grid-slot-toolbar').css('cursor', 'auto');

		this.is_edit_state = false;
	},

	// Set the widget content. The content should know how to render itself
	setContent: function(content) {

		this.content = content;

	},

	// Get the widget content
	getContent: function() {

		return this.content;

	},

	// Remove the widget content
	removeContent: function() {

		this.$widget.find('.grid-slot-content').html('');

		this.content = null;

	},

	// Show the spinner loader
	showLoader: function() {

		this.$widget.find('.content-loader').css('display', 'block');

		this.show_loader = true;

	},

	// Hide the spinner loader
	hideLoader: function() {

		this.$widget.find('.content-loader').css('display', 'none');

		this.show_loader = false;

	},

});
