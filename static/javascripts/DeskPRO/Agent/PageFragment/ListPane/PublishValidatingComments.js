Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

		var self = this;
		$('button.ignore-trigger', this.wrapper).click(function(ev) {
			ev.preventDefault();
			self.ignoreComment($(this).parent().parent().parent().parent());
		});

		$('button.approve-trigger', this.wrapper).click(function(ev) {
			ev.preventDefault();
			self.approveComment($(this).data('url'), $(this).parent().parent().parent().parent());
		});

		$('button.delete-trigger', this.wrapper).click(function(ev) {
			ev.preventDefault();
			self.deleteComment($(this).data('url'), $(this).parent().parent().parent().parent());
		});
	},

	ignoreComment: function(commentEl) {
		commentEl.fadeOut();
	},

	deleteComment: function(url, commentEl) {
		commentEl.fadeOut();
		this.updateCount('sub');

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				this.updateCount('add');
				commentEl.fadeIn();
			},
			success: function(data) {
				commentEl.remove();
			}
		});
	},

	approveComment: function(url, commentEl) {
		commentEl.fadeOut();
		this.updateCount('sub');
		
		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				this.updateCount('add');
				commentEl.fadeIn();
			},
			success: function(data) {
				commentEl.remove();
			}
		});
	},

	updateCount: function(action) {
		var countEl = $('#publish_validating_comments_count');
		var count = parseInt(countEl.text());

		if (action == 'add') {
			count++;
		} else {
			count--;
		}

		var countEl = $('#publish_validating_comments_count').text(count);
	}
});