Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.Ideas = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this.useAjax = this.el.data('ajax-update');
		this.initFilterForm();

		this.loadingTpl = $('.loading-tpl:first', this.el).detach();
		this.content = $('.portal_feedback:first', this.el);

		var voteHelper = new DeskPRO.User.ElementHandler.Helper.IdeaVote();
		$('.feedback-btn', this.el).on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			voteHelper.openMenu($(this));
		});
	},

	initFilterForm: function() {
		var self = this;
		$('select.category_id', this.el).on('change', function(ev) {
			self.updateView($(this).val());
		});

		if (this.useAjax) {
			$('#feedback_find_form_btm').on('submit', function(ev) {
				ev.preventDefault();
				self.submitForm($(this));
			});

			$('a.page-link', this.el).on('click', function(ev) {
				ev.preventDefault();
				self.updateView($(this).attr('href'));
			});
		}
	},

	injectLoadingEl: function(toEl) {
		var loadingEl = this.loadingTpl.clone();
		loadingEl.show();

		toEl.empty().append(loadingEl);
	},

	submitForm: function(form) {
		this.injectLoadingEl(this.content);

		var formData = form.serializeArray();

		var formUrl = form.attr('action');
		formUrl = Orb.appendQueryData(formUrl, '_partial');

		$.ajax({
			url: formUrl,
			data: formData,
			dataType: 'html',
			type: 'POST',
			context: this,
			success: function(html) {
				this.content.empty().html(html);
				DeskPRO_Window.initFeatures(content);
				this.initFilterForm();
			}
		});
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
