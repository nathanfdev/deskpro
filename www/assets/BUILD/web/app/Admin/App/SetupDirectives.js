// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Directive/DpClickHref',
  'DeskPRO/Directive/DpOpenPopover',
  'DeskPRO/Directive/DpClosestNumber',
  'DeskPRO/Directive/DpTimeWithUnit',
  'DeskPRO/Directive/DpFilesizeWithUnit',
  'DeskPRO/Directive/DpStateMark',
  'DeskPRO/Directive/DpStateMarkRegex',
  'DeskPRO/Directive/DpHelpPage',
  'DeskPRO/Directive/DpNavSubnav',
  'DeskPRO/Directive/DpTabBody',
  'DeskPRO/Directive/DpTabBtn',
  'DeskPRO/Directive/DpHideSpinning',
  'DeskPRO/Directive/DpJsonData',
  'DeskPRO/Directive/DpNgTemplate',
  'DeskPRO/Directive/DpScrollable',
  'DeskPRO/Directive/DpShowSpinning',
  'DeskPRO/Directive/DpSubmitForm',
  'DeskPRO/Directive/DpErrorClass',
  'DeskPRO/Directive/DpLabel',
  'DeskPRO/Directive/DpClipboard',
  'DeskPRO/Directive/DpPersonActions',

  'Admin/Main/Directive/Autofocus',
  'Admin/Main/Directive/BgImg',
  'Admin/Main/Directive/DpCommaSeparated',
  'Admin/Main/Directive/DpDevBar',
  'Admin/Main/Directive/DpInhelpBody',
  'Admin/Main/Directive/DpInhelpBtn',
  'Admin/Main/Directive/DpLiGroupSection',
  'Admin/Main/Directive/DpListAutoload',
  'Admin/Main/Directive/DpMaxHeight',
  'Admin/Main/Directive/DpOnOffSwitch',
  'Admin/Main/Directive/DpMatchMinHeight',
  'Admin/Main/Directive/DpOpenPhraseEditor',
  'Admin/Main/Directive/DpOpenPhraseMapEditor',
  'Admin/Main/Directive/DpOrderMenu',
  'Admin/Main/Directive/DpPingFlash',
  'Admin/Main/Directive/DpRegisterMessage',
  'Admin/Main/Directive/DpServerValidation',
  'Admin/Main/Directive/DpSliderSwitch',
  'Admin/Main/Directive/DpStatusUpdate',
  'Admin/Main/Directive/DpToggleSwitch',
  'Admin/Main/Directive/DpTristateCheck',
  'Admin/Main/Directive/DpWorkingHours',
  'Admin/Main/Directive/DpChange',
  'Admin/Main/Directive/DpPhoneNumber',
  'Admin/Main/Directive/DpPhoneNumberNoExt',
  'Admin/Main/Directive/DpRedactor',
  'Admin/Main/Directive/DpDate',
  'Admin/Main/Directive/DpReadFile',
  'Admin/Main/Directive/DpSemanticLanguageButton',

  'Admin/TicketDeps/Directive/LayoutEditor',
  'Admin/TicketDeps/Directive/LayoutEditorField',

  'Admin/License/Directive/PaymentFormDirective',

  'Admin/Portal/Directive/Editor/VariableForm',
  'Admin/Portal/Directive/Editor/ColorForm',
  'Admin/Portal/Directive/Editor/FloatForm',
  'Admin/Portal/Directive/Editor/FontForm',
  'Admin/Portal/Directive/Editor/SizeForm',
  'Admin/Portal/Directive/Editor/CodeEditor'
], (
  DeskPRO_Directive_DpClickHref,
  DeskPRO_Directive_DpOpenPopover,
  DeskPRO_Directive_DpClosestNumber,
  DeskPRO_Directive_DpTimeWithUnit,
  DeskPRO_Directive_DpFilesizeWithUnit,
  DeskPRO_Directive_DpStateMark,
  DeskPRO_Directive_DpStateMarkRegex,
  DeskPRO_Directive_DpHelpPage,
  DeskPRO_Directive_DpNavSubnav,
  DeskPRO_Directive_DpTabBody,
  DeskPRO_Directive_DpTabBtn,
  DeskPRO_Directive_DpHideSpinning,
  DeskPRO_Directive_DpJsonData,
  DeskPRO_Directive_DpNgTemplate,
  DeskPRO_Directive_DpScrollable,
  DeskPRO_Directive_DpShowSpinning,
  DeskPRO_Directive_DpSubmitForm,
  DeskPRO_Directive_DpErrorClass,
  DeskPRO_Directive_DpLabel,
  DeskPRO_Directive_DpClipboard,
  DeskPRO_Directive_DpPersonActions,

  Admin_Main_Directive_Autofocus,
  Admin_Main_Directive_BgImg,
  Admin_Main_Directive_DpCommaSeparated,
  Admin_Main_Directive_DpDevBar,
  Admin_Main_Directive_DpInhelpBody,
  Admin_Main_Directive_DpInhelpBtn,
  Admin_Main_Directive_DpLiGroupSection,
  Admin_Main_Directive_DpListAutoload,
  Admin_Main_Directive_DpMaxHeight,
  Admin_Main_Directive_DpOnOffSwitch,
  Admin_Main_Directive_DpMatchMinHeight,
  Admin_Main_Directive_DpOpenPhraseEditor,
  Admin_Main_Directive_DpOpenPhraseMapEditor,
  Admin_Main_Directive_DpOrderMenu,
  Admin_Main_Directive_DpPingFlash,
  Admin_Main_Directive_DpRegisterMessage,
  Admin_Main_Directive_DpServerValidation,
  Admin_Main_Directive_DpSliderSwitch,
  Admin_Main_Directive_DpStatusUpdate,
  Admin_Main_Directive_DpToggleSwitch,
  Admin_Main_Directive_DpTristateCheck,
  Admin_Main_Directive_DpWorkingHours,
  Admin_Main_Directive_DpChange,
  Admin_Main_Directive_DpPhoneNumber,
  Admin_Main_Directive_DpPhoneNumberNoExt,
  Admin_Main_Directive_DpRedactor,
  Admin_Main_Directive_DpDate,
  Admin_Main_Directive_DpReadFile,
  Admin_Main_Directive_DpSemanticLanguageButton,

  Admin_TicketDeps_Directive_LayoutEditor,
  Admin_TicketDeps_Directive_LayoutEditorField,

  Admin_License_Directive_PaymentFormDirective,

  Admin_Portal_Directive_Editor_VariableForm,
  Admin_Portal_Directive_Editor_ColorForm,
  Admin_Portal_Directive_Editor_FloatForm,
  Admin_Portal_Directive_Editor_FontForm,
  Admin_Portal_Directive_Editor_SizeForm,
  Admin_Portal_Directive_Editor_CodeEditor
) =>
  function(Module) {
    Module.directive('dpClickHref',                    DeskPRO_Directive_DpClickHref);
    Module.directive('dpOpenPopover',                  DeskPRO_Directive_DpOpenPopover);
    Module.directive('dpClosestNumber',                DeskPRO_Directive_DpClosestNumber);
    Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit);
    Module.directive('dpFilesizeWithUnit',             DeskPRO_Directive_DpFilesizeWithUnit);
    Module.directive('dpStateMark',                    DeskPRO_Directive_DpStateMark);
    Module.directive('dpStateMarkRegex',               DeskPRO_Directive_DpStateMarkRegex);
    Module.directive('dpHelpPage',                     DeskPRO_Directive_DpHelpPage);
    Module.directive('dpNavSubnav',                    DeskPRO_Directive_DpNavSubnav);
    Module.directive('dpTabBody',                      DeskPRO_Directive_DpTabBody);
    Module.directive('dpTabBtn',                       DeskPRO_Directive_DpTabBtn);
    Module.directive('dpHideSpinning',                 DeskPRO_Directive_DpHideSpinning);
    Module.directive('script',                         DeskPRO_Directive_DpJsonData);
    Module.directive('script',                         DeskPRO_Directive_DpNgTemplate);
    Module.directive('dpScrollable',                   DeskPRO_Directive_DpScrollable);
    Module.directive('dpShowSpinning',                 DeskPRO_Directive_DpShowSpinning);
    Module.directive('dpSubmitForm',                   DeskPRO_Directive_DpSubmitForm);
    Module.directive('dpErrorClass',                   DeskPRO_Directive_DpErrorClass);
    Module.directive('dpLabel',                        DeskPRO_Directive_DpLabel);
    Module.directive('dpClipboard',                    DeskPRO_Directive_DpClipboard);
    Module.directive('dpPersonActions',                DeskPRO_Directive_DpPersonActions);

    Module.directive('autofocus',                      Admin_Main_Directive_Autofocus);
    Module.directive('dpLiGroupSection',               Admin_Main_Directive_DpLiGroupSection);
    Module.directive('bgImg',                          Admin_Main_Directive_BgImg);
    Module.directive('dpCommaSeparated',               Admin_Main_Directive_DpCommaSeparated);
    Module.directive('dpDevbar'        ,               Admin_Main_Directive_DpDevBar);
    Module.directive('dpInhelpBody',                   Admin_Main_Directive_DpInhelpBody);
    Module.directive('dpInhelpBtn',                    Admin_Main_Directive_DpInhelpBtn);
    Module.directive('dpListAutoload',                 Admin_Main_Directive_DpListAutoload);
    Module.directive('dpMaxHeight',                    Admin_Main_Directive_DpMaxHeight);
    Module.directive('dpOnoffSwitch',                  Admin_Main_Directive_DpOnOffSwitch);
    Module.directive('dpMatchMinHeight',               Admin_Main_Directive_DpMatchMinHeight);
    Module.directive('dpOpenPhraseEditor',             Admin_Main_Directive_DpOpenPhraseEditor);
    Module.directive('dpOpenPhraseMapEditor',          Admin_Main_Directive_DpOpenPhraseMapEditor);
    Module.directive('dpOrderMenu',                    Admin_Main_Directive_DpOrderMenu);
    Module.directive('dpPingFlash',                    Admin_Main_Directive_DpPingFlash);
    Module.directive('dpRegisterMessage',              Admin_Main_Directive_DpRegisterMessage);
    Module.directive('dpServerValidation',             Admin_Main_Directive_DpServerValidation);
    Module.directive('dpSliderSwitch',                 Admin_Main_Directive_DpSliderSwitch);
    Module.directive('dpStatusUpdate',                 Admin_Main_Directive_DpStatusUpdate);
    Module.directive('dpToggleSwitch',                 Admin_Main_Directive_DpToggleSwitch);
    Module.directive('dpTristateCheck',                Admin_Main_Directive_DpTristateCheck);
    Module.directive('dpWorkingHours',                 Admin_Main_Directive_DpWorkingHours);
    Module.directive('dpChange',                       Admin_Main_Directive_DpChange);
    Module.directive('dpPhoneNumber',                  Admin_Main_Directive_DpPhoneNumber);
    Module.directive('dpPhoneNumberNoExt',             Admin_Main_Directive_DpPhoneNumberNoExt);
    Module.directive('dpRedactor',                     Admin_Main_Directive_DpRedactor);
    Module.directive('dpDate',                         Admin_Main_Directive_DpDate);
    Module.directive('dpReadFile',                     Admin_Main_Directive_DpReadFile);
    Module.directive('dpSemanticLanguageButton',       Admin_Main_Directive_DpSemanticLanguageButton);

    Module.directive('dpTicketLayoutEditor',           Admin_TicketDeps_Directive_LayoutEditor);
    Module.directive('dpTicketLayoutEditorField',      Admin_TicketDeps_Directive_LayoutEditorField);

    Module.directive('dpLicensePaymentForm',           Admin_License_Directive_PaymentFormDirective);

    Module.directive('dpPortalDesignerVariableForm',   Admin_Portal_Directive_Editor_VariableForm);
    Module.directive('dpPortalDesignerColorForm',      Admin_Portal_Directive_Editor_ColorForm);
    Module.directive('dpPortalDesignerFloatForm',      Admin_Portal_Directive_Editor_FloatForm);
    Module.directive('dpPortalDesignerFontForm',       Admin_Portal_Directive_Editor_FontForm);
    Module.directive('dpPortalDesignerSizeForm',       Admin_Portal_Directive_Editor_SizeForm);
    Module.directive('dpCodeEditor',                   Admin_Portal_Directive_Editor_CodeEditor);

    Module.directive('dpToggleShowIds', [ () =>
      ({
        restrict: 'A',
        link(scope, el, attrs) {
          window.DP_DO_SHOW_IDS = false;
          scope.do_show_ids = false;

          const update = function() {
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
            return scope.$apply(() => update());
          });
        }
      })
    
    ]);

    Module.directive('dpGo', [ '$location', $location =>
      ({
        restrict: 'A',
        link(scope, el, attrs) {
          return el.on('click', function(ev) {
            ev.stopPropagation();
            ev.preventDefault();

            const path = attrs.dpGo.replace(/^#/, '');
            const search = scope.$eval(attrs.dpGoParams);

            return scope.$apply(function() {
              $location.path(path);
              if (search) {
                return $location.search(search);
              }
            });
          });
        }
      })
    
    ]);

    Module.directive('dpNoDrag', [ () =>
      ({
        restrict: 'AC',
        link(scope, el, attrs) {
          return el.get(0).draggable = false;
        }
      })
    
    ]);

    Module.directive('dpMoveListToPos', [ '$timeout', $timeout =>
      ({
        restrict: 'A',
        scope: {},
        link(scope, element, attrs) {
          let update;
          let initial_run = false;
          let is_running = false;
          let run_again = false;
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
              }
              , 1);
            }
          });

          return update = function() {
            is_running = true;
            const all_lis = element.find('> li').filter('[data-move-to-pos]');

            all_lis.each(function() {
              const li = $(this);
              const toPos = parseInt(li.data('move-to-pos') || 0) || 0;
              if ((toPos === 0) || isNaN(toPos)) {
                return;
              }

              let use = null;
              element.find('> li').each(function() {
                const ro = parseInt($(this).attr('data-run-order') || 0) || 0;
                if ((ro === 0) || isNaN(ro)) {
                  return;
                }
                if ((ro < toPos) && (this !== element[0])) {
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
              }
              , 1);
            } else {
              return is_running = false;
            }
          };
        }
      })
    
    ]);

    Module.directive('dpHtmlRenderVar', [ () =>
      ({
        restrict: 'A',
        link(scope, element, attrs) {
          return scope.$watch(attrs.dpHtmlRenderVar, newVal => element.html(newVal));
        }
      })
    
    ]);

    return Module.directive('href', [ '$location', '$state', ($location, $state) =>
      ({
        restrict: 'A',
        link(scope, element, attrs) {
          return element.bind('click', function() {
            if (element[0] && element[0].href && (element[0].href === $location.absUrl())) {
              return $state.go($state.current.name, $state.current.data, {reload: true});
            }
          });
        }
      })
    
    ]);
  }
);
