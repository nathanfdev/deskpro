Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.supported = false;
		if (typeof HTMLCanvasElement != undefined) {
			this.supported = true;
		}

		this.faviconEl = $(options.favicon);
		this.badgeEl = null;
	},

	updateBadge: function(num) {
		if (!this.supported) return;
		
		var canvas = document.createElement('canvas');
		var img = document.createElement('img');

		canvas.height = canvas.width = 16;
		var canvasContext = canvas.getContext('2d');

		var self = this;

		// We have only two digits to play with
		if (num > 99) {
			num = 99;
		}

		img.onload = function() {
			canvasContext.drawImage(this, 0, 0);
			canvasContext.font = '11px "helvetica", sans-serif';

			canvasContext.fillStyle = 'rgba(255, 255, 255, 0.75)';
			canvasContext.fillRect(3, 6, 12, 10);

			canvasContext.fillStyle = '#000';
			canvasContext.fillText(num, 4, 16);

			if (self.badgeEl) {
				self.badgeEl.remove();
			}

			console.log(canvas);

			self.badgeEl = self.faviconEl.clone();
			self.badgeEl.get(0).href = canvas.toDataURL('image/png');
			$('body').append(self.badgeEl);
		};
		img.src = this.faviconEl.attr('href');
	}
});