Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.actionsMenu = new DeskPRO.UI.Menu({
			menuElement: $('ul.actions-menu:first', this.wrapper),
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

				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/publish/comments/validating-mass-actions/' + action,
					data: data,
					type: 'POST',
					dataType: 'json',
					success: function() {
						$(lines).fadeOut();
					}
				});
			}
		});
		this.ownObject(this.actionsMenu);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function(ev) {
				self.actionsMenu.open(ev);
			}
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

		this.wrapper.delegate('.validate-approve', 'click', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.approveComment(info.contentType, info.commentId, info.row);
		});
		this.wrapper.delegate('.validate-delete', 'click', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.deleteComment(info.contentType, info.commentId, info.row);
		});

		this.wrapper.delegate('.validate-edit', 'click', function(ev) {
			ev.stopPropagation();

			var info = findRowInfo(this);
			self.editComment(info.contentType, info.commentId, info.row, info);
		});

		this.wrapper.delegate('.comment-editsave-trigger', 'click', function(ev) {
			var info = findRowInfo(this);

			$.ajax({
				url: BASE_URL + 'agent/publish/comments/save-comment/'+info.contentType+'/'+info.commentId,
				type: 'POST',
				data: {
					comment: $('textarea:first', info.row).val()
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

		this.wrapper.delegate('.comment-editcancel-trigger', 'click', function(ev) {
			var info = findRowInfo(this);
			var editEl = info.editRow;

			info.row.show();
			editEl.hide();
		});

		this.wrapper.delegate('.validate-create-ticket', 'click', function(ev) {
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
				el.remove();
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
				el.fadeIn();
			},
			success: function(data) {
				el.remove();
			}
		});
	},

	editComment: function(typename, commentId, el, info) {

		el.hide();
		var editEl = info.editRow;

		if (!editEl.is('.rte-inited')) {
			editEl.addClass('rte-inited');
			$('textarea', editEl).tinymce({
				script_url: ASSETS_BASE_URL + '/vendor/tiny_mce/tiny_mce.js',

				theme: 'advanced',
				plugins : "fullscreen",
				fullscreen_new_window: true,
				theme_advanced_buttons1: 'bold,italic,underline,|,link,unlink,anchor,image',
				theme_advanced_buttons3: '',
				theme_advanced_toolbar_location: 'top',
				theme_advanced_toolbar_align: 'left',
				theme_advanced_statusbar_location: 'none'
			});
		}

		editEl.show();
	},

	updateCount: function(action) {
		var countEl = $('#publish_validating_comments_count');
		var count = parseInt(countEl.text());

		if (action == 'add') {
			count++;
		} else {
			count--;
		}

		if (count < 0) {
			count = 0;
		}

		var countEl = $('#publish_validating_comments_count').text(count);
	}
});
