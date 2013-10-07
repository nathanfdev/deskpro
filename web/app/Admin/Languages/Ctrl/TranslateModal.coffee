define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
	class Admin_Languages_Ctrl_TranslateModal extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Languages_Ctrl_TranslateModal'
		@CTRL_TYPE = 'modal'
		@CTRL_AS   = 'TranslateModal'
		@DEPS      = ['$timeout', '$modalInstance', 'phraseId', 'getWaitOnPromise', 'getPhraseIdGen']

		init: ->
			@phrase_map = {}
			@active_lang = null
			@active_trans = null
			@hasPendingPromise = false

			@$scope.dismiss = =>
				@$modalInstance.dismiss('cancel')

			@$scope.save = =>
				if @active_lang
					@phrase_map[@active_lang] = @active_trans

				@savePhrases()
				@$modalInstance.close()

			@$scope.$watch(=>
				return @active_lang
			, (newLangId, oldLangId) =>
				if not oldLangId then return

				@phrase_map[oldLangId] = @active_trans

				if @phrase_map[newLangId]
					@active_trans = @phrase_map[newLangId]
				else
					@active_trans = ''
			)

			@$scope.$watch(=>
				return @active_trans
			, =>
				if not @active_lang then return
				@phrase_map[@active_lang] = @active_trans
			)

		initialLoad: ->
			p = @Api.sendDataGet([
				'/langs',
				'/langs/phrases/' + @phraseId
			]).success( (data) =>
				@langs = data.api_langs.languages

				first = null
				for phrase in data.api_langs_getphrase.lang_phrases
					if not first then first = phrase
					lang_id = phrase.language.id
					@phrase_map[lang_id] = phrase.phrase

				if first
					@active_lang  = first.language.id
					@active_trans = first.phrase
				else
					@active_lang = @langs[0].id
					@active_trans = null
			)

			return p

		savePhrases: ->
			promise = @getWaitOnPromise()
			if not promise
				@doSavePhrases()

			# It's already queued to save
			if @hasPendingPromise
				return

			@hasPendingPromise = true
			promise.then(=>
				@doSavePhrases()
			).finally(=>
				@hasPendingPromise = false
			)

		doSavePhrases: ->
			phrase_map = angular.copy(@phrase_map)
			phrase_id = @phraseId

			phraseIdGen = @getPhraseIdGen()
			if phraseIdGen
				phrase_id = phraseIdGen(phrase_id)

			postData = {'lang_phrases': []}

			for own k, v of phrase_map
				postData.lang_phrases.push({
					phrase: v || '',
					language_id: k
				})

			promise = @Api.sendPostJson('/langs/phrases/' + phrase_id, postData)

			return promise

	Admin_Languages_Ctrl_TranslateModal.EXPORT_CTRL()