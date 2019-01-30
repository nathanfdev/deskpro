define ['DeskPRO/Util/Util'], (Util) ->
  Admin_License_Directive_PaymentFormDirective = [ ->
    return {
      restrict: 'A',
      require: "ngModel",
      link: (scope, element, attrs, model) ->
        scope.form = {}
        scope.localForm = { set_mode: null }

        getCcType = (number) ->
          return "" if not number
          re = new RegExp("^4");
          if number.match(re) != null
            return "visa";

          re = new RegExp("^(34|37)");
          if number.match(re) != null
            return "amex";

          re = new RegExp("^5[1-5]");
          if number.match(re) != null
            return "mastercard";

          re = new RegExp("^6011");
          if (number.match(re) != null)
            return "discover";

          return "unknown";

        model.$formatters.push( (modelValue) ->
          if not modelValue then modelValue = {}

          value = modelValue
          if not value.new_card then value.new_card = {}
          if not value.address then value.address = {}

          if not value.exist_card or Util.isBlankObject(value.exist_card)
            value.exist_card = null

          if not value.new_card.expire_mm then value.new_card.expire_mm = '0'
          if not value.new_card.expire_yy then value.new_card.expire_yy = '0'
          value.new_card.expire_mm = value.new_card.expire_mm + ""
          value.new_card.expire_yy = value.new_card.expire_yy + ""

          return value
        )

        model.$parsers.push( (viewValue) ->
          value = viewValue
          value.type = getCcType(viewValue.new_card.number)
          return value
        )

        scope.numberCharsFn = ->
          scope.form.new_card.number = (scope.form.new_card.number||"").replace(/[^0-9]/g, '')
          scope.form.new_card.type = getCcType(scope.form.new_card.number)
        scope.cv2CharsFn    = -> scope.form.new_card.cv2 = (scope.form.new_card.cv2||"").replace(/[^0-9]/g, '').substr(0,4)

        scope.$watch('form', (form) ->
          model.$setViewValue(form)
        , true)

        model.$render = ->
          value = model.$viewValue || {}
          scope.form = value
          scope.invoice = value.invoice || null
    }
  ]

  return Admin_License_Directive_PaymentFormDirective