Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.SnippetViewer = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			viewUrl: null,
			triggerElement: null,
			positionMode: 'side'
		};

		var self = this;
		this.setOptions(options);

		if (this.options.triggerElement) {
			$(this.options.triggerElement).on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				self.open();
			});
		}

		this.pop = new DeskPRO.Agent.PageHelper.Popover({
			positionMode: this.options.positionMode,
			pageUrl: this.options.viewUrl,
			destroyOnClose: false,
			onPageInit: function(pop, page) {
				page.addEvent('closeSelf', function(ev) {
					ev.cancel = true;
					self.close();
				});

				page.addEvent('snippetClick', function(ev) {
					self.fireEvent('snippetClick', [ev]);
				});
			}
		});
	},

	open: function() {
		this.pop.open();
	},

	close: function() {
		if (this.pop) {
			this.pop.close();
		}
	},

	destroy: function() {
		this.pop.destroy();
	}
});
