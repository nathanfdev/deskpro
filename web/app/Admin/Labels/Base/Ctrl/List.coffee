define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Labels_Base_Ctrl_List extends Admin_Ctrl_Base
		@DEPS = ['em', '$rootScope', 'LabelManager', 'LabelDefinition']
		@CTRL_AS = 'LabelsList'

		init: ->
			@api_endpoint = ''
			@ng_route = ''
			@typename = ''

			@new_label = ''
			@add_mode = false

			@$scope.order = 'label'
			@$scope.orderReverse = false
			@$scope.labels = {}
			@$scope.countDefinitions = =>
				count = 0
				for n of @$scope.labels
					count++
				count

			@$scope.$watch('sortOrder', =>
				if not @$scope.sortOrder then return
				@$scope.order = @$scope.sortOrder.field
				@$scope.orderReverse = @$scope.sortOrder.dir == 'DESC'
			)


		initialLoad: ->
			@LabelDefinition.all(@type()).then (definitions) =>
				@$scope.labels = definitions
