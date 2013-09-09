define ['angular', 'Admin/App'], (angular, Admin_App) ->

	######################################################################################################################
  # <dp-section>
	######################################################################################################################

	Admin_App.directive('dpSection', ->
		def = {}
		def.restrict = 'E'
		def.transclude = true
		def.replace = true
		def.template = '<div class="dp-section"></div>'
		def.compile = (element, attr, linker) ->
			return (scope, iElement, iAttrs, controller) ->
				div = iElement
				linker(scope, (clone) ->
					hasList = !!clone.filter('.dp-section-list')[0]
					if hasList
						clone.filter('.dp-section-page').addClass('with-list')
					div.append(clone)
				)

				return div

		return def
	)

	######################################################################################################################
  # <dp-section-nav>
	######################################################################################################################

	Admin_App.directive('dpSectionNav', ->
		def = {}
		def.restrict = 'E'
		def.replace = true
		def.transclude = true
		def.template = '<nav class="dp-section-nav sections-nav" ng-transclude></nav>'

		return def
	)

	######################################################################################################################
  # <dp-section-list>
	######################################################################################################################

	Admin_App.directive('dpSectionList', ->
		def = {}
		def.restrict = 'E'
		def.replace = true
		def.transclude = true
		def.template = '<div class="dp-section-list" ng-transclude></div>'

		return def
	)

	######################################################################################################################
  # <dp-section-content>
	######################################################################################################################

	Admin_App.directive('dpSectionContent', ->
		def = {}
		def.restrict = 'E'
		def.replace = true
		def.transclude = true
		def.template = '<div class="dp-section-page" ng-transclude></div>'

		return def
	)

	return Admin_App