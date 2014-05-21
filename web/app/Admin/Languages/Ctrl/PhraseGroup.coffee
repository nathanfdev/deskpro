define [
	'Admin/Main/Ctrl/Base',
	'Admin/Languages/PhraseSaver'
], (
	Admin_Ctrl_Base,
	PhraseSaver
) ->
	class Admin_Languages_Ctrl_PhraseGroup extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Languages_Ctrl_PhraseGroup'
		@CTRL_AS = 'EditCtrl'

		init: ->
			@langId  = @$stateParams.id.replace(/^phrases\-/, '')
			@groupId = @$stateParams.groupId

		initialLoad: ->
			promise = @Api.sendDataGet({
				phrase_info: "/langs/#{@langId}/#{@groupId}"
			}).then((result) =>
				@phrases = result.data.phrase_info.phrases
			)
			return promise

		doSave: ->
			@startSpinner('saving')
			saver = new PhraseSaver(@Api, @$q)
			saver.savePhrases(@langId, @phrases).then(=>
				@stopSpinner('saving')
			)

		###
    	# Opens new phrase modal
		###
		openNewPhrase: ->
			langId  = @langId
			groupId = @groupId
			phrasesCollection = @phrases

			@$modal.open({
				templateUrl: @getTemplatePath('Languages/modal-new-phrase.html'),
				controller: [ '$modalInstance', '$scope', 'Api', '$state', ($modalInstance, $scope, Api, $state) ->

					$scope.phrase = {name: '', phrase: ''}

					$scope.$watch('phrase.name', ->
						$scope.phrase.name = $scope.phrase.name.toLowerCase()
						$scope.phrase.name = $scope.phrase.name.replace(/\s/g, '-')
						$scope.phrase.name = $scope.phrase.name.replace(/[^a-z0-9\.\-_]/g, '')
					)

					$scope.dismiss = ->
						$modalInstance.dismiss('cancel')

					$scope.save = ->
						$scope.is_loading = true

						postData = {
							phrases: [{ name: 'custom.' + $scope.phrase.name, phrase: $scope.phrase.phrase}]
						}
						Api.sendPostJson("/langs/#{langId}/phrases", postData).then(->
							$modalInstance.close()
							$state.go('setup.phrases_go_viewgroup', {path: 'phrases-go-' + langId + '-' + groupId})
						)
				],
			}).result.then( (newPhrase) =>
				if not newPhrase then return
			)

	Admin_Languages_Ctrl_PhraseGroup.EXPORT_CTRL()