Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingComments = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.initRoutesOnCollection($('.with-route', el));

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

		this.wrapper.delegate('.validate-approve', 'click', function(ev) {
			ev.stopPropagation();

			var row = $(this);
			var x = 0;
			while (!row.is('article')) {
				if (x++ > 10) return;
				row = row.parent();
			}
			var check = $('input.item-select', row);
			if (!check.length) {
				return;
			}

			self.approveComment($(check).data('content-type'), $(check).data('comment-id'), row);
		});
		this.wrapper.delegate('.validate-delete', 'click', function(ev) {
			ev.stopPropagation();

			var row = $(this);
			var x = 0;
			while (!row.is('article')) {
				if (x++ > 10) return;
				row = row.parent();
			}
			var check = $('input.item-select', row);
			if (!check.length) {
				return;
			}

			self.deleteComment($(check).data('content-type'), $(check).data('comment-id'), row);
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
