(function() {
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
    var Admin_Main_Directive_DpMatchMinHeight;
    Admin_Main_Directive_DpMatchMinHeight = [
      '$timeout', '$interval', function($timeout, $interval) {
        var registry;
        registry = {};
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var interval, regId, resize, resizeDebounce, watchId;
            regId = attrs.watchScopeId || scope.$id;
            if (attrs.watchedAs) {
              registry[regId + "_" + attrs.watchedAs] = element;
              return scope.$on('$destroy', function() {
                return registry[attrs.watchedAs] && delete registry[regId + "_" + attrs.watchedAs];
              });
            } else {
              watchId = regId + "_" + attrs.watch;
              resize = function() {
                var h;
                if (registry[watchId] == null) {
                  return;
                }
                h = registry[watchId].height();
                return element.css('min-height', h).addClass('with-dp-min-height');
              };
              resizeDebounce = Functions.debounce(resize, 100, true);
              interval = $interval(function() {
                return resize();
              }, 600);
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

}).call(this);

//# sourceMappingURL=DpMatchMinHeight.js.map
