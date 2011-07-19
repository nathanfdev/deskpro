Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewIdea = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		$('#idea_title').change(this.showSuggestions.bind(this));
		this.suggestionsBox = $('.suggestions-box:first', this.el);
		this.resultsEl = $('.results:first', this.suggestionsBox);

		this.url = this.el.data('suggestions-url');
	},

	showSuggestions: function() {
		var title = $('#idea_title').val().trim();

		if (!title.length) {
			this.suggestionsBox.hide();
			return;
		}

		$.ajax({
			url: this.url,
			dataType: 'html',
			data: {'content': title},
			context: this,
			success: function(html) {
				this.resultsEl.html(html);

				if (!$('li:first', this.resultsEl).length) {
					this.suggestionsBox.hide();
				} else {
					this.suggestionsBox.show();
				}
			}
		});
	}
});