Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Person = new Class({
	
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {
		$('.section', el).each(function() {
			var sec = $(this);
			var title = $('h5', sec);
			var content = $('div:first', sec);

			title.click(function() {
				if (content.is(':visible')) {
					content.slideUp(function() { sec.addClass('closed'); });
					
				} else {
					sec.removeClass('closed');
					content.slideDown();
				}
			});
		});
	}
	
});