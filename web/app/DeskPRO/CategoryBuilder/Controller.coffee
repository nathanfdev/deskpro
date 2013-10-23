define ->
	class DeskPRO_CategoryBuilder_Controller
		constructor: (@$scope, @$element, @$attrs, @$compile, @$q) ->
			@$scope.categoryBuilder = @
			@rootListEl = @$element.find('.dp-cb-root')

			me = @
			@$element.on('click', '.dp-cb-addbtn', (ev) ->
				ev.preventDefault()
				me.addNewCatFromTrigger(this)
			)

		setModel: (@ngModel) ->
			@cat_rows = {}
			@ngModel.render = =>
				@updateView(@ngModel.$modelValue)

		updateView: (cats) ->
			for cat in cats
				if @cat_rows[cat.id]?
					@cat_rows[cat.id][0].detach()
				else
					@cat_rows[cat.id] = @renderRow(cat)

			old_p = @rootListEl.parent()
			@rootListEl.detach()
			@_procCats(cats, @rootListEl, null)
			@rootListEl.appendTo(old_p)

		_procCats: (cats, parentRow, parent_id) ->
			for cat in cats
				do_add = false
				if not parent_id and not cat.parent_id
					do_add = true
				else if parent_id and cat.parent_id == parent_id
					do_add = true

				if not do_add then continue

				row = @cat_rows[cat.id][0]
				@_procCats(cats, row[0], cat.id)
				$(row[0]).appendTo(parentRow)

		renderRow: (cat) ->
			tpl = """
				<li class="dp-cb-row">
					<input type="text" class="form-control input-sm" ng-model="cat.title" />
					<ul>
						<li class="dp-cb-addrow">
							<div class="dp-cb-rowwrap">
								<input type="text" class="form-control input-sm" />
								<button class="btn btn-xs dp-cb-addbtn">Add</button>
							</div>
						</li>
					</ul>
				</li>
			"""

			rowScope = @$scope.$new()
			rowScope.cat = cat
			newRow = @$compile(tpl)(rowScope)
			newRow.find('.dp-cb-addrow').data('parent-id', cat.id)
			return [newRow, rowScope]

		addCat: (catData) ->
			@ngModel.$modelValue.push(catData)
			@updateView(@ngModel.$modelValue)

		addNewCatFromTrigger: (triggerEl) ->
			rowEl = $(triggerEl).closest('.dp-cb-addrow')
			title = $.trim(rowEl.find('input').val() || '')

			if title == ''
				return

			catData = {
				id:            _.uniqueId('cb_'),
				is_new:        true,
				title:         title,
				parent_id:     null,
				display_order: 0
			}

			if rowEl.data('parent_id')
				catData.parent_id = rowEl.data('parent_id')

			@$scope.$apply( =>
				@addCat(catData)
			)

		@FACTORY = [ '$scope', '$element', '$attrs', '$compile', '$q', ($scope, $element, $attrs, $compile, $q) ->
			return new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q)
		]