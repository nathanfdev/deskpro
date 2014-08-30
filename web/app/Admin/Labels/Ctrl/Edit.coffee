define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Labels_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Labels_Ctrl_Edit'
		@CTRL_AS = 'LabelsEdit'
		@DEPS = ['em', '$stateParams', '$rootScope', 'LabelDefinition']



		init: ->
			@type = @$state.current.data.type
			@endpoint = '/labels/definitions'
			@$scope.isNew = true if !@$stateParams.label
			@definition = null
			@$scope.picker = false
			@$scope.colors = [
				'#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
				'#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
			]
			@$scope.form = {label: '', color: @$scope.colors[0], label_type: @type}
			@$scope.startDelete = => @startDelete()



		state: (to) ->
			to = '.' + to if to
			@$state.current.name.replace /(.+)\.edit|\.create$/, '$1' + to



		initialLoad: ->
			if @$stateParams.label
				@LabelDefinition.get(@type, @$stateParams.label).then (def) =>
					return if !def?
					@definition = def
					# @definition !== @$scope.form
					@$scope.form = angular.copy def



		saveLabel: ->
			return false if not @$scope.form.label
			return false if @definition && @definition.label == @$scope.form.label && @definition.color == @$scope.form.color

			dummy = $('<i></i>').css 'color', @$scope.form.color
			color = dummy.css 'color'
			if 0 == color.indexOf 'rgb'
				color = color.replace /^[^\d]+(\d{1,3})\s*\,\s*(\d{1,3})\s*\,\s*(\d{1,3}).+/, "$1,$2,$3"
				parts = color.split ','
				color = ((parts[0] << 16)|(parts[1] << 8)|parts[2]).toString 16
				color = '0' + color if color.length < 6
				color = '#' + color
			@$scope.form.color = color

			@startSpinner 'saving_label'

			if @definition
				sendData = {old: @definition || {}, new: @$scope.form}
				method = 'sendPutJson'
			else
				sendData = @$scope.form
				method = 'sendPostJson'

			@Api[method] @endpoint, sendData

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



	Admin_Labels_Ctrl_Edit.EXPORT_CTRL()