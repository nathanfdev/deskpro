Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Task = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
            this.buttonEl = $('#tasks_section');

            this.setSectionElement($('<section id="task_outline"></section>'));

            $.ajax({
                url: BASE_URL + 'agent/task/get-section-data.json',
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
