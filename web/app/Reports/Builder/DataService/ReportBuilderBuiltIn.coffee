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
		init: ->
			@setSubLists ['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads',
																	'Feedback', 'Tasks', 'Twitter']


		###
		#
		###
		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/reports/builder/builtIn').success( (data) =>

				models = data.reports
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise


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
		loadEditReportData: (id, params) ->

			deferred = @$q.defer()
			if id

				@Api.sendGet('/reports/builder/' + id, {params: params}).then( (result) =>

					if result.data.type != 'builtIn' then throw new Error('Report you are loading should be built-in report')

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

				@Api.sendGet('/reports/builder').then( (result) =>

					data = {}
					data.report = {user: {}}
					data.form = @getFormMapper().getFormFromModel(data)

					deferred.resolve(data)
				, ->
					deferred.reject()
				)

			return deferred.promise