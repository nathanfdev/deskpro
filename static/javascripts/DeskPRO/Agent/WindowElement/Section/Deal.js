Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Deal = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
            this.buttonEl = $('#deals_section');

			this.urlFragmentName = 'deals';

            this.setSectionElement($('<section id="deal_outline"></section>'));
            this.refresh();
            
        },

	refresh: function() {
		$.ajax({
                url: BASE_URL + 'agent/deal/get-section-data.json',
                context: this,
                success: function(data) {
                    this._initSection(data);
                }
            });
	},
	_initSection: function(data) {
		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);
	}
});
