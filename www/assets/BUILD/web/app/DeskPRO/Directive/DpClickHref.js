// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  const DeskPRO_Directive_DpClickHref = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const clickHref = attrs['dpClickHref'];
        return element.on('click', function(ev) {
          let a;
          if (element.is('a')) {
            a = element;
          } else {
            a = element.find('a').first();
          }

          if ((ev.which === 1) && !(ev.shiftKey || ev.altKey || ev.metaKey || ev.ctrlKey)) {
            ev.preventDefault();
            return a.attr('href', clickHref).click();
          }
        });
      }
    })
  
  ];

  return DeskPRO_Directive_DpClickHref;
});