define ->
  ###
    # Description
    # -----------
    #
    # This turns an element into an iOS7-style toggle on/off switch. It toggles the connected
    # model to true or false.
    #
    # Additional Attributes
    # ---------------------
    #
    # * locked-model: The model which indicates if the toggle is current locked
    # * ng-change:    A callback to fire when the model changes
    # * locked-tip:   A tooltip to display when the toggle is currently locked (locked-model is true)
    #
    # Example View
    # ------------
    # <button
    #     dp-toggle-switch
    #     locked-model="groupObj.perms.assign.locked"
    #     ng-model="groupObj.perms.assign.state"
    #     ng-change="TicketDepsEdit.propogatePermission(groupObj, 'assign')"
    #     locked-tip="This is locked because the 'full' permission is enabled"
    # ></button>
    ###
  Admin_Main_Directive_DpToggleSwitch = [ ->
    return {
      restrict: 'A',
      require:  ['ngModel', '^?form'],
      template: """
        <div class="dp-switch">
          <label><span></span></label>
        </div>
      """,
      replace: true,
      link: (scope, element, attrs, ctrls) ->

        ngModel = ctrls[0]
        formCtrl = ctrls[1] || null

        if formCtrl
          formCtrl.$addControl(ngModel)

          element.on('$destroy', ->
            formCtrl.$removeControl(ngModel)
          )

        ngModel.$viewChangeListeners.push(->
          ngModel.$render()
        )

        ngModel.$render = ->
          val = ngModel.$viewValue
          if val
            element.addClass('switch-on')
            element.removeClass('switch-off')
          else
            element.removeClass('switch-on')
            element.addClass('switch-off')

        element.on('click', (ev) ->
          ev.preventDefault()
          ev.stopPropagation()

          if element.hasClass('locked')
            return

          scope.$apply(->
            ngModel.$setViewValue(!ngModel.$viewValue)
          )
        )

        if attrs.lockedModel
          scope.$watch(attrs.lockedModel, (newVal) ->
            if newVal
              element.addClass('locked')
            else
              element.removeClass('locked')
          )

        if attrs.lockedTip
          tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>')
          tipTarget.attr('title', attrs.lockedTip)
          tipTarget.appendTo(element)
          tipTarget.tooltip({
            placement: 'auto top',
            trigger: 'hover',
            container: 'body'
          })
    }
  ]

  return Admin_Main_Directive_DpToggleSwitch