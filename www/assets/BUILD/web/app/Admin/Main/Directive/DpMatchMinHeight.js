/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Functions'], function(Functions) {
  /*
    * Description
    * -----------
    *
    * This enables an element to "pair" itself with another element so that its height is always at least
    * as height as the other.
    *
    * Example
    * -------
    * <div dp-match-min-height watched-as="sidebar">I'm a big sidebar</div>
    * <div dp-match-min-height watch="sidebar">I will have my min-height set to the height of sidebar</div>
  */
  const Admin_Main_Directive_DpMatchMinHeight = [ '$timeout', '$interval', function($timeout, $interval) {

    const registry = {};

    return {
      restrict: 'A',
      link(scope, element, attrs) {

        const regId = attrs.watchScopeId || scope.$id;

        if (attrs.watchedAs) {
          registry[regId + "_" + attrs.watchedAs] = element;
          return scope.$on('$destroy', () => registry[attrs.watchedAs] && delete registry[regId + "_" + attrs.watchedAs]);
        } else {
          const watchId = regId + "_" + attrs.watch;
          const resize = function() {
            if ((registry[watchId] == null)) { return; }
            const h = registry[watchId].outerHeight();
            return element.css('min-height', h).addClass('with-dp-min-height');
          };

          const resizeDebounce = Functions.debounce(resize, 100, true);

          const interval = $interval( () => resize()
          , 600);

          $(window).on('resize', resizeDebounce);
          scope.$on('$destroy', function() {
            $(window).off('resize', resizeDebounce);
            return $interval.cancel(interval);
          });

          return resize();
        }
      }
      };
  }
  ];

  return Admin_Main_Directive_DpMatchMinHeight;
});