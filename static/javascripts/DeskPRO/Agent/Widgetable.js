Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.Widgetable = new Class({
	widgets: [],
	initWidgets: function(widgetInfos, commonOptions) {

		commonOptions = commonOptions || {};

		Array.each(widgetInfos, function(info) {

			if (!info || typeOf(info) != 'object') return;

			var widget_class = Orb.getNamespacedObject(info.class);
			var options = Object.merge(commonOptions, { __wrapperSelector: info.wrapperSelector }, info.options||{});

			var widget = new widget_class(options);
			if (typeOf(info.prefs) == 'object') {
				widget.userPrefs = info.prefs;
			}

			this.widgets.push(widget);
		}, this);
	},

	initWidgetsDom: function(container) {
		var container = container || document.body;
		container = $(container);

		Array.each(this.widgets, function(widget) {
			var el = $(widget.options.__wrapperSelector, container);
			if (!el.length) {
				console.warn('Widget has no element wrapper: %o', widget);
				return;
			}

			widget.setWidgetElement(el);
		});

		if (this.fireEvent) {
			this.fireEvent('allWidgetsReady');
		}
	},

	getWidgets: function() {
		return this.widgets;
	}
});