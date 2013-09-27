(function() {
  define(function() {
    var Admin_Main_Directive_DpNavSubnav;
    Admin_Main_Directive_DpNavSubnav = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var $parent, $toggler;
            $parent = element.parent();
            $toggler = $parent.find('> a');
            $toggler.on('click', function(ev) {
              ev.preventDefault();
              ev.stopPropagation();
              if ($parent.hasClass('sublist-open')) {
                $parent.removeClass('sublist-open');
                return element.slideUp();
              } else {
                $parent.addClass('sublist-open');
                return element.slideDown();
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpNavSubnav;
  });

}).call(this);

/*
//@ sourceMappingURL=DpNavSubnav.js.map
*/