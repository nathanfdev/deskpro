(function() {
  define(function() {
    var DeskPRO_Directive_DpHelpPage;
    DeskPRO_Directive_DpHelpPage = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'E',
          scope: {},
          replace: true,
          transclude: true,
          template: "<section class=\"dp-help-content-wrapper\">\n	<div class=\"dp-help-content-outer\">\n		<div class=\"dp-help-content-outer2\">\n			<div class=\"dp-help-content\" ng-transclude></div>\n			<div class=\"dp-arrow-wrap\"><em><i class=\"fa fa-chevron-down down\"></i><i class=\"fa fa-chevron-up up\"></i></em></div>\n		</div>\n	</div>\n</section>",
          link: function(scope, element, attrs) {
            var backdrop, close, isOpen, open, toggle;
            isOpen = false;
            backdrop = null;
            element.find('header').first().prepend('<aside><i class="fa fa-question-circle"></i></aside>');
            open = function() {
              var article, origH;
              if (isOpen) {
                return;
              }
              origH = element.height();
              element.height(origH);
              if (!backdrop) {
                backdrop = $('<div/>').addClass('dp-help-content-backdrop');
                backdrop.on('click', function(ev) {
                  ev.preventDefault();
                  return close();
                });
                backdrop.insertBefore(element);
              }
              backdrop.show();
              element.addClass('open');
              article = element.find('.dp-help-content').find('article').first();
              article.slideDown(200, 'linear');
              return isOpen = true;
            };
            close = function() {
              var article;
              if (!isOpen) {
                return;
              }
              backdrop.hide();
              article = element.find('.dp-help-content').find('article').first();
              article.slideUp(200, 'linear', function() {
                return element.removeClass('open');
              });
              return isOpen = false;
            };
            toggle = function() {
              if (!isOpen) {
                return open();
              } else {
                return close();
              }
            };
            element.find('.dp-arrow-wrap').on('click', function(ev) {
              ev.preventDefault();
              return toggle();
            });
            element.find('header').first().on('click', function(ev) {
              ev.preventDefault();
              return toggle();
            });
            return element.find('.dp-help-content-outer').on('click', function(ev) {
              ev.stopPropagation();
              return open();
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpHelpPage;
  });

}).call(this);

//# sourceMappingURL=DpHelpPage.js.map
