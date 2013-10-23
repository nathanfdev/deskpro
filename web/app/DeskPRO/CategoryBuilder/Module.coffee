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
				template: """
					<div class="dp-category-builder">
						<ul class="dp-cb-root">
							<li class="dp-cb-addrow">
								<div class="dp-cb-rowwrap">
									<input type="text" class="form-control input-sm" />
									<button class="btn btn-xs dp-cb-addbtn">Add</button>
								</div>
							</li>
						</ul>
					</div>
				""",
				replace: true,
				controller: DeskPRO_CategoryBuilder_Controller.FACTORY,
				controllerAs: 'CategoryBuilder',
				link: (scope, iElement, iAttrs, ngModel) ->
					scope.categoryBuilder.setModel(ngModel)
			}
		])
