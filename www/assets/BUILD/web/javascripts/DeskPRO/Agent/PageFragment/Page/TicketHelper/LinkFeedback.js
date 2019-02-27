Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.TicketHelper');

DeskPRO.Agent.PageFragment.Page.TicketHelper.LinkFeedback = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		this.options = {
			loadUrl: '',
			saveUrl: '',
      reloadPageUrl: ''
		};

		this.setOptions(options);
		this.page = page;
	},

	_initOverlay: function() {
		var self = this;

		if (this.overlay) {
			return;
		}

		this.wrapperEl = $('<div class="link-ticket-overlay"><div class="overlay-content" style="width: 400px; height: 300px; "/><div>Loading...</div></div>');

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapperEl,
			destroyOnClose: true,
			zIndex: 1800,
			onOverlayClosed: function() {
				self.overlay = null;
			}
		});

		$.ajax({
			url: this.options.loadUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				if (this.overlay) {
					this.overlay.setContent($(html));
					this.wrapperEl = this.overlay.getWrapper();
					this._initControls();
					DeskPRO.ElementHandler_Exec(this.wrapperEl);
				}
			}
		});
	},

	_initControls: function() {
		var self = this;

		var wrapper = this.overlay.getWrapper();

		wrapper.on('click', '.save-trigger', this._doSave.bind(this));
    wrapper.find('.feedback-finder').bind('feedbacksearchboxclick', function(ev, feedbackId, title, sb) {
			var footerEl = self.overlay.getWrapper().find('.overlay-footer').addClass('loading');

			var isSubscribeOwner = wrapper.find("#is_subscribe_owner").is(":checked");
      var isSubscribeParticipants = wrapper.find("#is_subscribe_participants").is(":checked");

			if (confirm("Are you sure you want to link the current ticket to this feedback?")) {

        wrapper.find('.loading-on').show();
        sb.close();
        wrapper.find("#is_subscribe_owner").prop('disabled', true);
        wrapper.find("#is_subscribe_participants").prop('disabled', true);
        wrapper.find('.term').prop('disabled', true);

				$.ajax({
          url: self.options.saveUrl,
					data: {
            feedback: feedbackId,
            is_subscribe_ticket_owner: isSubscribeOwner ? 1 : 0,
            is_subscribe_ticket_participants: isSubscribeParticipants ? 1 : 0
          },
					type: 'POST',
					dataType: 'json',
					complete: function() {
						footerEl.removeClass('loading');
					},
					success: function(data) {

            // remove tabs with linked feedback, they are outdated
            DeskPRO_Window.getTabWatcher().findTabType('feedback').forEach(function(tab) {
              if (feedbackId == tab.page.getMetaData('feedback_id')) {
                DeskPRO_Window.TabBar.removeTabById(tab.id);
              }
            });

						self.overlay.close();
            DeskPRO_Window.loadPage(self.options.reloadPageUrl, {ignoreExist:true});
            self.page.closeSelf();
					},
					error: function(xhr, textStatus, errorThrown) {
						self.overlay.close();

						var status = (xhr.status || '') + ' ' + (errorThrown || '') + ' ' + (xhr.statusText || '');
						DeskPRO_Window._showAjaxError('<div class="error-details">Here is the raw output returned from the server error:<textarea class="raw">' + status + "\n\n" + Orb.escapeHtml(xhr.responseText) + '</textarea></div>');
					}
				});
			}
		});
	},

	_doSave: function(e) {
		this.close();
	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		if (this.overlay) {
			this.overlay.destroy();
			this.overlay = null;
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
			this.overlay = null;
		}
		this.page = null;
		this.wrapperEl = null;
	}
});
