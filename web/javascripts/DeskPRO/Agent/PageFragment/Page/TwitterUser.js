Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.TwitterUser = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-user';
	},

	initPage: function(el) {
		this.el = $(el);

		var self = this;

		// user follow/unfollow
		this.el.on('click', '.follow-button', function(e) {
			e.preventDefault();

			$(this).addClass('unfollow-button').removeClass('follow-button')
			$(this).find('.clean-white').text('Unfollow');

			$.ajax({
				url: self.getMetaData('saveFollowUrl'),
				type: 'POST',
				data: {
					user_id: self.getMetaData('userId'),
					account_id: self.getMetaData('accountId')
				}
			});
		});
		this.el.on('click', '.unfollow-button', function(e) {
			e.preventDefault();

			$(this).addClass('follow-button').removeClass('unfollow-button');
			$(this).find('.clean-white').text('Follow');

			$.ajax({
				url: self.getMetaData('saveUnfollowUrl'),
				type: 'POST',
				data: {
					user_id: self.getMetaData('userId'),
					account_id: self.getMetaData('accountId')
				}
			});
		});

		this.el.on('click', '.send-message-button', function(e) {
			e.preventDefault();

			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'ajax',
				contentAjax: { url: BASE_URL + 'agent/twitter/user/' + self.getMetaData('userId') + '/message-overlay' },
				zIndex: 40000, // Above floating people windows
				onAjaxDone: function() {
					var wrapper = overlay.getWrapper();

					wrapper.find('textarea[name=text]').focus();

					var helper = new DeskPRO.Agent.PageHelper.Twitter(wrapper, self, {
						saveMessageCallback: function(data) {
							wrapper.addClass('loading');

							$.ajax({
								url: self.getMetaData('saveUserMessageUrl'),
								type: 'POST',
								data: data,
								dataType: 'json'
							}).done(function(data) {
								if (data.success) {
									overlay.close();
								} else if (data.error) {
									alert(data.error);
								}
							}).always(function() {
								wrapper.removeClass('loading');
							});
						}
					});
				}
			});
			overlay.open();
		});

		$('.profile-box-container.tabbed', this.wrapper).each(function() {
			var simpleTabs = new DeskPRO.UI.SimpleTabs({
				triggerElements: '> header li',
				context: this
			});
		});

		$('.timeago', this.el).timeago();
	}
});
