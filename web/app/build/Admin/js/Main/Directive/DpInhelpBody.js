(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This is the body portion of dp-inhelp-switch. See that directive for more information.
     */
    var Admin_Main_Directive_DpInhelpBody;
    Admin_Main_Directive_DpInhelpBody = [
      'InhelpState', '$rootScope', function(InhelpState, $rootScope) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var bodyId, closeBtn, id, _ref;
            id = attrs['dpInhelpBody'].replace(/\./g, '_');
            bodyId = 'dp_inhelp_' + id;
            element.attr('id', bodyId).addClass('inhelp-body');
            closeBtn = angular.element('<button class="inhelp-body-closebtn"><i></i></button>');
            element.prepend(closeBtn);
            closeBtn.on('click', function(ev) {
              ev.preventDefault();
              return scope.$apply(function() {
                var _ref, _ref1;
                if ((_ref = $rootScope.dp_ctrl_inhelp_state) != null ? _ref[id] : void 0) {
                  return $rootScope.dp_ctrl_inhelp_state[id] = false;
                } else {
                  return (_ref1 = $rootScope.dp_ctrl_inhelp_state) != null ? _ref1[id] = true : void 0;
                }
              });
            });
            if ((_ref = $rootScope.dp_ctrl_inhelp_state) != null ? _ref[id] : void 0) {
              return element.show();
            } else {
              return element.hide();
            }
          }
        };
      }
    ];
    return Admin_Main_Directive_DpInhelpBody;
  });

}).call(this);

//# sourceMappingURL=DpInhelpBody.js.map
