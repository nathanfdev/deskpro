com_deskpro_example = new Class({
	Extends: DeskPRO.Widget,

	init: function() {
		console.log('Widget init');
	},

	initWidget: function() {
		console.log('Widget initWidget');

		this.getWidgetElement().css({ 'background-color': 'yellow'});
	}
});