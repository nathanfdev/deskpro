Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

/**
 * These standard options are simple values taken from a menu. Since
 * they're so similar, this single property class can handle all of them.
 */
DeskPRO.Agent.Ticket.Property.StandardOption = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	init: function() {
		this.optionName = null;
		this.displayNameType = 'standardOption';
		this._formEl = null;

    var valid_options = ['language_id', 'category_id', 'product_id', 'priority_id', 'workflow_id', 'problem_id', 'create_problem'];

		if (valid_options.indexOf(this.options.optionName) == -1) {
			throw 'invalidOptionName:'+this.options.optionName;
		}

		this.optionName = this.options.optionName;

		switch (this.optionName) {
			case 'language_id': this.displayNameType = 'language'; this.displayCaption = 'Language'; break;
			case 'category_id': this.displayNameType = 'ticket_category_full'; this.displayCaption = 'Category'; break;
			case 'product_id': this.displayNameType = 'product'; this.displayCaption = 'Product'; break;
			case 'priority_id': this.displayNameType = 'ticket_priority'; this.displayCaption = 'Priority'; break;
			case 'workflow_id': this.displayNameType = 'ticket_workflow'; this.displayCaption = 'Workflow'; break;
      case 'problem_id':
        this.displayCaption = 'Problem';
        break;
      case 'create_problem':
        this.displayCaption = 'Create Problem';
        break;
		}
	},


	getName: function() {
		return this.optionName;
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value, data) {
		this.getFormEl().val(value);

		if (value == "0") value = 0;

		// They are the same value,
		// dont try and trigger changes
		if (parseInt(value) == parseInt(this.ticketPage.getEl('value_form').find('.' + this.optionName).val())) {
			return;
		}

		// some elements (agent) have pictures associated with them
		var el = this.getInterfaceElement();
		var fieldEl = $('.prop-input-' + this.optionName, this.ticketPage.wrapper);
		var pictureEl = null;
		if (el.data('picture-element')) {
			pictureEl = $(el.data('picture-element'), el.parent());
			if (!pictureEl.length) {
				pictureEl = $(el.data('picture-element'), el.parent.parent());
				if (!pictureEl.length) {
					pictureEl = null;
				}
			}
		}

		if (value) {
			var displayName = value;
			if (this.displayNameType) {
				displayName = DeskPRO_Window.getDisplayName(this.displayNameType, value);

				if (!displayName && fieldEl.is('select')) {
					displayName = fieldEl.find('option[value="' + value + '"]').text();
				}

				if (!displayName) displayName = value;
			}

			el.removeClass('no-value').html(displayName);

			if (pictureEl) {
				pictureEl.removeClass('no-value').show().attr('src', pictureEl.data('picture-url').replace('{value}', value));
			}

		} else {
			if ('problem_id' === this.optionName) {
				el.siblings().remove();
			}
			el.addClass('no-value').html(el.data('no-value-label') || 'None');

			if (pictureEl) {
				pictureEl.addClass('no-value').hide();
			}
		}



		if (fieldEl.hasClass('with-select2')) {
			fieldEl.select2('val', value);
		} else {
			fieldEl.val(value);
		}

		this.ticketPage.getEl('value_form').find('.' + this.optionName).val(value).trigger('change');
	},

	getInterfaceElement: function() {
		return $('.prop-val.'+this.optionName, this.ticketPage.wrapper).first();
	},

	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('[name="ticket['+this.optionName+']"]', this.ticketPage.wrapper).first();

		return this._formEl;
	}
});
