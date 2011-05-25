Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tickets = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.button = $('#tickets_section');

		var sectionEl = $('#tickets_outline');
		this.setSectionElement(sectionEl);

		$.ajax({
			url: BASE_URL + 'agent/tickets/get-section-data.json',
			context: this,
			success: function(data) {
				this._initData(data);
			}
		});
	},

	_initData: function(data) {
		$('#tickets_outline_filters_list').html(data.filters);
	}
});