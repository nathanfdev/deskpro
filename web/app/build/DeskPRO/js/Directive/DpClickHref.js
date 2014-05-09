(function() {
  define(function() {
    var DeskPRO_Directive_DpClickHref;
    DeskPRO_Directive_DpClickHref = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var clickHref;
            clickHref = attrs['dpClickHref'];
            return element.on('click', function(ev) {
              if (ev.which === 1 && !(ev.shiftKey || ev.altKey || ev.metaKey || ev.ctrlKey)) {
                ev.preventDefault();
                return $('a').attr('href', clickHref).click();
              }
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpClickHref;
  });

}).call(this);

//# sourceMappingURL=DpClickHref.js.map
