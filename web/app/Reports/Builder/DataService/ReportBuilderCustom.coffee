define [
	'Admin/Main/DataService/BaseListEdit',
	'Reports/Builder/ReportEditFormMapper'
], (
	BaseListEdit,
	ReportEditFormMapper
)  ->
	class ReportBuilderCustom extends BaseListEdit
		@$inject = ['Api', '$q']


		###
		#
		###
		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/reports/builder/custom').success( (data) =>

				models = data.reports
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise


		###
  # Remove a model
  #
  # @param {Integer} id
  # @return {promise}
		###
		deleteReportById: (id) ->
			promise = @Api.sendDelete('/reports/builder/' + id).success( =>
				@removeListModelById(id)
			)
			return promise


		###
		# Get the form mapper
		#
		# @return {ReportEditFormMapper}
		###
		getFormMapper: ->

			if @formMapper then return @formMapper
			@formMapper = new ReportEditFormMapper()
			return @formMapper

		###
  # Get all data needed for the edit page
  #
  # @param {Integer} id
  # @return {promise}
		###
		loadEditReportData: (id) ->

			deferred = @$q.defer()
			if id

				@Api.sendGet('/reports/builder/' + id).then( (result) =>

					if result.data.type != 'custom' then throw new Error('Report you are loading should be custom report')

					data = {}
					data.report = result.data.report
					data.rendered_result = result.data.rendered_result
					data.query_parts = result.data.query_parts
					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			else

				data = {}
				data.report = {
					title: ''
					description: ''
					is_custom: true
				}
				data.rendered_result = ''
				data.query_parts = {}
				data.form = @getFormMapper().getFormFromModel(data)

				deferred.resolve(data)

			return deferred.promise


		###
  # Saves a form model and merges model with list data
  #
  # @param {Object} model report model
 	# @param {Object} formModel  The model representing the form
 	# @param {Object} queryParts object that is used for storing query builder data
  # @return {promise}
		###
		saveFormModel: (model, formModel, queryParts) ->

			mapper = @getFormMapper()
			postData = mapper.getPostDataFromForm(formModel)

			if model.id
				promise = @Api.sendPostJson('/reports/builder/' + model.id, {report: postData, parts: queryParts})
			else
				promise = @Api.sendPutJson('/reports/builder', {report: postData, parts: queryParts}).success( (data) ->
					model.id = data.id
				)

			promise.success(=>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise