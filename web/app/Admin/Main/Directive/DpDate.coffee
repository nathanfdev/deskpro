define ['moment'], (moment)->
	###
    # Description
    # -----------
    #
    # This converts model datetime from UTC to local timezone when rendered and back when saved
    #
    ###
	Admin_Main_Directive_DpDate = ['DpDateService', '$parse', (ds, $parse) ->
		return {
			restrict: 'A'
			link: (scope, el, attr) ->
				c = $parse(attr.dpDate)(scope)
				format = attr.format
				el.text(ds.format c, format)
		}
	]

	return Admin_Main_Directive_DpDate