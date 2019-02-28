define(['DeskPRO/Util/Numbers', 'perfect-scrollbar'], function(Numbers) {
  const DeskPRO_Directive_DpScrollable = ['$timeout', '$interval', ($timeout, $interval) =>
    ({
      restrict: 'A',
      link(scope, $el, attrs) {
        let hasSetup = false;
        let updateInterval = null;

        const getOpts = function () {
          const opt = {};
          for (const i of [
            'wheelSpeed', 'wheelPropagation', 'minScrollbarLength', 'useBothWheelAxes',
            'useKeyboard', 'suppressScrollX', 'suppressScrollY', 'scrollXMarginOffset',
            'scrollYMarginOffset', 'includePadding'
          ]) {
            if (attrs[i] != null) {
              opt[i] = attrs[i];
              if (Numbers.isNumeric(opt[i])) {
                opt[i] = Numbers.parseNumber(attrs[i]);
              } else if ((opt[i] === '1') || (opt[i] === 'on') || (opt[i] === 'yes')) {
                opt[i] = true;
              } else if ((opt[i] === '0') || (opt[i] === 'off') || (opt[i] === 'no')) {
                opt[i] = false;
              }
            }
          }

          return opt;
        };

        const update = function () {
          if (!hasSetup) { return; }
          return $el.perfectScrollbar('update');
        };

        const setup = function () {
          if (hasSetup) { return; }
          $el.perfectScrollbar(getOpts());
          if (attrs.autoUpdate && (attrs.autoUpdate !== '0') && (attrs.autoUpdate !== 'false') && (attrs.autoUpdate !== 'no')) {
            updateInterval = $interval(update, 350);
          }
          return hasSetup = true;
        };

        $timeout(setup);

        return scope.$on('$destroy', () => {
          if (hasSetup) {
            $el.perfectScrollbar('destroy');
            if (updateInterval) {
              return $interval.cancel(updateInterval);
            }
          }
        });
      }
    })

  ];

  return DeskPRO_Directive_DpScrollable;
});
