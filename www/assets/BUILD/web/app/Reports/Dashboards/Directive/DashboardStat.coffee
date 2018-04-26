define ->
  Reports_Directive_DashboardStat = ['$state', 'DashboardWidgetService', ($state, DashboardWidgetService) ->
    return {
    restrict: 'E',
    replace: true,
    scope:
      widgetId: '@'
      loaded: '@'
    template: """
      <div style="height: auto">
        <div ng-hide='loaded' class="stat-value no-data">loading...</div>
        <div ng-show='loaded && noData' class="box stat-box"><div class="stat-value no-data">no data</div></div>
        <div ng-show='loaded' class="stat">
            <div class="stat-value"></div>
            <div class="stat-description"></div>
        </div>
      </div>
    """
    link: (scope, element, attrs) ->
      scope.loaded = false

      initValue = (result) ->
        scope.loaded = true

        el = $(element)
        box = el.parent()

        if !result
          return

        scope.noData = false

        try
          options = if result.options then JSON.parse(result.options) else {}
        catch e
          options = {}
          console.warn("invalid options")
          console.log(e)

        data = if result.data then JSON.parse(result.data) else []

        if options.click_url?
          vars = {};
          matches = options?.click_url.match(/\$\{([a-zA-z0-9_]+)\}/)
          url = options.click_url

          for match, index in matches
            if index % 2 == 1
              vars[match] = matches[index - 1]
            for key, variable of vars
              if data[key]?
                url = url.replace(variable, data[key])

          box.css {cursor: 'pointer'}
          box.click () -> window.open url

        valueElement = el.find('.stat-value')
        valueElement.html(result.value)
        if result.description
          el.find('.stat-description').html(result.description)
        else
          el.find('.stat-description').remove()

      if attrs.jsCode
        try
          eval(attrs.jsCode)
        catch e
          console.log(e)

        if promise and promise.then
          promise.then (response) ->
            scope.loaded = true
            scope.noData = true
            initValue(response)
      else if attrs.value
        initValue(attrs)
      else
        DashboardWidgetService
          .getWidget(scope.widgetId || 0)
          .then (widget) =>
            scope.loaded = true
            scope.noData = true
            if widget? && widget.rendered_result
              initValue(widget.rendered_result)

      # dynamic handler position
      el = $(element)
      box = el.parent()
      listItem = box.parent()

      listItem.scroll () ->

        valueElementTop = box.offset().top - 47 - listItem.offset().top

        resHandlers = listItem.find('.gridster-item-resizable-handler')

        resHandlers.each (index, element) ->
          h = $(this)
          c = 1 + valueElementTop
          h[0].style.bottom = "#{c}px"


    }
  ]

  return Reports_Directive_DashboardStat