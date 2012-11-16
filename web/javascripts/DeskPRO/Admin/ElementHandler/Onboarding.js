Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.Onboarding = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.initTasks();
		this.initAsk();
	},

	initTasks: function() {
		var onboard_box = this.el;
		var openArticle = null;

		onboard_box.on('click', 'button.btn-dismiss, button.btn-done', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');

			if ($(this).hasClass('btn-done')) {
				var type = 'done';
			} else {
				var type = 'dismiss';
			}

			var id = row.data('task-id');

			$.ajax({
				url: BASE_URL + 'admin/onboard-mark-complete/'+type+'/'+id+'.json',
				type: 'POST'
			});

			if (row.hasClass('expanded')) {
				row.removeClass('expanded');
				openArticle = null;
			}

			row.find('article').slideUp('fast', function() {
				if (type == 'done') {
					row.addClass('finished');
				} else {
					row.addClass('dismissed');
				}
			});
		});

		onboard_box.on('click', 'header', function(ev) {
			var row = $(this).closest('li');
			if (!row.data('task-id')) {
				return;
			}

			$('#onboard_question').removeClass('expanded').find('article').hide();

			ev.preventDefault();
			ev.stopPropagation();

			var togglingSelf = false;
			var article = row.find('article').first();

			if (openArticle) {
				// Toggling self
				if (openArticle.get(0) == article.get(0)) {
					togglingSelf = true;
				}

				openArticle.slideUp('fast');
				openArticle.closest('li').removeClass('expanded');
				openArticle = null;

				if (togglingSelf) {
					return;
				}
			}

			row.addClass('expanded');
			article.slideDown('fast');
			openArticle = article;
		});
	},

	initAsk: function() {
		var sent = [];

		var row   = $('#onboard_question');
		var input = row.find('input.input-question');
		var btn   = row.find('button.submit-trigger');

		btn.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var text = input.val().trim();
			var dupecheck = text.toLowerCase().replace(/\s+/g, '');
			if (!text) {
				return;
			}

			if (sent.indexOf(dupecheck) !== -1) {
				row.addClass('expanded');
				row.find('article').slideDown('fast');
				return;
			}

			sent.push(dupecheck);

			row.removeClass('expanded');
			row.find('article').hide();

			btn.find('em').addClass('flat-spinner');
			$.ajax({
				url: row.data('submit-url'),
				type: 'POST',
				data: {
					message: text
				},
				complete: function() {
					btn.find('em').removeClass('flat-spinner');
				},
				success: function() {
					btn.find('em').removeClass('flat-spinner');
					row.addClass('expanded');
					row.find('article').slideDown('fast');
				}
			});
		});
	}
});