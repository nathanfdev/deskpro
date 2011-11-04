Orb.createNamespace('DeskPRO.Report.Dashboard');

/**
 * Represent a dashboard widget
 */
DeskPRO.Report.Dashboard.Widget = new Class({
        
        // Number of slot this widget takes up in the dashboard grid
        num_slots: 1,
        
        // State of the widget, can be in view or edit state
	is_edit_state: false,
        
        // The chart associated with the widget
        chart: null,
        
	initialize: function() {
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
                
                this.is_edit_state = true;
                
        },
        
        
        updateToViewable: function() {
                
                this.is_edit_state = false;
                
        },
        
        setChart: function(chart) {
                
                this.chart = chart;
                
        }
        
});