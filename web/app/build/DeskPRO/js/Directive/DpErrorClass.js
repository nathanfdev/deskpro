(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This adds a has-error class to the element when the specified model becomes
        * invalid.
        *
        * This only applies the has-error class if:
        * * The model is invalid AND
        * * The user has changed the value, or the form the model is a part of has been submitted
        *
        * E.g., this differs from just using ng-class in that it's an easy way to only show an error
        * when the error state actually matters. Like if you just load up a form and the default
        * value of a required field is blank, we shouldn't be showing a red 'invalid' error next to it.
        *
        * Example
        * -------
        * <div dp-error-class="myform.myfield">
        *    <input type="text" ng-model="myfield" name="myfield" required />
        * </div>
     */
    var DeskPRO_Directive_DpErrorClass;
    DeskPRO_Directive_DpErrorClass = [
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
    return DeskPRO_Directive_DpErrorClass;
  });

}).call(this);

//# sourceMappingURL=DpErrorClass.js.map
