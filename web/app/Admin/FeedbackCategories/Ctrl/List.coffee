define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackCategories_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List'
		@CTRL_AS = 'FeedbackCategoriesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->

			@feedback_categories = []
			@parent_data = []
			@child_data = {}

		initialLoad: ->

			list_promise = @FeedbackCategoriesData.loadList().then( (recs) =>

				@initHierarchyData(recs.values())

				@addManagedListener(@FeedbackCategoriesData.recs, 'changed', =>

					@initHierarchyData(@FeedbackCategoriesData.recs.values())
					@ngApply()
				)
			)

			return @$q.all([list_promise])

		initHierarchyData: (feedback_categories) ->

			@feedback_categories = feedback_categories

			for category in feedback_categories

				if category.parent_id

					if not @child_data[category.parent_id]
						@child_data[category.parent_id] = []

					@child_data[category.parent_id].push(category)

				else

					@parent_data.push(category)

	Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL()