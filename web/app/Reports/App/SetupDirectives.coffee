define [
	'DeskPRO/Directive/DpTimeWithUnit',
	'DeskPRO/Directive/DpStateMark',
	'DeskPRO/Directive/DpHelpPage',
], (
	DeskPRO_Directive_DpTimeWithUnit,
	DeskPRO_Directive_DpStateMark,
	DeskPRO_Directive_DpHelpPage,
) ->
	return (Module) ->
		Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit)
		Module.directive('dpStateMark',                    DeskPRO_Directive_DpStateMark)
		Module.directive('dpHelpPage',                     DeskPRO_Directive_DpHelpPage)