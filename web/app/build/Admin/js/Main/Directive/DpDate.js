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
          link: function(scope, el, attr) {
            var c, format;
            c = $parse(attr.dpDate)(scope);
            format = attr.format;
            return el.text(ds.format(c, format));
          }
        };
      }
    ];
    return Admin_Main_Directive_DpDate;
  });

}).call(this);

//# sourceMappingURL=DpDate.js.map
