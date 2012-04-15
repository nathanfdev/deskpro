Orb.createNamespace('DeskPRO.Agent.RuleBuilder');

DeskPRO.Agent.RuleBuilder.LabelsTerm = new Orb.Class({
	Extends: DeskPRO.Agent.RuleBuilder.TermAbstract,

	initRow: function() {
		this.inner = $('.label-chooser-wrap', this.rowEl);
		this.labelType = this.rowEl.data('label-type');
		this.labelsList = $('input.labels-box', this.rowEl).first();

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'tickets',
			textarea: this.labelsList,
			onChange: this.updateLabels.bind(this)
		});

		this.currentValue = $('.status-value', this.rowEl);
		this.currentValue.text('(click to set)');
		this.currentValue.on('click', this.show.bind(this));

		this.values = $('.label-values', this.rowEl);

		this.backdrop = $('<div class="backdrop" style="display: none"></div>');
		this.backdrop.appendTo('body');
		this.backdrop.on('click', this.hide.bind(this));

		this.wrapper = $('<div class="field-overlay labels-chooser" style="display:none"><div class="close-trigger"></div></div>');
		$('.close-trigger', this.wrapper).on('click', this.hide.bind(this));

		this.inner.detach().appendTo(this.wrapper).css('display', 'block');
		this.wrapper.appendTo('body');
	},

	updateLabels: function() {
		var labels = this.labelsInput.getLabels();
		var status = '(click to set)';

		if (labels.length) {
			status = labels.join(', ');
		}

		this.currentValue.text(status);

		this.values.empty();

		if (labels.length) {
			Array.each(labels, function(label) {
				var input = $('<option value="" selected="selected" />');
				input.val(label);

				input.appendTo(this.values);
			}, this);
		}
	},

	show: function() {
		this.wrapper.css({
			left: this.currentValue.offset().left,
			top: this.currentValue.offset().top
		});

		this.wrapper.show();
		this.backdrop.show().css('z-index', parseInt(this.wrapper.css('z-index')) - 1);
	},

	hide: function() {
		this.backdrop.hide();
		this.wrapper.hide();
	},

	destroy: function() {
		this.wrapper.remove();
		this.backdrop.remove();
		this.labelsInput.destroy();
	}
});
