Orb.createNamespace('DeskPRO.Report');

DeskPRO.Report.Window = new Orb.Class({
	Extends: DeskPRO.Admin.Window,

	initPage: function() {
		this.parent();
		$('#dp_admin_nav ul').sortable({
			axis: 'x',
			forceHelperSize: true,
			appendTo: 'body',
			items: 'li.dashboard',
			update: function() {
				var postData = [];
				$('#dp_admin_nav').find('li.dashboard').each(function() {
					postData.push({
						name: 'dashboard_ids[]',
						value: $(this).data('dashboard-id')
					});
				});

				$.ajax({
					url: $('#dp_admin_nav ul').data('update-orders-url'),
					data: postData,
					type: 'POST',
					dataType: 'json'
				});
			},
			helper: function(ev, el) {
				var helper = $('<div class="dashboard-drag-helper"><span></span></div>');
				helper.find('span').text(el.text().trim());

				return helper;
			}
		});

		var menu = new DeskPRO.UI.Menu({
			triggerElement: '#all_trends_menu_trigger',
			menuElement: '#all_trends_menu'
		});

		if ($('#report-container').length) {
			$(document.body).delegate('a.report-favorite-toggle', 'click', function(e) {
				var $this = $(this), isFavorite = $this.hasClass('favorited'),
					newValue = isFavorite ? 0 : 1,
					url = $this.attr('href'),
					matches = $('a.report-favorite-toggle[data-report-id="' + $this.data('report-id') + '"]');

				matches.toggleClass('favorited');

				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: { favorite: newValue }
				});

				e.preventDefault();
			});

			var pageBody = $('#report-page-body');

			$.history.init(function(hash) {
				if (hash == '') {
					return;
				}

				var loadingBlock = $('#report-loading-block'),
					left = pageBody.position().left + pageBody.outerWidth() / 2 - loadingBlock.outerWidth() / 2;

				loadingBlock.css('left', left + 'px').show();

				var failure = function() {
					pageBody.html($('#report-failed-block').html());
				};

				$.ajax({
					url: hash,
					type: 'GET',
					dataType: 'html'
				}).done(function(data) {
					var body = $(data).find('#report-page-body');
					if (body.length) {
						pageBody.html($(data).find('#report-page-body').html());
						DeskPRO.ElementHandler_Exec();
					} else {
						failure();
					}
				}).fail(failure).always(function() {
					loadingBlock.hide();
				});
			}, {unescape: '/'});

			$(document.body).delegate('a[rel=report-page-body]', 'click', function(e) {
				e.preventDefault();
				$.history.load($(this).attr('href'));
			});
		}
	},

	/**
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {
		if (!window.DESKPRO_URL_REGISTRY[name]) {
			DP.console.error('Unknown url name %s', name);
			return null;
		}

		var url = window.DESKPRO_URL_REGISTRY[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	}
});
