define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Languages_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Languages_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@$scope.isInstalled = (pack) -> return pack.is_installed
			@$scope.notInstalled = (pack) -> return !pack.is_installed

		initialLoad: ->
			promise = @Api.sendGet('/langs').then( (result) =>
				@packs = result.data.packs
				@installedPacks = []
				@availablePacks = []

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
				pack.flag_image = DP_ASSET_URL + '/images/flags/' + pack.flag

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
			promise = @Api.sendGet("/langs/#{pack_id}/install").then( (result) =>
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
			promise = @Api.sendGet("/langs/#{id}/uninstall").then( (result) =>
				pack = @_getPackByPackId(result.data.old_pack_id)
				pack.is_installed = false
				@resortPacks()
			)

			return promise

	Admin_Languages_Ctrl_List.EXPORT_CTRL()