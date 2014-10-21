define ->
	###
    # Description
    # -----------
    #
    # Read an input file and pass the text contents to a function
    #
    # Example
    # -------
    # <input type="file" dp-read-file="readContentsFunction($fileContent)" />
    ###
	Admin_Main_Directive_DpReadFile = [
		'$parse', ($parse) ->
			return {
				restrict: 'A',
				scope: false,
				link: (scope, element, attrs) ->
					fn = $parse(attrs.dpReadFile)

					element.on('change', (onChangeEvent) ->
						reader = new FileReader()

						reader.onload = (onLoadEvent) ->
							scope.$apply () ->
								fn(scope, {$fileContent: onLoadEvent.target?.result || onLoadEvent.result})


						fileSrc = onChangeEvent.srcElement || onChangeEvent.target
						reader.readAsText fileSrc.files[0]
					)
			}
	]

	return Admin_Main_Directive_DpReadFile