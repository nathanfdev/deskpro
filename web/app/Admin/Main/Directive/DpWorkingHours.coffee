define ['DeskPRO/Data/TzData'], (TzData) ->
	Admin_Main_Directive_DpWorkingHours = [ ->
		return {
			restrict: 'E',
			require: 'ngModel',
			replace: true,
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Common/work-hours-directive.html',
			scope: {},
			link: (scope, element, attrs, ngModel) ->

				scope.timezone       = 'UTC'
				scope.start_hour     = 9
				scope.start_min      = 0
				scope.end_hour       = 18
				scope.end_min        = 0
				scope.hol_year       = (new Date()).getFullYear()
				scope.hol_year       = (new Date()).getFullYear()
				scope.hol_new_month  = 1
				scope.hol_new_day    = 1
				scope.hol_new_name   = ''

				#------------------------------
				# Element references
				#------------------------------

				els = {
					hol_wrap:        element.find('.holiday-rows'),
				}

				#------------------------------
				# Init holiday years
				#------------------------------

				year = (new Date()).getFullYear()
				year_end = year + 4

				for i in [year..year_end]
					row = $('<div class="holiday-year-rows year-row year-'+i+'"></div>')
					row.appendTo(els.hol_wrap)

				row = $('<div class="holiday-year-rows year-repeat"></div>')
				row.appendTo(els.hol_wrap)

				#------------------------------
				# Adding/removing holidays
				#------------------------------

				holExists = (hol1, hol2) ->
					if hol1.year != hol2.year
						return false

					if hol1.month != hol2.month
						return false

					if hol1.day != hol2.day
						return false

					return true

				scope.addHoliday = ($event) ->
					$event.preventDefault()
					$event.stopPropagation()

					year   = parseInt(scope.hol_year)
					month  = parseInt(scope.hol_new_month)
					day    = parseInt(scope.hol_new_day)
					title  = $.trim(scope.hol_new_name)
					repeat = scope.hol_new_repeat

					if repeat
						year = 0

					if not month or not day
						return

					if not ngModel.$modelValue
						ngModel.$modelValue = {}

					if not ngModel.$modelValue.holidays?
						ngModel.$modelValue.holidays = []

					exists = false
					hol = { year: year, month: month, day: day, title: title }

					for checkHol in ngModel.$modelValue.holidays
						if holExists(hol, checkHol)
							exists = true
							break

					if exists
						scope.hol_new_name = ''
						scope.show_newhold = false
						return

					ngModel.$modelValue.holidays.push(hol)

					if repeat
						y_str = 'Every Year'
						rowContainer = els.hol_wrap.find('.year-repeat')
					else
						y_str = year
						rowContainer = els.hol_wrap.find('.year-' + year)

					row = $('<div class="hol-row"><div class="remove-btn"><i class="icon-remove-sign"></i></div> <span class="date-txt"></span> <span class="title-txt"></span></div></div>')

					m_str = if month < 10 then "0#{month}" else month
					d_str = if day < 10 then "0#{day}" else day
					row.find('.date-txt').text("#{y_str}-#{m_str}-#{d_str}")
					if title.length
						row.find('.title-txt').text(title)

					row.data('holRec', hol)

					scope.hol_new_name = ''
					scope.show_newhold = false
					rowContainer.append(row)
					updateYearList()

				element.find('.add-btn').on('click', (ev) ->
					scope.$apply( ->
						scope.addHoliday(ev)
					)
				)

				element.on('click', '.remove-btn', (ev) ->
					ev.preventDefault()
					holRec = $(this).data('holRec')
					$(this).closest('.hol-row').remove()

					if not ngModel.$modelValue?.holidays?
						return

					for hol, idx in ngModel.$modelValue.holidays
						if hol == holRec
							ngModel.$modelValue.holidays.slice(idx, 1)
							break
				)

				updateYearList = ->
					any = false
					if els.hol_wrap.find('.year-repeat').find('.hol-row')[0]
						any = true

					els.hol_wrap.find('.year-row').hide()
					if els.hol_wrap.find('.year-' + scope.hol_year).show().find('.hol-row')[0]
						any = true

					if any
						els.hol_wrap.show()
					else
						els.hol_wrap.hide()

					return null

				element.find('.holiday-years').on('change', ->
					updateYearList()
				)

				ngModel.$render = ->
					updateYearList()
		}
	]

	return Admin_Main_Directive_DpWorkingHours