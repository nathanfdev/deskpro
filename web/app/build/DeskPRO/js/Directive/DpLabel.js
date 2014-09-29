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
          var err, label, updateLabelElement;
          if (Strings.isBlank(attr.dpLabel)) {
            return;
          }
          try {
            label = $parse(attr.dpLabel)(scope);
          } catch (_error) {
            err = _error;
            return;
          }
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
            if (!data.r) {
              return element.text(data.label);
            }
          };
          if ('object' === typeof label && label.label_type) {
            return LabelDefinition.get(label.label_type, label.label).then((function(_this) {
              return function(def) {
                scope.$watch(def, function(newVal) {
                  return updateLabelElement(newVal);
                });
                return updateLabelElement(def);
              };
            })(this));
          } else {
            return LabelDefinition.getColor(attr.dpLabel).then((function(_this) {
              return function(color) {
                return updateLabelElement({
                  label: attr.dpLabel,
                  color: color,
                  r: true
                });
              };
            })(this));
          }
        }
      };
    };
    return DeskPRO_Directive_DpLabel;
  });

}).call(this);

//# sourceMappingURL=DpLabel.js.map
