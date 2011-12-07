Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Tasks = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#tasks_section');

		this.urlFragmentName = 'tasks';

		this.setSectionElement($('<section id="task_outline"></section>'));
		this.refresh();
	},

	refresh: function() {
		var countmap = {};
		$('span.list-counter', this.contentEl).each(function() {
			countmap[$(this).attr('id')] = $(this).text().trim();
		});

		var selected = $('.nav-selected', this.contentEl);
		var selectedCountId = null;
		if (selected) {
			var e = $('span.list-counter', selected).first();
			if (e.length) {
				selectedCountId = e.attr('id');
			}
		}

		DeskPRO_Window.getSectionData('tasks_section', (function(data) {
			this._initSection(data);

			// Get button count now
			var count = 0;
			$('span.count-in-badge', this.contentEl).each(function() {
				count += parseInt($(this).text()) || 0;
			});
			this.updateBadge(count);

			if (selectedCountId) {
				var countEl = $('#' + selectedCountId);
				var newCount = countEl.text().trim();
				var nav = countEl.closest('.is-nav-item');

				// Re-select the proper nav item
				nav.addClass('nav-selected');

				// And reload the view if its changed
				if (newCount != countmap[selectedCountId]) {
					var routeEl;
					if (nav.data('route')) {
						routeEl = nav;
					} else {
						routeEl = $('[data-route]', nav).first();
					}
					DeskPRO_Window.runPageRouteFromElement(routeEl);
				}

			}
		}).bind(this));
	},

	_initSection: function(data) {
		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);
	}
});
