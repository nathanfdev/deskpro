Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});

		this.actionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('button.perform-actions-trigger:first', this.wrapper),
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

		var findRowInfo = function(el) {
			var row = $(el);
			var x = 0;
			while (!row.is('article')) {
				if (x++ > 10) return;
				row = row.parent();
			}
			var check = $('input.item-select', row);
			if (!check.length) {
				return;
			}

			return {
				row: row,
				contentType: $(check).data('content-type'),
				commentId: $(check).data('comment-id')
			};
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
			self.editComment(info.contentType, info.commentId, info.row);
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

					var wr = $('.edit-comment', info.row).hide();
					$('.comment-display', info.row).show();
				}
			});
		});

		this.wrapper.delegate('.comment-editcancel-trigger', 'click', function(ev) {
			var info = findRowInfo(this);
			var wr = $('.edit-comment', info.row).hide();
			$('.comment-display', info.row).show();
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

	editComment: function(typename, commentId, el) {
		$('.comment-display', el).hide();
		var wr = $('.edit-comment', el).show();
		if (!wr.is('.rte-inited')) {
			wr.addClass('rte-inited');
			$('textarea', wr).tinymce({
				script_url: ASSETS_BASE_URL + '/vendor/tiny_mce/tiny_mce.js',

				theme: 'advanced',
				plugins : "fullscreen",
				fullscreen_new_window: true,
				theme_advanced_buttons1: 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect',
				theme_advanced_buttons2: ',bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen',
				theme_advanced_buttons3: '',
				theme_advanced_toolbar_location: 'top',
				theme_advanced_toolbar_align: 'left',
				theme_advanced_resizing: true,
				theme_advanced_statusbar_location: 'bottom'
			});
		}
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
