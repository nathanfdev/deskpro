define(function(){
    return function($compile) {
        return {
            restrict: 'E',
            scope: {},
            template: '<test></test>',
            replace: true,
            link: function($scope, $el, $attr) {
                window.console.error($el);
            }
        };
    };
});