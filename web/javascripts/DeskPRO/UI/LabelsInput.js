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
			 * The labels textareato apply to
			 */
			textarea: null,

			/**
			 * The field name the labels should be added (ie labels[])
			 */
			fieldName: 'labels',

			/**
			 * The label type. This is used with autocomplete.
			 * Values: tickets, people, articles, downloads, feedback, news
			 */
			type: '',

			showMax: 50,

			placeholder: false,
		};

		this.setOptions(options);

		var tagSource = false;

		if (this.options.type) {
			if (DeskPRO.UI.LabelsInput_Grouped[this.options.type]) {
				tagSource = DeskPRO.UI.LabelsInput_Grouped[this.options.type];
			} else if (window.DESKPRO_DATA_REGISTRY && window.DESKPRO_DATA_REGISTRY.labels) {
				tagSource = [];
				Object.each(window.DESKPRO_DATA_REGISTRY.labels, function(types, label) {
					if (types.indexOf(this.options.type) != -1) {
						tagSource.push(label);
					}
				}, this);

				DeskPRO.UI.LabelsInput_Grouped[this.options.type] = tagSource;
			}
		}

		if (!tagSource) tagSource = [];

		var exist = [];
		var val = this.options.textarea.val().trim();
		this.options.textarea.val('');
		Array.each(val.split(','), function(t) {
			t = t.trim();
			if (t.length) {
				exist.push(t);
			}
		});

		// TODO
		// Keep an eye on https://github.com/alexgorbatchev/jquery-textext/issues
		// - 'filter' plugin causes tags.items not to render properly
		// - Cant click 'autocomplete' items to enter them

		this.options.textarea.textext({
			plugins: 'autocomplete suggestions tags prompt',
			suggestions: tagSource,
			prompt: this.options.placeholder || 'Add a label...',
			tags: {
				items: exist
			}
		});

		self.data = exist;

		var last = (this.options.textarea.textext()[0]).hiddenInput().val();
		this.options.textarea.bind('setFormData', function(e, data, isEmpty) {
			var me = this;
			var textext = $(e.target).textext()[0];
			var str = textext.hiddenInput().val();
			if (str != last) {
				last = str;
				self.data = data;
				self.fireEvent('change', data);
			}
		});

		// Clicking a label opens the omni search boxeroo
		this.options.textarea.textext()[0].hiddenInput().closest('.text-core').on('click', '.text-tag', function(ev) {
			if (ev.target && $(ev.target).is('.text-remove')) {
				return;
			}

			ev.preventDefault();
			ev.stopPropagation();

			var label = $(this).find('.text-label').text().trim();
			if (label) {
				$('#dp_omniinput').data('handler').setSearch('[' + label + ']');
			}
		});
	},


	/**
	 * Get the labels currently added to the list
	 *
	 * @return {Array}
	 */
	getLabels: function() {
		return this.data;
	},


	/**
	 * Get labels serialized as a form array suitable with jQuery.ajax
	 *
	 * @return {Array}
	 */
	getFormData: function() {
		var tags = this.data;
		var field = this.options.fieldName;

		var postData = [];
		Array.each(tags, function(x) {
			postData.push({
				name: field + '[]',
				value: x
			});
		});

		return postData;
	}
});
