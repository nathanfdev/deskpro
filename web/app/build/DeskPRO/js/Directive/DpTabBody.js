(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This should be applied to the body portion of a tabbed interface. When the dp-tab-btn with this ID
        * is enabled, this body is displayed and others are removed.
        *
        * See dp-tab-btn for a full example.
        *
        * Example View
        * ------------
        * <section dp-tab-body="edit.main">...</section>
     */
    var DeskPRO_Directive_DpTabBody;
    DeskPRO_Directive_DpTabBody = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var full, id_segs, tab_group, tab_val;
            if (!scope.dp_tab_ids) {
              scope.dp_tab_ids = {};
            }
            if (!scope.dp_tabs_state) {
              scope.dp_tabs_state = {};
            }
            id_segs = attrs['dpTabBody'];
            if (!id_segs) {
              return;
            }
            full = id_segs;
            id_segs = id_segs.split('.');
            tab_val = id_segs.pop();
            tab_group = id_segs.join('.');
            if (scope.dp_tab_ids[tab_group] === tab_val) {
              element.show();
              scope.dp_tabs_state[full] = true;
            } else {
              element.hide();
              scope.dp_tabs_state[full] = false;
            }
            return scope.$watch(function() {
              return scope.dp_tab_ids[tab_group];
            }, function(newVal) {
              if (newVal === tab_val) {
                element.show();
                scope.dp_tabs_state[full] = true;
                return element.find('.with-ace-editor').each(function() {
                  var editor;
                  editor = $(this).data('ace-editor');
                  return editor.renderer.updateFull();
                });
              } else {
                element.hide();
                return scope.dp_tabs_state[full] = false;
              }
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpTabBody;
  });

}).call(this);

//# sourceMappingURL=DpTabBody.js.map
