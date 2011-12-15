Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.RadioExpander = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		var groupClass  = this.el.data('group-class');
		var expandClass = this.el.data('expand-class');
		var radios = $('.option-trigger', this.el);

		var currentGroup = null;

		$(':radio.option-trigger:checked', this.el).each(function() {
			var group = $(this).closest('.' + groupClass);
			$('.' + expandClass, group).show();

			currentGroup = group;
		});

		this.el.on('click', ':radio', function() {

			if (currentGroup) {
				$('.' + expandClass, currentGroup).hide();
			}

			var group = $(this).closest('.' + groupClass);
			$('.' + expandClass, group).show();
			currentGroup = group;
		});
	}
});
