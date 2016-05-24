Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.FeedbackCommentsValidating = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments,

	updateCount: function(action, num) {
		num = num || 1;
		DeskPRO_Window.util.modCountEl($('#feedback_comments_validating_count'), action, num);
		DeskPRO_Window.util.modCountEl($('.comments_validating_count_header', this.wrapper), action, num);
		DeskPRO_Window.sections.feedback_section.recountBadge();
	}
});
