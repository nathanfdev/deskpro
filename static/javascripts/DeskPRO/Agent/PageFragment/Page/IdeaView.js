Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.IdeaView = new Class({

	Extends: DeskPRO.Agent.PageFragment.Page.BasicTicket,

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

		this._initEditables();
		this._initMenus();
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
	}
});