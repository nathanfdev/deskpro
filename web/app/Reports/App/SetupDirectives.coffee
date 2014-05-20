define [
	'DeskPRO/Directive/DpTimeWithUnit',
	'DeskPRO/Directive/DpStateMark',
	'DeskPRO/Directive/DpHelpPage',
	'DeskPRO/Directive/DpNavSubnav',
	'Reports/Directive/DpReportBuilderSelectBox',
	'Reports/Directive/DpReportBillingSelectBox',
	'Reports/Directive/DpReportBuilderTitle',
	'DeskPRO/Directive/DpTabBody',
	'DeskPRO/Directive/DpTabBtn',
	'DeskPRO/Directive/DpHideSpinning',
	'DeskPRO/Directive/DpShowSpinning',
	'DeskPRO/Directive/DpSubmitForm',
	'DeskPRO/Directive/DpErrorClass',
], (
	DeskPRO_Directive_DpTimeWithUnit,
	DeskPRO_Directive_DpStateMark,
	DeskPRO_Directive_DpHelpPage,
	DeskPRO_Directive_DpNavSubnav,
	Reports_Directive_DpReportBuilderSelectBox,
	Reports_Directive_DpReportBillingSelectBox,
	Reports_Directive_DpReportBuilderTitle,
	DeskPRO_Directive_DpTabBody,
	DeskPRO_Directive_DpTabBtn,
	DeskPRO_Directive_DpHideSpinning,
	DeskPRO_Directive_DpShowSpinning,
	DeskPRO_Directive_DpSubmitForm,
	DeskPRO_Directive_DpErrorClass,
) ->
	return (Module) ->
		Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit)
		Module.directive('dpStateMark',                    DeskPRO_Directive_DpStateMark)
		Module.directive('dpHelpPage',                     DeskPRO_Directive_DpHelpPage)
		Module.directive('dpNavSubnav',                    DeskPRO_Directive_DpNavSubnav)
		Module.directive('dpReportBuilderSelectBox',       Reports_Directive_DpReportBuilderSelectBox)
		Module.directive('dpReportBillingSelectBox',       Reports_Directive_DpReportBillingSelectBox)
		Module.directive('dpReportBuilderTitle',           Reports_Directive_DpReportBuilderTitle)
		Module.directive('dpTabBody',                      DeskPRO_Directive_DpTabBody)
		Module.directive('dpTabBtn',                       DeskPRO_Directive_DpTabBtn)
		Module.directive('dpHideSpinning',                 DeskPRO_Directive_DpHideSpinning)
		Module.directive('dpShowSpinning',                 DeskPRO_Directive_DpShowSpinning)
		Module.directive('dpSubmitForm',                   DeskPRO_Directive_DpSubmitForm)
		Module.directive('dpErrorClass',                   DeskPRO_Directive_DpErrorClass)