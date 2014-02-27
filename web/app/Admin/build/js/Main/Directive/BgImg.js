(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This just adds a style background-image to an element using the evaluated value.
        *
        * Example
        * -------
        * <span bg-img="{{agent.picture_url}}"></span>
     */
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

//# sourceMappingURL=BgImg.js.map
