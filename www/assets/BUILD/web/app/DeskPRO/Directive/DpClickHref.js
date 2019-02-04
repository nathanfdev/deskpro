define(() => {
  const DeskPRO_Directive_DpClickHref = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const clickHref = attrs.dpClickHref;
        return element.on('click', (ev) => {
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
