Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Kb = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.contentList = $('.content-list:first > ul', this.el);
		this.catSwitcher = $('select.kb-cat-choose:first', this.el);

		if (this.catSwitcher.length) {
			this._initCatSwitcher();
		}
	},

	_initCatSwitcher: function() {
		var self = this;
		this.catSwitcher.change(function() {
			var cat_id = $(this).val();
			self.loadCat(cat_id);
		});
	},

	loadCat: function(cat_id) {
		$.ajax({
			url: BASE_URL + 'kb/quick-browser/' + cat_id + '?_partial',
			dataType: 'html',
			context: this,
			success: function(html) {
				this.contentList.empty().html(html);
			}
		});
	}
});