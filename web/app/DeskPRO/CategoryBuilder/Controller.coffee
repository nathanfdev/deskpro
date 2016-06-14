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
    constructor: (@$scope, @$element, @$attrs, @$compile, @$q, @$injector) ->
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

        id = row.data('catId')
        removeIds = [id]
        doRemoveIds = []
        'cb_' != id.toString().substr(0, 3) && doRemoveIds.push id

        row.find('li').each ->
          id = $(this).data('catId')
          removeIds.push(id)
          'cb_' != id.toString().substr(0, 3) && doRemoveIds.push id

        doRemove = ->
          viewValue = me.ngModel.$viewValue || []
          for id in removeIds
            delete me.cat_rows[id]
            idx = null
            for cat,k in viewValue
              if cat.id == id
                idx = k
                break
            if idx != null
              viewValue.splice(idx, 1)

          row.slideUp(200, ->
            me.$scope.$apply(->
              row.remove()
              me.ngModel.$setViewValue(viewValue)
              me.updateView(viewValue)
            )
          )

        # handle delete/update if only field type is defined
        option = row.data 'catId'
        $modal = me.$injector.get '$modal'
        try
          Api = me.$injector.get 'Api'
        catch error
          Api = null

        if !doRemoveIds.length || !me.$scope.fieldType || !Api
          return doRemove()

        Api.sendDelete('/custom_fields/option', {step: 1, type: me.$scope.fieldType, ids: doRemoveIds}).then(
          (res) ->
            # if nothing to do, just delete
            return doRemove() if !res.data.success || !res.data.options?

            $modal.open
              templateUrl: DP_BASE_ADMIN_URL + '/load-view/' + 'CustomFields/Common/delete-option-modal.html'
              controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->

                for k,v of res.data.options
                  delete res.data.options[k] if !me.cat_rows[k]

                $scope.dismiss = -> $modalInstance.dismiss()
                $scope.mode = 0
                $scope.options = res.data.options
                $scope.update_to = res.data.default
                $scope.type = me.$scope.fieldType
                $scope.name = row.children('div').children('input').val()

                $scope.confirm = ->
                  if $scope.mode
                    $scope.busy = true
                    data =
                      step:      2
                      type:      me.$scope.fieldType
                      ids:       removeIds
                      update_to: $scope.update_to
                    Api.sendDelete('/custom_fields/option', data)
                  doRemove()
                  $scope.dismiss()
              ]
          () ->
        )
      )

    updateOrder: ->
      order = 10
      cat_rows = @cat_rows
      @$element.find('.dp-cb-row').each(->
        rowId = $(this).data('cat-id')
        if not rowId or not cat_rows[rowId] then return
        cat_rows[rowId].display_order = order
        order += 10
      )

      viewValue = @ngModel.$viewValue || []
      for row in viewValue
        rowId = row.id
        if rowId and cat_rows[rowId]
          row.display_order = cat_rows[rowId].display_order

      @ngModel.$setViewValue(viewValue)

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
                  id: opt.id
                  title: title_segs.join(' > ')
                })

              title_segs.pop()

          return select_options

        @$scope.saveFlatArray = proc(null, [])

    _procCats: (cats, parentRow, parent_id, parent_titles = '', depth = 0) ->
      for cat in cats
        do_add = false
        if not parent_id and not cat.parent_id
          cat.depth = 0
          do_add = true
        else if parent_id and cat.parent_id == parent_id
          cat.depth = depth
          do_add = true

        if not do_add then continue

        if parent_titles.length
          full_title = parent_titles + ' > ' + cat.title
        else
          full_title = cat.title

        if depth+1 < @maxDepth
          @$scope.parent_cat_list.push({
            id: cat.id
            title: full_title
          })

        row = @cat_rows[cat.id][0]
        @_procCats(cats, $(row[0]).find('> ul').first(), cat.id, full_title, depth + 1)
        $(row[0]).appendTo(parentRow)

    renderRow: (cat) ->
      tpl = """
        <li class="dp-cb-row" data-cat-id="{{cat.id}}">
          <div class="dp-cb-titlewrap" style="padding-left: {{ 33 + cat.depth * 15 }}px;">
            <div class="dp-cb-row-move"><i class="fa fa-bars"></i></div>
            <div class="dp-cb-row-controls">
              <i class="fa fa-times-circle remove-trigger"></i>
            </div>
            <div class="dp-cb-row-indent" style="padding-right: 0px; width: {{ cat.depth * 15 }}px;"></div>
            <span class="title-id" title="ID" ng-if="cat.id && !cat['@is_new']">#<span ng-bind="cat.id"></span></span>
            <span class="title-id" title="ID will be generated after you save" ng-if="cat['@is_new']">?</span>
            <input type="text" name="{{ fieldName }}" class="form-control dp-cb-input" ng-model="cat.title" placeholder="Enter title..." />
          </div>
          <ul ui-sortable="sortedListOptions"></ul>
        </li>
      """

      rowScope = @$scope.$new()
      rowScope.sortedListOptions = @$scope.sortedListOptions

      rowScope.cat = cat
      rowScope.fieldName = cat.field_name || rowScope.fieldName
      newRow = @$compile(tpl)(rowScope)
      newRow.data('catId', cat.id)
      return [newRow, rowScope]

    addCat: (catData) ->
      viewValue = @ngModel.$viewValue || []
      viewValue.push(catData)
      @ngModel.$setViewValue(viewValue)
      @updateView(viewValue)

    getMaxDisplayOrder: (parentId) ->
      max = 10

      viewValue = @ngModel.$viewValue || []
      for row in viewValue
        if (parentId and (row.parent_id? and (row.parent_id+"") == (parentId+""))) or not parentId
          if row.display_order >= max
            max = row.display_order + 10

      return max

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
        id:        Util.uid('cb_')
        "@is_new": true
        title:     title
        parent_id: parent_id
        display_order: @getMaxDisplayOrder(parent_id)
      }

      if rowEl.data('parentId')
        catData.parent_id = rowEl.data('parentId')

      @$scope.new_cat_title = ''

      @$scope.$apply( =>
        @addCat(catData)
      )

    @FACTORY = ['$scope', '$element', '$attrs', '$compile', '$q', '$injector',
      ($scope, $element, $attrs, $compile, $q, $injector) ->
        return new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q, $injector)
    ]


    showDeleteOption: () ->
