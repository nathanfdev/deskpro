Orb.createNamespace('DeskPRO.Report.PageHandler');

DeskPRO.Report.PageHandler.ReportBuilder = new Orb.Class({
	Extends: DeskPRO.Report.PageHandler.Basic,

	initialize: function() {
	},

	// Init the page
	initPage: function() {
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
				if (data.match(/<!--dp:report-page-body-->([\s\S]*)<!--\/dp:report-page-body-->/)) {
					pageBody.html(RegExp.$1);
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

});
