define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackCategories_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List'
		@CTRL_AS = 'FeedbackCategoriesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl']

		init: ->

			@feedback_categories = []
			@parent_data = []
			@child_data = {}

			@sortedListOptions = {

				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {display_orders: []}

					x = 0
					em = @em

					$list.find('li').each(->

						x += 10
						feedback_category_id = parseInt($(this).data('id'))

						if feedback_category_id

							feedback_category = em.getById('feedback_category', feedback_category_id)

							if feedback_category
								feedback_category.display_order = x

						postData.display_orders.push(feedback_category_id)
					)

					@Api.sendPostJson('/feedback_categories/display_order', postData)
					@pingElement('display_orders')
			}

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
			@parent_data = []
			@child_data = {}

			for category in feedback_categories

				if parseInt(category.parent_id, 10)

					if not @child_data[category.parent_id]
						@child_data[category.parent_id] = []

					@child_data[category.parent_id].push(category)

				else

					@parent_data.push(category)

		###
		# Show the delete dlg
		###

		startDelete: (feedback_category) ->



			if @FeedbackCategoriesData.hasChildren(feedback_category)
				@showAlert("You cannot delete a category with sub-categories. Move or delete the sub-categories first.")
				return

			move_feedback_categories_list = @FeedbackCategoriesData.getListOfMovables(feedback_category)

			inst = @$modal.open({
				templateUrl: @getTemplatePath('FeedbackCategories/delete-modal.html'),
				controller: ['$scope', '$modalInstance', 'move_feedback_categories_list', ($scope, $modalInstance, move_feedback_categories_list) ->


					$scope.move_feedback_categories_list = move_feedback_categories_list
					$scope.selected = {
						move_to_id: if move_feedback_categories_list.length then move_feedback_categories_list[0].id else ''
					}

					$scope.confirm = ->
						$modalInstance.close($scope.selected.move_to_id);

					$scope.dismiss = ->
						$modalInstance.dismiss();
				],
				resolve: {
					move_feedback_categories_list: =>
						return move_feedback_categories_list
				}
			});

			inst.result.then((move_to) =>
				@deleteFeedbackCategory(feedback_category, move_to)
			)

		###
		# Actually do the delete
		###

		deleteFeedbackCategory: (feedback_category, move_to) ->

			@Api.sendDelete('/feedback_categories/' + feedback_category.id, {
				move_to: move_to
			}).success(=>

				@FeedbackCategoriesData.remove(feedback_category.id)
				@ngApply()

				# if currently viewing the deleted model, then should need to switch state

				if @$state.current.name == 'portal.feedback_categories.edit' and parseInt(@$state.params.id) == feedback_category.id
					@$state.go('portal.feedback_categories')
			)

	Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL()