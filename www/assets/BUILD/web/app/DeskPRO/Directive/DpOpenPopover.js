// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  const DeskPRO_Directive_DpOpenPopover = [ () =>
    ({
      restrict: 'A',
      link(scope, element) {
        return element.on('click', function(ev) {
            ev.preventDefault();
            const popover = window.parent.DeskPRO_Window._initInterfacePopover($(ev.currentTarget));
            return popover.open();
        });
      }
    })
  
  ];

  return DeskPRO_Directive_DpOpenPopover;
});
