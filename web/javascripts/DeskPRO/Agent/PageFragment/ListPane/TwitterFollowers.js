Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterFollowers = new Orb.Class({
    Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

    initPage: function(el) {
        this.wrapper = $(el);
		this.content = $('.content', this.wrapper);

		var self = this;

		// user follow/unfollow
		this.content.on('click', '.follow', function(e) {
			e.preventDefault();

			$(this).addClass('unfollow').removeClass('follow');
			$(this).find('label').text('Unfollow');

			var id = $(this).closest('.twitter-user').attr('data-user-id');

			$.ajax({
				url: self.getMetaData('saveFollowUrl'),
				type: 'POST',
				data: {
					user_id: id,
					account_id: self.getMetaData('accountId')
				}
			});
		});
		this.content.on('click', '.unfollow', function(e) {
			e.preventDefault();

			$(this).addClass('follow').removeClass('unfollow');
			$(this).find('label').text('Follow');

			var id = $(this).closest('.twitter-user').attr('data-user-id');

			$.ajax({
				url: self.getMetaData('saveUnfollowUrl'),
				type: 'POST',
				data: {
					user_id: id,
					account_id: self.getMetaData('accountId')
				}
			});
		});

		// user archive/unarchive (status class names used)
		this.content.on('click', '.status-archive', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-user');

			$(this).addClass('status-archived').removeClass('status-archive');
			row.addClass('archived');

			var id = row.attr('data-user-id');
			self.doArchive(id, 1);

			if (self.getMetaData('hideArchived')) {
				row.remove();
			}
		});
		this.content.on('click', '.status-archived', function(e) {
			e.preventDefault();

			$(this).addClass('status-archive').removeClass('status-archived');
			$(this).closest('.twitter-user').removeClass('archived');

			var id = $(this).closest('.twitter-user').attr('data-user-id');
			self.doArchive(id, 0);
		});

		// message triggers (reply class names used)
		this.content.on('click', 'li.opt-trigger.message', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-user');

			var newReply = row.find('.new-reply');
			if (newReply.is(':visible')) {
				newReply.hide();
			} else {
				newReply.show();

				var textarea = newReply.find('textarea');
				if (!$.trim(textarea.val()).length) {
					var name = row.find('h4.user .screen-name').text();

					textarea.val(name + ' ');
				}

				textarea.focus();
			}
		});
		this.content.on('click', '.new-reply .reply-type li', function() {
			var $this = $(this);
			var replyContainer = $this.closest('.new-reply');

			replyContainer.find('.reply-type li').removeClass('on');
			$this.addClass('on');
			replyContainer.find('.reply-type-hidden').val($this.data('type'));
		});
		this.content.on('click', '.cancel-reply-trigger', function() {
			var replyContainer = $(this).closest('.new-reply');
			replyContainer.hide();
		});
		this.content.on('click', '.save-reply-trigger', function(e) {
			e.preventDefault();

			var row = $(this).closest('.twitter-user');
			var userId = row.attr('data-user-id');
			var replyContainer = $(this).closest('.new-reply');

			var val = $.trim(replyContainer.find('textarea').val());
			if (!val.length) {
				replyContainer.hide();
				return;
			}

			var type = replyContainer.find('.reply-type-hidden').val();

			replyContainer.addClass('loading');

			$.ajax({
				url: self.getMetaData('saveUserMessageUrl'),
				type: 'POST',
				dataType: 'json',
				data: {
					user_id: userId,
					account_id: self.getMetaData('accountId'),
					text: val,
					type: type
				},
				success: function(json) {
					if (json.success) {
						row.find('.message-sent-confirmation .message').text(val);
						row.find('.message-sent-confirmation').show();

						replyContainer.hide();
						replyContainer.find('textarea').val('')
					} else {
						alert(json.error);
					}
				}
			}).always(function() {
				replyContainer.removeClass('loading');
			});
		});
    },

	doArchive: function(id, archive) {
		$.ajax({
			url: this.getMetaData('saveUserArchiveUrl'),
			type: 'POST',
			dataType: 'json',
			data: {
				user_id: id,
				account_id: this.getMetaData('accountId'),
				archive: archive ? 1 : 0
			},
			success: function(json) {
				if (json.error) {
					alert(json.error);
				}
			}
		});
	}

});
