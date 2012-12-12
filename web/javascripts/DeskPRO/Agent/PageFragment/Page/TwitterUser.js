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

					wrapper.on('click', '.reply-type li', function() {
						var $this = $(this);

						wrapper.find('.reply-type li').removeClass('on');
						$this.addClass('on');
						wrapper.find('input[name=type]').val($this.data('type'));
					});

					wrapper.on('click', '.send-trigger', function(e) {
						e.preventDefault();

						var text = $.trim(wrapper.find('textarea[name=text]').val());
						if (!text.length) {
							return;
						}

						wrapper.addClass('loading');

						var data = wrapper.find('form').serializeArray();
						data.push({
							name: 'user_id',
							value: self.getMetaData('userId')
						});

						$.ajax({
							url: self.getMetaData('saveUserMessageUrl'),
							type: 'POST',
							data: data,
							dataType: 'json'
						}).always(function() {
							wrapper.removeClass('loading');
						}).done(function(data) {
							if (data.success) {
								overlay.close();
							} else if (data.error) {
								alert(data.error);
							}
						});
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
