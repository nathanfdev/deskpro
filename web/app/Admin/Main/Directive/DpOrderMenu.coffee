define [
	'DeskPRO/Util/Strings'
], (Strings) ->
	Admin_Main_Directive_DpOrderMenu = [ ->
		return {
			restrict: 'AE',
			replace: true,
			require: 'ngModel',
			transclude: true,
			template: """
				<span class="dp-order-ctrl dropdown">
					<div class="orig" style="display: none;" ng-transclude></div>
					<a class="title dropdown-toggle" data-toggle="dropdown">Order by: {{title}} <i class="fa fa-sort-alpha-desc" ng-show="sortDir == 'DESC'"></i><i class="fa fa-sort-alpha-asc" ng-show="sortDir == 'ASC'"></i></a>
					<ul class="dropdown-menu">
						<li class="dropdown-header">Sort Field</li>
						<li ng-repeat="opt in options"><a ng-click="$event.preventDefault(); setSortField(opt.value);">{{opt.title}} <i class="fa fa-check" ng-show="sortField == opt.value"></i></a></li>
						<li class="divider"></li>
						<li class="dropdown-header">Sort Direction</li>
						<li><a ng-click="$event.preventDefault(); setSortDirection('ASC');">Ascending <i class="fa fa-check" ng-show="sortDir == 'ASC'"></i></a></li>
						<li><a ng-click="$event.preventDefault(); setSortDirection('DESC');">Descending <i class="fa fa-check" ng-show="sortDir == 'DESC'"></i></a></li>
					</ul>
				</span>
			""",
			link: (scope, element, attrs, ngModel) ->
				scope.sortField = null
				scope.sortDir = 'ASC'
				scope.title = ''
				scope.options = []

				orderPrefs = if attrs.orderDirPrefs then scope.$eval(attrs.orderDirPrefs) else {}
				console.log(orderPrefs)

				element.find('.orig').find('option').each(->
					scope.options.push({
						title: Strings.trim($(this).text()),
						value: $(this).val()
					})
				)

				scope.setSortField = (field) ->
					scope.sortField = field
				scope.setSortDirection = (dir) ->
					scope.sortDir = dir

				ngModel.$parsers.push( (viewValue) ->
					return viewValue
				)

				ngModel.$formatters.push( (modelValue) ->
					return modelValue
				)

				ngModel.$render = ->
					scope.sortField = ngModel.$viewValue?.field || scope.options[0].value
					scope.sortDir   = ngModel.$viewValue?.dir || 'ASC'

					for v in scope.options
						if v.value == scope.sortField
							scope.title = v.title
							break

				update = ->
					ngModel.$setViewValue({
						field: scope.sortField,
						dir: scope.sortDir
					})

					for v in scope.options
						if v.value == scope.sortField
							scope.title = v.title
							break

				scope.$watch('sortField', ->
					if orderPrefs[scope.sortField]
						scope.sortDir = orderPrefs[scope.sortField]

					update()
				)

				scope.$watch('sortDir', ->
					update()
				)
		}
	]

	return Admin_Main_Directive_DpOrderMenu