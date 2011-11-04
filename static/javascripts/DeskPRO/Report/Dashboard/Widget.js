Orb.createNamespace('DeskPRO.Report.Dashboard');

/**
 * Represent a dashboard widget
 */
DeskPRO.Report.Dashboard.Widget = new Class({
        
	// Reference back to dashboard
	dashboard: null,
	
        // Id of the widget
        widget_id: null,
        
	// HTML element ID
	element_id: null,
	
	// Stat Id
	stat_id: null,
	
	// Title
	title: '',
	
        // Number of slot this widget takes up in the dashboard grid
        num_slots: 1,
        
        // State of the widget, can be in view or edit state
	is_edit_state: false,
        
        // The chart associated with the widget
        chart: null,
        
	initialize: function(dashboard, widget_id, options) {
                
		this.dashboard = dashboard;
                this.widget_id = widget_id;
		
		this.element_id = Orb.getUniqueId();
		
		this.stat_id = options.stat_id || '';
		this.title   = options.title || '';
	},
        
	addUIHandlers: function() {
		var self = this;
		
		$("#" + this.element_id + " .edit").click(function() {
			self.dashboard.openOverlay('dashboard_widget_edit');
			return false;
		});
		
		$("#" + this.element_id + " .close").click(function() {
			self.dashboard.removeWidget(self);
			return false;
		});
		
	},
	
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
        
        updateToEditable: function() {
                
		$("#report-dashboard-grid li .grid-slot-toolbar .icons a").css('display', 'block');
		$("#report-dashboard-grid li .grid-slot-toolbar").css('cursor', 'move');
		
                this.is_edit_state = true;
                
        },
        
        updateToViewable: function() {
                
		$("#report-dashboard-grid li .grid-slot-toolbar .icons a").css('display', 'none');
		$("#report-dashboard-grid li .grid-slot-toolbar").css('cursor', 'auto');
		
                this.is_edit_state = false;
                
        },
        
        setChart: function(chart) {
                
                this.chart = chart;
                
        },
        
        removeChart: function() {
                
                this.chart = null;
                
        },
        
});