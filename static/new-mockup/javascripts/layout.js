Orb.createNamespace('DeskPRO.Agent.UI');

DeskPRO.Agent.UI.Layout = new Class({
	
	layoutEl: null,
	layoutRowEl: null,
	
	initialize: function() {
		this.layoutEl = $('#layout');
		this.layoutRowEl = $('#layout > tbody > tr');
		
		$('#add_btn').click(this.addColumn.bind(this));
		
		// Tabs
		$('.pane-head > ul').each(function() {
			var el = $(this);
			var tabs = new DeskPRO.UI.SimpleTabs({
				context: el.parent().parent()
			});
		});
		$('#nav .event-message-wrap button').click(function() {
			$('#nav .event-message-wrap').fadeOut(300);
		});
	},
	
	resizeColumns: function() {
		var h = this.layoutEl.outerHeight() - 32; // 30=padding
		$('div.pane', this.layoutEl).height(h);
	},
	
	addColumn: function() {
		var w = 100 / this.numColumns();
		this.layoutRowEl.append('<td><div class="pane"><div class="pane-head">Some Title</div><div class="pane-content">Pane Content</div></div></td>');
		$('> td', this.layoutRowEl).attr('width', w);
		this.resizeColumns();
	},
	
	numColumns: function() {
		return $('> td', this.layoutRowEl).length;
	}
});