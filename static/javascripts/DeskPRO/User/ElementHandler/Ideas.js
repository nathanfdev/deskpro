Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Ideas = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.initFilterForm();
		this.useAjax = this.el.data('ajax-update');

		this.loadingTpl = $('.loading-tpl:first', this.el).detach();
		this.content = $('.portal_ideas:first', this.el);
	},

	initFilterForm: function() {
		var self = this;
		$('select.category_id', this.el).change(function(ev) {
			self.updateView($(this).val());
		});

		$('select.order_by', this.el).change(function(ev) {
			self.updateView($(this).val(), 'order');
		});

		$('.pager a', this.el).click(function(ev) {
			ev.preventDefault();
			self.updateView($(this).attr('href'), 'page');
		});

		$('.idea-status-nav a', this.el).click(function(ev) {
			ev.preventDefault();
			self.updateView($(this).attr('href'));
		});
	},

	injectLoadingEl: function(toEl) {
		var loadingEl = this.loadingTpl.clone();
		loadingEl.show();

		toEl.empty().append(loadingEl);
	},

	updateView: function(url, type) {
		if (!this.useAjax) {
			window.location = url;
			return;
		}

		this.injectLoadingEl($('.content-wrapper:first', this.content));

		if (type == 'page' || type == 'order') {
			var pos = this.el.offset();
			$(document).scrollTop(pos.top);
		}

		url = Orb.appendQueryData(url, '_partial');

		$.ajax({
			url: url,
			dataType: 'html',
			context: this,
			success: function(html) {
				this.content.empty().html(html);
				DeskPRO_Window.initFeatures(this.content);
				this.initFilterForm();
			}
		});
	}
});