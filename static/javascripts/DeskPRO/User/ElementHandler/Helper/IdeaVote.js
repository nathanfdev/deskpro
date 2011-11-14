Orb.createNamespace('DeskPRO.User.ElementHandler.Helper');

DeskPRO.User.ElementHandler.Helper.IdeaVote = new Orb.Class({

	initialize: function() {
		var self = this;
		$('body').on('click', '.dp-idea-vote', function() {
			self.voteOnElement($(this));
		});
	},

	voteOnElement: function(el) {
		var rating;
		if (el.is('.dp-voted')) {
			rating = 0;
		} else {
			rating = 1;
		}

		$.ajax({
			url: el.data('vote-url'),
			data: { rating: rating },
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.voted) {
					el.addClass('dp-voted');
				} else {
					el.removeClass('dp-voted');
				}

				$('em', el).first().text(data.total_rating || 0);
			}
		});
	}
});
