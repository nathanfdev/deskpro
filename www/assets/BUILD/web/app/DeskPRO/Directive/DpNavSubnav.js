/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const DeskPRO_Directive_DpNavSubnav = ['$rootScope', '$state', ($rootScope, $state) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const $parent = element.parent();
        const $toggler = $parent.find('> a');
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
    })
  
  ];

  return DeskPRO_Directive_DpNavSubnav;
});