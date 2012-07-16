Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TaskList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'task-list';
	},

	initPage: function(el) {
		var self = this;

		var openForEl = null;

		var updateCount = function(op, row) {

			var els = [];

			var checksub = function(prefix) {
				if (row.data('in-sublist-overdue')) { els.push(document.getElementById(prefix + '_overdue')); }
				if (row.data('in-sublist-today')) { els.push(document.getElementById(prefix + '_today')); }
				if (row.data('in-sublist-future')) { els.push(document.getElementById(prefix + '_future')); }
			};

			if (row.data('in-my')) {
				els.push(document.getElementById('tasks_counter_own_total'));
				checksub('tasks_counter_own');
			}
			if (row.data('in-my-teams')) {
				els.push(document.getElementById('tasks_counter_team_total'));
				checksub('tasks_counter_team');
			}
			if (row.data('in-delegated')) {
				els.push(document.getElementById('tasks_counter_delegated_total'));
				checksub('tasks_counter_delegated');
			}
			els.push(document.getElementById('tasks_counter_all_total'));
			checksub('tasks_counter_all');

			Array.each(els, function(el) {
				DeskPRO_Window.util.modCountEl($(el), op);
			});
		};

		var sendUpdate = function(rowEl, prop, val, callback) {
			var taskId = rowEl.data('task-id');
			var url = BASE_URL + 'agent/tasks/'+taskId+'/ajax-save';

			var postData = [];
			postData.push({
				name: 'action',
				value: prop
			});
			postData.push({
				name: 'value',
				value: val
			});

			$.ajax({
				url: url,
				type: 'POST',
				data: postData,
				dataType: 'json',
				success: callback || function() {}
			});
		};

		var assignOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('assign_ob'),
			onClose: function(ob) {

				var agentId = parseInt(ob.getSelected('agents') || 0);
				var agentTeamId = parseInt(ob.getSelected('teams') || 0);

				var obel = self.getEl('assign_ob');

				if (agentId && agentId != DESKPRO_PERSON_ID) {
					var val = 'agent:' + agentId;
					var text = $('.agent-label-' + agentId).first().text().trim();
				} else if (agentTeamId) {
					var val = 'agent_team:' + agentTeamId;
					var text = $('.agent-team-label-' + agentTeamId).first().text().trim();
				} else {
					var val = '';
					var text = 'Me';
				}

				sendUpdate(openForEl, 'assigned', val, function() {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.tasks.refresh-task-list');
				});
				$('.opt-trigger.assigned_agent label', openForEl).text(text);
			}
		});

		var statusMenu = new DeskPRO.UI.Menu({
			menuElement: this.getEl('menu_vis'),
			onItemClicked: function(info) {
				sendUpdate(openForEl, 'visibility', $(info.itemEl).data('vis'));
				$('.opt-trigger.visibility label', openForEl).text($(info.itemEl).text());
			}
		});

		el.on('click', 'input.item-select', function(ev) {
			var row = $(this).closest('article.task');
			var value = $(this).is(':checked');

			if (value) {
				$('.task-sub-wrap', row).hide();
				row.addClass('completed');

				sendUpdate(row, 'completed', 1);

				updateCount('-', row);
			} else {
				row.removeClass('expanded');
				$('.task-sub-wrap', row).show();
				row.removeClass('completed');

				sendUpdate(row, 'completed', 0);

				updateCount('+', row);
			}

			self.updateUi();
		});
		el.on('click', '.opt-trigger.assigned_agent', function(ev) {
			openForEl = $(this).closest('article.task');
			assignOptionBox.open(ev);
		});
		el.on('click', '.opt-trigger.visibility', function(ev) {
			openForEl = $(this).closest('article.task');
			statusMenu.open(ev);
		});
		el.on('click', '.opt-trigger.date_due', function(ev) {
			openForEl = $(this).closest('article.task');

			var label = $('label', this);
			var date = openForEl.data('date-due');
			if (!date) {
				date = new Date();
			}

			openForEl.datepicker('dialog', date, function(date, inst) {
				sendUpdate(openForEl, 'date_due', date);
				label.text(date);
			}, {
				dateFormat: 'yy-mm-dd',
				showButtonPanel: true,
				beforeShow: function(input) {
					setTimeout(function() {
						var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");

						var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">Clear</button>');
						btn.unbind("click").bind("click", function () { $.datepicker._clearDate( input ); label.text('No due date'); });
						btn.appendTo( buttonPane );

						$(input).datepicker("widget").css('z-index', 30001);
					},1);
				}
			}, ev);
		});
		el.on('click', '.expand-collapse-icon', function(ev) {
			var row = $(this).closest('article.task');
			if (row.is('.expanded')) {
				row.removeClass('expanded');
				$('.task-info', row).hide();
				$('.task-comments', row).hide();
				$('.new-comment', row).hide();
				self.updateUi();

			} else {
				row.addClass('expanded');
				$('.task-info', row).show();
				$('.task-comments', row).show();
				$('.new-comment', row).show();
				self.updateUi();
			}
		});

		el.on('click', '.comment-btn', function(ev) {
			var row = $(this).closest('article.task');
			var input = $('.new-comment', row);
			if (input.is(':visible')) {
				input.hide();
			} else {
				input.show();
			}

			self.updateUi();
		});
		el.on('click', '.cancel-comment-trigger', function(ev) {
			var row = $(this).closest('article.task');
			var btn = $('.comment-btn', row);
			$('.new-comment', row).hide();
			self.updateUi();
		});

		el.on('click', '.save-comment-trigger', function(ev) {
			var row = $(this).closest('article.task');
			var commentTxt = $('textarea', row);

			var closefn = function() {
				commentTxt.val('');
				$('.new-comment', row).hide();
			};

			if (!commentTxt.val().trim().length) {
				return;
			}

			var postData = [];
			postData.push({
				name: 'comment',
				value: commentTxt.val().trim()
			});

			row.addClass('loading');

			var taskId = row.data('task-id');
			$.ajax({
				url: BASE_URL + 'agent/tasks/'+taskId+'/ajax-save-comment',
				type: 'POST',
				dataType: 'json',
				data: postData,
				complete: function() {
					row.removeClass('loading');
				},
				success: function(data) {
					closefn();

					if (data.error) {
						return;
					}

					var list = $('ul.task-comment-list', row);
					$(data.comment_li_html).appendTo(list);
					$('.task-comments', row).show();
					self.updateUi();
				}
			});
		});

		el.on('click', '.task-group header', function() {
			$(this).parent().toggleClass('collapsed')
			self.updateUi();
		});

		el.on('click', '.delete-task', function(ev) {
			var row = $(this).closest('.row-item');
			var taskId = row.data('task-id');

			row.slideUp();
			updateCount('-', row);
			$.ajax({
				url: BASE_URL + 'agent/tasks/' + taskId + '/delete',
				error: function() {
					row.show();
				},
				success: function() {
					row.remove();
				}
			});
		});
	}
});
