define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketLabels_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketLabels_Ctrl_Edit'
		@CTRL_AS = 'TicketLabelsEdit'
		@DEPS = ['$scope', 'Growl', 'TicketLabelsData', 'Api', '$stateParams']
		@CTRL_TYPE = 'page'

		init: ->
			@label_object = {label: '', count: 0}
			@$scope.saving_label = false
			@form = {}
			@$scope.form = @form
			return

		initialLoad: ->
			list_promise = @TicketLabelsData.loadList().then((recs) =>
				@labels = recs
				if @$stateParams.label
					@label_object = @TicketLabelsData.getObjectByLabel(@$stateParams.label)
					if not angular.isUndefined(@label_object)
						@$scope.form.label = @$stateParams.label
					else
						@label_object = {label: '', count: 0}
			)

			return @$q.all([list_promise]);

		addNewLabel: ->
			return false if not @form.label
			@startSpinner('saving_label')
			@Api.sendPost('/ticket_labels', {label: @form.label})
			.success(=>
					@labels.push({label: @form.label, count: 0})
					@label_object = @TicketLabelsData.getObjectByLabel(@form.label)
					@stopSpinner('saving_label', true).then(=>
						@Growl.success(@getRegisteredMessage('saved_label'))
					)
				)
			.finally(=>
					@stopSpinner('saving_label', true)
				)


		saveLabel: ->
			return false if not @form.label
			if not @label_object.label
				return @addNewLabel()
			@startSpinner('saving_label')
			@Api.sendPost('/ticket_labels/save', {
				label_old: @label_object.label, label_new: @form.label
			}).success(=>
				@label_object.label = @form.label
				@stopSpinner('saving_label', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_label'))
				)
			).error(=>

			).finally(=>
				@stopSpinner('saving_label', true)
			)

	Admin_TicketLabels_Ctrl_Edit.EXPORT_CTRL()