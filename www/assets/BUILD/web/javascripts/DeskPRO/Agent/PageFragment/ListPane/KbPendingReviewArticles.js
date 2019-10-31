Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbPendingReviewArticles = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		this.actionsMenu = new DeskPRO.UI.Menu({
			menuElement: $('ul.actions-menu:first', this.wrapper),
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
			onItemClicked: function(info) {
				var ids = self.selectionBar.getCheckedValues();
				var els = [];

				var formData = [];
				ids.forEach(function(id) {
					formData.push({
						name: 'ids[]',
						value: id
					});

					els.push($('article.review-article-' + id + ':first', self.wrapper).get(0));
				});

				$(els).fadeOut();

				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/kb/pending-review-articles/mass-actions/' + action,
					data: formData,
					type: 'POST',
					dataType: 'json',
					error: function() {
						$(els).show();
					},
					success: function() {
						$(els).remove();
						DeskPRO_Window.util.modCountEl('#kb_pending_review_count', '-', els.length);
            self.selectionBar.checkNone();
					}
				});
			}
		});
		this.ownObject(this.actionsMenu);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function(ev) {
				self.actionsMenu.open(ev);
			}
		});
		this.ownObject(this.selectionBar);

	},
});
