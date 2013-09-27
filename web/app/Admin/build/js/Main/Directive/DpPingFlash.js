(function() {
  define(function() {
    var Admin_Main_Directive_DpPingFlash;
    Admin_Main_Directive_DpPingFlash = [
      function() {
        return {
          restrict: 'A',
          scope: false,
          link: function(scope, element, attrs) {
            var id;
            element.addClass('dp-ping-flash');
            id = '_ctrl_elemnt_ping.' + attrs['dpPingFlash'];
            scope.$watch(id, function(newVal) {
              if (!newVal) {
                return;
              }
              return element.stop().fadeIn(500, function() {
                return window.setTimeout(function() {
                  if (element) {
                    return element.stop().fadeOut(400);
                  }
                }, 400);
              });
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpPingFlash;
  });

}).call(this);

/*
//@ sourceMappingURL=DpPingFlash.js.map
*/