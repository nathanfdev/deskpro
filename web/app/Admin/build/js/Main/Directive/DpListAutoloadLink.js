(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This configures an element for "autoloading that loads once a list is loaded.
       # For example, when loading Departments, the first department in the list should be loaded
       # automatically.
       #
       # Multiple elements can be registered to autoload and ones with lower priority will be
       # selected first.
       #
       # Example
       # -------
       # <div class="some-container" dp-list-autoload="a">
       #    <a href="...">Link 1</a>
       #    <a href="...">Link 2</a>
       #    <a href="...">Link 3</a>
       # </div>
       # <a href="..." dp-list-autoload autoload-priority="2">If all above links were removed, this link would run</a>
    */

    var Admin_Main_Directive_DpListAutoload;
    Admin_Main_Directive_DpListAutoload = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var pri, select;
            if (!scope._autoload_links) {
              scope._autoload_links = [];
            }
            pri = 0;
            select = 'a';
            if (attrs.autoloadPriority) {
              pri = parseInt(attrs.autoloadPriority);
            }
            if (attrs.dpListAutoload && attrs.dpListAutoload.length) {
              select = attrs.dpListAutoload;
            }
            return scope._autoload_links.push([element, select, pri]);
          }
        };
      }
    ];
    return Admin_Main_Directive_DpListAutoload;
  });

}).call(this);

/*
//@ sourceMappingURL=DpListAutoloadLink.js.map
*/