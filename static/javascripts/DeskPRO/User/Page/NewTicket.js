Orb.createNamespace('DeskPRO.User.Page');

/**
 * NewTicket functionality
 */
DeskPRO.User.Page.NewTicket = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			autoRun: false
		};

		if (options) this.setOptions(options);
	},

	initPage: function() {
		this._initFormPropReferences();
		this._initChangeListeners();

		if (this.autoRun) {
			this.updateDisplayedFields();
		}
	},

	_initFormPropReferences: function() {
		this.formProps = {};
		this.formProps.department_id = $('select.department_id:first');
		this.formProps.category_id   = $('select.category_id:first');
		this.formProps.product_id    = $('select.product_id:first');
		this.formProps.priority_id   = $('select.priority_id:first');

		// Some options are hidden depending on dep,
		// there is no "display:none" for option tags, only way is to
		// remove them, so we need a copy of the original so we can always put them back on switch
		this.formProps.category_id.data('originalOptions', $('option', this.formProps.category_id).clone(false));
	},

	_initChangeListeners: function() {

		var coll = $([
			this.formProps.department_id.get(0),
			this.formProps.category_id.get(0),
			this.formProps.product_id.get(0),
			this.formProps.priority_id.get(0)
		]);

		coll.change(this.updateDisplayedFields.bind(this));
	},

	updateDisplayedFields: function() {

		var map = DeskPRO_Window.get('ticketDepToCatMap');

		var depId = this.formProps.department_id.val();
		var catId = this.formProps.category_id.val();

		var validCatIds = [];
		if (map[depId]) {
			validCatIds = map[depId];
		}

		// Restore the original list,
		this.formProps.category_id.empty();
		this.formProps.category_id.append(this.formProps.category_id.data('originalOptions'));

		// Then remove each one that we cant have
		var selThis = null;
		var hasAny = false;
		$('option', this.formProps.category_id).each(function(index) {
			var id = $(this).val();
			if (id == '') return;

			if (!validCatIds.contains(id)) {
				$(this).remove();
			} else {
				hasAny = true;
				if (id == catId) {
					selThis = $(this);
				}
			}
		});

		if (hasAny) {
			if (!selThis) {
				selThis = $('option:first', this.formProps.category_id);
			}
			selThis.attr('selected', 'selected');
			$('#category_select_wrap').show();
		} else {
			$('#category_select_wrap').hide();
		}

		// We have to run rules to check custom fields now
		var ticketInfo = {
			department_id: depId,
			category_id: catId,
			product_id: this.formProps.product_id.val(),
			priority_id: this.formProps.priority_id.val(),
			workflow_id: 0
		};

		var all_ticket_display = DeskPRO_Window.get('ticketDisplay');
		var display_elements = [];
		if (all_ticket_display && all_ticket_display[depId]) {
			display_elements = all_ticket_display[depId];
		}

		var show = [];
		Array.each(display_elements, function(info) {

			var pass = info.check(ticketInfo);
			var state = info.initial_state;
			if (pass) {
				if (state == 'hidden') state = 'visible';
				else state = 'hidden';
			}

			if (state == 'visible') {
				show.push('.' + info.element_type + '-' + info.element_id);
			}
		});

		var displayElements = $('.ticket-display-element').hide();
		displayElements.filter(show.join(', ')).show();
		console.log(show);
	}
});