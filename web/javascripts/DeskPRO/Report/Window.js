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
			var initialize = function(context) {
				context.find('textarea.expander').TextAreaExpander().trigger('textareaexpander_fire');
				context.find('select.readonly option:not(:selected)').attr('disabled', true);
			};
			initialize($(document));

			$(document.body).delegate('a.report-favorite-toggle', 'click', function(e) {
				var $this = $(this), isFavorite = $this.hasClass('favorited'),
					newValue = isFavorite ? 0 : 1,
					url = $this.attr('href'),
					matches = $('a.report-favorite-toggle[data-report-id="' + $this.data('report-id') + '"]');

				matches.toggleClass('favorited');

				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'html',
					data: { favorite: newValue }
				}).done(function(data) {
					var favoriteContainer = $('#report-favorites');
					favoriteContainer.find('ul:first').replaceWith(data);
					if (favoriteContainer.find('li').length) {
						favoriteContainer.show();
					} else {
						favoriteContainer.hide();
					}
				});

				e.preventDefault();
			});

			$(document.body).delegate('.report-editor-controls-show', 'click', function(e) {
				var $this = $(this);

				e.preventDefault();
				$($this.data('target')).show();
				$this.hide();
			});

			var pageBody = $('#report-page-body'), initialized = false;

			$.history.init(function(hash) {
				if (hash == '' && !initialized) {
					initialized = true;
					return;
				}

				var loadingBlock = $('#report-loading-block'),
					left = pageBody.offset().left + pageBody.outerWidth() / 2 - loadingBlock.outerWidth() / 2;

				loadingBlock.appendTo(document.body).css('left', left + 'px').show();

				var failure = function() {
					pageBody.html($('#report-failed-block').html());
				};

				$.scrollTo(document.body, 200);

				$.ajax({
					url: hash || window.location.pathname + window.location.search,
					type: 'GET',
					dataType: 'html'
				}).done(function(data) {
					var body = $(data).find('#report-page-body');
					if (body.length) {
						pageBody.html($(data).find('#report-page-body').html());
						DeskPRO.ElementHandler_Exec();
						initialize(pageBody);
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
