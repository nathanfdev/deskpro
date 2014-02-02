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

					data = {}
					data.report = result.data.report
					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			else

				@Api.sendGet('/reports/builder').then( (result) =>

					data = {}
					data.report = {user: {}}
					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			return deferred.promise


		###
  # Saves a form model and merges model with list data
  #
  # @param {Object} model api_key model
 	# @param {Object} formModel  The model representing the form
  # @return {promise}
		###
		saveFormModel: (model, formModel) ->

			mapper = @getFormMapper()
			postData = mapper.getPostDataFromForm(formModel)

			if model.id
				promise = @Api.sendPostJson('/reports/builder/' + model.id, {report: postData})
			else
				promise = @Api.sendPutJson('/reports/builder', {report: postData}).success( (data) ->
					model.id = data.id
				)

			promise.success(=>
				mapper.applyFormToModel(model, formModel)
				@mergeDataModel(model)
			)

			return promise