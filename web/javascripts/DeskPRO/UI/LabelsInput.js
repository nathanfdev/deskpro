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
				Object.each(window.DESKPRO_DATA_REGISTRY.labels, function(types, label) {
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
			self.fireEvent('change', self.getLabels());
		});

		function markMatch(text, term, markup) {
			if (typeof text != 'string') {
				return '';
			}

			var match=text.toUpperCase().indexOf(term.toUpperCase()),
				tl=term.length;

			if (match<0) {
				markup.push(text);
				return;
			}

			markup.push(text.substring(0, match));
			markup.push("<span class='select2-match'>");
			markup.push(text.substring(match, match + tl));
			markup.push("</span>");
			markup.push(text.substring(match + tl, text.length));
		}

		DP.select(this.input, {
			tags: tagSource,
			id: function (e) { if (!e) return null; return e.id; },
			formatResult: function(result, container, query) {
				var markup=[];
				markMatch(result.text, query.term, markup);
				return markup.join("");
			},
			matcher: function(term, text) {
				if (typeof text != 'string') {
					return false;
				}

				return text.toUpperCase().indexOf(term.toUpperCase()) >= 0;
			}
		});

		this.input.select2('container').on('click', '.select2-search-choice', function(ev) {
			if (ev.target && $(ev.target).is('.select2-search-choice-close')) {
				return;
			}

			ev.preventDefault();
			ev.stopPropagation();

			var label = $(this).text().trim();
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
		return this.input.select2('val') || [];
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
		Array.each(tags, function(x) {
			postData.push({
				name: field + '[]',
				value: x
			});
			if (DeskPRO.UI.LabelsInput_Grouped[this.options.type].indexOf(x) === -1) {
				DeskPRO.UI.LabelsInput_Grouped[this.options.type].push(x);
			}
		}, this);

		return postData;
	}
});
