define(function() {
  const DeskPRO_Directive_DpOpenPopover = [() =>
    ({
      restrict: 'A',
      link(scope, element) {
        return element.on('click', (ev) => {
          ev.preventDefault();
          const popover = window.parent.DeskPRO_Window._initInterfacePopover($(ev.currentTarget));
          return popover.open();
        });
      }
    })

  ];

  return DeskPRO_Directive_DpOpenPopover;
});
