define ->
	Admin_Main_Directive_BgImg = [ ->
		return {
			restrict: 'A',
			scope: {
				'bgImg': '&'
			},
			link: (scope, element, attrs) ->
				element.css({
					'background-image': 'url("' + scope.$eval(scope.bgImg) + '")'
				})
		}
	]

	return Admin_Main_Directive_BgImg