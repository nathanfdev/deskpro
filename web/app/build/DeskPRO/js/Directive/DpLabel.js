(function() {
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
    var DeskPRO_Directive_DpLabel;
    DeskPRO_Directive_DpLabel = function($parse, LabelDefinition) {
      var getContrast;
      getContrast = function(hexcolor) {
        var b, g, r, yiq;
        hexcolor = hexcolor.toString();
        r = parseInt(hexcolor.substr(0, 2), 16);
        g = parseInt(hexcolor.substr(2, 2), 16);
        b = parseInt(hexcolor.substr(4, 2), 16);
        yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        if (yiq >= 128) {
          return 'black';
        } else {
          return 'white';
        }
      };
      return {
        restrict: 'A',
        link: function(scope, element, attr) {
          var err, label, labelParsed, updateLabelElement;
          updateLabelElement = function(data) {
            var color;
            if (data == null) {
              return;
            }
            color = data.color || null;
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
          label = {};
          if (attr.dpLabelString) {
            label.label = attr.dpLabelString;
          }
          if (attr.dpLabelType) {
            label.label_type = attr.dpLabelType;
          }
          if (attr.dpLabel && typeof attr.dpLabel === "string" && attr.dpLabel.length > 0) {
            try {
              labelParsed = $parse(attr.dpLabel)(scope);
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
            } catch (_error) {
              err = _error;
              return;
            }
          }
          if (!label.label || !label.label_type || Strings.isBlank(label.label) || Strings.isBlank(label.label_type)) {
            return;
          }
          return LabelDefinition.get(label.label_type, label.label).then((function(_this) {
            return function(def) {
              scope.$watch(def, function(newVal) {
                return updateLabelElement(newVal);
              });
              return updateLabelElement(def);
            };
          })(this));
        }
      };
    };
    return DeskPRO_Directive_DpLabel;
  });

}).call(this);

//# sourceMappingURL=DpLabel.js.map
