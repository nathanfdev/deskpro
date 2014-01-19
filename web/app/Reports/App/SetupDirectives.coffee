define [
	'DeskPRO/Directive/DpTimeWithUnit',
], (
	DeskPRO_Directive_DpTimeWithUnit,
) ->
	return (Module) ->
		Module.directive('dpTimeWithUnit',                 DeskPRO_Directive_DpTimeWithUnit)