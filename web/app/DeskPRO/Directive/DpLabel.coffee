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
			link: (scope, element, attr) ->
				label = $parse(attr.dpLabel)(scope)

				updateLabelElement = (data) ->
					return if !data?
					color = data.color || '#c8c8c8'
					element.css
						backgroundColor: color
						color: getContrast color.substr(1)
						textShadow: 'none'
						backgroundImage: 'none'

					if !data.r
						element.text data.label

				# we have label object, so we can track it
				if 'object' == typeof label && label.label_type
					LabelDefinition.get(label.label_type, label.label).then (def) =>
						scope.$watch def, (newVal) =>
							updateLabelElement newVal
						updateLabelElement def

				else
					LabelDefinition.getColor(attr.dpLabel).then (color) =>
						updateLabelElement {label: attr.dpLabel, color: color, r: true}
		}

	return DeskPRO_Directive_DpLabel