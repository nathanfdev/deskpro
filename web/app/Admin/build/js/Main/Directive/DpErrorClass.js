(function() {
  define(function() {
    var Admin_Main_Directive_DpErrorClass;
    Admin_Main_Directive_DpErrorClass = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var updateClass, watch_vars;
            updateClass = function() {
              var formProp, set_errorclass;
              formProp = scope.$eval(attrs.dpErrorClass);
              if (!formProp) {
                return;
              }
              set_errorclass = false;
              if (formProp.$invalid && (formProp.$dirty || formProp.$attempted)) {
                set_errorclass = true;
              }
              if (set_errorclass) {
                return element.addClass('has-error');
              } else {
                return element.removeClass('has-error');
              }
            };
            watch_vars = [attrs.dpErrorClass + '.$invalid', attrs.dpErrorClass + '.$dirty', attrs.dpErrorClass + '.$attempted'];
            return scope.$watch('dpErrorClass', function() {
              var varname, _i, _len, _results;
              _results = [];
              for (_i = 0, _len = watch_vars.length; _i < _len; _i++) {
                varname = watch_vars[_i];
                _results.push(scope.$watch(varname, function() {
                  return updateClass();
                }, true));
              }
              return _results;
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpErrorClass;
  });

}).call(this);

/*
//@ sourceMappingURL=DpErrorClass.js.map
*/