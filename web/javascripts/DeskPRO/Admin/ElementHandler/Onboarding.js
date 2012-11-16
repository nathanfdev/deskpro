Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.Onboarding = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
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
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
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
	}
});