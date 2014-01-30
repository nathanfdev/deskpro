define [
	'DeskPRO/Directive/DpTimeWithUnit',
	'DeskPRO/Directive/DpStateMark',
	'Admin/Main/Directive/DpHelpPage',
], (
	DeskPRO_Directive_DpTimeWithUnit,
	DeskPRO_Directive_DpStateMark,
	Admin_Main_Directive_DpHelpPage,
) ->
	return (Module) ->
		Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit)
		Module.directive('dpStateMark',                    DeskPRO_Directive_DpStateMark)
		Module.directive('dpHelpPage',                     Admin_Main_Directive_DpHelpPage)