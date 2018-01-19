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
      v = el.find('.stat-value')
      el.find('.stat-value').html(value)
      if description
        el.find('.stat-description').html(description)
      else
        el.find('.stat-description').remove()

      # dynamic handler position
      el = $(element)
      box = el.parent()
      listItem = box.parent()

      listItem.scroll () ->

        v = box.offset().top - 47 - listItem.offset().top

        resHandlers = listItem.find('.gridster-item-resizable-handler')

        resHandlers.each (index, element) ->
          h = $(this)
          c = 1 + t
          h[0].style.bottom = "#{c}px"
    }
  ]

  return Reports_Directive_DashboardStat