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
					getOptions:  '&options',
					optionTypes: '=optionTypes'
				}
			}
		])
		.directive('dpOptionbuilderRow', [ ->
			return {
				restrict: 'E',
				template: """
					<div class="dp-ob-row">
						<div class="remove-row-trigger"><i class="icon-remove-sign"></i></div>
						<table cellspacing="0" cellpadding="0" width="100%" style="margin: 0; padding: 0; border: none;">
							<tr>
								<td style="vertical-align: middle; padding: 0; margin: 0;"><div class="dp-ob-row-tag-wrap"></div></td>
								<td style="vertical-align: middle; padding: 0; margin: 0;" width="100%">
									<div class="dp-ob-row-content" ng-transclude></div>
								</td>
							</tr>
						</table>
					</div>
				""",
				replace: true,
				transclude: true,
				link: (scope, element, attrs) ->
					if scope.tag?
						tag = $('<em class="dp-ob-row-tag"></em>').addClass(scope.tag).text(scope.tag)
						tag.prependTo(element.find('.dp-ob-row-tag-wrap').addClass('with-tag'))
			}
		]).directive('dpOptionBuilderSet', [ '$compile', '$templateCache', ($compile, $templateCache) ->
			return {
			restrict: 'A',
			link: (scope, iElement, iAttrs) ->
				opts = scope.$eval(iAttrs.dpOptionBuilderSet)

				addRow = ->
					containRow = iElement.find('.dp-ob-addition-setrow')

					setId = _.uniqueId('set')
					opts.setsObject[setId] = {}

					tpl = $templateCache.get(opts.template)
					rowScope = scope.$new()
					rowScope.criteria_typedef = opts.typedef
					rowScope.criteria_set_row = opts.setsObject[setId]
					rowScope.option_types     = opts.option_types

					element = $compile(tpl)(rowScope)

					element.find('.removerow_btn').on('click', (ev) ->
						ev.preventDefault()
						rowScope.$destroy()
						element.slideUp(200, ->
							element.remove()
						)
					)

					containRow.append(element)

				iElement.find('.add_btn').on('click', (ev) ->
					ev.preventDefault()
					addRow()
				)

				addRow()
			}
		])