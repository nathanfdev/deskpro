Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.MergeIdea = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			ideaId: 0,
			destroyOnClose: false
		};

		this.setOptions(options);

		this.ideaId = this.options.ideaId;

		this.overlay = null;
	},

	_initOverlay: function() {
		if (this.overlay) return this.overlay;

		var data = [];

		Array.each(DeskPRO_Window.getTabWatcher().findTabType('idea'), function(tab) {
			var tid = tab.page.getMetaData('idea_id');
			if (tid && tid != this.ideaId) {
				data.push({
					name: 'open_idea_ids[]',
					value: tid
				});
			}
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/ideas/merge-overlay/' + this.ideaId,
				data: data
			}
		});

		this.overlay.addEvent('ajaxDone', this._initElements.bind(this));
	},

	_initElements: function() {
		this.wrapper = this.overlay.getWrapper();
		var self = this;

		$('.merge-trigger', this.wrapper).click(function() {
			$(this).text('...').attr('disabled', true);
			$('.merge-trigger', this.wrapper).attr('disabled', true );

			var otherIdeaId = $(this).data('idea-id');
			var ideaId = self.ideaId;

			$.ajax({
				url: BASE_URL + 'agent/ideas/merge/' + ideaId + '/' + otherIdeaId,
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					if (data.success) {
						self.fireEvent('mergeSuccess', [data]);
					} else {
						self.fireEvent('mergeError', [data]);
					}
				},
				error: function(data) {
					self.fireEvent('mergeError', [data]);
				}
			});
		});

		$('.with-route', this.wrapper).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		this.overlay.close();

		if (this.options.destroyOnClose) {
			this.desotry
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});
