define [
	'DeskPRO/Util/Util',
	'DeskPRO/Util/Strings',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Numbers'
], (
	Util,
	Strings,
	Arrays,
	Numbers
) ->
	class DeskPRO_CategoryBuilder_Controller
		constructor: (@$scope, @$element, @$attrs, @$compile, @$q) ->
			@$scope.categoryBuilder = @
			@$scope.new_cat_title = ''
			@$scope.new_cat_parent = '0'
			@$scope.parent_cat_list = []
			@$scope.sortedListOptions = {
				axis: 'y',
				handle: '.dp-cb-row-move',
				update: (ev, data) =>
					@updateOrder()
			}

			@addRowEl = @$element.find('.dp-cb-newrow')
			@rootListEl = @$element.find('.dp-cb-root')

			me = @
			@$element.on('click', '.dp-cb-addbtn', (ev) ->
				ev.preventDefault()
				me.addNewCatFromTrigger(this)
			)

			@maxDepth = 1000
			if @$attrs.maxDepth
				@maxDepth = parseInt(@$attrs.maxDepth)


			if @maxDepth < 1
				@addRowEl.find('.dp-cb-select-wrap').hide()

			@$element.on('click', '.remove-trigger', (ev) ->
				ev.preventDefault()
				row = $(this).closest('li')

				removeIds = [row.data('catId')]
				row.find('li').each(->
					removeIds.push($(this).data('catId'))
				)

				viewValue = me.ngModel.$viewValue || []

				for id in removeIds
					delete me.cat_rows[id]
					idx = null
					for cat,k in viewValue
						if cat.id == id
							idx = k
							break
					if idx != null
						viewValue.splice(idx,1)

				row.slideUp(200, ->
					me.$scope.$apply( ->
						row.remove()
						me.ngModel.$setViewValue(viewValue)
						me.updateView(viewValue)
					)
				)
			)

		updateOrder: ->


		setModel: (@ngModel) ->
			@cat_rows = {}
			@ngModel.$render = =>
				@updateView(@ngModel.$modelValue)

			@ngModel.$parsers.push( (viewValue) ->
				return viewValue || []
			)

			@ngModel.$formatters.push( (modelValue) ->
				return modelValue
			)

		updateView: (cats) ->
			if not cats
				cats = []

			for cat in cats
				if @cat_rows[cat.id]?
					@cat_rows[cat.id][0].detach()
				else
					@cat_rows[cat.id] = @renderRow(cat)

			old_parent_opt = @$scope.new_cat_parent
			@$scope.new_cat_parent = 0
			@$scope.parent_cat_list = []
			@$scope.parent_cat_list = []
			old_p = @rootListEl.parent()
			@rootListEl.detach()
			@_procCats(cats, @rootListEl, 0)
			@rootListEl.find('.dp-cb-addrow').each(->
				list = this.parentNode;
				$(this).detach().appendTo(list)
			)
			@rootListEl.prependTo(old_p)
			@$scope.new_cat_parent = old_parent_opt

			if @$attrs.saveFlatArray
				proc = (parent_id, title_segs) ->
					select_options = []
					for opt in cats
						if opt.parent_id == parent_id
							title_segs.push(opt.title)
							sub_options = proc(opt.id, title_segs)

							if sub_options.length
								Arrays.append(select_options, sub_options)
							else
								select_options.push({
									id: opt.id,
									title: title_segs.join(' > ')
								})

							title_segs.pop()

					return select_options

				@$scope.saveFlatArray = proc(null, [])

		_procCats: (cats, parentRow, parent_id, parent_titles = '', depth = 0) ->
			for cat in cats
				do_add = false
				if not parent_id and not cat.parent_id
					do_add = true
				else if parent_id and cat.parent_id == parent_id
					do_add = true

				if not do_add then continue

				if parent_titles.length
					full_title = parent_titles + ' > ' + cat.title
				else
					full_title = cat.title

				if depth+1 < @maxDepth
					@$scope.parent_cat_list.push({
						id: cat.id,
						title: full_title
					})

				row = @cat_rows[cat.id][0]
				@_procCats(cats, $(row[0]).find('> ul').first(), cat.id, full_title, depth + 1)
				$(row[0]).appendTo(parentRow)

		renderRow: (cat) ->
			tpl = """
				<li class="dp-cb-row">
					<div class="dp-cb-titlewrap">
						<div class="dp-cb-row-move"><i class="fa fa-bars"></i></div>
						<div class="dp-cb-row-controls">
							<i class="fa fa-times-circle remove-trigger"></i>
						</div>
						<div class="dp-cb-row-indent"></div>
						<input type="text" class="form-control dp-cb-input" ng-model="cat.title" placeholder="Enter title..." />
					</div>
					<ul ui-sortable="sortedListOptions"></ul>
				</li>
			"""

			rowScope = @$scope.$new()
			rowScope.sortedListOptions = @$scope.sortedListOptions

			rowScope.cat = cat
			newRow = @$compile(tpl)(rowScope)
			newRow.data('catId', cat.id)
			return [newRow, rowScope]

		addCat: (catData) ->
			viewValue = @ngModel.$viewValue || []
			viewValue.push(catData)
			@ngModel.$setViewValue(viewValue)
			@updateView(viewValue)

		addNewCatFromTrigger: (triggerEl) ->
			rowEl = $(triggerEl).closest('.dp-cb-addrow')
			title = Strings.trim(@$scope.new_cat_title)

			if title == ''
				return

			parent_id = @$scope.new_cat_parent
			if not parent_id or parent_id == "" or parent_id == "0" or parent_id == 0
				parent_id = null
			else if Numbers.isNumeric(parent_id)
				parent_id = parseInt(parent_id)

			catData = {
				id:            Util.uid('cb_'),
				"@is_new":     true,
				title:         title,
				parent_id:     parent_id,
				display_order: 0
			}

			if rowEl.data('parentId')
				catData.parent_id = rowEl.data('parentId')

			@$scope.new_cat_title = ''

			@$scope.$apply( =>
				@addCat(catData)
			)

		@FACTORY = [ '$scope', '$element', '$attrs', '$compile', '$q', ($scope, $element, $attrs, $compile, $q) ->
			return new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q)
		]