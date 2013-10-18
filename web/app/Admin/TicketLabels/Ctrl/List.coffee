define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketLabels_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketLabels_Ctrl_List'
		@CTRL_AS = 'TicketLabelsList'
		@DEPS = ['$scope', 'TicketLabelsData']
		@CTRL_TYPE = 'list'

		init: ->
			@labels = []
			@new_label = ''
			@add_mode = false
			@$scope.escape_url = (text)->
				return encodeURIComponent(text)

			console.log @$scope

			return

		initialLoad: ->
			list_promise = @TicketLabelsData.loadList().then((recs) =>
				@labels = recs
			)

			return @$q.all([list_promise]);

		startDelete: (label) ->
			label.delete_mode = true
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketLabels/delete-modal.html'),
				controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
						label.delete_mode = false
				]
			});

			inst.result.then(=>
				@deleteLabel(label)
			)

			inst.result.catch(=>
				label.delete_mode = false
			)

		deleteLabel: (label) ->
			@Api.sendDelete('/ticket_labels/'+label.label)
			.success(=>
					@TicketLabelsData.remove(label)

					# if currently viewing the deleted account, then should need to switch state
					if @$state.current.name=='tickets.labels.edit' and @$state.params.label==label.label
						@$state.go('tickets.labels')
				)
			.finally(=>
					label.delete_mode = false
				)

		switchSortOrder: (to) ->
			from = @$scope.order
			if from==to
				@$scope.orderReverse = !@$scope.orderReverse
			else
				@$scope.orderReverse = !(to=='label')

			@$scope.order = to


	Admin_TicketLabels_Ctrl_List.EXPORT_CTRL()