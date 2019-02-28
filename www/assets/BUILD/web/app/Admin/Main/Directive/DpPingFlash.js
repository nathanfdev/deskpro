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
  const Admin_Main_Directive_DpPingFlash = [() =>
    ({
      restrict: 'A',
      scope:    false,
      link(scope, element, attrs) {
        element.addClass('dp-ping-flash');
        const id = `dp_ctrl_elemnt_ping.${attrs.dpPingFlash}`;

        scope.$watch(id, (newVal) => {
          if (!newVal) { return; }
          return element.stop().fadeIn(500, () =>
            window.setTimeout(() => {
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
