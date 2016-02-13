define ['Admin/Main/Ctrl/Base'], (Admin_Main_Ctrl_Base) ->
	class Admin_Brand_Ctrl_List extends Admin_Main_Ctrl_Base
		@CTRL_ID = 'Admin_Brand_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@CTRL_TYPE = 'list'
		@DEPS = ['BrandData']

		init: ->
			@brands = []

		initialLoad: ->
			list_promise = @BrandData.loadList().then((recs) =>
				@brands =  recs.values()

				if @$state.current.name == 'brand' or @$state.current.name == 'brand.setup'
					if @brands[0]
						@$state.go('brand.setup.edit', {id: @brands[0].id})
					else
						@$state.go('brand.setup.create')

				@addManagedListener(@BrandData.recs, 'changed', =>
					@brands = @BrandData.recs.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise]);

		startDelete: (for_acc_id) ->
			for_acc = null
			for v in @brands
				if v.id == for_acc_id
					for_acc = v

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Brand/delete-modal.html'),
				controller: [
					'$scope', '$modalInstance', ($scope, $modalInstance) ->
						$scope.confirm = ->
							$modalInstance.close();

						$scope.dismiss = ->
							$modalInstance.dismiss();
				]
			});

			inst.result.then(=>
				@deleteAccount(for_acc)
			)

		deleteAccount: (acc) ->
			@Api.sendDelete('/brands/' + acc.id).success(=>
				@BrandData.remove(acc.id)
				@ngApply()

				# if currently viewing the deleted account, then should need to switch state
				if @$state.current.name == 'brand.setup.edit' and parseInt(@$state.params.id) == acc.id
					@$state.go('brand.setup')
			)

	Admin_Brand_Ctrl_List.EXPORT_CTRL()
