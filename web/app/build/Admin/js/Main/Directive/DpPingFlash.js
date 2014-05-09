(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This adds an element that can be flashed from the controller when it is pinged.
        *
        * Example View
        * ------------
        * <span dp-ping-flash="order_saved">Saved</button>
        *
        * Example Controller
        * ------------------
        * someAction: ->
        *     @pingElement('order_saved')
        *
        * @see Admin_Main_Ctrl_Base.pingElement()
     */
    var Admin_Main_Directive_DpPingFlash;
    Admin_Main_Directive_DpPingFlash = [
      function() {
        return {
          restrict: 'A',
          scope: false,
          link: function(scope, element, attrs) {
            var id;
            element.addClass('dp-ping-flash');
            id = 'dp_ctrl_elemnt_ping.' + attrs['dpPingFlash'];
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

//# sourceMappingURL=DpPingFlash.js.map
