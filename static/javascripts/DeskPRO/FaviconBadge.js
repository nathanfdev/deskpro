Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.supported = false;
		if ($('body').is('.canvas')) {
			this.supported = true;
		}

		this.faviconEl = $(options.faviconEl);
		this.badgeEl = null;
	},

	updateBadge: function(num) {
		if (!this.supported) return;
		
		var canvas = document.createElement('canvas');
		var img = document.createElement('img');

		canvas.height = canvas.width = 16;
		var canvasContext = canvas.getContext('2d');

		var self = this;

		img.onload = function() {
			canvasContext.drawImage(this, 0, 0);
			canvasContext.font = 'bold 10px "helvetica", sans-serif';
			canvasContext.fillStyle = '#F0EEDD';
			canvasContext.fillText(num, 2, 12);

			if (self.badgeEl) {
				self.badgeEl.remove();
			}

			self.badgeEl = this.faviconEl.clone();
			self.badgeEl.get(0).href = canvas.toDataUrl('image/png');
			$('body').append(self.badgeEl);
		};
		img.src = this.faviconEl.attr('href');
	}
});