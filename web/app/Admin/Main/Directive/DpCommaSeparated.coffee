define ->
	###
    # Description
    # -----------
    #
    # This inserts a comma between all elements in the list.
    # When used in conjunection with ng-if, it's an easy way
    # to generate a string of comma-separated elements that conditionally
    # appear.
    #
    # Example
    # -------
    # <span dp-comma-separated>
    #    <span ng-if="something1">value1</span>
    #    <span ng-if="something2">value2</span>
    #    <span ng-if="something3">value3</span>
    # </span>
	###
	Admin_Main_Directive_DpCommaSeparated = [ '$timeout', ($timeout) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				$timeout(->
					# removes appearance of any whitespace between the tags

					cleanWhitespace = (el) ->
						$(el).contents().filter(->
							if this.nodeType != 3
								cleanWhitespace(this)
								return false
							else
								this.textContent = $.trim(this.textContent)
								if not /\S/.test(this.nodeValue)
									return true
								return false
						).remove()

					cleanWhitespace(element)
					list = element.find('> *')
					list.addClass('dp-comma-list-item')
					list = list.toArray()
					list.pop()

					for el in list
						comma = $('<span class="dp-comma-list-item dp-comma">,</span>')
						comma.insertAfter(el)
				)
		}
	]

	return Admin_Main_Directive_DpCommaSeparated