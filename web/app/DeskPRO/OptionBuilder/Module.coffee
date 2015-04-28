define [
  'angular',
  'DeskPRO/OptionBuilder/Controller',
  'DeskPRO/Util/Arrays'
], (
  angular,
  DeskPRO_OptionBuilder_Controller,
  Arrays
) ->
  angular.module('deskpro.option_builder', [])
    .directive('dpOptionBuilder', [ ->
      return {
        restrict: 'E',
        templateUrl: DP_BASE_ADMIN_URL+'/load-view/OptionBuilder/control.html',
        replace: true,
        transclude: true,
        controller: DeskPRO_OptionBuilder_Controller.FACTORY,
        controllerAs: 'OptionBuilder',
        scope: {
          getTypesDef: '&typesDef',
          getOptions:  '&options',
          optionTypes: '=optionTypes',
          saveTarget: '=saveTarget'
        }
      }
    ])
    .directive('dpOptionbuilderRow', [ '$timeout', ($timeout) ->
      return {
        restrict: 'E',
        template: """
          <div class="dp-ob-row">
            <div class="remove-row-trigger" ng-click="rowFn.removeRow()" ng-if="!rowOpts.hideRemove"><i class="fa fa-times-circle"></i></div>
            <table cellspacing="0" cellpadding="0" width="100%" style="margin: 0; padding: 0; border: none;">
              <tr>
                <td style="vertical-align: middle; padding: 0; margin: 0;"><div class="dp-ob-row-tag-wrap"></div></td>
                <td style="vertical-align: middle; padding: 0; margin: 0;" width="1"><input type="checkbox" id="{{rowOpts.withCheckId}}" class="rowOpts-withCheck" ng-if="rowOpts.withCheck" ng-model="rowOpts.rowEnabled" ng-disabled="rowOpts.isFixedOn" /></td>
                <td style="vertical-align: middle; padding: 0; margin: 0;" width="100%">
                  <div class="dp-ob-row-content-wrap" ng-class="{'as-post-render': doShow}">
                    <div class="dp-ob-row-content-placeholder" ng-if="!doShow">
                      <span class="place1"></span> <span class="place2"></span> <span class="place3"></span>
                    </div>
                    <div class="dp-ob-row-content" ng-class="{'as-post-render': doShow}" ng-transclude></div>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        """,
        replace: true,
        transclude: true,
        link: (scope, element, attrs) ->
          tagWrap = element.find('.dp-ob-row-tag-wrap')
          updateTag = ->
            tag = tagWrap.find('.dp-ob-row-tag')
            if scope.rowOpts?.rowIdx > 1 and scope.rowOpts?.tagString
              if not tag[0]
                tag = $('<em class="dp-ob-row-tag"></em>').addClass(scope.rowOpts.tagClass)
                tag.prependTo(tagWrap)

              tag.text(scope.rowOpts?.tagString)
              tagWrap.addClass('with-tag')
            else
              if tag[0] then tag.remove()
              tagWrap.addClass('without-tag')

          scope.$watch('rowOpts.rowIdx', ->
            updateTag()
          )
          updateTag()

          $timeout(->
            $timeout(->
              $timeout(-> scope.doShow = true)
            )
          )
      }
    ]).directive('dpOptionBuilderSet', [ '$compile', '$templateCache', ($compile, $templateCache) ->
      return {
      restrict: 'A',
      link: (scope, iElement, iAttrs) ->

        rows = []
        opts = scope.$eval(iAttrs.dpOptionBuilderSet)
        scope.setCount = 0
        lastEmpty = null
        containRow = iElement.find('.dp-ob-addition-setrow')

        reset = (withSet) ->
          opts = scope.$eval(iAttrs.dpOptionBuilderSet)
          scope.setCount = 0
          lastEmpty = null
          containRow.empty()
          rows = []

          any = false
          for own setId, set of withSet
            addRow(setId)
            any = true

          if not any
            addRow()

        scope.$watch(iAttrs.setsObject, (newVal) ->
          reset(newVal)
        )

        recountRows = ->
          for row, i in rows
            if row.rowScope.setIndex != i+1
              row.rowScope.$apply(->
                row.rowScope.setIndex = i+1

                if row.rowScope.setIndex == 1
                  row.element.find('.remove-btn-wrap').hide()
                else
                  row.element.find('.remove-btn-wrap').show()
              )

        addRow = (useExistSetId) ->
          tpl = $templateCache.get(opts.template)
          rowScope = scope.$new()

          setsObject = scope.$eval(iAttrs.setsObject)
          if not setsObject
            setsObject = {}

          if useExistSetId
            setId = useExistSetId
          else
            setId = rowScope.$id
            setsObject[setId] = {}

          rowScope.criteria_typedef = opts.typedef
          rowScope.criteria_set_row = setsObject[setId]
          rowScope.option_types     = opts.option_types

          rowScope.$on('rowAdded', ->
            if (element.hasClass('empty') or scope.setCount <= 1) and lastEmpty == element
              addRow()

            element.removeClass('empty')
          )
          rowScope.$on('rowRemoved', (ev, ctrl, e, s, rowsCount) ->
            if rowsCount == 0
              if scope.setCount == 1
                element.addClass('empty')
          )
          rowScope.setIndex = rows.length+1

          element = $compile(tpl)(rowScope)

          rows.push({
            rowScope: rowScope,
            element: element
          })

          if scope.setCount >= 1
            element.addClass('empty')

          if rowScope.setIndex == 1
            element.find('.remove-btn-wrap').hide()
          else
            element.find('.remove-btn-wrap').show()

          element.find('.removerow_btn').on('click', (ev) ->
            ev.preventDefault()
            rowScope.$destroy()
            scope.setCount -= 1
            element.remove()

            Arrays.findAndRemove(rows, (v) -> v.rowScope == rowScope)

            # unset options that were on the set so the model is updated
            for own k,v of rowScope.criteria_set_row
              delete rowScope.criteria_set_row[k]

            recountRows()
            if scope.setCount == 0
              addRow()
          )

          containRow.append(element)
          scope.setCount += 1
          lastEmpty = element

        iElement.find('.add_btn').on('click', (ev) ->
          ev.preventDefault()
          addRow()
        )

        reset()
      }
    ])