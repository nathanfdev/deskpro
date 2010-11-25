Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,
	
	form: null,
	quickForm: null,
	quickFormLaoding: null,
	results: null,
	
	autoTimeout: null,

	initPage: function(el) {
		
		this.form = $('.search-form form', el);
		this.quickForm = $('.quick-search-form form', el);
		this.quickFormLaoding = $('.quick-search-form img.quick-search-loading', el);
		
		Orb.Compat.WebForms.placeholder($('input[placeholder]', this.quickForm));
		
		this.results = $('.search-results', el);

		this.form.submit((function (event) {
			event.preventDefault();
			this.ajaxSubmitForm();
		}).bind(this));
		
		// Set up quick/form toggle
		$('.listpane-top .toggle', el).click(function() {
			if ($(this).is('.toggle-search-form')) {
				$('.quick-search-form', el).slideUp(function() {
					$('.search-form', el).slideDown();
				});
			} else {
				$('.search-form', el).slideUp(function() {
					$('.quick-search-form', el).slideDown();
				});
			}
		});
		
		// Set up auto search
		var q = $('input[name="q"]', this.quickForm);
		q.keyup((function() {
			if (q.val() === '') {
				this.results.html('');
				return;
			}

			if (this.autoTimeout) {
				window.clearTimeout(this.autoTimeout);
				this.autoTimeout = null;
			}
			
			this.autoTimeout = this.ajaxQuickSubmitForm.delay(250, this);
		}).bind(this));
		
		// Set up search builder
		var editor = new DeskPRO.Form.RuleBuilder($('.search-builder-tpl', this.wrapper));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		$('.search-form .add .btn').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'criteria['+count+']';
			
			$(this).data('add-count', count+1);
			
			editor.addNewRow($('.search-form .search-terms'), basename);
		});
	},
	
	ajaxQuickSubmitForm: function() {
		this.quickFormLaoding.show();
		$.ajax({
			dataType: 'text',
			url: $(this.quickForm).attr('action'),
			data: $(this.quickForm).serialize(),
			success: this.handleQuickSubmitForm.bind(this)
		});
	},
	
	handleQuickSubmitForm: function(data) {
		this.quickFormLaoding.hide();
		this.results.html(data);
		
		$('table > tbody > tr', this.results).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
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