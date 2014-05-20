define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
	class Admin_TicketFields_Ctrl_EditCategories extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFields_Ctrl_EditCategories'
		@CTRL_AS = 'TicketCats'
		@DEPS    = []

		init: ->
			@cats             = []
			@default_id       = 0
			@agent_required   = false
			@user_required    = false
			@cat_parent_list  = []

			@$scope.$watchCollection('TicketCats.cats', =>
				@updateCatParentList()
			, true)
			return

		updateCatParentList: ->
			@cat_parent_list = []

			flat = Arrays.analyzeFlatCatStructure(@cats)
			valid_ids = []
			for cat in flat
				if not cat.child_ids.length
					valid_ids.push(cat.id)
					@cat_parent_list.push({
						id: cat.id,
						title: cat.full_title
					})

			if valid_ids.indexOf(@default_id) == -1
				@default_id = 0

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'info': '/ticket_cats',
				'layouts': '/ticket_layouts/fields/category'
			}).then( (res) =>
				@cats           = res.data.info.categories
				@default_id     = res.data.info.default_id
				@agent_required = res.data.info.agent_required
				@user_required  = res.data.info.user_required
				@enabled        = res.data.info.enabled

				@user_layouts  = res.data.layouts.user_layouts
				@agent_layouts = res.data.layouts.agent_layouts

				@updateCatParentList()
			)

			return data_promise

		save: ->
			postData = {
				categories:     @cats,
				default_id:     @default_id,
				user_required:  @user_required,
				agent_required: @agent_required,
				enabled:        @enabled
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/ticket_cats', postData).success( =>
				@$scope.$parent?.TicketFieldsList?.saveLayoutData('category', @user_layouts, @agent_layouts)
				@$scope.$parent?.TicketFieldsList?.setFieldEnabled('category', @enabled)
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_TicketFields_Ctrl_EditCategories.EXPORT_CTRL()