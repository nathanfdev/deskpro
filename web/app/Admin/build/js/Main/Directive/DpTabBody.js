(function() {
  define(function() {
    var Admin_Main_Directive_DpTabBody;
    Admin_Main_Directive_DpTabBody = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var id_segs, tab_group, tab_val;
            if (!scope.dp_tab_ids) {
              scope.dp_tab_ids = {};
            }
            id_segs = attrs['dpTabBody'];
            if (!id_segs) {
              return;
            }
            id_segs = id_segs.split('.');
            tab_val = id_segs.pop();
            tab_group = id_segs.join('.');
            if (scope.dp_tab_ids[tab_group] === tab_val) {
              element.show();
            } else {
              element.hide();
            }
            return scope.$watch(function() {
              return scope.dp_tab_ids[tab_group];
            }, function(newVal) {
              if (newVal === tab_val) {
                return element.show();
              } else {
                return element.hide();
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpTabBody;
  });

}).call(this);

/*
//@ sourceMappingURL=DpTabBody.js.map
*/