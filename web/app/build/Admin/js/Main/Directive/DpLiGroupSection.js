(function() {
  define(function() {
    var Admin_Main_Directive_DpLiGroupSection;
    Admin_Main_Directive_DpLiGroupSection = [
      '$timeout', function($timeout) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var a, contentEls, mainList;
            mainList = element.closest('ul');
            element.addClass('group-section');
            contentEls = element.find('.group-section-content');
            a = element.find('a.toggle').first();
            return a.on('click', function() {
              var mode;
              mode = element.hasClass('group-open') ? 'close' : 'open';
              if (mode === 'open') {
                mainList.find('li.group-section.group-open').each(function() {
                  return $(this).removeClass('group-open').find('.group-section-content').addClass('with-no-height');
                });
                $timeout(function() {
                  return element.find('.dp-item-list').find('a').first().click();
                });
                element.addClass('group-open');
                return contentEls.removeClass('with-no-height');
              } else {
                element.removeClass('group-open');
                return contentEls.addClass('with-no-height');
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpLiGroupSection;
  });

}).call(this);

//# sourceMappingURL=DpLiGroupSection.js.map
