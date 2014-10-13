define [
	'Admin/Main/Ctrl/Base',
	'Admin/ChannelFacebook/FormModel/EditFacebookPageModel',
	'facebook'
], (
	Admin_Ctrl_Base,
	Admin_ChannelFacebook_FormModel_EditFacebookPageModel,
	FB
) ->
	class Admin_ChannelFacebook_Ctrl_Create extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Create'
		@CTRL_AS = 'ChannelFacebookCreate'
		@DEPS    = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout']

		init: ->
			@app_id = '1496820407237522'
			@app_secret = null
			@app_connected = false
			@app_name = ''
			@app_icon_url = ''
			@app_logo_url = ''
			@app_credentials_required = false
			@user_graph_id = null
			@user_access_token = null
			@available_user_pages = []
			@fb_init = false
			@checked_for_pages = false
			# initial load MUST contain curent page ids so that we only display new ones

		appConnect: ->
			if !@app_id or !@app_secret
				@app_credentials_required = true
			else
				@app_credentials_required = false
				@startSpinner('connecting_app')
				@_getPages().then(() =>
					@Growl.success(@getRegisteredMessage('connected'))
					@_stopSpinnerTimeout('connecting_app')
				, (error) =>
					@Growl.error(@getRegisteredMessage('connected_fail'))
					@_stopSpinnerTimeout('connecting_app')
				)

		selectPage: (page_id) ->
			selected_page = null
			for page in @available_user_pages
				if page.id == page_id
					selected_page = page
					break
			if selected_page
				@new_page = {
					name: selected_page.name,
					graph_id: selected_page.id,
					page_token: selected_page.access_token,
					picture_url: selected_page.picture_url,
					user_graph_id: @user_graph_id,
					user_token: @user_access_token,
					import_wall_posts: true,
					disable_own_wall_posts: true,
					import_direct_messages: true,
					is_enbled: false,
					is_connected: false,
					is_tested: false,
					app: {
						app_id: @app_id,
						app_secret: @app_secret,
						name: @app_name,
						logo_url: @app_logo_url,
						icon_url: @app_icon_url
					},
				}

				postData = {
					page: @new_page
				}

				@Api.sendPostJson('/channel/facebook/pages', postData).then((response) =>
					if response.status == 200
						@available_user_pages = @available_user_pages.filter (page) -> page.id isnt response.data.graph_id
						@new_page_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(response.data || {})
						@$scope.$parent.ChannelFacebookList.pingElement('save_page')
						@FacebookPagesData.addToList(@new_page_model.getFormData())
						@$state.go('tickets.channel_facebook.edit', { id: response.data.id })
					else
						@Growl.error(@getRegisteredMessage('connected_fail'))
						@_stopSpinnerTimeout('connecting_app')
						@app_connected = false
						@app_credentials_required = true
						@checked_for_pages = false

				).catch((data, status) =>
					@Growl.error(@getRegisteredMessage('connected_fail'))
					@_stopSpinnerTimeout('connecting_app')
					@app_connected = false
					@app_credentials_required = true
					@checked_for_pages = false
				)

		_getPages: ->
			d2 = @$q.defer()

			start = () =>
				d = @$q.defer()
				@_FBinit()
				FB.getLoginStatus((response) =>
					if response.status == 'connected'
						FB.api('/me', 'GET', {}, (me) =>
							authResponse = FB.getAuthResponse()
							@user_graph_id = authResponse.userID
							@user_access_token = authResponse.accessToken
							FB.api("/#{@user_graph_id}/permissions", 'GET', {}, (perms) =>
								pp = (p.permission for p in perms.data)
								if not "manage_pages" in pp or not "read_page_mailboxes" in pp
									@Growl.error(@getRegisteredMessage('invalid_permissions'))
									@_stopSpinnerTimeout('connecting_app')

								FB.login((response) =>
									if response.authResponse
										@user_graph_id = response.authResponse.userID
										@user_access_token = response.authResponse.accessToken
									else
										@Growl.error(@getRegisteredMessage('connected_fail'))
										@_stopSpinnerTimeout('connecting_app')
									d.resolve(@user_graph_id)
								, {
										scope: 'public_profile,manage_pages,read_page_mailboxes'
									}
								)
							)
						)
					else
						FB.login((response) =>
							if response.authResponse
								@user_graph_id = response.authResponse.userID
								@user_access_token = response.authResponse.accessToken
							else
								@Growl.error(@getRegisteredMessage('connected_fail'))
								@_stopSpinnerTimeout('connecting_app')
							d.resolve()
						, {
								scope: 'public_profile,manage_pages,read_page_mailboxes'
							}
						)
				)
				return d.promise

			start().then(() =>
				FB.api("/#{@app_id}", 'GET', {}, (res) =>
					@app_icon_url = res.icon_url
					@app_logo_url = res.logo_url
					@app_name = res.name
				)
			, (error) =>
				@Growl.error(@getRegisteredMessage('connected_fail'))
				@_stopSpinnerTimeout('connecting_app')
			).then(() =>
				FB.api("/#{@user_graph_id}/permissions", 'GET', {}, (perms) =>
					pp = (p.permission for p in perms.data)
					if "manage_pages" in pp and "read_page_mailboxes" in pp
						@checked_for_pages = true
					else
						@Growl.error(@getRegisteredMessage('invalid_permissions'))
						@_stopSpinnerTimeout('connecting_app')
				)
			).then(() =>
				FB.api("/#{@user_graph_id}/accounts", 'GET', {}, (pages) =>
					# check ot see if already a channel, dont show if so
					@app_connected = true
					@available_user_pages = []
					callfunc = (pg) =>
						d3 = @$q.defer()
						FB.api("/#{pg.id}/picture", 'GET', {}, (pinfo) =>
							@available_user_pages.push({
								id: pg.id,
								access_token: pg.access_token,
								picture_url: pinfo.data.url,
								category: pg.category,
								name: pg.name,
							})
							d3.resolve()
						)
						return d3.promise

					promises = []
					for page in pages.data
						if not @FacebookPagesData.checkExistsByGraphId(page.id)
							promises.push(callfunc(page))

					d2.resolve(@$q.all(promises))
				)
			, (error) =>
				@Growl.error(@getRegisteredMessage('connected_fail'))
				@_stopSpinnerTimeout('connecting_app')
			)


			return d2.promise

		_stopSpinnerTimeout: (name) ->
			@$timeout () =>
				@stopSpinner(name, true)
			, 0

		_FBinit: ->
			if not @fb_init
				FB.init({
					appId: @app_id,
					xfbml: true,
					version: 'v2.1'
				})

		initialLoad: ->
			return



	Admin_ChannelFacebook_Ctrl_Create.EXPORT_CTRL()
