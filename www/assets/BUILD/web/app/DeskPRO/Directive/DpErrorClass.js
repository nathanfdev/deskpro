// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const DeskPRO_Directive_DpErrorClass = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const updateClass = function() {
          const formProp = scope.$eval(attrs.dpErrorClass);
          if (!formProp) { return; }

          let set_errorclass = false;
          if (formProp.$invalid && (formProp.$dirty || formProp.$attempted)) {
            set_errorclass = true;
          }

          if (set_errorclass) {
            return element.addClass('has-error');
          } else {
            return element.removeClass('has-error');
          }
        };

        const watch_vars = [
          attrs.dpErrorClass+'.$invalid',
          attrs.dpErrorClass+'.$dirty',
          attrs.dpErrorClass+'.$attempted'
        ];
        return scope.$watch('dpErrorClass', () =>
          Array.from(watch_vars).map((varname) =>
            scope.$watch(varname, () => updateClass()
            , true))
        );
      }
    })
  
  ];

  return DeskPRO_Directive_DpErrorClass;
});