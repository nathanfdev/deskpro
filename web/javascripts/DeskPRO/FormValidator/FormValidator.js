Orb.createNamespace('DeskPRO.Form');

DeskPRO.Form.FormValidator = new Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function(el) {
		var self = this;
		this.el = $(el);

		if (this.el.is('form')) {
			this.el.on('submit', function(ev) {
				self.validateAll();
				if (self.hasErrors()) {
					ev.preventDefault();
				}
			});

			$('[required]', this.el).each(function() {
				$(this).attr('required', false);
			});
		}

		this.refreshElements();
	},

	refreshElements: function() {
		var el = this.el;
		this.formElements = $('[data-field-validators]', this.el).each(function() {
			var names, i, validators, className, classObj;

			names = $(this).data('field-validators').split(',');

			validators = [];
			for (i = 0; i < names.length; i++) {

				if ($(this).data('field-validators-inst')) {
					Array.each($(this).data('field-validators-inst'), function(v) {
						v.destroy();
					});
					$(this).data('field-validators-inst', null);
				}

				var className = names[i].trim();
				var classObj = Orb.getNamespacedObject(className);

				if (!classObj) {
					DP.console.error("Unknown form validator `%s` on element %o", className, this);
					continue;
				}

				var obj = new classObj($(this));
				validators.push(obj);
			}

			if (validators.length) {
				$(this).data('field-validators-inst', validators);
			}
		});
	},

	validateAll: function() {
		this.el.find('.dp-error.dp-error-static').removeClass('dp-error');

		this.formElements.each(function() {

			var validators, i;

			if (!$(this).is(':visible')) {
				return;
			}

			validators = $(this).data('field-validators-inst');

			for (i = 0; i < validators.length; i++) {
				validators[i].validate('submit');
			}
		});
	},

	hasErrors: function() {
		if ($('.dp-error', this.el).filter(':visible').length > 0) {
			return true;
		}

		return false;
	}
});
