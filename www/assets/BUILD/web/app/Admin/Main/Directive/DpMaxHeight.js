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
  const Admin_Main_Directive_DpMaxHeight = ['$timeout', '$interval', ($timeout, $interval) =>
    ({
      restrict: 'A',
      priority: -10,
      link(scope, element, attrs) {
        const add = attrs.dpHeightAdd ? parseInt(attrs.dpHeightAdd || 0) : -100;
        const min = attrs.dpMinHeight ? parseInt(attrs.dpMinHeight || 0) : 300;
        const perc = parseInt(attrs.dpMaxHeight || 100) / 100;

        element.addClass('with-dp-max-height');

        const resize = function () {
          const top = element.offset().top + $('.dp-layout-appbody').scrollTop();
          const winH = $(window).height();
          let setH = (Math.ceil(winH * perc) - top) + add;
          if (setH < min) { setH = min; }
          return element.css('max-height', setH);
        };

        const resizeDebounce = Functions.debounce(resize, 100, true);

        const interval = $interval(() => resize()
        , 500);

        $(window).on('resize', resizeDebounce);
        scope.$on('$destroy', () => {
          $(window).off('resize', resizeDebounce);
          return $interval.cancel(interval);
        });

        resize();
        return $timeout(() => {
          resize();
          return $timeout(
            () => resize(),
            $timeout(
              () => resize())
          );
        });
      }
    })

  ];

  return Admin_Main_Directive_DpMaxHeight;
});
