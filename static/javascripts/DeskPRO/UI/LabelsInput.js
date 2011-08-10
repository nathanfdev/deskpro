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
		this.options = {
			/**
			 * The labels list to apply to
			 */
			list: null,

			/**
			 * The field name the labels should be added (ie labels[])
			 */
			fieldName: 'labels',

			/**
			 * The label type. This is used with autocomplete.
			 * Values: tickets, people, articles, downloads, ideas, news
			 */
			type: '',

			showMax: 50
		};

		this.setOptions(options);

		var tagitOptions = {
			enableBackspace: false,
			fieldName: this.options.labels,
			onchange: (function() {
				this.fireEvent('change');
			}).bind(this)
		};

		var tagSource = false;
		
		if (DeskPRO.UI.LabelsInput_Grouped[this.options.type]) {
			tagSource = DeskPRO.UI.LabelsInput_Grouped[this.options.type];
		} else if (window.DESKPRO_DATA_REGISTRY.labels) {
			tagSource = [];
			Object.each(window.DESKPRO_DATA_REGISTRY.labels, function(types, label) {
				if (types.indexOf(this.options.type) != -1) {
					tagSource.push(label);
				}
			}, this);

			DeskPRO.UI.LabelsInput_Grouped[this.options.type] = tagSource;
		}

		// If tagSource exists, we're using local (fast) autocomplete
		if (tagSource && tagSource.length) {
			tagitOptions.autocompleteOptions = {
				source: tagSource,
				minLength: 0,
				delay: 20
			};

			tagitOptions.focusShowAutocomplete = true;

		// Otherwise, we're using AJAX (slow) autocomplete
		} else {
			tagitOptions.autocompleteOptions = {
				source: BASE_URL + '/misc/ajax-labels/' + this.options.type
			}
		}

		// Limit to max entries
		var max = this.options.showMax;
		tagitOptions.autocompleteOptions.open = (function(event, ui) {
			var el = this.tagit.getInput();
			var list = $(el.autocomplete('widget'));

			var remove_lis = $('> li', list).slice(max);
			remove_lis.remove();
		}).bind(this);

		this.tagit = $(this.options.list).tagit(tagitOptions);
	}
});