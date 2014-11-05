(function() {
  define(['moment'], function(moment) {

    /*
        * Description
        * -----------
        *
        * This converts model datetime from UTC to local timezone when rendered and back when saved
        *
     */
    var Admin_Main_Directive_DpDate;
    Admin_Main_Directive_DpDate = [
      'DpDateService', '$parse', function(ds, $parse) {
        return {
          restrict: 'A',
          scope: {
            dpDate: "=dpDate"
          },
          link: function(scope, el, attr) {
            var format, update;
            format = attr.format || "fulltime";
            update = function() {
              var datestr, result;
              datestr = scope.dpDate;
              result = null;
              if (datestr) {
                result = ds.format(datestr, format);
              }
              if (result) {
                return el.text(ds.format(datestr, format));
              } else if (datestr) {
                return el.text(datestr);
              }
            };
            update();
            return scope.$watch('dpDate', function() {
              return update();
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpDate;
  });

}).call(this);

//# sourceMappingURL=DpDate.js.map
