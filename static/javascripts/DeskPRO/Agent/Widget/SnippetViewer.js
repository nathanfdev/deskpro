Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.SnippetViewer = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			viewUrl: null,
			triggerElement: null
		};

		this.setOptions(options);

		if (this.options.triggerElement) {
			var self = this;
			$(this.options.triggerElement).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				self.open();
			});
		}

		this.pop = new DeskPRO.Agent.PageHelper.Popover({
			pageUrl: this.options.viewUrl,
			onPageInit: function(pop, page) {
				page.addEvent('closeSelf', function(ev) {
					ev.cancel = true;
					self.destroyPop();
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