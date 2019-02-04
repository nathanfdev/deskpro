define(() => {
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
  const DeskPRO_Directive_DpTabBody = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        if (!scope.dp_tab_ids) {
          scope.dp_tab_ids = {};
        }
        if (!scope.dp_tabs_state) {
          scope.dp_tabs_state = {};
        }

        let id_segs = attrs.dpTabBody;
        if (!id_segs) {
          return;
        }

        const full      = id_segs;
        id_segs   = id_segs.split('.');
        const tab_val   = id_segs.pop();
        const tab_group = id_segs.join('.');

        if (scope.dp_tab_ids[tab_group] === tab_val) {
          element.show();
          scope.dp_tabs_state[full] = true;
        } else {
          element.hide();
          scope.dp_tabs_state[full] = false;
        }

        return scope.$watch(() => scope.dp_tab_ids[tab_group]
        , (newVal) => {
          if (newVal === tab_val) {
            element.show();
            scope.dp_tabs_state[full] = true;

            // If the ace editor is display:none (eg hidden tab) when the view
            // is loaded, then its possible it may be blank when trying to load it.
            // This is a workaround to the bug that refreshes the ui when the tab becomes
            // active.
            return element.find('.with-ace-editor').each(function () {
              const editor = $(this).data('ace-editor');
              return editor.renderer.updateFull();
            });
          }
          element.hide();
          return scope.dp_tabs_state[full] = false;
        });
      }
    })

  ];

  return DeskPRO_Directive_DpTabBody;
});
