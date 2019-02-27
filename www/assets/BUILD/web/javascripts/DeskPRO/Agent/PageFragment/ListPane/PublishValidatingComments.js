Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		this.wrapper = el;
    var self = this;
		var btn  = this.wrapper.find('.list-selection-bar .perform-actions-trigger');
		var load = this.wrapper.find('.list-selection-bar .ajax-loading');

		this.actionsMenu = new DeskPRO.UI.Menu({
			menuElement: $('ul.actions-menu:first', this.wrapper),
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
			onItemClicked: function(info) {
				var data = [];
				var lines = [];
				$('input.item-select:checked', this.wrapper).each(function() {
					lines.push($(this).parent().get(0));
					var typename = $(this).data('content-type');
					var id = $(this).data('comment-id');

					data.push({
						name: 'content[' + typename + '][]',
						value: id
					});
				});

				if (!data.length) {
					return;
				}

				btn.hide();
				load.show();
        $(lines).fadeOut();
        self.updateCount('sub', lines.length);
				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/publish/comments/validating-mass-actions/' + action,
					data: data,
					type: 'POST',
					dataType: 'json',
          error: function() {
            $(lines).fadeIn();
            self.updateCount('add', lines.length)
          },
					complete: function() {
						load.hide();
						btn.show();
					},
					success: function() {
						self.selectionBar.checkNone();
            self.removeElement($(lines));
					}
				});
			}
		});
		this.ownObject(this.actionsMenu);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function(ev) {
				self.actionsMenu.open(ev);
			}/*,
			onCountChange: function(count) {
				var isOpen = self.actionsMenu.isOpen();

				if (count > 0 && !isOpen) {
					self.actionsMenu.open();
				} else if (count <= 0 && isOpen) {
					self.actionsMenu.close();
				}
			}*/
		});
		this.ownObject(this.selectionBar);

		var findRowInfo = function(el) {

			var row = $(el);

			var editRow = $(el).closest('div.edit-comment');
			if (editRow.length) {
				var row = $('article.' + editRow.data('content-type') + '-' + editRow.data('comment-id'));
				return findRowInfo(row);
			}

			row = row.closest('article');

			var check = $('input.item-select', row);
			if (!check.length) {
				return;
			}

			var info = {
				row: row,
				contentType: $(check).data('content-type'),
				commentId: $(check).data('comment-id')
			};

			var editRow = $('div.edit-' + info.contentType + '-' + info.commentId, self.wrapper);
			console.log(editRow);
			info.editRow = editRow;

			return info;
		};

		this.wrapper.on('click', '.validate-approve', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.approveComment(info.contentType, info.commentId, info.row);
		});
		this.wrapper.on('click', '.validate-delete', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.deleteComment(info.contentType, info.commentId, info.row);
		});

		this.wrapper.on('click', '.validate-edit', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.editComment(info.contentType, info.commentId, info.row, info);
		});

		this.wrapper.on('click', '.comment-editsave-trigger', function(ev) {
			var info = findRowInfo(this);

			var commentText = $('textarea', info.editRow).val().trim();
			if (!commentText.length) {
				info.row.show();
				info.editRow.hide();
			}

			$.ajax({
				url: BASE_URL + 'agent/publish/comments/save-comment/'+info.contentType+'/'+info.commentId,
				type: 'POST',
				data: {
					comment: commentText
				},
				dataType: 'json',
				success: function(data) {
					var rendered = $('.rendered', info.row);
					rendered.html(data.comment_html);

					info.row.show();
					info.editRow.hide();
				}
			});
		});

		this.wrapper.on('click', '.comment-editcancel-trigger', function(ev) {
			var info = findRowInfo(this);
			var editEl = info.editRow;

			info.row.show();
			editEl.hide();
		});

		this.wrapper.on('click', '.validate-create-ticket', function(ev) {
			var info = findRowInfo(this);
			$.ajax({
				url: BASE_URL + 'agent/publish/comments/new-ticket-info/' + info.contentType + '/' + info.commentId + '.json',
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					DeskPRO_Window.newTicketLoader.open(function(page) {
						page.setNewByComment(data);
					});
				}
			});
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-ui.comment-remove', function(data) {
			$('article.' + data.comment_type + '-' + data.comment_id, this.wrapper).fadeOut();
		});
	},

	deleteComment: function(typename, commentId, el) {
		if (!el) {
			el = $('article.' + typename + '-' + commentId, this.wrapper);
		}

		el.fadeOut();

		this.updateCount('sub');

		$.ajax({
			url: BASE_URL + 'agent/publish/comments/delete/'+typename+'/'+commentId,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				this.updateCount('add');
				el.fadeIn();
			},
			success: function(data) {
        this.removeElement(el);
				if (DeskPRO_Window.sections.publish_section) {
					DeskPRO_Window.sections.publish_section.modCommentCount(typename, '-');
				}
			}
		});
	},

	approveComment: function(typename, commentId, el) {
		if (!el) {
			el = $('article.' + typename + '-' + commentId, this.wrapper);
		}

		el.fadeOut();

		this.updateCount('sub');

		$.ajax({
			url: BASE_URL + 'agent/publish/comments/approve/'+typename+'/'+commentId,
			type: 'POST',
			context: this,
			dataType: 'json',
			error: function() {
				this.updateCount('add');
        !el || el.fadeIn();
			},
			success: function(data) {
        this.removeElement(el);
			}
		});
	},

	editComment: function(typename, commentId, el, info) {

		el.hide();
		var editEl = info.editRow;

		editEl.show();
	},

	updateCount: function(action, num) {
		var countEl = '#publish_validating_comments_count';
		num = num || 1;
		DeskPRO_Window.util.modCountEl(countEl, action, num);
		DeskPRO_Window.sections.publish_section.recountBadge();
	},

  removeElement: function(el) {
    el.remove();
    if ($('.row-item', this.wrapper).not('.edit-comment').length < 1) {
      DeskPRO_Window.loadListPane(this.meta.resetUrl);
      this.destroy();
    }
  }
});
