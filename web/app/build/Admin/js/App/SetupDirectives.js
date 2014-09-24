(function() {
  define(['DeskPRO/Directive/DpClickHref', 'DeskPRO/Directive/DpClosestNumber', 'DeskPRO/Directive/DpTimeWithUnit', 'DeskPRO/Directive/DpFilesizeWithUnit', 'DeskPRO/Directive/DpStateMark', 'DeskPRO/Directive/DpHelpPage', 'DeskPRO/Directive/DpNavSubnav', 'DeskPRO/Directive/DpTabBody', 'DeskPRO/Directive/DpTabBtn', 'DeskPRO/Directive/DpHideSpinning', 'DeskPRO/Directive/DpJsonData', 'DeskPRO/Directive/DpNgTemplate', 'DeskPRO/Directive/DpShowSpinning', 'DeskPRO/Directive/DpSubmitForm', 'DeskPRO/Directive/DpErrorClass', 'DeskPRO/Directive/DpLabel', 'Admin/Main/Directive/Autofocus', 'Admin/Main/Directive/BgImg', 'Admin/Main/Directive/DpCommaSeparated', 'Admin/Main/Directive/DpDevBar', 'Admin/Main/Directive/DpInhelpBody', 'Admin/Main/Directive/DpInhelpBtn', 'Admin/Main/Directive/DpLiGroupSection', 'Admin/Main/Directive/DpListAutoload', 'Admin/Main/Directive/DpMaxHeight', 'Admin/Main/Directive/DpOpenPhraseEditor', 'Admin/Main/Directive/DpOrderMenu', 'Admin/Main/Directive/DpPingFlash', 'Admin/Main/Directive/DpRegisterMessage', 'Admin/Main/Directive/DpServerValidation', 'Admin/Main/Directive/DpSliderSwitch', 'Admin/Main/Directive/DpStatusUpdate', 'Admin/Main/Directive/DpToggleSwitch', 'Admin/Main/Directive/DpTristateCheck', 'Admin/Main/Directive/DpWorkingHours', 'Admin/Main/Directive/DpChange', 'Admin/Main/Directive/DpPhoneNumber', 'Admin/Main/Directive/DpRedactor', 'Admin/Portal/Directive/PortalEditor', 'Admin/TicketDeps/Directive/LayoutEditor', 'Admin/TicketDeps/Directive/LayoutEditorField'], function(DeskPRO_Directive_DpClickHref, DeskPRO_Directive_DpClosestNumber, DeskPRO_Directive_DpTimeWithUnit, DeskPRO_Directive_DpFilesizeWithUnit, DeskPRO_Directive_DpStateMark, DeskPRO_Directive_DpHelpPage, DeskPRO_Directive_DpNavSubnav, DeskPRO_Directive_DpTabBody, DeskPRO_Directive_DpTabBtn, DeskPRO_Directive_DpHideSpinning, DeskPRO_Directive_DpJsonData, DeskPRO_Directive_DpNgTemplate, DeskPRO_Directive_DpShowSpinning, DeskPRO_Directive_DpSubmitForm, DeskPRO_Directive_DpErrorClass, DeskPRO_Directive_DpLabel, Admin_Main_Directive_Autofocus, Admin_Main_Directive_BgImg, Admin_Main_Directive_DpCommaSeparated, Admin_Main_Directive_DpDevBar, Admin_Main_Directive_DpInhelpBody, Admin_Main_Directive_DpInhelpBtn, Admin_Main_Directive_DpLiGroupSection, Admin_Main_Directive_DpListAutoload, Admin_Main_Directive_DpMaxHeight, Admin_Main_Directive_DpOpenPhraseEditor, Admin_Main_Directive_DpOrderMenu, Admin_Main_Directive_DpPingFlash, Admin_Main_Directive_DpRegisterMessage, Admin_Main_Directive_DpServerValidation, Admin_Main_Directive_DpSliderSwitch, Admin_Main_Directive_DpStatusUpdate, Admin_Main_Directive_DpToggleSwitch, Admin_Main_Directive_DpTristateCheck, Admin_Main_Directive_DpWorkingHours, Admin_Main_Directive_DpChange, Admin_Main_Directive_DpPhoneNumber, Admin_Main_Directive_DpRedactor, Admin_Portal_Directive_PortalEditor, Admin_TicketDeps_Directive_LayoutEditor, Admin_TicketDeps_Directive_LayoutEditorField) {
    return function(Module) {
      Module.directive('dpClickHref', DeskPRO_Directive_DpClickHref);
      Module.directive('dpClosestNumber', DeskPRO_Directive_DpClosestNumber);
      Module.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
      Module.directive('dpFilesizeWithUnit', DeskPRO_Directive_DpFilesizeWithUnit);
      Module.directive('dpStateMark', DeskPRO_Directive_DpStateMark);
      Module.directive('dpHelpPage', DeskPRO_Directive_DpHelpPage);
      Module.directive('dpNavSubnav', DeskPRO_Directive_DpNavSubnav);
      Module.directive('dpTabBody', DeskPRO_Directive_DpTabBody);
      Module.directive('dpTabBtn', DeskPRO_Directive_DpTabBtn);
      Module.directive('dpHideSpinning', DeskPRO_Directive_DpHideSpinning);
      Module.directive('script', DeskPRO_Directive_DpJsonData);
      Module.directive('script', DeskPRO_Directive_DpNgTemplate);
      Module.directive('dpShowSpinning', DeskPRO_Directive_DpShowSpinning);
      Module.directive('dpSubmitForm', DeskPRO_Directive_DpSubmitForm);
      Module.directive('dpErrorClass', DeskPRO_Directive_DpErrorClass);
      Module.directive('dpLabel', DeskPRO_Directive_DpLabel);
      Module.directive('autofocus', Admin_Main_Directive_Autofocus);
      Module.directive('dpLiGroupSection', Admin_Main_Directive_DpLiGroupSection);
      Module.directive('bgImg', Admin_Main_Directive_BgImg);
      Module.directive('dpCommaSeparated', Admin_Main_Directive_DpCommaSeparated);
      Module.directive('dpDevbar', Admin_Main_Directive_DpDevBar);
      Module.directive('dpInhelpBody', Admin_Main_Directive_DpInhelpBody);
      Module.directive('dpInhelpBtn', Admin_Main_Directive_DpInhelpBtn);
      Module.directive('dpListAutoload', Admin_Main_Directive_DpListAutoload);
      Module.directive('dpMaxHeight', Admin_Main_Directive_DpMaxHeight);
      Module.directive('dpOpenPhraseEditor', Admin_Main_Directive_DpOpenPhraseEditor);
      Module.directive('dpOrderMenu', Admin_Main_Directive_DpOrderMenu);
      Module.directive('dpPingFlash', Admin_Main_Directive_DpPingFlash);
      Module.directive('dpRegisterMessage', Admin_Main_Directive_DpRegisterMessage);
      Module.directive('dpServerValidation', Admin_Main_Directive_DpServerValidation);
      Module.directive('dpSliderSwitch', Admin_Main_Directive_DpSliderSwitch);
      Module.directive('dpStatusUpdate', Admin_Main_Directive_DpStatusUpdate);
      Module.directive('dpToggleSwitch', Admin_Main_Directive_DpToggleSwitch);
      Module.directive('dpTristateCheck', Admin_Main_Directive_DpTristateCheck);
      Module.directive('dpWorkingHours', Admin_Main_Directive_DpWorkingHours);
      Module.directive('dpChange', Admin_Main_Directive_DpChange);
      Module.directive('dpPhoneNumber', Admin_Main_Directive_DpPhoneNumber);
      Module.directive('dpRedactor', Admin_Main_Directive_DpRedactor);
      Module.directive('dpPortalEditor', Admin_Portal_Directive_PortalEditor);
      Module.directive('dpTicketLayoutEditor', Admin_TicketDeps_Directive_LayoutEditor);
      Module.directive('dpTicketLayoutEditorField', Admin_TicketDeps_Directive_LayoutEditorField);
      Module.directive('dpToggleShowIds', [
        function() {
          return {
            restrict: 'A',
            link: function(scope, el, attrs) {
              var update;
              window.DP_DO_SHOW_IDS = false;
              scope.do_show_ids = false;
              update = function() {
                if (window.DP_DO_SHOW_IDS) {
                  scope.do_show_ids = true;
                  return $('body').addClass('show-title-ids');
                } else {
                  scope.do_show_ids = false;
                  return $('body').removeClass('show-title-ids');
                }
              };
              update();
              return el.on('click', function(ev) {
                ev.stopPropagation();
                ev.preventDefault();
                window.DP_DO_SHOW_IDS = !window.DP_DO_SHOW_IDS;
                return scope.$apply(function() {
                  return update();
                });
              });
            }
          };
        }
      ]);
      Module.directive('dpGo', [
        '$location', function($location) {
          return {
            restrict: 'A',
            link: function(scope, el, attrs) {
              return el.on('click', function(ev) {
                var path, search;
                ev.stopPropagation();
                ev.preventDefault();
                path = attrs.dpGo.replace(/^#/, '');
                search = scope.$eval(attrs.dpGoParams);
                return scope.$apply(function() {
                  $location.path(path);
                  if (search) {
                    return $location.search(search);
                  }
                });
              });
            }
          };
        }
      ]);
      Module.directive('dpNoDrag', [
        function() {
          return {
            restrict: 'AC',
            link: function(scope, el, attrs) {
              return el.get(0).draggable = false;
            }
          };
        }
      ]);
      Module.directive('dpMoveListToPos', [
        '$timeout', function($timeout) {
          return {
            restrict: 'A',
            scope: {},
            link: function(scope, element, attrs) {
              var initial_run, is_running, run_again, update;
              initial_run = false;
              is_running = false;
              run_again = false;
              scope.$on('resetDisplayOrders', function() {
                if (!initial_run) {
                  initial_run = false;
                  run_again = true;
                  return update();
                } else {
                  return $timeout(function() {
                    if (is_running) {
                      return run_again = true;
                    } else {
                      return update();
                    }
                  }, 1);
                }
              });
              return update = function() {
                var all_lis;
                is_running = true;
                all_lis = element.find('> li').filter('[data-move-to-pos]');
                all_lis.each(function() {
                  var li, toPos, use;
                  li = $(this);
                  toPos = parseInt(li.data('move-to-pos') || 0) || 0;
                  if (toPos === 0 || isNaN(toPos)) {
                    return;
                  }
                  use = null;
                  element.find('> li').each(function() {
                    var ro;
                    ro = parseInt($(this).attr('data-run-order') || 0) || 0;
                    if (ro === 0 || isNaN(ro)) {
                      return;
                    }
                    if (ro < toPos && this !== element[0]) {
                      return use = $(this);
                    }
                  });
                  li.detach();
                  if (!use) {
                    return li.detach().prependTo(element);
                  } else {
                    return li.detach().insertAfter(use);
                  }
                });
                if (run_again) {
                  is_running = true;
                  return $timeout(function() {
                    run_again = false;
                    return update();
                  }, 1);
                } else {
                  return is_running = false;
                }
              };
            }
          };
        }
      ]);
      return Module.directive('dpHtmlRenderVar', [
        function() {
          return {
            restrict: 'A',
            link: function(scope, element, attrs) {
              return scope.$watch(attrs.dpHtmlRenderVar, function(newVal) {
                return element.html(newVal);
              });
            }
          };
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupDirectives.js.map
