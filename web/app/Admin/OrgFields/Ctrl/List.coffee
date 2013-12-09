define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_OrgFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_OrgFields_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS    = []

		###
 	#
		###

		init: ->
			@chat_fields = @DataService.get('OrgFields')
			@custom_fields = []
			return

		###
 	#
		###

		initialLoad: ->
			promise = @chat_fields.loadList()
			promise.then( (list) =>
				@custom_fields = list
			)

			return promise

		###
 	#
		###

		updateCustomFieldEnabledState: (field) ->

			if field.is_enabled
				val = '1'
			else
				val = '0'

			@Api.sendPost('/org_fields/set-enabled/field_' + field.id + '/' + val)



	Admin_OrgFields_Ctrl_List.EXPORT_CTRL()