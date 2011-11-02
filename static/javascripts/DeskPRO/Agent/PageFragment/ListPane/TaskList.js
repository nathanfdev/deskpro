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

		var sendUpdate = function(rowEl, prop, val) {
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
				dataType: 'json'
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

				sendUpdate(openForEl, 'assigned', val);
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

		el.delegate('input.item-select', 'click', function(ev) {
			var row = $(this).closest('article.task');
			var value = $(this).is(':checked');

			if (value) {
				$('.task-info', row).slideUp();
				$('.task-comments', row).slideUp();
				$('.new-comment', row).slideUp();
				row.addClass('completed');

				sendUpdate(row, 'completed', 1);
			} else {
				$('.task-info', row).slideDown();
				$('.task-comments', row).slideDown();
				$('.new-comment', row).slideDown();
				row.removeClass('completed');

				sendUpdate(row, 'completed', 0);
			}
		});
		el.delegate('.opt-trigger.assigned_agent', 'click', function(ev) {
			openForEl = $(this).closest('article.task');
			assignOptionBox.open(ev);
		});
		el.delegate('.opt-trigger.visibility', 'click', function(ev) {
			openForEl = $(this).closest('article.task');
			statusMenu.open(ev);
		});
		el.delegate('.opt-trigger.date_due', 'click', function(ev) {
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

						$(input).datepicker("widget").css('z-index', 9999999);
					},1);
				}
			}, ev);
		});

		el.delegate('.comment-btn', 'click', function(ev) {
			var input = $(this).parent().find('.comment-input');
			$(this).slideUp('fast', function() {
				input.slideDown('fast');
			});
		});
		el.delegate('.cancel-comment-trigger', 'click', function(ev) {
			var btn = $(this).parent().parent().find('.comment-btn');
			$(this).parent().slideUp('fast', function() {
				btn.slideDown('fast');
			});
		});

		el.delegate('.save-comment-trigger', 'click', function(ev) {
			var row = $(this).closest('article.task');
			var commentTxt = $('textarea', row);

			var closefn = function() {
				commentTxt.val('');
				$('.comment-btn', row).show();
				$('.comment-input', row).hide();
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
					console.log(data);
					closefn();
					var list = $('ul.task-comment-list', row);
					$(data.comment_li_html).appendTo(list);
					list.show();
				}
			});
		});

		el.delegate('.task-group header', 'click', function() {
			$(this).parent().toggleClass('collapsed');
		});
	}
});
