define ['DeskPRO/Util/Strings'], (Strings) ->
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
        updateLabelElement = (data) ->
          return if !data?
          color = data.color || null
          if color
            element.css
              backgroundColor: color
              color: getContrast color.substr(1)
              textShadow: 'none'
              backgroundImage: 'none'
          else
            element.css
              backgroundColor: ''
              color: ''
              textShadow: 'none'
              backgroundImage: 'none'
          if label.gen
            element.text(label.label)

        label = {}

        if attr.dpLabelString
          label.label = attr.dpLabelString
        if attr.dpLabelType
          label.label_type = attr.dpLabelType

        if attr.dpLabel and typeof attr.dpLabel == "string" and attr.dpLabel.length > 0
          try
            labelParsed = $parse(attr.dpLabel)(scope)
            if labelParsed
              if labelParsed.label?
                label.label = labelParsed.label
              if labelParsed.label_type?
                label.label_type = labelParsed.label_type
              if typeof labelParsed == "string"
                label.label = labelParsed

              label.gen = true
              element.text(label.label)
          catch err
            return


        return if !label.label || !label.label_type || Strings.isBlank(label.label) || Strings.isBlank(label.label_type)

        LabelDefinition.get(label.label_type, label.label).then (def) =>
          scope.$watch def, (newVal) =>
            updateLabelElement newVal
          updateLabelElement def
    }

  return DeskPRO_Directive_DpLabel