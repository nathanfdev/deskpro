Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.TicketChangeUser = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			ticketId: 0,
			destroyOnClose: false
		};

		this.setOptions(options);

		this.ticketId = this.options.ticketId;

		this.overlay = null;
	},

	_initOverlay: function() {
		if (this.overlay) return this.overlay;

		var data = [];
		var self = this;

		this.overlay = new DeskPRO.UI.Overlay({
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/tickets/' + this.ticketId + '/change-user-overlay',
				data: data
			},
			onAjaxDone: function() {
				self.wrapper = self.overlay.elements.wrapper;
				DeskPRO_Window.initInterfaceLayerEvents(self.wrapper);

				self.wrapper.on('click', 'button.change-user-trigger', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					var personId = $(this).data('person-id');

					var keepParticipant = self.wrapper.find('.participant-check').is(':checked');
					var data = {'keep': (keepParticipant ? 1 : 0)};

					$.ajax({
						url: BASE_URL + 'agent/tickets/' + self.ticketId + '/change-user/' + personId,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function(data) {
							self.fireEvent('success', [data]);
						}
					});
				});

				self.wrapper.find('.person-finder').bind('personsearchboxclick', function(ev, personId, name, email, sb) {
					sb.close();

					$.ajax({
						url: BASE_URL + 'agent/tickets/' + self.ticketId + '/change-user-overlay/preview/' + personId,
						type: 'get',
						dataType: 'html',
						success: function(html) {
							self.wrapper.find('.person-preview-content').html(html);
						}
					});
				});
			}
		});

		this.overlay.addEvent('ajaxDone', this._initElements.bind(this));
	},

	_initElements: function() {
		this.wrapper = this.overlay.getWrapper();
		var self = this;


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
