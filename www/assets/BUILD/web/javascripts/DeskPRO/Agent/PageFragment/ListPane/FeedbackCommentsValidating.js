Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.FeedbackCommentsValidating = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments,

	updateCount: function(action) {
		var countEl = $('#feedback_comments_validating_count');
		var count = parseInt(countEl.text(), 10);

		if (action == 'add') {
			count++;
		} else {
			count--;
		}

		if (count < 0) {
			count = 0;
		}

		$('#feedback_comments_validating_count').text(count);
		var text = $('#comments_validating_count_header').text();
		$('#comments_validating_count_header').text(text.replace(/\d+/, count));

		DeskPRO_Window.sections.feedback_section.recountBadge();
	}
});
