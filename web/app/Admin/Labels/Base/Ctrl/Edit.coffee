define [
	'Admin/Main/Ctrl/Base'
	'angular'
], (Admin_Ctrl_Base, angular) ->
	class Admin_Labels_Base_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_AS = 'LabelsEdit'
		@DEPS = ['em', '$stateParams', '$rootScope', 'LabelManager', 'LabelDefinition']



		init: ->
			@endpoint = '/labels/definitions'
			@$scope.isNew = true if !@$stateParams.label
			@definition = null
			@$scope.picker = false
			@$scope.colors = [
				'#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
				'#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
			]
			@$scope.form = {label: '', color: @$scope.colors[0], label_type: @type()}
			@$scope.startDelete = => @startDelete()



		type: ->
			throw new Exception 'This method must be implemented by a sub-class'



		state: (to) ->
			to = '.' + to if to
			@$state.current.name.replace /(.+)\.edit|\.create$/, '$1' + to



		initialLoad: ->
			if @$stateParams.label
				@LabelDefinition.get(@type(), @$stateParams.label).then (def) =>
					return if !def?
					@definition = def
					# @definition !== @$scope.form
					@$scope.form = angular.copy def



		saveLabel: ->
			return false if not @$scope.form.label
			return false if @definition && @definition.label == @$scope.form.label && @definition.color == @$scope.form.color

			@startSpinner 'saving_label'
			@Api.sendPutJson @endpoint, {old: @definition || {}, new: @$scope.form}

			.success (data) =>
				@stopSpinner('saving_label', true).then =>
					@Growl.success @getRegisteredMessage 'saved_label'

				@LabelDefinition.update @definition, data
				@definition = data

				if @$scope.isNew
					@$state.go @state('gocreate')
				else
					@$state.go @state('edit'), {label: data.label}

			.error =>
				@Growl.error @getRegisteredMessage 'not_saved_label'

			.finally =>
				@stopSpinner 'saving_label', true



		startDelete: () ->
			return if !@definition

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Labels/delete-modal.html'),
				controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then =>
				@Api.sendDelete @endpoint, @definition

				.success =>
					@LabelDefinition.remove @definition
					@$state.go @state ''

			  # todo
				.error =>
					@$state.go @state ''

				# todo
				.finally =>
					@$state.go @state ''