(function() {
  define(['DeskPRO/Util/Functions'], function(Functions) {

    /*
        * Description
        * -----------
        *
        * This enables a calculated 'max-height' on an element based on the height of the screen.
        *
        * Example
        * -------
        * <div dp-max-height="40">...</div>
     */
    var Admin_Main_Directive_DpMaxHeight;
    Admin_Main_Directive_DpMaxHeight = [
      '$timeout', '$interval', function($timeout, $interval) {
        return {
          restrict: 'A',
          priority: -10,
          link: function(scope, element, attrs) {
            var add, interval, min, perc, resize, resizeDebounce;
            add = attrs.dpHeightAdd ? parseInt(attrs.dpHeightAdd || 0) : -100;
            min = attrs.dpMinHeight ? parseInt(attrs.dpMinHeight || 0) : 300;
            perc = parseInt(attrs.dpMaxHeight || 100) / 100;
            resize = function() {
              var setH, top, winH;
              top = element.offset().top;
              winH = $(window).height();
              setH = (Math.ceil(winH * perc) - top) + add;
              if (setH < min) {
                setH = min;
              }
              return element.css('max-height', setH);
            };
            resizeDebounce = Functions.debounce(resize, 100, true);
            interval = $interval(function() {
              return resize();
            }, 500);
            $(window).on('resize', resizeDebounce);
            scope.$on('$destroy', function() {
              $(window).off('resize', resizeDebounce);
              return $interval.cancel(interval);
            });
            resize();
            return $timeout(function() {
              resize();
              return $timeout(function() {
                return resize();
              }, $timeout(function() {
                return resize();
              }));
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpMaxHeight;
  });

}).call(this);

//# sourceMappingURL=DpMaxHeight.js.map
