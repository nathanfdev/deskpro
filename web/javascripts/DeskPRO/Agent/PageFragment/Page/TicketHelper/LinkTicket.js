Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.TicketHelper');

DeskPRO.Agent.PageFragment.Page.TicketHelper.LinkTicket = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			loadUrl: '',
			saveUrl: '',
			ticket_id: null
		};

		this.setOptions(options);
		this.page = page;

		this.page.addEvent('destroy', this.destroy, this);
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
			//zIndex: 'top',
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

		$('.ticket-search-box').on('click', 'ul.results-list li', function(){
			var footerEl = self.overlay.getWrapper().find('.overlay-footer').addClass('loading');
			
			var isParent = wrapper.find("#is-parent").is(":checked");
			
			if (confirm("Are you sure you want to link the current to this ticket?")) {
				$.ajax({
					url: '/agent/tickets/' + self.page.meta.ticket_id + '/link/' + $(this).attr('ticket-id'),
					data: {"isParent" : isParent ? 1 : 0},
					type: 'POST',
					dataType: 'json',
					complete: function() {
						footerEl.removeClass('loading');
					},
					success: function(data) {
						if (data.success) {
							// remove old tabs, theyre outdated
							Array.each(DeskPRO_Window.getTabWatcher().findTabType(self.options.tabType), function(tab) {
								var id = tab.page.getMetaData(self.options.metaIdName);
								if (id == data.old_id || id == data.id) {
									DeskPRO_Window.TabBar.removeTabById(tab.id);
								}
							});

							//DeskPRO_Window.runPageRoute(self.options.loadRoute.replace('{id}', data.id));
						}

						self.overlay.close();
						DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/' + self.page.getMetaData('ticket_id'), {ignoreExist:true});
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
		}
	}
});