Orb.createNamespace('DeskPRO.Report.Dashboard');

DeskPRO.Report.Dashboard.EditWidget = new Orb.Class({
	initialize: function(dashboard, widget_id) {

		this.dashboard = dashboard;
		this.dashboard_id = dashboard.dashboard_id;
		this.widget_id = widget_id;

		var self = this;
		var html = '';
		html += '<div>';
		html += '	<div class="overlay-title">';
		html += '		<span class="close-overlay close-trigger"></span>';
		html += '		<h4>Add A Stat</h4>';
		html += '	</div>';
		html += '	<div class="overlay-content">';
		html += '		<div class="loading"><div class="loading-icon-big with-message">Loading</div></div>';
		html += '		<div class="content" style="display:none"></div>';
		html += '	</div>';
		html += '</div>';

		var el = $(html);
		this.contentEl = el.find('.content');
		this.loadingEl = el.find('.loading');
		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: el,
			fullScreen: true,
			fullScreenMargin: '50px',
			destroyOnClose: true,
			onDestroyed: function() {
				self.destroy();
			}
		});

		this.overlay.open();
		this.loadEditWidget();
	},

	destroy: function() {

	},

	showLoading: function(noClear) {
		this.contentEl.hide();
		if (!noClear) {
			this.contentEl.empty();
		}
		this.loadingEl.show();
	},

	setContent: function(html) {
		this.loadingEl.hide();
		this.contentEl.html(html).show();
	},

	//##################################################################################################################
	//# Load Form
	//##################################################################################################################

	loadEditWidget: function() {
		var self = this;

		this.showLoading();

		$.ajax({
			url: DeskPRO_Window.getUrl('report_trend_dashboard_stat_edit', {dashboard_id: this.dashboard.dashboard_id, dashboard_stat_id: this.widget_id}),
			dataType: 'json',
			success: function(data) {
				self.setContent(data.html);
				self._initEditWidget();
			}
		});
	},

	_initEditWidget: function() {
		var self     = this;
		var critTpl  = this.contentEl.find('.search-builder-tpl');
		var critList = this.contentEl.find('.criteria_list');

		var editor = new DeskPRO.Form.RuleBuilder(critTpl);
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		$('.add-term', critList).data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('.search-terms', critList), basename);
		});

		var jsonEl = this.contentEl.find('script.set_terms_json');
		if (jsonEl[0]) {
			var json = jsonEl.get(0).innerHTML;
			var terms = $.parseJSON(json);

			if (terms && terms.length) {
				Array.each(terms, function(info, x) {
					var basename = 'terms[initial_' + x + ']';
					editor.addNewRow($('.search-form .search-terms', self.contentEl), basename, {
						type: info.type,
						options: info.options
					});
				});
			}
		}

		this.contentEl.find('form').on('submit', function(ev) {
			ev.preventDefault();
			var postData = $(this).serializeArray();

			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: postData,
				success: function() {
					window.location.reload(true);
				}
			});
		});
	}
});