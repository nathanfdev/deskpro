Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Twitter = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#twitter_section');

		this.setSectionElement($('<section id="twitter_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/twitter/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {
		this.contentEl.html(data.section_html);

		if (this.isVisible()) {
			this._onShowLoadList();
		}

		this.contentEl.addClass('scroll-content').tinyscrollbar();

		//this.contentEl.addClass('scroll-content');
		//this.contentEl.wrap('<div class="scroll-viewport" />');
		//$('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').insertBefore(this.contentEl);
//
		//this.sectionEl.addClass('with-scrollbar');
		//this.sectionEl.tinyscrollbar();
	}
});