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
	'DeskPRO/Directive/DpShowSpinning',
	'DeskPRO/Directive/DpSubmitForm',
	'DeskPRO/Directive/DpErrorClass',

	'Admin/Main/Directive/Autofocus',
	'Admin/Main/Directive/BgImg',
	'Admin/Main/Directive/DpCommaSeparated',
	'Admin/Main/Directive/DpDevBar',
	'Admin/Main/Directive/DpInhelpBody',
	'Admin/Main/Directive/DpInhelpBtn',
	'Admin/Main/Directive/DpListAutoload',
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

	'Admin/Portal/Directive/PortalEditor',
	'Admin/TicketDeps/Directive/LayoutEditor',
	'Admin/TicketDeps/Directive/LayoutEditorField',
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
	DeskPRO_Directive_DpShowSpinning,
	DeskPRO_Directive_DpSubmitForm,
	DeskPRO_Directive_DpErrorClass,

	Admin_Main_Directive_Autofocus,
	Admin_Main_Directive_BgImg,
	Admin_Main_Directive_DpCommaSeparated,
	Admin_Main_Directive_DpDevBar,
	Admin_Main_Directive_DpInhelpBody,
	Admin_Main_Directive_DpInhelpBtn,
	Admin_Main_Directive_DpListAutoload,
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

	Admin_Portal_Directive_PortalEditor,
	Admin_TicketDeps_Directive_LayoutEditor,
	Admin_TicketDeps_Directive_LayoutEditorField,
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
		Module.directive('dpShowSpinning',                 DeskPRO_Directive_DpShowSpinning)
		Module.directive('dpSubmitForm',                   DeskPRO_Directive_DpSubmitForm)
		Module.directive('dpErrorClass',                   DeskPRO_Directive_DpErrorClass)

		Module.directive('autofocus',                      Admin_Main_Directive_Autofocus)
		Module.directive('bgImg',                          Admin_Main_Directive_BgImg)
		Module.directive('dpCommaSeparated',               Admin_Main_Directive_DpCommaSeparated)
		Module.directive('dpDevbar'        ,               Admin_Main_Directive_DpDevBar)
		Module.directive('dpInhelpBody',                   Admin_Main_Directive_DpInhelpBody)
		Module.directive('dpInhelpBtn',                    Admin_Main_Directive_DpInhelpBtn)
		Module.directive('dpListAutoload',                 Admin_Main_Directive_DpListAutoload)
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

		Module.directive('dpPortalEditor',                 Admin_Portal_Directive_PortalEditor)

		Module.directive('dpTicketLayoutEditor',           Admin_TicketDeps_Directive_LayoutEditor)
		Module.directive('dpTicketLayoutEditorField',      Admin_TicketDeps_Directive_LayoutEditorField)