Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.RadioExpander = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		var groupClass  = this.el.data('group-class');
		var expandClass = this.el.data('expand-class');
		var radios = $('.option-trigger', this.el);

		var currentGroup = null;

		function switchtoradio(radio) {
			self.el.find('.' + groupClass + '.on').removeClass('on');

			if (currentGroup) {
				$('.' + expandClass, currentGroup).hide();
			}

			var group = radio.closest('.' + groupClass).addClass('on');
			$('.' + expandClass, group).show();
			currentGroup = group;
		}

		$(':radio.option-trigger:checked', this.el).each(function() {
			switchtoradio($(this));
		});

		this.el.on('click', ':radio', function() {
			switchtoradio($(this));
		});

		this.el.on('click', '.' + groupClass + ':not(.on)', function() {
			var radio = $(this).find('.option-trigger');
			if (radio.length) {
				radio.prop('checked', 'checked');
				switchtoradio(radio);
			}
		});
	}
});
