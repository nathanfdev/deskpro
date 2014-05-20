define ->
	###
    # Description
    # -----------
    #
    # This configures an element for "autoloading that loads once a list is loaded.
    # For example, when loading Departments, the first department in the list should be loaded
    # automatically.
    #
    # Multiple elements can be registered to autoload and ones with lower priority will be
    # selected first.
    #
    # Example
    # -------
    # <div class="some-container" dp-list-autoload="a">
    #    <a href="...">Link 1</a>
    #    <a href="...">Link 2</a>
    #    <a href="...">Link 3</a>
    # </div>
    # <a href="..." dp-list-autoload autoload-priority="2">If all above links were removed, this link would run</a>
	###
	Admin_Main_Directive_DpListAutoload = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				if not scope._autoload_links
					scope._autoload_links = []

				pri = 0
				select = null
				if attrs.autoloadPriority
					pri = parseInt(scope.$eval(attrs.autoloadPriority))
				if attrs.dpListAutoload and attrs.dpListAutoload.length
					select = attrs.dpListAutoload

				if not select and not element.is('a')
					select = 'a'

				scope._autoload_links.push({
					element: element,
					select:  select || 'a',
					pri:     pri
				})
		}
	]

	return Admin_Main_Directive_DpListAutoload