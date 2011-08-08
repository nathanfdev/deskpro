Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.IdeaView = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'ticket',

	popout: null,
	popout_overview: null,

	isMouseOverPopout: false,
	hasInitPopout: false,
	popoutPage: null,

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = $('.layout-content:first', this.wrapper).attr('id', Orb.getUniqueId());

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		DeskPRO_Window.getMessageBroker().sendMessage('ui.tab.opened', { type: 'ideas', id: this.getMetaData('idea_id') });

		this._initEditables();
		this._initMenus();
		this._initComments();
		$('button.who-voted-trigger', this.wrapper).click(this.showWhoVoted.bind(this));

		if (this.meta.is_validating) {
			this._initValidating();
			if (!this.meta.from_listing_result) {
				this.meta.from_listing_result = '';
			}
		}
	},

	//#################################################################
	//# Validation
	//#################################################################

	_initValidating: function() {
		$('button.approve-trigger', this.wrapper).click(this.approveIdea.bind(this));
		$('button.disapprove-trigger', this.wrapper).click(this.disapproveIdea.bind(this));
		$('button.skip-trigger', this.wrapper).click(this.skipValidateIdea.bind(this));
	},

	approveIdea: function() {
		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/validate?from_result_id=' + this.meta.from_listing_result,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {

				DeskPRO_Window.getMessageBroker().sendMessage('validating-ideas.approved', {idea_id: this.meta.idea_id});

				var load_url = info.next_url;
				if (info.next_url) {
					DeskPRO_Window.runPageRoute('page:' + info.next_url);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},

	disapproveIdea: function() {
		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/validate-delete?from_result_id=' + this.meta.from_listing_result,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {

				DeskPRO_Window.getMessageBroker().sendMessage('validating-ideas.deleted', {idea_id: this.meta.idea_id});

				var load_url = info.next_url;
				if (info.next_url) {
					DeskPRO_Window.runPageRoute('page:' + info.next_url);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},

	skipValidateIdea: function() {
		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/validate-skip?from_result_id=' + this.meta.from_listing_result,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {

				var load_url = info.next_url;
				if (info.next_url) {
					DeskPRO_Window.runPageRoute('page:' + info.next_url);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},


	//#################################################################
	//# Editables
	//#################################################################

	_initEditables: function() {
		var titleEl = $('h3.title.prop:first', this.wrapper);
		if (!titleEl.attr('id')) {
			titleEl.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/ajax-save-editables'
			}
		});
	},

	//#################################################################
	//# Menus
	//#################################################################

	_initMenus: function() {
		var self = this;
		this.catMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.menu-trigger.category_id:first', this.wrapper),
			menuElement: $('.menu.category_id:first', this.wrapper),
			onItemClicked: function(info) {
				self.updateCategory($(info.itemEl).data('option-value'));
			}
		});

		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.menu-trigger.status:first', this.wrapper),
			menuElement: $('.menu.status:first', this.wrapper),
			onItemClicked: function(info) {
				self.updateStatus($(info.itemEl).data('option-value'), $(info.itemEl).data('status-type'));
			}
		});
	},

	updateCategory: function(category_id) {
		var catEl = $('li.cat-' + category_id, this.catMenu.getListElement());
		var title = catEl.data('full-title');

		$('.prop-val.category_id', this.wrapper).html(Orb.escapeHtml(title));

		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/ajax-update-category/' + category_id,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(html) {
				
			}
		});
	},

	updateStatus: function(status, status_type) {
		var statusEl = $('li.status-' + status, this.statusMenu.getListElement());
		var title = statusEl.html();

		var propEl = $('.prop-val.status', this.wrapper).html(Orb.escapeHtml(title));
		var containEl = propEl.parent();
		var className = containEl.attr('class');
		className = className.replace(/\bstatus\-(.*?)\b/, '');
		className += ' status-' + status_type;
		containEl.attr('class', className);

		if (status != status_type) {
			var status_code = status_type + '.' + status;
		} else {
			var status_code = status;
		}

		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/ajax-update-status/' + status_code,
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(html) {

			}
		});
	},

	//#################################################################
	//# Who Voted
	//#################################################################

	showWhoVoted: function() {

		var displayEl = $('<div style="width: 650px; height: 400px;"></div>"');
		var spinner = new Spinner(displayEl, {
			radii: [15,9],
			padding: 15
		}).play();

		var overlay = new DeskPRO.UI.Overlay({
			contentElement: displayEl,
			maxWidth: 650,
			destroyOnClose: true
		});
		overlay.openOverlay();

		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/who-voted',
			context: this,
			dataType: 'html',
			success: function(html) {
				var el = $('<div style="width: 650px; height: 500px;">' + html + '</div>');
				overlay.setContent(el);

				spinner.remove();
				displayEl.remove();
				
				this._initWhoVotedEl(overlay.elements.wrapper);
			}
		});
	},

	_initWhoVotedEl: function(el) {
		var controls = $('.who-voted-controls', el);

		var self = this;
		$('.show-people, .show-guests', controls).click(function() {

			var show_people = $('.show-people', controls).is(':checked');
			var show_guests = $('.show-guests', controls).is(':checked');

			// Always at least one checked
			if (!show_people && !show_guests) {
				if ($(this).is('.show-people')) {
					$('.show-guests', controls).attr('checked', true);
				} else {
					$('.show-people', controls).attr('checked', true);
				}
			}

			if (show_people) {
				$('table.who-voted', el).addClass('do-show-people');
			} else {
				$('table.who-voted', el).removeClass('do-show-people');
			}

			if (show_guests) {
				$('table.who-voted', el).addClass('do-show-guests');
			} else {
				$('table.who-voted', el).removeClass('do-show-guests');
			}
		})
	},

	//#################################################################
	//# Comments
	//#################################################################

	_initComments: function() {
		this.commentWrapper = $('.messages-wrap', this.wrapper);
		this.newCommentWrapper = $('.new-comment:first', this.wrapper);
		$('button', this.newCommentWrapper).click(this.submitNewComment.bind(this));
	},

	submitNewComment: function() {

		var loadingOn = $('.loading-on', this.newCommentWrapper).show();
		var loadingOff = $('.loading-off', this.newCommentWrapper).hide();

		var data = [];
		data.push({
			name: 'content',
			value: $('textarea', this.newCommentWrapper).val()
		});

		$.ajax({
			url: BASE_URL + 'agent/ideas/view/' + this.meta.idea_id + '/ajax-save-comment',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				loadingOn.hide();
				loadingOff.show();

				$('textarea', this.newCommentWrapper).val('');
				var el = $(html);
				this.newCommentWrapper.before(el);
				this._initMessage(el);

				this._handleSendReplySuccess(html);
			}
		});
	},

});