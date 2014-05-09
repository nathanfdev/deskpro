(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This inserts a comma between all elements in the list.
        * When used in conjunection with ng-if, it's an easy way
        * to generate a string of comma-separated elements that conditionally
        * appear.
        *
        * Example
        * -------
        * <span dp-comma-separated>
        *    <span ng-if="something1">value1</span>
        *    <span ng-if="something2">value2</span>
        *    <span ng-if="something3">value3</span>
        * </span>
     */
    var Admin_Main_Directive_DpCommaSeparated;
    Admin_Main_Directive_DpCommaSeparated = [
      '$timeout', function($timeout) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            return $timeout(function() {
              var cleanWhitespace, comma, el, list, _i, _len, _results;
              cleanWhitespace = function(el) {
                return $(el).contents().filter(function() {
                  if (this.nodeType !== 3) {
                    cleanWhitespace(this);
                    return false;
                  } else {
                    this.textContent = $.trim(this.textContent);
                    if (!/\S/.test(this.nodeValue)) {
                      return true;
                    }
                    return false;
                  }
                }).remove();
              };
              cleanWhitespace(element);
              list = element.find('> *');
              list.addClass('dp-comma-list-item');
              list = list.toArray();
              list.pop();
              _results = [];
              for (_i = 0, _len = list.length; _i < _len; _i++) {
                el = list[_i];
                comma = $('<span class="dp-comma-list-item dp-comma">,</span>');
                _results.push(comma.insertAfter(el));
              }
              return _results;
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpCommaSeparated;
  });

}).call(this);

//# sourceMappingURL=DpCommaSeparated.js.map
