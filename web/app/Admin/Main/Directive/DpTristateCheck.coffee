define ->
	###
    # Description
    # -----------
    #
    # This turns a checkbox into a tri-state checkbox that reflects the state of
    # multiple models, and toggling it will toggle the watched models.
    #
    # Example
    # -------
    # <input type="checkbox" dp-tristate-check="model1, model2, model3" /> Status
    # -- <input type="checkbox" ng-model="model1" /> Sub-Checkbox 1
    # -- <input type="checkbox" ng-model="model2" /> Sub-Checkbox 2
    # -- <input type="checkbox" ng-model="model3" /> Sub-Checkbox 3
	###
	Admin_Main_Directive_TristateCheck = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				names = []
				for n in attrs['dpTristateCheck'].split(',')
					n = $.trim(n)
					names.push(n)

					scope.$watch(n, (val) ->
						updateState()
					)

				getValFromStr = (obj, str) ->
					parts = str.split('.')
					return parts.reduce( ((o, x) -> return o[x]), obj)

				setValFromStr = (obj, str, val) ->
					parts = str.split('.')
					name = parts.pop()
					obj = parts.reduce( ((o, x) -> return o[x]), obj)
					obj[name] = val

				element.on('click', ->
					val = this.checked
					scope.$apply(->
						for n in names
							setValFromStr(scope, n, val)
					)
				)

				updateState = ->
					count = 0
					for n in names
						if getValFromStr(scope, n)
							count++

					if count
						element.prop('checked', true)
						if count < names.length
							element.prop('indeterminate', true)
						else
							element.prop('indeterminate', false)
					else
						element.prop('checked', false)
						element.prop('indeterminate', false)
		}
	]

	return Admin_Main_Directive_TristateCheck