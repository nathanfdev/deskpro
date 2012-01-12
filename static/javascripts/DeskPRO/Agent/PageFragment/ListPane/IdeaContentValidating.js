Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaContentValidating = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.PublishValidatingContent,

	listRemove: function(el) {
		DeskPRO_Window.util.modCountEl($('#ideas_validating_count'), '-');
		DeskPRO_Window.sections.ideas_section.recountBadge();
		this.selectionBar.checkNone();
	}
});
