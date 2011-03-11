Orb.createNamespace('DeskPRO.Agent.Layout');

DeskPRO.Agent.Layout.WindowLayout = Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function() {

		this.TOP_POS = 107;
		this.PADDING = 5; // around each cell, +*2 for each edge of the window
		this.TABSTRIP_HEIGHT = 32;
		this.TABSTRIP_W_ALTER = 7; //2px border, 5px margin

		$('#window_panes').css({
			position: 'absolute',
			top: this.TOP_POS,
			left: 0,
			right: 0,
			bottom: 0
		}).addClass('has-layout').data('layout', this).show();

		this.columns = $('#window_panes > tbody > tr > td');
		var h = $(document).height();
		h -= this.TOP_POS;
		h -= (4*this.PADDING);

		var w = $(document).width();
		var each = Math.floor(w / this.columns.length) - (2*this.PADDING) - (this.columns.length * this.PADDING);

		var self = this;
		this.columns.each(function() {
			self.resizeColumn($(this), each, h);
		});

		this.drawDraggers();
	},

	resizeColumn: function(col, w, h) {

		var pane_content = $('> .pane > .pane-content', col);
		var pane_tabs = $('> .pane > .pane-tabs', col);
		pane_content.css('overflow', 'auto');

		if (h) {

			var pane_h = h;
			if (pane_tabs.length) {
				pane_h -= this.TABSTRIP_HEIGHT;
			}

			pane_content.css({
				height: pane_h
			});
		}

		if (w) {
			pane_content.css({
				width: w-2 //2=leftright border
			});

			$('> ul:first', pane_tabs).width(w-this.TABSTRIP_W_ALTER);
		}
	},

	resizeColumnWidths: function(col, width) {
		var left = this.columns.eq(col);
		var right = this.columns.eq(col+1);

		var total = left.width() + right.width();
		var new_right = total - width;

		this.resizeColumn(left, width);
		this.resizeColumn(right, new_right);
	},

	drawDraggers: function() {

		var dragEl = $('#pane_resizer');
		var col = this.columns.first();
		var pos = col.offset();

		dragEl.css({
			position: 'absolute',
			top: pos.top + 5, //5=padding
			bottom: 8, //8=padding/borders
			left: pos.left + col.outerWidth() - 3 //3= center it a bit
		});

		dragEl.draggable({
			axis: 'x',
			stop: this.dragResized.bind(this)
		});
	},

	dragResized: function(event, ui) {
		var resizer = $('#pane_resizer');
		var col = this.columns.first();

		var w = resizer.position().left - 13; //padding+ offcenter a bit

		this.resizeColumnWidths(0, w);
		this.fireEvent('resized', [this]);
	}
});