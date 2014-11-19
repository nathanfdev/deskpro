define ['DeskPRO/Util/Util'], (Util) ->
  ###
  # The dpOptionBuilder directive is a component that handles a form that adds/removes rows
  # (e.g., a search builder, an option builder etc)
  #
  # The dpOptionBuilder is made up of a few parts:
    #
  # - The options select box which defines the available options to choose from
    #
  # - Type definitions (the typesDef attribute) must be an object that
  # knows how to create the available options. It knows the correct template,
  # knows how to fetch the required data (e.g., options for a select box) and
  # optionally can specify methods to convert model data to and from the stored value.
    #
    # typesDef must have a getDef method that returns an object with these methods:
    # - getTemplate: Returns either a string template or a promise if the template is loaded somewhere else
    # - getData: Returns an object or a promise
    # - getDataFormatter: Optional. An object with getViewValue() to convert the view data into
    #                     a saveable model, and getValue() to convert a saved model to the view.
    #
    # Example
    # -------
    #
    # view.html:
    #
  #    <dp-option-builder types-def="myTypeDef" ng-model="trigger_criteria">
  #     <select>
  #       <option value="0">Add criteria</option>
  #       <option value="my_row">Example</option>
  #     </select>
  #    </dp-option-builder>
    #
    # myTypeDef.coffee:
  #     {
  #         getDef: (type) ->
  #           return {
  #           getTemplate: ->
  #             return "<dp-optionbuilder-row>{{ title }} <input type="text" ng-model="model.value" /></dp-optionbuilder-row>"
  #
  #           getData: ->
  #               return { title: "My Input" }
  #
  #             getDataFormatter: {
  #               getViewValue: (value) ->
    #             return { value: value.my_value }
    #             getValue: (view_model) ->
    #               return { my_value: view_model.value }
  #             }
  #           }
  #     }
  #
  ###
  class DeskPRO_OptionBuilder_Controller
    constructor: ($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) ->
      @dpTemplateManager = dpTemplateManager
      @$scope            = $scope
      @$compile          = $compile
      @element           = $element
      @attrs             = $attrs
      @rows              = {}
      @rowsCount         = 0
      @$q                = $q
      @typesDef          = @$scope.getTypesDef()
      @options           = @$scope.getOptions() || {}
      @$injector         = $injector
      @$timeout          = $timeout
      @currentAddPromise = null
      @resetTime         = (new Date()).getTime()

      @els = {}

      $transclude( (clone) =>
        select = $('<select/>').css('width', '100%')

        addBtnLabel = clone.filter('add-btn-label')
        addBtnText = 'Add'
        if addBtnLabel[0]
          addBtnText = addBtnLabel.text()

        @element.find('.select2-wrap').append(select)
        @element.find('.add_btn').find('label').text(addBtnText);
      )

      # The list to append options to
      @els.optionList = @element.find('.dp-ob-options')

      # The no options message
      @els.noOptionsMessage = @els.optionList.find('.dp-ob-no-options')

      # The loading message
      @els.loadingOptionMessage = @els.optionList.find('.dp-ob-loading-options')

      if @typesDef.loadDataOptions?
        @typesDef.loadDataOptions().then(=>
          @hasLoaded = true
          @initControl()
        )
      else
        @hasLoaded = true
        @initControl()


    initControl: ->
      # Select box
      @els.select = @element.find('.select2-wrap').find('select').first()
      @els.select.select2({
        dropdownCssClass: 'dp-ob-select2'
      })
      @els.select.on('change', =>
        selected_opt = @els.select.find(':selected').first()
        @addRow(selected_opt.val(), {})
        @els.select.select2('val', '0')
      )

      @updateOptionTypes()

      @$scope.$watchCollection('optionTypes', =>
        @updateOptionTypes()
      )

      # This is a shallow-watch on purpose
      # This builder isnt designed for full model-value syncing like ngModel
      # So this is looking for the actual saveTarget being changed (eg a new object)
      # Otherwise, the object is fully managed internally
      @$scope.$watch('saveTarget', =>
        @reset()
      )

    reset: ->
      @resetTime = (new Date()).getTime()
      if not @hasLoaded then return
      @rowsCount = 0

      for id, row of @rows
        row.element.remove()
        row.scope.$destroy()
        delete @rows[id]

      @els.noOptionsMessage.show()

      alreadyDone = {}
      if @options.fixedExpanded
        for f in @options.fixedExpanded
          exist = null
          existId = null
          for own rowId, term of @$scope.saveTarget
            if term and term.type and term.type == f
              exist = term
              existId = rowId
              alreadyDone[rowId] = true
              break

          @addRow(f, exist, existId, true, @options.fixOn?.indexOf(f) != -1)

      if @$scope.saveTarget
        for own rowId, term of @$scope.saveTarget
          if alreadyDone[rowId] then continue
          if term and term.type
            @addRow(term.type, term, rowId)

    ###
      # Updates the option types available in the select box
      ###
    updateOptionTypes: ->
      @els.select.empty()
      @els.select.append($('<option value="0" />'));
      for item in @$scope.optionTypes
        if item.subOptions?
          optgroup = $('<optgroup/>').attr('label', item.title)
          for subItem in item.subOptions
            opt = $('<option/>').val(subItem.value).text(subItem.title)
            optgroup.append(opt)
          @els.select.append(optgroup)
        else
          opt = $('<option/>').val(item.value).text(item.title)
          @els.select.append(opt)

    ###
      # Add a new row to the form
      #
      # @param {String} type
      # @param {Object} value
    ###
    addRow: (type, value, existId, isFixed, isFixedOn) ->
      if not @hasLoaded then return
      def = @typesDef.getDef(type)

      tplPromise    = def.getTemplate()
      dataPromise   = def.getData()
      dataFormatter = def.getDataFormatter()
      scopeInit     = if def.scopeInit? then def.scopeInit else null

      # They might optionally return values rather than promises
      # so wrap in a promise to simplify the api
      if not @$q.isPromise(tplPromise)
        retTpl = tplPromise
        tplPromise = @$q.fcall(->
          return retTpl
        )
      if not @$q.isPromise(dataPromise)
        retData = dataPromise
        dataPromise = @$q.fcall(->
          return retData
        )

      placeholder = $('<div/>')
      @els.optionList.append(placeholder)

      rowIdx = @rowsCount+1
      @rowsCount += 1

      time = (new Date()).getTime()
      @els.loadingOptionMessage.show().addClass('loading-on')
      run = (tpl, data, isRetry) =>
        # the row was added before we did our last reset
        if time < @resetTime then return

        option_title = null
        for v in @$scope.optionTypes
          if v.subOptions
            for sb in v.subOptions
              if type == sb.value
                option_title = sb.title
                break
          else
            if type == v.value
              option_title = v.title
          if option_title then break

        # sometimes titles may be regenerated elsewhere
        # so we need to wait a bit then try again so we
        # can show the proper title
        if not option_title and (not isRetry or isRetry < 20)
          @$timeout(->
            run(tpl, data, if isRetry then 1 else isRetry+1)
          , 140)
          return

        rowScope = @$scope.$new()
        rowScope.type = type
        rowScope.type_title = option_title

        for own k,v of @typesDef.getVars()
          rowScope[k] = v

        if existId
          rowId = existId
        else
          rowId = rowScope.$id

        if dataFormatter
          rowScope.value = value || {}

          rowScope.model = dataFormatter.getViewValue(rowScope.value, data)

          rowScope.$watch('model', =>
            rowScope.value = dataFormatter.getValue(rowScope.model, data)
            @$scope.saveTarget[rowId] = rowScope.value

            if rowScope.rowOpts.rowEnabled
              @$scope.saveTarget[rowId].DP_DISABLED = false
              delete @$scope.saveTarget[rowId].DP_DISABLED
            else
              @$scope.saveTarget[rowId].DP_DISABLED = true
          , true)

        else
          rowScope.model = {}
          rowScope.value = rowScope.model

          rowScope.$watch('model', =>
            rowScope.value = rowScope.model
            @$scope.saveTarget[rowId] = rowScope.value

            if rowScope.rowOpts.rowEnabled
              @$scope.saveTarget[rowId].DP_DISABLED = false
              delete @$scope.saveTarget[rowId].DP_DISABLED
            else
              @$scope.saveTarget[rowId].DP_DISABLED = true
          , true)

        if data
          for own k, v of data
            rowScope[k] = v

        if scopeInit
          @$injector.invoke(scopeInit, this, {
            '$scope': rowScope
          })

        rowScope.rowOpts = {}
        rowScope.rowOpts.rowEnabled = true
        if isFixed
          if existId or isFixedOn
            rowScope.rowOpts.rowEnabled = true
          else
            rowScope.rowOpts.rowEnabled = false

          rowScope.rowOpts.hideRemove = true
          rowScope.rowOpts.isFixedOn = isFixedOn
          rowScope.rowOpts.withCheck = true
          rowScope.rowOpts.withCheckId = Util.uid('check')

        rowScope.rowOpts.rowIdx = rowIdx
        rowScope.rowOpts.tagString = @options.tagString || null
        rowScope.rowOpts.tagClass = @options.tagClass || ''
        element = @$compile(tpl)(rowScope)

        rowScope.rowFn = {}
        rowScope.rowFn.removeRow = =>
          @removeRow(element)

        if rowScope.rowOpts.withCheckId
          element.find('.row-label').attr('for', rowScope.rowOpts.withCheckId)

        rowScope.$emit('rowAdded', this, element, rowScope)

        element.data('scopeId', rowId)
        @els.loadingOptionMessage.hide().removeClass('loading-on')
        @els.noOptionsMessage.hide()
        placeholder.replaceWith(element)
        @rows[rowId] = {
          element: element,
          scope: rowScope
        }
        @$scope.saveTarget[rowId] = rowScope.value

        if isFixed and not isFixedOn
          if rowScope.rowOpts.rowEnabled
            @$scope.saveTarget[rowId] = rowScope.value
          else
            @$scope.saveTarget[rowId] = {DP_DISABLED: true}

          rowScope.$watch('rowOpts.rowEnabled', (rowEnabled) =>
            if rowEnabled
              delete rowScope.value.DP_DISABLED
            else
              rowScope.value.DP_DISABLED = true
          )

      @$q.all([tplPromise, dataPromise]).then( (returns) =>
        tpl  = returns[0]
        data = returns[1]
        run(tpl, data)
      )

    ###
      # Removes a row by element
      #
      # @param {HTMLElememnt} row
    ###
    removeRow: (row) ->
      scopeId = $(row).data('scopeId')
      return @removeRowById(scopeId)


    ###
      # Removes a row by a scope ID
      #
      # @param {Integer} scopeId
    ###
    removeRowById: (scopeId) ->
      row = @rows[scopeId]

      delete @rows[scopeId]
      delete @$scope.saveTarget[scopeId]

      row.scope.$emit('rowRemoved', this, row.element, row.scope, @rowsCount-1)

      row.element.remove()
      row.scope.$destroy()

      @rowsCount--
      if @rowsCount == 0
        @els.noOptionsMessage.show()
      else
        idx = 1
        for own k, row of @rows
          row.scope.rowOpts.rowIdx = idx
          idx++

      return true

    @FACTORY = [ '$scope', '$element', '$attrs', '$transclude', 'dpTemplateManager', '$compile', '$q', '$injector', '$timeout', ($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) ->
      return new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout)
    ]