Orb.createNamespace('DeskPRO.Report.PageHandler');

/**
 * Dashboard page handler
 *
 * A dashboard has a number of widgets
 */
DeskPRO.Report.PageHandler.Dashboard = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function(dashboard_id, options) {

		options = options || {};

		// Id of the dashboard
		this.dashboard_id = dashboard_id;

		// List of the dashboard widgets
		this.widgets = [];

		this.grid = [];

		// State of the dashboard, can be in view or edit state
		this.is_edit_state = false;

		// Number of columns in the dashboard grid
		this.number_columns = options.number_columns || 4;

		// The width of 1 columns
		this.column_width = null;

		// The height of 1 column
		this.row_height = 250;

		// The absolute minimum a column can be resized to (px)
		this.min_column_width = 250;

		// The absolute minimum a column can be resized to (px)
		this.min_row_height = 240;

		// Spacing between widgets in the columns [top, right, bottom, left]
		this.column_spacing = [0, 10, 10, 0];

		// Time for animation of widget resize to take place
		this.animation_duration = 100;

		// UI Overlay
		this.overlay = null;

		// Fullscreen over lay
		this.fullscreen_overlay = null;

		// The supported vendor namespaces
		this.supported_vendors = ['AmChart', 'DeskPRO'];

		// The supported chart Classes
		this.supported_charts = ['Column', 'Line', 'Pie', 'SimpleVariation', 'SimpleDrillDown', 'DetailedDrillDown'];

		// Refernce to the dashboard
		this.$dashboard = $("#report-dashboard");
	},

	// Init the page
	initPage: function() {
		var self = this;

		this.setMinPageWidth();

		// Setup some templates
		$('#dashboard_widget').template('dashboard_widget');
		$('#dashboard_widget_create').template('dashboard_widget_create');
		$('#dashboard_widget_select').template('dashboard_widget_select');

		// Create the dashboard overlay
		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#overlay_wrapper')
		});
		$('#overlay_wrapper .close-overlay').on('click', function() {
			self.overlay.close();
		});

		// Create an overlay for fullscreen
		this.fullscreen_overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#fullscreen_overlay_wrapper'),
			fullScreen: true
		});
		$('#fullscreen_overlay_wrapper .close-overlay').on('click', function() {
			self.fullscreen_overlay.close();
		});

		// Setup the grid option slider
		$("#report-dashboard-options-num-columns-slider").slider({
			range: "max",
			min: 1,
			max: 4,
			value: this.number_columns,
			slide: function(event, ui) {
				self.number_columns = ui.value;

				self.calculateColumnWidth();
				self.resizeAllWidgets();
			},
			stop: function(event, ui) {
				// Need to re render the widgets - Flash charts will do
				// this for us, JS ones dont seem to support it
				//self.renderWidgets();
			}
		});


		// Delete dashboard confirmation
		$('#report-dashboard-delete').click(function() {
			if (confirm('Are you sure you want to delete this dashboard?')) {
				return true;
			}
			else {
				return false;
			}
		});

		$('#report-dashboard-set-editable').on('click', function() {
			self.setEditable(true);
			return false;
		});

		$('#report-dashboard-set-viewable').on('click', function() {
			self.setEditable(false);
			return false;
		});

		// Need to ensure dashboard update correctly if window size changes
		this.$dashboard.on('resize', function() {
			self.calculateColumnWidth();
			self.resizeAllWidgets();
		});


		// Calculate initial dashboard column width, grab the widgets
		this.calculateColumnWidth();

		this.fetchWidgets();
	},

	setupCellGrid: function(rowsRequired) {

		rowsRequired++;

		this.$dashboard.find('.slot').remove();

		var size = rowsRequired * this.number_columns;

		for (var i = 0; i < size; i++) {
			this.grid.push(null);

            var height = this.caclHeight(1);
            var width  = this.caclWidth(1);

            var top  = this.caclTop(i);
            var left = this.caclLeft(i);

            var html = '\
<div class="cell" id="cell_'+i+'" data-id="'+i+'" style="top:'+top+'px; left:'+left+'px; height:'+height+'px; width:'+width+'px;" >\
<span class="inner"> \
	<a href="#" class="dashboard-new-placeholder-link">Click to Add a Chart<br />Or<br />Drop an Existing Chart</a>\
</span>\
</div>';
            this.$dashboard.append(html);
        }

        this.resizeDashboardHeightToGrid();

	},

	// Open the overlay loading in a template
	openOverlay: function(overlay_content) {

		$('#overlay_wrapper .overlay-content').html(overlay_content);
		this.overlay.open();

	},

	// Clean the overlay and close it
	closeOverlay: function() {

		$('#overlay_wrapper .overlay-content').html('');
		this.overlay.close();

	},

	// Fetch the widgets
	fetchWidgets: function() {
		var self = this;
		$.ajax({
			url: DeskPRO_Window.getUrl('report_dashboard_ajaxfetchwidgets', {dashboard_id: this.dashboard_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {
				if (data.widgets.length > 0) {

					var rowsRequired = Math.ceil(data.widgets.length / self.number_columns);
					if (data.widgets.length % self.number_columns == 0)
						rowsRequired++;

					self.setupCellGrid(rowsRequired);

					Array.each(data.widgets, function(v) {
						self.createWidgetFromJSON(v);
					});

					self.calculateColumnWidth();
					self.resizeAllWidgets();

					self.renderWidgets();
				}
				else {
					self.addDashboardEmptyNotice();
				}
			}
		});
	},

	// Create a widget from a JSON response
	createWidgetFromJSON: function(data) {

		var widget = new DeskPRO.Report.Dashboard.Widget(this, data.id, data);
		this.addWidget(widget, data.grid_slots);

		widget.setContent(this.createChart(
			data.chart_vendor,
			data.chart_class,
			"widget_content_" + widget.element_id,
			data.id)
		);

		return widget;
	},

	// Create a chart, can be any number of vendors of chart types
	createChart: function(vendor, chart_type, chart_element_id, dashboad_stat_id) {

		if (this.supported_vendors.indexOf(vendor) == -1) {
			throw "Unsupported Vendor type [" + vendor + "]";
		}

		if (this.supported_charts.indexOf(chart_type) == -1) {
			throw "Unsupported Chart Class [" + chart_type + "]";
		}

		var chartClass = eval("DeskPRO.Report.Chart." + vendor + "." + chart_type);

		return new chartClass(chart_element_id, dashboad_stat_id, vendor, chart_type);
	},

	// Render the widget content. Need to do this when widget sizes change as
	// widget content may need to redraw itself
	renderWidgets: function() {

		Array.each(this.widgets, function(v) {
			v.widget.getContent().render();
			v.widget.hideLoader();
		});

	},

	// Add a widget to the dashboard.
	// pos start index at 0
	addWidget: function(widget, num_slots, pos) {

		pos = pos || -1;
		num_slots = num_slots || 1;

		var insert_widget = {
			widget: widget,
			num_slots: num_slots
		};

		// No position specified, add it to the end
		if (pos === -1) {
			this.widgets.push(insert_widget);
			pos = this.widgets.length - 1;
		}
		else {
			// Need to insert at postion
			this.widgets.splice(pos, 0, insert_widget)

		}


		// Add widget to UI - Need to insert before the add chart placeholder
		// if its showing
		if (this.is_edit_state) {
			this.removeAddChartPlaceholder();
			this.$dashboard.append($.tmpl('dashboard_widget', {widget: widget}));;
			this.createAddChartPlaceholder();
		}
		else {
			this.$dashboard.append($.tmpl('dashboard_widget', {widget: widget}));
		}
		widget.addUIHandlers();

		widget.setHeight(this.caclHeight(widget.units_height));
		widget.setWidth(this.caclWidth(widget.units_width));

		widget.setTop(this.caclTop(widget.slot_number));
		widget.setLeft(this.caclLeft(widget.slot_number));

		this.initWidgetInGrid(widget);
	},

	// Removes a widget from the dashboard
	deleteWidget: function(element_id) {
		var self         = this;
		var widget_index = this.getWidgetIndexByElementId(element_id);
		var widget       = this.widgets[widget_index];

		if (confirm('Are you sure you want to delete this chart?')) {
			// Destroy the widget
			$.ajax({
				url: DeskPRO_Window.getUrl('report_dashboard_ajaxdeletewidget', {dashboard_id: this.dashboard_id, dashboard_stat_id: widget.widget.widget_id}),
				dataType: 'json',
				type: 'GET',
				success: function(data) {
					self.$dashboard.find('li#' + element_id).remove();
					self.widgets.splice(widget_index, 1);
				}
			});
		}
		else {
			return false;
		}

	},

	// Add the notice informing the dashboard is empty
	addDashboardEmptyNotice: function() {
		var self = this;

		var html = '<div id="dashboard-empty-notice"><a href="#">The dashboard is currently empty. Click here to add some charts</a></div>';
		this.$dashboard.append(html);

		$('#dashboard-empty-notice a').click(function() {
			self.setEditable(true);
		})
	},

	// Remove the dashboard empty notice
	removeDashboardEmptyNotice: function() {

		$('#dashboard-empty-notice').remove();
	},

	// Set dashboard state, can be editable or viewable
	setEditable: function(editable) {

		if (editable) {
			this.removeDashboardEmptyNotice();

			// Switch dashbaord to edit state
			this.updateToEditable();
		}
		else {
			// Switch dashbaord to view stat
			this.updateToViewable();

			if (this.widgets.length === 0) {
				this.addDashboardEmptyNotice();
			}
		}
	},

	// Update UI state to editable
	updateToEditable: function() {
		var self = this;

		// Hide the edit link, show the view link
		$("#report-dashboard-set-editable").css('display', 'none');
		$("#report-dashboard-options").css('display', 'block');

		// Create the 'Add Widget' placeholder
		this.createAddChartPlaceholder();

		this.$dashboard.find('.widget').draggable({
			revert: 'invalid',
			handle: '.grid-slot-toolbar',
			start: function(event, ui) {
				$(this).addClass('dragging');
			},
			stop: function(event, ui) {
				$(this).removeClass('dragging');
			}
        });
        this.$dashboard.find('.cell').droppable({
        	tolerance: 'pointer',
        	hoverClass: 'dashboard-cell-hover-over',
            drop: function(event, ui) {
                self.doDrop($(this), ui.draggable);

                $(this) .removeClass("drop-allowed");
            },
            over: function(event, ui) {
                var dropAllowed = self.isDropAllowed($(this), ui.draggable);

                if (dropAllowed) {
                    $(this).addClass("drop-allowed");
                }
                else {
                    $(this).addClass("drop-denied");
                }
            },
            out: function(event, ui) {
                $(this).removeClass("drop-allowed");
                $(this).removeClass("drop-denied");
            },
        });

		this.$dashboard.disableSelection();

		// Make the dashboard widgets resizable
		this.applyResizeToWidgets();

		// Set each widget as editable
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(true);
		});

		this.calculateColumnWidth();

		this.is_edit_state = true;
	},

	// Update UI state to viewable
	updateToViewable: function() {

		// Save the new state of the dashboard
		this.saveDashboardState();

		// Hide the view link, show the edit link
		$("#report-dashboard-set-editable").css('display', 'block');
		$("#report-dashboard-options").css('display', 'none');

		// Remove the add placeholder
		this.removeAddChartPlaceholder();

		this.$dashboard.find(".widget").draggable('destroy');
		this.$dashboard.find(".cell").droppable('destroy');
		// Remove the resizable functionality
		this.$dashboard.find("div").resizable('destroy');

		// Set each widget back to viewable
		Array.each(this.widgets, function(v) {
			v.widget.setEditable(false);
		});

		this.is_edit_state = false;
	},

	// Apply the resizable plugin to widgets
	applyResizeToWidgets: function(selector) {

		var self = this;

		// Apply to all by default, otherwise we can specify, useful
		// for when a widget is added
		selector = selector || ".widget";

		// Make the dashboard widgets resizable
		this.$dashboard.find(selector).resizable({
			helper: "ui-resizable-helper",
			//handles: 'e',
			distance: 40,
			minWidth: this.column_width,
			minHeight: this.row_height,
			start: function(event, ui) {
				$(this).addClass('dragging');
				var html = '<div class="resize-overlay resize-left"></div>';
				$(this).find('.ui-resizable-helper').append(html);
			},
			stop: function(event, ui) {
				$(this).removeClass('dragging');
				// Remove the resize overlay
				$(this).find('.resize-overlay').remove();

				var resizeAllowed = self.isResizeAllowed($(this));
				if (resizeAllowed) {
					// TODO: remove this when window resize event handler is working
					self.calculateColumnWidth();

					var closest_column_size = self.calculateClosestColumnSize(ui.size.width);
					var closest_row_resize  = self.calculateClosestRowSize(ui.size.height);

					var widget_index = self.getWidgetIndexByElementId(ui.element.attr('id'));

					var widget = self.widgets[widget_index];
					var currentIndex = self.getWidgetPosById(widget.widget.widget_id);
					self.resizeWidget(widget.widget.units_width, widget.widget.units_height, closest_column_size, closest_row_resize, widget.widget);


					widget.widget.units_width = closest_column_size;
					widget.widget.units_height = closest_row_resize;

					// Adjust the resized widget to the closest column
					self.resizeWidgetToColumn(widget_index, closest_column_size, true);
					self.resizeWidgetToRow(widget_index, closest_row_resize, true);
				}
				else {
					var widget_index = self.getWidgetIndexByElementId(ui.element.attr('id'));
					var widget = self.widgets[widget_index];

					$(this).animate({
                        width: widget.widget.width,
                        height: widget.widget.height
                    }, 1000, function() {

                    });
				}

				self.dumpGrid();
			}
		});

	},

	// Add UI handlers for the add chart overlay
	addAddChartUIHandlers: function() {
		var self = this;

		$('#dashboard_widget_select a.add-chart').on('click', function() {
			// Set the edit content
			$('#overlay_wrapper .overlay-loader').css('display', 'block');

			var href = $(this).attr('href');
			$.ajax({
				url: href,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					$('#overlay_wrapper .overlay-content').html(data.html);
					$('#overlay_wrapper .overlay-loader').css('display', 'none');

					var form = $('#dashboard_widget_new_form');
					self.saveNewWidget(form);
				}
			});

			return false;
		});

	},

	// Save the state of the dashboard
	saveDashboardState: function() {
		var self = this;

		var form = $('#dashboard_edit_form');
		var postData = form.serializeArray();

		dashboardState = {
			widgets: [],
			number_columns: this.number_columns
		};

		// Get the state of each of the dashboard widgets
		this.$dashboard.find(".widget").each(function(i, el) {
			var widget_index = self.getWidgetIndexByElementId($(this).attr('id'));
			var v = self.widgets[widget_index];

			dashboardState.widgets.push({
				id: v.widget.widget_id,
				number_columns: v.num_slots,
				grid_columns: v.widget.units_width,
				grid_rows: v.widget.units_height,
				slot_number: self.getWidgetPosById(v.widget.widget_id),
			})
		});

		postData.push({name: 'dashboard_state', value: JSON.stringify(dashboardState)});

		$.ajax({
			url: form.attr('action'),
			data: postData,
			dataType: 'json',
			type: 'POST',
			success: function(data) {
				// Check stats return
				$('.dashboard-title').html($('#report_dashboard_title').val());
			}
		});
	},

	// Save the new widget form
	saveNewWidget: function(form) {
		var self = this;

		form.submit(function() {
			$('#overlay_wrapper .overlay-loader').css('display', 'block');

			var postData = form.serializeArray();

			$.ajax({
				url: form.attr('action'),
				data: postData,
				dataType: 'json',
				type: 'POST',
				success: function(data) {

					// Close the overlay
					$('#overlay_wrapper .overlay-loader').css('display', 'none');
					self.closeOverlay();

					// Insert the new widget
					var widget = self.createWidgetFromJSON(data.widget);
					widget.setEditable(true);
					widget.getContent().render();
					widget.hideLoader();

					self.calculateColumnWidth();
					self.resizeAllWidgets();

					self.applyResizeToWidgets('#' + widget.element_id);
				}
			});

			return false;
		});

	},

	// Save the edit widget form
	saveEditWidget: function(form) {
		var self = this;

		form.submit(function() {
			$('#overlay_wrapper .overlay-loader').css('display', 'block');

			var postData = form.serializeArray();

			$.ajax({
				url: form.attr('action'),
				data: postData,
				dataType: 'json',
				type: 'POST',
				success: function(data) {

					// Close the overlay
					$('#overlay_wrapper .overlay-loader').css('display', 'none');
					self.closeOverlay();

					// Update the new widget
					var widget_index = self.getWidgetIndexById(data.widget.id);
					var widget = self.widgets[widget_index];

					widget.widget.updateData(data.widget.stat);

					// Update the chart
					widget.widget.setContent(self.createChart(
						data.widget.chart_vendor,
						data.widget.chart_class,
						"widget_content_" + widget.widget.element_id,
						data.widget.id)
					);

					widget.widget.getContent().render();
				}
			});

			return false;
		});

	},

	// Create the placeholder for the add chart widget
	createAddChartPlaceholder: function() {

		var self = this;

		//this.$dashboard.find('.cell').html($.tmpl('dashboard_widget_create'));

		// Ensure widget is the correct size
		this.resizePlacerHolderWidget();

		// Set handler to process click events, we want to display an overlay
		$(".cell .dashboard-new-placeholder-link").on('click', function() {
			$('#overlay_wrapper .overlay-title h4').html('Add Dashboard Chart');
			$('#overlay_wrapper .overlay-loader').css('display', 'none');
			self.openOverlay($.tmpl('dashboard_widget_select'));
			self.addAddChartUIHandlers();

			return false;
		});
	},

	// Remove the placeholder
	removeAddChartPlaceholder: function() {

		// Remove the 'Add Widget' placeholder
		$('#dashboard-new-placeholder').remove();

	},

	// Show a chart fullscreen
	showChartFullscreen: function(widget) {

		var self = this;

		$('#fullscreen_overlay_wrapper .overlay-content #fullscreen_overlay_wrapper_content').html('');

		var chart = widget.getContent();

		$.ajax({
			url: DeskPRO_Window.getUrl('report_chart_get_fullscreen_details', {dashboard_stat_id: chart.dashboard_stat_id}),
			dataType: 'json',
			type: 'GET',
			success: function(data) {

				// Build and render the new chart
				var new_chart = self.createChart(data.chart.chart_vendor,
					data.chart.chart_class,
					'fullscreen_overlay_wrapper_content',
					data.chart.dashboard_stat_id);
				new_chart.chart_type_index = data.chart.chart_type;
				new_chart.render();

			}
		});

		this.fullscreen_overlay.open();

	},

	// Get a widget object by it element id
	getWidgetIndexByElementId: function(element_id) {
		var index      = 0;
		var foundIndex = 0;

		Array.each(this.widgets, function(v) {
			if (v.widget.element_id === element_id) {
				foundIndex = index;
			}
			index++;
		});

		return foundIndex;
	},

	// Get a widget object by its
	getWidgetIndexById: function(id) {
		var index      = 0;
		var foundIndex = 0;

		Array.each(this.widgets, function(v) {
			if (v.widget.widget_id === id) {
				foundIndex = index;
			}
			index++;
		});

		return foundIndex;
	},

	getWidgetPosById: function(id) {

		var index      = 0;
		var foundIndex = 0;
		var found = false

		Array.each(this.grid, function(v) {
			if (v === id && found === false) {
				foundIndex = index;
				found = true;
			}
			index++;
		});

		return foundIndex;

	},

	// Resize all the widgets
	resizeAllWidgets: function() {
		var self  = this;
		var index = 0;

		Array.each(this.widgets, function(v) {
			self.resizeWidgetToColumn(index, v.num_slots, false);
			index++;
		});

		// Resize the placeholder is we are in edit state
		//if (this.is_edit_state === true) {
			this.resizePlacerHolderWidget()
		//}
	},

	// Resize a widget to fit into number_columns
	resizeWidgetToColumn: function(widget_index, number_columns, animate) {

		// We need to shrink widgets that maybe to big if the column count
		// in the dashboard has been reduced
		if (number_columns > this.number_columns) {
			// Widget cannot be bigger than the number of columns available
			number_columns = this.number_columns;
		}

		// Update the column size for this widget
		this.widgets[widget_index].num_slots = number_columns;

		var new_width = this.calculateWidthOfWidgetByColumnCount(number_columns);

		this.widgets[widget_index].widget.setWidth(new_width);

		this.widgets[widget_index].widget.setTop(this.caclTop(this.widgets[widget_index].widget.slot_number));
		this.widgets[widget_index].widget.setLeft(this.caclLeft(this.widgets[widget_index].widget.slot_number));

		// Do the resize, we may want to animate
		if (animate) {
			$('#' + this.widgets[widget_index].widget.element_id).animate({
				width: new_width + 'px'
			}, this.animation_duration);
		} else {
			$('#' + this.widgets[widget_index].widget.element_id).css('width', new_width + 'px');
		}
	},

	// Resize a widget to fit into number_rows
	resizeWidgetToRow: function(widget_index, number_rows, animate) {

		// Update the row size for this widget
		//this.widgets[widget_index].num_slots = number_rows;

		var new_height = this.calculateHeightOfWidgetByRowCount(number_rows);

		this.widgets[widget_index].widget.height = new_height;
		this.widgets[widget_index].widget.units_height = number_rows;
		// Do the resize, we may want to animate
		if (animate) {
			$('#' + this.widgets[widget_index].widget.element_id).animate({
				height: new_height + 'px'
			}, this.animation_duration);
		} else {
			$('#' + this.widgets[widget_index].widget.element_id).css('height', new_height + 'px');
		}
	},

	// Resize the placeholder widget
	resizePlacerHolderWidget: function() {

		// Always takes up 1 column in width
		var new_width = this.calculateWidthOfWidgetByColumnCount(1);
		$('.cell').css('width', new_width + 'px');

		for (var i = 0; i < this.grid.length; i++) {
			$('#cell_'+i).css('top', this.caclTop(i));
			$('#cell_'+i).css('left', this.caclLeft(i));
		}

	},

	// Calculate the closets column size from a width
	calculateClosestColumnSize: function(width) {

		// Calculate to a half column - will snap up or down depending
		// which side of the half column the user resizes to
		var half_column = this.column_width / 2;

		var column_size = 1;
		for (var i = 0; i <= this.number_columns; i++) {
			if (width < (this.column_width * i) + half_column) {
				column_size = i;
				break;
			}
		}

		return column_size;
	},

	// Calculate the closets row size from a height
	calculateClosestRowSize: function(height) {

		// Calculate to a half row - will snap up or down depending
		// which side of the half row the user resizes to
		var half_row = this.row_height / 2;

		var row_size = 1;
		for (var i = 0; i <= 10; i++) {
			if (height < (this.row_height * i) + half_row) {
				row_size = i;
				break;
			}
		}

		return row_size;
	},

	// Calculate the width of 1 column
	calculateColumnWidth: function() {

		this.column_width = (this.getDashboardWidth() - this.getTotalSpacerWidth()) / this.number_columns;
		if (this.column_width < this.min_column_width) {
			this.column_width = this.min_column_width;
		}

	},

	// Calculate the width of a widget by the number of columns it takes up
	calculateWidthOfWidgetByColumnCount: function(size) {

		return (this.column_width * size) + (this.getWidgetSpacerWidth() * (size - 1));

	},

	// Calculate the height of a widget by the number of rows it takes up
	calculateHeightOfWidgetByRowCount: function(size) {

		return (this.row_height * size) + (this.getWidgetSpacerHeight() * (size - 1));

	},

	// Get the board width. The last widget in a row shouldn't have any right
	// spacing, but as it does for now, we need to reduce this dashboard size
	// by this amount
	getDashboardWidth: function() {

		// Last widget doesn't need to have spacing on the right
		return this.$dashboard.width() - this.getWidgetSpacerWidth();

	},

	// Get the spacer size for width
	getWidgetSpacerWidth: function() {

		return (this.column_spacing[1] + this.column_spacing[3]);

	},

	// Get the spacer size for height
	getWidgetSpacerHeight: function() {

		return (this.column_spacing[0] + this.column_spacing[2]);

	},

	// Get the total spacer size for entire row in dashboard
	getTotalSpacerWidth: function() {

		// Last column does not have a spacer on it
		return this.getWidgetSpacerWidth() * (this.number_columns - 1);

	},

	// Set the minimum page widht
	setMinPageWidth: function() {

		$('body').css('min-width', '1240px');

	},

	getGridCellByColRow: function(col, row) {

		return this.grid[this.number_columns*row + col];

	},

	caclWidth: function(units) {

        return units * this.column_width + ((units-1) * this.getWidgetSpacerWidth());

    },

    caclHeight: function(units) {

        return units * this.row_height + ((units-1) * this.getWidgetSpacerHeight());

    },

    caclTop: function(index) {

        var topNoSpacing = Math.floor(index / this.number_columns) * this.row_height;
        var spacing = Math.floor(index / this.number_columns) * 10;

        return topNoSpacing + spacing;

    },

    caclLeft: function(index) {

        var leftNoSpacing = (index % this.number_columns) * this.column_width;
        var spacing = (index % this.number_columns) * 10;

        return leftNoSpacing + spacing;

    },

    isDropAllowed: function(slot_el, widget_el) {
    	var slot_id = slot_el.data('id');
    	var widget_id =  widget_el.data('id');

        var slotx = (slot_id % this.number_columns);
        var sloty = Math.floor(slot_id / this.number_columns);

        var maxY  = Math.floor(this.grid.length / this.number_columns);

        var widget = this.widgets[this.getWidgetIndexById(widget_id)];

        // Check there is room for a drop - accross
        if (slotx + widget.widget.units_width > this.number_columns)
        	return false;

        // - down
        if (sloty + widget.widget.units_height > maxY)
        	return false;

        // Check neighbour spaces
        for (var x = slotx; x < (slotx + widget.widget.units_width); x++) {
            for (var y = sloty; y < (sloty + widget.widget.units_height); y++) {
                if (this.grid[this.number_columns*y + x] && this.grid[this.number_columns*y + x] != widget_el.data('id')) {
                    return false;
                }
            }
        }
        return true;
    },

    doDrop: function(slot_el, widget_el) {
    	var slot_id = slot_el.data('id');
    	var widget_id =  widget_el.data('id')

        var self = this;

        var dropAllowed = this.isDropAllowed(slot_el, widget_el);
        if (dropAllowed) {
            var widget = this.widgets[this.getWidgetIndexById(widget_id)];
            widget.widget.left = slot_el.css('left').replace('px', '');
            widget.widget.top = slot_el.css('top').replace('px', '');

            // Need to reorganise grid
            self.moveWidget(self.getWidgetPosById(widget_id), slot_id, widget.widget);

            widget_el.animate({
                left: slot_el.css('left'),
                top: slot_el.css('top')
            }, 500, function() {

            });
        }
        else {
            var widget = this.widgets[this.getWidgetIndexById(widget_id)];
            widget_el.animate({
                left: widget.widget.left,
                top: widget.widget.top
            }, 500, function() {
                // Animation complete.
            });
            $(this).addClass("drop-denied");
        }

        this.dumpGrid();
    },

    isResizeAllowed: function(widget_el) {
    	var widget_id =  this.getWidgetPosById(widget_el.data('id'));
    	var slot_id = widget_id;

        var slotx = (slot_id % this.number_columns);
        var sloty = Math.floor(slot_id / this.number_columns);

        var maxY  = Math.floor(this.grid.length / this.number_columns);

        var colSpan = this.calculateClosestColumnSize(widget_el.css('width').replace('px', ''));
        var rowSpan = this.calculateClosestRowSize(widget_el.css('height').replace('px', ''));

        for (var x = slotx; x < (slotx + colSpan); x++) {
            for (var y = sloty; y < (sloty + rowSpan); y++) {

                var currentIndex = this.number_columns*y + x;
                if (currentIndex != slot_id) {
                    if (this.grid[this.number_columns*y + x] && this.grid[this.number_columns*y + x] != widget_el.data('id')) {
                        return false;
                    }
                }
            }
        }
        return true;
    },

    calcColumnSpan: function(width) {

        return Math.ceil(width / this.column_width);

    },

    calcRowSpan: function(height) {

        return Math.ceil(height / this.row_height);

    },

    moveWidget: function(currentIndex, newIndex, widget) {
        var colSpan = widget.units_width;
        var rowSpan = widget.units_height;

        var startX = newIndex % this.number_columns;
        var startY = Math.floor(newIndex / this.number_columns);

        for (var x = startX; x < (startX + colSpan); x++) {
            for (var y = startY; y < (startY + rowSpan); y++) {

                var newLoopIndex = this.number_columns*y + x;
                var oldLoopIndex = newLoopIndex - (newIndex - currentIndex);

                this.grid.splice(oldLoopIndex, 1, null);
        		this.grid.splice(newLoopIndex, 1, widget.widget_id);

            }
        }
    },

    resizeWidget: function(current_cols, current_rows, new_cols, new_rows, widget) {

        var currentIndex = this.getWidgetPosById(widget.widget_id);

        var startX = currentIndex % this.number_columns;
        var startY = Math.floor(currentIndex / this.number_columns);

         // Clean up the old
        for (var x = startX; x < (startX + current_cols); x++) {
            for (var y = startY; y < (startY + current_rows); y++) {

                var newLoopIndex = this.number_columns*y + x;
                this.grid.splice(newLoopIndex, 1, null);

            }
        }

        for (var x = startX; x < (startX + new_cols); x++) {
            for (var y = startY; y < (startY + new_rows); y++) {

                var newLoopIndex = this.number_columns*y + x;
        		this.grid.splice(newLoopIndex, 1, widget.widget_id);

            }
        }
    },

    initWidgetInGrid: function(widget) {
        var currentIndex = widget.slot_number;

        var startX = currentIndex % this.number_columns;
        var startY = Math.floor(currentIndex / this.number_columns);

        for (var x = startX; x < (startX + widget.units_width); x++) {
            for (var y = startY; y < (startY + widget.units_height); y++) {

                var newLoopIndex = this.number_columns*y + x;
        		this.grid.splice(newLoopIndex, 1, widget.widget_id);

            }
        }

        this.dumpGrid();
    },

    resizeDashboardHeightToGrid: function() {

    	var numberRows = Math.floor(this.grid.length / this.number_columns);
    	var height = numberRows * this.row_height + (numberRows * 10);

    	this.$dashboard.css('height', height + 'px');
    },

    dumpGrid: function() {
    	return;

    	var rows = Math.floor(this.grid.length / this.number_columns);

    	console.log("----");
    	for (var x = 0; x < rows; x++) {
    		var debug = '';
    		for (var y = 0; y < this.number_columns; y++) {
    			debug += this.grid[this.number_columns*x + y] + ', ';
    		}
    		console.log(debug);
    	}
    	console.log("----");
    }
});
