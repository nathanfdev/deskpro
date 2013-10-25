define ->
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

			tpl = """
				<div class="dp-cb-newrow">
					<input type="text" class="form-control" ng-model="new_cat_title" placeholder="Enter a title..." />
					<span class="dp-cb-select-wrap">
						<select ng-model="new_cat_parent"
							ui-select2
							style="min-width:200px;"
						>
							<option value="{{c.id}}" ng-repeat="c in parent_cat_list">{{c.title}}</option>
						</select>
					</span>
					<button class="btn dp-cb-addbtn">Add</button>
				</div>
			"""

			@addRowEl = @$compile(tpl)(@$scope)
			@addRowEl.appendTo(@$element)

			@rootListEl = @$compile('<ul class="dp-cb-root" ui-sortable="sortedListOptions"></ul>')(@$scope)
			@rootListEl.appendTo(@$element)

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

				for id in removeIds
					delete me.cat_rows[id]
					idx = null
					for cat,k in me.ngModel.$modelValue
						if cat.id == id
							idx = k
							break
					if idx != null
						me.ngModel.$modelValue.splice(idx,1)

				row.slideUp(200, ->
					row.remove()
					me.updateView(me.ngModel.$modelValue)
				)
			)

		updateOrder: ->


		setModel: (@ngModel) ->
			@cat_rows = {}
			@ngModel.$render = =>
				@updateView(@ngModel.$modelValue)

		updateView: (cats) ->
			for cat in cats
				if @cat_rows[cat.id]?
					@cat_rows[cat.id][0].detach()
				else
					@cat_rows[cat.id] = @renderRow(cat)

			@$scope.parent_cat_list.length = 0
			@$scope.parent_cat_list.push({
				id: 0,
				title: 'No Parent'
			})
			old_p = @rootListEl.parent()
			@rootListEl.detach()
			@_procCats(cats, @rootListEl, 0)
			@rootListEl.find('.dp-cb-addrow').each(->
				list = this.parentNode;
				$(this).detach().appendTo(list)
			)
			@rootListEl.prependTo(old_p)

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
						<div class="dp-cb-row-move"><i class="icon-reorder"></i></div>
						<div class="dp-cb-row-controls">
							<i class="icon-remove remove-trigger"></i>
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
			@ngModel.$modelValue.push(catData)
			@updateView(@ngModel.$modelValue)

		addNewCatFromTrigger: (triggerEl) ->
			rowEl = $(triggerEl).closest('.dp-cb-addrow')
			title = $.trim(@$scope.new_cat_title)

			if title == ''
				return

			parent_id = parseInt(@$scope.new_cat_parent)
			if not parent_id
				parent_id = null

			catData = {
				id:            _.uniqueId('cb_'),
				"@is_new":     true,
				title:         title,
				parent_id:     parent_id,
				display_order: 0
			}

			if rowEl.data('parentId')
				catData.parent_id = rowEl.data('parentId')

			@$scope.$apply( =>
				@addCat(catData)
			)

		@FACTORY = [ '$scope', '$element', '$attrs', '$compile', '$q', ($scope, $element, $attrs, $compile, $q) ->
			return new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q)
		]