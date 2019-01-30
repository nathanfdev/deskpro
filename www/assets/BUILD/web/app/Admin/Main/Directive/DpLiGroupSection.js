// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  const Admin_Main_Directive_DpLiGroupSection = [ '$timeout', $timeout =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const mainList = element.closest('ul');
        element.addClass('group-section');

        const contentEls = element.find('.group-section-content');

        const a = element.find('a.toggle, .link.toggle').first();
        return a.on('click', function() {
          const mode = element.hasClass('group-open') ? 'close' : 'open';

          if (mode === 'open') {
            mainList.find('li.group-section.group-open').each(function() {
              return $(this).removeClass('group-open').find('.group-section-content').addClass('with-no-height');
            });

            $timeout(function() {
              const firstLink = element.find('.dp-item-list').find('a').first();
              if (window.location.hash !== firstLink.attr('href')) {
                return firstLink.click();
              }
            });

            element.addClass('group-open');
            return contentEls.removeClass('with-no-height');
          } else {
            element.removeClass('group-open');
            return contentEls.addClass('with-no-height');
          }
        });
      }
    })
  
  ];

  return Admin_Main_Directive_DpLiGroupSection;
});
