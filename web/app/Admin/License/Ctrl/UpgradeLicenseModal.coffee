define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) ->
	class Admin_License_Ctrl_UpgradeLicenseModal extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_License_Ctrl_UpgradeLicenseModal'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['$modalInstance', 'upgradeType', 'upgradeOptions', 'Api', 'DpLicense']

		init: ->
			@$scope.upgradeType     = @upgradeType
			@$scope.upgradeOptions  = @upgradeOptions
			@$scope.initial_loading = true
			@$scope.phase           = 1
			@$scope.paymentForm = {}

			@$scope.month_opts = []
			for i in [1..12]
				@$scope.month_opts.push(if i < 10 then "0#{i}" else i)

			@$scope.year_opts = []
			for i in [(new Date().getFullYear())..(new Date().getFullYear())+10]
				@$scope.year_opts.push((i+"").substr(2))

			@$scope.$watch('paymentForm.new_card', =>
				if @$scope.formErrors and @$scope.formErrors.length
					@validate()
			, true)
			@$scope.$watch('paymentForm.mode', =>
				if @$scope.formErrors and @$scope.formErrors.length
					@$scope.formErrors = []
			)

			@$scope.dismiss      = => @$modalInstance.dismiss()
			@$scope.closeSuccess = => @$modalInstance.close()

			@DpLicense.getPlanUpgradeInfo().then((info) =>
				if info.error_code
					console.error("License server error code: #{info.error_code}")
					@$scope.not_online = true
					return

				@planInfo = info
				@$scope.planInfo = info
				@$scope.initial_loading = false
				@$scope.paymentForm.exist_card = info.card_details || null
				@$scope.paymentForm.invoice = info.invoice || null

				if info.currency_pref == 'usd'
					name = 'upgrade_cost_total_display'
				else
					name = 'upgrade_cost_total_' + info.currency_pref + '_display'

				@$scope.paymentSummary = {
					line_title:         "Upgrade license to " + info.next_plan.agents + " agents",
					cost:                   info.next_plan.upgrade_cost,
					cost_display:           info.next_plan.upgrade_cost_display,
					cost_vat:               info.next_plan.upgrade_cost_vat,
					cost_vat_display:       info.next_plan.upgrade_cost_vat_display,
					cost_total:             info.next_plan.upgrade_cost_total,
					cost_total_display:     info.next_plan.upgrade_cost_total_display,
					currency_total_display: info.next_plan[name]
					currency:               info.currency_pref
					currency_display:       info.currency_pref.toUpperCase()
					vat_rate:               info.vat_rate,
					has_vat:                info.vat_rate > 0.0,
					invoice_link:           if info.invoice then info.invoice.pdf_link else null
				}

				if @$scope.paymentForm.exist_card
					@$scope.paymentForm.mode = 'exist'
				else
					@$scope.paymentForm.mode = 'new'

				if not info.allow_inline_form
					@$scope.not_online = true

# DEBUG
#				@$scope.paymentForm.mode = 'new'
#				@$scope.paymentForm.new_card = {
#					number: '4929000000006',
#					cv2: '123',
#					name: 'CN',
#					expire_yy: '18'
#					expire_mm: '01',
#					type: 'visa'
#				}
			, =>
				@$scope.not_online = true
			)
			return

		validate: ->
			errors = []
			if @$scope.paymentForm.mode == 'new'
				card = @$scope.paymentForm.new_card
				if not card.number or not card.number.length
					errors.push('number')
				if not card.cv2 or not card.cv2.length
					errors.push('cv2')
				if not card.name or not card.name.length
					errors.push('name')
				if not parseInt(card.expire_mm) or not parseInt(card.expire_yy)
					errors.push('expire')

			@$scope.formErrors = errors

			return errors.length == 0

		sendPayment: ->
			if not @validate() then return
			@$scope.loading = true
			@$scope.phase = 2
			@$scope.stepId = 0
			@$scope.error_code = null

			@DpLicense.sendPayInvoiceRequest(@$scope.paymentForm.mode, @$scope.paymentForm.new_card, @planInfo.invoice.id).then( (data) =>

				if not data.success
					@$scope.phase = 1
					@$scope.loading = false
					if data.error_message
						@$scope.formErrors = [data.error_message]
					else
						@$scope.formErrors = ["There was a problem processing your payment. Please try agian."]
					return

				@$scope.stepId = 1

				@DpLicense.getNewLicenseKey().then( (res) =>
					@$scope.stepId = 2

					@DpLicense.setNewLicenseCode(res.license_code).then(=>
						@$scope.loading = false
						@$scope.phase = 3
						@$scope.show_done = true
					, ->
						@$scope.loading = false
						@$scope.error_code = 'failed_get_lic_key'
					)
				, ->
					@$scope.loading = false
					@$scope.error_code = 'failed_get_lic_key'
				)

			, (data) =>
				@$scope.loading = false
				@$scope.phase = 1
				@$scope.formErrors = ["There was a problem processing your payment."]
			)

	Admin_License_Ctrl_UpgradeLicenseModal.EXPORT_CTRL()