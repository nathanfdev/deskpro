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
				postData = {
					app: {
						id: @app_id,
						name: @app_name,
						secret: @app_secret,
						logo_url: @app_logo_url,
						icon_url: @app_icon_url
					},
					page: {
						id: selected_page.id,
						access_token: selected_page.access_token,
						picture_url: selected_page.picture_url,
						catgory: selected_page.category,
						name: selected_page.name
					}
				}
				console.log "POST DATA HERE:"
				console.log postData

		_getPages: ->
			d2 = @$q.defer()

			start = () =>
				d = @$q.defer()
				@_FBinit()
				FB.getLoginStatus((response) =>
					if response.status == 'connected'
						FB.api('/me', 'GET', {}, (me) =>
							@user_graph_id = me.id
							d.resolve(@user_graph_id)
						)
					else
						@_login()
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
					if "manage_pages" in pp
						@checked_for_pages = true
					else
						@Growl.error(@getRegisteredMessage('invalid_permissions'))
						@_stopSpinnerTimeout('connecting_app')
				)
			).then(() =>
				FB.api("/#{@user_graph_id}/accounts", 'GET', {}, (pages) =>
					@app_connected = true
					@available_user_pages = []
					callfunc = (pg) =>
						d3 = @$q.defer()
						FB.api("/#{pg.id}/picture", 'GET', {}, (pinfo) =>
							@available_user_pages.push({
								id: pg.id,
								access_token: pg.access_token,
								picture_url: pinfo.data.url,
								catgory: pg.category,
								name: pg.name,
							})
							d3.resolve()
						)
						return d3.promise

					promises = []
					for page in pages.data
						promises.push(callfunc(page))

					d2.resolve(@$q.all(promises))
				)
			, (error) =>
				@Growl.error(@getRegisteredMessage('connected_fail'))
				@_stopSpinnerTimeout('connecting_app')
			)


			return d2.promise

		_login: ->
			FB.login((response) =>
				if response.authResponse
					@user_graph_id = response.authResponse.userID
					@user_access_token = response.authResponse.accessToken
				else
					@Growl.error(@getRegisteredMessage('connected_fail'))
					@_stopSpinnerTimeout('connecting_app')
			, {
					scope: 'public_profile,manage_pages'
				}
			)

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
