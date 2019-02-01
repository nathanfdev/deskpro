define(['DeskPRO/Util/Strings'], function(Strings) {
  /*
    * Description
    * -----------
    *
    * This builds label template
    *
    * Example
    * -------
    * <span dp-label="myticket.labels[0]"></span>
    *
  */
  const DeskPRO_Directive_DpLabel = function($parse, LabelDefinition) {

    const getContrast = function(hexcolor) {
      hexcolor = hexcolor.toString();
      const r = parseInt(hexcolor.substr(0,2),16);
      const g = parseInt(hexcolor.substr(2,2),16);
      const b = parseInt(hexcolor.substr(4,2),16);
      const yiq = ((r*299)+(g*587)+(b*114))/1000;
      if (yiq >= 128) { return 'black'; } else { return 'white'; }
    };

    return {
      restrict: 'A',
      link(scope, element, attr) {
        const updateLabelElement = function(data) {
          if ((data == null)) { return; }
          const color = data.color || null;
          if (color) {
            element.css({
              backgroundColor: color,
              color: getContrast(color.substr(1)),
              textShadow: 'none',
              backgroundImage: 'none'
            });
          } else {
            element.css({
              backgroundColor: '',
              color: '',
              textShadow: 'none',
              backgroundImage: 'none'
            });
          }
          if (label.gen) {
            return element.text(label.label);
          }
        };

        var label = {};

        if (attr.dpLabelString) {
          label.label = attr.dpLabelString;
        }
        if (attr.dpLabelType) {
          label.label_type = attr.dpLabelType;
        }

        if (attr.dpLabel && (typeof attr.dpLabel === "string") && (attr.dpLabel.length > 0)) {
          try {
            const labelParsed = $parse(attr.dpLabel)(scope);
            if (labelParsed) {
              if (labelParsed.label != null) {
                label.label = labelParsed.label;
              }
              if (labelParsed.label_type != null) {
                label.label_type = labelParsed.label_type;
              }
              if (typeof labelParsed === "string") {
                label.label = labelParsed;
              }

              label.gen = true;
              element.text(label.label);
            }
          } catch (err) {
            return;
          }
        }


        if (!label.label || !label.label_type || Strings.isBlank(label.label) || Strings.isBlank(label.label_type)) { return; }

        return LabelDefinition.get(label.label_type, label.label).then(def => {
          scope.$watch(def, newVal => {
            return updateLabelElement(newVal);
          });
          return updateLabelElement(def);
        });
      }
    };
  };

  return DeskPRO_Directive_DpLabel;
});