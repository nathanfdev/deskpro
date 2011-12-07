Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaCommentsValidating = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments,

	updateCount: function(action) {
		var countEl = $('#ideas_comments_validating_count');
		var count = parseInt(countEl.text());

		if (action == 'add') {
			count++;
		} else {
			count--;
		}

		if (count < 0) {
			count = 0;
		}

		var countEl = $('#ideas_comments_validating_count').text(count);

		DeskPRO_Window.sections.ideas_section.recountBadge();
	}
});
