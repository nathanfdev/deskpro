define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Labels_Base_Ctrl_List extends Admin_Ctrl_Base
		@DEPS = ['em', '$rootScope', 'LabelManager']
		@CTRL_AS = 'LabelsList'

		init: ->
			@api_endpoint = ''
			@ng_route = ''
			@typename = ''

			@labels = []
			@new_label = ''
			@add_mode = false

			@$scope.order = 'label'
			@$scope.orderReverse = false

			@$rootScope.$on("#{@api_endpoint}_new", (rec) =>
				if @labels.indexOf(rec) == -1
					@labels.push(rec)
			)

			@$scope.$watch('sortOrder', =>
				if not @$scope.sortOrder then return
				@$scope.order = @$scope.sortOrder.field
				@$scope.orderReverse = @$scope.sortOrder.dir == 'DESC'
			)


		initialLoad: ->
			promise = @LabelManager.loadLabels(@api_endpoint).then( (labels) =>
				@labels = labels
			)
			return promise;

		startDelete: (label) ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('Labels/delete-modal.html'),
				controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then(=>
				@deleteLabel(label)
			)

			inst.result.catch(=>

			)

		deleteLabel: (label) ->
			@LabelManager.removeLabel(@api_endpoint, label)
			@Api.sendDelete(@api_endpoint, {label: label}).success(=>
				if @$state.current.name== "#{@ng_route}.edit" and @$state.params.label==label
					@$state.go(@ng_route)
			).finally(=>

			)