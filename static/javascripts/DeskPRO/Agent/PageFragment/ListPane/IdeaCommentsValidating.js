Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.IdeaCommentsValidating = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	filterSearchForm: null,

	initPage: function(el) {
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));
		this.initCommentRows(this.wrapper);
	},

	initCommentRows: function(commentWrapper) {
		var self = this;
		$('button.delete-trigger', commentWrapper).click(function() {
			self.handleDisapprove($(this).data('comment-id'));
		});
		$('button.approve-trigger', commentWrapper).click(function() {
			self.handleApprove($(this).data('comment-id'));
		});
	},

	handleApprove: function(comment_id) {
		$('article.comment-' + comment_id, this.wrapper).fadeOut();

		$.ajax({
			url: BASE_URL + 'agent/ideas/approve-comment/' + comment_id,
			type: 'POST',
			dataType: 'json'
		});
	},

	handleDisapprove: function(comment_id) {
		$('article.comment-' + comment_id, this.wrapper).fadeOut();

		$.ajax({
			url: BASE_URL + 'agent/ideas/disapprove-comment/' + comment_id,
			type: 'POST',
			dataType: 'json'
		});
	}
});