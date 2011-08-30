Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.SelectionBar = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.page = page;

		this.options = {
			selectionBar: null,
			selectedCount: null,
			button: null
		};
		this.setOptions(options);

		if (!this.options.selectionBar) {
			this.options.selectionBar = $('.selection-bar:first', this.page.wrapper);
		}
		this.selectionBar = $(this.options.selectionBar);

		if (!this.options.selectedCount) {
			this.options.selectedCount = $('.selected-count:first', this.selectionBar);
		}
		this.selectedCount = $(this.options.selectedCount);

		if (!this.options.button) {
			this.options.button = $('button.perform-actions-trigger:first', this.selectionBar);
		}
		this.button = $(this.options.button);
		this.button.addClass('disabled');
		this.button.click(this.buttonClicked.bind(this));

		this.controlCheck = $('.selection-control', this.page.wrapper).click(function() {
			if ($(this).is(':checked')) {
				self.checkAll();
			} else {
				self.checkNone();
			}
		});

		this.page.wrapper.delegate('input.item-select', 'click', function() {
			var el = $(this);
			self.handleCheckChange(el, el.is(':checked'));
		});
	},

	buttonClicked: function() {
		if (this.button.is('.disabled')) {
			return;
		}

		this.fireEvent('buttonClick');
	},

	getCheckedValues: function() {
		var values = [];

		$('input.item-select:checked', this.page.wrapper).each(function() {
			values.push($(this).val());
		});

		return values;
	},

	getCheckedFormValues: function (form_name, appendArray) {
		appendArray = appendArray || [];

		$('input.item-select:checked', this.page.wrapper).each(function() {
			appendArray.push({
				name: form_name,
				value: $(this).val()
			});
		});

		return appendArray;
	},

	getChecked: function() {
		return $('input.item-select:checked', this.page.wrapper);
	},

	getCount: function() {
		return $('input.item-select:checked', this.page.wrapper).length;
	},

	checkAll: function() {
		$('input.item-select', this.page.wrapper).attr('checked', true);

		var count = this.getCount();
		this.selectedCount.text(count);

		if (count > 0) {
			this.button.removeClass('disabled');
		}
	},

	checkNone: function() {
		$('input.item-select:checked', this.page.wrapper).attr('checked', false);

		var count = this.getCount();
		this.selectedCount.text(count);

		this.button.addClass('disabled');
	},

	handleCheckChange: function(el, is_checked) {
		var count = this.getCount();

		this.selectedCount.text(count);
		if (count > 0) {
			this.button.removeClass('disabled');
		} else {
			this.button.addClass('disabled');
		}


		if ($('input.item-select:not(:checked):first', this.page.wrapper).length) {
			this.controlCheck.attr('checked', false);
		} else {
			this.controlCheck.attr('checked', true);
		}

		this.fireEvent('checkChange', [el, is_checked, count]);
	}
});
