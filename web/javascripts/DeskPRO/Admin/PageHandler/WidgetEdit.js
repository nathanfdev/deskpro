Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.WidgetEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		var textAreas = $('textarea.expander');

		textAreas.TextAreaExpander().trigger('textareaexpander_fire');

		this.simpleTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#widget-tabs > li')
		});
		this.simpleTabs.addEvent('tabSwitch', function(e) {
			textAreas.trigger('textareaexpander_fire');
			this.getActiveTabContent().find('textarea:first').focus();
		});

		var pageSelect = $('#page-select'),
			pageLocations = $('#page-location-select');

		var pageChange = function() {
			var page = pageSelect.val(), haveSelected = false, firstVisible;
			pageLocations.find('option').each(function() {
				var $this = $(this);
				if ($this.data('page') == page) {
					$this.show();
					if (!firstVisible) {
						firstVisible = $this;
					}
					if ($this.is(':selected')) {
						haveSelected = true;
					}
				} else {
					$this.hide();
				}
			});

			if (!haveSelected && firstVisible) {
				pageLocations.val(firstVisible.val());
			}
		};

		pageLocations.width(pageLocations.outerWidth());
		pageSelect.change(pageChange);
		pageChange();
	}
});
