Orb.createNamespace('DeskPRO.Report.PageHandler');

DeskPRO.Report.PageHandler.ReportBuilder = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function() {
	},

	// Init the page
	initPage: function() {
		var self = this;

		var initialize = function(context) {
			context.find('textarea.expander').TextAreaExpander().trigger('textareaexpander_fire');
			context.find('select.readonly option:not(:selected)').attr('disabled', true);
		};
		initialize($(document));

		$(document.body).delegate('a.report-favorite-toggle', 'click', function(e) {
			var $this = $(this), isFavorite = $this.hasClass('favorited'),
				newValue = isFavorite ? 0 : 1,
				url = $this.attr('href'),
				params = $this.data('report-params') || '',
				matches = $('a.report-favorite-toggle[data-report-id="' + $this.data('report-id') + '"]');

			matches.each(function() {
				var $this = $(this);

				if (($this.data('report-params') || '') === params) {
					$this.toggleClass('favorited');
				}
			});

			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'html',
				data: { favorite: newValue, params: params }
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
				if (data.match(/<!--dp:report-page-body-->([\s\S]*)<!--\/dp:report-page-body-->/)) {
					pageBody.html(RegExp.$1);
					DeskPRO.ElementHandler_Exec();
					initialize(pageBody);
					self.updateFavorites();
				} else {
					failure();
				}
			}).fail(failure).always(function() {
				loadingBlock.hide();
			});
		}, {unescape: '/'});

		$(document.body).delegate('a[rel=report-page-body]', 'click', function(e) {
			var $this = $(this), href = $this.data('report-original-href') || $this.attr('href');

			e.preventDefault();

			if ($this.is('.report-list-title')) {
				var data = self.updateReportParams($this);
				if (data) {
					href += (href.indexOf('?') >= 0 ? '&' : '?') + 'params=' + encodeURIComponent(data);
				}
			}

			$.history.load(href);
		});
	},

	updateReportParams: function(item) {
		var params = {}, keys = [], value;

		item.find('.report-list-query-selector').each(function (){
			var $selector = $(this),
				id = 0,
				selected = $selector.data('report-list-query-selected');

			if ($selector.data('report-list-query').match(/^(\d+):/)) {
				id = RegExp.$1;
			} else {
				return;
			}

			params[id] = selected;
			keys.push(id);
		});

		if (keys.length) {
			keys.sort();
			var out = [];
			for (var i = 0; i < keys.length; i++) {
				out.push(params[keys[i]]);
			}

			value = out.join(',');
		} else {
			value = '';
		}

		item.data('report-params', value);
		item.siblings('.report-favorite-toggle').data('report-params', value);

		if (item.is('.report-list-title')) {
			var href = item.data('report-original-href');
			if (!href) {
				href = item.attr('href');
				item.data('report-original-href', href);
			}
			if (value) {
				href += (href.indexOf('?') >= 0 ? '&' : '?') + 'params=' + encodeURIComponent(value);
			}
			item.attr('href', href);
		}

		return value;
	},

	updateFavorites: function(update) {
		if ($.isArray(update)) {
			this.favorites = update;
		}

		if (!this.favorites) {
			this.favorites = [];
		}

		var favorites = this.favorites;

		$('a.report-favorite-toggle').each(function() {
			var $this = $(this),
				reportId = $this.data('report-id'),
				params = $this.data('report-params') || '';

			for (var i = 0; i < favorites.length; i++) {
				if (favorites[i].id == reportId && params === favorites[i].params) {
					$this.addClass('favorited');
					return;
				}
			}

			$this.removeClass('favorited');
		});
	}

});
