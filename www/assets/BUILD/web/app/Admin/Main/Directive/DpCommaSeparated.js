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
  const Admin_Main_Directive_DpCommaSeparated = ['$timeout', $timeout =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        return $timeout(() => {
          // removes appearance of any whitespace between the tags

          var cleanWhitespace = el =>
            $(el).contents().filter(function () {
              if (this.nodeType !== 3) {
                cleanWhitespace(this);
                return false;
              }
              this.textContent = $.trim(this.textContent);
              if (!/\S/.test(this.nodeValue)) {
                return true;
              }
              return false;
            }).remove()
          ;

          cleanWhitespace(element);
          let list = element.find('> *');
          list.addClass('dp-comma-list-item');
          list = list.toArray();
          list.pop();

          return (() => {
            const result = [];
            for (const el of Array.from(list)) {
              const comma = $('<span class="dp-comma-list-item dp-comma">,</span>');
              result.push(comma.insertAfter(el));
            }
            return result;
          })();
        });
      }
    })

  ];

  return Admin_Main_Directive_DpCommaSeparated;
});
