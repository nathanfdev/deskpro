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

	// Setup the UI handlers for edit, close, etc
	addUIHandlers: function() {
		var self = this;

		// Save a reference to the widget
		this.$widget = $('#' + this.element_id);

		this.$widget.find('.edit').click(function() {
			// Show the edit overlay
			self.dashboard.openOverlay('dashboard_widget_edit');

			return false;
		});

		this.$widget.find('.close').click(function() {
			// Destroy the widget

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

		console.log(this.$widget.find('.content-loader'));

		this.$widget.find('.content-loader').css('display', 'block');

		this.show_loader = true;

	},

	// Hide the spinner loader
	hideLoader: function() {

		this.$widget.find('.content-loader').css('display', 'none');

		this.show_loader = false;

	}


});