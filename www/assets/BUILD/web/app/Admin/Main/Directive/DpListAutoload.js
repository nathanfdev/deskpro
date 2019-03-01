define(function() {
  /*
    * Description
    * -----------
    *
    * This configures an element for "autoloading that loads once a list is loaded.
    * For example, when loading Departments, the first department in the list should be loaded
    * automatically.
    *
    * Multiple elements can be registered to autoload and ones with lower priority will be
    * selected first.
    *
    * Example
    * -------
    * <div class="some-container" dp-list-autoload="a">
    *    <a href="...">Link 1</a>
    *    <a href="...">Link 2</a>
    *    <a href="...">Link 3</a>
    * </div>
    * <a href="..." dp-list-autoload autoload-priority="2">If all above links were removed, this link would run</a>
  */
  const Admin_Main_Directive_DpListAutoload = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        if (!scope._autoload_links) {
          scope._autoload_links = [];
        }

        let pri = 0;
        let select = null;
        if (attrs.autoloadPriority) {
          pri = parseInt(scope.$eval(attrs.autoloadPriority));
        }
        if (attrs.dpListAutoload && attrs.dpListAutoload.length) {
          select = attrs.dpListAutoload;
        }

        if (!select && !element.is('a')) {
          select = 'a';
        }

        return scope._autoload_links.push({
          element,
          select: select || 'a',
          pri
        });
      }
    })

  ];

  return Admin_Main_Directive_DpListAutoload;
});
