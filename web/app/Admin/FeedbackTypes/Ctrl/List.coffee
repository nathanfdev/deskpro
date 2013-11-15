define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackTypes_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List'
		@CTRL_AS = 'FeedbackTypesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl']

		init: ->

			@feedback_types = [];

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
						feedback_type_id = parseInt($(this).data('id'))

						if feedback_type_id

							feedback_type = em.getById('feedback_type', feedback_type_id)

							if feedback_type
								feedback_type.display_order = x

						postData.display_orders.push(feedback_type_id)
					)

					promise = @Api.sendPostJson('/feedback_types/display_order', postData)
					@pingElement('display_orders')
			}

		initialLoad: ->

			list_promise = @FeedbackTypesData.loadList().then( (recs) =>

				@feedback_types = recs.values()

				@addManagedListener(@FeedbackTypesData.recs, 'changed', =>

					@feedback_types = @FeedbackTypesData.recs.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise])

		###
  # Show the delete dlg
  ###

		startDelete: (feedback_type) ->

			move_feedback_types_list = @FeedbackTypesData.getListOfMovables(feedback_type)

			if not move_feedback_types_list.length
				@showAlert('@no_delete_last');
				return

			inst = @$modal.open({
				templateUrl: @getTemplatePath('FeedbackTypes/delete-modal.html'),
				controller: ['$scope', '$modalInstance', 'move_feedback_types_list', ($scope, $modalInstance, move_feedback_types_list) ->

					$scope.move_feedback_types_list = move_feedback_types_list
					$scope.selected = {
						move_to_id: move_feedback_types_list[0].id
					}

					$scope.confirm = ->
						$modalInstance.close($scope.selected.move_to_id);

					$scope.dismiss = ->
						$modalInstance.dismiss();
				],
				resolve: {
					move_feedback_types_list: =>
						return move_feedback_types_list
				}
			});

			inst.result.then( (move_to) =>
				@deleteFeedbackType(feedback_type, move_to)
			)

		###
		# Actually do the delete
 	# @param feedback_type - feedback type we want to delete
 	# @param move_to - to what type feedback should be moved
		###

		deleteFeedbackType: (feedback_type, move_to) ->

			@Api.sendDelete('/feedback_types/' + feedback_type.id, {
				move_to: move_to
			}).success( =>

				@FeedbackTypesData.remove(feedback_type.id)
				@ngApply()

				# if currently viewing the deleted feedback type, then should need to switch state
				if @$state.current.name == 'portal.feedback_types.edit' and parseInt(@$state.params.id) == feedback_type.id
					@$state.go('portal.feedback_types')
			)

	Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL()