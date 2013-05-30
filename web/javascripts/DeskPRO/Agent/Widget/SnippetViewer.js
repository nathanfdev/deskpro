Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.SnippetViewer = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			viewUrl: null,
			triggerElement: null,
			positionMode: 'side',
			sidePosition: 'bottom',
			destroyOnClose: false
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
			sidePosition: this.options.sidePosition,
			pageUrl: this.options.viewUrl,
			pageSource: DeskPRO_Window.util.getPlainTpl($('#tickets_snippet_shell_tpl')),
			destroyOnClose: false,
			onPageInit: function(pop, page) {
				page.addEvent('closeSelf', function(ev) {
					ev.cancel = true;
					self.close();
				});

				page.addEvent('snippetClick', function(ev) {
					ev.page = page;
					self.fireEvent('snippetClick', [ev]);
				});
			}
		});
	},

	open: function() {
		this.fireEvent('onBeforeOpen');
		this.pop.open();
		DeskPRO.Agent.Widget.SnippetViewer.HasOpen = true;
	},

	close: function() {
		if (this.pop) {
			this.pop.close();
		}
		DeskPRO.Agent.Widget.SnippetViewer.HasOpen = false;

		if (this.options.destroyOnClose) {
			this.destroy();
		}
	},

	destroy: function() {
		this.pop.destroy();
		DeskPRO.Agent.Widget.SnippetViewer.HasOpen = false;
	}
});
DeskPRO.Agent.Widget.SnippetViewer.HasOpen = false;