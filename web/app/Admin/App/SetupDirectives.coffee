define [
  'DeskPRO/Directive/DpClickHref',
  'DeskPRO/Directive/DpClosestNumber',
  'DeskPRO/Directive/DpTimeWithUnit',
  'DeskPRO/Directive/DpFilesizeWithUnit',
  'DeskPRO/Directive/DpStateMark',
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
  'Admin/Main/Directive/DpRedactor',
  'Admin/Main/Directive/DpDate',
  'Admin/Main/Directive/DpReadFile',

  'Admin/TicketDeps/Directive/LayoutEditor',
  'Admin/TicketDeps/Directive/LayoutEditorField',

  'Admin/License/Directive/PaymentFormDirective',
], (
  DeskPRO_Directive_DpClickHref,
  DeskPRO_Directive_DpClosestNumber,
  DeskPRO_Directive_DpTimeWithUnit,
  DeskPRO_Directive_DpFilesizeWithUnit,
  DeskPRO_Directive_DpStateMark,
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
  Admin_Main_Directive_DpRedactor,
  Admin_Main_Directive_DpDate,
  Admin_Main_Directive_DpReadFile,

  Admin_TicketDeps_Directive_LayoutEditor,
  Admin_TicketDeps_Directive_LayoutEditorField,

  Admin_License_Directive_PaymentFormDirective
) ->
  return (Module) ->
    Module.directive('dpClickHref',                    DeskPRO_Directive_DpClickHref)
    Module.directive('dpClosestNumber',                DeskPRO_Directive_DpClosestNumber)
    Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit)
    Module.directive('dpFilesizeWithUnit',             DeskPRO_Directive_DpFilesizeWithUnit)
    Module.directive('dpStateMark',                    DeskPRO_Directive_DpStateMark)
    Module.directive('dpHelpPage',                     DeskPRO_Directive_DpHelpPage)
    Module.directive('dpNavSubnav',                    DeskPRO_Directive_DpNavSubnav)
    Module.directive('dpTabBody',                      DeskPRO_Directive_DpTabBody)
    Module.directive('dpTabBtn',                       DeskPRO_Directive_DpTabBtn)
    Module.directive('dpHideSpinning',                 DeskPRO_Directive_DpHideSpinning)
    Module.directive('script',                         DeskPRO_Directive_DpJsonData)
    Module.directive('script',                         DeskPRO_Directive_DpNgTemplate)
    Module.directive('dpScrollable',                   DeskPRO_Directive_DpScrollable)
    Module.directive('dpShowSpinning',                 DeskPRO_Directive_DpShowSpinning)
    Module.directive('dpSubmitForm',                   DeskPRO_Directive_DpSubmitForm)
    Module.directive('dpErrorClass',                   DeskPRO_Directive_DpErrorClass)
    Module.directive('dpLabel',                        DeskPRO_Directive_DpLabel)

    Module.directive('autofocus',                      Admin_Main_Directive_Autofocus)
    Module.directive('dpLiGroupSection',               Admin_Main_Directive_DpLiGroupSection)
    Module.directive('bgImg',                          Admin_Main_Directive_BgImg)
    Module.directive('dpCommaSeparated',               Admin_Main_Directive_DpCommaSeparated)
    Module.directive('dpDevbar'        ,               Admin_Main_Directive_DpDevBar)
    Module.directive('dpInhelpBody',                   Admin_Main_Directive_DpInhelpBody)
    Module.directive('dpInhelpBtn',                    Admin_Main_Directive_DpInhelpBtn)
    Module.directive('dpListAutoload',                 Admin_Main_Directive_DpListAutoload)
    Module.directive('dpMaxHeight',                    Admin_Main_Directive_DpMaxHeight)
    Module.directive('dpOnoffSwitch',                  Admin_Main_Directive_DpOnOffSwitch)
    Module.directive('dpMatchMinHeight',               Admin_Main_Directive_DpMatchMinHeight)
    Module.directive('dpOpenPhraseEditor',             Admin_Main_Directive_DpOpenPhraseEditor)
    Module.directive('dpOrderMenu',                    Admin_Main_Directive_DpOrderMenu)
    Module.directive('dpPingFlash',                    Admin_Main_Directive_DpPingFlash)
    Module.directive('dpRegisterMessage',              Admin_Main_Directive_DpRegisterMessage)
    Module.directive('dpServerValidation',             Admin_Main_Directive_DpServerValidation)
    Module.directive('dpSliderSwitch',                 Admin_Main_Directive_DpSliderSwitch)
    Module.directive('dpStatusUpdate',                 Admin_Main_Directive_DpStatusUpdate)
    Module.directive('dpToggleSwitch',                 Admin_Main_Directive_DpToggleSwitch)
    Module.directive('dpTristateCheck',                Admin_Main_Directive_DpTristateCheck)
    Module.directive('dpWorkingHours',                 Admin_Main_Directive_DpWorkingHours)
    Module.directive('dpChange',                       Admin_Main_Directive_DpChange)
    Module.directive('dpPhoneNumber',                  Admin_Main_Directive_DpPhoneNumber)
    Module.directive('dpRedactor',                     Admin_Main_Directive_DpRedactor)
    Module.directive('dpDate',                         Admin_Main_Directive_DpDate)
    Module.directive('dpReadFile',                     Admin_Main_Directive_DpReadFile)

    Module.directive('dpTicketLayoutEditor',           Admin_TicketDeps_Directive_LayoutEditor)
    Module.directive('dpTicketLayoutEditorField',      Admin_TicketDeps_Directive_LayoutEditorField)

    Module.directive('dpLicensePaymentForm',           Admin_License_Directive_PaymentFormDirective)

    Module.directive('dpToggleShowIds', [ ->
      return {
        restrict: 'A',
        link: (scope, el, attrs) ->
          window.DP_DO_SHOW_IDS = false;
          scope.do_show_ids = false

          update = ->
            if window.DP_DO_SHOW_IDS
              scope.do_show_ids = true
              $('body').addClass('show-title-ids')
            else
              scope.do_show_ids = false
              $('body').removeClass('show-title-ids')

          update()

          el.on('click', (ev) ->
            ev.stopPropagation()
            ev.preventDefault()
            window.DP_DO_SHOW_IDS = !window.DP_DO_SHOW_IDS
            scope.$apply(-> update())
          )
      }
    ])

    Module.directive('dpGo', [ '$location', ($location) ->
      return {
        restrict: 'A',
        link: (scope, el, attrs) ->
          el.on('click', (ev) ->
            ev.stopPropagation();
            ev.preventDefault();

            path = attrs.dpGo.replace(/^#/, '')
            search = scope.$eval(attrs.dpGoParams)

            scope.$apply(->
              $location.path(path)
              if search
                $location.search(search)
            )
          )
      }
    ])

    Module.directive('dpNoDrag', [ ->
      return {
        restrict: 'AC',
        link: (scope, el, attrs) ->
          el.get(0).draggable = false
      }
    ])

    Module.directive('dpMoveListToPos', [ '$timeout', ($timeout) ->
      return {
        restrict: 'A',
        scope: {},
        link: (scope, element, attrs) ->
          initial_run = false
          is_running = false
          run_again = false
          scope.$on('resetDisplayOrders', ->
            if not initial_run
              initial_run = false
              run_again = true
              update()
            else
              $timeout(->
                if is_running
                  run_again = true
                else
                  update()
              , 1)
          )

          update = ->
            is_running = true
            all_lis = element.find('> li').filter('[data-move-to-pos]')

            all_lis.each(->
              li = $(this)
              toPos = parseInt(li.data('move-to-pos') || 0) || 0
              if toPos == 0 || isNaN(toPos)
                return

              use = null
              element.find('> li').each(->
                ro = parseInt($(this).attr('data-run-order') || 0) || 0
                if ro == 0 || isNaN(ro)
                  return
                if ro < toPos and this != element[0]
                  use = $(this)
              )
              li.detach()
              if not use
                li.detach().prependTo(element)
              else
                li.detach().insertAfter(use)
            )

            if run_again
              is_running = true
              $timeout(->
                run_again = false
                update()
              , 1)
            else
              is_running = false
      }
    ])

    Module.directive('dpHtmlRenderVar', [ ->
      return {
        restrict: 'A',
        link: (scope, element, attrs) ->
          scope.$watch(attrs.dpHtmlRenderVar, (newVal) ->
            element.html(newVal)
          )
      }
    ])
