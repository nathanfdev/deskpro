(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This should be applied to a sub-nav list. It will attach a click handler to the
        * parent that toggles the sub-nav's visibility.
        *
        * Example
        * -------
        * <ul>
        *     <li>
        *         <a>Parent Option</a>
    	 *         <ul dp-nav-subnav>
        *            <li><a>Sub Option</a><li>
        *         </ul>
        *     </li>
        * </ul>
     */
    var DeskPRO_Directive_DpNavSubnav;
    DeskPRO_Directive_DpNavSubnav = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var $parent, $toggler;
            $parent = element.parent();
            $toggler = $parent.find('> a');
            $toggler.on('click', function(ev) {
              ev.preventDefault();
              ev.stopPropagation();
              if ($parent.hasClass('sublist-open')) {
                $parent.removeClass('sublist-open');
                return element.slideUp();
              } else {
                $parent.addClass('sublist-open');
                return element.slideDown();
              }
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpNavSubnav;
  });

}).call(this);

//# sourceMappingURL=DpNavSubnav.js.map
