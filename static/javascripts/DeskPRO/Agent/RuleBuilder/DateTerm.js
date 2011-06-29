Orb.createNamespace('DeskPRO.Agent.RuleBuilder');

DeskPRO.Agent.RuleBuilder.DateTerm = new Orb.Class({
	Extends: DeskPRO.Agent.RuleBuilder.TermAbstract,

	initRow: function() {

		this._initUi();
	},

	initValues: function() {
		var timestamp = null, date = null;

		timestamp = this.date1Input.val();
		if (timestamp) {
			date = new Date(timestamp * 1000);
			this.date1Widget.datepicker('setDate', date);
		}

		timestamp = this.date2Input.val();
		if (timestamp) {
			date = new Date(timestamp * 1000);
			this.date2Widget.datepicker('setDate', date);
		}

		this.updateStatus();
	},

	_initUi: function() {

		//------------------------------
		// References to elements and move
		// overlay into body
		//------------------------------

		this.opInput = $('select.op', this.rowEl);

		this.date1Input = $('input.date1-input', this.rowEl);
		this.date2Input = $('input.date2-input', this.rowEl);

		this.date1Display = $('input.date1-display', this.rowEl);
		this.date2Display = $('input.date2-display', this.rowEl);

		this.currentValue = $('.status-value', this.rowEl);
		this.currentValue.text('(click to set)');
		this.currentValue.click(this.show.bind(this));

		this.dateWrap = $('.date-wrap', this.rowEl);

		this.backdrop = $('<div class="backdrop" style="display: none"></div>');
		this.backdrop.appendTo('body');
		this.backdrop.click(this.hide.bind(this));

		this.wrapper = $('<div class="field-overlay" style="display:none"><div class="close-trigger"></div></div>');
		$('.close-trigger', this.wrapper).click(this.hide.bind(this));

		this.dateWrap.detach().appendTo(this.wrapper).css('display', 'block');
		this.wrapper.appendTo('body');

		this.date1 = $('.date1', this.dateWrap);
		this.date2 = $('.date2', this.dateWrap);

		//------------------------------
		// Init date elements
		//------------------------------

		var self = this;
		this.date1Widget = $('.widget', this.date1).datepicker({
			dateFormat: 'M d, yy',
			onSelect: function(dateText, inst) {

				self.date1Input.val(self.date1Widget.datepicker('getDate').getTime() / 1000);

				self.date1Display.val(dateText);
				self.updateStatus();
			}
		});

		this.date2Widget = $('.widget', this.date2).datepicker({
			dateFormat: 'M d, yy',
			onSelect: function(dateText, inst) {

				self.date2Input.val(self.date2Widget.datepicker('getDate').getTime() / 1000);

				self.date2Display.val(dateText);
				self.updateStatus();
			}
		});

		var getDate = function (el) {
			var timestamp = strtotime(el.val());
			if (!timestamp) {
				return null;
			}

			var date = new Date(timestamp * 1000);
			return date;
		};

		//------------------------------
		// Detect changes to text fields for
		// human times "1 day ago" etc
		//------------------------------

		this.date1Display.change(function() {
			var date = getDate($(this));
			if (!date) {
				$(this).val('');
				return;
			}
			self.date1Widget.datepicker('setDate', date);
		});

		this.date2Display.change(function() {
			var date = getDate($(this));
			if (!date) {
				$(this).val('');
				return;
			}
			self.date2Widget.datepicker('setDate', date);
		});
	},

	show: function() {

		if (this.opInput.val() == 'between') {
			this.dateWrap.addClass('two');
		} else {
			this.dateWrap.removeClass('two');
		}

		this.wrapper.css({
			left: this.currentValue.offset().left,
			top: this.currentValue.offset().top
		});

		this.backdrop.show();
		this.wrapper.show();
	},

	updateStatus: function() {
		if (this.opInput.val() == 'between') {
			var date1 = this.date1Widget.datepicker('getDate');
			var date2 = this.date2Widget.datepicker('getDate');

			var str = '';
			if (date1) {
				str = $.datepicker.formatDate('M d, yy', date1);
			} else {
				str = '(click to set)';
			}

			str += ' and ';

			if (date2) {
				str += $.datepicker.formatDate('M d, yy', date2);
			} else {
				str += '(click to set)';
			}
		} else {
			var date1 = this.date1Widget.datepicker('getDate');
			var str = '';
			if (date1) {
				str = $.datepicker.formatDate('M d, yy', date1);
			} else {
				str = '(click to set)';
			}
		}

		this.currentValue.text(str);
	},

	hide: function() {
		this.backdrop.hide();
		this.wrapper.hide();
	},

	destroy: function() {
		this.wrapper.remove();
		this.backdrop.remove();
	}
});