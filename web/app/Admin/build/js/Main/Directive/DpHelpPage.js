(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This element defines the "help page" for a section. The help page is detached and re-positioned into the
       # right-most pane and has the ability to minimise into the section help icon.
       #
       # Example
       # -------
       # <dp-help-page>
       #    .....
       # </dp-help-page>
       #
       # <!-- In the list content we need the trigger as well: -->
       # <button class="btn help-page-trigger"><i class="icon-question-sign"></i></button>
    */

    var Admin_Main_Directive_DpHelpPage;
    Admin_Main_Directive_DpHelpPage = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'AE',
          scope: {},
          replace: true,
          transclude: true,
          template: '<section class="dp-help-page dp-section-page ng-hide" ng-hide="loading.dp_section_list"><div class="inner"><div class="close-btn"><i class="icon-remove"></i></div><div ng-transclude></div></div></section>',
          link: function(scope, element, attrs) {
            var $border, $button, $page, btnMod, buttonH, buttonW, closeFn, isOpen, my_state, openFn, state_segs, _ref, _ref1, _ref2;
            element.hide();
            isOpen = false;
            $button = element.closest('.dp-section-list').find('.help-page-trigger').first();
            $border = angular.element('<div class="help-min-frame"></div>').hide().appendTo('body');
            $page = $('#dp_section_page');
            buttonW = $button.outerWidth();
            buttonH = $button.outerHeight();
            btnMod = -6;
            element.detach().appendTo('#dp_section_body').css({
              position: 'absolute',
              'z-index': '10000',
              'overflow': 'auto'
            });
            scope.$on('$destroy', function() {
              element.remove();
              return $border.remove();
            });
            my_state = null;
            if ((_ref = $state.current) != null ? _ref.name : void 0) {
              state_segs = $state.current.name.split('.');
              if (state_segs.length === 3) {
                state_segs.pop();
              }
              my_state = state_segs.join('.');
            }
            if (!$state.with_page_view || ((_ref1 = $state.current) != null ? (_ref2 = _ref1.views['dp_section_page@']) != null ? _ref2.controller : void 0 : void 0) === 'Admin_Main_Ctrl_Bare') {
              isOpen = true;
              element.show();
              $button.hide();
            }
            openFn = function() {
              var buttonOffset, pageH, pageOffset, pageW;
              if (isOpen) {
                return;
              }
              isOpen = true;
              pageH = $page.height();
              pageW = $page.width();
              pageOffset = $page.offset();
              buttonOffset = $button.offset();
              $border.css({
                width: 5,
                height: 5,
                left: buttonOffset.left + (buttonW / 2) - 3,
                top: buttonOffset.top + (buttonH / 2) - 3,
                borderRadius: 0
              });
              $border.show();
              $border.animate({
                height: pageH,
                width: pageW,
                left: pageOffset.left,
                top: pageOffset.top
              }, 310, function() {
                return $border.hide();
              });
              window.setTimeout(function() {
                return element.fadeIn(100);
              }, 210);
              return $button.fadeOut(200);
            };
            closeFn = function(instantly) {
              var buttonOffset, pageH, pageOffset, pageW;
              if (!isOpen) {
                return;
              }
              isOpen = false;
              if (instantly) {
                element.hide();
                $border.hide();
                $button.show();
                return;
              }
              pageH = $page.height();
              pageW = $page.width();
              pageOffset = $page.offset();
              $border.css({
                height: pageH,
                width: pageW,
                left: pageOffset.left,
                top: pageOffset.top
              });
              $button.fadeIn(200);
              buttonOffset = $button.offset();
              $border.show();
              element.fadeOut(125);
              return $border.animate({
                width: 5,
                height: 5,
                left: buttonOffset.left + (buttonW / 2) - 3,
                top: buttonOffset.top + (buttonH / 2) - 3
              }, 310, function() {
                return $border.hide();
              });
            };
            $button.on('click', function(ev) {
              ev.preventDefault();
              if (isOpen) {
                return closeFn();
              } else {
                return openFn();
              }
            });
            element.find('.close-btn').on('click', function(ev) {
              ev.preventDefault();
              return closeFn();
            });
            $rootScope.$on('$stateChangeStart', function(ev, toState, toParams, fromState, fromParams) {
              var new_state;
              if (my_state) {
                state_segs = toState.name.split('.');
                if (state_segs.length === 3) {
                  state_segs.pop();
                }
                new_state = state_segs.join('.');
                if (new_state !== my_state) {
                  return closeFn(true);
                } else {
                  return closeFn();
                }
              } else {
                return closeFn();
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpHelpPage;
  });

}).call(this);

/*
//@ sourceMappingURL=DpHelpPage.js.map
*/