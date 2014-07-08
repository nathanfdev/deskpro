define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Banning_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Banning_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS      = ['Api', '$http']

		init: ->
			@banData = @DataService.get('Bans')
			@$scope.fileUploadOptions = {url: @$http.formatApiUrl('/banning/import_emails') }
			@$scope.exportUrl = @$http.formatApiUrl('/banning/export_emails')

			@$scope.$on('fileuploaddone', (e, data) =>
				@goFirstEmailBanPage()
			)

			@$scope.$on('fileuploadfail', (e, data) =>
				# todo error handling
				@goFirstEmailBanPage()
			)

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @banData.loadList().then( (list) =>

				@list = list
				@pagination = @banData.getPagination()
				@search_phrase = @banData.getSearchPhrase()

				@initializeScopeWatching()
			)

			return promise

		###
		#	Here we watching scope 'page' variable in order to load new page of results
 	  # Reason - 'ng-change' is not working for ui-select2
		###

		initializeScopeWatching: ->

			@$scope.$watch('ListCtrl.pagination', (newVal, oldVal) =>

				ip_bans_page_old = parseInt(oldVal.ip_bans.page)
				ip_bans_page_new = parseInt(newVal.ip_bans.page)
				email_bans_page_old = parseInt(newVal.email_bans.page)
				email_bans_page_new = parseInt(oldVal.email_bans.page)

				if ip_bans_page_old == ip_bans_page_new and email_bans_page_old == email_bans_page_new
					return undefined

				if isNaN(ip_bans_page_new) and isNaN(email_bans_page_new)
					return undefined

				@reloadList(ip_bans_page_old != ip_bans_page_new, email_bans_page_old != email_bans_page_new)

			, true)

		###
 	# Reloads the lists with bans taking into current page & search phrase
 	#
		# @param {Boolean} reload_ip - whether we want to reload list with ip bans
 	# @param {Boolean} reload_email - whether we want to reload list with email bans
		###

		reloadList: (reload_ip, reload_email) ->

			@startSpinner('paginating_ip_bans') if reload_ip
			@startSpinner('paginating_email_bans') if reload_email

			@banData.refreshList().then((list) =>
				@stopSpinner('paginating_ip_bans', true) if reload_ip
				@stopSpinner('paginating_email_bans', true) if reload_email

				@list = list
				@pagination = @banData.getPagination()
			)

		###
 	#
 	###

		goNextIpBanPage: ->

			@pagination.ip_bans.page++

		###
		#
		###

		goPrevIpBanPage: ->

			@pagination.ip_bans.page--

		###
		#
		###

		goFirstIpBanPage: ->

			@pagination.ip_bans.page = 0

		###
 	  #
 	  ###

		goNextEmailBanPage: ->

			@pagination.email_bans.page++

		###
		#
		###

		goPrevEmailBanPage: ->

			@pagination.email_bans.page--

		###
		#
		###

		goFirstEmailBanPage: ->

			@pagination.email_bans.page = 0

		###
		# Show the delete dlg
		###

		startDelete: (for_ban_id) ->

			key = @banData.findListModelById(for_ban_id)

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Banning/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then(=>
				@deleteBan(key)
			)

		###
		# Actually do the delete
		###

		deleteBan: (for_ban) ->

			if for_ban.banned_ip then key = 'ip'
			if for_ban.banned_email then key = 'email'

			@banData.deleteBanById(for_ban['banned_' + key]).success( =>

				if @$state.current.name == ('crm.banning.edit_' + key) and @$state.params.ban == for_ban['banned_' + key]
					@$state.go('crm.banning')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

		###
		# Show the delete dlg
		###

		deleteList: (list) ->

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Banning/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.multiple = true
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then(=>
				if list == @list.email_bans
					@banData.deleteBanByType('email').then( =>
						@reloadList(false, true)
						@$state.go('crm.banning')
					)
				else
					@banData.deleteBanByType('ip').then( =>
						@reloadList(true)
						@$state.go('crm.banning')
					)
			)

	Admin_Banning_Ctrl_List.EXPORT_CTRL()