Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.WelcomeBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var formWrap = $('#admin_contact_form');
		var messageTxt = formWrap.find('textarea.message');
		var emailTxt   = formWrap.find('input.email_address');
		var submitUrl  = formWrap.data('submit-url');

		messageTxt.one('focus', function() {
			$(this).animate({height: '100px'}, 500);
			formWrap.find('.email-addy-wrap').fadeIn(250);
		});

		formWrap.find('.default-addy').on('click', function() {
			$(this).hide();
			formWrap.find('.input-addy').show().find('input').focus();
		})

		formWrap.find('button.send-trigger').on('click', function(ev) {

			ev.preventDefault();

			if (!messageTxt.val().trim()) {
				alert('Please enter a message');
				return;
			}

			var me = $(this);
			var load = formWrap.find('.send-loading');

			me.hide();
			load.show();
			$.ajax({
				url: submitUrl,
				type: 'POST',
				data: {
					message: messageTxt.val(),
					email_address: emailTxt.val()
				},
				complete: function() {
					me.show();
					load.hide();
				},
				success: function() {
					formWrap.find('.form-input').fadeOut(600, function() {
						formWrap.find('.form-success').fadeIn(300);
					});
				}
			});
		});
	}
});