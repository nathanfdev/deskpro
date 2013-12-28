define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserRules_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserRules_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@userRulesData = @DataService.get('UserRules')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @userRulesData.loadList().then( (list) =>
				@list = list
			)

			return promise

		###
		# Show the delete dlg
		###

		startDelete: (for_rule_id) ->

			rule = @userRulesData.findListModelById(for_rule_id)

			inst = @$modal.open({
				templateUrl: @getTemplatePath('UserRules/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close()

					$scope.dismiss = ->
						$modalInstance.dismiss()
				]
			});

			inst.result.then( =>
				@deleteUserRule(rule)
			)

		###
		# Actually do the delete
		###

		deleteUserRule: (for_rule) ->

			@userRulesData.deleteUserRuleById(for_rule.id).success( =>

				if @$state.current.name == 'crm.rules.edit' and parseInt(@$state.params.id) == for_rule.id
					@$state.go('crm.rules')

			).error((info, code) =>
				@applyErrorResponseToView(info)
			)

	Admin_UserRules_Ctrl_List.EXPORT_CTRL()