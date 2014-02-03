define(function() {
	return {
		init: function() {
			this.renderTemplateTab("Test Tab ({{tab.count}})", 'Ticket/after-props.html', '@properties.tab');
		}
	}
});