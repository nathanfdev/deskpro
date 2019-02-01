define(() => {
  const DeskPRO_Directive_DpHelpPage = ['$rootScope', '$state', ($rootScope, $state) =>
    ({
      restrict:   'E',
      scope:      {},
      replace:    true,
      transclude: true,
      template:   `\
<section class="dp-help-content-wrapper">
  <div class="dp-help-content-outer">
    <div class="dp-help-content-outer2">
      <div class="dp-help-content" ng-transclude></div>
      <div class="dp-arrow-wrap"><em><i class="fa fa-chevron-down down"></i><i class="fa fa-chevron-up up"></i></em></div>
    </div>
  </div>
</section>\
`,
      link(scope, element, attrs) {
        let isOpen = false;
        let backdrop = null;
        element.find('header').first().prepend('<aside><i class="fa fa-question-circle"></i></aside>');

        const open = function () {
          if (isOpen) { return; }
          const origH = element.height();
          element.height(origH);

          if (!backdrop) {
            backdrop = $('<div/>').addClass('dp-help-content-backdrop');
            backdrop.on('click', (ev) => {
              ev.preventDefault();
              return close();
            });
            backdrop.insertBefore(element);
          }

          backdrop.show();
          element.addClass('open');
          const article = element.find('.dp-help-content').find('article').first();
          article.slideDown(200, 'linear');
          return isOpen = true;
        };

        var close = function () {
          if (!isOpen) { return; }
          backdrop.hide();
          const article = element.find('.dp-help-content').find('article').first();
          article.slideUp(200, 'linear', () => element.removeClass('open'));
          return isOpen = false;
        };

        const toggle = function () {
          if (!isOpen) {
            return open();
          }
          return close();
        };

        element.find('.dp-arrow-wrap').on('click', (ev) => {
          ev.preventDefault();
          return toggle();
        });
        element.find('header').first().on('click', (ev) => {
          ev.preventDefault();
          return toggle();
        });

        return element.find('.dp-help-content-outer').on('click', (ev) => {
          ev.stopPropagation();
          return open();
        });
      }
    })

  ];

  return DeskPRO_Directive_DpHelpPage;
});
