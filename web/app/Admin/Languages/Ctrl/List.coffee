define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Languages_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Languages_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@$scope.isInstalled = (pack) -> return pack.is_installed
			@$scope.notInstalled = (pack) -> return !pack.is_installed
			@default_lang_id = null

			@$scope.$watch('ListCtrl.default_lang_id', (oldVal, newVal) =>
				if oldVal && newVal && parseInt(oldVal) != parseInt(newVal)
					@updateDefaultLang()
			)

		initialLoad: ->
			promise = @Api.sendGet('/langs').then( (result) =>
				@packs = result.data.packs
				@installedPacks = []
				@availablePacks = []
				@default_lang_id = result.data.default_lang_id

				@resortPacks()
			)
			return promise

		###
    	# Fetch a pack from its packId
    	#
    	# @param {String} packId
    	# @return {Object}
    	###
		_getPackByPackId: (packId) ->
			for pack in @packs
				if pack.id == packId
					return pack
			return null


		###
		# Resort packs into installed/available lists
    	###
		resortPacks: ->
			@installedPacks = []
			@availablePacks = []

			for pack in @packs
				pack.flag_image = DP_ASSET_URL + '/images/flags/' + pack.show_flag

				if pack.is_installed
					@installedPacks.push(pack)
				else
					@availablePacks.push(pack)


		###
    	# Install a language by pack_id
    	#
		# @return promise
		###
		installLang: (pack_id) ->
			promise = @Api.sendPost("/langs/#{pack_id}/install").then( (result) =>
				pack = @_getPackByPackId(result.data.pack_id)
				pack.is_installed = true
				@resortPacks()
			)

			return promise


		###
    	# Uninstall a language by language_id or pack_id
    	#
    	# @return promise
		###
		uninstallLang: (id) ->
			promise = @Api.sendPost("/langs/#{id}/uninstall").then( (result) =>
				pack = @_getPackByPackId(result.data.old_pack_id)
				pack.is_installed = false
				@resortPacks()
			)

			return promise

		###
    	# Save a language
    	#
    	# @return promise
		###
		saveLanguage: (id, details) ->
			pack = @_getPackByPackId(id)

			postData = {
				language: details
			}

			promise = @Api.sendPostJson("/langs/#{id}", postData).then( (result) =>
				pack.show_title = details.title
				pack.locale     = details.locale
				pack.show_flag  = details.flag_image
				@resortPacks()
			)

			return promise

		updateDefaultLang: ->
			if @default_lang_id and @hasLoaded()
				@startSpinner('saving_default_lang')
				@Api.sendPost("/langs/#{@default_lang_id}/set-default").then(=>
					@stopSpinner('saving_default_lang')
				)


	Admin_Languages_Ctrl_List.EXPORT_CTRL()