define [
	'Admin/Main/Ctrl/Base',
	'DeskPRO/Util/Util'
], (
	Admin_Ctrl_Base,
	Util
) ->
	class Admin_TicketFilters_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_TicketFilters_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['dpObTypesDefTicketFilter', '$stateParams']

		init: ->
			@filterId = parseInt(@$stateParams.id || 0)
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

					for term in @filter.terms.terms
						rowId = Util.uid('term')
						@filter_criteria[rowId] = term
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

		saveForm: ->
			if not @$scope.form_props.$valid then return

			if @filterId
				method = 'POST'
				url = "/ticket_filters/#{@filterId}"
			else
				method = 'PUT'
				url = "/ticket_filters"

			postData = {
				filter: @form
			}
			postData.filter.terms = @filter_criteria

			@sendFormSaveApiCall(method, url, postData).then( (res) =>
				@Growl.success(@getRegisteredMessage('saved_filter'))

				@filter.title = @form.title
				if res.data.filter_id
					@filter.id = res.data.filter_id

				@filterData.mergeDataModel(@filter)

				if !@filterId
					@$state.go('tickets.ticket_filters.gocreate')
			)

	Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL()