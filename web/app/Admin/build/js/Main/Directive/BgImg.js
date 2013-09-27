(function() {
  define(function() {
    var Admin_Main_Directive_BgImg;
    Admin_Main_Directive_BgImg = [
      function() {
        return {
          restrict: 'A',
          scope: {
            'bgImg': '&'
          },
          link: function(scope, element, attrs) {
            return element.css({
              'background-image': 'url("' + scope.$eval(scope.bgImg) + '")'
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_BgImg;
  });

}).call(this);

/*
//@ sourceMappingURL=BgImg.js.map
*/