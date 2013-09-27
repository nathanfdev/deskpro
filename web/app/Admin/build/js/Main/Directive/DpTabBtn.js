(function() {
  define(function() {
    var Admin_Main_Directive_DpTabBtn;
    Admin_Main_Directive_DpTabBtn = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var id_segs, tab_group, tab_val;
            if (!scope.dp_tab_ids) {
              scope.dp_tab_ids = {};
            }
            id_segs = attrs['dpTabBtn'];
            if (!id_segs) {
              return;
            }
            id_segs = id_segs.split('.');
            tab_val = id_segs.pop();
            tab_group = id_segs.join('.');
            if (element.hasClass('active')) {
              scope.dp_tab_ids[tab_group] = tab_val;
            }
            element.on('click', function(ev) {
              ev.preventDefault();
              scope.dp_tab_ids[tab_group] = tab_val;
              return scope.$apply();
            });
            return scope.$watch(function() {
              return scope.dp_tab_ids[tab_group];
            }, function(newVal) {
              if (newVal === tab_val) {
                return element.addClass('active');
              } else {
                return element.removeClass('active');
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpTabBtn;
  });

}).call(this);

/*
//@ sourceMappingURL=DpTabBtn.js.map
*/