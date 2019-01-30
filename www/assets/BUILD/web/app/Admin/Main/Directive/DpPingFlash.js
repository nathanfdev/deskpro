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
  const Admin_Main_Directive_DpPingFlash = [ () =>
    ({
      restrict: 'A',
      scope: false,
      link(scope, element, attrs) {
        element.addClass('dp-ping-flash');
        const id = `dp_ctrl_elemnt_ping.${attrs['dpPingFlash']}`;

        scope.$watch(id, function(newVal) {
          if (!newVal) { return; }
          return element.stop().fadeIn(500, () =>
            window.setTimeout(function() {
              if (element) {
                return element.stop().fadeOut(400);
              }
            }
            , 400)
          );
        });
      }
    })
  
  ];

  return Admin_Main_Directive_DpPingFlash;
});