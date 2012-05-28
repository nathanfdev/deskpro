Orb.createNamespace('DeskPRO.Admin.ElementHandler.OverviewStat');

DeskPRO.Admin.ElementHandler.OverviewStat.StandardWithGroup = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.initContents();
	},

	initContents: function() {
		var self = this;

		this.el.find('header .drop-option').each(function() {
			var dropopt = $(this);
			var label = dropopt.find('i');
			var sel = dropopt.find('select');

			label.text(sel.find(':selected').text());
			sel.on('change', function() {
				label.text(sel.find('option:selected').text());
				self.updateGrouping(sel.find('option:selected').val());
			});
		})
	},

	updateGrouping: function(val) {

		var data = [];
		this.el.find('header .drop-option select').each(function() {
			var name = $(this).attr('name');
			var val = $(this).val();

			data.push({
				name: name,
				value: val
			});
		});

		this.el.find('.loading-overlay').show();
		$.ajax({
			url: this.el.data('update-url'),
			context: this,
			data: data,
			complete: function() {
				this.el.find('.loading-overlay').hide();
			},
			success: function(html) {
				this.el.empty().html(html);
				this.initContents();
			}
		});
	}
});
