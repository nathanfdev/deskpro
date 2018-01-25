define ->
  Reports_Directive_DashboardStat = ['$state', ($state) ->
    return {
    restrict: 'E',
    replace: true,
    template: """
      <div class="stat">
          <div class="stat-value"></div>
          <div class="stat-description"></div>
      </div>
    """
    link: (scope, element, attrs) ->
      value       = attrs.value
      description = attrs.description

      el = $(element)
      valueElement = el.find('.stat-value')
      valueElement.html(value)
      if description
        el.find('.stat-description').html(description)
      else
        el.find('.stat-description').remove()

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