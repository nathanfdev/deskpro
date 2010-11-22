Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,
	
	form: null,
	table: null,

	initPage: function(el) {
		this.form = $('.search-form', el);
		
		this.results = $('.search-results', el);

		this.form.submit((function (event) {
			event.preventDefault();
			this.ajaxSubmitForm();
		}).bind(this));
	},
	
	ajaxSubmitForm: function() {
		$('input[type="submit"]', this.form).val('Searching...').attr('disabled', 'disabled');
		
		$.ajax({
			dataType: 'text',
			url: $(this.form).attr('action'),
			data: $(this.form).serialize(),
			success: this.handleSubmitForm.bind(this)
		});
	},
	
	handleSubmitForm: function(data) {
		$('input[type="submit"]', this.form).val('Search').attr('disabled', '');
		this.results.html(data);
		
		$('table > tbody > tr', this.results).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});