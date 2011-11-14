Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.Comments = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options)  {
		var self = this;
		this.page = page;

		this.options = {
			/**
			 * The main comments wrapper element
			 */
			commentsWrapper: null
		};

		this.setOptions(options);

		this.commentsWrapper = $(this.options.commentsWrapper);

		this.commentsWrapper.delegate('.comment-edit-btn', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			self.getCommentMenu().open(ev);
		});

		this.commentsWrapper.delegate('.comment-validate-btn', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			self.getCommentValidationMenu().open(ev);
		});
	},

	getCommentMenu: function() {
		if (this._commentMenu) return this._commentMenu;

		var self = this;
		this._commentMenu = new DeskPRO.UI.Menu({
			menuElement: $('#comment_tools_menu'),
			onItemClicked: function(info) {
				var commentEl = $(info.menu.getOpenTriggerElement()).parent().parent().parent();
				var action = $(info.itemEl).data('action');

				switch (action) {
					case 'edit':
						self.editComment(commentEl, commentEl.data('content-type'), commentEl.data('comment-id'));
						break;

					case 'delete':
						self.deleteComment(commentEl, commentEl.data('content-type'), commentEl.data('comment-id'));
						break;

					case 'create-ticket':
						$.ajax({
							url: BASE_URL + 'agent/publish/comments/new-ticket-info/' + commentEl.data('content-type') + '/' + commentEl.data('comment-id') + '.json',
							type: 'GET',
							dataType: 'json',
							success: function(data) {
								DeskPRO_Window.newTicketLoader.open(function(page) {
									page.setNewByComment(data);
								});
							}
						});
						break;
				}
			}
		});

		return this._commentMenu;
	},

	getCommentValidationMenu: function() {
		if (this._commentValidationMenu) return this._commentValidationMenu;

		var self = this;
		this._commentValidationMenu = new DeskPRO.UI.Menu({
			menuElement: $('#comment_validation_menu'),
			onItemClicked: function(info) {
				var commentEl = $(info.menu.getOpenTriggerElement()).parent().parent().parent();
				var action = $(info.itemEl).data('action');

				switch (action) {
					case 'approve':
						self.approveComment(commentEl, commentEl.data('content-type'), commentEl.data('comment-id'));
						break;

					case 'delete':
						self.deleteComment(commentEl, commentEl.data('content-type'), commentEl.data('comment-id'));
						break;
				}
			}
		});

		return this._commentValidationMenu;
	},


	editComment: function(commentEl, typename, commentId) {
		var self = this;
		$.ajax({
			url: BASE_URL + 'agent/publish/comments/info/'+typename+'/'+commentId,
			type: 'GET',
			dataType: 'json',
			context: this,
			success: function(data) {
				var editEl = $(DeskPRO_Window.util.getPlainTpl('#comment_edit_tpl'));
				$('.save-trigger', editEl).on('click', function(ev) {
					ev.preventDefault();
					self._saveEditComment(commentEl, editEl, typename, commentId);
				});
				$('.cancel-trigger', editEl).on('click', function(ev) {
					ev.preventDefault();
					self._closeEditComment(commentEl, editEl);
				});
				editEl.hide();

				$('textarea.comment', editEl).val(data.comment_text);

				var rendered = $('.rendered-message', commentEl);
				editEl.insertBefore(rendered);

				rendered.slideUp('fast', function() {
					editEl.slideDown('fast');
				});
			}
		});
	},

	_saveEditComment: function(commentEl, editEl, typename, commentId) {
		$.ajax({
			url: BASE_URL + 'agent/publish/comments/save-comment/'+typename+'/'+commentId,
			type: 'POST',
			data: {
				comment: $('textarea.comment', editEl).val()
			},
			dataType: 'json',
			context: this,
			error: function() {

			},
			success: function(data) {
				var rendered = $('.rendered-message', commentEl);
				rendered.html(data.comment_html);
				this._closeEditComment(commentEl, editEl);
			}
		});
	},

	_closeEditComment: function(commentEl, editEl) {
		var rendered = $('.rendered-message', commentEl);
		editEl.slideUp('fast', function() {
			rendered.slideDown();
			editEl.remove();
		});
	},

	deleteComment: function(commentEl, typename, commentId) {
		commentEl.fadeOut();
		$.ajax({
			url: BASE_URL + 'agent/publish/comments/delete/'+typename+'/'+commentId,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				commentEl.show();
			},
			success: function(data) {
				el.remove();
			}
		});
	},

	approveComment: function(commentEl, typename, commentId) {
		commentEl.removeClass('validating');
		$.ajax({
			url: BASE_URL + 'agent/publish/comments/approve/'+typename+'/'+commentId,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				commentEl.addClass('validating');
			}
		});
	}
});
