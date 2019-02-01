define(function() {
  /*
    * Description
    * -----------
    *
    * This sets up a tab trigger for a particular tab body. When this element is clicked,
    * then the specified tab body will be displayed. If there are other tab bodies in the same
    * ID group, they will first be hidden.
    *
    * Tab IDs
    * -------
    *
    * Tab ID's must be unique per controller. They should follow the format:
    *
    *     tab_group.tab_id
    *
    * When a tab_id is toggled on, all other tabs of tab_group are toggled off first.
    * Thus, we get a tabbed interface but still allow the ability to have multiple tab controls
    * in the same controller with no additional overhead or comlpexity.
    *
    * Example View
    * ------------
    * <button dp-tab-btn="main.tab1">Tab 1</button>
    * <button dp-tab-btn="main.tab2">Tab 2</button>
    *
    * <section dp-tab-body="main.tab1"><h1>This is content for Tab 1</h1></section>
    * <section dp-tab-body="main.tab2">
    *     <h1>This is content for Tab 2</h2>
    *
    *     <button dp-tab-btn="subtabs.tab1">Subtab 1</button>
    *     <button dp-tab-btn="subtabs.tab2">Subtab 2</button>
    *
    *     <div dp-tab-body="subtabs.tab1">Subtab Content 1</div>
    *     <div dp-tab-body="subtabs.tab2">Subtab Content 2</div>
    * </section>
    */
  const DeskPRO_Directive_DpTabBtn = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        if (!scope.dp_tab_ids) {
          scope.dp_tab_ids = {};
        }

        let id_segs = attrs['dpTabBtn'];
        if (!id_segs) {
          return;
        }

        id_segs   = id_segs.split('.');
        const tab_val   = id_segs.pop();
        const tab_group = id_segs.join('.');

        if (element.hasClass('active')) {
          scope.dp_tab_ids[tab_group] = tab_val;
        }

        element.on('click', function(ev) {
          ev.preventDefault();
          scope.dp_tab_ids[tab_group] = tab_val;
          return scope.$apply();
        });

        return scope.$watch(() => scope.dp_tab_ids[tab_group]
        , function(newVal) {
          if (newVal === tab_val) {
            return element.addClass('active');
          } else {
            return element.removeClass('active');
          }
        });
      }
    })
  
  ];

  return DeskPRO_Directive_DpTabBtn;
});
