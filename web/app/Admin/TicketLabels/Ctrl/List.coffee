define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketLabels_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketLabels_Ctrl_List'
		@CTRL_AS = 'TicketLabelsList'
		@DEPS = ['$scope']
		@CTRL_TYPE = 'list'

		init: ->
			@labels = []
			@new_label = ''
			@add_mode = false
			return

		initialLoad: ->
			data_promise = @Api.sendDataGet([
				'/ticket_labels'
			]).then((res) =>
				console.log res.data.api_ticket_labels
				for label in res.data.api_ticket_labels.labels
					@labels.push(label)
			)

			return @$q.all([data_promise])

		startDelete: (label) ->
			label.delete_mode = true
			@Api.sendDelete('/ticket_labels/'+label.label)
			.success(=>
					@labels.remove(label)
				).finally(=>
				label.delete_mode = false
			)

		addNewLabel: ->
			return false if not @new_label
			@add_mode = true
			@Api.sendPost('/ticket_labels', {label: @new_label})
			.success(=>
					@labels.push({label: @new_label, count: 0})
				)
			.finally(=>
					@add_mode = false
				)


		saveLabel: (label) ->
			return false if not label.new_label
			label.save_mode = true
			@Api.sendPut('/ticket_labels', {
				label_old: label.label, label_new: label.new_label
			}).success(=>
				label.label = label.new_label
			).error(=>
				label.new_label = label.label
			).finally(=>
				label.edit_mode = false
				label.save_mode = false
			)
		
		switchSortOrder: (to) ->
			from = @$scope.order
			if from==to
				@$scope.orderReverse = !@$scope.orderReverse
			else
				@$scope.orderReverse = (to == 'label')
			
			@$scope.order = to


	Admin_TicketLabels_Ctrl_List.EXPORT_CTRL()