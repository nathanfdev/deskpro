define ->
	###
    # Description
    # -----------
    #
    # This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
    #
    # Example
    # -------
    # <button dp-open-phrase-editor="agent.general.departments">Edit Phrase</button>
    ###
	Admin_Main_Directive_DpOpenPhraseEditor = ['$modal', '$controller', ($modal, $controller) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				element.on('click', (ev) ->
					ev.preventDefault()

					editorOptions = scope.$eval(attrs.dpOpenPhraseEditor)

					modalInstance = $modal.open({
						templateUrl: DP_BASE_ADMIN_URL+'/load-view/Languages/modal-translate-phrase.html',
						controller: 'Admin_Languages_Ctrl_TranslateModal',
						resolve: {
							phraseId: ->
								return editorOptions.phraseId || null

							getWaitOnPromise: ->
								if attrs.dpSavePhrasePromise
									promise = scope.$eval(attrs.dpSavePhrasePromise)
									return promise
								else
									return null

							getPhraseIdGen: ->
								->
									if attrs.dpPhraseIdGen
										return (id) ->
											id_vars = scope.$eval(attrs.dpPhraseIdGen)
											for own find_k, replace_v of id_vars
												re = new RegExp('%' + find_k + '%', 'g')
												id = id.replace(re, replace_v)

											return id
									else
										return null
						}
					})
				)
				return
		}
	]

	return Admin_Main_Directive_DpOpenPhraseEditor