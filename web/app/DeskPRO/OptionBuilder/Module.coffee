define [
	'angular',
	'DeskPRO/OptionBuilder/Controller'
], (
	angular,
	DeskPRO_OptionBuilder_Controller
) ->
	angular.module('deskpro.option_builder', [])
		.directive('dpOptionBuilder', [ ->
			return {
				restrict: 'E',
				require: 'ngModel',
				templateUrl: DP_BASE_ADMIN_URL+'/load-view/OptionBuilder/control.html',
				replace: true,
				transclude: true,
				controller: DeskPRO_OptionBuilder_Controller.FACTORY,
				controllerAs: 'OptionBuilder',
				scope: {
					getTypesDef: '&typesDef',
					getOptions: '&options'
				}
			}
		])
		.directive('dpOptionbuilderRow', [ ->
			return {
				restrict: 'E',
				template: """
					<div class="dp-ob-row">
						<div class="remove-row-trigger"><i class="icon-remove-sign"></i></div>
						<div class="dp-ob-row-content" ng-transclude></div>
					</div>
				""",
				replace: true,
				transclude: true
			}
		])