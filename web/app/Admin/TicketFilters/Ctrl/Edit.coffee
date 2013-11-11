define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketFilters_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketFilters_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketFilter', '$stateParams']

		init: ->
			@filterData = @DataService.get('TicketFilters')
			@filter = null

			@filter_criteria = {}
			@criteriaTypeDef = @dpObTypesDefTicketFilter
			@criteriaOptionTypes = @criteriaTypeDef.getOptionsForTypes()

		initialLoad: ->
			if @$stateParams.id
				promise = @filterData.loadEditFilterData(@$stateParams.id).then( (data) =>
					@filter = data.filter
					@form = @getFormFromModel(@filter)
				)
				return promise
			else
				@filter = {}
				@form = @getFormFromModel(@filter)
				return null

		getFormFromModel: (filterModel) ->
			form = {}
			form.title = filterModel.title || ''

			return form

	Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL()