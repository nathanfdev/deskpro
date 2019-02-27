Orb.createNamespace('DeskPRO.UI');

/**
 * The registry contains a list of labels with the types
 * they apply to, so when a new autocomplete source is set up
 * we need the actual array list for a particular type.
 * This is the pre-computed lists.
 */
DeskPRO.UI.LabelsInput_Grouped = {};

DeskPRO.UI.LabelsInput = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		var self = this;
		this.options = {
			/**
			 * The labels select
			 */
			input: null,

			/**
			 * The field name the labels should be added (ie labels[])
			 */
			fieldName: 'labels',

			/**
			 * The label type. This is used with autocomplete.
			 * Values: tickets, people, articles, downloads, feedback, news
			 */
			type: '',

			placeholder: false
		};

		this.setOptions(options);

		this.input = $(this.options.input);

		var tagSource = false;

		if (this.options.type) {
			if (DeskPRO.UI.LabelsInput_Grouped[this.options.type]) {
				tagSource = DeskPRO.UI.LabelsInput_Grouped[this.options.type];
			} else if (window.DESKPRO_DATA_REGISTRY && window.DESKPRO_DATA_REGISTRY.labels) {
				tagSource = [];
				Object.entries(window.DESKPRO_DATA_REGISTRY.labels).forEach(function(_vk) { var label = _vk[0], types = _vk[1];
					if (types.indexOf(this.options.type) != -1) {
						tagSource.push(label);
					}
				}, this);

				DeskPRO.UI.LabelsInput_Grouped[this.options.type] = tagSource;
			}

			if (!tagSource) {
				console.warn('No type %s', this.options.type);
			}
		}

		if (!tagSource) tagSource = [];

		this.input.on('change', function() {
			self.fireEvent('change', [self.getLabels()]);
		});

		var allowNew = !(this.input[0] && 'SELECT' !== this.input.tagName && 1 !== this.input.data('allow-new'));

		self.$scope = DeskPRO_Window.$scope.$new();

		DP.select(this.input, {
			tags: tagSource,
			multiple: true,
			id: function (e) { if (!e) return null; return e.id; },
			formatResult: function(result, container, query) {
				if (!result || !result.text) {
					return '';
				}
				return Orb.escapeHtml(result.text);
			},
			matcher: function(term, text) {
				if (typeOf(text)  != 'string' || typeOf(term) != 'string') {
					return;
				}

				return text.toUpperCase().indexOf(term.toUpperCase()) >= 0;
			},
			formatSelection: function(data, container) {
				var name = Orb.escapeHtml(data.text);

				container.parent().attr('dp-label', "");
				container.parent().attr('dp-label-string', name);
				container.parent().attr('dp-label-type', self.options.type);
				window.AppPlatform.getNgInjector().invoke(['$compile', function($compile) {
					$compile(container.parent())(self.$scope);
				}]);
				return name;
			},
			formatNoMatches: function() {
				if (allowNew) {
					return options.placeholder || self.input.data('placeholder-new') || 'Press enter to create a new label.';
				} else {
					return 'You are not allowed to create new labels. Please use an existing label.'
				}
			}
		});
	},

	/**
	 * Get the labels currently added to the list
	 *
	 * @return {Array}
	 */
	getLabels: function() {
		return this.input && this.input.select2('val') || [];
	},

  setLabels: function (labels) {
    labels = labels || [];
    this.input && this.input.select2('val', labels);
  },

	/**
	 * Get labels serialized as a form array suitable with jQuery.ajax
	 *
	 * @return {Array}
	 */
	getFormData: function() {
		var tags = this.getLabels();
		var field = this.options.fieldName;

		var postData = [];
		tags.forEach(function(x) {
			postData.push({
				name: field + '[]',
				value: x
			});

			// Make sure the group exists
			if (!DeskPRO.UI.LabelsInput_Grouped[this.options.type]) {
				DeskPRO.UI.LabelsInput_Grouped[this.options.type] = [];
			}
			// Add the label to the group if its new
			if (DeskPRO.UI.LabelsInput_Grouped[this.options.type].indexOf(x) === -1) {
				DeskPRO.UI.LabelsInput_Grouped[this.options.type].push(x);
			}
		}, this);

		return postData;
	},

	destroy: function() {
    this.$scope && this.$scope.$destroy();
    this.$scope = null;
		this.input.off();
    this.input.select2 && this.input.select2('destroy');
		this.input = null;
    this.options.input = null;
    this.options.onChange = null;
		this.options = null;
		this.destroyEvents();
	}
});
