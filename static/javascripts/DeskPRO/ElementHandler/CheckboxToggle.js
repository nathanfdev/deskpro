Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.ElementHandler.CheckboxToggle = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;
		var targets = $(this.el.data('targets'));

		if (!targets.length) return;

		var checkFn = function() {
			if (self.el.is(':checked')) {
				targets.show();
			} else {
				targets.hide();
			}
		}

		this.el.on('click', function() {
			checkFn();
		});

		checkFn();
	}
});
