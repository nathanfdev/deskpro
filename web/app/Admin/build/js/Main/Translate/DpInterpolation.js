(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var Admin_Main_Translate_DpInterpolation;
    Admin_Main_Translate_DpInterpolation = [
      function() {
        var choosePlural, regexQuote;
        choosePlural = function(text, number) {
          var parts;
          parts = text.split('|');
          if (number === 0 || number !== 1) {
            return parts[1];
          } else {
            return parts[0];
          }
        };
        regexQuote = function(strRegex) {
          return strRegex.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1");
        };
        return {
          setLocale: function(locale) {},
          getInterpolationIdentifier: function() {
            return 'dp';
          },
          interpolate: function(text, vars) {
            var is_raw, key, re, value;
            if (!vars) {
              return text;
            }
            if (vars.count_length != null) {
              vars.count = vars.count_length.length;
            }
            if (vars.count != null) {
              text = choosePlural(text, parseInt(vars.count));
            }
            is_raw = vars.as_raw != null;
            for (key in vars) {
              if (!__hasProp.call(vars, key)) continue;
              value = vars[key];
              re = new RegExp('\{\{\s*' + regexQuote(key) + '\s*\}\}', 'g');
              if (is_raw) {
                text = text.replace(re, value);
              } else {
                text = text.replace(re, _.escape(value));
              }
            }
            return text;
          }
        };
      }
    ];
    return Admin_Main_Translate_DpInterpolation;
  });

}).call(this);

/*
//@ sourceMappingURL=DpInterpolation.js.map
*/