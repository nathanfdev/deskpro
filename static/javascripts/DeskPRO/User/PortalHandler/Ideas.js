Orb.createNamespace('DeskPRO.User.PortalHandler');

DeskPRO.User.PortalHandler.Ideas = new Orb.Class({

	Extends: DeskPRO.User.PortalHandler.PortalHandlerAbstract,

	init: function() {
		this.contentList = $('.content-list:first > ul', this.el);
		this.catSwitcher = $('select.ideas-cat-choose:first', this.el);
		this.statusSwitcher = $('select.ideas-status-choose:first', this.el);

		if (this.catSwitcher.length) {
			this._initCatSwitcher();
		}
	},

	_initCatSwitcher: function() {
		var self = this;

		function getChoices() {
			return {
				'status': self.statusSwitcher.val(),
				'category_id': self.catSwitcher.val()
			}
		}
		
		this.catSwitcher.change(function() {
			var c = getChoices();
			self.loadList(c.status, c.category_id);
		});
		this.statusSwitcher.change(function() {
			var c = getChoices();
			self.loadList(c.status, c.category_id);
		});
	},

	loadList: function(status, category_id) {
		$.ajax({
			url: BASE_URL + 'feedback/quick-browser/' + status + '/' + category_id + '?_partial',
			dataType: 'html',
			context: this,
			success: function(html) {
				this.contentList.empty().html(html);
			}
		});
	}
});