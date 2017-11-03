define ->
  Reports_Directive_DashboardStat = ['$state', ($state) ->
    return {
    restrict: 'E',
    replace: true,
    template: """
      <div class="stat">
          <div id="stat-title"></div>
          <div id="stat-value"></div>
      </div>
    """
    link: (scope, element, attrs) ->
      title = attrs.title
      value = attrs.value

      el = $(element)
      t = el.find('#stat-title')
      el.find('#stat-title').html(title)
      el.find('#stat-value').html(value)


#      // dynamic handler position
      el = $(element)
      box = el.parent()
      listItem = box.parent()

      listItem.scroll () ->

        t = box.offset().top - 47 - listItem.offset().top

        resHandlers = listItem.find('.gridster-item-resizable-handler')

        resHandlers.each (index, element) ->
          h = $(this)
          c = 1 + t
          h[0].style.bottom = "#{c}px"
    }
  ]

  return Reports_Directive_DashboardStat