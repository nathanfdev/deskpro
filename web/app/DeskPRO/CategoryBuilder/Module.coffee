define [
	'angular',
	'DeskPRO/CategoryBuilder/Controller'
], (
	angular,
	DeskPRO_CategoryBuilder_Controller
) ->
	angular.module('deskpro.category_builder', [])
		.directive('dpCategoryBuilder', [ ->
			return {
				restrict: 'E',
				require: 'ngModel',
				template: """<div class="dp-category-builder"></div>""",
				replace: true,
				controller: DeskPRO_CategoryBuilder_Controller.FACTORY,
				controllerAs: 'CategoryBuilder',
				link: (scope, iElement, iAttrs, ngModel) ->
					scope.categoryBuilder.setModel(ngModel)
			}
	])
