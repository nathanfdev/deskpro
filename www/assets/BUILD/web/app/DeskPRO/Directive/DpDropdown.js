define(() => {
  const DeskPRO_Directive_DpDropdown = ['$rootScope', '$document', ($rootScope, $document) =>
    ({
      restrict: 'A',
      scope:    {
        dropdownId: '@dpDropdown',
        openerId:   '@dpDropdownOpener',
        closerId:   '@dpDropdownCloser',
        useClass:   '@dpDropdownUseClass'
      },

      link(scope, element) {
        let dropdown,
          opener;
        scope.visible = false;

        const closeDropdown = function () {
          scope.visible = false;
          return processDropdown();
        };

        const toggleDropdown = function () {
          scope.visible = !scope.visible;
          return processDropdown();
        };

        var processDropdown = function () {
          if (scope.visible === true) {
            return dropdown.show();
          }
          return dropdown.hide();
        };

        if (scope.openerId != null) {
          if (scope.useClass) {
            opener = element.find(`.${scope.openerId}`);
          } else {
            opener = element.find(`#${scope.openerId}`);
          }
        } else {
          opener = element;
        }

        if (scope.closerId != null) {
          const closer = element.find(`#${scope.closerId}`);
          closer.bind('click', (event) => {
            event.stopPropagation();
            return closeDropdown();
          });
        }

        opener.bind('click', toggleDropdown);
        if (scope.useClass) {
          dropdown = element.find(`.${scope.dropdownId}`);
        } else {
          dropdown = element.find(`#${scope.dropdownId}`);
        }

        processDropdown();
        return $document.bind('click', (event) => {
          event.stopPropagation();
          const target = angular.element(event.target);
          const clickedSystem = element
            .find(event.target)
            .length > 0;


          if (clickedSystem) {
            if (target.attr('dp-dropdown-item') != null) {
              return closeDropdown();
            }
            scope.visible = true;
            return processDropdown();
          }
          return closeDropdown();
        });
      }
    })

  ];

  return DeskPRO_Directive_DpDropdown;
});
