// Written by DeskPRO
// http://www.deskpro.com/
(function($) {
	$.fn.TextAreaExpander = function (settings) {

		var config = {
			classmodifier: "tae"
		};

		if (settings) {
			$.extend(config, settings);
		}

		function update(element) {
			var $elem  = $(element);
			var height = $elem.height();
			var max    = $elem.data('expander-max-height') || 1000;
			var min    = $elem.data('expander-min-height') || 50;

			while (element.clientHeight < element.scrollHeight) {
				height += 5;
				$elem.height(height);
				if (height > max) {
					break;
				}
			}

			while (element.clientHeight >= element.scrollHeight) {
				last = height;
				height -= 5;
				$elem.height(height);
				if (height < min) {
					break;
				}
			}
			$elem.height(last);
		}

		return this.each(function () {
			$(this).addClass(config.classmodifier).css({ overflow: "hidden" });
			if (!$(this).data('expander-min-height')) {
				$(this).data('expander-min-height', $(this).height());
			}
			$(this).bind("keyup", function () {
				update(this);
				$(this).trigger('textareaexpander_expanded');
			});
		});
	};
})(jQuery);