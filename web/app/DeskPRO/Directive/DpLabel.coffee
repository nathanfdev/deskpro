define ->
	###
    # Description
    # -----------
    #
    # This builds label template
    #
    # Example
    # -------
    # <span dp-label="myticket.labels[0]"></span>
    #
	###
	DeskPRO_Directive_DpLabel = ($parse, LabelDefinition) ->

		getContrast = (hexcolor) ->
			hexcolor = hexcolor.toString()
			r = parseInt(hexcolor.substr(0,2),16)
			g = parseInt(hexcolor.substr(2,2),16)
			b = parseInt(hexcolor.substr(4,2),16)
			yiq = ((r*299)+(g*587)+(b*114))/1000
			if (yiq >= 128) then 'black' else 'white'

		return {
			restrict: 'A'
			template: '<span></span>'
			link: (scope, element, attr) ->
				$span = element.children 'span'
				label = $parse(attr.dpLabel)(scope)

				$span.css
					display: 'inline-block'
					borderRadius: '3px'
					padding: '1% 2%'
					verticalAlign: 'middle'

				updateLabelElement = (data) ->
					return if !data?
					color = data.color || '#c8c8c8'
					$span.text data.label
					$span.css
						backgroundColor: color
						color: getContrast(color.substr(1))

				if attr.labelType?
					LabelDefinition.get($parse(attr.labelType)(scope), label.label).then (def) =>
						scope.$watch def, (newVal) =>
							updateLabelElement newVal
						updateLabelElement def
				else
					LabelDefinition.getColor(label.label).then (color) =>
						updateLabelElement {label: label.label, color: color}
		}

	return DeskPRO_Directive_DpLabel