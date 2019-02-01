define(function() {
  /*
    * Description
    * -----------
    *
    * This is the body portion of dp-inhelp-switch. See that directive for more information.
    */
  const Admin_Main_Directive_DpInhelpBody = [ 'InhelpState', '$rootScope', (InhelpState, $rootScope) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const id = attrs['dpInhelpBody'].replace(/\./g, '_');

        const bodyId = `dp_inhelp_${id}`;
        element.attr('id', bodyId).addClass('inhelp-body');

        const closeBtn = angular.element('<button class="inhelp-body-closebtn"><i></i></button>');
        element.prepend(closeBtn);

        closeBtn.on('click', function(ev) {
          ev.preventDefault();
          return scope.$apply(function() {
            if (($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] : undefined)) {
              return $rootScope.dp_ctrl_inhelp_state[id] = false;
            } else {
              return ($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] = true : undefined);
            }
          });
        });

        if (($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] : undefined)) {
          return element.show();
        } else {
          return element.hide();
        }
      }
    })
  
  ];

  return Admin_Main_Directive_DpInhelpBody;
});