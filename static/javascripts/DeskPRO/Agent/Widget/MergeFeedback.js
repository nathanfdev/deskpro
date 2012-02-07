Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.MergeFeedback = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			feedbackId: 0,
			destroyOnClose: false
		};

		this.setOptions(options);

		this.feedbackId = this.options.feedbackId;

		this.overlay = null;
	},

	_initOverlay: function() {
		if (this.overlay) return this.overlay;

		var data = [];

		Array.each(DeskPRO_Window.getTabWatcher().findTabType('feedback'), function(tab) {
			var tid = tab.page.getMetaData('feedback_id');
			if (tid && tid != this.feedbackId) {
				data.push({
					name: 'open_feedback_ids[]',
					value: tid
				});
			}
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/feedback/merge-overlay/' + this.feedbackId,
				data: data
			}
		});

		this.overlay.addEvent('ajaxDone', this._initElements.bind(this));
	},

	_initElements: function() {
		this.wrapper = this.overlay.getWrapper();
		var self = this;

		$('.merge-trigger', this.wrapper).on('click', function() {
			$(this).text('...').attr('disabled', true);
			$('.merge-trigger', this.wrapper).attr('disabled', true );

			var otherFeedbackId = $(this).data('feedback-id');
			var feedbackId = self.feedbackId;

			$.ajax({
				url: BASE_URL + 'agent/feedback/merge/' + feedbackId + '/' + otherFeedbackId,
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
