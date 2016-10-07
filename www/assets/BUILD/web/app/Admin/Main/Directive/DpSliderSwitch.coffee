define ->
  ###
    # Description
    # -----------
    #
    # This turns an element into an iOS7-style toggle on/off switch. It toggles the connected
    # model to true or false.
    #
    # (This is similar to DpToggleSwitch, this is just cleaner; aka 'version 2' of that component)
    #
    # Additional Attributes
    # ---------------------
    #
    # * is-locked:    Expression to evaluate when checking if the locked symbol is on
    # * is-on:        Expression to evaluate when showing this as 'on'. When ng-model is true or when this is true, then it shows on
    # * is-some:      Expression to evaluate when showing if a sub-option is one. Use this to show 'half on' status.
    # * ng-model:     The on/off model
    # * locked-tip:   A string for the locked tooltop
    # * locked-top-e: An expression that returns a string
    #
    # Example View
    # ------------
    # <input
    #     dp-slider-switch
    #     ng-model="myModel"
    #     is-on="myOtherModel.showAsOn"
    #     is-locked="myOtherModel.isLocked"
    #     locked-tip="This is locked because the 'full' permission is enabled"
    # />
    ###
  Admin_Main_Directive_DpToggleSwitch = [ ->
    return {
      restrict: 'A',
      require:  'ngModel',
      template: """
        <div class="dp-switch">
          <label><span></span></label>
        </div>
      """,
      replace: true,
      scope: {
        isLocked: "=?",
        isOn: "=?",
        isSome: "=?",
        lockedTipE: "=?"
      }
      link: (scope, element, attr, ngModel) ->

        ngModel.$render = ->
          if scope.isOn || ngModel.$viewValue
            element.addClass('switch-on')
            element.removeClass('switch-off switch-some')
          else if scope.isSome
            element.removeClass('switch-on switch-off')
            element.addClass('switch-some')
          else
            element.removeClass('switch-on switch-some')
            element.addClass('switch-off')

          if scope.isLocked
            element.addClass('locked')
          else
            element.removeClass('locked')

        element.on('click', (ev) ->
          ev.preventDefault()
          ev.stopPropagation()

          if element.hasClass('locked')
            return

          scope.$apply(->
            ngModel.$setViewValue(!ngModel.$viewValue)
          )
          ngModel.$render()
        )

        if attr.lockedTip or scope.lockedTipE
          tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>')
          tipTarget.appendTo(element)
          tipTarget.tooltip({
            placement: 'auto top',
            trigger: 'hover',
            container: 'body',
            title: ->
              if scope.lockedTipE
                return scope.lockedTipE
              else
                return attr.lockedTip
          })
    }
  ]

  return Admin_Main_Directive_DpToggleSwitch