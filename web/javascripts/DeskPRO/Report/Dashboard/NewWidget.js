Orb.createNamespace('DeskPRO.Report.Dashboard');

DeskPRO.Report.Dashboard.NewWidget = new Orb.Class({
	initialize: function(dashboard) {

		this.dashboard = dashboard;
		this.dashboard_id = dashboard.dashboard_id;

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
		this.loadChooseType();
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
	//# Choose Type
	//##################################################################################################################

	loadChooseType: function() {
		var self = this;
		$.ajax({
			url: BASE_URL + '/reports/trends/dashboards/'+this.dashboard_id+'/widget/new',
			dataType: 'html',
			success: function(html) {
				self.setContent(html);
				self._initChooseType();
			}
		});
	},

	_initChooseType: function() {
		var self = this;
		var form = this.contentEl.find('form');

		form.on('submit', function(ev) {
			ev.preventDefault();

			var selection = form.find('input[name="concept_class"]:checked').val();
			self.loadEditWidget(selection);
		});
	},

	//##################################################################################################################
	//# Load Form
	//##################################################################################################################

	loadEditWidget: function(type) {
		var self = this;

		this.showLoading();

		$.ajax({
			url: BASE_URL + '/reports/trends/dashboards/' + this.dashboard_id + '/new-stat/' + type,
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